<?php

namespace App\Notifications;

use App\Models\Contract;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ContractRejectedByAdvisor extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Contract $contract,
        public User $advisor,
        public string $reason
    ) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Contrato Rechazado por Asesor')
            ->greeting('Hola ' . $notifiable->name)
            ->line('El asesor ' . $this->advisor->name . ' ha rechazado el contrato #' . ($this->contract->code ?? $this->contract->number))
            ->line('Motivo: ' . $this->reason)
            ->line('El contrato ha vuelto al pool general para reasignación.')
            ->action('Ver Contratos', route('contracts.index'))
            ->salutation('Sistema de Contratos');
    }

    public function toArray($notifiable): array
    {
        return [
            'contract_id' => $this->contract->id,
            'contract_number' => $this->contract->number,
            'contract_code' => $this->contract->code,
            'advisor_id' => $this->advisor->id,
            'advisor_name' => $this->advisor->name,
            'reason' => $this->reason,
            'message' => 'Contrato #' . ($this->contract->code ?? $this->contract->number) . ' rechazado por ' . $this->advisor->name,
        ];
    }
}
