<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CompetidorBloqueado implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $id_competidor;
    public $id_evaluadorAN;
    private $id_competencia;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($id_competidor, $id_evaluadorAN, $id_competencia)
    {
        $this->id_competidor = $id_competidor;
        $this->id_evaluadorAN = $id_evaluadorAN;
        $this->id_competencia = $id_competencia;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('evaluacion.' . $this->id_competencia);
    }
}
