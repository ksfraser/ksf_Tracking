<?php
/**
 * TrackingService
 *
 * Processes and manages tracking events
 *
 * @package Ksfraser\Tracking\Service
 * @author KSFII
 * @license MIT
 */

declare(strict_types=1);

namespace Ksfraser\Tracking\Service;

use Ksfraser\Tracking\Entity\TrackingEvent;
use Ksfraser\Tracking\Entity\Visitor;
use Ksfraser\Tracking\Repository\TrackingRepositoryInterface;

/**
 * TrackingService - Track visitors and events
 */
class TrackingService
{
    private TrackingRepositoryInterface $repository;
    private ?string $currentVisitorId;

    public function __construct(TrackingRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get or create visitor by ID
     */
    public function getVisitor(string $visitorId): ?Visitor
    {
        return $this->repository->getVisitor($visitorId);
    }

    /**
     * Start tracking session
     */
    public function startSession(?string $visitorId = null): string
    {
        if ($visitorId === null) {
            $visitorId = $this->generateVisitorId();
        }

        $visitor = $this->repository->getVisitor($visitorId);

        if ($visitor === null) {
            $visitor = new Visitor($visitorId);
            $this->repository->saveVisitor($visitor);
        }

        $this->currentVisitorId = $visitorId;
        return $visitorId;
    }

    /**
     * Track page view
     */
    public function trackPageView(
        string $url,
        ?string $referrer = null,
        ?string $userAgent = null,
        ?string $ipAddress = null
    ): TrackingEvent {
        $visitorId = $this->getCurrentVisitorId();
        
        $event = new TrackingEvent(
            uniqid('evt_'),
            TrackingEvent::EVENT_PAGE_VIEW,
            $url
        );

        if ($visitorId) {
            $event->setVisitorId($visitorId);
        }

        if ($referrer) {
            $event->setReferrer($referrer);
        }
        if ($userAgent) {
            $event->setUserAgent($userAgent);
        }
        if ($ipAddress) {
            $event->setIpAddress($ipAddress);
        }

        $this->repository->saveEvent($event);

        if ($visitorId) {
            $this->repository->incrementPageViews($visitorId);
        }

        return $event;
    }

    /**
     * Track form view
     */
    public function trackFormView(
        string $formId,
        string $url,
        ?string $userAgent = null,
        ?string $ipAddress = null
    ): TrackingEvent {
        $visitorId = $this->getCurrentVisitorId();
        
        $event = new TrackingEvent(
            uniqid('evt_'),
            TrackingEvent::EVENT_FORM_VIEW,
            $url
        );
        $event->setEventData(['form_id' => $formId]);

        if ($visitorId) {
            $event->setVisitorId($visitorId);
        }
        if ($userAgent) {
            $event->setUserAgent($userAgent);
        }
        if ($ipAddress) {
            $event->setIpAddress($ipAddress);
        }

        $this->repository->saveEvent($event);
        return $event;
    }

    /**
     * Track form submit - link to contact
     */
    public function trackFormSubmit(
        string $formId,
        string $url,
        string $contactId,
        ?string $email = null,
        array $formData = [],
        ?string $userAgent = null,
        ?string $ipAddress = null
    ): TrackingEvent {
        $visitorId = $this->getCurrentVisitorId();
        
        $event = new TrackingEvent(
            uniqid('evt_'),
            TrackingEvent::EVENT_FORM_SUBMIT,
            $url
        );
        $event->setEventData(array_merge(['form_id' => $formId], $formData));
        $event->setContactId($contactId);

        if ($visitorId) {
            $event->setVisitorId($visitorId);
            $this->repository->linkVisitorToContact($visitorId, $contactId, $email);
        }
        if ($userAgent) {
            $event->setUserAgent($userAgent);
        }
        if ($ipAddress) {
            $event->setIpAddress($ipAddress);
        }

        $this->repository->saveEvent($event);
        return $event;
    }

    /**
     * Track link click
     */
    public function trackLinkClick(
        string $url,
        string $linkId,
        ?string $userAgent = null,
        ?string $ipAddress = null
    ): TrackingEvent {
        $visitorId = $this->getCurrentVisitorId();
        
        $event = new TrackingEvent(
            uniqid('evt_'),
            TrackingEvent::EVENT_LINK_CLICK,
            $url
        );
        $event->setEventData(['link_id' => $linkId]);

        if ($visitorId) {
            $event->setVisitorId($visitorId);
        }
        if ($userAgent) {
            $event->setUserAgent($userAgent);
        }
        if ($ipAddress) {
            $event->setIpAddress($ipAddress);
        }

        $this->repository->saveEvent($event);
        return $event;
    }

    /**
     * Identify visitor by email
     */
    public function identify(string $email, ?string $contactId = null): ?string
    {
        $visitorId = $this->getCurrentVisitorId();

        if (!$visitorId) {
            return null;
        }

        $existing = $this->repository->findVisitorByEmail($email);
        if ($existing && $existing->getId() !== $visitorId) {
            $this->repository->mergeVisitors($existing->getId(), $visitorId);
            return $existing->getId();
        }

        if ($contactId === null) {
            $contactId = $this->repository->findContactByEmail($email);
        }

        $this->repository->linkVisitorToContact($visitorId, $contactId ?? '', $email);
        return $visitorId;
    }

    /**
     * Get visitor statistics
     */
    public function getStatistics(?DateTime $since = null): array
    {
        return $this->repository->getStatistics($since);
    }

    /**
     * Get recent events
     */
    public function getRecentEvents(int $limit = 100): array
    {
        return $this->repository->getRecentEvents($limit);
    }

    /**
     * Generate tracking JavaScript
     */
    public function generateTrackingScript(string $trackingUrl, string $formId): string
    {
        $script = <<<JS
(function() {
    var visitorId = localStorage.getItem('ksf_visitor_id');
    if (!visitorId) {
        visitorId = 'v_' + Math.random().toString(36).substr(2, 9);
        localStorage.setItem('ksf_visitor_id', visitorId);
    }
    
    var data = {
        visitor_id: visitorId,
        url: window.location.href,
        referrer: document.referrer,
        user_agent: navigator.userAgent,
        screen_width: screen.width,
        screen_height: screen.height,
        form_id: '{$formId}'
    };
    
    var img = new Image();
    img.src = '{$trackingUrl}?data=' + encodeURIComponent(JSON.stringify(data));
})();
JS;
        return $script;
    }

    private function getCurrentVisitorId(): ?string
    {
        return $this->currentVisitorId;
    }

    private function generateVisitorId(): string
    {
        return 'v_' . bin2hex(random_bytes(8));
    }
}