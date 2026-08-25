<style id="it-conecta-color-tokens">
    :root {
        --brand-blue:#3B82F6; --brand-blue-dark:#2563EB; --brand-blue-light:#60A5FA;
        --brand-green:#22C55E; --brand-green-dark:#16A34A;
        --brand-purple:#8B5CF6; --brand-purple-dark:#7C3AED;
        --impact-low:#22C55E; --impact-medium:#FAD201; --impact-high:#EF4444;
        --danger:#EF4444; --danger-dark:#DC2626; --warning-orange:#F97316;
        --text-primary:#111827; --text-secondary:#4B5563;
        --surface:#FFFFFF; --surface-muted:#F9FAFB; --app-background:#F0FDF4; --border:#E5E7EB;
        --info-soft:color-mix(in srgb,var(--brand-blue) 12%,var(--surface));
        --positive-soft:color-mix(in srgb,var(--brand-green) 12%,var(--surface));
        --brand-blue-soft:var(--info-soft); --brand-blue-soft-border:color-mix(in srgb,var(--brand-blue) 32%,var(--surface));
        --brand-green-soft:var(--positive-soft); --brand-green-soft-border:color-mix(in srgb,var(--brand-green) 32%,var(--surface));
        --game-soft:color-mix(in srgb,var(--brand-purple) 12%,var(--surface));
        --danger-soft:color-mix(in srgb,var(--danger) 12%,var(--surface));
        --danger-soft-border:color-mix(in srgb,var(--danger) 32%,var(--surface));
        --warning-soft:color-mix(in srgb,var(--warning-orange) 12%,var(--surface)); --warning-orange-soft:var(--warning-soft);
        --impact-medium-soft:color-mix(in srgb,var(--impact-medium) 24%,var(--surface));
        --focus-ring:color-mix(in srgb,var(--brand-blue) 28%,transparent);
    }
    .bg-brand-blue{background-color:var(--brand-blue)} .bg-ui-surface{background-color:var(--surface)}
    .bg-ui-text{background-color:var(--text-primary)} .bg-impact-high{background-color:var(--impact-high)}
    .text-brand-blue-dark{color:var(--brand-blue-dark)} .text-sustainability-dark{color:var(--brand-green-dark)}
    .text-impact-high{color:var(--impact-high)} .text-ui-text{color:var(--text-primary)} .text-ui-muted{color:var(--text-secondary)}
    .border-brand-blue{border-color:var(--brand-blue)} .border-ui-border{border-color:var(--border)}
    .hover\:bg-brand-blue-dark:hover,.active\:bg-brand-blue-dark:active,.focus\:bg-brand-blue-dark:focus{background-color:var(--brand-blue-dark)}
    .hover\:bg-ui-background:hover,.focus\:bg-ui-background:focus{background-color:var(--surface-muted)}
    .hover\:text-ui-text:hover,.focus\:text-ui-text:focus{color:var(--text-primary)}
    .focus\:text-brand-blue-dark:focus{color:var(--brand-blue-dark)}
    .hover\:border-ui-border:hover,.focus\:border-ui-border:focus{border-color:var(--border)}
    .focus\:border-brand-blue:focus{border-color:var(--brand-blue)} .focus\:border-brand-blue-dark:focus{border-color:var(--brand-blue-dark)}
    .focus\:ring-brand-blue:focus,.focus\:ring-brand-blue-light:focus{--tw-ring-color:var(--brand-blue-light)}
    .focus\:ring-impact-high:focus{--tw-ring-color:var(--impact-high)}
    :focus-visible{outline:3px solid var(--brand-blue-light);outline-offset:2px}

    /* Fondo ambiental uniforme; las tarjetas conservan su superficie blanca. */
    html,body{background-color:var(--app-background)!important}

    .page-wrapper{background:linear-gradient(to bottom,transparent 25%,var(--brand-blue-dark) 58%),url('/images/reciclado.png'),linear-gradient(135deg,var(--brand-blue),var(--brand-blue-dark))!important}
    .page-wrapper .card{background:color-mix(in srgb,var(--surface) 97%,transparent)!important;border-color:var(--border)!important}
    .page-wrapper label,.page-wrapper .card h2,.page-wrapper .extra a,.page-wrapper .actions-wrapper a{color:var(--brand-blue-dark)!important}
    .page-wrapper input:not([type='checkbox']){color:var(--text-primary)!important;background:var(--surface-muted)!important;border-color:var(--border)!important}
    .page-wrapper input:not([type='checkbox']):focus{background:var(--surface)!important;border-color:var(--brand-blue)!important;box-shadow:0 0 0 4px var(--focus-ring)!important}
    .page-wrapper input[type='checkbox']{accent-color:var(--brand-blue)!important}
    .page-wrapper .btn{background:var(--brand-blue)!important;color:var(--surface)!important}.page-wrapper .btn:hover{background:var(--brand-blue-dark)!important}
    .page-wrapper .error{color:var(--danger-dark)!important}
    .join-card{background:var(--surface)!important;border:1px solid var(--border)}
    .join-card .room-badge{background:var(--info-soft)!important;color:var(--brand-blue-dark)!important}
    .join-card .form-group label,.join-card h2{color:var(--text-primary)!important}
    .join-card .form-input{color:var(--text-primary)!important;border-color:var(--border)!important}
    .join-card .form-input:focus{border-color:var(--brand-blue)!important;box-shadow:0 0 0 3px var(--focus-ring)!important}
    .join-card .btn-enter{background:var(--brand-blue)!important;color:var(--surface)!important}.join-card .btn-enter:hover{background:var(--brand-blue-dark)!important}
</style>
