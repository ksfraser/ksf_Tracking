<?php
/**
 * TrackingEntityTest
 *
 * @package Ksfraser\Tracking\Tests
 * @author KSFII
 * @license MIT
 */

declare(strict_types=1);

namespace Ksfraser\Tracking\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Ksfraser\Tracking\Entity\TrackingEvent;
use Ksfraser\Tracking\Entity\Visitor;

final class TrackingEntityTest extends TestCase
{
    public function testTrackingEventCreation(): void
    {
        $event = new TrackingEvent('evt_001', TrackingEvent::EVENT_PAGE_VIEW, '/test');
        
        $this->assertEquals('evt_001', $event->getId());
        $this->assertEquals(TrackingEvent::EVENT_PAGE_VIEW, $event->getEventType());
        $this->assertEquals('/test', $event->getUrl());
    }

    public function testVisitorCreation(): void
    {
        $visitor = new Visitor('v_123');
        
        $this->assertEquals('v_123', $visitor->getId());
        $this->assertEquals(1, $visitor->getVisitCount());
        $this->assertFalse($visitor->isKnown());
    }

    public function testVisitorLinkToContact(): void
    {
        $visitor = new Visitor('v_123');
        $visitor->linkToContact('c_001', 'test@example.com');
        
        $this->assertEquals('c_001', $visitor->getContactId());
        $this->assertEquals('test@example.com', $visitor->getEmail());
        $this->assertTrue($visitor->isKnown());
    }

    public function testVisitorPageViews(): void
    {
        $visitor = new Visitor('v_123');
        $visitor->incrementPageViews(3);
        
        $this->assertEquals(3, $visitor->getPageViewCount());
    }

    public function testTrackingEventAnonymous(): void
    {
        $event = new TrackingEvent('evt_001', TrackingEvent::EVENT_PAGE_VIEW, '/test');
        
        $this->assertTrue($event->isAnonymous());
        $this->assertFalse($event->isKnown());
    }

    public function testTrackingEventKnown(): void
    {
        $event = new TrackingEvent('evt_001', TrackingEvent::EVENT_PAGE_VIEW, '/test');
        $event->setContactId('c_001');
        
        $this->assertFalse($event->isAnonymous());
        $this->assertTrue($event->isKnown());
    }

    public function testEventData(): void
    {
        $event = new TrackingEvent('evt_001', TrackingEvent::EVENT_FORM_VIEW, '/form');
        $event->setEventData(['form_id' => 'contact_form', 'fields' => ['name', 'email']]);
        
        $this->assertEquals('contact_form', $event->getEventData()['form_id']);
    }

    public function testTrackingEventSerialization(): void
    {
        $event = new TrackingEvent('evt_001', TrackingEvent::EVENT_PAGE_VIEW, '/test');
        $event->setVisitorId('v_123');
        
        $json = json_encode($event);
        $decoded = json_decode($json, true);
        
        $this->assertEquals('evt_001', $decoded['id']);
        $this->assertEquals('v_123', $decoded['visitor_id']);
    }

    public function testVisitorSerialization(): void
    {
        $visitor = new Visitor('v_123');
        $visitor->setSource('google');
        $visitor->setDevice('desktop');
        
        $json = json_encode($visitor);
        $decoded = json_decode($json, true);
        
        $this->assertEquals('v_123', $decoded['id']);
        $this->assertEquals('google', $decoded['source']);
        $this->assertEquals('desktop', $decoded['device']);
    }

    public function testVisitorNewStatus(): void
    {
        $visitor = new Visitor('v_123');
        
        $this->assertTrue($visitor->isNew());
    }

    public function testVisitorNotNewAfterVisits(): void
    {
        $visitor = new Visitor('v_123');
        $visitor->incrementVisit();
        
        $this->assertFalse($visitor->isNew());
    }
}