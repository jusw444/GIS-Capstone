@extends('layouts.app')
@section('page_title', $page['pageTitle'])
@section('content')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.fullscreen@1.6.0/Control.FullScreen.css" />
    <link
        href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;600&family=Syne:wght@500;700;800&family=DM+Sans:wght@400;500;600&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    @push('styles')
        <style>
            :root {
                --red: #b71c1c;
                --red-mid: #c62828;
                --red-dark: #7f0000;
                --red-glow: rgba(183, 28, 28, .18);
                --red-border: rgba(183, 28, 28, .28);
                --red-light: rgba(183, 28, 28, .07);
                --bg: #eef0f4;
                --surface: #ffffff;
                --panel2: #f6f7f9;
                --border: rgba(0, 0, 0, .09);
                --text: #0f1117;
                --text-sub: #3d4a5c;
                --muted: #8a94a8;
                --font: 'DM Sans', sans-serif;
                --display: 'Syne', sans-serif;
                --mono: 'IBM Plex Mono', monospace;
                --shadow-sm: 0 2px 8px rgba(0, 0, 0, .07);
                --shadow-md: 0 6px 24px rgba(0, 0, 0, .10);
                --shadow-lg: 0 14px 48px rgba(0, 0, 0, .14);
                --radius: 14px;
            }

            *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

            body { font-family: var(--font); background: var(--bg); color: var(--text); }

            /* ── LAYOUT ── */
            #map-root {
                position: fixed;
                top: 0;
                left: 300px;
                width: calc(100% - 300px);
                height: 100vh;
                transition: all .3s ease;
            }

            body.sidebar-collapsed #map-root {
                left: 0 !important;
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            #map { width: 100%; height: 100%; background: #dde2e8; }

            /* ── TOP BAR ── */
            #filter-bar {
                position: absolute;
                top: 14px;
                left: 50%;
                transform: translateX(-50%);
                z-index: 800;
                display: flex;
                align-items: center;
                gap: 12px;
                background: rgba(255, 255, 255, .97);
                border: 1px solid var(--border);
                border-radius: 50px;
                padding: 6px 8px 6px 16px;
                backdrop-filter: blur(14px);
                -webkit-backdrop-filter: blur(14px);
                box-shadow: var(--shadow-md), 0 0 0 1px rgba(183, 28, 28, .05);
                white-space: nowrap;
                width: calc(100% - 32px);
                max-width: 900px;
            }

            .fb-search-wrap {
                display: flex;
                align-items: center;
                gap: 7px;
                background: var(--panel2);
                border: 1px solid var(--border);
                border-radius: 30px;
                padding: 6px 14px;
                transition: border-color .2s, box-shadow .2s;
                flex: 1;
                min-width: 0;
            }

            .fb-search-wrap:focus-within {
                border-color: var(--red);
                box-shadow: 0 0 0 3px var(--red-glow);
                background: #fff;
            }

            .fb-search-wrap i { color: var(--muted); font-size: 11px; flex-shrink: 0; }

            #quickSearch {
                border: none;
                outline: none;
                background: transparent;
                font-family: var(--font);
                font-size: 12.5px;
                font-weight: 500;
                color: var(--text);
                width: 100%;
                min-width: 0;
            }

            #quickSearch::placeholder { color: var(--muted); }

            #advSearchBtn {
                display: flex;
                align-items: center;
                gap: 6px;
                padding: 7px 16px;
                background: var(--red-light);
                border: 1px solid var(--red-border);
                border-radius: 30px;
                font-family: var(--font);
                font-size: 12px;
                font-weight: 700;
                color: var(--red);
                cursor: pointer;
                letter-spacing: .2px;
                transition: background .2s, box-shadow .2s, transform .15s;
                white-space: nowrap;
                flex-shrink: 0;
                user-select: none;
            }

            #advSearchBtn:hover {
                background: var(--red);
                color: #fff;
                box-shadow: 0 4px 14px rgba(183, 28, 28, .28);
                transform: translateY(-1px);
            }

            #advSearchBtn.active {
                background: var(--red);
                color: #fff;
                box-shadow: 0 4px 16px rgba(183, 28, 28, .32);
            }

            #advSearchBtn i { font-size: 10px; }
            #advChevron { transition: transform .2s; }
            #advSearchBtn.active #advChevron { transform: rotate(180deg); }

            /* ── FILTER ROW ── */
            #filter-row {
                position: absolute;
                top: 68px;
                left: 50%;
                transform: translateX(-50%);
                z-index: 800;
                display: none;
                align-items: center;
                gap: 8px;
                background: rgba(255, 255, 255, .97);
                border: 1px solid var(--border);
                border-radius: 40px;
                padding: 7px 16px;
                backdrop-filter: blur(14px);
                -webkit-backdrop-filter: blur(14px);
                box-shadow: var(--shadow-md);
                flex-wrap: wrap;
                width: calc(100% - 32px);
                max-width: 900px;
                justify-content: center;
                min-height: 52px;
            }

            #filter-row.visible { display: flex; }

            /* ── Shared pill ── */
            .ms-pill, .metadata-pill { position: relative; flex-shrink: 0; }

            .ms-trigger, .metadata-trigger {
                display: flex;
                align-items: center;
                gap: 6px;
                padding: 6px 13px;
                background: var(--panel2);
                border: 1px solid var(--border);
                border-radius: 20px;
                cursor: pointer;
                font-family: var(--font);
                font-size: 12px;
                font-weight: 600;
                color: var(--text-sub);
                user-select: none;
                transition: border-color .2s, background .2s, box-shadow .2s;
                min-width: 148px;
                max-width: 200px;
                height: 34px;
            }

            .ms-trigger > span:first-of-type,
            .metadata-trigger > span:first-of-type {
                flex: 1;
                min-width: 0;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            .ms-trigger:hover, .metadata-trigger:hover,
            .ms-pill.open .ms-trigger, .metadata-pill.open .metadata-trigger {
                border-color: var(--red);
                background: var(--red-light);
                color: var(--red);
                box-shadow: 0 0 0 3px rgba(183, 28, 28, .07);
            }

            #locTrigger.loc-open {
                border-color: var(--red);
                background: var(--red-light);
                color: var(--red);
                box-shadow: 0 0 0 3px rgba(183, 28, 28, .07);
            }

            .ms-trigger i, .metadata-trigger i { color: var(--red); font-size: 10px; }

            .ms-badge {
                background: var(--red);
                color: #fff;
                border-radius: 10px;
                font-size: 10px;
                font-weight: 700;
                padding: 1px 6px;
                min-width: 18px;
                text-align: center;
                flex-shrink: 0;
            }

            .ms-arrow, .metadata-arrow {
                font-size: 8px;
                color: var(--muted);
                transition: transform .2s;
                flex-shrink: 0;
            }

            .ms-pill.open .ms-arrow, .metadata-pill.open .metadata-arrow { transform: rotate(180deg); }

            /* ── Dropdown ── */
            .ms-dropdown {
                display: none;
                position: absolute;
                top: calc(100% + 8px);
                left: 50%;
                transform: translateX(-50%);
                min-width: 220px;
                max-width: 290px;
                width: max-content;
                background: var(--surface);
                border: 1px solid var(--border);
                border-radius: 12px;
                padding: 6px;
                z-index: 2100;
                box-shadow: var(--shadow-lg);
                flex-direction: column;
                gap: 2px;
                max-height: 290px;
                overflow-y: auto;
                overflow-x: hidden;
            }

            .ms-dropdown::-webkit-scrollbar { width: 4px; }
            .ms-dropdown::-webkit-scrollbar-thumb { background: #dde2ea; border-radius: 4px; }

            .ms-pill.open .ms-dropdown { display: flex; }

            .ms-opt {
                display: flex;
                align-items: center;
                gap: 9px;
                padding: 7px 10px;
                border-radius: 8px;
                cursor: pointer;
                font-size: 12px;
                font-weight: 500;
                color: var(--text-sub);
                transition: background .15s, color .15s;
                white-space: normal;
                word-break: break-word;
            }

            .ms-opt:hover { background: var(--panel2); color: var(--text); }
            .ms-opt.selected { background: var(--red-light); color: var(--red); }

            .ms-check {
                width: 14px;
                height: 14px;
                border: 1.5px solid #cbd2de;
                border-radius: 3px;
                display: grid;
                place-items: center;
                flex-shrink: 0;
                transition: all .15s;
            }

            .ms-opt.selected .ms-check { background: var(--red); border-color: var(--red); }

            .ms-check::after {
                content: '';
                width: 4px;
                height: 7px;
                border: 2px solid #fff;
                border-top: none;
                border-left: none;
                transform: rotate(45deg) translateY(-1px);
                display: none;
            }

            .ms-opt.selected .ms-check::after { display: block; }

            .ms-all {
                font-size: 10px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: .6px;
                color: var(--red);
                border-bottom: 1px solid var(--border);
                margin-bottom: 4px;
                padding-bottom: 4px;
            }

            .ms-all:hover { background: var(--red-light); }

            /* ── Date Dropdown enhanced ── */
            .ms-date-dropdown {
                min-width: 280px !important;
                max-width: 320px !important;
            }

            /* Manual range at top */
            .ms-date-manual {
                padding: 8px 8px 4px;
                border-bottom: 1px solid var(--border);
            }

            .ms-date-manual-title {
                font-size: 9px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: .6px;
                color: var(--muted);
                margin-bottom: 6px;
            }

            .ms-date-row {
                display: flex;
                flex-direction: column;
                gap: 5px;
                padding: 0 0 6px 0;
            }

            .ms-date-row label {
                font-size: 10px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: .5px;
                color: var(--muted);
            }

            .ms-date-row input[type="date"] {
                border: 1px solid var(--border);
                border-radius: 8px;
                padding: 5px 9px;
                font-size: 12px;
                font-family: var(--mono);
                color: var(--text);
                outline: none;
                width: 100%;
                transition: border-color .2s;
            }

            .ms-date-row input[type="date"]:focus { border-color: var(--red); }

            .ms-date-presets {
                padding: 6px 8px;
            }

            .ms-date-presets-title {
                font-size: 9px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: .6px;
                color: var(--muted);
                margin-bottom: 5px;
            }

            .ms-date-preset-list {
                display: flex;
                flex-direction: column;
                gap: 1px;
                max-height: 160px;
                overflow-y: auto;
            }

            .ms-date-preset-list::-webkit-scrollbar { width: 3px; }
            .ms-date-preset-list::-webkit-scrollbar-thumb { background: #dde2ea; border-radius: 4px; }

            .ms-date-preset-item {
                display: flex;
                align-items: center;
                gap: 8px;
                padding: 5px 8px;
                border-radius: 6px;
                cursor: pointer;
                font-size: 11px;
                font-weight: 500;
                color: var(--text-sub);
                transition: background .15s, color .15s;
                user-select: none;
            }

            .ms-date-preset-item:hover { background: var(--panel2); color: var(--text); }

            .ms-date-preset-item.selected {
                background: var(--red-light);
                color: var(--red);
                font-weight: 600;
            }

            .ms-date-preset-item .ms-date-dot {
                width: 6px;
                height: 6px;
                border-radius: 50%;
                background: var(--muted);
                flex-shrink: 0;
                transition: background .15s;
            }

            .ms-date-preset-item.selected .ms-date-dot { background: var(--red); }

            /* ── Metadata redesigned like location, with fixed height scroll for key dropdown ── */
            .metadata-pill { position: relative; flex-shrink: 0; }

            #metadataPill .ms-trigger {
                min-width: 148px;
                max-width: 200px;
                height: 34px;
            }

            .metadata-loc-dropdown {
                display: none;
                position: absolute;
                top: calc(100% + 8px);
                left: 50%;
                transform: translateX(-50%);
                width: 360px;
                max-width: 90vw;
                background: var(--surface);
                border: 1px solid var(--border);
                border-radius: 14px;
                z-index: 2100;
                box-shadow: var(--shadow-lg);
                flex-direction: column;
                overflow: visible;
                max-height: 460px;
            }

            .metadata-loc-dropdown .scrollable-body {
    overflow-y: auto;
    max-height: calc(460px - 130px); /* Adjust based on header + footer height */
}

            .metadata-pill.open .metadata-loc-dropdown { display: flex; }

            .metadata-loc-dropdown .meta-hdr {
    position: sticky;
    top: 0;
    background: var(--panel2);
    z-index: 1;
}

            .meta-hdr-title {
                font-family: var(--display);
                font-size: 10px;
                font-weight: 700;
                letter-spacing: .8px;
                text-transform: uppercase;
                color: var(--red);
                margin-bottom: 10px;
                display: flex;
                align-items: center;
                gap: 6px;
            }

            .meta-key-row {
                display: flex;
                gap: 6px;
                align-items: flex-start;
            }

            /* Custom dropdown for meta keys */
            .custom-select {
                flex: 1;
                position: relative;
            }

            .custom-select-trigger {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 6px 10px;
                border: 1px solid var(--border);
                border-radius: 8px;
                background: #fff;
                font-size: 11px;
                font-family: var(--font);
                color: var(--text);
                cursor: pointer;
                transition: border-color .2s;
                min-height: 32px;
            }

            .custom-select-trigger:hover,
            .custom-select.open .custom-select-trigger {
                border-color: var(--red);
            }

            .custom-select-trigger span:first-child {
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            .custom-select-trigger i {
                font-size: 8px;
                color: var(--muted);
                transition: transform .2s;
            }

            .custom-select.open .custom-select-trigger i {
                transform: rotate(180deg);
            }

            .custom-select-dropdown {
    display: none;
    position: absolute;
    top: calc(100% + 4px);
    left: 0;
    right: 0;
    background: #fff;
    border: 1px solid var(--border);
    border-radius: 8px;
    z-index: 2200;
    max-height: 200px;
    overflow-y: auto;
    box-shadow: var(--shadow-md);
}

            .custom-select.open .custom-select-dropdown {
                display: block;
            }

            .custom-select-option {
                padding: 8px 10px;
                font-size: 11px;
                cursor: pointer;
                transition: background .15s;
                color: var(--text-sub);
            }

            .custom-select-option:hover {
                background: var(--panel2);
                color: var(--text);
            }

            .custom-select-option.selected {
                background: var(--red-light);
                color: var(--red);
                font-weight: 600;
            }

            .meta-op-select {
                width: 85px;
                flex-shrink: 0;
                border: 1px solid var(--border);
                border-radius: 8px;
                padding: 6px 10px;
                font-size: 11px;
                font-family: var(--font);
                background: #fff;
                outline: none;
                transition: border-color .2s;
                color: var(--text);
            }

            .meta-op-select:focus { border-color: var(--red); }

            .meta-chips {
                display: flex;
                align-items: center;
                gap: 5px;
                flex-wrap: wrap;
                padding: 7px 12px;
                background: #fff;
                border-bottom: 1px solid var(--border);
                min-height: 38px;
            }

            .meta-chips-empty {
                font-size: 11px;
                color: var(--muted);
                font-style: italic;
            }

            .meta-chip {
                display: inline-flex;
                align-items: center;
                gap: 4px;
                background: var(--red-light);
                border: 1px solid var(--red-border);
                border-radius: 20px;
                padding: 2px 8px 2px 7px;
                font-size: 11px;
                font-weight: 600;
                color: var(--red);
            }

            .meta-chip-lbl {
                font-size: 9px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: .4px;
                color: var(--muted);
                margin-right: 1px;
            }

            .meta-chip-x {
                cursor: pointer;
                font-size: 10px;
                color: var(--red);
                opacity: .65;
                transition: opacity .15s;
                line-height: 1;
                padding: 0 1px;
            }

            .meta-chip-x:hover { opacity: 1; }

            .meta-value-row {
                display: flex;
                gap: 6px;
                align-items: center;
                padding: 10px 12px;
                border-bottom: 1px solid var(--border);
            }

            .meta-value-input {
                flex: 1;
                border: 1px solid var(--border);
                border-radius: 8px;
                padding: 6px 10px;
                font-size: 12px;
                font-family: var(--font);
                outline: none;
                transition: border-color .2s;
                color: var(--text);
            }

            .meta-value-input:focus { border-color: var(--red); }

            .meta-add-btn {
                display: flex;
                align-items: center;
                gap: 5px;
                padding: 6px 14px;
                background: var(--red);
                border: none;
                border-radius: 8px;
                color: #fff;
                font-size: 11px;
                font-weight: 700;
                font-family: var(--font);
                cursor: pointer;
                transition: background .2s;
                white-space: nowrap;
                flex-shrink: 0;
            }

            .meta-add-btn:hover { background: var(--red-dark); }

            .meta-footer {
                padding: 8px 14px;
                background: var(--panel2);
                border-top: 1px solid var(--border);
                display: flex;
                align-items: center;
                justify-content: space-between;
                position: sticky;
                bottom: 0;
                background: var(--panel2);
            }

            .meta-footer-info {
                font-size: 10px;
                color: var(--muted);
                font-style: italic;
            }

            .meta-clear-btn {
                font-size: 10px;
                font-weight: 700;
                color: var(--red);
                cursor: pointer;
                text-transform: uppercase;
                letter-spacing: .4px;
                padding: 3px 10px;
                border-radius: 6px;
                border: 1px solid var(--red-border);
                background: transparent;
                font-family: var(--font);
                transition: background .15s;
            }

            .meta-clear-btn:hover { background: var(--red-light); }

            .meta-notice {
                font-size: 11px;
                color: var(--muted);
                font-style: italic;
                text-align: center;
                padding: 10px 12px;
                display: flex;
                align-items: center;
                gap: 5px;
                justify-content: center;
            }

            /* ── Filter row actions ── */
            .filter-row-actions {
                display: flex;
                align-items: center;
                gap: 8px;
                flex-shrink: 0;
            }

            .fb-clear {
                display: flex;
                align-items: center;
                gap: 5px;
                padding: 6px 13px;
                background: transparent;
                border: 1px solid var(--border);
                border-radius: 20px;
                color: var(--muted);
                font-family: var(--font);
                font-size: 11px;
                font-weight: 600;
                cursor: pointer;
                transition: all .2s;
                white-space: nowrap;
                height: 34px;
            }

            .fb-clear:hover { border-color: var(--red); color: var(--red); background: var(--red-light); }

            .fb-count {
                display: flex;
                align-items: center;
                gap: 6px;
                background: var(--red-light);
                border: 1px solid var(--red-border);
                border-radius: 20px;
                padding: 5px 13px;
                white-space: nowrap;
                height: 34px;
            }

            .fb-count-num { font-family: var(--mono); font-size: 13px; font-weight: 700; color: var(--red); }
            .fb-count-lbl { font-size: 10px; font-weight: 600; text-transform: uppercase; color: var(--text-sub); }

            /* ── Map Fix Checkbox ── */
            .fb-map-fix {
                display: flex;
                align-items: center;
                gap: 6px;
                padding: 5px 12px;
                background: var(--panel2);
                border: 1px solid var(--border);
                border-radius: 20px;
                cursor: pointer;
                font-family: var(--font);
                font-size: 11px;
                font-weight: 600;
                color: var(--text-sub);
                user-select: none;
                transition: border-color .2s, background .2s;
                flex-shrink: 0;
                height: 34px;
            }

            .fb-map-fix:hover { border-color: var(--red); color: var(--red); background: var(--red-light); }
            .fb-map-fix.active { border-color: var(--red); background: var(--red-light); color: var(--red); }

            .fb-map-fix input[type="checkbox"] { display: none; }

            .fb-map-fix-indicator {
                width: 14px;
                height: 14px;
                border: 1.5px solid #cbd2de;
                border-radius: 3px;
                display: grid;
                place-items: center;
                flex-shrink: 0;
                transition: all .15s;
            }

            .fb-map-fix.active .fb-map-fix-indicator { background: var(--red); border-color: var(--red); }

            .fb-map-fix.active .fb-map-fix-indicator::after {
                content: '';
                width: 4px;
                height: 7px;
                border: 2px solid #fff;
                border-top: none;
                border-left: none;
                transform: rotate(45deg) translateY(-1px);
                display: block;
            }

            /* ── Location Filter ── */
            #locWrapper { position: relative; }

            #locDropdown {
                position: absolute;
                top: calc(100% + 8px);
                left: 50%;
                transform: translateX(-50%);
                width: 320px;
                max-width: 90vw;
                background: var(--surface);
                border: 1px solid var(--border);
                border-radius: 14px;
                z-index: 2100;
                box-shadow: var(--shadow-lg);
                display: none;
                flex-direction: column;
                overflow: hidden;
            }

            #locDropdown.loc-visible { display: flex; }

            .loc-hdr {
                padding: 12px 14px 10px;
                background: var(--panel2);
                border-bottom: 1px solid var(--border);
            }

            .loc-hdr-title {
                font-family: var(--display);
                font-size: 10px;
                font-weight: 700;
                letter-spacing: .8px;
                text-transform: uppercase;
                color: var(--red);
                margin-bottom: 8px;
                display: flex;
                align-items: center;
                gap: 6px;
            }

            .loc-search-wrap {
                display: flex;
                align-items: center;
                gap: 7px;
                background: #fff;
                border: 1px solid var(--border);
                border-radius: 8px;
                padding: 5px 10px;
                transition: border-color .2s, box-shadow .2s;
            }

            .loc-search-wrap:focus-within { border-color: var(--red); box-shadow: 0 0 0 2px var(--red-glow); }
            .loc-search-wrap i { color: var(--muted); font-size: 10px; flex-shrink: 0; }

            #locSearchInput {
                border: none;
                outline: none;
                background: transparent;
                font-family: var(--font);
                font-size: 12px;
                font-weight: 500;
                color: var(--text);
                width: 100%;
            }

            #locSearchInput::placeholder { color: var(--muted); }

            .loc-chips {
                display: flex;
                align-items: center;
                gap: 5px;
                flex-wrap: wrap;
                padding: 7px 12px;
                background: #fff;
                border-bottom: 1px solid var(--border);
                min-height: 38px;
            }

            .loc-chips-empty { font-size: 11px; color: var(--muted); font-style: italic; }

            .loc-chip {
                display: inline-flex;
                align-items: center;
                gap: 4px;
                background: var(--red-light);
                border: 1px solid var(--red-border);
                border-radius: 20px;
                padding: 2px 8px 2px 7px;
                font-size: 11px;
                font-weight: 600;
                color: var(--red);
            }

            .loc-chip-lbl {
                font-size: 9px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: .4px;
                color: var(--muted);
                margin-right: 1px;
            }

            .loc-chip-x {
                cursor: pointer;
                font-size: 10px;
                color: var(--red);
                opacity: .65;
                transition: opacity .15s;
                line-height: 1;
                padding: 0 1px;
            }

            .loc-chip-x:hover { opacity: 1; }

            .loc-tabs {
                display: flex;
                border-bottom: 1px solid var(--border);
                background: var(--panel2);
            }

            .loc-tab {
                flex: 1;
                padding: 8px 4px;
                text-align: center;
                font-size: 10px;
                font-weight: 700;
                letter-spacing: .4px;
                text-transform: uppercase;
                color: var(--muted);
                cursor: pointer;
                border-bottom: 2px solid transparent;
                transition: color .2s, border-color .2s, background .2s;
                user-select: none;
            }

            .loc-tab:hover { color: var(--text-sub); background: rgba(0,0,0,.03); }
            .loc-tab.active { color: var(--red); border-bottom-color: var(--red); background: #fff; }

            .loc-tab-cnt {
                display: inline-block;
                border-radius: 8px;
                font-size: 9px;
                font-weight: 700;
                padding: 0 5px;
                margin-left: 4px;
                min-width: 16px;
                text-align: center;
                line-height: 14px;
                vertical-align: middle;
                background: var(--muted);
                color: #fff;
            }

            .loc-tab.active .loc-tab-cnt { background: var(--red); }

            .loc-list { overflow-y: auto; max-height: 200px; }
            .loc-list::-webkit-scrollbar { width: 4px; }
            .loc-list::-webkit-scrollbar-thumb { background: #dde2ea; border-radius: 4px; }

            .loc-panel { padding: 4px; }

            .loc-item {
                display: flex;
                align-items: center;
                gap: 9px;
                padding: 7px 10px;
                border-radius: 8px;
                cursor: pointer;
                font-size: 12px;
                font-weight: 500;
                color: var(--text-sub);
                transition: background .15s, color .15s;
                user-select: none;
            }

            .loc-item:hover { background: var(--panel2); color: var(--text); }
            .loc-item.active { background: var(--red-light); color: var(--red); font-weight: 600; }

            .loc-item-ico {
                width: 22px;
                height: 22px;
                border-radius: 6px;
                display: grid;
                place-items: center;
                font-size: 9px;
                flex-shrink: 0;
                background: rgba(0,0,0,.04);
                color: var(--muted);
                transition: background .15s, color .15s;
            }

            .loc-item.active .loc-item-ico { background: rgba(183,28,28,.12); color: var(--red); }

            .loc-item-txt { flex: 1; min-width: 0; }
            .loc-item-name { display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
            .loc-item-sub { display: block; font-size: 10px; color: var(--muted); margin-top: 1px; }

            .loc-item-chk {
                width: 14px;
                height: 14px;
                border: 1.5px solid #cbd2de;
                border-radius: 3px;
                display: grid;
                place-items: center;
                flex-shrink: 0;
                transition: all .15s;
            }

            .loc-item.active .loc-item-chk { background: var(--red); border-color: var(--red); }

            .loc-item-chk::after {
                content: '';
                width: 4px;
                height: 7px;
                border: 2px solid #fff;
                border-top: none;
                border-left: none;
                transform: rotate(45deg) translateY(-1px);
                display: none;
            }

            .loc-item.active .loc-item-chk::after { display: block; }

            .loc-empty { text-align: center; padding: 18px 14px; color: var(--muted); font-size: 11px; }
            .loc-empty i { display: block; font-size: 18px; margin-bottom: 5px; opacity: .35; }

            .loc-footer {
                padding: 8px 14px;
                background: var(--panel2);
                border-top: 1px solid var(--border);
                display: flex;
                align-items: center;
                justify-content: space-between;
            }

            .loc-footer-info { font-size: 10px; color: var(--muted); font-style: italic; }

            .loc-clear-btn {
                font-size: 10px;
                font-weight: 700;
                color: var(--red);
                cursor: pointer;
                text-transform: uppercase;
                letter-spacing: .4px;
                padding: 3px 10px;
                border-radius: 6px;
                border: 1px solid var(--red-border);
                background: transparent;
                font-family: var(--font);
                transition: background .15s;
            }

            .loc-clear-btn:hover { background: var(--red-light); }

            /* ── Analysis Panel ── */
            #analysis-panel {
                position: absolute;
                bottom: 44px;
                left: 14px;
                z-index: 800;
                width: 270px;
                background: rgba(255,255,255,.97);
                border: 1px solid var(--border);
                border-radius: var(--radius);
                backdrop-filter: blur(12px);
                -webkit-backdrop-filter: blur(12px);
                box-shadow: var(--shadow-md);
                overflow: hidden;
            }

            .ap-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 10px 14px;
                border-bottom: 1px solid var(--border);
                cursor: pointer;
                user-select: none;
                background: #fff;
            }

            .ap-header:hover { background: var(--panel2); }

            .ap-header-left { display: flex; align-items: center; gap: 8px; }

            .ap-icon {
                width: 26px;
                height: 26px;
                background: var(--red-light);
                border: 1px solid var(--red-border);
                border-radius: 7px;
                display: grid;
                place-items: center;
                font-size: 11px;
                color: var(--red);
                flex-shrink: 0;
            }

            .ap-title {
                font-family: var(--display);
                font-size: 11px;
                font-weight: 700;
                letter-spacing: .5px;
                text-transform: uppercase;
                color: var(--text);
            }

            .ap-toggle { font-size: 10px; color: var(--muted); transition: transform .25s; }
            .ap-toggle.collapsed { transform: rotate(180deg); }

            .ap-body {
                padding: 10px;
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 6px;
                max-height: 260px;
                overflow-y: auto;
                background: #fff;
                transition: max-height .25s ease, padding .25s ease;
            }

            .ap-body::-webkit-scrollbar { width: 4px; }
            .ap-body::-webkit-scrollbar-thumb { background: #e2e6ea; border-radius: 4px; }

            .ap-body.hidden { max-height: 0; overflow: hidden; padding: 0 10px; }

            .ap-card {
                padding: 10px 10px 8px;
                border-radius: 9px;
                border: 1px solid rgba(0,0,0,.06);
                background: var(--panel2);
                transition: box-shadow .2s, transform .2s;
                position: relative;
                overflow: hidden;
            }

            .ap-card:hover { box-shadow: var(--shadow-sm); transform: translateY(-1px); }
            .ap-card-num { font-family: var(--mono); font-size: 22px; font-weight: 700; line-height: 1; margin-bottom: 5px; }

            .ap-card-name {
                font-size: 10px;
                font-weight: 600;
                letter-spacing: .4px;
                text-transform: uppercase;
                color: var(--text-sub);
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            /* ── Coord Bar ── */
            #coord-bar {
                position: absolute;
                bottom: 10px;
                left: 50%;
                transform: translateX(-50%);
                z-index: 700;
                display: flex;
                align-items: center;
                gap: 10px;
                padding: 4px 14px;
                background: rgba(255,255,255,.92);
                border: 1px solid var(--border);
                border-radius: 20px;
                font-family: var(--mono);
                font-size: 10px;
                color: var(--muted);
                pointer-events: none;
                backdrop-filter: blur(8px);
                box-shadow: var(--shadow-sm);
            }

            #coord-bar span { color: var(--text-sub); font-weight: 700; }
            .cb-sep { width: 1px; height: 12px; background: var(--border); }

            /* ── Legend ── */
            .gis-legend {
                background: rgba(255,255,255,.97) !important;
                border: 1px solid var(--border) !important;
                border-radius: 12px !important;
                padding: 12px 14px !important;
                backdrop-filter: blur(10px);
                box-shadow: var(--shadow-md) !important;
            }

            .legend-title {
                font-family: var(--display);
                font-size: 10px;
                font-weight: 700;
                letter-spacing: .8px;
                text-transform: uppercase;
                color: var(--red);
                margin-bottom: 8px;
            }

            .legend-row {
                display: flex;
                align-items: center;
                gap: 8px;
                font-family: var(--font);
                font-size: 11px;
                color: var(--text-sub);
                margin-bottom: 5px;
            }

            .legend-swatch { width: 12px; height: 12px; border-radius: 3px; flex-shrink: 0; }

            /* ── Leaflet overrides ── */
            .leaflet-bar a { background: #fff !important; border-color: #dde2ea !important; color: var(--text-sub) !important; }
            .leaflet-bar a:hover { background: var(--red) !important; color: #fff !important; }
            .leaflet-bar { border: none !important; box-shadow: var(--shadow-md) !important; }
            .leaflet-bar a { border-bottom-color: #eee !important; }

            .leaflet-control-fullscreen a {
                background: white !important;
                display: flex !important;
                align-items: center;
                justify-content: center;
                font-size: 16px;
            }

            .leaflet-control-fullscreen a::before {
                font-family: "Font Awesome 5 Free";
                font-weight: 900;
                content: "\f065";
                color: var(--text-sub);
                transition: color 0.2s;
            }

            .leaflet-control-fullscreen a:hover::before { color: #fff; }
            .leaflet-fullscreen-on .leaflet-control-fullscreen a::before { content: "\f066"; }

            .leaflet-control-layers {
                background: rgba(255,255,255,.97) !important;
                border: 1px solid var(--border) !important;
                border-radius: 10px !important;
                box-shadow: var(--shadow-md) !important;
            }

            .leaflet-control-layers label { color: var(--text-sub) !important; font-size: 12px; }
            .leaflet-control-scale-line {
                background: rgba(255,255,255,.85) !important;
                border-color: var(--muted) !important;
                color: var(--muted) !important;
                font-size: 10px !important;
            }

            .leaflet-popup-content-wrapper {
                background: #fff !important;
                border-radius: 12px !important;
                border-top: 3px solid var(--red) !important;
                box-shadow: var(--shadow-lg) !important;
                padding: 0 !important;
            }

            .leaflet-popup-content { margin: 0 !important; }
            .leaflet-popup-tip { background: #fff !important; }
            .leaflet-popup-close-button { color: var(--muted) !important; right: 8px !important; top: 8px !important; font-size: 16px !important; }
            .leaflet-popup-close-button:hover { color: var(--red) !important; }

            /* ── Popup ── */
            .popup-wrap { padding: 14px 16px; font-family: var(--font); min-width: 240px; }
            .popup-cat { font-family: var(--display); font-size: 10px; font-weight: 700; letter-spacing: .7px; text-transform: uppercase; color: var(--muted); margin-bottom: 3px; }
            .popup-cls { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; color: #fff; margin-bottom: 10px; }
            .popup-divider { height: 1px; background: #f0f0f0; margin: 8px 0; }

            .popup-row {
                display: flex;
                justify-content: space-between;
                align-items: baseline;
                gap: 10px;
                padding: 4px 0;
                border-bottom: 1px solid #f5f5f5;
                font-size: 11px;
            }

            .popup-row:last-child { border-bottom: none; }
            .popup-key { color: var(--muted); font-weight: 500; }
            .popup-val { color: var(--text); font-weight: 600; text-align: right; max-width: 140px; word-break: break-word; }
            .popup-more { margin-top: 10px; text-align: center; }

            .popup-more-btn {
                background: var(--red);
                color: #fff;
                border: none;
                border-radius: 20px;
                padding: 5px 16px;
                font-size: 11px;
                font-weight: 700;
                font-family: var(--font);
                cursor: pointer;
                letter-spacing: .3px;
                transition: background .2s, box-shadow .2s;
            }

            .popup-more-btn:hover { background: var(--red-dark); box-shadow: 0 4px 12px rgba(183,28,28,.25); }
            .popup-empty { text-align: center; padding: 8px 0; color: var(--muted); font-size: 11px; }

            /* ── Hint & Toast ── */
            #no-filter-hint {
                position: absolute;
                top: 70px;
                left: 50%;
                transform: translateX(-50%);
                z-index: 900;
                background: rgba(255,255,255,.96);
                border: 1px solid var(--border);
                border-radius: 8px;
                padding: 5px 12px 5px 14px;
                font-size: 11px;
                font-weight: 600;
                color: var(--text-sub);
                display: flex;
                align-items: center;
                gap: 7px;
                box-shadow: var(--shadow-sm);
                transition: opacity .3s;
            }

            #no-filter-hint i { color: var(--red); font-size: 10px; }

            .hint-close-btn {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 18px;
                height: 18px;
                border-radius: 50%;
                background: rgba(0,0,0,.06);
                border: none;
                cursor: pointer;
                color: var(--muted);
                font-size: 9px;
                line-height: 1;
                flex-shrink: 0;
                transition: background .15s, color .15s;
                padding: 0;
            }

            .hint-close-btn:hover { background: var(--red-light); color: var(--red); }

            #no-results-toast {
                position: absolute;
                top: 70px;
                left: 50%;
                transform: translateX(-50%);
                z-index: 900;
                background: #fff3cd;
                border: 1px solid #ffc107;
                border-radius: 10px;
                padding: 8px 18px;
                font-size: 12px;
                font-weight: 600;
                color: #856404;
                display: none;
                pointer-events: none;
                box-shadow: var(--shadow-sm);
            }

            /* ── Modal ── */
            .modal { z-index: 3000 !important; }
            .modal-content { border-radius: 14px !important; overflow: hidden; }
            .modal-header { background-color: #b71c1c !important; border-bottom: none !important; }
            .sticky-top { background: #f8f9fa !important; }

            .metadata-item {
                background: #f8f9fa;
                border-radius: 8px;
                padding: 12px;
                margin-bottom: 8px;
                border-left: 4px solid var(--red);
                transition: all .2s;
                font-size: 13px;
            }

            .metadata-item:hover { background: #f0f2f5; transform: translateX(3px); box-shadow: var(--shadow-sm); }

            /* ── Print ── */
            @media print {
                @page { margin: 0; size: landscape; }

                body * { visibility: hidden; }

                #map-root, #map-root *, #map { visibility: visible; }

                #map-root {
                    position: absolute !important;
                    top: 0 !important;
                    left: 0 !important;
                    width: 100vw !important;
                    height: 100vh !important;
                    z-index: 99999 !important;
                    margin: 0 !important;
                    padding: 0 !important;
                    box-shadow: none !important;
                }

                #map { width: 100% !important; height: 100% !important; }

                #filter-bar, #filter-row, #analysis-panel, #coord-bar,
                #no-filter-hint, #no-results-toast, .modal { display: none !important; }

                .leaflet-control-zoom, .leaflet-control-layers,
                .leaflet-control-fullscreen, .leaflet-control-scale-line,
                .leaflet-top.leaflet-left, .leaflet-top.leaflet-right:not(.gis-legend) { display: none !important; }

                .gis-legend {
                    display: block !important;
                    box-shadow: var(--shadow-md) !important;
                    background: rgba(255,255,255,.98) !important;
                }

                .leaflet-container { background: #f8f9fa !important; }
            }
        </style>
    @endpush

    <div id="map-root">
        <div id="map"></div>

        <!-- TOP BAR -->
        <div id="filter-bar">
            <div class="fb-search-wrap">
                <i class="fas fa-search"></i>
                <input type="text" id="quickSearch" placeholder="Search features, location, district, barangay…"
                    oninput="onQuickSearch()" />
            </div>
            <button id="advSearchBtn" onclick="toggleAdvSearch()">
                <i class="fas fa-sliders-h"></i>
                Advanced Search
                <i class="fas fa-chevron-down" id="advChevron" style="font-size:8px;"></i>
            </button>
        </div>

        <!-- FILTER ROW -->
        <div id="filter-row">

            <!-- Category -->
            <div class="ms-pill" id="catPill">
                <div class="ms-trigger" onclick="togglePill('catPill')">
                    <i class="fas fa-layer-group"></i>
                    <span id="catLabel">Category</span>
                    <span class="ms-badge" id="catBadge" style="display:none">0</span>
                    <span class="ms-arrow">▼</span>
                </div>
                <div class="ms-dropdown" id="catDropdown">
                    <div class="ms-opt ms-all" onclick="selectAllCat()">
                        <div class="ms-check" id="catAllChk"></div> Select All
                    </div>
                    @foreach ($categories as $cat)
                        <div class="ms-opt" data-val="{{ $cat->name }}" onclick="toggleCat('{{ $cat->name }}', this)">
                            <div class="ms-check"></div>
                            <span>{{ ucfirst($cat->name) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Classification -->
            <div class="ms-pill" id="clsPill">
                <div class="ms-trigger" onclick="togglePill('clsPill')">
                    <i class="fas fa-tags"></i>
                    <span id="clsLabel">Classification</span>
                    <span class="ms-badge" id="clsBadge" style="display:none">0</span>
                    <span class="ms-arrow">▼</span>
                </div>
                <div class="ms-dropdown" id="clsDropdown">
                    <div class="ms-opt ms-all" onclick="selectAllCls()">
                        <div class="ms-check" id="clsAllChk"></div> Select All
                    </div>
                    @foreach ($classifications as $c)
                        <div class="ms-opt" data-val="{{ $c->id }}" onclick="toggleCls('{{ $c->id }}', this)">
                            <div class="ms-check"></div>
                            <span>{{ $c->name }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Date Collected (manual at top, preset list below) -->
            <div class="ms-pill" id="datePill">
                <div class="ms-trigger" onclick="togglePill('datePill')">
                    <i class="fas fa-calendar-alt"></i>
                    <span id="dateLabel">Date Collected</span>
                    <span class="ms-badge" id="dateBadge" style="display:none">●</span>
                    <span class="ms-arrow">▼</span>
                </div>
                <div class="ms-dropdown ms-date-dropdown" id="dateDropdown">
                    <!-- Manual range FIRST -->
                    <div class="ms-date-manual">
                        <div class="ms-date-manual-title">Manual Range</div>
                        <div class="ms-date-row">
                            <label>From</label>
                            <input type="date" id="dateFrom" onchange="onDateChange()" />
                        </div>
                        <div class="ms-date-row">
                            <label>To</label>
                            <input type="date" id="dateTo" onchange="onDateChange()" />
                        </div>
                    </div>
                    <!-- Existing dates list BELOW -->
                    <div class="ms-date-presets">
                        <div class="ms-date-presets-title">Available Dates</div>
                        <div class="ms-date-preset-list" id="datePresetList">
                            <div class="ms-date-preset-item" style="justify-content:center;color:var(--muted);font-style:italic;font-size:10px;cursor:default;">
                                No dates available
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Location Filter -->
            <div id="locWrapper">
                <div class="ms-trigger" id="locTrigger" style="cursor:pointer;">
                    <i class="fas fa-map-marker-alt" style="color:var(--red);font-size:10px;"></i>
                    <span id="locLabel">Location</span>
                    <span class="ms-badge" id="locBadge" style="display:none">0</span>
                    <span class="ms-arrow" id="locArrow">▼</span>
                </div>
                <div id="locDropdown">
                    <div class="loc-hdr">
                        <div class="loc-hdr-title">
                            <i class="fas fa-map-marked-alt"></i> Filter by Location
                        </div>
                        <div class="loc-search-wrap">
                            <i class="fas fa-search"></i>
                            <input type="text" id="locSearchInput"
                                placeholder="Search district, municipality or barangay…" />
                        </div>
                    </div>
                    <div class="loc-chips" id="locChips">
                        <span class="loc-chips-empty" id="locChipsEmpty">No location filter applied</span>
                    </div>
                    <div class="loc-tabs" id="locTabBar">
                        <div class="loc-tab active" data-tab="district">
                            <i class="fas fa-city" style="margin-right:3px;font-size:9px;"></i>District
                            <span class="loc-tab-cnt" id="cntDistrict">0</span>
                        </div>
                        <div class="loc-tab" data-tab="municity">
                            <i class="fas fa-building" style="margin-right:3px;font-size:9px;"></i>Municipality
                            <span class="loc-tab-cnt" id="cntMunicity">0</span>
                        </div>
                        <div class="loc-tab" data-tab="brgy">
                            <i class="fas fa-home" style="margin-right:3px;font-size:9px;"></i>Barangay
                            <span class="loc-tab-cnt" id="cntBrgy">0</span>
                        </div>
                    </div>
                    <div class="loc-list">
                        <div class="loc-panel" id="panelDistrict"></div>
                        <div class="loc-panel" id="panelMunicity" style="display:none;"></div>
                        <div class="loc-panel" id="panelBrgy" style="display:none;"></div>
                    </div>
                    <div class="loc-footer">
                        <span class="loc-footer-info" id="locFooterInfo">Select a location to filter</span>
                        <button class="loc-clear-btn" id="locClearBtn">
                            <i class="fas fa-times" style="margin-right:3px;font-size:9px;"></i>Clear
                        </button>
                    </div>
                </div>
            </div>

            <!-- Metadata Filter (redesigned with custom dropdown for keys) -->
            <div class="metadata-pill" id="metadataPill">
                <div class="ms-trigger" onclick="toggleMetadataPill()">
                    <i class="fas fa-database"></i>
                    <span id="metadataLabel">Metadata</span>
                    <span class="ms-badge" id="metadataBadge" style="display:none">0</span>
                    <span class="ms-arrow">▼</span>
                </div>
                <div class="metadata-loc-dropdown" id="metadataLocDropdown">
                    <div class="meta-hdr">
                        <div class="meta-hdr-title">
                            <i class="fas fa-database"></i> Filter by Metadata
                        </div>
                        <div class="meta-key-row">
                            <!-- Custom dropdown for meta keys -->
                            <div class="custom-select" id="metaKeyCustomSelect">
                                <div class="custom-select-trigger" onclick="toggleMetaKeyDropdown(event)">
                                    <span id="selectedMetaKeyLabel">-- Select key --</span>
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                                <div class="custom-select-dropdown" id="metaKeyDropdownList">
                                    <div class="custom-select-option" data-value="">-- Select key --</div>
                                </div>
                            </div>
                            <select class="meta-op-select" id="metaOpSelect">
                                <option value="=">=</option>
                                <option value="!=">≠</option>
                                <option value=">">&gt;</option>
                                <option value="<">&lt;</option>
                                <option value=">=">≥</option>
                                <option value="<=">≤</option>
                            </select>
                        </div>
                    </div>

                    <div class="scrollable-body">
                        <div class="meta-chips" id="metaChips">
                        <span class="meta-chips-empty" id="metaChipsEmpty">No metadata filter applied</span>
                    </div>
                    <div class="meta-value-row">
                        <input type="text" class="meta-value-input" id="metaValueInput" placeholder="Enter value…" />
                        <button class="meta-add-btn" onclick="addMetadataFilter()">
                            <i class="fas fa-plus" style="font-size:9px;"></i> Add
                        </button>
                    </div>
                    <div class="meta-footer">
                        <span class="meta-footer-info" id="metaFooterInfo">Select key and enter value</span>
                        <button class="meta-clear-btn" id="metaClearBtn">
                            <i class="fas fa-times" style="margin-right:3px;font-size:9px;"></i>Clear All
                        </button>
                    </div>
                    </div>
                </div>
            </div>

            <!-- Map Fix Checkbox + Reset All + Feature Count -->
            <div class="filter-row-actions">
                <label class="fb-map-fix" id="mapFixLabel" title="Lock map position while filtering">
                    <input type="checkbox" id="mapFixChk" onchange="onMapFixChange()" />
                    <div class="fb-map-fix-indicator"></div>
                    <span>Fix Map</span>
                </label>
                <button class="fb-clear" onclick="clearFilters()">
                    <i class="fas fa-times" style="font-size:9px;"></i> Reset All
                </button>
                <div class="fb-count">
                    <span class="fb-count-num" id="featureCount">0</span>
                    <span class="fb-count-lbl">Features</span>
                </div>
            </div>

        </div><!-- /filter-row -->

        <div id="no-filter-hint">
            <i class="fas fa-info-circle"></i>
            Showing boundary areas. Use <strong>&nbsp;Advanced Search&nbsp;</strong> or Quick Search to load features.
            <button class="hint-close-btn" onclick="dismissHint()" title="Dismiss">✕</button>
        </div>
        <div id="no-results-toast">
            <i class="fas fa-exclamation-triangle me-1"></i>No features match the current filters.
        </div>

        <!-- Analysis Panel -->
        <div id="analysis-panel">
            <div class="ap-header" onclick="toggleAnalysis()">
                <div class="ap-header-left">
                    <div class="ap-icon"><i class="fas fa-chart-pie"></i></div>
                    <span class="ap-title">Analysis Summary</span>
                </div>
                <i class="fas fa-chevron-up ap-toggle" id="apToggleIcon"></i>
            </div>
            <div class="ap-body" id="apBody">
                @foreach ($categoryLegend as $cat)
                    <div class="ap-card" data-cat="{{ $cat['name'] }}">
                        <div
                            style="position:absolute;left:0;top:0;bottom:0;width:3px;background:{{ $cat['color'] }};border-radius:9px 0 0 9px;">
                        </div>
                        <div class="ap-card-num" id="apCount-{{ Str::slug($cat['name']) }}"
                            style="color:{{ $cat['color'] }}">0</div>
                        <div class="ap-card-name">{{ str_replace('_', ' ', $cat['name']) }}</div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Coord Bar -->
        <div id="coord-bar">
            <span style="color:var(--muted)">LAT</span>&nbsp;<span id="coordLat">—</span>
            <div class="cb-sep"></div>
            <span style="color:var(--muted)">LNG</span>&nbsp;<span id="coordLng">—</span>
            <div class="cb-sep"></div>
            <span style="color:var(--muted)">ZOOM</span>&nbsp;<span id="coordZoom">10</span>
        </div>
    </div><!-- /map-root -->

    <!-- Metadata Modal -->
    <div class="modal fade" id="metadataModal" tabindex="-1" aria-hidden="true">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header text-white">
                <h5 class="modal-title fw-bold"><i class="fas fa-database me-2"></i>Shapefile Metadata</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="sticky-top p-4 border-bottom">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-2">
                                <span class="badge fs-6" id="modalCategory"
                                    style="background-color:rgba(183,28,28,.1);color:#b71c1c;"></span>
                            </div>
                            <h6 class="fw-semibold mb-1">Metadata Items</h6>
                            <p class="text-muted mb-0" id="metadataCount">0 items</p>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <small class="text-muted">
                                <i class="fas fa-calendar-alt me-1" style="color:#b71c1c;"></i>
                                <span id="modalTimestamp">Loaded just now</span>
                            </small>
                        </div>
                    </div>
                </div>
                <div class="p-4" id="metadataModalBody"></div>
            </div>
            <div class="modal-footer border-top-0 bg-light">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Close
                </button>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet.fullscreen@1.6.0/Control.FullScreen.js"></script>

    @push('scripts')
        <script>
            /* ═══════════════════════════════════════════════════════
               SERVER DATA
            ═══════════════════════════════════════════════════════ */
            const shapefiles = @json($geojson);
            const categories = @json($categories);
            const classifications = @json($classifications);
            const defaultLoc = @json($defaultLoc);
            const provinceBoundary = @json($provinceBoundary);

            const locById = {};
            (defaultLoc || []).forEach(function(loc) {
                locById[loc.id] = {
                    district: (loc.district || '').trim(),
                    municity: (loc.municity || '').trim(),
                    brgy: (loc.brgy || '').trim(),
                };
            });

            const locIdx = (function() {
                const districts = new Map(), municities = new Map(), brgys = new Map();
                (defaultLoc || []).forEach(function(loc) {
                    const d = (loc.district || '').trim();
                    const m = (loc.municity || '').trim();
                    const b = (loc.brgy || '').trim();
                    if (d) {
                        if (!districts.has(d)) districts.set(d, new Set());
                        if (m) districts.get(d).add(m);
                    }
                    if (m) {
                        if (!municities.has(m)) municities.set(m, { district: d, brgys: new Set() });
                        if (b) municities.get(m).brgys.add(b);
                    }
                    if (b) {
                        if (!brgys.has(b)) brgys.set(b, { district: d, municity: m });
                    }
                });
                return { districts, municities, brgys };
            })();

            /* ── State ── */
            let selCats = new Set(), selCls = new Set();
            let advOpen = false, apOpen = true;
            let quickSearchQ = '', dateFrom = '', dateTo = '';
            let selDistricts = new Set(), selMunicities = new Set(), selBrgys = new Set();
            let locSearchQ = '', activeLocTab = 'district', locOpen = false;
            let metadataFilters = []; // Array of {key, operator, value}
            let mapFixed = false;
            let hintDismissed = false;
            let selectedDatePreset = null;
            let currentMetaKey = ''; // selected key for custom dropdown

            function escapeHtml(str) {
                return String(str).replace(/[&<>'"]/g, function(m) {
                    if (m === '&') return '&amp;';
                    if (m === '<') return '&lt;';
                    if (m === '>') return '&gt;';
                    if (m === '"') return '&quot;';
                    if (m === "'") return '&#39;';
                    return m;
                });
            }
            function esc(s) { return escapeHtml(s); }

            function dismissHint() {
                hintDismissed = true;
                document.getElementById('no-filter-hint').style.display = 'none';
            }

            function onMapFixChange() {
                mapFixed = document.getElementById('mapFixChk').checked;
                document.getElementById('mapFixLabel').classList.toggle('active', mapFixed);
            }

            function hasAnyActiveFilter() {
                return quickSearchQ.length > 0 || selCats.size > 0 || selCls.size > 0 ||
                    dateFrom !== '' || dateTo !== '' ||
                    selDistricts.size > 0 || selMunicities.size > 0 || selBrgys.size > 0 ||
                    metadataFilters.length > 0;
            }

            function hasAnyActiveFilterWithoutMeta() {
                return quickSearchQ.length > 0 || selCats.size > 0 || selCls.size > 0 ||
                    dateFrom !== '' || dateTo !== '' ||
                    selDistricts.size > 0 || selMunicities.size > 0 || selBrgys.size > 0;
            }

            function hasLocationFilter() {
                return selDistricts.size > 0 || selMunicities.size > 0 || selBrgys.size > 0;
            }

            /* ── Dynamic helpers for metadata keys and date presets ── */
            function getFilteredFeaturesExcludingMetadata() {
                return shapefiles.filter(function(item) {
                    if (!item.geometry) return false;
                    if (selCats.size && !selCats.has(item.category)) return false;
                    if (selCls.size && !selCls.has(String(item.classification_id))) return false;
                    if (dateFrom && item.survey_date && item.survey_date < dateFrom) return false;
                    if (dateTo && item.survey_date && item.survey_date > dateTo) return false;
                    if ((dateFrom || dateTo) && !item.survey_date) return false;
                    if (!featureMatchesLocation(item)) return false;
                    if (quickSearchQ) {
                        const hay = [item.description||'', item.location||'', item.category||'', item.classification||'', item.survey_date||''].join(' ').toLowerCase();
                        if (!hay.includes(quickSearchQ)) return false;
                    }
                    return true;
                });
            }

            function getFilteredFeaturesExcludingDate() {
                return shapefiles.filter(function(item) {
                    if (!item.geometry) return false;
                    if (selCats.size && !selCats.has(item.category)) return false;
                    if (selCls.size && !selCls.has(String(item.classification_id))) return false;
                    if (!featureMatchesLocation(item)) return false;
                    if (!featureMatchesMetadata(item)) return false;
                    if (quickSearchQ) {
                        const hay = [item.description||'', item.location||'', item.category||'', item.classification||'', item.survey_date||''].join(' ').toLowerCase();
                        if (!hay.includes(quickSearchQ)) return false;
                    }
                    return true;
                });
            }

            // Custom dropdown for meta keys
            function toggleMetaKeyDropdown(event) {
                event.stopPropagation();
                const container = document.getElementById('metaKeyCustomSelect');
                const wasOpen = container.classList.contains('open');
                // close all other custom selects
                document.querySelectorAll('.custom-select.open').forEach(el => {
                    if (el !== container) el.classList.remove('open');
                });
                if (wasOpen) {
                    container.classList.remove('open');
                } else {
                    container.classList.add('open');
                }
            }

            function closeMetaKeyDropdown() {
                const container = document.getElementById('metaKeyCustomSelect');
                container.classList.remove('open');
            }

            function selectMetaKey(key) {
                currentMetaKey = key;
                const labelEl = document.getElementById('selectedMetaKeyLabel');
                labelEl.textContent = key === '' ? '-- Select key --' : key;
                // Update active class in dropdown
                document.querySelectorAll('#metaKeyDropdownList .custom-select-option').forEach(opt => {
                    if (opt.dataset.value === key) {
                        opt.classList.add('selected');
                    } else {
                        opt.classList.remove('selected');
                    }
                });
                closeMetaKeyDropdown();
            }

            function refreshMetadataKeys() {
                const filtered = getFilteredFeaturesExcludingMetadata();
                const keySet = new Set();
                filtered.forEach(function(item) {
                    (item.metadata || []).forEach(function(m) {
                        if (m.meta_key) keySet.add(m.meta_key);
                    });
                });
                const keys = Array.from(keySet).sort();
                const dropdownList = document.getElementById('metaKeyDropdownList');
                let html = '<div class="custom-select-option" data-value="">-- Select key --</div>';
                keys.forEach(function(k) {
                    const selectedAttr = (currentMetaKey === k) ? 'selected' : '';
                    html += `<div class="custom-select-option ${selectedAttr}" data-value="${esc(k)}">${esc(k)}</div>`;
                });
                dropdownList.innerHTML = html;
                // Re-attach click handlers
                dropdownList.querySelectorAll('.custom-select-option').forEach(opt => {
                    opt.addEventListener('click', function(e) {
                        e.stopPropagation();
                        const val = this.dataset.value;
                        selectMetaKey(val);
                    });
                });
                // Update label if current key no longer exists
                if (currentMetaKey && !keySet.has(currentMetaKey)) {
                    selectMetaKey('');
                } else if (!currentMetaKey) {
                    document.getElementById('selectedMetaKeyLabel').textContent = '-- Select key --';
                } else {
                    document.getElementById('selectedMetaKeyLabel').textContent = currentMetaKey;
                }
                // Show/hide notice
                if (keys.length === 0) {
                    let notice = document.querySelector('#metadataLocDropdown .meta-notice');
                    if (!notice) {
                        notice = document.createElement('div');
                        notice.className = 'meta-notice';
                        notice.innerHTML = '<i class="fas fa-info-circle"></i> No metadata keys available with current filters';
                        document.querySelector('#metadataLocDropdown .meta-hdr').after(notice);
                    }
                } else {
                    const notice = document.querySelector('#metadataLocDropdown .meta-notice');
                    if (notice) notice.remove();
                }
            }

            function populateDatePresets() {
                const filtered = getFilteredFeaturesExcludingDate();
                const dateSet = new Set();
                filtered.forEach(function(item) {
                    if (item.survey_date) dateSet.add(item.survey_date);
                });
                const dates = Array.from(dateSet).sort().reverse();
                const listEl = document.getElementById('datePresetList');
                if (!dates.length) {
                    listEl.innerHTML = '<div class="ms-date-preset-item" style="justify-content:center;color:var(--muted);font-style:italic;font-size:10px;cursor:default;">No dates available</div>';
                    return;
                }
                let html = '';
                dates.forEach(function(d) {
                    const isSelected = selectedDatePreset === d;
                    html += `<div class="ms-date-preset-item ${isSelected ? 'selected' : ''}" data-date="${esc(d)}">
                        <div class="ms-date-dot"></div>
                        <span>${esc(d)}</span>
                    </div>`;
                });
                listEl.innerHTML = html;
                if (selectedDatePreset && !dateSet.has(selectedDatePreset)) {
                    selectedDatePreset = null;
                    dateFrom = '';
                    dateTo = '';
                    document.getElementById('dateFrom').value = '';
                    document.getElementById('dateTo').value = '';
                    document.getElementById('dateBadge').style.display = 'none';
                }
            }

            function updateDynamicData() {
                refreshMetadataKeys();
                populateDatePresets();
            }

            function refreshAll() {
                updateDynamicData();
                renderMap();
            }

            /* ── Hint ── */
            function updateHint() {
                if (hintDismissed) return;
                const hint = document.getElementById('no-filter-hint');
                if (!advOpen && !hasAnyActiveFilter()) {
                    hint.style.display = '';
                } else {
                    hint.style.display = 'none';
                }
            }

            /* ── Advanced Search Toggle ── */
            function toggleAdvSearch() {
                advOpen = !advOpen;
                document.getElementById('advSearchBtn').classList.toggle('active', advOpen);
                const row = document.getElementById('filter-row');
                if (advOpen) {
                    row.classList.add('visible');
                    updateHint();
                    renderLocLists();
                    refreshAll();
                } else {
                    row.classList.remove('visible');
                    document.querySelectorAll('.ms-pill.open, .metadata-pill.open').forEach(p => p.classList.remove('open'));
                    closeLoc();
                    updateHint();
                }
            }

            function togglePill(id) {
                const pill = document.getElementById(id);
                const was = pill.classList.contains('open');
                document.querySelectorAll('.ms-pill.open, .metadata-pill.open').forEach(p => p.classList.remove('open'));
                closeLoc();
                if (!was) pill.classList.add('open');
            }

            /* ── Metadata (multi-filter, like location) ── */
            function toggleMetadataPill() {
                const pill = document.getElementById('metadataPill');
                const was = pill.classList.contains('open');
                document.querySelectorAll('.ms-pill.open, .metadata-pill.open').forEach(p => p.classList.remove('open'));
                closeLoc();
                if (!was) {
                    pill.classList.add('open');
                    refreshMetadataKeys();
                }
            }

            function addMetadataFilter() {
                const key = currentMetaKey;
                const op = document.getElementById('metaOpSelect').value;
                const value = document.getElementById('metaValueInput').value.trim();
                if (!key || value === '') return;
                const exists = metadataFilters.some(f => f.key === key && f.operator === op && f.value === value);
                if (!exists) {
                    metadataFilters.push({ key, operator: op, value });
                }
                document.getElementById('metaValueInput').value = '';
                renderMetaChips();
                syncMetaLabel();
                refreshAll();
            }

            function removeMetadataFilter(index) {
                metadataFilters.splice(index, 1);
                renderMetaChips();
                syncMetaLabel();
                refreshAll();
            }

            function clearMetadataFilters() {
                metadataFilters = [];
                document.getElementById('metaValueInput').value = '';
                selectMetaKey('');
                renderMetaChips();
                syncMetaLabel();
                refreshAll();
            }

            function renderMetaChips() {
                const chipsEl = document.getElementById('metaChips');
                const emptyEl = document.getElementById('metaChipsEmpty');
                chipsEl.querySelectorAll('.meta-chip').forEach(c => c.remove());
                if (!metadataFilters.length) {
                    emptyEl.style.display = '';
                    document.getElementById('metaFooterInfo').textContent = 'Select key and enter value';
                    return;
                }
                emptyEl.style.display = 'none';
                metadataFilters.forEach(function(f, i) {
                    const span = document.createElement('span');
                    span.className = 'meta-chip';
                    span.innerHTML = `<span class="meta-chip-lbl">${esc(f.operator)}</span>${esc(f.key)}: ${esc(f.value)}<span class="meta-chip-x" data-idx="${i}">✕</span>`;
                    chipsEl.appendChild(span);
                });
                const total = metadataFilters.length;
                document.getElementById('metaFooterInfo').textContent = `${total} filter${total === 1 ? '' : 's'} active`;
            }

            function syncMetaLabel() {
                const lbl = document.getElementById('metadataLabel');
                const bdg = document.getElementById('metadataBadge');
                const n = metadataFilters.length;
                if (!n) {
                    lbl.textContent = 'Metadata';
                    bdg.style.display = 'none';
                } else {
                    lbl.textContent = n === 1 ? ('Meta: ' + metadataFilters[0].key) : 'Metadata';
                    bdg.textContent = n;
                    bdg.style.display = 'inline-block';
                }
            }

            function featureMatchesMetadata(feature) {
                if (!metadataFilters.length) return true;
                for (const mf of metadataFilters) {
                    if (!mf.key || mf.value === '') continue;
                    if (!feature.metadata || !feature.metadata.length) return false;
                    const entry = feature.metadata.find(m => m.meta_key === mf.key);
                    if (!entry) return false;
                    const mv = entry.meta_value, fv = mf.value, op = mf.operator;
                    const nm = parseFloat(mv), nf = parseFloat(fv);
                    let pass = false;
                    if (op === 'contains') {
                        pass = String(mv).toLowerCase().includes(String(fv).toLowerCase());
                    } else if (!isNaN(nm) && !isNaN(nf)) {
                        switch (op) {
                            case '=':  pass = nm == nf; break;
                            case '!=': pass = nm != nf; break;
                            case '>':  pass = nm > nf; break;
                            case '<':  pass = nm < nf; break;
                            case '>=': pass = nm >= nf; break;
                            case '<=': pass = nm <= nf; break;
                            default:   pass = false;
                        }
                    } else {
                        switch (op) {
                            case '=':  pass = String(mv) === String(fv); break;
                            case '!=': pass = String(mv) !== String(fv); break;
                            case 'contains': pass = String(mv).toLowerCase().includes(String(fv).toLowerCase()); break;
                            default:   pass = false;
                        }
                    }
                    if (!pass) return false;
                }
                return true;
            }

            /* ── Date Presets click ── */
            function onDatePresetClick(date) {
                if (selectedDatePreset === date) {
                    selectedDatePreset = null;
                    dateFrom = '';
                    dateTo = '';
                    document.getElementById('dateFrom').value = '';
                    document.getElementById('dateTo').value = '';
                } else {
                    selectedDatePreset = date;
                    dateFrom = date;
                    dateTo = date;
                    document.getElementById('dateFrom').value = date;
                    document.getElementById('dateTo').value = date;
                }
                document.getElementById('dateBadge').style.display = (dateFrom || dateTo) ? 'inline-block' : 'none';
                refreshAll();
            }

            /* ── Location ── */
            function openLoc() {
                locOpen = true;
                document.getElementById('locDropdown').classList.add('loc-visible');
                document.getElementById('locArrow').style.transform = 'rotate(180deg)';
                document.getElementById('locTrigger').classList.add('loc-open');
                renderLocLists();
            }

            function closeLoc() {
                locOpen = false;
                document.getElementById('locDropdown').classList.remove('loc-visible');
                document.getElementById('locArrow').style.transform = '';
                document.getElementById('locTrigger').classList.remove('loc-open');
            }

            function toggleLocItem(type, value) {
                const set = type === 'district' ? selDistricts : type === 'municity' ? selMunicities : selBrgys;
                set.has(value) ? set.delete(value) : set.add(value);
                renderLocLists();
                syncLocLabel();
                refreshAll();
            }

            function clearLocFilter() {
                selDistricts.clear();
                selMunicities.clear();
                selBrgys.clear();
                locSearchQ = '';
                document.getElementById('locSearchInput').value = '';
                renderLocLists();
                syncLocLabel();
                refreshAll();
            }

            function renderLocLists() {
                const q = locSearchQ.toLowerCase();

                const allDist = Array.from(locIdx.districts.keys()).sort();
                const visDist = allDist.filter(d => !q || d.toLowerCase().includes(q));
                document.getElementById('cntDistrict').textContent = visDist.length;
                document.getElementById('panelDistrict').innerHTML = visDist.length ?
                    visDist.map(function(d) {
                        const on = selDistricts.has(d);
                        const mc = locIdx.districts.get(d).size;
                        return `<div class="loc-item${on?' active':''}" data-value="${esc(d)}">
                            <div class="loc-item-ico"><i class="fas fa-city"></i></div>
                            <div class="loc-item-txt">
                                <span class="loc-item-name">${esc(d)}</span>
                                <span class="loc-item-sub">${mc} municipalit${mc===1?'y':'ies'}</span>
                            </div><div class="loc-item-chk"></div></div>`;
                    }).join('') :
                    '<div class="loc-empty"><i class="fas fa-search"></i>No districts found</div>';

                let allMuni = Array.from(locIdx.municities.keys()).sort();
                if (selDistricts.size) allMuni = allMuni.filter(m => selDistricts.has(locIdx.municities.get(m).district));
                const visMuni = allMuni.filter(m => !q || m.toLowerCase().includes(q));
                document.getElementById('cntMunicity').textContent = visMuni.length;
                document.getElementById('panelMunicity').innerHTML = visMuni.length ?
                    visMuni.map(function(m) {
                        const on = selMunicities.has(m);
                        const info = locIdx.municities.get(m);
                        const bc = info.brgys.size;
                        return `<div class="loc-item${on?' active':''}" data-value="${esc(m)}">
                            <div class="loc-item-ico"><i class="fas fa-building"></i></div>
                            <div class="loc-item-txt">
                                <span class="loc-item-name">${esc(m)}</span>
                                <span class="loc-item-sub">${esc(info.district||'—')} · ${bc} barangay${bc===1?'':'s'}</span>
                            </div><div class="loc-item-chk"></div></div>`;
                    }).join('') :
                    '<div class="loc-empty"><i class="fas fa-search"></i>No municipalities found</div>';

                let allBrgy = Array.from(locIdx.brgys.keys()).sort();
                if (selMunicities.size) allBrgy = allBrgy.filter(b => selMunicities.has(locIdx.brgys.get(b).municity));
                else if (selDistricts.size) allBrgy = allBrgy.filter(b => selDistricts.has(locIdx.brgys.get(b).district));
                const visBrgy = allBrgy.filter(b => !q || b.toLowerCase().includes(q));
                document.getElementById('cntBrgy').textContent = visBrgy.length;
                document.getElementById('panelBrgy').innerHTML = visBrgy.length ?
                    visBrgy.map(function(b) {
                        const on = selBrgys.has(b);
                        const info = locIdx.brgys.get(b);
                        return `<div class="loc-item${on?' active':''}" data-value="${esc(b)}">
                            <div class="loc-item-ico"><i class="fas fa-home"></i></div>
                            <div class="loc-item-txt">
                                <span class="loc-item-name">${esc(b)}</span>
                                <span class="loc-item-sub">${esc(info.municity||info.district||'—')}</span>
                            </div><div class="loc-item-chk"></div></div>`;
                    }).join('') :
                    '<div class="loc-empty"><i class="fas fa-search"></i>No barangays found</div>';

                const chipsEl = document.getElementById('locChips');
                const emptyEl = document.getElementById('locChipsEmpty');
                chipsEl.querySelectorAll('.loc-chip').forEach(c => c.remove());
                const chips = [];
                selDistricts.forEach(d => chips.push({ type: 'district', value: d, label: 'District' }));
                selMunicities.forEach(m => chips.push({ type: 'municity', value: m, label: 'Muni' }));
                selBrgys.forEach(b => chips.push({ type: 'brgy', value: b, label: 'Brgy' }));
                if (chips.length) {
                    emptyEl.style.display = 'none';
                    chips.forEach(function(c) {
                        const span = document.createElement('span');
                        span.className = 'loc-chip';
                        span.innerHTML = `<span class="loc-chip-lbl">${esc(c.label)}</span>${esc(c.value)}<span class="loc-chip-x" data-type="${c.type}" data-value="${esc(c.value)}">✕</span>`;
                        chipsEl.appendChild(span);
                    });
                } else {
                    emptyEl.style.display = '';
                }

                const total = selDistricts.size + selMunicities.size + selBrgys.size;
                document.getElementById('locFooterInfo').textContent = total ?
                    `${total} location filter${total===1?'':'s'} active` : 'Select a location to filter';
            }

            function syncLocLabel() {
                const total = selDistricts.size + selMunicities.size + selBrgys.size;
                const lbl = document.getElementById('locLabel');
                const bdg = document.getElementById('locBadge');
                if (!total) {
                    lbl.textContent = 'Location';
                    bdg.style.display = 'none';
                } else {
                    const first = selDistricts.size ? [...selDistricts][0] : selMunicities.size ? [...selMunicities][0] : [...selBrgys][0];
                    lbl.textContent = total === 1 ? first : 'Location';
                    bdg.textContent = total;
                    bdg.style.display = 'inline-block';
                }
            }

            function featureMatchesLocation(item) {
                if (!hasLocationFilter()) return true;
                const locId = item.default_location_id;
                if (locId && locById[locId]) {
                    const ld = locById[locId];
                    if (selBrgys.size && selBrgys.has(ld.brgy)) return true;
                    if (selMunicities.size && selMunicities.has(ld.municity)) return true;
                    if (selDistricts.size && selDistricts.has(ld.district)) return true;
                    return false;
                }
                const loc = (item.location || '').toLowerCase();
                if (!loc) return false;
                for (const b of selBrgys) if (loc.includes(b.toLowerCase())) return true;
                for (const m of selMunicities) if (loc.includes(m.toLowerCase())) return true;
                for (const d of selDistricts) if (loc.includes(d.toLowerCase())) return true;
                return false;
            }

            /* ── Category / Classification ── */
            function toggleCat(val, el) {
                selCats.has(val) ? selCats.delete(val) : selCats.add(val);
                el.classList.toggle('selected', selCats.has(val));
                syncCatLabel();
                syncClsOptions();
                refreshAll();
            }

            function selectAllCat() {
                const opts = document.querySelectorAll('#catDropdown .ms-opt:not(.ms-all)');
                const allOn = selCats.size === categories.length;
                selCats.clear();
                opts.forEach(o => o.classList.remove('selected'));
                if (!allOn) {
                    categories.forEach(c => selCats.add(c.name));
                    opts.forEach(o => o.classList.add('selected'));
                }
                syncCatLabel();
                syncClsOptions();
                refreshAll();
            }

            function syncCatLabel() {
                const lbl = document.getElementById('catLabel');
                const bdg = document.getElementById('catBadge');
                if (!selCats.size || selCats.size === categories.length) {
                    lbl.textContent = 'Category';
                    bdg.style.display = 'none';
                } else {
                    lbl.textContent = selCats.size === 1 ? [...selCats][0] : 'Category';
                    bdg.textContent = selCats.size;
                    bdg.style.display = 'inline-block';
                }
            }

            function toggleCls(val, el) {
                val = String(val);
                selCls.has(val) ? selCls.delete(val) : selCls.add(val);
                el.classList.toggle('selected', selCls.has(val));
                syncClsLabel();
                refreshAll();
            }

            function selectAllCls() {
                const opts = document.querySelectorAll('#clsDropdown .ms-opt:not(.ms-all):not([style*="display: none"])');
                const allOn = [...opts].every(o => o.classList.contains('selected'));
                if (allOn) {
                    selCls.clear();
                    opts.forEach(o => o.classList.remove('selected'));
                } else {
                    opts.forEach(o => {
                        selCls.add(String(o.dataset.val));
                        o.classList.add('selected');
                    });
                }
                syncClsLabel();
                refreshAll();
            }

            function syncClsLabel() {
                const lbl = document.getElementById('clsLabel');
                const bdg = document.getElementById('clsBadge');
                if (!selCls.size || selCls.size === classifications.length) {
                    lbl.textContent = 'Classification';
                    bdg.style.display = 'none';
                } else {
                    const f = classifications.find(c => selCls.has(String(c.id)));
                    lbl.textContent = selCls.size === 1 && f ? f.name : 'Classification';
                    bdg.textContent = selCls.size;
                    bdg.style.display = 'inline-block';
                }
            }

            function syncClsOptions() {
                document.querySelectorAll('#clsDropdown .ms-opt:not(.ms-all)').forEach(function(o) {
                    const cid = parseInt(o.dataset.val);
                    const show = !selCats.size || shapefiles.some(s => selCats.has(s.category) && s.classification_id === cid);
                    o.style.display = show ? '' : 'none';
                    if (!show) {
                        selCls.delete(String(cid));
                        o.classList.remove('selected');
                    }
                });
                syncClsLabel();
            }

            function onDateChange() {
                selectedDatePreset = null;
                dateFrom = document.getElementById('dateFrom').value;
                dateTo = document.getElementById('dateTo').value;
                document.getElementById('dateBadge').style.display = (dateFrom || dateTo) ? 'inline-block' : 'none';
                refreshAll();
            }

            function onQuickSearch() {
                quickSearchQ = document.getElementById('quickSearch').value.trim().toLowerCase();
                updateHint();
                refreshAll();
            }

            /* ── Feature Filters ── */
            function featureMatchesFilters(item) {
                if (!item.geometry) return false;
                if (selCats.size && !selCats.has(item.category)) return false;
                if (selCls.size && !selCls.has(String(item.classification_id))) return false;
                if (dateFrom && item.survey_date && item.survey_date < dateFrom) return false;
                if (dateTo && item.survey_date && item.survey_date > dateTo) return false;
                if ((dateFrom || dateTo) && !item.survey_date) return false;
                if (!featureMatchesLocation(item)) return false;
                if (!featureMatchesMetadata(item)) return false;
                if (quickSearchQ) {
                    const hay = [item.description||'', item.location||'', item.category||'', item.classification||'', item.survey_date||''].join(' ').toLowerCase();
                    if (!hay.includes(quickSearchQ)) return false;
                }
                return true;
            }

            function clearFilters() {
                selCats.clear();
                selCls.clear();
                quickSearchQ = '';
                dateFrom = '';
                dateTo = '';
                selectedDatePreset = null;
                metadataFilters = [];
                currentMetaKey = '';
                document.getElementById('quickSearch').value = '';
                document.getElementById('dateFrom').value = '';
                document.getElementById('dateTo').value = '';
                document.getElementById('dateBadge').style.display = 'none';
                document.getElementById('metaValueInput').value = '';
                document.querySelectorAll('.ms-opt').forEach(o => o.classList.remove('selected'));
                syncCatLabel();
                syncClsOptions();
                syncClsLabel();
                renderMetaChips();
                syncMetaLabel();
                selectMetaKey('');
                clearLocFilter();
                refreshAll();
                updateHint();
            }

            /* ── Analysis ── */
            function toggleAnalysis() {
                apOpen = !apOpen;
                document.getElementById('apBody').classList.toggle('hidden', !apOpen);
                document.getElementById('apToggleIcon').classList.toggle('collapsed', !apOpen);
            }

            function updateAnalysisCounts(items) {
                document.querySelectorAll('#apBody .ap-card').forEach(function(card) {
                    const slug = card.dataset.cat.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
                    const el = document.getElementById('apCount-' + slug);
                    if (el) el.textContent = '0';
                });
                const counts = {};
                items.forEach(function(item) {
                    counts[item.category] = (counts[item.category] || 0) + 1;
                });
                document.querySelectorAll('#apBody .ap-card').forEach(function(card) {
                    const catName = card.dataset.cat;
                    const slug = catName.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
                    const el = document.getElementById('apCount-' + slug);
                    if (el) el.textContent = counts[catName] || 0;
                });
            }

            /* ── Map ── */
            let mapInstance = null, featureLayer = null, defaultLocLayer = null, provinceLayer = null, legendCtrl = null;

            function getColor(item) { return item.classification_color || '#b71c1c'; }

            function updateLegend(shapes) {
                if (legendCtrl) { mapInstance.removeControl(legendCtrl); legendCtrl = null; }
                if (!shapes.length) return;
                legendCtrl = L.control({ position: 'bottomright' });
                legendCtrl.onAdd = function() {
                    const div = L.DomUtil.create('div', 'gis-legend');
                    const used = classifications.filter(c => [...new Set(shapes.map(s => s.classification_id))].includes(c.id));
                    if (!used.length) return div;
                    div.innerHTML = '<div class="legend-title">Legend</div>' +
                        used.map(c => `<div class="legend-row"><div class="legend-swatch" style="background:${c.color};"></div><span>${esc(c.name)}</span></div>`).join('');
                    return div;
                };
                legendCtrl.addTo(mapInstance);
            }

            function fitToProvince() {
                if (provinceLayer && provinceLayer.getLayers().length) {
                    const b = provinceLayer.getBounds();
                    if (b && b.isValid()) {
                        mapInstance.fitBounds(b, { padding: [50, 50], maxZoom: 12 });
                        return;
                    }
                }
                mapInstance.setView([14.28, 121.4], 10);
            }

            function renderMap() {
                if (!featureLayer) return;

                featureLayer.clearLayers();
                defaultLocLayer.clearLayers();

                const filterActive = hasAnyActiveFilter();
                const locActive = hasLocationFilter();
                const toastEl = document.getElementById('no-results-toast');

                updateHint();
                toastEl.style.display = 'none';

                if (!filterActive) {
                    document.getElementById('featureCount').textContent = '0';
                    updateAnalysisCounts([]);
                    if (legendCtrl) { mapInstance.removeControl(legendCtrl); legendCtrl = null; }
                    if (!mapFixed) fitToProvince();
                    return;
                }

                const filtered = shapefiles.filter(featureMatchesFilters);
                document.getElementById('featureCount').textContent = filtered.length;
                updateAnalysisCounts(filtered);

                if (!filtered.length) {
                    toastEl.style.display = 'block';
                    updateLegend([]);
                } else {
                    filtered.forEach(function(item) {
                        const color = getColor(item);
                        const style = { color, fillColor: color, weight: 1, opacity: 0.85, fillOpacity: 0.22 };
                        L.geoJSON(item.geometry, {
                            style,
                            pointToLayer: function(_, latlng) {
                                return L.circleMarker(latlng, {
                                    radius: 8, fillColor: color, color, weight: 2, opacity: 1, fillOpacity: 0.85
                                });
                            },
                            onEachFeature: function(_, layer) {
                                const MAX = 5;
                                let rows = '';
                                [{ key: 'Description', val: item.description }, { key: 'Location', val: item.location }, { key: 'Date Collected', val: item.survey_date }]
                                    .forEach(function(f) {
                                        if (f.val) rows += `<div class="popup-row"><span class="popup-key">${esc(f.key)}</span><span class="popup-val">${esc(f.val)}</span></div>`;
                                    });
                                let extra = 0;
                                if (item.metadata && item.metadata.length) {
                                    item.metadata.slice(0, MAX).forEach(function(m) {
                                        rows += `<div class="popup-row"><span class="popup-key">${esc(m.meta_key)}</span><span class="popup-val">${m.meta_value ? esc(m.meta_value) : '<em style="opacity:.4">—</em>'}</span></div>`;
                                    });
                                    extra = item.metadata.length - MAX;
                                }
                                const popup = `<div class="popup-wrap">
                                    <div class="popup-cat">${esc(item.category)}</div>
                                    <span class="popup-cls" style="background:${item.classification_color||'#6c757d'}">${esc(item.classification||'No Classification')}</span>
                                    <div class="popup-divider"></div>
                                    ${rows||'<div class="popup-empty"><i class="fas fa-info-circle me-1"></i>No data available</div>'}
                                    ${extra>0?`<div class="popup-more"><button class="popup-more-btn view-meta" data-id="${item.feature_id}"><i class="fas fa-table me-1"></i>View all ${item.metadata.length} fields</button></div>`:''}
                                </div>`;
                                layer.bindPopup(popup, { maxWidth: 340 });
                                layer.on('mouseover', function() { layer.setStyle({ weight: 2, fillOpacity: 0.38 }); });
                                layer.on('mouseout', function() { layer.setStyle(style); });
                                layer.addTo(featureLayer);
                            }
                        });
                    });
                }

                const filteredBoundaries = defaultLoc.filter(function(loc) {
                    const d = (loc.district || '').trim();
                    const m = (loc.municity || '').trim();
                    const b = (loc.brgy || '').trim();

                    if (locActive) {
                        const distOk = !selDistricts.size || selDistricts.has(d);
                        const muniOk = !selMunicities.size || selMunicities.has(m);
                        const brgyOk = !selBrgys.size || selBrgys.has(b);
                        return distOk && muniOk && brgyOk;
                    }

                    if (quickSearchQ) {
                        const term = quickSearchQ.toLowerCase();
                        return d.toLowerCase().includes(term) || m.toLowerCase().includes(term) || b.toLowerCase().includes(term);
                    }

                    return false;
                });

                if (filteredBoundaries.length > 0) {
                    L.geoJSON(
                        filteredBoundaries.map(function(loc) {
                            return { type: 'Feature', geometry: loc.geometry, properties: { district: loc.district, municity: loc.municity, brgy: loc.brgy } };
                        }), {
                            style: { color: '#3b82f6', weight: 2, fillOpacity: 0, opacity: 0.7 },
                            interactive: false
                        }
                    ).addTo(defaultLocLayer);
                }

                if (!mapFixed) {
                    if (locActive) {
                        const locBounds = defaultLocLayer.getBounds();
                        if (locBounds && locBounds.isValid()) {
                            mapInstance.fitBounds(locBounds, { padding: [60, 60], maxZoom: 14 });
                        } else {
                            const featBounds = featureLayer.getBounds();
                            if (featBounds && featBounds.isValid()) {
                                mapInstance.fitBounds(featBounds, { padding: [80, 80], maxZoom: 14 });
                            } else {
                                fitToProvince();
                            }
                        }
                    } else {
                        const featBounds = featureLayer.getBounds();
                        if (featBounds && featBounds.isValid()) {
                            mapInstance.fitBounds(featBounds, { padding: [80, 80], maxZoom: 14 });
                        } else {
                            fitToProvince();
                        }
                    }
                }

                updateLegend(filtered);
            }

            function printMap() {
                if (!mapFixed) {
                    if (hasLocationFilter()) {
                        const lb = defaultLocLayer.getBounds();
                        if (lb && lb.isValid()) {
                            mapInstance.fitBounds(lb, { padding: [60, 60], maxZoom: 14 });
                        } else if (featureLayer.getLayers().length) {
                            mapInstance.fitBounds(featureLayer.getBounds(), { padding: [80, 80], maxZoom: 16 });
                        }
                    } else if (featureLayer && featureLayer.getLayers().length > 0) {
                        mapInstance.fitBounds(featureLayer.getBounds(), { padding: [80, 80], maxZoom: 16 });
                    } else {
                        fitToProvince();
                    }
                }
                setTimeout(function() { window.print(); }, 800);
            }

            /* ─────────────── MAP RESIZE FIX ─────────────── */
            function handleMapResize() {
                if (mapInstance) {
                    setTimeout(() => mapInstance.invalidateSize({ animate: false }), 50);
                }
            }

            const bodyObserver = new MutationObserver(function(mutations) {
                mutations.forEach(function(mut) {
                    if (mut.attributeName === 'class') handleMapResize();
                });
            });
            bodyObserver.observe(document.body, { attributes: true });

            const mapRoot = document.getElementById('map-root');
            if (mapRoot) mapRoot.addEventListener('transitionend', handleMapResize);
            window.addEventListener('resize', handleMapResize);

            /* ─────────────── INIT ─────────────── */
            document.addEventListener('DOMContentLoaded', function() {

                mapInstance = L.map('map', {
                    center: [14.28, 121.4],
                    zoom: 10,
                    zoomControl: true,
                    scrollWheelZoom: true
                });

                const osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 20,
                    attribution: '&copy; OpenStreetMap contributors'
                }).addTo(mapInstance);
                const satellite = L.tileLayer('https://{s}.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
                    maxZoom: 20, subdomains: ['mt0','mt1','mt2','mt3']
                });
                const hybrid = L.tileLayer('https://{s}.google.com/vt/lyrs=y&x={x}&y={y}&z={z}', {
                    maxZoom: 20, subdomains: ['mt0','mt1','mt2','mt3']
                });
                const cartoLight = L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
                    maxZoom: 20, attribution: '&copy; CartoDB'
                });
                const cartoDark = L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
                    maxZoom: 20, attribution: '&copy; CartoDB'
                });

                L.control.scale({ imperial: false, position: 'bottomleft' }).addTo(mapInstance);
                L.control.layers({
                    'OSM (Street)': osm,
                    'Satellite': satellite,
                    'Hybrid': hybrid,
                    'Carto Light': cartoLight,
                    'Carto Dark': cartoDark
                }).addTo(mapInstance);
                L.control.fullscreen({
                    position: 'topleft',
                    title: 'Fullscreen',
                    titleCancel: 'Exit fullscreen'
                }).addTo(mapInstance);

                const PrintControl = L.Control.extend({
                    options: { position: 'topleft' },
                    onAdd: function() {
                        const container = L.DomUtil.create('div', 'leaflet-bar leaflet-control');
                        const button = L.DomUtil.create('a', '', container);
                        button.href = '#';
                        button.title = 'Print Map (filtered view)';
                        button.innerHTML = '<i class="fas fa-print"></i>';
                        L.DomEvent.disableClickPropagation(button);
                        L.DomEvent.on(button, 'click', function(e) {
                            L.DomEvent.preventDefault(e);
                            printMap();
                        });
                        return container;
                    }
                });
                new PrintControl().addTo(mapInstance);

                mapInstance.on('mousemove', function(e) {
                    document.getElementById('coordLat').textContent = e.latlng.lat.toFixed(5);
                    document.getElementById('coordLng').textContent = e.latlng.lng.toFixed(5);
                });
                mapInstance.on('zoomend', function() {
                    document.getElementById('coordZoom').textContent = mapInstance.getZoom();
                });

                featureLayer = L.featureGroup().addTo(mapInstance);
                defaultLocLayer = L.featureGroup().addTo(mapInstance);
                provinceLayer = L.featureGroup().addTo(mapInstance);

                if (provinceBoundary && provinceBoundary.geometry) {
                    try {
                        L.geoJSON(provinceBoundary.geometry, {
                            style: { color: '#3388ff', weight: 2, fillOpacity: 0, opacity: 0.8 },
                            interactive: false
                        }).addTo(provinceLayer);
                    } catch (e) { console.error('Province boundary error:', e); }
                }

                /* ── Loc trigger ── */
                document.getElementById('locTrigger').addEventListener('click', function(e) {
                    e.stopPropagation();
                    document.querySelectorAll('.ms-pill.open, .metadata-pill.open').forEach(p => p.classList.remove('open'));
                    locOpen ? closeLoc() : openLoc();
                });
                document.getElementById('locDropdown').addEventListener('click', function(e) { e.stopPropagation(); });
                document.getElementById('locSearchInput').addEventListener('input', function() {
                    locSearchQ = this.value.trim().toLowerCase();
                    renderLocLists();
                });
                document.getElementById('locTabBar').addEventListener('click', function(e) {
                    const tab = e.target.closest('.loc-tab');
                    if (!tab) return;
                    activeLocTab = tab.dataset.tab;
                    document.querySelectorAll('.loc-tab').forEach(t => t.classList.toggle('active', t.dataset.tab === activeLocTab));
                    ['district','municity','brgy'].forEach(function(t) {
                        document.getElementById('panel' + t.charAt(0).toUpperCase() + t.slice(1)).style.display = (t === activeLocTab) ? '' : 'none';
                    });
                });
                ['District','Municity','Brgy'].forEach(function(cap) {
                    document.getElementById('panel' + cap).addEventListener('click', function(e) {
                        const item = e.target.closest('.loc-item');
                        if (!item) return;
                        toggleLocItem(cap.toLowerCase(), item.dataset.value);
                    });
                });
                document.getElementById('locChips').addEventListener('click', function(e) {
                    const x = e.target.closest('.loc-chip-x');
                    if (x) toggleLocItem(x.dataset.type, x.dataset.value);
                });
                document.getElementById('locClearBtn').addEventListener('click', clearLocFilter);

                /* ── Meta chips click ── */
                document.getElementById('metaChips').addEventListener('click', function(e) {
                    const x = e.target.closest('.meta-chip-x');
                    if (x) removeMetadataFilter(parseInt(x.dataset.idx));
                });
                document.getElementById('metaClearBtn').addEventListener('click', clearMetadataFilters);
                document.getElementById('metadataLocDropdown').addEventListener('click', function(e) { e.stopPropagation(); });

                // Close custom dropdown when clicking outside
                document.addEventListener('click', function(e) {
                    if (!e.target.closest('#metaKeyCustomSelect')) {
                        closeMetaKeyDropdown();
                    }
                });

                /* ── Date preset click ── */
                document.getElementById('datePresetList').addEventListener('click', function(e) {
                    const item = e.target.closest('.ms-date-preset-item');
                    if (!item || !item.dataset.date) return;
                    onDatePresetClick(item.dataset.date);
                });

                /* ── Global click close ── */
                document.addEventListener('click', function(e) {
                    if (!e.target.closest('.ms-pill') && !e.target.closest('.metadata-pill')) {
                        document.querySelectorAll('.ms-pill.open, .metadata-pill.open').forEach(p => p.classList.remove('open'));
                    }
                    if (locOpen && !e.target.closest('#locWrapper')) closeLoc();
                });

                /* ── Metadata modal ── */
                document.addEventListener('click', function(e) {
                    const btn = e.target.closest('.view-meta');
                    if (!btn) return;
                    const item = shapefiles.find(s => s.feature_id == btn.dataset.id);
                    if (!item) return;
                    document.getElementById('modalCategory').textContent = item.category;
                    document.getElementById('metadataCount').textContent = `${item.metadata.length} metadata items`;
                    document.getElementById('modalTimestamp').textContent = new Date().toLocaleString();
                    let html = '';
                    if (!item.metadata.length) {
                        html = `<div class="text-center py-5"><i class="fas fa-database fa-3x mb-3" style="color:#b71c1c;"></i><h6 class="text-muted">No metadata available</h6><p class="small text-muted mt-2">This shapefile doesn't have any metadata attached.</p></div>`;
                    } else {
                        html = '<div class="row g-3">';
                        item.metadata.forEach(function(m, i) {
                            html += `<div class="col-md-6"><div class="metadata-item"><div class="d-flex justify-content-between align-items-start mb-2"><span class="fw-bold">${esc(m.meta_key)}</span><span class="badge bg-light text-dark small">#${i+1}</span></div><div class="text-muted" style="word-break:break-word;line-height:1.6;">${m.meta_value?esc(m.meta_value):'<span class="text-muted fst-italic">Not specified</span>'}</div></div></div>`;
                        });
                        html += `</div><div class="mt-4 p-3 rounded-3" style="background-color:rgba(183,28,28,.05);"><div class="row"><div class="col-md-6 small"><i class="fas fa-layer-group me-1" style="color:#b71c1c;"></i><strong style="color:#b71c1c;">Total Items:</strong> ${item.metadata.length}</div><div class="col-md-6 text-md-end small"><i class="fas fa-tag me-1" style="color:#b71c1c;"></i><strong style="color:#b71c1c;">Category:</strong> ${esc(item.category)}</div></div></div>`;
                    }
                    document.getElementById('metadataModalBody').innerHTML = html;
                    new bootstrap.Modal(document.getElementById('metadataModal'), { backdrop: 'static' }).show();
                });

                renderLocLists();
                updateDynamicData();
                renderMap();

                /* ── Expose globals ── */
                window.renderMap = renderMap;
                window.toggleAdvSearch = toggleAdvSearch;
                window.togglePill = togglePill;
                window.toggleMetadataPill = toggleMetadataPill;
                window.selectAllCat = selectAllCat;
                window.toggleCat = toggleCat;
                window.selectAllCls = selectAllCls;
                window.toggleCls = toggleCls;
                window.onDateChange = onDateChange;
                window.onQuickSearch = onQuickSearch;
                window.addMetadataFilter = addMetadataFilter;
                window.removeMetadataFilter = removeMetadataFilter;
                window.clearMetadataFilters = clearMetadataFilters;
                window.clearFilters = clearFilters;
                window.toggleAnalysis = toggleAnalysis;
                window.printMap = printMap;
                window.dismissHint = dismissHint;
                window.onMapFixChange = onMapFixChange;
            });
        </script>
    @endpush
@endsection