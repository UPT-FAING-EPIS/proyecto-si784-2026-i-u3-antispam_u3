<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Aegis Filter – Panel de administración">
    <title>@yield('title', 'Admin') | Aegis Filter</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg:          #0a0e1a;
            --bg-card:     #111827;
            --bg-row:      #0f172a;
            --bg-row-alt:  #111827;
            --border:      #1f2d45;
            --primary:     #6366f1;
            --accent:      #06b6d4;
            --success:     #10b981;
            --warning:     #f59e0b;
            --danger:      #ef4444;
            --muted:       #64748b;
            --sub:         #94a3b8;
            --text:        #f1f5f9;
            --font:        'Inter', system-ui, sans-serif;
            --radius:      10px;
            --transition:  all 0.2s ease;
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: var(--font);
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            background-image:
                radial-gradient(ellipse at 10% 0%, rgba(99,102,241,0.07) 0%, transparent 50%),
                radial-gradient(ellipse at 90% 100%, rgba(6,182,212,0.05) 0%, transparent 50%);
        }

        /* ─── Header ─────────────────────────────────── */
        .header {
            padding: 1.25rem 2rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 1rem;
            background: rgba(17,24,39,0.85);
            backdrop-filter: blur(10px);
            position: sticky; top: 0; z-index: 50;
            flex-wrap: wrap;
        }
        .logo { display:flex; align-items:center; gap:.75rem; text-decoration:none; }
        .logo-icon {
            width:38px; height:38px;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            border-radius: 8px; display:flex; align-items:center; justify-content:center; font-size:1.1rem;
        }
        .logo-title {
            font-size:1.1rem; font-weight:700;
            background: linear-gradient(90deg, var(--primary), var(--accent));
            -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
        }
        .logo-sub { font-size:.68rem; color:var(--muted); display:block; margin-top:-3px; }
        .nav-links { display:flex; gap:.4rem; align-items:center; flex-wrap:wrap; }
        .nav-link {
            padding:.4rem .8rem; border-radius:var(--radius); font-size:.82rem; font-weight:500;
            color:var(--sub); text-decoration:none; transition:var(--transition); border:1px solid transparent;
        }
        .nav-link:hover { color:var(--text); background:rgba(99,102,241,.08); }
        .nav-link.active { color:var(--text); background:rgba(99,102,241,.12); border-color:rgba(99,102,241,.3); }
        .header-actions { margin-left:auto; display:flex; gap:.75rem; align-items:center; }
        .btn { padding:.45rem 1rem; border-radius:var(--radius); font-family:var(--font); font-size:.84rem; font-weight:500; cursor:pointer; transition:var(--transition); text-decoration:none; display:inline-flex; align-items:center; gap:.4rem; border:none; }
        .btn-ghost { background:transparent; color:var(--sub); border:1px solid var(--border); }
        .btn-ghost:hover { color:var(--text); background:rgba(99,102,241,.08); border-color:rgba(99,102,241,.3); }
        .btn-primary { background:linear-gradient(135deg,var(--primary),#4f46e5); color:#fff; }
        .btn-primary:hover { box-shadow:0 4px 15px rgba(99,102,241,.35); transform:translateY(-1px); }
        .btn-sm { padding:.3rem .7rem; font-size:.78rem; }
        .btn-success { background:rgba(16,185,129,.15); color:#34d399; border:1px solid rgba(16,185,129,.25); }
        .btn-success:hover { background:rgba(16,185,129,.25); }
        .btn-danger  { background:rgba(239,68,68,.1); color:#f87171; border:1px solid rgba(239,68,68,.2); }
        .btn-danger:hover  { background:rgba(239,68,68,.2); }

        /* ─── Main ───────────────────────────────────── */
        .container { max-width:1280px; margin:0 auto; padding:2rem 1.5rem; }

        /* ─── Page Title ─────────────────────────────── */
        .page-title { font-size:1.6rem; font-weight:800; margin-bottom:.4rem; }
        .page-subtitle { color:var(--muted); font-size:.9rem; margin-bottom:2rem; }

        /* ─── Stats Grid ─────────────────────────────── */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 1.25rem 1.5rem;
            position: relative;
            overflow: hidden;
            transition: var(--transition);
        }
        .stat-card:hover { border-color: rgba(99,102,241,.3); transform:translateY(-2px); }
        .stat-card::before {
            content:''; position:absolute; top:0; left:0; right:0; height:2px;
            background: linear-gradient(90deg, var(--primary), var(--accent));
        }
        .stat-icon { font-size:1.5rem; margin-bottom:.6rem; }
        .stat-value { font-size:2rem; font-weight:800; line-height:1; margin-bottom:.25rem; color: var(--primary); }
        .stat-label { font-size:.8rem; color:var(--muted); font-weight:500; text-transform:uppercase; letter-spacing:.04em; }

        /* ─── Card / Form ─────────────────────────────── */
        .card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .card-header {
            display:flex; align-items:center; justify-content:space-between;
            margin-bottom: 1rem;
        }
        .card-title { font-size:.95rem; font-weight:600; }
        label { display:block; font-size:.82rem; color:var(--sub); margin-bottom:.35rem; font-weight:500; }
        input[type="text"], input[type="email"], input[type="password"], input[type="number"], select, textarea {
            width:100%; padding:.6rem .85rem; border-radius:var(--radius);
            border:1px solid var(--border); background:var(--bg-row);
            color:var(--text); font-family:var(--font); font-size:.88rem;
        }
        input:focus, select:focus, textarea:focus { outline:none; border-color:var(--primary); }
        .form-group { margin-bottom:1rem; }
        .form-row { display:flex; gap:1rem; flex-wrap:wrap; }
        .form-row .form-group { flex:1; min-width:200px; }
        .error-text { color:#f87171; font-size:.78rem; margin-top:.35rem; }

        /* ─── Table ─────────────────────────────────── */
        .table-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            overflow: hidden;
        }
        .table-header {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--border);
            display: flex; align-items: center; justify-content: space-between;
        }
        .table-title { font-size:.95rem; font-weight:600; }
        .table-count { font-size:.8rem; color:var(--muted); }
        table { width:100%; border-collapse:collapse; }
        thead tr { background: rgba(255,255,255,.02); }
        th {
            padding:.75rem 1rem; text-align:left;
            font-size:.76rem; font-weight:600; color:var(--muted);
            text-transform:uppercase; letter-spacing:.06em;
            border-bottom: 1px solid var(--border);
            white-space: nowrap;
        }
        td {
            padding:.85rem 1rem; font-size:.875rem;
            border-bottom: 1px solid rgba(31,45,69,.5);
            vertical-align: middle;
        }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: rgba(99,102,241,.03); }

        /* ─── Badges ─────────────────────────────────── */
        .badge {
            display:inline-flex; align-items:center; gap:.35rem;
            padding:.28rem .75rem; border-radius:999px;
            font-size:.76rem; font-weight:600; white-space:nowrap;
        }
        .badge-approved, .badge-active { background:rgba(16,185,129,.12); color:#34d399; border:1px solid rgba(16,185,129,.25); }
        .badge-spam, .badge-inactive    { background:rgba(239,68,68,.12);  color:#f87171; border:1px solid rgba(239,68,68,.25); }
        .badge-pending  { background:rgba(245,158,11,.12); color:#fbbf24; border:1px solid rgba(245,158,11,.25); }
        .badge-channel  { background:rgba(99,102,241,.12); color:#a5b4fc; border:1px solid rgba(99,102,241,.25); }

        /* ─── Empty State ────────────────────────────── */
        .empty-state {
            text-align:center; padding:4rem 2rem;
            color:var(--muted);
        }
        .empty-state .icon { font-size:3rem; margin-bottom:1rem; display:block; opacity:.5; }
        .empty-state p { font-size:.95rem; }

        /* ─── Alert ──────────────────────────────────── */
        .alert { padding:.9rem 1.2rem; border-radius:var(--radius); margin-bottom:1.25rem; font-size:.88rem; font-weight:500; border:1px solid; animation:slideIn .3s ease; display:flex; align-items:center; gap:.6rem; }
        @keyframes slideIn { from{opacity:0;transform:translateY(-8px)} to{opacity:1;transform:translateY(0)} }
        .alert-success { background:rgba(16,185,129,.07); border-color:rgba(16,185,129,.25); color:#34d399; }
        .alert-danger  { background:rgba(239,68,68,.07);  border-color:rgba(239,68,68,.25);  color:#f87171; }
        .alert-warning { background:rgba(245,158,11,.07); border-color:rgba(245,158,11,.25); color:#fbbf24; }

        /* ─── Pagination ─────────────────────────────── */
        .pagination-wrap { padding:1.25rem 1.5rem; border-top:1px solid var(--border); }
        .pagination-wrap nav { display:flex; gap:.4rem; align-items:center; flex-wrap:wrap; }
        .pagination-wrap .page-link {
            padding:.35rem .75rem; border-radius:6px; font-size:.82rem;
            color:var(--sub); text-decoration:none; border:1px solid var(--border);
            transition:var(--transition);
        }
        .pagination-wrap .page-link:hover { color:var(--text); border-color:var(--primary); }
        .pagination-wrap .page-link.active { background:var(--primary); color:#fff; border-color:var(--primary); }

        /* ─── Footer ─────────────────────────────────── */
        .footer { text-align:center; padding:1.5rem; color:var(--muted); font-size:.78rem; border-top:1px solid var(--border); margin-top:2rem; }
        .footer a { color:var(--primary); text-decoration:none; }

        @media(max-width:768px){
            .container{padding:1rem;}
        }

        @yield('extra-style')
    </style>
</head>
<body>

    @auth
    <header class="header">
        <a href="{{ route('dashboard') }}" class="logo">
            <div class="logo-icon">🛡️</div>
            <div>
                <span class="logo-title">Aegis Filter</span>
                <span class="logo-sub">Panel de Administración</span>
            </div>
        </a>
        <nav class="nav-links" aria-label="Navegación de administración">
            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">💬 Comentarios</a>
            <a href="{{ route('admin.blacklist.index') }}" class="nav-link {{ request()->routeIs('admin.blacklist.*') ? 'active' : '' }}">⛔ Blacklist</a>
            <a href="{{ route('admin.settings.edit') }}" class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">⚙️ Settings</a>
            <a href="{{ route('admin.integration-keys.index') }}" class="nav-link {{ request()->routeIs('admin.integration-keys.*') ? 'active' : '' }}">🔑 Keys</a>
            <a href="{{ route('admin.audit-log.index') }}" class="nav-link {{ request()->routeIs('admin.audit-log.index') ? 'active' : '' }}">📋 Audit Log</a>
            <a href="{{ route('admin.audit-log.metrics') }}" class="nav-link {{ request()->routeIs('admin.audit-log.metrics') ? 'active' : '' }}">📈 Métricas</a>
        </nav>
        <div class="header-actions">
            <form action="{{ route('logout') }}" method="POST" style="display:inline;">
                @csrf
                <button type="submit" class="btn btn-ghost">🚪 Salir</button>
            </form>
        </div>
    </header>
    @endauth

    <main class="container" role="main">
        @if(session('success'))
            <div class="alert alert-success" role="alert">✅ {{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger" role="alert">❌ {{ session('error') }}</div>
        @endif
        @if(session('warning'))
            <div class="alert alert-warning" role="alert">⚠️ {{ session('warning') }}</div>
        @endif

        @yield('content')
    </main>

    <footer class="footer">
        <p>
            <strong>Aegis Filter</strong> – Sistema Antispam &middot;
            SI784 Calidad y Pruebas &copy; {{ date('Y') }}
        </p>
    </footer>

</body>
</html>
