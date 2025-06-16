<?php

namespace Rutrue\Sendsay\Notifications;

use Illuminate\Notifications\Notification;
use Rutrue\Sendsay\Services\SendsayService;
use Rutrue\Sendsay\Notifications\SendsayMessage;

class SendsayChannel
{
    public function __construct(
        private SendsayService $sendsayService
    ) {
    }

    public function send($notifiable, Notification $notification)
    {
        $message = $notification->toSendsay($notifiable);

        if (is_string($message)) {
            $message = new SendsayMessage($message);
        }

        $params = [
            'to' => $this->getRecipient($notifiable),
        ];

        if ($message->draftId) {
            $params['draft_id'] = $message->draftId;
        } else {
            $params['subject'] = $message->subject;
            $params[$message->contentType] = $message->content;
        }

        if ($message->from) {
            $params['from'] = $message->from;
        }

        if ($message->sendWhen) {
            $params['sendwhen'] = $message->sendWhen;
        }

        if ($message->group) {
            $params['group'] = $message->group;
        }

        if ($message->extra) {
            $params['extra'] = $message->extra;
        }

        if ($message->attaches) {
            $params['attaches'] = $message->attaches;
        }

        return $this->sendsayService->sendTransactionalMail($params);
    }

    protected function getRecipient($notifiable)
    {
        return $notifiable->routeNotificationFor('sendsay') ?: $notifiable->email;
    }
}
