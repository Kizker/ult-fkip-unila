<?php

namespace App\Notifications;

use App\Enums\RequestStatus;
use App\Models\Request as UltRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class RequestStatusChanged extends Notification
{
    use Queueable;

    public function __construct(
        public readonly UltRequest $request,
        public readonly RequestStatus $from,
        public readonly RequestStatus $to,
        public readonly ?string $note = null
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'request_id' => $this->request->id,
            'service_title' => $this->request->display_title,
            'from_status' => $this->from->value,
            'to_status' => $this->to->value,
            'note' => $this->note,
        ];
    }
}
