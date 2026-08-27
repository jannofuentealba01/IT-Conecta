import './bootstrap';
import { bindConfirmations, bindFlashFeedback } from './confirmations';
import { createGameSyncStatus, fetchWithTimeout } from './game-sync-status';
import { createQrScanner } from './qr-scanner';
import jsQR from 'jsqr';

import Alpine from 'alpinejs';

window.Alpine = Alpine;
window.jsQR = jsQR;
window.ITConectaGameSync = { createGameSyncStatus, fetchWithTimeout };
window.ITConectaQrScanner = { createQrScanner };

Alpine.start();

bindConfirmations();
bindFlashFeedback();

window.dispatchEvent(new Event('it-conecta:frontend-ready'));
