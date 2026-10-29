<?php

namespace App\Services\Business;

use App\Models\FeatureDefinition;
use App\Models\User;
use App\Models\UserPlanGrant;

class StudentPlanSummary
{
    public function __construct(private EntitlementService $entitlements) {}

    /**
     * Resolve the student's actual access, including sponsorship and maintenance.
     *
     * @return array<string, mixed>
     */
    public function for(User $student): array
    {
        $access = $this->entitlements->accessFor($student);
        $features = FeatureDefinition::query()
            ->where('category', '!=', 'Advertising')
            ->orderBy('sort_order')->orderBy('name')->get()
            ->map(function (FeatureDefinition $definition) use ($student): array {
                $state = $this->entitlements->featureAccess($student, $definition->key);
                $value = $this->entitlements->value($student, $definition->key);

                return [
                    'name' => $definition->name,
                    'category' => $definition->category,
                    'status' => $state['allowed']
                        ? ($definition->value_type === 'boolean' ? 'Available' : $this->describe($definition->key, $value))
                        : $state['message'],
                ];
            })->groupBy('category');

        $history = $student->planGrants()
            ->whereHas('plan', fn ($query) => $query->where('code', 'premium'))
            ->where(function ($query): void {
                $query->where('is_active', false)
                    ->orWhere('ends_at', '<', now())
                    ->orWhere('starts_at', '>', now());
            })
            ->orderByDesc('id')->limit(5)->get()
            ->map(fn (UserPlanGrant $grant): array => [
                'status' => ! $grant->is_active ? 'Inactive'
                    : ($grant->ends_at?->isPast() ? 'Expired' : 'Scheduled'),
                'starts_at' => $grant->starts_at,
                'ends_at' => $grant->ends_at,
                'granted_at' => $grant->created_at,
            ]);

        return [
            'access' => $access,
            'source' => match ($access['source']) {
                'direct' => $access['grant']?->source === 'trial' ? 'Premium trial' : 'Direct account grant',
                'sponsored' => 'Organisation sponsorship',
                'default' => 'Default plan',
                default => 'No active plan',
            },
            'features' => $features,
            'history' => $history,
        ];
    }

    private function describe(string $key, mixed $value): string
    {
        if (! is_array($value)) {
            return str_ends_with($key, 'regeneration_cooldown')
                ? $value.' seconds'
                : (string) $value;
        }

        return match ($value['mode'] ?? null) {
            'unlimited' => 'Unlimited',
            'total' => $value['amount'].' total uses',
            'recurring' => $value['amount'].' uses every '.$value['period_value'].' '.$value['period_unit'].'(s)',
            default => 'Not configured',
        };
    }
}
