<?php
/**
 * Visitor Entity
 *
 * Represents an anonymous or known website visitor
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
 * Visitor - Website visitor (anonymous or known)
 */
class Visitor implements JsonSerializable
{
    private string $id;
    private ?string $contactId;
    private ?string $email;
    private string $firstVisit;
    private ?string $lastVisit;
    private int $visitCount;
    private int $pageViewCount;
    private array $lastKnownUrl;
    private string $source;
    private string $device;
    private string $browser;
    private bool $isActive;

    public function __construct(string $id)
    {
        $this->id = $id;
        $this->firstVisit = date('Y-m-d H:i:s');
        $this->lastVisit = date('Y-m-d H:i:s');
        $this->visitCount = 1;
        $this->pageViewCount = 0;
        $this->isActive = true;
        $this->contactId = null;
        $this->email = null;
        $this->lastKnownUrl = [];
        $this->source = '';
        $this->device = '';
        $this->browser = '';
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getContactId(): ?string
    {
        return $this->contactId;
    }

    public function linkToContact(string $contactId, ?string $email = null): self
    {
        $this->contactId = $contactId;
        if ($email) {
            $this->email = $email;
        }
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getFirstVisit(): string
    {
        return $this->firstVisit;
    }

    public function getLastVisit(): ?string
    {
        return $this->lastVisit;
    }

    public function updateLastVisit(): self
    {
        $this->lastVisit = date('Y-m-d H:i:s');
        return $this;
    }

    public function getVisitCount(): int
    {
        return $this->visitCount;
    }

    public function incrementVisit(): self
    {
        $this->visitCount++;
        $this->updateLastVisit();
        return $this;
    }

    public function getPageViewCount(): int
    {
        return $this->pageViewCount;
    }

    public function incrementPageViews(int $count = 1): self
    {
        $this->pageViewCount += $count;
        return $this;
    }

    public function getLastKnownUrl(): array
    {
        return $this->lastKnownUrl;
    }

    public function setLastKnownUrl(array $url): self
    {
        $this->lastKnownUrl = $url;
        return $this;
    }

    public function getSource(): string
    {
        return $this->source;
    }

    public function setSource(string $source): self
    {
        $this->source = $source;
        return $this;
    }

    public function getDevice(): string
    {
        return $this->device;
    }

    public function setDevice(string $device): self
    {
        $this->device = $device;
        return $this;
    }

    public function getBrowser(): string
    {
        return $this->browser;
    }

    public function setBrowser(string $browser): self
    {
        $this->browser = $browser;
        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setActive(bool $active): self
    {
        $this->isActive = $active;
        return $this;
    }

    public function isKnown(): bool
    {
        return $this->contactId !== null;
    }

    public function isNew(): bool
    {
        return $this->visitCount <= 1;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'contact_id' => $this->contactId,
            'email' => $this->email,
            'first_visit' => $this->firstVisit,
            'last_visit' => $this->lastVisit,
            'visit_count' => $this->visitCount,
            'page_view_count' => $this->pageViewCount,
            'last_known_url' => $this->lastKnownUrl,
            'source' => $this->source,
            'device' => $this->device,
            'browser' => $this->browser,
            'is_active' => $this->isActive,
        ];
    }

    public static function fromArray(array $data): self
    {
        $visitor = new self($data['id']);

        if (isset($data['contact_id'])) {
            $visitor->linkToContact($data['contact_id'], $data['email'] ?? null);
        }
        if (isset($data['source'])) {
            $visitor->setSource($data['source']);
        }
        if (isset($data['device'])) {
            $visitor->setDevice($data['device']);
        }
        if (isset($data['browser'])) {
            $visitor->setBrowser($data['browser']);
        }
        if (isset($data['is_active'])) {
            $visitor->setActive($data['is_active']);
        }

        return $visitor;
    }
}