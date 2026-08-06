# Platform Support Messaging — Design

**Date:** 2026-08-06  
**Status:** Approved for implementation planning  
**Scope:** Tenant owner/admin ↔ platform owner in-app support messaging

## Problem

Courier tenants need an easy way to tell the platform owner about bugs, billing questions, and product updates. Today the platform console only lists tenants and can suspend/activate them. There is no channel from tenant staff to the platform owner inside CourierOS.

## Goals

- Let tenant **owners and admins** open a support conversation with the platform owner without leaving the tenant admin.
- Let the **platform owner** read and reply from `/platform` (Catalyst-styled owner console).
- Keep history threaded per issue so multiple problems don’t blur together.
- Notify both sides in-app and by email when new activity lands.
- Preserve tenancy isolation: platform owner never needs to log into the tenant admin for this feature.

## Non-goals (v1)

- File attachments
- Live chat / real-time websockets
- Full ticketing (categories, priorities, SLAs, assignment)
- Impersonation / “enter tenant admin” support mode
- Staff- or customer-facing access to this channel
- SMS / WhatsApp

## Decisions

| Topic | Choice |
|---|---|
| Model | Conversation **threads** (subject + messages), not one chat blob per tenant |
| Who can write (tenant) | `owner` and `admin` only |
| Who can write (platform) | `platform_owner` only |
| Status | `open` \| `closed` |
| Notifications | In-app unread + email both directions |
| Attachments | None in v1 |
| Platform can initiate | Yes (“Message this tenant”) |

## Architecture

### Data (central database)

Threads and messages live in the **central** connection (same as `tenants`), not in a tenant-scoped table that relies on the `BelongsToTenant` global scope for safety. Every query for tenant users **must** filter `tenant_id` explicitly. Platform owner queries may span tenants.

#### `platform_support_threads`

| Column | Type | Notes |
|---|---|---|
| `id` | bigint PK | |
| `tenant_id` | FK → tenants | Required |
| `subject` | string(160) | Required |
| `status` | string | `open` \| `closed`, default `open` |
| `created_by_user_id` | FK → users | Tenant user or platform owner who opened it |
| `last_message_at` | timestamp | Updated on each message |
| `closed_at` | timestamp nullable | Set when closed |
| `closed_by_user_id` | FK → users nullable | |
| `created_at` / `updated_at` | timestamps | |

Indexes: `(tenant_id, status, last_message_at)`, `(status, last_message_at)` for platform inbox.

#### `platform_support_messages`

| Column | Type | Notes |
|---|---|---|
| `id` | bigint PK | |
| `thread_id` | FK → platform_support_threads | Cascade delete |
| `user_id` | FK → users | Author |
| `author_side` | string | `tenant` \| `platform` (denormalised for fast filtering / mail copy) |
| `body` | text | Plain text, max ~10k chars validated |
| `created_at` / `updated_at` | timestamps | |

No `read_at` on messages. Unread is derived per-user via a receipts table so multiple tenant admins can track independently.

#### `platform_support_thread_reads`

| Column | Type | Notes |
|---|---|---|
| `id` | bigint PK | |
| `thread_id` | FK | |
| `user_id` | FK | |
| `last_read_at` | timestamp | Upsert when user opens thread |
| unique | `(thread_id, user_id)` | |

Unread for a user = thread has a message with `created_at > last_read_at` (or no read row) from the **other** side.

### Domain objects

- `App\Models\PlatformSupportThread`
- `App\Models\PlatformSupportMessage`
- `App\Models\PlatformSupportThreadRead`
- `App\Services\Platform\PlatformSupportService` — create thread, reply, close/reopen, mark read, inbox queries
- Notifications:
  - `PlatformSupportMessageFromTenant` → notify platform owner(s)
  - `PlatformSupportMessageFromPlatform` → notify tenant participants

### Authorization

| Actor | Can |
|---|---|
| Tenant `owner` / `admin` | List/create/reply/close threads for **their** `tenant_id` only |
| Tenant `staff` / `customer` | Denied |
| `platform_owner` | List all tenants’ threads; reply; close/reopen; create thread targeting a tenant |
| Unauthenticated | Denied |

Tenant routes stay on tenant host behind existing `auth` + `tenant` + `tenant.member` + role middleware (`owner,admin`).  
Platform routes stay on central host behind `auth` + `platform`.

### Routes

**Platform (central.php)**

- `GET /platform/support` — inbox  
- `GET /platform/support/{thread}` — show  
- `POST /platform/support` — start thread (tenant_id + subject + body)  
- `POST /platform/support/{thread}/messages` — reply  
- `PATCH /platform/support/{thread}` — close / reopen  

**Tenant admin (web.php under /admin)**

- `GET /admin/platform-support` — inbox  
- `GET /admin/platform-support/{thread}` — show  
- `POST /admin/platform-support` — start thread  
- `POST /admin/platform-support/{thread}/messages` — reply  
- `PATCH /admin/platform-support/{thread}` — close / reopen  

Thread IDs are global; tenant show/update must 404 if `thread.tenant_id !== current tenant`.

### UI

**Platform (`PlatformLayout`)**

- Sidebar nav item: **Support** (with unread count badge when > 0)
- Inbox: table of threads — tenant name, subject, status badge, last message preview, relative time, unread indicator
- Show: subject header, tenant meta, message list (tenant vs platform visually distinct), reply composer, close/reopen
- Tenants list: optional “Message” action → create thread pre-bound to that tenant
- Visual language: match current Catalyst-style platform console (light sidebar, white panel)

**Tenant admin**

- Nav item: **Platform support** (owner/admin only; unread badge)
- Same inbox / show / compose patterns using existing tenant admin layout
- Empty state copy that makes the purpose obvious (“Ask CourierOS about billing, bugs, or product changes”)

### Notifications

1. **Tenant posts (new thread or reply)**  
   - Email all users with `role = platform_owner`  
   - In-app unread via read receipts (platform owner opens thread → mark read)

2. **Platform owner posts**  
   - Email: thread creator if still owner/admin on that tenant, plus any other owner/admin who has already posted in the thread (dedupe)  
   - Fallback if none: tenant’s current owners  
   - In-app unread for those tenant users

Mail content: tenant name, subject, excerpt, deep link to the correct host (`/platform/support/{id}` or tenant `/admin/platform-support/{id}`).

Use queued notifications (existing mail + queue stack).

### Validation

- Subject: required, 3–160 chars  
- Body: required, 1–10_000 chars  
- Close/reopen: status must flip meaningfully  
- Rate limit creates/replies (e.g. `throttle:30,1` per user)

### Error handling

- Wrong-tenant thread access → 404 (not 403) to avoid leaking IDs  
- Closed thread: a new reply **auto-reopens** the thread (status `open`, clears `closed_at`)  
- Suspended tenant: tenant UI may be locked by existing middleware; platform owner can still read historical threads and reply (email still sends)

## Testing

Feature tests covering:

1. Owner/admin can create and list threads for their tenant  
2. Staff cannot access platform-support routes  
3. Tenant A cannot open Tenant B’s thread (404)  
4. Platform owner sees all threads and can reply  
5. Non–platform-owner cannot hit `/platform/support`  
6. Reply from tenant notifies platform owner (Notification fake)  
7. Reply from platform notifies tenant participants  
8. Marking a thread read clears unread for that user only  
9. Closing and reopening (including auto-reopen on reply)

## Rollout

1. Migrate tables  
2. Models + service + notifications  
3. Platform inbox UI + nav badge  
4. Tenant admin UI + nav badge  
5. “Message” from platform Tenants list  
6. Tests + manual pass on `courieros.test` / a tenant subdomain

## Success criteria

- A tenant admin can send a message and see your reply in-app without emailing outside the product.  
- You see a Support inbox on `/platform` with unread state and can reply.  
- Both sides get email for new activity.  
- No cross-tenant data leakage in tests.
