# DoctorStats — API Reference (v1)

Base URL: `{APP_URL}/api/v1`

Authentication: Laravel Sanctum session cookies for the first-party SPA (`/login`, `/dashboard`). Send `X-XSRF-TOKEN` after calling `GET /sanctum/csrf-cookie`.

Protected routes require a verified email (`auth:sanctum` + `verified`).

---

## Auth

| Method | Path | Description |
|---|---|---|
| `POST` | `/auth/register` | Register individual or org account |
| `POST` | `/auth/login` | Session login |
| `POST` | `/auth/logout` | Session logout |
| `POST` | `/auth/forgot-password` | Request password reset email |
| `POST` | `/auth/reset-password` | Reset password with token |
| `GET` | `/auth/email/verify/{id}/{hash}` | Verify email (signed URL) |

## Profile & GDPR

| Method | Path | Description |
|---|---|---|
| `GET` | `/me` | Current user profile |
| `PATCH` | `/me` | Update name/email |
| `PATCH` | `/me/password` | Change password |
| `DELETE` | `/me` | Deactivate account |
| `GET` | `/me/activity` | Audit log activity |
| `GET` | `/me/export` | GDPR JSON data export |

## Dashboard

| Method | Path | Description |
|---|---|---|
| `GET` | `/dashboard` | KPIs, recent jobs, subscription summary |

## Data files

| Method | Path | Description |
|---|---|---|
| `POST` | `/data-files/upload/init` | Start chunked upload |
| `POST` | `/data-files/upload/{session}/chunk` | Upload chunk |
| `POST` | `/data-files/upload/{session}/complete` | Finalize chunked upload |
| `DELETE` | `/data-files/upload/{session}` | Abort upload session |
| `POST` | `/data-files` | Direct file upload |
| `GET` | `/data-files/{id}` | File metadata |
| `GET` | `/data-files/{id}/preview` | Preview rows |
| `GET` | `/data-files/{id}/sheets` | Excel sheet names |
| `DELETE` | `/data-files/{id}` | Delete file |

## Analysis jobs

| Method | Path | Description |
|---|---|---|
| `GET` | `/analysis-jobs` | List accessible jobs |
| `POST` | `/analysis-jobs` | Create analysis job |
| `GET` | `/analysis-jobs/{id}` | Job detail |
| `GET` | `/analysis-jobs/{id}/status` | Job status |
| `GET` | `/analysis-jobs/{id}/results` | Statistical results |
| `PATCH` | `/analysis-jobs/{id}/access` | Update org access scope |

## Payments

| Method | Path | Description |
|---|---|---|
| `GET` | `/payments` | Payment history |
| `GET` | `/payments/quote` | Pay-per-job quote |
| `POST` | `/payments/checkout` | Start pay-per-job checkout |
| `POST` | `/payments/{id}/stub-confirm` | Confirm stub payment (dev) |

## Subscriptions

| Method | Path | Description |
|---|---|---|
| `GET` | `/subscriptions/plans` | Available plans |
| `GET` | `/subscriptions/quote` | Plan price quote |
| `GET` | `/subscriptions/current` | Active subscription |
| `POST` | `/subscriptions/checkout` | Purchase/change plan |
| `POST` | `/subscriptions/renew` | Renewal checkout |
| `POST` | `/subscriptions/cancel` | Cancel auto-renew |

## Reports

| Method | Path | Description |
|---|---|---|
| `GET` | `/reports` | List reports |
| `GET` | `/reports/{id}` | Report metadata |
| `PATCH` | `/reports/{id}` | Update title/folder/tags |
| `GET` | `/reports/{id}/view` | HTML report view |
| `GET` | `/reports/{id}/download/{format}` | Download `html`, `pdf`, or `excel` |
| `GET` | `/reports/{id}/shares` | List shares |
| `POST` | `/reports/{id}/shares` | Create share link/email |
| `DELETE` | `/reports/{id}/shares/{shareId}` | Revoke share |

## Report library

| Method | Path | Description |
|---|---|---|
| `GET/POST` | `/report-folders` | Manage folders |
| `PATCH/DELETE` | `/report-folders/{id}` | Update/delete folder |
| `GET/POST` | `/report-tags` | Manage tags |
| `PATCH/DELETE` | `/report-tags/{id}` | Update/delete tag |

## Organizations

| Method | Path | Description |
|---|---|---|
| `GET` | `/organizations/{id}` | Org profile (member) |
| `GET` | `/organizations/{id}/analytics` | Org analytics (member) |
| `GET` | `/organizations/{id}/members` | Member list (member) |
| `PATCH` | `/organizations/{id}` | Update org (admin) |
| `GET/POST/DELETE` | `/organizations/{id}/invitations` | Manage invitations (admin) |
| `PATCH/DELETE` | `/organizations/{id}/members/{memberId}` | Manage members (admin) |
| `POST` | `/invitations/{token}/accept` | Accept invitation |

## Notifications

| Method | Path | Description |
|---|---|---|
| `GET` | `/notifications` | List notifications (`?unread=1`) |
| `PATCH` | `/notifications/{id}/read` | Mark read |
| `POST` | `/notifications/read-all` | Mark all read |

## Public shared reports

| Method | Path | Description |
|---|---|---|
| `GET` | `/shared/{token}` | View shared report metadata |
| `POST` | `/shared/{token}/unlock` | Unlock password-protected share |

## Internal (service-to-service)

| Method | Path | Auth | Description |
|---|---|---|---|
| `POST` | `/internal/v1/analysis-callback` | `X-Service-Token` | Stats service callback |
| `GET` | `/internal/v1/data-files/{id}/download` | `X-Service-Token` | File download for stats service |
| `POST` | `/internal/v1/webhooks/zoho` | Zoho signature | Payment webhooks |

---

## Web UI routes

| Path | Description |
|---|---|
| `/` | Landing page |
| `/login` | Sign in (Alpine + API) |
| `/register` | Registration |
| `/dashboard` | Authenticated dashboard |

See [Production_Integrations.md](Production_Integrations.md) for environment configuration.
