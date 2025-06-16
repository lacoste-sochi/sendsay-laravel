<?php

namespace Rutrue\Sendsay\Contracts;

interface SendsayClientInterface
{
    /**
     * Отправка запроса к API Sendsay
     *
     * @param string $method HTTP-метод (не используется, оставлен для совместимости)
     * @param string $endpoint Не используется, оставлен для совместимости
     * @param array $data Данные для отправки
     * @return array Ответ API
     */
    public function request(string $method, string $endpoint, array $data = []): array;
}
