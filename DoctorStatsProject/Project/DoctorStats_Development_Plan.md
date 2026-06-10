# DoctorStats — Development Plan

> **Project**: DoctorStats — Statistical Analysis Platform (Analysis as a Service)
> **Document Version**: 1.0
> **Status**: Draft

---

## Tech Stack Summary

| Layer | Technology |
|---|---|
| Backend | Laravel 12.x / PHP 8.2+ |
| Frontend | Tailwind CSS 3.x, Alpine.js / Vue.js, Vite |
| Statistical Engine | Python 3.10+ microservice |
| Database | MySQL 8.0+ / PostgreSQL 13+, Redis |
| File Storage | S3-compatible (AWS S3 / DigitalOcean Spaces) |
| AI | Anthropic Claude (primary), Google Gemini (fallback) |
| Payments | Zoho Pay |
| Email | ZeptoMail |

---

## Phase Overview

| Phase | Focus | Estimated Duration |
|---|---|---|
| 1 | Foundation & Infrastructure | 2–3 weeks |
| 2 | User Management & Organisations | 2–3 weeks |
| 3 | Data Upload & Analysis Job Creation | 2–3 weeks |
| 4 | Python Statistical Engine | 3–4 weeks |
| 5 | AI Interpretation & Report Generation | 2–3 weeks |
| 6 | Payments & Subscriptions | 2 weeks |
| 7 | Report Management & Sharing | 1–2 weeks |
| 8 | Notifications | 1 week |
| 9 | Security & Compliance | 1–2 weeks |
| 10 | Testing & QA | 2 weeks |
| 11 | DevOps, Monitoring & Documentation | 1 week |
| **Total** | | **~20–27 weeks** |

---

## Phase 1 — Foundation & Infrastructure

**Duration: 2–3 weeks**

The goal is a working skeleton that all future features plug into.

### Environment Setup
- Configure Dev / Staging / Production environments
- Set up CI/CD pipeline (GitHub Actions or similar) with automated testing and deployment hooks
- Provision S3-compatible storage, Redis, and the database server

### Project Scaffolding
- Initialise Laravel 12 project with Vite + Tailwind CSS + Alpine.js
- Set up the Python statistical microservice as a separate service with a RESTful API contract defined upfront
- Configure Laravel Queues backed by Redis
- Integrate Sentry (or equivalent) for error tracking from day one

### Database Migrations
- Implement all core table migrations:
  - `users`, `organizations`, `organization_members`
  - `analysis_jobs`, `analysis_job_members`, `analysis_results`
  - `reports`, `payments`, `subscriptions`, `data_files`
  - `notifications`, `report_shares`, `organization_invitations`
- Add indexes on all frequently queried foreign keys and status fields

### Authentication Scaffolding
- Laravel Sanctum / Fortify for session-based auth
- CSRF, XSS, and SQL injection protections configured from the start
- Rate limiting applied to all API endpoints

---

## Phase 2 — User Management & Organisations

**Duration: 2–3 weeks**

### Registration & Verification
- Individual and organisational account registration flows
- ZeptoMail integration for:
  - Email verification links
  - Welcome emails
  - Password reset emails
- Terms of service / privacy policy acceptance gate

### Authentication
- Login, logout, "remember me", configurable session timeout
- Password reset flow
- Password strength enforcement (min 8 chars, uppercase, lowercase, number, special character)

### Profile Management
- Edit profile, change password, email update with re-verification
- Account deletion / deactivation
- Account activity history view

### Organisation Management
- Organisation creation during registration (admin = account creator)
- Member invitation via tokenised email links (ZeptoMail) with expiry
- Role assignment: **Admin** / **Analyst** / **Viewer**
- Member status tracking: active, pending invitation, inactive
- Organisation admin panel: member list, activity logs, settings, branding (Enterprise)

### User Dashboard
- Job status overview: Pending, In-Progress, Completed, Failed
- Quick stats: total analyses, active subscription, account balance, recent activity
- Org-specific views: member count, shared analyses count
- Quick-access links: New Analysis, Reports, Subscription, Payment History, Org Management

---

## Phase 3 — Data Upload & Analysis Job Creation

**Duration: 2–3 weeks**

### File Upload
- Supported formats: `.xlsx`, `.xls`, `.csv` (max 50 MB per upload)
- Chunked upload with progress indicator and cancel capability
- Server-side validation: format, structure (headers + data rows), file integrity, virus scanning
- Multi-sheet support for Excel files
- Preview of first 10 rows after upload
- Secure storage in S3 with access-controlled paths

### Column Description Interface
- Visual mapping table showing uploaded columns
- Per-column metadata input:
  - Data type (categorical, numerical, date, text, etc.)
  - Description and unit of measurement
  - Variable type (independent, dependent, control, identifier, excluded)
- Data quality warnings: missing values, type inconsistencies, optional outlier preview

### Analysis Job Creation Wizard
1. Upload data file
2. Enter objectives (min 50 characters, with template suggestions)
3. Describe columns via mapping interface
4. Assign access permissions (org accounts: all members / specific members / private)
5. Select payment method (pay-per-job) or confirm subscription usage
6. Review and submit

- Unique job ID generated on submission
- Job status lifecycle: **Pending → Processing → Completed / Failed**
- Org jobs associated with the organisation, not just the creator

---

## Phase 4 — Python Statistical Analysis Engine

**Duration: 3–4 weeks** *(develop in parallel with Phase 3)*

> **Note**: This is the most technically complex component. Begin early and validate against reference datasets before integration.

### Core Service
- FastAPI or Flask microservice exposing:
  - `POST /api/analyze` — submit data for analysis
  - `GET /api/analysis/{id}/status` — check job status
  - `GET /api/analysis/{id}/results` — retrieve results
  - `POST /api/generate-graphs` — generate visualisation graphs
- Async job execution integrated with Laravel's queue system

### Automated Test Selection Logic
- **Data Profiling**:
  - Descriptive statistics (mean, median, mode, SD, etc.)
  - Distribution analysis
  - Missing data pattern detection
  - Outlier identification
- **Test Selection Engine** (based on data types, group count, distribution assumptions, objectives):

| Category | Tests |
|---|---|
| Frequency Analysis | Frequency tables, cross-tabulations, chi-square |
| Hypothesis Testing | T-tests (one-sample, independent, paired), ANOVA (one-way, two-way, repeated measures) |
| Non-Parametric | Mann-Whitney U, Kruskal-Wallis, Wilcoxon |
| Correlation | Pearson, Spearman |
| Regression | Linear, logistic, multiple |
| Advanced | Survival analysis, time series, multivariate, factor analysis, cluster analysis |

### Result Storage
Per test: name, parameters, test statistic, p-value, confidence intervals, effect sizes, assumptions validation results.

### Visualisation Generation
- Publication-ready, high-resolution graphs via matplotlib / seaborn:
  - Bar charts, histograms, box plots, scatter plots, line charts, heatmaps (correlation matrices)
- Graph captions and descriptions generated automatically
- Outputs returned to Laravel for report assembly

### Error Handling
- Graceful handling of: insufficient data, violated assumptions, computational errors
- Progress updates streamed back to Laravel for long-running jobs (>30s)
- Automatic retry with configurable limits

---

## Phase 5 — AI Interpretation & Report Generation

**Duration: 2–3 weeks**

### AI Service Layer
- Provider abstraction layer for multi-provider support
- **Anthropic Claude** as primary provider
- **Google Gemini** as automatic fallback
- Structured prompt construction: objectives + column descriptions + statistical results → interpretation
- Token usage and cost tracking per provider
- Error handling with automatic retry and fallback switching

### AI-Generated Content
Each report interpretation includes:
- Plain-language explanation of statistical results
- Clinical / research significance
- Limitations and caveats
- Recommendations for further analysis

### Report Assembly
Sections in every report:
1. **Executive Summary** — key findings, conclusions, recommendations
2. **Data Overview** — dataset description, sample size, data quality assessment
3. **Methodology** — tests performed, rationale, assumptions checked
4. **Results** — detailed test results, significance, effect sizes, confidence intervals
5. **Visualisations** — all generated graphs with captions
6. **Interpretation** — AI-generated analysis and clinical implications
7. **Appendices** — raw statistical output, data summary tables, assumption test results

### Report Export Formats
| Format | Details |
|---|---|
| Web (HTML) | Interactive, expandable sections, interactive charts (Chart.js / D3.js) |
| PDF | Formatted, print-ready, publication quality |
| Excel | Raw results in structured spreadsheet |

- Section include/exclude options
- Custom branding for Enterprise / org accounts

---

## Phase 6 — Payments & Subscriptions

**Duration: 2 weeks**

### Zoho Pay Integration
- Payment processing for pay-per-job (payment required before analysis starts)
- Webhook handling for payment status events
- Invoice generation and receipt delivery via ZeptoMail
- Refund processing for system-error failures only

### Subscription Tiers

**Individual Plans**
| Plan | Features |
|---|---|
| Basic | Limited analyses per month |
| Professional | Unlimited analyses, priority processing |
| Enterprise | Custom limits, dedicated support, custom branding |

**Organisational Plans**
| Plan | Features |
|---|---|
| Organisation Basic | Limited analyses/month, up to 5 members |
| Organisation Professional | Unlimited analyses, priority processing, up to 25 members |
| Organisation Enterprise | Custom limits, dedicated support, custom branding, unlimited members |

### Subscription Features
- Monthly / annual billing with auto-renewal (configurable)
- Upgrade / downgrade with prorated mid-cycle billing
- Subscription usage dashboard (analyses used, member count)
- Organisation members **cannot** use personal subscriptions for org analyses

---

## Phase 7 — Report Management & Sharing

**Duration: 1–2 weeks**

### Report Storage & Organisation
- List, search, and filter reports by: date range, keywords, analysis type, creator (org)
- Folder / tag system and favourites / bookmarks
- Org-wide shared folders and tags

### Report Actions
- View, download (PDF / Excel / HTML)
- Re-run analysis (if source data still available)
- Delete, duplicate with modifications
- Transfer analysis ownership (org accounts)

### Sharing Options
| Method | Details |
|---|---|
| Secure link | Optional password protection, time-limited |
| Email | Via ZeptoMail — link or attachment |
| Org internal | Organisation-wide or specific members |
| External | Public link for non-organisation users |

---

## Phase 8 — Notifications

**Duration: 1 week**

### Email Notifications (ZeptoMail)
Full template library covering:
- Analysis completion
- Payment confirmation and subscription renewal reminders
- Account activity alerts and system maintenance notices
- Email verification, password reset, welcome emails
- Organisation: member invitations, added/removed, analysis shared/assigned, subscription updates

All templates use DoctorStats branding (HTML + plain text).
Queue-based sending for performance. Delivery status tracking and bounce handling.

### In-App Notifications
- Real-time job status updates (polling or WebSockets)
- Payment and system message alerts
- Per-user notification preferences management

---

## Phase 9 — Security & Compliance

**Duration: 1–2 weeks**

### Data Security
- Encryption at rest: AES-256
- Encryption in transit: TLS 1.3
- Encrypted sensitive database fields
- File virus scanning, type validation, automatic cleanup for expired data
- Org data isolation: members can only access their own organisation's data

### Access Control Audit
- RBAC enforcement review across all routes and API endpoints
- Organisation membership verification on all org-scoped operations
- Analysis-level access control verification

### Compliance
- **GDPR**: data export, right to deletion, cookie consent management
- **HIPAA readiness**: data anonymisation options, audit logging, BAA documentation guidance
- Full audit log coverage for all user actions and data access events

### Authentication Hardening
- Password hashing: bcrypt / argon2
- CSRF, XSS, rate limiting final hardening pass
- Session security review

---

## Phase 10 — Testing & QA

**Duration: 2 weeks** *(ongoing throughout all phases)*

### Test Coverage Targets
| Type | Target |
|---|---|
| Unit tests | >80% code coverage |
| Integration tests | All critical workflows |
| E2E tests | All primary user journeys |
| Statistical accuracy | Validated against reference datasets |

### Test Tooling
- **PHP**: PHPUnit (unit), Laravel Feature Tests, Laravel Dusk or Playwright (E2E)
- **Python**: pytest (unit + statistical accuracy)
- **API**: Postman / automated API test suite

### Performance & Load Testing
- Verify all response time SLAs:
  - Page load: <2s
  - Simple analysis: <30s
  - Complex analysis: <5 min
  - Very complex analysis: <15 min
- Concurrency testing for 1,000+ simultaneous users

### Security Testing
- Penetration testing pass covering OWASP Top 10
- File upload security validation
- Auth and session security review

---

## Phase 11 — DevOps, Monitoring & Documentation

**Duration: 1 week**

### CI/CD & Deployment
- Finalise CI/CD: automated test → code quality check → zero-downtime deployment
- Database migration version control and rollback strategy
- Environment separation: Development / Staging / Production

### Monitoring
- Application monitoring: uptime, performance metrics (APM)
- Real-time error monitoring via Sentry
- Resource monitoring: CPU, memory, disk
- User analytics: usage patterns, feature adoption

### Documentation
| Type | Deliverable |
|---|---|
| Technical | API docs (Swagger / Postman collection) |
| Technical | Database schema documentation |
| Technical | Developer setup and deployment guides |
| User-facing | User guide / manual |
| User-facing | Help centre with FAQ and search |

---

## Key Development Notes

### Start the Python Engine Early
Phase 4 is the longest and most technically risky component. Begin building and validating the statistical engine in parallel with Phase 3 so it does not become a blocker at report generation time.

### Define the API Contract in Phase 1
Establish the Laravel ↔ Python service API contract during Phase 1 so both tracks can work independently without integration surprises.

### Organisational Access Control is Pervasive
Org-level access touches jobs, reports, files, notifications, and payments. Implement it cleanly in Phase 2 and enforce it consistently throughout — retrofitting access control is significantly more expensive.

### Budget Time for AI Prompt Engineering
The AI interpretation layer will require iteration. Plan for testing and refinement of prompts against real (anonymised) clinical datasets before the MVP launch date.

### MVP Success Criteria (from Requirements)
- 95% of analysis jobs complete successfully
- Average report generation time < 5 minutes
- User satisfaction score > 4.0 / 5.0
- 99.9% system uptime

---

*Development Plan based on Requirements Document v1.0 — Approved by Mandar Sahasrabuddhe (30 Nov 2025)*
