const api = window.axios;

export const DEMO_EMAIL = 'test@example.com';
export const DEMO_PASSWORD = 'password';

export async function ensureCsrf() {
    await api.get('/sanctum/csrf-cookie');
}

function flattenErrors(error) {
    const errors = error.response?.data?.errors ?? {};

    return Object.values(errors).flat().join(' ')
        || error.response?.data?.message
        || 'Something went wrong. Please try again.';
}

export async function login(email, password, remember = false) {
    await ensureCsrf();

    return api.post('/api/v1/auth/login', { email, password, remember });
}

export async function register(payload) {
    await ensureCsrf();

    return api.post('/api/v1/auth/register', payload);
}

export async function logout() {
    await ensureCsrf();

    return api.post('/api/v1/auth/logout');
}

export async function forgotPassword(email) {
    await ensureCsrf();

    return api.post('/api/v1/auth/forgot-password', { email });
}

export async function resetPassword(payload) {
    await ensureCsrf();

    return api.post('/api/v1/auth/reset-password', payload);
}

export async function fetchProfile() {
    return api.get('/api/v1/me');
}

export async function updateProfile(payload) {
    return api.patch('/api/v1/me', payload);
}

export async function changePassword(payload) {
    return api.patch('/api/v1/me/password', payload);
}

export async function deactivateAccount() {
    return api.delete('/api/v1/me');
}

export async function fetchDashboard() {
    return api.get('/api/v1/dashboard');
}

export async function uploadDataFile(file) {
    const formData = new FormData();
    formData.append('file', file);

    return api.post('/api/v1/data-files', formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
    });
}

export async function fetchDataFile(dataFileId) {
    return api.get(`/api/v1/data-files/${dataFileId}`);
}

export async function waitForDataFileReady(dataFileId, maxAttempts = 30, intervalMs = 1000) {
    for (let attempt = 0; attempt < maxAttempts; attempt += 1) {
        const response = await fetchDataFile(dataFileId);
        const file = response.data.data ?? response.data;
        const status = file.virus_scan_status;

        if (status === 'clean') {
            return file;
        }

        if (status === 'rejected') {
            throw new Error('The uploaded file failed validation.');
        }

        await new Promise((resolve) => setTimeout(resolve, intervalMs));
    }

    throw new Error('File validation is taking too long. Ensure the queue worker is running.');
}

export async function fetchDataFilePreview(dataFileId, sheetName = null) {
    const params = sheetName ? { sheet_name: sheetName } : {};

    return api.get(`/api/v1/data-files/${dataFileId}/preview`, { params });
}

export async function fetchDataFileSheets(dataFileId) {
    return api.get(`/api/v1/data-files/${dataFileId}/sheets`);
}

export async function fetchAnalysisJobs(status = null) {
    const params = status ? { status } : {};

    return api.get('/api/v1/analysis-jobs', { params });
}

export async function fetchAnalysisJob(jobId) {
    return api.get(`/api/v1/analysis-jobs/${jobId}`);
}

export async function fetchAnalysisStatus(jobId) {
    return api.get(`/api/v1/analysis-jobs/${jobId}/status`);
}

export async function createAnalysisJob(payload) {
    return api.post('/api/v1/analysis-jobs', payload);
}

export async function fetchReports() {
    return api.get('/api/v1/reports');
}

export async function fetchReport(reportId) {
    return api.get(`/api/v1/reports/${reportId}`);
}

export async function fetchOrganization(organizationId) {
    return api.get(`/api/v1/organizations/${organizationId}`);
}

export async function fetchOrganizationMembers(organizationId) {
    return api.get(`/api/v1/organizations/${organizationId}/members`);
}

export async function fetchOrganizationInvitations(organizationId) {
    return api.get(`/api/v1/organizations/${organizationId}/invitations`);
}

export async function inviteOrganizationMember(organizationId, payload) {
    return api.post(`/api/v1/organizations/${organizationId}/invitations`, payload);
}

export async function acceptInvitation(token) {
    return api.post(`/api/v1/invitations/${token}/accept`);
}

export async function fetchSubscriptionCurrent() {
    return api.get('/api/v1/subscriptions/current');
}

export function statusBadgeClass(status) {
    return {
        completed: 'badge-completed',
        processing: 'badge-processing',
        pending: 'badge-pending',
        failed: 'badge-failed',
    }[status] ?? 'badge-pending';
}

export function formatRelativeDate(iso) {
    if (!iso) return '';

    const date = new Date(iso);
    const diff = Date.now() - date.getTime();
    const minutes = Math.floor(diff / 60000);

    if (minutes < 1) return 'Just now';
    if (minutes < 60) return `${minutes}m ago`;

    const hours = Math.floor(minutes / 60);

    if (hours < 24) return `${hours}h ago`;

    const days = Math.floor(hours / 24);

    if (days < 7) return `${days}d ago`;

    return date.toLocaleDateString();
}

export { flattenErrors };
