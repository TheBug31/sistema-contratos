<?php

namespace App\Notifications;

use App\Models\Contract;
use App\Models\ContractStatusRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ContractRequestRejected extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public ContractStatusRequest $statusRequest,
        public Contract $contract
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $statusLabels = [
            'limpio' => 'Limpio',
            'lleno' => 'Lleno',
            'venta' => 'Venta',
            'anulado' => 'Anulado',
        ];

        $requestedStatus = $statusLabels[$this->statusRequest->requested_status] ?? $this->statusRequest->requested_status;

        return (new MailMessage)
            ->subject('Solicitud de cambio de estado RECHAZADA')
            ->greeting('¡Hola ' . $notifiable->name . '!')
            ->line('Tu solicitud de cambio de estado ha sido **rechazada**.')
            ->line('**Contrato:** ' . ($this->contract->code ?? 'CL-' . $this->contract->number))
            ->line('**Estado solicitado:** ' . $requestedStatus)
            ->line('**Motivo del rechazo:** ' . ($this->statusRequest->rejection_reason ?? 'No especificado'))
            ->line('**Motivo de tu solicitud:** ' . $this->statusRequest->reason)
            ->action('Ver contrato', url('/contracts/' . $this->contract->id))
            ->line('Si tienes dudas, contacta a tu gerente.');
    }
}
