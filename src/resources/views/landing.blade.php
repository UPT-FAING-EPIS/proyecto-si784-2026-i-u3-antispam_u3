<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Aegis Filter – Motor antispam multi-canal para foros, Telegram, Alexa y WordPress">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Aegis Filter – Antispam Multi-canal</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --color-bg:          #0a0e1a;
            --color-bg-card:     #111827;
            --color-bg-input:    #1a2235;
            --color-border:      #1f2d45;
            --color-border-focus:#6366f1;
            --color-primary:     #6366f1;
            --color-primary-dark:#4f46e5;
            --color-accent:      #06b6d4;
            --color-success:     #10b981;
            --color-warning:     #f59e0b;
            --color-error:       #ef4444;
            --color-text:        #f1f5f9;
            --color-text-muted:  #64748b;
            --color-text-sub:    #94a3b8;
            --font-sans:         'Inter', system-ui, sans-serif;
            --radius-sm:         8px;
            --radius-md:         12px;
            --radius-lg:         20px;
            --shadow-glow:       0 0 40px rgba(99,102,241,0.15);
            --transition:        all 0.25s cubic-bezier(0.4,0,0.2,1);
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html { scroll-behavior: smooth; }
        body {
            font-family: var(--font-sans);
            background-color: var(--color-bg);
            color: var(--color-text);
            min-height: 100vh;
            background-image:
                radial-gradient(ellipse at 20% 10%, rgba(99,102,241,0.08) 0%, transparent 60%),
                radial-gradient(ellipse at 80% 90%, rgba(6,182,212,0.06) 0%, transparent 60%);
        }

        .header {
            padding: 1.5rem 2rem;
            border-bottom: 1px solid var(--color-border);
            display: flex;
            align-items: center;
            gap: 1rem;
            backdrop-filter: blur(10px);
            background: rgba(17,24,39,0.8);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .header-logo { display:flex; align-items:center; gap:.75rem; text-decoration:none; color:var(--color-text); }
        .logo-icon {
            width:40px; height:40px;
            background: linear-gradient(135deg, var(--color-primary), var(--color-accent));
            border-radius: var(--radius-sm); display:flex; align-items:center; justify-content:center; font-size:1.2rem;
        }
        .logo-text {
            font-size:1.25rem; font-weight:700;
            background: linear-gradient(90deg, var(--color-primary), var(--color-accent));
            -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text;
        }
        .logo-sub { font-size:.7rem; color:var(--color-text-muted); display:block; margin-top:-4px; }
        .header-nav { margin-left:auto; display:flex; gap:1rem; }
        .nav-link { color:var(--color-text-sub); text-decoration:none; font-size:.875rem; font-weight:500; padding:.4rem .8rem; border-radius:var(--radius-sm); transition:var(--transition); }
        .nav-link:hover { color:var(--color-text); background:rgba(99,102,241,0.1); }

        section { padding: 4rem 1.5rem; max-width: 1080px; margin: 0 auto; }

        /* ─── Hero ───────────────────────────────────── */
        .hero { text-align:center; padding-top:5rem; }
        .hero-badge {
            display:inline-flex; align-items:center; gap:.4rem;
            background:rgba(99,102,241,0.1); border:1px solid rgba(99,102,241,0.3);
            color:var(--color-primary); font-size:.78rem; font-weight:600;
            padding:.35rem .85rem; border-radius:999px; margin-bottom:1.2rem;
            letter-spacing:.03em; text-transform:uppercase;
        }
        .hero-title {
            font-size:clamp(2.2rem, 5vw, 3.5rem); font-weight:800; line-height:1.15; margin-bottom:1.25rem;
            background: linear-gradient(135deg, #fff 0%, var(--color-text-sub) 100%);
            -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text;
        }
        .hero-description { color:var(--color-text-muted); font-size:1.1rem; line-height:1.7; max-width:620px; margin:0 auto 2rem; }
        .hero-actions { display:flex; gap:1rem; justify-content:center; flex-wrap:wrap; }
        .btn { padding:.85rem 1.75rem; border-radius:var(--radius-sm); font-family:var(--font-sans); font-size:.95rem; font-weight:600; cursor:pointer; transition:var(--transition); text-decoration:none; display:inline-flex; align-items:center; gap:.5rem; border:none; }
        .btn-primary { background:linear-gradient(135deg,var(--color-primary),var(--color-primary-dark)); color:#fff; }
        .btn-primary:hover { box-shadow:0 8px 25px rgba(99,102,241,.4); transform:translateY(-1px); }
        .btn-ghost { background:transparent; color:var(--color-text-sub); border:1px solid var(--color-border); }
        .btn-ghost:hover { color:var(--color-text); background:rgba(99,102,241,.08); }

        /* ─── Section Title ──────────────────────────── */
        .section-title { font-size:1.8rem; font-weight:800; text-align:center; margin-bottom:.5rem; }
        .section-subtitle { color:var(--color-text-muted); text-align:center; margin-bottom:2.5rem; max-width:560px; margin-left:auto; margin-right:auto; }

        /* ─── Integrations Grid ──────────────────────── */
        .integrations-grid { display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:1.25rem; }
        .integration-card {
            background: var(--color-bg-card); border:1px solid var(--color-border);
            border-radius: var(--radius-lg); padding:1.75rem; text-align:center;
            transition: var(--transition); position:relative; overflow:hidden;
        }
        .integration-card:hover { transform:translateY(-3px); border-color:rgba(99,102,241,.3); }
        .integration-icon { font-size:2.25rem; margin-bottom:.75rem; }
        .integration-name { font-weight:700; margin-bottom:.35rem; }
        .integration-status { font-size:.78rem; font-weight:600; padding:.25rem .7rem; border-radius:999px; display:inline-block; margin-top:.5rem; }
        .integration-status.active { background:rgba(16,185,129,.12); color:#34d399; border:1px solid rgba(16,185,129,.25); }
        .integration-status.soon { background:rgba(245,158,11,.12); color:#fbbf24; border:1px solid rgba(245,158,11,.25); }

        /* ─── Features ───────────────────────────────── */
        .features-grid { display:grid; grid-template-columns:repeat(auto-fit, minmax(240px, 1fr)); gap:1.25rem; }
        .feature-card { background: var(--color-bg-card); border:1px solid var(--color-border); border-radius:var(--radius-md); padding:1.5rem; }
        .feature-icon { font-size:1.75rem; margin-bottom:.6rem; }
        .feature-title { font-weight:700; margin-bottom:.4rem; font-size:.98rem; }
        .feature-desc { color:var(--color-text-muted); font-size:.86rem; line-height:1.55; }

        /* ─── Demo Widget ─────────────────────────────── */
        .demo-card {
            background: var(--color-bg-card); border:1px solid var(--color-border);
            border-radius: var(--radius-lg); padding:2rem; box-shadow:var(--shadow-glow);
            position:relative; overflow:hidden; max-width:680px; margin:0 auto;
        }
        .demo-card::before {
            content:''; position:absolute; top:0; left:0; right:0; height:1px;
            background: linear-gradient(90deg, transparent, rgba(99,102,241,.5), rgba(6,182,212,.5), transparent);
        }
        .form-group { display:flex; flex-direction:column; gap:.5rem; margin-bottom:1.25rem; }
        .form-label { font-size:.85rem; font-weight:600; color:var(--color-text-sub); }
        .form-textarea, .form-input {
            background: var(--color-bg-input); border:1.5px solid var(--color-border); border-radius:var(--radius-sm);
            color:var(--color-text); font-family:var(--font-sans); font-size:.9375rem; padding:.75rem 1rem;
            transition:var(--transition); width:100%; outline:none;
        }
        .form-textarea:focus, .form-input:focus { border-color:var(--color-border-focus); box-shadow:0 0 0 3px rgba(99,102,241,.15); }
        .form-textarea { resize:vertical; min-height:120px; line-height:1.6; }
        .demo-result { margin-top:1.25rem; padding:1rem 1.25rem; border-radius:var(--radius-sm); font-size:.9rem; font-weight:500; border:1px solid; display:none; }
        .demo-result.show { display:block; animation:slideIn .3s ease; }
        .demo-result.spam { background:rgba(239,68,68,.08); border-color:rgba(239,68,68,.3); color:#f87171; }
        .demo-result.clean { background:rgba(16,185,129,.08); border-color:rgba(16,185,129,.3); color:#34d399; }
        @keyframes slideIn { from{opacity:0;transform:translateY(-8px)} to{opacity:1;transform:translateY(0)} }

        /* ─── Footer ─────────────────────────────────── */
        .footer { text-align:center; padding:2rem 1.5rem; border-top:1px solid var(--color-border); color:var(--color-text-muted); font-size:.85rem; }
        .footer a { color:var(--color-primary); text-decoration:none; }

        @media (max-width:580px) { .header{padding:1rem;} section{padding:3rem 1rem;} }
    </style>
</head>
<body>

    <header class="header" role="banner">
        <a href="{{ route('landing') }}" class="header-logo">
            <div class="logo-icon">🛡️</div>
            <div>
                <span class="logo-text">Aegis Filter</span>
                <span class="logo-sub">Antispam Multi-canal</span>
            </div>
        </a>
        <nav class="header-nav">
            <a href="{{ route('comments.form') }}" class="nav-link">📝 Foro Demo</a>
            @auth
                <a href="{{ route('dashboard') }}" class="nav-link">📊 Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="nav-link">🔐 Login Admin</a>
            @endauth
        </nav>
    </header>

    <section class="hero">
        <div class="hero-badge">🛡️ Protección Antispam en Tiempo Real</div>
        <h1 class="hero-title">Detén el spam antes<br>de que llegue a tus usuarios</h1>
        <p class="hero-description">
            Aegis Filter analiza mensajes en tiempo real para bloquear spam, phishing
            y contenido abusivo — en tu foro, en Telegram, por voz con Alexa, y muy
            pronto en WordPress.
        </p>
        <div class="hero-actions">
            <a href="#demo" class="btn btn-primary">🔍 Probar el filtro ahora</a>
            <a href="{{ route('comments.form') }}" class="btn btn-ghost">📝 Ver foro demo</a>
        </div>
    </section>

    <section id="integrations">
        <h2 class="section-title">Integraciones</h2>
        <p class="section-subtitle">Un solo motor antispam, conectado a todos tus canales.</p>
        <div class="integrations-grid">
            <div class="integration-card">
                <div class="integration-icon">📝</div>
                <div class="integration-name">Foro Web</div>
                <span class="integration-status active">✅ Activo</span>
            </div>
            <div class="integration-card">
                <div class="integration-icon">✈️</div>
                <div class="integration-name">Telegram</div>
                <span class="integration-status active">✅ Activo</span>
            </div>
            <div class="integration-card">
                <div class="integration-icon">🗣️</div>
                <div class="integration-name">Alexa</div>
                <span class="integration-status active">✅ Activo</span>
            </div>
            <div class="integration-card">
                <div class="integration-icon">📰</div>
                <div class="integration-name">WordPress</div>
                <span class="integration-status soon">⏳ Próximamente</span>
            </div>
        </div>
    </section>

    <section id="features">
        <h2 class="section-title">¿Por qué Aegis Filter?</h2>
        <p class="section-subtitle">Diseñado para foros y comunidades que necesitan moderación automática y auditable.</p>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">⚡</div>
                <div class="feature-title">Análisis en tiempo real</div>
                <div class="feature-desc">Cada mensaje se evalúa al instante, sin retrasos perceptibles para el usuario.</div>
            </div>
            <div class="feature-card">
                <div class="feature-icon">⚙️</div>
                <div class="feature-title">Reglas configurables</div>
                <div class="feature-desc">Lista negra de palabras y umbral de URLs ajustables desde el panel, sin tocar código.</div>
            </div>
            <div class="feature-card">
                <div class="feature-icon">📊</div>
                <div class="feature-title">Auditoría completa</div>
                <div class="feature-desc">Cada análisis se registra con su canal de origen, para métricas y trazabilidad.</div>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🔑</div>
                <div class="feature-title">API keys por canal</div>
                <div class="feature-desc">Cada integración externa usa su propia credencial, revocable en cualquier momento.</div>
            </div>
        </div>
    </section>

    <section id="demo">
        <h2 class="section-title">Pruébalo tú mismo</h2>
        <p class="section-subtitle">Escribe un mensaje y mira cómo lo evalúa el motor antispam en vivo.</p>

        <div class="demo-card">
            <div class="form-group">
                <label class="form-label" for="demo-content">Mensaje a analizar</label>
                <textarea id="demo-content" class="form-textarea" placeholder="Ej: Compra ahora esta oferta increíble..." maxlength="2000"></textarea>
            </div>
            <button type="button" id="demo-submit" class="btn btn-primary" style="width:100%; justify-content:center;">
                🔍 Analizar mensaje
            </button>
            <div id="demo-result" class="demo-result" role="status" aria-live="polite"></div>
        </div>
    </section>

    <footer class="footer">
        <p>
            <strong>Aegis Filter</strong> – Sistema Antispam Multi-canal &middot;
            SI784 Calidad y Pruebas de Software &copy; {{ date('Y') }}
        </p>
    </footer>

    <script>
        const demoContent = document.getElementById('demo-content');
        const demoSubmit  = document.getElementById('demo-submit');
        const demoResult  = document.getElementById('demo-result');
        const csrfToken   = document.querySelector('meta[name="csrf-token"]').content;

        demoSubmit.addEventListener('click', async () => {
            const content = demoContent.value.trim();

            if (content.length < 10) {
                demoResult.textContent = '⚠️ Escribe al menos 10 caracteres para analizar.';
                demoResult.className = 'demo-result show spam';
                return;
            }

            demoSubmit.disabled = true;
            demoSubmit.textContent = '⏳ Analizando...';

            try {
                const response = await fetch('{{ route('api.check-spam') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({ author: 'Visitante Demo', content }),
                });

                const data = await response.json();

                if (data.isSpam) {
                    demoResult.textContent = `🚫 Spam detectado (${data.reason}) – puntaje ${data.score}/100`;
                    demoResult.className = 'demo-result show spam';
                } else {
                    demoResult.textContent = '✅ Mensaje limpio, aprobado por el filtro.';
                    demoResult.className = 'demo-result show clean';
                }
            } catch (e) {
                demoResult.textContent = '❌ Ocurrió un error al analizar el mensaje.';
                demoResult.className = 'demo-result show spam';
            } finally {
                demoSubmit.disabled = false;
                demoSubmit.textContent = '🔍 Analizar mensaje';
            }
        });
    </script>
</body>
</html>
