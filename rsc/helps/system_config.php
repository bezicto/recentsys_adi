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
    <title><?= htmlspecialchars($product_name ?? 'My Library');?> : System &amp; Policies User Guide</title>
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
            <span class="fw-bold text-dark">System &amp; Policies Administration Guide</span>
        </div>

        <!-- Section 1: Overview -->
        <div class="guide-section">
            <div class="guide-section-title">
                <i class="fa-solid fa-sliders"></i> Library System Policies &amp; Setup
            </div>
            <p>Superadmins can customize institutional library rules, material types, subject vocabularies, borrowing privileges, and circulation quotas directly through the administration screens.</p>

            <div class="quick-card-grid">
                <div class="card p-3 bg-light border">
                    <h5 class="fw-bold text-primary mb-1"><i class="fa-solid fa-shapes me-2"></i> Material Types</h5>
                    <p class="text-muted mb-2" style="font-size: 0.875rem;">Manage standard collection categories such as Open Shelf, Red Spot, Audio Visual, Serials, and Digital Documents.</p>
                    <a href="../admin/addtype.php" class="btn btn-primary btn-sm mt-auto"><i class="fa-solid fa-tags me-1"></i> Open Types Settings</a>
                </div>

                <div class="card p-3 bg-light border">
                    <h5 class="fw-bold text-success mb-1"><i class="fa-solid fa-layer-group me-2"></i> Subject Thesaurus</h5>
                    <p class="text-muted mb-2" style="font-size: 0.875rem;">Maintain the controlled vocabulary of topical subject headings available to catalogers during record entry.</p>
                    <a href="../admin/addsubject.php" class="btn btn-success btn-sm mt-auto"><i class="fa-solid fa-layer-group me-1"></i> Open Subject Settings</a>
                </div>

                <div class="card p-3 bg-light border">
                    <h5 class="fw-bold text-warning mb-1"><i class="fa-solid fa-calendar-check me-2"></i> Loan Eligibility</h5>
                    <p class="text-muted mb-2" style="font-size: 0.875rem;">Set maximum borrowing days and maximum concurrent loan items for each patron category.</p>
                    <a href="../admin/chan_loandays.php" class="btn btn-warning btn-sm mt-auto text-dark"><i class="fa-solid fa-calendar-check me-1"></i> Open Loan Rules</a>
                </div>
            </div>
        </div>

        <!-- Section 2: Material Types Management -->
        <div class="guide-section">
            <div class="guide-section-title">
                <i class="fa-solid fa-shapes"></i> Material Types Administration
            </div>
            <p>Administered via the <strong>Types</strong> screen from the top toolbar:</p>

            <table class="table-guide">
                <thead>
                    <tr>
                        <th style="width: 180px;">Material Type</th>
                        <th>Collection Purpose</th>
                        <th style="width: 140px;">Default Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Open Shelf</strong></td>
                        <td>Standard circulating collection available for general loan to registered patrons.</td>
                        <td><span class="badge badge-success">System Default</span></td>
                    </tr>
                    <tr>
                        <td><strong>Red Spot</strong></td>
                        <td>High-demand course reserves and reference books with restricted short-term loan rules.</td>
                        <td><span class="badge badge-success">System Default</span></td>
                    </tr>
                    <tr>
                        <td><strong>Audio Visual</strong></td>
                        <td>Non-print and multimedia resources including CDs, DVDs, kits, and educational media.</td>
                        <td><span class="badge badge-success">System Default</span></td>
                    </tr>
                    <tr>
                        <td><strong>Serial</strong></td>
                        <td>Continuing resources such as journals, periodicals, magazines, and newspapers.</td>
                        <td><span class="badge badge-success">System Default</span></td>
                    </tr>
                    <tr>
                        <td><strong>Digital File</strong></td>
                        <td>Electronic e-books, theses, reports, and digital full-text documents.</td>
                        <td><span class="badge badge-success">System Default</span></td>
                    </tr>
                    <tr>
                        <td><strong>Custom Types</strong></td>
                        <td>Libraries can add custom types (e.g. Braille, Kit, Microform, Thesis) and set specific fine rates for each.</td>
                        <td><span class="badge badge-secondary">Custom</span></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Section 3: Subject Headings & Thesaurus -->
        <div class="guide-section">
            <div class="guide-section-title">
                <i class="fa-solid fa-layer-group"></i> Controlled Subject Headings Thesaurus
            </div>
            <p>Administered via the <strong>Subjects</strong> screen from the top toolbar:</p>

            <ul class="text-muted" style="line-height: 1.65; font-size: 0.9rem;">
                <li><strong>Adding Headings:</strong> In the <em>Subject Headings</em> screen (<code>addsubject.php</code>), enter the <strong>Subject Heading Title</strong> (e.g. <em>Pendidikan &mdash; Malaysia</em> or <em>Computer networks &mdash; Security measures</em>) along with its <strong>Subject Code / Classification</strong> (e.g. <code>EDU</code> or <code>CS</code>), and click <strong>Add Subject Heading</strong>.</li>
                <li><strong>Cataloging Integration:</strong> Headings registered here appear in the <strong>Select Subject Heading</strong> modal (launched by clicking the <strong>[...]</strong> button next to the subject field on the Add/Edit Record screen). Clicking a heading inserts its designated classification code directly into the record, maintaining standardized classification.</li>
            </ul>
        </div>

        <!-- Section 4: Loan Eligibility & Quota Rules -->
        <div class="guide-section">
            <div class="guide-section-title">
                <i class="fa-solid fa-calendar-check"></i> Patron Loan Categories &amp; Quotas
            </div>
            <p>Administered via <strong>Loan Eligibility</strong> from the Users screen (<code>chan_loandays.php</code>):</p>

            <table class="table-guide">
                <thead>
                    <tr>
                        <th>User Type Code</th>
                        <th>Type Description</th>
                        <th style="width: 140px;">Max Loan Days</th>
                        <th style="width: 140px;">Max Items Limit</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>PATRON</strong></td>
                        <td>Standard circulating library member account tier.</td>
                        <td><code>14 Days</code></td>
                        <td><code>4 Books</code></td>
                    </tr>
                    <tr>
                        <td><strong>POSTGRAD</strong></td>
                        <td>Master's and PhD postgraduate research scholars.</td>
                        <td><code>30 Days</code></td>
                        <td><code>8 Books</code></td>
                    </tr>
                    <tr>
                        <td><strong>FACULTY</strong></td>
                        <td>Lecturers, professors, and academic researchers.</td>
                        <td><code>60 Days</code></td>
                        <td><code>15 Books</code></td>
                    </tr>
                    <tr>
                        <td><strong>STAFF</strong></td>
                        <td>University administration and institutional support staff.</td>
                        <td><code>21 Days</code></td>
                        <td><code>6 Books</code></td>
                    </tr>
                </tbody>
            </table>
            <p class="text-muted" style="font-size: 0.875rem;"><em>Admins can create new eligibility codes or edit loan durations (0 to 365 days) and borrowing limits (0 to 100 items) at any time.</em></p>
        </div>

        <!-- Section 5: Routine Library Best Practices Checklist -->
        <div class="guide-section">
            <div class="guide-section-title">
                <i class="fa-solid fa-list-check"></i> Library Administrator Best Practices Checklist
            </div>

            <div class="workflow-step">
                <div class="step-number">1</div>
                <div class="step-content">
                    <h5>Daily Operational Check</h5>
                    <p class="mb-0 text-muted">Review counter charging and discharging, ensure returned items are shelved, and process overdue fine settlements.</p>
                </div>
            </div>

            <div class="workflow-step">
                <div class="step-number">2</div>
                <div class="step-content">
                    <h5>Weekly Holiday Updates</h5>
                    <p class="mb-0 text-muted">Check the upcoming semester calendar and add upcoming public holidays in the <strong>Holidays</strong> screen to ensure due dates shift smoothly.</p>
                </div>
            </div>

            <div class="workflow-step">
                <div class="step-number">3</div>
                <div class="step-content">
                    <h5>Monthly Catalog &amp; Security Audits</h5>
                    <p class="mb-0 text-muted">Check the <strong>Duplicates</strong> tool to merge any duplicate titles, review the <strong>Blocked IPs</strong> screen, and review monthly circulation and fine collection reports in <strong>Reports</strong>.</p>
                </div>
            </div>
        </div>

        <?php include_once '../includes/footerbar.php';?>
    </div>
</body>
</html>
