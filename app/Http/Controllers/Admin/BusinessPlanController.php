<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FeatureDefinition;
use App\Models\Plan;
use App\Models\PlanFeature;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class BusinessPlanController extends Controller
{
    /**
     * Display global feature controls and
     * configurable values for every plan.
     */
    public function index(): View
    {
        $plans = Plan::query()
            ->with('features')
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();

        $features = FeatureDefinition::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $featuresByCategory = $features
            ->groupBy('category');

        return view(
            'admin.business.plans.index',
            compact(
                'plans',
                'features',
                'featuresByCategory'
            )
        );
    }

    /**
     * Update configurable feature values for
     * a single plan.
     */
    public function updatePlan(
        Request $request,
        Plan $plan
    ): RedirectResponse {
        $request->validate([
            'features' => [
                'required',
                'array',
                'min:1',
            ],
        ]);

        $submittedFeatures =
            $request->input(
                'features',
                []
            );

        $featureIds = array_map(
            'intval',
            array_keys(
                $submittedFeatures
            )
        );

        $definitions = FeatureDefinition::query()
            ->whereIn(
                'id',
                $featureIds
            )
            ->get()
            ->keyBy('id');

        if (
            $definitions->count()
            !== count($featureIds)
        ) {
            throw ValidationException::withMessages([
                'features' =>
                    'One or more feature settings are invalid.',
            ]);
        }

        $rules = [];

        foreach (
            $submittedFeatures
            as $featureId => $input
        ) {
            $definition =
                $definitions->get(
                    (int) $featureId
                );

            if (! $definition) {
                continue;
            }

            $base =
                'features.' .
                $featureId;

            if (
                $definition->value_type
                === 'boolean'
            ) {
                $rules[
                    $base . '.value'
                ] = [
                    'required',
                    'boolean',
                ];

                continue;
            }

            if (
                $definition->value_type
                === 'number'
            ) {
                $rules[
                    $base . '.value'
                ] = [
                    'required',
                    'integer',
                    'min:1',
                ];

                continue;
            }

            if (
                $definition->value_type
                === 'quota'
            ) {
                $rules[
                    $base . '.mode'
                ] = [
                    'required',
                    Rule::in([
                        'unlimited',
                        'total',
                        'recurring',
                    ]),
                ];

                $mode =
                    $input['mode']
                    ?? null;

                if (
                    in_array(
                        $mode,
                        [
                            'total',
                            'recurring',
                        ],
                        true
                    )
                ) {
                    $rules[
                        $base . '.amount'
                    ] = [
                        'required',
                        'integer',
                        'min:1',
                    ];
                }

                if (
                    $mode === 'recurring'
                ) {
                    $rules[
                        $base .
                        '.period_value'
                    ] = [
                        'required',
                        'integer',
                        'min:1',
                    ];

                    $rules[
                        $base .
                        '.period_unit'
                    ] = [
                        'required',
                        Rule::in([
                            'minute',
                            'hour',
                            'day',
                            'week',
                            'month',
                            'year',
                        ]),
                    ];
                }

                continue;
            }

            throw ValidationException::withMessages([
                'features.' . $featureId =>
                    'Unsupported feature value type.',
            ]);
        }

        $validated =
            $request->validate(
                $rules
            );

        DB::transaction(
            function () use (
                $plan,
                $definitions,
                $validated
            ) {
                foreach (
                    $validated['features']
                    as $featureId => $input
                ) {
                    $definition =
                        $definitions->get(
                            (int) $featureId
                        );

                    if (! $definition) {
                        continue;
                    }

                    $value =
                        $this->normaliseValue(
                            $definition,
                            $input
                        );

                    PlanFeature::updateOrCreate(
                        [
                            'plan_id' =>
                                $plan->id,

                            'key' =>
                                $definition->key,
                        ],
                        [
                            'value' =>
                                $value,
                        ]
                    );
                }
            }
        );

        return redirect()
            ->route(
                'admin.business.plans.index'
            )
            ->with(
                'success',
                $plan->name .
                ' plan features updated successfully.'
            );
    }

    /**
     * Turn a feature on/off globally.
     *
     * A global OFF state overrides every plan
     * and sponsorship value.
     */
    public function updateGlobalFeature(
        Request $request,
        FeatureDefinition $feature
    ): RedirectResponse {
        $validated =
            $request->validate([
                'global_enabled' => [
                    'required',
                    'boolean',
                ],
            ]);

        $feature->update([
            'global_enabled' =>
                (bool)
                $validated[
                    'global_enabled'
                ],
        ]);

        return redirect()
            ->route(
                'admin.business.plans.index'
            )
            ->with(
                'success',
                $feature->name .
                ' global availability updated successfully.'
            );
    }

    /**
     * Convert validated form values into the
     * JSON-compatible values stored by PlanFeature.
     */
    private function normaliseValue(
        FeatureDefinition $definition,
        array $input
    ): mixed {
        if (
            $definition->value_type
            === 'boolean'
        ) {
            return filter_var(
                $input['value'],
                FILTER_VALIDATE_BOOLEAN
            );
        }

        if (
            $definition->value_type
            === 'number'
        ) {
            return (int)
                $input['value'];
        }

        if (
            $definition->value_type
            !== 'quota'
        ) {
            throw ValidationException::withMessages([
                'features' =>
                    'Unsupported feature value type.',
            ]);
        }

        $mode =
            $input['mode'];

        if ($mode === 'unlimited') {
            return [
                'mode' =>
                    'unlimited',
            ];
        }

        if ($mode === 'total') {
            return [
                'mode' =>
                    'total',

                'amount' =>
                    (int)
                    $input['amount'],
            ];
        }

        return [
            'mode' =>
                'recurring',

            'amount' =>
                (int)
                $input['amount'],

            'period_value' =>
                (int)
                $input[
                    'period_value'
                ],

            'period_unit' =>
                $input[
                    'period_unit'
                ],
        ];
    }
}