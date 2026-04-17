<?php

namespace App\Jobs;

use App\Models\Reservations;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendReservationConfirmation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Reservations $reservation
    ) {}

    public function handle(): void
    {
        //logica para enviar el correo de confirmacion de reserva
        Log::info('Enviando correo de confirmacion de reserva para la reserva: ' . $this->reservation->id);
    }
}
