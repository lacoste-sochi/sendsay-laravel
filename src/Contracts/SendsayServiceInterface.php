<?php

namespace Rutrue\Sendsay\Contracts;

interface SendsayServiceInterface
{
    /**
     * Отправка индивидуального транзакционного письма через Sendsay API.
     *
     * @param array $params
     * @return array
     */
    public function sendTransactionalMail(array $params): array;

}