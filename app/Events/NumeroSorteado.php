<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NumeroSorteado implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public $codigoJuego;
    public $numero;
    public $numerosSorteados;
    public function __construct($codigoJuego, $numero, $numerosSorteados)
    {
        $this->codigoJuego = $codigoJuego;
        $this->numero = $numero;
        $this->numerosSorteados = $numerosSorteados;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array   
    {
        return [new Channel('bingo.' . $this->codigoJuego)];
    }

    public function broadcastAs()
    {
        return 'numero.sorteado';
    }
}
