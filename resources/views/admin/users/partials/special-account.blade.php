{{--
    A special account: for showing the platform, or for staff
    trying it. Each rule is chosen on its own. Roles let it switch
    between being a student, a lecturer and an admin, each with
    its own data; the role chosen above is the one it starts as.
--}}
@php
    $specialRules = old('special', $user?->special_rules ?? []);
    $specialRoles = (array) old('special.roles', $specialRules['roles'] ?? []);
@endphp

<fieldset class="cpbn-special">
    <legend>Special account</legend>
    <p class="cpbn-hint" style="margin:0 0 12px;">
        Optional. For a demonstration account, or one with rules of its own.
        Every admin can already switch to a student view of their own.
    </p>

    <div class="cpbn-special-grid">
        @foreach([
            'unlimited_ai' => ['Unlimited AI use', 'No limits on the CPBN Bot, Career Adviser or recommendations.'],
            'premium' => ['Premium features', 'Everything in Premium, without paying.'],
            'no_ads' => ['No advertisements', 'Never shown ads, whatever the plan.'],
        ] as $rule => [$title, $hint])
            <div class="cpbn-field" style="margin-bottom:0">
                <label class="cpbn-waive">
                    <input type="hidden" name="special[{{ $rule }}]" value="0">
                    <input type="checkbox" name="special[{{ $rule }}]" value="1" @checked(! empty($specialRules[$rule]))>
                    <span>
                        <span class="cpbn-waive-title">{{ $title }}</span>
                        <span class="cpbn-hint" style="display:block;margin:2px 0 0;">{{ $hint }}</span>
                    </span>
                </label>
            </div>
        @endforeach
    </div>

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

    @error('special')<p class="err">{{ $message }}</p>@enderror
    @error('special.roles')<p class="err">{{ $message }}</p>@enderror
</fieldset>

@once
    <style>
        .cpbn-special {
            margin: 20px 0 0;
            padding: 16px 18px 18px;
            border: 1px dashed var(--line, #e7e2d4);
            border-radius: 6px;
        }

        .cpbn-special legend {
            padding: 0 6px;
            font-size: 13px;
            font-weight: 600;
        }

        .cpbn-special-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px;
        }

        .cpbn-special-sub {
            margin: 16px 0 8px;
            font-size: 12.5px;
            font-weight: 600;
            color: var(--ink-dim, #5b6675);
        }
    </style>
@endonce
