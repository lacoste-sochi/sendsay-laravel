<?php

namespace Tests\Unit;

use Rutrue\Sendsay\Contracts\SendsayClientInterface;
use Rutrue\Sendsay\Contracts\SendsayServiceInterface;
use Tests\TestCase;
use Illuminate\Support\Facades\Storage;
use Rutrue\Sendsay\Facades\Sendsay;
use Rutrue\Sendsay\Services\SendsayService;
use Rutrue\Sendsay\Exceptions\SendsayException;
use Mockery;

class SendsayServiceTest extends TestCase
{
    protected $clientMock;
    protected $service;

    protected function setUp(): void
    {
        parent::setUp();

        // Создаем мок клиента
        $this->clientMock = Mockery::mock(SendsayClientInterface::class);

        // Инициализируем сервис с моком
        $this->service = new SendsayService(
            $this->clientMock,
            'noreply@example.com'
        );

        // Подменяем фасад
        Sendsay::swap($this->service);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_sends_basic_text_email()
    {
        $this->clientMock->shouldReceive('request')
            ->once()
            ->with('POST', '', [
                'action' => 'issue.send',
                'sendwhen' => 'now',
                'group' => 'personal',
                'email' => 'info@rutrue.ru',
                'letter' => [
                    'from.email' => 'noreply@example.com',
                    'subject' => 'Ваш заказ принят',
                    'message' => [
                        'text' => 'Спасибо за заказ №12345',
                    ]
                ]
            ])
            ->andReturn(['status' => 'ok']);

        $response = $this->service->sendTransactionalMail([
            'to' => 'info@rutrue.ru',
            'subject' => 'Ваш заказ принят',
            'text' => 'Спасибо за заказ №12345',
        ]);

        $this->assertEquals(['status' => 'ok'], $response);
    }

    /** @test */
    public function it_sends_html_email_with_custom_from()
    {
        $this->clientMock->shouldReceive('request')
            ->once()
            ->with('POST', '', Mockery::on(function ($params) {
                return $params['letter']['from']['email'] === 'custom@example.com'
                    && !empty($params['letter']['message']['html']);
            }))
            ->andReturn(['status' => 'ok']);

        $response = $this->service->sendTransactionalMail([
            'from' => 'custom@example.com',
            'to' => 'info@rutrue.ru',
            'subject' => 'Ваш заказ принят',
            'html' => '<h1>Спасибо за заказ!</h1>',
        ]);

        $this->assertEquals(['status' => 'ok'], $response);
    }

    /** @test */
    public function it_sends_email_with_template()
    {
        $this->clientMock->shouldReceive('request')
            ->once()
            ->with('POST', '', [
                'action' => 'issue.send',
                'sendwhen' => 'now',
                'group' => 'personal',
                'email' => 'info@rutrue.ru',
                'letter' => [
                    'from.email' => 'noreply@example.com',
                    'subject' => 'Ваш заказ принят',
                    'draft.id' => '85',
                ],
                'extra' => [
                    'foo' => 'Содержимое которое я написал в foo',
                ]
            ])
            ->andReturn(['status' => 'ok']);

        $response = $this->service->sendTransactionalMail([
            'to' => 'info@rutrue.ru',
            'draft_id' => '85',
            'subject' => 'Ваш заказ принят',
            'extra' => [
                'foo' => 'Содержимое которое я написал в foo',
            ],
        ]);

        $this->assertEquals(['status' => 'ok'], $response);
    }

    /** @test */
    public function it_handles_attachments_correctly()
    {
        Storage::fake('public');
        Storage::disk('public')->put('file.pdf', 'PDF content');

        $this->clientMock->shouldReceive('request')
            ->once()
            ->with('POST', '', Mockery::on(function ($params) {
                return isset($params['letter']['attaches'])
                    && count($params['letter']['attaches']) === 2;
            }))
            ->andReturn(['status' => 'ok']);

        $response = $this->service->sendTransactionalMail([
            'to' => 'info@rutrue.ru',
            'subject' => 'Documents',
            'text' => 'See attachments',
            'attaches' => [
                [
                    'name' => 'Document.pdf',
                    'path' => storage_path('app/public/file.pdf')
                ],
                [
                    'url' => 'https://example.com/report.pdf'
                ]
            ]
        ]);

        $this->assertEquals(['status' => 'ok'], $response);
    }

    /** @test */
    public function it_throws_exception_when_no_recipient()
    {
        $this->expectException(SendsayException::class);
        $this->expectExceptionMessage('Recipient email is required');

        $this->service->sendTransactionalMail([
            'subject' => 'No recipient',
            'text' => 'Test'
        ]);
    }

    /** @test */
    public function it_throws_exception_when_no_content()
    {
        $this->expectException(SendsayException::class);
        $this->expectExceptionMessage('Either draft_id or subject must be specified');

        $this->service->sendTransactionalMail([
            'to' => 'info@rutrue.ru'
        ]);
    }

    /** @test */
    public function it_throws_exception_when_both_text_and_html_provided()
    {
        $this->expectException(SendsayException::class);
        $this->expectExceptionMessage('You cannot use both "text" and "html" at the same time');

        $this->service->sendTransactionalMail([
            'to' => 'info@rutrue.ru',
            'subject' => 'Test',
            'text' => 'Text',
            'html' => '<p>HTML</p>'
        ]);
    }

    /** @test */
    public function facade_works_correctly()
    {
        $this->clientMock->shouldReceive('request')
            ->once()
            ->andReturn(['status' => 'ok']);

        $response = Sendsay::sendTransactionalMail([
            'to' => 'info@rutrue.ru',
            'subject' => 'Test',
            'text' => 'Test'
        ]);

        $this->assertEquals(['status' => 'ok'], $response);
    }

    /** @test */
    public function it_can_be_injected_via_dependency_injection()
    {
        $this->clientMock->shouldReceive('request')
            ->once()
            ->andReturn(['status' => 'ok']);

        $result = app()->call([$this, 'sendEmailDependency'], [
            'emailDriver' => $this->service
        ]);

        $this->assertEquals(['status' => 'ok'], $result);
    }

    // Метод для тестирования инъекции зависимостей
    public function sendEmailDependency(SendsayServiceInterface $emailDriver)
    {
        return $emailDriver->sendTransactionalMail([
            'to' => 'info@rutrue.ru',
            'subject' => 'Ваш заказ принят',
            'text' => 'Спасибо за заказ №12345',
        ]);
    }
}
