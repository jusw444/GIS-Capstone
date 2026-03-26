@extends('layouts.app')

@section('page_title', $page['pageTitle'])

@section('content')

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.fullscreen@1.6.0/Control.FullScreen.css" />
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Space+Mono:wght@400;700&display=swap"
        rel="stylesheet">

    @push('styles')
        <style>
            :root {
                --red: #b71c1c;
                --red-mid: #c62828;
                --red-dark: #8c1c1c;
                --red-light: rgba(183, 28, 28, .08);
                --red-border: rgba(183, 28, 28, .3);
                --bg: #f0f2f5;
                --surface: #ffffff;
                --panel: #ffffff;
                --panel2: #f8f9fa;
                --border: rgba(0, 0, 0, .09);
                --border-hi: rgba(183, 28, 28, .35);
                --text: #1a1e2e;
                --text-sub: #4a5568;
                --muted: #8a94a8;
                --font: 'Inter', sans-serif;
                --mono: 'Space Mono', monospace;
                --shadow-sm: 0 2px 8px rgba(0, 0, 0, .08);
                --shadow-md: 0 6px 24px rgba(0, 0, 0, .10);
                --shadow-lg: 0 12px 40px rgba(0, 0, 0, .13);
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

            /* ── FULL VIEWPORT ── */
            #map-root {
                position: fixed;
                top: 0;
                left: 300px;
                width: calc(100% - 300px);
                height: 100vh;
                transition: all 0.3s ease;
            }

            /* When sidebar is hidden */
            body.sidebar-collapsed #map-root {
                left: 0;
                width: 100%;
            }

            #map {
                width: 100%;
                height: 100%;
                background: #e8ecf0;
            }

            /* ── TOP FILTER BAR ── */
            #filter-bar {
                position: absolute;
                top: 14px;
                left: 50%;
                transform: translateX(-50%);
                z-index: 800;
                display: flex;
                align-items: center;
                gap: 8px;
                background: rgba(255, 255, 255, .96);
                border: 1px solid var(--border);
                border-radius: 40px;
                padding: 7px 10px 7px 16px;
                backdrop-filter: blur(12px);
                -webkit-backdrop-filter: blur(12px);
                box-shadow: var(--shadow-md), 0 0 0 1px rgba(183, 28, 28, .06);
                white-space: nowrap;
            }

            .fb-brand {
                display: flex;
                align-items: center;
                gap: 8px;
                padding-right: 12px;
                border-right: 1px solid var(--border);
                margin-right: 4px;
            }

            .fb-brand-icon {
                width: 26px;
                height: 26px;
                background: var(--red);
                border-radius: 7px;
                display: grid;
                place-items: center;
                font-size: 11px;
                color: #fff;
                flex-shrink: 0;
            }

            .fb-brand-name {
                font-size: 12px;
                font-weight: 700;
                letter-spacing: .4px;
                color: var(--text);
                text-transform: uppercase;
            }

            .fb-sep {
                width: 1px;
                height: 20px;
                background: var(--border);
                flex-shrink: 0;
            }

            .fb-label {
                font-size: 10px;
                font-weight: 600;
                letter-spacing: .7px;
                text-transform: uppercase;
                color: var(--muted);
            }

            /* ── MULTI-SELECT PILL ── */
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

            /* clear + count */
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

            /* ── ANALYSIS PANEL (bottom-left) ── */
            #analysis-panel {
                position: absolute;
                bottom: 44px;
                left: 14px;
                z-index: 800;
                width: 264px;
                background: rgba(255, 255, 255, .96);
                border: 1px solid var(--border);
                border-radius: 14px;
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
                font-size: 12px;
                font-weight: 700;
                letter-spacing: .4px;
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

            /* ── COORD BAR (bottom-center) ── */
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

            /* ── LEGEND ── */
            .gis-legend {
                background: rgba(255, 255, 255, .96) !important;
                border: 1px solid var(--border) !important;
                border-radius: 12px !important;
                padding: 12px 14px !important;
                backdrop-filter: blur(10px);
                box-shadow: var(--shadow-md) !important;
            }

            .legend-title {
                font-size: 10px;
                font-weight: 700;
                letter-spacing: .8px;
                text-transform: uppercase;
                color: var(--red);
                margin-bottom: 8px;
                font-family: var(--font);
            }

            .legend-row {
                display: flex;
                align-items: center;
                gap: 8px;
                font-size: 11px;
                color: var(--text-sub);
                margin-bottom: 5px;
                font-family: var(--font);
            }

            .legend-swatch {
                width: 12px;
                height: 12px;
                border-radius: 3px;
                flex-shrink: 0;
            }

            /* ── LEAFLET OVERRIDES ── */
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
                background: rgba(255, 255, 255, .96) !important;
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

            /* ── POPUP ── */
            .popup-wrap {
                padding: 14px 16px;
                font-family: var(--font);
                min-width: 230px;
            }

            .popup-cat {
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

            /* ── MODAL ── */
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
        </style>
    @endpush

    <!-- FULL-SCREEN MAP -->
    <div id="map-root">
        <div id="map"></div>

        <!-- TOP FILTER BAR -->
        <div id="filter-bar">

            <div class="fb-brand">
                <div class="fb-brand-icon"><i class="fas fa-map-marked-alt"></i></div>
                <span class="fb-brand-name">{{ $page['pageName'] }}</span>
            </div>

            <span class="fb-label">Filter</span>

            <!-- Category Multi-Select -->
            <div class="ms-pill" id="catPill">
                <div class="ms-trigger" onclick="togglePill('catPill')">
                    <i class="fas fa-layer-group"></i>
                    <span id="catLabel">All Categories</span>
                    <span class="ms-badge" id="catBadge" style="display:none">0</span>
                    <span class="ms-arrow">▼</span>
                </div>
                <div class="ms-dropdown" id="catDropdown">
                    <div class="ms-opt ms-all" onclick="selectAllCat()">
                        <div class="ms-check" id="catAllChk"></div>
                        Select All
                    </div>
                    @foreach ($categories as $cat)
                        <div class="ms-opt" data-val="{{ $cat->name }}" onclick="toggleCat('{{ $cat->name }}', this)">
                            <div class="ms-check"></div>
                            <span>{{ ucfirst($cat->name) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="fb-sep"></div>

            <!-- Classification Multi-Select -->
            <div class="ms-pill" id="clsPill">
                <div class="ms-trigger" onclick="togglePill('clsPill')">
                    <i class="fas fa-tags"></i>
                    <span id="clsLabel">All Classifications</span>
                    <span class="ms-badge" id="clsBadge" style="display:none">0</span>
                    <span class="ms-arrow">▼</span>
                </div>
                <div class="ms-dropdown" id="clsDropdown">
                    <div class="ms-opt ms-all" onclick="selectAllCls()">
                        <div class="ms-check" id="clsAllChk"></div>
                        Select All
                    </div>
                    @foreach ($classifications as $c)
                        <div class="ms-opt" data-val="{{ $c->id }}" onclick="toggleCls('{{ $c->id }}', this)">
                            <div class="ms-check"></div>
                            <span>{{ $c->name }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="fb-sep"></div>

            <button class="fb-clear" onclick="clearFilters()">
                <i class="fas fa-times" style="font-size:9px;"></i> Reset
            </button>

            <div class="fb-count">
                <span class="fb-count-num" id="featureCount">0</span>
                <span class="fb-count-lbl">Features</span>
            </div>

        </div><!-- /filter-bar -->

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
                            style="position:absolute; left:0; top:0; bottom:0; width:3px; background:{{ $cat['color'] }}; border-radius:9px 0 0 9px;">
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
                    <h5 class="modal-title fw-bold"><i class="fas fa-database me-2"></i>Shapefile Metadata</h5>
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
            const shapefiles = @json($geojson);
            const categories = @json($categories);
            const classifications = @json($classifications);

            let selCats = new Set();
            let selCls = new Set();
            let apOpen = true;

            /* ── PILL TOGGLE ── */
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

            /* ── CATEGORY ── */
            function toggleCat(val, el) {
                selCats.has(val) ? (selCats.delete(val), el.classList.remove('selected')) :
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
                    lbl.textContent = 'All Categories';
                    bdg.style.display = 'none';
                } else {
                    lbl.textContent = selCats.size === 1 ? [...selCats][0] : 'Categories';
                    bdg.textContent = selCats.size;
                    bdg.style.display = 'inline-block';
                }
            }

            /* ── CLASSIFICATION ── */
            function toggleCls(val, el) {
                val = String(val);
                selCls.has(val) ? (selCls.delete(val), el.classList.remove('selected')) :
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
                    lbl.textContent = 'All Classifications';
                    bdg.style.display = 'none';
                } else {
                    const found = classifications.find(c => selCls.has(String(c.id)));
                    lbl.textContent = selCls.size === 1 && found ? found.name : 'Classifications';
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

            /* ── CLEAR ── */
            function clearFilters() {
                selCats.clear();
                selCls.clear();
                document.querySelectorAll('.ms-opt').forEach(o => o.classList.remove('selected'));
                syncCatLabel();
                syncClsOptions();
                syncClsLabel();
                renderMap();
            }

            /* ── ANALYSIS TOGGLE ── */
            function toggleAnalysis() {
                apOpen = !apOpen;
                document.getElementById('apBody').classList.toggle('hidden', !apOpen);
                document.getElementById('apToggleIcon').classList.toggle('collapsed', !apOpen);
            }

            /* ── MAP INIT ── */
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

                window.renderMap = function() {
                    layerGroup.clearLayers();

                    const filtered = shapefiles.filter(item => {
                        if (!item.geometry) return false;
                        if (selCats.size && !selCats.has(item.category)) return false;
                        if (selCls.size && !selCls.has(String(item.classification_id))) return false;
                        return true;
                    });

                    document.getElementById('featureCount').textContent = filtered.length;

                    filtered.forEach(item => {
                        const color = getColor(item);
                        const style = {
                            color,
                            fillColor: color,
                            weight: 2.5,
                            opacity: 0.85,
                            fillOpacity: 0.18
                        };

                        L.geoJSON(item.geometry, {
                            style,

                            pointToLayer: function(feature, latlng) {
                                return L.circleMarker(latlng, {
                                    radius: 8,
                                    fillColor: color,
                                    color: color,
                                    weight: 2,
                                    opacity: 1,
                                    fillOpacity: 0.8
                                });
                            },

                            onEachFeature: (feature, layer) => {
                                const MAX = 5;
                                let rows = '';
                                let extra = 0;
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
    <div class="popup-name">${item.classification || 'Unnamed Feature'}</div>
    <span class="popup-cls" style="background:${item.classification_color || '#6c757d'}">
        ${item.classification || 'No Classification'}
    </span>
    <div class="popup-divider"></div>
    ${rows || `<div class="popup-empty"><i class="fas fa-info-circle me-1"></i>No metadata available</div>`}
    ${extra > 0 ? `<div class="popup-more">
                        <button class="popup-more-btn view-meta" data-id="${item.feature_id}">
                            <i class="fas fa-table me-1"></i>View all ${item.metadata.length} fields
                        </button>
                    </div>` : ''}
</div>`;
                                layer.bindPopup(popup, {
                                    maxWidth: 320
                                });
                                layer.on('mouseover', () => layer.setStyle({
                                    weight: 4,
                                    fillOpacity: 0.3
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

                /* ── METADATA MODAL ── */
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
            });
        </script>
    @endpush

@endsection
