#!/usr/bin/env python3
"""Genera un reporte HTML legible a partir de semgrep.json."""
import json
import sys
import html
from collections import Counter

SEVERITY_ORDER = {"ERROR": 0, "WARNING": 1, "INFO": 2}
SEVERITY_LABEL = {"ERROR": "Alta", "WARNING": "Media", "INFO": "Baja"}
SEVERITY_CLASS = {"ERROR": "sev-high", "WARNING": "sev-medium", "INFO": "sev-low"}


def load_results(path):
    with open(path, encoding="utf-8") as f:
        data = json.load(f)
    return data.get("results", []), data.get("errors", [])


def build_html(results, errors, out_path):
    counts = Counter(r.get("extra", {}).get("severity", "INFO") for r in results)
    total = len(results)

    rows = sorted(
        results,
        key=lambda r: SEVERITY_ORDER.get(r.get("extra", {}).get("severity", "INFO"), 3),
    )

    summary_cells = "".join(
        f'<div class="stat {SEVERITY_CLASS.get(sev, "sev-low")}">'
        f'<div class="stat-num">{counts.get(sev, 0)}</div>'
        f'<div class="stat-label">{SEVERITY_LABEL.get(sev, sev)}</div></div>'
        for sev in ("ERROR", "WARNING", "INFO")
    )

    table_rows = []
    for r in rows:
        extra = r.get("extra", {})
        sev = extra.get("severity", "INFO")
        message = html.escape(extra.get("message", ""))
        check_id = html.escape(r.get("check_id", ""))
        path_ = html.escape(r.get("path", ""))
        start_line = r.get("start", {}).get("line", "")
        table_rows.append(
            f'<tr class="{SEVERITY_CLASS.get(sev, "sev-low")}">'
            f'<td class="badge">{SEVERITY_LABEL.get(sev, sev)}</td>'
            f'<td>{path_}:{start_line}</td>'
            f'<td>{check_id}</td>'
            f'<td>{message}</td>'
            f'</tr>'
        )
    table_body = "\n".join(table_rows) if table_rows else (
        '<tr><td colspan="4" class="empty">Sin hallazgos 🎉</td></tr>'
    )

    errors_html = ""
    if errors:
        errors_html = (
            '<h2>Errores del análisis</h2><ul>'
            + "".join(f"<li>{html.escape(str(e))}</li>" for e in errors)
            + "</ul>"
        )

    html_doc = f"""<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Semgrep — Reporte SAST</title>
<style>
  body {{ font-family: -apple-system, Segoe UI, Arial, sans-serif; margin: 2rem; background: #fff; color: #1a1a1a; }}
  h1 {{ margin-bottom: .25rem; }}
  .subtitle {{ color: #666; margin-bottom: 1.5rem; }}
  .stats {{ display: flex; gap: 1rem; margin-bottom: 2rem; }}
  .stat {{ flex: 1; padding: 1rem; border-radius: 8px; text-align: center; }}
  .stat-num {{ font-size: 2rem; font-weight: bold; }}
  .stat-label {{ font-size: .85rem; text-transform: uppercase; letter-spacing: .05em; }}
  .sev-high {{ background: #fdecea; color: #a61b1b; }}
  .sev-medium {{ background: #fff4e5; color: #8a5a00; }}
  .sev-low {{ background: #eef6ff; color: #2058a8; }}
  table {{ border-collapse: collapse; width: 100%; }}
  th, td {{ text-align: left; padding: .5rem .75rem; border-bottom: 1px solid #e5e5e5; font-size: .9rem; }}
  th {{ background: #f5f5f5; }}
  td.badge {{ font-weight: 600; white-space: nowrap; }}
  tr.sev-high td.badge {{ color: #a61b1b; }}
  tr.sev-medium td.badge {{ color: #8a5a00; }}
  tr.sev-low td.badge {{ color: #2058a8; }}
  td.empty {{ text-align: center; padding: 2rem; color: #888; }}
  footer {{ margin-top: 2rem; color: #888; font-size: .8rem; }}
</style>
</head>
<body>
  <h1>🔍 Semgrep — Reporte de análisis estático (SAST)</h1>
  <p class="subtitle">{total} hallazgo(s) encontrados</p>
  <div class="stats">{summary_cells}</div>
  <table>
    <thead>
      <tr><th>Severidad</th><th>Ubicación</th><th>Regla</th><th>Mensaje</th></tr>
    </thead>
    <tbody>
      {table_body}
    </tbody>
  </table>
  {errors_html}
  <footer>Generado a partir de semgrep.json</footer>
</body>
</html>
"""
    with open(out_path, "w", encoding="utf-8") as f:
        f.write(html_doc)


if __name__ == "__main__":
    json_path = sys.argv[1] if len(sys.argv) > 1 else "semgrep.json"
    out_path = sys.argv[2] if len(sys.argv) > 2 else "semgrep.html"
    results, errors = load_results(json_path)
    build_html(results, errors, out_path)
    print(f"Reporte HTML generado en {out_path} ({len(results)} hallazgos)")
