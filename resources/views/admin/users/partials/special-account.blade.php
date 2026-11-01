{{--
    A special account: for showing the platform, or for staff
    trying it, including an admin's own student side. Closed
    until ticked. Inside, the account can be put on a plan of its
    own, and any feature set for it alone, the same way a plan's
    features are; anything left alone follows the plan.
--}}
@php
    $featureValues = app(\App\Services\Business\AccountFeatureValues::class);
    $savedRules = $user?->special_rules ?? [];
    $specialOn = (bool) old('special_enabled', ! empty($savedRules));
    $specialPlan = old('special.plan', $savedRules['plan'] ?? '');
    $specialRoles = (array) old('special.roles', $savedRules['roles'] ?? []);
    $savedFeatures = $savedRules['features'] ?? [];
    $oldFeatures = old('special.features');
    $isOwnAccount = $user && $user->id === auth()->id();
@endphp

<div class="cpbn-special" id="special-account">
    <div class="cpbn-field" style="margin-bottom:0">
        <label class="cpbn-waive">
            <input type="hidden" name="special_enabled" value="0">
            <input type="checkbox" name="special_enabled" value="1" data-special-toggle @checked($specialOn)>
            <span>
                <span class="cpbn-waive-title">Special account</span>
                <span class="cpbn-hint" style="display:block;margin:2px 0 0;">
                    @if($isOwnAccount)
                        Your own student side: a plan and features of its own, instead of following the plans.
                    @else
                        Its own plan and feature values, and other roles it can switch to.
                    @endif
                </span>
            </span>
        </label>
    </div>

    <div class="cpbn-special-body" data-special-body @if(! $specialOn) hidden @endif>
        <div class="cpbn-field">
            <label for="special-plan">Plan</label>
            <select name="special[plan]" id="special-plan">
                <option value="">Follow the plans, as for anyone</option>
                @foreach($featureValues->plans() as $plan)
                    <option value="{{ $plan->code }}" @selected($specialPlan === $plan->code)>{{ $plan->name }}</option>
                @endforeach
            </select>
            @error('special.plan')<p class="err">{{ $message }}</p>@enderror
        </div>

        <details class="cpbn-special-features" @if(! empty($savedFeatures) || $errors->has('special.features.*')) open @endif>
            <summary>Features for this account <span class="cpbn-hint">(each follows the plan unless set here)</span></summary>

            @foreach($featureValues->featuresByCategory() as $category => $definitions)
                <p class="cpbn-special-sub">{{ $category }}</p>

                @foreach($definitions as $definition)
                    @php
                        $key = $definition->key;
                        $base = 'special[features]['.$key.']';
                        $input = $oldFeatures[$key] ?? null;
                        $saved = $savedFeatures[$key] ?? null;
                        $custom = $input !== null ? (($input['custom'] ?? '0') === '1') : array_key_exists($key, $savedFeatures);
                        $quota = is_array($saved) ? $saved : [];
                        $mode = $input['mode'] ?? ($quota['mode'] ?? 'unlimited');
                        $onOff = isset($input['value']) ? (string) $input['value'] : ($saved === false ? '0' : '1');
                    @endphp

                    <div class="cpbn-special-feature" data-special-feature>
                        <span class="cpbn-special-name">{{ $definition->name }}</span>

                        <select name="{{ $base }}[custom]" data-special-custom aria-label="{{ $definition->name }}">
                            <option value="0" @selected(! $custom)>Follow plan</option>
                            <option value="1" @selected($custom)>Set for this account</option>
                        </select>

                        <span class="cpbn-special-value" data-special-value @if(! $custom) hidden @endif>
                            @if($definition->value_type === 'boolean')
                                <select name="{{ $base }}[value]">
                                    <option value="1" @selected($onOff === '1')>On</option>
                                    <option value="0" @selected($onOff === '0')>Off</option>
                                </select>
                            @elseif($definition->value_type === 'number')
                                <input type="number" min="1" name="{{ $base }}[value]" value="{{ $input['value'] ?? (is_int($saved) ? $saved : '') }}">
                            @elseif($definition->value_type === 'quota')
                                <select name="{{ $base }}[mode]" data-special-mode>
                                    <option value="unlimited" @selected($mode === 'unlimited')>Unlimited</option>
                                    <option value="total" @selected($mode === 'total')>A total of</option>
                                    <option value="recurring" @selected($mode === 'recurring')>Per period</option>
                                </select>
                                <input type="number" min="1" placeholder="Amount" name="{{ $base }}[amount]" value="{{ $input['amount'] ?? ($quota['amount'] ?? '') }}" data-special-amount>
                                <span data-special-period>
                                    every
                                    <input type="number" min="1" name="{{ $base }}[period_value]" value="{{ $input['period_value'] ?? ($quota['period_value'] ?? 1) }}" style="width:70px">
                                    <select name="{{ $base }}[period_unit]">
                                        @foreach(\App\Services\Business\AccountFeatureValues::PERIODS as $unit)
                                            <option value="{{ $unit }}" @selected(($input['period_unit'] ?? ($quota['period_unit'] ?? 'month')) === $unit)>{{ $unit }}</option>
                                        @endforeach
                                    </select>
                                </span>
                            @endif
                        </span>
                    </div>

                    @foreach(['value', 'mode', 'amount', 'period_value', 'period_unit'] as $part)
                        @error('special.features.'.$key.'.'.$part)<p class="err">{{ $definition->name }}: {{ $message }}</p>@enderror
                    @endforeach
                @endforeach
            @endforeach
        </details>

        <p class="cpbn-special-sub">Can also switch to</p>

        <div class="cpbn-special-grid">
            @foreach(['student' => 'Student', 'lecturer' => 'Lecturer', 'admin' => 'Admin'] as $role => $title)
                <div class="cpbn-field" style="margin-bottom:0">
                    <label class="cpbn-waive">
                        <input type="checkbox" name="special[roles][]" value="{{ $role }}" @checked(in_array($role, $specialRoles, true))>
                        <span class="cpbn-waive-title">{{ $title }}</span>
                    </label>
                </div>
            @endforeach
        </div>
        <p class="cpbn-hint">Every admin can already switch to a student side of their own.</p>
        @error('special.roles')<p class="err">{{ $message }}</p>@enderror
    </div>
</div>

@once
    <style>
        .cpbn-special {
            margin: 20px 0 0;
        }

        .cpbn-special-body {
            margin-top: 12px;
            padding: 14px 16px 16px;
            border: 1px solid var(--line, #e7e2d4);
            border-radius: 6px;
        }

        .cpbn-special-body[hidden] {
            display: none;
        }

        .cpbn-special-features summary {
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 6px;
        }

        .cpbn-special-feature {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 8px;
            padding: 8px 0;
            border-bottom: 1px solid var(--line, #e7e2d4);
            font-size: 13px;
        }

        .cpbn-special-name {
            flex: 1 1 200px;
        }

        .cpbn-special-feature select,
        .cpbn-special-feature input {
            width: auto !important;
            padding: 6px 8px !important;
            font-size: 13px !important;
        }

        .cpbn-special-feature input[type="number"] {
            max-width: 110px;
        }

        .cpbn-special-value {
            display: inline-flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 6px;
        }

        .cpbn-special-value[hidden],
        [data-special-period][hidden],
        [data-special-amount][hidden] {
            display: none !important;
        }

        .cpbn-special-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 10px;
        }

        .cpbn-special-sub {
            margin: 14px 0 6px;
            font-size: 12px;
            font-weight: 600;
            color: var(--ink-dim, #5b6675);
            text-transform: uppercase;
            letter-spacing: .04em;
        }
    </style>

    <script>
        /* Open only when ticked; each feature's value only when set here. */
        document.addEventListener('DOMContentLoaded', function () {
            var toggle = document.querySelector('[data-special-toggle]');
            var body = document.querySelector('[data-special-body]');

            if (toggle && body) {
                toggle.addEventListener('change', function () {
                    body.hidden = ! toggle.checked;
                });
            }

            document.querySelectorAll('[data-special-feature]').forEach(function (row) {
                var custom = row.querySelector('[data-special-custom]');
                var value = row.querySelector('[data-special-value]');
                var mode = row.querySelector('[data-special-mode]');

                custom.addEventListener('change', function () {
                    value.hidden = custom.value !== '1';
                });

                if (! mode) {
                    return;
                }

                var amount = row.querySelector('[data-special-amount]');
                var period = row.querySelector('[data-special-period]');

                var settle = function () {
                    amount.hidden = mode.value === 'unlimited';
                    period.hidden = mode.value !== 'recurring';
                };

                mode.addEventListener('change', settle);
                settle();
            });
        });
    </script>
@endonce
