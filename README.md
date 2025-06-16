# Laravel Sendsay Package

Пакет для работы с транзакционными письмами через API sendsay.ru

## Установка

```bash
composer require rutrue/laravel-sendsay
```

Публикация конфига:
```bash
php artisan vendor:publish --provider="Rutrue\Sendsay\SendsayServiceProvider" --tag="config"
```

## Конфигурация (.env)

```ini
SENDSAY_DEFAULT_FROM=noreply@example.com
SENDSAY_ACCOUNT=your_account
SENDSAY_API_KEY=your_api_key
```

## Использование

### Базовый пример
```php
use Rutrue\Sendsay\Facades\Sendsay;

Sendsay::sendTransactionalMail([
    'to' => 'client@example.com', // Кому 
    'subject' => "Сервис ООО 'Рога и Копыта'", // Тема письма 

    'text' => 'Спасибо за заказ! Номер: 12345' // Текст письма 
    // или
    'html' => '<h1>Спасибо за заказ!</h1><p>Номер: 12345</p>', // Текст письма HTML
]);
```

### Использование шаблонов
```php
Sendsay::sendTransactionalMail([
    'to' => 'client@example.com', // Кому 
    'draft_id' => '123', // ID шаблона, легко можно узнать из ЛК, в ссылке на странице шаблоне будет его ID 
    'extra' => [ // Переменные и их значения, в шаблоне переменные размещаются в тексте так: Моя переменная в шаблоне - [% foo %]
        'name' => 'Иванов Иван',
        'order_id' => '456'
    ]
]);
```

### Отправка с вложениями
```php
Sendsay::sendTransactionalMail([
    'to' => 'client@example.com',
    'subject' => 'Documents',
    'attaches' => [
        [    // локальные файлы
            'name' => 'Document.pdf', // необязательно, без указание файла будет название файла оригинальное
            'path' => storage_path('app/documents/file.pdf')
        ],
        [   // ссылки на файлы которые необходимо прикрепить к письму
            'url' => 'https://example.com/report.pdf'
        ]
    ]
]);
```

### Внедрение зависимости сервиса
```php
use Rutrue\Sendsay\Contracts\SendsayServiceInterface;

class OrderController
{
    public function sendReceipt(SendsayServiceInterface $sendsay)
    {
        $sendsay->sendTransactionalMail([...]);
    }
}
```



## Интеграция с Laravel Notifications

Создайте канал уведомлений:

```php
namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Rutrue\Sendsay\Messages\SendsayMessage;

class InvoicePaid extends Notification
{
    public function via($notifiable)
    {
        return ['sendsay'];
    }

    public function toSendsay($notifiable)
    {
        return (new SendsayMessage)
            ->subject('Счет оплачен')
            ->html("<h1>Заказ #{$this->orderId} оплачен!</h1>")
            ->from('billing@example.com')
            ->attaches([
                ['path' => storage_path('app/invoices/invoice.pdf')]
            ]);
    }
}
```

Отправка уведомления:

```php
$user->notify(new InvoicePaid($orderId));

// Или для нескольких пользователей
Notification::send($users, new InvoicePaid($orderId));
```

## Рекомендации для Production

1. Обрабатывайте ошибки:
```php
try {
    Sendsay::sendTransactionalMail([...]);
} catch (\Rutrue\Sendsay\Exceptions\SendsayException $e) {
    // Логирование ошибки
}
```


### Методы контроллера которые успешно прошли испытания в тестовой среде

```php
class TestSendayController extends Controller
{
    public function sendEmail()
    {
        // Только текст
        //return Sendsay::sendTransactionalMail([
        //    'to' => 'info@rutrue.ru',
        //    'subject' => 'Ваш заказ принят',
        //    'text' => 'Спасибо за заказ №12345',
        //    'attaches' => [
        //        [
        //            'name' => 'Мой файл.pdf', // необязательно, без указание файла будет название файла оригинальное
        //            'path' => storage_path('app/public/file.pdf') // Путь в storage
        //        ],
        //        [
        //            'url' => 'https://t3.ftcdn.net/jpg/03/06/48/88/360_F_306488873_EA34BzCGxmTmn3DRhDcrYiani5Vp8vSD.jpg',
        //        ]
        //    ]
        //]);

        // HTML-письмо
        //return Sendsay::sendTransactionalMail([
        //    'from' => 'info@rutrue.ru',
        //    'to' => 'info@rutrue.ru',
        //    'subject' => 'Ваш заказ принят',
        //    'html' => '<h1>Спасибо за заказ!</h1><p>Номер: 12345</p>',
        //    'attaches' => [
        //        [
        //            'name' => 'Мой файл.pdf', // необязательно, без указание файла будет название файла оригинальное
        //            'path' => storage_path('app/public/file.pdf') // Путь в storage
        //        ],
        //        [
        //            'url' => 'https://t3.ftcdn.net/jpg/03/06/48/88/360_F_306488873_EA34BzCGxmTmn3DRhDcrYiani5Vp8vSD.jpg',
        //        ]
        //    ]
        //]);

        // Отправка с шаблоном
        return Sendsay::sendTransactionalMail([
            'to' => 'info@rutrue.ru',
            'from' => 'info@rutrue.ru',
            'draft_id' => '85', // ID шаблона
            'subject' => 'Ваш заказ принят',
            'text' => 'Привет',
            'extra' => [
                'foo' => 'Содержимое которое я написал в foo',
            ],
            'attaches' => [
                [
                    'name' => 'Мой файл.pdf', // необязательно, без указание файла будет название файла оригинальное
                    'path' => storage_path('app/public/file.pdf') // Путь в storage
                ],
                [
                    'url' => 'https://t3.ftcdn.net/jpg/03/06/48/88/360_F_306488873_EA34BzCGxmTmn3DRhDcrYiani5Vp8vSD.jpg',
                ]
            ]
        ]);
    }


    public function sendEmailDependency(SendsayServiceInterface $emailDriver)
    {
        // Отправка через внедрение
        return $emailDriver->sendTransactionalMail([
            'to' => 'info@rutrue.ru',
            'subject' => 'Ваш заказ принят',
            'text' => 'Спасибо за заказ №12345',
        ]);
    }

    public function sendEmailNotification()
    {
        // отправляем как уведомление

        // Создаем или находим пользователя
        $user = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'phone' => '79996502245',
                'password' => bcrypt('password'),
                'email' => 'info@rutrue.ru'
            ]
        );

        try {
            // Вариант 1: Получаем результат через канал напрямую
            $notification = new InvoicePaid('12345');
            $channel = app(SendsayChannel::class);
            $apiResponse = $channel->send($user, $notification);

            // Вариант 2: Через стандартный механизм уведомлений
            // $user->notify($notification);
            // $apiResponse = ['status' => 'sent']; // Если не нужен ответ API

            // Логируем для проверки
            Log::info('EMAIL отправлено', [
                'phone' => $user->phone,
                'api_response' => $apiResponse
            ]);

            return response()->json([
                'status' => 'success',
                'message' => "EMAIL отправлено на почту: {$user->email}",
                'api_response' => $apiResponse
            ]);

        } catch (\Exception $e) {
            Log::error('Ошибка отправки EMAIL', [
                'phone' => $user->phone,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => "Ошибка: " . $e->getMessage()
            ], 500);
        }

    }
}
```
