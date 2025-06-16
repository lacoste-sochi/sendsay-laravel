<?php

namespace Rutrue\Sendsay\Notifications;

class SendsayMessage
{
    public string $contentType = 'html';
    public ?string $subject = null;
    public ?string $content = null;
    public ?string $draftId = null;
    public ?string $from = null;
    public ?string $sendWhen = null;
    public ?string $group = null;
    public ?array $extra = null;
    public ?array $attaches = null;

    public function __construct(string $content = '')
    {
        $this->content = $content;
    }

    public function subject(string $subject): self
    {
        $this->subject = $subject;
        return $this;
    }

    public function text(string $content): self
    {
        $this->content = $content;
        $this->contentType = 'text';
        return $this;
    }

    public function html(string $content): self
    {
        $this->content = $content;
        $this->contentType = 'html';
        return $this;
    }

    public function draft(string $draftId): self
    {
        $this->draftId = $draftId;
        return $this;
    }

    public function from(string $from): self
    {
        $this->from = $from;
        return $this;
    }

    public function sendWhen(string $sendWhen): self
    {
        $this->sendWhen = $sendWhen;
        return $this;
    }

    public function group(string $group): self
    {
        $this->group = $group;
        return $this;
    }

    public function extra(array $extra): self
    {
        $this->extra = $extra;
        return $this;
    }

    public function attaches(array $attaches): self
    {
        $this->attaches = $attaches;
        return $this;
    }
}