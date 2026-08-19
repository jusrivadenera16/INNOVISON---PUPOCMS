<style>
    .access-console {
        max-width: 1420px;
        margin: 0 auto;
        padding: 18px 20px 36px;
        color: #202332;
    }

    .access-console__hero,
    .access-console__panel,
    .access-console__stat {
        background: rgba(255, 255, 255, .96);
        border: 1px solid #eee4e6;
        box-shadow: 0 10px 30px rgba(55, 21, 26, .06);
    }

    .access-console__hero {
        min-height: 90px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 22px;
        padding: 18px 20px;
        border-left: 1px solid #eee4e6;
        border-radius: 16px;
    }

    .access-console__hero-title {
        display: flex;
        align-items: center;
        gap: 14px;
        margin: 0;
        color: #211b20;
        font-size: 26px;
        font-weight: 900;
        letter-spacing: 0;
    }

    .access-console__hero-icon,
    .access-console__stat-icon,
    .access-console__filter-icon {
        display: inline-grid;
        place-items: center;
        flex: 0 0 auto;
    }

    .access-console__hero-icon {
        width: 54px;
        height: 54px;
        color: #8f1222;
        background: #fff0f2;
        border-radius: 14px;
    }

    .access-console__hero-icon svg { width: 28px; height: 28px; }

    .access-console__hero-copy {
        margin: 6px 0 0 68px;
        color: #727684;
        font-size: 13px;
        font-weight: 700;
    }

    .access-console__sync {
        display: grid;
        grid-template-columns: 42px auto;
        gap: 10px;
        align-items: center;
        min-width: 180px;
        color: #0f172a;
    }
    .access-console__sync-icon {
        display: grid;
        place-items: center;
        width: 42px;
        height: 42px;
        border-radius: 12px;
        color: #b91c1c;
        background: #fff1f2;
    }
    .access-console__sync-icon svg { width: 20px; height: 20px; }
    .access-console__sync span:not(.access-console__sync-icon),
    .access-console__sync strong,
    .access-console__sync small { display: block; }
    .access-console__sync span:not(.access-console__sync-icon) { color: #64748b; font-size: 11px; font-weight: 900; letter-spacing: .04em; text-transform: uppercase; }
    .access-console__sync strong { margin-top: 2px; color: #0f172a; font-size: 13px; font-weight: 900; line-height: 1.25; }
    .access-console__sync small { margin-top: 2px; color: #737887; font-size: 11px; font-weight: 700; }

    .access-console__stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
        margin: 14px 0 18px;
    }

    .access-console__stat {
        position: relative;
        display: flex;
        align-items: center;
        min-height: 100px;
        padding: 14px;
        border-radius: 14px;
        cursor: default;
        overflow: visible;
        transition: transform .18s ease, border-color .18s ease, box-shadow .18s ease, background .18s ease;
    }

    .access-console__stat-icon {
        width: 44px;
        height: 44px;
        margin-right: 14px;
        border-radius: 50%;
    }

    .access-console__stat-icon svg { width: 25px; height: 25px; }
    .access-console__stat:nth-child(1) .access-console__stat-icon { color: #a2152b; background: #fff0f2; }
    .access-console__stat:nth-child(2) .access-console__stat-icon { color: #108247; background: #eaf8ef; }
    .access-console__stat:nth-child(3) .access-console__stat-icon { color: #b55323; background: #fff1e8; }
    .access-console__stat:nth-child(4) .access-console__stat-icon { color: #2563b8; background: #edf4ff; }
    .access-console__stat-label { display: block; color: #111827; font-size: 11px; font-weight: 900; letter-spacing: .04em; text-transform: uppercase; }
    .access-console__stat-value { display: block; margin-top: 6px; color: #0f172a; font-size: 24px; font-weight: 900; line-height: 1; }
    .access-console__stat-note { display: block; margin-top: 7px; color: #64748b; font-size: 12px; font-weight: 900; }
    .access-console__stat:nth-child(3),
    .access-console__stat:nth-child(4) { border-color: #eee4e6; background: #fff; }
    .access-console__stat:nth-child(3) .access-console__stat-icon,
    .access-console__stat:nth-child(4) .access-console__stat-icon { background: #fff1e8; }
    .access-console__stat:nth-child(3) .access-console__stat-icon { color: #b55323; }
    .access-console__stat:nth-child(4) .access-console__stat-icon { color: #2563b8; background: #edf4ff; }
    .access-console__stat:nth-child(3) .access-console__stat-label,
    .access-console__stat:nth-child(3) .access-console__stat-value,
    .access-console__stat:nth-child(3) .access-console__stat-note,
    .access-console__stat:nth-child(4) .access-console__stat-label,
    .access-console__stat:nth-child(4) .access-console__stat-value,
    .access-console__stat:nth-child(4) .access-console__stat-note { color: #111827; }
    .access-console__summary-card { cursor: pointer; }
    .access-console__stat:hover { transform: translateY(-3px); border-color: #facc15; box-shadow: 0 18px 34px rgba(112, 19, 27, .14); background: #fffaf0; }
    .access-console__stat:hover .access-console__stat-label,
    .access-console__stat:hover .access-console__stat-value,
    .access-console__stat:hover .access-console__stat-note { color: #70131b; }
    .access-console__stat:hover .access-console__stat-icon { color: #70131b; background: rgba(112, 19, 27, .14); }

    .access-console__panel { border-radius: 11px; overflow: visible; }
    .access-console__filters { display: flex; align-items: center; gap: 10px; padding: 12px; border-bottom: 1px solid #f0e7e8; }
    .access-console__search { position: relative; flex: 1 1 250px; min-width: 190px; }
    .access-console__search svg { position: absolute; top: 50%; left: 12px; width: 16px; height: 16px; color: #8b4350; transform: translateY(-50%); }
    .access-console__search input,
    .access-console__filter {
        width: 100%;
        height: 36px;
        border: 1px solid #eadfe2;
        border-radius: 7px;
        background: #fff;
        color: #373a46;
        font: inherit;
        font-size: 13px;
        outline: none;
    }
    .access-console__search input { padding: 0 12px 0 35px; }
    .access-console__filter { width: 132px; padding: 0 28px 0 10px; }
    .access-console__search input:focus,
    .access-console__filter:focus { border-color: #9e1d31; box-shadow: 0 0 0 3px rgba(158, 29, 49, .09); }
    .access-console__add {
        position: relative;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        height: 36px;
        padding: 0 15px;
        border: 0;
        border-radius: 7px;
        color: #fff;
        background: #8f1020;
        overflow: hidden;
        box-shadow: 0 10px 22px rgba(112, 19, 27, .20);
        font: 900 13px inherit;
        cursor: pointer;
        transition: color .18s ease, border-color .18s ease, background .18s ease, box-shadow .18s ease, transform .18s ease;
    }
    .access-console__add::after { content: ''; position: absolute; top: -40%; left: -90%; width: 48%; height: 180%; background: linear-gradient(115deg, transparent 0%, rgba(255, 247, 181, .65) 50%, transparent 100%); transform: skewX(-18deg); transition: left .48s ease; pointer-events: none; }
    .access-console__add > * { position: relative; z-index: 1; }
    .access-console__add:hover { color: #76101c; background: #ffd21f; box-shadow: 0 15px 28px rgba(112, 19, 27, .25); transform: translateY(-2px); }
    .access-console__add:hover::after { left: 145%; }
    .access-console__add svg { width: 15px; height: 15px; }

    .access-console__list { min-height: 180px; }
    .access-console__row {
        display: grid;
        grid-template-columns: 48px minmax(240px, 1fr) minmax(210px, .82fr) minmax(155px, .55fr) 95px;
        align-items: center;
        gap: 13px;
        padding: 12px 15px;
        border-bottom: 1px solid #f3eaeb;
        cursor: pointer;
        transition: background .18s ease;
    }
    .access-console__row:last-child { border-bottom: 0; }
    .access-console__row:hover { background: #fff9fa; }
    .access-console__initial {
        display: inline-grid;
        place-items: center;
        width: 43px;
        height: 43px;
        border: 1px solid #f1dce0;
        border-radius: 50%;
        color: #8b1423;
        background: #fff0f2;
        font-size: .86rem;
        font-weight: 800;
    }
    .access-console__person { min-width: 0; }

    .access-summary-modal {
        position: fixed;
        z-index: 1200;
        inset: 0;
        display: grid;
        place-items: center;
        padding: 20px;
        background: rgba(15, 23, 42, .48);
        opacity: 0;
        pointer-events: none;
        transition: opacity .18s ease;
    }
    .access-summary-modal.is-open { opacity: 1; pointer-events: auto; }
    .access-summary-modal__card {
        width: min(390px, 100%);
        padding: 20px;
        border: 1px solid #eadfe2;
        border-radius: 10px;
        color: #1f2937;
        background: #fff;
        box-shadow: 0 22px 48px rgba(15, 23, 42, .26);
        transform: translateY(10px) scale(.98);
        transition: transform .18s ease;
    }
    .access-summary-modal.is-open .access-summary-modal__card { transform: translateY(0) scale(1); }
    .access-summary-modal__head { display: flex; align-items: flex-start; justify-content: space-between; gap: 14px; }
    .access-summary-modal__eyebrow { margin: 0; color: #8f1020; font-size: 11px; font-weight: 900; letter-spacing: .04em; text-transform: uppercase; }
    .access-summary-modal__title { margin: 5px 0 0; color: #111827; font-size: 20px; font-weight: 900; line-height: 1.15; }
    .access-summary-modal__close { position: relative; display: grid; place-items: center; width: 34px; height: 34px; overflow: hidden; border: 1px solid #eadfe2; border-radius: 50%; color: #8f1020; background-color: #fff; background-image: linear-gradient(90deg, #ffd21f, #ffd21f); background-repeat: no-repeat; background-position: left center; background-size: 0 100%; font-size: 22px; line-height: 1; cursor: pointer; transition: color .2s ease, border-color .2s ease, background-size .24s ease, transform .2s ease; }
    .access-summary-modal__close:hover { color: #7b101f; border-color: #ffd21f; background-size: 100% 100%; transform: translateY(-1px); }
    .access-summary-modal__value { margin-top: 20px; color: #8f1020; font-size: 34px; font-weight: 900; line-height: 1; }
    .access-summary-modal__copy { margin: 10px 0 0; color: #64748b; font-size: 13px; font-weight: 700; line-height: 1.55; }
    .access-console__name { display: inline-block; max-width: 100%; color: #252833; font-size: 14px; font-weight: 800; vertical-align: middle; }
    .access-console__email { display: block; overflow: hidden; margin-top: 4px; color: #717684; font-size: 12px; text-overflow: ellipsis; white-space: nowrap; }
    .access-console__email svg { width: 12px; height: 12px; margin-right: 4px; vertical-align: -2px; }
    .access-console__meta { display: grid; grid-template-columns: minmax(0, 1fr); align-content: center; gap: 5px; min-width: 0; color: #565c68; font-size: 12px; }
    .access-console__tag,
    .access-console__role { display: inline-flex; box-sizing: border-box; width: 100%; min-height: 25px; align-items: center; gap: 5px; padding: 4px 7px; border-radius: 5px; overflow: hidden; font-size: 11px; font-weight: 700; line-height: 1.2; white-space: nowrap; text-overflow: ellipsis; }
    .access-console__tag { color: #7d1625; background: #fff0f2; }
    .access-console__role { color: #5c6370; border: 1px solid #edf0f3; background: #f8fafc; }
    .access-console__tag svg,
    .access-console__role svg { width: 12px; height: 12px; flex: 0 0 auto; }
    .access-console__role svg { color: #8f1020; }
    .access-console__state { color: #666d7b; font-size: 12px; }
    .access-console__state strong { display: inline-flex; align-items: center; gap: 5px; padding: 4px 8px; border-radius: 999px; color: #14733e; background: #e9f8ee; font-size: 11px; }
    .access-console__state strong::before { content: ''; width: 6px; height: 6px; border-radius: 999px; background: #1eab59; }
    .access-console__state--inactive strong { color: #ad2336; background: #fff0f2; }
    .access-console__state--inactive strong::before { background: #d6384c; }
    .access-console__state small { display: block; margin-top: 7px; color: #898e99; font-size: 11px; }
    .access-console__manage { height: 34px; border: 1px solid #dca2aa; border-radius: 6px; color: #841423; background: #fff; font: 900 12px inherit; cursor: pointer; }
    .access-console__manage:hover { color: #76101c; background: #ffd21f; border-color: #ffd21f; }
    .access-console__empty { padding: 42px 20px; color: #737885; text-align: center; font-size: .74rem; }
    .access-console__footer { display: flex; justify-content: space-between; align-items: center; padding: 11px 15px; color: #747986; border-top: 1px solid #f3eaeb; font-size: .66rem; }

    /* Add access modal */
    #lookupModal .access-onboard-modal { display: flex; flex-direction: column; width: min(1000px, calc(100vw - 28px)); height: min(720px, calc(100vh - 28px)); max-height: min(720px, calc(100vh - 28px)); border-radius: 10px; overflow: hidden; }
    #lookupModal .access-onboard-modal .um-modal-head { padding: 15px 20px; color: #fff; border: 0; background: #8f1020; }
    #lookupModal .access-onboard-modal .um-modal-head h3,
    #lookupModal .access-onboard-modal .um-modal-head .um-note { color: #fff; }
    #lookupModal .access-onboard-modal .um-modal-head h3 { font-size: 1.05rem; }
    #lookupModal .access-onboard-modal .um-modal-head .um-note { margin-top: 4px; font-size: .79rem; }
    #lookupModal .access-onboard-modal .um-modal-head-badge { color: #fff; border-color: rgba(255,255,255,.22); background: rgba(255,255,255,.13); }
    #lookupModal .access-onboard-modal .um-modal-close { color: #fff; border-color: rgba(255,255,255,.18); background: rgba(57, 4, 12, .2); }
    .access-onboard-steps { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; padding: 14px 20px; border-bottom: 1px solid #eee7e8; }
    .access-onboard-step { position: relative; display: grid; grid-template-columns: 29px 1fr; gap: 8px; align-items: center; color: #6f7481; font-size: .75rem; }
    .access-onboard-step::after { content: ''; position: absolute; top: 14px; right: -8px; width: 16px; height: 1px; background: #e7dfe1; }
    .access-onboard-step:last-child::after { display: none; }
    .access-onboard-step__number { display: grid; place-items: center; width: 28px; height: 28px; border-radius: 50%; color: #5c6070; background: #f2f3f5; font-weight: 800; }
    .access-onboard-step.is-current { color: #831424; font-weight: 800; }
    .access-onboard-step.is-current .access-onboard-step__number { color: #fff; background: #8f1020; }
    .access-onboard-step strong,
    .access-onboard-step small { display: block; }
    .access-onboard-step small { margin-top: 2px; color: #89909c; font-size: .68rem; font-weight: 500; }
    #lookupModal .access-onboard-modal .um-modal-body { flex: 1 1 auto; min-height: 0; padding: 0; overflow: hidden; }
    .access-onboard-layout { display: grid; grid-template-columns: minmax(310px, .78fr) minmax(0, 1.22fr); min-height: 0; height: 100%; }
    .access-onboard-search { min-height: 0; padding: 15px 16px; border-right: 1px solid #eee7e8; overflow-y: auto; }
    .access-onboard-search .um-search { display: flex; gap: 8px; }
    .access-onboard-search .um-search input { height: 38px; min-width: 0; font-size: .84rem; }
    .access-onboard-search .um-search .um-btn { height: 38px; padding: 0 15px; border-radius: 6px; font-size: .76rem; }
    .access-onboard-filter-row { display: flex; gap: 8px; margin: 10px 0; }
    .access-onboard-filter-row select { flex: 1; height: 32px; min-width: 0; padding: 0 8px; border: 1px solid #e8e0e2; border-radius: 6px; color: #565d6b; background: #fff; font-size: .72rem; }
    .access-onboard-count { display: flex; justify-content: space-between; color: #811323; font-size: .7rem; }
    .access-onboard-count span:last-child { color: #787e89; }
    #lookupModal .access-onboard-modal .um-directory-panel { display: block; margin-top: 10px !important; }
    #lookupModal .access-onboard-modal .um-table-wrap { overflow: visible; }
    #lookupModal .access-onboard-modal .um-table { min-width: 0 !important; border: 0; }
    #lookupModal .access-onboard-modal .um-table thead { display: none; }
    #lookupModal .access-onboard-modal .um-table tbody,
    #lookupModal .access-onboard-modal .um-table tr { display: block; }
    #lookupModal .access-onboard-modal .um-table tr { position: relative; min-height: 65px; padding: 9px 38px 9px 12px; border: 1px solid transparent; border-bottom-color: #f0e9ea; }
    #lookupModal .access-onboard-modal .um-table tr:hover,
    #lookupModal .access-onboard-modal .um-table tr.is-selected { border-color: #b51d31; background: #fff5f6; }
    #lookupModal .access-onboard-modal .um-table tr::after { content: '\203A'; position: absolute; top: 20px; right: 13px; color: #8d1827; font-size: 1.3rem; }
    #lookupModal .access-onboard-modal .um-table td { display: none; padding: 0; border: 0; }
    #lookupModal .access-onboard-modal .um-table td:first-child { display: block; }
    #lookupModal .access-onboard-modal .um-user { align-items: center; }
    #lookupModal .access-onboard-modal .um-avatar { width: 33px; height: 33px; font-size: .7rem; }
    #lookupModal .access-onboard-modal .um-name { font-size: .78rem; }
    #lookupModal .access-onboard-modal .um-sub { font-size: .68rem; }
    .access-onboard-profile { min-height: 0; padding: 20px 22px; overflow-y: auto; }
    .access-onboard-profile__eyebrow { margin: 0; color: #811323; font-size: .8rem; font-weight: 800; }
    .access-onboard-profile__empty { display: grid; place-items: center; min-height: 125px; color: #9aa0ab; text-align: center; font-size: .84rem; }
    .access-onboard-profile__identity { display: none; align-items: center; gap: 12px; min-height: 78px; }
    .access-onboard-profile.has-selection .access-onboard-profile__identity { display: flex; }
    .access-onboard-profile.has-selection .access-onboard-profile__empty { display: none; }
    .access-onboard-profile__avatar { display: grid; place-items: center; width: 58px; height: 58px; border-radius: 50%; color: #fff; background: #801020; font-size: 1rem; font-weight: 800; }
    .access-onboard-profile__name { color: #272a34; font-size: 1rem; font-weight: 800; line-height: 1.15; }
    .access-onboard-profile__email { margin-top: 2px; color: #757b88; font-size: .78rem; line-height: 1.25; }
    .access-onboard-profile__identifier { margin-top: 4px; color: #7d8491; font-size: .67rem; line-height: 1.15; }
    .access-onboard-profile__identifier span { display: block; font-weight: 700; letter-spacing: .02em; text-transform: uppercase; }
    .access-onboard-profile__identifier strong { display: block; margin-top: 1px; color: #30343d; font-size: .76rem; font-weight: 800; text-transform: none; }
    .access-onboard-profile__details { display: none; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 14px; padding: 12px; border: 1px solid #eee6e8; border-radius: 7px; color: #606674; font-size: .76rem; }
    .access-onboard-profile.has-selection .access-onboard-profile__details { display: grid; }
    .access-onboard-profile__details strong { display: block; margin-top: 3px; color: #30343d; font-size: .78rem; }
    .access-onboard-profile__access { display: none; }
    .access-onboard-profile.has-selection .access-onboard-profile__access { display: block; }
    .access-onboard-role-title { margin: 20px 0 9px; color: #8b1423; font-size: .8rem; font-weight: 800; }
    .access-onboard-role-option { display: flex; align-items: center; gap: 9px; padding: 11px; border: 1px solid #eadfe1; border-radius: 6px; color: #393d49; font-size: .78rem; cursor: pointer; }
    .access-onboard-role-option + .access-onboard-role-option { margin-top: 7px; }
    .access-onboard-role-option input { accent-color: #8f1020; }
    .access-onboard-role-option strong,
    .access-onboard-role-option small { display: block; }
    .access-onboard-role-option small { margin-top: 3px; color: #838996; font-size: .71rem; }
    .access-onboard-role-option:has(input:checked) { border-color: #a2182a; background: #fff4f5; }
    .access-onboard-module-slot { margin-top: 16px; }
    #lookupModuleAccessSlot .um-module-access-preview { margin: 0; padding: 14px; border: 1px solid #eadfe2; border-radius: 8px; background: #fafcff; }
    #lookupModuleAccessSlot .um-module-access-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 10px; margin-bottom: 11px; }
    #lookupModuleAccessSlot .um-module-access-head h5 { margin: 0; color: #811323; font-size: .92rem; font-weight: 900; }
    #lookupModuleAccessSlot .um-module-access-head p { margin: 4px 0 0; color: #677184; font-size: .72rem; line-height: 1.4; }
    #lookupModuleAccessSlot .um-preview-badge { display: inline-flex; min-height: 23px; flex: 0 0 auto; align-items: center; padding: 3px 7px; border: 1px solid #f3d08a; border-radius: 5px; color: #92400e; background: #fffbeb; font-size: 9px; font-weight: 900; text-transform: uppercase; }
    #lookupModuleAccessSlot .um-module-access-toolbar { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 10px; padding: 9px 10px; border: 1px solid #e4e9ef; border-radius: 7px; background: #fff; }
    #lookupModuleAccessSlot .um-module-selection-summary { min-width: 0; }
    #lookupModuleAccessSlot .um-module-selection-summary strong { display: block; color: #2f3643; font-size: .76rem; font-weight: 900; }
    #lookupModuleAccessSlot .um-module-selection-summary span { display: block; margin-top: 3px; color: #758093; font-size: .65rem; font-weight: 650; }
    #lookupModuleAccessSlot .um-reset-module-defaults { min-height: 29px; flex: 0 0 auto; padding: 6px 8px; border: 1px solid #d7b7ba; border-radius: 6px; color: #70131b; background: #fff; font-size: .63rem; font-weight: 900; cursor: pointer; }
    #lookupModuleAccessSlot .um-reset-module-defaults:hover,
    #lookupModuleAccessSlot .um-reset-module-defaults:focus-visible { border-color: #8f1020; color: #70131b; background: #ffd21f; outline: none; }
    #lookupModuleAccessSlot .um-module-access-grid { display: grid; grid-template-columns: 1fr; gap: 7px; }
    #lookupModuleAccessSlot .um-module-item { overflow: hidden; border: 1px solid #e2e8f0; border-radius: 7px; background: #fff; transition: border-color .18s ease, background .18s ease, box-shadow .18s ease; }
    #lookupModuleAccessSlot .um-module-item:hover { border-color: rgba(112, 19, 27, .3); }
    #lookupModuleAccessSlot .um-module-item:has(.um-module-option input:checked) { border-color: rgba(112, 19, 27, .34); background: #fffafa; box-shadow: inset 3px 0 #8f1020; }
    #lookupModuleAccessSlot .um-module-item.is-disabled { opacity: .62; background: #f8fafc; }
    #lookupModuleAccessSlot .um-module-row { display: grid; grid-template-columns: minmax(0, 1fr) auto; align-items: center; }
    #lookupModuleAccessSlot .um-module-option { position: relative; display: grid; grid-template-columns: 29px minmax(0, 1fr) 18px; min-height: 57px; margin: 0; padding: 8px 8px 8px 10px; align-items: center; gap: 9px; border: 0; border-radius: 0; background: transparent; cursor: pointer; text-transform: none; letter-spacing: 0; }
    #lookupModuleAccessSlot .um-module-option input,
    #lookupModuleAccessSlot .um-action-permission input { position: absolute; width: 1px; height: 1px; margin: 0; opacity: 0; }
    #lookupModuleAccessSlot .um-module-icon { display: inline-flex; width: 29px; height: 29px; align-items: center; justify-content: center; border-radius: 7px; color: #8f1020; background: #fff0f2; }
    #lookupModuleAccessSlot .um-module-icon svg { width: 15px !important; height: 15px !important; }
    #lookupModuleAccessSlot .um-module-copy { min-width: 0; }
    #lookupModuleAccessSlot .um-module-title { display: flex; min-width: 0; align-items: center; gap: 6px; }
    #lookupModuleAccessSlot .um-module-title strong { display: block; min-width: 0; overflow: hidden; color: #27303d; font-size: .76rem; font-weight: 900; text-overflow: ellipsis; white-space: nowrap; }
    #lookupModuleAccessSlot .um-module-title > span { display: inline-flex; min-height: 16px; flex: 0 0 auto; align-items: center; padding: 2px 5px; border-radius: 4px; color: #475569; background: #f1f5f9; font-size: 8px; font-weight: 900; text-transform: uppercase; }
    #lookupModuleAccessSlot .um-module-copy small { display: block; margin-top: 3px; color: #718096; font-size: .66rem; font-weight: 600; line-height: 1.3; }
    #lookupModuleAccessSlot .um-module-check { display: inline-flex; width: 17px; height: 17px; align-items: center; justify-content: center; border: 1px solid #cbd5e1; border-radius: 5px; color: transparent; background: #fff; }
    #lookupModuleAccessSlot .um-module-check svg { width: 11px !important; height: 11px !important; stroke-width: 2.5; }
    #lookupModuleAccessSlot .um-module-option input:checked ~ .um-module-check { border-color: #8f1020; color: #fff; background: #8f1020; }
    #lookupModuleAccessSlot .um-module-expand { display: inline-flex; width: 29px; height: 29px; margin-right: 8px; padding: 0; align-items: center; justify-content: center; border: 1px solid #e2e8f0; border-radius: 6px; color: #70131b; background: #fff; cursor: pointer; }
    #lookupModuleAccessSlot .um-module-expand svg { width: 14px !important; height: 14px !important; transition: transform .18s ease; }
    #lookupModuleAccessSlot .um-module-expand:hover,
    #lookupModuleAccessSlot .um-module-expand:focus-visible { border-color: #8f1020; color: #70131b; background: #ffd21f; outline: none; }
    #lookupModuleAccessSlot .um-module-expand[aria-expanded="true"] svg { transform: rotate(180deg); }
    #lookupModuleAccessSlot .um-module-actions { padding: 2px 10px 10px 46px; border-top: 1px solid #f1f5f9; background: #f8fafc; }
    #lookupModuleAccessSlot .um-module-actions[hidden] { display: none; }
    #lookupModuleAccessSlot .um-module-actions-head { display: flex; align-items: center; justify-content: space-between; gap: 8px; padding: 8px 0 5px; }
    #lookupModuleAccessSlot .um-module-actions-head strong { color: #475569; font-size: .62rem; font-weight: 900; text-transform: uppercase; }
    #lookupModuleAccessSlot .um-module-actions-head span { color: #94a3b8; font-size: .58rem; font-weight: 700; }
    #lookupModuleAccessSlot .um-action-permission { position: relative; display: grid; grid-template-columns: 17px minmax(0, 1fr); min-height: 42px; margin: 0; padding: 7px 8px; align-items: center; gap: 8px; border-top: 1px solid #eef2f7; color: #1e293b; cursor: pointer; text-transform: none; letter-spacing: 0; }
    #lookupModuleAccessSlot .um-action-permission:has(input:checked) { background: #fff7f7; }
    #lookupModuleAccessSlot .um-action-check { display: inline-flex; width: 16px; height: 16px; align-items: center; justify-content: center; border: 1px solid #cbd5e1; border-radius: 4px; color: transparent; background: #fff; }
    #lookupModuleAccessSlot .um-action-check svg { width: 10px !important; height: 10px !important; stroke-width: 2.5; }
    #lookupModuleAccessSlot .um-action-permission input:checked + .um-action-check { border-color: #8f1020; color: #fff; background: #8f1020; }
    #lookupModuleAccessSlot .um-action-permission strong { display: block; color: #334155; font-size: .7rem; font-weight: 850; }
    #lookupModuleAccessSlot .um-action-permission small { display: block; margin-top: 2px; color: #718096; font-size: .62rem; font-weight: 600; line-height: 1.3; }
    #lookupModuleAccessSlot .um-action-permission.is-locked { grid-template-columns: 20px minmax(0, 1fr) auto; cursor: default; background: #fff8e6; }
    #lookupModuleAccessSlot .um-action-lock { display: inline-flex; width: 19px; height: 19px; align-items: center; justify-content: center; border-radius: 50%; color: #fff; background: #70131b; }
    #lookupModuleAccessSlot .um-action-lock svg { width: 11px !important; height: 11px !important; }
    #lookupModuleAccessSlot .um-locked-badge { display: inline-flex; min-height: 19px; align-items: center; padding: 3px 5px; border-radius: 4px; color: #92400e; background: #fef3c7; font-size: .58rem; font-weight: 900; text-transform: uppercase; }
    #lookupModuleAccessSlot .um-superadmin-access-summary { display: grid; grid-template-columns: 29px minmax(0, 1fr); gap: 9px; margin-top: 9px; padding: 9px; border: 1px solid #ead8b1; border-radius: 7px; color: #78350f; background: #fffbeb; }
    #lookupModuleAccessSlot .um-superadmin-summary-icon { display: inline-flex; width: 29px; height: 29px; align-items: center; justify-content: center; border-radius: 7px; color: #fff; background: #70131b; }
    #lookupModuleAccessSlot .um-superadmin-summary-icon svg { width: 15px !important; height: 15px !important; }
    #lookupModuleAccessSlot .um-superadmin-access-summary strong { display: block; color: #70131b; font-size: .7rem; font-weight: 900; }
    #lookupModuleAccessSlot .um-superadmin-access-list { display: flex; flex-wrap: wrap; gap: 4px; margin-top: 5px; }
    #lookupModuleAccessSlot .um-superadmin-access-list span { display: inline-flex; min-height: 18px; align-items: center; padding: 3px 5px; border: 1px solid #f0dfbb; border-radius: 4px; color: #78350f; background: #fff; font-size: .58rem; font-weight: 750; }
    .access-onboard-footer { flex: 0 0 auto; display: flex; justify-content: space-between; align-items: center; padding: 12px 17px; border-top: 1px solid #eee7e8; }
    .access-onboard-footer small { color: #818692; font-size: .71rem; }
    .access-onboard-footer__actions { display: flex; gap: 8px; }
    .access-onboard-footer button { height: 34px; padding: 0 16px; border-radius: 6px; font: 700 .75rem inherit; cursor: pointer; }
    .access-onboard-cancel,
    .access-onboard-continue:not(:disabled) { position: relative; isolation: isolate; overflow: hidden; transition: color .18s ease, border-color .18s ease, background .18s ease, box-shadow .18s ease, transform .18s ease; }
    .access-onboard-cancel::after,
    .access-onboard-continue:not(:disabled)::after { content: ''; position: absolute; inset: 0; z-index: -1; background: linear-gradient(120deg, transparent, rgba(255, 248, 196, .56), transparent); transform: translateX(-135%); transition: transform .7s ease; }
    .access-onboard-cancel { color: #5e6674; border: 1px solid #d8dce2; background: #fff; }
    .access-onboard-continue { color: #fff; border: 1px solid #8f1020; background: #8f1020; }
    .access-onboard-cancel:hover,
    .access-onboard-cancel:focus-visible,
    .access-onboard-continue:not(:disabled):hover,
    .access-onboard-continue:not(:disabled):focus-visible { color: #70131b !important; border-color: #ffd21f; background: #ffd21f; box-shadow: 0 0 0 3px rgba(255, 210, 31, .18), 0 12px 22px rgba(112, 19, 27, .14); outline: none; transform: translateY(-1px); }
    .access-onboard-cancel:hover::after,
    .access-onboard-cancel:focus-visible::after,
    .access-onboard-continue:not(:disabled):hover::after,
    .access-onboard-continue:not(:disabled):focus-visible::after { transform: translateX(135%); }
    .access-onboard-continue:disabled { color: #a6abb4; border-color: #e2e4e8; background: #f0f1f3; cursor: not-allowed; }
    #lookupModal .um-lookup-pagination { margin: 14px 0 0; border-radius: 8px; }
    #lookupModal .um-lookup-page-btn { min-width: 86px; height: 34px; border-radius: 6px; font-size: 12px; font-weight: 800; }

    .um-modal-close {
        position: relative;
        overflow: hidden;
        border: 0 !important;
        color: #fff !important;
        background-color: transparent !important;
        background-image: linear-gradient(90deg, #ffd21f, #ffd21f);
        background-repeat: no-repeat;
        background-position: left center;
        background-size: 0 100%;
        transition: color .2s ease, border-color .2s ease, background-size .24s ease, transform .2s ease;
    }
    .um-modal-close:hover {
        color: #7b101f !important;
        border-color: transparent !important;
        background-size: 100% 100%;
        transform: translateY(-1px);
    }

    .access-summary-modal__close {
        border: 0;
        color: #8f1020;
        background-color: transparent;
        background-image: linear-gradient(90deg, #ffd21f, #ffd21f);
        background-repeat: no-repeat;
        background-position: left center;
        background-size: 0 100%;
    }
    .access-summary-modal__close:hover {
        color: #7b101f;
        border-color: transparent;
        background-size: 100% 100%;
    }

    /* Keep the onboarding actions visible while either column scrolls independently. */
    #lookupModal .access-onboard-modal {
        height: min(720px, calc(100dvh - 28px)) !important;
        max-height: min(720px, calc(100dvh - 28px)) !important;
    }
    #lookupModal .access-onboard-modal > .um-modal-head,
    #lookupModal .access-onboard-modal > .access-onboard-steps,
    #lookupModal .access-onboard-modal > .access-onboard-footer {
        flex: 0 0 auto;
    }
    #lookupModal .access-onboard-modal > .um-modal-body {
        display: block;
        flex: 1 1 auto;
        min-height: 0 !important;
        height: 0;
        padding: 0 !important;
        overflow: hidden !important;
    }
    #lookupModal .access-onboard-modal .access-onboard-layout {
        min-height: 0 !important;
        height: 100% !important;
    }
    #lookupModal .access-onboard-modal .access-onboard-search,
    #lookupModal .access-onboard-modal .access-onboard-profile {
        min-height: 0 !important;
        overflow-y: auto !important;
        overscroll-behavior: contain;
    }
    #lookupModal .access-onboard-modal > .access-onboard-footer {
        position: relative;
        z-index: 2;
        border-top: 1px solid #eee7e8;
        background: #fff;
        box-shadow: 0 -7px 16px rgba(15, 23, 42, .04);
    }

    /* The close control uses the same yellow sweep as the primary actions. */
    #lookupModal .access-onboard-modal .um-modal-close,
    #settingsModal .um-modal-close,
    .access-summary-modal__close {
        border: 0 !important;
        box-shadow: none !important;
        color: #fff !important;
        background-color: transparent !important;
        background-image: linear-gradient(90deg, #ffd21f, #ffd21f) !important;
        background-repeat: no-repeat !important;
        background-position: left center !important;
        background-size: 0 100% !important;
    }
    #lookupModal .access-onboard-modal .um-modal-close::after,
    #settingsModal .um-modal-close::after {
        background: linear-gradient(120deg, transparent, rgba(255,255,255,.48), transparent) !important;
    }
    #lookupModal .access-onboard-modal .um-modal-close:hover,
    #lookupModal .access-onboard-modal .um-modal-close:focus-visible,
    #settingsModal .um-modal-close:hover,
    #settingsModal .um-modal-close:focus-visible,
    .access-summary-modal__close:hover,
    .access-summary-modal__close:focus-visible {
        border-color: transparent !important;
        box-shadow: none !important;
        color: #70131b !important;
        background-size: 100% 100% !important;
        outline: none;
    }

    /* User settings: a fixed profile sidebar with a focused access workspace. */
    #settingsModal .um-settings-console {
        display: flex;
        flex-direction: column;
        width: min(960px, calc(100vw - 28px)) !important;
        height: min(720px, calc(100dvh - 28px));
        max-height: min(720px, calc(100dvh - 28px)) !important;
        overflow: hidden;
        border: 1px solid #eadfe2;
        border-top: 3px solid #8f1020;
        border-bottom: 0;
        border-radius: 10px;
        background: #fff;
    }
    #settingsModal .um-settings-console > .um-modal-head {
        position: relative;
        display: flex;
        min-height: 78px;
        height: auto;
        flex: 0 0 auto;
        padding: 16px 20px !important;
        overflow: visible;
        border: 0;
        background: linear-gradient(135deg, #7f1d1d, #991b1b 55%, #b91c1c);
    }
    #settingsModal .um-settings-console .um-modal-head-main { display: flex; }
    #settingsModal .um-settings-console .um-modal-close {
        position: relative;
        z-index: 8;
        top: auto;
        right: auto;
        width: 40px;
        height: 40px;
        min-width: 40px;
        padding-bottom: 3px;
        color: #fff !important;
        background-color: rgba(112, 19, 27, .32) !important;
        font-size: 24px;
    }
    #settingsModal .um-settings-console > .um-modal-body {
        display: block;
        height: 0;
        min-height: 0;
        flex: 1 1 auto;
        max-height: none;
        padding: 0 !important;
        overflow: hidden;
        background: #fff;
    }
    #settingsModal .um-settings-console .um-modal-grid {
        display: grid;
        grid-template-columns: 276px minmax(0, 1fr);
        height: 100%;
        min-height: 0;
        gap: 0;
    }
    #settingsModal .um-settings-console .um-detail-card {
        min-height: 0;
        margin: 0;
        border: 0;
        border-radius: 0;
        background: #fff;
        box-shadow: none;
    }
    #settingsModal .um-settings-console .um-profile-summary-card {
        height: 100%;
        min-height: 0;
        overflow-y: auto;
        overscroll-behavior: contain;
        border-right: 1px solid #eee6e8;
        scrollbar-color: #a62638 #f2edef;
        scrollbar-width: thin;
    }
    #settingsModal .um-settings-console .um-profile-identity {
        display: grid;
        grid-template-columns: 56px minmax(0, 1fr);
        gap: 12px;
        padding: 20px;
        border-bottom: 1px solid #eee6e8;
        background: linear-gradient(135deg, #fff8f8, #fff4f5);
    }
    #settingsModal .um-settings-console .um-detail-photo {
        grid-row: span 2;
        width: 56px;
        height: 56px;
        min-width: 56px;
        border: 2px solid #fff;
        border-radius: 50%;
        color: #fff;
        background: #89111f;
        box-shadow: 0 7px 16px rgba(112, 19, 27, .2);
        font-size: 1.1rem;
    }
    #settingsModal .um-settings-console .um-profile-eyebrow { margin: 0 0 4px; font-size: 10px; }
    #settingsModal .um-settings-console .um-profile-heading { font-size: 15px; line-height: 1.2; }
    #settingsModal .um-settings-console .um-profile-copy { margin-top: 4px; font-size: 11px; line-height: 1.42; }
    #settingsModal .um-settings-console .um-profile-verified {
        display: inline-flex;
        grid-column: 2;
        align-items: center;
        gap: 4px;
        width: max-content;
        margin-top: 1px;
        padding: 3px 7px;
        border-radius: 999px;
        color: #138447;
        background: #e9f8ee;
        font-size: 9px;
        font-weight: 900;
    }
    #settingsModal .um-settings-console .um-profile-verified svg { width: 11px; height: 11px; stroke-width: 2.4; }
    #settingsModal .um-settings-console .um-profile-fields { display: block; padding: 0 18px; }
    #settingsModal .um-settings-console .um-profile-fields .um-field {
        position: relative;
        min-height: 56px;
        margin: 0 !important;
        padding: 10px 0 10px 37px;
        border-bottom: 1px solid #eee6e8;
    }
    #settingsModal .um-settings-console .um-profile-fields .um-field:last-child { border-bottom: 0; }
    #settingsModal .um-settings-console .um-profile-field-icon {
        position: absolute;
        top: 50%;
        left: 0;
        display: grid;
        width: 26px;
        height: 26px;
        place-items: center;
        border-radius: 7px;
        color: #8f1020;
        background: #fff1f2;
        transform: translateY(-50%);
    }
    #settingsModal .um-settings-console .um-profile-field-icon svg { width: 14px; height: 14px; }
    #settingsModal .um-settings-console .um-profile-fields label {
        display: block;
        margin: 0;
        color: #697386;
        font-size: 9px;
        font-weight: 900;
        letter-spacing: .045em;
        text-transform: uppercase;
    }
    #settingsModal .um-settings-console .um-profile-fields input[readonly] {
        display: block;
        width: 100%;
        min-height: 0;
        height: auto;
        margin: 3px 0 0;
        padding: 0;
        border: 0;
        border-radius: 0;
        color: #202534;
        background: transparent;
        box-shadow: none;
        font-size: 11px;
        font-weight: 800;
        line-height: 1.35;
    }
    #settingsModal .um-settings-console .um-settings-form-card {
        display: flex;
        flex-direction: column;
        min-height: 0;
        overflow: hidden;
        scrollbar-color: #a62638 #f2edef;
        scrollbar-width: thin;
    }
    #settingsModal .um-settings-console .um-settings-card-head {
        flex: 0 0 auto;
        padding: 18px 24px 15px;
        border-bottom: 1px solid #eee6e8;
        border-radius: 0;
        background: #fff;
    }
    #settingsModal .um-settings-console .um-settings-card-head h4 {
        display: flex;
        align-items: center;
        gap: 9px;
        color: #811323;
        font-size: 16px;
    }
    #settingsModal .um-settings-console .um-settings-title-icon {
        display: inline-grid;
        width: 25px;
        height: 25px;
        place-items: center;
        border-radius: 7px;
        color: #8f1020;
        background: #fff0f2;
    }
    #settingsModal .um-settings-console .um-settings-title-icon svg { width: 15px; height: 15px; }
    #settingsModal .um-settings-console .um-settings-card-head p { font-size: 11px; }
    #settingsModal .um-settings-console .um-settings-card-badge {
        min-width: 34px;
        height: 30px;
        border-radius: 7px;
        font-size: 10px;
    }
    #settingsModal .um-settings-console .um-settings-form-body { height: 0; flex: 1 1 auto; min-height: 0; padding: 18px 24px 24px; overflow-y: auto !important; overscroll-behavior: contain; }
    #settingsModal .um-settings-console .um-section-block {
        padding: 16px;
        border: 1px solid #eadfe2;
        border-radius: 8px;
        background: #fff;
    }
    #settingsModal .um-settings-console .um-section-title {
        display: flex;
        align-items: center;
        gap: 7px;
        color: #811323;
        font-size: 14px;
    }
    #settingsModal .um-settings-console .um-section-title svg { width: 16px; height: 16px; }
    #settingsModal .um-settings-console .um-section-copy { margin-bottom: 13px; font-size: 11px; line-height: 1.45; }
    #settingsModal .um-settings-console .um-section-block > .um-field { margin-bottom: 0; }
    #settingsModal .um-settings-console .um-section-block > .um-field label { font-size: 10px; }
    #settingsModal .um-settings-console .um-section-block > .um-field select { min-height: 40px; border-radius: 7px; font-size: 12px; }
    #settingsModal .um-settings-console .um-module-access-preview { margin: 16px 0 0; padding: 14px; border-radius: 8px; }
    #settingsModal .um-settings-console .um-module-access-head h5 { font-size: 14px; }
    #settingsModal .um-settings-console .um-module-access-head p { font-size: 11px; }
    #settingsModal .um-settings-console .um-module-access-toolbar { padding: 9px 10px; border-radius: 7px; }
    #settingsModal .um-settings-console .um-module-item { border-radius: 7px; }
    #settingsModal .um-settings-console .um-module-option { min-height: 59px; }
    #settingsModal .um-settings-console .um-actions {
        position: sticky;
        bottom: 0;
        z-index: 5;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 10px;
        margin: 20px -24px -24px;
        padding: 13px 24px;
        border-top: 1px solid #e9e3e5;
        background: rgba(255, 255, 255, .97);
        box-shadow: 0 -8px 18px rgba(15, 23, 42, .04);
        backdrop-filter: blur(8px);
    }
    #settingsModal .um-settings-console .um-settings-actions-footer {
        position: relative;
        bottom: auto;
        flex: 0 0 auto;
        margin: 0;
        padding: 13px 24px;
    }
    #settingsModal .um-settings-console .um-settings-action {
        min-height: 40px;
        padding: 8px 10px;
        border-radius: 7px;
        font-size: 11px;
        line-height: 1.2;
    }
    html[data-theme="dark"] #settingsModal .um-settings-console,
    html[data-theme="dark"] #settingsModal .um-settings-console > .um-modal-body,
    html[data-theme="dark"] #settingsModal .um-settings-console .um-detail-card,
    html[data-theme="dark"] #settingsModal .um-settings-console .um-settings-card-head,
    html[data-theme="dark"] #settingsModal .um-settings-console .um-section-block {
        border-color: #2f3c4e;
        background: #111a26 !important;
    }
    html[data-theme="dark"] #settingsModal .um-settings-console .um-profile-summary-card { border-right-color: #2f3c4e; }
    html[data-theme="dark"] #settingsModal .um-settings-console .um-profile-identity { border-bottom-color: #2f3c4e; background: #24151c; }
    html[data-theme="dark"] #settingsModal .um-settings-console .um-profile-fields .um-field { border-bottom-color: #2f3c4e; }
    html[data-theme="dark"] #settingsModal .um-settings-console .um-profile-fields label,
    html[data-theme="dark"] #settingsModal .um-settings-console .um-profile-copy,
    html[data-theme="dark"] #settingsModal .um-settings-console .um-section-copy { color: #aebacd !important; }
    html[data-theme="dark"] #settingsModal .um-settings-console .um-profile-fields input[readonly],
    html[data-theme="dark"] #settingsModal .um-settings-console .um-profile-heading,
    html[data-theme="dark"] #settingsModal .um-settings-console .um-settings-card-head h4,
    html[data-theme="dark"] #settingsModal .um-settings-console .um-section-title { color: #f4f7fb !important; }
    html[data-theme="dark"] #settingsModal .um-settings-console .um-profile-field-icon,
    html[data-theme="dark"] #settingsModal .um-settings-console .um-settings-title-icon { color: #fecdd3; background: #3a1d28; }
    html[data-theme="dark"] #settingsModal .um-settings-console .um-actions { border-top-color: #2f3c4e; background: rgba(17, 26, 38, .97); }
    html[data-theme="dark"] #settingsModal .um-settings-console .um-action-neutral { border-color: #4b5b70; color: #e7edf6; background: #172130; }

    /* Dark mode for the redesigned console and its access onboarding flow. */
    html[data-theme="dark"] .access-console { color: #e7edf6; }
    html[data-theme="dark"] .access-console__hero,
    html[data-theme="dark"] .access-console__panel,
    html[data-theme="dark"] .access-console__stat,
    html[data-theme="dark"] .access-summary-modal__card {
        border-color: #2f3c4e !important;
        background: #121a26 !important;
        box-shadow: 0 16px 32px rgba(0, 0, 0, .28);
    }
    html[data-theme="dark"] .access-console__hero-title,
    html[data-theme="dark"] .access-console__stat-label,
    html[data-theme="dark"] .access-console__stat-value,
    html[data-theme="dark"] .access-console__name,
    html[data-theme="dark"] .access-console__role,
    html[data-theme="dark"] .access-console__state,
    html[data-theme="dark"] .access-console__empty,
    html[data-theme="dark"] .access-summary-modal__title,
    html[data-theme="dark"] .access-summary-modal__value {
        color: #f4f7fb !important;
    }
    html[data-theme="dark"] .access-console__hero-copy,
    html[data-theme="dark"] .access-console__sync span:not(.access-console__sync-icon),
    html[data-theme="dark"] .access-console__sync small,
    html[data-theme="dark"] .access-console__stat-note,
    html[data-theme="dark"] .access-console__email,
    html[data-theme="dark"] .access-console__state small,
    html[data-theme="dark"] .access-console__footer,
    html[data-theme="dark"] .access-summary-modal__copy {
        color: #aebacd !important;
    }
    html[data-theme="dark"] .access-console__sync strong { color: #f4f7fb !important; }
    html[data-theme="dark"] .access-console__hero-icon { color: #fecdd3; background: rgba(159, 31, 48, .30); }
    html[data-theme="dark"] .access-console__sync-icon { color: #fbbf24; background: rgba(180, 83, 35, .24); }
    html[data-theme="dark"] .access-console__initial { border-color: #5c3740; color: #fecdd3; background: #321923; }
    html[data-theme="dark"] .access-console__tag { color: #fecdd3; background: #3a1d28; }
    html[data-theme="dark"] .access-console__role { border-color: #334155; color: #d9e3f1; background: #172130; }
    html[data-theme="dark"] .access-console__role svg { color: #fda4af; }
    html[data-theme="dark"] .access-console__row { border-bottom-color: #2b3748; }
    html[data-theme="dark"] .access-console__row:hover { background: #1a2635; }
    html[data-theme="dark"] .access-console__filters,
    html[data-theme="dark"] .access-console__footer { border-color: #2b3748; }
    html[data-theme="dark"] .access-console__search input,
    html[data-theme="dark"] .access-console__filter {
        border-color: #3a495e !important;
        color: #edf2f8 !important;
        background: #0e1621 !important;
    }
    html[data-theme="dark"] .access-console__search input::placeholder { color: #8492a6; }
    html[data-theme="dark"] .access-console__search svg { color: #fda4af; }
    html[data-theme="dark"] .access-console__manage {
        border-color: #bd6773;
        color: #fecdd3;
        background: #172130;
    }
    html[data-theme="dark"] .access-console__manage:hover { border-color: #ffd21f; color: #70131b; background: #ffd21f; }
    html[data-theme="dark"] .access-console__stat:hover { border-color: #ffd21f !important; background: #1a2635 !important; }
    html[data-theme="dark"] .access-console__stat:hover .access-console__stat-label,
    html[data-theme="dark"] .access-console__stat:hover .access-console__stat-value,
    html[data-theme="dark"] .access-console__stat:hover .access-console__stat-note { color: #fde68a !important; }
    html[data-theme="dark"] .access-console__state strong,
    html[data-theme="dark"] .um-badge.active { border: 1px solid rgba(74, 222, 128, .32); color: #bbf7d0 !important; background: rgba(20, 83, 45, .8) !important; }
    html[data-theme="dark"] .access-console__state strong::before { background: #4ade80; }
    html[data-theme="dark"] .access-console__state--inactive strong,
    html[data-theme="dark"] .um-badge.inactive { border: 1px solid rgba(252, 165, 165, .32); color: #fecaca !important; background: rgba(127, 29, 29, .82) !important; }
    html[data-theme="dark"] .access-console__state--inactive strong::before { background: #f87171; }

    html[data-theme="dark"] #lookupModal .access-onboard-modal,
    html[data-theme="dark"] #lookupModal .access-onboard-modal > .um-modal-body,
    html[data-theme="dark"] #lookupModal .access-onboard-modal .access-onboard-search,
    html[data-theme="dark"] #lookupModal .access-onboard-modal .access-onboard-profile,
    html[data-theme="dark"] #lookupModal .access-onboard-modal > .access-onboard-footer {
        color: #e6edf6;
        background: #111a26 !important;
    }
    html[data-theme="dark"] #lookupModal .access-onboard-modal .access-onboard-steps,
    html[data-theme="dark"] #lookupModal .access-onboard-modal .access-onboard-search,
    html[data-theme="dark"] #lookupModal .access-onboard-modal > .access-onboard-footer { border-color: #2f3c4e; }
    html[data-theme="dark"] .access-onboard-step { color: #d5ddeb; }
    html[data-theme="dark"] .access-onboard-step small,
    html[data-theme="dark"] .access-onboard-count span:last-child,
    html[data-theme="dark"] .access-onboard-profile__email,
    html[data-theme="dark"] .access-onboard-profile__empty,
    html[data-theme="dark"] .access-onboard-footer small { color: #9eabbf; }
    html[data-theme="dark"] .access-onboard-profile__eyebrow,
    html[data-theme="dark"] .access-onboard-role-title,
    html[data-theme="dark"] .access-onboard-count { color: #fecdd3; }
    html[data-theme="dark"] .access-onboard-profile__name,
    html[data-theme="dark"] .access-onboard-profile__identifier strong,
    html[data-theme="dark"] .access-onboard-profile__details strong,
    html[data-theme="dark"] .access-onboard-role-option { color: #edf2f8; }
    html[data-theme="dark"] .access-onboard-profile__details,
    html[data-theme="dark"] .access-onboard-role-option,
    html[data-theme="dark"] #lookupModal .access-onboard-modal .um-table tr { border-color: #2f3c4e; }
    html[data-theme="dark"] .access-onboard-profile__details,
    html[data-theme="dark"] .access-onboard-role-option { background: #172130; }
    html[data-theme="dark"] .access-onboard-role-option:has(input:checked),
    html[data-theme="dark"] #lookupModal .access-onboard-modal .um-table tr:hover,
    html[data-theme="dark"] #lookupModal .access-onboard-modal .um-table tr.is-selected { border-color: #fda4af; background: #36202a; }
    html[data-theme="dark"] #lookupModal .access-onboard-modal .um-name,
    html[data-theme="dark"] #lookupModal .access-onboard-modal .um-sub { color: #edf2f8; }
    html[data-theme="dark"] #lookupModal .access-onboard-modal .um-table tr::after { color: #fda4af; }
    html[data-theme="dark"] .access-onboard-cancel { border-color: #4a5a70; color: #dce5f1; background: #172130; }
    html[data-theme="dark"] #lookupModuleAccessSlot .um-module-access-preview,
    html[data-theme="dark"] #lookupModuleAccessSlot .um-module-access-toolbar,
    html[data-theme="dark"] #lookupModuleAccessSlot .um-module-item,
    html[data-theme="dark"] #lookupModuleAccessSlot .um-module-expand,
    html[data-theme="dark"] #lookupModuleAccessSlot .um-module-check,
    html[data-theme="dark"] #lookupModuleAccessSlot .um-action-check { border-color: #35445a; background: #172130; }
    html[data-theme="dark"] #lookupModuleAccessSlot .um-module-access-head h5,
    html[data-theme="dark"] #lookupModuleAccessSlot .um-module-selection-summary strong,
    html[data-theme="dark"] #lookupModuleAccessSlot .um-module-title strong,
    html[data-theme="dark"] #lookupModuleAccessSlot .um-action-permission strong { color: #f4f7fb; }
    html[data-theme="dark"] #lookupModuleAccessSlot .um-module-access-head p,
    html[data-theme="dark"] #lookupModuleAccessSlot .um-module-selection-summary span,
    html[data-theme="dark"] #lookupModuleAccessSlot .um-module-copy small,
    html[data-theme="dark"] #lookupModuleAccessSlot .um-action-permission small { color: #aebacd; }
    html[data-theme="dark"] #lookupModuleAccessSlot .um-module-item:has(.um-module-option input:checked),
    html[data-theme="dark"] #lookupModuleAccessSlot .um-action-permission:has(input:checked) { border-color: #87404d; background: #36202a; }
    html[data-theme="dark"] #lookupModuleAccessSlot .um-module-actions { border-top-color: #2f3c4e; background: #111a26; }
    html[data-theme="dark"] #lookupModuleAccessSlot .um-action-permission { border-top-color: #2f3c4e; color: #e7edf6; }
    html[data-theme="dark"] #lookupModuleAccessSlot .um-module-title > span { color: #cbd5e1; background: #263347; }
    html[data-theme="dark"] #lookupModuleAccessSlot .um-module-icon { color: #fecdd3; background: #3a1d28; }
    html[data-theme="dark"] #lookupModuleAccessSlot .um-superadmin-access-summary { border-color: rgba(245, 158, 11, .28); color: #fde68a; background: #2a2114; }
    html[data-theme="dark"] #lookupModuleAccessSlot .um-superadmin-access-summary strong { color: #fde68a; }
    html[data-theme="dark"] #lookupModuleAccessSlot .um-superadmin-access-list span { border-color: rgba(245, 158, 11, .22); color: #fef3c7; background: #17130c; }
    html[data-theme="dark"] .access-summary-modal { background: rgba(2, 6, 23, .72); }
    html[data-theme="dark"] .access-summary-modal__eyebrow { color: #fda4af; }

    @media (max-width: 850px) {
        .access-console__stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .access-console__filters { flex-wrap: wrap; }
        .access-console__filter { flex: 1 1 130px; }
        .access-console__row { grid-template-columns: 43px minmax(0, 1fr) 90px; }
        .access-console__meta { grid-column: 2 / -1; }
        .access-console__state { display: none; }
        .access-onboard-layout { grid-template-columns: 1fr; }
        .access-onboard-search { border-right: 0; border-bottom: 1px solid #eee7e8; }
        #settingsModal .um-settings-console {
            width: min(720px, calc(100vw - 24px)) !important;
            height: min(760px, calc(100dvh - 24px));
            max-height: min(760px, calc(100dvh - 24px)) !important;
        }
        #settingsModal .um-settings-console .um-modal-grid { grid-template-columns: 1fr; }
        #settingsModal .um-settings-console .um-profile-summary-card {
            max-height: 230px;
            border-right: 0;
            border-bottom: 1px solid #eee6e8;
        }
        #settingsModal .um-settings-console .um-profile-fields { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); column-gap: 16px; }
    }

    @media (max-width: 560px) {
        .access-console { padding: 12px; }
        .access-console__hero { align-items: flex-start; }
        .access-console__sync { display: none; }
        .access-console__hero-copy { margin-left: 0; }
        .access-console__stats { grid-template-columns: 1fr; }
        .access-console__filters { gap: 8px; }
        .access-console__search { flex-basis: 100%; }
        .access-console__add { flex: 1; }
        .access-console__row { grid-template-columns: 40px minmax(0, 1fr) 82px; gap: 9px; padding: 11px; }
        .access-console__meta { gap: 5px; }
        .access-onboard-steps { padding: 12px; gap: 5px; }
        .access-onboard-step { grid-template-columns: 24px 1fr; font-size: .53rem; }
        .access-onboard-step__number { width: 24px; height: 24px; }
        .access-onboard-step small { display: none; }
        #settingsModal .um-settings-console {
            width: calc(100vw - 16px) !important;
            height: calc(100dvh - 16px);
            max-height: calc(100dvh - 16px) !important;
        }
        #settingsModal .um-settings-console .um-profile-summary-card { max-height: 205px; }
        #settingsModal .um-settings-console .um-profile-identity { padding: 16px; }
        #settingsModal .um-settings-console .um-profile-fields { display: block; padding: 0 14px; }
        #settingsModal .um-settings-console .um-settings-card-head,
        #settingsModal .um-settings-console .um-settings-form-body { padding-left: 14px; padding-right: 14px; }
        #settingsModal .um-settings-console .um-actions {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            padding: 11px 14px;
        }
    }
</style>
