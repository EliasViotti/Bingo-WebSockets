<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tarjeta;
use Illuminate\Support\Str;
use App\Models\Linea;
use App\Models\Juego;

class TarjetaController extends Controller
{

    public function index()
    {
        //
    }


    public function create($codigoJuego)
    {
        //generamos 10 numeros aleatorios entre 0 y 99
        $numeros = collect(range(0, 99))->random(10)->sort()->values()->toArray();

        //creamos cada tarjeta
        $tarjeta = Tarjeta::create([
            'codigo' => Str::random(8),
            'nombre' => 'Jugador ' . Str::random(4),
            'creada_en' => now(),
        ]);

        //creamos la linea con los 10 numeros generados
        Linea::create([
            'tarjeta_id' => $tarjeta->id,
            'numero_linea' => 1,
            'n1' => $numeros[0],
            'n2' => $numeros[1],
            'n3' => $numeros[2],
            'n4' => $numeros[3],
            'n5' => $numeros[4],
            'n6' => $numeros[5],
            'n7' => $numeros[6],
            'n8' => $numeros[7],
            'n9' => $numeros[8],
            'n10' => $numeros[9],
        ]);

        return view('bingo.tarjeta', [ //falta crear la vista
            'tarjeta' => $tarjeta->load('lineas'),
            'codigoJuego' => $codigoJuego,
        ]);
    }

    public function verificarGanador(Request $request, $codigoJuego, $tarjetaId)
    {
        $juego = Juego::where('codigo', $codigoJuego)->firstOrFail();
        $tarjeta = Tarjeta::findOrFail($tarjetaId);

        $numerosSorteados = $juego->numeros_sorteados ?? [];
        $numerosTarjeta = $tarjeta->getNumeros();

        //vemos cuantos numeros de la tarjeta estan entre los numeros sorteados
        $numerosAcertados = array_intersect($numerosTarjeta, $numerosSorteados);
        $esGanador = count($numerosAcertados) === 10;

        if ($esGanador && $juego->estado !== 'finalizado') {
            $juego->update([
                'estado' => 'finalizado',
                'tarjeta_ganadora_id' => $tarjeta->id,
            ]);

            broadcast(new \App\Events\JuegoGanado($codigoJuego, $tarjeta));

            return response()->json([
                'ganador' => true,
                'mensaje' => 'BINGO! Has ganado el juego',
            ]);  
        }
        return response()->json([
            'ganador' => false,
            'acertados' => count($numerosAcertados),
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
