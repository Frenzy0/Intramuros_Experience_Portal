<?php
include 'config.php';

$sessionAdmin = $_SESSION['admin_username'] ?? '';

$sql = "SELECT * FROM feedback ORDER BY visit_date DESC";
$result = $conn->query($sql);
$feedbackData = [];
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $avg = ($row['cleanliness'] + $row['restroom'] + $row['guides'] + $row['accommodation'] + $row['overall']) / 5;
        $pct = max(0, min(100, ($avg / 5) * 100));
        $ratingText = '<span class="star-fraction"><span class="star-fraction-bg">★★★★★</span><span class="star-fraction-fg" style="width:' . $pct . '%">★★★★★</span></span>';
        $feedbackData[] = [
            'id' => $row['id'],
            'nationality' => $row['nationality'],
            'visitDate' => $row['visit_date'],
            'comments' => $row['comments'],
            'average' => $avg,
            'ratingText' => $ratingText
        ];
    }
}

$survey_sql = "SELECT * FROM survey ORDER BY id DESC";
$survey_result = $conn->query($survey_sql);
$surveyData = [];
if ($survey_result && $survey_result->num_rows > 0) {
    while($row = $survey_result->fetch_assoc()) {
        $surveyData[] = $row;
    }
}

$conn->close();
?>
<?php
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['clear_survey']) && !isset($_POST['admin_login'])) {

    $nationality = $_POST['nationality'] ?? '';
    $visit_date = $_POST['visitDate'] ?? '';
    $comments = $_POST['comments'] ?? '';

    $cleanliness = $_POST['cleanliness'] ?? null;
    $restroom = $_POST['restroom'] ?? null;
    $guides = $_POST['guides'] ?? null;
    $accommodation = $_POST['accommodation'] ?? null;
    $overall = $_POST['overall'] ?? null;

    if ($cleanliness !== null && $restroom !== null && $guides !== null && $accommodation !== null && $overall !== null) {
        $average = ($cleanliness + $restroom + $guides + $accommodation + $overall) / 5;

        $sql = "INSERT INTO feedback (nationality, visit_date, cleanliness, restroom, guides, accommodation, overall, comments, average)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "ssiiiiisd",
            $nationality,
            $visit_date,
            $cleanliness,
            $restroom,
            $guides,
            $accommodation,
            $overall,
            $comments,
            $average
        );

        if ($stmt->execute()) {
            echo "success";
            $stmt->close();
            exit();
        } else {
            echo "Error: " . $conn->error;
            $stmt->close();
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Intramuros Visitor System</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>

    <div id="toastContainer" aria-live="polite" aria-atomic="true"></div>

    <div class="container">
        <div class="header-section">
            <h2 id="portalTitle"><span class="material-icons">castle</span>Intramuros Tourist Feedback</h2>
            <div class="actions-group">
                <button class="theme-toggle" id="darkModeBtn" onclick="toggleDarkMode()">
                    <span class="material-icons" id="darkModeIcon">dark_mode</span>
                    <span id="darkModeLabel">Dark Mode</span>
                </button>
                <button class="theme-toggle" id="topAdminBtn" onclick="switchView('admin-login')">
                    <span class="material-icons">admin_panel_settings</span>
                    Admin
                </button>
            </div>
        </div>

        <div id="windowPlanner" class="view-section active">
            <p class="context">Welcome, traveler!<br>
            Share your experience from today's visit or check out feedback from past visitors.</p>

            <div class="planner-card">
                <div class="planner-tagline">
                    <span class="material-icons icon-inline">travel_explore</span>
                    Planning your trip? See what our past visitors rate us!
                </div>

                <div class="slideshow" id="feedbackSlideshow" aria-live="polite">
                    <button type="button" class="slide-nav slide-prev" id="slidePrevBtn" aria-label="Previous">
                        <span class="material-icons">chevron_left</span>
                    </button>
                    <div class="slide-track" id="slideTrack">
                        <div class="slide-empty">
                            <span class="material-icons">forum</span>
                            No visitor comments yet — be the first to share!
                        </div>
                    </div>
                    <button type="button" class="slide-nav slide-next" id="slideNextBtn" aria-label="Next">
                        <span class="material-icons">chevron_right</span>
                    </button>
                </div>
                <div class="slide-dots" id="slideDots"></div>

                <hr class="hr-soft">

                <div class="planner-divider-label">
                    <span class="material-icons icon-inline-sm">trending_up</span>
                    OVERALL VISITOR RATING
                </div>
                <div class="average-score">
                    <?php
                    include_once 'config.php';
                    $avg_sql = "SELECT AVG(overall) AS average_score FROM feedback";
                    $avg_result = $conn->query($avg_sql);
                    $average = 0.0;

                    if ($avg_result && $avg_row = $avg_result->fetch_assoc()) {
                        if ($avg_row['average_score'] !== null) {
                            $average = round(floatval($avg_row['average_score']), 1);
                        }
                    }
                    echo number_format($average, 1) . " / 5.0";
                    ?>
                </div>

                <div class="stars-static">
                    <?php
                    $avgPct = max(0, min(100, ($average / 5) * 100));
                    ?>
                    <span class="star-fraction">
                        <span class="star-fraction-bg">★★★★★</span>
                        <span class="star-fraction-fg" style="width: <?php echo $avgPct; ?>%">★★★★★</span>
                    </span>
                </div>

                <button class="action-btn" onclick="switchView('form')">
                    <span class="material-icons">rate_review</span>
                    Leave Feedback
                </button>
                <button class="action-btn action-btn-secondary" onclick="switchView('table')">
                    <span class="material-icons">reviews</span>
                    View Visitor Reviews
                </button>
            </div>
        </div>

        <div id="windowForm" class="view-section">
            <p class="context">Help us improve the historic Intramuros walls and facilities by sharing your experience with us!</p>

            <form id="feedbackForm" onsubmit="submitFeedback(event)" novalidate>
                <div class="form-group">
                    <label for="nationality" class="label-with-icon"><span class="material-icons">public</span>Nationality <span class="required-mark">*</span></label>
                    <select id="nationality" name="nationality">
                        <option value="" disabled selected>Select an option</option>
                        <option value="Local">Local</option>
                        <option value="Foreign">Foreign</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="visitDate" class="label-with-icon"><span class="material-icons">event</span>Visit Date <span class="required-mark">*</span></label>
                    <input type="date" id="visitDate" name="visitDate" min="<?php echo date('Y-m-d'); ?>" max="<?php echo date('Y-m-d'); ?>">
                </div>

                <hr class="hr-soft-lg">

                <div class="form-group">
                    <label class="label-with-icon"><span class="material-icons">cleaning_services</span>Cleanliness Rating <span class="required-mark">*</span></label>
                    <div class="star-rating">
                        <input type="radio" id="cleanliness5" name="cleanliness" value="5"><label for="cleanliness5" title="5 stars">★</label>
                        <input type="radio" id="cleanliness4" name="cleanliness" value="4"><label for="cleanliness4" title="4 stars">★</label>
                        <input type="radio" id="cleanliness3" name="cleanliness" value="3"><label for="cleanliness3" title="3 stars">★</label>
                        <input type="radio" id="cleanliness2" name="cleanliness" value="2"><label for="cleanliness2" title="2 stars">★</label>
                        <input type="radio" id="cleanliness1" name="cleanliness" value="1"><label for="cleanliness1" title="1 star">★</label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="label-with-icon"><span class="material-icons">wc</span>Restroom Rating <span class="required-mark">*</span></label>
                    <div class="star-rating">
                        <input type="radio" id="restroom5" name="restroom" value="5"><label for="restroom5" title="5 stars">★</label>
                        <input type="radio" id="restroom4" name="restroom" value="4"><label for="restroom4" title="4 stars">★</label>
                        <input type="radio" id="restroom3" name="restroom" value="3"><label for="restroom3" title="3 stars">★</label>
                        <input type="radio" id="restroom2" name="restroom" value="2"><label for="restroom2" title="2 stars">★</label>
                        <input type="radio" id="restroom1" name="restroom" value="1"><label for="restroom1" title="1 star">★</label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="label-with-icon"><span class="material-icons">tour</span>Tour Guides Rating <span class="required-mark">*</span></label>
                    <div class="star-rating">
                        <input type="radio" id="guides5" name="guides" value="5"><label for="guides5" title="5 stars">★</label>
                        <input type="radio" id="guides4" name="guides" value="4"><label for="guides4" title="4 stars">★</label>
                        <input type="radio" id="guides3" name="guides" value="3"><label for="guides3" title="3 stars">★</label>
                        <input type="radio" id="guides2" name="guides" value="2"><label for="guides2" title="2 stars">★</label>
                        <input type="radio" id="guides1" name="guides" value="1"><label for="guides1" title="1 star">★</label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="label-with-icon"><span class="material-icons">hotel</span>Accommodation/Hotel Rating <span class="required-mark">*</span></label>
                    <div class="star-rating">
                        <input type="radio" id="accommodation5" name="accommodation" value="5"><label for="accommodation5" title="5 stars">★</label>
                        <input type="radio" id="accommodation4" name="accommodation" value="4"><label for="accommodation4" title="4 stars">★</label>
                        <input type="radio" id="accommodation3" name="accommodation" value="3"><label for="accommodation3" title="3 stars">★</label>
                        <input type="radio" id="accommodation2" name="accommodation" value="2"><label for="accommodation2" title="2 stars">★</label>
                        <input type="radio" id="accommodation1" name="accommodation" value="1"><label for="accommodation1" title="1 star">★</label>
                    </div>
                </div>

                <hr class="hr-soft-lg">

                <div class="form-group">
                    <label class="label-with-icon"><span class="material-icons">star_rate</span>Overall Rating <span class="required-mark">*</span></label>
                    <div class="star-rating">
                        <input type="radio" id="overall5" name="overall" value="5"><label for="overall5" title="5 stars">★</label>
                        <input type="radio" id="overall4" name="overall" value="4"><label for="overall4" title="4 stars">★</label>
                        <input type="radio" id="overall3" name="overall" value="3"><label for="overall3" title="3 stars">★</label>
                        <input type="radio" id="overall2" name="overall" value="2"><label for="overall2" title="2 stars">★</label>
                        <input type="radio" id="overall1" name="overall" value="1"><label for="overall1" title="1 star">★</label>
                    </div>
                </div>


                <div class="form-group">
                    <label for="comments" class="label-with-icon"><span class="material-icons">comment</span>Additional Comments</label>
                    <textarea id="comments" name="comments" placeholder="Share your experience or suggestions for Intramuros..."></textarea>
                </div>

                <button type="submit" class="submit-btn" id="submitBtn">
                    <span class="material-icons">send</span>
                    Submit Feedback
                </button>
                <button type="button" class="action-btn action-btn-back" onclick="switchView('planner')">
                    <span class="material-icons">arrow_back</span>
                    Go Back to Homepage
                </button>
            </form>
        </div>

        <div id="windowTable" class="view-section">
            <p class="context">A summary of the latest visitor experience ratings.</p>

            <table class="styled-table">
                <colgroup>
                    <col class="col-nationality">
                    <col class="col-date">
                    <col class="col-comments">
                    <col class="col-rating">
                </colgroup>
                <thead>
                    <tr>
                        <th><span class="th-label"><span class="material-icons">public</span><span>Nationality</span></span></th>
                        <th><span class="th-label"><span class="material-icons">event</span><span>Visit Date</span></span></th>
                        <th><span class="th-label"><span class="material-icons">comment</span><span>Comments</span></span></th>
                        <th><span class="th-label"><span class="material-icons">star_rate</span><span>Overall Rating</span></span></th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                </tbody>
            </table>
            <div class="pagination" id="tablePagination"></div>

            <button class="action-btn action-btn-back-large" onclick="switchView('planner')">
                <span class="material-icons">arrow_back</span>
                Go Back to Homepage
            </button>
        </div>

        <div id="windowAdminLogin" class="view-section">
            <p class="context">Please enter your credentials to manage the feedback.</p>

            <form onsubmit="handleAdminLogin(event)">
                <div class="form-group">
                    <label for="adminUsername" class="label-with-icon"><span class="material-icons">person</span>Username</label>
                    <input type="text" id="adminUsername" placeholder="Enter username">
                </div>
                <div class="form-group">
                    <label for="adminPassword" class="label-with-icon"><span class="material-icons">key</span>Password</label>
                    <div class="password-field">
                        <input type="password" id="adminPassword" placeholder="Enter password">
                        <button type="button" class="password-toggle" id="adminPasswordToggle" onclick="toggleAdminPassword()" aria-label="Show password">
                            <span class="material-icons" id="adminPasswordToggleIcon">visibility</span>
                        </button>
                    </div>
                </div>
                <button type="submit" class="submit-btn">
                    <span class="material-icons">login</span>
                    Login
                </button>
                <button type="button" class="action-btn action-btn-back" onclick="switchView('planner')">
                    <span class="material-icons">arrow_back</span>
                    Go Back to Homepage
                </button>
            </form>
        </div>

        <div id="windowAdminDashboard" class="view-section">
            <div class="admin-bar">
                <div class="admin-bar-info">
                    <span class="material-icons">account_circle</span>
                    Signed in as <span id="adminBarUsername">admin</span>
                </div>
                <div class="admin-bar-actions">
                    <button type="button" onclick="openProfileModal()">
                        <span class="material-icons">manage_accounts</span>
                        Edit Profile
                    </button>
                    <button type="button" class="secondary" onclick="confirmLogout()">
                        <span class="material-icons">logout</span>
                        Logout
                    </button>
                </div>
            </div>

            <p class="context">Review and delete feedback submissions.</p>

            <table class="styled-table">
                <thead>
                    <tr>
                        <th><span class="material-icons">public</span>Nationality</th>
                        <th><span class="material-icons">event</span>Visit Date</th>
                        <th><span class="material-icons">star_rate</span>Overall Average</th>
                        <th><span class="material-icons">settings</span>Action</th>
                    </tr>
                </thead>
                <tbody id="adminTableBody">
                </tbody>
            </table>
            <div class="pagination" id="adminPagination"></div>

            <hr class="section-divider">

            <h3 class="section-heading"><span class="material-icons">poll</span>Quick Survey Responses</h3>
            <table class="styled-table styled-table-tight">
                <thead>
                    <tr>
                        <th><span class="material-icons">help_outline</span>Helpful?</th>
                        <th><span class="material-icons">lightbulb</span>Suggestions / Comments</th>
                    </tr>
                </thead>
                <tbody id="surveyTableBody">
                </tbody>
            </table>
            <div class="pagination" id="surveyPagination"></div>

            <div class="action-row-right">
                <button type="button" onclick="confirmClearSurvey()" class="action-btn clear-survey-btn">
                    <span class="material-icons">delete_sweep</span>
                    Clear Survey Responses
                </button>
            </div>

            <hr class="section-divider">

            <div class="section-header-row">
                <h3><span class="material-icons">history</span>Activity Log</h3>
                <button type="button" class="theme-toggle" onclick="loadActivityLog()">
                    <span class="material-icons">refresh</span>
                    Refresh
                </button>
            </div>
            <table class="styled-table activity-log-table">
                <thead>
                    <tr>
                        <th><span class="material-icons">schedule</span>When</th>
                        <th><span class="material-icons">person</span>User</th>
                        <th><span class="material-icons">bolt</span>Action</th>
                        <th><span class="material-icons">notes</span>Details</th>
                    </tr>
                </thead>
                <tbody id="activityLogBody">
                    <tr><td colspan="4" class="text-center">Loading activity...</td></tr>
                </tbody>
            </table>
            <div class="pagination compact" id="activityPagination"></div>
        </div>

        <div class="app-footer">
            <div><strong>DIT 2-4 | TEAM 9</strong></div>
            <div class="app-footer-line"><span class="material-icons icon-inline-sm">favorite</span> Thank you for visiting our portal!</div>
        </div>
    </div>

    <div class="modal-backdrop" id="confirmModal" role="dialog" aria-modal="true" aria-labelledby="confirmModalTitle">
        <div class="modal">
            <div class="modal-header">
                <h3 id="confirmModalTitle"><span class="material-icons" id="confirmModalIcon">help_outline</span><span id="confirmModalTitleText">Confirm</span></h3>
                <button class="modal-close" type="button" onclick="closeConfirmModal()" aria-label="Close">
                    <span class="material-icons">close</span>
                </button>
            </div>
            <div class="modal-body">
                <p id="confirmModalMessage">Are you sure?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="modal-btn modal-btn-secondary" onclick="closeConfirmModal()">
                    <span class="material-icons">close</span>
                    Cancel
                </button>
                <button type="button" class="modal-btn modal-btn-danger" id="confirmModalConfirmBtn">
                    <span class="material-icons">check</span>
                    Confirm
                </button>
            </div>
        </div>
    </div>

    <div class="modal-backdrop" id="profileModal" role="dialog" aria-modal="true" aria-labelledby="profileModalTitle">
        <div class="modal">
            <div class="modal-header">
                <h3 id="profileModalTitle"><span class="material-icons">manage_accounts</span>Edit Admin Profile</h3>
                <button class="modal-close" type="button" onclick="closeProfileModal()" aria-label="Close">
                    <span class="material-icons">close</span>
                </button>
            </div>
            <form id="profileForm" onsubmit="submitProfileChange(event)">
                <div class="modal-body">
                    <p class="modal-help-text">Update your username and/or password. Old password is required to confirm.</p>

                    <div class="form-group">
                        <label for="profileOldPassword" class="label-with-icon"><span class="material-icons">lock</span>Old Password <span class="required-mark">*</span></label>
                        <div class="password-field">
                            <input type="password" id="profileOldPassword" placeholder="Enter current password">
                            <button type="button" class="password-toggle" id="profileOldPasswordToggle" onclick="togglePasswordVisibility('profileOldPassword', 'profileOldPasswordToggleIcon', 'profileOldPasswordToggle')" aria-label="Show password">
                                <span class="material-icons" id="profileOldPasswordToggleIcon">visibility</span>
                            </button>
                        </div>
                    </div>

                    <hr class="hr-soft-md">

                    <div class="form-group">
                        <label for="profileNewUsername" class="label-with-icon"><span class="material-icons">person</span>New Username <span class="optional-mark">(optional)</span></label>
                        <input type="text" id="profileNewUsername" placeholder="Leave blank to keep current (case-sensitive)">
                    </div>

                    <div class="form-group">
                        <label for="profileNewPassword" class="label-with-icon"><span class="material-icons">key</span>New Password</label>
                        <div class="password-field">
                            <input type="password" id="profileNewPassword" placeholder="Min 12 chars; upper, lower, number, special">
                            <button type="button" class="password-toggle" id="profileNewPasswordToggle" onclick="togglePasswordVisibility('profileNewPassword', 'profileNewPasswordToggleIcon', 'profileNewPasswordToggle')" aria-label="Show password">
                                <span class="material-icons" id="profileNewPasswordToggleIcon">visibility</span>
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="profileConfirmPassword" class="label-with-icon"><span class="material-icons">key</span>Confirm New Password</label>
                        <div class="password-field">
                            <input type="password" id="profileConfirmPassword" placeholder="Re-type new password">
                            <button type="button" class="password-toggle" id="profileConfirmPasswordToggle" onclick="togglePasswordVisibility('profileConfirmPassword', 'profileConfirmPasswordToggleIcon', 'profileConfirmPasswordToggle')" aria-label="Show password">
                                <span class="material-icons" id="profileConfirmPasswordToggleIcon">visibility</span>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="modal-btn modal-btn-secondary" onclick="closeProfileModal()">
                        <span class="material-icons">close</span>
                        Cancel
                    </button>
                    <button type="submit" class="modal-btn modal-btn-primary">
                        <span class="material-icons">save</span>
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <button type="button" class="survey-toggle-btn" id="surveyToggleBtn" onclick="reopenSurvey()" aria-label="Open Quick Survey" title="Quick Survey">
        <span class="material-icons">quiz</span>
    </button>

    <div class="floating-survey" id="floatingSurvey">
        <button class="close-survey-btn" type="button" onclick="closeSurvey()" aria-label="Close survey">&times;</button>
        <h3><span class="material-icons">chat</span>Quick Survey</h3>
        <p>Did you find this feedback form helpful and easy to use?</p>

        <form action="#" method="POST" id="surveyForm" onsubmit="submitSurvey(event)">
            <div class="survey-radio-row">
                <label><input type="radio" name="helpful" value="yes" required> Yes</label>
                <label><input type="radio" name="helpful" value="no" required> No</label>
            </div>

            <div class="form-group form-group-tight">
                <label for="surveySuggestions" class="survey-suggestion-label">Suggestions?</label>
                <textarea id="surveySuggestions" name="surveySuggestions" placeholder="Tell us how to improve..."></textarea>
            </div>

            <button type="submit" class="submit-btn submit-btn-survey">
                <span class="material-icons">send</span>
                Submit
            </button>
        </form>
    </div>

    <script>
        window.feedbackData = <?php echo json_encode($feedbackData); ?>;
        window.surveyData = <?php echo json_encode($surveyData); ?>;
        window.currentAdminUsername = <?php echo json_encode($sessionAdmin); ?>;
    </script>
    <script src="js/script.js"></script>
</body>
</html>
