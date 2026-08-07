<style>
    .teacher-shell { max-width: 1050px; margin: 0 auto; }
    .teacher-header { display:flex; justify-content:space-between; align-items:flex-start; gap:16px; margin-bottom:22px; flex-wrap:wrap; }
    .teacher-eyebrow { color:#059669; font-size:13px; font-weight:800; text-transform:uppercase; letter-spacing:.08em; margin:0 0 6px; }
    .teacher-title { color:#064e3b; font-size:28px; line-height:1.2; margin:0 0 7px; }
    .teacher-subtitle { color:#6b7280; margin:0; line-height:1.5; }
    .teacher-card { background:#fff; border:1px solid #d1fae5; border-radius:18px; padding:22px; box-shadow:0 10px 28px rgba(6,78,59,.07); }
    .teacher-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(210px,1fr)); gap:15px; }
    .teacher-stat { background:linear-gradient(145deg,#f0fdf4,#fff); border:1px solid #bbf7d0; border-radius:15px; padding:18px; }
    .teacher-stat strong { display:block; color:#047857; font-size:28px; margin-top:6px; }
    .teacher-btn { min-height:48px; display:inline-flex; justify-content:center; align-items:center; gap:7px; border:0; border-radius:10px; padding:11px 16px; font-weight:750; text-decoration:none; cursor:pointer; font-size:14px; }
    .teacher-btn:disabled { opacity:.55; cursor:not-allowed; }
    .teacher-btn-primary { background:linear-gradient(135deg,#059669,#047857); color:#fff; }
    .teacher-btn-secondary { background:#ecfdf5; color:#047857; border:1px solid #a7f3d0; }
    .teacher-btn-danger { background:#fff1f2; color:#be123c; border:1px solid #fecdd3; }
    .teacher-btn-muted { background:#f3f4f6; color:#4b5563; border:1px solid #e5e7eb; }
    .teacher-list { display:flex; flex-direction:column; gap:12px; }
    .teacher-row { display:flex; justify-content:space-between; align-items:center; gap:15px; border:1px solid #e5e7eb; border-radius:13px; padding:16px; flex-wrap:wrap; }
    .teacher-row h3 { color:#065f46; margin:0 0 5px; font-size:17px; }
    .teacher-meta { color:#6b7280; font-size:13px; margin:0; }
    .teacher-badge { display:inline-flex; border-radius:999px; padding:5px 10px; font-size:12px; font-weight:800; }
    .status-draft { background:#f3f4f6; color:#4b5563; }
    .status-open { background:#dcfce7; color:#166534; }
    .status-closed { background:#fef3c7; color:#92400e; }
    .status-archived { background:#e5e7eb; color:#4b5563; }
    .teacher-form-group { margin-bottom:17px; }
    .teacher-form-group label { display:block; color:#065f46; font-size:14px; font-weight:750; margin-bottom:7px; }
    .teacher-input { width:100%; border:1.5px solid #a7f3d0; border-radius:10px; padding:12px 13px; font-size:15px; color:#1f2937; background:#fff; }
    .teacher-input:focus { outline:0; border-color:#059669; box-shadow:0 0 0 3px rgba(5,150,105,.13); }
    .teacher-error { color:#b91c1c; font-size:13px; margin-top:5px; }
    .session-code { color:#064e3b; font-size:clamp(48px,10vw,88px); letter-spacing:.13em; font-weight:900; text-align:center; margin:12px 0; font-variant-numeric:tabular-nums; }
    .empty-state { color:#6b7280; text-align:center; padding:30px 15px; border:1px dashed #a7f3d0; border-radius:13px; }
    @media (max-width:640px) { .teacher-title { font-size:23px; } .teacher-card { padding:17px; } .teacher-btn { width:100%; } }
</style>
