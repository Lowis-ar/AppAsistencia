<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Panel de Administración — Códigos QR">
    <title>Códigos QR — Control de Asistencia</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --bg-base:         #F7F8FA;
            --bg-card:         #FFFFFF;
            --bg-sidebar:      #FFFFFF;
            --border-subtle:   #E8EAED;
            --text-primary:    #111827;
            --text-secondary:  #6B7280;
            --text-muted:      #9CA3AF;
            --accent:          #4F46E5;
            --accent-light:    #EEF2FF;
            --shadow-float:    0 4px 24px rgba(0,0,0,0.06), 0 1px 4px rgba(0,0,0,0.04);
            --shadow-hover:    0 8px 32px rgba(79,70,229,0.12), 0 2px 8px rgba(0,0,0,0.06);
            --radius-card:     1rem;
            --radius-pill:     9999px;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-base);
            color: var(--text-primary);
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
        }

        /* Layout Reutilizado */
        .layout { display: flex; min-height: 100vh; }
        
        .sidebar {
            width: 72px;
            background: var(--bg-sidebar);
            border-right: 1px solid var(--border-subtle);
            display: flex; flex-direction: column; align-items: center;
            padding: 1.5rem 0; position: fixed; top: 0; left: 0; bottom: 0;
            z-index: 100; transition: width 0.3s cubic-bezier(0.4,0,0.2,1);
            overflow: hidden;
        }
        .sidebar:hover { width: 220px; box-shadow: var(--shadow-float); }
        .sidebar-logo {
            width: 40px; height: 40px; background: var(--accent); border-radius: 12px;
            display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-bottom: 2rem;
        }
        .sidebar-logo svg { color: white; }
        .sidebar-nav { flex: 1; width: 100%; display: flex; flex-direction: column; gap: 4px; padding: 0 12px; }
        .nav-item {
            display: flex; align-items: center; gap: 12px; padding: 10px 10px; border-radius: 10px;
            color: var(--text-secondary); text-decoration: none; font-size: 0.875rem; font-weight: 500;
            transition: all 0.2s ease; white-space: nowrap; overflow: hidden;
        }
        .nav-item:hover, .nav-item.active { background: var(--accent-light); color: var(--accent); }
        .nav-item svg { flex-shrink: 0; }
        .nav-label { opacity: 0; transition: opacity 0.2s ease 0.05s; }
        .sidebar:hover .nav-label { opacity: 1; }

        .main { margin-left: 72px; flex: 1; display: flex; flex-direction: column; min-height: 100vh; }
        .topbar {
            background: var(--bg-card); border-bottom: 1px solid var(--border-subtle);
            padding: 0 2rem; height: 64px; display: flex; align-items: center; justify-content: space-between;
            position: sticky; top: 0; z-index: 50;
        }
        .topbar-title { font-size: 1.125rem; font-weight: 700; color: var(--text-primary); letter-spacing: -0.02em; }
        .topbar-title span { color: var(--accent); }

        .content { padding: 2rem; flex: 1; }
        .page-header { margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center; }
        .page-title { font-size: 1.75rem; font-weight: 800; letter-spacing: -0.03em; }
        .page-subtitle { font-size: 0.9375rem; color: var(--text-secondary); margin-top: 0.25rem; }

        /* Table Styles */
        .table-container { background: var(--bg-card); border-radius: var(--radius-card); padding: 1rem 0; box-shadow: var(--shadow-float); border: 1px solid var(--border-subtle); overflow-x: auto; }
        .data-table { width: 100%; border-collapse: separate; border-spacing: 0; text-align: left; }
        .data-table th { color: var(--text-secondary); font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; padding: 1rem 2rem; border-bottom: 1px solid var(--border-subtle); white-space: nowrap; }
        .data-table td { padding: 1rem 2rem; border-bottom: 1px solid var(--border-subtle); vertical-align: middle; color: var(--text-primary); font-size: 0.875rem; }
        .data-table tr:last-child td { border-bottom: none; }
        
        .user-cell { display: flex; align-items: center; gap: 12px; }
        .user-avatar { width: 36px; height: 36px; border-radius: 50%; background: var(--accent-light); color: var(--accent); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.875rem; flex-shrink: 0; }
        .user-name { font-weight: 600; color: var(--text-primary); }
        .user-email { color: var(--text-secondary); font-size: 0.75rem; }

        .btn-view {
            padding: 0.5rem 1rem; background: var(--accent-light); color: var(--accent);
            border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: background 0.2s;
            font-size: 0.8125rem; display: inline-flex; align-items: center; gap: 6px;
        }
        .btn-view:hover { background: #E0E7FF; }

        /* Modal Styles */
        .modal-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(17, 24, 39, 0.4); backdrop-filter: blur(4px);
            display: none; align-items: center; justify-content: center; z-index: 1000;
            opacity: 0; transition: opacity 0.2s ease;
        }
        .modal-overlay.active { display: flex; opacity: 1; }
        .modal-content {
            background: var(--bg-card); border-radius: var(--radius-card);
            padding: 2.5rem; max-width: 340px; width: 100%; position: relative;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            transform: translateY(20px) scale(0.95); transition: all 0.2s ease;
        }
        .modal-overlay.active .modal-content { transform: translateY(0) scale(1); }
        .modal-close {
            position: absolute; top: 1rem; right: 1rem; background: transparent; border: none;
            color: var(--text-muted); cursor: pointer; transition: color 0.2s; padding: 4px;
        }
        .modal-close:hover { color: var(--text-primary); }
        
        .modal-body { text-align: center; }
        .qr-image-wrap {
            background: white; padding: 16px; border-radius: 16px; border: 1px solid #E5E7EB;
            margin-bottom: 1.5rem; display: inline-flex; align-items: center; justify-content: center;
        }
        .qr-name { font-weight: 700; font-size: 1.125rem; color: var(--text-primary); margin-bottom: 0.25rem; }
        .qr-email { font-size: 0.8125rem; color: var(--text-secondary); margin-bottom: 1rem; }
        .qr-badge {
            display: inline-flex; align-items: center; padding: 4px 10px; border-radius: var(--radius-pill);
            font-size: 0.75rem; font-weight: 600; background: var(--accent-light); color: var(--accent);
            letter-spacing: 0.05em; margin-bottom: 1.5rem;
        }

        .btn-print {
            height: 38px; padding: 0 18px; border-radius: 8px; font-family: 'Inter', sans-serif;
            font-size: 0.875rem; font-weight: 600; cursor: pointer; border: none; width: 100%;
            background: var(--bg-card); color: var(--text-primary); border: 1px solid var(--border-subtle);
            display: inline-flex; align-items: center; justify-content: center; gap: 8px; transition: all 0.2s;
        }
        .btn-print:hover { background: var(--accent-light); border-color: var(--accent); color: var(--accent); }

        @media print {
            body * { visibility: hidden; }
            #qr-modal, #qr-modal * { visibility: visible; }
            #qr-modal {
                position: absolute; left: 0; top: 0; width: 100%; height: 100%;
                background: white; display: flex; align-items: flex-start; justify-content: center; padding-top: 2rem;
            }
            .modal-content { box-shadow: none; border: none; max-width: 100%; }
            .modal-close, .btn-print { display: none !important; }
        }
    </style>
</head>
<body>

<div class="layout">
    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="sidebar-logo">
            <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>

        <nav class="sidebar-nav">
            <a href="{{ route('dashboard') }}" class="nav-item">
                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/>
                </svg>
                <span class="nav-label">Dashboard</span>
            </a>
            
            <a href="{{ route('qrcodes') }}" class="nav-item active">
                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75h.75v.75h-.75v-.75zM6.75 16.5h.75v.75h-.75v-.75zM16.5 6.75h.75v.75h-.75v-.75zM13.5 13.5h.75v.75h-.75v-.75zM13.5 19.5h.75v.75h-.75v-.75zM19.5 13.5h.75v.75h-.75v-.75zM19.5 19.5h.75v.75h-.75v-.75zM16.5 16.5h.75v.75h-.75v-.75z"/>
                </svg>
                <span class="nav-label">Códigos QR</span>
            </a>
        </nav>
    </aside>

    <!-- MAIN -->
    <div class="main">
        <header class="topbar">
            <div class="topbar-title">Control de <span>Asistencia</span></div>
        </header>

        <main class="content">
            <div class="page-header">
                <div>
                    <h1 class="page-title">Directorio de Empleados</h1>
                    <p class="page-subtitle">Visualiza y genera el código QR individual para el registro en la app móvil.</p>
                </div>
            </div>

            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>EMPLEADO</th>
                            <th>ID</th>
                            <th>DEPARTAMENTO</th>
                            <th>ACCIÓN</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($employees as $employee)
                            @php
                                $qrHtml = (string) QrCode::size(160)->color(17, 24, 39)->generate('USER_QR_' . $employee->id);
                            @endphp
                            <tr>
                                <td>
                                    <div class="user-cell">
                                        <div class="user-avatar">
                                            {{ strtoupper(substr($employee->name ?? '?', 0, 2)) }}
                                        </div>
                                        <div>
                                            <div class="user-name">{{ $employee->name }}</div>
                                            <div class="user-email">{{ $employee->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td style="font-variant-numeric: tabular-nums;">
                                    #{{ $employee->id }}
                                </td>
                                <td>
                                    {{ $employee->department->name ?? 'Sin asignar' }}
                                </td>
                                <td>
                                    <button class="btn-view" onclick="openQrModal('{{ $employee->name }}', '{{ $employee->email }}', '{{ $employee->id }}', '{{ base64_encode($qrHtml) }}')">
                                        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z"/>
                                        </svg>
                                        Ver QR
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</div>

<!-- Modal global para QR -->
<div class="modal-overlay" id="qr-modal">
    <div class="modal-content">
        <button class="modal-close" onclick="closeQrModal()">
            <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
        <div class="modal-body">
            <div class="qr-image-wrap" id="modal-qr-container">
                <!-- SVG inyectado por JS -->
            </div>
            <div class="qr-name" id="modal-name">Nombre</div>
            <div class="qr-email" id="modal-email">Email</div>
            <div class="qr-badge" id="modal-id">ID: #0</div>
            
            <button class="btn-print" onclick="window.print()">
                <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 15.75h10.5m-10.5 0v3.75c0 .414.336.75.75.75h9c.414 0 .75-.336.75-.75v-3.75m-10.5 0H5.25a2.25 2.25 0 01-2.25-2.25v-6a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v6a2.25 2.25 0 01-2.25 2.25h-1.5m-10.5-6h10.5m-10.5 0V3.75c0-.414.336-.75.75-.75h9c.414 0 .75.336.75.75v3.75"/>
                </svg>
                Imprimir QR
            </button>
        </div>
    </div>
</div>

<script>
    function openQrModal(name, email, id, qrBase64) {
        document.getElementById('modal-name').textContent = name;
        document.getElementById('modal-email').textContent = email;
        document.getElementById('modal-id').textContent = 'ID: #' + id;
        
        // Decodificamos el SVG generado y lo insertamos
        const svgContent = atob(qrBase64);
        document.getElementById('modal-qr-container').innerHTML = svgContent;

        const modal = document.getElementById('qr-modal');
        modal.classList.add('active');
    }

    function closeQrModal() {
        document.getElementById('qr-modal').classList.remove('active');
    }

    // Cerrar al hacer click fuera del contenido
    document.getElementById('qr-modal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeQrModal();
        }
    });
    
    // Cerrar con Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeQrModal();
        }
    });
</script>

</body>
</html>
