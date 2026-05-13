# Visitor/Conversion Tracking Core - Architecture

## Document Information

| Field | Value |
|-------|-------|
| **Module Name** | ksf_Tracking |
| **Module Type** | Business Logic / Framework-Agnostic Core |
| **Version** | 1.0.0 |

---

## 1. Technical Architecture

### 1.1 Architecture Pattern

```
┌──────────────────────────────────────────────────────────────────────┐
│                      ksf_Tracking                                   │
│                    (Business Logic Layer)                           │
├──────────────────────────────────────────────────────────────────────┤
│                                                                      │
│  ┌──────────────────┐  ┌──────────────────┐  ┌──────────────────┐   │
│  │   TrackingService │  │    Visitor       │  │  TrackingEvent  │   │
│  │                  │  │    Entity        │  │  Entity         │   │
│  │ - startSession() │  │                  │  │                 │   │
│  │ - trackPageView()│  │ - id             │  │ - id            │   │
│  │ - trackFormView()│  │ - contactId      │  │ - eventType     │   │
│  │ - trackFormSubmit│  │ - visitCount     │  │ - visitorId     │   │
│  │ - identify()     │  │ - firstVisit     │  │ - contactId     │   │
│  └────────┬─────────┘  │ - lastVisit      │  │ - url           │   │
│           │            │ - source         │  │ - referrer     │   │
│           │            │ - device         │  │ - ipAddress    │   │
│           │            │ - browser        │  │ - eventData    │   │
│           │            └──────────────────┘  └──────────────────┘   │
│           │                                                      │
│           └──────────────────┐                                   │
│                              │ uses                                │
│                              ▼                                    │
│  ┌──────────────────────────────────────────────────────────────┐   │
│  │              TrackingRepositoryInterface                     │   │
│  │  Responsibilities:                                          │   │
│  │  - Data persistence abstraction                              │   │
│  │  - Platform-specific implementations                         │   │
│  │  - Visitor and event CRUD operations                         │   │
│  └──────────────────────────────────────────────────────────────┘   │
└──────────────────────────────────────────────────────────────────────┘
```

### 1.2 Directory Structure

```
ksf_Tracking/
├── src/Ksfraser/Tracking/
│   ├── Entity/
│   │   ├── Visitor.php              # Visitor entity
│   │   └── TrackingEvent.php        # Tracking event entity
│   ├── Service/
│   │   └── TrackingService.php      # Core tracking service
│   └── Repository/
│       └── TrackingRepositoryInterface.php
├── tests/
│   ├── Unit/
│   │   ├── VisitorTest.php
│   │   ├── TrackingEventTest.php
│   │   └── TrackingServiceTest.php
│   └── fixtures/
├── ProjectDcs/
└── composer.json
```

### 1.3 Namespace Structure

```php
namespace Ksfraser\Tracking\Entity;     # Entity classes
namespace Ksfraser\Tracking\Service;    # Service classes
namespace Ksfraser\Tracking\Repository; # Repository interface
```

---

## 2. Class Diagrams

### 2.1 Visitor Entity

```
┌─────────────────────────────────────────────────────────────────┐
│                         Visitor                                 │
├─────────────────────────────────────────────────────────────────┤
│ - id: string                                                    │
│ - contactId: ?string                                            │
│ - email: ?string                                                │
│ - firstVisit: string                                            │
│ - lastVisit: ?string                                            │
│ - visitCount: int                                               │
│ - pageViewCount: int                                            │
│ - lastKnownUrl: array                                            │
│ - source: string                                                │
│ - device: string                                                │
│ - browser: string                                                │
│ - isActive: bool                                                 │
├─────────────────────────────────────────────────────────────────┤
│ + __construct(string id)                                         │
│ + getId(): string                                                │
│ + getContactId(): ?string                                        │
│ + linkToContact(string contactId, ?string email): self            │
│ + getEmail(): ?string                                            │
│ + getFirstVisit(): string                                        │
│ + getLastVisit(): ?string                                        │
│ + updateLastVisit(): self                                        │
│ + getVisitCount(): int                                           │
│ + incrementVisit(): self                                         │
│ + getPageViewCount(): int                                        │
│ + incrementPageViews(int count): self                            │
│ + getLastKnownUrl(): array                                        │
│ + setLastKnownUrl(array url): self                                │
│ + getSource(): string                                            │
│ + setSource(string source): self                                 │
│ + getDevice(): string                                            │
│ + setDevice(string device): self                                 │
│ + getBrowser(): string                                            │
│ + setBrowser(string browser): self                               │
│ + isActive(): bool                                               │
│ + setActive(bool active): self                                    │
│ + isKnown(): bool                                                │
│ + isNew(): bool                                                  │
│ + jsonSerialize(): array                                         │
│ + fromArray(array data): self                                    │
└─────────────────────────────────────────────────────────────────┘
```

### 2.2 TrackingEvent Entity

```
┌─────────────────────────────────────────────────────────────────┐
│                      TrackingEvent                              │
├─────────────────────────────────────────────────────────────────┤
│ - id: string                                                    │
│ - visitorId: ?string                                            │
│ - contactId: ?string                                            │
│ - eventType: string                                             │
│ - url: string                                                   │
│ - referrer: ?string                                             │
│ - ipAddress: ?string                                            │
│ - userAgent: ?string                                             │
│ - eventData: array                                              │
│ - createdAt: DateTime                                            │
├─────────────────────────────────────────────────────────────────┤
│ + EVENT_PAGE_VIEW = 'page_view'                                │
│ + EVENT_FORM_VIEW = 'form_view'                                 │
│ + EVENT_FORM_SUBMIT = 'form_submit'                             │
│ + EVENT_LINK_CLICK = 'link_click'                               │
│ + EVENT_EMAIL_OPEN = 'email_open'                               │
│ + EVENT_EMAIL_CLICK = 'email_click'                             │
├─────────────────────────────────────────────────────────────────┤
│ + __construct(string id, string eventType, string url)           │
│ + getId(): string                                                │
│ + getVisitorId(): ?string                                        │
│ + setVisitorId(?string visitorId): self                          │
│ + getContactId(): ?string                                        │
│ + setContactId(?string contactId): self                         │
│ + getEventType(): string                                         │
│ + getUrl(): string                                               │
│ + getReferrer(): ?string                                         │
│ + setReferrer(?string referrer): self                           │
│ + getIpAddress(): ?string                                        │
│ + setIpAddress(?string ipAddress): self                         │
│ + getUserAgent(): ?string                                        │
│ + setUserAgent(?string userAgent): self                         │
│ + getEventData(): array                                          │
│ + setEventData(array data): self                                 │
│ + getCreatedAt(): DateTime                                        │
│ + isAnonymous(): bool                                            │
│ + isKnown(): bool                                               │
│ + jsonSerialize(): array                                         │
│ + fromArray(array data): self                                    │
└─────────────────────────────────────────────────────────────────┘
```

### 2.3 TrackingService

```
┌─────────────────────────────────────────────────────────────────┐
│                       TrackingService                            │
├─────────────────────────────────────────────────────────────────┤
│ - repository: TrackingRepositoryInterface                        │
│ - currentVisitorId: ?string                                      │
├─────────────────────────────────────────────────────────────────┤
│ + __construct(TrackingRepositoryInterface repository)             │
│ + getVisitor(string visitorId): ?Visitor                        │
│ + startSession(?string visitorId): string                       │
│ + trackPageView(string url, ?string referrer, ?string userAgent, │
│                 ?string ipAddress): TrackingEvent                 │
│ + trackFormView(string formId, string url, ?string userAgent,    │
│                 ?string ipAddress): TrackingEvent                │
│ + trackFormSubmit(string formId, string url, string contactId,  │
│                 ?string email, array formData, ?string userAgent,│
│                 ?string ipAddress): TrackingEvent                │
│ + trackLinkClick(string url, string linkId, ?string userAgent, │
│                  ?string ipAddress): TrackingEvent                │
│ + identify(string email, ?string contactId): ?string            │
│ + getStatistics(?DateTime since): array                         │
│ + getRecentEvents(int limit): array                             │
│ + generateTrackingScript(string trackingUrl, string formId):    │
│                  string                                           │
│ - getCurrentVisitorId(): ?string                                │
│ - generateVisitorId(): string                                   │
└─────────────────────────────────────────────────────────────────┘
```

---

## 3. Data Flow

### 3.1 Session Start Flow

```
┌──────────────┐    ┌──────────────────┐    ┌──────────────────┐
│   Browser    │    │  TrackingService │    │   Repository     │
│             │    │                  │    │                  │
└──────┬───────┘    └────────┬─────────┘    └───────┬──────────┘
       │                     │                        │
       │  startSession()     │                        │
       │───────────────────▶│                        │
       │                     │                        │
       │                     │  getVisitor(visitorId) │
       │                     │──────────────────────▶│
       │                     │                        │
       │                     │  [Visitor|null]       │
       │                     │◀──────────────────────│
       │                     │                        │
       │                     │  [if null] new Visitor │
       │                     │──────────────────────▶│
       │                     │                        │
       │                     │  saveVisitor()         │
       │                     │◀──────────────────────│
       │                     │                        │
       │  [visitorId]        │                        │
       │◀───────────────────│                        │
       │                     │                        │
```

### 3.2 Form Submit with Contact Linking

```
┌──────────────┐    ┌──────────────────┐    ┌──────────────────┐
│   Browser    │    │  TrackingService │    │   Repository     │
└──────┬───────┘    └────────┬─────────┘    └───────┬──────────┘
       │                     │                        │
       │  trackFormSubmit()  │                        │
       │───────────────────▶│                        │
       │                     │                        │
       │                     │  [Create TrackingEvent]│
       │                     │  setContactId(contactId)│
       │                     │                        │
       │                     │  saveEvent()           │
       │                     │──────────────────────▶│
       │                     │                        │
       │                     │  linkVisitorToContact()│
       │                     │──────────────────────▶│
       │                     │                        │
       │  [TrackingEvent]     │                        │
       │◀───────────────────│                        │
       │                     │                        │
```

---

## 4. JavaScript Tracking Script

### 4.1 Generated Script Structure

```javascript
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
        form_id: 'form-id'
    };
    
    var img = new Image();
    img.src = 'tracking-url?data=' + encodeURIComponent(JSON.stringify(data));
})();
```

### 4.2 Data Collection Points

| Data Point | Collection Method | Purpose |
|------------|-------------------|---------|
| visitor_id | localStorage/get parameter | Identify returning visitors |
| url | window.location.href | Track page views |
| referrer | document.referrer | Source attribution |
| user_agent | navigator.userAgent | Browser/device detection |
| screen_width | screen.width | Device type determination |
| screen_height | screen.height | Device type determination |

---

## 5. Repository Interface

### 5.1 TrackingRepositoryInterface

```php
interface TrackingRepositoryInterface {
    public function getVisitor(string $visitorId): ?Visitor;
    public function saveVisitor(Visitor $visitor): void;
    public function saveEvent(TrackingEvent $event): void;
    public function incrementPageViews(string $visitorId): void;
    public function linkVisitorToContact(string $visitorId, string $contactId, ?string $email): void;
    public function findVisitorByEmail(string $email): ?Visitor;
    public function findContactByEmail(string $email): ?string;
    public function mergeVisitors(string $sourceId, string $targetId): void;
    public function getStatistics(?DateTime $since): array;
    public function getRecentEvents(int $limit): array;
}
```

---

*Document Version: 1.0.0*  
*Author: KSFII Development Team*