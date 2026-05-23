let feedbackData = Array.isArray(window.feedbackData) ? window.feedbackData : [];
let surveyData = Array.isArray(window.surveyData) ? window.surveyData : [];
let activityLog = [];
let currentAdminUsername = typeof window.currentAdminUsername === 'string' ? window.currentAdminUsername : '';

const PAGE_SIZE = 5;
let tablePage = 1;
let adminPage = 1;
let surveyPage = 1;
let activityPage = 1;

let slideIndex = 0;
let slideTimer = null;
const SLIDE_INTERVAL = 6000;

function toggleDarkMode() {
    const body = document.body;
    body.classList.toggle('dark-mode');
    const isDark = body.classList.contains('dark-mode');
    updateDarkModeUI(isDark);
    try {
        localStorage.setItem('darkMode', isDark ? 'enabled' : 'disabled');
    } catch (e) { }
}

function updateDarkModeUI(isDark) {
    const icon = document.getElementById('darkModeIcon');
    const label = document.getElementById('darkModeLabel');
    if (!icon || !label) return;
    if (isDark) {
        icon.textContent = 'light_mode';
        label.textContent = 'Light Mode';
    } else {
        icon.textContent = 'dark_mode';
        label.textContent = 'Dark Mode';
    }
}

function applyStoredDarkMode() {
    try {
        const stored = localStorage.getItem('darkMode');
        if (stored === 'enabled') {
            document.body.classList.add('dark-mode');
            updateDarkModeUI(true);
        } else {
            updateDarkModeUI(false);
        }
    } catch (e) { }
}

function toggleAdminPassword() {
    togglePasswordVisibility('adminPassword', 'adminPasswordToggleIcon', 'adminPasswordToggle');
}

function togglePasswordVisibility(inputId, iconId, btnId) {
    const passwordInput = document.getElementById(inputId);
    const icon = document.getElementById(iconId);
    const btn = document.getElementById(btnId);
    if (!passwordInput) return;
    const showing = passwordInput.type === 'password';
    passwordInput.type = showing ? 'text' : 'password';
    if (icon) icon.textContent = showing ? 'visibility_off' : 'visibility';
    if (btn) btn.setAttribute('aria-label', showing ? 'Hide password' : 'Show password');
}

// Strong-password policy: 12+ chars with upper, lower, number, special.
// Returns null when valid, otherwise a human-readable message listing what's missing.
function validateStrongPassword(pwd) {
    const missing = [];
    if (pwd.length < 12) missing.push('12+ characters');
    if (!/[A-Z]/.test(pwd)) missing.push('an uppercase letter');
    if (!/[a-z]/.test(pwd)) missing.push('a lowercase letter');
    if (!/[0-9]/.test(pwd)) missing.push('a number');
    if (!/[^A-Za-z0-9]/.test(pwd)) missing.push('a special character');
    if (missing.length === 0) return null;
    return 'Password must include ' + missing.join(', ') + '.';
}

function closeSurvey() {
    document.getElementById('floatingSurvey').style.display = 'none';
    const toggleBtn = document.getElementById('surveyToggleBtn');
    if (toggleBtn && document.getElementById('windowForm').classList.contains('active')) {
        toggleBtn.classList.add('show');
    }
}

function reopenSurvey() {
    document.getElementById('floatingSurvey').style.display = 'block';
    const toggleBtn = document.getElementById('surveyToggleBtn');
    if (toggleBtn) toggleBtn.classList.remove('show');
}

function switchView(view) {
    ['windowPlanner', 'windowForm', 'windowTable', 'windowAdminLogin', 'windowAdminDashboard']
        .forEach(id => document.getElementById(id).classList.remove('active'));

    const survey = document.getElementById('floatingSurvey');
    const surveyToggleBtn = document.getElementById('surveyToggleBtn');
    const portalTitle = document.getElementById('portalTitle');
    const topAdminBtn = document.getElementById('topAdminBtn');
    if (topAdminBtn) topAdminBtn.style.display = (view === 'planner') ? '' : 'none';
    if (surveyToggleBtn) surveyToggleBtn.classList.remove('show');

    if (view === 'planner') {
        document.getElementById('windowPlanner').classList.add('active');
        if (portalTitle) portalTitle.innerHTML = '<span class="material-icons">castle</span>Intramuros Experience Portal';
        survey.style.display = 'none';
        startSlideshow();
    } else {
        stopSlideshow();
    }

    if (view === 'form') {
        document.getElementById('windowForm').classList.add('active');
        if (portalTitle) portalTitle.innerHTML = '<span class="material-icons">rate_review</span>Leave Feedback';
        survey.style.display = 'block';
    } else if (view === 'table') {
        document.getElementById('windowTable').classList.add('active');
        if (portalTitle) portalTitle.innerHTML = '<span class="material-icons">reviews</span>Visitor Reviews';
        survey.style.display = 'none';
        tablePage = 1;
        renderTable();
    } else if (view === 'admin-login') {
        document.getElementById('windowAdminLogin').classList.add('active');
        if (portalTitle) portalTitle.innerHTML = '<span class="material-icons">lock</span>Admin Login';
        survey.style.display = 'none';
    } else if (view === 'admin-dashboard') {
        document.getElementById('windowAdminDashboard').classList.add('active');
        if (portalTitle) portalTitle.innerHTML = '<span class="material-icons">dashboard</span>Admin Dashboard';
        survey.style.display = 'none';
        adminPage = 1;
        surveyPage = 1;
        activityPage = 1;
        document.getElementById('adminBarUsername').textContent = currentAdminUsername || 'admin';
        renderAdminTable();
        renderSurveyTable();
        loadActivityLog();
    }
}

function handleAdminLogin(event) {
    event.preventDefault();
    const user = document.getElementById('adminUsername').value;
    const pass = document.getElementById('adminPassword').value;

    const formData = new FormData();
    formData.append('username', user);
    formData.append('password', pass);
    formData.append('admin_login', '1');

    fetch('admin_login.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                currentAdminUsername = data.username || user;
                showToast('Logged in successfully!', 'success');
                document.getElementById('adminUsername').value = '';
                document.getElementById('adminPassword').value = '';
                switchView('admin-dashboard');
            } else {
                showToast(data.message || 'Invalid username or password.', 'error');
            }
        })
        .catch(err => {
            showToast('Login failed: ' + err, 'error');
        });
}

function submitFeedback(event) {
    event.preventDefault();

    const nationality = document.getElementById('nationality').value;
    const visitDate = document.getElementById('visitDate').value;
    const cleanliness = document.querySelector('input[name="cleanliness"]:checked');
    const restroom = document.querySelector('input[name="restroom"]:checked');
    const guides = document.querySelector('input[name="guides"]:checked');
    const accommodation = document.querySelector('input[name="accommodation"]:checked');
    const overall = document.querySelector('input[name="overall"]:checked');

    if (!nationality) {
        showToast('Please select your nationality.', 'error');
        document.getElementById('nationality').focus();
        return;
    }
    if (!visitDate) {
        showToast('Please choose your visit date.', 'error');
        document.getElementById('visitDate').focus();
        return;
    }
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const selectedDate = new Date(visitDate + 'T00:00:00');
    if (selectedDate < today) {
        showToast('Visit date cannot be in the past.', 'error');
        document.getElementById('visitDate').focus();
        return;
    }
    if (selectedDate > today) {
        showToast('Visit date cannot be in the future.', 'error');
        document.getElementById('visitDate').focus();
        return;
    }
    if (!cleanliness) {
        showToast('Please rate the cleanliness.', 'error');
        return;
    }
    if (!restroom) {
        showToast('Please rate the restroom.', 'error');
        return;
    }
    if (!guides) {
        showToast('Please rate the tour guides.', 'error');
        return;
    }
    if (!accommodation) {
        showToast('Please rate the accommodation/hotel.', 'error');
        return;
    }
    if (!overall) {
        showToast('Please give an overall rating.', 'error');
        return;
    }

    const comments = document.getElementById('comments').value;

    const formData = new FormData();
    formData.append('nationality', nationality);
    formData.append('visitDate', visitDate);
    formData.append('cleanliness', cleanliness.value);
    formData.append('restroom', restroom.value);
    formData.append('guides', guides.value);
    formData.append('accommodation', accommodation.value);
    formData.append('overall', overall.value);
    formData.append('comments', comments);

    fetch('Index.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.text())
        .then(data => {
            if (data.trim() === 'success') {
                showToast('Feedback submitted successfully!', 'success');
                document.getElementById('feedbackForm').reset();
                setTimeout(() => window.location.reload(), 1500);
            } else {
                showToast('Error submitting feedback: ' + data, 'error');
            }
        })
        .catch(error => {
            showToast('Error: ' + error, 'error');
        });
}

function submitSurvey(event) {
    event.preventDefault();

    const helpfulRadio = document.querySelector('input[name="helpful"]:checked');
    if (!helpfulRadio) {
        showToast('Please select whether you found the form helpful.', 'error');
        return;
    }

    const helpful = helpfulRadio.value;
    const surveySuggestions = document.getElementById('surveySuggestions').value;

    const formData = new FormData();
    formData.append('helpful', helpful);
    formData.append('surveySuggestions', surveySuggestions);

    fetch('save_survey.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.text())
        .then(data => {
            if (data.trim() === 'success') {
                showToast('Thank you for your response!', 'success');
                document.getElementById('surveyForm').reset();
                document.getElementById('floatingSurvey').style.display = 'none';
            } else {
                showToast('Error submitting survey: ' + data, 'error');
            }
        })
        .catch(err => {
            showToast('Error: ' + err, 'error');
        });
}

function showToast(message, type = 'info', duration = 4000) {
    const container = document.getElementById('toastContainer');
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;

    const iconName = type === 'success' ? 'check_circle'
        : type === 'error' ? 'error'
            : 'info';

    toast.innerHTML = `
                <span class="material-icons">${iconName}</span>
                <div class="toast-body"></div>
                <button class="toast-close" aria-label="Close">
                    <span class="material-icons">close</span>
                </button>
            `;
    toast.querySelector('.toast-body').textContent = message;

    const dismiss = () => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 350);
    };
    toast.querySelector('.toast-close').addEventListener('click', dismiss);

    container.appendChild(toast);
    requestAnimationFrame(() => toast.classList.add('show'));

    setTimeout(dismiss, duration);
}

function escapeHtml(value) {
    if (value === null || value === undefined) return '';
    return String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function withPlaceholder(value, placeholderText) {
    if (value === null || value === undefined || String(value).trim() === '') {
        return `<span class="empty-placeholder">${escapeHtml(placeholderText)}</span>`;
    }
    return escapeHtml(value);
}

function renderPagination(containerId, totalItems, currentPage, onChange, options = {}) {
    const container = document.getElementById(containerId);
    if (!container) return;
    const totalPages = Math.max(1, Math.ceil(totalItems / PAGE_SIZE));
    if (totalItems <= PAGE_SIZE) {
        container.innerHTML = '';
        container.classList.remove('compact');
        return;
    }

    const compact = options.compact === true;
    container.classList.toggle('compact', compact);

    let pages = [];
    if (compact) {
        const windowSize = 3;
        let start = Math.max(1, currentPage - 1);
        let end = Math.min(totalPages, start + windowSize - 1);
        start = Math.max(1, end - windowSize + 1);
        for (let i = start; i <= end; i++) pages.push(i);
    } else {
        for (let i = 1; i <= totalPages; i++) pages.push(i);
    }

    let html = '';
    html += `<button ${currentPage === 1 ? 'disabled' : ''} data-page="${currentPage - 1}" aria-label="Previous">
                        <span class="material-icons">chevron_left</span>
                     </button>`;

    pages.forEach(i => {
        html += `<button class="${i === currentPage ? 'active' : ''}" data-page="${i}">${i}</button>`;
    });

    html += `<button ${currentPage === totalPages ? 'disabled' : ''} data-page="${currentPage + 1}" aria-label="Next">
                        <span class="material-icons">chevron_right</span>
                     </button>`;

    container.innerHTML = html;
    container.querySelectorAll('button[data-page]').forEach(btn => {
        btn.addEventListener('click', () => {
            const page = parseInt(btn.getAttribute('data-page'), 10);
            if (!isNaN(page) && page >= 1 && page <= totalPages) onChange(page);
        });
    });
}

function paginate(items, page) {
    const start = (page - 1) * PAGE_SIZE;
    return items.slice(start, start + PAGE_SIZE);
}

function renderTable() {
    const tableBody = document.getElementById('tableBody');
    let rows = '';

    if (feedbackData.length === 0) {
        rows = '<tr><td colspan="4" style="text-align: center;">No feedback submissions available.</td></tr>';
    } else {
        paginate(feedbackData, tablePage).forEach(item => {
            rows += `
                        <tr>
                            <td data-label="Nationality">${escapeHtml(item.nationality)}</td>
                            <td data-label="Visit Date">${escapeHtml(item.visitDate)}</td>
                            <td data-label="Comments">${withPlaceholder(item.comments, 'No comments left.')}</td>
                            <td data-label="Overall Rating">${ratingCellHtml(item)}</td>
                        </tr>
                    `;
        });
    }
    tableBody.innerHTML = rows;
    renderPagination('tablePagination', feedbackData.length, tablePage, (p) => {
        tablePage = p;
        renderTable();
    });
}

function ratingCellHtml(item) {
    const numeric = (typeof item.average === 'number')
        ? item.average.toFixed(1)
        : Number(item.average || 0).toFixed(1);
    return `
                <div class="rating-cell">
                    <span class="rating-numeric">${numeric}/5.0</span>
                    <span class="rating-stars">${item.ratingText}</span>
                </div>
            `;
}

function renderAdminTable() {
    const adminTableBody = document.getElementById('adminTableBody');
    let rows = '';

    if (feedbackData.length === 0) {
        rows = '<tr><td colspan="4" style="text-align: center;">No feedback submissions available.</td></tr>';
    } else {
        paginate(feedbackData, adminPage).forEach((item) => {
            const recordId = item.id || item.ID || '';
            const realIndex = feedbackData.indexOf(item);
            rows += `
                        <tr>
                            <td>${escapeHtml(item.nationality)}</td>
                            <td>${escapeHtml(item.visitDate)}</td>
                            <td>${ratingCellHtml(item)}</td>
                            <td>
                                <button class="delete-btn" onclick="confirmDeleteFeedback(${recordId}, ${realIndex})">
                                    <span class="material-icons">delete</span>
                                    Delete
                                </button>
                            </td>
                        </tr>
                    `;
        });
    }
    adminTableBody.innerHTML = rows;
    renderPagination('adminPagination', feedbackData.length, adminPage, (p) => {
        adminPage = p;
        renderAdminTable();
    });
}

function renderSurveyTable() {
    const tbody = document.getElementById('surveyTableBody');
    let rows = '';

    if (!surveyData || surveyData.length === 0) {
        rows = '<tr><td colspan="2" style="text-align: center;">No survey responses found.</td></tr>';
    } else {
        paginate(surveyData, surveyPage).forEach(s => {
            const helpful = s.helpful ?? s.Helpful ?? '';
            const suggestions = s.survey_suggestions ?? s.suggestions ?? s.Suggestions ?? '';
            rows += `
                        <tr>
                            <td>${escapeHtml(helpful)}</td>
                            <td>${withPlaceholder(suggestions, 'No suggestions provided.')}</td>
                        </tr>
                    `;
        });
    }
    tbody.innerHTML = rows;
    renderPagination('surveyPagination', surveyData.length, surveyPage, (p) => {
        surveyPage = p;
        renderSurveyTable();
    });
}

// ---------------- Confirm modal ----------------
function openConfirmModal({ title, message, confirmText, confirmIcon, variant, icon, onConfirm }) {
    const modal = document.getElementById('confirmModal');
    document.getElementById('confirmModalTitleText').textContent = title || 'Confirm';
    document.getElementById('confirmModalIcon').textContent = icon || 'help_outline';
    document.getElementById('confirmModalMessage').textContent = message || 'Are you sure?';

    const btn = document.getElementById('confirmModalConfirmBtn');
    btn.className = 'modal-btn ' + (variant === 'primary' ? 'modal-btn-primary' : 'modal-btn-danger');
    btn.innerHTML = `<span class="material-icons">${confirmIcon || 'check'}</span>${confirmText || 'Confirm'}`;

    const handler = () => {
        btn.removeEventListener('click', handler);
        closeConfirmModal();
        if (typeof onConfirm === 'function') onConfirm();
    };
    const newBtn = btn.cloneNode(true);
    btn.parentNode.replaceChild(newBtn, btn);
    newBtn.addEventListener('click', handler);

    modal.classList.add('show');
}

function closeConfirmModal() {
    document.getElementById('confirmModal').classList.remove('show');
}

// Click backdrop or Escape to close any modal
document.addEventListener('click', (e) => {
    if (e.target.classList && e.target.classList.contains('modal-backdrop')) {
        e.target.classList.remove('show');
    }
});
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-backdrop.show').forEach(m => m.classList.remove('show'));
    }
});

// ---------------- Delete + clear survey (now via modal) ----------------
function confirmDeleteFeedback(id, index) {
    openConfirmModal({
        title: 'Delete Feedback',
        icon: 'delete_forever',
        message: 'This will permanently delete this feedback record. Continue?',
        confirmText: 'Delete',
        confirmIcon: 'delete',
        variant: 'danger',
        onConfirm: () => deleteFeedback(id, index)
    });
}

function deleteFeedback(id, index) {
    const url = 'delete_feedback.php?id=' + encodeURIComponent(id)
        + '&actor=' + encodeURIComponent(currentAdminUsername);
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                feedbackData.splice(index, 1);
                const totalPages = Math.max(1, Math.ceil(feedbackData.length / PAGE_SIZE));
                if (adminPage > totalPages) adminPage = totalPages;
                renderAdminTable();
                showToast('Feedback deleted successfully.', 'success');
                loadActivityLog();
            } else {
                showToast('Error deleting feedback: ' + data.message, 'error');
            }
        })
        .catch(error => showToast('Error: ' + error, 'error'));
}

function confirmClearSurvey() {
    openConfirmModal({
        title: 'Clear Survey Responses',
        icon: 'delete_sweep',
        message: 'This will remove every survey response. This cannot be undone. Continue?',
        confirmText: 'Clear All',
        confirmIcon: 'delete_sweep',
        variant: 'danger',
        onConfirm: clearSurveyResponses
    });
}

function clearSurveyResponses() {
    fetch('clear_survey.php?actor=' + encodeURIComponent(currentAdminUsername))
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                showToast('Survey responses cleared successfully.', 'success');
                surveyData = [];
                surveyPage = 1;
                renderSurveyTable();
                loadActivityLog();
            } else {
                showToast('Error clearing survey responses: ' + data.message, 'error');
            }
        })
        .catch(error => showToast('Error: ' + error, 'error'));
}

function confirmLogout() {
    openConfirmModal({
        title: 'Logout',
        icon: 'logout',
        message: 'Sign out of the admin dashboard?',
        confirmText: 'Logout',
        confirmIcon: 'logout',
        variant: 'primary',
        onConfirm: () => {
            fetch('admin_logout.php')
                .then(r => r.json())
                .catch(() => { })
                .finally(() => {
                    currentAdminUsername = '';
                    showToast('You have been logged out.', 'info');
                    switchView('planner');
                });
        }
    });
}

// ---------------- Profile modal ----------------
function openProfileModal() {
    document.getElementById('profileForm').reset();
    document.getElementById('profileModal').classList.add('show');
}

function closeProfileModal() {
    document.getElementById('profileModal').classList.remove('show');
}

function submitProfileChange(event) {
    event.preventDefault();
    const oldPassword = document.getElementById('profileOldPassword').value;
    const newUsername = document.getElementById('profileNewUsername').value.trim();
    const newPassword = document.getElementById('profileNewPassword').value;
    const confirmPassword = document.getElementById('profileConfirmPassword').value;

    if (!oldPassword) {
        showToast('Please enter your old password.', 'error');
        document.getElementById('profileOldPassword').focus();
        return;
    }
    if (!newUsername && !newPassword) {
        showToast('Provide a new username or a new password.', 'error');
        document.getElementById('profileNewPassword').focus();
        return;
    }
    if (newPassword) {
        const strongErr = validateStrongPassword(newPassword);
        if (strongErr) {
            showToast(strongErr, 'error');
            document.getElementById('profileNewPassword').focus();
            return;
        }
        if (!confirmPassword) {
            showToast('Please confirm your new password.', 'error');
            document.getElementById('profileConfirmPassword').focus();
            return;
        }
        if (newPassword !== confirmPassword) {
            showToast('New password and confirmation do not match.', 'error');
            document.getElementById('profileConfirmPassword').focus();
            return;
        }
    }

    const formData = new FormData();
    formData.append('current_username', currentAdminUsername);
    formData.append('old_password', oldPassword);
    formData.append('new_username', newUsername);
    formData.append('new_password', newPassword);

    fetch('admin_change_credentials.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            if (data.status === 'success') {
                currentAdminUsername = data.username || currentAdminUsername;
                document.getElementById('adminBarUsername').textContent = currentAdminUsername;
                showToast('Profile updated successfully.', 'success');
                closeProfileModal();
                loadActivityLog();
            } else {
                showToast(data.message || 'Failed to update profile.', 'error');
            }
        })
        .catch(err => showToast('Error: ' + err, 'error'));
}

// ---------------- Activity log ----------------
const ACTION_BADGES = {
    login_success: { variant: 'success', icon: 'login', label: 'Login' },
    login_failed: { variant: 'error', icon: 'lock', label: 'Login failed' },
    feedback_deleted: { variant: 'warn', icon: 'delete', label: 'Feedback deleted' },
    survey_cleared: { variant: 'warn', icon: 'delete_sweep', label: 'Survey cleared' },
    profile_updated: { variant: 'info', icon: 'manage_accounts', label: 'Profile updated' },
    profile_update_failed: { variant: 'error', icon: 'error', label: 'Profile update failed' }
};

function actionBadge(action) {
    const meta = ACTION_BADGES[action] || { variant: 'info', icon: 'bolt', label: action || '—' };
    return `<span class="badge badge-${meta.variant}"><span class="material-icons">${meta.icon}</span>${escapeHtml(meta.label)}</span>`;
}

function formatTimestamp(ts) {
    if (!ts) return '';
    const d = new Date(ts.replace(' ', 'T'));
    if (isNaN(d.getTime())) return ts;
    return d.toLocaleString();
}

function loadActivityLog() {
    fetch('admin_activity_log.php?limit=200')
        .then(r => r.json())
        .then(data => {
            activityLog = (data && data.entries) ? data.entries : [];
            const totalPages = Math.max(1, Math.ceil(activityLog.length / PAGE_SIZE));
            if (activityPage > totalPages) activityPage = 1;
            renderActivityLog();
        })
        .catch(err => {
            document.getElementById('activityLogBody').innerHTML =
                `<tr><td colspan="4" style="text-align:center;">Failed to load: ${escapeHtml(String(err))}</td></tr>`;
        });
}

// ---------------- Slideshow ----------------
function getCommentedFeedback() {
    return (feedbackData || []).filter(item =>
        item && item.comments && String(item.comments).trim() !== ''
    );
}

function renderSlide() {
    const track = document.getElementById('slideTrack');
    const dots = document.getElementById('slideDots');
    const prevBtn = document.getElementById('slidePrevBtn');
    const nextBtn = document.getElementById('slideNextBtn');
    if (!track) return;

    const slides = getCommentedFeedback();

    if (slides.length === 0) {
        track.innerHTML = `
                    <div class="slide-empty">
                        <span class="material-icons">forum</span>
                        No visitor comments yet — be the first to share!
                    </div>
                `;
        if (dots) dots.innerHTML = '';
        if (prevBtn) prevBtn.style.display = 'none';
        if (nextBtn) nextBtn.style.display = 'none';
        return;
    }

    if (slideIndex >= slides.length) slideIndex = 0;
    if (slideIndex < 0) slideIndex = slides.length - 1;

    const item = slides[slideIndex];
    const numeric = Number(item.average || 0).toFixed(1);
    const comments = escapeHtml(item.comments);

    track.innerHTML = `
                <div class="slide-card">
                    <div class="slide-header">
                        <span class="slide-badge">
                            <span class="material-icons">public</span>
                            ${escapeHtml(item.nationality)}
                        </span>
                        <span class="slide-date">
                            <span class="material-icons">event</span>
                            ${escapeHtml(item.visitDate)}
                        </span>
                    </div>
                    <div class="slide-rating">
                        <span class="slide-rating-numeric">${numeric}/5.0</span>
                        <span class="slide-rating-stars">${item.ratingText}</span>
                    </div>
                    <div class="slide-comment">
                        <span class="material-icons">format_quote</span>
                        <p>${comments}</p>
                    </div>
                </div>
            `;

    if (prevBtn) prevBtn.style.display = slides.length > 1 ? '' : 'none';
    if (nextBtn) nextBtn.style.display = slides.length > 1 ? '' : 'none';

    if (dots) {
        if (slides.length > 1) {
            let dotsHtml = '';
            slides.forEach((_, i) => {
                dotsHtml += `<button type="button" class="slide-dot ${i === slideIndex ? 'active' : ''}" data-slide="${i}" aria-label="Slide ${i + 1}"></button>`;
            });
            dots.innerHTML = dotsHtml;
            dots.querySelectorAll('.slide-dot').forEach(btn => {
                btn.addEventListener('click', () => {
                    slideIndex = parseInt(btn.getAttribute('data-slide'), 10) || 0;
                    renderSlide();
                    restartSlideshowTimer();
                });
            });
        } else {
            dots.innerHTML = '';
        }
    }
}

function nextSlide() {
    const slides = getCommentedFeedback();
    if (slides.length === 0) return;
    slideIndex = (slideIndex + 1) % slides.length;
    renderSlide();
}

function prevSlide() {
    const slides = getCommentedFeedback();
    if (slides.length === 0) return;
    slideIndex = (slideIndex - 1 + slides.length) % slides.length;
    renderSlide();
}

function startSlideshow() {
    renderSlide();
    stopSlideshow();
    if (getCommentedFeedback().length > 1) {
        slideTimer = setInterval(nextSlide, SLIDE_INTERVAL);
    }
}

function stopSlideshow() {
    if (slideTimer) {
        clearInterval(slideTimer);
        slideTimer = null;
    }
}

function restartSlideshowTimer() {
    stopSlideshow();
    if (getCommentedFeedback().length > 1) {
        slideTimer = setInterval(nextSlide, SLIDE_INTERVAL);
    }
}

function renderActivityLog() {
    const tbody = document.getElementById('activityLogBody');
    let rows = '';

    if (activityLog.length === 0) {
        rows = '<tr><td colspan="4" style="text-align: center;">No activity recorded yet.</td></tr>';
    } else {
        paginate(activityLog, activityPage).forEach(entry => {
            rows += `
                        <tr>
                            <td>${escapeHtml(formatTimestamp(entry.created_at))}</td>
                            <td>${escapeHtml(entry.username || '—')}</td>
                            <td class="action-cell">${actionBadge(entry.action)}</td>
                            <td>${escapeHtml(entry.details || '')}</td>
                        </tr>
                    `;
        });
    }
    tbody.innerHTML = rows;
    renderPagination('activityPagination', activityLog.length, activityPage, (p) => {
        activityPage = p;
        renderActivityLog();
    }, { compact: true });
}

// ---------------- Initial setup ----------------
document.addEventListener('DOMContentLoaded', () => {
    applyStoredDarkMode();

    const prevBtn = document.getElementById('slidePrevBtn');
    const nextBtn = document.getElementById('slideNextBtn');
    if (prevBtn) prevBtn.addEventListener('click', () => { prevSlide(); restartSlideshowTimer(); });
    if (nextBtn) nextBtn.addEventListener('click', () => { nextSlide(); restartSlideshowTimer(); });

    const slideshowEl = document.getElementById('feedbackSlideshow');
    if (slideshowEl) {
        slideshowEl.addEventListener('mouseenter', stopSlideshow);
        slideshowEl.addEventListener('mouseleave', restartSlideshowTimer);
    }

    if (currentAdminUsername) {
        switchView('admin-dashboard');
    } else {
        switchView('planner');
    }
});