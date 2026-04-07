# Breeze Application - Architecture Documentation

## Table of Contents
1. [Application Overview](#1-application-overview)
2. [Architecture Overview](#2-architecture-overview)
3. [Design Patterns Used](#3-design-patterns-used)
4. [Database Schema](#4-database-schema)
5. [Key Features & Workflows](#5-key-features--workflows)
6. [Strengths of Current Implementation](#6-strengths-of-current-implementation)
7. [Pagination Implementation](#7-pagination-implementation)
8. [Alert System Architecture](#8-alert-system-architecture)

---

## 1. Application Overview

**Breeze** is a social networking modification (mod) for **Simple Machines Forum (SMF)** that adds Facebook-like wall functionality to user profiles. It enables users to:
- Post status updates on their own or other users' walls
- Comment on status updates
- Like statuses and comments
- Receive notifications for interactions
- View a general activity feed from buddies
- Customize individual user settings

### Technology Stack
- **Backend:** PHP 8.3+ with SMF 2.1.x integration
- **Frontend:** React 19.1.0 (TypeScript)
- **Build Tools:** Vite, Vitest, Biome
- **Database:** MySQL (via SMF's database layer)
- **Dependencies:** League Container (DI), League Event (Event System)

---

## 2. Architecture Overview

### 2.1 Backend Architecture (PHP)

The backend follows a **layered architecture** with clear separation of concerns:

#### Layer Structure:
1. **Entry Point Layer** - `Breeze.php` main class
2. **Controller Layer** - Handles HTTP requests
3. **Service Layer** - Business logic
4. **Repository Layer** - Data access
5. **Entity Layer** - Data models
6. **Validation Layer** - Input validation
7. **Event Layer** - Event-driven notifications

#### Integration with SMF:
- Uses SMF's hook system for integration
- Registers actions, menu items, permissions, and alerts
- No file modifications required (theme-agnostic)

### 2.2 Frontend Architecture (React)

The frontend is a **Single Page Application (SPA)** built with React:

#### Component Structure:
- **Wall Component** - Main container managing status list
- **Status Component** - Individual status display with comments
- **Comment Component** - Comment display and management
- **Editor Component** - Content creation (status/comments)
- **Tabs Component** - Tab navigation for profile sections
- **Like Component** - Like functionality display

#### State Management:
- Uses React hooks (`useState`, `useCallback`, `useEffect`)
- Context API for permissions
- Local component state for UI state

---

## 3. Design Patterns Used

### 3.1 Backend Patterns

#### 1. Dependency Injection (DI)
- **Implementation:** League Container
- **Location:** `Sources/Breeze/Config/DependenciesServiceProvider.php`
- **Purpose:** Manages object creation and dependencies
- **Example:**
```php
protected const array DEPENDENCIES = [
    // Shared service (Singleton)
    DatabaseClient::class => ['arguments' => [], 'shared' => true],

    // Controller with dependencies
    StatusController::class => ['arguments' => [
        StatusService::class,
        ValidateStatus::class,
        Response::class,
    ]],
];
```

#### 2. Repository Pattern
- **Purpose:** Abstracts data access logic
- **Implementation:** `BaseRepository` with specific implementations
- **Benefits:** Testability, separation of concerns
- **Key Classes:**
  - `StatusRepository`
  - `CommentRepository`
  - `LikeRepository`
  - `AlertRepository`

#### 3. Service Layer Pattern
- **Purpose:** Encapsulates business logic
- **Implementation:** Service classes between controllers and repositories
- **Key Classes:**
  - `StatusService`
  - `CommentService`
  - `AlertService`
  - `PermissionsService`

#### 4. Entity Pattern (Active Record-like)
- **Purpose:** Represents database tables as objects
- **Implementation:** Entity classes with type casting and serialization
- **Features:**
  - Type casting via `castValue()`
  - JSON serialization
  - Factory method `from()`

#### 5. Event-Driven Architecture
- **Implementation:** League Event library
- **Purpose:** Decoupled notification system
- **Events:**
  - `StatusCreatedEvent`
  - `StatusDeletedEvent`
  - `CommentCreatedEvent`
  - `CommentDeletedEvent`
  - `LikeCreatedEvent`

#### 6. Strategy Pattern
- **Implementation:** Validators with different strategies
- **Location:** `Validate/` directory
- **Purpose:** Different validation strategies for different actions

#### 7. Trait Composition
- **Traits Used:**
  - `RequestTrait` - HTTP request handling
  - `TextTrait` - Text/translation handling
  - `PermissionsTrait` - Permission checking
  - `CacheTrait` - Caching operations
  - `PersistenceTrait` - Global state access

#### 8. Factory Pattern
- **Implementation:** Entity creation via `from()` static methods
- **Purpose:** Consistent object creation

#### 9. Template Method Pattern


### 3.2 Frontend Patterns

#### 1. Component Composition
- React components composed hierarchically
- Props drilling for data flow

#### 2. Container/Presentational Pattern
- Wall component acts as container
- Status/Comment components are presentational

#### 3. Custom Hooks Pattern
- Uses React hooks for state and side effects

#### 4. Context Pattern
- `PermissionsContext` for global permissions state

---

## 4. Database Schema

### Tables:

#### 1. `breeze_status`
- `id` (PK, auto-increment)
- `wall_id` (user profile ID where status is posted)
- `user_id` (poster ID)
- `body` (status content - text)
- `likes` (like count - integer)
- `created_at` (timestamp - varchar)

#### 2. `breeze_comments`
- `id` (PK, auto-increment)
- `status_id` (FK to breeze_status)
- `user_id` (commenter ID)
- `body` (comment content - text)
- `likes` (like count - integer)
- `created_at` (timestamp - varchar)

#### 3. `breeze_options`
- `member_id` (PK)
- `variable` (PK - setting name)
- `value` (user setting value - text)

#### 4. `user_likes` (SMF table)
- Used for tracking likes on statuses/comments
- `id_member` (user who liked)
- `content_type` (status or comment)
- `content_id` (ID of liked content)
- `like_time` (timestamp)

---

## 5. Key Features & Workflows

### 5.1 Status Posting Workflow
1. User types in Editor component
2. React calls `postStatus()` API
3. `StatusController` receives request
4. `ValidateStatus` validates input
5. `StatusService` handles business logic
6. `StatusRepository` inserts to database
7. Event dispatched (`StatusCreatedEvent`)
8. `AlertService` creates notifications
9. Response returned to React
10. UI updates with new status

### 5.2 Commenting Workflow
1. Similar flow to status posting
2. Comments attached to specific status via `statusId`
3. Nested display in Status component
4. Supports likes on comments

### 5.3 Likes System
1. Toggle-based (like/unlike)
2. Tracks who liked what content
3. Displays like count and user list
4. Uses enum for type safety (`LikesEnum::Status`, `LikesEnum::Comments`)
5. Integrated with SMF's user_likes table

### 5.4 Notifications System
1. Event-driven via `EventServiceProvider`
2. `AlertService` creates notifications
3. Integrated with SMF's alert system
4. Notifies:
   - Wall owner when someone posts
   - Status owner when someone comments
   - Comment/Status owner when someone likes

### 5.5 Permissions System
1. Granular permissions per action
2. Checked at multiple layers (Controller, Service, Repository)
3. Permissions context passed to React frontend
4. User-specific settings override global settings

---

## 6. Strengths of Current Implementation

### Architecture
✅ **Clean Architecture** - Well-separated layers with clear responsibilities
✅ **SOLID Principles** - Good adherence to SOLID design principles
✅ **Dependency Injection** - Proper DI container usage with League Container
✅ **Event-Driven** - Decoupled notification system using events

### Code Quality
✅ **Modern PHP** - PHP 8.3 with strict types and modern features
✅ **Type Safety** - TypeScript on frontend, typed PHP on backend
✅ **Testing** - Has test infrastructure (PHPUnit + Vitest)
✅ **Code Standards** - Uses Rector, PHPStan, Biome for code quality

### Frontend
✅ **Modern Frontend** - React 19 with hooks and functional components
✅ **Build Tools** - Vite for fast builds and HMR
✅ **TypeScript** - Type-safe frontend code

### Integration
✅ **No File Edits** - Clean SMF integration via hooks
✅ **Theme Agnostic** - Works with all SMF themes
✅ **Modular** - Easy to extend and maintain

### Design Patterns
✅ **Repository Pattern** - Clean data access layer
✅ **Service Layer** - Business logic separation
✅ **Entity Pattern** - Type-safe data models
✅ **Strategy Pattern** - Flexible validation
✅ **Factory Pattern** - Consistent object creation

---

## 7. Pagination Implementation

### Overview

The Breeze application uses **cursor-based pagination** for improved performance and scalability.
This was implemented to address the limitations of traditional offset-based pagination, especially for large datasets.

### Current Implementation

The application uses cursor-based pagination with composite cursors (id + created_at) for stable, performant pagination:

```php
// Sources/Breeze/Repository/StatusRepository.php
$request = $this->dbClient->query(
    '
    SELECT {raw:columns}
    FROM {db_prefix}{raw:from}
    WHERE {raw:columnName} IN ({array_int:ids})
    AND (
        parent.created_at < {int:cursor_created_at}
        OR (parent.created_at = {int:cursor_created_at} AND parent.id < {int:cursor_id})
    )
    ORDER BY parent.created_at DESC, parent.id DESC
    LIMIT {int:limit}',
    $queryParams
);
```

**Benefits of cursor-based pagination:**
- Consistent performance regardless of dataset position
- No skipped or duplicate items during concurrent updates
- Scales efficiently with large datasets
- Works well with proper database indexes

### Key Features

- **Composite Cursors**: Uses both `id` and `created_at` for stable ordering
- **Efficient Queries**: Scans only required rows with proper indexes
- **Cache-Friendly**: Cursor-based cache keys for better hit rates
- **Backward Compatible**: Gracefully handles null cursors for initial page

### Usage

**Quick Example:**

```php
// Get first page
$statuses = $statusRepository->getByProfile([1], 10, null);

// Generate cursor for next page
$nextCursor = $statusRepository->getNextCursor($statuses);

// Get next page
if ($nextCursor !== null) {
    $moreStatuses = $statusRepository->getByProfile([1], 10, $nextCursor);
}
```

## 8. Alert System Architecture

### 8.1 Current Alert System

Breeze alert system is built on SMF's native alert infrastructure and uses an event-driven architecture for decoupled notification handling.

#### Implemented Alerts

1. **Status Created** (`Breeze_status_owner`)
   - Recipient: Wall owner
   - Trigger: Someone posts a status on their wall
   - Handler: `StatusCreatedHandler`

2. **Comment Created** (`Breeze_comment_status_owner`, `Breeze_comment_profile_owner`)
   - Recipients: Status owner AND/OR wall owner (with duplicate prevention)
   - Trigger: Someone comments on a status
   - Handler: `CommentCreatedHandler`

3. **Like Created** (`Breeze_like_status`, `Breeze_like_comment`)
   - Recipient: Content owner (status or comment)
   - Trigger: Someone likes content
   - Handler: `LikeCreatedHandler`

#### Alert Flow

```
User Action → Event Dispatched → Event Listener → AlertService::send()
→ Handler Registered → SMF Alert System → User Notification
```

### 8.2 Alert System Components

#### AlertService
- **Location:** `Sources/Breeze/Service/AlertService.php`
- **Purpose:** Manages alert creation and handling
- **Key Methods:**
  - `send(AlertEntity)` - Creates and sends alerts
  - `handle(array &$alerts)` - Processes alerts for display

#### Event Listeners
- **StatusEventListener** - Handles status-related events
- **CommentEventListener** - Handles comment-related events
- **LikeEventListener** - Handles like-related events

#### Alert Handlers
- **Location:** `Sources/Breeze/Event/*/`
- **Purpose:** Format alert text and links for display
- **Registration:** `HandlerServiceProvider`

### 8.3 Alert Entity Structure

```php
AlertEntity::from([
    AlertEntity::ID_MEMBER => $recipientId,
    AlertEntity::ID_MEMBER_STARTED => $actorId,
    AlertEntity::CONTENT_TYPE => 'Breeze_status_owner',
    AlertEntity::CONTENT_ID => $statusId,
    AlertEntity::CONTENT_ACTION => 'created',
]);
```

### 8.4 Integration with SMF

- Uses SMF's `user_alerts` table
- Integrates with SMF's alert preferences system
- Supports email notifications via SMF
- Respects user notification settings

---

**Document Version:** 2.0
**Last Updated:** 2026-02-25
**Status:** Current Architecture Documentation
