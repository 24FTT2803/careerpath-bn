@extends('admin.layouts.admin')

@section('title', 'Plans & Features')

@section('content')
<style>
    .business-plans-page {
        max-width: 1500px;
        margin: 0 auto;
    }

    .business-page-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 24px;
        margin-bottom: 24px;
    }

    .business-page-header h1 {
        font-size: 28px;
        color: #1a3a5c;
        margin: 0 0 6px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .business-page-header p {
        color: #6b7280;
        font-size: 14px;
        margin: 0;
        max-width: 760px;
        line-height: 1.6;
    }

    .business-summary {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    .business-summary-item {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        padding: 10px 16px;
        min-width: 105px;
        text-align: center;
    }

    .business-summary-number {
        display: block;
        color: #1a3a5c;
        font-size: 20px;
        font-weight: 700;
    }

    .business-summary-label {
        display: block;
        margin-top: 2px;
        color: #6b7280;
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: .06em;
    }

    .business-info {
        background: #fbf1de;
        border: 1px solid #e8d4a0;
        border-radius: 10px;
        padding: 14px 16px;
        margin-bottom: 26px;
        color: #6f571d;
        font-size: 13px;
        line-height: 1.6;
        display: flex;
        gap: 12px;
        align-items: flex-start;
    }

    .business-info i {
        color: #c9a84c;
        margin-top: 3px;
    }

    .business-section {
        margin-bottom: 32px;
    }

    .business-section-heading {
        margin-bottom: 14px;
    }

    .business-section-heading h2 {
        color: #1a3a5c;
        font-size: 20px;
        margin: 0 0 5px;
    }

    .business-section-heading p {
        color: #6b7280;
        margin: 0;
        font-size: 13px;
        line-height: 1.5;
    }

    .global-category {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        overflow: hidden;
        margin-bottom: 14px;
        box-shadow: 0 3px 16px rgba(
            26,
            58,
            92,
            .05
        );
    }

    .global-category-header {
        padding: 13px 18px;
        background: #f7f8fa;
        border-bottom: 1px solid #e5e7eb;
        font-size: 13px;
        font-weight: 700;
        color: #1a3a5c;
    }

    .global-feature-row {
        display: grid;
        grid-template-columns:
            minmax(260px, 1fr)
            110px
            150px;
        gap: 16px;
        align-items: center;
        padding: 13px 18px;
        border-bottom: 1px solid #f0f1f3;
    }

    .global-feature-row:last-child {
        border-bottom: none;
    }

    .feature-name {
        font-size: 13px;
        font-weight: 600;
        color: #1a1a2e;
    }

    .feature-key {
        display: block;
        margin-top: 3px;
        color: #9ca3af;
        font-family:
            'IBM Plex Mono',
            monospace;
        font-size: 9px;
        word-break: break-all;
    }

    .feature-child {
        padding-left: 22px;
        position: relative;
    }

    .feature-child::before {
        content: '';
        position: absolute;
        left: 5px;
        top: 5px;
        bottom: 5px;
        width: 2px;
        background: #e8d4a0;
        border-radius: 10px;
    }

    .value-type {
        justify-self: start;
        display: inline-flex;
        align-items: center;
        border-radius: 100px;
        padding: 4px 9px;
        font-size: 10px;
        font-weight: 600;
        background: #eef2f7;
        color: #526171;
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    .global-toggle-form {
        justify-self: end;
    }

    .status-button {
        border: none;
        border-radius: 100px;
        padding: 7px 13px;
        cursor: pointer;
        font-size: 11px;
        font-weight: 700;
        min-width: 100px;
        transition: all .2s ease;
    }

    .status-button.enabled {
        background: #e0f4e9;
        color: #23734a;
    }

    .status-button.disabled {
        background: #f5e4e1;
        color: #a53e32;
    }

    .status-button:hover {
        transform: translateY(-1px);
        filter: brightness(.97);
    }

    .plans-grid {
        display: grid;
        grid-template-columns:
            repeat(
                auto-fit,
                minmax(420px, 1fr)
            );
        gap: 20px;
        align-items: start;
    }

    .plan-card {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(
            26,
            58,
            92,
            .06
        );
    }

    .plan-card-header {
        padding: 18px 20px;
        background:
            linear-gradient(
                135deg,
                #0d1f33,
                #1a3a5c
            );
        color: white;
        display: flex;
        justify-content: space-between;
        gap: 14px;
        align-items: center;
    }

    .plan-card-header h3 {
        margin: 0;
        font-size: 19px;
    }

    .plan-code {
        display: block;
        color: rgba(
            255,
            255,
            255,
            .55
        );
        font-family:
            'IBM Plex Mono',
            monospace;
        font-size: 10px;
        margin-top: 3px;
    }

    .plan-badges {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 5px;
        flex-wrap: wrap;
    }

    .plan-badge {
        padding: 4px 9px;
        border-radius: 100px;
        font-size: 9px;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .plan-badge.active {
        background: #dff3e7;
        color: #256b46;
    }

    .plan-badge.inactive {
        background: #f3dddd;
        color: #973f37;
    }

    .plan-badge.default {
        background: #e8d4a0;
        color: #594515;
    }

    .plan-category {
        border-bottom: 1px solid #e5e7eb;
    }

    .plan-category:last-of-type {
        border-bottom: none;
    }

    .plan-category-title {
        background: #f7f8fa;
        padding: 11px 18px;
        color: #1a3a5c;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .06em;
    }

    .plan-feature {
        padding: 13px 18px;
        border-top: 1px solid #f0f1f3;
    }

    .plan-feature:first-of-type {
        border-top: none;
    }

    .plan-feature.child {
        margin-left: 18px;
        border-left: 2px solid #e8d4a0;
    }

    .plan-feature-heading {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 9px;
    }

    .plan-feature-name {
        font-size: 12px;
        font-weight: 600;
        color: #303744;
    }

    .global-off-label {
        background: #f5e4e1;
        color: #a53e32;
        border-radius: 100px;
        padding: 3px 7px;
        font-size: 8px;
        font-weight: 700;
        white-space: nowrap;
    }

    .boolean-control {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
    }

    .boolean-control input {
        width: 17px;
        height: 17px;
        accent-color: #c9a84c;
    }

    .boolean-control span {
        font-size: 12px;
        color: #525b68;
    }

    .feature-input {
        width: 100%;
        border: 1px solid #d8dde5;
        border-radius: 7px;
        padding: 8px 10px;
        background: white;
        color: #1a1a2e;
        font-family: inherit;
        font-size: 12px;
        transition: border .2s ease;
    }

    .feature-input:focus {
        outline: none;
        border-color: #c9a84c;
        box-shadow: 0 0 0 3px rgba(
            201,
            168,
            76,
            .12
        );
    }

    .quota-row {
        display: grid;
        grid-template-columns:
            minmax(125px, 1.2fr)
            minmax(90px, .8fr)
            minmax(90px, .8fr)
            minmax(105px, 1fr);
        gap: 7px;
        align-items: end;
    }

    .quota-field label {
        display: block;
        color: #8b939f;
        font-size: 9px;
        margin-bottom: 4px;
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    .quota-hidden {
        display: none;
    }

    .plan-save {
        padding: 16px 18px;
        background: #fafafa;
        border-top: 1px solid #e5e7eb;
        display: flex;
        justify-content: flex-end;
    }

    .save-plan-button {
        border: none;
        border-radius: 7px;
        padding: 9px 16px;
        background: #1a3a5c;
        color: white;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: all .2s ease;
    }

    .save-plan-button:hover {
        background: #0d1f33;
        transform: translateY(-1px);
    }

    .validation-errors {
        margin-bottom: 20px;
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
        border-radius: 9px;
        padding: 13px 16px;
        font-size: 12px;
    }

    .validation-errors ul {
        margin: 7px 0 0 18px;
    }

    @media (max-width: 900px) {
        .business-page-header {
            flex-direction: column;
        }

        .business-summary {
            justify-content: flex-start;
        }

        .plans-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 700px) {
        .global-feature-row {
            grid-template-columns: 1fr;
            gap: 8px;
        }

        .global-toggle-form {
            justify-self: start;
        }

        .quota-row {
            grid-template-columns:
                1fr 1fr;
        }
    }

    @media (max-width: 480px) {
        .quota-row {
            grid-template-columns: 1fr;
        }

        .business-summary-item {
            min-width: 90px;
        }
    }
</style>

<div class="business-plans-page">

    <div class="business-page-header">
        <div>
            <h1>
                <i
                    class="fas fa-sliders-h"
                    style="color:#c9a84c;"
                ></i>
                Plans & Features
            </h1>

            <p>
                Configure system-wide feature availability
                and the values provided by each access plan.
            </p>
        </div>

        <div class="business-summary">
            <div class="business-summary-item">
                <span class="business-summary-number">
                    {{ $plans->count() }}
                </span>
                <span class="business-summary-label">
                    Plans
                </span>
            </div>

            <div class="business-summary-item">
                <span class="business-summary-number">
                    {{ $features->count() }}
                </span>
                <span class="business-summary-label">
                    Features
                </span>
            </div>

            <div class="business-summary-item">
                <span class="business-summary-number">
                    {{
                        $features
                            ->where(
                                'global_enabled',
                                true
                            )
                            ->count()
                    }}
                </span>
                <span class="business-summary-label">
                    Globally On
                </span>
            </div>
        </div>
    </div>

    @if($errors->any())
        <div class="validation-errors">
            <strong>
                Please correct the following:
            </strong>

            <ul>
                @foreach(
                    $errors->all()
                    as $error
                )
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="business-info">
        <i class="fas fa-info-circle"></i>

        <div>
            A global switch overrides every plan and
            sponsored-access setting. Turning a feature
            off globally is intended for maintenance or
            operational control. Plan values are retained
            so they become effective again when the
            feature is re-enabled.

            Configuration for a future capability does
            not itself implement that capability.
        </div>
    </div>

    {{-- =========================================
         GLOBAL FEATURE CONTROLS
         ========================================= --}}
    <section class="business-section">
        <div class="business-section-heading">
            <h2>
                Global Feature Controls
            </h2>

            <p>
                System-level maintenance switches.
                OFF means the feature is unavailable
                regardless of Free, Premium or sponsored
                access.
            </p>
        </div>

        @foreach(
            $featuresByCategory
            as $category => $categoryFeatures
        )
            <div class="global-category">
                <div class="global-category-header">
                    {{ $category }}
                </div>

                @foreach(
                    $categoryFeatures
                    as $feature
                )
                    <div class="global-feature-row">
                        <div
                            class="{{
                                $feature->parent_key
                                    ? 'feature-child'
                                    : ''
                            }}"
                        >
                            <span class="feature-name">
                                {{ $feature->name }}
                            </span>

                            <span class="feature-key">
                                {{ $feature->key }}
                            </span>
                        </div>

                        <span class="value-type">
                            {{ $feature->value_type }}
                        </span>

                        <form
                            method="POST"
                            action="{{
                                route(
                                    'admin.business.features.global',
                                    $feature
                                )
                            }}"
                            class="global-toggle-form"
                            data-confirm-update
                            data-item-name="{{
                                $feature->name .
                                ' global availability'
                            }}"
                        >
                            @csrf
                            @method('PUT')

                            <input
                                type="hidden"
                                name="global_enabled"
                                value="{{
                                    $feature->global_enabled
                                        ? 0
                                        : 1
                                }}"
                            >

                            <button
                                type="submit"
                                class="status-button {{
                                    $feature->global_enabled
                                        ? 'enabled'
                                        : 'disabled'
                                }}"
                            >
                                <i
                                    class="fas {{
                                        $feature->global_enabled
                                            ? 'fa-toggle-on'
                                            : 'fa-toggle-off'
                                    }}"
                                ></i>

                                {{
                                    $feature->global_enabled
                                        ? 'Enabled'
                                        : 'Disabled'
                                }}
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        @endforeach
    </section>

    {{-- =========================================
         PLAN VALUES
         ========================================= --}}
    <section class="business-section">
        <div class="business-section-heading">
            <h2>
                Plan Feature Values
            </h2>

            <p>
                Configure what each plan receives.
                Sponsored access can inherit these values
                or override them separately in the
                sponsorship management section later.
            </p>
        </div>

        <div class="plans-grid">
            @foreach($plans as $plan)
                @php
                    $useOld =
                        (string) old(
                            'editing_plan'
                        )
                        ===
                        (string) $plan->id;
                @endphp

                <div class="plan-card">
                    <div class="plan-card-header">
                        <div>
                            <h3>
                                {{ $plan->name }}
                            </h3>

                            <span class="plan-code">
                                {{ $plan->code }}
                            </span>
                        </div>

                        <div class="plan-badges">
                            <span
                                class="plan-badge {{
                                    $plan->is_active
                                        ? 'active'
                                        : 'inactive'
                                }}"
                            >
                                {{
                                    $plan->is_active
                                        ? 'Active'
                                        : 'Inactive'
                                }}
                            </span>

                            @if($plan->is_default)
                                <span
                                    class="plan-badge default"
                                >
                                    Default
                                </span>
                            @endif
                        </div>
                    </div>

                    <form
                        method="POST"
                        action="{{
                            route(
                                'admin.business.plans.update',
                                $plan
                            )
                        }}"
                        data-confirm-update
                        data-item-name="{{
                            $plan->name .
                            ' plan features'
                        }}"
                    >
                        @csrf
                        @method('PUT')

                        <input
                            type="hidden"
                            name="editing_plan"
                            value="{{ $plan->id }}"
                        >

                        @foreach(
                            $featuresByCategory
                            as $category => $categoryFeatures
                        )
                            <div class="plan-category">
                                <div class="plan-category-title">
                                    {{ $category }}
                                </div>

                                @foreach(
                                    $categoryFeatures
                                    as $feature
                                )
                                    @php
                                        $storedFeature =
                                            $plan
                                                ->features
                                                ->firstWhere(
                                                    'key',
                                                    $feature->key
                                                );

                                        $storedValue =
                                            $storedFeature
                                                ? $storedFeature->value
                                                : null;

                                        $oldBase =
                                            'features.' .
                                            $feature->id;

                                        if (
                                            $feature->value_type
                                            === 'boolean'
                                        ) {
                                            $booleanValue =
                                                $useOld
                                                    ? old(
                                                        $oldBase .
                                                        '.value',
                                                        $storedValue
                                                    )
                                                    : $storedValue;
                                        }

                                        if (
                                            $feature->value_type
                                            === 'number'
                                        ) {
                                            $numberValue =
                                                $useOld
                                                    ? old(
                                                        $oldBase .
                                                        '.value',
                                                        $storedValue
                                                    )
                                                    : $storedValue;
                                        }

                                        if (
                                            $feature->value_type
                                            === 'quota'
                                        ) {
                                            $quotaValue =
                                                is_array(
                                                    $storedValue
                                                )
                                                    ? $storedValue
                                                    : [
                                                        'mode'
                                                            => 'unlimited',
                                                    ];

                                            $quotaMode =
                                                $useOld
                                                    ? old(
                                                        $oldBase .
                                                        '.mode',
                                                        $quotaValue[
                                                            'mode'
                                                        ]
                                                            ?? 'unlimited'
                                                    )
                                                    : (
                                                        $quotaValue[
                                                            'mode'
                                                        ]
                                                            ?? 'unlimited'
                                                    );

                                            $quotaAmount =
                                                $useOld
                                                    ? old(
                                                        $oldBase .
                                                        '.amount',
                                                        $quotaValue[
                                                            'amount'
                                                        ]
                                                            ?? 1
                                                    )
                                                    : (
                                                        $quotaValue[
                                                            'amount'
                                                        ]
                                                            ?? 1
                                                    );

                                            $quotaPeriodValue =
                                                $useOld
                                                    ? old(
                                                        $oldBase .
                                                        '.period_value',
                                                        $quotaValue[
                                                            'period_value'
                                                        ]
                                                            ?? 1
                                                    )
                                                    : (
                                                        $quotaValue[
                                                            'period_value'
                                                        ]
                                                            ?? 1
                                                    );

                                            $quotaPeriodUnit =
                                                $useOld
                                                    ? old(
                                                        $oldBase .
                                                        '.period_unit',
                                                        $quotaValue[
                                                            'period_unit'
                                                        ]
                                                            ?? 'month'
                                                    )
                                                    : (
                                                        $quotaValue[
                                                            'period_unit'
                                                        ]
                                                            ?? 'month'
                                                    );
                                        }
                                    @endphp

                                    <div
                                        class="plan-feature {{
                                            $feature->parent_key
                                                ? 'child'
                                                : ''
                                        }}"
                                    >
                                        <div
                                            class="plan-feature-heading"
                                        >
                                            <span
                                                class="plan-feature-name"
                                            >
                                                {{ $feature->name }}
                                            </span>

                                            @if(
                                                ! $feature
                                                    ->global_enabled
                                            )
                                                <span
                                                    class="global-off-label"
                                                >
                                                    Global Off
                                                </span>
                                            @endif
                                        </div>

                                        @if(
                                            $feature->value_type
                                            === 'boolean'
                                        )
                                            <input
                                                type="hidden"
                                                name="features[{{
                                                    $feature->id
                                                }}][value]"
                                                value="0"
                                            >

                                            <label
                                                class="boolean-control"
                                            >
                                                <input
                                                    type="checkbox"
                                                    name="features[{{
                                                        $feature->id
                                                    }}][value]"
                                                    value="1"
                                                    {{
                                                        filter_var(
                                                            $booleanValue,
                                                            FILTER_VALIDATE_BOOLEAN
                                                        )
                                                            ? 'checked'
                                                            : ''
                                                    }}
                                                >

                                                <span>
                                                    Enabled for
                                                    {{ $plan->name }}
                                                </span>
                                            </label>

                                        @elseif(
                                            $feature->value_type
                                            === 'number'
                                        )
                                            <input
                                                type="number"
                                                class="feature-input"
                                                name="features[{{
                                                    $feature->id
                                                }}][value]"
                                                value="{{
                                                    $numberValue
                                                }}"
                                                min="1"
                                                step="1"
                                                required
                                            >

                                        @elseif(
                                            $feature->value_type
                                            === 'quota'
                                        )
                                            <div
                                                class="quota-row"
                                                data-quota-group
                                            >
                                                <div
                                                    class="quota-field"
                                                >
                                                    <label>
                                                        Mode
                                                    </label>

                                                    <select
                                                        class="feature-input"
                                                        name="features[{{
                                                            $feature->id
                                                        }}][mode]"
                                                        data-quota-mode
                                                        required
                                                    >
                                                        <option
                                                            value="unlimited"
                                                            {{
                                                                $quotaMode
                                                                === 'unlimited'
                                                                    ? 'selected'
                                                                    : ''
                                                            }}
                                                        >
                                                            Unlimited
                                                        </option>

                                                        <option
                                                            value="total"
                                                            {{
                                                                $quotaMode
                                                                === 'total'
                                                                    ? 'selected'
                                                                    : ''
                                                            }}
                                                        >
                                                            Total / Lifetime
                                                        </option>

                                                        <option
                                                            value="recurring"
                                                            {{
                                                                $quotaMode
                                                                === 'recurring'
                                                                    ? 'selected'
                                                                    : ''
                                                            }}
                                                        >
                                                            Recurring
                                                        </option>
                                                    </select>
                                                </div>

                                                <div
                                                    class="quota-field"
                                                    data-quota-amount
                                                >
                                                    <label>
                                                        Amount
                                                    </label>

                                                    <input
                                                        type="number"
                                                        class="feature-input"
                                                        name="features[{{
                                                            $feature->id
                                                        }}][amount]"
                                                        value="{{
                                                            $quotaAmount
                                                        }}"
                                                        min="1"
                                                        step="1"
                                                    >
                                                </div>

                                                <div
                                                    class="quota-field"
                                                    data-quota-period
                                                >
                                                    <label>
                                                        Every
                                                    </label>

                                                    <input
                                                        type="number"
                                                        class="feature-input"
                                                        name="features[{{
                                                            $feature->id
                                                        }}][period_value]"
                                                        value="{{
                                                            $quotaPeriodValue
                                                        }}"
                                                        min="1"
                                                        step="1"
                                                    >
                                                </div>

                                                <div
                                                    class="quota-field"
                                                    data-quota-period
                                                >
                                                    <label>
                                                        Unit
                                                    </label>

                                                    <select
                                                        class="feature-input"
                                                        name="features[{{
                                                            $feature->id
                                                        }}][period_unit]"
                                                    >
                                                        @foreach(
                                                            [
                                                                'minute'
                                                                    => 'Minute',
                                                                'hour'
                                                                    => 'Hour',
                                                                'day'
                                                                    => 'Day',
                                                                'week'
                                                                    => 'Week',
                                                                'month'
                                                                    => 'Month',
                                                                'year'
                                                                    => 'Year',
                                                            ]
                                                            as $unitValue
                                                                => $unitLabel
                                                        )
                                                            <option
                                                                value="{{
                                                                    $unitValue
                                                                }}"
                                                                {{
                                                                    $quotaPeriodUnit
                                                                    === $unitValue
                                                                        ? 'selected'
                                                                        : ''
                                                                }}
                                                            >
                                                                {{ $unitLabel }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endforeach

                        <div class="plan-save">
                            <button
                                type="submit"
                                class="save-plan-button"
                            >
                                <i class="fas fa-save"></i>
                                Save {{ $plan->name }}
                            </button>
                        </div>
                    </form>
                </div>
            @endforeach
        </div>
    </section>
</div>

<script>
document.addEventListener(
    'DOMContentLoaded',
    function () {
        function updateQuotaGroup(group) {
            const mode =
                group.querySelector(
                    '[data-quota-mode]'
                );

            if (! mode) {
                return;
            }

            const amountFields =
                group.querySelectorAll(
                    '[data-quota-amount]'
                );

            const periodFields =
                group.querySelectorAll(
                    '[data-quota-period]'
                );

            const amountInputs =
                group.querySelectorAll(
                    '[data-quota-amount] input'
                );

            const periodInputs =
                group.querySelectorAll(
                    '[data-quota-period] input, ' +
                    '[data-quota-period] select'
                );

            const showAmount =
                mode.value === 'total'
                || mode.value === 'recurring';

            const showPeriod =
                mode.value === 'recurring';

            amountFields.forEach(
                function (field) {
                    field.classList.toggle(
                        'quota-hidden',
                        ! showAmount
                    );
                }
            );

            periodFields.forEach(
                function (field) {
                    field.classList.toggle(
                        'quota-hidden',
                        ! showPeriod
                    );
                }
            );

            amountInputs.forEach(
                function (input) {
                    input.required =
                        showAmount;
                }
            );

            periodInputs.forEach(
                function (input) {
                    input.required =
                        showPeriod;
                }
            );
        }

        document
            .querySelectorAll(
                '[data-quota-group]'
            )
            .forEach(
                function (group) {
                    updateQuotaGroup(
                        group
                    );

                    const mode =
                        group.querySelector(
                            '[data-quota-mode]'
                        );

                    mode.addEventListener(
                        'change',
                        function () {
                            updateQuotaGroup(
                                group
                            );
                        }
                    );
                }
            );
    }
);
</script>
@endsection