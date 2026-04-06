@extends('layouts.homepage')

@section('page_title', $page['pageTitle'])

@section('content')

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.fullscreen@1.6.0/Control.FullScreen.css" />
    <link
        href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;600&family=Syne:wght@500;700;800&family=DM+Sans:wght@400;500;600&display=swap"
        rel="stylesheet">

    @push('styles')
        <style>
            /* ═══════════════════════════════════════════════
                               DESIGN TOKENS
                            ═══════════════════════════════════════════════ */
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
                --border-hi: rgba(183, 28, 28, .3);

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

            *,
            *::before,
            *::after {
                box-sizing: border-box;
                margin: 0;
                padding: 0;
            }

            body {
                font-family: var(--font);
                background: var(--bg);
                color: var(--text);
            }

            /* ── LAYOUT ── */
/* ── LAYOUT — force map to escape all containers ── */
body, html {
    overflow: hidden !important;
    height: 100% !important;
}

/* Hide the footer on the map page */
.gis-footer {
    display: none !important;
}

/* Make main a neutral full-height container */
main.gis-main {
    max-width: 100% !important;
    padding: 0 !important;
    margin: 0 !important;
    animation: none !important;
    flex: 1;
    position: relative;
    overflow: hidden;
}

#map-root {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 10;
}

body.sidebar-collapsed #map-root {
    left: 0 !important;
    width: 100% !important;
}

#map {
    width: 100%;
    height: 100%;
    background: #dde2e8;
}

            /* ═══════════════════════════════════════════════
                               TOP BAR — Search + Advanced Search
                            ═══════════════════════════════════════════════ */
            #filter-bar {
                position: absolute;
                top: 14px;
                left: 50%;
                transform: translateX(-50%);
                z-index: 800;
                display: flex;
                align-items: center;
                gap: 8px;
                background: rgba(255, 255, 255, .97);
                border: 1px solid var(--border);
                border-radius: 50px;
                padding: 6px 8px 6px 14px;
                backdrop-filter: blur(14px);
                -webkit-backdrop-filter: blur(14px);
                box-shadow: var(--shadow-md), 0 0 0 1px rgba(183, 28, 28, .05);
                white-space: nowrap;
                max-width: calc(100vw - 340px);
                flex-wrap: nowrap;
            }

            /* ── Quick Search ── */
            .fb-search-wrap {
                display: flex;
                align-items: center;
                gap: 7px;
                background: var(--panel2);
                border: 1px solid var(--border);
                border-radius: 30px;
                padding: 5px 12px;
                transition: border-color .2s, box-shadow .2s;
                min-width: 220px;
            }

            .fb-search-wrap:focus-within {
                border-color: var(--red);
                box-shadow: 0 0 0 3px var(--red-glow);
                background: #fff;
            }

            .fb-search-wrap i {
                color: var(--muted);
                font-size: 11px;
                flex-shrink: 0;
            }

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

            #quickSearch::placeholder {
                color: var(--muted);
            }

            /* ── Advanced Search button ── */
            #advSearchBtn {
                display: flex;
                align-items: center;
                gap: 6px;
                padding: 6px 14px;
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

            #advSearchBtn i {
                font-size: 10px;
            }

            /* separator + dynamic controls — hidden by default */
            .fb-sep {
                width: 1px;
                height: 20px;
                background: var(--border);
                flex-shrink: 0;
                display: none;
            }

            .fb-sep.visible {
                display: block;
            }

            .fb-dynamic {
                display: none;
                align-items: center;
                gap: 8px;
            }

            .fb-dynamic.visible {
                display: flex;
            }

            /* ── Multi-Select Pill ── */
            .ms-pill {
                position: relative;
            }

            .ms-trigger {
                display: flex;
                align-items: center;
                gap: 6px;
                padding: 5px 12px;
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
            }

            .ms-trigger:hover,
            .ms-pill.open .ms-trigger {
                border-color: var(--red);
                background: var(--red-light);
                color: var(--red);
                box-shadow: 0 0 0 3px rgba(183, 28, 28, .07);
            }

            .ms-trigger i {
                color: var(--red);
                font-size: 10px;
            }

            .ms-badge {
                background: var(--red);
                color: #fff;
                border-radius: 10px;
                font-size: 10px;
                font-weight: 700;
                padding: 1px 6px;
                min-width: 18px;
                text-align: center;
            }

            .ms-arrow {
                font-size: 8px;
                color: var(--muted);
                transition: transform .2s;
            }

            .ms-pill.open .ms-arrow {
                transform: rotate(180deg);
            }

            .ms-dropdown {
                display: none;
                position: absolute;
                top: calc(100% + 8px);
                left: 50%;
                transform: translateX(-50%);
                min-width: 210px;
                background: var(--surface);
                border: 1px solid var(--border);
                border-radius: 12px;
                padding: 6px;
                z-index: 2000;
                box-shadow: var(--shadow-lg);
                flex-direction: column;
                gap: 2px;
                max-height: 280px;
                overflow-y: auto;
            }

            .ms-dropdown::-webkit-scrollbar {
                width: 4px;
            }

            .ms-dropdown::-webkit-scrollbar-thumb {
                background: #dde2ea;
                border-radius: 4px;
            }

            .ms-pill.open .ms-dropdown {
                display: flex;
            }

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
            }

            .ms-opt:hover {
                background: var(--panel2);
                color: var(--text);
            }

            .ms-opt.selected {
                background: var(--red-light);
                color: var(--red);
            }

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

            .ms-opt.selected .ms-check {
                background: var(--red);
                border-color: var(--red);
            }

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

            .ms-opt.selected .ms-check::after {
                display: block;
            }

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

            .ms-all:hover {
                background: var(--red-light);
            }

            /* ── Date range inputs in pill dropdown ── */
            .ms-date-row {
                display: flex;
                flex-direction: column;
                gap: 5px;
                padding: 8px 10px;
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

            .ms-date-row input[type="date"]:focus {
                border-color: var(--red);
            }

            /* ── Location search ── */
            .ms-location-row {
                padding: 8px 10px;
            }

            .ms-location-row input[type="text"] {
                border: 1px solid var(--border);
                border-radius: 8px;
                padding: 5px 9px;
                font-size: 12px;
                font-family: var(--font);
                color: var(--text);
                outline: none;
                width: 100%;
                transition: border-color .2s;
            }

            .ms-location-row input[type="text"]:focus {
                border-color: var(--red);
            }

            /* ── Reset + Count (dynamic) ── */
            .fb-clear {
                display: flex;
                align-items: center;
                gap: 5px;
                padding: 5px 12px;
                background: transparent;
                border: 1px solid var(--border);
                border-radius: 20px;
                color: var(--muted);
                font-family: var(--font);
                font-size: 11px;
                font-weight: 600;
                cursor: pointer;
                transition: all .2s;
                letter-spacing: .3px;
                white-space: nowrap;
            }

            .fb-clear:hover {
                border-color: var(--red);
                color: var(--red);
                background: var(--red-light);
            }

            .fb-count {
                display: flex;
                align-items: center;
                gap: 6px;
                padding: 5px 14px;
                background: var(--red-light);
                border: 1px solid var(--red-border);
                border-radius: 20px;
                white-space: nowrap;
            }

            .fb-count-num {
                font-family: var(--mono);
                font-size: 13px;
                font-weight: 700;
                color: var(--red);
            }

            .fb-count-lbl {
                font-size: 10px;
                font-weight: 600;
                letter-spacing: .5px;
                text-transform: uppercase;
                color: var(--text-sub);
            }

            /* ═══════════════════════════════════════════════
                               ANALYSIS PANEL (bottom-left)
                            ═══════════════════════════════════════════════ */
            #analysis-panel {
                position: absolute;
                bottom: 44px;
                left: 14px;
                z-index: 800;
                width: 270px;
                background: rgba(255, 255, 255, .97);
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

            .ap-header:hover {
                background: var(--panel2);
            }

            .ap-header-left {
                display: flex;
                align-items: center;
                gap: 8px;
            }

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

            .ap-toggle {
                font-size: 10px;
                color: var(--muted);
                transition: transform .25s;
            }

            .ap-toggle.collapsed {
                transform: rotate(180deg);
            }

            .ap-body {
                padding: 10px;
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 6px;
                max-height: 260px;
                overflow-y: auto;
                transition: max-height .25s ease, padding .25s ease;
                background: #fff;
            }

            .ap-body::-webkit-scrollbar {
                width: 4px;
            }

            .ap-body::-webkit-scrollbar-thumb {
                background: #e2e6ea;
                border-radius: 4px;
            }

            .ap-body.hidden {
                max-height: 0;
                overflow: hidden;
                padding: 0 10px;
            }

            .ap-card {
                padding: 10px 10px 8px;
                border-radius: 9px;
                border: 1px solid rgba(0, 0, 0, .06);
                background: var(--panel2);
                transition: box-shadow .2s, transform .2s;
                position: relative;
                overflow: hidden;
            }

            .ap-card:hover {
                box-shadow: var(--shadow-sm);
                transform: translateY(-1px);
            }

            .ap-card-num {
                font-family: var(--mono);
                font-size: 22px;
                font-weight: 700;
                line-height: 1;
                margin-bottom: 5px;
            }

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

            /* ═══════════════════════════════════════════════
                               COORD BAR
                            ═══════════════════════════════════════════════ */
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
                background: rgba(255, 255, 255, .92);
                border: 1px solid var(--border);
                border-radius: 20px;
                font-family: var(--mono);
                font-size: 10px;
                color: var(--muted);
                pointer-events: none;
                backdrop-filter: blur(8px);
                box-shadow: var(--shadow-sm);
            }

            #coord-bar span {
                color: var(--text-sub);
                font-weight: 700;
            }

            .cb-sep {
                width: 1px;
                height: 12px;
                background: var(--border);
            }

            /* ═══════════════════════════════════════════════
                               GIS LEGEND
                            ═══════════════════════════════════════════════ */
            .gis-legend {
                background: rgba(255, 255, 255, .97) !important;
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

            .legend-swatch {
                width: 12px;
                height: 12px;
                border-radius: 3px;
                flex-shrink: 0;
            }

            /* ═══════════════════════════════════════════════
                               LEAFLET OVERRIDES
                            ═══════════════════════════════════════════════ */
            .leaflet-control-zoom a,
            .leaflet-control-fullscreen a {
                background: #fff !important;
                border-color: #dde2ea !important;
                color: var(--text-sub) !important;
            }

            .leaflet-control-zoom a:hover,
            .leaflet-control-fullscreen a:hover {
                background: var(--red) !important;
                color: #fff !important;
            }

            .leaflet-bar {
                border: none !important;
                box-shadow: var(--shadow-md) !important;
            }

            .leaflet-bar a {
                border-bottom-color: #eee !important;
            }

            .leaflet-control-layers {
                background: rgba(255, 255, 255, .97) !important;
                border: 1px solid var(--border) !important;
                border-radius: 10px !important;
                box-shadow: var(--shadow-md) !important;
            }

            .leaflet-control-layers label {
                color: var(--text-sub) !important;
                font-size: 12px;
            }

            .leaflet-control-scale-line {
                background: rgba(255, 255, 255, .85) !important;
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

            .leaflet-popup-content {
                margin: 0 !important;
            }

            .leaflet-popup-tip {
                background: #fff !important;
            }

            .leaflet-popup-close-button {
                color: var(--muted) !important;
                right: 8px !important;
                top: 8px !important;
                font-size: 16px !important;
            }

            .leaflet-popup-close-button:hover {
                color: var(--red) !important;
            }

            /* ═══════════════════════════════════════════════
                               POPUP
                            ═══════════════════════════════════════════════ */
            .popup-wrap {
                padding: 14px 16px;
                font-family: var(--font);
                min-width: 240px;
            }

            .popup-cat {
                font-family: var(--display);
                font-size: 10px;
                font-weight: 700;
                letter-spacing: .7px;
                text-transform: uppercase;
                color: var(--muted);
                margin-bottom: 3px;
            }

            .popup-name {
                font-size: 14px;
                font-weight: 700;
                color: var(--text);
                margin-bottom: 7px;
            }

            .popup-cls {
                display: inline-block;
                padding: 3px 10px;
                border-radius: 20px;
                font-size: 11px;
                font-weight: 600;
                color: #fff;
                margin-bottom: 10px;
            }

            .popup-divider {
                height: 1px;
                background: #f0f0f0;
                margin: 8px 0;
            }

            .popup-row {
                display: flex;
                justify-content: space-between;
                align-items: baseline;
                gap: 10px;
                padding: 4px 0;
                border-bottom: 1px solid #f5f5f5;
                font-size: 11px;
            }

            .popup-row:last-child {
                border-bottom: none;
            }

            .popup-key {
                color: var(--muted);
                font-weight: 500;
            }

            .popup-val {
                color: var(--text);
                font-weight: 600;
                text-align: right;
                max-width: 140px;
                word-break: break-word;
            }

            .popup-more {
                margin-top: 10px;
                text-align: center;
            }

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

            .popup-more-btn:hover {
                background: var(--red-dark);
                box-shadow: 0 4px 12px rgba(183, 28, 28, .25);
            }

            .popup-empty {
                text-align: center;
                padding: 8px 0;
                color: var(--muted);
                font-size: 11px;
            }

            /* ═══════════════════════════════════════════════
                               MODAL
                            ═══════════════════════════════════════════════ */
            .modal {
                z-index: 3000 !important;
            }

            .modal-content {
                border-radius: 14px !important;
                overflow: hidden;
            }

            .modal-header {
                background-color: #b71c1c !important;
                border-bottom: none !important;
            }

            .sticky-top {
                background: #f8f9fa !important;
            }

            .metadata-item {
                background: #f8f9fa;
                border-radius: 8px;
                padding: 12px;
                margin-bottom: 8px;
                border-left: 4px solid var(--red);
                transition: all .2s;
                font-size: 13px;
            }

            .metadata-item:hover {
                background: #f0f2f5;
                transform: translateX(3px);
                box-shadow: var(--shadow-sm);
            }

            /* ── No-results toast ── */
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
        </style>
    @endpush

    <!-- FULL-SCREEN MAP -->
    <div id="map-root">
        <div id="map"></div>

        <!-- ══════════════════════════════════
                     TOP FILTER BAR
                     — Only Search + Adv Search shown initially
                     — Filters, Reset, Count appear after Adv Search click
                ══════════════════════════════════ -->
        <div id="filter-bar">

            <!-- Quick search box -->
            <div class="fb-search-wrap">
                <i class="fas fa-search"></i>
                <input type="text" id="quickSearch" placeholder="Search features… (description, location, category…)"
                    oninput="onQuickSearch()" />
            </div>

            <!-- Advanced Search toggle button -->
            <button id="advSearchBtn" onclick="toggleAdvSearch()">
                <i class="fas fa-sliders-h"></i>
                Advanced Search
                <i class="fas fa-chevron-down" id="advChevron" style="font-size:8px;"></i>
            </button>

            <!-- ── Everything below is hidden until Adv Search is activated ── -->

            <div class="fb-sep" id="sepCat"></div>

            <!-- Category Filter (dynamic) -->
            <div class="fb-dynamic" id="dynCat">
                <div class="ms-pill" id="catPill">
                    <div class="ms-trigger" onclick="togglePill('catPill')">
                        <i class="fas fa-layer-group"></i>
                        <span id="catLabel">Category</span>
                        <span class="ms-badge" id="catBadge" style="display:none">0</span>
                        <span class="ms-arrow">▼</span>
                    </div>
                    <div class="ms-dropdown" id="catDropdown">
                        <div class="ms-opt ms-all" onclick="selectAllCat()">
                            <div class="ms-check" id="catAllChk"></div>
                            Select All
                        </div>
                        @foreach ($categories as $cat)
                            <div class="ms-opt" data-val="{{ $cat->name }}"
                                onclick="toggleCat('{{ $cat->name }}', this)">
                                <div class="ms-check"></div>
                                <span>{{ ucfirst($cat->name) }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="fb-sep" id="sepCls"></div>

            <!-- Classification Filter (dynamic) -->
            <div class="fb-dynamic" id="dynCls">
                <div class="ms-pill" id="clsPill">
                    <div class="ms-trigger" onclick="togglePill('clsPill')">
                        <i class="fas fa-tags"></i>
                        <span id="clsLabel">Classification</span>
                        <span class="ms-badge" id="clsBadge" style="display:none">0</span>
                        <span class="ms-arrow">▼</span>
                    </div>
                    <div class="ms-dropdown" id="clsDropdown">
                        <div class="ms-opt ms-all" onclick="selectAllCls()">
                            <div class="ms-check" id="clsAllChk"></div>
                            Select All
                        </div>
                        @foreach ($classifications as $c)
                            <div class="ms-opt" data-val="{{ $c->id }}"
                                onclick="toggleCls('{{ $c->id }}', this)">
                                <div class="ms-check"></div>
                                <span>{{ $c->name }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="fb-sep" id="sepDate"></div>

            <!-- Date Collected Filter (dynamic) -->
            <div class="fb-dynamic" id="dynDate">
                <div class="ms-pill" id="datePill">
                    <div class="ms-trigger" onclick="togglePill('datePill')">
                        <i class="fas fa-calendar-alt"></i>
                        <span id="dateLabel">Date Collected</span>
                        <span class="ms-badge" id="dateBadge" style="display:none">●</span>
                        <span class="ms-arrow">▼</span>
                    </div>
                    <div class="ms-dropdown" id="dateDropdown" style="min-width:230px;">
                        <div class="ms-date-row">
                            <label>From</label>
                            <input type="date" id="dateFrom" onchange="onDateChange()" />
                        </div>
                        <div class="ms-date-row">
                            <label>To</label>
                            <input type="date" id="dateTo" onchange="onDateChange()" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="fb-sep" id="sepLoc"></div>

            <!-- Location Filter (dynamic) -->
            <div class="fb-dynamic" id="dynLoc">
                <div class="ms-pill" id="locPill">
                    <div class="ms-trigger" onclick="togglePill('locPill')">
                        <i class="fas fa-map-pin"></i>
                        <span id="locLabel">Location</span>
                        <span class="ms-badge" id="locBadge" style="display:none">●</span>
                        <span class="ms-arrow">▼</span>
                    </div>
                    <div class="ms-dropdown" id="locDropdown" style="min-width:230px;">
                        <div class="ms-location-row">
                            <input type="text" id="locationSearch" placeholder="Filter by location…"
                                oninput="onLocationInput()" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="fb-sep" id="sepReset"></div>

            <!-- Reset (dynamic) -->
            <div class="fb-dynamic" id="dynReset">
                <button class="fb-clear" onclick="clearFilters()">
                    <i class="fas fa-times" style="font-size:9px;"></i> Reset
                </button>
            </div>

            <!-- Feature Count (dynamic) -->
            <div class="fb-dynamic" id="dynCount">
                <div class="fb-count">
                    <span class="fb-count-num" id="featureCount">0</span>
                    <span class="fb-count-lbl">Features</span>
                </div>
            </div>

        </div><!-- /filter-bar -->

        <!-- No-results toast -->
        <div id="no-results-toast">
            <i class="fas fa-exclamation-triangle me-1"></i>
            No features match the current filters.
        </div>

        <!-- ANALYSIS PANEL (bottom-left) -->
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
                    <div class="ap-card">
                        <div
                            style="position:absolute; left:0; top:0; bottom:0; width:3px;
                             background:{{ $cat['color'] }}; border-radius:9px 0 0 9px;">
                        </div>
                        <div class="ap-card-num" style="color:{{ $cat['color'] }}">{{ $cat['count'] }}</div>
                        <div class="ap-card-name">{{ str_replace('_', ' ', $cat['name']) }}</div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- COORD BAR (bottom-center) -->
        <div id="coord-bar">
            <span style="color:var(--muted)">LAT</span>&nbsp;<span id="coordLat">—</span>
            <div class="cb-sep"></div>
            <span style="color:var(--muted)">LNG</span>&nbsp;<span id="coordLng">—</span>
            <div class="cb-sep"></div>
            <span style="color:var(--muted)">ZOOM</span>&nbsp;<span id="coordZoom">10</span>
        </div>

    </div><!-- /map-root -->

    <!-- METADATA MODAL -->
    <div class="modal fade" id="metadataModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header text-white">
                    <h5 class="modal-title fw-bold">
                        <i class="fas fa-database me-2"></i>Shapefile Metadata
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="sticky-top p-4 border-bottom">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-2">
                                    <span class="badge fs-6" id="modalCategory"
                                        style="background-color:rgba(183,28,28,.1); color:#b71c1c;"></span>
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
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet.fullscreen@1.6.0/Control.FullScreen.js"></script>

    @push('scripts')
        <script>
            /* ── DATA FROM SERVER ── */
            const shapefiles = @json($geojson);
            const categories = @json($categories);
            const classifications = @json($classifications);

            /* ── STATE ── */
            let selCats = new Set();
            let selCls = new Set();
            let advOpen = false;
            let apOpen = true;
            let quickSearchQ = '';
            let dateFrom = '';
            let dateTo = '';
            let locFilter = '';

            /* ═══════════════════════════════════════════
               ADVANCED SEARCH TOGGLE
               Shows/hides all filter pills, reset, count
            ═══════════════════════════════════════════ */
            function toggleAdvSearch() {
                advOpen = !advOpen;
                const btn = document.getElementById('advSearchBtn');
                const chevron = document.getElementById('advChevron');
                const dynamics = document.querySelectorAll('.fb-dynamic');
                const seps = document.querySelectorAll('.fb-sep');

                if (advOpen) {
                    btn.classList.add('active');
                    chevron.style.transform = 'rotate(180deg)';
                    dynamics.forEach(el => el.classList.add('visible'));
                    seps.forEach(el => el.classList.add('visible'));
                } else {
                    btn.classList.remove('active');
                    chevron.style.transform = '';
                    dynamics.forEach(el => el.classList.remove('visible'));
                    seps.forEach(el => el.classList.remove('visible'));
                }
                renderMap();
            }

            /* ═══════════════════════════════════════════
               PILL TOGGLE
            ═══════════════════════════════════════════ */
            function togglePill(id) {
                const pill = document.getElementById(id);
                const wasOpen = pill.classList.contains('open');
                document.querySelectorAll('.ms-pill.open').forEach(p => p.classList.remove('open'));
                if (!wasOpen) pill.classList.add('open');
            }
            document.addEventListener('click', e => {
                if (!e.target.closest('.ms-pill')) {
                    document.querySelectorAll('.ms-pill.open').forEach(p => p.classList.remove('open'));
                }
            });

            /* ═══════════════════════════════════════════
               QUICK SEARCH  (description, location,
               category, classification, date)
            ═══════════════════════════════════════════ */
            function onQuickSearch() {
                quickSearchQ = document.getElementById('quickSearch').value.trim().toLowerCase();
                renderMap();
            }

            /* ═══════════════════════════════════════════
               CATEGORY
            ═══════════════════════════════════════════ */
            function toggleCat(val, el) {
                selCats.has(val) ?
                    (selCats.delete(val), el.classList.remove('selected')) :
                    (selCats.add(val), el.classList.add('selected'));
                syncCatLabel();
                syncClsOptions();
                renderMap();
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
                renderMap();
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

            /* ═══════════════════════════════════════════
               CLASSIFICATION
            ═══════════════════════════════════════════ */
            function toggleCls(val, el) {
                val = String(val);
                selCls.has(val) ?
                    (selCls.delete(val), el.classList.remove('selected')) :
                    (selCls.add(val), el.classList.add('selected'));
                syncClsLabel();
                renderMap();
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
                renderMap();
            }

            function syncClsLabel() {
                const lbl = document.getElementById('clsLabel');
                const bdg = document.getElementById('clsBadge');
                if (!selCls.size || selCls.size === classifications.length) {
                    lbl.textContent = 'Classification';
                    bdg.style.display = 'none';
                } else {
                    const found = classifications.find(c => selCls.has(String(c.id)));
                    lbl.textContent = selCls.size === 1 && found ? found.name : 'Classification';
                    bdg.textContent = selCls.size;
                    bdg.style.display = 'inline-block';
                }
            }

            function syncClsOptions() {
                document.querySelectorAll('#clsDropdown .ms-opt:not(.ms-all)').forEach(o => {
                    const cid = parseInt(o.dataset.val);
                    const show = !selCats.size ||
                        shapefiles.some(s => selCats.has(s.category) && s.classification_id === cid);
                    o.style.display = show ? '' : 'none';
                    if (!show) {
                        selCls.delete(String(cid));
                        o.classList.remove('selected');
                    }
                });
                syncClsLabel();
            }

            /* ═══════════════════════════════════════════
               DATE FILTER
            ═══════════════════════════════════════════ */
            function onDateChange() {
                dateFrom = document.getElementById('dateFrom').value;
                dateTo = document.getElementById('dateTo').value;
                const bdg = document.getElementById('dateBadge');
                bdg.style.display = (dateFrom || dateTo) ? 'inline-block' : 'none';
                renderMap();
            }

            /* ═══════════════════════════════════════════
               LOCATION FILTER
            ═══════════════════════════════════════════ */
            function onLocationInput() {
                locFilter = document.getElementById('locationSearch').value.trim().toLowerCase();
                const bdg = document.getElementById('locBadge');
                bdg.style.display = locFilter ? 'inline-block' : 'none';
                renderMap();
            }

            /* ═══════════════════════════════════════════
               RESET ALL
            ═══════════════════════════════════════════ */
            function clearFilters() {
                selCats.clear();
                selCls.clear();
                document.querySelectorAll('.ms-opt').forEach(o => o.classList.remove('selected'));
                document.getElementById('quickSearch').value = '';
                document.getElementById('dateFrom').value = '';
                document.getElementById('dateTo').value = '';
                document.getElementById('locationSearch').value = '';
                document.getElementById('dateBadge').style.display = 'none';
                document.getElementById('locBadge').style.display = 'none';
                quickSearchQ = '';
                dateFrom = '';
                dateTo = '';
                locFilter = '';
                syncCatLabel();
                syncClsOptions();
                syncClsLabel();
                renderMap();
            }

            /* ═══════════════════════════════════════════
               ANALYSIS TOGGLE
            ═══════════════════════════════════════════ */
            function toggleAnalysis() {
                apOpen = !apOpen;
                document.getElementById('apBody').classList.toggle('hidden', !apOpen);
                document.getElementById('apToggleIcon').classList.toggle('collapsed', !apOpen);
            }

            /* ═══════════════════════════════════════════
               MAP INIT
            ═══════════════════════════════════════════ */
            document.addEventListener('DOMContentLoaded', () => {

                const map = L.map('map', {
                    center: [14.28, 121.4],
                    zoom: 10,
                    zoomControl: true,
                    scrollWheelZoom: true
                });

                const osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 20,
                    attribution: '&copy; OpenStreetMap contributors'
                }).addTo(map);
                const satellite = L.tileLayer('https://{s}.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
                    maxZoom: 20,
                    subdomains: ['mt0', 'mt1', 'mt2', 'mt3']
                });
                const hybrid = L.tileLayer('https://{s}.google.com/vt/lyrs=y&x={x}&y={y}&z={z}', {
                    maxZoom: 20,
                    subdomains: ['mt0', 'mt1', 'mt2', 'mt3']
                });
                const cartoLight = L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
                    maxZoom: 20,
                    attribution: '&copy; CartoDB'
                });
                const cartoDark = L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
                    maxZoom: 20,
                    attribution: '&copy; CartoDB'
                });

                L.control.scale({
                    imperial: false,
                    position: 'bottomleft'
                }).addTo(map);
                L.control.layers({
                    "OSM": osm,
                    "Satellite": satellite,
                    "Hybrid": hybrid,
                    "Carto Light": cartoLight,
                    "Carto Dark": cartoDark
                }).addTo(map);
                L.control.fullscreen({
                    position: 'topleft',
                    title: 'Fullscreen',
                    titleCancel: 'Exit fullscreen'
                }).addTo(map);

                map.on('mousemove', e => {
                    document.getElementById('coordLat').textContent = e.latlng.lat.toFixed(5);
                    document.getElementById('coordLng').textContent = e.latlng.lng.toFixed(5);
                });
                map.on('zoomend', () => {
                    document.getElementById('coordZoom').textContent = map.getZoom();
                });

                const layerGroup = L.featureGroup().addTo(map);
                let legendCtrl = null;

                function getColor(item) {
                    return item.classification_color || '#b71c1c';
                }

                function updateLegend(shapes) {
                    if (legendCtrl) map.removeControl(legendCtrl);
                    legendCtrl = L.control({
                        position: 'bottomright'
                    });
                    legendCtrl.onAdd = function() {
                        const div = L.DomUtil.create('div', 'gis-legend');
                        const usedIds = [...new Set(shapes.map(s => s.classification_id))];
                        const used = classifications.filter(c => usedIds.includes(c.id));
                        if (!used.length) return div;
                        let html = `<div class="legend-title">Legend</div>`;
                        used.forEach(c => {
                            html += `<div class="legend-row">
                                <div class="legend-swatch" style="background:${c.color};"></div>
                                <span>${c.name}</span>
                            </div>`;
                        });
                        div.innerHTML = html;
                        return div;
                    };
                    legendCtrl.addTo(map);
                }

                /* ─────────────────────────────────
                   FILTER HELPER
                ───────────────────────────────── */
                function featureMatchesFilters(item) {
                    if (!item.geometry) return false;

                    // Category filter
                    if (selCats.size && !selCats.has(item.category)) return false;

                    // Classification filter
                    if (selCls.size && !selCls.has(String(item.classification_id))) return false;

                    // Date range filter (survey_date)
                    if (dateFrom && item.survey_date && item.survey_date < dateFrom) return false;
                    if (dateTo && item.survey_date && item.survey_date > dateTo) return false;
                    if ((dateFrom || dateTo) && !item.survey_date) return false;

                    // Location filter
                    if (locFilter && !(item.location || '').toLowerCase().includes(locFilter)) return false;

                    // Quick search: description, location, category, classification, survey_date
                    if (quickSearchQ) {
                        const haystack = [
                            item.description || '',
                            item.location || '',
                            item.category || '',
                            item.classification || '',
                            item.survey_date || '',
                        ].join(' ').toLowerCase();
                        if (!haystack.includes(quickSearchQ)) return false;
                    }

                    return true;
                }

                /* ─────────────────────────────────
                   RENDER
                ───────────────────────────────── */
                window.renderMap = function() {
                    layerGroup.clearLayers();

                    const filtered = shapefiles.filter(featureMatchesFilters);

                    // Show count only when adv search is open
                    document.getElementById('featureCount').textContent = filtered.length;

                    // No-results toast
                    const toast = document.getElementById('no-results-toast');
                    toast.style.display = (!filtered.length && (advOpen || quickSearchQ)) ? 'block' : 'none';

                    filtered.forEach(item => {
                        const color = getColor(item);
                        const style = {
                            color,
                            fillColor: color,
                            weight: 1,
                            opacity: 0.85,
                            fillOpacity: 0.18
                        };

                        L.geoJSON(item.geometry, {
                            style,
                            pointToLayer: (feature, latlng) => L.circleMarker(latlng, {
                                radius: 8,
                                fillColor: color,
                                color,
                                weight: 1,
                                opacity: 1,
                                fillOpacity: 0.8
                            }),
                            onEachFeature: (feature, layer) => {
                                const MAX = 5;
                                let rows = '';
                                let extra = 0;

                                // Core fields first
                                const coreFields = [{
                                        key: 'Description',
                                        val: item.description
                                    },
                                    {
                                        key: 'Location',
                                        val: item.location
                                    },
                                    {
                                        key: 'Date Collected',
                                        val: item.survey_date
                                    },
                                ];
                                coreFields.forEach(f => {
                                    if (f.val) {
                                        rows += `<div class="popup-row">
                                            <span class="popup-key">${f.key}</span>
                                            <span class="popup-val">${f.val}</span>
                                        </div>`;
                                    }
                                });

                                // Metadata rows (up to MAX)
                                if (item.metadata?.length) {
                                    item.metadata.slice(0, MAX).forEach(m => {
                                        rows += `<div class="popup-row">
                                            <span class="popup-key">${m.meta_key}</span>
                                            <span class="popup-val">${m.meta_value || '<em style="opacity:.4">—</em>'}</span>
                                        </div>`;
                                    });
                                    extra = item.metadata.length - MAX;
                                }

                                const popup = `
<div class="popup-wrap">
    <div class="popup-cat">${item.category}</div>
    <span class="popup-cls" style="background:${item.classification_color || '#6c757d'}">
        ${item.classification || 'No Classification'}
    </span>
    <div class="popup-divider"></div>
    ${rows || `<div class="popup-empty"><i class="fas fa-info-circle me-1"></i>No data available</div>`}
    ${extra > 0 ? `<div class="popup-more">
                        <button class="popup-more-btn view-meta" data-id="${item.feature_id}">
                            <i class="fas fa-table me-1"></i>View all ${item.metadata.length} fields
                        </button>
                    </div>` : ''}
</div>`;

                                layer.bindPopup(popup, {
                                    maxWidth: 340
                                });
                                layer.on('mouseover', () => layer.setStyle({
                                    weight: 2,
                                    fillOpacity: 0.32
                                }));
                                layer.on('mouseout', () => layer.setStyle(style));
                                layer.addTo(layerGroup);
                            }
                        });
                    });

                    if (layerGroup.getLayers().length) {
                        map.fitBounds(layerGroup.getBounds(), {
                            padding: [80, 80],
                            maxZoom: 15
                        });
                    }
                    updateLegend(filtered);
                };

                renderMap();

                /* ─────────────────────────────────
                   METADATA MODAL
                ───────────────────────────────── */
                document.addEventListener('click', e => {
                    const btn = e.target.closest('.view-meta');
                    if (!btn) return;
                    const item = shapefiles.find(s => s.feature_id == btn.dataset.id);
                    if (!item) return;

                    document.getElementById('modalCategory').textContent = item.category;
                    document.getElementById('metadataCount').textContent =
                        `${item.metadata.length} metadata items`;
                    document.getElementById('modalTimestamp').textContent = new Date().toLocaleString();

                    let html = '';
                    if (!item.metadata.length) {
                        html = `<div class="text-center py-5">
                            <i class="fas fa-database fa-3x mb-3" style="color:#b71c1c;"></i>
                            <h6 class="text-muted">No metadata available</h6>
                            <p class="small text-muted mt-2">This shapefile doesn't have any metadata attached.</p>
                        </div>`;
                    } else {
                        html = '<div class="row g-3">';
                        item.metadata.forEach((m, i) => {
                            html += `<div class="col-md-6"><div class="metadata-item">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="fw-bold">${m.meta_key}</span>
                                    <span class="badge bg-light text-dark small">#${i+1}</span>
                                </div>
                                <div class="text-muted" style="word-break:break-word;line-height:1.6;">
                                    ${m.meta_value || '<span class="text-muted fst-italic">Not specified</span>'}
                                </div>
                            </div></div>`;
                        });
                        html += '</div>';
                        html += `<div class="mt-4 p-3 rounded-3" style="background-color:rgba(183,28,28,0.05);">
                            <div class="row">
                                <div class="col-md-6 small">
                                    <i class="fas fa-layer-group me-1" style="color:#b71c1c;"></i>
                                    <strong style="color:#b71c1c;">Total Items:</strong> ${item.metadata.length}
                                </div>
                                <div class="col-md-6 text-md-end small">
                                    <i class="fas fa-tag me-1" style="color:#b71c1c;"></i>
                                    <strong style="color:#b71c1c;">Category:</strong> ${item.category}
                                </div>
                            </div>
                        </div>`;
                    }
                    document.getElementById('metadataModalBody').innerHTML = html;
                    new bootstrap.Modal(document.getElementById('metadataModal'), {
                        backdrop: 'static'
                    }).show();
                });

            }); // DOMContentLoaded
        </script>
    @endpush
@endsection
