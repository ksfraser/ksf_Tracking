<?php
/**
 * TrackingRepositoryInterface
 *
 * @package Ksfraser\Tracking\Repository
 * @author KSFII
 * @license MIT
 */

declare(strict_types=1);

namespace Ksfraser\Tracking\Repository;

use Ksfraser\Tracking\Entity\TrackingEvent;
use Ksfraser\Tracking\Entity\Visitor;

/**
 * Repository interface for tracking persistence
 */
interface TrackingRepositoryInterface
{
    public function saveVisitor(Visitor $visitor): void;
    public function getVisitor(string $id): ?Visitor;
    public function findVisitorByEmail(string $email): ?Visitor;
    public function findContactByEmail(string $email): ?string;
    public function linkVisitorToContact(string $visitorId, string $contactId, ?string $email = null): void;
    public function mergeVisitors(string $fromId, string $toId): void;
    public function incrementPageViews(string $visitorId): void;

    public function saveEvent(TrackingEvent $event): void;
    public function getEvent(string $id): ?TrackingEvent;
    public function getRecentEvents(int $limit = 100): array;
    public function getEventsForVisitor(string $visitorId): array;
    public function getEventsForContact(string $contactId): array;

    public function getStatistics(?\DateTime $since = null): array;
}