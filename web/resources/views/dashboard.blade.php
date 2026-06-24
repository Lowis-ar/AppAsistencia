<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Panel de Administración — Sistema de Control de Asistencia. Visualiza horas trabajadas, registros y ubicaciones en tiempo real.">
    <title>Dashboard — Control de Asistencia</title>

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* ============================================================
           DISEÑO "ANTIGRAVITY" — Variables y estilos base
           ============================================================ */
        :root {
            --bg-base:         #F7F8FA;
            --bg-card:         #FFFFFF;
            --bg-sidebar:      #FFFFFF;
            --border-subtle:   #E8EAED;
            --text-primary:    #111827;
            --text-secondary:  #6B7280;
            --text-muted:      #9CA3AF;
            --accent:          #4F46E5;        /* Indigo-600 */
            --accent-light:    #EEF2FF;        /* Indigo-50  */
            --accent-mid:      #818CF8;        /* Indigo-400 */
            --success:         #10B981;
            --warning:         #F59E0B;
            --danger:          #EF4444;
            --shadow-float:    0 4px 24px rgba(0,0,0,0.06), 0 1px 4px rgba(0,0,0,0.04);
            --shadow-hover:    0 8px 32px rgba(79,70,229,0.12), 0 2px 8px rgba(0,0,0,0.06);
            --radius-card:     1rem;           /* 16px */
            --radius-pill:     9999px;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg-base);
            color: var(--text-primary);
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
        }

        /* ---- Layout ---- */
        .layout { display: flex; min-height: 100vh; }

        /* ============================================================
           SIDEBAR
           ============================================================ */
        .sidebar {
            width: 72px;
            background: var(--bg-sidebar);
            border-right: 1px solid var(--border-subtle);
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 1.5rem 0;
            position: fixed;
            top: 0; left: 0; bottom: 0;
            z-index: 100;
            transition: width 0.3s cubic-bezier(0.4,0,0.2,1);
            overflow: hidden;
        }

        .sidebar:hover {
            width: 220px;
            box-shadow: var(--shadow-float);
        }

        .sidebar-logo {
            width: 40px; height: 40px;
            background: var(--accent);
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
            margin-bottom: 2rem;
        }

        .sidebar-logo svg { color: white; }

        .sidebar-nav {
            flex: 1;
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 4px;
            padding: 0 12px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 10px;
            border-radius: 10px;
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.2s ease;
            white-space: nowrap;
            overflow: hidden;
        }

        .nav-item:hover {
            background: var(--accent-light);
            color: var(--accent);
        }

        .nav-item.active {
            background: var(--accent-light);
            color: var(--accent);
        }

        .nav-item svg { flex-shrink: 0; }
        .nav-label { opacity: 0; transition: opacity 0.2s ease 0.05s; }
        .sidebar:hover .nav-label { opacity: 1; }

        .sidebar-footer {
            width: 100%;
            padding: 0 12px;
            border-top: 1px solid var(--border-subtle);
            padding-top: 1rem;
        }

        /* ============================================================
           MAIN CONTENT
           ============================================================ */
        .main {
            margin-left: 72px;
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* ---- Topbar ---- */
        .topbar {
            background: var(--bg-card);
            border-bottom: 1px solid var(--border-subtle);
            padding: 0 2rem;
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .topbar-title {
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--text-primary);
            letter-spacing: -0.02em;
        }

        .topbar-title span {
            color: var(--accent);
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .date-badge {
            font-size: 0.8125rem;
            color: var(--text-secondary);
            font-weight: 500;
            background: var(--bg-base);
            padding: 6px 14px;
            border-radius: var(--radius-pill);
            border: 1px solid var(--border-subtle);
        }

        .avatar {
            width: 36px; height: 36px;
            background: var(--accent);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.8125rem;
            font-weight: 700;
            color: white;
            cursor: pointer;
            position: relative;
        }

        /* ---- Content Area ---- */
        .content {
            padding: 2rem;
            flex: 1;
        }

        .page-header {
            margin-bottom: 2rem;
        }

        .page-title {
            font-size: 1.75rem;
            font-weight: 800;
            letter-spacing: -0.03em;
            color: var(--text-primary);
        }

        .page-subtitle {
            font-size: 0.9375rem;
            color: var(--text-secondary);
            margin-top: 0.25rem;
        }

        /* ============================================================
           METRIC CARDS (Tarjetas flotantes)
           ============================================================ */
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.25rem;
            margin-bottom: 2rem;
        }

        .metric-card {
            background: var(--bg-card);
            border-radius: var(--radius-card);
            box-shadow: var(--shadow-float);
            padding: 1.75rem;
            border: 1px solid var(--border-subtle);
            transition: box-shadow 0.25s ease, transform 0.25s ease;
            position: relative;
            overflow: hidden;
        }

        .metric-card:hover {
            box-shadow: var(--shadow-hover);
            transform: translateY(-2px);
        }

        .metric-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--accent), var(--accent-mid));
            border-radius: var(--radius-card) var(--radius-card) 0 0;
        }

        .metric-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
        }

        .metric-icon {
            width: 42px; height: 42px;
            background: var(--accent-light);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            color: var(--accent);
        }

        .metric-period {
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--text-muted);
            background: var(--bg-base);
            padding: 4px 10px;
            border-radius: var(--radius-pill);
        }

        .metric-value {
            font-size: 2.5rem;
            font-weight: 800;
            letter-spacing: -0.04em;
            color: var(--text-primary);
            line-height: 1;
            margin-bottom: 0.25rem;
        }

        .metric-value span {
            font-size: 1rem;
            font-weight: 500;
            color: var(--text-secondary);
            letter-spacing: 0;
        }

        .metric-label {
            font-size: 0.875rem;
            color: var(--text-secondary);
            font-weight: 400;
            margin-bottom: 1.25rem;
        }

        /* Sparkline CSS */
        .sparkline {
            display: flex;
            align-items: flex-end;
            gap: 4px;
            height: 40px;
        }

        .spark-bar {
            flex: 1;
            background: var(--accent-light);
            border-radius: 3px 3px 0 0;
            transition: background 0.2s;
            min-height: 4px;
            position: relative;
        }

        .spark-bar:hover { background: var(--accent); }

        .spark-bar[data-today="true"] {
            background: var(--accent);
        }

        /* Stats Row bajo el sparkline */
        .metric-footer {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 0.75rem;
        }

        .trend-badge {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 0.75rem;
            font-weight: 600;
            padding: 3px 8px;
            border-radius: var(--radius-pill);
        }

        .trend-up   { background: #DCFCE7; color: #16A34A; }
        .trend-down { background: #FEE2E2; color: #DC2626; }

        .metric-footer-text {
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        /* Quick Stats Cards */
        .quick-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .quick-card {
            background: var(--bg-card);
            border-radius: 12px;
            box-shadow: var(--shadow-float);
            padding: 1.25rem;
            border: 1px solid var(--border-subtle);
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .quick-icon {
            width: 36px; height: 36px;
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }

        .quick-icon.green  { background: #DCFCE7; color: #16A34A; }
        .quick-icon.blue   { background: #DBEAFE; color: #2563EB; }
        .quick-icon.orange { background: #FEF3C7; color: #D97706; }

        .quick-num  { font-size: 1.375rem; font-weight: 700; }
        .quick-text { font-size: 0.75rem; color: var(--text-secondary); }

        /* ============================================================
           TABLA DE REGISTROS
           ============================================================ */
        .table-container {
            background: var(--bg-card);
            border-radius: var(--radius-card);
            box-shadow: var(--shadow-float);
            border: 1px solid var(--border-subtle);
            overflow: hidden;
        }

        .table-header {
            padding: 1.5rem 1.75rem;
            border-bottom: 1px solid var(--border-subtle);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .table-title {
            font-size: 1.0625rem;
            font-weight: 700;
            color: var(--text-primary);
        }

        .table-subtitle {
            font-size: 0.8125rem;
            color: var(--text-secondary);
        }

        /* Filtros */
        .filters-bar {
            padding: 1rem 1.75rem;
            border-bottom: 1px solid var(--border-subtle);
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
            align-items: center;
            background: var(--bg-base);
        }

        .filter-input {
            height: 38px;
            border: 1px solid var(--border-subtle);
            border-radius: 8px;
            padding: 0 12px;
            font-family: 'Inter', sans-serif;
            font-size: 0.875rem;
            color: var(--text-primary);
            background: var(--bg-card);
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .filter-input:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(79,70,229,0.1);
        }

        .filter-input::placeholder { color: var(--text-muted); }

        .filter-input.wide { min-width: 220px; flex: 1; }
        .filter-input.date { width: 145px; }
        .filter-input.select-field { padding-right: 28px; cursor: pointer; }

        .btn {
            height: 38px;
            padding: 0 18px;
            border-radius: 8px;
            font-family: 'Inter', sans-serif;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-primary {
            background: var(--accent);
            color: white;
        }

        .btn-primary:hover {
            background: #4338CA;
            box-shadow: 0 4px 12px rgba(79,70,229,0.35);
            transform: translateY(-1px);
        }

        .btn-ghost {
            background: transparent;
            color: var(--text-secondary);
            border: 1px solid var(--border-subtle);
        }

        .btn-ghost:hover {
            background: var(--bg-base);
            color: var(--text-primary);
        }

        /* Tabla */
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table th {
            padding: 12px 20px;
            text-align: left;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            color: var(--text-muted);
            border-bottom: 1px solid var(--border-subtle);
            white-space: nowrap;
        }

        .data-table td {
            padding: 14px 20px;
            font-size: 0.875rem;
            color: var(--text-primary);
            border-bottom: 1px solid var(--border-subtle);
            vertical-align: middle;
        }

        .data-table tr:last-child td { border-bottom: none; }

        .data-table tbody tr {
            transition: background 0.15s;
        }

        .data-table tbody tr:hover {
            background: #FAFBFF;
        }

        /* Celda: usuario */
        .user-cell {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-avatar {
            width: 32px; height: 32px;
            border-radius: 50%;
            background: var(--accent-light);
            color: var(--accent);
            font-size: 0.75rem;
            font-weight: 700;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }

        .user-name  { font-weight: 600; font-size: 0.875rem; }
        .user-email { font-size: 0.75rem; color: var(--text-muted); }

        /* Badge tipo */
        .type-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            border-radius: var(--radius-pill);
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-checkin  { background: #DCFCE7; color: #15803D; }
        .badge-checkout { background: #FEE2E2; color: #B91C1C; }

        /* Botón Google Maps */
        .maps-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 12px;
            border-radius: var(--radius-pill);
            background: var(--accent-light);
            color: var(--accent);
            font-size: 0.75rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
        }

        .maps-btn:hover {
            background: var(--accent);
            color: white;
            transform: translateY(-1px);
        }

        .no-location {
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        /* Paginación */
        .pagination-wrap {
            padding: 1.25rem 1.75rem;
            border-top: 1px solid var(--border-subtle);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .pagination-info {
            font-size: 0.8125rem;
            color: var(--text-secondary);
        }

        /* ============================================================
           ESTADO VACÍO
           ============================================================ */
        .empty-state {
            padding: 4rem 2rem;
            text-align: center;
        }

        .empty-icon {
            width: 64px; height: 64px;
            background: var(--bg-base);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 1rem;
            color: var(--text-muted);
        }

        .empty-title { font-size: 1rem; font-weight: 600; margin-bottom: 0.5rem; }
        .empty-sub   { font-size: 0.875rem; color: var(--text-secondary); }

        /* ============================================================
           RESPONSIVE
           ============================================================ */
        @media (max-width: 768px) {
            .sidebar { display: none; }
            .main    { margin-left: 0; }
            .content { padding: 1.25rem; }
            .metrics-grid { grid-template-columns: 1fr; }
            .topbar  { padding: 0 1rem; }
        }
    </style>
</head>
<body>

<div class="layout">

    <!-- ================================================================
         SIDEBAR
         ================================================================ -->
    <aside class="sidebar">
        <!-- Logo -->
        <div class="sidebar-logo">
            <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>

        <!-- Nav -->
        <nav class="sidebar-nav">
            <a href="{{ route('dashboard') }}" class="nav-item active" id="nav-dashboard">
                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/>
                </svg>
                <span class="nav-label">Dashboard</span>
            </a>

            <a href="#" class="nav-item" id="nav-employees">
                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/>
                </svg>
                <span class="nav-label">Empleados</span>
            </a>

            <a href="#" class="nav-item" id="nav-reports">
                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 14.25v2.25m3-4.5v4.5m3-6.75v6.75m3-9v9M6 20.25h12A2.25 2.25 0 0020.25 18V6A2.25 2.25 0 0018 3.75H6A2.25 2.25 0 003.75 6v12A2.25 2.25 0 006 20.25z"/>
                </svg>
                <span class="nav-label">Reportes</span>
            </a>

            <a href="#" class="nav-item" id="nav-qrcodes">
                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75h.75v.75h-.75v-.75zM6.75 16.5h.75v.75h-.75v-.75zM16.5 6.75h.75v.75h-.75v-.75zM13.5 13.5h.75v.75h-.75v-.75zM13.5 19.5h.75v.75h-.75v-.75zM19.5 13.5h.75v.75h-.75v-.75zM19.5 19.5h.75v.75h-.75v-.75zM16.5 16.5h.75v.75h-.75v-.75z"/>
                </svg>
                <span class="nav-label">Códigos QR</span>
            </a>

            <a href="#" class="nav-item" id="nav-settings">
                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 010 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 010-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <span class="nav-label">Configuración</span>
            </a>
        </nav>

        <!-- Footer del sidebar: logout -->
        <div class="sidebar-footer">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="nav-item" style="width:100%; background:none; border:none; cursor:pointer;" id="btn-logout">
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="color:#EF4444">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75"/>
                    </svg>
                    <span class="nav-label" style="color:#EF4444;font-weight:600;">Cerrar Sesión</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- ================================================================
         MAIN
         ================================================================ -->
    <div class="main">

        <!-- Topbar -->
        <header class="topbar">
            <div class="topbar-title">
                Control de <span>Asistencia</span>
            </div>
            <div class="topbar-right">
                <div class="date-badge" id="live-date">
                    {{ now()->isoFormat('dddd, D [de] MMMM YYYY') }}
                </div>
                <div class="avatar" title="{{ Auth::user()->name }}">
                    {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                </div>
            </div>
        </header>

        <!-- Content -->
        <main class="content">

            <!-- Encabezado de página -->
            <div class="page-header">
                <h1 class="page-title">Dashboard</h1>
                <p class="page-subtitle">Bienvenido, {{ Auth::user()->name }}. Aquí tienes un resumen de hoy.</p>
            </div>

            <!-- ========================================================
                 QUICK STATS
                 ======================================================== -->
            <div class="quick-stats">
                <div class="quick-card">
                    <div class="quick-icon green">
                        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="quick-num">{{ $totalEmployees }}</div>
                        <div class="quick-text">Total empleados</div>
                    </div>
                </div>

                <div class="quick-card">
                    <div class="quick-icon blue">
                        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="quick-num">{{ $todayCheckins }}</div>
                        <div class="quick-text">Entradas hoy</div>
                    </div>
                </div>

                <div class="quick-card">
                    <div class="quick-icon orange">
                        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="quick-num">{{ $hoursToday }}<span style="font-size:0.75rem;font-weight:500">h</span></div>
                        <div class="quick-text">Horas hoy</div>
                    </div>
                </div>
            </div>

            <!-- ========================================================
                 METRIC CARDS
                 ======================================================== -->
            <div class="metrics-grid">

                {{-- Hoy --}}
                <div class="metric-card">
                    <div class="metric-header">
                        <div class="metric-icon">
                            <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <span class="metric-period">Hoy</span>
                    </div>
                    <div class="metric-value">{{ $hoursToday }}<span> hrs</span></div>
                    <div class="metric-label">Horas trabajadas hoy en total</div>
                    <div class="sparkline" id="sparkline-today">
                        @foreach($sparklineData as $i => $day)
                            @php $maxH = collect($sparklineData)->max('hours'); $pct = $maxH > 0 ? ($day['hours'] / $maxH * 100) : 0; @endphp
                            <div class="spark-bar"
                                 style="height: {{ max(6, $pct) }}%;"
                                 data-today="{{ $loop->last ? 'true' : 'false' }}"
                                 title="{{ $day['date'] }}: {{ $day['hours'] }}h">
                            </div>
                        @endforeach
                    </div>
                    <div class="metric-footer">
                        <span class="trend-badge trend-up">↑ Activo</span>
                        <span class="metric-footer-text">{{ now()->format('d M Y') }}</span>
                    </div>
                </div>

                {{-- Semana --}}
                <div class="metric-card">
                    <div class="metric-header">
                        <div class="metric-icon">
                            <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/>
                            </svg>
                        </div>
                        <span class="metric-period">Semana</span>
                    </div>
                    <div class="metric-value">{{ $hoursWeek }}<span> hrs</span></div>
                    <div class="metric-label">Horas trabajadas esta semana</div>
                    @php
                        $weekDays = array_slice($sparklineData, -7);
                        $weekMax  = collect($weekDays)->max('hours');
                    @endphp
                    <div class="sparkline">
                        @foreach($weekDays as $day)
                            @php $pct = $weekMax > 0 ? ($day['hours'] / $weekMax * 100) : 0; @endphp
                            <div class="spark-bar"
                                 style="height: {{ max(6, $pct) }}%; background: #818CF8;"
                                 title="{{ $day['date'] }}: {{ $day['hours'] }}h">
                            </div>
                        @endforeach
                    </div>
                    <div class="metric-footer">
                        <span class="metric-footer-text">Lun {{ now()->startOfWeek()->format('d/m') }} — Hoy</span>
                    </div>
                </div>

                {{-- Mes --}}
                <div class="metric-card">
                    <div class="metric-header">
                        <div class="metric-icon">
                            <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/>
                            </svg>
                        </div>
                        <span class="metric-period">{{ now()->isoFormat('MMMM') }}</span>
                    </div>
                    <div class="metric-value">{{ $hoursMonth }}<span> hrs</span></div>
                    <div class="metric-label">Horas trabajadas este mes</div>
                    <div class="sparkline">
                        @foreach($sparklineData as $day)
                            @php
                                $maxH = collect($sparklineData)->max('hours');
                                $pct  = $maxH > 0 ? ($day['hours'] / $maxH * 100) : 0;
                            @endphp
                            <div class="spark-bar"
                                 style="height: {{ max(6, $pct) }}%; background: #A5B4FC;"
                                 title="{{ $day['date'] }}: {{ $day['hours'] }}h">
                            </div>
                        @endforeach
                    </div>
                    <div class="metric-footer">
                        <span class="metric-footer-text">Del 1 al {{ now()->format('d') }} de {{ now()->isoFormat('MMMM') }}</span>
                    </div>
                </div>

            </div>

            <!-- ========================================================
                 TABLA DE REGISTROS
                 ======================================================== -->
            <div class="table-container">

                <!-- Encabezado -->
                <div class="table-header">
                    <div>
                        <div class="table-title">Registros de Asistencia</div>
                        <div class="table-subtitle">
                            Mostrando {{ $attendances->firstItem() ?? 0 }}–{{ $attendances->lastItem() ?? 0 }}
                            de {{ $attendances->total() }} registros
                        </div>
                    </div>
                </div>

                <!-- Barra de Filtros -->
                <form method="GET" action="{{ route('dashboard') }}" id="filter-form">
                    <div class="filters-bar">
                        <!-- Buscador -->
                        <input
                            type="text"
                            name="search"
                            class="filter-input wide"
                            placeholder="🔍  Buscar por nombre o ID..."
                            value="{{ request('search') }}"
                            id="input-search"
                        >

                        <!-- Desde -->
                        <input
                            type="date"
                            name="date_from"
                            class="filter-input date"
                            value="{{ request('date_from') }}"
                            id="input-date-from"
                            title="Desde"
                        >

                        <!-- Hasta -->
                        <input
                            type="date"
                            name="date_to"
                            class="filter-input date"
                            value="{{ request('date_to') }}"
                            id="input-date-to"
                            title="Hasta"
                        >

                        <!-- Zona residencial -->
                        <select name="zone" class="filter-input select-field" id="select-zone">
                            <option value="">Todas las zonas</option>
                            @foreach($zones as $zone)
                                <option value="{{ $zone }}" {{ request('zone') === $zone ? 'selected' : '' }}>
                                    {{ $zone }}
                                </option>
                            @endforeach
                        </select>

                        <!-- Tipo -->
                        <select name="type" class="filter-input select-field" id="select-type">
                            <option value="">Entrada y Salida</option>
                            <option value="check_in"  {{ request('type') === 'check_in'  ? 'selected' : '' }}>Solo Entradas</option>
                            <option value="check_out" {{ request('type') === 'check_out' ? 'selected' : '' }}>Solo Salidas</option>
                        </select>

                        <button type="submit" class="btn btn-primary" id="btn-filter">
                            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                            </svg>
                            Filtrar
                        </button>

                        @if(request()->hasAny(['search','date_from','date_to','zone','type']))
                            <a href="{{ route('dashboard') }}" class="btn btn-ghost" id="btn-clear-filters">Limpiar</a>
                        @endif
                    </div>
                </form>

                <!-- Tabla -->
                <div style="overflow-x: auto;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>EMPLEADO</th>
                                <th>TIPO</th>
                                <th>FECHA</th>
                                <th>HORA</th>
                                <th>DEPARTAMENTO</th>
                                <th>ZONA</th>
                                <th>UBICACIÓN</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($attendances as $record)
                                <tr>
                                    <!-- Empleado -->
                                    <td>
                                        <div class="user-cell">
                                            <div class="user-avatar">
                                                {{ strtoupper(substr($record->user->name ?? '?', 0, 2)) }}
                                            </div>
                                            <div>
                                                <div class="user-name">{{ $record->user->name ?? 'Sin nombre' }}</div>
                                                <div class="user-email">#{{ $record->user->id }}</div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Tipo -->
                                    <td>
                                        @if($record->type === 'check_in')
                                            <span class="type-badge badge-checkin">
                                                <svg width="10" height="10" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.707l-3-3a1 1 0 00-1.414 1.414L10.586 9H7a1 1 0 100 2h3.586l-1.293 1.293a1 1 0 101.414 1.414l3-3a1 1 0 000-1.414z" clip-rule="evenodd"/>
                                                </svg>
                                                Entrada
                                            </span>
                                        @else
                                            <span class="type-badge badge-checkout">
                                                <svg width="10" height="10" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm-3.707-8.707l3 3a1 1 0 001.414-1.414L9.414 9H13a1 1 0 100-2H9.414l1.293-1.293a1 1 0 00-1.414-1.414l-3 3a1 1 0 000 1.414z" clip-rule="evenodd"/>
                                                </svg>
                                                Salida
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Fecha -->
                                    <td style="font-variant-numeric: tabular-nums;">
                                        {{ $record->date->format('d/m/Y') }}
                                    </td>

                                    <!-- Hora -->
                                    <td style="font-variant-numeric: tabular-nums; font-weight: 600;">
                                        {{ \Carbon\Carbon::parse($record->time)->format('H:i') }}
                                    </td>

                                    <!-- Departamento -->
                                    <td>{{ $record->user->department->name ?? '—' }}</td>

                                    <!-- Zona -->
                                    <td>{{ $record->user->residential_zone ?? '—' }}</td>

                                    <!-- Ubicación: Google Maps -->
                                    <td>
                                        @if($record->latitude && $record->longitude)
                                            <a
                                                href="https://maps.google.com/?q={{ $record->latitude }},{{ $record->longitude }}"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="maps-btn"
                                                id="maps-link-{{ $record->id }}"
                                            >
                                                <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
                                                </svg>
                                                Ver mapa
                                            </a>
                                        @else
                                            <span class="no-location">Sin ubicación</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7">
                                        <div class="empty-state">
                                            <div class="empty-icon">
                                                <svg width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                                                </svg>
                                            </div>
                                            <div class="empty-title">No se encontraron registros</div>
                                            <div class="empty-sub">Intenta ajustar los filtros de búsqueda.</div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                @if($attendances->hasPages())
                    <div class="pagination-wrap">
                        <div class="pagination-info">
                            Mostrando {{ $attendances->firstItem() }} al {{ $attendances->lastItem() }}
                            de {{ $attendances->total() }} registros
                        </div>
                        <div>
                            {{-- Paginación de Laravel con estilos custom --}}
                            @php
                                $links = $attendances->links();
                            @endphp
                            {{ $attendances->links() }}
                        </div>
                    </div>
                @endif

            </div>
            <!-- /table-container -->

        </main>
        <!-- /content -->

    </div>
    <!-- /main -->

</div>
<!-- /layout -->

</body>
</html>
