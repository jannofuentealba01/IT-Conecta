const CONNECTION_STATES = {
    checking: 'Verificando conexión…',
    online: 'Sincronización activa',
    offline: 'Sin conexión · reintentando automáticamente',
    recovered: 'Conexión recuperada',
};

export function createGameSyncStatus(element, options = {}) {
    const actionRoot = options.actionRoot ?? document;
    const label = element?.querySelector('[data-sync-label]');
    let currentState = 'checking';
    let recoveryTimer = null;

    const actions = () => [...actionRoot.querySelectorAll('[data-sync-action]')];

    function setActionsBlocked(blocked) {
        actions().forEach((action) => {
            if (blocked) {
                if (!action.hasAttribute('data-sync-was-disabled')) {
                    action.dataset.syncWasDisabled = action.disabled ? '1' : '0';
                }
                action.disabled = true;
                action.setAttribute('aria-disabled', 'true');
                return;
            }

            if (action.hasAttribute('data-sync-was-disabled')) {
                action.disabled = action.dataset.syncWasDisabled === '1';
                delete action.dataset.syncWasDisabled;
            }
            action.removeAttribute('aria-disabled');
        });
    }

    function render(state) {
        if (!element || !label) return;
        element.className = `sync-status sync-status--${state}`;
        element.dataset.connectionState = state;
        label.textContent = CONNECTION_STATES[state];
    }

    function checking() {
        clearTimeout(recoveryTimer);
        currentState = 'checking';
        setActionsBlocked(true);
        render('checking');
    }

    function failed() {
        clearTimeout(recoveryTimer);
        currentState = 'offline';
        setActionsBlocked(true);
        render('offline');
    }

    function succeeded() {
        const recovered = currentState === 'offline';
        clearTimeout(recoveryTimer);
        setActionsBlocked(false);
        currentState = recovered ? 'recovered' : 'online';
        render(currentState);

        if (recovered) {
            recoveryTimer = setTimeout(() => {
                currentState = 'online';
                render('online');
            }, 3000);
        }
    }

    window.addEventListener('offline', failed);
    window.addEventListener('online', () => {
        checking();
        options.onRetry?.();
    });

    checking();

    return { checking, failed, succeeded };
}

export async function fetchWithTimeout(url, options = {}, timeoutMilliseconds = 5000) {
    const controller = new AbortController();
    const timeout = setTimeout(() => controller.abort(), timeoutMilliseconds);

    try {
        const response = await fetch(url, { ...options, signal: controller.signal });

        if (!response.ok) {
            throw new Error(`Synchronization failed with status ${response.status}`);
        }

        return response;
    } finally {
        clearTimeout(timeout);
    }
}
