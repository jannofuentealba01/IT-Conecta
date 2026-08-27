<?php

namespace Tests\Feature;

use Tests\TestCase;

class ImpostorConnectionFeedbackTest extends TestCase
{
    public function test_active_game_views_expose_accessible_connection_feedback(): void
    {
        $views = [
            resource_path('views/impostor/play.blade.php'),
            resource_path('views/teacher/impostor/show.blade.php'),
        ];

        foreach ($views as $view) {
            $contents = file_get_contents($view);

            $this->assertStringContainsString('class="sync-status sync-status--checking"', $contents);
            $this->assertStringContainsString('role="status"', $contents);
            $this->assertStringContainsString('aria-live="polite"', $contents);
            $this->assertStringContainsString('connection.succeeded()', $contents);
            $this->assertStringContainsString('connection.failed()', $contents);
            $this->assertStringContainsString('fetchWithTimeout', $contents);
            $this->assertStringContainsString('data-sync-action', $contents);
        }
    }

    public function test_shared_sync_client_reports_failures_and_recovers_actions(): void
    {
        $javascript = file_get_contents(resource_path('js/game-sync-status.js'));

        $this->assertStringContainsString('Sin conexión · reintentando automáticamente', $javascript);
        $this->assertStringContainsString('Conexión recuperada', $javascript);
        $this->assertStringContainsString("window.addEventListener('offline'", $javascript);
        $this->assertStringContainsString("window.addEventListener('online'", $javascript);
        $this->assertStringContainsString('action.disabled = true', $javascript);
        $this->assertStringContainsString('setActionsBlocked(true)', $javascript);
        $this->assertStringContainsString('new AbortController()', $javascript);
    }

    public function test_waiting_room_keeps_its_screen_during_a_connection_failure(): void
    {
        $lobby = file_get_contents(resource_path('views/impostor/lobby.blade.php'));

        $this->assertStringNotContainsString('http-equiv="refresh"', $lobby);
        $this->assertStringContainsString('lobbyConnectionStatus', $lobby);
        $this->assertStringContainsString('fetchWithTimeout', $lobby);
        $this->assertStringContainsString('setInterval(checkForGame,5000)', $lobby);
        $this->assertStringContainsString('connection.failed()', $lobby);
        $this->assertStringContainsString('data-sync-action', $lobby);
    }
}
