# DoctorStats — Production Integrations

This guide explains how to move from local stub mode to real payment and AI providers.

## Quick reference

| Integration | Local / dev (default) | Production |
|---|---|---|
| Payments | `DOCTORSTATS_PAYMENT_STUB=true` | `DOCTORSTATS_PAYMENT_STUB=false` + Zoho Pay credentials |
| AI interpretation | `DOCTORSTATS_AI_STUB=true` | `DOCTORSTATS_AI_STUB=false` + Anthropic and/or Gemini keys |
| Stats service | `STATS_SERVICE_URL=http://stats-service:8001` | Same pattern; set a shared `STATS_SERVICE_TOKEN` |

---

## Payments (Zoho Pay)

### How it works

1. Client calls `POST /api/v1/payments/checkout` (pay-per-job) or `POST /api/v1/subscriptions/checkout` (subscription).
2. Laravel creates a `payments` row and returns a `checkout_url`.
3. User completes payment on Zoho Pay.
4. Zoho posts to `POST /internal/v1/webhooks/zoho`.
5. `PaymentService` marks the payment succeeded and, for subscription/renewal payments, `SubscriptionService` activates or renews the subscription.

### Stub mode (local)

```env
DOCTORSTATS_PAYMENT_STUB=true
```

Checkout URLs point to `POST /api/v1/payments/{id}/stub-confirm`, which simulates a successful payment without contacting Zoho.

### Production setup

```env
DOCTORSTATS_PAYMENT_STUB=false

ZOHO_PAY_API_KEY=your_api_key
ZOHO_PAY_ACCOUNT_ID=your_account_id
ZOHO_PAY_WEBHOOK_SECRET=your_webhook_signing_secret
ZOHO_PAY_BASE_URL=https://payments.zoho.com/api/v1
```

**Webhook URL to register in Zoho Pay:**

```
https://<your-domain>/internal/v1/webhooks/zoho
```

**Return URLs used by checkout sessions:**

- Pay-per-job: `{APP_URL}/payments/return`
- Subscriptions: `{APP_URL}/subscriptions/return`

### Subscription API endpoints

| Method | Endpoint | Purpose |
|---|---|---|
| `GET` | `/api/v1/subscriptions/quote` | Price quote (`plan_tier`, `billing_cycle`) |
| `POST` | `/api/v1/subscriptions/checkout` | Start subscription purchase |
| `POST` | `/api/v1/subscriptions/renew` | Start renewal checkout |
| `POST` | `/api/v1/subscriptions/cancel` | Cancel auto-renew (access continues until `ends_at`) |
| `GET` | `/api/v1/subscriptions/current` | Active subscription for user or org |

Confirm subscription payments with the same stub endpoint in dev:

```
POST /api/v1/payments/{payment_id}/stub-confirm
```

---

## AI interpretation (Anthropic + Gemini)

### How it works

When a stats callback completes, `GenerateReportJob` calls `AiInterpretationService`, which:

1. Builds a structured context payload from the analysis job and results.
2. Tries providers in order: **Anthropic → Gemini → stub** (when stub mode is off).
3. Stores the interpretation in the generated report.

### Stub mode (local)

```env
DOCTORSTATS_AI_STUB=true
```

Only the stub provider runs. No external API calls are made.

### Production setup

```env
DOCTORSTATS_AI_STUB=false

ANTHROPIC_API_KEY=sk-ant-...
ANTHROPIC_MODEL=claude-sonnet-4-5

GEMINI_API_KEY=...
GEMINI_MODEL=gemini-2.5-pro
```

At least one provider key must be valid. If Anthropic fails, Gemini is tried automatically. If both fail, the stub provider is used as a last resort so report generation still completes.

### Verifying AI in staging

1. Set `DOCTORSTATS_AI_STUB=false`.
2. Provide a valid `ANTHROPIC_API_KEY` (or `GEMINI_API_KEY`).
3. Complete an analysis job end-to-end.
4. Inspect the report's `ai_interpretation` field and audit log entry `report.generated` (includes `ai_provider`).

---

## Statistical service

```env
STATS_SERVICE_URL=http://stats-service:8001
STATS_SERVICE_TOKEN=shared-secret-between-laravel-and-python
STATS_SERVICE_TIMEOUT=900
```

Set the same token in `stats-service/.env` as `SERVICE_TOKEN`. Laravel sends it as `X-Service-Token` on analyze and graph requests; the Python service validates it on every internal endpoint.

---

## Checklist before go-live

- [ ] `DOCTORSTATS_PAYMENT_STUB=false`
- [ ] `DOCTORSTATS_AI_STUB=false`
- [ ] Zoho Pay API key, account ID, and webhook secret configured
- [ ] Zoho webhook URL registered and reachable from the internet
- [ ] Anthropic and/or Gemini API keys configured
- [ ] `STATS_SERVICE_TOKEN` set on both Laravel and Python services
- [ ] `SENTRY_LARAVEL_DSN` set for error tracking
- [ ] `ZEPTOMAIL_API_KEY` set for transactional email (or production SMTP)
- [ ] `APP_URL` set to the public HTTPS origin
