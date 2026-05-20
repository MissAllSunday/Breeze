# Content Visibility Filtering — Implementation Reference

This document describes how Breeze enforces the visibility rules at the code
level. For the business rationale behind those rules see
[`docs/WALL_VISIBILITY_RULES.md`](WALL_VISIBILITY_RULES.md).

## Table of Contents
1. [Two-layer architecture](#1-two-layer-architecture)
2. [The five gates](#2-the-five-gates)
3. [WallVisibilityService API](#3-wallvisibilityservice-api)
4. [Call sites in StatusService](#4-call-sites-in-statusservice)
5. [SQL pre-exclusion](#5-sql-pre-exclusion)
6. [Cache coherence](#6-cache-coherence)

---

## 1. Two-layer architecture

Visibility enforcement uses two complementary layers that work together:

| Layer | Where | Purpose |
|---|---|---|
| **SQL pre-exclusion** | `StatusRepository::getByBuddyActivity()` | Removes mutually blocked users from the query so `LIMIT` returns a full page of visible rows |
| **PHP filtering** | `WallVisibilityService` | Authoritative correctness gate; runs on every path regardless of how data was fetched |

The SQL layer is a **performance optimisation only**. Without it, pagination
density degrades — you request 10 rows and receive 5 visible ones because half
the page was consumed by blocked users. The PHP layer is the single source of
truth for what is and is not visible; it cannot be skipped.

---

## 2. The five gates

`WallVisibilityService` evaluates up to five ordered gates for feed surfaces.
Walls and direct-link surfaces skip gates 1 and 2.

| # | Gate | Applies to |
|---|---|---|
| 1 | Author or wall owner is in viewer's buddy list | Feed only |
| 2 | Author has `generalWall` opt-in enabled | Feed only |
| 3 | No block between viewer and author (both directions) | All surfaces |
| 4 | No block between viewer and wall owner (both directions) | All surfaces |
| 5 | Viewer's member group has the `viewGeneralWall` / `canViewActivity` permission | All surfaces |

Gates 3 and 4 implement **symmetric blocking**: both `viewer → author` and
`author → viewer` directions are checked independently. A block in either
direction is sufficient to hide the post.

---

## 3. WallVisibilityService API

All public methods are declared on `WallVisibilityServiceInterface`.

### `isVisibleToViewer(int $authorId, int $wallOwnerId, int $viewerId): bool`

Single-item check applying **all five gates**. Use this when you need to test
whether one specific piece of content is visible to a given viewer without
needing to filter a collection. It loads settings internally.

```php
if (!$this->wallVisibilityService->isVisibleToViewer($authorId, $wallId, $viewerId)) {
    // deny access
}
```

### `filterStatusesForFeed(array $statuses, int $viewerId): array`

Filters a collection of `StatusEntity` objects applying **all five gates**
(gates 1–5). Used for the buddy-activity feed. Returns only statuses that pass
every gate. Loads settings in a single batch query.

### `filterStatusesForWall(array $statuses, int $viewerId): array`

Filters a collection applying **gates 3–5 only** (safety + permission gates).
Used for profile walls and single-status views, where the viewer navigated
intentionally and the buddy/generalWall inclusion gates do not apply.

### `filterVisibleComments(array $comments, int $viewerId): array`

Filters a collection of `CommentEntity` objects applying **symmetric block
only** (gates 3 and 5). The wall owner is not a party to a comment, so gate 4
does not apply here. Called via `filterCommentsOnStatuses()` in `StatusService`
after the parent statuses have already been filtered.

### `getMutualBlockIds(int $viewerId, array $participantIds): array`

Returns the union of:
- IDs that the viewer has blocked
- IDs among `$participantIds` that have the viewer in their own block list

This is the set to pass to `StatusRepository::getByBuddyActivity()` as
`$excludeIds`. It is **not** a filtering method; it feeds the SQL layer.

---

## 4. Call sites in StatusService

| `StatusService` method | SQL pre-exclusion | PHP filter |
|---|---|---|
| `getByBuddies()` | `getMutualBlockIds()` → `getByBuddyActivity($excludeIds)` | `filterStatusesForFeed()` + `filterVisibleComments()` |
| `getByProfile()` | none | `filterStatusesForWall()` + `filterVisibleComments()` |
| `getById()` | none | `filterStatusesForWall()` + `filterVisibleComments()` |

`filterCommentsOnStatuses()` is a private helper in `StatusService` that
iterates the already-filtered status collection and calls
`filterVisibleComments()` on each status's comment list.

---

## 5. SQL pre-exclusion

`StatusRepository::getByBuddyActivity()` accepts `$excludeIds` and builds a
`NOT IN` clause to remove those rows before `LIMIT` is applied:

```sql
WHERE user_id IN ({array_int:buddyIds})
  AND user_id NOT IN ({array_int:excludeIds})
  AND wall_id NOT IN ({array_int:excludeIds})
ORDER BY created_at DESC, id DESC
LIMIT {int:limit}
```

Both `user_id` and `wall_id` are excluded because a block affects content
regardless of whether the blocked person is the author or the wall owner.

The `NOT IN` form is standard SQL-92 and is compatible with MySQL 5.0.3+ and
PostgreSQL 8.0+, both of which are Breeze's minimum supported versions.

---

## 6. Cache coherence

Every settings save flushes two cache namespaces via
`SettingsRepository::invalidateBlockListCaches(int $userId)`:

1. **User settings cache** (`getById_<userId>`, `getByIds_*`) — ensures the
   next call to `loadSettings()` reads fresh block-list data.
2. **Buddy-activity initial-page cache**
   (`getByBuddyActivity_viewer_<userId>`) — ensures the viewer's first feed
   page is rebuilt with the updated mutual block set.

Without (2), a viewer could save new block settings and still see a stale
cached first page that was built before the block took effect.
