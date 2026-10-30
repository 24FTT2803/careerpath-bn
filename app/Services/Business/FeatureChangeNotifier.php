<?php

namespace App\Services\Business;

use App\Helpers\NotificationHelper;
use App\Models\FeatureDefinition;
use App\Models\User;
use Closure;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class FeatureChangeNotifier
{
    public function __construct(private EntitlementService $entitlements) {}

    /**
     * Save settings and one bell notification per affected student atomically.
     * Compare resolved access so sponsorship overrides and disabled parents count.
     *
     * @param  Collection<int|string, FeatureDefinition>  $definitions
     */
    public function during(Collection $definitions, Closure $change): void
    {
        if ($definitions->isEmpty()) {
            DB::transaction($change);

            return;
        }

        DB::transaction(function () use ($definitions, $change): void {
            $before = [];

            User::query()->where('role', 'student')->chunkById(100, function ($students) use (&$before, $definitions): void {
                foreach ($students as $student) {
                    $before[$student->id] = $this->snapshot($student, $definitions);
                }
            });

            $change();

            User::query()->where('role', 'student')->chunkById(100, function ($students) use ($before, $definitions): void {
                foreach ($students as $student) {
                    $after = $this->snapshot($student, $definitions);
                    $messages = [];

                    foreach ($definitions as $definition) {
                        $old = $before[$student->id][$definition->key] ?? null;
                        $new = $after[$definition->key];

                        if ($old === null || $old === $new || (! $old['allowed'] && ! $new['allowed'])) {
                            continue;
                        }

                        if (! $new['allowed']) {
                            $messages[] = $definition->name.': '.$new['message'];
                        } elseif (! $old['allowed']) {
                            $messages[] = $definition->name.' is now available to you.';
                        } else {
                            $messages[] = $definition->name.' has changed to '.$this->describe($new['value']).'.';
                        }
                    }

                    if ($messages !== []) {
                        NotificationHelper::create(
                            $student->id,
                            'system',
                            'Feature access updated',
                            'An administrator updated your feature access. These changes take effect now. '.implode(' ', $messages),
                            route('student.settings', absolute: false).'#your-plan'
                        );
                    }
                }
            });
        });
    }

    /**
     * Unavailable features carry no value: editing a hidden quota must not
     * announce a change to someone who cannot use its parent feature.
     *
     * @param  Collection<int|string, FeatureDefinition>  $definitions
     * @return array<string, array{allowed: bool, value: mixed, message: ?string}>
     */
    private function snapshot(User $student, Collection $definitions): array
    {
        $states = [];

        foreach ($definitions as $definition) {
            $access = $this->entitlements->featureAccess($student, $definition->key);
            $states[$definition->key] = [
                'allowed' => $access['allowed'],
                'value' => $access['allowed']
                    ? $this->entitlements->value($student, $definition->key)
                    : null,
                'message' => $access['allowed'] ? null : $access['message'],
            ];
        }

        return $states;
    }

    private function describe(mixed $value): string
    {
        if (! is_array($value)) {
            return (string) $value;
        }

        return match ($value['mode'] ?? null) {
            'unlimited' => 'unlimited use',
            'total' => $value['amount'].' total uses',
            'recurring' => $value['amount'].' uses every '.$value['period_value'].' '.$value['period_unit'].'(s)',
            default => 'an updated allowance',
        };
    }
}
