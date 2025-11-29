<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Juego;
use Illuminate\Support\Str;
use App\Events\NumeroSorteado;

class JuegoController extends Controller
{
    public function index()
    {
        //
    }

    public function crear()
    {
        $juego = Juego::create([
            'codigo' => strtoupper(Str::random(6)),
            'estado' => 'esperando',
            'numeros_sorteados' => []
        ]);

        return redirect()->route('bingo.control', $juego->codigo);
    }

    public function control($codigo)
    {
        $juego = Juego::where('codigo', $codigo)->firstOrFail();

        return view('bingo.control', compact('juego'));
    }

    public function sortearNumero($codigo)
    {
        $juego = Juego::where('codigo', $codigo)->firstOrFail();

        if ($juego->estado === 'finalizado') {
            return response()->json(['error' => 'El juego ha finalizado'], 400);
        }

        $numerosSorteados = $juego->numeros_sorteados ?? [];
        $numerosDisponibles = array_diff(range(1, 99), $numerosSorteados);

        if (empty($numerosDisponibles)) {
            return response()->json(['error' => 'No hay más números'], 400);
        }

        $nuevoNumero = $numerosDisponibles[array_rand($numerosDisponibles)];
        $numerosSorteados[] = $nuevoNumero;

        $juego->update([
            'estado' => 'jugando',
            'numeros_sorteados' => $numerosSorteados
        ]);

        broadcast(new NumeroSorteado($codigo, $nuevoNumero, $numerosSorteados));

        return response()->json([
            'numero' => $nuevoNumero,
            'total_sorteados' => count($numerosSorteados)
        ]);
    }

    public function store(Request $request)
    {
        //
    }


    public function show(string $id)
    {
        //
    }

    public function edit(string $id)
    {
        //
    }

    public function update(Request $request, string $id)
    {
        //
    }

    public function destroy(string $id)
    {
        //
    }
}
