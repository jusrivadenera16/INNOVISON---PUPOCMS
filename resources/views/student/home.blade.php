@extends('layouts.student')

@section('title', 'Home')

@push('styles')
<style>
    /* --- CRITICAL FIX: REMOVE TOP PADDING FOR HOME PAGE ONLY --- */
    main {
        padding-top: 0 !important;
        padding-bottom: 0 !important;
        background: #06101f;
    }
    html { scroll-behavior: smooth; }

    /* --- HERO SECTION STYLES --- */
    .PUPBG {
        position: relative;
        min-height: calc(100vh - 74px);
        display: flex;
        align-items: center;
        overflow: visible;
        isolation: isolate;
        z-index: 2;
        margin-bottom: -44px;
    }
    .PUPBG::before {
        content: "";
        position: absolute;
        inset: 0;
        background:
            radial-gradient(circle at 91% 11%, rgba(250, 204, 21, .18), transparent 170px),
            radial-gradient(circle at 54% 87%, rgba(250, 204, 21, .16), transparent 180px),
            linear-gradient(90deg, rgba(72, 3, 12, .96) 0%, rgba(112, 19, 27, .88) 45%, rgba(112, 19, 27, .58) 100%),
            linear-gradient(180deg, rgba(28, 4, 10, .16), rgba(28, 4, 10, .34)),
            url('{{ asset("images/PUPBG.jpg") }}') center center / cover fixed no-repeat;
        z-index: 0;
    }
    .PUPBG-overlay {
        position: absolute;
        inset: 0;
        z-index: 1;
        background:
            radial-gradient(circle, rgba(255, 255, 255, .18) 0 1px, transparent 1.6px) left 42px top 50px / 11px 11px no-repeat,
            radial-gradient(circle, rgba(255, 255, 255, .14) 0 1px, transparent 1.6px) right 20px bottom 58px / 11px 11px no-repeat,
            linear-gradient(180deg, rgba(255, 255, 255, .03), rgba(0, 0, 0, .14));
        background-size: 132px 190px, 132px 108px, auto;
        pointer-events: none;
    }
    .PUPBG-overlay::before,
    .PUPBG-overlay::after {
        content: "";
        position: absolute;
        border: 1px solid rgba(255, 255, 255, .08);
        pointer-events: none;
    }
    .PUPBG-overlay::before {
        left: 30px;
        top: 225px;
        width: 28px;
        height: 28px;
        border-radius: 4px;
    }
    .PUPBG-overlay::after {
        right: 66px;
        top: 260px;
        width: 120px;
        height: 42px;
        border-radius: 999px;
        border-left: 0;
        border-right: 0;
        opacity: .55;
    }
    .PUPBG-inner {
        position: relative;
        z-index: 3;
        width: min(1180px, calc(100% - 44px));
        margin: 0 auto;
        padding: clamp(28px, 5vh, 48px) 0 96px;
        color: #fff;
        display: grid;
        grid-template-columns: minmax(0, 620px) minmax(360px, 470px);
        align-items: center;
        gap: clamp(40px, 6vw, 92px);
        text-align: left;
    }

    .hero-copy {
        max-width: 700px;
    }

    .kicker {
        letter-spacing: 1.6px;
        font-weight: 900;
        margin: 0 0 13px 0;
        color: #facc15;
        font-size: 13px;
        text-transform: uppercase;
    }
    .PUPBG-title {
        max-width: 700px;
        font-size: clamp(2.8rem, 5.5vw, 4.65rem);
        margin: 0 0 18px 0;
        line-height: 1.08;
        font-weight: 950;
        letter-spacing: -.035em;
        text-shadow: 0 12px 28px rgba(0,0,0,0.24);
        color: #fff;
    }
    .hero-title-line {
        display: block;
    }
    .hero-title-rotating-line {
        white-space: nowrap;
        font-size: .92em;
    }
    .PUPBG-title .hero-rotating-word {
        color: #facc15;
    }
    .hero-rotating-word {
        display: inline-block;
        min-width: 0;
        transition: opacity .24s ease, transform .24s ease;
    }
    .hero-rotating-word.is-changing {
        opacity: 0;
        transform: translateY(8px);
    }
    .PUPBG-title::after {
        content: "";
        display: block;
        width: 86px;
        height: 3px;
        margin-top: 22px;
        border-radius: 999px;
        background: #ffffff;
        box-shadow: 32px 0 0 #facc15;
    }
    .PUPBG-lead {
        color: rgba(255, 255, 255, .88);
        margin: 0 0 26px;
        max-width: 350px;
        font-size: 15px;
        line-height: 1.58;
        font-weight: 500;
    }

    .hero-status-card {
        width: min(238px, 100%);
        min-height: 82px;
        margin-top: 22px;
        border-radius: 14px;
        background: transparent;
        border: 1px solid rgba(255, 255, 255, .32);
        box-shadow:
            inset 0 1px 0 rgba(255,255,255,.12),
            0 18px 34px rgba(0,0,0,.24),
            0 0 20px rgba(250,204,21,.08);
        backdrop-filter: blur(14px);
        -webkit-backdrop-filter: blur(14px);
        display: grid;
        grid-template-columns: 34px minmax(0, 1fr);
        align-items: center;
        gap: 9px;
        padding: 14px 18px;
        color: #ffffff;
    }
    .hero-status-card.is-closed .hero-status-dot {
        background: #ef4444;
        box-shadow: 0 0 18px rgba(239,68,68,.68);
    }
    .hero-status-dot {
        width: 18px;
        height: 18px;
        border-radius: 999px;
        justify-self: center;
        background: #22c55e;
        box-shadow: 0 0 18px rgba(34,197,94,.72);
    }
    .hero-status-title {
        display: block;
        font-size: 14px;
        font-weight: 900;
    }
    .hero-status-time {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        margin-top: 8px;
        color: rgba(255,255,255,.82);
        font-size: 13px;
        font-weight: 600;
    }
    .hero-status-time svg {
        width: 15px;
        height: 15px;
        stroke-width: 1.9;
    }
    .hero-status-countdown {
        display: block;
        margin-top: 6px;
        color: #facc15;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: .01em;
    }
    .hero-status-countdown strong {
        font-variant-numeric: tabular-nums;
    }
    .btn { position:relative; overflow:hidden; display:inline-flex; align-items:center; justify-content:center; gap:10px; padding:12px 24px; border-radius:8px; text-decoration:none; font-weight:700; cursor: pointer; border: none; transition:transform .18s ease, background .18s ease, color .18s ease, border-color .18s ease, box-shadow .18s ease; }
    .btn::after {
        content:"";
        position:absolute;
        inset:0;
        background:linear-gradient(120deg, transparent 0%, rgba(255,255,255,.18) 28%, rgba(255,255,255,.58) 50%, rgba(255,255,255,.18) 72%, transparent 100%);
        transform:translateX(-135%);
        transition:transform .72s ease;
        pointer-events:none;
    }
    .btn:hover::after,
    .btn:focus-visible::after { transform:translateX(135%); }
    .btn svg { width:18px; height:18px; flex:0 0 auto; stroke-width:1.8; }
    .hero-actions {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
        align-items: stretch;
        width: min(470px, 100%);
        justify-self: end;
        transform: translateX(46px);
    }
    @property --hero-card-glow-angle {
        syntax: '<angle>';
        inherits: false;
        initial-value: 0deg;
    }
    .hero-action-card {
        min-height: 244px;
        flex-direction: column;
        justify-content: flex-start;
        gap: 13px;
        padding: 32px 20px 22px;
        color: #ffffff;
        border-radius: 18px;
        background: transparent;
        border: 1px solid rgba(255, 255, 255, .28);
        box-shadow:
            inset 0 1px 0 rgba(255,255,255,.18),
            0 22px 42px rgba(0,0,0,.36),
            0 0 30px rgba(250,204,21,.11);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        text-align: center;
        overflow: visible;
        transition: transform .35s cubic-bezier(.22, 1, .36, 1), background .35s ease, box-shadow .35s ease;
    }
    .hero-action-card > * {
        position: relative;
        z-index: 2;
    }
    .hero-action-card::before {
        content: "";
        position: absolute;
        inset: -3px;
        z-index: 1;
        padding: 3px;
        border-radius: 21px;
        background:
            conic-gradient(
                from var(--hero-card-glow-angle, 0deg),
                transparent 0deg,
                transparent 235deg,
                rgba(250, 204, 21, .02) 247deg,
                rgba(250, 204, 21, .14) 257deg,
                rgba(255, 245, 180, .48) 266deg,
                rgba(255, 255, 255, .68) 270deg,
                rgba(255, 245, 180, .48) 274deg,
                rgba(250, 204, 21, .14) 283deg,
                rgba(250, 204, 21, .02) 293deg,
                transparent 305deg,
                transparent 360deg
        );
        opacity: .82;
        filter: drop-shadow(0 0 7px rgba(250, 204, 21, .52)) drop-shadow(0 0 16px rgba(250, 204, 21, .24));
        -webkit-mask:
            linear-gradient(#000 0 0) content-box,
            linear-gradient(#000 0 0);
        -webkit-mask-composite: xor;
        mask:
            linear-gradient(#000 0 0) content-box,
            linear-gradient(#000 0 0);
        mask-composite: exclude;
        pointer-events: none;
        animation: heroCardGlowTrace 6.5s linear infinite;
    }
    .hero-action-card::after {
        content: "";
        position: absolute;
        inset: -11px;
        z-index: 0;
        padding: 11px;
        border-radius: 29px;
        background:
            conic-gradient(
                from var(--hero-card-glow-angle, 0deg),
                transparent 0deg,
                transparent 238deg,
                rgba(250, 204, 21, .02) 250deg,
                rgba(250, 204, 21, .13) 263deg,
                rgba(255, 250, 205, .38) 270deg,
                rgba(250, 204, 21, .13) 277deg,
                rgba(250, 204, 21, .02) 290deg,
                transparent 302deg,
                transparent 360deg
            );
        opacity: .36;
        filter: blur(13px);
        transform: none;
        transition: none;
        -webkit-mask:
            linear-gradient(#000 0 0) content-box,
            linear-gradient(#000 0 0);
        -webkit-mask-composite: xor;
        mask:
            linear-gradient(#000 0 0) content-box,
            linear-gradient(#000 0 0);
        mask-composite: exclude;
        pointer-events: none;
        animation: heroCardGlowTrace 6.5s linear infinite;
    }
    .hero-action-card:hover::after,
    .hero-action-card:focus-visible::after {
        transform: none;
    }
    .hero-action-card svg {
        width: 32px;
        height: 32px;
        flex: 0 0 auto;
    }
    .hero-action-icon {
        width: 52px;
        height: 52px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        background: rgba(139, 0, 0, .58);
        border: 1px solid rgba(250,204,21,.48);
        box-shadow: 0 0 28px rgba(250,204,21,.17), inset 0 1px 0 rgba(255,255,255,.12);
    }
    .hero-action-icon svg {
        width: 24px;
        height: 24px;
    }
    .hero-action-card .hero-action-copy {
        display: grid;
        gap: 12px;
        min-width: 0;
        font-size: 19px;
        line-height: 1.18;
        font-weight: 950;
    }
    .hero-action-copy::after {
        content: "";
        width: 40px;
        height: 2px;
        border-radius: 999px;
        background: #facc15;
        justify-self: center;
    }
    .hero-action-card .hero-action-description {
        max-width: 188px;
        color: rgba(255,255,255,.86);
        font-size: 13px;
        line-height: 1.55;
        font-weight: 500;
    }
    .hero-action-card .hero-action-arrow {
        width: 38px;
        height: 38px;
        padding: 10px;
        margin-top: 6px;
        border-radius: 999px;
        border: 1px solid rgba(250,204,21,.64);
        color: #facc15;
        transition: background .2s ease, color .2s ease, transform .2s ease;
    }
    .hero-action-card:hover,
    .hero-action-card:focus-visible {
        animation: none;
        transform: translateY(-9px) scale(1.035);
        border-color: rgba(250, 204, 21, .46);
        background: rgba(112, 19, 27, .42);
        box-shadow:
            inset 0 0 0 1px rgba(250,204,21,.10),
            inset 0 1px 0 rgba(255,255,255,.16),
            0 28px 52px rgba(0,0,0,.42),
            0 0 36px rgba(250,204,21,.16);
        outline: none;
    }
    .hero-action-card:hover .hero-action-arrow,
    .hero-action-card:focus-visible .hero-action-arrow {
        background: #facc15;
        color: #8B0000;
        transform: translateX(3px);
    }
    .btn-primary { background:#8B0000; color:#fff; border:2px solid rgba(0,0,0,0.08); box-shadow:0 6px 18px rgba(0,0,0,0.25); }
    .btn-secondary { background:#fff; color:#15222a; border:0; }
    .btn-primary:hover,
    .btn-primary:focus-visible,
    .btn-secondary:hover,
    .btn-secondary:focus-visible {
        background:#facc15;
        color:#8B0000;
        border-color:#facc15;
        transform:translateY(-2px);
        box-shadow:0 12px 28px rgba(139,0,0,.22);
        outline:none;
    }

    .hero-scroll {
        position: absolute;
        left: 50%;
        bottom: 8px;
        z-index: 7;
        display: inline-flex;
        flex-direction: row;
        align-items: center;
        gap: 10px;
        color: #ffffff;
        -webkit-text-fill-color: #ffffff;
        opacity: 1 !important;
        filter: none;
        mix-blend-mode: normal;
        font-size: 13px;
        font-weight: 800;
        text-transform: none;
        text-decoration: none;
        transform: translateX(-50%);
        text-shadow: 0 1px 3px rgba(0,0,0,.35);
        transition: opacity .22s ease, visibility .22s ease, transform .22s ease;
        animation: scrollPop 1.9s ease-in-out infinite;
    }
    .hero-scroll,
    .hero-scroll:visited,
    .hero-scroll:hover,
    .hero-scroll:focus-visible,
    .hero-scroll span,
    .hero-scroll svg {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        opacity: 1;
    }
    .hero-scroll.is-hidden {
        opacity: 0 !important;
        visibility: hidden;
        transform: translateX(-50%) translateY(8px);
        pointer-events: none;
    }
    .hero-scroll-button {
        width: auto;
        height: auto;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        background: transparent;
        border: 0;
        box-shadow: none;
    }
    .hero-scroll .hero-scroll-chevron {
        width: 19px;
        height: 19px;
        animation: scrollCue 1.35s ease-in-out infinite;
    }

    .hero-curve {
        position: absolute;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 4;
        width: 100%;
        height: 82px;
        background: linear-gradient(
            180deg,
            rgba(8, 20, 38, 0) 0%,
            rgba(8, 20, 38, .28) 34%,
            rgba(8, 20, 38, .64) 68%,
            rgba(8, 20, 38, .94) 100%
        );
        pointer-events: none;
    }

    @keyframes scrollCue {
        0%, 100% { transform: translateY(0); opacity: 1; }
        50% { transform: translateY(5px); opacity: 1; }
    }

    @keyframes scrollPop {
        0%, 100% { transform: translateX(-50%) scale(1); }
        50% { transform: translateX(-50%) scale(1.045); }
    }

    @keyframes heroCardGlowTrace {
        from { --hero-card-glow-angle: 0deg; }
        to { --hero-card-glow-angle: 360deg; }
    }

    /* --- WELCOME SECTION --- */
    .student-home-info-bg {
        position: relative;
        isolation: isolate;
        overflow: hidden;
        z-index: 1;
        padding-top: 0;
        background:
            linear-gradient(180deg, rgba(255, 250, 250, 0.70), rgba(255, 255, 255, 0.58) 42%, rgba(245, 248, 247, 0.72) 100%),
            url('{{ asset("images/student-bg.png") }}') center top / cover no-repeat;
    }
    .student-home-info-bg::before {
        content: "";
        position: absolute;
        inset: 0;
        z-index: -1;
        background:
            radial-gradient(circle at 12% 18%, rgba(139, 0, 0, 0.12), transparent 170px),
            radial-gradient(circle at 92% 8%, rgba(250, 204, 21, 0.12), transparent 140px);
        pointer-events: none;
    }
    .about-panel {
        padding: 34px 0 46px;
    }
    .about-section-title {
        margin: 0 0 22px;
        text-align: center;
        color: #5b0714;
        font-size: 22px;
        font-weight: 950;
        line-height: 1.2;
    }
    .about-section-title::after {
        content: "";
        display: block;
        width: 32px;
        height: 2px;
        margin: 10px auto 0;
        border-radius: 999px;
        background: #facc15;
    }
    .why-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 0;
        align-items: center;
        margin-bottom: 20px;
    }
    .why-item {
        display: grid;
        grid-template-columns: 70px minmax(0, 1fr);
        gap: 14px;
        align-items: center;
        padding: 0 24px;
        min-height: 70px;
        border-right: 1px solid rgba(139, 0, 0, .10);
    }
    .why-item:last-child { border-right: 0; }
    .why-icon {
        width: 58px;
        height: 58px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(139, 0, 0, .08);
        color: #8B0000;
    }
    .why-icon svg {
        width: 31px;
        height: 31px;
        stroke-width: 1.8;
    }
    .why-copy strong {
        display: block;
        color: #6d0715;
        font-size: 14px;
        font-weight: 900;
        margin-bottom: 6px;
    }
    .why-copy span {
        display: block;
        color: #334155;
        font-size: 13px;
        line-height: 1.45;
        font-weight: 500;
    }
    .home-announcement-shell {
        position: relative;
        width: min(1180px, calc(100% - 96px));
        height: 360px;
        margin: 0 auto;
        transform: none;
    }
    .home-announcement-track {
        position: absolute;
        inset: 0;
        overflow: hidden;
    }
    .home-announcement-band {
        position: relative;
        width: 100%;
        min-height: max(620px, calc(100svh - 72px));
        display: flex;
        align-items: center;
        overflow: hidden;
        margin: -34px 0 0;
        background: #5b071b url('{{ asset("images/announcement-bg.png") }}') center / cover no-repeat;
        box-shadow: none;
    }
    .home-announcement-band::before {
        content: "";
        position: absolute;
        inset: 0;
        pointer-events: none;
        background: linear-gradient(180deg, rgba(45, 2, 14, .08), rgba(45, 2, 14, .18));
    }
    .home-announcement-band + .container {
        padding-top: 42px;
    }
    .home-announcement-heading {
        position: absolute;
        top: 30px;
        left: 50%;
        z-index: 7;
        width: min(760px, calc(100% - 40px));
        margin: 0;
        color: #ffffff;
        text-align: center;
        transform: translateX(-50%);
    }
    .home-announcement-kicker {
        display: block;
        margin-bottom: 8px;
        color: #facc15;
        font-size: 12px;
        font-weight: 950;
        letter-spacing: 4px;
        line-height: 1.2;
        text-transform: uppercase;
    }
    .home-announcement-heading h2 {
        margin: 0;
        color: #ffffff;
        font-size: 36px;
        font-weight: 950;
        letter-spacing: 0;
        line-height: 1.12;
    }
    .home-announcement-title-line {
        display: block;
        width: 34px;
        height: 2px;
        margin: 14px auto 0;
        border-radius: 999px;
        background: #facc15;
    }
    .home-announcement-card {
        position: absolute;
        top: 50%;
        left: 50%;
        z-index: 1;
        width: 405px;
        min-height: 255px;
        display: flex;
        flex-direction: column;
        padding: 34px 30px 20px;
        overflow: hidden;
        border-radius: 18px;
        border: 1px solid rgba(250, 204, 21, .30);
        background: linear-gradient(145deg, rgba(95, 13, 35, .74), rgba(50, 5, 19, .78));
        box-shadow: 0 20px 42px rgba(27, 0, 8, .30), inset 0 1px rgba(255, 255, 255, .08);
        color: #f8fafc;
        opacity: 0;
        visibility: hidden;
        transform: translate(-50%, -50%) scale(.78);
        pointer-events: none;
        cursor: pointer;
        transition: left .48s cubic-bezier(.22, .8, .24, 1), width .48s ease,
            min-height .48s ease, opacity .35s ease, transform .48s cubic-bezier(.22, .8, .24, 1),
            visibility .35s ease, filter .35s ease, border-color .2s ease, box-shadow .2s ease;
    }
    .home-announcement-card.is-current,
    .home-announcement-card.is-next {
        z-index: 3;
        width: 405px;
        min-height: 255px;
        opacity: 1;
        visibility: visible;
        transform: translate(-50%, -50%) scale(1);
        pointer-events: auto;
        box-shadow: 0 20px 42px rgba(27, 0, 8, .30), inset 0 1px rgba(255, 255, 255, .08);
    }
    .home-announcement-card.is-current { left: calc(50% - 215px); }
    .home-announcement-card.is-next { left: calc(50% + 215px); }
    .home-announcement-card.is-prev,
    .home-announcement-card.is-next-far {
        z-index: 2;
        width: 405px;
        min-height: 255px;
        opacity: .36;
        visibility: visible;
        filter: blur(1.15px);
        transform: translate(-50%, -50%) scale(.94);
        pointer-events: auto;
    }
    .home-announcement-card.is-prev {
        left: 0;
        -webkit-mask-image: linear-gradient(90deg, transparent 42%, rgba(0, 0, 0, .32) 50%, #000 68%);
        mask-image: linear-gradient(90deg, transparent 42%, rgba(0, 0, 0, .32) 50%, #000 68%);
    }
    .home-announcement-card.is-next-far {
        left: 100%;
        -webkit-mask-image: linear-gradient(90deg, #000 32%, rgba(0, 0, 0, .32) 50%, transparent 58%);
        mask-image: linear-gradient(90deg, #000 32%, rgba(0, 0, 0, .32) 50%, transparent 58%);
    }
    .home-announcement-shell.is-static .home-announcement-card {
        z-index: 3;
        width: 405px;
        min-height: 255px;
        opacity: 1;
        visibility: visible;
        transform: translate(-50%, -50%) scale(1);
        pointer-events: auto;
    }
    .home-announcement-shell.static-count-1 .home-announcement-card:nth-of-type(1) {
        left: 50%;
    }
    .home-announcement-shell.static-count-2 .home-announcement-card:nth-of-type(1) {
        left: calc(50% - 215px);
    }
    .home-announcement-shell.static-count-2 .home-announcement-card:nth-of-type(2) {
        left: calc(50% + 215px);
    }
    .home-announcement-shell.static-count-3 .home-announcement-card:nth-of-type(1) {
        left: calc(50% - 290px);
    }
    .home-announcement-shell.static-count-3 .home-announcement-card:nth-of-type(2) {
        left: 50%;
    }
    .home-announcement-shell.static-count-3 .home-announcement-card:nth-of-type(3) {
        left: calc(50% + 290px);
    }
    .home-announcement-shell.carousel-count-3 .home-announcement-card.is-current {
        left: 50%;
        width: 405px;
        min-height: 255px;
        opacity: 1;
        filter: none;
    }
    .home-announcement-shell.carousel-count-3 .home-announcement-card.is-prev,
    .home-announcement-shell.carousel-count-3 .home-announcement-card.is-next {
        z-index: 2;
        width: 405px;
        min-height: 255px;
        opacity: .36;
        visibility: visible;
        filter: blur(1.15px);
        transform: translate(-50%, -50%) scale(.94);
        pointer-events: auto;
    }
    .home-announcement-shell.carousel-count-3 .home-announcement-card.is-prev {
        left: 0;
    }
    .home-announcement-shell.carousel-count-3 .home-announcement-card.is-next {
        left: 100%;
        -webkit-mask-image: linear-gradient(90deg, #000 32%, rgba(0, 0, 0, .32) 50%, transparent 58%);
        mask-image: linear-gradient(90deg, #000 32%, rgba(0, 0, 0, .32) 50%, transparent 58%);
    }
    .home-announcement-card:hover,
    .home-announcement-card:focus-visible {
        border-color: rgba(250, 204, 21, .48);
        box-shadow: 0 28px 52px rgba(27, 0, 8, .44), inset 0 1px rgba(255, 255, 255, .10);
        outline: none;
    }
    .home-announcement-card.is-current:hover,
    .home-announcement-card.is-current:focus-visible,
    .home-announcement-card.is-next:hover,
    .home-announcement-card.is-next:focus-visible,
    .home-announcement-shell.is-static .home-announcement-card:hover,
    .home-announcement-shell.is-static .home-announcement-card:focus-visible {
        transform: translate(-50%, -50%) scale(1.035);
    }
    .home-announcement-card.is-prev:hover,
    .home-announcement-card.is-prev:focus-visible,
    .home-announcement-card.is-next-far:hover,
    .home-announcement-card.is-next-far:focus-visible,
    .home-announcement-shell.carousel-count-3 .home-announcement-card.is-next:hover,
    .home-announcement-shell.carousel-count-3 .home-announcement-card.is-next:focus-visible {
        transform: translate(-50%, -50%) scale(.985);
    }
    .announcement-card-head {
        display: grid;
        grid-template-columns: 72px minmax(0, 1fr);
        gap: 22px;
        align-items: center;
        margin-bottom: 10px;
    }
    .announcement-icon {
        width: 68px;
        height: 68px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(126, 8, 35, .70);
        border: 1px solid rgba(250, 204, 21, .70);
        color: #facc15;
        box-shadow: 0 8px 18px rgba(0, 0, 0, .18);
    }
    .announcement-icon svg {
        width: 32px;
        height: 32px;
        stroke-width: 1.8;
    }
    .announcement-eyebrow {
        margin: 0 0 6px;
        color: #facc15;
        font-size: 11px;
        letter-spacing: 0;
        font-weight: 950;
        text-transform: uppercase;
    }
    .announcement-title {
        margin: 0;
        color: #f8fafc;
        font-size: 21px;
        line-height: 1.24;
        font-weight: 900;
        text-transform: uppercase;
    }
    .announcement-message {
        display: -webkit-box;
        margin: 0 0 0 94px;
        overflow: hidden;
        color: #e2e8f0;
        font-size: 14px;
        line-height: 1.48;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 4;
    }
    .announcement-message p,
    .announcement-message ul { margin: 0; }
    .announcement-message ul { padding-left: 18px; }
    .announcement-message strong { font-weight: 950; }
    .home-announcement-image-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 6px;
        width: calc(100% - 94px);
        margin: 0 0 0 94px;
    }
    .home-announcement-image {
        display: block;
        width: 100%;
        height: 62px;
        border: 1px solid rgba(255,255,255,.18);
        border-radius: 8px;
        object-fit: cover;
    }
    .announcement-date {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        margin-top: auto;
        padding: 16px 10px 0;
        border-top: 1px solid rgba(255, 255, 255, .26);
        color: #e2e8f0;
        font-size: 13px;
        font-weight: 600;
    }
    .announcement-date svg {
        width: 18px;
        height: 18px;
    }
    .home-announcement-view-all {
        position: absolute;
        left: 50%;
        bottom: 28px;
        z-index: 8;
        min-width: 268px;
        height: 50px;
        padding: 0 24px;
        border: 0;
        border-radius: 999px;
        background: rgba(78, 4, 25, .72);
        color: #ffffff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 18px;
        font-size: 14px;
        font-weight: 900;
        cursor: pointer;
        transform: translateX(-50%);
        box-shadow: 0 10px 26px rgba(27, 0, 8, .32), inset 0 1px rgba(255, 255, 255, .08);
        transition: color .2s ease, background .2s ease, transform .2s ease, box-shadow .2s ease;
    }
    .home-announcement-view-all:hover,
    .home-announcement-view-all:focus-visible {
        background: #facc15;
        color: #70131b;
        transform: translateX(-50%) translateY(-2px);
        box-shadow: 0 14px 30px rgba(27, 0, 8, .42);
        outline: none;
    }
    .home-announcement-view-all svg {
        width: 20px;
        height: 20px;
    }
    .announcement-modal {
        display: flex;
        position: fixed;
        inset: 0;
        z-index: 1300;
        align-items: center;
        justify-content: center;
        padding: 24px;
        background: rgba(3, 7, 18, .58);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
        transition: opacity .3s ease, visibility 0s linear .32s;
    }
    .announcement-modal.is-open {
        opacity: 1;
        visibility: visible;
        pointer-events: auto;
        transition-delay: 0s;
    }
    .announcement-modal-card {
        width: min(560px, 100%);
        max-height: min(680px, calc(100dvh - 48px));
        display: flex;
        flex-direction: column;
        overflow: hidden;
        border-radius: 18px;
        background: #fffaf7;
        border: 1px solid rgba(250, 204, 21, .34);
        box-shadow: 0 30px 80px rgba(15, 23, 42, .36);
        opacity: 0;
        transform: translateY(22px) scale(.965);
        transition: opacity .25s ease, transform .34s cubic-bezier(.22, 1, .36, 1);
    }
    .announcement-modal.is-open .announcement-modal-card {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
    .announcement-modal-head {
        flex: 0 0 auto;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 18px;
        padding: 22px 24px;
        background: linear-gradient(135deg, #70131B, #8f2230);
        color: #fff;
    }
    .announcement-modal-eyebrow {
        margin: 0 0 8px;
        color: #facc15;
        font-size: 11px;
        letter-spacing: .8px;
        text-transform: uppercase;
        font-weight: 950;
    }
    .announcement-modal-title {
        margin: 0;
        font-size: 22px;
        line-height: 1.2;
        font-weight: 950;
    }
    .announcement-modal-close {
        position: relative;
        overflow: hidden;
        width: 40px;
        height: 40px;
        border-radius: 999px;
        border: 1px solid transparent;
        background: #70131b;
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        flex: 0 0 auto;
        outline: none;
        transition: color .2s ease, background .2s ease, transform .2s ease;
    }
    .announcement-modal-close::after {
        content: "";
        position: absolute;
        top: -35%;
        left: -78%;
        width: 42%;
        height: 170%;
        background: linear-gradient(
            115deg,
            transparent 0%,
            rgba(255, 255, 255, .18) 30%,
            rgba(255, 255, 255, .82) 50%,
            rgba(255, 255, 255, .18) 70%,
            transparent 100%
        );
        transform: skewX(-18deg);
        transition: left .48s ease;
        pointer-events: none;
    }
    .announcement-modal-close:hover,
    .announcement-modal-close:focus-visible {
        border-color: transparent;
        background: #facc15;
        color: #70131b;
        transform: translateY(-1px);
        outline: none;
    }
    .announcement-modal-close:hover::after,
    .announcement-modal-close:focus-visible::after {
        left: 136%;
    }
    .announcement-modal-close svg {
        position: relative;
        z-index: 1;
        width: 18px;
        height: 18px;
    }
    .announcement-modal-body {
        min-height: 0;
        overflow-y: auto;
        overscroll-behavior: contain;
        padding: 24px;
        color: #334155;
        line-height: 1.7;
        overflow-wrap: anywhere;
        white-space: pre-wrap;
        scrollbar-color: rgba(112, 19, 27, .55) transparent;
        scrollbar-width: thin;
    }
    .announcement-modal-body::-webkit-scrollbar {
        width: 7px;
    }
    .announcement-modal-body::-webkit-scrollbar-thumb {
        border-radius: 999px;
        background: rgba(112, 19, 27, .55);
    }
    .announcement-modal-image-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 10px;
        width: 100%;
        margin: 20px 0 0;
    }
    .announcement-modal-image-grid[hidden] { display: none; }
    .announcement-modal-image-card {
        position: relative;
        overflow: hidden;
        border-radius: 8px;
    }
    .announcement-modal-image-button {
        display: block;
        width: 100%;
        padding: 0;
        border: 0;
        background: transparent;
        cursor: pointer;
    }
    .announcement-modal-image {
        display: block;
        width: 100%;
        height: 150px;
        border: 1px solid rgba(112, 19, 27, .16);
        border-radius: 8px;
        object-fit: cover;
    }
    .announcement-modal-image-open {
        position: absolute;
        inset: 0;
        display: grid;
        place-items: center;
        opacity: 0;
        pointer-events: none;
        background: rgba(15, 23, 42, .58);
        color: #690014;
        text-decoration: none;
        transition: opacity .18s ease;
    }
    .announcement-modal-image-open span {
        min-height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0 14px;
        border-radius: 6px;
        background: #ffd21f;
        font-size: 12px;
        font-weight: 950;
    }
    .announcement-modal-image-card.is-open .announcement-modal-image-open {
        opacity: 1;
        pointer-events: auto;
    }
    .announcement-rich-content { white-space: normal; }
    .announcement-rich-content p,
    .announcement-rich-content ul { margin: 0 0 12px; }
    .announcement-rich-content p:last-child,
    .announcement-rich-content ul:last-child { margin-bottom: 0; }
    .announcement-rich-content ul { padding-left: 20px; }
    .announcement-modal-published {
        flex: 0 0 auto;
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 7px;
        padding: 12px 24px 16px;
        border-top: 1px solid rgba(112, 19, 27, .1);
        color: #64748b;
        font-size: 12px;
        font-weight: 650;
    }
    .announcement-modal-published svg {
        width: 15px;
        height: 15px;
        color: #8b0b24;
    }
    .announcement-all-card {
        width: min(760px, 100%);
        max-height: min(780px, calc(100vh - 48px));
        display: flex;
        flex-direction: column;
    }
    .announcement-all-list {
        min-height: 0;
        flex: 1 1 auto;
        display: grid;
        gap: 12px;
        padding: 20px;
        overflow-y: auto;
    }
    .announcement-all-item {
        width: 100%;
        display: grid;
        grid-template-columns: 46px minmax(0, 1fr) auto;
        gap: 14px;
        align-items: center;
        padding: 16px;
        border: 1px solid rgba(112, 19, 27, .16);
        border-radius: 8px;
        background: #ffffff;
        color: #111827;
        text-align: left;
        cursor: pointer;
        transition: border-color .2s ease, background .2s ease, transform .2s ease;
    }
    .announcement-all-item:hover,
    .announcement-all-item:focus-visible {
        border-color: rgba(250, 204, 21, .86);
        background: #fffaf0;
        transform: translateY(-1px);
        outline: none;
    }
    .announcement-all-item-icon {
        width: 46px;
        height: 46px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #8b0b24;
        color: #facc15;
    }
    .announcement-all-item-icon svg {
        width: 23px;
        height: 23px;
    }
    .announcement-all-item-copy strong,
    .announcement-all-item-copy span {
        display: block;
    }
    .announcement-all-item-copy strong {
        font-size: 14px;
        color: #70131b;
    }
    .announcement-all-item-copy span,
    .announcement-all-item-date {
        margin-top: 4px;
        color: #64748b;
        font-size: 12px;
    }
    html[data-theme="dark"] .announcement-all-item {
        border-color: rgba(255, 255, 255, .12);
        background: #172033;
        color: #f8fafc;
    }
    html[data-theme="dark"] .announcement-all-item:hover,
    html[data-theme="dark"] .announcement-all-item:focus-visible {
        background: #202b40;
        border-color: rgba(250, 204, 21, .72);
    }
    html[data-theme="dark"] .announcement-all-item-copy strong {
        color: #ffffff;
    }
    html[data-theme="dark"] .announcement-modal-card {
        background: #111827;
        border-color: rgba(250,204,21,.26);
    }
    html[data-theme="dark"] .announcement-modal-body {
        color: #f8fafc;
    }
    html[data-theme="dark"] .announcement-modal-published {
        border-top-color: rgba(255,255,255,.1);
        color: #cbd5e1;
    }
    html[data-theme="dark"] .announcement-modal-published svg {
        color: #facc15;
    }
    html.announcement-modal-open,
    body.announcement-modal-open {
        overflow: hidden;
    }
    @media (prefers-reduced-motion: reduce) {
        .announcement-modal,
        .announcement-modal-card,
        .announcement-modal-close {
            transition-duration: .01ms !important;
        }
    }
    .announcement-nav {
        position: absolute;
        top: 50%;
        z-index: 8;
        transform: translateY(-50%);
        width: 38px;
        height: 38px;
        border-radius: 999px;
        border: 1px solid rgba(255, 255, 255, .72);
        background: #ffffff;
        color: #8B0000;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        box-shadow: 0 10px 24px rgba(2, 6, 23, .28);
        transition: transform .2s ease, background .2s ease, color .2s ease;
    }
    .announcement-nav:hover,
    .announcement-nav:focus-visible {
        background: #facc15;
        color: #70131b;
        transform: translateY(-50%) scale(1.08);
        outline: none;
    }
    .announcement-nav:disabled {
        opacity: .82;
        cursor: default;
    }
    .announcement-nav:disabled:hover,
    .announcement-nav:disabled:focus-visible {
        background: #ffffff;
        color: #8B0000;
        transform: translateY(-50%);
    }
    .announcement-nav svg {
        width: 19px;
        height: 19px;
        stroke-width: 2;
    }
    .announcement-nav.prev { left: -30px; }
    .announcement-nav.next { right: -30px; }
    .announcement-pagination {
        position: absolute;
        left: 50%;
        bottom: 1px;
        z-index: 8;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        transform: translateX(-50%);
    }
    .announcement-dot {
        width: 5px;
        height: 5px;
        padding: 0;
        border: 0;
        border-radius: 999px;
        background: rgba(203, 213, 225, .68);
        cursor: pointer;
        transition: width .2s ease, background .2s ease, transform .2s ease;
    }
    .announcement-dot:hover,
    .announcement-dot:focus-visible {
        background: #f8fafc;
        transform: scale(1.25);
        outline: none;
    }
    .announcement-dot.is-active {
        width: 15px;
        background: #facc15;
    }
    .btn-outline { display:inline-flex; align-items:center; gap:8px; padding:0; border-radius:0; border:0; color:#8B0000; background:transparent; text-decoration:none; margin-top:14px; font-weight:900; transition:color .18s ease; }
    .btn-outline:hover,
    .btn-outline:focus-visible {
        background:transparent;
        border-color:transparent;
        color:#facc15;
        transform:none;
        box-shadow:none;
        outline:none;
    }
    .btn-outline svg { width:18px; height:18px; flex:0 0 auto; stroke-width:1.8; }
    .about-learn-more {
        text-align: center;
        margin: 10px 0 18px;
    }

    /* --- TESTIMONIALS / COMMENTS --- */
    .student-home-info-bg .about-panel {
        background: transparent;
    }
    html[data-theme="dark"] .student-home-info-bg {
        background:
            linear-gradient(180deg, rgba(2, 6, 23, 0.82), rgba(15, 23, 42, 0.74) 42%, rgba(2, 6, 23, 0.84) 100%),
            url('{{ asset("images/student-bg.png") }}') center top / cover no-repeat;
    }
    html[data-theme="dark"] .student-home-info-bg::before {
        background:
            radial-gradient(circle at 12% 18%, rgba(250, 204, 21, 0.10), transparent 170px),
            radial-gradient(circle at 92% 8%, rgba(139, 0, 0, 0.22), transparent 140px);
    }
    html[data-theme="dark"] .home-announcement-band {
        background: #500d20;
    }
    html[data-theme="dark"] .hero-curve {
        background: linear-gradient(
            180deg,
            rgba(2, 6, 23, 0) 0%,
            rgba(8, 20, 38, .34) 42%,
            rgba(8, 20, 38, .72) 72%,
            rgba(8, 20, 38, .96) 100%
        );
    }
    html[data-theme="dark"] .about-section-title,
    html[data-theme="dark"] .comments-section h3 {
        color: #f8fafc;
    }
    html[data-theme="dark"] .why-item {
        border-right-color: rgba(255, 255, 255, .12);
    }
    html[data-theme="dark"] .why-icon {
        background: rgba(255, 255, 255, .08);
        color: #facc15;
    }
    html[data-theme="dark"] .why-copy strong {
        color: #f8fafc;
    }
    html[data-theme="dark"] .why-copy span {
        color: #cbd5e1;
    }
    html[data-theme="dark"] .btn-outline,
    html[data-theme="dark"] .btn-outline:hover,
    html[data-theme="dark"] .btn-outline:focus-visible {
        color: #facc15;
    }
    html[data-theme="dark"] .comment-card {
        background: #151d2b;
        color: #f8fafc;
        box-shadow: 0 8px 28px rgba(0, 0, 0, .26);
    }
    html[data-theme="dark"] .comment-card:hover {
        box-shadow: 0 20px 48px rgba(0, 0, 0, .38);
    }
    html[data-theme="dark"] .comment-card::before {
        color: #facc15;
    }
    html[data-theme="dark"] .comment-body h4 {
        color: #f8fafc;
    }
    html[data-theme="dark"] .comment-meta,
    html[data-theme="dark"] .comment-card > p,
    html[data-theme="dark"] .comment-body p {
        color: #cbd5e1;
    }
    html[data-theme="dark"] .comment-chip {
        background: rgba(255, 255, 255, .08);
        color: #e2e8f0;
    }
    html[data-theme="dark"] .feedback-more {
        background: #1e293b;
        border-color: rgba(250, 204, 21, .28);
        color: #facc15;
        box-shadow: 0 8px 20px rgba(0, 0, 0, .24);
    }
    html[data-theme="dark"] .feedback-more:hover {
        background: rgba(250, 204, 21, .16);
    }
    html[data-theme="dark"] .comments-section {
        background: transparent !important;
    }
    html[data-theme="dark"] .student-home-info-bg .comments-section {
        background: transparent !important;
    }
    .comments-section { padding:0; margin-top:34px; background:transparent; }
    .comments-section .section-head { display:block; text-align:center; margin: 18px 0 24px; }
    .comments-section h3 { margin:0; font-size:22px; color: #5b0714; font-weight: 950; }
    .comments-section h3::after {
        content: "";
        display: block;
        width: 32px;
        height: 2px;
        margin: 10px auto 0;
        border-radius: 999px;
        background: #facc15;
    }
    .comments-section p.lead { display:none; }
    .feedback-more {
        width: 40px;
        height: 40px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        background: #ffffff;
        border: 1px solid #ead7d7;
        color: #8B0000;
        box-shadow: 0 8px 20px rgba(16,24,28,0.06);
        transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
    }
    .feedback-more svg { width:18px; height:18px; stroke-width:2; }
    .feedback-more:hover {
        transform: translateX(2px);
        background: #fff7f7;
        box-shadow: 0 14px 28px rgba(16,24,28,0.10);
    }
    
    .comments-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:18px; margin-top:18px; }
    .comment-card { background:#ffffff; padding:18px 22px; min-height:116px; border-radius:8px; box-shadow:0 8px 28px rgba(16,24,28,0.08); display:grid; grid-template-columns:42px minmax(0, 1fr); grid-template-areas:"avatar author" "avatar body"; gap:10px 12px; align-items:start; transition:transform 220ms ease; position: relative; }
    .comment-card::before {
        content: "“";
        position: absolute;
        left: 20px;
        top: 10px;
        color: #8B0000;
        font-size: 48px;
        line-height: 1;
        font-weight: 950;
    }
    .comment-card:hover { transform:translateY(-8px); box-shadow:0 20px 48px rgba(16,24,28,0.14); }
    
    .comment-author {
        display: flex;
        align-items: center;
        gap: 12px;
        padding-left: 0;
    }
    .avatar { grid-area: avatar; width:42px; height:42px; border-radius:50%; flex:0 0 42px; overflow:hidden; fill: #cbd5e0; margin-top:18px; }
    .comment-body { display:contents; }
    .comment-body h4 { grid-area: author; margin:18px 0 0; font-size:13px; color: #8B0000; }
    .comment-meta { display:block; color:#334155; font-size:12px; font-weight: 500; margin-top:2px; }
    .comment-card > p { margin:28px 0 0; color:#334155; line-height:1.5; font-size: 13px; padding-left: 38px; }
    .comment-body p { grid-area: body; margin:0 0 8px; color:#334155; line-height:1.5; font-size: 13px; }
    .comment-footer { display:none !important; }
    .comment-chip { background:rgba(15,27,38,0.04); padding:6px 10px; border-radius:999px; font-size:13px; color:#334; }

    /* --- ABOUT / FEEDBACKS REDESIGN --- */
    #about {
        position: relative;
        isolation: isolate;
        width: 100%;
        max-width: none;
        margin: 0;
        padding: 58px max(24px, calc((100vw - 1050px) / 2)) 52px;
        overflow: hidden;
        color: #ffffff;
        background: linear-gradient(135deg, #500718 0%, #710d25 48%, #9d1d32 100%);
    }
    #about::before {
        content: "";
        position: absolute;
        inset: 0;
        z-index: -1;
        background: url('{{ asset("images/announcement-bg.png") }}') center / cover no-repeat;
        opacity: .24;
        pointer-events: none;
    }
    .about-heading,
    .feedback-heading {
        text-align: center;
    }
    .about-heading-kicker,
    .feedback-heading-kicker {
        display: block;
        margin-bottom: 7px;
        color: #facc15;
        font-size: 10px;
        font-weight: 950;
        letter-spacing: 1px;
        text-transform: uppercase;
    }
    .about-section-title,
    .comments-section h3 {
        margin: 0;
        color: #ffffff;
        font-size: 27px;
        font-weight: 950;
        line-height: 1.15;
    }
    .about-section-title::after,
    .comments-section h3::after {
        content: "";
        display: block;
        width: 28px;
        height: 2px;
        margin: 11px auto 0;
        border-radius: 999px;
        background: #facc15;
    }
    #about .why-grid {
        width: min(900px, 100%);
        margin: 34px auto 20px;
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 20px;
    }
    #about .why-item {
        position: relative;
        min-height: 210px;
        padding: 24px 22px 20px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: flex-start;
        gap: 14px;
        border: 1px solid rgba(255, 255, 255, .34);
        border-radius: 10px;
        background: linear-gradient(145deg, rgba(112, 17, 42, .62), rgba(77, 7, 27, .55));
        box-shadow: 1px 1px 0 rgba(250, 204, 21, .62), 0 18px 36px rgba(33, 0, 10, .22);
        text-align: center;
    }
    #about .why-icon {
        width: 60px;
        height: 60px;
        flex: 0 0 60px;
        border: 1px solid rgba(250, 204, 21, .82);
        background: rgba(112, 6, 31, .72);
        color: #facc15;
    }
    #about .why-icon svg {
        width: 30px;
        height: 30px;
    }
    #about .why-copy strong {
        margin: 0;
        color: #ffffff;
        font-size: 14px;
        font-weight: 950;
    }
    #about .why-copy strong::after {
        content: "";
        display: block;
        width: 24px;
        height: 2px;
        margin: 10px auto 0;
        background: #facc15;
    }
    #about .why-copy span {
        max-width: 180px;
        margin: 10px auto 0;
        color: rgba(255, 255, 255, .88);
        font-size: 12px;
        line-height: 1.55;
    }
    #about .about-learn-more {
        margin: 20px 0 24px;
    }
    #about .btn-outline,
    #about .btn-outline:hover,
    #about .btn-outline:focus-visible {
        color: #ffffff;
    }
    #about .btn-outline svg {
        color: #facc15;
    }
    #about .comments-section {
        margin: 12px calc(-1 * max(24px, calc((100vw - 1050px) / 2))) -52px;
        padding: 28px max(24px, calc((100vw - 1050px) / 2)) 42px;
        border-top: 1px solid rgba(255, 255, 255, .14);
        background: rgba(66, 4, 22, .26) !important;
    }
    #about .comments-section .section-head {
        position: relative;
        margin: 0 0 24px;
    }
    #about .feedback-more {
        position: absolute;
        right: 0;
        top: 50%;
        color: #70131b;
        transform: translateY(-50%);
    }
    #about .comments-grid {
        position: relative;
        width: min(760px, 100%);
        min-height: 150px;
        margin: 0 auto;
        display: block;
    }
    #about .comment-card {
        position: absolute;
        inset: 0;
        min-height: 126px;
        padding: 28px 30px 22px 84px;
        display: grid;
        grid-template-columns: 54px minmax(0, 1fr) auto;
        grid-template-rows: auto auto;
        grid-template-areas: "avatar author stars" "avatar body stars";
        gap: 4px 14px;
        border: 1px solid rgba(255, 255, 255, .30);
        border-radius: 10px;
        background: linear-gradient(145deg, rgba(117, 16, 43, .70), rgba(78, 6, 27, .66));
        box-shadow: 1px 1px 0 rgba(250, 204, 21, .58), 0 16px 34px rgba(33, 0, 10, .22);
        color: #ffffff;
        opacity: 0;
        visibility: hidden;
        transform: translateX(12px);
        pointer-events: none;
        transition: opacity .35s ease, transform .35s ease, visibility .35s ease;
    }
    #about .comment-card.is-active {
        opacity: 1;
        visibility: visible;
        transform: translateX(0);
        pointer-events: auto;
    }
    #about .comment-card::before {
        content: "\201C";
        left: 22px;
        top: 8px;
        color: #facc15;
        font-size: 48px;
    }
    #about .comment-card .avatar {
        grid-area: avatar;
        width: 52px;
        height: 52px;
        margin: 0;
        align-self: center;
    }
    #about .comment-body {
        display: contents;
    }
    #about .comment-body h4 {
        grid-area: author;
        margin: 3px 0 0;
        color: #ffffff;
        font-size: 13px;
    }
    #about .comment-meta {
        color: rgba(255, 255, 255, .70);
        font-size: 11px;
    }
    #about .comment-body p {
        grid-area: body;
        margin: 5px 0 0;
        color: rgba(255, 255, 255, .92);
        font-size: 12px;
    }
    .feedback-stars {
        grid-area: stars;
        align-self: start;
        color: #facc15;
        font-size: 16px;
        letter-spacing: 2px;
        white-space: nowrap;
    }
    .feedback-pagination {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        margin-top: 14px;
    }
    .feedback-dot {
        width: 6px;
        height: 6px;
        padding: 0;
        border: 0;
        border-radius: 999px;
        background: rgba(255, 255, 255, .30);
        cursor: pointer;
    }
    .feedback-dot.is-active {
        width: 14px;
        background: #facc15;
    }
    @media (max-width: 760px) {
        #about {
            padding: 44px 18px 40px;
        }
        #about .why-grid {
            grid-template-columns: 1fr;
            width: min(360px, 100%);
        }
        #about .comments-section {
            margin: 10px -18px -40px;
            padding: 28px 18px 36px;
        }
        #about .comment-card {
            padding: 28px 20px 20px 58px;
            grid-template-columns: 48px minmax(0, 1fr);
            grid-template-areas: "avatar author" "avatar body" "stars stars";
        }
        .feedback-stars {
            margin-top: 8px;
        }
        .home-footer-signature {
            gap: 10px;
            font-size: 13px;
        }
        .home-footer-signature__version {
            font-size: 12px;
        }
    }

    /* --- FOOTER STYLES --- */
    .site-footer {
        position: relative;
        overflow: hidden;
        color:#dbe4ee;
        padding: 66px 0 0;
        font-size: 15px;
        margin-top: -62px;
        background:
            radial-gradient(circle at 14% 0%, rgba(190, 18, 60, .18), transparent 270px),
            radial-gradient(circle at 76% 6%, rgba(250, 204, 21, .10), transparent 220px),
            linear-gradient(180deg, rgba(8, 17, 32, .96) 0%, rgba(8, 17, 32, .98) 36%, rgba(13, 20, 37, .98) 100%),
            url('{{ asset("images/PUPBG.jpg") }}') center bottom / cover no-repeat;
    }
    .site-footer::before {
        content: "";
        position: absolute;
        inset: 0;
        background:
            linear-gradient(180deg, rgba(6, 12, 24, .30), rgba(6, 12, 24, .78)),
            repeating-linear-gradient(160deg, transparent 0 8px, rgba(139,0,0,.20) 8px 9px, transparent 9px 18px);
        opacity: .58;
        pointer-events: none;
    }
    .site-footer::after {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 184px;
        z-index: 0;
        background: linear-gradient(180deg, rgba(8, 17, 32, 0) 0%, rgba(8, 17, 32, .52) 42%, rgba(8, 17, 32, 0) 100%);
        pointer-events: none;
    }
    .footer-top,
    .footer-bottom {
        position: relative;
        z-index: 1;
    }
    .footer-grid {
        display:grid;
        grid-template-columns:1.16fr 1fr 1fr 1fr;
        gap:16px;
        align-items:stretch;
        padding-bottom:34px;
    }
    .footer-col {
        min-height: 296px;
        padding: 28px 28px;
        border-radius: 12px;
        border: 1px solid rgba(255,255,255,.18);
        background:
            linear-gradient(145deg, rgba(255,255,255,.075), rgba(255,255,255,.025)),
            rgba(10, 18, 35, .58);
        box-shadow:
            inset 0 1px 0 rgba(255,255,255,.08),
            0 20px 46px rgba(0,0,0,.28);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        transition: border-color .22s ease, box-shadow .22s ease, transform .22s ease;
    }
    .footer-col:hover,
    .footer-col:focus-within {
        border-color: rgba(250, 204, 21, .46);
        box-shadow:
            inset 0 1px 0 rgba(255,255,255,.10),
            0 22px 48px rgba(0,0,0,.30),
            0 0 0 1px rgba(250, 204, 21, .10);
    }
    .brand { display:flex; align-items:center; gap:12px; margin-bottom:24px; transform: translateY(-9px); transition: opacity .28s ease, transform .28s ease; }
    .brand-logo img { width:70px; height:70px; border-radius:50%; object-fit:cover; border:1px solid rgba(250,204,21,.42); box-shadow:0 0 16px rgba(250,204,21,.12); }
    .brand-name { font-weight:900; color:#fff; font-size:16px; line-height:1.05; }
    .brand-sub { display:block; font-size:14px; color:#facc15; font-weight:800; margin-top:3px; }
    .brand::after,
    .footer-col h4::after {
        content:"";
        display:block;
        width:44px;
        height:2px;
        border-radius:999px;
        background:linear-gradient(90deg, transparent, #facc15, transparent);
        box-shadow:0 0 12px rgba(250,204,21,.65);
    }
    .brand::after { position:absolute; left:110px; top:58px; transition: opacity .28s ease, transform .28s ease; }
    .footer-brand {
        position: relative;
        min-height: 332px;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        border-radius: 18px;
    }
    .footer-brand::before {
        content: "";
        position: absolute;
        inset: 0;
        z-index: 0;
        border-radius: inherit;
        background:
            radial-gradient(circle at 46% 0%, rgba(250, 204, 21, .72), transparent 3px),
            radial-gradient(circle at 47% 100%, rgba(250, 204, 21, .72), transparent 3px),
            radial-gradient(circle at 95% 8%, rgba(250, 204, 21, .12), transparent 90px),
            linear-gradient(180deg, rgba(255, 255, 255, .035), rgba(255, 255, 255, 0));
        opacity: 0;
        transition: opacity .34s ease;
        pointer-events: none;
    }
    .footer-brand::after {
        content: "";
        position: absolute;
        right: 18px;
        top: 22px;
        width: 72px;
        height: 96px;
        z-index: 0;
        opacity: 0;
        background-image: radial-gradient(circle, rgba(250, 204, 21, .20) 1px, transparent 1.5px);
        background-size: 8px 8px;
        transition: opacity .34s ease;
        pointer-events: none;
    }
    .footer-brand:hover,
    .footer-brand:focus-within {
        border-color: rgba(250, 204, 21, .76);
        box-shadow:
            inset 0 1px 0 rgba(255,255,255,.10),
            0 24px 54px rgba(0,0,0,.34),
            0 0 0 1px rgba(250, 204, 21, .18);
    }
    .footer-brand:hover::before,
    .footer-brand:focus-within::before,
    .footer-brand:hover::after,
    .footer-brand:focus-within::after {
        opacity: 1;
    }
    .footer-brand > * {
        position: relative;
        z-index: 1;
    }
    .brand-desc { color:#c6d1dc; max-width:190px; line-height:1.8; margin:0 0 22px; transition: opacity .28s ease, transform .28s ease; }
    .footer-brand-alt {
        position: absolute;
        left: 28px;
        right: 28px;
        top: 31px;
        bottom: 108px;
        z-index: 2;
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        justify-content: space-between;
        gap: 20px;
        opacity: 0;
        visibility: hidden;
        transform: translateY(10px);
        transition: opacity .32s ease, transform .32s ease, visibility .32s ease;
        pointer-events: none;
    }
    .footer-brand-alt::before {
        content: "BAYAN";
        position: absolute;
        left: -32px;
        top: 42px;
        z-index: -1;
        color: transparent;
        -webkit-text-stroke: 1px rgba(255, 255, 255, .055);
        font-family: Georgia, 'Times New Roman', serif;
        font-size: 126px;
        font-weight: 900;
        letter-spacing: .08em;
        line-height: .78;
        opacity: .9;
        pointer-events: none;
    }
    .footer-brand-alt__kicker {
        display: flex;
        align-items: center;
        gap: 10px;
        color: rgba(255, 255, 255, .72);
        font-size: 8px;
        font-weight: 800;
        letter-spacing: .18em;
        line-height: 1.2;
        text-transform: uppercase;
        white-space: nowrap;
    }
    .footer-brand-alt__mark {
        width: 26px;
        height: 26px;
        flex: 0 0 26px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid rgba(250, 204, 21, .38);
        border-radius: 50%;
        overflow: hidden;
        background: rgba(255, 255, 255, .94);
        box-shadow: 0 0 16px rgba(250, 204, 21, .16);
    }
    .footer-brand-alt__mark img {
        width: 100%;
        height: 100%;
        display: block;
        object-fit: cover;
    }
    .footer-brand-alt__rule {
        width: 1px;
        height: 24px;
        background: rgba(250, 204, 21, .55);
    }
    .footer-brand-alt__message {
        display: grid;
        gap: 4px;
        color: rgba(255, 255, 255, .94);
        line-height: 1;
    }
    .footer-brand-alt__message em {
        display: block;
        font-family: Georgia, 'Times New Roman', serif;
        font-size: 32px;
        font-weight: 500;
        line-height: 1.1;
        opacity: 0;
        transform: translateY(16px) scale(.97);
    }
    .footer-brand-alt__message strong {
        position: relative;
        display: block;
        width: max-content;
        max-width: 100%;
        color: #facc15;
        font-family: 'Arial Narrow', 'Roboto Condensed', Impact, sans-serif;
        font-size: 32px;
        font-weight: 1000;
        letter-spacing: .01em;
        line-height: .95;
        text-transform: uppercase;
        white-space: nowrap;
        opacity: 0;
        transform: translateY(18px) scale(.97);
        overflow: hidden;
    }
    .footer-brand-alt__message strong::after {
        content: attr(data-sweep-text);
        position: absolute;
        inset: 0;
        color: transparent;
        background: linear-gradient(100deg, transparent 0%, transparent 42%, rgba(255, 247, 188, .98) 50%, transparent 58%, transparent 100%);
        background-repeat: no-repeat;
        background-size: 65% 100%;
        background-position: -95% 0;
        -webkit-background-clip: text;
        background-clip: text;
        white-space: nowrap;
        opacity: 0;
        pointer-events: none;
    }
    .footer-brand-alt__spark {
        align-self: center;
        width: min(180px, 82%);
        height: 1px;
        display: block;
        background: linear-gradient(90deg, transparent, rgba(250, 204, 21, .58), transparent);
    }
    .footer-brand-alt__spark::after {
        content: "";
        width: 13px;
        height: 13px;
        display: block;
        margin: -6px auto 0;
        background: #facc15;
        clip-path: polygon(50% 0, 62% 38%, 100% 50%, 62% 62%, 50% 100%, 38% 62%, 0 50%, 38% 38%);
        box-shadow: 0 0 18px rgba(250, 204, 21, .65);
    }
    .footer-brand:hover .brand,
    .footer-brand:focus-within .brand,
    .footer-brand:hover .brand-desc,
    .footer-brand:focus-within .brand-desc,
    .footer-brand:hover .brand::after,
    .footer-brand:focus-within .brand::after {
        opacity: 0;
        transform: translateY(-19px);
    }
    .footer-brand:hover .footer-brand-alt,
    .footer-brand:focus-within .footer-brand-alt {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }
    .footer-brand:hover .footer-brand-alt__message em,
    .footer-brand:focus-within .footer-brand-alt__message em {
        animation: footerBrandTextPopup .52s cubic-bezier(.22, 1, .36, 1) .08s both;
    }
    .footer-brand:hover .footer-brand-alt__message strong,
    .footer-brand:focus-within .footer-brand-alt__message strong {
        animation: footerBrandTextPopup .58s cubic-bezier(.22, 1, .36, 1) .34s both;
    }
    .footer-brand:hover .footer-brand-alt__message strong::after,
    .footer-brand:focus-within .footer-brand-alt__message strong::after {
        animation: footerBrandTextSweep 1.18s ease .9s both;
    }
    @keyframes footerBrandTextPopup {
        0% {
            opacity: 0;
            transform: translateY(18px) scale(.96);
        }
        100% {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }
    @keyframes footerBrandTextSweep {
        0% {
            opacity: 0;
            background-position: -95% 0;
        }
        18% {
            opacity: 1;
        }
        82% {
            opacity: 1;
        }
        100% {
            opacity: 0;
            background-position: 195% 0;
        }
    }
    .social { display:flex; align-items:center; gap:14px; margin-top:auto; padding-top:22px; border-top:1px solid rgba(255,255,255,.08); }
    .social-link { width:46px; height:46px; border-radius:999px; display:inline-flex; align-items:center; justify-content:center; border:1px solid rgba(250,204,21,.30); background:rgba(139,0,0,.18); color:#facc15; transition:background 0.2s ease, transform 0.2s ease, border-color 0.2s ease; }
    .social-link:hover { background:rgba(139,0,0,.32); border-color:rgba(250,204,21,.62); transform:translateY(-2px); }
    .social-link svg { width:20px; height:20px; stroke:currentColor; fill:none; stroke-width:1.8; }
    .footer-card-version {
        margin-left: auto;
        color: rgba(255, 255, 255, .76);
        font-size: 11px;
        font-weight: 400;
        letter-spacing: 0;
        white-space: nowrap;
    }

    .site-footer h4 {
        position: relative;
        display:flex;
        align-items:center;
        gap:12px;
        color:#fff;
        margin:0 0 24px;
        font-size:16px;
        font-weight:900;
    }
    .footer-heading-icon {
        width:42px;
        height:42px;
        flex:0 0 42px;
        border-radius:999px;
        background:rgba(139,0,0,.42);
        border:1px solid rgba(250,204,21,.18);
        box-shadow:inset 0 1px 0 rgba(255,255,255,.10);
        color:#ff8a8a;
        display:inline-flex;
        align-items:center;
        justify-content:center;
    }
    .footer-heading-icon svg {
        width:20px;
        height:20px;
        stroke-width:1.8;
    }
    .site-footer h4::after {
        position:absolute;
        left: 4px;
        top: 54px;
    }
    .footer-links { list-style:none; padding:0; margin:0; }
    .footer-links li { margin:0; color:#d1d9e3; border-bottom:1px solid rgba(255,255,255,.07); }
    .footer-links li:last-child { border-bottom:0; }
    .footer-links a,
    .footer-service-item {
        min-height:51px;
        color:#d1d9e3;
        text-decoration:none;
        display:flex;
        align-items:center;
        gap:13px;
    }
    .footer-service-item::after {
        content: "›";
        margin-left:auto;
        color:#facc15;
        font-size:20px;
        line-height:1;
    }
    .footer-service-icon {
        width:18px;
        height:18px;
        flex:0 0 18px;
        color:#ff8a8a;
    }
    .footer-service-item::after {
        content: none;
    }
    .footer-links a::after {
        content: "›";
        margin-left:auto;
        color:#facc15;
        font-size:20px;
        line-height:1;
    }
    .footer-links a:hover { color:#fff; text-decoration:none; }
    .footer-link-icon { width:18px; height:18px; flex:0 0 auto; stroke:#ff8a8a; stroke-width:1.8; }

    .contact-list { list-style:none; padding:0; margin:0; }
    .contact-list li { display:flex; align-items:flex-start; gap:14px; color:#d1d9e3; margin:0; padding:19px 0; line-height:1.55; border-bottom:1px solid rgba(255,255,255,.07); }
    .contact-list li:last-child { border-bottom:0; }
    .contact-icon { width:20px; height:20px; stroke:#ff8a8a; fill:none; stroke-width:1.8; flex:0 0 20px; margin-top:3px; }
    .footer-bottom {
        border-top:0;
        border-bottom: 0;
        padding:13px 0;
        text-align:center;
        color:rgba(255,255,255,0.72);
        font-size:14px;
        margin-top:0;
        background: rgba(3, 10, 23, .46);
    }
    .site-footer {
        padding-bottom: 0 !important;
        margin-bottom: 0 !important;
    }
    .footer-bottom svg {
        width: 14px;
        height: 14px;
        vertical-align: -2px;
        fill: currentColor;
    }
    .home-footer-signature {
        min-height: 28px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 15px;
        color: rgba(255, 255, 255, .92);
        line-height: 1.4;
    }
    .home-footer-signature em {
        font-weight: 500;
    }
    .home-footer-signature strong {
        color: #facc15;
        font-weight: 900;
    }
    .home-footer-signature__separator {
        width: 1px;
        height: 20px;
        flex: 0 0 auto;
        background: rgba(255, 255, 255, .38);
    }
    .home-footer-signature__version {
        color: rgba(255, 255, 255, .78);
        font-size: 13px;
        font-weight: 400;
        letter-spacing: 0;
    }
    .about-learn-more {
        position: relative;
        display: flex;
        justify-content: center;
    }
    .learn-more-bubble {
        position: absolute;
        left: 50%;
        bottom: calc(100% + 16px);
        z-index: 8;
        width: min(430px, calc(100vw - 40px));
        padding: 16px 17px;
        border: 1px solid rgba(250, 204, 21, .54);
        border-radius: 12px;
        background: rgba(62, 5, 20, .94);
        color: #fffaf7;
        box-shadow: 0 20px 44px rgba(23, 0, 8, .36), 0 0 0 1px rgba(255, 255, 255, .08);
        opacity: 0;
        visibility: hidden;
        transform: translate(-50%, 10px) scale(.97);
        transition: opacity .24s ease, transform .28s cubic-bezier(.22, 1, .36, 1), visibility .24s ease;
        pointer-events: none;
        text-align: left;
    }
    .learn-more-bubble::after {
        content: "";
        position: absolute;
        left: 50%;
        bottom: -7px;
        width: 13px;
        height: 13px;
        border-right: 1px solid rgba(250, 204, 21, .54);
        border-bottom: 1px solid rgba(250, 204, 21, .54);
        background: rgba(62, 5, 20, .94);
        transform: translateX(-50%) rotate(45deg);
    }
    .about-learn-more .btn-outline:hover + .learn-more-bubble,
    .about-learn-more .btn-outline:focus-visible + .learn-more-bubble {
        opacity: 1;
        visibility: visible;
        transform: translate(-50%, 0) scale(1);
    }
    .learn-more-bubble strong {
        display: block;
        margin-bottom: 7px;
        color: #facc15;
        font-size: 13px;
        font-weight: 900;
    }
    .learn-more-bubble span {
        display: block;
        color: rgba(255, 250, 247, .88);
        font-size: 12px;
        line-height: 1.55;
    }
    .learn-more-bubble span + span {
        margin-top: 8px;
        padding-top: 8px;
        border-top: 1px solid rgba(250, 204, 21, .16);
    }

   
    @keyframes slideUp {
        from { opacity: 0; transform: translateY(30px); }
        to { opacity: 1; transform: translateY(0); }
    }

    html.home-reveal-enabled .home-scroll-reveal {
        opacity: 0;
        filter: blur(3px);
        transition:
            opacity 560ms cubic-bezier(.22, 1, .36, 1) var(--home-reveal-delay, 0ms),
            filter 560ms cubic-bezier(.22, 1, .36, 1) var(--home-reveal-delay, 0ms);
        will-change: opacity, filter;
    }

    html.home-reveal-enabled .home-scroll-reveal[data-reveal-motion="up"] {
        translate: 0 18px;
        transition:
            opacity 560ms cubic-bezier(.22, 1, .36, 1) var(--home-reveal-delay, 0ms),
            translate 560ms cubic-bezier(.22, 1, .36, 1) var(--home-reveal-delay, 0ms),
            filter 560ms cubic-bezier(.22, 1, .36, 1) var(--home-reveal-delay, 0ms);
        will-change: opacity, translate, filter;
    }

    html.home-reveal-enabled .home-scroll-reveal[data-reveal-motion="up"][data-reveal-direction="up"] {
        translate: 0 -18px;
    }

    html.home-reveal-enabled .home-scroll-reveal.is-visible {
        opacity: 1;
        filter: blur(0);
    }

    html.home-reveal-enabled .home-scroll-reveal.is-visible[data-reveal-motion="up"] {
        translate: 0 0;
    }

    @media (prefers-reduced-motion: reduce) {
        html.home-reveal-enabled .home-scroll-reveal,
        html.home-reveal-enabled .home-scroll-reveal[data-reveal-motion="up"] {
            opacity: 1 !important;
            filter: none !important;
            transition: none !important;
        }

        html.home-reveal-enabled .home-scroll-reveal[data-reveal-motion="up"] {
            translate: none !important;
        }
    }


    @media (max-width:1200px){
        .PUPBG-inner {
            grid-template-columns: minmax(0, 590px) minmax(340px, 430px);
            gap: 34px;
        }
        .hero-actions {
            width: min(430px, 100%);
            transform: none;
        }
        .hero-action-card {
            min-height: 226px;
        }
    }
    @media (max-width:900px){
        .PUPBG { min-height: calc(100vh - 74px); }
        .PUPBG-inner {
            grid-template-columns: 1fr;
            padding: 48px 0 130px;
            text-align: center;
        }
        .hero-copy,
        .PUPBG-lead,
        .PUPBG-title { margin-left: auto; margin-right: auto; }
        .PUPBG-title::after { margin-left: auto; margin-right: auto; }
        .hero-actions { grid-template-columns: 1fr; width: min(420px, 100%); margin-left: auto; margin-right: auto; transform: none; }
        .hero-status-card { margin-left: auto; margin-right: auto; min-height: 72px; }
        .hero-action-card { min-height: 238px; }
        .phonecard::before {
            width: 300px;
            height: 92px;
            bottom: -24px;
        }
        .phone-float-icons {
            left: 10px;
            top: 14px;
            width: 128px;
            height: 122px;
        }
        .phone-float-icon {
            width: 40px;
            height: 40px;
        }
        .phone-float-icon:nth-child(2) {
            left: 56px;
            top: 42px;
        }
        .phone-float-icon:nth-child(3) {
            left: 70px;
            top: -18px;
        }
        .why-grid { grid-template-columns: 1fr; gap: 14px; }
        .why-item {
            border-right: 0;
            padding: 0;
            width: min(360px, 100%);
            margin: 0 auto;
            text-align: left;
        }
        .home-announcement-band {
            min-height: max(620px, calc(100svh - 68px));
        }
        .home-announcement-shell {
            width: calc(100% - 52px);
            height: 350px;
            transform: translateY(46px);
        }
        .home-announcement-card.is-current,
        .home-announcement-card.is-next {
            width: 244px;
            min-height: 196px;
        }
        .home-announcement-card.is-current { left: calc(50% - 128px); }
        .home-announcement-card.is-next { left: calc(50% + 128px); }
        .home-announcement-card.is-prev,
        .home-announcement-card.is-next-far,
        .home-announcement-shell.carousel-count-3 .home-announcement-card.is-prev,
        .home-announcement-shell.carousel-count-3 .home-announcement-card.is-next {
            width: 244px;
            min-height: 196px;
        }
        .home-announcement-card.is-prev,
        .home-announcement-shell.carousel-count-3 .home-announcement-card.is-prev {
            left: 0;
        }
        .home-announcement-card.is-next-far,
        .home-announcement-shell.carousel-count-3 .home-announcement-card.is-next {
            left: 100%;
        }
        .home-announcement-shell.is-static .home-announcement-card {
            width: 230px;
            min-height: 196px;
        }
        .home-announcement-shell.static-count-2 .home-announcement-card:nth-of-type(1) {
            left: calc(50% - 122px);
        }
        .home-announcement-shell.static-count-2 .home-announcement-card:nth-of-type(2) {
            left: calc(50% + 122px);
        }
        .home-announcement-shell.static-count-3 .home-announcement-card:nth-of-type(1) {
            left: calc(50% - 244px);
        }
        .home-announcement-shell.static-count-3 .home-announcement-card:nth-of-type(2) {
            left: 50%;
        }
        .home-announcement-shell.static-count-3 .home-announcement-card:nth-of-type(3) {
            left: calc(50% + 244px);
        }
        .home-announcement-shell.carousel-count-3 .home-announcement-card.is-current {
            left: 50%;
            width: 244px;
            min-height: 196px;
        }
        .announcement-nav.prev { left: -10px; }
        .announcement-nav.next { right: -10px; }
        .comments-grid { grid-template-columns:repeat(2,1fr); }
        .footer-grid { grid-template-columns:repeat(2,1fr); }
        .PUPBG-title { font-size:36px; max-width: 100%; }
        .hero-title-rotating-line { white-space: normal; }
    }
    @media (max-width:680px){
        .home-announcement-band {
            min-height: max(620px, calc(100svh - 64px));
            margin-bottom: 0;
        }
        .home-announcement-shell {
            width: calc(100% - 28px);
            height: 326px;
            transform: translateY(8px);
        }
        .home-announcement-heading {
            top: 54px;
            font-size: 17px;
        }
        .home-announcement-card,
        .home-announcement-card.is-current {
            left: 50%;
            width: min(330px, calc(100% - 54px));
            min-height: 220px;
        }
        .home-announcement-card.is-current {
            opacity: 1;
            visibility: visible;
            transform: translate(-50%, -50%) scale(1);
            pointer-events: auto;
        }
        .home-announcement-card.is-next,
        .home-announcement-card.is-prev,
        .home-announcement-card.is-next-far {
            left: 50%;
            opacity: 0;
            visibility: hidden;
            transform: translate(-50%, -50%) scale(.88);
            pointer-events: none;
        }
        .home-announcement-shell.is-static {
            display: flex;
            align-items: center;
            gap: 14px;
            height: 326px;
            padding: 64px 28px 22px;
            overflow-x: auto;
            scroll-snap-type: x mandatory;
            transform: none;
            scrollbar-width: none;
        }
        .home-announcement-shell.is-static::-webkit-scrollbar {
            display: none;
        }
        .home-announcement-shell.is-static .home-announcement-card {
            position: relative;
            top: auto;
            left: auto !important;
            flex: 0 0 min(300px, calc(100vw - 86px));
            width: min(300px, calc(100vw - 86px));
            min-height: 220px;
            opacity: 1;
            visibility: visible;
            transform: none;
            pointer-events: auto;
            scroll-snap-align: center;
        }
        .announcement-nav {
            width: 36px;
            height: 36px;
        }
        .announcement-nav.prev { left: 0; }
        .announcement-nav.next { right: 0; }
        .announcement-pagination { bottom: 0; }
    }
    @media (max-width:600px){
        .comments-grid { grid-template-columns:1fr; }
        .footer-grid { grid-template-columns:1fr; }
    }

    /* --- CONTINUOUS ANNOUNCEMENTS / ABOUT EXPERIENCE --- */
    .hero-curve {
        bottom: -2px;
        height: 0;
        background: transparent;
    }
    html[data-theme="dark"] .hero-curve {
        background: transparent;
    }
    .student-home-info-bg {
        position: relative;
        margin-top: -72px;
        padding-top: 72px;
        isolation: isolate;
        background:
            radial-gradient(circle at 91% 11%, rgba(250, 204, 21, .16), transparent 190px),
            radial-gradient(circle at 54% 87%, rgba(250, 204, 21, .13), transparent 220px),
            linear-gradient(90deg, rgba(72, 3, 12, .96) 0%, rgba(112, 19, 27, .88) 45%, rgba(112, 19, 27, .58) 100%),
            linear-gradient(180deg, rgba(28, 4, 10, .16), rgba(28, 4, 10, .34)),
            url('{{ asset("images/PUPBG.jpg") }}') center center / cover fixed no-repeat;
    }
    html[data-theme="dark"] .student-home-info-bg {
        margin-top: -72px;
        padding-top: 72px;
        background:
            radial-gradient(circle at 91% 11%, rgba(250, 204, 21, .12), transparent 190px),
            radial-gradient(circle at 54% 87%, rgba(250, 204, 21, .09), transparent 220px),
            linear-gradient(90deg, rgba(3, 10, 24, .97) 0%, rgba(8, 24, 43, .9) 48%, rgba(12, 34, 55, .68) 100%),
            linear-gradient(180deg, rgba(2, 6, 18, .2), rgba(2, 6, 18, .42)),
            url('{{ asset("images/PUPBG.jpg") }}') center center / cover fixed no-repeat;
    }
    .student-home-info-bg::before,
    html[data-theme="dark"] .student-home-info-bg::before {
        content: none;
    }
    .about-panel { padding: 0; }
    .home-announcement-band,
    html[data-theme="dark"] .home-announcement-band {
        margin: 0;
        background: transparent;
        box-shadow: none;
    }
    .home-announcement-band::before { content: none; }
    .home-announcement-band + #about { padding-top: 52px; }

    #about.about-experience {
        position: relative;
        width: 100%;
        max-width: none;
        padding: 52px 24px 96px;
        color: #fffaf7;
        background: linear-gradient(
            180deg,
            transparent 0%,
            transparent calc(100% - 238px),
            rgba(120, 13, 38, .58) calc(100% - 188px),
            rgba(71, 7, 26, .70) calc(100% - 126px),
            rgba(20, 16, 32, .86) calc(100% - 54px),
            rgba(8, 17, 32, .96) 100%
        );
        border-top: 0;
        box-shadow: none;
    }
    html[data-theme="dark"] #about.about-experience {
        background: linear-gradient(
            180deg,
            transparent 0%,
            transparent calc(100% - 238px),
            rgba(8, 24, 43, .42) calc(100% - 188px),
            rgba(5, 13, 27, .62) calc(100% - 126px),
            rgba(5, 13, 27, .84) calc(100% - 54px),
            rgba(8, 17, 32, .96) 100%
        );
    }
    #about.about-experience::before {
        content: "";
        position: absolute;
        top: 0;
        left: 50%;
        width: min(1100px, calc(100% - 48px));
        height: 1px;
        transform: translateX(-50%);
        background: linear-gradient(90deg, transparent, rgba(250, 204, 21, .55) 14%, rgba(250, 204, 21, .55) 86%, transparent);
        pointer-events: none;
        z-index: 2;
    }
    .about-decor {
        position: absolute;
        inset: 0;
        z-index: 0;
        overflow: hidden;
        pointer-events: none;
    }
    .about-decor::before {
        content: "CARE";
        position: absolute;
        top: 12px;
        left: max(22px, calc((100vw - 1100px) / 2 - 20px));
        color: transparent;
        -webkit-text-stroke: 1px rgba(255, 255, 255, .055);
        font-family: Georgia, 'Times New Roman', serif;
        font-size: clamp(92px, 15vw, 190px);
        font-weight: 900;
        letter-spacing: .08em;
        line-height: 1;
        opacity: .72;
        white-space: nowrap;
    }
    .about-decor__word {
        position: absolute;
        top: 86px;
        left: max(64px, calc((100vw - 1100px) / 2 + 46px));
        color: transparent;
        -webkit-text-stroke: 1px rgba(255, 255, 255, .04);
        font-family: Georgia, 'Times New Roman', serif;
        font-size: clamp(84px, 13vw, 168px);
        font-weight: 900;
        letter-spacing: .08em;
        line-height: 1;
        opacity: .46;
        white-space: nowrap;
    }
    .about-decor::after {
        content: "";
        position: absolute;
        inset: 0;
        background:
            radial-gradient(circle at 50% 44%, rgba(250, 204, 21, .13), transparent 130px),
            radial-gradient(circle at 9% 32%, rgba(250, 204, 21, .82) 0 2px, transparent 3px),
            radial-gradient(circle at 87% 26%, rgba(250, 204, 21, .78) 0 2px, transparent 3px),
            radial-gradient(circle at 95% 58%, rgba(250, 204, 21, .72) 0 2px, transparent 3px),
            radial-gradient(circle at 4% 82%, rgba(250, 204, 21, .64) 0 1px, transparent 2px),
            radial-gradient(circle at 18% 79%, rgba(250, 204, 21, .58) 0 1px, transparent 2px),
            radial-gradient(circle at 78% 73%, rgba(250, 204, 21, .58) 0 1px, transparent 2px),
            radial-gradient(circle, rgba(250, 204, 21, .28) 0 1px, transparent 1.6px) left 26px top 28px / 11px 11px no-repeat,
            radial-gradient(circle, rgba(250, 204, 21, .24) 0 1px, transparent 1.6px) right 40px bottom 96px / 11px 11px no-repeat,
            radial-gradient(circle, rgba(250, 204, 21, .18) 0 1px, transparent 1.6px) left 11% bottom 48px / 10px 10px no-repeat;
        opacity: .78;
    }
    .about-decor__pulse,
    .about-decor__arc,
    .about-decor__plus,
    .about-decor__word {
        position: absolute;
        display: block;
    }
    .about-decor__pulse {
        width: 188px;
        height: 46px;
        opacity: .42;
        background:
            linear-gradient(90deg, transparent 0 6%, rgba(250, 204, 21, .42) 6% 38%, transparent 38% 100%) center / 100% 1px no-repeat;
    }
    .about-decor__pulse::before {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(90deg, transparent 0 8%, rgba(250, 204, 21, .60) 8% 9%, transparent 9% 18%, rgba(250, 204, 21, .38) 18% 20%, transparent 20% 28%, rgba(250, 204, 21, .55) 28% 30%, transparent 30% 100%);
        clip-path: polygon(0 50%, 22% 50%, 28% 50%, 33% 18%, 39% 82%, 45% 50%, 100% 50%, 100% 52%, 45% 52%, 39% 84%, 33% 20%, 28% 52%, 0 52%);
    }
    .about-decor__pulse--left {
        left: 28px;
        top: 38%;
    }
    .about-decor__pulse--right {
        right: 26px;
        top: 36%;
        transform: scaleX(-1);
    }
    .about-decor__pulse--lower-left {
        left: 8%;
        bottom: 26%;
        width: 150px;
        opacity: .24;
    }
    .about-decor__pulse--lower-right {
        right: 4%;
        bottom: 16%;
        width: 160px;
        opacity: .26;
        transform: scaleX(-1);
    }
    .about-decor__pulse--center {
        left: 50%;
        bottom: 31%;
        width: min(360px, 36vw);
        height: 38px;
        opacity: .14;
        transform: translateX(-50%);
    }
    .about-decor__arc {
        width: 220px;
        height: 220px;
        border: 1px solid rgba(250, 204, 21, .16);
        border-radius: 50%;
        opacity: .5;
    }
    .about-decor__arc::before,
    .about-decor__arc::after {
        content: "";
        position: absolute;
        inset: 14px;
        border: 1px solid rgba(250, 204, 21, .10);
        border-radius: inherit;
    }
    .about-decor__arc::after {
        inset: 28px;
    }
    .about-decor__arc--left {
        left: -110px;
        bottom: 18px;
    }
    .about-decor__arc--right {
        right: -96px;
        top: -82px;
    }
    .about-decor__plus {
        width: 20px;
        height: 20px;
        opacity: .22;
    }
    .about-decor__plus::before,
    .about-decor__plus::after {
        content: "";
        position: absolute;
        background: rgba(250, 204, 21, .72);
        border-radius: 999px;
    }
    .about-decor__plus::before {
        left: 8px;
        top: 0;
        width: 4px;
        height: 20px;
    }
    .about-decor__plus::after {
        left: 0;
        top: 8px;
        width: 20px;
        height: 4px;
    }
    .about-decor__plus--one {
        left: 9%;
        top: 30%;
    }
    .about-decor__plus--two {
        right: 10%;
        top: 42%;
    }
    .about-decor__plus--three {
        left: 14%;
        bottom: 20%;
        transform: scale(.72);
        opacity: .15;
    }
    .about-decor__plus--four {
        right: 14%;
        bottom: 30%;
        transform: scale(.64);
        opacity: .15;
    }
    #about .about-heading,
    #about .why-grid,
    #about .about-learn-more,
    #about .comments-section {
        position: relative;
        z-index: 1;
    }
    #about,
    #about * { box-sizing: border-box; }
    .about-heading,
    .feedback-heading { text-align: center; }
    .about-kicker,
    .feedback-kicker {
        display: block;
        margin-bottom: 7px;
        color: #facc15;
        font-size: 11px;
        font-weight: 950;
        letter-spacing: .08em;
        line-height: 1.2;
        text-transform: uppercase;
    }
    #about .about-section-title,
    #about .comments-section h3 {
        margin: 0;
        color: #fffaf7;
        font-size: clamp(24px, 2.5vw, 31px);
        font-weight: 900;
        letter-spacing: 0;
        line-height: 1.12;
    }
    #about .about-section-title::after,
    #about .comments-section h3::after {
        content: "";
        display: block;
        width: 36px;
        height: 2px;
        margin: 11px auto 0;
        border-radius: 999px;
        background: #facc15;
    }
    #about .why-grid {
        width: min(880px, 100%);
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 18px;
        align-items: stretch;
        margin: 28px auto 18px;
    }
    #about .why-item {
        box-sizing: border-box;
        height: 100%;
        min-height: 190px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0;
        padding: 24px 22px;
        border: 1px solid rgba(128, 0, 32, .42);
        border-radius: 17px;
        background: transparent;
        text-align: center;
        box-shadow: 12px 17px 51px rgba(0, 0, 0, .22);
        backdrop-filter: blur(6px);
        -webkit-backdrop-filter: blur(6px);
        cursor: pointer;
        transition: all .5s;
        user-select: none;
        transform-origin: center;
    }
    #about .why-item:last-child { border-right: 1px solid rgba(128, 0, 32, .42); }
    #about .why-item:hover {
        border-color: rgba(250, 204, 21, .54);
        transform: scale(1.05);
    }
    #about .why-item:active {
        transform: scale(.95) rotateZ(1.7deg);
    }
    #about .why-icon {
        width: 62px;
        height: 62px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid rgba(250, 204, 21, .66);
        border-radius: 50%;
        background: rgba(108, 10, 31, .62);
        color: #facc15;
    }
    #about .why-icon svg { width: 32px; height: 32px; stroke-width: 1.65; }
    #about .why-copy { display: block; width: 100%; }
    #about .why-copy strong {
        display: block;
        margin: 14px 0 0;
        color: #fffaf7;
        font-size: 15px;
        font-weight: 850;
    }
    #about .why-copy strong::after {
        content: "";
        display: block;
        width: 30px;
        height: 2px;
        margin: 10px auto 9px;
        background: #facc15;
    }
    #about .why-copy span {
        display: block;
        color: rgba(255, 250, 247, .84);
        font-size: 12px;
        font-weight: 500;
        line-height: 1.55;
    }
    #about .about-learn-more { margin: 8px 0 26px; text-align: center; }
    #about .btn-outline,
    #about .btn-outline:visited { gap: 9px; margin: 0; color: #fffaf7; font-size: 13px; }
    #about .btn-outline svg { color: #facc15; }
    #about .btn-outline:hover,
    #about .btn-outline:focus-visible { color: #facc15; }

    #about .comments-section {
        position: relative;
        width: min(1100px, 100%);
        margin: 0 auto;
        padding: 28px 0 0;
        border-top: 0;
        background: transparent !important;
    }
    #about .comments-section::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(250, 204, 21, .55) 14%, rgba(250, 204, 21, .55) 86%, transparent);
        pointer-events: none;
        z-index: 2;
    }
    #about .comments-section .section-head { display: block; margin: 0 0 18px; text-align: center; }
    .feedback-stage {
        width: min(1060px, 100%);
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 18px;
        margin: 0 auto;
    }
    .feedback-slide {
        min-height: 190px;
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        grid-template-areas: "quote stars" "identity identity";
        gap: 10px 20px;
        padding: 22px;
        border: 1px solid rgba(255, 247, 240, .36);
        border-radius: 10px;
        background: rgba(69, 5, 23, .32);
        box-shadow: 0 16px 30px rgba(27, 0, 8, .2), 1px 1px 0 rgba(250, 204, 21, .34);
        transition: transform .32s cubic-bezier(.22, 1, .36, 1), border-color .25s ease, box-shadow .25s ease;
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
    }
    .feedback-slide:hover,
    .feedback-slide:focus-within {
        transform: translateY(-7px) scale(1.012);
        border-color: rgba(250, 204, 21, .64);
        box-shadow: 0 24px 42px rgba(27, 0, 8, .3), 1px 1px 0 rgba(250, 204, 21, .62);
    }
    .feedback-slide.is-empty {
        min-height: 150px;
        grid-column: 1 / -1;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
    }
    .feedback-quote {
        grid-area: quote;
        color: #facc15;
        font-family: Georgia, "Times New Roman", serif;
        font-size: 52px;
        font-weight: 900;
        line-height: .7;
    }
    .feedback-stars { grid-area: stars; display: flex; justify-content: flex-end; gap: 4px; color: rgba(255,255,255,.2); }
    .feedback-stars span { font-size: 18px; line-height: 1; }
    .feedback-stars span.is-filled { color: #facc15; text-shadow: 0 0 8px rgba(250,204,21,.24); }
    .feedback-identity {
        grid-area: identity;
        width: 100%;
        min-width: 0;
        display: grid;
        grid-template-columns: 56px minmax(0, 1fr);
        gap: 15px;
        align-items: center;
    }
    .feedback-identity .avatar {
        grid-area: auto !important;
        width: 56px;
        height: 56px;
        margin: 0;
        align-self: center;
        fill: #cbd5e0;
        border: 1px solid rgba(255,255,255,.16);
        border-radius: 50%;
        background: rgba(255,255,255,.1);
    }
    .feedback-copy { width: 100%; min-width: 0; }
    .feedback-copy h4 { margin: 0; color: #fffaf7; font-size: 13px; font-weight: 800; }
    .feedback-copy .comment-meta { display: block; margin-top: 1px; color: rgba(255,250,247,.68); font-size: 11px; }
    .feedback-copy p {
        width: 100%;
        margin: 7px 0 0;
        color: rgba(255,250,247,.9);
        font-size: 13px;
        line-height: 1.5;
        overflow-wrap: break-word;
    }
    .feedback-pagination,
    .feedback-dot { display: none; }

    html[data-theme="dark"] .PUPBG::before {
        background:
            radial-gradient(circle at 91% 11%, rgba(250, 204, 21, .12), transparent 170px),
            radial-gradient(circle at 54% 87%, rgba(250, 204, 21, .09), transparent 180px),
            linear-gradient(90deg, rgba(3, 10, 24, .97) 0%, rgba(8, 24, 43, .9) 48%, rgba(12, 34, 55, .68) 100%),
            linear-gradient(180deg, rgba(2, 6, 18, .2), rgba(2, 6, 18, .42)),
            url('{{ asset("images/PUPBG.jpg") }}') center center / cover fixed no-repeat;
    }
    html[data-theme="dark"] .PUPBG-overlay {
        background:
            radial-gradient(circle, rgba(255, 255, 255, .12) 0 1px, transparent 1.6px) left 42px top 50px / 11px 11px no-repeat,
            radial-gradient(circle, rgba(255, 255, 255, .1) 0 1px, transparent 1.6px) right 20px bottom 58px / 11px 11px no-repeat,
            linear-gradient(180deg, rgba(15, 23, 42, .02), rgba(2, 6, 18, .2));
    }
    html[data-theme="dark"] .hero-action-card,
    html[data-theme="dark"] .hero-status-card {
        background: rgba(7, 20, 38, .34);
        border-color: rgba(148, 163, 184, .34);
        box-shadow: inset 0 1px 0 rgba(255,255,255,.1), 0 22px 42px rgba(0,0,0,.42);
    }
    html[data-theme="dark"] .hero-action-card:hover,
    html[data-theme="dark"] .hero-action-card:focus-visible {
        border-color: rgba(250, 204, 21, .46);
        background: rgba(112, 19, 27, .42);
        box-shadow: inset 0 0 0 1px rgba(250,204,21,.10), 0 28px 52px rgba(0,0,0,.52), 0 0 36px rgba(250,204,21,.11);
    }
    html[data-theme="dark"] .home-announcement-card,
    html[data-theme="dark"] .feedback-slide {
        background: linear-gradient(145deg, rgba(12, 31, 52, .76), rgba(4, 14, 29, .82));
        border-color: rgba(148, 163, 184, .34);
    }
    html[data-theme="dark"] #about .why-item {
        background: transparent;
        border-color: rgba(128, 0, 32, .52);
    }
    html[data-theme="dark"] #about .why-item:hover {
        border-color: rgba(250, 204, 21, .54);
    }
    .PUPBG .hero-scroll,
    .PUPBG .hero-scroll:visited,
    .PUPBG .hero-scroll:hover,
    .PUPBG .hero-scroll:focus-visible,
    .PUPBG .hero-scroll span,
    .PUPBG .hero-scroll svg {
        color: #ffffff !important;
        stroke: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }

    @media (max-width: 760px) {
        #about.about-experience { padding: 44px 18px 48px; }
        .about-decor::before {
            top: 28px;
            left: -24px;
            font-size: 86px;
            opacity: .45;
        }
        .about-decor__word {
            top: 82px;
            left: 18px;
            font-size: 78px;
            opacity: .28;
        }
        .about-decor__pulse {
            width: 118px;
            opacity: .24;
        }
        .about-decor__pulse--center,
        .about-decor__pulse--lower-left,
        .about-decor__pulse--lower-right {
            opacity: .14;
        }
        .about-decor__pulse--center {
            width: 210px;
            bottom: 36%;
        }
        .about-decor__pulse--lower-left {
            left: -18px;
        }
        .about-decor__pulse--lower-right {
            right: -18px;
        }
        .about-decor__arc {
            width: 160px;
            height: 160px;
            opacity: .34;
        }
        .about-decor__arc--right {
            right: -118px;
        }
        .about-decor__arc--left {
            left: -120px;
        }
        #about .why-grid { width: min(390px, 100%); grid-template-columns: 1fr; gap: 14px; }
        #about .why-item { width: 100%; min-height: 168px; padding: 18px 22px; }
        .home-announcement-view-all {
            width: min(300px, calc(100% - 36px));
            min-width: 0;
            white-space: nowrap;
        }
        .feedback-stage {
            grid-template-columns: 1fr;
        }
        .feedback-slide {
            grid-template-columns: 1fr;
            grid-template-areas: "quote" "stars" "identity";
            gap: 10px;
            padding: 20px;
        }
        .feedback-stars { justify-content: flex-start; }
    }

    @media (min-width: 681px) and (max-width: 1040px) {
        .feedback-stage {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 680px) {
        .home-announcement-band,
        html[data-theme="dark"] .home-announcement-band {
            box-sizing: border-box;
            min-height: 610px;
            margin-top: 0;
            padding: 96px 0 94px;
            display: block;
        }
        .home-announcement-heading {
            top: 24px;
        }
        .home-announcement-heading h2 {
            font-size: 27px;
        }
        .announcement-modal {
            padding: 12px;
        }
        .announcement-modal-card {
            max-height: calc(100dvh - 24px);
        }
        .home-announcement-shell {
            width: 100%;
            height: 390px;
            margin: 0;
            transform: none;
        }
        .home-announcement-shell.is-static {
            display: block;
            height: 390px;
            padding: 0;
            overflow: hidden;
        }
        .home-announcement-card,
        .home-announcement-card.is-current,
        .home-announcement-shell.carousel-count-3 .home-announcement-card.is-current,
        .home-announcement-shell.is-static .home-announcement-card {
            position: absolute;
            top: 56px;
            left: 50%;
            width: min(330px, calc(100% - 84px));
            min-height: 220px;
        }
        .home-announcement-card.is-current,
        .home-announcement-shell.carousel-count-3 .home-announcement-card.is-current,
        .home-announcement-shell.is-static .home-announcement-card {
            transform: translateX(-50%) scale(1);
        }
        .home-announcement-card.is-next,
        .home-announcement-card.is-prev,
        .home-announcement-card.is-next-far,
        .home-announcement-shell.carousel-count-3 .home-announcement-card.is-next,
        .home-announcement-shell.carousel-count-3 .home-announcement-card.is-prev {
            left: 50%;
            opacity: 0;
            visibility: hidden;
            transform: translateX(-50%) scale(.88);
            pointer-events: none;
        }
        .announcement-nav.prev { left: 14px; }
        .announcement-nav.next { right: 14px; }
        .announcement-nav:disabled { visibility: hidden; }
        .home-announcement-shell.is-static .home-announcement-card:hover,
        .home-announcement-shell.is-static .home-announcement-card:focus-visible,
        .home-announcement-card.is-current:hover,
        .home-announcement-card.is-current:focus-visible,
        .home-announcement-shell.carousel-count-3 .home-announcement-card.is-current:hover,
        .home-announcement-shell.carousel-count-3 .home-announcement-card.is-current:focus-visible {
            transform: translateX(-50%) translateY(-4px) scale(1.025);
        }
        .home-announcement-view-all {
            bottom: 24px;
        }
    }
/*phone css*/
.phonecard {
  perspective: 1200px;
  transform-style: preserve-3d;
  position: relative;
  width: 237px;
  height: 400px;
  margin: 60px auto;
}

.phonecard::before {
  content: "";
  position: absolute;
  left: 50%;
  bottom: -34px;
  width: 390px;
  height: 118px;
  border-radius: 50%;
  background:
    radial-gradient(ellipse at center, rgba(122, 0, 25, 0.4) 0%, rgba(122, 0, 25, 0.22) 45%, rgba(122, 0, 25, 0) 70%),
    conic-gradient(from 190deg, rgba(250, 204, 21, 0), rgba(250, 204, 21, 0.8), rgba(250, 204, 21, 0.12), rgba(250, 204, 21, 0));
  opacity: 0.72;
  transform: translateX(-50%) rotate(-8deg) skewX(-18deg);
  filter: blur(0.2px);
  z-index: 0;
  pointer-events: none;
  transition: opacity 0.45s ease, transform 0.7s ease;
}

.phonecard::after {
  content: "";
  position: absolute;
  left: 50%;
  bottom: -18px;
  width: 248px;
  height: 52px;
  border-radius: 999px;
  background: radial-gradient(ellipse at center, rgba(0, 0, 0, 0.44) 0%, rgba(0, 0, 0, 0.24) 45%, rgba(0, 0, 0, 0) 74%);
  filter: blur(8px);
  opacity: 0.72;
  transform: translateX(-50%) rotate(-8deg) skewX(-18deg);
  transform-origin: center;
  z-index: 0;
  pointer-events: none;
  transition: opacity 0.45s ease, transform 0.7s ease, width 0.7s ease;
}

.phonecard:hover::before {
  opacity: 0.56;
  transform: translateX(-50%) translateY(10px) rotate(0deg) skewX(0deg) scale(0.82);
}

.phonecard:hover::after {
  width: 212px;
  opacity: 0.48;
  transform: translateX(-50%) translateY(10px) rotate(0deg) skewX(0deg);
}

.phone-float-icons {
  position: absolute;
  left: -84px;
  top: 96px;
  z-index: 12;
  width: 150px;
  height: 146px;
  transform: translateZ(90px);
  pointer-events: none;
}

.phone-float-icon {
  position: absolute;
  left: 0;
  top: 0;
  width: 46px;
  height: 46px;
  display: grid;
  place-items: center;
  border: 1px solid rgba(250, 204, 21, 0.7);
  border-radius: 14px;
  background: rgba(255, 250, 247, 0.95);
  color: #7a0019;
  box-shadow:
    10px 12px 18px rgba(31, 0, 9, 0.28),
    -3px 5px 10px rgba(31, 0, 9, 0.16),
    0 0 14px rgba(250, 204, 21, 0.28);
  isolation: isolate;
  opacity: 0;
  transform: translate(132px, 98px) scale(0.36) rotate(20deg) skewX(-5deg);
  animation:
    phoneIconPopOut 0.95s cubic-bezier(0.18, 0.92, 0.24, 1.16) 3.05s forwards,
    phoneIconFloat 3.4s ease-in-out 4s infinite;
}

.phone-float-icon::before {
  content: "";
  position: absolute;
  inset: 7px 5px 3px 8px;
  border-radius: inherit;
  background: rgba(31, 0, 9, 0.34);
  filter: blur(13px);
  transform: translate(16px, 12px) scale(0.95);
  z-index: -1;
  opacity: 0.78;
}

.phone-float-icon:nth-child(1) {
  border-color: rgba(250, 204, 21, 0.78);
  background: rgba(122, 0, 25, 0.94);
  color: #facc15;
}

.phone-float-icon:nth-child(2) {
  left: 66px;
  top: 38px;
  border-color: rgba(122, 0, 25, 0.46);
  background: rgba(250, 204, 21, 0.94);
  color: #7a0019;
  animation-delay: 3.24s, 4.18s;
}

.phone-float-icon:nth-child(3) {
  left: 82px;
  top: -22px;
  animation-delay: 3.43s, 4.36s;
}

.phonecard:hover .phone-float-icon {
  animation: phoneIconSinkIntoPhone 0.48s cubic-bezier(0.64, 0, 0.36, 1) forwards;
}

.phonecard:hover .phone-float-icon:nth-child(2) {
  animation-delay: 0.05s;
}

.phonecard:hover .phone-float-icon:nth-child(3) {
  animation-delay: 0.1s;
}

.phone-float-icon svg {
  width: 22px;
  height: 22px;
}

@keyframes phoneIconPopOut {
  0% {
    opacity: 0;
    transform: translate(132px, 98px) scale(0.36) rotate(20deg) skewX(-5deg);
  }
  55% {
    opacity: 1;
    transform: translate(-8px, -6px) scale(1.08) rotate(-20deg) skewX(-5deg);
  }
  100% {
    opacity: 1;
    transform: translate(0, 0) scale(1) rotate(-18deg) skewX(-5deg);
  }
}

@keyframes phoneIconFloat {
  0%, 100% { opacity: 1; transform: translateY(0) rotate(-18deg) skewX(-5deg); }
  50% { opacity: 1; transform: translateY(-7px) rotate(-15deg) skewX(-5deg); }
}

@keyframes phoneIconSinkIntoPhone {
  0% {
    opacity: 1;
    transform: translateY(0) scale(1) rotate(-18deg) skewX(-5deg);
  }
  100% {
    opacity: 0;
    transform: translate(132px, 98px) scale(0.35) rotate(18deg) skewX(-5deg);
  }
}

.phone {
  position: relative;
  width: 100%;
  height: 100%;
  transform-style: preserve-3d;
  transform: rotateX(55deg) rotateY(0deg) rotateZ(35deg);
  border-radius: 12px;
  box-shadow: -15px 25px 35px rgba(0, 0, 0, 0.65);
  transition: transform 1s cubic-bezier(0.2, 0.8, 0.2, 1), box-shadow 0.5s ease;
  z-index: 2;
  
}

.front {
  width: 100%;
  height: 100%;
  border: solid 2.5px rgb(0, 0, 0);
  background: radial-gradient(circle, #510515 30%, #1f0107 100%);
  padding: 1.5rem 0.5rem 1.8rem;
  border-radius: 12px;
  transform: translateZ(12px);
  z-index: 2;
  position: relative;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  gap: 12px;
  animation: neonPulse 3s infinite ease-in-out;
  transition: box-shadow 0.5s ease;
}

@keyframes neonPulse {
  0%, 100% {
    box-shadow: 
      -6px -6px 12px rgba(255, 255, 255, 0.4),
      inset 6px 6px 10px rgba(255, 255, 255, 0.35);
  }
  50% {
    box-shadow: 
      -12px -12px 25px rgba(255, 255, 255, 0.75),
      inset 10px 10px 18px rgba(255, 255, 255, 0.6);
  }
}

.phone:hover {
  transform: rotateX(0deg) rotateY(0deg) rotateZ(0deg) scale(1.3);
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.5); 
}

.phone:hover .front {
  animation: none !important;
  box-shadow: none !important;
}

.face {
  position: absolute;
  box-sizing: border-box;
  top: 0;
  left: 0;
}

.back {
  width: 100%;
  height: 100%;
  border: solid 2.5px rgb(0, 0, 0);
  background: #111113;
  border-radius: 12px !important;
  transform: rotateY(180deg) translateZ(12px);
  
}

.left {
  height: 400px;
  width: 24px;
  background: linear-gradient(to bottom, #222225, #141415);
  transform: translateX(-6px) rotateY(-90deg);
  border-top: 2px solid #000;
  border-bottom: 2px solid #000;
}

.right {
  height: 400px;
  width: 24px;
  background: linear-gradient(to bottom, #2a2a2e, #1a1a1c);
  transform: translateX(225px) rotateY(90deg);
  border-top: 2px solid #000;
  border-bottom: 2px solid #000;
}

.top {
  width: 237px;
  height: 24px;
  background: #222225;
  transform: translateY(-6px) rotateX(90deg);
  border-left: 2px solid #000;
  border-right: 2px solid #000;
}

.bottom {
  width: 237px;
  height: 24px;
  background: #141415;
  transform: translateY(388px) rotateX(-90deg);
  border-left: 2px solid #000;
  border-right: 2px solid #000;
}
.phone:hover .left,
.phone:hover .right,
.phone:hover .top,
.phone:hover .bottom {
  opacity: 0;
  transition: opacity 0.3s ease;
}

.left, .right, .top, .bottom {
  transition: opacity 0.3s ease;
}
.lock-screen {
  position: absolute;
  inset: 0;
  background: 
    linear-gradient(rgba(255, 255, 255, 0.85), rgba(255, 255, 255, 0.85)), 
    url('image_kR8D5B.png') center center / cover no-repeat;
  z-index: 4;
  display: flex;
  flex-direction: column;
  justify-content: flex-start;
  align-items: center;
  font-family: system-ui, -apple-system, sans-serif;
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  opacity: 1;
  visibility: visible;
  padding-top: 1.8rem;
}

.lock-screen::before {
  content: "";
  position: absolute;
  left: 50%;
  top: 52%;
  width: 86px;
  height: 86px;
  background: url('{{ asset("images/pup_logo.png") }}') center / contain no-repeat;
  opacity: 0.18;
  transform: translate(-50%, -50%);
  filter: drop-shadow(0 6px 12px rgba(0, 0, 0, 0.12));
  pointer-events: none;
}

.lock-wallpaper {
  display: none;
}

.lock-time {
  font-size: 24px;
  font-weight: 800;
  letter-spacing: 0.5px;
  color: #000000;
  text-shadow: 0 1px 2px rgba(255, 255, 255, 0.6);
}

.lock-date {
  font-size: 8px;
  font-weight: 600;
  opacity: 0.8;
  margin-top: 1px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: #000000;
  text-shadow: 0 1px 2px rgba(255, 255, 255, 0.6);
}

.unlock-label {
  position: absolute;
  bottom: 24px; 
  font-size: 8.5px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 1px;
  color: #333333;
  opacity: 0.8;
  animation: labelBlink 2s infinite ease-in-out;
  pointer-events: none;
}

@keyframes labelBlink {
  0%, 100% { opacity: 0.3; transform: scale(0.96); }
  50% { opacity: 0.9; transform: scale(1); }
}

.phone:hover .lock-screen {
  opacity: 0;
  visibility: hidden;
  transform: scale(0.9) translateY(-10px);
}

.phone:hover .btn-action {
  opacity: 1;
  transform: translateY(0) scale(1);
}

.head {
  font-size: 6px;
  position: absolute;
  width: calc(100% - 1rem);
  left: 0.5rem;
  top: 5px;
  height: 10px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  color: #000000; 
  z-index: 6;
  transition: color 0.4s ease;
}
.head .h-left { font-family: sans-serif; font-weight: bold; }
.head .h-right { display: flex; align-items: center; gap: 2px; }
.head .h-right span { display: inline-block; width: 8px; height: 8px; }
.logo-head { width: 100%; height: 100%; fill: currentColor; stroke: currentColor; }

.phone:hover .head {
  color: #ffffff;
}

.front-camera {
  width: 8px;
  height: 8px;
  background-color: rgb(0, 0, 0);
  border-radius: 50%;
  position: absolute;
  top: 5px;
  left: 50%;
  transform: translateX(-50%);
  border: 1px solid #333;
  z-index: 15;
}

.navigation {
  position: absolute;
  bottom: 6px;
  left: 0;
  width: 100%;
  display: flex;
  justify-content: space-around;
  align-items: center;
  padding: 0 1.2rem;
  box-sizing: border-box;
  z-index: 6;
  color: #000000; 
  transition: color 0.4s ease;
}

.navigation .btn-nav-svg {
  width: 9px;
  height: 9px;
  opacity: 0.85;
  stroke: currentColor;
}

.phone:hover .navigation {
  color: #ffffff;
}

.phone-unlocked-logo {
  position: absolute;
  top: 72px;
  left: 50%;
  z-index: 5;
  width: 58px;
  height: 58px;
  display: grid;
  place-items: center;
  border: 1px solid rgba(250, 204, 21, 0.42);
  border-radius: 999px;
  background: rgba(255, 255, 255, 0.06);
  box-shadow: 0 0 18px rgba(250, 204, 21, 0.14);
  opacity: 0;
  transform: translate(-50%, 14px) scale(0.86);
  transition: opacity 0.45s ease, transform 0.45s cubic-bezier(0.18, 0.92, 0.24, 1.16);
  pointer-events: none;
}

.phone-unlocked-logo img {
  width: 48px;
  height: 48px;
  display: block;
  object-fit: contain;
}

.phone-version-label {
  position: absolute;
  left: 50%;
  bottom: 27px;
  z-index: 5;
  color: rgba(255, 255, 255, 0.84);
  font-size: 7px;
  font-weight: 700;
  letter-spacing: 1.3px;
  text-transform: uppercase;
  opacity: 0;
  transform: translate(-50%, 8px);
  transition: opacity 0.42s ease 0.08s, transform 0.42s ease 0.08s;
  pointer-events: none;
}

.phone:hover .phone-unlocked-logo {
  opacity: 1;
  transform: translate(-50%, 0) scale(1);
}

.phone:hover .phone-version-label {
  opacity: 1;
  transform: translate(-50%, 0);
}


.btn-action {
  width: 85%;
  padding: 0.5rem 0.6rem;
  font-size: 8.5px; 
  font-weight: bold;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  box-sizing: border-box;
  cursor: pointer;
  border-radius: 4px; 
  opacity: 0;
  transform: translateY(12px) scale(0.9);
  transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 6px; 
  outline: none !important;
  filter: none;
  text-rendering: geometricPrecision;
  -webkit-font-smoothing: antialiased;
  backface-visibility: hidden;
  transform-style: flat;
}

.btn-action span,
.btn-action svg {
  filter: none;
  text-shadow: none;
  backface-visibility: hidden;
}


.btn-action .btn-icon {
  width: 11px;
  height: 11px;
  flex-shrink: 0;
  stroke-width: 2; /* Mas makapal para malinaw tingnan */
}

.btn-book { 
  background-color: #73061a; 
  color: white; 
  border: 1px solid rgba(250, 204, 21, 0.45); 
  box-shadow: 
    0 4px 10px rgba(115, 6, 26, 0.4),
    0 0 3px rgba(250, 204, 21, 0.24);
  transition-delay: 0.05s;
}

.btn-book:hover { 
  background-color: yellow; 
  color: maroon;
  border: 1px solid rgba(250, 204, 21, 0.6);
  transform: translateY(-2px) scale(1.03) !important;
  box-shadow: 0 6px 15px rgba(250, 204, 21, 0.65); 
}

.btn-view { 
  background-color: transparent; 
  color: #ffffff; 
  border: 1px solid rgba(250, 204, 21, 0.45);
  box-shadow: 0 0 3px rgba(250, 204, 21, 0.18);
  transition-delay: 0.1s; 
}

.btn-view:hover { 
  background-color: #facc15; 
  color: #0f0c22;
  border: 1px solid rgba(250, 204, 21, 0.6);
  transform: translateY(-2px) scale(1.03) !important; 
  box-shadow: 0 6px 15px rgba(250, 204, 21, 0.55);
}

.splash-screen {
  position: absolute;
  inset: 0;
  background: #ffffff;
  z-index: 10;
  display: flex;
  justify-content: center;
  align-items: center;
  animation: phoneBoot 3s ease-in-out forwards;
}
.splash-logo {
  display: inline-flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 8px;
  animation: logoPulse 1.5s infinite ease-in-out;
}
.splash-logo img {
  width: 58px;
  height: 58px;
  display: block;
  object-fit: contain;
}
.splash-logo span {
  font-family: 'Arial Black', -apple-system, sans-serif;
  font-size: 15px;
  font-weight: 900;
  color: #0f0c22;
  letter-spacing: .6px;
}
@keyframes phoneBoot {
  0% { opacity: 1; visibility: visible; }
  80% { opacity: 1; transform: scale(1); }
  100% { opacity: 0; transform: scale(1.1); visibility: hidden; }
}
@keyframes logoPulse {
  0%, 100% { transform: scale(0.9); opacity: 0.7; }
  50% { transform: scale(1.05); opacity: 1; }
}
</style>
@endpush

@section('content')
    <svg style="display: none;">
      <symbol id="avatar-placeholder" viewBox="0 0 24 24">
        <circle cx="12" cy="12" r="12" fill="#e2e8f0"/>
        <path d="M12 12a5 5 0 1 0 0-10 5 5 0 0 0 0 10zm0 2c-5.33 0-8 2.67-8 4v2h16v-2c0-1.33-2.67-4-8-4z" fill="#cbd5e0"/>
      </symbol>
    </svg>

    <section class="PUPBG" id="top">
      <div class="PUPBG-overlay"></div>
      <div class="container PUPBG-inner">
        <div class="hero-copy">
          <p class="kicker">PUP TAGUIG HEALTH SERVICES</p>
          <h1 class="PUPBG-title">
            <span class="hero-title-line">Your Health,</span>
            <span class="hero-title-line hero-title-rotating-line">Our <span class="hero-rotating-word" id="heroRotatingWord">Care</span></span>
          </h1>
          <p class="PUPBG-lead">Access quality healthcare services online, anytime, anywhere.</p>

          @php
            $clinicHoursStatus = $clinicHoursStatus ?? [
                'is_open' => false,
                'label' => 'Clinic Closed',
                'hours' => '8:00 AM - 5:00 PM',
            ];
          @endphp
          <div class="hero-status-card {{ !($clinicHoursStatus['is_open'] ?? false) ? 'is-closed' : '' }}">
            <span class="hero-status-dot" aria-hidden="true"></span>
            <span>
              <span class="hero-status-title">{{ $clinicHoursStatus['label'] }}</span>
              @if($clinicHoursStatus['is_open'] ?? false)
                <span class="hero-status-time">
                  <x-outline-icon name="clock" />
                  <span>{{ $clinicHoursStatus['hours'] }}</span>
                </span>
              @endif
              @if(!($clinicHoursStatus['is_open'] ?? false) && !empty($clinicHoursStatus['next_open_at']))
                <span class="hero-status-countdown" data-clinic-countdown data-countdown-target="{{ $clinicHoursStatus['next_open_at'] }}">
                  Opens in <strong data-countdown-value>--</strong>
                </span>
              @endif
            </span>
          </div>
        </div>

        @if(!($clinicHoursStatus['is_open'] ?? false) && !empty($clinicHoursStatus['next_open_at']))
          <script>
            (() => {
              const countdown = document.querySelector('[data-clinic-countdown]');
              const value = countdown?.querySelector('[data-countdown-value]');
              const target = countdown ? Date.parse(countdown.dataset.countdownTarget) : NaN;

              if (!countdown || !value || Number.isNaN(target)) return;

              const updateCountdown = () => {
                const remaining = Math.max(0, target - Date.now());
                const totalMinutes = Math.ceil(remaining / 60000);
                const days = Math.floor(totalMinutes / 1440);
                const hours = Math.floor((totalMinutes % 1440) / 60);
                const minutes = totalMinutes % 60;

                value.textContent = days > 0
                  ? `${days}d ${hours}h ${minutes}m`
                  : `${hours}h ${minutes}m`;

                if (remaining <= 0) {
                  value.textContent = 'now';
                  window.setTimeout(() => window.location.reload(), 1000);
                  return;
                }

                window.setTimeout(updateCountdown, 1000);
              };

              updateCountdown();
            })();
          </script>
        @endif

                <!-- === 3D Phone === -->
        <div class="hero-actions-phone-wrapper">
          <div class="phonecard">
            <div class="phone-float-icons" aria-hidden="true">
              <span class="phone-float-icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 2.994v2.25m10.5-2.25v2.25m-14.252 13.5V7.491a2.25 2.25 0 0 1 2.25-2.25h13.5a2.25 2.25 0 0 1 2.25 2.25v11.251m-18 0a2.25 2.25 0 0 0 2.25 2.25h13.5a2.25 2.25 0 0 0 2.25-2.25m-18 0v-7.5a2.25 2.25 0 0 1 2.25-2.25h13.5a2.25 2.25 0 0 1 2.25 2.25v7.5m-6.75-6h2.25m-9 2.25h4.5m.002-2.25h.005v.006H12v-.006Zm-.001 4.5h.006v.006h-.006v-.005Zm-2.25.001h.005v.006H9.75v-.006Zm-2.25 0h.005v.005h-.006v-.005Zm6.75-2.247h.005v.005h-.005v-.005Zm0 2.247h.006v.006h-.006v-.006Zm2.25-2.248h.006V15H16.5v-.005Z" />
                </svg>
              </span>
              <span class="phone-float-icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                </svg>
              </span>
              <span class="phone-float-icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                </svg>
              </span>
            </div>
            <div class="phone">
              
              <!-- 3D Thickness Structural Layers -->
              <div class="face back"></div>
              <div class="face left"></div>
              <div class="face right"></div>
              <div class="face top"></div>
              <div class="face bottom"></div>

              <!-- Main Front Screen Surface -->
              <div class="face front">
                
                <!-- Notch Element -->
                <div class="front-camera"></div>

                <!-- System Loading Layer -->
                <div class="splash-screen">
                  <div class="splash-logo">
                    <img src="{{ asset('images/clinic_logo_transparent.png') }}" alt="PUP Taguig Clinic logo">
                    <span>CareSync</span>
                  </div>
                </div>

                <!-- 🔒 Lock Screen Layer (May Background Cover via CSS) -->
                <div class="lock-screen">
                  <div class="lock-time" id="liveTime">--:--</div>
                  <div class="lock-date" id="liveDate">--, --- --</div>
                  <div class="unlock-label">Hover to unlock</div>
                </div>
                
                <!-- Top Status Component -->
                <div class="head">
                  <div class="h-left">CareSync</div>
                  <div class="h-right">
                    <span class="wifi">
                      <svg stroke="currentColor" fill="currentColor" viewBox="0 0 365.892 365.892" class="logo-head">
                        <circle r="41.494" cy="286.681" cx="182.945"></circle>
                        <path d="M182.946,176.029c-35.658,0-69.337,17.345-90.09,46.398c-5.921,8.288-4.001,19.806,4.286,25.726 c3.249,2.321,6.994,3.438,10.704,3.438c5.754,0,11.423-2.686,15.021-7.724c13.846-19.383,36.305-30.954,60.078-30.954 c23.775,0,46.233,11.571,60.077,30.953c5.919,8.286,17.437,10.209,25.726,4.288c8.288-5.92,10.208-17.438,4.288-25.726 C252.285,193.373,218.606,176.029,182.946,176.029z"></path>
                        <path d="M182.946,106.873c-50.938,0-99.694,21.749-133.77,59.67c-6.807,7.576-6.185,19.236,1.392,26.044 c3.523,3.166,7.929,4.725,12.32,4.725c5.051-0.001,10.082-2.063,13.723-6.116c27.091-30.148,65.849-47.439,106.336-47.439 s79.246,17.291,106.338,47.438c6.808,7.576,18.468,8.198,26.043,1.391c7.576-6.808,8.198-18.468,1.391-26.043 C282.641,128.621,233.883,106.873,182.946,106.873z"></path>
                        <path d="M360.611,112.293c-47.209-48.092-110.305-74.577-177.665-74.577c-67.357,0-130.453,26.485-177.664,74.579 c-7.135,7.269-7.027,18.944,0.241,26.079c3.59,3.524,8.255,5.282,12.918,5.281c4.776,0,9.551-1.845,13.161-5.522 c40.22-40.971,93.968-63.534,151.344-63.534c57.379,0,111.127,22.563,151.343,63.532c7.136,7.269,18.812,7.376,26.08,0.242 C367.637,131.238,365.892,119.562,360.611,112.293z"></path>
                      </svg>
                    </span>
                    <span class="network">
                      <svg fill="currentColor" viewBox="0 0 16 16" class="logo-head">
                        <path d="m 13 1 c -0.554688 0 -1 0.445312 -1 1 v 12 c 0 0.554688 0.445312 1 1 1 h 1 c 0.554688 0 1 -0.445312 1 -1 v -12 c 0 -0.554688 -0.445312 -1 -1 -1 z m -4 3 c -0.554688 0 -1 0.445312 -1 1 v 9 c 0 0.554688 0.445312 1 1 1 h 1 c 0.554688 0 1 -0.445312 1 -1 v -9 c 0 -0.554688 -0.445312 -1 -1 -1 z m -4 3 c -0.554688 0 -1 0.445312 -1 1 v 6 c 0 0.554688 0.445312 1 1 1 h 1 c 0.554688 0 1 -0.445312 1 -1 v -6 c 0 -0.554688 -0.445312 -1 -1 -1 z m -4 3 c -0.554688 0 -1 0.445312 -1 1 v 3 c 0 0.554688 0.445312 1 1 1 h 1 c 0.554688 0 1 -0.445312 1 -1 v -3 c 0 -0.554688 -0.445312 -1 -1 -1 z"></path>
                      </svg>
                    </span>
                    <span class="battery">
                      <svg transform="matrix(-1, 0, 0, 1, 0, 0)" fill="currentColor" viewBox="1 10 20 5" class="logo-head">
                        <path fill-opacity="0.3" d="M20,10V8.33A1.34,1.34,0,0,0,18.67,7H8V17H18.67A1.34,1.34,0,0,0,20,15.67V14h2V10Z"></path>
                        <path d="M3.33,17H8V7H3.34A1.34,1.34,0,0,0,2,8.33v7.33A1.34,1.34,0,0,0,3.33,17Z"></path>
                      </svg>
                    </span>
                  </div>
                </div>
                
                <!-- 🌟 LARAVEL DYNAMIC ROUTE ANCHORS (Ginamit ang bago mong SVG Icons) -->
                <div class="phone-unlocked-logo" aria-hidden="true">
                  <img src="{{ asset('images/clinic_logo_transparent.png') }}" alt="">
                </div>

                <a href="{{ url('/student/booking') }}" class="btn-action btn-book">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="btn-icon">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 2.994v2.25m10.5-2.25v2.25m-14.252 13.5V7.491a2.25 2.25 0 0 1 2.25-2.25h13.5a2.25 2.25 0 0 1 2.25 2.25v11.251m-18 0a2.25 2.25 0 0 0 2.25 2.25h13.5a2.25 2.25 0 0 0 2.25-2.25m-18 0v-7.5a2.25 2.25 0 0 1 2.25-2.25h13.5a2.25 2.25 0 0 1 2.25 2.25v7.5m-6.75-6h2.25m-9 2.25h4.5m.002-2.25h.005v.006H12v-.006Zm-.001 4.5h.006v.006h-.006v-.005Zm-2.25.001h.005v.006H9.75v-.006Zm-2.25 0h.005v.005h-.006v-.005Zm6.75-2.247h.005v.005h-.005v-.005Zm0 2.247h.006v.006h-.006v-.006Zm2.25-2.248h.006V15H16.5v-.005Z" />
                  </svg>
                  <span>Book</span>
                </a>

                <a href="{{ url('/student/history') }}" class="btn-action btn-view">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="btn-icon">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                  </svg>
                  <span>View</span>
                </a>
                
                <!-- Bottom Soft-Keys Navigation Panel -->
                <div class="phone-version-label" aria-hidden="true">Clinic V.26</div>

                <div class="navigation">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="btn-nav-svg">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" />
                  </svg>
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="btn-nav-svg">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                  </svg>
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="btn-nav-svg">
                    <rect x="5" y="5" width="14" height="14" rx="2" stroke-width="1.5" stroke="currentColor"/>
                  </svg>
                </div>
                
              </div>
            </div>
          </div>
        </div>

        

        <a href="#announcements" class="hero-scroll" aria-label="Scroll to Explore">
          <span>Scroll to Explore</span>
          <span class="hero-scroll-button" aria-hidden="true">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="hero-scroll-chevron">
              <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 5.25 7.5 7.5 7.5-7.5m-15 6 7.5 7.5 7.5-7.5" />
            </svg>
          </span>
        </a>
      </div>
      <div class="hero-curve" aria-hidden="true"></div>
    </section>

    <div class="student-home-info-bg" id="homeAboutArea">
    <section class="about-panel">
      @php
        $announcementSlides = ($homeAnnouncements ?? collect())->isNotEmpty()
            ? $homeAnnouncements
            : collect([[
                'priority' => 'ANNOUNCEMENT',
                'title' => 'No clinic announcements posted yet.',
                'message' => 'New clinic updates will appear here once they are published.',
                'date' => now(config('app.timezone'))->format('M j, Y'),
            ]]);
        $announcementCount = $announcementSlides->count();
      @endphp

      <div id="announcements" class="home-announcement-band" aria-label="Clinic announcements" style="scroll-margin-top: 86px;">
        <header class="home-announcement-heading">
          <span class="home-announcement-kicker">Latest Updates</span>
          <h2>Announcements &amp; Advisories</h2>
          <span class="home-announcement-title-line" aria-hidden="true"></span>
        </header>
        <div class="home-announcement-shell {{ $announcementCount < 2 ? 'is-static static-count-' . $announcementCount : 'is-carousel carousel-count-' . $announcementCount }}" data-home-announcements>
          <div class="home-announcement-track">
            @foreach($announcementSlides as $index => $announcement)
            @php
              $announcementSearch = strtolower(($announcement['priority'] ?? '') . ' ' . ($announcement['title'] ?? ''));
              $announcementIcon = str_contains($announcementSearch, 'schedule') || str_contains($announcementSearch, 'service')
                  ? 'calendar-days'
                  : (str_contains($announcementSearch, 'health') || str_contains($announcementSearch, 'reminder')
                      ? 'heart-pulse'
                      : (str_contains($announcementSearch, 'form') || str_contains($announcementSearch, 'document')
                          ? 'document-text'
                          : 'megaphone'));
              $announcementPosition = match (true) {
                  $index === 0 => 'is-current',
                  $index === 1 => 'is-next',
                  $announcementCount > 2 && $index === $announcementCount - 1 => 'is-prev',
                  $announcementCount > 3 && $index === 2 => 'is-next-far',
                  default => '',
              };
            @endphp
            <article
              class="home-announcement-card {{ $announcementPosition }}"
              data-announcement-slide
              data-announcement-detail
              data-announcement-id="{{ $announcement['id'] ?? '' }}"
              data-priority="{{ e($announcement['priority'] ?: 'ANNOUNCEMENT') }}"
              data-title="{{ e($announcement['title']) }}"
              data-message-html="{!! e($announcement['message_html'] ?? nl2br(e($announcement['message'] ?? ''))) !!}"
              data-image-urls='@json($announcement['image_urls'] ?? [])'
              data-date="{{ e($announcement['date'] ?? now(config('app.timezone'))->format('M j, Y')) }}"
              role="button"
              tabindex="0"
              aria-label="View announcement: {{ $announcement['title'] }}"
            >
              <div class="announcement-card-head">
                <span class="announcement-icon" aria-hidden="true">
                  <x-outline-icon :name="$announcementIcon" />
                </span>
                <div>
                  <p class="announcement-eyebrow">{{ $announcement['priority'] ?: 'ANNOUNCEMENT' }}</p>
                  <h3 class="announcement-title">{{ $announcement['title'] }}</h3>
                </div>
              </div>
              <div class="announcement-message">{!! $announcement['message_html'] ?? nl2br(e(\Illuminate\Support\Str::limit($announcement['message'], 175))) !!}</div>
              <span class="announcement-date">
                <x-outline-icon name="calendar-days" />
                <span>{{ $announcement['date'] ?? now(config('app.timezone'))->format('M j, Y') }}</span>
              </span>
              </article>
            @endforeach
          </div>

          <button
            type="button"
            class="announcement-nav prev"
            data-announcement-prev
            aria-label="Previous announcement"
            aria-disabled="{{ $announcementCount < 2 ? 'true' : 'false' }}"
            @disabled($announcementCount < 2)
          >
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="m15.75 19.5-7.5-7.5 7.5-7.5" />
            </svg>
          </button>
          <button
            type="button"
            class="announcement-nav next"
            data-announcement-next
            aria-label="Next announcement"
            aria-disabled="{{ $announcementCount < 2 ? 'true' : 'false' }}"
            @disabled($announcementCount < 2)
          >
            <x-outline-icon name="chevron-right" />
          </button>

          @if($announcementCount >= 2)
            <div class="announcement-pagination" aria-label="Choose announcement">
              @foreach($announcementSlides as $index => $announcement)
                <button
                  type="button"
                  class="announcement-dot {{ $index === 0 ? 'is-active' : '' }}"
                  data-announcement-dot="{{ $index }}"
                  aria-label="Show announcement {{ $index + 1 }}"
                  aria-current="{{ $index === 0 ? 'true' : 'false' }}"
                ></button>
              @endforeach
            </div>
          @endif
        </div>

        <button type="button" class="home-announcement-view-all" id="viewAllAnnouncementsBtn">
          <span>View All Announcements</span>
          <x-outline-icon name="arrow-long-right" />
        </button>
      </div>

      <div id="about" class="about-experience" style="scroll-margin-top: 100px;">
        <div class="about-decor" aria-hidden="true">
          <span class="about-decor__word">CARE</span>
          <span class="about-decor__arc about-decor__arc--left"></span>
          <span class="about-decor__arc about-decor__arc--right"></span>
          <span class="about-decor__pulse about-decor__pulse--left"></span>
          <span class="about-decor__pulse about-decor__pulse--right"></span>
          <span class="about-decor__pulse about-decor__pulse--lower-left"></span>
          <span class="about-decor__pulse about-decor__pulse--lower-right"></span>
          <span class="about-decor__pulse about-decor__pulse--center"></span>
          <span class="about-decor__plus about-decor__plus--one"></span>
          <span class="about-decor__plus about-decor__plus--two"></span>
          <span class="about-decor__plus about-decor__plus--three"></span>
          <span class="about-decor__plus about-decor__plus--four"></span>
        </div>
        <header class="about-heading">
          <span class="about-kicker">Why Choose</span>
          <h2 class="about-section-title">PUP Taguig Clinic?</h2>
        </header>

        <div class="why-grid">
          <div class="why-item">
            <span class="why-icon" aria-hidden="true">
              <x-outline-icon name="check" />
            </span>
            <span class="why-copy">
              <strong>Secure &amp; Private</strong>
              <span>Your data is always protected.</span>
            </span>
          </div>
          <div class="why-item">
            <span class="why-icon" aria-hidden="true">
              <x-outline-icon name="users" />
            </span>
            <span class="why-copy">
              <strong>User-Centered</strong>
              <span>Services designed for your well-being.</span>
            </span>
          </div>
          <div class="why-item">
            <span class="why-icon" aria-hidden="true">
              <x-outline-icon name="heart-pulse" />
            </span>
            <span class="why-copy">
              <strong>Quality Care</strong>
              <span>Compassionate care you can trust.</span>
            </span>
          </div>
        </div>

        <div class="about-learn-more">
          <a href="#" class="btn-outline" id="learnMoreBtn">
              <x-outline-icon name="arrow-long-right" />
              <span>Learn More</span>
          </a>
          <div class="learn-more-bubble" role="tooltip">
            <strong>About PUPT Clinic</strong>
            <span>The PUP Taguig Clinic supports students with consultations, medical record processing, health clearance workflows, and appointment coordination.</span>
            <span>Public guests can explore clinic information. Students should sign in through One Portal to book appointments and access private records.</span>
          </div>
        </div>

        @php
          $feedbackSlides = collect($recentFeedback ?? []);
        @endphp
        <section class="comments-section feedback-showcase" aria-labelledby="feedbacksTitle">
          <header class="section-head feedback-heading">
            <span class="feedback-kicker">What Students Are Saying</span>
            <h3 id="feedbacksTitle">Feedbacks</h3>
          </header>

          <div class="feedback-stage">
            @forelse($feedbackSlides as $feedback)
              @php
                $feedbackRating = max(1, min(5, (int) ($feedback['rating'] ?? 5)));
              @endphp
              <article class="feedback-slide">
                <span class="feedback-quote" aria-hidden="true">&ldquo;</span>
                <div class="feedback-stars" aria-label="{{ $feedbackRating }} out of 5 stars">
                  @for($star = 1; $star <= 5; $star++)
                    <span class="{{ $star <= $feedbackRating ? 'is-filled' : '' }}" aria-hidden="true">&#9733;</span>
                  @endfor
                </div>
                <div class="feedback-identity">
                  <svg class="avatar" role="img" aria-label="User avatar"><use href="#avatar-placeholder"></use></svg>
                  <div class="feedback-copy">
                    <h4>{{ $feedback['name'] }}</h4>
                    <span class="comment-meta">{{ $feedback['role'] }} &middot; {{ $feedback['time'] }}</span>
                    <p>{{ $feedback['message'] }}</p>
                  </div>
                </div>
              </article>
            @empty
              <article class="feedback-slide is-empty">
                <div class="feedback-copy">
                  <h4>No feedback has been shared yet.</h4>
                  <p>Student feedback will appear here after a clinic visit is completed.</p>
                </div>
              </article>
            @endforelse
          </div>
        </section>


      </div>
    </section>
    </div>

    <footer class="site-footer">
      <div class="footer-top">
        <div class="container footer-grid">
          <div class="footer-col footer-brand">
            <div class="brand">
              <div class="brand-logo">
                <img src="{{ asset('images/clinic_logo_transparent.png') }}" alt="PUP Taguig Clinic logo" />
              </div>
              <div>
                <div class="brand-name">PUP TAGUIG <span class="brand-sub">MEDICAL CLINIC</span></div>
              </div>
            </div>
            <p class="brand-desc">Providing quality healthcare services to the PUP Taguig community.</p>
            <div class="footer-brand-alt" aria-hidden="true">
              <span class="footer-brand-alt__kicker">
                <span class="footer-brand-alt__mark"><img src="{{ asset('images/pup_logo.png') }}" alt="" /></span>
                <span class="footer-brand-alt__rule" aria-hidden="true"></span>
                <span>PUP Taguig &bull; Medical Services</span>
              </span>
              <span class="footer-brand-alt__message">
                <em>Mula sa'yo,</em>
                <strong data-sweep-text="Para sa Bayan.">Para sa Bayan.</strong>
              </span>
              <span class="footer-brand-alt__spark" aria-hidden="true"></span>
            </div>

            <div class="social">
              <a class="social-link" href="#" aria-label="Official clinic site">
                <x-outline-icon name="globe-alt" />
              </a>
              <a class="social-link" href="#announcements" aria-label="Clinic announcements">
                <x-outline-icon name="megaphone" />
              </a>
              <span class="footer-card-version">OCMS V.26</span>
            </div>
          </div>

          <div class="footer-col">
            <h4><span class="footer-heading-icon"><x-outline-icon name="link" /></span><span>Quick Links</span></h4>
            <ul class="footer-links">
              <li><a href="{{ url('/student/home') }}"><x-outline-icon name="home" class="footer-link-icon" /><span>Home</span></a></li>
              <li><a href="#about"><x-outline-icon name="information-circle" class="footer-link-icon" /><span>About Us</span></a></li>
              <li><a href="{{ url('/student/booking') }}"><x-outline-icon name="calendar-days" class="footer-link-icon" /><span>Book Appointment</span></a></li>
              <li><a href="{{ url('/student/faq') }}"><x-outline-icon name="question-mark-circle" class="footer-link-icon" /><span>FAQ</span></a></li>
            </ul>
          </div>

          <div class="footer-col">
            <h4><span class="footer-heading-icon"><x-outline-icon name="heart-pulse" /></span><span>Services</span></h4>
            <ul class="footer-links">
              <li><span class="footer-service-item"><x-outline-icon name="phone" class="footer-service-icon" /><span>General Consultation</span></span></li>
              <li><span class="footer-service-item"><x-outline-icon name="heart-pulse" class="footer-service-icon" /><span>Blood Pressure Monitoring</span></span></li>
              <li><span class="footer-service-item"><x-outline-icon name="document-check" class="footer-service-icon" /><span>Medical Clearance Issuance</span></span></li>
            </ul>
          </div>

          <div class="footer-col">
            <h4><span class="footer-heading-icon"><x-outline-icon name="map-pin" /></span><span>Contact Us</span></h4>
            <ul class="contact-list">
              <li>
                <x-outline-icon name="map-pin" class="contact-icon" />
                General Santos Ave, Taguig City
              </li>
              <li>
                <x-outline-icon name="envelope" class="contact-icon" />
                puptclinic@gmail.com
              </li>
              <li>
                <x-outline-icon name="phone" class="contact-icon" />
                (02) 8837-5858
              </li>
            </ul>
          </div>
        </div>
      </div>

    </footer>

    <div class="announcement-modal" id="announcementDetailModal" aria-hidden="true">
      <section class="announcement-modal-card" role="dialog" aria-modal="true" aria-labelledby="announcementDetailTitle">
        <div class="announcement-modal-head">
          <div>
            <p class="announcement-modal-eyebrow" id="announcementDetailPriority">ANNOUNCEMENT</p>
            <h3 class="announcement-modal-title" id="announcementDetailTitle">Announcement</h3>
          </div>
          <button type="button" class="announcement-modal-close" id="announcementDetailClose" aria-label="Close announcement details">
            <x-outline-icon name="x-mark" />
          </button>
        </div>
        <div class="announcement-modal-body announcement-rich-content">
          <div id="announcementDetailMessage"></div>
          <div class="announcement-modal-image-grid" id="announcementDetailImages" hidden></div>
        </div>
        <div class="announcement-modal-published">
          <x-outline-icon name="calendar-days" />
          <span>Published <time id="announcementDetailDate"></time></span>
        </div>
      </section>
    </div>

    <div class="announcement-modal" id="allAnnouncementsModal" aria-hidden="true">
      <section class="announcement-modal-card announcement-all-card" role="dialog" aria-modal="true" aria-labelledby="allAnnouncementsTitle">
        <div class="announcement-modal-head">
          <div>
            <p class="announcement-modal-eyebrow">LATEST UPDATES</p>
            <h3 class="announcement-modal-title" id="allAnnouncementsTitle">All Announcements</h3>
          </div>
          <button type="button" class="announcement-modal-close" id="allAnnouncementsClose" aria-label="Close all announcements">
            <x-outline-icon name="x-mark" />
          </button>
        </div>
        <div class="announcement-all-list">
          @foreach($announcementSlides as $announcement)
            <button
              type="button"
              class="announcement-all-item"
              data-announcement-list-item
              data-announcement-id="{{ $announcement['id'] ?? '' }}"
              data-priority="{{ e($announcement['priority'] ?: 'ANNOUNCEMENT') }}"
              data-title="{{ e($announcement['title']) }}"
              data-message-html="{!! e($announcement['message_html'] ?? nl2br(e($announcement['message'] ?? ''))) !!}"
              data-image-urls='@json($announcement['image_urls'] ?? [])'
              data-date="{{ e($announcement['date'] ?? now(config('app.timezone'))->format('M j, Y')) }}"
            >
              <span class="announcement-all-item-icon" aria-hidden="true">
                <x-outline-icon name="megaphone" />
              </span>
              <span class="announcement-all-item-copy">
                <strong>{{ $announcement['title'] }}</strong>
                <span>{{ \Illuminate\Support\Str::limit($announcement['message'], 100) }}</span>
              </span>
              <span class="announcement-all-item-date">{{ $announcement['date'] ?? now(config('app.timezone'))->format('M j, Y') }}</span>
            </button>
          @endforeach
        </div>
      </section>
    </div>

    <script>
      document.addEventListener('DOMContentLoaded', function () {
        const learnMoreBtn = document.getElementById('learnMoreBtn');
        const announcementDetailModal = document.getElementById('announcementDetailModal');
        const announcementDetailClose = document.getElementById('announcementDetailClose');
        const announcementDetailPriority = document.getElementById('announcementDetailPriority');
        const announcementDetailTitle = document.getElementById('announcementDetailTitle');
        const announcementDetailMessage = document.getElementById('announcementDetailMessage');
        const announcementDetailImages = document.getElementById('announcementDetailImages');
        const announcementDetailDate = document.getElementById('announcementDetailDate');
        const viewAllAnnouncementsBtn = document.getElementById('viewAllAnnouncementsBtn');
        const allAnnouncementsModal = document.getElementById('allAnnouncementsModal');
        const allAnnouncementsClose = document.getElementById('allAnnouncementsClose');
        const heroRotatingWord = document.getElementById('heroRotatingWord');
        const heroScrollLink = document.querySelector('.hero-scroll');
        const announcementShell = document.querySelector('[data-home-announcements]');

        const reducedMotionQuery = window.matchMedia('(prefers-reduced-motion: reduce)');
        const revealDefinitions = [
          { selector: '.home-announcement-heading' },
          { selector: '.home-announcement-shell', motion: 'fade' },
          { selector: '.home-announcement-view-all' },
          { selector: '#about .about-heading' },
          { selector: '#about .why-item', stagger: 90 },
          { selector: '#about .about-learn-more' },
          { selector: '.feedback-showcase .feedback-heading' },
          { selector: '.feedback-showcase .feedback-stage' },
          { selector: '.site-footer .footer-col', stagger: 80 },
          { selector: '.site-footer .footer-bottom' }
        ];

        function initializeHomeScrollReveal() {
          const revealElements = [];
          let previousRevealScrollY = window.scrollY;
          let revealScrollDirection = 'down';

          revealDefinitions.forEach(function (definition) {
            document.querySelectorAll(definition.selector).forEach(function (element, index) {
              element.classList.add('home-scroll-reveal');
              if (definition.motion !== 'fade') {
                element.dataset.revealMotion = 'up';
                element.dataset.revealDirection = 'down';
              }

              const delay = Math.min(index * (definition.stagger || 0), 240);
              element.style.setProperty('--home-reveal-delay', `${delay}ms`);
              revealElements.push(element);
            });
          });

          if (!revealElements.length || reducedMotionQuery.matches) {
            revealElements.forEach(function (element) {
              element.classList.add('is-visible');
            });
            return;
          }

          document.documentElement.classList.add('home-reveal-enabled');

          window.addEventListener('scroll', function () {
            const currentScrollY = window.scrollY;
            if (Math.abs(currentScrollY - previousRevealScrollY) > 2) {
              revealScrollDirection = currentScrollY > previousRevealScrollY ? 'down' : 'up';
              previousRevealScrollY = currentScrollY;
            }
          }, { passive: true });

          if (!('IntersectionObserver' in window)) {
            revealElements.forEach(function (element) {
              element.classList.add('is-visible');
            });
            return;
          }

          const revealObserver = new IntersectionObserver(function (entries, observer) {
            entries.forEach(function (entry) {
              if (entry.isIntersecting && entry.intersectionRatio >= 0.08) {
                entry.target.dataset.revealDirection = revealScrollDirection;
                void entry.target.offsetWidth;
                entry.target.classList.add('is-visible');
                return;
              }

              entry.target.classList.remove('is-visible');
            });
          }, {
            root: null,
            threshold: [0, 0.08, 0.2],
            rootMargin: '-3% 0px -3% 0px'
          });

          revealElements.forEach(function (element) {
            revealObserver.observe(element);
          });
        }

        initializeHomeScrollReveal();

        const homeNavLink = document.querySelector('[data-student-nav="home"]');
        const aboutNavLink = document.querySelector('[data-student-nav="about"]');
        const aboutSection = document.getElementById('about');
        if (homeNavLink && aboutNavLink && aboutSection) {
          const syncHomeNavActiveState = function () {
            const headerOffset = 130;
            const aboutTop = aboutSection.getBoundingClientRect().top + window.scrollY - headerOffset;
            const isAboutArea = window.scrollY >= aboutTop;

            homeNavLink.classList.toggle('active', !isAboutArea);
            aboutNavLink.classList.toggle('active', isAboutArea);
          };

          syncHomeNavActiveState();
          window.addEventListener('scroll', syncHomeNavActiveState, { passive: true });
          window.addEventListener('resize', syncHomeNavActiveState);
        }

        if (heroRotatingWord) {
          const words = ['Care', 'Commitment', 'Continuity', 'Concern'];
          let activeWord = 0;

          setInterval(function () {
            heroRotatingWord.classList.add('is-changing');

            window.setTimeout(function () {
              activeWord = (activeWord + 1) % words.length;
              heroRotatingWord.textContent = words[activeWord];
              heroRotatingWord.classList.remove('is-changing');
            }, 240);
          }, 3000);
        }

        if (announcementDetailModal && announcementDetailModal.parentElement !== document.body) {
          document.body.appendChild(announcementDetailModal);
        }
        if (allAnnouncementsModal && allAnnouncementsModal.parentElement !== document.body) {
          document.body.appendChild(allAnnouncementsModal);
        }

        if (learnMoreBtn) {
          learnMoreBtn.addEventListener('click', function (event) {
            event.preventDefault();
          });
        }

        function syncAnnouncementModalLock() {
          const hasOpenAnnouncementModal = Boolean(
            announcementDetailModal?.classList.contains('is-open') ||
            allAnnouncementsModal?.classList.contains('is-open')
          );
          document.documentElement.classList.toggle('announcement-modal-open', hasOpenAnnouncementModal);
          document.body.classList.toggle('announcement-modal-open', hasOpenAnnouncementModal);
        }

        function setAnnouncementDetailOpen(isOpen, trigger = null) {
          if (!announcementDetailModal) return;

          if (trigger && isOpen) {
            if (announcementDetailPriority) announcementDetailPriority.textContent = trigger.dataset.priority || 'ANNOUNCEMENT';
            if (announcementDetailTitle) announcementDetailTitle.textContent = trigger.dataset.title || 'Announcement';
            if (announcementDetailMessage) announcementDetailMessage.innerHTML = trigger.dataset.messageHtml || '';
            if (announcementDetailImages) {
              let imageUrls = [];
              try {
                imageUrls = JSON.parse(trigger.dataset.imageUrls || '[]');
              } catch (error) {
                imageUrls = [];
              }
              announcementDetailImages.replaceChildren(...imageUrls.map((imageUrl, index) => {
                const card = document.createElement('div');
                card.className = 'announcement-modal-image-card';

                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'announcement-modal-image-button';
                button.setAttribute('aria-label', `Show open option for announcement image ${index + 1}`);

                const image = document.createElement('img');
                image.className = 'announcement-modal-image';
                image.src = imageUrl;
                image.alt = `Announcement image ${index + 1} for ${trigger.dataset.title || 'clinic announcement'}`;
                button.append(image);
                button.addEventListener('click', () => card.classList.toggle('is-open'));

                const openLink = document.createElement('a');
                openLink.className = 'announcement-modal-image-open';
                openLink.href = imageUrl;
                openLink.target = '_blank';
                openLink.rel = 'noopener noreferrer';
                openLink.innerHTML = '<span>Open</span>';

                card.append(button, openLink);
                return card;
              }));
              announcementDetailImages.hidden = imageUrls.length === 0;
            }
            if (announcementDetailDate) announcementDetailDate.textContent = trigger.dataset.date || '';
          }

          announcementDetailModal.classList.toggle('is-open', isOpen);
          announcementDetailModal.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
          syncAnnouncementModalLock();
        }

        function setAllAnnouncementsOpen(isOpen) {
          if (!allAnnouncementsModal) return;
          allAnnouncementsModal.classList.toggle('is-open', isOpen);
          allAnnouncementsModal.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
          syncAnnouncementModalLock();
        }

        viewAllAnnouncementsBtn?.addEventListener('click', function () {
          setAllAnnouncementsOpen(true);
        });

        allAnnouncementsClose?.addEventListener('click', function () {
          setAllAnnouncementsOpen(false);
        });

        allAnnouncementsModal?.addEventListener('click', function (event) {
          if (event.target === allAnnouncementsModal) {
            setAllAnnouncementsOpen(false);
          }
        });

        document.querySelectorAll('[data-announcement-list-item]').forEach(function (announcementItem) {
          announcementItem.addEventListener('click', function () {
            setAllAnnouncementsOpen(false);
            setAnnouncementDetailOpen(true, announcementItem);
          });
        });

        document.querySelectorAll('[data-announcement-detail]').forEach(function (announcementCard) {
          announcementCard.addEventListener('click', function () {
            setAnnouncementDetailOpen(true, announcementCard);
          });
          announcementCard.addEventListener('keydown', function (event) {
            if (event.key !== 'Enter' && event.key !== ' ') return;
            event.preventDefault();
            setAnnouncementDetailOpen(true, announcementCard);
          });
        });

        const requestedAnnouncementId = new URLSearchParams(window.location.search).get('announcement');
        if (requestedAnnouncementId) {
          const requestedAnnouncement = Array.from(document.querySelectorAll('[data-announcement-id]'))
            .find(function (item) {
              return item.dataset.announcementId === requestedAnnouncementId;
            });

          if (requestedAnnouncement) {
            window.setTimeout(function () {
              setAnnouncementDetailOpen(true, requestedAnnouncement);
            }, 180);
          }
        }

        announcementDetailClose?.addEventListener('click', function () {
          setAnnouncementDetailOpen(false);
        });

        announcementDetailModal?.addEventListener('click', function (event) {
          if (event.target === announcementDetailModal) {
            setAnnouncementDetailOpen(false);
          }
        });

        document.addEventListener('keydown', function (event) {
          if (event.key !== 'Escape') return;
          if (announcementDetailModal?.classList.contains('is-open')) {
            setAnnouncementDetailOpen(false);
            return;
          }
          if (allAnnouncementsModal?.classList.contains('is-open')) {
            setAllAnnouncementsOpen(false);
          }
        });

        if (heroScrollLink) {
          heroScrollLink.addEventListener('click', function (event) {
            const target = document.querySelector(heroScrollLink.getAttribute('href'));
            if (!target) return;

            event.preventDefault();
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
          });

          const syncHeroScrollVisibility = function () {
            heroScrollLink.classList.toggle('is-hidden', window.scrollY > 80);
          };

          syncHeroScrollVisibility();
          window.addEventListener('scroll', syncHeroScrollVisibility, { passive: true });
        }

        if (announcementShell) {
          const slides = Array.from(announcementShell.querySelectorAll('[data-announcement-slide]'));
          const prevButton = announcementShell.querySelector('[data-announcement-prev]');
          const nextButton = announcementShell.querySelector('[data-announcement-next]');
          const dots = Array.from(announcementShell.querySelectorAll('[data-announcement-dot]'));
          const positionClasses = ['is-current', 'is-next', 'is-prev', 'is-next-far'];
          let activeAnnouncement = slides.findIndex((slide) => slide.classList.contains('is-current'));
          let announcementTimer = null;

          if (activeAnnouncement < 0) activeAnnouncement = 0;

          const showAnnouncement = function (nextIndex) {
            if (!slides.length) return;
            activeAnnouncement = (nextIndex + slides.length) % slides.length;

            slides.forEach((slide, index) => {
              slide.classList.remove(...positionClasses);

              if (index === activeAnnouncement) {
                slide.classList.add('is-current');
              } else if (slides.length > 1 && index === (activeAnnouncement + 1) % slides.length) {
                slide.classList.add('is-next');
              } else if (slides.length > 2 && index === (activeAnnouncement - 1 + slides.length) % slides.length) {
                slide.classList.add('is-prev');
              } else if (slides.length > 3 && index === (activeAnnouncement + 2) % slides.length) {
                slide.classList.add('is-next-far');
              }
            });

            dots.forEach((dot, index) => {
              const isActive = index === activeAnnouncement;
              dot.classList.toggle('is-active', isActive);
              dot.setAttribute('aria-current', isActive ? 'true' : 'false');
            });
          };

          const restartAnnouncementTimer = function () {
            if (announcementTimer) window.clearInterval(announcementTimer);
            if (slides.length >= 2) {
              announcementTimer = window.setInterval(function () {
                showAnnouncement(activeAnnouncement + 1);
              }, 5000);
            }
          };

          prevButton?.addEventListener('click', function () {
            showAnnouncement(activeAnnouncement - 1);
            restartAnnouncementTimer();
          });

          nextButton?.addEventListener('click', function () {
            showAnnouncement(activeAnnouncement + 1);
            restartAnnouncementTimer();
          });

          dots.forEach(function (dot) {
            dot.addEventListener('click', function () {
              showAnnouncement(Number(dot.dataset.announcementDot || 0));
              restartAnnouncementTimer();
            });
          });

          announcementShell.addEventListener('mouseenter', function () {
            if (announcementTimer) window.clearInterval(announcementTimer);
          });

          announcementShell.addEventListener('mouseleave', restartAnnouncementTimer);

          showAnnouncement(activeAnnouncement);
          restartAnnouncementTimer();
        }

      });
      <!-- ⚡ LIVE TICKER CLOCK ENGINE -->
          (() => {
            const updatePhoneClock = () => {
              const now = new Date();
              let hours = now.getHours();
              let minutes = now.getMinutes();
              hours = hours < 10 ? '0' + hours : hours;
              minutes = minutes < 10 ? '0' + minutes : minutes;
              
              const timeEl = document.getElementById('liveTime');
              const dateEl = document.getElementById('liveDate');
              
              if (timeEl) timeEl.textContent = `${hours}:${minutes}`;
              if (dateEl) {
                const options = { weekday: 'short', month: 'short', day: '2-digit' };
                dateEl.textContent = now.toLocaleDateString('en-US', options);
              }
            };
            window.setInterval(updatePhoneClock, 1000);
            updatePhoneClock();
          })();
        
    </script>

@endsection
