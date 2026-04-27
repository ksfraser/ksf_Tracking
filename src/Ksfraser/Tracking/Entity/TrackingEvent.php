<?php
/**
 * TrackingEvent Entity
 *
 * Represents a visitor tracking event
 *
 * @package Ksfraser\Tracking\Entity
 * @author KSFII
 * @license MIT
 */

declare(strict_types=1);

namespace Ksfraser\Tracking\Entity;

use DateTime;
use DateTimeInterface;
use JsonSerializable;

/**
 * TrackingEvent - Website visitor event
 */
class TrackingEvent implements JsonSerializable
{
    private string $id;
    private ?string $visitorId;
    private ?string $contactId;
    private string $eventType;
    private string $url;
    private ?string $referrer;
    private ?string $ipAddress;
    private ?string $userAgent;
    private array $eventData;
    private DateTime $createdAt;

    public const EVENT_PAGE_VIEW = 'page_view';
    public const EVENT_FORM_VIEW = 'form_view';
    public const EVENT_FORM_SUBMIT = 'form_submit';
    public const EVENT_LINK_CLICK = 'link_click';
    public const EVENT_EMAIL_OPEN = 'email_open';
    public const EVENT_EMAIL_CLICK = 'email_click';

    public function __construct(
        string $id,
        string $eventType,
        string $url
    ) {
        $this->id = $id;
        $this->eventType = $eventType;
        $this->url = $url;
        $this->eventData = [];
        $this->createdAt = new DateTime();
        $this->contactId = null;
        $this->visitorId = null;
        $this->referrer = null;
        $this->ipAddress = null;
        $this->userAgent = null;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getVisitorId(): ?string
    {
        return $this->visitorId;
    }

    public function setVisitorId(?string $visitorId): self
    {
        $this->visitorId = $visitorId;
        return $this;
    }

    public function getContactId(): ?string
    {
        return $this->contactId;
    }

    public function setContactId(?string $contactId): self
    {
        $this->contactId = $contactId;
        return $this;
    }

    public function getEventType(): string
    {
        return $this->eventType;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getReferrer(): ?string
    {
        return $this->referrer;
    }

    public function setReferrer(?string $referrer): self
    {
        $this->referrer = $referrer;
        return $this;
    }

    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function setIpAddress(?string $ipAddress): self
    {
        $this->ipAddress = $ipAddress;
        return $this;
    }

    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

    public function setUserAgent(?string $userAgent): self
    {
        $this->userAgent = $userAgent;
        return $this;
    }

    public function getEventData(): array
    {
        return $this->eventData;
    }

    public function setEventData(array $data): self
    {
        $this->eventData = $data;
        return $this;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function isAnonymous(): bool
    {
        return $this->contactId === null;
    }

    public function isKnown(): bool
    {
        return $this->contactId !== null;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'visitor_id' => $this->visitorId,
            'contact_id' => $this->contactId,
            'event_type' => $this->eventType,
            'url' => $this->url,
            'referrer' => $this->referrer,
            'ip_address' => $this->ipAddress,
            'user_agent' => $this->userAgent,
            'event_data' => $this->eventData,
            'created_at' => $this->createdAt->format(DateTimeInterface::ATOM),
        ];
    }

    public static function fromArray(array $data): self
    {
        $event = new self(
            $data['id'],
            $data['event_type'],
            $data['url']
        );

        if (isset($data['visitor_id'])) {
            $event->setVisitorId($data['visitor_id']);
        }
        if (isset($data['contact_id'])) {
            $event->setContactId($data['contact_id']);
        }
        if (isset($data['referrer'])) {
            $event->setReferrer($data['referrer']);
        }
        if (isset($data['ip_address'])) {
            $event->setIpAddress($data['ip_address']);
        }
        if (isset($data['user_agent'])) {
            $event->setUserAgent($data['user_agent']);
        }
        if (isset($data['event_data'])) {
            $event->setEventData($data['event_data']);
        }

        return $event;
    }
}