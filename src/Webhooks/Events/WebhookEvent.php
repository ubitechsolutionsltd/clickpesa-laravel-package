<?php

declare(strict_types=1);

namespace ClickPesa\Webhooks\Events;

abstract class WebhookEvent
{
    protected string $event;
    protected array $data;
    protected array $rawPayload;

    public function __construct(string $event, array $data, array $rawPayload)
    {
        $this->event = $event;
        $this->data = $data;
        $this->rawPayload = $rawPayload;
    }

    public function getEvent(): string
    {
        return $this->event;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getRawPayload(): array
    {
        return $this->rawPayload;
    }

    public function getId(): ?string
    {
        return $this->data['id'] ?? null;
    }

    public function getOrderReference(): ?string
    {
        return $this->data['orderReference'] ?? null;
    }

    public function getStatus(): ?string
    {
        return $this->data['status'] ?? null;
    }

    public function getMessage(): ?string
    {
        return $this->data['message'] ?? null;
    }

    public function getChannel(): ?string
    {
        return $this->data['channel'] ?? null;
    }
}
