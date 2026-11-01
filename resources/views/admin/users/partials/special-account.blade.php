{{--
    A special account: for showing the platform, or for staff
    trying it, including an admin's own student side. Closed
    until ticked. Inside, the account can be put on a plan of its
    own, and any feature set for it alone, the same way a plan's
    features are; anything left alone follows the plan.

    Features are named by id in the form: their keys hold dots,
    which a form field name would read as levels.
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
    $setCount = count($savedFeatures);
@endphp

<section class="sa" id="special-account">
    <div class="cpbn-field" style="margin-bottom:0">
        <label class="cpbn-waive">
            <input type="hidden" name="special_enabled" value="0">
            <input type="checkbox" name="special_enabled" value="1" data-special-toggle @checked($specialOn)>
            <span>
                <span class="cpbn-waive-title">
                    {{ $isOwnAccount ? 'Set up my own student side' : 'Special account' }}
                </span>
                <span class="sa-hint">
                    @if($isOwnAccount)
                        A plan and features of its own for when you View as Student. Leave unticked to follow the plans.
                    @else
                        Its own plan and feature values, and other roles it can switch to.
                    @endif
                </span>
            </span>
        </label>
    </div>

    <div class="sa-body" data-special-body @if(! $specialOn) hidden @endif>
        <div class="sa-block">
            <h3 class="sa-title">Plan</h3>
            <div class="cpbn-field" style="margin-bottom:0">
                <select name="special[plan]" id="special-plan" aria-label="Plan">
                    <option value="">Follow the plans, as for anyone</option>
                    @foreach($featureValues->plans() as $plan)
                        <option value="{{ $plan->code }}" @selected($specialPlan === $plan->code)>{{ $plan->name }}</option>
                    @endforeach
                </select>
                @error('special.plan')<p class="err">{{ $message }}</p>@enderror
            </div>
        </div>

        <details class="sa-block sa-features" @if($setCount > 0 || $errors->has('special.features.*')) open @endif>
            <summary>
                <h3 class="sa-title">Features</h3>
                <span class="sa-count">{{ $setCount > 0 ? $setCount.' set for this account' : 'All follow the plan' }}</span>
                <svg class="sa-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg>
            </summary>

            @foreach($featureValues->featuresByCategory() as $category => $definitions)
                <p class="sa-category">{{ $category }}</p>

                @foreach($definitions as $definition)
                    @php
                        $id = $definition->id;
                        $base = 'special[features]['.$id.']';
                        $input = $oldFeatures[$id] ?? null;
                        $saved = $savedFeatures[$definition->key] ?? null;
                        $custom = $input !== null
                            ? (($input['custom'] ?? '0') === '1')
                            : array_key_exists($definition->key, $savedFeatures);
                        $quota = is_array($saved) ? $saved : [];
                        $mode = $input['mode'] ?? ($quota['mode'] ?? 'unlimited');
                        $onOff = isset($input['value']) ? (string) $input['value'] : ($saved === false ? '0' : '1');
                    @endphp

                    <div class="sa-feature {{ $custom ? 'is-custom' : '' }}" data-special-feature>
                        <div class="sa-feature-head">
                            <span class="sa-name">{{ $definition->name }}</span>

                            <div class="sa-switch" role="radiogroup" aria-label="{{ $definition->name }}">
                                <label>
                                    <input type="radio" name="{{ $base }}[custom]" value="0" data-special-custom @checked(! $custom)>
                                    <span>Follow plan</span>
                                </label>
                                <label>
                                    <input type="radio" name="{{ $base }}[custom]" value="1" data-special-custom @checked($custom)>
                                    <span>Custom</span>
                                </label>
                            </div>
                        </div>

                        <div class="sa-value" data-special-value @if(! $custom) hidden @endif>
                            @if($definition->value_type === 'boolean')
                                <div class="cpbn-field">
                                    <select name="{{ $base }}[value]" aria-label="{{ $definition->name }} value">
                                        <option value="1" @selected($onOff === '1')>On</option>
                                        <option value="0" @selected($onOff === '0')>Off</option>
                                    </select>
                                </div>
                            @elseif($definition->value_type === 'number')
                                <div class="cpbn-field">
                                    <input type="number" min="1" name="{{ $base }}[value]" value="{{ $input['value'] ?? (is_int($saved) ? $saved : '') }}" aria-label="{{ $definition->name }} value">
                                </div>
                            @elseif($definition->value_type === 'quota')
                                <div class="cpbn-field">
                                    <select name="{{ $base }}[mode]" data-special-mode aria-label="{{ $definition->name }} allowance">
                                        <option value="unlimited" @selected($mode === 'unlimited')>Unlimited</option>
                                        <option value="total" @selected($mode === 'total')>A total of</option>
                                        <option value="recurring" @selected($mode === 'recurring')>An amount every…</option>
                                    </select>
                                </div>
                                <div class="cpbn-field" data-special-amount>
                                    <input type="number" min="1" placeholder="Amount" name="{{ $base }}[amount]" value="{{ $input['amount'] ?? ($quota['amount'] ?? '') }}" aria-label="Amount">
                                </div>
                                <div class="cpbn-field sa-period" data-special-period>
                                    <input type="number" min="1" name="{{ $base }}[period_value]" value="{{ $input['period_value'] ?? ($quota['period_value'] ?? 1) }}" aria-label="Every how many">
                                    <select name="{{ $base }}[period_unit]" aria-label="Period">
                                        @foreach(\App\Services\Business\AccountFeatureValues::PERIODS as $unit)
                                            <option value="{{ $unit }}" @selected(($input['period_unit'] ?? ($quota['period_unit'] ?? 'month')) === $unit)>{{ $unit }}s</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                        </div>

                        @foreach(['value', 'mode', 'amount', 'period_value', 'period_unit'] as $part)
                            @error('special.features.'.$id.'.'.$part)<p class="err">{{ $message }}</p>@enderror
                        @endforeach
                    </div>
                @endforeach
            @endforeach
        </details>

        <div class="sa-block">
            <h3 class="sa-title">Can also switch to</h3>
            <div class="sa-roles">
                @foreach(['student' => 'Student', 'lecturer' => 'Lecturer', 'admin' => 'Admin'] as $role => $title)
                    <label class="sa-role">
                        <input type="checkbox" name="special[roles][]" value="{{ $role }}" @checked(in_array($role, $specialRoles, true))>
                        <span>{{ $title }}</span>
                    </label>
                @endforeach
            </div>
            <p class="sa-hint">The role chosen above is the one it starts as. Every admin can already view as a student.</p>
            @error('special.roles')<p class="err">{{ $message }}</p>@enderror
        </div>
    </div>
</section>

@once
    <style>
        .sa { margin-top: 22px; }

        .sa-hint {
            display: block;
            margin-top: 3px;
            font-size: 12px;
            font-weight: 400;
            color: var(--ink-dim, #5b6675);
        }

        .sa-body {
            margin-top: 12px;
            border: 1px solid var(--line, #e7e2d4);
            border-radius: 6px;
            background: #fcfbf7;
        }

        .sa-body[hidden] { display: none; }

        .sa-block {
            padding: 16px 18px;
            border-bottom: 1px solid var(--line, #e7e2d4);
        }

        .sa-block:last-child { border-bottom: 0; }

        .sa-title {
            margin: 0 0 10px;
            font-family: var(--font-mono, 'IBM Plex Mono', monospace);
            font-size: 11px;
            font-weight: 600;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: var(--ink-dim, #5b6675);
        }

        .sa-features summary {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            list-style: none;
        }

        .sa-features summary::-webkit-details-marker { display: none; }

        .sa-features summary .sa-title { margin: 0; }

        .sa-count {
            font-size: 12px;
            color: var(--gold, #cf9a3d);
            font-weight: 500;
        }

        .sa-chevron {
            width: 16px;
            height: 16px;
            margin-left: auto;
            color: var(--ink-dim, #5b6675);
            transition: transform .15s ease;
        }

        .sa-features[open] .sa-chevron { transform: rotate(180deg); }

        .sa-category {
            margin: 16px 0 6px;
            font-size: 13px;
            font-weight: 600;
            color: var(--ink, #0d1a2b);
        }

        .sa-feature {
            padding: 10px 12px;
            margin-bottom: 6px;
            border: 1px solid var(--line, #e7e2d4);
            border-radius: 6px;
            background: #fff;
            transition: border-color .15s ease;
        }

        .sa-feature.is-custom { border-color: var(--gold, #cf9a3d); }

        .sa-feature-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            flex-wrap: wrap;
        }

        .sa-name { font-size: 13.5px; }

        /* Follow plan / Custom, as a two-part switch. */
        .sa-switch {
            display: inline-flex;
            padding: 2px;
            border: 1px solid var(--line, #e7e2d4);
            border-radius: 999px;
            background: #f6f3ea;
        }

        .sa-switch label { margin: 0; cursor: pointer; }

        .sa-switch input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .sa-switch span {
            display: block;
            padding: 4px 12px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 500;
            color: var(--ink-dim, #5b6675);
            transition: background .15s ease, color .15s ease;
        }

        .sa-switch input:checked + span {
            background: var(--gold, #cf9a3d);
            color: var(--ink, #0d1a2b);
        }

        .sa-switch input:focus-visible + span {
            box-shadow: 0 0 0 3px rgba(207, 154, 61, .3);
        }

        .sa-value {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 8px;
            margin-top: 10px;
        }

        .sa-value[hidden],
        .sa-value [hidden] { display: none !important; }

        .sa-value .cpbn-field { margin-bottom: 0; }

        .sa-period {
            display: grid;
            grid-template-columns: 80px 1fr;
            gap: 8px;
        }

        .sa-roles {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .sa-role {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 14px;
            border: 1px solid var(--line, #e7e2d4);
            border-radius: 999px;
            background: #fff;
            font-size: 13px;
            cursor: pointer;
        }

        .sa-role:has(input:checked) {
            border-color: var(--gold, #cf9a3d);
            background: rgba(207, 154, 61, .08);
        }

        .sa-role input { accent-color: var(--gold, #cf9a3d); margin: 0; }
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
                var value = row.querySelector('[data-special-value]');
                var mode = row.querySelector('[data-special-mode]');

                row.querySelectorAll('[data-special-custom]').forEach(function (radio) {
                    radio.addEventListener('change', function () {
                        var custom = radio.value === '1' && radio.checked;
                        value.hidden = ! custom;
                        row.classList.toggle('is-custom', custom);
                    });
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
