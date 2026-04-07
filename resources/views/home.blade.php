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
                --red:        #b71c1c;
                --red-mid:    #c62828;
                --red-dark:   #7f0000;
                --red-glow:   rgba(183,28,28,.18);
                --red-border: rgba(183,28,28,.28);
                --red-light:  rgba(183,28,28,.07);

                --bg:      #eef0f4;
                --surface: #ffffff;
                --panel2:  #f6f7f9;
                --border:  rgba(0,0,0,.09);

                --text:     #0f1117;
                --text-sub: #3d4a5c;
                --muted:    #8a94a8;

                --font:    'DM Sans', sans-serif;
                --display: 'Syne', sans-serif;
                --mono:    'IBM Plex Mono', monospace;

                --shadow-sm: 0 2px 8px  rgba(0,0,0,.07);
                --shadow-md: 0 6px 24px rgba(0,0,0,.10);
                --shadow-lg: 0 14px 48px rgba(0,0,0,.14);
                --radius: 14px;
            }

            *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
            body { font-family: var(--font); background: var(--bg); color: var(--text); }

            /* ── LAYOUT ── */
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
                               FILTER BAR
               ═══════════════════════════════════════════════ */
            #filter-bar {
                position: absolute; top: 14px; left: 50%;
                transform: translateX(-50%);
                z-index: 800;
                display: flex; align-items: center; gap: 8px;
                background: rgba(255,255,255,.97);
                border: 1px solid var(--border);
                border-radius: 50px;
                padding: 6px 8px 6px 14px;
                backdrop-filter: blur(14px); -webkit-backdrop-filter: blur(14px);
                box-shadow: var(--shadow-md), 0 0 0 1px rgba(183,28,28,.05);
                white-space: nowrap;
                max-width: calc(100vw - 340px);
                flex-wrap: nowrap;
            }

            /* Quick Search */
            .fb-search-wrap {
                display: flex; align-items: center; gap: 7px;
                background: var(--panel2); border: 1px solid var(--border);
                border-radius: 30px; padding: 5px 12px;
                transition: border-color .2s, box-shadow .2s; min-width: 220px;
            }
            .fb-search-wrap:focus-within {
                border-color: var(--red); box-shadow: 0 0 0 3px var(--red-glow); background: #fff;
            }
            .fb-search-wrap i { color: var(--muted); font-size: 11px; flex-shrink: 0; }
            #quickSearch {
                border: none; outline: none; background: transparent;
                font-family: var(--font); font-size: 12.5px; font-weight: 500;
                color: var(--text); width: 100%; min-width: 0;
            }
            #quickSearch::placeholder { color: var(--muted); }

            /* Adv Search btn */
            #advSearchBtn {
                display: flex; align-items: center; gap: 6px;
                padding: 6px 14px; background: var(--red-light);
                border: 1px solid var(--red-border); border-radius: 30px;
                font-family: var(--font); font-size: 12px; font-weight: 700;
                color: var(--red); cursor: pointer; letter-spacing: .2px;
                transition: background .2s, box-shadow .2s, transform .15s;
                white-space: nowrap; flex-shrink: 0; user-select: none;
            }
            #advSearchBtn:hover {
                background: var(--red); color: #fff;
                box-shadow: 0 4px 14px rgba(183,28,28,.28); transform: translateY(-1px);
            }
            #advSearchBtn.active {
                background: var(--red); color: #fff; box-shadow: 0 4px 16px rgba(183,28,28,.32);
            }
            #advSearchBtn i { font-size: 10px; }

            .fb-sep { width: 1px; height: 20px; background: var(--border); flex-shrink: 0; display: none; }
            .fb-sep.visible { display: block; }
            .fb-dynamic { display: none; align-items: center; gap: 8px; }
            .fb-dynamic.visible { display: flex; }

            /* ── Multi-Select Pill (shared) ── */
            .ms-pill { position: relative; }
            .ms-trigger {
                display: flex; align-items: center; gap: 6px;
                padding: 5px 12px; background: var(--panel2);
                border: 1px solid var(--border); border-radius: 20px;
                cursor: pointer; font-family: var(--font);
                font-size: 12px; font-weight: 600; color: var(--text-sub);
                user-select: none; transition: border-color .2s, background .2s, box-shadow .2s;
            }
            .ms-trigger:hover,
            .ms-pill.open .ms-trigger {
                border-color: var(--red); background: var(--red-light);
                color: var(--red); box-shadow: 0 0 0 3px rgba(183,28,28,.07);
            }
            /* loc trigger uses js-controlled class instead of .ms-pill.open */
            #locTrigger.loc-open {
                border-color: var(--red); background: var(--red-light);
                color: var(--red); box-shadow: 0 0 0 3px rgba(183,28,28,.07);
            }
            .ms-trigger i { color: var(--red); font-size: 10px; }
            .ms-badge {
                background: var(--red); color: #fff; border-radius: 10px;
                font-size: 10px; font-weight: 700; padding: 1px 6px;
                min-width: 18px; text-align: center;
            }
            .ms-arrow { font-size: 8px; color: var(--muted); transition: transform .2s; }
            .ms-pill.open .ms-arrow { transform: rotate(180deg); }

            .ms-dropdown {
                display: none; position: absolute;
                top: calc(100% + 8px); left: 50%; transform: translateX(-50%);
                min-width: 210px; background: var(--surface);
                border: 1px solid var(--border); border-radius: 12px;
                padding: 6px; z-index: 2000; box-shadow: var(--shadow-lg);
                flex-direction: column; gap: 2px; max-height: 280px; overflow-y: auto;
            }
            .ms-dropdown::-webkit-scrollbar { width: 4px; }
            .ms-dropdown::-webkit-scrollbar-thumb { background: #dde2ea; border-radius: 4px; }
            .ms-pill.open .ms-dropdown { display: flex; }

            .ms-opt {
                display: flex; align-items: center; gap: 9px;
                padding: 7px 10px; border-radius: 8px; cursor: pointer;
                font-size: 12px; font-weight: 500; color: var(--text-sub);
                transition: background .15s, color .15s;
            }
            .ms-opt:hover { background: var(--panel2); color: var(--text); }
            .ms-opt.selected { background: var(--red-light); color: var(--red); }
            .ms-check {
                width: 14px; height: 14px; border: 1.5px solid #cbd2de;
                border-radius: 3px; display: grid; place-items: center;
                flex-shrink: 0; transition: all .15s;
            }
            .ms-opt.selected .ms-check { background: var(--red); border-color: var(--red); }
            .ms-check::after {
                content: ''; width: 4px; height: 7px;
                border: 2px solid #fff; border-top: none; border-left: none;
                transform: rotate(45deg) translateY(-1px); display: none;
            }
            .ms-opt.selected .ms-check::after { display: block; }
            .ms-all {
                font-size: 10px; font-weight: 700; text-transform: uppercase;
                letter-spacing: .6px; color: var(--red);
                border-bottom: 1px solid var(--border); margin-bottom: 4px; padding-bottom: 4px;
            }
            .ms-all:hover { background: var(--red-light); }

            /* Date inputs */
            .ms-date-row { display: flex; flex-direction: column; gap: 5px; padding: 8px 10px; }
            .ms-date-row label {
                font-size: 10px; font-weight: 700; text-transform: uppercase;
                letter-spacing: .5px; color: var(--muted);
            }
            .ms-date-row input[type="date"] {
                border: 1px solid var(--border); border-radius: 8px;
                padding: 5px 9px; font-size: 12px; font-family: var(--mono);
                color: var(--text); outline: none; width: 100%; transition: border-color .2s;
            }
            .ms-date-row input[type="date"]:focus { border-color: var(--red); }

            /* Reset / Count */
            .fb-clear {
                display: flex; align-items: center; gap: 5px;
                padding: 5px 12px; background: transparent;
                border: 1px solid var(--border); border-radius: 20px;
                color: var(--muted); font-family: var(--font);
                font-size: 11px; font-weight: 600; cursor: pointer;
                transition: all .2s; letter-spacing: .3px; white-space: nowrap;
            }
            .fb-clear:hover { border-color: var(--red); color: var(--red); background: var(--red-light); }
            .fb-count {
                display: flex; align-items: center; gap: 6px;
                padding: 5px 14px; background: var(--red-light);
                border: 1px solid var(--red-border); border-radius: 20px; white-space: nowrap;
            }
            .fb-count-num { font-family: var(--mono); font-size: 13px; font-weight: 700; color: var(--red); }
            .fb-count-lbl {
                font-size: 10px; font-weight: 600; letter-spacing: .5px;
                text-transform: uppercase; color: var(--text-sub);
            }

            /* ═══════════════════════════════════════════════
               LOCATION FILTER — Hierarchical GIS Dropdown
               ═══════════════════════════════════════════════ */
            #locDropdown {
                position: absolute;
                top: calc(100% + 8px);
                left: 50%; transform: translateX(-50%);
                width: 320px;
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

            /* Header */
            .loc-hdr {
                padding: 12px 14px 10px; background: var(--panel2);
                border-bottom: 1px solid var(--border);
            }
            .loc-hdr-title {
                font-family: var(--display); font-size: 10px; font-weight: 700;
                letter-spacing: .8px; text-transform: uppercase; color: var(--red);
                margin-bottom: 8px; display: flex; align-items: center; gap: 6px;
            }
            .loc-search-wrap {
                display: flex; align-items: center; gap: 7px;
                background: #fff; border: 1px solid var(--border);
                border-radius: 8px; padding: 5px 10px;
                transition: border-color .2s, box-shadow .2s;
            }
            .loc-search-wrap:focus-within {
                border-color: var(--red); box-shadow: 0 0 0 2px var(--red-glow);
            }
            .loc-search-wrap i { color: var(--muted); font-size: 10px; flex-shrink: 0; }
            #locSearchInput {
                border: none; outline: none; background: transparent;
                font-family: var(--font); font-size: 12px; font-weight: 500;
                color: var(--text); width: 100%;
            }
            #locSearchInput::placeholder { color: var(--muted); }

            /* Chips */
            .loc-chips {
                display: flex; align-items: center; gap: 5px; flex-wrap: wrap;
                padding: 7px 12px; background: #fff;
                border-bottom: 1px solid var(--border); min-height: 38px;
            }
            .loc-chips-empty { font-size: 11px; color: var(--muted); font-style: italic; }
            .loc-chip {
                display: inline-flex; align-items: center; gap: 4px;
                background: var(--red-light); border: 1px solid var(--red-border);
                border-radius: 20px; padding: 2px 8px 2px 7px;
                font-size: 11px; font-weight: 600; color: var(--red);
            }
            .loc-chip-lbl {
                font-size: 9px; font-weight: 700; text-transform: uppercase;
                letter-spacing: .4px; color: var(--muted); margin-right: 1px;
            }
            .loc-chip-x {
                cursor: pointer; font-size: 10px; color: var(--red);
                opacity: .65; transition: opacity .15s; line-height: 1; padding: 0 1px;
            }
            .loc-chip-x:hover { opacity: 1; }

            /* Tabs */
            .loc-tabs { display: flex; border-bottom: 1px solid var(--border); background: var(--panel2); }
            .loc-tab {
                flex: 1; padding: 8px 4px; text-align: center;
                font-size: 10px; font-weight: 700; letter-spacing: .4px;
                text-transform: uppercase; color: var(--muted);
                cursor: pointer; border-bottom: 2px solid transparent;
                transition: color .2s, border-color .2s, background .2s; user-select: none;
            }
            .loc-tab:hover { color: var(--text-sub); background: rgba(0,0,0,.03); }
            .loc-tab.active { color: var(--red); border-bottom-color: var(--red); background: #fff; }
            .loc-tab-cnt {
                display: inline-block; border-radius: 8px;
                font-size: 9px; font-weight: 700;
                padding: 0 5px; margin-left: 4px; min-width: 16px;
                text-align: center; line-height: 14px; vertical-align: middle;
                background: var(--muted); color: #fff;
            }
            .loc-tab.active .loc-tab-cnt { background: var(--red); }

            /* List */
            .loc-list { overflow-y: auto; max-height: 200px; }
            .loc-list::-webkit-scrollbar { width: 4px; }
            .loc-list::-webkit-scrollbar-thumb { background: #dde2ea; border-radius: 4px; }
            .loc-panel { padding: 4px; }

            .loc-item {
                display: flex; align-items: center; gap: 9px;
                padding: 7px 10px; border-radius: 8px;
                cursor: pointer; font-size: 12px; font-weight: 500;
                color: var(--text-sub); transition: background .15s, color .15s; user-select: none;
            }
            .loc-item:hover { background: var(--panel2); color: var(--text); }
            .loc-item.active { background: var(--red-light); color: var(--red); font-weight: 600; }
            .loc-item-ico {
                width: 22px; height: 22px; border-radius: 6px;
                display: grid; place-items: center; font-size: 9px; flex-shrink: 0;
                background: rgba(0,0,0,.04); color: var(--muted); transition: background .15s, color .15s;
            }
            .loc-item.active .loc-item-ico { background: rgba(183,28,28,.12); color: var(--red); }
            .loc-item-txt { flex: 1; min-width: 0; }
            .loc-item-name { display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
            .loc-item-sub  { display: block; font-size: 10px; color: var(--muted); margin-top: 1px; }
            .loc-item-chk {
                width: 14px; height: 14px; border: 1.5px solid #cbd2de;
                border-radius: 3px; display: grid; place-items: center;
                flex-shrink: 0; transition: all .15s;
            }
            .loc-item.active .loc-item-chk { background: var(--red); border-color: var(--red); }
            .loc-item-chk::after {
                content: ''; width: 4px; height: 7px;
                border: 2px solid #fff; border-top: none; border-left: none;
                transform: rotate(45deg) translateY(-1px); display: none;
            }
            .loc-item.active .loc-item-chk::after { display: block; }
            .loc-empty {
                text-align: center; padding: 18px 14px;
                color: var(--muted); font-size: 11px;
            }
            .loc-empty i { display: block; font-size: 18px; margin-bottom: 5px; opacity: .35; }

            /* Footer */
            .loc-footer {
                padding: 8px 14px; background: var(--panel2);
                border-top: 1px solid var(--border);
                display: flex; align-items: center; justify-content: space-between;
            }
            .loc-footer-info { font-size: 10px; color: var(--muted); font-style: italic; }
            .loc-clear-btn {
                font-size: 10px; font-weight: 700; color: var(--red); cursor: pointer;
                text-transform: uppercase; letter-spacing: .4px;
                padding: 3px 10px; border-radius: 6px;
                border: 1px solid var(--red-border); background: transparent;
                font-family: var(--font); transition: background .15s;
            }
            .loc-clear-btn:hover { background: var(--red-light); }

            /* ═══════════════════════════════════════════════
                               ANALYSIS PANEL
               ═══════════════════════════════════════════════ */
            #analysis-panel {
                position: absolute; bottom: 44px; left: 14px; z-index: 800; width: 270px;
                background: rgba(255,255,255,.97); border: 1px solid var(--border);
                border-radius: var(--radius); backdrop-filter: blur(12px);
                -webkit-backdrop-filter: blur(12px); box-shadow: var(--shadow-md); overflow: hidden;
            }
            .ap-header {
                display: flex; align-items: center; justify-content: space-between;
                padding: 10px 14px; border-bottom: 1px solid var(--border);
                cursor: pointer; user-select: none; background: #fff;
            }
            .ap-header:hover { background: var(--panel2); }
            .ap-header-left { display: flex; align-items: center; gap: 8px; }
            .ap-icon {
                width: 26px; height: 26px; background: var(--red-light);
                border: 1px solid var(--red-border); border-radius: 7px;
                display: grid; place-items: center; font-size: 11px; color: var(--red); flex-shrink: 0;
            }
            .ap-title {
                font-family: var(--display); font-size: 11px; font-weight: 700;
                letter-spacing: .5px; text-transform: uppercase; color: var(--text);
            }
            .ap-toggle { font-size: 10px; color: var(--muted); transition: transform .25s; }
            .ap-toggle.collapsed { transform: rotate(180deg); }
            .ap-body {
                padding: 10px; display: grid; grid-template-columns: 1fr 1fr; gap: 6px;
                max-height: 260px; overflow-y: auto;
                transition: max-height .25s ease, padding .25s ease; background: #fff;
            }
            .ap-body::-webkit-scrollbar { width: 4px; }
            .ap-body::-webkit-scrollbar-thumb { background: #e2e6ea; border-radius: 4px; }
            .ap-body.hidden { max-height: 0; overflow: hidden; padding: 0 10px; }
            .ap-card {
                padding: 10px 10px 8px; border-radius: 9px;
                border: 1px solid rgba(0,0,0,.06); background: var(--panel2);
                transition: box-shadow .2s, transform .2s; position: relative; overflow: hidden;
            }
            .ap-card:hover { box-shadow: var(--shadow-sm); transform: translateY(-1px); }
            .ap-card-num {
                font-family: var(--mono); font-size: 22px; font-weight: 700;
                line-height: 1; margin-bottom: 5px;
            }
            .ap-card-name {
                font-size: 10px; font-weight: 600; letter-spacing: .4px;
                text-transform: uppercase; color: var(--text-sub);
                white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
            }

            /* ═══════════════════════════════════════════════
                               COORD BAR
               ═══════════════════════════════════════════════ */
            #coord-bar {
                position: absolute; bottom: 10px; left: 50%;
                transform: translateX(-50%); z-index: 700;
                display: flex; align-items: center; gap: 10px; padding: 4px 14px;
                background: rgba(255,255,255,.92); border: 1px solid var(--border);
                border-radius: 20px; font-family: var(--mono); font-size: 10px;
                color: var(--muted); pointer-events: none;
                backdrop-filter: blur(8px); box-shadow: var(--shadow-sm);
            }
            #coord-bar span { color: var(--text-sub); font-weight: 700; }
            .cb-sep { width: 1px; height: 12px; background: var(--border); }

            /* ═══════════════════════════════════════════════
                               GIS LEGEND
               ═══════════════════════════════════════════════ */
            .gis-legend {
                background: rgba(255,255,255,.97) !important; border: 1px solid var(--border) !important;
                border-radius: 12px !important; padding: 12px 14px !important;
                backdrop-filter: blur(10px); box-shadow: var(--shadow-md) !important;
            }
            .legend-title {
                font-family: var(--display); font-size: 10px; font-weight: 700;
                letter-spacing: .8px; text-transform: uppercase; color: var(--red); margin-bottom: 8px;
            }
            .legend-row {
                display: flex; align-items: center; gap: 8px;
                font-family: var(--font); font-size: 11px; color: var(--text-sub); margin-bottom: 5px;
            }
            .legend-swatch { width: 12px; height: 12px; border-radius: 3px; flex-shrink: 0; }

            /* ═══════════════════════════════════════════════
                               LEAFLET OVERRIDES
               ═══════════════════════════════════════════════ */
            .leaflet-control-zoom a, .leaflet-control-fullscreen a {
                background: #fff !important; border-color: #dde2ea !important; color: var(--text-sub) !important;
            }
            .leaflet-control-zoom a:hover, .leaflet-control-fullscreen a:hover {
                background: var(--red) !important; color: #fff !important;
            }
            .leaflet-bar { border: none !important; box-shadow: var(--shadow-md) !important; }
            .leaflet-bar a { border-bottom-color: #eee !important; }
            .leaflet-control-layers {
                background: rgba(255,255,255,.97) !important; border: 1px solid var(--border) !important;
                border-radius: 10px !important; box-shadow: var(--shadow-md) !important;
            }
            .leaflet-control-layers label { color: var(--text-sub) !important; font-size: 12px; }
            .leaflet-control-scale-line {
                background: rgba(255,255,255,.85) !important; border-color: var(--muted) !important;
                color: var(--muted) !important; font-size: 10px !important;
            }
            .leaflet-popup-content-wrapper {
                background: #fff !important; border-radius: 12px !important;
                border-top: 3px solid var(--red) !important;
                box-shadow: var(--shadow-lg) !important; padding: 0 !important;
            }
            .leaflet-popup-content { margin: 0 !important; }
            .leaflet-popup-tip { background: #fff !important; }
            .leaflet-popup-close-button {
                color: var(--muted) !important; right: 8px !important;
                top: 8px !important; font-size: 16px !important;
            }
            .leaflet-popup-close-button:hover { color: var(--red) !important; }

            /* ═══════════════════════════════════════════════
                               POPUP
               ═══════════════════════════════════════════════ */
            .popup-wrap { padding: 14px 16px; font-family: var(--font); min-width: 240px; }
            .popup-cat {
                font-family: var(--display); font-size: 10px; font-weight: 700;
                letter-spacing: .7px; text-transform: uppercase; color: var(--muted); margin-bottom: 3px;
            }
            .popup-cls {
                display: inline-block; padding: 3px 10px; border-radius: 20px;
                font-size: 11px; font-weight: 600; color: #fff; margin-bottom: 10px;
            }
            .popup-divider { height: 1px; background: #f0f0f0; margin: 8px 0; }
            .popup-row {
                display: flex; justify-content: space-between; align-items: baseline;
                gap: 10px; padding: 4px 0; border-bottom: 1px solid #f5f5f5; font-size: 11px;
            }
            .popup-row:last-child { border-bottom: none; }
            .popup-key { color: var(--muted); font-weight: 500; }
            .popup-val { color: var(--text); font-weight: 600; text-align: right; max-width: 140px; word-break: break-word; }
            .popup-more { margin-top: 10px; text-align: center; }
            .popup-more-btn {
                background: var(--red); color: #fff; border: none; border-radius: 20px;
                padding: 5px 16px; font-size: 11px; font-weight: 700;
                font-family: var(--font); cursor: pointer; letter-spacing: .3px;
                transition: background .2s, box-shadow .2s;
            }
            .popup-more-btn:hover { background: var(--red-dark); box-shadow: 0 4px 12px rgba(183,28,28,.25); }
            .popup-empty { text-align: center; padding: 8px 0; color: var(--muted); font-size: 11px; }

            /* ── No-filter hint overlay ── */
            #no-filter-hint {
                position: absolute; top: 70px; left: 50%; transform: translateX(-50%);
                z-index: 900;
                background: rgba(255,255,255,.96); border: 1px solid var(--border);
                border-radius: 12px; padding: 10px 20px;
                font-size: 12px; font-weight: 600; color: var(--text-sub);
                display: flex; align-items: center; gap: 8px;
                box-shadow: var(--shadow-sm); pointer-events: none;
                transition: opacity .3s;
            }
            #no-filter-hint i { color: var(--red); }

            /* ═══════════════════════════════════════════════
                               MODAL
               ═══════════════════════════════════════════════ */
            .modal { z-index: 3000 !important; }
            .modal-content { border-radius: 14px !important; overflow: hidden; }
            .modal-header { background-color: #b71c1c !important; border-bottom: none !important; }
            .sticky-top { background: #f8f9fa !important; }
            .metadata-item {
                background: #f8f9fa; border-radius: 8px; padding: 12px; margin-bottom: 8px;
                border-left: 4px solid var(--red); transition: all .2s; font-size: 13px;
            }
            .metadata-item:hover { background: #f0f2f5; transform: translateX(3px); box-shadow: var(--shadow-sm); }

            #no-results-toast {
                position: absolute; top: 70px; left: 50%; transform: translateX(-50%);
                z-index: 900; background: #fff3cd; border: 1px solid #ffc107;
                border-radius: 10px; padding: 8px 18px; font-size: 12px; font-weight: 600;
                color: #856404; display: none; pointer-events: none; box-shadow: var(--shadow-sm);
            }
        </style>
    @endpush

    <div id="map-root">
        <div id="map"></div>

        <!-- ══════════════════════════════════════
                         FILTER BAR
             ══════════════════════════════════════ -->
        <div id="filter-bar">

            <!-- Quick Search -->
            <div class="fb-search-wrap">
                <i class="fas fa-search"></i>
                <input type="text" id="quickSearch"
                    placeholder="Search features, location, district, barangay…"
                    oninput="onQuickSearch()" />
            </div>

            <!-- Advanced Search Toggle -->
            <button id="advSearchBtn" onclick="toggleAdvSearch()">
                <i class="fas fa-sliders-h"></i>
                Advanced Search
                <i class="fas fa-chevron-down" id="advChevron" style="font-size:8px;"></i>
            </button>

            <!-- ── Category ── -->
            <div class="fb-sep" id="sepCat"></div>
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

            <!-- ── Classification ── -->
            <div class="fb-sep" id="sepCls"></div>
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

            <!-- ── Date Collected ── -->
            <div class="fb-sep" id="sepDate"></div>
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

            <!-- ══════════════════════════════════════════
                 LOCATION FILTER  — Hierarchical GIS pill
                 ══════════════════════════════════════════ -->
            <div class="fb-sep" id="sepLoc"></div>
            <div class="fb-dynamic" id="dynLoc">
                <div style="position:relative;" id="locWrapper">

                    <!-- Trigger button -->
                    <div class="ms-trigger" id="locTrigger" style="cursor:pointer;">
                        <i class="fas fa-map-marker-alt" style="color:var(--red);font-size:10px;"></i>
                        <span id="locLabel">Location</span>
                        <span class="ms-badge" id="locBadge" style="display:none">0</span>
                        <span class="ms-arrow" id="locArrow">▼</span>
                    </div>

                    <div id="locDropdown">

                        <!-- Header + Search -->
                        <div class="loc-hdr">
                            <div class="loc-hdr-title">
                                <i class="fas fa-map-marked-alt"></i>
                                Filter by Location
                            </div>
                            <div class="loc-search-wrap">
                                <i class="fas fa-search"></i>
                                <input type="text" id="locSearchInput"
                                       placeholder="Search district, municipality or barangay…" />
                            </div>
                        </div>

                        <!-- Active chips -->
                        <div class="loc-chips" id="locChips">
                            <span class="loc-chips-empty" id="locChipsEmpty">No location filter applied</span>
                        </div>

                        <!-- Tabs -->
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

                        <!-- Panels -->
                        <div class="loc-list">
                            <div class="loc-panel" id="panelDistrict"></div>
                            <div class="loc-panel" id="panelMunicity" style="display:none;"></div>
                            <div class="loc-panel" id="panelBrgy"     style="display:none;"></div>
                        </div>

                        <!-- Footer -->
                        <div class="loc-footer">
                            <span class="loc-footer-info" id="locFooterInfo">Select a location to filter</span>
                            <button class="loc-clear-btn" id="locClearBtn">
                                <i class="fas fa-times" style="margin-right:3px;font-size:9px;"></i>Clear
                            </button>
                        </div>

                    </div><!-- /locDropdown -->
                </div><!-- /locWrapper -->
            </div>

            <!-- ── Reset All ── -->
            <div class="fb-sep" id="sepReset"></div>
            <div class="fb-dynamic" id="dynReset">
                <button class="fb-clear" onclick="clearFilters()">
                    <i class="fas fa-times" style="font-size:9px;"></i> Reset All
                </button>
            </div>

            <!-- ── Feature Count ── -->
            <div class="fb-dynamic" id="dynCount">
                <div class="fb-count">
                    <span class="fb-count-num" id="featureCount">0</span>
                    <span class="fb-count-lbl">Features</span>
                </div>
            </div>

        </div><!-- /filter-bar -->

        <!-- Hint shown on initial load (no filter applied) -->
        <div id="no-filter-hint">
            <i class="fas fa-info-circle"></i>
            Showing boundary areas. Use <strong>&nbsp;Advanced Search&nbsp;</strong> or Quick Search to load features.
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
                        <div style="position:absolute;left:0;top:0;bottom:0;width:3px;
                             background:{{ $cat['color'] }};border-radius:9px 0 0 9px;"></div>
                        <div class="ap-card-num" id="apCount-{{ Str::slug($cat['name']) }}"
                             style="color:{{ $cat['color'] }}">0</div>
                        <div class="ap-card-name">{{ str_replace('_',' ',$cat['name']) }}</div>
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
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
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
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet.fullscreen@1.6.0/Control.FullScreen.js"></script>

    @push('scripts')
    <script>
    /* ═══════════════════════════════════════════════════════════
       SERVER DATA
       NOTE: Only PUBLIC spatial data is sent from the controller
       (visibility = 'public' filter applied in the query).
       All shapefiles displayed are public.
    ═══════════════════════════════════════════════════════════ */
    const shapefiles      = @json($geojson);          // Only public shapefiles
    const categories      = @json($categories);
    const classifications = @json($classifications);
    const defaultLoc      = @json($defaultLoc);       // boundary polygons

    /*
     * Build a lookup: default_location_id → { district, municity, brgy }
     * Used for accurate client-side location filtering
     */
    const locById = {};
    (defaultLoc || []).forEach(function(loc) {
        locById[loc.id] = {
            district: (loc.district || '').trim(),
            municity: (loc.municity || '').trim(),
            brgy:     (loc.brgy     || '').trim(),
        };
    });

    /* ═══════════════════════════════════════════════════════════
       FILTER STATE
    ═══════════════════════════════════════════════════════════ */
    let selCats      = new Set();
    let selCls       = new Set();
    let advOpen      = false;
    let apOpen       = true;
    let quickSearchQ = '';
    let dateFrom     = '';
    let dateTo       = '';

    /* Location state */
    let selDistricts  = new Set();
    let selMunicities = new Set();
    let selBrgys      = new Set();
    let locSearchQ    = '';
    let activeLocTab  = 'district';
    let locOpen       = false;

    /* ═══════════════════════════════════════════════════════════
       BUILD LOCATION INDEX
    ═══════════════════════════════════════════════════════════ */
    const locIdx = (function() {
        const districts  = new Map();
        const municities = new Map();
        const brgys      = new Map();

        (defaultLoc || []).forEach(function(loc) {
            const d = (loc.district || '').trim();
            const m = (loc.municity || '').trim();
            const b = (loc.brgy    || '').trim();

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

    /* ═══════════════════════════════════════════════════════════
       UTILITY
    ═══════════════════════════════════════════════════════════ */
    function esc(s) {
        return String(s)
            .replace(/&/g,'&amp;').replace(/</g,'&lt;')
            .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    /* ═══════════════════════════════════════════════════════════
       ADVANCED SEARCH TOGGLE
    ═══════════════════════════════════════════════════════════ */
    function toggleAdvSearch() {
        advOpen = !advOpen;
        document.getElementById('advSearchBtn').classList.toggle('active', advOpen);
        document.getElementById('advChevron').style.transform = advOpen ? 'rotate(180deg)' : '';
        document.querySelectorAll('.fb-dynamic').forEach(el => el.classList.toggle('visible', advOpen));
        document.querySelectorAll('.fb-sep').forEach(el => el.classList.toggle('visible', advOpen));
        if (advOpen) renderLocLists();
        renderMap();
    }

    /* ═══════════════════════════════════════════════════════════
       STANDARD PILL TOGGLE
    ═══════════════════════════════════════════════════════════ */
    function togglePill(id) {
        const pill    = document.getElementById(id);
        const wasOpen = pill.classList.contains('open');
        document.querySelectorAll('.ms-pill.open').forEach(p => p.classList.remove('open'));
        closeLoc();
        if (!wasOpen) pill.classList.add('open');
    }

    document.addEventListener('click', function(e) {
        if (!e.target.closest('.ms-pill')) {
            document.querySelectorAll('.ms-pill.open').forEach(p => p.classList.remove('open'));
        }
    });

    /* ═══════════════════════════════════════════════════════════
       LOCATION PILL
    ═══════════════════════════════════════════════════════════ */
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

    document.addEventListener('DOMContentLoaded', function() {

        document.getElementById('locTrigger').addEventListener('click', function(e) {
            e.stopPropagation();
            document.querySelectorAll('.ms-pill.open').forEach(p => p.classList.remove('open'));
            locOpen ? closeLoc() : openLoc();
        });

        document.getElementById('locDropdown').addEventListener('click', function(e) {
            e.stopPropagation();
        });

        document.addEventListener('click', function() {
            if (locOpen) closeLoc();
        });

        document.getElementById('locSearchInput').addEventListener('input', function() {
            locSearchQ = this.value.trim().toLowerCase();
            renderLocLists();
        });

        document.getElementById('locTabBar').addEventListener('click', function(e) {
            const tab = e.target.closest('.loc-tab');
            if (!tab) return;
            activeLocTab = tab.dataset.tab;
            document.querySelectorAll('.loc-tab').forEach(t =>
                t.classList.toggle('active', t.dataset.tab === activeLocTab));
            ['district','municity','brgy'].forEach(function(t) {
                const cap = t.charAt(0).toUpperCase() + t.slice(1);
                document.getElementById('panel' + cap).style.display =
                    t === activeLocTab ? '' : 'none';
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
            if (!x) return;
            toggleLocItem(x.dataset.type, x.dataset.value);
        });

        document.getElementById('locClearBtn').addEventListener('click', function() {
            clearLocFilter();
        });

        renderLocLists();

    });

    function toggleLocItem(type, value) {
        const set = type === 'district' ? selDistricts
                  : type === 'municity'  ? selMunicities
                  : selBrgys;
        set.has(value) ? set.delete(value) : set.add(value);
        renderLocLists();
        syncLocLabel();
        renderMap();
    }

    function clearLocFilter() {
        selDistricts.clear();
        selMunicities.clear();
        selBrgys.clear();
        locSearchQ = '';
        document.getElementById('locSearchInput').value = '';
        renderLocLists();
        syncLocLabel();
        renderMap();
    }

    function renderLocLists() {
        const q = locSearchQ;

        const allDist  = Array.from(locIdx.districts.keys()).sort();
        const visDist  = allDist.filter(d => !q || d.toLowerCase().includes(q));
        document.getElementById('cntDistrict').textContent = visDist.length;
        document.getElementById('panelDistrict').innerHTML = visDist.length
            ? visDist.map(d => {
                const on = selDistricts.has(d);
                const mc = locIdx.districts.get(d).size;
                return `<div class="loc-item${on?' active':''}" data-value="${esc(d)}">
                    <div class="loc-item-ico"><i class="fas fa-city"></i></div>
                    <div class="loc-item-txt">
                        <span class="loc-item-name">${esc(d)}</span>
                        <span class="loc-item-sub">${mc} municipalit${mc===1?'y':'ies'}</span>
                    </div>
                    <div class="loc-item-chk"></div>
                </div>`;
            }).join('')
            : '<div class="loc-empty"><i class="fas fa-search"></i>No districts found</div>';

        let allMuni = Array.from(locIdx.municities.keys()).sort();
        if (selDistricts.size) {
            allMuni = allMuni.filter(m => selDistricts.has(locIdx.municities.get(m).district));
        }
        const visMuni = allMuni.filter(m => !q || m.toLowerCase().includes(q));
        document.getElementById('cntMunicity').textContent = visMuni.length;
        document.getElementById('panelMunicity').innerHTML = visMuni.length
            ? visMuni.map(m => {
                const on   = selMunicities.has(m);
                const info = locIdx.municities.get(m);
                const bc   = info.brgys.size;
                return `<div class="loc-item${on?' active':''}" data-value="${esc(m)}">
                    <div class="loc-item-ico"><i class="fas fa-building"></i></div>
                    <div class="loc-item-txt">
                        <span class="loc-item-name">${esc(m)}</span>
                        <span class="loc-item-sub">${esc(info.district||'—')} · ${bc} barangay${bc===1?'':'s'}</span>
                    </div>
                    <div class="loc-item-chk"></div>
                </div>`;
            }).join('')
            : '<div class="loc-empty"><i class="fas fa-search"></i>No municipalities found</div>';

        let allBrgy = Array.from(locIdx.brgys.keys()).sort();
        if (selMunicities.size) {
            allBrgy = allBrgy.filter(b => selMunicities.has(locIdx.brgys.get(b).municity));
        } else if (selDistricts.size) {
            allBrgy = allBrgy.filter(b => selDistricts.has(locIdx.brgys.get(b).district));
        }
        const visBrgy = allBrgy.filter(b => !q || b.toLowerCase().includes(q));
        document.getElementById('cntBrgy').textContent = visBrgy.length;
        document.getElementById('panelBrgy').innerHTML = visBrgy.length
            ? visBrgy.map(b => {
                const on   = selBrgys.has(b);
                const info = locIdx.brgys.get(b);
                return `<div class="loc-item${on?' active':''}" data-value="${esc(b)}">
                    <div class="loc-item-ico"><i class="fas fa-home"></i></div>
                    <div class="loc-item-txt">
                        <span class="loc-item-name">${esc(b)}</span>
                        <span class="loc-item-sub">${esc(info.municity||info.district||'—')}</span>
                    </div>
                    <div class="loc-item-chk"></div>
                </div>`;
            }).join('')
            : '<div class="loc-empty"><i class="fas fa-search"></i>No barangays found</div>';

        const chipsEl  = document.getElementById('locChips');
        const emptyEl  = document.getElementById('locChipsEmpty');
        chipsEl.querySelectorAll('.loc-chip').forEach(c => c.remove());

        const chips = [];
        selDistricts.forEach(d  => chips.push({ type:'district',  value:d, label:'District' }));
        selMunicities.forEach(m => chips.push({ type:'municity',  value:m, label:'Muni'     }));
        selBrgys.forEach(b      => chips.push({ type:'brgy',      value:b, label:'Brgy'     }));

        if (chips.length) {
            emptyEl.style.display = 'none';
            chips.forEach(c => {
                const span = document.createElement('span');
                span.className = 'loc-chip';
                span.innerHTML =
                    `<span class="loc-chip-lbl">${esc(c.label)}</span>` +
                    esc(c.value) +
                    `<span class="loc-chip-x" data-type="${c.type}" data-value="${esc(c.value)}">✕</span>`;
                chipsEl.appendChild(span);
            });
        } else {
            emptyEl.style.display = '';
        }

        const total = selDistricts.size + selMunicities.size + selBrgys.size;
        document.getElementById('locFooterInfo').textContent = total
            ? `${total} location filter${total === 1 ? '' : 's'} active`
            : 'Select a location to filter';
    }

    function syncLocLabel() {
        const total = selDistricts.size + selMunicities.size + selBrgys.size;
        const lbl   = document.getElementById('locLabel');
        const bdg   = document.getElementById('locBadge');
        if (!total) {
            lbl.textContent   = 'Location';
            bdg.style.display = 'none';
        } else {
            const first = selDistricts.size  ? [...selDistricts][0]
                        : selMunicities.size ? [...selMunicities][0]
                        : [...selBrgys][0];
            lbl.textContent   = total === 1 ? first : 'Location';
            bdg.textContent   = total;
            bdg.style.display = 'inline-block';
        }
    }

    function onQuickSearch() {
        quickSearchQ = document.getElementById('quickSearch').value.trim().toLowerCase();
        renderMap();
    }

    function toggleCat(val, el) {
        selCats.has(val) ? (selCats.delete(val), el.classList.remove('selected'))
                         : (selCats.add(val),    el.classList.add('selected'));
        syncCatLabel(); syncClsOptions(); renderMap();
    }

    function selectAllCat() {
        const opts  = document.querySelectorAll('#catDropdown .ms-opt:not(.ms-all)');
        const allOn = selCats.size === categories.length;
        selCats.clear();
        opts.forEach(o => o.classList.remove('selected'));
        if (!allOn) {
            categories.forEach(c => selCats.add(c.name));
            opts.forEach(o => o.classList.add('selected'));
        }
        syncCatLabel(); syncClsOptions(); renderMap();
    }

    function syncCatLabel() {
        const lbl = document.getElementById('catLabel');
        const bdg = document.getElementById('catBadge');
        if (!selCats.size || selCats.size === categories.length) {
            lbl.textContent   = 'Category'; bdg.style.display = 'none';
        } else {
            lbl.textContent   = selCats.size === 1 ? [...selCats][0] : 'Category';
            bdg.textContent   = selCats.size; bdg.style.display = 'inline-block';
        }
    }

    function toggleCls(val, el) {
        val = String(val);
        selCls.has(val) ? (selCls.delete(val), el.classList.remove('selected'))
                        : (selCls.add(val),    el.classList.add('selected'));
        syncClsLabel(); renderMap();
    }

    function selectAllCls() {
        const opts  = document.querySelectorAll('#clsDropdown .ms-opt:not(.ms-all):not([style*="display: none"])');
        const allOn = [...opts].every(o => o.classList.contains('selected'));
        if (allOn) {
            selCls.clear();
            opts.forEach(o => o.classList.remove('selected'));
        } else {
            opts.forEach(o => { selCls.add(String(o.dataset.val)); o.classList.add('selected'); });
        }
        syncClsLabel(); renderMap();
    }

    function syncClsLabel() {
        const lbl = document.getElementById('clsLabel');
        const bdg = document.getElementById('clsBadge');
        if (!selCls.size || selCls.size === classifications.length) {
            lbl.textContent   = 'Classification'; bdg.style.display = 'none';
        } else {
            const found = classifications.find(c => selCls.has(String(c.id)));
            lbl.textContent   = selCls.size === 1 && found ? found.name : 'Classification';
            bdg.textContent   = selCls.size; bdg.style.display = 'inline-block';
        }
    }

    function syncClsOptions() {
        document.querySelectorAll('#clsDropdown .ms-opt:not(.ms-all)').forEach(o => {
            const cid  = parseInt(o.dataset.val);
            const show = !selCats.size ||
                shapefiles.some(s => selCats.has(s.category) && s.classification_id === cid);
            o.style.display = show ? '' : 'none';
            if (!show) { selCls.delete(String(cid)); o.classList.remove('selected'); }
        });
        syncClsLabel();
    }

    function onDateChange() {
        dateFrom = document.getElementById('dateFrom').value;
        dateTo   = document.getElementById('dateTo').value;
        document.getElementById('dateBadge').style.display =
            (dateFrom || dateTo) ? 'inline-block' : 'none';
        renderMap();
    }

    function clearFilters() {
        selCats.clear(); selCls.clear();
        document.querySelectorAll('.ms-opt').forEach(o => o.classList.remove('selected'));
        document.getElementById('quickSearch').value = '';
        document.getElementById('dateFrom').value    = '';
        document.getElementById('dateTo').value      = '';
        document.getElementById('dateBadge').style.display = 'none';
        quickSearchQ = ''; dateFrom = ''; dateTo = '';
        syncCatLabel(); syncClsOptions(); syncClsLabel();
        clearLocFilter();
    }

    function toggleAnalysis() {
        apOpen = !apOpen;
        document.getElementById('apBody').classList.toggle('hidden', !apOpen);
        document.getElementById('apToggleIcon').classList.toggle('collapsed', !apOpen);
    }

    function updateAnalysisCounts(filteredItems) {
        document.querySelectorAll('#apBody .ap-card').forEach(card => {
            const catName = card.dataset.cat;
            const slug    = catName.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
            const el      = document.getElementById('apCount-' + slug);
            if (el) el.textContent = '0';
        });

        const counts = {};
        filteredItems.forEach(item => {
            counts[item.category] = (counts[item.category] || 0) + 1;
        });

        document.querySelectorAll('#apBody .ap-card').forEach(card => {
            const catName = card.dataset.cat;
            const slug    = catName.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
            const el      = document.getElementById('apCount-' + slug);
            if (el) el.textContent = counts[catName] || 0;
        });
    }

    function featureMatchesLocation(item) {
        const hasLocFilter = selDistricts.size || selMunicities.size || selBrgys.size;
        if (!hasLocFilter) return true;

        const locId = item.default_location_id;
        if (locId && locById[locId]) {
            const locData = locById[locId];

            if (selBrgys.size) {
                if (selBrgys.has(locData.brgy)) return true;
            }
            if (selMunicities.size) {
                if (selMunicities.has(locData.municity)) return true;
            }
            if (selDistricts.size) {
                if (selDistricts.has(locData.district)) return true;
            }
            return false;
        }

        const loc = (item.location || '').toLowerCase();
        if (!loc) return false;

        for (const b of selBrgys)      { if (loc.includes(b.toLowerCase())) return true; }
        for (const m of selMunicities) { if (loc.includes(m.toLowerCase())) return true; }
        for (const d of selDistricts)  { if (loc.includes(d.toLowerCase())) return true; }

        return false;
    }

    function hasAnyActiveFilter() {
        return (
            quickSearchQ.length > 0         ||
            selCats.size > 0                ||
            selCls.size  > 0                ||
            dateFrom     !== ''             ||
            dateTo       !== ''             ||
            selDistricts.size  > 0          ||
            selMunicities.size > 0          ||
            selBrgys.size      > 0
        );
    }

    function featureMatchesFilters(item) {
        if (!item.geometry) return false;
        if (selCats.size && !selCats.has(item.category)) return false;
        if (selCls.size  && !selCls.has(String(item.classification_id))) return false;
        if (dateFrom && item.survey_date && item.survey_date < dateFrom) return false;
        if (dateTo   && item.survey_date && item.survey_date > dateTo)   return false;
        if ((dateFrom || dateTo) && !item.survey_date) return false;
        if (!featureMatchesLocation(item)) return false;

        if (quickSearchQ) {
            const hay = [
                item.description    || '',
                item.location       || '',
                item.category       || '',
                item.classification || '',
                item.survey_date    || '',
            ].join(' ').toLowerCase();
            if (!hay.includes(quickSearchQ)) return false;
        }
        return true;
    }

    /* ═══════════════════════════════════════════════════════════
       MAP INIT
    ═══════════════════════════════════════════════════════════ */
    document.addEventListener('DOMContentLoaded', function() {

        const map = L.map('map', {
            center: [14.28, 121.4], zoom: 10,
            zoomControl: true, scrollWheelZoom: true
        });

        const osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 20, attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);
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

        L.control.scale({ imperial: false, position: 'bottomleft' }).addTo(map);
        L.control.layers({
            'OSM': osm, 'Satellite': satellite,
            'Hybrid': hybrid, 'Carto Light': cartoLight, 'Carto Dark': cartoDark
        }).addTo(map);
        L.control.fullscreen({ position:'topleft', title:'Fullscreen', titleCancel:'Exit fullscreen' }).addTo(map);

        map.on('mousemove', e => {
            document.getElementById('coordLat').textContent = e.latlng.lat.toFixed(5);
            document.getElementById('coordLng').textContent = e.latlng.lng.toFixed(5);
        });
        map.on('zoomend', () => {
            document.getElementById('coordZoom').textContent = map.getZoom();
        });

        const featureLayer = L.featureGroup().addTo(map);
        const defaultLayer = L.featureGroup().addTo(map);

        let legendCtrl = null;

        function renderDefaultLocations() {
            defaultLayer.clearLayers();
            (defaultLoc || []).forEach(function(loc) {
                if (!loc.geometry) return;
                const dStyle = {
                    color: '#3388ff', 
                    weight: 1.5,
                    opacity: 0.75, 
                    fillOpacity: 0, 
                    fillColor: '#3388ff',
                    interactive: false
                };
                
                L.geoJSON(loc.geometry, {
                    style: dStyle,
                    interactive: false
                }).addTo(defaultLayer);
            });

            if (defaultLayer.getLayers().length) {
                map.fitBounds(defaultLayer.getBounds(), { padding: [60, 60], maxZoom: 13 });
            }
        }

        function getColor(item) { return item.classification_color || '#b71c1c'; }

        function updateLegend(shapes) {
            if (legendCtrl) map.removeControl(legendCtrl);
            if (!shapes.length) return;

            legendCtrl = L.control({ position: 'bottomright' });
            legendCtrl.onAdd = function() {
                const div     = L.DomUtil.create('div', 'gis-legend');
                const usedIds = [...new Set(shapes.map(s => s.classification_id))];
                const used    = classifications.filter(c => usedIds.includes(c.id));
                if (!used.length) return div;
                let html = '<div class="legend-title">Legend</div>';
                used.forEach(c => {
                    html += `<div class="legend-row">
                        <div class="legend-swatch" style="background:${c.color};"></div>
                        <span>${esc(c.name)}</span></div>`;
                });
                div.innerHTML = html;
                return div;
            };
            legendCtrl.addTo(map);
        }

        window.renderMap = function() {
            featureLayer.clearLayers();

            const filterActive = hasAnyActiveFilter();
            const hintEl       = document.getElementById('no-filter-hint');
            const toastEl      = document.getElementById('no-results-toast');

            hintEl.style.display  = 'none';
            toastEl.style.display = 'none';

            if (!filterActive) {
                document.getElementById('featureCount').textContent = '0';
                updateAnalysisCounts([]);
                if (legendCtrl) { map.removeControl(legendCtrl); legendCtrl = null; }
                hintEl.style.display = 'flex';
                return;
            }

            const filtered = shapefiles.filter(featureMatchesFilters);
            document.getElementById('featureCount').textContent = filtered.length;
            updateAnalysisCounts(filtered);

            if (!filtered.length) {
                toastEl.style.display = 'block';
                updateLegend([]);
                return;
            }

            filtered.forEach(function(item) {
                const color = getColor(item);
                const style = {
                    color, fillColor: color,
                    weight: 2.5, opacity: 0.85, fillOpacity: 0.22
                };

                L.geoJSON(item.geometry, {
                    style,
                    pointToLayer: function(_, latlng) {
                        return L.circleMarker(latlng, {
                            radius: 8, fillColor: color, color,
                            weight: 2, opacity: 1, fillOpacity: 0.85
                        });
                    },
                    onEachFeature: function(_, layer) {
                        const MAX = 5;
                        let rows  = '';

                        [
                            { key: 'Description',    val: item.description  },
                            { key: 'Location',       val: item.location     },
                            { key: 'Date Collected', val: item.survey_date  },
                        ].forEach(function(f) {
                            if (f.val) {
                                rows += `<div class="popup-row">
                                    <span class="popup-key">${esc(f.key)}</span>
                                    <span class="popup-val">${esc(f.val)}</span>
                                </div>`;
                            }
                        });

                        let extra = 0;
                        if (item.metadata && item.metadata.length) {
                            item.metadata.slice(0, MAX).forEach(function(m) {
                                rows += `<div class="popup-row">
                                    <span class="popup-key">${esc(m.meta_key)}</span>
                                    <span class="popup-val">${m.meta_value
                                        ? esc(m.meta_value)
                                        : '<em style="opacity:.4">—</em>'}</span>
                                </div>`;
                            });
                            extra = item.metadata.length - MAX;
                        }

                        const popup =
                            `<div class="popup-wrap">
                                <div class="popup-cat">${esc(item.category)}</div>
                                <span class="popup-cls"
                                    style="background:${item.classification_color || '#6c757d'}">
                                    ${esc(item.classification || 'No Classification')}
                                </span>
                                <div class="popup-divider"></div>
                                ${rows || '<div class="popup-empty"><i class="fas fa-info-circle me-1"></i>No data available</div>'}
                                ${extra > 0
                                    ? `<div class="popup-more">
                                        <button class="popup-more-btn view-meta"
                                            data-id="${item.feature_id}">
                                            <i class="fas fa-table me-1"></i>
                                            View all ${item.metadata.length} fields
                                        </button>
                                       </div>`
                                    : ''}
                            </div>`;

                        layer.bindPopup(popup, { maxWidth: 340 });
                        layer.on('mouseover', function() {
                            layer.setStyle({ weight: 4, fillOpacity: 0.38 });
                        });
                        layer.on('mouseout',  function() {
                            layer.setStyle(style);
                        });
                        layer.addTo(featureLayer);
                    }
                });
            });

            if (featureLayer.getLayers().length) {
                map.fitBounds(featureLayer.getBounds(), { padding: [80, 80], maxZoom: 15 });
            }

            updateLegend(filtered);
        };

        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.view-meta');
            if (!btn) return;
            const item = shapefiles.find(s => s.feature_id == btn.dataset.id);
            if (!item) return;

            document.getElementById('modalCategory').textContent  = item.category;
            document.getElementById('metadataCount').textContent  = `${item.metadata.length} metadata items`;
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
                item.metadata.forEach(function(m, i) {
                    html += `<div class="col-md-6"><div class="metadata-item">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="fw-bold">${esc(m.meta_key)}</span>
                            <span class="badge bg-light text-dark small">#${i + 1}</span>
                        </div>
                        <div class="text-muted" style="word-break:break-word;line-height:1.6;">
                            ${m.meta_value
                                ? esc(m.meta_value)
                                : '<span class="text-muted fst-italic">Not specified</span>'}
                        </div>
                    </div></div>`;
                });
                html += `</div>
                <div class="mt-4 p-3 rounded-3" style="background-color:rgba(183,28,28,0.05);">
                    <div class="row">
                        <div class="col-md-6 small">
                            <i class="fas fa-layer-group me-1" style="color:#b71c1c;"></i>
                            <strong style="color:#b71c1c;">Total Items:</strong> ${item.metadata.length}
                        </div>
                        <div class="col-md-6 text-md-end small">
                            <i class="fas fa-tag me-1" style="color:#b71c1c;"></i>
                            <strong style="color:#b71c1c;">Category:</strong> ${esc(item.category)}
                        </div>
                    </div>
                </div>`;
            }

            document.getElementById('metadataModalBody').innerHTML = html;
            new bootstrap.Modal(
                document.getElementById('metadataModal'), { backdrop: 'static' }
            ).show();
        });

        renderDefaultLocations();
        renderMap();

    });
    </script>
    @endpush
@endsection