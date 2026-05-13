# Visitor/Conversion Tracking Core - Test Plan

## Document Information

| Field | Value |
|-------|-------|
| **Module Name** | ksf_Tracking |
| **Document Type** | Test Plan |
| **Version** | 1.0.0 |

---

## 1. Test Objectives

| Objective | Description |
|-----------|-------------|
| **Visitor Management** | Verify visitor creation, retrieval, and updates |
| **Event Tracking** | Verify all event types tracked correctly |
| **Contact Linking** | Verify visitor-contact linking |
| **Visitor Merging** | Verify merge functionality |
| **Script Generation** | Verify JavaScript output |

---

## 2. Test Scenarios

### 2.1 Visitor Entity Tests

#### TRACK-TEST-001: Create Visitor

```php
public function testCreateVisitor(): void
{
    $visitor = new Visitor('test-id');
    
    $this->assertEquals('test-id', $visitor->getId());
    $this->assertEquals(1, $visitor->getVisitCount());
    $this->assertEquals(0, $visitor->getPageViewCount());
    $this->assertFalse($visitor->isKnown());
}
```

**Pass Criteria:** Visitor created with correct initial state

---

#### TRACK-TEST-002: Increment Visit Count

```php
public function testIncrementVisit(): void
{
    $visitor = new Visitor('test-id');
    $visitor->incrementVisit();
    
    $this->assertEquals(2, $visitor->getVisitCount());
}
```

**Pass Criteria:** Visit count incremented

---

#### TRACK-TEST-003: Link to Contact

```php
public function testLinkToContact(): void
{
    $visitor = new Visitor('test-id');
    $visitor->linkToContact('CUST-001', 'test@example.com');
    
    $this->assertEquals('CUST-001', $visitor->getContactId());
    $this->assertEquals('test@example.com', $visitor->getEmail());
    $this->assertTrue($visitor->isKnown());
}
```

**Pass Criteria:** Visitor linked to contact, isKnown() returns true

---

### 2.2 TrackingEvent Entity Tests

#### TRACK-TEST-010: Create Page View Event

```php
public function testCreatePageViewEvent(): void
{
    $event = new TrackingEvent('evt-123', TrackingEvent::EVENT_PAGE_VIEW, '/page');
    
    $this->assertEquals('evt-123', $event->getId());
    $this->assertEquals(TrackingEvent::EVENT_PAGE_VIEW, $event->getEventType());
    $this->assertEquals('/page', $event->getUrl());
    $this->assertTrue($event->isAnonymous());
}
```

**Pass Criteria:** Event created with correct type

---

#### TRACK-TEST-011: Form Submit with Contact

```php
public function testFormSubmitWithContact(): void
{
    $event = new TrackingEvent('evt-123', TrackingEvent::EVENT_FORM_SUBMIT, '/contact');
    $event->setContactId('CUST-001');
    $event->setEventData(['form_id' => 'contact-form']);
    
    $this->assertTrue($event->isKnown());
    $this->assertEquals('CUST-001', $event->getContactId());
    $this->assertEquals('contact-form', $event->getEventData()['form_id']);
}
```

**Pass Criteria:** Event linked to contact

---

#### TRACK-TEST-012: JSON Serialization

```php
public function testJsonSerialize(): void
{
    $event = new TrackingEvent('evt-123', TrackingEvent::EVENT_PAGE_VIEW, '/page');
    $event->setVisitorId('v_abc123');
    $event->setIpAddress('192.168.1.1');
    
    $json = $event->jsonSerialize();
    
    $this->assertEquals('evt-123', $json['id']);
    $this->assertEquals('v_abc123', $json['visitor_id']);
    $this->assertEquals('192.168.1.1', $json['ip_address']);
}
```

**Pass Criteria:** JSON contains all expected fields

---

### 2.3 TrackingService Tests

#### TRACK-TEST-020: Start Session New Visitor

```php
public function testStartSessionNewVisitor(): void
{
    $mockRepo = $this->createMock(TrackingRepositoryInterface::class);
    $mockRepo->method('getVisitor')->willReturn(null);
    $mockRepo->expects($this->once())->method('saveVisitor');
    
    $service = new TrackingService($mockRepo);
    $visitorId = $service->startSession();
    
    $this->assertNotEmpty($visitorId);
    $this->assertStringStartsWith('v_', $visitorId);
}
```

**Pass Criteria:** New visitor ID generated

---

#### TRACK-TEST-021: Start Session Existing Visitor

```php
public function testStartSessionExistingVisitor(): void
{
    $existingVisitor = new Visitor('v_existing');
    
    $mockRepo = $this->createMock(TrackingRepositoryInterface::class);
    $mockRepo->method('getVisitor')->with('v_existing')->willReturn($existingVisitor);
    $mockRepo->expects($this->once())->method('saveVisitor'); // Increment visit
    
    $service = new TrackingService($mockRepo);
    $visitorId = $service->startSession('v_existing');
    
    $this->assertEquals('v_existing', $visitorId);
}
```

**Pass Criteria:** Existing visitor returned

---

#### TRACK-TEST-022: Track Page View

```php
public function testTrackPageView(): void
{
    $mockRepo = $this->createMock(TrackingRepositoryInterface::class);
    $mockRepo->expects($this->once())->method('saveEvent');
    $mockRepo->expects($this->once())->method('incrementPageViews');
    
    $service = new TrackingService($mockRepo);
    $service->startSession('v_test');
    
    $event = $service->trackPageView('/page', 'https://google.com');
    
    $this->assertEquals(TrackingEvent::EVENT_PAGE_VIEW, $event->getEventType());
    $this->assertEquals('/page', $event->getUrl());
}
```

**Pass Criteria:** Page view event created and saved

---

#### TRACK-TEST-023: Track Form Submit

```php
public function testTrackFormSubmit(): void
{
    $mockRepo = $this->createMock(TrackingRepositoryInterface::class);
    $mockRepo->expects($this->once())->method('saveEvent');
    $mockRepo->expects($this->once())->method('linkVisitorToContact');
    
    $service = new TrackingService($mockRepo);
    $service->startSession('v_test');
    
    $event = $service->trackFormSubmit(
        'contact-form',
        '/contact',
        'CUST-001',
        'test@example.com'
    );
    
    $this->assertEquals(TrackingEvent::EVENT_FORM_SUBMIT, $event->getEventType());
    $this->assertEquals('CUST-001', $event->getContactId());
}
```

**Pass Criteria:** Form submit event created with contact link

---

#### TRACK-TEST-024: Identify Visitor by Email

```php
public function testIdentifyByEmail(): void
{
    $existingVisitor = new Visitor('v_existing');
    $existingVisitor->linkToContact('CUST-002', 'test@example.com');
    
    $mockRepo = $this->createMock(TrackingRepositoryInterface::class);
    $mockRepo->method('findVisitorByEmail')->willReturn($existingVisitor);
    $mockRepo->expects($this->once())->method('mergeVisitors');
    
    $service = new TrackingService($mockRepo);
    $service->startSession('v_new');
    
    $returnedId = $service->identify('test@example.com');
    
    $this->assertEquals('v_existing', $returnedId);
}
```

**Pass Criteria:** Visitors merged on identification

---

### 2.4 Script Generation Tests

#### TRACK-TEST-030: Generate Tracking Script

```php
public function testGenerateTrackingScript(): void
{
    $mockRepo = $this->createMock(TrackingRepositoryInterface::class);
    $service = new TrackingService($mockRepo);
    
    $script = $service->generateTrackingScript('https://example.com/track', 'my-form');
    
    $this->assertStringContainsString('ksf_visitor_id', $script);
    $this->assertStringContainsString('localStorage', $script);
    $this->assertStringContainsString('my-form', $script);
    $this->assertStringContainsString('example.com/track', $script);
}
```

**Pass Criteria:** Script contains required elements

---

## 3. Test Data

### 3.1 Standard Test Data

```php
$validVisitor = [
    'id' => 'v_test123',
    'contact_id' => null,
    'email' => null,
    'first_visit' => '2026-05-01 10:00:00',
    'last_visit' => '2026-05-10 14:00:00',
    'visit_count' => 5,
    'page_view_count' => 20,
    'source' => 'google',
    'device' => 'desktop',
    'browser' => 'Chrome',
    'is_active' => true
];

$knownVisitor = [
    'id' => 'v_known456',
    'contact_id' => 'CUST-001',
    'email' => 'customer@example.com'
];
```

### 3.2 Event Test Data

```php
$pageViewEvent = [
    'event_type' => 'page_view',
    'url' => 'https://example.com/products',
    'referrer' => 'https://google.com',
    'ip_address' => '192.168.1.100'
];

$formSubmitEvent = [
    'event_type' => 'form_submit',
    'form_id' => 'contact-form',
    'contact_id' => 'CUST-001',
    'url' => 'https://example.com/contact'
];
```

---

## 4. Pass Criteria Summary

| Test Category | Pass Criteria |
|--------------|---------------|
| Visitor Entity | All getters/setters work correctly |
| Visitor State | isKnown(), isAnonymous(), isNew() work |
| Event Tracking | All event types created correctly |
| Event Linking | Visitor/contact linking works |
| Service Integration | Repository methods called correctly |
| Script Generation | JavaScript output is valid |

---

## 5. Coverage Targets

| Component | Target Coverage |
|-----------|-----------------|
| Visitor Entity | 100% |
| TrackingEvent Entity | 100% |
| TrackingService | 95% |
| Repository Interface | 100% |

---

*Document Version: 1.0.0*  
*Author: KSFII Development Team*