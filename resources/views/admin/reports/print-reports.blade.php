@php
    $canRenderPdfImages = empty($isPdf) || extension_loaded('gd');
    $isPdfMode = !empty($isPdf);
    $isMarReport = ($type ?? '') === 'mar';
    $isInventoryReport = ($type ?? '') === 'inventory';
    $isAppointmentReport = ($type ?? '') === 'appointment';
    $isOfficialFormReport = in_array(($type ?? ''), ['inventory', 'mar', 'appointment'], true);
    $pageMargin = $isMarReport ? '150px 18px 88px' : (($isInventoryReport || $isAppointmentReport) ? '24px 18px 88px' : '115px 28px 85px');
    $pupOfficialLogoSrc = $isPdfMode ? public_path('images/pup_logo_print.jpg') : asset('images/pup_logo_print.jpg');
    $bpOfficialLogoSrc = $isPdfMode ? public_path('images/bagong_pilipinas_logo_print.jpg') : asset('images/bagong_pilipinas_logo_print.jpg');
    $officialLogosAvailable = file_exists(public_path('images/pup_logo_print.jpg')) && file_exists(public_path('images/bagong_pilipinas_logo_print.jpg'));
    $footerBgSrc = $isPdfMode ? public_path('images/footer_bg_print.jpg') : asset('images/footer_bg.png');
    $footerBgAvailable = $isPdfMode ? file_exists(public_path('images/footer_bg_print.jpg')) : file_exists(public_path('images/footer_bg.png'));
    $marMonthStart = \Carbon\Carbon::parse(($monthFilter ?? now()->format('Y-m')) . '-01')->startOfMonth();
    $marReportAsOf = $marMonthStart->isCurrentMonth() ? now() : $marMonthStart->copy()->endOfMonth();
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} - {{ $monthFilter }}</title>

    <style>
    /* 1. Print & Base Styles */
    @page {
        margin: {{ $pageMargin }};
    }

    @media print {
        .no-print { display: none !important; }
        body { margin: 0; padding: 0; }
        .page-header,
        .page-footer {
            position: fixed;
            left: 0;
            right: 0;
        }
        .page-header {
            top: -95px;
        }
        .page-footer {
            bottom: -65px;
        }
        .report-shell {
            margin: 0;
        }
    }
    
    body { 
        font-family: 'Arial', sans-serif; 
        color: #000; 
        line-height: 1.2; 
        margin: 40px; 
    }

    .pdf-mode .no-print {
        display: none !important;
    }

    .report-shell {
        position: relative;
    }

    .page-header {
        display: none;
        border-bottom: 2px solid #800000;
        padding: 0 28px 10px;
        background: #fff;
    }

    .page-header-inner {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
    }

    .page-header-left {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .page-header-left img {
        width: 44px;
        height: auto;
    }

    .pdf-logo-fallback {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 44px;
        height: 44px;
        border-radius: 50%;
        border: 2px solid #800000;
        color: #800000;
        font-size: 11px;
        font-weight: 800;
        text-align: center;
        line-height: 1;
        vertical-align: middle;
    }

    .page-header-copy {
        line-height: 1.15;
    }

    .page-header-copy strong {
        display: block;
        font-size: 14px;
        color: #800000;
        letter-spacing: 0.35px;
    }

    .page-header-copy span {
        display: block;
        font-size: 10px;
        color: #374151;
        text-transform: uppercase;
        font-weight: 700;
    }

    .page-header-meta {
        font-size: 10px;
        text-align: right;
        line-height: 1.3;
    }

    /* 2. Header & Logo Layout */
    .header-top {
        width: 100%;
        display: table;
        table-layout: fixed;
        margin-bottom: 18px;
    }

    .pup-logo-section {
        display: table-cell;
        width: 110px;
        vertical-align: middle;
        text-align: left;
    }

    .pup-logo-section img {
        width: 62px;
        height: auto;
        display: inline-block;
    }

    .report-header-center {
        display: table-cell;
        vertical-align: middle;
        text-align: center;
        line-height: 1.15;
    }

    .report-header-center .university {
        display: block;
        font-size: 12px;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #111827;
    }

    .report-header-center .department,
    .report-header-center .campus {
        display: block;
        font-size: 10px;
        font-weight: 700;
        color: #374151;
    }

    .logo-text-box {
        text-align: left;
        line-height: 1.1;
    }

    .logo-text-box .title {
        font-weight: bold;
        font-size: 18px;
        color: #800000;
        letter-spacing: 0.5px;
    }

    .logo-text-box .sub {
        font-size: 11px;
        color: #333;
        text-transform: uppercase;
        font-weight: 600;
    }

    .bp-logo {
        display: table-cell;
        width: 110px;
        vertical-align: middle;
        text-align: right;
    }

    .bp-logo img {
        width: 74px;
        height: auto;
        object-fit: contain;
        display: inline-block;
    }

    /* 3. Report Info & Titles */
    .report-main-title { 
        text-align: center; 
        margin: 25px 0; 
        font-weight: bold; 
        font-size: 18px; 
        text-transform: uppercase;
        text-decoration: underline;
    }
            
    .info-section { 
        display: flex; 
        justify-content: space-between; 
        margin-bottom: 25px; 
        font-size: 13px; 
    }

    .info-left, .info-right { 
        width: 48%; 
    }

    .info-row { 
        margin-bottom: 8px; 
        display: flex; 
        align-items: flex-end;
    }

    .label { 
        font-weight: bold; 
        width: 130px; 
    }

    .value { 
        border-bottom: 1px solid #000; 
        flex-grow: 1; 
        padding-left: 8px; 
        padding-bottom: 2px;
    }

    /* 4. Table Customization */
    table { 
        width: 100%; 
        table-layout: fixed;
        border-collapse: collapse; 
        margin-top: 15px; 
        background: #fff; 
    }

    th, td { 
        border: 1px solid #000; 
        padding: 10px 6px; 
        font-size: 11px; 
        text-align: left; 
        overflow-wrap: anywhere;
        word-break: break-word;
        vertical-align: top;
    }

    th { 
        background-color: #f2f2f2; 
        font-weight: bold; 
        text-transform: uppercase;
        text-align: center;
    }

    .text-left { text-align: left; }
    .mar-report-table col:first-child {
        width: 38%;
    }

    .mar-report-table col.metric-col {
        width: 12.4%;
    }

    .bg-category { 
        background-color: #f9f9f9; 
        font-weight: bold; 
        text-align: left; 
        padding-left: 10px;
    }

    .inventory-group-row td {
        background-color: #f9f9f9;
        font-weight: bold;
        text-transform: uppercase;
    }

    .inventory-group-label {
        color: #800000;
        letter-spacing: 0.04em;
        text-align: left;
    }

    .inventory-group-spacer {
        background-color: #f9f9f9;
    }

    /* 5. Signatures & Footer */
    .footer-signatures { 
        margin-top: 50px; 
        display: flex; 
        justify-content: space-between; 
    }

    .sig-box { 
        width: 250px; 
        text-align: center; 
    }

    .sig-line { 
        border-top: 1px solid #000; 
        margin-top: 45px; 
        font-weight: bold; 
        padding-top: 5px; 
        text-transform: uppercase; 
        font-size: 12px; 
    }

    .official-footer { 
        border-top: 2px solid #800000; 
        margin-top: 60px; 
        padding-top: 12px; 
        font-size: 10px; 
        color: #333; 
    }

    .footer-details p { 
        margin: 2px 0; 
    }

    .footer-motto { 
        text-align: center; 
        font-weight: bold; 
        font-size: 15px; 
        margin-top: 15px; 
        text-transform: uppercase;
        color: #000;
    }

    .generated-report-caption {
        border-top: 1px solid #9ca3af;
        margin-top: 14px;
        padding-top: 7px;
        font-size: 10px;
        line-height: 1.35;
    }

    .generated-report-caption .signature-note {
        text-align: right;
        color: #4b5563;
        font-weight: 700;
        margin-bottom: 5px;
    }

    .generated-report-caption .privacy-note {
        text-align: center;
        color: #9f4b5a;
        font-weight: 800;
    }

    .page-footer {
        display: none;
        padding: 7px 28px 0;
        background: #fff;
        border-top: 2px solid #800000;
        font-size: 9px;
        color: #374151;
    }

    .page-footer-inner {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
    }

    .page-footer-copy {
        line-height: 1.3;
    }

    .page-footer-motto {
        font-weight: 800;
        text-transform: uppercase;
        text-align: right;
        white-space: nowrap;
    }

    .page-footer-generated {
        border-top: 1px solid #9ca3af;
        margin-top: 6px;
        padding-top: 4px;
        font-size: 8.5px;
        line-height: 1.25;
    }

    .page-footer-generated .signature-note {
        text-align: right;
        color: #4b5563;
        font-weight: 700;
    }

    .page-footer-generated .privacy-note {
        text-align: center;
        color: #9f4b5a;
        font-weight: 800;
    }

    @media print {
        .page-header,
        .page-footer {
            display: block;
        }
    }

    /* 6. UI Components (No-Print) */
    .no-print-bar { 
        background: #1e293b; 
        color: white; 
        padding: 15px 25px; 
        display: flex; 
        justify-content: space-between; 
        align-items: center;
        border-radius: 8px; 
        margin-bottom: 25px; 
    }

    .btn-print { 
        background: #ef4444; 
        color: white; 
        border: none; 
        padding: 10px 20px; 
        cursor: pointer; 
        border-radius: 6px; 
        font-weight: bold; 
        transition: background 0.3s;
    }

    .btn-print:hover {
        background: #dc2626;
    }

    .pdf-warning {
        margin: 0 0 14px;
        padding: 12px 14px;
        border-radius: 10px;
        background: #fff4d6;
        border: 1px solid #facc15;
        color: #7c2d12;
        font-size: 13px;
        line-height: 1.45;
    }

    .official-form-report .page-header,
    .official-form-report .page-footer {
        display: none !important;
    }

    body.pdf-mode.official-form-report .footer-signatures,
    body.pdf-mode.official-form-report .official-footer,
    body.pdf-mode.official-form-report .page-footer,
    body.pdf-mode.official-form-report .page-header {
        display: none !important;
    }

    body.pdf-mode.official-form-report {
        margin: 0;
    }

    .official-inventory-report {
        font-family: Arial, Helvetica, sans-serif;
        color: #000;
    }

    .official-inventory-page-footer {
        position: fixed;
        right: 0;
        bottom: -58px;
        left: 0;
        border-top: 1.5px solid #000;
        padding-top: 5px;
        font-family: Arial, Helvetica, sans-serif;
        color: #000;
        text-align: center;
    }

    .official-inventory-footer-contact {
        margin: 0;
        font-size: 7px;
        line-height: 1.3;
    }

    .official-inventory-footer-motto {
        margin: 4px 0 0;
        font-size: 7px;
        font-weight: 700;
        letter-spacing: .35px;
        text-transform: uppercase;
    }

    .official-inventory-header {
        position: relative;
        min-height: 118px;
        padding: 4px 92px 12px 76px;
        border-bottom: 1.5px solid #000;
        text-align: center;
    }

    .official-inventory-logo {
        position: absolute;
        top: 2px;
        left: 4px;
        width: 58px;
        height: 58px;
        object-fit: contain;
    }

    .official-inventory-government-logo {
        position: absolute;
        top: 8px;
        right: 0;
        width: 86px;
        height: 58px;
        object-fit: contain;
    }

    .official-inventory-university {
        margin: 0;
        font-family: "Times New Roman", serif;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0;
        text-transform: uppercase;
    }

    .official-inventory-office {
        margin: 2px 0 0;
        font-family: "Times New Roman", serif;
        font-size: 12px;
        font-weight: 700;
    }

    .official-inventory-campus {
        margin: 1px 0 8px;
        font-size: 11px;
        font-weight: 700;
    }

    .official-inventory-header-title {
        margin: 0;
        font-size: 13px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .official-inventory-header-date {
        margin: 3px 0 0;
        font-size: 10px;
        font-weight: 700;
    }

    .official-inventory-form-code {
        position: absolute;
        right: 0;
        bottom: -13px;
        font-size: 6.5px;
        white-space: nowrap;
    }

    .official-inventory-meta {
        width: 100%;
        margin: 24px 0 10px;
        border-collapse: collapse;
        table-layout: fixed;
    }

    .official-inventory-meta td {
        border: 0;
        padding: 2px 4px;
        font-size: 9px;
        vertical-align: bottom;
    }

    .official-inventory-meta .meta-label {
        display: inline-block;
        min-width: 55px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .official-inventory-meta .meta-value {
        display: inline-block;
        min-width: 150px;
        border-bottom: 1px solid #000;
        padding: 0 4px 1px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .official-inventory-table {
        width: 100%;
        margin: 0;
        border-collapse: collapse;
        table-layout: fixed;
    }

    .official-inventory-table thead {
        display: table-header-group;
    }

    .mar-report-table thead {
        display: table-header-group;
    }

    .mar-report-table tr {
        page-break-inside: avoid;
    }

    .official-inventory-table tr {
        page-break-inside: avoid;
    }

    .official-inventory-table th,
    .official-inventory-table td {
        border: 1px solid #000;
        padding: 3px 3px;
        font-size: 7.5px;
        line-height: 1.2;
        vertical-align: middle;
    }

    .official-inventory-table th {
        height: 31px;
        background: #fff;
        color: #000;
        font-size: 7px;
        font-weight: 700;
        text-align: center;
    }

    .official-inventory-table .inventory-category-row td {
        height: 17px;
        background: #fff;
        font-size: 8px;
        font-weight: 700;
        text-align: center;
        text-transform: uppercase;
    }

    .official-inventory-table .number-cell,
    .official-inventory-table .date-cell,
    .official-inventory-table .unit-cell {
        text-align: center;
    }

    .official-inventory-table .item-cell {
        text-align: left;
    }

    .official-inventory-signatures {
        width: 100%;
        margin-top: 25px;
        border-collapse: collapse;
        table-layout: fixed;
    }

    .official-inventory-signatures td {
        width: 50%;
        border: 0;
        padding: 0 28px;
        font-size: 8px;
        vertical-align: top;
    }

    .official-inventory-signature-space {
        height: 30px;
    }

    .official-inventory-signature-name {
        border-bottom: 1px solid #000;
        padding-bottom: 2px;
        font-size: 9px;
        font-weight: 700;
        text-align: center;
        text-transform: uppercase;
    }

    .official-inventory-signature-role {
        margin-top: 2px;
        text-align: center;
    }

    .mar-service-table tbody td:last-child:not(.bg-category) {
        color: transparent;
    }

    body.mar-form-report {
        font-family: Arial, Helvetica, sans-serif;
        color: #000;
    }

    body.mar-form-report .official-inventory-header {
        min-height: 92px;
        padding: 2px 112px 8px 78px;
        border-bottom: 0;
    }

    body.mar-form-report .official-inventory-logo {
        top: 2px;
        left: 8px;
        width: 52px;
        height: 52px;
    }

    body.mar-form-report .official-inventory-government-logo {
        top: 6px;
        right: 42px;
        width: 72px;
        height: 48px;
    }

    body.mar-form-report .official-inventory-university {
        font-size: 8px;
        line-height: 1.05;
    }

    body.mar-form-report .official-inventory-office {
        margin-top: 1px;
        font-size: 8px;
        line-height: 1.05;
    }

    body.mar-form-report .official-inventory-campus {
        margin: 0 0 12px;
        font-size: 7px;
        line-height: 1.05;
    }

    body.mar-form-report .official-inventory-header-title {
        font-size: 9px;
        line-height: 1.1;
        letter-spacing: .02em;
    }

    body.mar-form-report .official-inventory-header-date {
        display: none;
    }

    body.mar-form-report .official-inventory-form-code {
        top: 2px;
        right: 0;
        bottom: auto;
        width: 70px;
        min-height: 22px;
        border: 1px solid #000;
        padding: 3px 4px;
        font-size: 4.8px;
        line-height: 1.15;
        white-space: normal;
        text-align: center;
    }

    body.mar-form-report .official-inventory-meta {
        margin: 8px 0 8px;
    }

    body.mar-form-report .official-inventory-meta td {
        padding: 1px 3px;
        font-size: 6.6px;
        line-height: 1.1;
    }

    body.mar-form-report .official-inventory-meta .meta-label {
        min-width: 64px;
        font-size: 6.2px;
    }

    body.mar-form-report .official-inventory-meta .meta-value {
        min-width: 132px;
        padding-bottom: 0;
        font-size: 6.4px;
    }

    body.mar-form-report .mar-report-table {
        margin-top: 6px;
        border-collapse: collapse;
    }

    body.mar-form-report .mar-report-table th,
    body.mar-form-report .mar-report-table td {
        border: 1px solid #000;
        padding: 2px 3px;
        font-size: 6.6px;
        line-height: 1.1;
        vertical-align: middle;
    }

    body.mar-form-report .mar-report-table th {
        height: 18px;
        background: #fff;
        color: #000;
        font-size: 6.4px;
        font-weight: 700;
        text-align: center;
    }

    body.mar-form-report .bg-category,
    body.mar-form-report tr.bg-category td {
        background: #fff;
        font-weight: 700;
        text-transform: uppercase;
    }

    body.mar-form-report .mar-report-table + .mar-report-table {
        margin-top: 18px !important;
    }

    .mar-footer-strip {
        width: 100%;
        margin-top: 5px;
        border-collapse: collapse;
        table-layout: fixed;
    }

    .mar-footer-strip td {
        border: 0;
        padding: 0 3px;
        vertical-align: top;
        font-size: 4.8px;
        line-height: 1.1;
        text-align: center;
    }

    .mar-footer-badge {
        display: inline-block;
        width: 52px;
        min-height: 17px;
        border: 1px solid #c8a400;
        padding: 2px 3px;
        color: #6b4f00;
        font-size: 5px;
        font-weight: 700;
        line-height: 1.05;
        text-transform: uppercase;
    }

    body.mar-form-report .official-inventory-footer-contact {
        margin: 0;
        font-size: 5px;
        line-height: 1.16;
        text-align: left;
    }

    body.mar-form-report .official-inventory-footer-motto {
        margin: 5px 0 0;
        font-family: "Times New Roman", serif;
        font-size: 8px;
        font-weight: 700;
        text-align: left;
        text-transform: uppercase;
    }

    /* Inventory PDF reference sizing for MAR: same official header, footer, and content scale. */
    body.mar-form-report .official-inventory-header {
        min-height: 118px !important;
        padding: 4px 92px 12px 76px !important;
        border-bottom: 1.5px solid #000 !important;
    }

    body.mar-form-report .official-inventory-logo {
        top: 2px !important;
        left: 4px !important;
        width: 58px !important;
        height: 58px !important;
    }

    body.mar-form-report .official-inventory-government-logo {
        top: 8px !important;
        right: 0 !important;
        width: 86px !important;
        height: 58px !important;
    }

    body.mar-form-report .official-inventory-university,
    body.mar-form-report .official-inventory-office {
        font-family: "Times New Roman", serif !important;
        font-size: 12px !important;
        line-height: normal !important;
    }

    body.mar-form-report .official-inventory-office {
        margin: 2px 0 0 !important;
    }

    body.mar-form-report .official-inventory-campus {
        margin: 1px 0 8px !important;
        font-size: 11px !important;
        line-height: normal !important;
    }

    body.mar-form-report .official-inventory-header-title {
        font-size: 13px !important;
        line-height: normal !important;
    }

    body.mar-form-report .official-inventory-header-date {
        display: block !important;
        margin: 3px 0 0 !important;
        font-size: 10px !important;
        line-height: normal !important;
    }

    body.mar-form-report .official-inventory-form-code {
        top: auto !important;
        right: 0 !important;
        bottom: -13px !important;
        width: auto !important;
        min-height: 0 !important;
        border: 0 !important;
        padding: 0 !important;
        font-size: 6.5px !important;
        line-height: normal !important;
        white-space: nowrap !important;
        text-align: left !important;
    }

    body.mar-form-report .official-inventory-meta {
        margin: 24px 0 10px !important;
    }

    body.mar-form-report .official-inventory-meta td {
        padding: 2px 4px !important;
        font-size: 9px !important;
        line-height: normal !important;
    }

    body.mar-form-report .official-inventory-meta .meta-label {
        min-width: 55px !important;
        font-size: 9px !important;
    }

    body.mar-form-report .official-inventory-meta .meta-value {
        min-width: 150px !important;
        padding: 0 4px 1px !important;
        font-size: 9px !important;
    }

    body.mar-form-report .mar-report-table {
        margin-top: 15px !important;
    }

    body.mar-form-report .mar-report-table th,
    body.mar-form-report .mar-report-table td {
        padding: 3px 3px !important;
        font-size: 7.5px !important;
        line-height: 1.2 !important;
        vertical-align: middle !important;
    }

    body.mar-form-report .mar-report-table th {
        height: 31px !important;
        font-size: 7px !important;
    }

    body.mar-form-report .mar-report-table + .mar-report-table {
        margin-top: 25px !important;
    }

    body.mar-form-report .official-inventory-footer-contact {
        margin: 0 !important;
        font-size: 7px !important;
        line-height: 1.3 !important;
        text-align: center !important;
    }

    body.mar-form-report .official-inventory-footer-motto {
        margin: 4px 0 0 !important;
        font-family: Arial, Helvetica, sans-serif !important;
        font-size: 7px !important;
        letter-spacing: .35px !important;
        text-align: center !important;
    }

    .mar-footer-layout {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }

    .mar-footer-layout td {
        border: 0;
        padding: 0;
        vertical-align: top;
    }

    .mar-footer-text {
        width: 34%;
        text-align: left;
    }

    body.mar-form-report .mar-footer-text .official-inventory-footer-contact,
    body.mar-form-report .mar-footer-text .official-inventory-footer-motto {
        text-align: left !important;
    }

    .mar-footer-image-cell {
        width: 66%;
        text-align: right;
    }

    .mar-footer-image {
        display: inline-block;
        width: 350px;
        max-height: 44px;
        object-fit: contain;
    }

    /* Larger MAR rendering to match the supplied inventory PDF viewer scale. */
    body.mar-form-report .official-inventory-header {
        min-height: 126px !important;
        padding: 6px 104px 14px 82px !important;
    }

    body.mar-form-report .official-inventory-logo {
        width: 66px !important;
        height: 66px !important;
    }

    body.mar-form-report .official-inventory-government-logo {
        top: 9px !important;
        width: 110px !important;
        height: 66px !important;
    }

    body.mar-form-report .official-inventory-university {
        font-size: 14px !important;
    }

    body.mar-form-report .official-inventory-office {
        font-size: 14px !important;
    }

    body.mar-form-report .official-inventory-campus {
        font-size: 12px !important;
        margin-bottom: 10px !important;
    }

    body.mar-form-report .official-inventory-header-title {
        font-size: 15px !important;
    }

    body.mar-form-report .official-inventory-header-date {
        font-size: 11px !important;
    }

    body.mar-form-report .official-inventory-form-code {
        font-size: 7px !important;
    }

    body.mar-form-report .official-inventory-meta {
        margin: 8px 0 10px !important;
    }

    body.mar-form-report .official-inventory-meta td {
        font-size: 10px !important;
        padding: 2px 4px !important;
    }

    body.mar-form-report .official-inventory-meta .meta-label,
    body.mar-form-report .official-inventory-meta .meta-value {
        font-size: 10px !important;
    }

    body.mar-form-report .official-inventory-meta .meta-label {
        min-width: 64px !important;
    }

    body.mar-form-report .official-inventory-meta .meta-value {
        min-width: 170px !important;
    }

    body.mar-form-report .mar-report-table th,
    body.mar-form-report .mar-report-table td {
        padding: 4px 5px !important;
        font-size: 10px !important;
        line-height: 1.24 !important;
    }

    body.mar-form-report .mar-report-table th {
        height: 36px !important;
        font-size: 9px !important;
    }

    body.mar-form-report .bg-category,
    body.mar-form-report tr.bg-category td {
        font-size: 10px !important;
    }

    body.mar-form-report .official-inventory-footer-contact {
        font-size: 8px !important;
        line-height: 1.25 !important;
    }

    body.mar-form-report .official-inventory-footer-motto {
        font-size: 8px !important;
    }

    .mar-footer-image {
        width: 400px;
        max-height: 50px;
    }

    /* Bigger MAR pass requested after PDF comparison. */
    body.mar-form-report .official-inventory-header {
        min-height: 96px !important;
        padding: 4px 118px 6px 96px !important;
    }

    body.mar-form-report .official-inventory-logo {
        width: 82px !important;
        height: 82px !important;
    }

    body.mar-form-report .official-inventory-government-logo {
        top: 10px !important;
        width: 116px !important;
        height: 82px !important;
    }

    body.mar-form-report .official-inventory-university,
    body.mar-form-report .official-inventory-office {
        font-size: 16px !important;
    }

    body.mar-form-report .official-inventory-campus {
        font-size: 14px !important;
        margin-bottom: 12px !important;
    }

    body.mar-form-report .official-inventory-header-title {
        font-size: 17px !important;
    }

    body.mar-form-report .official-inventory-header-date {
        font-size: 13px !important;
    }

    body.mar-form-report .official-inventory-form-code {
        top: 20px !important;
        right: -22px !important;
        bottom: auto !important;
        width: 108px !important;
        min-height: 42px !important;
        border: 1px solid #000 !important;
        padding: 6px 7px 5px !important;
        font-size: 8.2px !important;
        line-height: 1.35 !important;
        white-space: nowrap !important;
        text-align: left !important;
        font-weight: 400 !important;
        background: #fff !important;
    }

    body.mar-form-report .official-inventory-meta {
        margin: 18px 0 10px !important;
    }

    body.mar-form-report .official-inventory-meta td,
    body.mar-form-report .official-inventory-meta .meta-label,
    body.mar-form-report .official-inventory-meta .meta-value {
        font-size: 12px !important;
    }

    body.mar-form-report .official-inventory-meta .meta-label {
        min-width: 74px !important;
    }

    body.mar-form-report .official-inventory-meta .meta-value {
        min-width: 190px !important;
    }

    body.mar-form-report .mar-report-table th,
    body.mar-form-report .mar-report-table td {
        padding: 5px 6px !important;
        font-size: 12px !important;
        line-height: 1.28 !important;
    }

    body.mar-form-report .mar-report-table th {
        height: 42px !important;
        font-size: 11px !important;
    }

    body.mar-form-report .bg-category,
    body.mar-form-report tr.bg-category td {
        font-size: 12px !important;
    }

    body.mar-form-report .official-inventory-footer-contact {
        font-size: 9px !important;
        line-height: 1.25 !important;
    }

    body.mar-form-report .official-inventory-footer-motto {
        font-size: 9px !important;
    }

    .mar-footer-text {
        width: 40%;
        padding-left: 24px !important;
    }

    .mar-footer-image-cell {
        width: 60%;
        text-align: left;
    }

    .mar-footer-image {
        width: 390px;
        max-height: 54px;
        object-fit: contain;
    }

    body.mar-form-report .mar-footer-text .official-inventory-footer-contact {
        font-size: 10px !important;
        line-height: 1.28 !important;
    }

    body.mar-form-report .mar-footer-text .official-inventory-footer-motto {
        font-family: "Times New Roman", Times, serif !important;
        font-size: 12px !important;
        line-height: 1.08 !important;
        letter-spacing: .35px !important;
        font-weight: 700 !important;
    }

    body.mar-form-report .mar-footer-image {
        width: 410px !important;
        max-height: 60px !important;
    }

    /* Final MAR header alignment pass. */
    body.mar-form-report .official-inventory-header {
        text-align: left !important;
        padding-left: 116px !important;
        padding-right: 120px !important;
        border-bottom: 0 !important;
    }

    body.mar-form-report .official-inventory-logo {
        left: 28px !important;
        width: 97.5px !important;
        height: 97.5px !important;
    }

    body.mar-form-report .official-inventory-government-logo {
        right: 126px !important;
        width: 110px !important;
        height: 96px !important;
        object-fit: contain !important;
    }

    body.mar-form-report .official-inventory-university,
    body.mar-form-report .official-inventory-office,
    body.mar-form-report .official-inventory-campus,
    body.mar-form-report .official-inventory-header-title,
    body.mar-form-report .official-inventory-header-date {
        text-align: left !important;
    }

    body.mar-form-report .official-inventory-university {
        font-weight: 400 !important;
        line-height: 1.05 !important;
        margin-top: 16px !important;
        margin-bottom: 0 !important;
        width: 430px !important;
        text-align: left !important;
        margin-left: 34px !important;
    }

    body.mar-form-report .official-inventory-office {
        line-height: 1.05 !important;
        margin-top: 0 !important;
        width: 430px !important;
        text-align: center !important;
        font-style: italic !important;
        font-weight: 700 !important;
    }

    body.mar-form-report .official-inventory-campus {
        line-height: 1.05 !important;
        margin-top: 0 !important;
        width: 430px !important;
        text-align: center !important;
        font-style: normal !important;
        font-weight: 400 !important;
    }

    body.mar-form-report .official-inventory-header-title,
    body.mar-form-report .official-inventory-header-date {
        width: 430px !important;
        text-align: center !important;
        font-style: italic !important;
        font-weight: 700 !important;
    }

    body.mar-form-report .official-inventory-header-title {
        margin-top: 18px !important;
        font-size: 14px !important;
    }

    body.mar-form-report .official-inventory-header-date {
        margin-top: 2px !important;
    }

    body.mar-form-report .official-inventory-header .official-inventory-header-title,
    body.mar-form-report .official-inventory-header .official-inventory-header-date {
        display: none !important;
    }

    .mar-page-title {
        margin: 14px 0 8px;
        text-align: center;
    }

    .mar-page-title h1 {
        margin: 0;
        font-size: 14px;
        font-weight: 700;
        font-style: italic;
        line-height: 1.12;
        text-transform: uppercase;
    }

    .mar-page-title p {
        margin: 2px 0 0;
        font-size: 12px;
        font-weight: 700;
        font-style: italic;
        line-height: 1.12;
    }

    .mar-closing-block {
        margin: 18px 0 6px;
        font-family: Arial, Helvetica, sans-serif;
        color: #000;
        page-break-inside: avoid;
    }

    .mar-closing-signatures {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }

    .mar-closing-signatures td {
        border: 0;
        width: 50%;
        padding: 3px 36px 7px;
        font-size: 11px;
        text-align: center;
    }

    .mar-closing-signatures .mar-closing-label {
        padding-bottom: 24px;
        font-size: 11px;
        font-weight: 400;
    }

    .mar-closing-name {
        border-top: 1px solid #000;
        padding-top: 4px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .mar-closing-role {
        margin-top: 2px;
        font-size: 9px;
        font-weight: 400;
        text-transform: none;
    }

    .mar-footer-note {
        margin: 3px 0 0;
        font-size: 7.5px;
        line-height: 1.15;
        text-align: center;
        color: #7f1d1d;
        white-space: nowrap;
    }

    .mar-footer-note strong {
        display: inline;
        margin-bottom: 0;
        color: inherit;
        font-weight: 400;
    }

    body.mar-form-report .official-inventory-page-footer,
    body.mar-form-report .mar-pdf-footer {
        border-top: 0 !important;
    }

    body.official-inventory-template {
        font-family: Arial, Helvetica, sans-serif;
        color: #000;
    }

    body.official-inventory-template .official-inventory-header {
        min-height: 96px !important;
        padding: 4px 120px 6px 116px !important;
        border-bottom: 0 !important;
        text-align: left !important;
    }

    body.official-inventory-template .official-inventory-logo {
        left: 28px !important;
        width: 97.5px !important;
        height: 97.5px !important;
    }

    body.official-inventory-template .official-inventory-government-logo {
        right: 126px !important;
        width: 110px !important;
        height: 96px !important;
        object-fit: contain !important;
    }

    body.official-inventory-template .official-inventory-university {
        width: 430px !important;
        margin: 16px 0 0 34px !important;
        font-size: 16px !important;
        font-weight: 400 !important;
        line-height: 1.05 !important;
        text-align: left !important;
    }

    body.official-inventory-template .official-inventory-office {
        width: 430px !important;
        margin: 0 !important;
        font-size: 16px !important;
        font-style: italic !important;
        font-weight: 700 !important;
        line-height: 1.05 !important;
        text-align: center !important;
    }

    body.official-inventory-template .official-inventory-campus {
        width: 430px !important;
        margin: 0 !important;
        font-size: 14px !important;
        font-style: normal !important;
        font-weight: 400 !important;
        line-height: 1.05 !important;
        text-align: center !important;
    }

    body.official-inventory-template .official-inventory-header-title,
    body.official-inventory-template .official-inventory-header-date {
        width: 430px !important;
        text-align: center !important;
        font-style: italic !important;
        font-weight: 700 !important;
    }

    body.official-inventory-template .official-inventory-header-title {
        margin-top: 14px !important;
        font-size: 14px !important;
    }

    body.official-inventory-template .official-inventory-header-date {
        margin-top: 2px !important;
        font-size: 12px !important;
    }

    body.official-inventory-template .official-inventory-form-code {
        top: 20px !important;
        right: -22px !important;
        bottom: auto !important;
        width: 108px !important;
        min-height: 42px !important;
        border: 1px solid #000 !important;
        padding: 6px 7px 5px !important;
        background: #fff !important;
        font-size: 8.2px !important;
        font-weight: 400 !important;
        line-height: 1.35 !important;
        text-align: left !important;
        white-space: normal !important;
    }

    body.official-inventory-template .official-inventory-meta {
        margin: 18px 0 10px !important;
    }

    body.official-inventory-template .official-inventory-meta td,
    body.official-inventory-template .official-inventory-meta .meta-label,
    body.official-inventory-template .official-inventory-meta .meta-value {
        font-size: 12px !important;
    }

    body.official-inventory-template .official-inventory-table th,
    body.official-inventory-template .official-inventory-table td {
        padding: 5px 6px !important;
        font-size: 12px !important;
        line-height: 1.28 !important;
        vertical-align: middle !important;
    }

    body.official-inventory-template .official-inventory-table th {
        height: 42px !important;
        font-size: 11px !important;
    }

    body.official-inventory-template .inventory-category-row td {
        font-size: 12px !important;
        padding-left: 22% !important;
        text-align: left !important;
    }

    body.official-inventory-template .official-inventory-signatures td,
    body.official-inventory-template .official-inventory-signature-name,
    body.official-inventory-template .official-inventory-signature-role {
        font-size: 12px !important;
        line-height: 1.2 !important;
    }

    body.official-inventory-template .official-inventory-page-footer {
        bottom: -74px !important;
        border-top: 0 !important;
        padding-top: 0 !important;
    }

    body.official-inventory-template .mar-footer-layout {
        width: 100%;
    }

    body.official-inventory-template .mar-footer-text {
        width: 40%;
        padding-left: 24px !important;
        text-align: left !important;
    }

    body.official-inventory-template .mar-footer-image-cell {
        width: 60%;
        text-align: left !important;
    }

    body.official-inventory-template .mar-footer-image {
        width: 410px !important;
        max-height: 60px !important;
        object-fit: contain !important;
    }

    body.official-inventory-template .mar-footer-text .official-inventory-footer-contact {
        margin: 0 !important;
        font-size: 10px !important;
        line-height: 1.28 !important;
        text-align: left !important;
    }

    body.official-inventory-template .mar-footer-text .official-inventory-footer-motto {
        margin: 4px 0 0 !important;
        font-family: "Times New Roman", Times, serif !important;
        font-size: 12px !important;
        font-weight: 700 !important;
        line-height: 1.08 !important;
        letter-spacing: .35px !important;
        text-align: left !important;
    }

    .mar-pdf-header,
    .mar-pdf-footer {
        display: none;
    }

    body.pdf-mode.mar-form-report .mar-pdf-header {
        display: block;
        position: fixed;
        top: -122px;
        right: 0;
        left: 0;
        height: 96px;
        background: #fff;
        z-index: 10;
    }

    body.pdf-mode.mar-form-report .mar-pdf-footer {
        display: block;
        position: fixed;
        right: 0;
        bottom: -74px;
        left: 0;
        border-top: 0;
        padding-top: 0;
        font-family: Arial, Helvetica, sans-serif;
        color: #000;
        text-align: center;
        z-index: 10;
    }

    body.pdf-mode.mar-form-report .official-inventory-report > .official-inventory-header,
    body.pdf-mode.mar-form-report .official-inventory-report > .official-inventory-page-footer,
    body.pdf-mode.mar-form-report .report-shell > .official-inventory-page-footer {
        display: none !important;
    }

    body.pdf-mode.mar-form-report .official-inventory-report {
        margin: 0;
    }

    body.pdf-mode.mar-form-report .official-inventory-meta {
        margin-top: 0;
    }

    @media print {
        body.official-form-report {
            margin: 0;
        }

        .official-inventory-report {
            margin: 0;
        }

        body.mar-form-report .mar-pdf-header {
            display: block;
            position: fixed;
            top: -122px;
            right: 0;
            left: 0;
            height: 96px;
            background: #fff;
        }

        body.mar-form-report .mar-pdf-footer {
            display: block;
            position: fixed;
            right: 0;
            bottom: -74px;
            left: 0;
        }
    }
</style>
</head>
<body class="{{ $isPdfMode ? 'pdf-mode' : '' }} {{ $isOfficialFormReport ? 'official-form-report' : '' }} {{ $isMarReport ? 'mar-form-report' : '' }} {{ ($isInventoryReport || $isAppointmentReport) ? 'official-inventory-template' : '' }}">

    <div class="page-header" aria-hidden="true">
        <div class="page-header-inner">
            <div class="page-header-left">
                @if($canRenderPdfImages)
                    <img src="{{ asset('images/pup_logo.png') }}" alt="PUP Logo">
                @else
                    <span class="pdf-logo-fallback">PUP</span>
                @endif
                <div class="page-header-copy">
                    <strong>PUP TAGUIG</strong>
                    <span>{{ strtoupper($title) }}</span>
                </div>
            </div>
            <div class="page-header-meta">
                <div>Medical Services Unit</div>
                <div>{{ date('F d, Y') }}</div>
            </div>
        </div>
    </div>

    <div class="page-footer" aria-hidden="true">
        <div class="page-footer-inner">
            <div class="page-footer-copy">
                General Santos Avenue, Lower Bicutan, Taguig City, Philippines 1632
                <br>Direct Line (02) 8837 5658 to 60 | Website: www.pup.edu.ph | Email: taguig@pup.edu.ph
            </div>
            <div class="page-footer-motto">The Country's First PolytechnicU</div>
        </div>
        <div class="page-footer-generated">
            <div class="signature-note">This is system-generated, signature is not required.</div>
            <div class="privacy-note">
                This document contains personal-identifiable information that is subject to Data Privacy.<br>
                Please keep this document protected and in a safe place.
            </div>
        </div>
    </div>

    @if($isMarReport)
        <header class="mar-pdf-header official-inventory-header">
            @if($officialLogosAvailable)
                <img class="official-inventory-logo" src="{{ $pupOfficialLogoSrc }}" alt="PUP Logo">
                <img class="official-inventory-government-logo" src="{{ $bpOfficialLogoSrc }}" alt="Bagong Pilipinas Logo">
            @else
                <span class="official-inventory-logo pdf-logo-fallback">PUP</span>
                <span class="official-inventory-government-logo pdf-logo-fallback">BP</span>
            @endif
            <p class="official-inventory-university">Polytechnic University of the Philippines</p>
            <p class="official-inventory-office">Medical Services Department</p>
            <p class="official-inventory-campus">Taguig Campus</p>
            <span class="official-inventory-form-code">PUP-MIOS-6-MEDS-031<br>July 11, 2024<br>Revision: 0</span>
        </header>

        <footer class="mar-pdf-footer">
            <table class="mar-footer-layout">
                <tr>
                    <td class="mar-footer-text">
                        <p class="official-inventory-footer-contact">
                            Gen. Santos Avenue, Lower Bicutan, Taguig City 1632<br>
                            Direct Line: (02) 8837 5858 to 60 | Email: taguig@pup.edu.ph<br>
                            Website: www.pup.edu.ph | Inquiries: https://bit.ly/PUPSINTA
                        </p>
                        <p class="official-inventory-footer-motto">A Leading Comprehensive<br>Polytechnic University in Asia</p>
                    </td>
                    <td class="mar-footer-image-cell">
                        @if($footerBgAvailable)
                            <img class="mar-footer-image" src="{{ $footerBgSrc }}" alt="PUP accreditation marks">
                        @endif
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <p class="mar-footer-note">
                            <strong>This is system-generated, signature is not required.</strong>
                            This document contains personal-identifiable information that is subject to Data Privacy. Please keep this document protected and in a safe place.
                        </p>
                    </td>
                </tr>
            </table>
        </footer>
    @endif

    <div class="report-shell">
    <div class="no-print no-print-bar">
        <span><strong>Preview Mode:</strong> {{ $title }}</span>
        <div>
            <button onclick="window.print()" class="btn-print">Print / Save as PDF</button>
            <button onclick="window.close()" style="padding: 8px 15px; cursor: pointer;">Close</button>
        </div>
    </div>

    @if(!empty($pdfUnavailable))
        <div class="no-print pdf-warning">
            PDF export is not available on this server yet, so this report opened in the HTML preview instead.
        </div>
    @endif

    @if(!in_array($type, ['inventory', 'mar', 'appointment'], true))
        <div class="header-top">
            <div class="pup-logo-section">
                @if($canRenderPdfImages)
                    <img src="{{ asset('images/pup_logo.png') }}" alt="PUP Logo">
                @else
                    <span class="pdf-logo-fallback">PUP</span>
                @endif
            </div>
            <div class="report-header-center">
                <span class="university">POLYTECHNIC UNIVERSITY OF THE PHILIPPINES</span>
                <span class="department">Medical Services Department</span>
                <span class="campus">Taguig Campus</span>
            </div>
            <div class="bp-logo">
                @if($canRenderPdfImages)
                    <img src="{{ asset('images/Bagong_Pilipinas_logo.png') }}" alt="Bagong Pilipinas Logo">
                @else
                    <span class="pdf-logo-fallback">BP</span>
                @endif
            </div>
        </div>

        <div class="report-main-title">
        @if($type == 'mar')
            MEDICAL ACCOMPLISHMENT REPORT
        @elseif($type == 'appointment')
            APPOINTMENT REPORT
        @else
            ACCOMPLISHMENT REPORT as of {{ date('F d, Y') }}
        @endif
        </div>

        <div class="info-section">
            <div class="info-left">
                <div class="info-row"><span class="label">Name:</span> <span class="value">Nurse Joyce</span></div>
                <div class="info-row"><span class="label">Position:</span> <span class="value">Nurse</span></div>
            </div>
            <div class="info-right">
                <div class="info-row"><span class="label">Date of Submission:</span> <span class="value">{{ date('m/d/Y') }}</span></div>
                <div class="info-row"><span class="label">Unit/Department:</span> <span class="value">Medical Services Unit</span></div>
            </div>
        </div>
    @endif

    @if($type == 'mar')
        @php
            $consultationGad = $gadTables['consultation'] ?? [];
            $certificateGad = $gadTables['certificate'] ?? [];
            $triageOnlineGad = $gadTables['triage_online'] ?? [];
            $combinedGad = $gadTables['combined'] ?? [];
            $freshmenClearanceGad = $gadTables['freshmen_clearance'] ?? [];
            $marPreparedBy = auth('admin')->user() ?? auth()->user();
            $marPreparedByName = trim((string) optional($marPreparedBy)->name) ?: 'CLINIC STAFF';
            $marPreparedByPosition = \App\Models\User::normalizeRole(optional($marPreparedBy)->user_role) === \App\Models\User::ROLE_ADMIN
                ? 'Nurse / Clinic Staff'
                : 'Clinic Staff';
        @endphp

        @unless($isPdfMode)
            <footer class="official-inventory-page-footer">
                <table class="mar-footer-layout">
                    <tr>
                        <td class="mar-footer-text">
                            <p class="official-inventory-footer-contact">
                                Gen. Santos Avenue, Lower Bicutan, Taguig City 1632<br>
                                Direct Line: (02) 8837 5858 to 60 | Email: taguig@pup.edu.ph<br>
                                Website: www.pup.edu.ph | Inquiries: https://bit.ly/PUPSINTA
                            </p>
                            <p class="official-inventory-footer-motto">A Leading Comprehensive<br>Polytechnic University in Asia</p>
                        </td>
                        <td class="mar-footer-image-cell">
                            @if($footerBgAvailable)
                                <img class="mar-footer-image" src="{{ $footerBgSrc }}" alt="PUP accreditation marks">
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <p class="mar-footer-note">
                                <strong>This is system-generated, signature is not required.</strong>
                                This document contains personal-identifiable information that is subject to Data Privacy. Please keep this document protected and in a safe place.
                            </p>
                        </td>
                    </tr>
                </table>
            </footer>
        @endunless

        <section class="official-inventory-report">
            <header class="official-inventory-header">
                @if($officialLogosAvailable)
                    <img class="official-inventory-logo" src="{{ $pupOfficialLogoSrc }}" alt="PUP Logo">
                    <img class="official-inventory-government-logo" src="{{ $bpOfficialLogoSrc }}" alt="Bagong Pilipinas Logo">
                @else
                    <span class="official-inventory-logo pdf-logo-fallback">PUP</span>
                    <span class="official-inventory-government-logo pdf-logo-fallback">BP</span>
                @endif
                <p class="official-inventory-university">Polytechnic University of the Philippines</p>
                <p class="official-inventory-office">Medical Services Department</p>
                <p class="official-inventory-campus">Taguig Campus</p>
                <span class="official-inventory-form-code">PUP-MIOS-6-MEDS-031<br>July 11, 2024<br>Revision: 0</span>
            </header>

            <div class="mar-page-title">
                <h1>Accomplishment Report</h1>
                <p>As of {{ $marReportAsOf->format('F d, Y') }}</p>
            </div>

            <table class="official-inventory-meta">
                <tr>
                    <td>
                        <span class="meta-label">Name:</span>
                        <span class="meta-value">{{ $marPreparedByName }}</span>
                    </td>
                    <td>
                        <span class="meta-label">Date of Submission:</span>
                        <span class="meta-value">{{ $marReportAsOf->format('F d, Y') }}</span>
                    </td>
                </tr>
                <tr>
                    <td>
                        <span class="meta-label">Position:</span>
                        <span class="meta-value">{{ $marPreparedByPosition }}</span>
                    </td>
                    <td>
                        <span class="meta-label">Unit / Department:</span>
                        <span class="meta-value">Taguig Campus</span>
                    </td>
                </tr>
            </table>

        <table class="mar-report-table mar-service-table">
            <colgroup>
                <col>
                <col class="metric-col">
                <col class="metric-col">
                <col class="metric-col">
                <col class="metric-col">
                <col class="metric-col">
            </colgroup>
            <thead>
                <tr>
                    <th>MEDICAL SERVICE RENDERED</th>
                    <th>STUDENTS</th>
                    <th>FACULTY</th>
                    <th>ADMIN</th>
                    <th>DEPENDENTS</th>
                    <th>REMARKS</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $resolveConsultationPatientType = function ($consultation) {
                        $value = strtolower(trim((string) ($consultation->user_role ?: $consultation->user_type ?: '')));

                        return match ($value) {
                            'student' => 'student',
                            'faculty' => 'faculty',
                            'admin', 'staff' => 'admin',
                            'dependent', 'dependents' => 'dependent',
                            default => null,
                        };
                    };

                    $countByPatientType = function ($consultations, string $type) use ($resolveConsultationPatientType) {
                        return $consultations->filter(function ($consultation) use ($resolveConsultationPatientType, $type) {
                            return $resolveConsultationPatientType($consultation) === $type;
                        })->count();
                    };

                    $countCertificateByType = function ($consultations, string $certificateType, string $patientType) use ($countByPatientType) {
                        $filtered = $consultations->filter(function ($consultation) use ($certificateType) {
                            return trim((string) ($consultation->certificate_type ?? 'none')) === $certificateType;
                        });

                        return $countByPatientType($filtered, $patientType);
                    };

                    $roman = function (int $value) {
                        $map = [
                            10 => 'X',
                            9 => 'IX',
                            8 => 'VIII',
                            7 => 'VII',
                            6 => 'VI',
                            5 => 'V',
                            4 => 'IV',
                            3 => 'III',
                            2 => 'II',
                            1 => 'I',
                        ];

                        return $map[$value] ?? (string) $value;
                    };

                    $consultationTotals = ['student' => 0, 'faculty' => 0, 'admin' => 0, 'dependent' => 0];
                    $excusedLetterTotals = ['student' => 0, 'faculty' => 0, 'admin' => 0, 'dependent' => 0];
                    $cocIjtTotals = ['student' => 0, 'faculty' => 0, 'admin' => 0, 'dependent' => 0];
                    $cocLadderizedTotals = ['student' => 0, 'faculty' => 0, 'admin' => 0, 'dependent' => 0];
                    $excusedLetterCategoryRows = collect();
                    $onlineTotals = [
                        'consultation' => ['student' => 0, 'faculty' => 0, 'admin' => 0, 'dependent' => 0],
                        'medical_clearance' => ['student' => 0, 'faculty' => 0, 'admin' => 0, 'dependent' => 0],
                        'others' => ['student' => 0, 'faculty' => 0, 'admin' => 0, 'dependent' => 0],
                    ];
                @endphp
                <tr>
                    <td colspan="6" class="bg-category">{{ $roman(1) }}. CONSULTATION / TREATMENT</td>
                </tr>
                @foreach($data as $catIndex => $cat)
                    @php
                        $categoryConsultations = $cat->medicalConditions->flatMap->consultations;
                        $categoryExcused = [
                            'student' => $countCertificateByType($categoryConsultations, 'excused_letter', 'student'),
                            'faculty' => $countCertificateByType($categoryConsultations, 'excused_letter', 'faculty'),
                            'admin' => $countCertificateByType($categoryConsultations, 'excused_letter', 'admin'),
                            'dependent' => $countCertificateByType($categoryConsultations, 'excused_letter', 'dependent'),
                        ];

                        if (array_sum($categoryExcused) > 0) {
                            $excusedLetterCategoryRows->push([
                                'label' => chr(65 + $catIndex) . '. ' . $cat->name,
                                'student' => $categoryExcused['student'],
                                'faculty' => $categoryExcused['faculty'],
                                'admin' => $categoryExcused['admin'],
                                'dependent' => $categoryExcused['dependent'],
                                'total' => array_sum($categoryExcused),
                            ]);
                        }

                        foreach (['student', 'faculty', 'admin', 'dependent'] as $type) {
                            $excusedLetterTotals[$type] += $categoryExcused[$type];
                            $cocIjtTotals[$type] += $countCertificateByType($categoryConsultations, 'coc_ijt', $type);
                            $cocLadderizedTotals[$type] += $countCertificateByType($categoryConsultations, 'coc_ladderized', $type);
                        }

                        $onlineConsultations = $categoryConsultations->filter(function ($consultation) {
                            return trim((string) ($consultation->consultation_source ?? '')) === 'online';
                        });

                        $onlineBuckets = [
                            'consultation' => $onlineConsultations->filter(function ($consultation) {
                                $service = strtolower(trim((string) ($consultation->service ?? '')));
                                return in_array($service, ['general consultation', 'consultation'], true);
                            }),
                            'medical_clearance' => $onlineConsultations->filter(function ($consultation) {
                                $service = strtolower(trim((string) ($consultation->service ?? '')));
                                return str_contains($service, 'clearance');
                            }),
                        ];
                        $onlineBuckets['others'] = $onlineConsultations->reject(function ($consultation) {
                            $service = strtolower(trim((string) ($consultation->service ?? '')));
                            return in_array($service, ['general consultation', 'consultation'], true) || str_contains($service, 'clearance');
                        });

                        foreach ($onlineBuckets as $bucket => $consultations) {
                            foreach (['student', 'faculty', 'admin', 'dependent'] as $type) {
                                $onlineTotals[$bucket][$type] += $countByPatientType($consultations, $type);
                            }
                        }
                    @endphp
                    <tr class="bg-category">
                        <td colspan="6">{{ chr(65 + $catIndex) }}. {{ $cat->name }}</td>
                    </tr>
                    @foreach($cat->medicalConditions as $conditionIndex => $condition)
                        @php
                            $stu = $countByPatientType($condition->consultations, 'student');
                            $fac = $countByPatientType($condition->consultations, 'faculty');
                            $sta = $countByPatientType($condition->consultations, 'admin');
                            $dep = $countByPatientType($condition->consultations, 'dependent');
                            $rowTotal = $stu + $fac + $sta +$dep;
                            $consultationTotals['student'] += $stu;
                            $consultationTotals['faculty'] += $fac;
                            $consultationTotals['admin'] += $sta;
                            $consultationTotals['dependent'] += $dep;
                        @endphp
                        <tr>
                            <td class="text-left" style="padding-left: 15px;">{{ $conditionIndex + 1 }}. {{ $condition->name }}</td>
                            <td>{{ $stu ?: '' }}</td>
                            <td>{{ $fac ?: '' }}</td>
                            <td>{{ $sta ?: '' }}</td>
                            <td>{{ $dep ?: '' }}</td>
                            <td></td>
                        </tr>
                    @endforeach
                @endforeach
                <tr class="bg-category">
                    <td>Total Consultation</td>
                    <td>{{ $consultationTotals['student'] }}</td>
                    <td>{{ $consultationTotals['faculty'] }}</td>
                    <td>{{ $consultationTotals['admin'] }}</td>
                    <td>{{ $consultationTotals['dependent'] }}</td>
                    <td></td>
                </tr>
                <tr class="bg-category"><td colspan="6">{{ $roman(2) }}. MEDICAL CERTIFICATE / CLEARANCE - CERTIFICATE OF COMPLIANCE</td></tr>
                <tr><td class="text-left" style="padding-left: 15px;">A. Excused Letter</td><td>{{ $excusedLetterTotals['student'] }}</td><td>{{ $excusedLetterTotals['faculty'] }}</td><td>{{ $excusedLetterTotals['admin'] }}</td><td>{{ $excusedLetterTotals['dependent'] }}</td><td></td></tr>
                @forelse($excusedLetterCategoryRows as $categoryRow)
                    <tr><td class="text-left" style="padding-left: 30px;">{{ $categoryRow['label'] }}</td><td>{{ $categoryRow['student'] }}</td><td>{{ $categoryRow['faculty'] }}</td><td>{{ $categoryRow['admin'] }}</td><td>{{ $categoryRow['dependent'] }}</td><td>{{ $categoryRow['total'] }}</td></tr>
                @empty
                    <tr><td class="text-left" style="padding-left: 30px;">No excused letter category recorded yet.</td><td>0</td><td>0</td><td>0</td><td>0</td><td>0</td></tr>
                @endforelse
                <tr><td class="text-left" style="padding-left: 15px;">B. COC for IJT</td><td>{{ $cocIjtTotals['student'] }}</td><td>{{ $cocIjtTotals['faculty'] }}</td><td>{{ $cocIjtTotals['admin'] }}</td><td>{{ $cocIjtTotals['dependent'] }}</td><td></td></tr>
                <tr><td class="text-left" style="padding-left: 15px;">C. COC for Ladderized</td><td>{{ $cocLadderizedTotals['student'] }}</td><td>{{ $cocLadderizedTotals['faculty'] }}</td><td>{{ $cocLadderizedTotals['admin'] }}</td><td>{{ $cocLadderizedTotals['dependent'] }}</td><td></td></tr>
                <tr class="bg-category"><td colspan="6">{{ $roman(3) }}. INJECTIONS</td></tr>
                <tr><td class="text-left" style="padding-left: 15px;">A. Injection Services</td><td>0</td><td>0</td><td>0</td><td>0</td><td>0</td></tr>
                <tr class="bg-category"><td colspan="6">{{ $roman(4) }}. REFERRALS</td></tr>
                <tr><td class="text-left" style="padding-left: 15px;">A. Ref. to Hospital without nurse</td><td>0</td><td>0</td><td>0</td><td>0</td><td>0</td></tr>
                <tr><td class="text-left" style="padding-left: 15px;">B. Ref. to Hospital with nurse</td><td>0</td><td>0</td><td>0</td><td>0</td><td>0</td></tr>
                <tr><td class="text-left" style="padding-left: 15px;">C. Referral</td><td>0</td><td>0</td><td>0</td><td>0</td><td>0</td></tr>
                <tr class="bg-category"><td colspan="6">{{ $roman(5) }}. OTHERS</td></tr>
                <tr><td class="text-left" style="padding-left: 15px;">A. Other Services</td><td>0</td><td>0</td><td>0</td><td>0</td><td>0</td></tr>
                <tr class="bg-category"><td colspan="6">{{ $roman(6) }}. ON-LINE CONSULTATION</td></tr>
                <tr><td class="text-left" style="padding-left: 15px;">A. Consultation</td><td>{{ $onlineTotals['consultation']['student'] }}</td><td>{{ $onlineTotals['consultation']['faculty'] }}</td><td>{{ $onlineTotals['consultation']['admin'] }}</td><td>{{ $onlineTotals['consultation']['dependent'] }}</td><td></td></tr>
                <tr><td class="text-left" style="padding-left: 15px;">B. Medical Clearance</td><td>{{ $onlineTotals['medical_clearance']['student'] }}</td><td>{{ $onlineTotals['medical_clearance']['faculty'] }}</td><td>{{ $onlineTotals['medical_clearance']['admin'] }}</td><td>{{ $onlineTotals['medical_clearance']['dependent'] }}</td><td></td></tr>
                <tr><td class="text-left" style="padding-left: 15px;">C. Others</td><td>{{ $onlineTotals['others']['student'] }}</td><td>{{ $onlineTotals['others']['faculty'] }}</td><td>{{ $onlineTotals['others']['admin'] }}</td><td>{{ $onlineTotals['others']['dependent'] }}</td><td></td></tr>
                <tr class="bg-category"><td colspan="6">{{ $roman(7) }}. TRIAGE SURVEY</td></tr>
                <tr><td class="text-left" style="padding-left: 15px;">A. Online</td><td>0</td><td>0</td><td>0</td><td>0</td><td>0</td></tr>
                <tr class="bg-category"><td colspan="6">{{ $roman(8) }}. BULLETIN UPDATES</td></tr>
                <tr><td class="text-left" style="padding-left: 15px;">A. Bulletin Updates</td><td>0</td><td>0</td><td>0</td><td>0</td><td>0</td></tr>
            </tbody>
        </table>

        <table class="mar-report-table" style="margin-top: 28px;">
            <colgroup>
                <col>
                <col class="metric-col">
                <col class="metric-col">
                <col class="metric-col">
                <col class="metric-col">
                <col class="metric-col">
            </colgroup>
            <thead>
                <tr>
                    <th>GAD (CONSULTATION)</th>
                    <th>STUDENTS</th>
                    <th>FACULTY</th>
                    <th>ADMIN</th>
                    <th>DEPENDENT</th>
                    <th>TOTAL</th>
                </tr>
            </thead>
            <tbody>
                <tr class="bg-category"><td colspan="6">GAD SUMMARY</td></tr>
                <tr><td class="text-left">Female</td><td>{{ $consultationGad['female']['student'] ?? 0 }}</td><td>{{ $consultationGad['female']['faculty'] ?? 0 }}</td><td>{{ $consultationGad['female']['admin'] ?? 0 }}</td><td>{{ $consultationGad['female']['dependent'] ?? 0 }}</td><td>{{ $consultationGad['female']['total'] ?? 0 }}</td></tr>
                <tr><td class="text-left">Male</td><td>{{ $consultationGad['male']['student'] ?? 0 }}</td><td>{{ $consultationGad['male']['faculty'] ?? 0 }}</td><td>{{ $consultationGad['male']['admin'] ?? 0 }}</td><td>{{ $consultationGad['male']['dependent'] ?? 0 }}</td><td>{{ $consultationGad['male']['total'] ?? 0 }}</td></tr>
                <tr class="bg-category"><td colspan="6">PWD</td></tr>
                <tr><td class="text-left" style="padding-left: 15px;">Male</td><td>{{ $consultationGad['pwd_male']['student'] ?? 0 }}</td><td>{{ $consultationGad['pwd_male']['faculty'] ?? 0 }}</td><td>{{ $consultationGad['pwd_male']['admin'] ?? 0 }}</td><td>{{ $consultationGad['pwd_male']['dependent'] ?? 0 }}</td><td>{{ $consultationGad['pwd_male']['total'] ?? 0 }}</td></tr>
                <tr><td class="text-left" style="padding-left: 15px;">Female</td><td>{{ $consultationGad['pwd_female']['student'] ?? 0 }}</td><td>{{ $consultationGad['pwd_female']['faculty'] ?? 0 }}</td><td>{{ $consultationGad['pwd_female']['admin'] ?? 0 }}</td><td>{{ $consultationGad['pwd_female']['dependent'] ?? 0 }}</td><td>{{ $consultationGad['pwd_female']['total'] ?? 0 }}</td></tr>
                <tr class="bg-category"><td colspan="6">Senior</td></tr>
                <tr><td class="text-left" style="padding-left: 15px;">Male</td><td>{{ $consultationGad['senior_male']['student'] ?? 0 }}</td><td>{{ $consultationGad['senior_male']['faculty'] ?? 0 }}</td><td>{{ $consultationGad['senior_male']['admin'] ?? 0 }}</td><td>{{ $consultationGad['senior_male']['dependent'] ?? 0 }}</td><td>{{ $consultationGad['senior_male']['total'] ?? 0 }}</td></tr>
                <tr><td class="text-left" style="padding-left: 15px;">Female</td><td>{{ $consultationGad['senior_female']['student'] ?? 0 }}</td><td>{{ $consultationGad['senior_female']['faculty'] ?? 0 }}</td><td>{{ $consultationGad['senior_female']['admin'] ?? 0 }}</td><td>{{ $consultationGad['senior_female']['dependent'] ?? 0 }}</td><td>{{ $consultationGad['senior_female']['total'] ?? 0 }}</td></tr>
                <tr class="bg-category"><td>Total</td><td>{{ $consultationGad['total']['student'] ?? 0 }}</td><td>{{ $consultationGad['total']['faculty'] ?? 0 }}</td><td>{{ $consultationGad['total']['admin'] ?? 0 }}</td><td>{{ $consultationGad['total']['dependent'] ?? 0 }}</td><td>{{ $consultationGad['total']['total'] ?? 0 }}</td></tr>
            </tbody>
        </table>

        <table class="mar-report-table" style="margin-top: 28px;">
            <colgroup>
                <col>
                <col class="metric-col">
                <col class="metric-col">
                <col class="metric-col">
                <col class="metric-col">
                <col class="metric-col">
            </colgroup>
            <thead>
                <tr>
                    <th>GAD (OJT/MEDICAL COMPLIANCE/MEDICAL FOR PROMOTION/EXCUSED LETTER)</th>
                    <th>STUDENTS</th>
                    <th>FACULTY</th>
                    <th>ADMIN</th>
                    <th>DEPENDENT</th>
                    <th>TOTAL</th>
                </tr>
            </thead>
            <tbody>
                <tr class="bg-category"><td colspan="6">GAD SUMMARY</td></tr>
                <tr><td class="text-left">Female</td><td>{{ $certificateGad['female']['student'] ?? 0 }}</td><td>{{ $certificateGad['female']['faculty'] ?? 0 }}</td><td>{{ $certificateGad['female']['admin'] ?? 0 }}</td><td>{{ $certificateGad['female']['dependent'] ?? 0 }}</td><td>{{ $certificateGad['female']['total'] ?? 0 }}</td></tr>
                <tr><td class="text-left">Male</td><td>{{ $certificateGad['male']['student'] ?? 0 }}</td><td>{{ $certificateGad['male']['faculty'] ?? 0 }}</td><td>{{ $certificateGad['male']['admin'] ?? 0 }}</td><td>{{ $certificateGad['male']['dependent'] ?? 0 }}</td><td>{{ $certificateGad['male']['total'] ?? 0 }}</td></tr>
                <tr class="bg-category"><td colspan="6">PWD</td></tr>
                <tr><td class="text-left" style="padding-left: 15px;">Male</td><td>{{ $certificateGad['pwd_male']['student'] ?? 0 }}</td><td>{{ $certificateGad['pwd_male']['faculty'] ?? 0 }}</td><td>{{ $certificateGad['pwd_male']['admin'] ?? 0 }}</td><td>{{ $certificateGad['pwd_male']['dependent'] ?? 0 }}</td><td>{{ $certificateGad['pwd_male']['total'] ?? 0 }}</td></tr>
                <tr><td class="text-left" style="padding-left: 15px;">Female</td><td>{{ $certificateGad['pwd_female']['student'] ?? 0 }}</td><td>{{ $certificateGad['pwd_female']['faculty'] ?? 0 }}</td><td>{{ $certificateGad['pwd_female']['admin'] ?? 0 }}</td><td>{{ $certificateGad['pwd_female']['dependent'] ?? 0 }}</td><td>{{ $certificateGad['pwd_female']['total'] ?? 0 }}</td></tr>
                <tr class="bg-category"><td colspan="6">Senior</td></tr>
                <tr><td class="text-left" style="padding-left: 15px;">Male</td><td>{{ $certificateGad['senior_male']['student'] ?? 0 }}</td><td>{{ $certificateGad['senior_male']['faculty'] ?? 0 }}</td><td>{{ $certificateGad['senior_male']['admin'] ?? 0 }}</td><td>{{ $certificateGad['senior_male']['dependent'] ?? 0 }}</td><td>{{ $certificateGad['senior_male']['total'] ?? 0 }}</td></tr>
                <tr><td class="text-left" style="padding-left: 15px;">Female</td><td>{{ $certificateGad['senior_female']['student'] ?? 0 }}</td><td>{{ $certificateGad['senior_female']['faculty'] ?? 0 }}</td><td>{{ $certificateGad['senior_female']['admin'] ?? 0 }}</td><td>{{ $certificateGad['senior_female']['dependent'] ?? 0 }}</td><td>{{ $certificateGad['senior_female']['total'] ?? 0 }}</td></tr>
                <tr class="bg-category"><td>Total</td><td>{{ $certificateGad['total']['student'] ?? 0 }}</td><td>{{ $certificateGad['total']['faculty'] ?? 0 }}</td><td>{{ $certificateGad['total']['admin'] ?? 0 }}</td><td>{{ $certificateGad['total']['dependent'] ?? 0 }}</td><td>{{ $certificateGad['total']['total'] ?? 0 }}</td></tr>
            </tbody>
        </table>

        <table class="mar-report-table" style="margin-top: 28px;">
            <colgroup>
                <col>
                <col class="metric-col">
                <col class="metric-col">
                <col class="metric-col">
                <col class="metric-col">
                <col class="metric-col">
            </colgroup>
            <thead>
                <tr>
                    <th>GAD (TRIAGE ONLINE)</th>
                    <th>STUDENTS</th>
                    <th>FACULTY</th>
                    <th>ADMIN</th>
                    <th>DEPENDENT</th>
                    <th>TOTAL</th>
                </tr>
            </thead>
            <tbody>
                <tr class="bg-category"><td colspan="6">GAD SUMMARY</td></tr>
                <tr><td class="text-left">Female</td><td>{{ $triageOnlineGad['female']['student'] ?? 0 }}</td><td>{{ $triageOnlineGad['female']['faculty'] ?? 0 }}</td><td>{{ $triageOnlineGad['female']['admin'] ?? 0 }}</td><td>{{ $triageOnlineGad['female']['dependent'] ?? 0 }}</td><td>{{ $triageOnlineGad['female']['total'] ?? 0 }}</td></tr>
                <tr><td class="text-left">Male</td><td>{{ $triageOnlineGad['male']['student'] ?? 0 }}</td><td>{{ $triageOnlineGad['male']['faculty'] ?? 0 }}</td><td>{{ $triageOnlineGad['male']['admin'] ?? 0 }}</td><td>{{ $triageOnlineGad['male']['dependent'] ?? 0 }}</td><td>{{ $triageOnlineGad['male']['total'] ?? 0 }}</td></tr>
                <tr class="bg-category"><td colspan="6">PWD</td></tr>
                <tr><td class="text-left" style="padding-left: 15px;">Male</td><td>{{ $triageOnlineGad['pwd_male']['student'] ?? 0 }}</td><td>{{ $triageOnlineGad['pwd_male']['faculty'] ?? 0 }}</td><td>{{ $triageOnlineGad['pwd_male']['admin'] ?? 0 }}</td><td>{{ $triageOnlineGad['pwd_male']['dependent'] ?? 0 }}</td><td>{{ $triageOnlineGad['pwd_male']['total'] ?? 0 }}</td></tr>
                <tr><td class="text-left" style="padding-left: 15px;">Female</td><td>{{ $triageOnlineGad['pwd_female']['student'] ?? 0 }}</td><td>{{ $triageOnlineGad['pwd_female']['faculty'] ?? 0 }}</td><td>{{ $triageOnlineGad['pwd_female']['admin'] ?? 0 }}</td><td>{{ $triageOnlineGad['pwd_female']['dependent'] ?? 0 }}</td><td>{{ $triageOnlineGad['pwd_female']['total'] ?? 0 }}</td></tr>
                <tr class="bg-category"><td colspan="6">Senior</td></tr>
                <tr><td class="text-left" style="padding-left: 15px;">Male</td><td>{{ $triageOnlineGad['senior_male']['student'] ?? 0 }}</td><td>{{ $triageOnlineGad['senior_male']['faculty'] ?? 0 }}</td><td>{{ $triageOnlineGad['senior_male']['admin'] ?? 0 }}</td><td>{{ $triageOnlineGad['senior_male']['dependent'] ?? 0 }}</td><td>{{ $triageOnlineGad['senior_male']['total'] ?? 0 }}</td></tr>
                <tr><td class="text-left" style="padding-left: 15px;">Female</td><td>{{ $triageOnlineGad['senior_female']['student'] ?? 0 }}</td><td>{{ $triageOnlineGad['senior_female']['faculty'] ?? 0 }}</td><td>{{ $triageOnlineGad['senior_female']['admin'] ?? 0 }}</td><td>{{ $triageOnlineGad['senior_female']['dependent'] ?? 0 }}</td><td>{{ $triageOnlineGad['senior_female']['total'] ?? 0 }}</td></tr>
                <tr class="bg-category"><td>Total</td><td>{{ $triageOnlineGad['total']['student'] ?? 0 }}</td><td>{{ $triageOnlineGad['total']['faculty'] ?? 0 }}</td><td>{{ $triageOnlineGad['total']['admin'] ?? 0 }}</td><td>{{ $triageOnlineGad['total']['dependent'] ?? 0 }}</td><td>{{ $triageOnlineGad['total']['total'] ?? 0 }}</td></tr>
            </tbody>
        </table>

        <table class="mar-report-table" style="margin-top: 28px;">
            <colgroup>
                <col>
                <col class="metric-col">
                <col class="metric-col">
                <col class="metric-col">
                <col class="metric-col">
                <col class="metric-col">
            </colgroup>
            <thead>
                <tr>
                    <th>GAD (CONSULTATION + OJT/MEDICAL COMPLIANCE/MEDICAL FOR PROMOTION + TRIAGE)</th>
                    <th>STUDENTS</th>
                    <th>FACULTY</th>
                    <th>ADMIN</th>
                    <th>DEPENDENT</th>
                    <th>TOTAL</th>
                </tr>
            </thead>
            <tbody>
                <tr class="bg-category"><td colspan="6">GAD SUMMARY</td></tr>
                <tr><td class="text-left">Female</td><td>{{ $combinedGad['female']['student'] ?? 0 }}</td><td>{{ $combinedGad['female']['faculty'] ?? 0 }}</td><td>{{ $combinedGad['female']['admin'] ?? 0 }}</td><td>{{ $combinedGad['female']['dependent'] ?? 0 }}</td><td>{{ $combinedGad['female']['total'] ?? 0 }}</td></tr>
                <tr><td class="text-left">Male</td><td>{{ $combinedGad['male']['student'] ?? 0 }}</td><td>{{ $combinedGad['male']['faculty'] ?? 0 }}</td><td>{{ $combinedGad['male']['admin'] ?? 0 }}</td><td>{{ $combinedGad['male']['dependent'] ?? 0 }}</td><td>{{ $combinedGad['male']['total'] ?? 0 }}</td></tr>
                <tr class="bg-category"><td colspan="6">PWD</td></tr>
                <tr><td class="text-left" style="padding-left: 15px;">Male</td><td>{{ $combinedGad['pwd_male']['student'] ?? 0 }}</td><td>{{ $combinedGad['pwd_male']['faculty'] ?? 0 }}</td><td>{{ $combinedGad['pwd_male']['admin'] ?? 0 }}</td><td>{{ $combinedGad['pwd_male']['dependent'] ?? 0 }}</td><td>{{ $combinedGad['pwd_male']['total'] ?? 0 }}</td></tr>
                <tr><td class="text-left" style="padding-left: 15px;">Female</td><td>{{ $combinedGad['pwd_female']['student'] ?? 0 }}</td><td>{{ $combinedGad['pwd_female']['faculty'] ?? 0 }}</td><td>{{ $combinedGad['pwd_female']['admin'] ?? 0 }}</td><td>{{ $combinedGad['pwd_female']['dependent'] ?? 0 }}</td><td>{{ $combinedGad['pwd_female']['total'] ?? 0 }}</td></tr>
                <tr class="bg-category"><td colspan="6">Senior</td></tr>
                <tr><td class="text-left" style="padding-left: 15px;">Male</td><td>{{ $combinedGad['senior_male']['student'] ?? 0 }}</td><td>{{ $combinedGad['senior_male']['faculty'] ?? 0 }}</td><td>{{ $combinedGad['senior_male']['admin'] ?? 0 }}</td><td>{{ $combinedGad['senior_male']['dependent'] ?? 0 }}</td><td>{{ $combinedGad['senior_male']['total'] ?? 0 }}</td></tr>
                <tr><td class="text-left" style="padding-left: 15px;">Female</td><td>{{ $combinedGad['senior_female']['student'] ?? 0 }}</td><td>{{ $combinedGad['senior_female']['faculty'] ?? 0 }}</td><td>{{ $combinedGad['senior_female']['admin'] ?? 0 }}</td><td>{{ $combinedGad['senior_female']['dependent'] ?? 0 }}</td><td>{{ $combinedGad['senior_female']['total'] ?? 0 }}</td></tr>
                <tr class="bg-category"><td>Total</td><td>{{ $combinedGad['total']['student'] ?? 0 }}</td><td>{{ $combinedGad['total']['faculty'] ?? 0 }}</td><td>{{ $combinedGad['total']['admin'] ?? 0 }}</td><td>{{ $combinedGad['total']['dependent'] ?? 0 }}</td><td>{{ $combinedGad['total']['total'] ?? 0 }}</td></tr>
            </tbody>
        </table>

        <table class="mar-report-table" style="margin-top: 28px;">
            <colgroup>
                <col>
                <col class="metric-col">
                <col class="metric-col">
                <col class="metric-col">
                <col class="metric-col">
                <col class="metric-col">
            </colgroup>
            <thead>
                <tr>
                    <th>GAD (MEDICAL CLEARANCE FOR FRESHMEN)</th>
                    <th>STUDENTS</th>
                    <th>FACULTY</th>
                    <th>ADMIN</th>
                    <th>DEPENDENT</th>
                    <th>TOTAL</th>
                </tr>
            </thead>
            <tbody>
                <tr class="bg-category"><td colspan="6">GAD SUMMARY</td></tr>
                <tr><td class="text-left">Female</td><td>{{ $freshmenClearanceGad['female']['student'] ?? 0 }}</td><td>{{ $freshmenClearanceGad['female']['faculty'] ?? 0 }}</td><td>{{ $freshmenClearanceGad['female']['admin'] ?? 0 }}</td><td>{{ $freshmenClearanceGad['female']['dependent'] ?? 0 }}</td><td>{{ $freshmenClearanceGad['female']['total'] ?? 0 }}</td></tr>
                <tr><td class="text-left">Male</td><td>{{ $freshmenClearanceGad['male']['student'] ?? 0 }}</td><td>{{ $freshmenClearanceGad['male']['faculty'] ?? 0 }}</td><td>{{ $freshmenClearanceGad['male']['admin'] ?? 0 }}</td><td>{{ $freshmenClearanceGad['male']['dependent'] ?? 0 }}</td><td>{{ $freshmenClearanceGad['male']['total'] ?? 0 }}</td></tr>
                <tr class="bg-category"><td colspan="6">PWD</td></tr>
                <tr><td class="text-left" style="padding-left: 15px;">Male</td><td>{{ $freshmenClearanceGad['pwd_male']['student'] ?? 0 }}</td><td>{{ $freshmenClearanceGad['pwd_male']['faculty'] ?? 0 }}</td><td>{{ $freshmenClearanceGad['pwd_male']['admin'] ?? 0 }}</td><td>{{ $freshmenClearanceGad['pwd_male']['dependent'] ?? 0 }}</td><td>{{ $freshmenClearanceGad['pwd_male']['total'] ?? 0 }}</td></tr>
                <tr><td class="text-left" style="padding-left: 15px;">Female</td><td>{{ $freshmenClearanceGad['pwd_female']['student'] ?? 0 }}</td><td>{{ $freshmenClearanceGad['pwd_female']['faculty'] ?? 0 }}</td><td>{{ $freshmenClearanceGad['pwd_female']['admin'] ?? 0 }}</td><td>{{ $freshmenClearanceGad['pwd_female']['dependent'] ?? 0 }}</td><td>{{ $freshmenClearanceGad['pwd_female']['total'] ?? 0 }}</td></tr>
                <tr class="bg-category"><td colspan="6">Senior</td></tr>
                <tr><td class="text-left" style="padding-left: 15px;">Male</td><td>{{ $freshmenClearanceGad['senior_male']['student'] ?? 0 }}</td><td>{{ $freshmenClearanceGad['senior_male']['faculty'] ?? 0 }}</td><td>{{ $freshmenClearanceGad['senior_male']['admin'] ?? 0 }}</td><td>{{ $freshmenClearanceGad['senior_male']['dependent'] ?? 0 }}</td><td>{{ $freshmenClearanceGad['senior_male']['total'] ?? 0 }}</td></tr>
                <tr><td class="text-left" style="padding-left: 15px;">Female</td><td>{{ $freshmenClearanceGad['senior_female']['student'] ?? 0 }}</td><td>{{ $freshmenClearanceGad['senior_female']['faculty'] ?? 0 }}</td><td>{{ $freshmenClearanceGad['senior_female']['admin'] ?? 0 }}</td><td>{{ $freshmenClearanceGad['senior_female']['dependent'] ?? 0 }}</td><td>{{ $freshmenClearanceGad['senior_female']['total'] ?? 0 }}</td></tr>
                <tr class="bg-category"><td>Total</td><td>{{ $freshmenClearanceGad['total']['student'] ?? 0 }}</td><td>{{ $freshmenClearanceGad['total']['faculty'] ?? 0 }}</td><td>{{ $freshmenClearanceGad['total']['admin'] ?? 0 }}</td><td>{{ $freshmenClearanceGad['total']['dependent'] ?? 0 }}</td><td>{{ $freshmenClearanceGad['total']['total'] ?? 0 }}</td></tr>
            </tbody>
        </table>

            <div class="mar-closing-block">
                <table class="mar-closing-signatures">
                    <tr>
                        <td class="mar-closing-label">Prepared by:</td>
                        <td class="mar-closing-label">Noted by:</td>
                    </tr>
                    <tr>
                        <td>
                            <div class="mar-closing-name">Nurse / Medical Staff</div>
                        </td>
                        <td>
                            <div class="mar-closing-name">Branch Director</div>
                        </td>
                    </tr>
                </table>
            </div>

        </section>


    @elseif($type == 'inventory')
        @php
            $inventoryScope = $inventoryScope ?? 'all';
            $reportAsOf = $inventoryReportAsOf ?? \Carbon\Carbon::parse(($monthFilter ?? now()->format('Y-m')) . '-01')->endOfMonth();
            $preparedBy = $inventoryPreparedBy ?? null;
            $preparedByName = trim((string) optional($preparedBy)->name) ?: 'CLINIC STAFF';
            $preparedByOffice = trim((string) optional(optional($preparedBy)->adminProfile)->office);
            $preparedByPosition = $preparedByOffice !== ''
                ? $preparedByOffice
                : (\App\Models\User::normalizeRole(optional($preparedBy)->user_role) === \App\Models\User::ROLE_ADMIN ? 'Nurse / Clinic Staff' : 'Clinic Staff');
            $inventoryTitle = $inventoryScope === 'medicines'
                ? 'Inventory of Medicines'
                : 'Inventory of Supplies';
            $inventoryFormCode = 'PUP-MIOS-6-MEDS-031';
            $inventoryGroups = collect($data)
                ->sortBy(function ($item) use ($inventoryScope) {
                    $group = $inventoryScope === 'medicines'
                        ? ($item->medicine_type ?: 'Uncategorized Medicine')
                        : 'Supplies';

                    return strtoupper($group . ' ' . $item->name);
                })
                ->groupBy(function ($item) use ($inventoryScope) {
                    return $inventoryScope === 'medicines'
                        ? ($item->medicine_type ?: 'Uncategorized Medicine')
                        : 'Supplies';
                });
            $formatInventoryQuantity = function ($value) {
                return rtrim(rtrim(number_format((float) $value, 2, '.', ''), '0'), '.');
            };
        @endphp

        <footer class="official-inventory-page-footer">
            <table class="mar-footer-layout">
                <tr>
                    <td class="mar-footer-text">
                        <p class="official-inventory-footer-contact">
                            Gen. Santos Avenue, Lower Bicutan, Taguig City 1632<br>
                            Direct Line: (02) 8837 5858 to 60 | Email: taguig@pup.edu.ph<br>
                            Website: www.pup.edu.ph | Inquiries: https://bit.ly/PUPSINTA
                        </p>
                        <p class="official-inventory-footer-motto">A Leading Comprehensive<br>Polytechnic University in Asia</p>
                    </td>
                    <td class="mar-footer-image-cell">
                        @if($footerBgAvailable)
                            <img class="mar-footer-image" src="{{ $footerBgSrc }}" alt="PUP accreditation marks">
                        @endif
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <p class="mar-footer-note">
                            <strong>This is system-generated, signature is not required.</strong>
                            This document contains personal-identifiable information that is subject to Data Privacy. Please keep this document protected and in a safe place.
                        </p>
                    </td>
                </tr>
            </table>
        </footer>

        <section class="official-inventory-report">
            <header class="official-inventory-header">
                @if($officialLogosAvailable)
                    <img class="official-inventory-logo" src="{{ $pupOfficialLogoSrc }}" alt="PUP Logo">
                    <img class="official-inventory-government-logo" src="{{ $bpOfficialLogoSrc }}" alt="Bagong Pilipinas Logo">
                @else
                    <span class="official-inventory-logo pdf-logo-fallback">PUP</span>
                    <span class="official-inventory-government-logo pdf-logo-fallback">BP</span>
                @endif
                <p class="official-inventory-university">Polytechnic University of the Philippines</p>
                <p class="official-inventory-office">Medical Services Department</p>
                <p class="official-inventory-campus">Taguig Campus</p>
                <h1 class="official-inventory-header-title">{{ $inventoryTitle }}</h1>
                <p class="official-inventory-header-date">As of {{ $reportAsOf->format('F d, Y') }}</p>
                <span class="official-inventory-form-code">{{ $inventoryFormCode }}<br>July 11, 2024<br>Revision: 0</span>
            </header>

            <table class="official-inventory-meta">
                <tr>
                    <td>
                        <span class="meta-label">Name:</span>
                        <span class="meta-value">{{ $preparedByName }}</span>
                    </td>
                    <td>
                        <span class="meta-label">Date of Submission:</span>
                        <span class="meta-value">{{ $reportAsOf->format('F d, Y') }}</span>
                    </td>
                </tr>
                <tr>
                    <td>
                        <span class="meta-label">Position:</span>
                        <span class="meta-value">{{ $preparedByPosition }}</span>
                    </td>
                    <td>
                        <span class="meta-label">Unit / Department:</span>
                        <span class="meta-value">Taguig Campus</span>
                    </td>
                </tr>
            </table>

            <table class="official-inventory-table">
                <colgroup>
                    <col style="width: 10%;">
                    <col style="width: 11%;">
                    <col style="width: 31%;">
                    <col style="width: 9%;">
                    <col style="width: 10%;">
                    <col style="width: 10%;">
                    <col style="width: 9%;">
                    <col style="width: 10%;">
                </colgroup>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Stock Number</th>
                        <th>Medicines &amp; Materials</th>
                        <th>Units</th>
                        <th>Quantity</th>
                        <th>Consumed</th>
                        <th>Balance</th>
                        <th>Expiration Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($inventoryGroups as $groupName => $items)
                        <tr class="inventory-category-row">
                            <td colspan="8">{{ $groupName }}</td>
                        </tr>
                        @foreach($items as $item)
                            <tr>
                                <td class="date-cell">{{ optional($item->date_added)->format('d-M-y') ?: '-' }}</td>
                                <td class="number-cell">{{ $item->stock_number ?: '-' }}</td>
                                <td class="item-cell">{{ $item->name }}</td>
                                <td class="unit-cell">{{ $item->unit ?: 'Piece' }}</td>
                                <td class="number-cell">{{ $formatInventoryQuantity($item->starting_stock) }}</td>
                                <td class="number-cell">{{ $formatInventoryQuantity($item->consumed) }}</td>
                                <td class="number-cell">{{ $formatInventoryQuantity($item->current_balance) }}</td>
                                <td class="date-cell">{{ optional($item->expiration_date)->format('M Y') ?: '-' }}</td>
                            </tr>
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="8" style="height: 38px; text-align: center;">
                                No {{ $inventoryScope === 'medicines' ? 'medicines' : 'supplies' }} found in the inventory.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <table class="official-inventory-signatures">
                <tr>
                    <td>Prepared by:</td>
                    <td>Noted by:</td>
                </tr>
                <tr>
                    <td class="official-inventory-signature-space"></td>
                    <td class="official-inventory-signature-space"></td>
                </tr>
                <tr>
                    <td>
                        <div class="official-inventory-signature-name">&nbsp;</div>
                        <div class="official-inventory-signature-role">&nbsp;</div>
                    </td>
                    <td>
                        <div class="official-inventory-signature-name">&nbsp;</div>
                        <div class="official-inventory-signature-role">&nbsp;</div>
                    </td>
                </tr>
            </table>
        </section>




    @elseif($type == 'appointment')
        @php
            $appointmentPreparedBy = auth('admin')->user() ?? auth()->user();
            $appointmentPreparedByName = trim((string) optional($appointmentPreparedBy)->name) ?: 'CLINIC STAFF';
            $appointmentPreparedByPosition = \App\Models\User::normalizeRole(optional($appointmentPreparedBy)->user_role) === \App\Models\User::ROLE_ADMIN
                ? 'Nurse / Clinic Staff'
                : 'Clinic Staff';
            $appointmentReportAsOf = $dateTo ?? now();
        @endphp

        <footer class="official-inventory-page-footer">
            <table class="mar-footer-layout">
                <tr>
                    <td class="mar-footer-text">
                        <p class="official-inventory-footer-contact">
                            Gen. Santos Avenue, Lower Bicutan, Taguig City 1632<br>
                            Direct Line: (02) 8837 5858 to 60 | Email: taguig@pup.edu.ph<br>
                            Website: www.pup.edu.ph | Inquiries: https://bit.ly/PUPSINTA
                        </p>
                        <p class="official-inventory-footer-motto">A Leading Comprehensive<br>Polytechnic University in Asia</p>
                    </td>
                    <td class="mar-footer-image-cell">
                        @if($footerBgAvailable)
                            <img class="mar-footer-image" src="{{ $footerBgSrc }}" alt="PUP accreditation marks">
                        @endif
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <p class="mar-footer-note">
                            <strong>This is system-generated, signature is not required.</strong>
                            This document contains personal-identifiable information that is subject to Data Privacy. Please keep this document protected and in a safe place.
                        </p>
                    </td>
                </tr>
            </table>
        </footer>

        <section class="official-inventory-report">
            <header class="official-inventory-header">
                @if($officialLogosAvailable)
                    <img class="official-inventory-logo" src="{{ $pupOfficialLogoSrc }}" alt="PUP Logo">
                    <img class="official-inventory-government-logo" src="{{ $bpOfficialLogoSrc }}" alt="Bagong Pilipinas Logo">
                @else
                    <span class="official-inventory-logo pdf-logo-fallback">PUP</span>
                    <span class="official-inventory-government-logo pdf-logo-fallback">BP</span>
                @endif
                <p class="official-inventory-university">Polytechnic University of the Philippines</p>
                <p class="official-inventory-office">Medical Services Department</p>
                <p class="official-inventory-campus">Taguig Campus</p>
                <h1 class="official-inventory-header-title">Appointment Summary Report</h1>
                <p class="official-inventory-header-date">As of {{ \Carbon\Carbon::parse($appointmentReportAsOf)->format('F d, Y') }}</p>
                <span class="official-inventory-form-code">PUP-MIOS-6-MEDS-031<br>July 11, 2024<br>Revision: 0</span>
            </header>

            <table class="official-inventory-meta">
                <tr>
                    <td>
                        <span class="meta-label">Name:</span>
                        <span class="meta-value">{{ $appointmentPreparedByName }}</span>
                    </td>
                    <td>
                        <span class="meta-label">Date of Submission:</span>
                        <span class="meta-value">{{ \Carbon\Carbon::parse($appointmentReportAsOf)->format('F d, Y') }}</span>
                    </td>
                </tr>
                <tr>
                    <td>
                        <span class="meta-label">Position:</span>
                        <span class="meta-value">{{ $appointmentPreparedByPosition }}</span>
                    </td>
                    <td>
                        <span class="meta-label">Unit / Department:</span>
                        <span class="meta-value">Taguig Campus</span>
                    </td>
                </tr>
            </table>

            <table class="official-inventory-table">
                <colgroup>
                    <col style="width: 13%;">
                    <col style="width: 27%;">
                    <col style="width: 17%;">
                    <col style="width: 28%;">
                    <col style="width: 15%;">
                </colgroup>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Patient Name</th>
                        <th>User Type</th>
                        <th>Purpose / Reason</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $app)
                        <tr>
                            <td class="date-cell">{{ $app->date ? \Carbon\Carbon::parse($app->date)->format('d-M-y') : '-' }}</td>
                            <td class="item-cell">{{ $app->name ?: optional($app->user)->name ?: 'N/A' }}</td>
                            <td class="unit-cell">{{ $app->user_type ?? 'N/A' }}</td>
                            <td class="item-cell">{{ $app->service ?: $app->remarks ?: 'N/A' }}</td>
                            <td class="unit-cell">{{ ucfirst((string) $app->status) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="height: 38px; text-align: center;">No recorded appointments.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <table class="official-inventory-signatures">
                <tr>
                    <td>Prepared by:</td>
                    <td>Noted by:</td>
                </tr>
                <tr>
                    <td class="official-inventory-signature-space"></td>
                    <td class="official-inventory-signature-space"></td>
                </tr>
                <tr>
                    <td>
                        <div class="official-inventory-signature-name">&nbsp;</div>
                        <div class="official-inventory-signature-role">&nbsp;</div>
                    </td>
                    <td>
                        <div class="official-inventory-signature-name">&nbsp;</div>
                        <div class="official-inventory-signature-role">&nbsp;</div>
                    </td>
                </tr>
            </table>
        </section>
    @elseif($type == 'audit')
        <p style="font-size: 12px; font-weight: bold; margin: 0 0 10px;">
            Date Range:
            {{ optional($dateFrom ?? null)->format('F d, Y') ?? 'N/A' }}
            to
            {{ optional($dateTo ?? null)->format('F d, Y') ?? 'N/A' }}
        </p>
        <table>
            <thead>
                <tr>
                    <th>Date / Time</th>
                    <th>Actor</th>
                    <th>Role</th>
                    <th>Event</th>
                    <th>Module</th>
                    <th>Request</th>
                    <th>Status</th>
                    <th>IP</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $log)
                <tr>
                    <td>{{ optional($log->created_at)->format('M d, Y g:i A') }}</td>
                    <td class="text-left">{{ $log->user_name ?: 'Unknown' }}</td>
                    <td>{{ ucwords(str_replace('_', ' ', $log->user_role ?: 'unknown')) }}</td>
                    <td>{{ ucwords(str_replace('_', ' ', $log->event_type ?: 'action')) }}</td>
                    <td>{{ $log->module ?: '-' }}</td>
                    <td class="text-left">{{ trim(($log->http_method ?: '-') . ' ' . ($log->request_path ?: '-')) }}</td>
                    <td>{{ $log->status_code ?: '-' }}</td>
                    <td>{{ $log->ip_address ?: '-' }}</td>
                    <td class="text-left">{{ $log->description ?: $log->action ?: '-' }}</td>
                </tr>
                @empty
                <tr><td colspan="9">No audit trail records found for the selected date range.</td></tr>
                @endforelse
            </tbody>
        </table>
    @elseif($type == 'health_forms')
        <table>
            <thead>
                <tr>
                    <th>COURSE</th>
                    <th>ISSUED FORMS</th>
                    <th>WITH CONDITION</th>
                    <th>NO CONDITION</th>
                    <th>FOR APPROVAL</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $form)
                <tr>
                    <td class="text-left">{{ $form->course }}</td>
                    <td>{{ $form->issued_count }}</td>
                    <td>{{ $form->with_condition_count }}</td>
                    <td>{{ $form->no_condition_count }}</td>
                    <td>{{ $form->for_approval_count ?? 0 }}</td>
                </tr>
                @empty
                <tr><td colspan="5">No issued health forms found.</td></tr>
                @endforelse
            </tbody>
        </table>
    @endif

    @if(!in_array($type, ['inventory', 'mar', 'appointment'], true))
        <div class="footer-signatures" style="margin-top: 40px;">
            <div class="sig-box">
                <p>Prepared by:</p>
                <div class="sig-line">NURSE / MEDICAL STAFF</div>
            </div>
            <div class="sig-box">
                <p>Noted by:</p>
                <div class="sig-line">BRANCH DIRECTOR</div>
            </div>
        </div>

        <div class="official-footer">
            <div class="footer-details">
                <p>General Santos Avenue, Lower Bicutan Taguig City Philippines, 1632</p>
                <p>Direct Line (02) 8837 5658 to 60</p>
                <p>Website: www.pup.edu.ph | Email: taguig@pup.edu.ph</p>
            </div>
            <div class="footer-motto">
                THE COUNTRY'S FIRST POLYTECHNICU
            </div>
            <div class="generated-report-caption">
                <div class="signature-note">This is system-generated, signature is not required.</div>
                <div class="privacy-note">
                    This document contains personal-identifiable information that is subject to Data Privacy.<br>
                    Please keep this document protected and in a safe place.
                </div>
            </div>
        </div>
    @endif
    </div>

</body>
</html>
