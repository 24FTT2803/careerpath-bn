<?php

namespace App\Services\Business;

use App\Models\FeatureDefinition;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

/**
 * The plan and feature values set for one special account, read
 * from the admin's form the same way a plan's are: an on/off, a
 * number, or an allowance (unlimited, a total, or so many per
 * period). Each feature can instead follow the plan.
 */
class AccountFeatureValues
{
    public const PERIODS = ['minute', 'hour', 'day', 'week', 'month', 'year'];

    /**
     * Validation rules for the special-account part of the form.
     *
     * @return array<string, mixed>
     */
    public function rules(Request $request): array
    {
        $rules = [
            'special_enabled' => ['nullable', 'boolean'],
        ];

        /* Unticked, nothing inside is read, so nothing inside is checked. */
        if (! $request->boolean('special_enabled')) {
            return $rules;
        }

        $rules += [
            'special' => ['nullable', 'array'],
            'special.plan' => ['nullable', 'string', Rule::exists('plans', 'code')->where('is_active', true)],
            'special.roles' => ['nullable', 'array'],
            'special.roles.*' => ['string', 'in:student,lecturer,admin'],
            'special.features' => ['nullable', 'array'],
        ];

        /*
         * Features are sent by their id. Their keys hold dots
         * ("cpbn_bot.token_quota"), which a form name would read
         * as levels and lose.
         */
        foreach ((array) $request->input('special.features', []) as $id => $input) {
            if (! is_array($input) || ($input['custom'] ?? '0') !== '1') {
                continue;
            }

            $definition = FeatureDefinition::query()->find((int) $id);
            $base = 'special.features.'.$id;

            if ($definition === null) {
                $rules[$base] = ['prohibited'];

                continue;
            }

            if ($definition->value_type === 'boolean') {
                $rules[$base.'.value'] = ['required', 'boolean'];
            } elseif ($definition->value_type === 'number') {
                $rules[$base.'.value'] = ['required', 'integer', 'min:1'];
            } elseif ($definition->value_type === 'quota') {
                $mode = $input['mode'] ?? null;
                $rules[$base.'.mode'] = ['required', Rule::in(['unlimited', 'total', 'recurring'])];

                if (in_array($mode, ['total', 'recurring'], true)) {
                    $rules[$base.'.amount'] = ['required', 'integer', 'min:1'];
                }

                if ($mode === 'recurring') {
                    $rules[$base.'.period_value'] = ['required', 'integer', 'min:1'];
                    $rules[$base.'.period_unit'] = ['required', Rule::in(self::PERIODS)];
                }
            }
        }

        return $rules;
    }

    /**
     * What is stored for the account, or null for an ordinary one.
     * Its own role is never listed among those it may switch to.
     *
     * @return array<string, mixed>|null
     */
    public function fromRequest(Request $request): ?array
    {
        if (! $request->boolean('special_enabled')) {
            return null;
        }

        $features = [];

        foreach ((array) $request->input('special.features', []) as $id => $input) {
            if (! is_array($input) || ($input['custom'] ?? '0') !== '1') {
                continue;
            }

            $definition = FeatureDefinition::query()->find((int) $id);

            if ($definition !== null) {
                $features[$definition->key] = $this->value($definition, $input);
            }
        }

        $roles = array_values(array_diff(
            array_intersect(['student', 'lecturer', 'admin'], (array) $request->input('special.roles', [])),
            [(string) $request->input('role')]
        ));

        $plan = $request->input('special.plan') ?: null;

        /* Ticked but nothing chosen: an ordinary account. */
        if ($plan === null && $features === [] && $roles === []) {
            return null;
        }

        return [
            'plan' => $plan,
            'features' => $features,
            'roles' => $roles,
        ];
    }

    /**
     * The plans a special account can be put on.
     *
     * @return Collection<int, Plan>
     */
    public function plans()
    {
        return Plan::query()->where('is_active', true)->orderByDesc('is_default')->orderBy('name')->get();
    }

    /**
     * Every feature, grouped as on the plans page.
     *
     * @return Collection<string, Collection<int, FeatureDefinition>>
     */
    public function featuresByCategory()
    {
        return FeatureDefinition::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->groupBy(fn (FeatureDefinition $definition) => $definition->category ?: 'Other');
    }

    private function value(FeatureDefinition $definition, array $input): mixed
    {
        if ($definition->value_type === 'boolean') {
            return filter_var($input['value'] ?? false, FILTER_VALIDATE_BOOLEAN);
        }

        if ($definition->value_type === 'number') {
            return (int) ($input['value'] ?? 0);
        }

        $mode = $input['mode'] ?? 'unlimited';

        if ($mode === 'total') {
            return ['mode' => 'total', 'amount' => (int) $input['amount']];
        }

        if ($mode === 'recurring') {
            return [
                'mode' => 'recurring',
                'amount' => (int) $input['amount'],
                'period_value' => (int) $input['period_value'],
                'period_unit' => $input['period_unit'],
            ];
        }

        return ['mode' => 'unlimited'];
    }
}
