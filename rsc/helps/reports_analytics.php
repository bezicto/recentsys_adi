<?php
    session_start();
    define('includeExist', true);
    include_once '../includes/access_logged.php';
    include_once '../config.php';
    include_once '../includes/functions.php';
?>
<!DOCTYPE HTML>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?= htmlspecialchars($product_name ?? 'My Library');?> : Reports &amp; Analytics User Guide</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="../<?= htmlspecialchars($mini_icon_path ?? '', ENT_QUOTES, 'UTF-8');?>" rel="icon" type="image/png" />
    <link href="../assets/styles/style.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
</head>
<body>
    <?php include_once '../includes/loggedinfo.php';?>

    <div class="app-container">
        <?php include_once 'nav_header.php';?>

        <!-- Breadcrumb -->
        <div class="mb-3">
            <a href="index.php" class="text-decoration-none text-muted"><i class="fa-solid fa-house me-1"></i> Admin Guide</a>
            <span class="text-muted mx-2">/</span>
            <span class="fw-bold text-dark">Statistical Reports &amp; Analytics Guide</span>
        </div>

        <!-- Section 1: Overview -->
        <div class="guide-section">
            <div class="guide-section-title">
                <i class="fa-solid fa-chart-line"></i> Institutional Statistical Reports
            </div>
            <p><?= htmlspecialchars($product_name ?? 'My Library');?> provides a comprehensive statistical analytics dashboard accessed via the <strong>Reports</strong> button on the top toolbar. These reports are designed for library committee meetings, annual reports, accreditation audits, and collection development.</p>

            <div class="guide-callout guide-callout-info">
                <strong><i class="fa-solid fa-shield-halved me-1"></i> Access Scope:</strong><br/>
                Standard staff members can review their own cataloging history via <strong>"My Input"</strong>. The full 6-tab statistical suite is available to <strong>Superadmin</strong> users.
            </div>
        </div>

        <!-- Section 2: Six Reporting Tabs Breakdown -->
        <div class="guide-section">
            <div class="guide-section-title">
                <i class="fa-solid fa-table-columns"></i> The 6 Reporting Dimensions
            </div>

            <table class="table-guide">
                <thead>
                    <tr>
                        <th style="width: 220px;">Report Dimension</th>
                        <th>Key Metrics &amp; Operational Insights</th>
                        <th>Recommended Use</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>1. Data Managers Report</strong></td>
                        <td>
                            Cataloging productivity leaderboard showing the total number of records added by each staff cataloger, percentage share of the collection, and last login activity.
                        </td>
                        <td>Staff KPI evaluations and annual performance reviews.</td>
                    </tr>
                    <tr>
                        <td><strong>2. Type Statistics</strong></td>
                        <td>
                            Collection breakdown across material types (Open Shelf, Red Spot, Audio Visual, Serials, Digital Documents), showing total titles, copy holdings, and digital PDF document coverage.
                        </td>
                        <td>Collection development and accreditation audits.</td>
                    </tr>
                    <tr>
                        <td><strong>3. Search Analytics</strong></td>
                        <td>
                            Patron search demand intelligence. Tracks popular search terms, zero-result searches (books patrons searched for that the library does not yet have), and most-viewed catalog records.
                        </td>
                        <td>Book acquisition planning and identifying unfulfilled student demand.</td>
                    </tr>
                    <tr>
                        <td><strong>4. Loan Analytics</strong></td>
                        <td>
                            Circulation trends showing active loans, total historic circulations, monthly loan volume, and the most frequently borrowed books leaderboard.
                        </td>
                        <td>Monitoring collection usage and library footfall.</td>
                    </tr>
                    <tr>
                        <td><strong>5. Fine Collections</strong></td>
                        <td>
                            Financial cashiering audit summarizing total overdue fines incurred, cash collected, approved fee waivers, cashier breakdowns, and outstanding unpaid penalties.
                        </td>
                        <td>Monthly financial audits and treasury reporting.</td>
                    </tr>
                    <tr>
                        <td><strong>6. Cache Manager</strong></td>
                        <td>
                            Performance dashboard showing report status and providing a <strong>Purge All Stats Cache</strong> button to immediately refresh all statistical metrics on demand.
                        </td>
                        <td>Refreshing statistics after large batch cataloging sessions.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Section 3: High-Performance Cache & Purging -->
        <div class="guide-section">
            <div class="guide-section-title">
                <i class="fa-solid fa-bolt"></i> Instant Reports &amp; Refreshing Statistics
            </div>
            <p>To ensure reports load instantly even in libraries with over 100,000 books, <?= htmlspecialchars($product_name ?? 'My Library');?> stores pre-calculated summary reports in the background:</p>

            <div class="workflow-step">
                <div class="step-number">1</div>
                <div class="step-content">
                    <h5>Automatic Background Summaries</h5>
                    <p class="mb-0 text-muted">Summary metrics are compiled periodically so reports load in less than a second whenever an administrator opens the page.</p>
                </div>
            </div>

            <div class="workflow-step">
                <div class="step-number">2</div>
                <div class="step-content">
                    <h5>Need Instant Real-Time Numbers?</h5>
                    <p class="mb-0 text-muted">If you have just finished a large batch cataloging session or processed counter payments and want the new numbers to appear immediately, navigate to <strong>Tab 6 (Cache Manager)</strong> and click the red <strong>Purge All Stats Cache</strong> button. The system will immediately clear pre-calculated caches and compile fresh real-time statistics.</p>
                </div>
            </div>
        </div>

        <!-- Section 4: Exporting & Printing Reports -->
        <div class="guide-section">
            <div class="guide-section-title">
                <i class="fa-solid fa-print"></i> Exporting &amp; Printing Reports for Committee Meetings
            </div>
            <p>All statistical reports are styled with clean, printer-friendly formatting:</p>
            <ol class="text-muted mb-0" style="line-height: 1.65; font-size: 0.9rem;">
                <li>Select the report tab you wish to print (e.g. Material Type Statistics or Loan Analytics).</li>
                <li>Press <kbd>Ctrl + P</kbd> (Windows) or <kbd>Cmd + P</kbd> (Mac) in your browser.</li>
                <li>The page automatically removes toolbars and navigation buttons, applying official library headers, date stamps, and structured tables ready to save as a PDF or print on paper.</li>
            </ol>
        </div>

        <?php include_once '../includes/footerbar.php';?>
    </div>
</body>
</html>
