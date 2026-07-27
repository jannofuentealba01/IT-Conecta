<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CarbonCalculator;

class CarbonCalculatorController extends Controller
{
    /**
     * Muestra la vista con el formulario de 10 preguntas.
     */
    public function showForm()
    {
        return view('carbon.calculator');
    }

    public function calculate(Request $request)
    {
        $request->validate([
            'p1'  => 'required|numeric',
            'p2'  => 'required|numeric',
            'p3'  => 'required|numeric',
            'p4'  => 'required|numeric',
            'p5'  => 'required|numeric',
            'p6'  => 'required|numeric',
            'p7'  => 'required|numeric',
            'p8'  => 'required|numeric',
            'p9'  => 'required|numeric',
            'p10' => 'required|numeric',
        ]);

        $calculator = new CarbonCalculator();

        $totalKg = $calculator->calculate($request->all());

        return redirect()->back()->with([
            'success'  => "¡Cálculo completado! Tu huella es de {$totalKg} kg de CO₂.",
            'total_co2' => $totalKg
        ]);
    }
}