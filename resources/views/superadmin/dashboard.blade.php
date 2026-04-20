@extends('layouts.app')

@section('page_title', $page['pageTitle'])

@section('content')

    <!-- Font Awesome & Google Fonts -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    @push('styles')
    <style>
        :root {
            --primary:       #b71c1c;
            --primary-mid:   #c62828;
            --primary-light: rgba(183,28,28,0.08);
            --primary-glow:  rgba(183,28,28,0.18);
            --green:         #2e7d32;
            --green-light:   rgba(46,125,50,0.08);
            --blue:          #1565c0;
            --blue-light:    rgba(21,101,192,0.08);
            --surface:       #ffffff;
            --surface-2:     #f7f8fa;
            --surface-3:     #f0f1f4;
            --border:        #e8eaed;
            --border-strong: #d1d5db;
            --text:          #111827;
            --text-2:        #4b5563;
            --text-3:        #9ca3af;
            --radius-sm:     6px;
            --radius:        10px;
            --radius-lg:     16px;
            --shadow-sm:     0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
            --shadow:        0 4px 12px rgba(0,0,0,0.07), 0 1px 3px rgba(0,0,0,0.04);
            --shadow-lg:     0 12px 32px rgba(0,0,0,0.10), 0 2px 6px rgba(0,0,0,0.04);
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'Space Grotesk', sans-serif;
            background: var(--surface-2);
            color: var(--text);
        }

        /* ── DASHBOARD WRAPPER ── */
        .gis-dashboard {
            max-width: 1440px;
            margin: 0 auto;
            padding: 28px 28px 48px;
        }

        /* ── HEADER (original design preserved) ── */
        .avatar-placeholder {
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        /* ── ALERT ── */
        .gis-alert {
            display: flex;
            align-items: center;
            gap: 12px;
            background: var(--surface);
            border: 1px solid #bbf7d0;
            border-left: 3px solid #16a34a;
            border-radius: var(--radius);
            padding: 12px 16px;
            margin-bottom: 28px;
            box-shadow: var(--shadow-sm);
            font-size: 13.5px;
        }

        .gis-alert-icon {
            color: #16a34a;
            font-size: 16px;
            flex-shrink: 0;
        }

        .gis-alert-close {
            margin-left: auto;
            background: none;
            border: none;
            cursor: pointer;
            color: var(--text-3);
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 14px;
        }

        .gis-alert-close:hover { color: var(--text); background: var(--surface-3); }

        /* ── STATS GRID ── */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 14px;
            margin-bottom: 28px;
        }

        @media (max-width: 1100px) { .stats-grid { grid-template-columns: repeat(3, 1fr); } }
        @media (max-width: 700px)  { .stats-grid { grid-template-columns: 1fr 1fr; } }

        /* ── STAT CARD ── */
        .stat-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 20px;
            box-shadow: var(--shadow-sm);
            display: flex;
            flex-direction: column;
            gap: 16px;
            transition: box-shadow .2s, transform .2s;
            position: relative;
            overflow: hidden;
        }

        .stat-card::after {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 2px;
            background: var(--card-accent, var(--primary));
            border-radius: var(--radius-lg) var(--radius-lg) 0 0;
        }

        .stat-card:hover {
            box-shadow: var(--shadow);
            transform: translateY(-2px);
        }

        .stat-card-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .stat-label {
            font-size: 11.5px;
            font-weight: 500;
            letter-spacing: 0.3px;
            color: var(--text-3);
            margin-bottom: 6px;
        }

        .stat-value {
            font-size: 32px;
            font-weight: 700;
            line-height: 1;
            color: var(--card-accent, var(--primary));
        }

        .stat-icon-wrap {
            width: 44px;
            height: 44px;
            border-radius: var(--radius);
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--card-icon-bg, var(--primary-light));
            flex-shrink: 0;
        }

        .stat-icon-wrap i {
            font-size: 18px;
            color: var(--card-accent, var(--primary));
        }

        .stat-footer {
            font-size: 12px;
            color: var(--text-3);
            display: flex;
            align-items: center;
            gap: 6px;
            border-top: 1px solid var(--border);
            padding-top: 12px;
        }

        .stat-cta {
            margin-top: 4px;
        }

        .stat-cta a {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            font-weight: 600;
            color: var(--card-accent, var(--primary));
            text-decoration: none;
            padding: 5px 10px;
            border-radius: var(--radius-sm);
            border: 1px solid var(--card-accent, var(--primary));
            transition: background .15s, color .15s;
            width: 100%;
            justify-content: center;
        }

        .stat-cta a:hover {
            background: var(--card-accent, var(--primary));
            color: white;
        }

        /* Mini stat cards (public/private) */
        .stat-card.mini {
            padding: 16px;
            gap: 10px;
        }

        .stat-card.mini .stat-value {
            font-size: 24px;
        }

        .stat-card.mini .stat-icon-wrap {
            width: 36px;
            height: 36px;
        }

        .stat-card.mini .stat-icon-wrap i {
            font-size: 15px;
        }

        /* ── MAIN CONTENT LAYOUT ── */
        .dash-body {
            display: grid;
            grid-template-columns: 1fr;
            gap: 20px;
        }

        /* ── CARD BASE ── */
        .panel {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
        }

        .panel-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 18px 22px 14px;
            border-bottom: 1px solid var(--border);
        }

        .panel-title {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            font-weight: 700;
            color: var(--text);
        }

        .panel-title-icon {
            width: 30px;
            height: 30px;
            border-radius: var(--radius-sm);
            background: var(--primary-light);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            font-size: 13px;
        }

        .panel-subtitle {
            font-size: 12px;
            color: var(--text-3);
            font-weight: 400;
            margin-top: 1px;
        }

        /* ── ACTIVITY LIST ── */
        .activity-scroll {
            max-height: 540px;
            overflow-y: auto;
        }

        .activity-scroll::-webkit-scrollbar { width: 4px; }
        .activity-scroll::-webkit-scrollbar-thumb { background: var(--border-strong); border-radius: 10px; }

        .activity-row {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            padding: 14px 22px;
            border-bottom: 1px solid var(--border);
            transition: background .15s;
        }

        .activity-row:last-child { border-bottom: none; }
        .activity-row:hover { background: var(--surface-2); }

        .activity-dot {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: var(--primary-light);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            font-size: 13px;
            flex-shrink: 0;
            margin-top: 1px;
        }

        .activity-main { flex: 1; min-width: 0; }

        .activity-desc {
            font-size: 13.5px;
            font-weight: 600;
            color: var(--text);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .activity-user {
            font-size: 12px;
            color: var(--text-3);
            margin-top: 2px;
        }

        .activity-meta {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 6px;
            flex-shrink: 0;
        }

        .activity-time {
            font-family: 'JetBrains Mono', monospace;
            font-size: 11px;
            color: var(--text-3);
        }

        .activity-badge {
            display: inline-flex;
            align-items: center;
            padding: 2px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            color: white;
            letter-spacing: 0.2px;
        }

        /* Empty state */
        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 56px 24px;
            color: var(--text-3);
        }

        .empty-state i {
            font-size: 36px;
            color: var(--primary-light);
            opacity: 0.5;
        }

        .empty-state p { font-size: 13px; margin: 0; }

        /* ── GIS COORDINATE TICKER ── */
        .coord-ticker {
            display: flex;
            align-items: center;
            gap: 20px;
            padding: 10px 22px;
            background: var(--surface-2);
            border-bottom: 1px solid var(--border);
            font-family: 'JetBrains Mono', monospace;
            font-size: 11.5px;
            color: var(--text-3);
            flex-wrap: wrap;
            gap: 12px;
        }

        .coord-item {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .coord-label {
            text-transform: uppercase;
            letter-spacing: 0.8px;
            font-size: 10px;
            color: var(--primary);
            font-weight: 600;
        }
    </style>
    @endpush

    <div class="gis-dashboard">

        <!-- ── HEADER (original) ── -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-5">
            <div>
                <h1 class="h2 fw-bold mb-2" style="color:#b71c1c;">
                    <i class="fas fa-crown me-2"></i>{{ $page['pageName'] }}
                </h1>
                <p class="text-muted mb-0">Complete system overview and management control panel</p>
            </div>
            <div class="mt-3 mt-md-0">
                <div class="d-flex align-items-center bg-white p-3 rounded-3 shadow-sm">
                    <div class="me-3">
                        <div class="avatar-placeholder rounded-circle d-flex align-items-center justify-content-center"
                            style="width: 45px; height: 45px; background-color: rgba(183, 28, 28, 0.1); color: #b71c1c;">
                            <i class="fas fa-user-shield"></i>
                        </div>
                    </div>
                    <div>
                        <div class="small text-muted">Super Administrator</div>
                        <div class="fw-semibold">{{ auth()->user()->name }}</div>
                        <div class="small text-muted">
                            <i class="fas fa-calendar-alt me-1"></i>
                            {{ now()->format('F j, Y') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── SUCCESS ALERT ── -->
        @if (session('success'))
        <div class="gis-alert" role="alert" id="success-alert">
            <i class="fas fa-check-circle gis-alert-icon"></i>
            <div>
                <strong>Success!</strong>&nbsp;&nbsp;{{ session('success') }}
            </div>
            <button class="gis-alert-close" onclick="document.getElementById('success-alert').remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        @endif

        <!-- ── STATS GRID ── -->
        <div class="stats-grid">

            <!-- Total Admins -->
            <div class="stat-card" style="--card-accent:#b71c1c; --card-icon-bg:rgba(183,28,28,0.08); grid-column: span 1;">
                <div class="stat-card-top">
                    <div>
                        <div class="stat-label">Total Admins</div>
                        <div class="stat-value">{{ $totalAdmins ?? 0 }}</div>
                    </div>
                    <div class="stat-icon-wrap">
                        <i class="fas fa-user-tie"></i>
                    </div>
                </div>
                <div class="stat-footer">
                    <i class="fas fa-shield-alt"></i>
                    System administrators with full access
                </div>
                <div class="stat-cta">
                    <a href="{{ route('superadmin.users') }}">
                        <i class="fas fa-arrow-right"></i> View All
                    </a>
                </div>
            </div>

            <!-- Total Users -->
            <div class="stat-card" style="--card-accent:#2e7d32; --card-icon-bg:rgba(46,125,50,0.08);">
                <div class="stat-card-top">
                    <div>
                        <div class="stat-label">Total Users</div>
                        <div class="stat-value">{{ $totalUsers ?? 0 }}</div>
                    </div>
                    <div class="stat-icon-wrap">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
                <div class="stat-footer">
                    <i class="fas fa-user-check"></i>
                    Registered platform users
                </div>
                <div class="stat-cta">
                    <a href="{{ route('superadmin.users') }}" style="--card-accent:#2e7d32;">
                        <i class="fas fa-user-plus"></i> Manage Users
                    </a>
                </div>
            </div>

            <!-- Spatial Data -->
            <div class="stat-card" style="--card-accent:#1565c0; --card-icon-bg:rgba(21,101,192,0.08);">
                <div class="stat-card-top">
                    <div>
                        <div class="stat-label">Created Spatial Data</div>
                        <div class="stat-value">{{ $totalShapefiles ?? 0 }}</div>
                    </div>
                    <div class="stat-icon-wrap">
                        <i class="fas fa-map-marked-alt"></i>
                    </div>
                </div>
                <div class="stat-footer">
                    <i class="fas fa-layer-group"></i>
                    System-generated datasets
                </div>
                <div class="stat-cta">
                    <a href="{{ route('admin.view') }}" style="--card-accent:#1565c0;">
                        <i class="fas fa-external-link-alt"></i> View on Map
                    </a>
                </div>
            </div>

            <!-- Public Data -->
            <div class="stat-card mini" style="--card-accent:#198754; --card-icon-bg:rgba(25,135,84,0.08);">
                <div class="stat-card-top">
                    <div>
                        <div class="stat-label">Public Data</div>
                        <div class="stat-value">{{ $totalPublicDatasets }}</div>
                    </div>
                    <div class="stat-icon-wrap">
                        <i class="fas fa-globe"></i>
                    </div>
                </div>
                <div class="stat-footer">
                    <i class="fas fa-eye"></i>
                    Publicly accessible
                </div>
            </div>

            <!-- Private Data -->
            <div class="stat-card mini" style="--card-accent:#b71c1c; --card-icon-bg:rgba(183,28,28,0.08);">
                <div class="stat-card-top">
                    <div>
                        <div class="stat-label">Private Data</div>
                        <div class="stat-value">{{ $totalPrivateDatasets }}</div>
                    </div>
                    <div class="stat-icon-wrap">
                        <i class="fas fa-lock"></i>
                    </div>
                </div>
                <div class="stat-footer">
                    <i class="fas fa-eye-slash"></i>
                    Restricted access
                </div>
            </div>

        </div>

        <!-- ── ACTIVITY PANEL ── -->
        <div class="dash-body">
            <div class="panel">

                <!-- GIS metadata bar -->
                <div class="coord-ticker">
                    <div class="coord-item">
                        <span class="coord-label">System</span>
                        <span>WGS 84 / EPSG:4326</span>
                    </div>
                    <div class="coord-item">
                        <span class="coord-label">Region</span>
                        <span>Philippines</span>
                    </div>
                    <div class="coord-item">
                        <span class="coord-label">Updated</span>
                        <span>{{ now()->format('Y-m-d H:i') }} PHT</span>
                    </div>
                    <div class="coord-item">
                        <span class="coord-label">Datasets</span>
                        <span>{{ ($totalShapefiles ?? 0) }} layers</span>
                    </div>
                </div>

                <div class="panel-header">
                    <div>
                        <div class="panel-title">
                            <div class="panel-title-icon">
                                <i class="fas fa-history"></i>
                            </div>
                            Recent Activity
                        </div>
                        <div class="panel-subtitle" style="padding-left:40px;">Latest system activities and events</div>
                    </div>
                </div>

                <div class="activity-scroll">
                    @forelse($recentActivities ?? [] as $activity)
                        <div class="activity-row">
                            <div class="activity-dot">
                                <i class="fas fa-map-pin"></i>
                            </div>
                            <div class="activity-main">
                                <div class="activity-desc">{{ $activity->description ?? $activity->location }}</div>
                                <div class="activity-user">
                                    <i class="fas fa-user" style="font-size:10px; margin-right:4px;"></i>
                                    {{ $activity->user_name }}
                                </div>
                            </div>
                            <div class="activity-meta">
                                <div class="activity-time">
                                    {{ $activity->created_at->diffForHumans() }}
                                </div>
                                <span class="activity-badge" style="background-color: {{ $activity->action_color }};">
                                    {{ $activity->action }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="empty-state">
                            <i class="fas fa-satellite"></i>
                            <strong style="color:var(--text-2); font-size:14px;">No recent activity</strong>
                            <p>System activity will appear here as events occur</p>
                        </div>
                    @endforelse
                </div>

                @if (count($recentActivity ?? []) > 0)
                    <div style="padding: 14px 22px; border-top: 1px solid var(--border); text-align:center;">
                        <a href="#" style="
                            display: inline-flex;
                            align-items: center;
                            gap: 6px;
                            font-size: 13px;
                            font-weight: 600;
                            color: var(--primary);
                            text-decoration: none;
                            padding: 7px 18px;
                            border: 1px solid var(--primary);
                            border-radius: var(--radius-sm);
                            transition: background .15s, color .15s;
                        " onmouseover="this.style.background='var(--primary)';this.style.color='white';"
                           onmouseout="this.style.background='';this.style.color='var(--primary)';">
                            <i class="fas fa-list"></i> View All Activities
                        </a>
                    </div>
                @endif

            </div>
        </div>

    </div>

    @push('scripts')
    <script>
        // Auto-dismiss success alert after 5s
        const alert = document.getElementById('success-alert');
        if (alert) setTimeout(() => alert.remove(), 5000);
    </script>
    @endpush

@endsection