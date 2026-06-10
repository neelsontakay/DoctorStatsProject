# DoctorStats - Requirements Document

## 1\. Project Overview

DoctorStats is a statistical analysis platform for clinical data analysis that operates on an "Analysis as a Service" model. The platform enables doctors and researchers to upload clinical data, specify analysis objectives, and receive automated statistical analysis with AI-powered interpretations and comprehensive reports.

### 1.1 Target Users

* Medical doctors
* Clinical researchers
* Healthcare data analysts
* Medical institutions

### 1.2 Business Model

*  **Pay-per-job**: Users pay for individual analysis jobs
*  **Subscription**: Users can subscribe for unlimited or tiered access to analysis services

---

## 2\. Functional Requirements

### 2.1 User Management

#### 2.1.1 User Registration

* Users must be able to create an account with:
  *  **Account Type Selection**:
    * Individual account (personal use)
    * Organizational account (institutional/team use)
  * Full name
  * Email address (must be unique and verified)
  * Password (minimum 8 characters, must include uppercase, lowercase, number, and special character)
  * Professional title/affiliation (optional)
  * Organization/institution (required for organizational accounts)
*  **For Organizational Accounts**:
  * Organization name (required)
  * Organization domain/website (optional)
  * Organization size (number of members, optional)
  * Initial admin user (account creator becomes organization administrator)
* Email verification required before account activation
* Terms of service and privacy policy acceptance required
* Account activation via email verification link (sent via ZeptoMail)

#### 2.1.2 User Authentication

* Login with email and password
* "Remember me" functionality
* Password reset via email (sent via ZeptoMail)
* Session management with configurable timeout
* Two-factor authentication (optional, future enhancement)

#### 2.1.3 User Profile Management

* Users can view and edit their profile information
* Change password functionality
* Email address update (requires re-verification)
* Account deletion/deactivation option
* View account activity history

#### 2.1.4 User Dashboard

* Overview of all analysis jobs (pending, in-progress, completed, failed)
  * For organizational accounts: View all organization's analyses or filter by assigned analyses
* Quick statistics:
  * Total analyses performed (individual or organizational)
  * Active subscriptions
  * Account balance (for pay-per-job users)
  * Recent activity
  * For organizational accounts: Organization member count, shared analyses count
* Quick access to:
  * Create new analysis
  * View previous reports
  * Manage subscription
  * Payment history
  * For organizational accounts: Organization management, member management

#### 2.1.5 Organizational Account Management

*  **Organization Administration** (for organization admins):
  * View organization profile and settings
  * Manage organization subscription (separate from individual subscriptions)
  * View organization billing and payment history
  * Organization branding customization (for Enterprise plans)
  * Organization settings and preferences
*  **Member Management**:
  * Add members to organization:
    * Invite members via email (sent via ZeptoMail)
    * Invitation link with expiration
    * Member role assignment during invitation (Admin, Analyst, Viewer)
  * Remove members from organization
  * View all organization members
  * Member role management:
    *  **Admin**: Full access to organization settings, member management, all analyses
    *  **Analyst**: Can create analyses, view assigned analyses, manage own analyses
    *  **Viewer**: Can only view analyses assigned to them
  * Member status tracking (active, pending invitation, inactive)
  * Member activity logs
*  **Access Control for Analyses**:
  * When creating an analysis job, assign access to:
    * Organization (all members can access)
    * Specific members (select individual members)
    * Only creator (private to creator)
  * Modify access permissions for existing analyses
  * View all analyses accessible to the user (created by user or shared with user)
  * Transfer analysis ownership between organization members
*  **Organization Analytics**:
  * View organization-wide statistics:
    * Total analyses performed by organization
    * Analyses by member
    * Usage trends
    * Subscription utilization

### 2.2 Data Upload and Management

#### 2.2.1 File Upload

* Support for Excel file formats:
  * ` .xlsx`(Excel 2007+)
  * ` .xls`(Excel 97-2003)
  * ` .csv`(Comma-separated values)
* Maximum file size: 50MB per upload
* File validation:
  * Format verification
  * Basic structure validation (headers, data rows)
  * File integrity checks
* Upload progress indicator
* Ability to cancel uploads in progress
* Support for multiple sheets (if Excel format)
* Data preview after upload (first 10 rows)

#### 2.2.2 Data Description Input

* Users must provide:
  *  **Objectives**: Clear description of what they want to analyze
    * Free-text input (minimum 50 characters)
    * Suggested templates/guidelines
  *  **Column Descriptions**: For each column in the dataset:
    * Column name
    * Data type (categorical, numerical, date, text, etc.)
    * Description/meaning of the column
    * Unit of measurement (if applicable)
    * Variable type (independent, dependent, control, etc.)
* Column mapping interface:
  * Visual table showing uploaded data
  * Ability to select and describe each column
  * Mark columns as identifiers (patient ID, etc.)
  * Mark columns to exclude from analysis
* Data validation warnings:
  * Missing values detection
  * Data type inconsistencies
  * Outlier detection (optional preview)

#### 2.2.3 Data Storage

* Secure storage of uploaded files
* Data retention policy:
  * Active data: Stored for duration of subscription or 90 days (whichever is longer)
  * Completed analyses: Data retained for 1 year
  * Deleted data: Permanently removed after 30 days
* Data encryption at rest
* Access control:
  * Individual accounts: Users can only access their own data
  * Organizational accounts: Users can access data based on assigned permissions (organization-wide, member-specific, or private)

### 2.3 Analysis Workflow

#### 2.3.1 Analysis Request Creation

* Users create a new analysis job by:
  1. Uploading data file
  2. Providing objectives
  3. Describing data columns
  4.  **For organizational accounts**: Assigning access permissions:
     * Select who can access this analysis (all organization members, specific members, or private)
     * Default: Organization-wide access for organizational accounts
  5. Selecting payment method (if pay-per-job)
     * For organizational accounts: Uses organization subscription or organization payment method
  6. Confirming and submitting
* Analysis job receives unique identifier
* Job status tracking: Pending → Processing → Completed/Failed
*  **For organizational accounts**: Analysis jobs are associated with the organization, not just the creator

#### 2.3.2 Automated Analysis Selection

The system must automatically determine:

*  **Data exploration needs**:
  * Descriptive statistics (mean, median, mode, standard deviation, etc.)
  * Data distribution analysis
  * Missing data patterns
  * Outlier identification
*  **Appropriate statistical tests** based on:
  * Data types (categorical vs. numerical)
  * Number of groups/variables
  * Distribution assumptions
  * Research objectives
*  **Test selection logic**:
  *  **Frequency Analysis**: For categorical data
    * Frequency tables
    * Cross-tabulations
    * Chi-square tests (when applicable)
  *  **Hypothesis Testing**:
    * T-tests (one-sample, independent, paired)
    * ANOVA (one-way, two-way, repeated measures)
    * Non-parametric tests (Mann-Whitney U, Kruskal-Wallis, Wilcoxon)
    * Chi-square tests for independence
    * Correlation analysis (Pearson, Spearman)
    * Regression analysis (linear, logistic, multiple)
  *  **Advanced Analysis** (when data supports):
    * Survival analysis
    * Time series analysis
    * Multivariate analysis
    * Factor analysis
    * Cluster analysis

#### 2.3.3 Analysis Execution

* Statistical tests executed in appropriate order
* Results stored with:
  * Test name and parameters
  * Test statistics
  * P-values
  * Confidence intervals
  * Effect sizes (when applicable)
  * Assumptions validation results
* Error handling for:
  * Insufficient data
  * Violated assumptions
  * Computational errors
* Progress tracking for long-running analyses

### 2.4 AI-Powered Result Interpretation

#### 2.4.1 AI Analysis Assessment

* Integration with multiple AI providers:
  *  **Anthropic Claude** (primary)
  *  **Google Gemini** (fallback/alternative)
* AI receives:
  * Analysis objectives
  * Data description
  * Statistical test results
  * Test parameters and assumptions
* AI generates:
  * Interpretation of statistical results
  * Clinical/research significance
  * Limitations and caveats
  * Recommendations for further analysis
  * Plain-language explanations

#### 2.4.2 Report Generation

* Comprehensive analysis report including:
  *  **Executive Summary**
    * Key findings
    * Main conclusions
    * Recommendations
  *  **Data Overview**
    * Dataset description
    * Sample size
    * Data quality assessment
  *  **Methodology**
    * Statistical tests performed
    * Rationale for test selection
    * Assumptions checked
  *  **Results Section**
    * Detailed test results
    * Statistical significance
    * Effect sizes
    * Confidence intervals
  *  **Visualizations**
    * Appropriate graphs for each analysis:
      * Bar charts (categorical data)
      * Histograms (distribution)
      * Box plots (comparisons)
      * Scatter plots (correlations)
      * Line charts (trends)
      * Heatmaps (correlation matrices)
    * Graph quality: Publication-ready, high resolution
    * Graph descriptions and captions
  *  **Interpretation**
    * AI-generated interpretations
    * Clinical/research implications
    * Limitations
    * Future research suggestions
  *  **Appendices**
    * Raw statistical output
    * Data summary tables
    * Assumption test results

#### 2.4.3 Report Formats

*  **Web View**: Interactive HTML report with:
  * Expandable sections
  * Interactive graphs
  * Download options
*  **PDF Export**: Printable, formatted PDF report
*  **Excel Export**: Detailed results in spreadsheet format
* Report customization:
  * Include/exclude sections
  * Custom branding (for institutional users)

### 2.5 Payment and Subscription Management

#### 2.5.1 Payment Integration

*  **Zoho Pay** integration for:
  * Payment processing
  * Subscription management
  * Invoice generation
  * Payment history

#### 2.5.2 Pay-Per-Job Model

* Users can purchase individual analysis jobs
* Pricing tiers based on:
  * Dataset size (number of rows/columns)
  * Complexity of analysis
  * Number of statistical tests required
* Payment required before analysis starts
* Refund policy for failed analyses (system errors only)

#### 2.5.3 Subscription Model

*  **Individual Subscriptions**:
  * Multiple subscription tiers:
    *  **Basic**: Limited analyses per month
    *  **Professional**: Unlimited analyses, priority processing
    *  **Enterprise**: Custom limits, dedicated support, custom branding
  * Subscription features:
    * Monthly/annual billing options
    * Auto-renewal (configurable)
    * Upgrade/downgrade options
    * Prorated billing for mid-cycle changes
  * Subscription management:
    * View current plan
    * Change subscription tier
    * Cancel subscription
    * Payment method management
*  **Organizational Subscriptions**:
  * Separate subscription model for organizations (not individual)
  * Multiple subscription tiers:
    *  **Organization Basic**: Limited analyses per month, up to 5 members
    *  **Organization Professional**: Unlimited analyses, priority processing, up to 25 members
    *  **Organization Enterprise**: Custom limits, dedicated support, custom branding, unlimited members
  * Organization subscription features:
    * Monthly/annual billing options
    * Auto-renewal (configurable)
    * Upgrade/downgrade options
    * Prorated billing for mid-cycle changes
    * Member limit based on subscription tier
    * Shared analysis quota across all organization members
    * Organization admin manages subscription
  * Organization subscription management:
    * View current organization plan
    * Change subscription tier
    * Cancel subscription
    * Payment method management
    * View subscription usage (analyses used, member count)
  *  **Important**: Individual users who are part of an organization cannot use their individual subscription for organization analyses; organization must have its own subscription

#### 2.5.4 Billing and Invoicing

* Automatic invoice generation
* Payment history accessible to users
* Receipt generation
* Tax calculation (if applicable)
* Multiple currency support (future enhancement)

### 2.6 Report Management

#### 2.6.1 Report Storage

* All completed reports stored in user account or organization account
*  **For Individual Accounts**: Reports are private to the user
*  **For Organizational Accounts**: Reports are accessible based on analysis access permissions
* Search and filter functionality:
  * By date range
  * By keywords
  * By analysis type
  * For organizational accounts: By creator, by assigned members
* Report organization:
  * Folders/tags (user-created)
  * Favorites/bookmarks
  * For organizational accounts: Organization-wide folders/tags

#### 2.6.2 Report Sharing

* Share reports via:
  * Secure link (password-protected, optional)
  * Email (sent via ZeptoMail with report link or attachment)
  * Download link (time-limited)
  *  **For organizational accounts**: Share with organization members directly
* Access control:
  * Public (anyone with link)
  * Private (only user/creator)
  * Shared (specific users)
  *  **For organizational accounts**:
    * Organization-wide (all members)
    * Specific members (selected organization members)
    * External sharing (non-organization users via link)

#### 2.6.3 Report Actions

* View report
* Download report (PDF, Excel, HTML)
* Re-run analysis (if data still available)
* Delete report
* Duplicate analysis with modifications

### 2.7 Notifications

#### 2.7.1 Email Notifications

*  **ZeptoMail Integration** for all email communications:
  * Analysis completion notifications
  * Payment confirmations
  * Subscription renewal reminders
  * Account activity alerts
  * System maintenance notifications
  * Email verification links
  * Password reset emails
  * Welcome emails for new users
  * Report sharing via email
  *  **Organizational notifications**:
    * Organization member invitation emails
    * Member added/removed notifications
    * Analysis assigned/shared notifications
    * Organization subscription updates
* Email templates with DoctorStats branding
* Email delivery tracking and status monitoring
* Support for HTML and plain text email formats
* Email preference management for users

#### 2.7.2 In-App Notifications

* Real-time status updates for analysis jobs
* Payment confirmations
* System messages
* Notification preferences management

---

## 3\. Technical Requirements

### 3.1 Technology Stack

#### 3.1.1 Backend

*  **Framework**: Laravel 12.x
*  **PHP**: PHP 8.2 or higher
*  **Database**:
  * Primary: MySQL 8.0+ or PostgreSQL 13+
  * Cache: Redis (for session and cache management)
*  **Queue System**: Laravel Queues with Redis/Database driver
*  **File Storage**:
  * Local storage for development
  * S3-compatible storage for production (AWS S3, DigitalOcean Spaces, etc.)

#### 3.1.2 Frontend

*  **CSS Framework**: Tailwind CSS 3.x
*  **JavaScript**: Vanilla JavaScript with Alpine.js (or Vue.js for complex interactions)
*  **Build Tool**: Vite
*  **Charts/Graphs**:
  * Chart.js or D3.js for interactive visualizations
  * Python libraries (via API) for statistical graphs

#### 3.1.3 Statistical Analysis

*  **Primary**: Python-based statistical engine
  * Python 3.10+
  * Libraries:
    * pandas (data manipulation)
    * numpy (numerical computing)
    * scipy (statistical tests)
    * statsmodels (advanced statistics)
    * matplotlib/seaborn (graph generation)
    * scikit-learn (machine learning, if needed)
*  **Communication**: RESTful API between Laravel and Python service
*  **Alternative**: PHP statistical libraries (for simpler analyses):
  * MathPHP (basic statistics)
  * PHP-ML (machine learning)

#### 3.1.4 AI Integration

*  **Anthropic Claude API**:
  * API key management
  * Request/response handling
  * Error handling and retries
  * Token usage tracking
*  **Google Gemini API**:
  * API key management
  * Fallback mechanism
  * Response comparison (optional)
*  **AI Service Layer**:
  * Abstraction layer for multiple AI providers
  * Provider selection logic
  * Response standardization
  * Cost tracking per provider

#### 3.1.5 Payment Integration

*  **Zoho Pay API**:
  * Payment processing
  * Webhook handling for payment events
  * Subscription management
  * Invoice generation
  * Refund processing

#### 3.1.6 Email Integration

*  **ZeptoMail API**:
  * Email delivery service for all transactional and notification emails
  * API key management and configuration
  * Email template management
  * Request/response handling
  * Error handling and retries
  * Email delivery status tracking
  * Bounce and complaint handling
  * Email analytics and reporting
  * Support for:
    * Transactional emails (verification, password reset, notifications)
    * Bulk emails (newsletters, announcements)
    * HTML and plain text formats
    * Email attachments (for report sharing)
  * Integration with Laravel Mail system
  * Queue-based email sending for better performance

### 3.2 Database Schema

#### 3.2.1 Core Tables

*  **users**: User accounts
  * id, name, email, email_verified_at, password, account_type (individual/organizational), organization_id (nullable, FK), subscription_type, subscription_expires_at, created_at, updated_at
*  **organizations**: Organizational accounts
  * id, name, domain, website, subscription_type, subscription_expires_at, member_limit, custom_branding (JSON), settings (JSON), created_at, updated_at
*  **organization_members**: Organization membership and roles
  * id, organization_id (FK), user_id (FK), role (admin/analyst/viewer), status (active/pending/inactive), invited_by (FK to users), invited_at, joined_at, created_at, updated_at
*  **analysis_jobs**: Analysis job tracking
  * id, user_id (FK, creator), organization_id (nullable, FK), status, file_path, objectives, column_descriptions (JSON), payment_id, subscription_used, access_type (private/organization/specific_members), created_at, updated_at, completed_at
*  **analysis_job_members**: Access control for analysis jobs
  * id, analysis_job_id (FK), user_id (FK), access_granted_at, created_at
*  **analysis_results**: Statistical test results
  * id, analysis_job_id, test_name, test_type, parameters (JSON), results (JSON), assumptions (JSON), created_at
*  **reports**: Generated reports
  * id, analysis_job_id, report_path, report_format, ai_interpretation, generated_at, created_at
*  **payments**: Payment transactions
  * id, user_id, amount, currency, payment_method, zoho_payment_id, status, invoice_url, created_at
*  **subscriptions**: User and organization subscriptions
  * id, user_id (nullable, FK), organization_id (nullable, FK), plan_type, subscription_type (individual/organizational), status, billing_cycle, zoho_subscription_id, started_at, expires_at, created_at, updated_at
*  **data_files**: Uploaded data files
  * id, user_id, analysis_job_id, original_filename, stored_filename, file_size, mime_type, uploaded_at

#### 3.2.2 Additional Tables

*  **notifications**: User notifications
*  **report_shares**: Shared report access
*  **organization_invitations**: Organization member invitations
  * id, organization_id (FK), email, role, token, invited_by (FK to users), expires_at, accepted_at, created_at, updated_at
*  **ai_usage_logs**: AI API usage tracking
*  **email_logs**: Email delivery tracking (ZeptoMail)
  * id, user_id, email_type, recipient_email, subject, status, zeptomail_message_id, sent_at, delivered_at, opened_at, clicked_at, bounced_at, created_at
*  **system_logs**: System activity logs

### 3.3 API Design

#### 3.3.1 Internal APIs

*  **Analysis Service API** (Python):
  * POST `/api/analyze`: Submit data for analysis
  * GET `/api/analysis/{id}/status`: Check analysis status
  * GET `/api/analysis/{id}/results`: Get analysis results
  * POST `/api/generate-graphs`: Generate visualization graphs

#### 3.3.2 External APIs

*  **Zoho Pay Webhooks**: Payment status updates
*  **AI Provider APIs**: Anthropic and Gemini endpoints
*  **ZeptoMail API**: Email delivery and status tracking

### 3.4 Security Requirements

#### 3.4.1 Data Security

*  **Encryption**:
  * Data at rest: AES-256 encryption
  * Data in transit: TLS 1.3
  * Database encryption for sensitive fields
*  **Access Control**:
  * Role-based access control (RBAC)
  * User authentication required for all operations
  * API authentication via tokens
  *  **Organizational Access Control**:
    * Organization membership verification
    * Role-based permissions (Admin, Analyst, Viewer)
    * Analysis-level access control
    * Member invitation and validation
    * Organization data isolation (members can only access their organization's data)
*  **File Security**:
  * Virus scanning for uploaded files
  * File type validation
  * Secure file storage with access controls
  * Automatic file cleanup for expired data
  *  **Organizational file access**: Files accessible only to authorized organization members

#### 3.4.2 Compliance

*  **HIPAA Considerations** (if applicable):
  * Data anonymization options
  * Audit logging
  * Access controls
  * Business Associate Agreement (BAA) with service providers
*  **GDPR Compliance**:
  * Data export functionality
  * Right to deletion
  * Privacy policy compliance
  * Cookie consent management

#### 3.4.3 Authentication Security

* Password hashing: bcrypt/argon2
* CSRF protection
* XSS prevention
* SQL injection prevention (using Eloquent ORM)
* Rate limiting on API endpoints
* Session security

### 3.5 Performance Requirements

#### 3.5.1 Response Times

* Page load time: < 2 seconds
* File upload: Progress indication, no timeout for large files
* Analysis job creation: < 5 seconds
* Analysis processing:
  * Simple analyses: < 30 seconds
  * Complex analyses: < 5 minutes
  * Very complex analyses: < 15 minutes (with progress updates)
* Report generation: < 1 minute after analysis completion

#### 3.5.2 Scalability

* Support for concurrent users: 1000+ simultaneous users
* Queue system for background processing
* Horizontal scaling capability
* Database optimization:
  * Indexing on frequently queried fields
  * Query optimization
  * Connection pooling

#### 3.5.3 Resource Management

* Memory limits for analysis jobs
* Timeout handling for long-running processes
* Automatic retry for failed jobs
* Resource cleanup for completed jobs

### 3.6 Error Handling and Logging

#### 3.6.1 Error Handling

* Graceful error messages for users
* Detailed error logging for developers
* Error categorization:
  * User errors (validation, invalid data)
  * System errors (server, database)
  * External service errors (AI, payment)
* Error recovery mechanisms

#### 3.6.2 Logging

* Application logs: Laravel logging system
* Error tracking: Sentry or similar
* Audit logs: User actions, data access
* Performance monitoring: Application performance monitoring (APM) tools

### 3.7 Testing Requirements

#### 3.7.1 Test Coverage

* Unit tests: > 80% code coverage
* Integration tests: Critical workflows
* End-to-end tests: User journeys
* Statistical accuracy tests: Validate test results against known datasets

#### 3.7.2 Test Types

* Unit tests (PHPUnit for Laravel, pytest for Python)
* Feature tests (Laravel)
* API tests
* Browser tests (Laravel Dusk or similar)

### 3.8 Deployment and DevOps

#### 3.8.1 Deployment

* Environment separation: Development, Staging, Production
* CI/CD pipeline:
  * Automated testing
  * Code quality checks
  * Automated deployment
* Database migrations: Version-controlled migrations
* Zero-downtime deployment strategy

#### 3.8.2 Monitoring

* Application monitoring: Uptime, performance metrics
* Error monitoring: Real-time error tracking
* Resource monitoring: CPU, memory, disk usage
* User analytics: Usage patterns, feature adoption

### 3.9 Documentation Requirements

#### 3.9.1 Technical Documentation

* API documentation
* Database schema documentation
* Deployment guides
* Development setup guides

#### 3.9.2 User Documentation

* User guide/manual
* FAQ section
* Video tutorials (optional)
* Help center with search functionality

---

## 4\. Non-Functional Requirements

### 4.1 Usability

* Intuitive user interface
* Responsive design (mobile, tablet, desktop)
* Accessibility: WCAG 2.1 AA compliance
* Multi-language support (future enhancement)

### 4.2 Reliability

* System uptime: 99.9%
* Data backup: Daily automated backups
* Disaster recovery plan
* Redundancy for critical services

### 4.3 Maintainability

* Clean, documented code
* Modular architecture
* Version control (Git)
* Code review process

### 4.4 Extensibility

* Plugin architecture for additional statistical tests
* API for third-party integrations
* Custom report templates
* White-label options (for enterprise)

---

## 5\. Future Enhancements (Out of Scope for MVP)

* Real-time collaboration on analyses
* Advanced machine learning models
* Custom statistical test creation
* Mobile applications (iOS/Android)
* Integration with electronic health records (EHR)
* Multi-language support
* Advanced data visualization options
* Export to research paper formats (LaTeX)
* Citation generation for statistical methods used

---

## 6\. Branding Guidelines

### 6.1 Colors

*  **Primary Blue**: #0C2E8A (rgb(12,46,138))
*  **Primary Green**: #50D8AF (rgb(80,216,175))
* Logo and branding assets are available in `public/images/`

### 6.2 Brand Application

* All user-facing interfaces must use DoctorStats branding
* Consistent color scheme throughout the application
* Logo placement on all pages (header/footer)

---

## 7\. Success Criteria

### 7.1 MVP Success Metrics

* Users can successfully upload data and receive analysis reports
* 95% of analysis jobs complete successfully
* Average report generation time < 5 minutes
* User satisfaction score > 4.0/5.0

### 7.2 Business Metrics

* User registration and retention rates
* Analysis job completion rate
* Payment success rate
* Subscription conversion rate

---

**Document Version**: 1.0
**Last Updated**: 30 Nov 2025 **Status**: Approved by Mandar Sahasrabuddhe