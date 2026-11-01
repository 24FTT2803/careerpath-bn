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
    <label class="sa-toggle">
        <input type="hidden" name="special_enabled" value="0">
        <input type="checkbox" name="special_enabled" value="1" data-special-toggle @checked($specialOn)>
        <span class="sa-toggle-track" aria-hidden="true"><span></span></span>
        <span class="sa-toggle-text">
            <strong>{{ $isOwnAccount ? 'Set up my own student side' : 'Special account' }}</strong>
            <small>
                @if($isOwnAccount)
                    A plan and features of its own for when you view as a student. Leave off to follow the plans.
                @else
                    Its own plan and feature values, and other roles it can switch to.
                @endif
            </small>
        </span>
    </label>

    <div class="sa-body" data-special-body @if(! $specialOn) hidden @endif>
        <div class="sa-block">
            <h3 class="sa-title">Plan</h3>
            <div class="sa-control">
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
                                <div class="sa-control">
                                    <select name="{{ $base }}[value]" aria-label="{{ $definition->name }} value">
                                        <option value="1" @selected($onOff === '1')>On</option>
                                        <option value="0" @selected($onOff === '0')>Off</option>
                                    </select>
                                </div>
                            @elseif($definition->value_type === 'number')
                                <div class="sa-control">
                                    <input type="number" min="1" name="{{ $base }}[value]" value="{{ $input['value'] ?? (is_int($saved) ? $saved : '') }}" aria-label="{{ $definition->name }} value">
                                </div>
                            @elseif($definition->value_type === 'quota')
                                <div class="sa-control">
                                    <select name="{{ $base }}[mode]" data-special-mode aria-label="{{ $definition->name }} allowance">
                                        <option value="unlimited" @selected($mode === 'unlimited')>Unlimited</option>
                                        <option value="total" @selected($mode === 'total')>A total of</option>
                                        <option value="recurring" @selected($mode === 'recurring')>An amount every…</option>
                                    </select>
                                </div>
                                <div class="sa-control" data-special-amount>
                                    <input type="number" min="1" placeholder="Amount" name="{{ $base }}[amount]" value="{{ $input['amount'] ?? ($quota['amount'] ?? '') }}" aria-label="Amount">
                                </div>
                                <div class="sa-control sa-period" data-special-period>
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
                    <label class="sa-role" data-special-role="{{ $role }}">
                        <input type="checkbox" name="special[roles][]" value="{{ $role }}" @checked(in_array($role, $specialRoles, true))>
                        <span>{{ $title }}</span>
                    </label>
                @endforeach
            </div>
            <p class="sa-hint" data-special-roles-note>It starts as the role chosen above.</p>
            @error('special.roles')<p class="err">{{ $message }}</p>@enderror
        </div>
    </div>
</section>

@once
    <style>
        /*
         * In the site's current look, as on Edit Profile and the
         * BIICF Explorer: Inter, navy and gold, soft borders and
         * rounded fields.
         */
        .sa {
            --sa-navy: #1a3a5c;
            --sa-gold: #c9a84c;
            --sa-gold-soft: #faf6ea;
            --sa-line: #e6e8ec;
            --sa-muted: #6b7785;
            margin-top: 24px;
            font-family: 'Inter', -apple-system, sans-serif;
            color: #1a1a2e;
        }

        .sa select,
        .sa input,
        .sa button {
            font-family: inherit;
        }

        .sa-toggle {
            position: relative;
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 16px 18px;
            border: 1px solid var(--sa-line);
            border-radius: 16px;
            background: #fff;
            cursor: pointer;
            transition: border-color .2s ease, background .2s ease;
        }

        .sa-toggle:hover { border-color: #cdd3da; }

        .sa-toggle:has(input:checked) {
            border-color: #e8d4a0;
            background: var(--sa-gold-soft);
        }

        .sa-toggle input[type="checkbox"] {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .sa-toggle-track {
            position: relative;
            flex: 0 0 auto;
            width: 42px;
            height: 24px;
            border-radius: 999px;
            background: #d5dbe2;
            transition: background .2s ease;
        }

        .sa-toggle-track span {
            position: absolute;
            top: 3px;
            left: 3px;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background: #fff;
            box-shadow: 0 1px 3px rgba(13, 31, 51, .25);
            transition: transform .2s ease;
        }

        .sa-toggle input:checked + .sa-toggle-track { background: var(--sa-navy); }

        .sa-toggle input:checked + .sa-toggle-track span { transform: translateX(18px); }

        .sa-toggle input:focus-visible + .sa-toggle-track { box-shadow: 0 0 0 4px rgba(201, 168, 76, .25); }

        .sa-toggle-text strong {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: var(--sa-navy);
        }

        .sa-toggle-text small,
        .sa-hint {
            display: block;
            margin-top: 3px;
            font-size: 12.5px;
            line-height: 1.5;
            color: var(--sa-muted);
        }

        .sa-body {
            margin-top: 12px;
            border: 1px solid var(--sa-line);
            border-radius: 18px;
            background: #fff;
            box-shadow: 0 10px 30px -26px rgba(13, 31, 51, .45);
            overflow: hidden;
        }

        .sa-body[hidden] { display: none; }

        .sa-block {
            padding: 20px 22px;
            border-bottom: 1px solid #eef0f3;
        }

        .sa-block:last-child { border-bottom: 0; }

        .sa-title {
            margin: 0 0 12px;
            padding-left: 10px;
            border-left: 3px solid var(--sa-gold);
            font-family: 'Playfair Display', serif;
            font-size: 16px;
            font-weight: 700;
            line-height: 1.25;
            color: var(--sa-navy);
        }

        .sa-control select,
        .sa-control input {
            box-sizing: border-box;
            width: 100%;
            min-height: 44px;
            padding: 10px 14px;
            border: 1px solid #dfe3e8;
            border-radius: 12px;
            background: #fcfcfd;
            font-size: 14px;
            color: #1a1a2e;
            transition: border-color .2s ease, box-shadow .2s ease, background .2s ease;
        }

        .sa-control select:hover,
        .sa-control input:hover { border-color: #cdd3da; }

        .sa-control select:focus,
        .sa-control input:focus {
            outline: none;
            background: #fff;
            border-color: var(--sa-gold);
            box-shadow: 0 0 0 4px rgba(201, 168, 76, .16);
        }

        .sa-features summary {
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
            list-style: none;
        }

        .sa-features summary::-webkit-details-marker { display: none; }

        .sa-features summary .sa-title { margin: 0; }

        .sa-count {
            padding: 3px 10px;
            border-radius: 999px;
            background: #eef3f9;
            color: var(--sa-navy);
            font-size: 12px;
            font-weight: 600;
        }

        .sa-chevron {
            width: 18px;
            height: 18px;
            margin-left: auto;
            color: var(--sa-muted);
            transition: transform .2s ease;
        }

        .sa-features[open] .sa-chevron { transform: rotate(180deg); }

        .sa-category {
            margin: 20px 0 8px;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: .05em;
            text-transform: uppercase;
            color: var(--sa-muted);
        }

        .sa-feature {
            padding: 12px 14px;
            margin-bottom: 8px;
            border: 1px solid var(--sa-line);
            border-radius: 14px;
            background: #fbfbfd;
            transition: border-color .2s ease, background .2s ease, box-shadow .2s ease;
        }

        .sa-feature:hover {
            border-color: #e8d4a0;
            box-shadow: 0 12px 26px -22px rgba(13, 31, 51, .5);
        }

        .sa-feature.is-custom {
            border-color: #e8d4a0;
            background: var(--sa-gold-soft);
        }

        .sa-feature-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }

        .sa-name {
            font-size: 14px;
            font-weight: 500;
            color: var(--sa-navy);
        }

        /* Follow plan / Custom, as a two-part switch. */
        .sa-switch {
            display: inline-flex;
            padding: 3px;
            border-radius: 999px;
            background: #eef1f5;
        }

        .sa-switch label {
            position: relative;
            margin: 0;
            cursor: pointer;
        }

        .sa-switch input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .sa-switch span {
            display: block;
            padding: 6px 14px;
            border-radius: 999px;
            font-size: 12.5px;
            font-weight: 600;
            color: var(--sa-muted);
            transition: background .2s ease, color .2s ease, box-shadow .2s ease;
        }

        .sa-switch input:checked + span {
            background: var(--sa-navy);
            color: #fff;
            box-shadow: 0 2px 6px -2px rgba(13, 31, 51, .5);
        }

        .sa-switch input:focus-visible + span { box-shadow: 0 0 0 3px rgba(201, 168, 76, .35); }

        .sa-value {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            align-items: start;
            gap: 10px;
            margin-top: 12px;
        }

        .sa-value[hidden],
        .sa-value [hidden] { display: none !important; }

        .sa-period {
            display: grid;
            grid-template-columns: 72px minmax(0, 1fr);
            gap: 8px;
        }

        .sa-roles {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .sa-role {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            border: 1px solid var(--sa-line);
            border-radius: 999px;
            background: #fff;
            font-size: 13.5px;
            font-weight: 500;
            color: var(--sa-navy);
            cursor: pointer;
            transition: border-color .2s ease, background .2s ease, color .2s ease;
        }

        .sa-role[hidden] { display: none; }

        .sa-role:hover { border-color: #cdd3da; }

        .sa-role:has(input:checked) {
            border-color: var(--sa-navy);
            background: var(--sa-navy);
            color: #fff;
        }

        .sa-role input {
            margin: 0;
            accent-color: var(--sa-gold);
        }

        .sa .err {
            margin-top: 6px;
            font-size: 12px;
            color: #c0392b;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            /* Open only when switched on. */
            var toggle = document.querySelector('[data-special-toggle]');
            var body = document.querySelector('[data-special-body]');

            if (toggle && body) {
                toggle.addEventListener('change', function () {
                    body.hidden = ! toggle.checked;
                });
            }

            /*
             * Only the roles it does not have already: never its own,
             * and never Student for an admin, who can always view as one.
             */
            var roleSelect = document.getElementById('role-select');
            var roleNote = document.querySelector('[data-special-roles-note]');

            var settleRoles = function () {
                var own = roleSelect ? roleSelect.value : '';

                document.querySelectorAll('[data-special-role]').forEach(function (pill) {
                    var role = pill.getAttribute('data-special-role');
                    var hide = role === own || (own === 'admin' && role === 'student');

                    pill.hidden = hide;

                    if (hide) {
                        pill.querySelector('input').checked = false;
                    }
                });

                if (roleNote) {
                    roleNote.textContent = own === 'admin'
                        ? 'It starts as an admin, and can always view as a student.'
                        : 'It starts as the role chosen above.';
                }
            };

            if (roleSelect) {
                roleSelect.addEventListener('change', settleRoles);
            }

            settleRoles();

            /* Each feature's value only when set here. */
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
