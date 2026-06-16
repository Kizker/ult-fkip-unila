<?php

namespace App\Notifications;

use App\Models\Request as UltRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DocumentSignerStepAssigned extends Notification
{
    use Queueable;

    public function __construct(
        public readonly UltRequest $request,
        public readonly string $signerRole,
        public readonly int $orderIndex,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'doc.signer_assigned',
            'request_id' => $this->request->id,
            'service_title' => $this->request->display_title,
            'signer_role' => $this->signerRole,
            'order_index' => $this->orderIndex,
        ];
    }
}
