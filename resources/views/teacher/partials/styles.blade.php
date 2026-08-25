<style>
    .teacher-shell { max-width: 1050px; margin: 0 auto; }
    .teacher-header { display:flex; justify-content:space-between; align-items:flex-start; gap:16px; margin-bottom:22px; flex-wrap:wrap; }
    .teacher-eyebrow { color:var(--brand-blue); font-size:13px; font-weight:800; text-transform:uppercase; letter-spacing:.08em; margin:0 0 6px; }
    .teacher-title { color:var(--text-primary); font-size:28px; line-height:1.2; margin:0 0 7px; }
    .teacher-subtitle { color:var(--text-secondary); margin:0; line-height:1.5; }
    .teacher-card { background:var(--surface); border:1px solid var(--border); border-radius:18px; padding:22px; box-shadow:0 10px 28px rgba(17,24,39,.07); }
    .teacher-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(210px,1fr)); gap:15px; }
    .teacher-stat { background:linear-gradient(145deg,var(--info-soft),var(--surface)); border:1px solid var(--brand-blue-light); border-radius:15px; padding:18px; }
    .teacher-stat strong { display:block; color:var(--brand-blue-dark); font-size:28px; margin-top:6px; }
    .teacher-btn { min-height:48px; display:inline-flex; justify-content:center; align-items:center; gap:7px; border:0; border-radius:10px; padding:11px 16px; font-weight:750; text-decoration:none; cursor:pointer; font-size:14px; }
    .teacher-btn:disabled { opacity:.55; cursor:not-allowed; }
    .teacher-btn-primary { background:var(--brand-blue); color:var(--surface); }
    .teacher-btn-primary:hover { background:var(--brand-blue-dark); }
    .teacher-btn-secondary { background:var(--info-soft); color:var(--brand-blue-dark); border:1px solid var(--brand-blue-light); }
    .teacher-btn-secondary:hover { background:color-mix(in srgb,var(--brand-blue) 18%,var(--surface)); }
    .teacher-btn-danger { background:var(--danger); color:var(--surface); border:1px solid var(--danger); }
    .teacher-btn-danger:hover { background:var(--danger-dark); }
    .teacher-btn-muted { background:var(--surface-muted); color:var(--text-secondary); border:1px solid var(--border); }
    .teacher-list { display:flex; flex-direction:column; gap:12px; }
    .teacher-row { display:flex; justify-content:space-between; align-items:center; gap:15px; border:1px solid var(--border); border-radius:13px; padding:16px; flex-wrap:wrap; }
    .teacher-row h3 { color:var(--text-primary); margin:0 0 5px; font-size:17px; }
    .teacher-meta { color:var(--text-secondary); font-size:13px; margin:0; }
    .teacher-badge { display:inline-flex; border-radius:999px; padding:5px 10px; font-size:12px; font-weight:800; }
    .status-draft { background:var(--surface-muted); color:var(--text-secondary); }
    .status-open { background:var(--positive-soft); color:var(--brand-green-dark); }
    .status-closed { background:var(--warning-soft); color:var(--text-primary); }
    .status-archived { background:var(--border); color:var(--text-secondary); }
    .teacher-form-group { margin-bottom:17px; }
    .teacher-form-group label { display:block; color:var(--text-primary); font-size:14px; font-weight:750; margin-bottom:7px; }
    .teacher-input { width:100%; border:1.5px solid var(--border); border-radius:10px; padding:12px 13px; font-size:15px; color:var(--text-primary); background:var(--surface); }
    .teacher-input:focus { outline:0; border-color:var(--brand-blue); box-shadow:0 0 0 3px var(--focus-ring); }
    .teacher-error { color:var(--danger-dark); font-size:13px; margin-top:5px; }
    .session-code { color:var(--brand-blue-dark); font-size:clamp(48px,10vw,88px); letter-spacing:.13em; font-weight:900; text-align:center; margin:12px 0; font-variant-numeric:tabular-nums; }
    .empty-state { color:var(--text-secondary); text-align:center; padding:30px 15px; border:1px dashed var(--border); border-radius:13px; }
    @media (max-width:640px) { .teacher-title { font-size:23px; } .teacher-card { padding:17px; } .teacher-btn { width:100%; } }
</style>
