import * as DoctorStats from './doctorstats';

const DATA_TYPES = ['text', 'numerical', 'categorical', 'date'];
const VARIABLE_ROLES = ['identifier', 'independent', 'dependent', 'control', 'excluded'];

export function registerPages(Alpine) {
    Alpine.data('appShell', () => ({
        profile: null,
        orgName: 'DoctorStats',
        menuOpen: false,
        mobileOpen: false,
        async init() {
            try {
                const response = await DoctorStats.fetchProfile();
                this.profile = response.data.data ?? response.data;
                const org = this.profile.organization_memberships?.[0]?.organization;
                this.orgName = org?.name ?? (this.profile.account_type === 'organizational' ? 'Organization' : 'Personal workspace');
            } catch {
                this.profile = null;
            }
        },
        async logout() {
            await DoctorStats.logout();
            window.location.href = '/login';
        },
    }));

    Alpine.data('loginForm', () => ({
        email: '',
        password: '',
        error: '',
        loading: false,
        async submit() {
            this.error = '';
            this.loading = true;
            try {
                await DoctorStats.login(this.email, this.password);
                window.location.href = '/dashboard';
            } catch (error) {
                this.error = DoctorStats.flattenErrors(error);
            } finally {
                this.loading = false;
            }
        },
        async demoLogin() {
            this.email = DoctorStats.DEMO_EMAIL;
            this.password = DoctorStats.DEMO_PASSWORD;
            this.error = '';
            this.loading = true;
            try {
                await DoctorStats.login(DoctorStats.DEMO_EMAIL, DoctorStats.DEMO_PASSWORD);
                window.location.href = '/dashboard';
            } catch (error) {
                this.error = DoctorStats.flattenErrors(error);
            } finally {
                this.loading = false;
            }
        },
    }));

    Alpine.data('registerForm', () => ({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
        account_type: 'individual',
        organization_name: '',
        accepted_terms: false,
        error: '',
        success: '',
        loading: false,
        async submit() {
            this.error = '';
            this.loading = true;
            try {
                await DoctorStats.register({
                    name: this.name,
                    email: this.email,
                    password: this.password,
                    password_confirmation: this.password_confirmation,
                    account_type: this.account_type,
                    organization_name: this.account_type === 'organizational' ? this.organization_name : undefined,
                    accepted_terms: this.accepted_terms,
                });
                window.location.href = '/login?registered=1';
            } catch (error) {
                this.error = DoctorStats.flattenErrors(error);
            } finally {
                this.loading = false;
            }
        },
    }));

    Alpine.data('forgotPasswordForm', () => ({
        email: '',
        message: '',
        error: '',
        loading: false,
        async submit() {
            this.error = '';
            this.message = '';
            this.loading = true;
            try {
                const response = await DoctorStats.forgotPassword(this.email);
                this.message = response.data.message;
            } catch (error) {
                this.error = DoctorStats.flattenErrors(error);
            } finally {
                this.loading = false;
            }
        },
    }));

    Alpine.data('resetPasswordForm', (token, email) => ({
        token,
        email: email ?? '',
        password: '',
        password_confirmation: '',
        error: '',
        loading: false,
        async submit() {
            this.error = '';
            this.loading = true;
            try {
                await DoctorStats.resetPassword({
                    token: this.token,
                    email: this.email,
                    password: this.password,
                    password_confirmation: this.password_confirmation,
                });
                window.location.href = '/login?reset=1';
            } catch (error) {
                this.error = DoctorStats.flattenErrors(error);
            } finally {
                this.loading = false;
            }
        },
    }));

    Alpine.data('dashboardPage', () => ({
        loading: true,
        error: '',
        profile: null,
        dashboard: null,
        async init() {
            await this.load();
        },
        async load() {
            this.loading = true;
            this.error = '';
            try {
                const [profileResponse, dashboardResponse] = await Promise.all([
                    DoctorStats.fetchProfile(),
                    DoctorStats.fetchDashboard(),
                ]);
                this.profile = profileResponse.data.data ?? profileResponse.data;
                this.dashboard = dashboardResponse.data.data;
            } catch {
                this.error = 'Unable to load dashboard data.';
            } finally {
                this.loading = false;
            }
        },
        statusClass: DoctorStats.statusBadgeClass,
        formatDate: DoctorStats.formatRelativeDate,
        firstName() {
            return (this.profile?.name ?? 'there').split(' ')[0];
        },
    }));

    Alpine.data('analysesListPage', () => ({
        loading: true,
        error: '',
        jobs: [],
        filter: 'all',
        async init() {
            await this.load();
        },
        async load() {
            this.loading = true;
            this.error = '';
            try {
                const status = this.filter === 'all' ? null : this.filter;
                const response = await DoctorStats.fetchAnalysisJobs(status);
                this.jobs = response.data.data ?? [];
            } catch {
                this.error = 'Unable to load analyses.';
            } finally {
                this.loading = false;
            }
        },
        async setFilter(filter) {
            this.filter = filter;
            await this.load();
        },
        statusClass: DoctorStats.statusBadgeClass,
        formatDate: DoctorStats.formatRelativeDate,
    }));

    Alpine.data('analysisJobPage', (jobId) => ({
        jobId,
        loading: true,
        polling: false,
        error: '',
        job: null,
        reportId: null,
        pollTimer: null,
        async init() {
            await this.load();
            if (this.job && !['completed', 'failed'].includes(this.job.status)) {
                this.startPolling();
            }
        },
        async load() {
            this.loading = true;
            this.error = '';
            try {
                const response = await DoctorStats.fetchAnalysisJob(this.jobId);
                this.job = response.data.data ?? response.data;
                if (this.job.status === 'completed') {
                    await this.findReport();
                }
            } catch {
                this.error = 'Unable to load analysis job.';
            } finally {
                this.loading = false;
            }
        },
        async findReport() {
            try {
                const response = await DoctorStats.fetchReports();
                const reports = response.data.data ?? [];
                const match = reports.find((report) => report.job_id === this.jobId);
                this.reportId = match?.id ?? null;
            } catch {
                this.reportId = null;
            }
        },
        startPolling() {
            this.polling = true;
            const poll = async () => {
                try {
                    const response = await DoctorStats.fetchAnalysisStatus(this.jobId);
                    const status = response.data.data.status;
                    this.job = { ...this.job, status: status, progress: response.data.data.progress };
                    if (status === 'completed') {
                        this.polling = false;
                        await this.findReport();
                        return;
                    }
                    if (status === 'failed') {
                        this.polling = false;
                        return;
                    }
                    this.pollTimer = setTimeout(poll, 3000);
                } catch {
                    this.polling = false;
                    this.error = 'Unable to poll job status.';
                }
            };
            poll();
        },
        statusClass: DoctorStats.statusBadgeClass,
    }));

    Alpine.data('analysisWorkflowPage', () => ({
        step: 1,
        uploadState: 'empty',
        uploadProgress: 0,
        file: null,
        fileName: '',
        dataFileId: null,
        objectives: '',
        columns: [],
        selectedSheet: '',
        sheets: [],
        accessScope: 'private',
        isOrgAccount: false,
        error: '',
        submitting: false,
        dataTypes: DATA_TYPES,
        variableRoles: VARIABLE_ROLES,
        objectiveTemplates: [
            'Compare treatment outcomes between groups',
            'Identify risk factors for disease progression',
            'Assess correlation between biomarkers',
        ],
        accessOptions: [
            { value: 'all_members', label: 'All members', description: 'Everyone in your organisation can view this analysis' },
            { value: 'specific_members', label: 'Specific members', description: 'Only selected team members can access' },
            { value: 'private', label: 'Private', description: 'Only you can view this analysis' },
        ],
        async init() {
            try {
                const response = await DoctorStats.fetchProfile();
                const profile = response.data.data ?? response.data;
                this.isOrgAccount = profile.account_type === 'organizational';
                if (this.isOrgAccount) {
                    this.accessScope = 'all_members';
                }
            } catch {
                this.isOrgAccount = false;
            }
        },
        get totalSteps() {
            return this.isOrgAccount ? 5 : 4;
        },
        get displayStep() {
            return this.isOrgAccount ? this.step : (this.step >= 4 ? this.step + 1 : this.step);
        },
        get reviewStep() {
            return this.isOrgAccount ? 5 : 4;
        },
        get objectivesValid() {
            return this.objectives.length >= 50;
        },
        canProceed() {
            if (this.step === 1) return this.uploadState === 'ready';
            if (this.step === 2) return this.objectivesValid;
            if (this.step === 3) return this.columns.every((c) => c.data_type && c.variable_type);
            return true;
        },
        onFileSelect(event) {
            const selected = event.target.files?.[0];
            if (!selected) return;
            this.uploadFile(selected);
        },
        onDrop(event) {
            event.preventDefault();
            const selected = event.dataTransfer.files?.[0];
            if (selected) this.uploadFile(selected);
        },
        async uploadFile(selected) {
            this.file = selected;
            this.fileName = selected.name;
            this.uploadState = 'uploading';
            this.uploadProgress = 10;
            this.error = '';
            try {
                const response = await DoctorStats.uploadDataFile(selected);
                this.dataFileId = response.data.data_file.id;
                this.uploadProgress = 100;
                this.uploadState = 'scanning';
                await this.loadPreview();
            } catch (error) {
                this.uploadState = 'error';
                this.error = DoctorStats.flattenErrors(error);
            }
        },
        async loadPreview() {
            try {
                const sheetsResponse = await DoctorStats.fetchDataFileSheets(this.dataFileId);
                this.sheets = sheetsResponse.data.data ?? [];
                this.selectedSheet = this.sheets[0] ?? '';
                const previewResponse = await DoctorStats.fetchDataFilePreview(this.dataFileId, this.selectedSheet || null);
                const preview = previewResponse.data.data;
                const warningsByColumn = {};
                (preview.quality_warnings ?? []).forEach((w) => {
                    warningsByColumn[w.column] = w;
                });
                this.columns = (preview.headers ?? []).map((header, index) => ({
                    column_name: header,
                    column_index: index,
                    data_type: this.guessDataType(header, preview.rows, index),
                    description: '',
                    unit_of_measurement: '',
                    variable_type: index === 0 ? 'identifier' : 'independent',
                    missing_percent: warningsByColumn[header]?.type === 'missing' ? 5 : undefined,
                }));
                this.uploadState = 'ready';
            } catch (error) {
                this.uploadState = 'error';
                this.error = DoctorStats.flattenErrors(error);
            }
        },
        guessDataType(header, rows, index) {
            const sample = (rows ?? []).slice(0, 5).map((row) => row[index]).filter((v) => v !== null && v !== '');
            if (sample.length === 0) return 'text';
            if (sample.every((v) => !Number.isNaN(Number(v)))) return 'numerical';
            return 'categorical';
        },
        async changeSheet() {
            if (!this.dataFileId || !this.selectedSheet) return;
            await this.loadPreview();
        },
        updateColumn(index, field, value) {
            this.columns = this.columns.map((col, i) => (i === index ? { ...col, [field]: value } : col));
        },
        applyTemplate(text) {
            this.objectives = text;
        },
        nextStep() {
            if (!this.canProceed()) return;
            if (this.step === 3 && !this.isOrgAccount) {
                this.step = 4;
                return;
            }
            if (this.step < this.totalSteps) this.step += 1;
        },
        prevStep() {
            if (this.step === 4 && !this.isOrgAccount) {
                this.step = 3;
                return;
            }
            if (this.step > 1) this.step -= 1;
        },
        async submit() {
            this.error = '';
            this.submitting = true;
            try {
                const response = await DoctorStats.createAnalysisJob({
                    data_file_id: this.dataFileId,
                    objectives: this.objectives,
                    access_scope: this.accessScope,
                    payment_method: 'pay_per_job',
                    columns: this.columns.map((col) => ({
                        column_name: col.column_name,
                        column_index: col.column_index,
                        data_type: col.data_type,
                        description: col.description || null,
                        unit_of_measurement: col.unit_of_measurement || null,
                        variable_type: col.variable_type,
                    })),
                });
                const job = response.data.job;
                window.location.href = `/analyses/${job.job_id}`;
            } catch (error) {
                this.error = DoctorStats.flattenErrors(error);
            } finally {
                this.submitting = false;
            }
        },
    }));

    Alpine.data('reportsListPage', () => ({
        loading: true,
        error: '',
        reports: [],
        search: '',
        filter: 'all',
        async init() {
            await this.load();
        },
        async load() {
            this.loading = true;
            this.error = '';
            try {
                const response = await DoctorStats.fetchReports();
                this.reports = response.data.data ?? [];
            } catch {
                this.error = 'Unable to load reports.';
            } finally {
                this.loading = false;
            }
        },
        filteredReports() {
            return this.reports.filter((report) => {
                const matchesFilter = this.filter === 'all' || report.status === this.filter;
                const query = this.search.toLowerCase();
                const matchesSearch = !query
                    || (report.title ?? '').toLowerCase().includes(query)
                    || (report.job_id ?? '').toLowerCase().includes(query);
                return matchesFilter && matchesSearch;
            });
        },
        statusClass: DoctorStats.statusBadgeClass,
        formatDate(iso) {
            return iso ? new Date(iso).toLocaleDateString() : '';
        },
    }));

    Alpine.data('profilePage', () => ({
        loading: true,
        saving: false,
        passwordSaving: false,
        error: '',
        message: '',
        passwordError: '',
        passwordMessage: '',
        profile: null,
        name: '',
        email: '',
        currentPassword: '',
        newPassword: '',
        newPasswordConfirmation: '',
        async init() {
            await this.load();
        },
        async load() {
            this.loading = true;
            try {
                const response = await DoctorStats.fetchProfile();
                this.profile = response.data.data ?? response.data;
                this.name = this.profile.name;
                this.email = this.profile.email;
            } catch {
                this.error = 'Unable to load profile.';
            } finally {
                this.loading = false;
            }
        },
        async saveProfile() {
            this.saving = true;
            this.error = '';
            this.message = '';
            try {
                const response = await DoctorStats.updateProfile({ name: this.name });
                this.profile = response.data.data ?? response.data;
                this.message = 'Profile updated successfully.';
            } catch (error) {
                this.error = DoctorStats.flattenErrors(error);
            } finally {
                this.saving = false;
            }
        },
        async savePassword() {
            this.passwordSaving = true;
            this.passwordError = '';
            this.passwordMessage = '';
            try {
                const response = await DoctorStats.changePassword({
                    current_password: this.currentPassword,
                    password: this.newPassword,
                    password_confirmation: this.newPasswordConfirmation,
                });
                this.passwordMessage = response.data.message;
                this.currentPassword = '';
                this.newPassword = '';
                this.newPasswordConfirmation = '';
            } catch (error) {
                this.passwordError = DoctorStats.flattenErrors(error);
            } finally {
                this.passwordSaving = false;
            }
        },
        async deactivate() {
            if (!confirm('Deactivate your account? This cannot be undone.')) return;
            await DoctorStats.deactivateAccount();
            window.location.href = '/';
        },
    }));

    Alpine.data('organizationPage', () => ({
        loading: true,
        error: '',
        profile: null,
        organization: null,
        members: [],
        inviteEmail: '',
        inviteRole: 'analyst',
        inviting: false,
        inviteMessage: '',
        inviteError: '',
        async init() {
            await this.load();
        },
        async load() {
            this.loading = true;
            this.error = '';
            try {
                const profileResponse = await DoctorStats.fetchProfile();
                this.profile = profileResponse.data.data ?? profileResponse.data;
                const membership = this.profile.organization_memberships?.[0];
                if (!membership?.organization_id) {
                    this.loading = false;
                    return;
                }
                const [orgResponse, membersResponse] = await Promise.all([
                    DoctorStats.fetchOrganization(membership.organization_id),
                    DoctorStats.fetchOrganizationMembers(membership.organization_id),
                ]);
                this.organization = orgResponse.data.data ?? orgResponse.data;
                this.members = membersResponse.data.data ?? [];
            } catch {
                this.error = 'Unable to load organization.';
            } finally {
                this.loading = false;
            }
        },
        async invite() {
            if (!this.organization) return;
            this.inviting = true;
            this.inviteError = '';
            this.inviteMessage = '';
            try {
                const response = await DoctorStats.inviteOrganizationMember(this.organization.id, {
                    email: this.inviteEmail,
                    role: this.inviteRole,
                });
                this.inviteMessage = response.data.message ?? 'Invitation sent.';
                this.inviteEmail = '';
                await this.load();
            } catch (error) {
                this.inviteError = DoctorStats.flattenErrors(error);
            } finally {
                this.inviting = false;
            }
        },
        isAdmin() {
            const membership = this.profile?.organization_memberships?.[0];
            return membership?.role === 'admin';
        },
    }));

    Alpine.data('invitationAcceptPage', (token) => ({
        token,
        loading: false,
        error: '',
        message: '',
        async accept() {
            this.loading = true;
            this.error = '';
            try {
                const response = await DoctorStats.acceptInvitation(this.token);
                this.message = response.data.message;
                setTimeout(() => { window.location.href = '/dashboard'; }, 1500);
            } catch (error) {
                this.error = DoctorStats.flattenErrors(error);
            } finally {
                this.loading = false;
            }
        },
    }));

    Alpine.data('demoAnalysisPage', () => ({
        loading: false,
        polling: false,
        error: '',
        job: null,
        reportId: null,
        pollTimer: null,
        demoColumns: [
            { column_name: 'patient_id', column_index: 0, data_type: 'text', description: 'Anonymous patient identifier', variable_type: 'identifier' },
            { column_name: 'age', column_index: 1, data_type: 'numerical', description: 'Patient age in years', unit_of_measurement: 'years', variable_type: 'independent' },
            { column_name: 'sex', column_index: 2, data_type: 'categorical', description: 'Patient sex', variable_type: 'control' },
            { column_name: 'treatment_group', column_index: 3, data_type: 'categorical', description: 'Randomized treatment arm', variable_type: 'independent' },
            { column_name: 'baseline_systolic_bp', column_index: 4, data_type: 'numerical', description: 'Baseline systolic blood pressure', unit_of_measurement: 'mmHg', variable_type: 'control' },
            { column_name: 'post_treatment_systolic_bp', column_index: 5, data_type: 'numerical', description: 'Post-treatment systolic blood pressure', unit_of_measurement: 'mmHg', variable_type: 'dependent' },
            { column_name: 'cholesterol_mg_dl', column_index: 6, data_type: 'numerical', description: 'Total cholesterol', unit_of_measurement: 'mg/dL', variable_type: 'independent' },
        ],
        statusClass: DoctorStats.statusBadgeClass,
        async runDemo() {
            this.error = '';
            this.job = null;
            this.reportId = null;
            this.loading = true;
            try {
                await DoctorStats.ensureCsrf();
                const csvResponse = await fetch('/demo/clinical-trial-demo.csv');
                const csvBlob = await csvResponse.blob();
                const formData = new FormData();
                formData.append('file', csvBlob, 'clinical-trial-demo.csv');
                const uploadResponse = await window.axios.post('/api/v1/data-files', formData, {
                    headers: { 'Content-Type': 'multipart/form-data' },
                });
                const dataFileId = uploadResponse.data.data_file.id;
                const scanStatus = uploadResponse.data.data_file.virus_scan_status;
                if (scanStatus !== 'clean') {
                    await DoctorStats.waitForDataFileReady(dataFileId);
                }
                const jobResponse = await DoctorStats.createAnalysisJob({
                    data_file_id: dataFileId,
                    objectives: 'Compare post-treatment systolic blood pressure between Treatment and Control arms in this hypertension trial, and explore associations with age and cholesterol.',
                    access_scope: 'private',
                    payment_method: 'pay_per_job',
                    columns: this.demoColumns,
                });
                this.job = jobResponse.data.job;
                this.startPolling(this.job.job_id);
            } catch (error) {
                this.error = DoctorStats.flattenErrors(error);
            } finally {
                this.loading = false;
            }
        },
        startPolling(jobId) {
            this.polling = true;
            const poll = async () => {
                try {
                    const statusResponse = await DoctorStats.fetchAnalysisStatus(jobId);
                    const status = statusResponse.data.data.status;
                    this.job = { ...this.job, status };
                    if (status === 'completed') {
                        const reportsResponse = await DoctorStats.fetchReports();
                        const reports = reportsResponse.data.data || [];
                        const match = reports.find((report) => report.job_id === jobId);
                        if (match?.id) {
                            this.reportId = match.id;
                            this.polling = false;
                            return;
                        }
                        this.pollTimer = setTimeout(poll, 3000);
                        return;
                    }
                    if (status === 'failed') {
                        this.polling = false;
                        return;
                    }
                    this.pollTimer = setTimeout(poll, 3000);
                } catch {
                    this.polling = false;
                    this.error = 'Unable to poll job status.';
                }
            };
            poll();
        },
    }));
}
