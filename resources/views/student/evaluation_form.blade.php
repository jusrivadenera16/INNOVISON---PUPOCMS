@extends('layouts.evaluation')

@section('title', 'Online Client Satisfaction Survey')

@php
    $isPreview = (bool) ($isPreview ?? false);
    $isSubmitted = $evaluation->status === 'submitted' && $evaluation->submitted_at;
    $clientTypes = [
        'Faculty',
        'Administrative Employee',
        'Student',
        'Alumni',
        'Parent of a Student',
        'Industry Partner',
        'Visitor/Guest',
    ];
    $sexOptions = ['Male', 'Female', 'Prefer not to say'];
    $ageGroups = [
        '19 or lower',
        '20-34 years old',
        '35-49 years old',
        '50-64 years old',
        '65 or higher',
        'Prefer not to say',
    ];
    $clientOptionIcons = [
        'Faculty' => 'user-circle',
        'Administrative Employee' => 'briefcase',
        'Student' => 'academic-cap',
        'Alumni' => 'users',
        'Parent of a Student' => 'users',
        'Industry Partner' => 'briefcase',
        'Visitor/Guest' => 'user-circle',
    ];
    $sexOptionIcons = [
        'Male' => 'user-circle',
        'Female' => 'user-circle',
        'Prefer not to say' => 'eye',
    ];
    $ageOptionIcons = [
        '19 or lower' => 'user-circle',
        '20-34 years old' => 'user-circle',
        '35-49 years old' => 'users',
        '50-64 years old' => 'users',
        '65 or higher' => 'user-circle',
        'Prefer not to say' => 'eye',
    ];
    $ccQuestions = [
        'cc1' => [
            'Alin sa mga sumusunod ang naglalarawan sa iyong kaalaman sa Citizen\'s Charter (CC)?',
            [
                '1' => 'Alam ko ang CC at nakita ko ito sa napuntahang opisina.',
                '2' => 'Alam ko ang CC pero hindi ko ito nakita sa napuntahang opisina.',
                '3' => 'Nalaman ko ang CC nang makita ko ito sa napuntahang opisina.',
                '4' => 'Hindi ko alam kung ano ang CC at wala akong nakita sa napuntahang opisina.',
            ],
        ],
        'cc2' => [
            'Kung alam ang Citizen\'s Charter (CC), masasabi mo ba na ang Citizen\'s Charter (CC) ng napuntahang opisina ay...',
            [
                '1' => 'Madaling makita',
                '2' => 'Medyo madaling makita',
                '3' => 'Mahirap makita',
                '4' => 'Hindi makita',
            ],
        ],
        'cc3' => [
            'Kung alam ang Citizen\'s Charter (CC), gaano nakatulong ang Citizen\'s Charter (CC) sa transaksyon mo?',
            [
                '1' => 'Sobrang nakatulong',
                '2' => 'Nakatulong naman',
                '3' => 'Hindi nakatulong',
            ],
        ],
    ];
    $sqdScale = [
        5 => 'Labis na sumasang-ayon',
        4 => 'Sumasang-ayon',
        3 => 'Walang kinikilingan',
        2 => 'Hindi sumasang-ayon',
        1 => 'Lubos na hindi sumasang-ayon',
        0 => 'N/A',
    ];
    $sqdQuestions = [
        ['code' => 'SQD0', 'title' => 'Overall Satisfaction', 'text' => 'Nasiyahan ako sa serbisyo na aking natanggap sa napuntahan na tanggapan.'],
        ['code' => 'SQD1', 'title' => 'Responsiveness / Time', 'text' => 'Makatwiran ang oras na aking ginugol para sa pagproseso ng aking transaksyon.'],
        ['code' => 'SQD2', 'title' => 'Requirements', 'text' => 'Nasusunod ang kinakailangang dokumento at mga hakbang batay sa impormasyong ibinigay.'],
        ['code' => 'SQD3', 'title' => 'Ease of Transaction', 'text' => 'Ang mga hakbang sa pagproseso, kasama na ang pagbayad ay madali at simple lamang.'],
        ['code' => 'SQD4', 'title' => 'Accessibility of Information', 'text' => 'Mabilis at madali kong nahanap ang impormasyon tungkol sa aking transaksyon mula sa opisina.'],
        ['code' => 'SQD5', 'title' => 'Cost / Fees', 'text' => 'Nagbayad ako ng makatwirang halaga para sa aking transaksyon.'],
        ['code' => 'SQD6', 'title' => 'Fairness', 'text' => 'Pakiramdam ko ay patas ang opisina sa lahat, o “walang palakasan”, sa aking transaksyon.'],
        ['code' => 'SQD7', 'title' => 'Courtesy and Helpfulness', 'text' => 'Magalang akong trinato ng mga tauhan, at alam ko na sila ay handang tumulong sa akin.'],
        ['code' => 'SQD8', 'title' => 'Service Outcome', 'text' => 'Nakuha ang kinakailangan mula sa opisina, kung tinanggihan man, ito ay sapat na naipaliwanag.'],
    ];
    $initialStep = $isSubmitted ? 4 : ($errors->any() ? 1 : 0);
@endphp

@push('styles')
<style>
    body.evaluation-page {
        position: relative;
        isolation: isolate;
        min-height: 100vh;
        overflow-x: hidden;
        background: #f7f1f2 !important;
    }

    body.evaluation-page::before {
        position: fixed;
        inset: 0;
        z-index: -2;
        content: '';
        background:
            linear-gradient(180deg, rgba(255, 255, 255, .14) 0%, rgba(255, 255, 255, .3) 32%, rgba(255, 255, 255, .76) 68%, rgba(255, 255, 255, .98) 100%),
            url('{{ asset('images/PUPBG.jpg') }}') center / cover no-repeat;
    }

    body.evaluation-page::after {
        position: fixed;
        inset: 0;
        z-index: -1;
        content: '';
        background: linear-gradient(180deg, rgba(255, 255, 255, 0), rgba(255, 255, 255, .38));
        pointer-events: none;
    }

    .survey-shell {
        position: relative;
        z-index: 1;
        width: min(100%, 818px);
        margin: 0 auto;
        padding: 0 14px 28px;
    }

    .survey-flow-layout,
    .survey-flow-main {
        display: contents;
    }

    .survey-step-nav {
        display: none;
    }

    .survey-waves {
        position: fixed;
        inset: 0;
        z-index: 0;
        overflow: hidden;
        pointer-events: none;
    }

    .survey-waves::before,
    .survey-waves::after {
        position: absolute;
        width: min(1000px, 100vw);
        height: 145vh;
        border: 78px solid rgba(141, 16, 32, .52);
        border-radius: 50%;
        content: '';
        opacity: .82;
    }

    .survey-waves::before {
        top: 50vh;
        left: -34vw;
        transform: rotate(25deg);
        box-shadow: 0 0 0 18px rgba(141, 16, 32, .14);
    }

    .survey-waves::after {
        top: 50vh;
        right: -34vw;
        transform: rotate(-25deg);
        box-shadow: 0 0 0 18px rgba(141, 16, 32, .14);
    }

    .survey-card {
        position: relative;
        z-index: 2;
        overflow: hidden;
        border: 0;
        border-radius: 0;
        background: transparent;
        box-shadow: none;
    }

    .survey-shell.is-flow-layout {
        width: min(calc(100% - 56px), 1280px);
        padding: 0 0 30px;
    }

    .survey-shell:not(.is-flow-layout) .survey-hero {
        display: flex;
        height: auto;
        flex-direction: column;
        align-items: center;
        padding: 0 12px 12px;
        text-align: center;
    }

    .survey-shell:not(.is-flow-layout) .survey-brand-lockup {
        justify-content: center;
    }

    .survey-shell:not(.is-flow-layout) .survey-brand-lockup img {
        width: 84px;
        height: 84px;
        margin: 0 auto 5px;
    }

    .survey-shell:not(.is-flow-layout) .survey-brand-name,
    .survey-shell:not(.is-flow-layout) .survey-hero-divider,
    .survey-shell:not(.is-flow-layout) .survey-hero-motto {
        display: none;
    }

    .survey-shell:not(.is-flow-layout) .survey-heading-lockup {
        text-align: center;
    }

    .survey-shell.is-flow-layout .survey-hero {
        display: grid;
        position: relative;
        left: 50%;
        grid-template-columns: 194px 1px minmax(0, 1fr) 112px;
        column-gap: 16px;
        align-items: center;
        width: 100vw;
        height: 80px;
        margin-left: -50vw;
        padding: 4px max(42px, calc((100vw - 1280px) / 2));
        border-bottom: 1px solid rgba(141, 16, 32, .2);
        background: rgba(255, 255, 255, .84);
        text-align: left;
    }

    .survey-brand-lockup {
        display: flex;
        min-width: 0;
        align-items: center;
        gap: 7px;
    }

    .survey-brand-lockup img {
        width: 70px;
        height: 70px;
        flex: 0 0 auto;
        margin: 0;
    }

    .survey-brand-name {
        color: #171717;
        font-family: Georgia, 'Times New Roman', serif;
        font-size: 9px;
        font-weight: 700;
        line-height: 1.25;
    }

    .survey-hero-divider {
        width: 1px;
        height: 40px;
        background: #d7c4c8;
    }

    .survey-heading-lockup {
        min-width: 0;
    }

    .survey-shell.is-flow-layout .survey-hero h1 {
        font-size: clamp(24px, 2vw, 28px);
        line-height: 1.05;
        white-space: nowrap;
    }

    .survey-shell.is-flow-layout .survey-hero p {
        margin-top: 3px;
        color: #263244;
        font-size: 11px;
    }

    .survey-shell.is-flow-layout .survey-reference {
        margin-top: 2px;
        color: #344054;
        font-size: 10px;
    }

    .survey-hero-motto {
        justify-self: end;
        max-width: 112px;
        color: #667085;
        font-size: 10px;
        font-style: italic;
        line-height: 1.25;
        text-align: right;
    }

    .survey-shell.is-flow-layout .survey-flow-layout {
        display: grid;
        grid-template-columns: 180px minmax(0, 1fr);
        gap: 12px;
        align-items: stretch;
        margin-top: 8px;
    }

    .survey-shell.is-flow-layout .survey-flow-main {
        display: block;
        min-width: 0;
    }

    .survey-shell.is-flow-layout .survey-step-nav {
        position: relative;
        display: block;
        min-height: 100%;
        padding: 17px 11px 100px;
        border-radius: 0 0 4px 4px;
        background:
            linear-gradient(180deg, rgba(126, 12, 28, .94), rgba(126, 12, 28, .58)),
            url('{{ asset('images/PUPBG.jpg') }}') center / cover no-repeat;
        box-shadow: 0 14px 30px rgba(74, 16, 25, .16);
    }

    .survey-sidebar-signature {
        position: absolute;
        right: 10px;
        bottom: 15px;
        left: 10px;
        color: rgba(255, 255, 255, .88);
        text-align: center;
    }

    .survey-sidebar-signature strong {
        display: block;
        font-family: cursive;
        font-size: 36px;
        font-style: italic;
        font-weight: 500;
        line-height: 1;
    }

    .survey-sidebar-signature span {
        display: block;
        margin-top: 4px;
        font-family: Georgia, 'Times New Roman', serif;
        font-size: 9px;
        line-height: 1.3;
    }

    .survey-step-nav-title {
        margin: 0 8px 14px;
        color: #ffffff;
        font-size: 12px;
        font-weight: 800;
        letter-spacing: .02em;
        text-transform: uppercase;
    }

    .survey-step-nav-list {
        display: grid;
        gap: 8px;
        margin: 0;
        padding: 0;
        list-style: none;
    }

    .survey-step-nav-item button {
        display: flex;
        width: 100%;
        align-items: center;
        gap: 9px;
        padding: 8px 9px;
        border: 0;
        border-radius: 5px;
        background: transparent;
        color: rgba(255, 255, 255, .78);
        font: inherit;
        font-size: 11px;
        font-weight: 700;
        line-height: 1.25;
        text-align: left;
        cursor: pointer;
    }

    .survey-step-nav-item button:hover,
    .survey-step-nav-item.is-active button {
        background: #8d1020;
        color: #ffffff;
    }

    .survey-step-nav-number {
        display: grid;
        width: 26px;
        height: 26px;
        flex: 0 0 auto;
        place-items: center;
        border-radius: 50%;
        background: rgba(255, 255, 255, .7);
        color: #8d1020;
        font-size: 11px;
        font-weight: 900;
    }

    .survey-step-nav-item.is-active .survey-step-nav-number {
        background: #ffffff;
    }

    .survey-shell.is-flow-layout .survey-progress-wrap {
        display: block;
        padding: 15px 22px 0;
        border: 1px solid #e6d8dc;
        border-bottom: 0;
        border-radius: 8px 8px 0 0;
        background: rgba(255, 255, 255, .94);
    }

    .survey-shell.is-flow-layout .survey-progress-label {
        color: #667085;
        font-size: 11px;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .survey-shell.is-flow-layout .survey-progress-count {
        text-transform: none;
    }

    .survey-shell.is-flow-layout .survey-body {
        min-height: 440px;
        margin-top: 0;
        padding: 14px 22px 16px;
        border-top: 0;
        border-radius: 0 0 8px 8px;
        background: rgba(255, 255, 255, .94);
        box-shadow: 0 14px 30px rgba(15, 23, 42, .1);
    }

    .survey-shell.is-flow-layout .survey-step-heading {
        display: block;
        position: relative;
        padding: 0 0 15px;
    }

    .survey-shell.is-flow-layout .survey-step-heading h2 {
        font-size: 24px;
    }

    .survey-shell.is-flow-layout .survey-step-heading p {
        margin-top: 6px;
        font-size: 12px;
    }

    .survey-shell.is-flow-layout .survey-step-heading::after {
        display: block;
        width: 42px;
        height: 3px;
        margin-top: 9px;
        content: '';
        background: #a31629;
    }

    .survey-profile-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
        margin-top: 16px;
    }

    .survey-shell.is-flow-layout .survey-profile-grid .survey-fieldset {
        min-width: 0;
        margin: 0;
        padding: 13px 12px 12px;
        border: 1px solid #e6e8ed;
        border-radius: 5px;
        background: linear-gradient(180deg, rgba(255, 255, 255, .98), rgba(250, 251, 252, .95));
    }

    .survey-shell.is-flow-layout .survey-profile-grid .survey-fieldset legend {
        margin-bottom: 3px;
        color: #192334;
        font-size: 14px;
    }

    .survey-shell.is-flow-layout .survey-profile-grid .survey-fieldset::after {
        display: block;
        margin-bottom: 9px;
        color: #667085;
        font-size: 11px;
    }

    .survey-shell.is-flow-layout .survey-profile-grid .survey-fieldset:nth-child(1)::after {
        content: 'Select the option that best describes you.';
    }

    .survey-shell.is-flow-layout .survey-profile-grid .survey-fieldset:nth-child(2)::after {
        content: 'Select your sex.';
    }

    .survey-shell.is-flow-layout .survey-profile-grid .survey-fieldset:nth-child(3)::after {
        content: 'Select your age range.';
    }

    .survey-shell.is-flow-layout .survey-profile-grid .survey-option-grid {
        grid-template-columns: 1fr;
        gap: 5px;
    }

    .survey-shell.is-flow-layout .survey-profile-grid .survey-option {
        position: relative;
        align-items: center;
        gap: 8px;
        min-height: 35px;
        padding: 3px 7px;
        border-color: #e1e5ea;
        border-radius: 4px;
        font-size: 12px;
        line-height: 1.25;
        letter-spacing: 0;
        text-transform: none;
        transition: border-color .18s ease, background-color .18s ease, color .18s ease;
    }

    .survey-shell.is-flow-layout .survey-profile-grid .survey-option > span:last-child {
        text-transform: none;
        letter-spacing: 0;
    }

    .survey-shell.is-flow-layout .survey-profile-grid .survey-option input {
        position: absolute;
        width: 1px;
        height: 1px;
        opacity: 0;
    }

    .survey-option-icon {
        display: grid;
        width: 29px;
        height: 29px;
        flex: 0 0 auto;
        place-items: center;
        border-radius: 50%;
        background: #fff0f1;
        color: #a31629;
    }

    .survey-option-icon svg {
        width: 16px;
        height: 16px;
    }

    .survey-step[data-survey-step="1"] .survey-profile-grid .survey-option:hover {
        border-color: #8d1020;
    }

    .survey-step[data-survey-step="1"] .survey-profile-grid .survey-option:has(input:checked) {
        border-color: #8d1020;
        background: #8d1020;
        color: #ffffff;
    }

    .survey-step[data-survey-step="1"] .survey-profile-grid .survey-option:has(input:checked) .survey-option-icon {
        background: #facc15;
        color: #8d1020;
    }

    .survey-shell.is-flow-layout .survey-profile-grid .survey-option:has(input:focus-visible) {
        outline: 2px solid rgba(250, 204, 21, .7);
        outline-offset: 1px;
    }

    .survey-shell.is-flow-layout .survey-actions {
        margin-top: 16px;
        padding-top: 14px;
    }

    .survey-shell.is-flow-layout .survey-actions .survey-button {
        min-height: 38px;
        padding: 0 17px;
        font-size: 12px;
    }

    .survey-shell.is-flow-layout .survey-actions .survey-button svg {
        width: 16px;
        height: 16px;
    }

    .survey-hero {
        padding: 0 12px 12px;
        color: #86111f;
        text-align: center;
    }

    .survey-hero-logo {
        display: block;
        width: 84px;
        height: 84px;
        margin: 0 auto 5px;
        object-fit: contain;
    }

    .survey-hero h1 {
        margin: 0;
        color: #86111f;
        font-size: clamp(24px, 3vw, 30px);
        line-height: 1.2;
        letter-spacing: .01em;
    }

    .survey-hero p {
        margin: 5px 0 0;
        color: #ffffff;
        font-size: 14px;
    }

    .survey-reference {
        margin-top: 9px;
        color: #ffffff;
        font-style: italic;
        font-size: 12px;
        line-height: 1.5;
    }

    .survey-progress-wrap {
        display: none;
        padding: 14px 30px 0;
        border: 1px solid #e6d8dc;
        border-bottom: 0;
        border-radius: 14px 14px 0 0;
        background: #ffffff;
    }

    .survey-progress-label {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        color: #697386;
        font-size: 11px;
        font-weight: 800;
    }

    .survey-progress-track {
        height: 6px;
        margin-top: 7px;
        overflow: hidden;
        border-radius: 999px;
        background: #eee4e6;
    }

    .survey-progress-bar {
        width: 0%;
        height: 100%;
        border-radius: inherit;
        background: #8d1020;
        transition: width .2s ease;
    }

    .survey-body {
        padding: 20px 30px 14px;
        border: 1px solid #e6d8dc;
        border-radius: 14px;
        background: #ffffff;
        box-shadow: 0 18px 42px rgba(15, 23, 42, .12);
    }

    .survey-step {
        display: none;
    }

    .survey-step.is-active {
        display: block;
    }

    .survey-step-heading {
        display: flex;
        align-items: center;
        gap: 14px;
        padding-bottom: 16px;
        border-bottom: 1px solid #eadde0;
    }

    .survey-step-heading:not(:has(.survey-step-heading-icon)) {
        display: block;
    }

    .survey-step-heading-icon {
        display: grid;
        width: 54px;
        height: 54px;
        flex: 0 0 auto;
        place-items: center;
        border-radius: 50%;
        background: #fcecef;
        color: #8d1020;
    }

    .survey-step-heading-icon svg {
        width: 28px;
        height: 28px;
    }

    .survey-step-heading h2 {
        margin: 0;
        color: #86111f;
        font-size: 20px;
    }

    .survey-step-heading p {
        margin: 6px 0 0;
        color: #667085;
        font-size: 13px;
        line-height: 1.5;
    }

    .survey-section-copy {
        margin: 20px 0 12px;
        color: #263244;
        font-size: 13px;
        font-weight: 800;
        line-height: 1.5;
    }

    .survey-privacy-rows {
        margin-top: 16px;
    }

    .survey-privacy-row {
        display: grid;
        grid-template-columns: 46px minmax(0, 1fr);
        gap: 13px;
        align-items: start;
        padding: 0 0 12px;
    }

    .survey-privacy-row-icon,
    .survey-consent-box-icon {
        display: grid;
        width: 46px;
        height: 46px;
        place-items: center;
        border-radius: 50%;
        background: #fff0f1;
        color: #a31629;
    }

    .survey-privacy-row-icon svg {
        width: 24px;
        height: 24px;
    }

    .survey-privacy-row strong,
    .survey-consent-box strong {
        display: block;
        color: #192334;
        font-size: 13px;
        line-height: 1.25;
    }

    .survey-privacy-row p,
    .survey-consent-box-text {
        margin: 2px 0 0;
        color: #465164;
        font-size: 12px;
        line-height: 1.42;
    }

    .survey-privacy-row a {
        color: #86111f;
        font-weight: 800;
        text-decoration: underline;
    }

    .survey-privacy-row a svg {
        display: inline;
        width: 12px;
        height: 12px;
        margin-left: 2px;
        vertical-align: -2px;
    }

    .survey-consent-box {
        position: relative;
        display: grid;
        grid-template-columns: 46px minmax(0, 1fr);
        gap: 13px;
        align-items: start;
        margin-top: 2px;
        padding: 11px 12px;
        border: 1px solid #f1cfd3;
        border-radius: 7px;
        background: #fff3f4;
        cursor: pointer;
    }

    .survey-consent-box-icon {
        background: #a31629;
        color: #ffffff;
    }

    .survey-consent-box-icon svg {
        width: 25px;
        height: 25px;
    }

    .survey-consent-box strong {
        margin-top: 1px;
        color: #86111f;
    }

    .survey-option input,
    .survey-scale-option input {
        accent-color: #8d1020;
    }

    .survey-actions-consent {
        display: block;
        margin-top: 11px;
        padding-top: 14px;
    }

    .survey-actions-consent .survey-button {
        width: 100%;
        min-height: 37px;
        gap: 8px;
        border-radius: 7px;
        font-size: 14px;
    }

    .survey-actions-consent .survey-button svg {
        width: 17px;
        height: 17px;
    }

    .survey-consent-continue {
        position: relative;
        overflow: hidden;
        isolation: isolate;
        transition: background-color .25s ease, border-color .25s ease, color .25s ease, box-shadow .25s ease;
    }

    .survey-consent-continue::before {
        position: absolute;
        top: -70%;
        left: -30%;
        z-index: 0;
        width: 24%;
        height: 240%;
        content: '';
        background: rgba(255, 255, 255, .78);
        pointer-events: none;
        transform: translateX(-220%) rotate(18deg);
    }

    .survey-consent-continue > span,
    .survey-consent-continue > svg {
        position: relative;
        z-index: 1;
    }

    .survey-button.survey-consent-continue:hover,
    .survey-button.survey-consent-continue:focus-visible {
        border-color: #facc15;
        background: #facc15;
        color: #86111f;
        box-shadow: 0 8px 18px rgba(250, 204, 21, .28);
    }

    .survey-button.survey-consent-continue:hover::before {
        animation: survey-consent-sweep .8s ease both;
    }

    @keyframes survey-consent-sweep {
        from { transform: translateX(-220%) rotate(18deg); }
        to { transform: translateX(720%) rotate(18deg); }
    }

    .survey-fieldset {
        min-width: 0;
        margin: 20px 0 0;
        padding: 0;
        border: 0;
    }

    .survey-fieldset legend {
        margin-bottom: 10px;
        color: #263244;
        font-size: 13px;
        font-weight: 800;
        line-height: 1.5;
    }

    .survey-required {
        color: #b42318;
    }

    .survey-option-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 8px;
    }

    .survey-option {
        display: flex;
        align-items: flex-start;
        gap: 9px;
        min-height: 44px;
        padding: 11px 12px;
        border: 1px solid #dfcdd1;
        border-radius: 7px;
        background: #ffffff;
        color: #344054;
        cursor: pointer;
        font-size: 12px;
        line-height: 1.4;
    }

    .survey-option:has(input:checked) {
        border-color: #8d1020;
        background: #fff4f5;
        color: #68101b;
    }

    .survey-option input {
        flex: 0 0 auto;
        margin-top: 2px;
    }

    .survey-cc-options .survey-option {
        align-items: flex-start;
        letter-spacing: 0;
        text-transform: none;
        transition: border-color .18s ease, background-color .18s ease, color .18s ease;
    }

    .survey-step[data-survey-step="2"] .survey-cc-options .survey-option:hover {
        border-color: #8d1020;
    }

    .survey-step[data-survey-step="2"] .survey-cc-options .survey-option:has(input[type="radio"]:checked) {
        border-color: #8d1020;
        background: #8d1020;
        color: #ffffff;
    }

    .survey-cc-options .survey-option input[type="radio"] {
        width: 17px;
        height: 17px;
        flex: 0 0 17px;
        margin: 1px 0 0;
        padding: 0;
        border: 0;
        border-radius: 50%;
        box-shadow: none;
    }

    .survey-step[data-survey-step="2"] .survey-cc-options input[type="radio"]:checked {
        accent-color: #facc15;
    }

    .survey-cc-list {
        display: grid;
        gap: 16px;
        margin-top: 18px;
    }

    .survey-cc-question {
        padding: 14px;
        border: 1px solid #e6d8dc;
        border-radius: 8px;
        background: #fffafb;
    }

    .survey-cc-question > p {
        margin: 0 0 10px;
        color: #263244;
        font-size: 13px;
        font-weight: 800;
        line-height: 1.5;
    }

    .survey-cc-options {
        display: grid;
        gap: 7px;
    }

    .survey-scale-note {
        margin-top: 18px;
        overflow-x: auto;
        border: 1px solid #e6d8dc;
        border-radius: 8px;
    }

    .survey-scale-table {
        width: 100%;
        min-width: 640px;
        border-collapse: collapse;
        font-size: 12px;
    }

    .survey-scale-table th,
    .survey-scale-table td {
        padding: 9px 10px;
        border-bottom: 1px solid #eee3e5;
        text-align: left;
    }

    .survey-scale-table th {
        background: #fff4f5;
        color: #68101b;
        font-weight: 800;
    }

    .survey-scale-table tr:last-child td {
        border-bottom: 0;
    }

    .survey-sqd-list {
        display: grid;
        gap: 12px;
        margin-top: 18px;
    }

    .survey-sqd-question {
        padding: 14px;
        border: 1px solid #e6d8dc;
        border-radius: 8px;
        background: #ffffff;
    }

    .survey-sqd-title {
        color: #86111f;
        font-size: 12px;
        font-weight: 900;
    }

    .survey-sqd-text {
        margin: 4px 0 12px;
        color: #344054;
        font-size: 13px;
        line-height: 1.5;
    }

    .survey-sqd-options {
        display: grid;
        grid-template-columns: repeat(6, minmax(38px, 1fr));
        gap: 5px;
    }

    .survey-scale-option {
        position: relative;
        min-width: 0;
    }

    .survey-scale-option input {
        position: absolute;
        width: 1px;
        height: 1px;
        opacity: 0;
    }

    .survey-scale-option label {
        display: grid;
        min-height: 36px;
        place-items: center;
        border: 1px solid #dfcdd1;
        border-radius: 6px;
        background: #ffffff;
        color: #475467;
        cursor: pointer;
        font-size: 12px;
        font-weight: 900;
        transition: border-color .18s ease, background-color .18s ease, color .18s ease;
    }

    .survey-step[data-survey-step="3"] .survey-scale-option label:hover {
        border-color: #8d1020;
    }

    .survey-scale-option input:checked + label {
        border-color: #8d1020;
        background: #8d1020;
        color: #ffffff;
    }

    .survey-scale-option input:focus-visible + label {
        outline: 3px solid rgba(250, 204, 21, .55);
        outline-offset: 2px;
    }

    .survey-suggestion-label {
        display: block;
        margin-top: 20px;
        color: #263244;
        font-size: 13px;
        font-weight: 800;
    }

    .survey-suggestion {
        width: 100%;
        min-height: 180px;
        margin-top: 9px;
        padding: 12px 13px;
        border: 1px solid #d7c4c8;
        border-radius: 7px;
        color: #263244;
        font: inherit;
        font-size: 13px;
        line-height: 1.5;
        resize: vertical;
    }

    .survey-suggestion:focus {
        border-color: #8d1020;
        outline: 3px solid rgba(141, 16, 32, .1);
    }

    .survey-suggestion-meta {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        margin-top: 5px;
        color: #667085;
        font-size: 11px;
    }

    .survey-actions {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        margin-top: 24px;
        padding-top: 18px;
        border-top: 1px solid #eadde0;
    }

    .survey-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 42px;
        padding: 0 18px;
        border: 1px solid transparent;
        border-radius: 7px;
        font-size: 13px;
        font-weight: 800;
        cursor: pointer;
    }

    .survey-button.secondary {
        border-color: #d7c4c8;
        background: #ffffff;
        color: #86111f;
    }

    .survey-button.primary {
        background: #8d1020;
        color: #ffffff;
    }

    .survey-step[data-survey-step="1"] .survey-actions .survey-button,
    .survey-step[data-survey-step="2"] .survey-actions .survey-button,
    .survey-step[data-survey-step="3"] .survey-actions .survey-button {
        transition: border-color .18s ease, background-color .18s ease, color .18s ease, box-shadow .18s ease, transform .18s ease;
    }

    .survey-step[data-survey-step="1"] .survey-actions .survey-button:focus-visible,
    .survey-step[data-survey-step="2"] .survey-actions .survey-button:focus-visible,
    .survey-step[data-survey-step="3"] .survey-actions .survey-button:focus-visible {
        outline: 2px solid #8d1020;
        outline-offset: 2px;
    }

    .survey-step .survey-actions .survey-button {
        position: relative;
        overflow: hidden;
        isolation: isolate;
        transition: background-color .25s ease, border-color .25s ease, color .25s ease, box-shadow .25s ease, transform .25s ease;
    }

    .survey-step .survey-actions .survey-button::before {
        position: absolute;
        top: -70%;
        left: -30%;
        z-index: 0;
        width: 24%;
        height: 240%;
        content: '';
        background: rgba(255, 255, 255, .78);
        pointer-events: none;
        transform: translateX(-220%) rotate(18deg);
    }

    .survey-step .survey-actions .survey-button > span,
    .survey-step .survey-actions .survey-button > svg {
        position: relative;
        z-index: 1;
    }

    .survey-step .survey-actions .survey-button:hover,
    .survey-step .survey-actions .survey-button:focus-visible {
        border-color: #facc15;
        background: #facc15;
        color: #86111f;
        box-shadow: 0 8px 18px rgba(250, 204, 21, .28);
        transform: translateY(-1px);
    }

    .survey-step .survey-actions .survey-button:hover::before,
    .survey-step .survey-actions .survey-button:focus-visible::before {
        animation: survey-action-sweep .8s ease both;
    }

    @keyframes survey-action-sweep {
        from { transform: translateX(-220%) rotate(18deg); }
        to { transform: translateX(720%) rotate(18deg); }
    }

    .survey-error {
        margin: 0 0 16px;
        padding: 10px 12px;
        border: 1px solid #f0b8b8;
        border-radius: 7px;
        background: #fff5f5;
        color: #b42318;
        font-size: 12px;
        font-weight: 700;
    }

    .survey-submitted {
        padding: 18px;
        border: 1px solid #bbdfc8;
        border-radius: 8px;
        background: #f0faf3;
        color: #166534;
        font-size: 14px;
        font-weight: 700;
        line-height: 1.5;
    }

    @media (max-width: 680px) {
        .survey-shell {
            padding: 16px 10px 40px;
        }

        .survey-shell.is-flow-layout {
            width: calc(100% - 20px);
            padding: 0 0 24px;
        }

        .survey-waves::before,
        .survey-waves::after {
            width: 160vw;
            height: 130vh;
            border-width: 52px;
        }

        .survey-waves::before {
            top: 52vh;
            left: -46vw;
        }

        .survey-waves::after {
            top: 52vh;
            right: -46vw;
        }

        .survey-hero-logo {
            width: 76px;
            height: 76px;
        }

        .survey-hero,
        .survey-body,
        .survey-progress-wrap {
            padding-left: 18px;
            padding-right: 18px;
        }

        .survey-shell.is-flow-layout .survey-hero {
            grid-template-columns: 54px minmax(0, 1fr);
            grid-template-rows: auto;
            column-gap: 10px;
            height: auto;
            min-height: 64px;
            padding: 6px 14px;
        }

        .survey-shell.is-flow-layout .survey-brand-lockup {
            grid-column: 1;
            grid-row: 1;
        }

        .survey-shell.is-flow-layout .survey-brand-lockup img {
            width: 54px;
            height: 54px;
        }

        .survey-shell.is-flow-layout .survey-brand-name,
        .survey-shell.is-flow-layout .survey-hero-divider,
        .survey-shell.is-flow-layout .survey-hero-motto {
            display: none;
        }

        .survey-shell.is-flow-layout .survey-heading-lockup {
            grid-column: 2;
            grid-row: 1;
        }

        .survey-shell.is-flow-layout .survey-hero h1 {
            font-size: 19px;
            white-space: normal;
        }

        .survey-shell.is-flow-layout .survey-hero p,
        .survey-shell.is-flow-layout .survey-reference {
            font-size: 9px;
        }

        .survey-shell.is-flow-layout .survey-step-nav {
            padding-bottom: 9px;
        }

        .survey-sidebar-signature {
            display: none;
        }

        .survey-shell.is-flow-layout .survey-flow-layout {
            grid-template-columns: 1fr;
        }

        .survey-shell.is-flow-layout .survey-step-nav {
            min-height: auto;
            padding: 9px 7px;
        }

        .survey-shell.is-flow-layout .survey-step-nav-title {
            margin-bottom: 7px;
        }

        .survey-shell.is-flow-layout .survey-step-nav-list {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 4px;
        }

        .survey-shell.is-flow-layout .survey-step-nav-item button {
            min-height: 38px;
            padding: 6px;
            font-size: 10px;
        }

        .survey-shell.is-flow-layout .survey-profile-grid {
            grid-template-columns: 1fr;
        }

        .survey-option-grid {
            grid-template-columns: 1fr;
        }

        .survey-actions {
            flex-direction: column-reverse;
        }

        .survey-button {
            width: 100%;
        }
    }
</style>
@endpush

@section('content')
<div class="survey-shell">
    <div class="survey-waves" aria-hidden="true"></div>
    <article class="survey-card">
        <header class="survey-hero">
            <div class="survey-brand-lockup">
                <img src="{{ asset('images/pup_logo.png') }}" alt="">
                <span class="survey-brand-name">POLYTECHNIC UNIVERSITY<br>OF THE PHILIPPINES</span>
            </div>
            <span class="survey-hero-divider" aria-hidden="true"></span>
            <div class="survey-heading-lockup">
                <h1>Online Client Satisfaction Survey</h1>
                <p>Polytechnic University of the Philippines (PUP)</p>
                <div class="survey-reference">Reference: ARTA Memorandum Circular No. 2023-05, Series of 2023</div>
            </div>
            <div class="survey-hero-motto">A more responsive public service.</div>
        </header>

        <div class="survey-flow-layout">
            <aside class="survey-step-nav" aria-label="Survey sections">
                <p class="survey-step-nav-title">Survey sections</p>
                <ol class="survey-step-nav-list">
                    <li class="survey-step-nav-item" data-survey-nav-item="1">
                        <button type="button" data-survey-jump="1"><span class="survey-step-nav-number">1</span><span>Profile of Client</span></button>
                    </li>
                    <li class="survey-step-nav-item" data-survey-nav-item="2">
                        <button type="button" data-survey-jump="2"><span class="survey-step-nav-number">2</span><span>Citizen's Charter (CC)</span></button>
                    </li>
                    <li class="survey-step-nav-item" data-survey-nav-item="3">
                        <button type="button" data-survey-jump="3"><span class="survey-step-nav-number">3</span><span>Service Quality Dimensions (SQD)</span></button>
                    </li>
                    <li class="survey-step-nav-item" data-survey-nav-item="4">
                        <button type="button" data-survey-jump="4"><span class="survey-step-nav-number">4</span><span>Suggestions</span></button>
                    </li>
                </ol>
                <div class="survey-sidebar-signature" aria-hidden="true">
                    <strong>PUP</strong>
                    <span>The Country's Premier Polytechnic University</span>
                </div>
            </aside>
            <div class="survey-flow-main">
                <div class="survey-progress-wrap" aria-live="polite">
                    <div class="survey-progress-label">
                        <span id="surveyProgressLabel">{{ $isSubmitted ? 'Section 4 of 4' : 'Data Privacy Notice' }}</span>
                        <span id="surveyProgressCount">{{ $isSubmitted ? '100% completed' : '0% completed' }}</span>
                    </div>
                    <div class="survey-progress-track" aria-hidden="true">
                        <div class="survey-progress-bar" id="surveyProgressBar" style="width: {{ $isSubmitted ? '100%' : '0%' }}"></div>
                    </div>
                </div>
                <div class="survey-body">
            @if($errors->any())
                <div class="survey-error" role="alert">{{ $errors->first() }}</div>
            @endif

            @if($isSubmitted)
                <div class="survey-submitted">Your Online Client Satisfaction Survey has already been submitted. Thank you for helping PUP improve its services.</div>
            @else
                <form method="POST" action="{{ $isPreview ? '#' : route('student.evaluation.store', ['evaluation' => $evaluation->id]) }}" id="clientSatisfactionSurvey" data-initial-step="{{ $initialStep }}" novalidate @if($isPreview) onsubmit="return false;" @endif>
                    @if(!$isPreview)
                        @csrf
                    @endif

                    <section class="survey-step is-active" data-survey-step="0" data-survey-title="Data Privacy Notice">
                        <div class="survey-step-heading">
                            <span class="survey-step-heading-icon" aria-hidden="true"><x-outline-icon name="document-check" /></span>
                            <div>
                                <h2>Data Privacy Notice</h2>
                                <p>Please read and confirm before continuing with the survey.</p>
                            </div>
                        </div>
                        <div class="survey-privacy-rows">
                            <div class="survey-privacy-row">
                                <span class="survey-privacy-row-icon" aria-hidden="true"><x-outline-icon name="users" /></span>
                                <div>
                                    <strong>We respect your rights</strong>
                                    <p>We respect and value your rights as a data subject under the Data Privacy Act (DPA). PUP is committed to protecting the personal data you provide in accordance with the requirements under the DPA and its IRR.</p>
                                </div>
                            </div>
                            <div class="survey-privacy-row">
                                <span class="survey-privacy-row-icon" aria-hidden="true"><x-outline-icon name="shield-check" /></span>
                                <div>
                                    <strong>Our commitment</strong>
                                    <p>In this regard, PUP implements reasonable and appropriate security measures to maintain the confidentiality, integrity, and availability of your personal data.</p>
                                </div>
                            </div>
                            <div class="survey-privacy-row">
                                <span class="survey-privacy-row-icon" aria-hidden="true"><x-outline-icon name="document-text" /></span>
                                <div>
                                    <strong>Learn more</strong>
                                    <p>For more detailed Privacy Statement, you may visit <a href="https://www.pup.edu.ph/privacy/" target="_blank" rel="noopener noreferrer">PUP Privacy Statement <x-outline-icon name="link" /></a>.</p>
                                </div>
                            </div>
                        </div>
                        <div class="survey-consent-box">
                            <span class="survey-consent-box-icon" aria-hidden="true"><x-outline-icon name="shield-check" /></span>
                            <span>
                                <strong>Consent and agreement</strong>
                                <span class="survey-consent-box-text">I understand and agree that by filling out this form, I am allowing this institution to collect, process, use, share, and disclose my personal information and also to store it as long as necessary for the fulfillment of the stated purpose and in accordance with applicable laws including the Data Privacy Act of 2012 and its Implementing Rules and Regulations. The purpose and extent of collection use, sharing, disclosure and storage of my personal information was explained to me.</span>
                            </span>
                            <input type="hidden" name="consent" value="1">
                        </div>
                        <div class="survey-actions survey-actions-consent">
                            <button type="button" class="survey-button primary survey-consent-continue" data-survey-next><span>Continue</span> <x-outline-icon name="arrow-long-right" /></button>
                        </div>
                    </section>

                    <section class="survey-step" data-survey-step="1" data-survey-title="I. Profile of Client">
                        <div class="survey-step-heading">
                            <h2>I. Profile of Client</h2>
                            <p>Please provide the following information about yourself.</p>
                        </div>

                        <div class="survey-profile-grid">
                        <fieldset class="survey-fieldset">
                            <legend>A. Type of Client <span class="survey-required">*</span></legend>
                            <div class="survey-option-grid">
                                @foreach($clientTypes as $clientType)
                                    <label class="survey-option">
                                        <input type="radio" name="client_type" value="{{ $clientType }}" {{ old('client_type') === $clientType ? 'checked' : '' }} required>
                                        <span class="survey-option-icon" aria-hidden="true"><x-outline-icon name="{{ $clientOptionIcons[$clientType] ?? 'user-circle' }}" /></span>
                                        <span>{{ $clientType }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>

                        <fieldset class="survey-fieldset">
                            <legend>B. Sex <span class="survey-required">*</span></legend>
                            <div class="survey-option-grid">
                                @foreach($sexOptions as $sex)
                                    <label class="survey-option">
                                        <input type="radio" name="sex" value="{{ $sex }}" {{ old('sex') === $sex ? 'checked' : '' }} required>
                                        <span class="survey-option-icon" aria-hidden="true"><x-outline-icon name="{{ $sexOptionIcons[$sex] ?? 'user-circle' }}" /></span>
                                        <span>{{ $sex }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>

                        <fieldset class="survey-fieldset">
                            <legend>C. Age <span class="survey-required">*</span></legend>
                            <div class="survey-option-grid">
                                @foreach($ageGroups as $ageGroup)
                                    <label class="survey-option">
                                        <input type="radio" name="age_group" value="{{ $ageGroup }}" {{ old('age_group') === $ageGroup ? 'checked' : '' }} required>
                                        <span class="survey-option-icon" aria-hidden="true"><x-outline-icon name="{{ $ageOptionIcons[$ageGroup] ?? 'user-circle' }}" /></span>
                                        <span>{{ $ageGroup }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>

                        </div>

                        <div class="survey-actions">
                            <button type="button" class="survey-button secondary" data-survey-prev><span>Previous</span></button>
                            <button type="button" class="survey-button primary" data-survey-next><span>Next</span> <x-outline-icon name="arrow-long-right" /></button>
                        </div>
                    </section>

                    <section class="survey-step" data-survey-step="2" data-survey-title="II. Citizen's Charter">
                        <div class="survey-step-heading">
                            <h2>II. Citizen's Charter (CC)</h2>
                            <p>Piliin ang iyong sagot sa mga sumusunod na katanungan tungkol sa Citizen's Charter (CC).</p>
                        </div>

                        <div class="survey-cc-list">
                            @foreach($ccQuestions as $name => [$question, $choices])
                                <fieldset class="survey-cc-question survey-fieldset" style="margin-top:0;">
                                    <p>{{ strtoupper($name) }}. {{ $question }} <span class="survey-required">*</span></p>
                                    <div class="survey-cc-options">
                                        @foreach($choices as $value => $choice)
                                            <label class="survey-option">
                                                <input type="radio" name="{{ $name }}" value="{{ $value }}" {{ old($name) === (string) $value ? 'checked' : '' }} required>
                                                <span>{{ $value }}. {{ $choice }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </fieldset>
                            @endforeach
                        </div>

                        <div class="survey-actions">
                            <button type="button" class="survey-button secondary" data-survey-prev><span>Back</span></button>
                            <button type="button" class="survey-button primary" data-survey-next><span>Continue</span></button>
                        </div>
                    </section>

                    <section class="survey-step" data-survey-step="3" data-survey-title="III. Service Quality Dimensions">
                        <div class="survey-step-heading">
                            <h2>III. Service Quality Dimensions (SQD)</h2>
                            <p>Paki-rate ang serbisyong ibinigay sa iyo ng Opisina batay sa sumusunod:</p>
                        </div>

                        <div class="survey-scale-note">
                            <table class="survey-scale-table">
                                <thead>
                                    <tr><th>Rating</th><th>Description</th></tr>
                                </thead>
                                <tbody>
                                    @foreach($sqdScale as $rating => $description)
                                        <tr><td><strong>{{ $rating }}</strong></td><td>{{ $description }}</td></tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="survey-sqd-list">
                            @foreach($sqdQuestions as $question)
                                @php
                                    $selectedSqdAnswer = old('sqd_answers.' . $question['code'], data_get($evaluation->sqd_answers, $question['code']));
                                @endphp
                                <fieldset class="survey-sqd-question survey-fieldset" style="margin-top:0;">
                                    <legend class="survey-sqd-title">{{ $question['code'] }}. {{ $question['title'] }} <span class="survey-required">*</span></legend>
                                    <p class="survey-sqd-text">{{ $question['text'] }}</p>
                                    <div class="survey-sqd-options" role="radiogroup" aria-label="{{ $question['code'] }} rating">
                                        @foreach($sqdScale as $rating => $description)
                                            <div class="survey-scale-option">
                                                <input type="radio" id="{{ strtolower($question['code']) }}{{ $rating }}" name="sqd_answers[{{ $question['code'] }}]" value="{{ $rating }}" {{ (string) $selectedSqdAnswer === (string) $rating ? 'checked' : '' }} required>
                                                <label for="{{ strtolower($question['code']) }}{{ $rating }}" title="{{ $description }}">{{ $rating }}</label>
                                            </div>
                                        @endforeach
                                    </div>
                                </fieldset>
                            @endforeach
                        </div>

                        <div class="survey-actions">
                            <button type="button" class="survey-button secondary" data-survey-prev><span>Back</span></button>
                            <button type="button" class="survey-button primary" data-survey-next><span>Continue</span></button>
                        </div>
                    </section>

                    <section class="survey-step" data-survey-step="4" data-survey-title="IV. Suggestions and Recommendations">
                        <div class="survey-step-heading">
                            <h2>IV. Suggestions and Recommendations</h2>
                            <p>Mga suhestiyon kung paano pa mapapabuti pa ang aming mga serbisyo (opsyonal).</p>
                        </div>

                        <label class="survey-suggestion-label" for="surveySuggestions">Suggestions and Recommendations <span style="font-weight:500; color:#667085;">(optional)</span></label>
                        <textarea class="survey-suggestion" id="surveySuggestions" name="suggestions" maxlength="1500" placeholder="Share your suggestions here.">{{ old('suggestions', $evaluation->suggestions ?? $evaluation->feedback) }}</textarea>
                        <div class="survey-suggestion-meta">
                            <span>Long text response</span>
                            <span><b id="suggestionCount">0</b> / 1,500 characters</span>
                        </div>

                        <div class="survey-actions">
                            <button type="button" class="survey-button secondary" data-survey-prev><span>Back</span></button>
                            <button type="{{ $isPreview ? 'button' : 'submit' }}" class="survey-button primary"><span>{{ $isPreview ? 'Submit Preview' : 'Submit' }}</span></button>
                        </div>
                    </section>
                </form>
            @endif
                </div>
        </div>
        </div>
    </article>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('clientSatisfactionSurvey');
        if (!form) return;

        const shell = document.querySelector('.survey-shell');
        const navItems = Array.from(document.querySelectorAll('[data-survey-jump]'));
        const steps = Array.from(form.querySelectorAll('[data-survey-step]'));
        const progressBar = document.getElementById('surveyProgressBar');
        const progressLabel = document.getElementById('surveyProgressLabel');
        const progressCount = document.getElementById('surveyProgressCount');
        const suggestion = document.getElementById('surveySuggestions');
        const suggestionCount = document.getElementById('suggestionCount');
        let currentStep = Math.min(Number(form.dataset.initialStep || 0), steps.length - 1);
        const requiredSections = steps.filter(function (step) {
            return ['1', '2', '3'].includes(step.dataset.surveyStep);
        });

        function getRequiredControls(step) {
            const radioGroups = new Set();
            return Array.from(step.querySelectorAll('[required]')).filter(function (control) {
                if (control.type !== 'radio') return true;
                if (radioGroups.has(control.name)) return false;
                radioGroups.add(control.name);
                return true;
            });
        }

        const requiredResponses = requiredSections.flatMap(getRequiredControls);

        function updateSuggestionCount() {
            if (suggestion && suggestionCount) {
                suggestionCount.textContent = String(suggestion.value.length);
            }
        }

        function findInvalidControl(step) {
            return getRequiredControls(step).find(function (control) {
                return !control.validity.valid;
            });
        }

        function updateProgress() {
            const answered = requiredResponses.filter(function (control) {
                return control.validity.valid;
            }).length;
            const completion = requiredResponses.length ? Math.round((answered / requiredResponses.length) * 100) : 0;

            if (progressLabel) {
                progressLabel.textContent = currentStep === 0
                    ? (steps[0]?.dataset.surveyTitle || 'Data Privacy Notice')
                    : 'Section ' + currentStep + ' of ' + (steps.length - 1);
            }
            if (progressCount) progressCount.textContent = completion + '% completed';
            if (progressBar) progressBar.style.width = completion + '%';
        }

        function showStep(index) {
            currentStep = Math.max(0, Math.min(index, steps.length - 1));
            steps.forEach(function (step, stepIndex) {
                step.classList.toggle('is-active', stepIndex === currentStep);
            });

            shell?.classList.toggle('is-flow-layout', currentStep > 0);
            navItems.forEach(function (item) {
                item.closest('[data-survey-nav-item]')?.classList.toggle('is-active', Number(item.dataset.surveyJump) === currentStep);
            });

            updateProgress();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function validateCurrentStep() {
            const invalid = findInvalidControl(steps[currentStep]);
            if (!invalid) return true;
            invalid.reportValidity();
            return false;
        }

        form.querySelectorAll('[data-survey-next]').forEach(function (button) {
            button.addEventListener('click', function () {
                if (validateCurrentStep()) showStep(currentStep + 1);
            });
        });

        form.querySelectorAll('[data-survey-prev]').forEach(function (button) {
            button.addEventListener('click', function () {
                showStep(currentStep - 1);
            });
        });

        navItems.forEach(function (button) {
            button.addEventListener('click', function () {
                showStep(Number(button.dataset.surveyJump));
            });
        });

        form.addEventListener('submit', function (event) {
            if (form.getAttribute('action') === '#') {
                event.preventDefault();
                return;
            }

            const invalidStep = requiredSections.find(function (step) {
                return findInvalidControl(step);
            });
            if (invalidStep) {
                event.preventDefault();
                showStep(steps.indexOf(invalidStep));
                window.requestAnimationFrame(function () {
                    findInvalidControl(invalidStep)?.reportValidity();
                });
            }
        });

        suggestion?.addEventListener('input', updateSuggestionCount);
        form.addEventListener('input', updateProgress);
        form.addEventListener('change', updateProgress);
        updateSuggestionCount();
        showStep(currentStep);
    });
</script>
@endpush
