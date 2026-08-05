@extends('layouts.student')

@section('title', 'Home')

@push('styles')
<style>
    /* --- CRITICAL FIX: REMOVE TOP PADDING FOR HOME PAGE ONLY --- */
    main { padding-top: 0 !important; background: #06101f; }
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
            url('{{ asset("images/PUPBG.jpg") }}') center center / cover no-repeat;
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
        padding: clamp(56px, 8vh, 82px) 0 96px;
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
        border-color: transparent;
        background: linear-gradient(180deg, rgba(255,255,255,.22) 0%, rgba(0,0,0,.34) 100%);
        box-shadow:
            inset 0 0 0 1px rgba(255,255,255,.58),
            inset 0 1px 0 rgba(255,255,255,.2),
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
    }
    .hero-scroll.is-hidden {
        opacity: 0;
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
        0%, 100% { transform: translateY(0); opacity: .7; }
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
        width: min(1120px, calc(100% - 88px));
        height: 310px;
        margin: 0 auto;
        transform: translateY(24px);
    }
    .home-announcement-band {
        position: relative;
        width: 100%;
        min-height: 372px;
        display: flex;
        align-items: center;
        overflow: hidden;
        margin: -34px 0 34px;
        border-top: 1px solid rgba(250, 204, 21, .46);
        border-bottom: 1px solid rgba(148, 163, 184, .24);
        background:
            linear-gradient(90deg, rgba(91, 7, 20, .91) 0%, rgba(9, 20, 38, .95) 48%, rgba(91, 7, 32, .88) 100%),
            url('{{ asset("images/student-bg.png") }}') center 46% / cover no-repeat;
        box-shadow:
            inset 0 24px 56px rgba(43, 0, 14, .34),
            inset 0 -30px 60px rgba(2, 6, 23, .48);
    }
    .home-announcement-band::before {
        content: "";
        position: absolute;
        inset: 0;
        pointer-events: none;
        background:
            linear-gradient(90deg, rgba(93, 10, 24, .24), transparent 24%, transparent 76%, rgba(93, 10, 42, .24)),
            linear-gradient(180deg, rgba(255, 255, 255, .035), transparent 32%, rgba(2, 6, 23, .18));
    }
    .home-announcement-heading {
        position: absolute;
        top: 25px;
        left: 50%;
        z-index: 7;
        margin: 0;
        color: #ffffff;
        font-size: 20px;
        font-weight: 950;
        line-height: 1.2;
        text-align: center;
        text-transform: uppercase;
        transform: translateX(-50%);
    }
    .home-announcement-heading::after {
        content: "";
        display: block;
        width: 34px;
        height: 2px;
        margin: 9px auto 0;
        border-radius: 999px;
        background: #facc15;
    }
    .home-announcement-card {
        position: absolute;
        top: 50%;
        left: 50%;
        z-index: 1;
        width: 272px;
        min-height: 204px;
        display: flex;
        flex-direction: column;
        padding: 20px 22px 18px;
        overflow: hidden;
        border-radius: 7px;
        border: 1px solid rgba(203, 213, 225, .30);
        background: linear-gradient(145deg, rgba(24, 31, 49, .96), rgba(13, 24, 43, .96));
        box-shadow: 0 18px 34px rgba(2, 6, 23, .30), inset 0 1px rgba(255, 255, 255, .035);
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
        width: 272px;
        min-height: 204px;
        opacity: 1;
        visibility: visible;
        transform: translate(-50%, -50%) scale(1);
        pointer-events: auto;
    }
    .home-announcement-card.is-current { left: calc(50% - 145px); }
    .home-announcement-card.is-next { left: calc(50% + 145px); }
    .home-announcement-card.is-prev,
    .home-announcement-card.is-next-far {
        z-index: 2;
        width: 178px;
        min-height: 174px;
        opacity: .42;
        visibility: visible;
        filter: blur(.35px);
        transform: translate(-50%, -50%) scale(.94);
        pointer-events: auto;
    }
    .home-announcement-card.is-prev { left: calc(50% - 382px); }
    .home-announcement-card.is-next-far { left: calc(50% + 382px); }
    .home-announcement-shell.is-static .home-announcement-card {
        z-index: 3;
        width: 272px;
        min-height: 204px;
        opacity: 1;
        visibility: visible;
        transform: translate(-50%, -50%) scale(1);
        pointer-events: auto;
    }
    .home-announcement-shell.static-count-1 .home-announcement-card:nth-of-type(1) {
        left: 50%;
    }
    .home-announcement-shell.static-count-2 .home-announcement-card:nth-of-type(1) {
        left: calc(50% - 145px);
    }
    .home-announcement-shell.static-count-2 .home-announcement-card:nth-of-type(2) {
        left: calc(50% + 145px);
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
        width: 272px;
        min-height: 204px;
        opacity: 1;
        filter: none;
    }
    .home-announcement-shell.carousel-count-3 .home-announcement-card.is-prev,
    .home-announcement-shell.carousel-count-3 .home-announcement-card.is-next {
        z-index: 2;
        width: 178px;
        min-height: 174px;
        opacity: .42;
        visibility: visible;
        filter: blur(.35px);
        transform: translate(-50%, -50%) scale(.94);
        pointer-events: auto;
    }
    .home-announcement-shell.carousel-count-3 .home-announcement-card.is-prev {
        left: calc(50% - 382px);
    }
    .home-announcement-shell.carousel-count-3 .home-announcement-card.is-next {
        left: calc(50% + 382px);
    }
    .home-announcement-card:hover,
    .home-announcement-card:focus-visible {
        border-color: rgba(250, 204, 21, .56);
        box-shadow: 0 22px 42px rgba(2, 6, 23, .44), 0 0 0 1px rgba(250, 204, 21, .10);
        outline: none;
    }
    .announcement-card-head {
        display: grid;
        grid-template-columns: 52px minmax(0, 1fr);
        gap: 14px;
        align-items: center;
        margin-bottom: 14px;
    }
    .announcement-icon {
        width: 50px;
        height: 50px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(139, 0, 0, .56);
        border: 1px solid rgba(250, 204, 21, .70);
        color: #facc15;
        box-shadow: 0 8px 18px rgba(0, 0, 0, .18);
    }
    .announcement-icon svg {
        width: 25px;
        height: 25px;
        stroke-width: 1.8;
    }
    .announcement-eyebrow {
        margin: 0 0 6px;
        color: #facc15;
        font-size: 9px;
        letter-spacing: 0;
        font-weight: 950;
        text-transform: uppercase;
    }
    .announcement-title {
        margin: 0;
        color: #f8fafc;
        font-size: 15px;
        line-height: 1.24;
        font-weight: 900;
        text-transform: uppercase;
    }
    .announcement-message {
        display: -webkit-box;
        margin: 0;
        overflow: hidden;
        color: #e2e8f0;
        font-size: 12px;
        line-height: 1.62;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 4;
    }
    .announcement-date {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        margin-top: auto;
        padding-top: 13px;
        color: #94a3b8;
        font-size: 10px;
        font-weight: 600;
    }
    .announcement-date svg {
        width: 13px;
        height: 13px;
    }
    .home-announcement-card.is-prev .announcement-card-head,
    .home-announcement-card.is-next-far .announcement-card-head,
    .home-announcement-shell.carousel-count-3 .home-announcement-card.is-next .announcement-card-head {
        grid-template-columns: 42px minmax(0, 1fr);
        gap: 10px;
    }
    .home-announcement-card.is-prev .announcement-icon,
    .home-announcement-card.is-next-far .announcement-icon,
    .home-announcement-shell.carousel-count-3 .home-announcement-card.is-next .announcement-icon {
        width: 40px;
        height: 40px;
    }
    .home-announcement-card.is-prev .announcement-icon svg,
    .home-announcement-card.is-next-far .announcement-icon svg,
    .home-announcement-shell.carousel-count-3 .home-announcement-card.is-next .announcement-icon svg {
        width: 20px;
        height: 20px;
    }
    .home-announcement-card.is-prev .announcement-title,
    .home-announcement-card.is-next-far .announcement-title,
    .home-announcement-shell.carousel-count-3 .home-announcement-card.is-next .announcement-title {
        font-size: 11px;
    }
    .home-announcement-card.is-prev .announcement-eyebrow,
    .home-announcement-card.is-next-far .announcement-eyebrow,
    .home-announcement-shell.carousel-count-3 .home-announcement-card.is-next .announcement-eyebrow {
        font-size: 7px;
    }
    .home-announcement-card.is-prev .announcement-message,
    .home-announcement-card.is-next-far .announcement-message,
    .home-announcement-shell.carousel-count-3 .home-announcement-card.is-next .announcement-message {
        font-size: 9px;
        -webkit-line-clamp: 4;
    }
    .announcement-modal {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 1300;
        align-items: center;
        justify-content: center;
        padding: 24px;
        background: rgba(3, 7, 18, .58);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
    }
    .announcement-modal.is-open {
        display: flex;
    }
    .announcement-modal-card {
        width: min(560px, 100%);
        overflow: hidden;
        border-radius: 18px;
        background: #fffaf7;
        border: 1px solid rgba(250, 204, 21, .34);
        box-shadow: 0 30px 80px rgba(15, 23, 42, .36);
    }
    .announcement-modal-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 18px;
        padding: 22px 24px;
        background: linear-gradient(135deg, #8B0000, #b91c1c);
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
        width: 40px;
        height: 40px;
        border-radius: 999px;
        border: 1px solid rgba(255,255,255,.24);
        background: rgba(255,255,255,.12);
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        flex: 0 0 auto;
    }
    .announcement-modal-close svg {
        width: 18px;
        height: 18px;
    }
    .announcement-modal-body {
        padding: 24px;
        color: #334155;
        line-height: 1.7;
        white-space: pre-wrap;
    }
    html[data-theme="dark"] .announcement-modal-card {
        background: #111827;
        border-color: rgba(250,204,21,.26);
    }
    html[data-theme="dark"] .announcement-modal-body {
        color: #f8fafc;
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
    .btn-outline { position:relative; overflow:hidden; display:inline-flex; align-items:center; gap:8px; padding:0; border-radius:0; border:0; color:#8B0000; background:transparent; text-decoration:none; margin-top:14px; font-weight:900; transition:transform .18s ease, color .18s ease; }
    .btn-outline::after {
        content:"";
        position:absolute;
        inset:0;
        background:linear-gradient(120deg, transparent 0%, rgba(255,255,255,.16) 28%, rgba(255,255,255,.55) 50%, rgba(255,255,255,.16) 72%, transparent 100%);
        transform:translateX(-135%);
        transition:transform .72s ease;
        pointer-events:none;
    }
    .btn-outline:hover,
    .btn-outline:focus-visible {
        background:transparent;
        border-color:transparent;
        color:#8B0000;
        transform:translateY(-2px);
        box-shadow:none;
        outline:none;
    }
    .btn-outline:hover::after,
    .btn-outline:focus-visible::after { transform:translateX(135%); }
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

    /* --- FOOTER STYLES --- */
    .site-footer {
        position: relative;
        overflow: hidden;
        color:#dbe4ee;
        padding: 36px 0 0;
        font-size: 15px;
        margin-top: 0;
        background:
            radial-gradient(circle at 14% 0%, rgba(190, 18, 60, .34), transparent 250px),
            radial-gradient(circle at 76% 6%, rgba(250, 204, 21, .10), transparent 220px),
            linear-gradient(135deg, rgba(48, 6, 18, .94), rgba(8, 17, 32, .98) 48%, rgba(13, 20, 37, .98)),
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
    }
    .brand { display:flex; align-items:center; gap:12px; margin-bottom:24px; }
    .brand-logo img { width:70px; height:70px; border-radius:50%; object-fit:cover; border:2px solid rgba(255,255,255,.72); box-shadow:0 0 20px rgba(250,204,21,.18); }
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
    .brand::after { position:absolute; left:98px; top:92px; }
    .footer-brand { position:relative; }
    .brand-desc { color:#c6d1dc; max-width:190px; line-height:1.8; margin:0 0 22px; }
    .social { display:flex; gap:14px; margin-top:18px; padding-top:22px; border-top:1px solid rgba(255,255,255,.08); }
    .social-link { width:46px; height:46px; border-radius:999px; display:inline-flex; align-items:center; justify-content:center; border:1px solid rgba(250,204,21,.30); background:rgba(139,0,0,.18); color:#facc15; transition:background 0.2s ease, transform 0.2s ease, border-color 0.2s ease; }
    .social-link:hover { background:rgba(139,0,0,.32); border-color:rgba(250,204,21,.62); transform:translateY(-2px); }
    .social-link svg { width:20px; height:20px; stroke:currentColor; fill:none; stroke-width:1.8; }

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
        border-top:1px solid rgba(239, 68, 68, .80);
        border-bottom: 2px solid rgba(255, 255, 255, .92);
        padding:18px 0 8px;
        text-align:center;
        color:rgba(255,255,255,0.72);
        font-size:14px;
        margin-top:0;
        background:
            radial-gradient(circle at 50% 0%, rgba(250,204,21,.38), transparent 100px),
            rgba(3, 10, 23, .52);
    }
    .footer-bottom svg {
        width: 14px;
        height: 14px;
        vertical-align: -2px;
        fill: currentColor;
    }
    .learn-more-modal {
        display:none;
        position:fixed;
        inset:0;
        z-index:1200;
        align-items:center;
        justify-content:center;
        padding:24px;
        background:transparent;
        backdrop-filter:none;
        -webkit-backdrop-filter:none;
        overflow-y:auto;
        pointer-events:none;
    }
    html.learn-more-open,
    body.learn-more-open {
        overflow:hidden;
    }
    .learn-more-modal.is-open { display:flex; }
    .learn-more-card {
        width:min(560px,100%);
        overflow:hidden;
        border-radius:18px;
        background:#fff;
        border-top:4px solid #facc15;
        border-bottom:4px solid #facc15;
        box-shadow:
            0 28px 80px rgba(15, 23, 42, 0.34),
            0 0 0 1px rgba(112, 19, 27, 0.08),
            0 18px 36px rgba(0, 0, 0, 0.18);
        pointer-events:auto;
    }
    .learn-more-head {
        display:flex;
        justify-content:space-between;
        gap:16px;
        padding:20px 22px;
        background:linear-gradient(135deg,#70131B,#8f2230);
        color:#fff;
    }
    .learn-more-head h3 { margin:0; color:#fff; font-size:22px; font-weight:900; }
    .learn-more-head p { margin:5px 0 0; color:rgba(255,255,255,.78); font-size:13px; line-height:1.5; }
    .learn-more-close {
        position:relative;
        overflow:hidden;
        isolation:isolate;
        width:42px;
        height:42px;
        border-radius:999px;
        border:1px solid rgba(255,255,255,.24);
        background:rgba(255,255,255,.12);
        color:#fff;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        cursor:pointer;
        transition:background .22s ease, border-color .22s ease, color .22s ease, transform .22s ease, box-shadow .22s ease;
    }
    .learn-more-close::before {
        content:"";
        position:absolute;
        inset:0;
        background:linear-gradient(120deg, transparent 0%, rgba(255,255,255,.14) 28%, rgba(255,255,255,.5) 50%, rgba(255,255,255,.14) 72%, transparent 100%);
        transform:translateX(-140%);
        transition:transform .72s ease;
        z-index:-1;
        pointer-events:none;
    }
    .learn-more-close:hover,
    .learn-more-close:focus-visible {
        background:#5e0f17;
        border-color:#facc15;
        color:#fff;
        transform:translateY(-1px);
        box-shadow:0 12px 24px rgba(94,15,23,.26);
        outline:none;
    }
    .learn-more-close:hover::before,
    .learn-more-close:focus-visible::before {
        transform:translateX(140%);
    }
    .learn-more-close svg { width:18px; height:18px; }
    .learn-more-body {
        display:grid;
        gap:12px;
        padding:22px;
    }
    .learn-more-info {
        padding:14px 16px;
        border-radius:14px;
        border:1px solid rgba(112,19,27,.12);
        background:#fffaf7;
        color:#334155;
        line-height:1.65;
    }
    html[data-theme="dark"] .learn-more-card { background:#111827; }
    html[data-theme="dark"] .learn-more-info {
        background:rgba(31,41,55,.92);
        border-color:rgba(250,204,21,.18);
        color:#f8fafc;
    }

   
    @keyframes slideUp {
        from { opacity: 0; transform: translateY(30px); }
        to { opacity: 1; transform: translateY(0); }
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
            padding: 78px 0 130px;
            text-align: center;
        }
        .hero-copy,
        .PUPBG-lead,
        .PUPBG-title { margin-left: auto; margin-right: auto; }
        .PUPBG-title::after { margin-left: auto; margin-right: auto; }
        .hero-actions { grid-template-columns: 1fr; width: min(420px, 100%); margin-left: auto; margin-right: auto; transform: none; }
        .hero-status-card { margin-left: auto; margin-right: auto; min-height: 72px; }
        .hero-action-card { min-height: 238px; }
        .why-grid { grid-template-columns: 1fr; gap: 14px; }
        .why-item {
            border-right: 0;
            padding: 0;
            width: min(360px, 100%);
            margin: 0 auto;
            text-align: left;
        }
        .home-announcement-band { min-height: 342px; }
        .home-announcement-shell {
            width: calc(100% - 52px);
            height: 286px;
            transform: translateY(22px);
        }
        .home-announcement-card.is-current,
        .home-announcement-card.is-next {
            width: 244px;
            min-height: 196px;
        }
        .home-announcement-card.is-current { left: calc(50% - 128px); }
        .home-announcement-card.is-next { left: calc(50% + 128px); }
        .home-announcement-card.is-prev { left: calc(50% - 330px); }
        .home-announcement-card.is-next-far { left: calc(50% + 330px); }
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
        .home-announcement-shell.carousel-count-3 .home-announcement-card.is-prev {
            left: calc(50% - 330px);
        }
        .home-announcement-shell.carousel-count-3 .home-announcement-card.is-next {
            left: calc(50% + 330px);
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
            min-height: 380px;
            margin-bottom: 28px;
        }
        .home-announcement-shell {
            width: calc(100% - 28px);
            height: 326px;
            transform: translateY(8px);
        }
        .home-announcement-heading {
            top: 18px;
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

        <div class="hero-actions">
          <a href="{{ url('/student/booking') }}" class="btn hero-action-card">
            <span class="hero-action-icon" aria-hidden="true">
              <x-outline-icon name="calendar-days" />
            </span>
            <span class="hero-action-copy">
              <span>Book</span>
              <span>Appointment</span>
            </span>
            <span class="hero-action-description">Schedule a consultation or medical appointment.</span>
            <x-outline-icon name="arrow-long-right" class="hero-action-arrow" />
          </a>

          <a href="{{ url('/student/history') }}" class="btn hero-action-card">
            <span class="hero-action-icon" aria-hidden="true">
              <x-outline-icon name="document-text" />
            </span>
            <span class="hero-action-copy">
              <span>View Appointments</span>
            </span>
            <span class="hero-action-description">Check your upcoming visits and appointment status.</span>
            <x-outline-icon name="arrow-long-right" class="hero-action-arrow" />
          </a>
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
        <h2 class="home-announcement-heading">Announcements</h2>
        <div class="home-announcement-shell {{ $announcementCount < 3 ? 'is-static static-count-' . $announcementCount : 'is-carousel carousel-count-' . $announcementCount }}" data-home-announcements>
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
              data-priority="{{ e($announcement['priority'] ?: 'ANNOUNCEMENT') }}"
              data-title="{{ e($announcement['title']) }}"
              data-message="{{ e($announcement['message']) }}"
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
              <p class="announcement-message">{{ \Illuminate\Support\Str::limit($announcement['message'], 175) }}</p>
              <span class="announcement-date">
                <x-outline-icon name="calendar-days" />
                <span>{{ $announcement['date'] ?? now(config('app.timezone'))->format('M j, Y') }}</span>
              </span>
            </article>
          @endforeach

          @if($announcementCount >= 3)
            <button type="button" class="announcement-nav prev" data-announcement-prev aria-label="Previous announcement">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m15.75 19.5-7.5-7.5 7.5-7.5" />
              </svg>
            </button>
            <button type="button" class="announcement-nav next" data-announcement-next aria-label="Next announcement">
              <x-outline-icon name="chevron-right" />
            </button>

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
      </div>

      <div id="about" class="container" style="scroll-margin-top: 100px;">
        <h2 class="about-section-title">Why choose PUP Taguig Clinic?</h2>

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
              <strong>Student-Centered</strong>
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
        </div>

        <section class="comments-section">
        <div class="section-head">
          <div>
            <h3>What students are saying...</h3>
            <p class="lead">Latest feedback on clinic services...</p>
          </div>
          @if(($feedbackCount ?? 0) > 3)
            <a href="{{ route('student.feedback.index') }}" class="feedback-more" aria-label="View more feedback" title="View more feedback">
              <x-outline-icon name="arrow-long-right" />
            </a>
          @endif
        </div>

        <div class="comments-grid">
          @forelse(($recentFeedback ?? []) as $feedback)
          <article class="comment-card" tabindex="0">
            <svg class="avatar" role="img" aria-label="User avatar"><use href="#avatar-placeholder"></use></svg>
            <div class="comment-body">
              <h4>{{ $feedback['name'] }} <span class="comment-meta">· {{ $feedback['role'] }} · {{ $feedback['time'] }}</span></h4>
              <p>{{ $feedback['message'] }}</p>
              <div class="comment-footer"><span class="comment-chip">{{ $feedback['service'] }}</span></div>
            </div>
          </article>
          @empty
          <article class="comment-card" tabindex="0">
            <svg class="avatar" role="img" aria-label="User avatar"><use href="#avatar-placeholder"></use></svg>
            <div class="comment-body">
              <h4>Jane D. <span class="comment-meta">· Student · 2 days ago</span></h4>
              <p>Quick and easy booking process — staff were helpful and the consultation was very informative. Highly recommended.</p>
              <div class="comment-footer"><span class="comment-chip">General Consultation</span></div>
            </div>
          </article>

          <article class="comment-card" tabindex="0">
            <svg class="avatar" role="img" aria-label="User avatar"><use href="#avatar-placeholder"></use></svg>
            <div class="comment-body">
              <h4>Mark R. <span class="comment-meta">· Faculty · 1 week ago</span></h4>
              <p>Received my medical certificate quickly. The online system saved me a lot of time.</p>
              <div class="comment-footer"><span class="comment-chip">Medical Certificates</span></div>
            </div>
          </article>

          <article class="comment-card" tabindex="0">
            <svg class="avatar" role="img" aria-label="User avatar"><use href="#avatar-placeholder"></use></svg>
            <div class="comment-body">
              <h4>Anna L. <span class="comment-meta">· Student · 3 weeks ago</span></h4>
              <p>Staff were friendly and the mental health support session was very helpful. Thank you!</p>
              <div class="comment-footer"><span class="comment-chip">Mental Health</span></div>
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
                <img src="{{ asset('images/pup_logo.png') }}" alt="PUP Taguig logo" />
              </div>
              <div>
                <div class="brand-name">PUP TAGUIG <span class="brand-sub">ONLINE CLINIC</span></div>
              </div>
            </div>
            <p class="brand-desc">Providing quality healthcare services to the PUP Taguig community.</p>

            <div class="social">
              <a class="social-link" href="#" aria-label="Official clinic site">
                <x-outline-icon name="globe-alt" />
              </a>
              <a class="social-link" href="#announcements" aria-label="Clinic announcements">
                <x-outline-icon name="megaphone" />
              </a>
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
              <li><span class="footer-service-item"><x-outline-icon name="heart-pulse" class="footer-service-icon" /><span>Mental Health Support</span></span></li>
              <li><span class="footer-service-item"><x-outline-icon name="link" class="footer-service-icon" /><span>Prescription Services</span></span></li>
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

      <div class="footer-bottom">
        <div class="container">Empowering minds. Building the future. <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Free v7.3.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2026 Fonticons, Inc.--><path d="M305 151.1L320 171.8L335 151.1C360 116.5 400.2 96 442.9 96C516.4 96 576 155.6 576 229.1L576 231.7C576 343.9 436.1 474.2 363.1 529.9C350.7 539.3 335.5 544 320 544C304.5 544 289.2 539.4 276.9 529.9C203.9 474.2 64 343.9 64 231.7L64 229.1C64 155.6 123.6 96 197.1 96C239.8 96 280 116.5 305 151.1z"/></svg></div>
      </div>
    </footer>

    <div class="learn-more-modal" id="learnMoreModal" aria-hidden="true">
      <section class="learn-more-card" role="dialog" aria-modal="true" aria-labelledby="learnMoreTitle">
        <div class="learn-more-head">
          <div>
            <h3 id="learnMoreTitle">About PUPT Clinic</h3>
            <p>Student-centered care through online access and campus clinic support.</p>
          </div>
          <button type="button" class="learn-more-close" id="learnMoreCloseBtn" aria-label="Close learn more modal">
            <x-outline-icon name="x-mark" />
          </button>
        </div>
        <div class="learn-more-body">
          <div class="learn-more-info">The PUP Taguig Clinic supports students with consultations, medical record processing, health clearance workflows, and appointment coordination.</div>
          <div class="learn-more-info">Public guests can explore clinic information. Students should sign in through One Portal to book appointments and access private records.</div>
        </div>
      </section>
    </div>

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
        <div class="announcement-modal-body" id="announcementDetailMessage"></div>
      </section>
    </div>

    <script>
      document.addEventListener('DOMContentLoaded', function () {
        const learnMoreBtn = document.getElementById('learnMoreBtn');
        const learnMoreModal = document.getElementById('learnMoreModal');
        const learnMoreCloseBtn = document.getElementById('learnMoreCloseBtn');
        const announcementDetailModal = document.getElementById('announcementDetailModal');
        const announcementDetailClose = document.getElementById('announcementDetailClose');
        const announcementDetailPriority = document.getElementById('announcementDetailPriority');
        const announcementDetailTitle = document.getElementById('announcementDetailTitle');
        const announcementDetailMessage = document.getElementById('announcementDetailMessage');
        const heroRotatingWord = document.getElementById('heroRotatingWord');
        const heroScrollLink = document.querySelector('.hero-scroll');
        const announcementShell = document.querySelector('[data-home-announcements]');

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

        if (learnMoreModal && learnMoreModal.parentElement !== document.body) {
          document.body.appendChild(learnMoreModal);
        }
        if (announcementDetailModal && announcementDetailModal.parentElement !== document.body) {
          document.body.appendChild(announcementDetailModal);
        }

        function setLearnMoreOpen(isOpen) {
          if (!learnMoreModal) return;
          learnMoreModal.classList.toggle('is-open', isOpen);
          learnMoreModal.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
          document.documentElement.classList.toggle('learn-more-open', isOpen);
          document.body.classList.toggle('learn-more-open', isOpen);
        }

        if (learnMoreBtn) {
          learnMoreBtn.addEventListener('click', function (event) {
            event.preventDefault();
            setLearnMoreOpen(true);
          });
        }

        if (learnMoreCloseBtn) {
          learnMoreCloseBtn.addEventListener('click', function () {
            setLearnMoreOpen(false);
          });
        }

        if (learnMoreModal) {
          learnMoreModal.addEventListener('click', function (event) {
            if (event.target === learnMoreModal) {
              setLearnMoreOpen(false);
            }
          });
        }

        function setAnnouncementDetailOpen(isOpen, trigger = null) {
          if (!announcementDetailModal) return;

          if (trigger && isOpen) {
            if (announcementDetailPriority) announcementDetailPriority.textContent = trigger.dataset.priority || 'ANNOUNCEMENT';
            if (announcementDetailTitle) announcementDetailTitle.textContent = trigger.dataset.title || 'Announcement';
            if (announcementDetailMessage) announcementDetailMessage.textContent = trigger.dataset.message || '';
          }

          announcementDetailModal.classList.toggle('is-open', isOpen);
          announcementDetailModal.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
        }

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

        announcementDetailClose?.addEventListener('click', function () {
          setAnnouncementDetailOpen(false);
        });

        announcementDetailModal?.addEventListener('click', function (event) {
          if (event.target === announcementDetailModal) {
            setAnnouncementDetailOpen(false);
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
            if (slides.length >= 3) {
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

        const homeNavLink = document.querySelector('[data-student-nav="home"]');
        const aboutNavLink = document.querySelector('[data-student-nav="about"]');
        const aboutArea = document.getElementById('homeAboutArea');

        function setHomeNavState(isAboutActive) {
          if (!homeNavLink || !aboutNavLink) return;
          homeNavLink.classList.toggle('active', !isAboutActive);
          aboutNavLink.classList.toggle('active', isAboutActive);
        }

        if (aboutArea && homeNavLink && aboutNavLink) {
          if ('IntersectionObserver' in window) {
            const aboutObserver = new IntersectionObserver(function (entries) {
              const entry = entries[0];
              setHomeNavState(entry.isIntersecting && entry.intersectionRatio > 0.18);
            }, {
              root: null,
              threshold: [0, 0.18, 0.4],
              rootMargin: '-96px 0px -45% 0px'
            });

            aboutObserver.observe(aboutArea);
          } else {
            const syncHomeNav = function () {
              const rect = aboutArea.getBoundingClientRect();
              const viewportTrigger = window.innerHeight * 0.55;
              setHomeNavState(rect.top < viewportTrigger && rect.bottom > 120);
            };

            syncHomeNav();
            window.addEventListener('scroll', syncHomeNav, { passive: true });
            window.addEventListener('resize', syncHomeNav);
          }
        }
      });
    </script>

@endsection
