<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CarbonController extends Controller
{
    public function calculate(Request $request)
    {
        // 1. Validar inputs
        $data = $request->validate([
            'p1' => 'required|numeric',
            'p2' => 'required|numeric',
            'p3' => 'required|numeric',
            'p4' => 'required|numeric',
            'p5' => 'required|numeric',
            'p6' => 'required|numeric',
            'p7' => 'required|numeric',
            'p8' => 'required|numeric',
            'p9' => 'required|numeric',
            'p10' => 'required|numeric',
        ]);

        // 2. Cálculo principal
        $total = (
            ($data['p1'] * $data['p2']) + // transporte con multiplicador
            $data['p3'] +
            $data['p4'] +
            $data['p5'] +
            $data['p6'] +
            $data['p7'] +
            $data['p8'] +
            $data['p9'] +
            $data['p10']
        );

        // 3. Redondear
        $total = round($total, 2);

        // 4. Clasificación (MUY IMPORTANTE para gamificación)
        if ($total < 200) {
            $mensaje = "🌱 ¡Excelente! Tu impacto es bajo";
        } elseif ($total < 600) {
            $mensaje = "⚖️ Impacto medio, puedes mejorar";
        } else {
            $mensaje = "🔥 Impacto alto, necesitas reducir tu huella";
        }

        // 5. Retornar resultado
        return back()->with([
            'total_co2' => $total,
            'success' => $mensaje
        ]);
    }
}