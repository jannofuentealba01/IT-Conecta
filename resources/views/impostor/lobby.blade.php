<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>Esperando juego | IT Conecta</title>@vite(['resources/css/app.css', 'resources/js/app.js'])@include('partials.color-tokens')
    <style>
        *{box-sizing:border-box}body{margin:0;min-height:100vh;padding:18px;display:grid;place-items:center;font-family:"Segoe UI",Arial,sans-serif;background:var(--surface-muted);color:var(--text-primary)}.card{width:min(520px,100%);background:var(--surface);border:1px solid var(--border);border-radius:22px;padding:30px 22px;text-align:center;box-shadow:0 18px 40px rgba(17,24,39,.13)}.icon{font-size:55px}.pulse{width:12px;height:12px;border-radius:50%;background:var(--brand-purple);display:inline-block;animation:p 1.3s infinite}@keyframes p{50%{opacity:.25;transform:scale(.8)}}h1{margin:12px 0 8px;font-size:26px;color:var(--brand-purple-dark)}p{color:var(--text-secondary);line-height:1.5}.btn{min-height:52px;width:100%;display:flex;align-items:center;justify-content:center;border:0;border-radius:13px;background:var(--brand-purple);color:var(--surface);text-decoration:none;font:inherit;font-weight:800;margin-top:18px;cursor:pointer}.btn:hover{background:var(--brand-purple-dark)}.btn:disabled{opacity:.55;cursor:not-allowed}
    </style>
</head>
<body><main class="card">@include('student.partials.identity-bar', ['participant' => $participant])<div class="icon">🎭</div><h1>Esperando al profesor</h1><p><span class="pulse"></span> Esta pantalla buscará una nueva ronda automáticamente.</p><div id="lobbyConnectionStatus" class="sync-status sync-status--checking" role="status" aria-live="polite" aria-atomic="true"><span data-sync-label>Verificando conexión…</span></div><button type="button" id="lobbyManualCheck" class="btn" data-sync-action>Comprobar ahora</button><a class="btn" style="background:var(--surface-muted);color:var(--text-primary);border:1px solid var(--border)" href="{{ route('student.dashboard') }}">Volver al panel</a></main>
<script>
function initializeLobbySync(){
    let syncing=false;
    const connection=window.ITConectaGameSync.createGameSyncStatus(document.getElementById('lobbyConnectionStatus'),{onRetry:checkForGame});
    document.getElementById('lobbyManualCheck')?.addEventListener('click',checkForGame);
    async function checkForGame(){
        if(syncing)return;
        syncing=true;
        try{
            const response=await window.ITConectaGameSync.fetchWithTimeout('{{ route('student.impostor.lobby') }}',{headers:{Accept:'text/html'},cache:'no-store'});
            connection.succeeded();
            if(response.redirected&&new URL(response.url).pathname!==location.pathname)location.replace(response.url);
        }catch(error){connection.failed()}
        finally{syncing=false}
    }
    checkForGame();
    setInterval(checkForGame,5000);
    document.addEventListener('visibilitychange',()=>{if(!document.hidden)checkForGame()});
}
if(window.ITConectaGameSync)initializeLobbySync();else window.addEventListener('it-conecta:frontend-ready',initializeLobbySync,{once:true});
</script></body>
</html>
