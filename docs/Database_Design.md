# DoctorStats — Database Design

> Implements `DoctorStats_ERD.md` as Laravel migrations in `webapp/database/migrations/`.
> Verified: `migrate:fresh` and `migrate:rollback` both pass (SQLite dry run).

---

## Migration Map

| Order | Migration | Tables |
|---|---|---|
| 0001_01_01_000000 | `create_users_table` (extended) | `users`, `password_reset_tokens`, `sessions` |
| 2026_06_10_000001 | `create_organizations_table` | `organizations` |
| 2026_06_10_000002 | `create_organization_members_table` | `organization_members` |
| 2026_06_10_000003 | `create_organization_invitations_table` | `organization_invitations` |
| 2026_06_10_000004 | `create_data_files_table` | `data_files` |
| 2026_06_10_000005 | `create_subscriptions_table` | `subscriptions` |
| 2026_06_10_000006 | `create_analysis_jobs_table` | `analysis_jobs` |
| 2026_06_10_000007 | `create_payments_table` | `payments` |
| 2026_06_10_000008 | `add_payment_foreign_key_to_analysis_jobs_table` | (alters `analysis_jobs`) |
| 2026_06_10_000009 | `create_analysis_job_members_table` | `analysis_job_members` |
| 2026_06_10_000010 | `create_analysis_columns_table` | `analysis_columns` |
| 2026_06_10_000011 | `create_analysis_results_table` | `analysis_results` |
| 2026_06_10_000012 | `create_report_folders_table` | `report_folders` |
| 2026_06_10_000013 | `create_reports_table` | `reports` |
| 2026_06_10_000014 | `create_report_tags_table` | `report_tags`, `report_report_tag` (pivot) |
| 2026_06_10_000015 | `create_report_shares_table` | `report_shares` |
| 2026_06_10_000016 | `create_notifications_table` | `notifications` |
| 2026_06_10_000017 | `create_audit_logs_table` | `audit_logs` |

---

## Key Design Decisions

### 1. Circular FK: `analysis_jobs` ↔ `payments`
A pay-per-job payment references its job (`payments.analysis_job_id`) and the job
references the payment that funded it (`analysis_jobs.payment_id`). Migration ordering:

1. `analysis_jobs` is created with `payment_id` as a plain nullable `unsignedBigInteger` (no constraint).
2. `payments` is created with its FK to `analysis_jobs`.
3. A follow-up migration (`...000008`) adds the `analysis_jobs.payment_id` FK constraint.

### 2. Soft deletes
`deleted_at` on **users, organizations, data_files, analysis_jobs, reports** —
supports GDPR deletion workflows, report delete/restore, and "re-run if source
data still available" (Phase 7) without breaking referential history.
Transactional/audit tables (`payments`, `audit_logs`, `analysis_results`, …) are never soft-deleted.

### 3. Extended `users` table (base migration edited)
Greenfield project, so the default Laravel migration was extended directly with
`account_type` (individual | organizational), `status` (active | deactivated), and soft deletes.

### 4. Enum columns
Native `enum` columns per the ERD (supported by MySQL 8 and PostgreSQL via check
constraints in Laravel). Changing allowed values later requires an alter migration.

### 5. Numeric precision
- `payments.amount`: `decimal(10,2)`, `currency` char(3) defaulting to `INR` (Zoho Pay).
- `analysis_results.p_value`: `decimal(11,10)` (range 0–1, 10 dp).
- `analysis_results.test_statistic`: `decimal(20,6)`.
- Complex/structured stats (CIs, effect sizes, assumption checks, raw output) stored as JSON.

### 6. On-delete behaviour
| Rule | Applied to |
|---|---|
| `cascadeOnDelete` | Dependent rows: members, invitations, job members, columns, results, reports (from job), shares, notifications, pivot rows |
| `nullOnDelete` | Nullable context FKs: `organization_id` on files/jobs/reports/payments, `report_folder_id`, `payment_id`/`subscription_id` on jobs |
| `restrictOnDelete` | `organizations.admin_user_id` (org must transfer admin first), `analysis_jobs.data_file_id` (file cannot vanish under a job) |

### 7. Indexes
- All FKs are indexed (implicit via `constrained()`).
- Status/lifecycle fields indexed: `users.status`, `organization_members.status`, `organization_invitations.status`, `data_files.virus_scan_status`, `analysis_jobs.status`, `payments.status`, `subscriptions.status`, `reports.status`.
- Composite: `analysis_jobs (organization_id, status)` for org dashboards, `notifications (user_id, read_at)` for unread counts, `audit_logs (entity_type, entity_id)` for entity history.
- Unique: `users.email`, `analysis_jobs.job_id` (public ID), `organization_invitations.token`, `report_shares.token`, `payments.zoho_payment_id`, `subscriptions.zoho_subscription_id`, `organization_members (organization_id, user_id)`, `analysis_job_members (analysis_job_id, user_id)`, `analysis_columns (analysis_job_id, column_index)`, `reports.analysis_job_id` (enforces the 1:1 job → report).

### 8. Report ↔ tag pivot
`report_report_tag` (composite PK `report_id + report_tag_id`) implements the
M:N "tagged_with" relationship; the ERD listed the relationship without naming the table.

### 9. Application-layer constraints (not enforceable in schema)
- `subscriptions` / `report_folders` / `report_tags`: exactly one of `user_id` / `organization_id` should be set (individual vs org context).
- Org members cannot fund org analyses with personal subscriptions (Phase 6).
- One **active** subscription per user/org (partial unique index possible on PostgreSQL only; keep in app layer for MySQL parity).

---

*Database design based on DoctorStats Development Plan v1.0 and ERD v1.0*
