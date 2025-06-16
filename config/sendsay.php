<?php

return [
    // Базовый URL API Sendsay (без логина в конце)
    'base_url' => env('SENDSAY_BASE_URL', 'https://api.sendsay.ru/general/api/v100/json/'),

    // Логин аккаунта Sendsay (будет добавлен к base_url)
    'account' => env('SENDSAY_ACCOUNT'),

    // API-ключ из личного кабинета Sendsay
    'api_key' => env('SENDSAY_API_KEY'),

    // Email отправителя по умолчанию
    'default_from' => env('SENDSAY_DEFAULT_FROM'),

    // Таймаут запросов к API (сек)
    'timeout' => env('SENDSAY_TIMEOUT', 10),
];