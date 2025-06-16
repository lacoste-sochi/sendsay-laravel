<?php

namespace Rutrue\Sendsay\Services;

use Illuminate\Support\Facades\Storage;
use Rutrue\Sendsay\Contracts\SendsayClientInterface;
use Rutrue\Sendsay\Contracts\SendsayServiceInterface;
use Rutrue\Sendsay\Exceptions\SendsayException;

class SendsayService implements SendsayServiceInterface
{
    public function __construct(
        private SendsayClientInterface $client,
        private string $defaultFrom
    ) {
    }

    public function sendTransactionalMail(array $params): array
    {
        $this->validateParams($params);

        $requestData = [
            'action' => 'issue.send',
            'sendwhen' => $params['sendwhen'] ?? 'now',
            'group' => $params['group'] ?? 'personal',
            'email' => $params['to'],
            'letter' => [
                'from.email' => $params['from'] ?? $this->defaultFrom,
                'subject' => $params['subject'] ?? '', // Почему то игнорируется - если указываю черновик
            ],
        ];

        // Контент письма (если не используется шаблон)
        if (empty($params['draft_id'])) {
            $requestData['letter']['message'] = array_filter([
                'text' => $params['text'] ?? null,
                'html' => $params['html'] ?? null,
            ]);
        } else {
            $requestData['letter']['draft.id'] = $params['draft_id'];
            // Переменные для шаблона
            if (!empty($params['extra'])) {
                $requestData['extra'] = $params['extra'];
            }
        }

        // Вложения
        if (!empty($params['attaches'])) {
            $requestData['letter']['attaches'] = $this->prepareAttachments($params['attaches']);
        }

        return $this->client->request('POST', '', $requestData);
    }

    private function prepareAttachments(array $attachments): array
    {
        return array_map(function ($attachment) {
            // Если передан URL
            if (isset($attachment['url'])) {
                return [
                    'url' => $attachment['url'],
                ];
            }

            // Если передан путь к файлу (локальный или из storage)
            if (isset($attachment['path'])) {
                $filePath = $attachment['path'];
                $content = Storage::exists($filePath)
                    ? Storage::get($filePath)
                    : file_get_contents($filePath);

                return [
                    'name' => $attachment['name'] ?? basename($filePath),
                    'content' => base64_encode($content),
                    'encoding' => 'base64',
                ];
            }

            // Если передан готовый контент
            if (isset($attachment['content'])) {
                return [
                    'name' => $attachment['name'],
                    'content' => $attachment['content']
                ];
            }

            return $attachment;
        }, $attachments);
    }

    private function validateParams(array $params): void
    {
        if (empty($params['to'])) {
            throw new SendsayException('Recipient email is required');
        }

        if (empty($params['draft_id']) && empty($params['subject'])) {
            throw new SendsayException('Either draft_id or subject must be specified');
        }

        if (isset($params['text']) && isset($params['html'])) {
            throw new SendsayException('Нельзя одновременно слать text и html содержимое');
        }

        //if (!empty($params['draft_id']) && (isset($params['text']) || isset($params['html']))) {
        //    // Можно разрешить, если шаблон поддерживает message, но обычно нельзя
        //    //throw new SendsayException('Cannot use both draft_id and direct message content');
        //}

        // Проверка: нельзя одновременно text и html
        if (!empty($params['text']) && !empty($params['html'])) {
            throw new SendsayException('You cannot use both "text" and "html" at the same time. Use only one.');
        }
    }
}