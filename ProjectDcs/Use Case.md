# Visitor/Conversion Tracking Core - Use Case Specification

## Document Information

| Field | Value |
|-------|-------|
| **Module Name** | ksf_Tracking |
| **Document Type** | Use Case Specification |
| **Version** | 1.0.0 |

---

## 1. Use Case Overview

| Use Case ID | UC-TRACK-001 |
|-------------|--------------|
| **Use Case Name** | Track Visitor Session |
| **Primary Actor** | Website Visitor |
| **Secondary Actors** | Tracking Repository |
| **Brief Description** | System identifies and tracks a visitor across their website session |
| **Pre-condition** | Visitor accesses website |
| **Post-condition** | Visitor identified and session started |

---

## 2. Use Cases

### 2.1 UC-TRACK-001: Start Tracking Session

**Basic Flow:**

| Step | Actor | Action |
|------|-------|--------|
| 1 | Visitor | Visits website |
| 2 | JS Tracker | Checks localStorage for visitor ID |
| 3 | If no ID | Generates new visitor ID |
| 4 | JS Tracker | Stores ID in localStorage |
| 5 | JS Tracker | Sends session start event |
| 6 | Tracking Service | Receives session start request |
| 7 | Tracking Service | Checks repository for visitor |
| 8 | If new visitor | Creates visitor record |
| 9 | If existing visitor | Updates visit count |
| 10 | Tracking Service | Returns visitor ID |

**Alternative Flows:**

| Step | Branch Condition | Action |
|------|------------------|--------|
| 3a | ID exists in localStorage | Use existing ID |
| 5a | Cookie consent not given | Do not track (GDPR) |

**Pre-conditions:**
- Visitor has JavaScript enabled
- GDPR consent given (if applicable)

**Post-conditions:**
- Visitor record exists in database
- localStorage contains visitor ID

---

### 2.2 UC-TRACK-002: Track Page View

**Basic Flow:**

| Step | Actor | Action |
|------|-------|--------|
| 1 | Visitor | Views page |
| 2 | JS Tracker | Captures page URL |
| 3 | JS Tracker | Captures referrer |
| 4 | JS Tracker | Captures user agent |
| 5 | JS Tracker | Sends page view event |
| 6 | Tracking Service | Creates TrackingEvent |
| 7 | Tracking Service | Associates with visitor |
| 8 | Tracking Service | Saves event |
| 9 | Tracking Service | Increments page views |

**Pre-conditions:**
- Session already started
- Visitor ID known

**Post-conditions:**
- Page view event recorded
- Visitor page view count incremented

---

### 2.3 UC-TRACK-003: Track Form View

**Basic Flow:**

| Step | Actor | Action |
|------|-------|--------|
| 1 | Visitor | Views form |
| 2 | JS Tracker | Captures form ID |
| 3 | JS Tracker | Captures page URL |
| 4 | JS Tracker | Sends form view event |
| 5 | Tracking Service | Creates TrackingEvent with form_id |
| 6 | Tracking Service | Saves event |

**Pre-conditions:**
- Session already started
- Form rendered on page

**Post-conditions:**
- Form view event recorded

---

### 2.4 UC-TRACK-004: Track Form Submission

**Basic Flow:**

| Step | Actor | Action |
|------|-------|--------|
| 1 | Visitor | Submits form |
| 2 | JS Tracker | Captures form data |
| 3 | JS Tracker | Captures contact email |
| 4 | JS Tracker | Sends form submit event |
| 5 | Tracking Service | Creates TrackingEvent |
| 6 | Tracking Service | Links contact ID to event |
| 7 | Tracking Service | Links visitor to contact |
| 8 | Tracking Service | Saves event |

**Alternative Flows:**

| Step | Branch Condition | Action |
|------|------------------|--------|
| 3a | No email in form | Track as anonymous |
| 7a | Contact not found | Create new contact (if integrated) |

**Pre-conditions:**
- Session already started
- Contact email captured

**Post-conditions:**
- Form submit event recorded
- Visitor linked to contact
- Contact linked to visitor

---

### 2.5 UC-TRACK-005: Identify Known Visitor

**Basic Flow:**

| Step | Actor | Action |
|------|-------|--------|
| 1 | System | Receives identify request with email |
| 2 | Tracking Service | Checks for existing visitor by email |
| 3 | If found | Merges current visitor to found visitor |
| 4 | Tracking Service | Finds or creates contact |
| 5 | Tracking Service | Links visitor to contact |
| 6 | Tracking Service | Returns visitor ID |

**Pre-conditions:**
- Session already started
- Email available

**Post-conditions:**
- Visitor linked to contact
- Visitor history preserved

---

### 2.6 UC-TRACK-006: Generate Tracking Script

**Basic Flow:**

| Step | Actor | Action |
|------|-------|--------|
| 1 | Administrator | Requests tracking script |
| 2 | Tracking Service | Generates JavaScript |
| 3 | Tracking Service | Includes tracking URL |
| 4 | Tracking Service | Includes form ID |
| 5 | Tracking Service | Returns embeddable script |

**Pre-conditions:**
- Tracking URL configured

**Post-conditions:**
- JavaScript provided for embedding

---

## 3. Sequence Diagrams

### 3.1 Track Page View Sequence

```
┌──────────────┐    ┌──────────────┐    ┌──────────────────┐    ┌──────────────┐
│   Browser   │    │  JS Tracker  │    │ TrackingService  │    │  Repository  │
└──────┬───────┘    └──────┬───────┘    └────────┬─────────┘    └──────┬───────┘
       │                  │                      │                     │
       │  Page Load       │                      │                     │
       │─────────────────▶│                      │                     │
       │                  │                      │                     │
       │                  │  trackPageView()      │                     │
       │                  │─────────────────────▶│                     │
       │                  │                      │                     │
       │                  │                      │  new TrackingEvent()│
       │                  │                      │────────────────────▶│
       │                  │                      │                     │
       │                  │                      │  [saveEvent()]      │
       │                  │                      │────────────────────▶│
       │                  │                      │                     │
       │                  │                      │  [increment views] │
       │                  │                      │────────────────────▶│
       │                  │                      │                     │
       │  [Event Created] │                      │                     │
       │◀─────────────────│                      │                     │
       │                  │                      │                     │
```

---

## 4. Activity Diagrams

### 4.1 Visitor Session Flow

```
┌─────────────┐
│   Start     │
└──────┬──────┘
       │
       ▼
┌─────────────────────┐
│  Check localStorage  │
│  for visitor_id      │
└──────────┬──────────┘
           │
      ┌────┴────┐
      │ ID      │ No
      │ exists? │
      ▼         ▼
┌───────────┐ ┌─────────────────┐
│ Use       │ │ Generate new     │
│ existing  │ │ visitor ID       │
│ ID        │ │ 'v_' + random   │
└─────┬─────┘ └────────┬─────────┘
      │                │
      └───────┬────────┘
              │
              ▼
      ┌─────────────────┐
      │ Store in        │
      │ localStorage     │
      └────────┬────────┘
               │
               ▼
      ┌─────────────────┐
      │ startSession()  │
      │ with visitor ID │
      └────────┬────────┘
               │
               ▼
         ┌─────────────┐
         │  Session    │
         │  Started    │
         └─────────────┘
```

---

## 5. Use Case Summary

| UC ID | Use Case Name | Primary Actor | Priority |
|-------|--------------|----------------|----------|
| UC-TRACK-001 | Start Tracking Session | Visitor | Critical |
| UC-TRACK-002 | Track Page View | Visitor | Critical |
| UC-TRACK-003 | Track Form View | Visitor | High |
| UC-TRACK-004 | Track Form Submission | Visitor | Critical |
| UC-TRACK-005 | Identify Known Visitor | System | High |
| UC-TRACK-006 | Generate Tracking Script | Administrator | Medium |

---

*Document Version: 1.0.0*  
*Author: KSFII Development Team*