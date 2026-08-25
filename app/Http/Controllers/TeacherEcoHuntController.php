<?php

namespace App\Http\Controllers;

use App\Models\EcoActivityProfile;
use App\Models\EcoHunt;
use App\Models\Room;
use App\Services\EcoHuntService;
use DomainException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class TeacherEcoHuntController extends Controller
{
    public function index(int $room, EcoHuntService $service)
    {
        $room = $this->ownedRoom($room);
        $hunt = $room->ecoHunts()->with('activities.ecoProfile')->latest()->first();
        if ($hunt) {
            $hunt = $service->refresh($hunt);
        }
        $profiles = EcoActivityProfile::with('activity')->where('is_active', true)
            ->whereHas('activity', fn ($query) => $query->where('is_active', true))
            ->orderBy('activity_type')->orderBy('id')->get();

        return view('teacher.eco-hunts.index', compact('room', 'hunt', 'profiles'));
    }

    public function store(Request $request, int $room)
    {
        $room = $this->ownedRoom($room);
        if ($room->ecoHunts()->whereIn('status', EcoHunt::OPEN_STATUSES)->exists()) {
            return back()->with('error', 'Ya existe una EcoBúsqueda preparada o activa para esta sala.');
        }

        $validated = $this->validated($request);
        DB::transaction(function () use ($room, $validated): void {
            $hunt = $room->ecoHunts()->create([
                'name' => $validated['name'],
                'status' => EcoHunt::STATUS_READY,
                'duration_seconds' => 900,
            ]);
            $hunt->activities()->sync($validated['activities']);
        });

        return back()->with('success', 'EcoBúsqueda preparada. La duración quedó fijada en 15 minutos.');
    }

    public function update(Request $request, int $room, int $hunt)
    {
        $room = $this->ownedRoom($room);
        $hunt = $room->ecoHunts()->findOrFail($hunt);
        abort_unless($hunt->status === EcoHunt::STATUS_READY, 409, 'Solo una EcoBúsqueda preparada y aún no iniciada puede modificarse.');
        $validated = $this->validated($request);

        DB::transaction(function () use ($hunt, $validated): void {
            $hunt->update(['name' => $validated['name'], 'duration_seconds' => 900]);
            $hunt->activities()->sync($validated['activities']);
        });

        return back()->with('success', 'Selección de EcoBúsqueda actualizada.');
    }

    public function start(int $room, int $hunt, EcoHuntService $service)
    {
        $room = $this->ownedRoom($room);
        $hunt = $room->ecoHunts()->findOrFail($hunt);
        if (! $room->isOpen()) {
            return back()->with('error', 'Abre la sala antes de iniciar la EcoBúsqueda.');
        }
        try {
            $service->start($hunt);
        } catch (DomainException $exception) {
            return back()->with('error', $exception->getMessage());
        }
        return back()->with('success', '¡EcoBúsqueda iniciada! Los estudiantes tienen 15 minutos.');
    }

    public function finish(int $room, int $hunt, EcoHuntService $service)
    {
        $room = $this->ownedRoom($room);
        $hunt = $room->ecoHunts()->findOrFail($hunt);
        $service->finish($hunt);
        return redirect()->route('teacher.eco-hunts.results', [$room, $hunt]);
    }

    public function reopen(int $room, int $hunt, EcoHuntService $service)
    {
        $room = $this->ownedRoom($room);
        $hunt = $room->ecoHunts()->findOrFail($hunt);

        try {
            $service->reopen($hunt);
        } catch (DomainException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()->route('teacher.eco-hunts.index', $room)
            ->with('success', 'EcoBúsqueda reabierta por 5 minutos. Puedes finalizarla manualmente cuando quieras.');
    }

    public function results(int $room, int $hunt, EcoHuntService $service)
    {
        $room = $this->ownedRoom($room);
        $hunt = $service->refresh($room->ecoHunts()->findOrFail($hunt));
        if ($hunt->status !== EcoHunt::STATUS_FINISHED) {
            return redirect()->route('teacher.eco-hunts.index', $room)->with('error', 'Los resultados se mostrarán cuando finalice la EcoBúsqueda.');
        }
        $ranking = $service->ranking($hunt);
        return view('teacher.eco-hunts.results', compact('room', 'hunt', 'ranking'));
    }

    public function kit(int $room, int $hunt)
    {
        $room = $this->ownedRoom($room);
        $hunt = $room->ecoHunts()->with(['activities.ecoProfile'])->findOrFail($hunt);
        $activities = $hunt->activities->filter(fn ($activity) => $activity->ecoProfile?->is_active)
            ->sortBy(fn ($activity) => [$activity->ecoProfile->activity_type, $activity->ecoProfile->id])->values();
        abort_if($activities->isEmpty(), 409, 'La EcoBúsqueda no tiene actividades seleccionadas.');

        $qrImages = $activities->mapWithKeys(function ($activity): array {
            $url = route('student.eco-hunt.show', $activity->ecoProfile->qr_token);
            $svg = QrCode::format('svg')->size(280)->margin(1)->errorCorrection('H')->generate($url);
            return [$activity->id => 'data:image/svg+xml;base64,'.base64_encode($svg)];
        });

        return Pdf::loadView('teacher.eco-hunts.kit-pdf', compact('room', 'hunt', 'activities', 'qrImages'))
            ->setPaper('a4', 'portrait')
            ->download('kit-ecobusqueda-'.$room->code.'.pdf');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'activities' => ['required', 'array', 'min:1', 'max:20'],
            'activities.*' => [
                'integer', 'distinct',
                Rule::exists('activities', 'id')->where(fn ($query) => $query
                    ->where('is_active', true)
                    ->whereIn('id', EcoActivityProfile::where('is_active', true)->select('activity_id'))),
            ],
        ]);
    }

    private function ownedRoom(int $id): Room
    {
        return Room::where('user_id', auth()->id())->findOrFail($id);
    }
}
