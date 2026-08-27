<?php

namespace Tests\Feature;

use App\Models\CarbonFootprint;
use App\Models\Course;
use App\Models\EcoActivityProfile;
use App\Models\EcoHunt;
use App\Models\EcoHuntCompletion;
use App\Models\ImpostorClue;
use App\Models\ImpostorGame;
use App\Models\ImpostorVote;
use App\Models\Participant;
use App\Models\PointTransaction;
use App\Models\Room;
use App\Models\User;
use App\Services\RoomReportService;
use Database\Seeders\EcoHuntActivitySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ExportResultsServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_room_results_are_normalized_for_future_export_without_sensitive_identifiers(): void
    {
        Carbon::setTestNow('2026-08-25 12:00:00');
        $this->seed(EcoHuntActivitySeeder::class);

        $teacher = User::factory()->create();
        $course = Course::create([
            'user_id' => $teacher->id,
            'name' => '4° Medio D',
            'school_name' => 'Liceo IT Conecta',
            'is_active' => true,
        ]);
        $room = Room::create([
            'user_id' => $teacher->id,
            'course_id' => $course->id,
            'code' => '371040',
            'name' => 'Sesión demostrativa',
            'status' => 'closed',
            'duration_minutes' => 120,
            'opened_at' => now()->subHour(),
            'closed_at' => now(),
            'expires_at' => now()->addHour(),
        ]);
        $crew = $this->participant($room, 'Camila', 'token-camila');
        $impostor = $this->participant($room, 'Diego', 'token-diego');
        $observer = $this->participant($room, 'Elena', 'token-elena');

        CarbonFootprint::create([
            'participant_id' => $crew->id,
            'initial_kg_co2e_year' => 290.50,
            'answers' => ['transport' => 'walk'],
            'calculator_version' => '1.0',
            'is_current' => true,
            'current_marker' => 1,
        ]);

        $profile = EcoActivityProfile::with('activity')->firstOrFail();
        $hunt = EcoHunt::create([
            'room_id' => $room->id,
            'name' => 'EcoBúsqueda del patio',
            'status' => EcoHunt::STATUS_FINISHED,
            'duration_seconds' => 900,
            'started_at' => now()->subMinutes(30),
            'ends_at' => now()->subMinutes(15),
            'finished_at' => now()->subMinutes(15),
            'finished_by' => 'teacher',
        ]);
        $hunt->activities()->attach($profile->activity_id);
        EcoHuntCompletion::create([
            'eco_hunt_id' => $hunt->id,
            'room_id' => $room->id,
            'participant_id' => $crew->id,
            'activity_id' => $profile->activity_id,
            'points_awarded' => $profile->game_points,
            'verification_type' => 'quiz',
            'completed_at' => now()->subMinutes(20),
        ]);
        PointTransaction::create([
            'participant_id' => $crew->id,
            'room_id' => $room->id,
            'category' => PointTransaction::CATEGORY_ACTION,
            'source_type' => 'eco_hunt_completion',
            'source_id' => 1,
            'source_key' => 'eco-export-test',
            'points' => $profile->game_points,
            'description' => 'Actividad EcoBúsqueda',
        ]);

        $game = ImpostorGame::create([
            'room_id' => $room->id,
            'word' => 'Reciclaje',
            'status' => 'finished',
            'impostor_id' => $impostor->id,
            'started_at' => now()->subMinutes(10),
            'voting_at' => now()->subMinutes(6),
            'closes_at' => now()->subMinutes(5),
            'results_at' => now()->subMinutes(4)->subSeconds(30),
        ]);
        $game->impostors()->attach($impostor->id);
        ImpostorClue::create(['game_id' => $game->id, 'participant_id' => $crew->id, 'clue' => 'Separar']);
        ImpostorClue::create(['game_id' => $game->id, 'participant_id' => $impostor->id, 'clue' => 'Material']);
        ImpostorVote::create(['game_id' => $game->id, 'voter_id' => $crew->id, 'suspect_id' => $impostor->id]);
        ImpostorVote::create(['game_id' => $game->id, 'voter_id' => $observer->id, 'suspect_id' => $impostor->id]);
        PointTransaction::create([
            'participant_id' => $crew->id,
            'room_id' => $room->id,
            'category' => PointTransaction::CATEGORY_LEARNING,
            'source_type' => 'impostor_game',
            'source_id' => $game->id,
            'source_key' => 'impostor-export-test-crew',
            'points' => 20,
            'description' => 'Detectó al impostor',
        ]);

        $transactionCount = PointTransaction::count();
        $report = app(RoomReportService::class)->build($room);
        $export = $report['exportResults'];

        $this->assertSame('it-conecta-results', $export['schema']);
        $this->assertSame(1, $export['schema_version']);
        $this->assertSame('Sesión demostrativa', $export['session']['name']);
        $this->assertSame('4° Medio D', $export['session']['course']);
        $this->assertSame(3, $export['summary']['participants']);
        $this->assertSame(1, $export['summary']['completed_activities']);
        $this->assertSame(1, $export['summary']['eco_hunts']);
        $this->assertSame(1, $export['summary']['impostor_games']);
        $this->assertCount(3, $export['participants']);

        $ecoHunt = collect($export['experiences'])->firstWhere('type', 'eco_hunt');
        $this->assertSame('Camila', $ecoHunt['result']['winner_label']);
        $this->assertSame('EcoBúsqueda del patio', $ecoHunt['name']);

        $impostorGame = collect($export['experiences'])->firstWhere('type', 'impostor_game');
        $this->assertSame('Tripulación', $impostorGame['result']['winner_label']);
        $this->assertSame(['Diego'], $impostorGame['result']['caught_impostors']);
        $this->assertSame(2, $impostorGame['metrics']['votes_submitted']);
        $this->assertSame('Camila', $impostorGame['ranking'][0]['participant']);
        $this->assertSame(20, $impostorGame['ranking'][0]['points']);

        $serialized = json_encode($export, JSON_THROW_ON_ERROR);
        $this->assertStringNotContainsString('371040', $serialized);
        $this->assertStringNotContainsString('token-camila', $serialized);
        $this->assertStringNotContainsString('participant_id', $serialized);
        $this->assertStringNotContainsString('room_id', $serialized);
        $this->assertSame($transactionCount, PointTransaction::count(), 'Preparar la exportación no debe otorgar puntos nuevamente.');

        $this->actingAs($teacher)
            ->get(route('teacher.sessions.report', $room))
            ->assertOk()
            ->assertSee('Juego del Impostor')
            ->assertSee('Equipo ganador')
            ->assertSee('Tripulación')
            ->assertSee('EcoBúsqueda del patio');

        $pdfResponse = $this->actingAs($teacher)
            ->get(route('teacher.sessions.report.pdf', $room))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
        $this->assertStringContainsString('attachment; filename=resultados-sesion-demostrativa-2026-08-25.pdf', $pdfResponse->headers->get('content-disposition'));
        $this->assertStringStartsWith('%PDF-', $pdfResponse->getContent());

        $otherTeacher = User::factory()->create();
        $this->actingAs($otherTeacher)
            ->get(route('teacher.sessions.report.pdf', $room))
            ->assertNotFound();

        Carbon::setTestNow();
    }

    private function participant(Room $room, string $name, string $token): Participant
    {
        return Participant::create([
            'room_id' => $room->id,
            'name' => $name,
            'normalized_name' => str($name)->lower()->ascii()->toString(),
            'course' => '4° Medio D',
            'recovery_token' => $token,
            'joined_at' => now()->subHour(),
        ]);
    }
}
