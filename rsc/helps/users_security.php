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
    <title><?= htmlspecialchars($product_name ?? 'My Library');?> : Users &amp; Security User Guide</title>
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
            <span class="fw-bold text-dark">User Accounts &amp; Security Controls Guide</span>
        </div>

        <!-- Section 1: User Roles -->
        <div class="guide-section">
            <div class="guide-section-title">
                <i class="fa-solid fa-users-gear"></i> User Roles &amp; Responsibilities
            </div>
            <p>Administered via the <strong>Users</strong> screen from the top toolbar:</p>

            <table class="table-guide">
                <thead>
                    <tr>
                        <th style="width: 160px;">Role Level</th>
                        <th>Intended User Group</th>
                        <th>System Access &amp; Permissions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Superadmin</strong></td>
                        <td>Head Librarians, System Administrators, Chief Officers</td>
                        <td>
                            Complete administrative authority across all modules: Full cataloging, circulation counter, cashiering, modifying fine policies, holiday calendars, user accounts, password resets, unblocking locked IP addresses, and statistical reporting.
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Staff Librarian</strong></td>
                        <td>Desk Officers, Catalogers, Assistant Librarians</td>
                        <td>
                            Operational daily authority: Adding/editing catalog records, registering physical copies, editing copy statuses, Serials Kardex, Charging, Discharging, collecting overdue fine payments at the desk, Barcode printing, viewing personal cataloging history ("My Input"), and changing personal password.
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Patron / Member</strong></td>
                        <td>Students, Academic Faculty, Registered Public Members</td>
                        <td>
                            Patron portal access: Viewing currently borrowed books and return due dates, renewing loans online (up to allowable limit), viewing paid fine receipts, and updating profile photos.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Section 2: Adding & Managing Users -->
        <div class="guide-section">
            <div class="guide-section-title">
                <i class="fa-solid fa-user-plus"></i> Creating &amp; Managing Member Accounts
            </div>

            <div class="workflow-step">
                <div class="step-number">1</div>
                <div class="step-content">
                    <h5>Open the Add User Screen</h5>
                    <p class="mb-0 text-muted">From the Users management page, click the green <strong>Add User</strong> button. Enter the patron's IC Number / Matric ID / Staff ID as their unique username.</p>
                </div>
            </div>

            <div class="workflow-step">
                <div class="step-number">2</div>
                <div class="step-content">
                    <h5>Fill Patron Profile &amp; Role</h5>
                    <p class="mb-1 text-muted">Provide Full Name, Address / Department, and select the <strong>User Role / Eligibility Level</strong> (<code>PATRON</code> for standard library users, <code>TRUE</code> for library staff, or <code>SUPER</code> for administrators). New accounts are initialized with default password <code>1</code> (which patrons update after logging in). Click <strong>Add User Record</strong> to save.</p>
                    <p class="mb-0 text-muted" style="font-size: 0.875rem;"><em>Note: Borrowing quotas (max items and loan days) are administered through the <strong>Loan Eligibility</strong> settings on the Users page.</em></p>
                </div>
            </div>

            <div class="workflow-step">
                <div class="step-number">3</div>
                <div class="step-content">
                    <h5>Quick Administrative Actions</h5>
                    <p class="mb-1 text-muted">From the User Accounts Management list, Superadmins can perform instant one-click actions:</p>
                    <ul class="text-muted mb-0" style="font-size: 0.875rem;">
                        <li><strong>Reset Pwd:</strong> Instantly resets the user's password to default <code>1</code>. The patron can then log in and update their password in their My Account portal.</li>
                        <li><strong>Deactivate:</strong> Suspends the account and revokes login access immediately.</li>
                        <li><strong>Set Offline:</strong> Clears hung active login sessions.</li>
                        <li><strong>History:</strong> View the patron's complete lifetime circulation history, active loans, and fine payment receipts.</li>
                        <li><strong>Card &amp; QR:</strong> View the digital CR80 plastic membership card or standalone QR code.</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Section 3: Security Lockouts & Blocked IPs -->
        <div class="guide-section">
            <div class="guide-section-title">
                <i class="fa-solid fa-shield-virus"></i> Security Lockouts &amp; Blocked IPs Management
            </div>
            <p><?= htmlspecialchars($product_name ?? 'My Library');?> includes an automated security system to protect library accounts from unauthorized login attempts:</p>

            <div class="quick-card-grid">
                <div class="card p-3 bg-light border">
                    <h6 class="fw-bold text-danger mb-1"><i class="fa-solid fa-ban me-2"></i> 3-Attempt Auto Lockout</h6>
                    <p class="text-muted mb-0" style="font-size: 0.875rem;">
                        If <strong>3 consecutive incorrect passwords</strong> are entered from an IP address or device, the system automatically locks that IP address for <strong>7 days</strong> to prevent unauthorized access.
                    </p>
                </div>

                <div class="card p-3 bg-light border">
                    <h6 class="fw-bold text-success mb-1"><i class="fa-solid fa-unlock-keyhole me-2"></i> 1-Click Admin Unblock</h6>
                    <p class="text-muted mb-0" style="font-size: 0.875rem;">
                        If a genuine patron or staff member accidentally locks themselves out, a Superadmin can open <strong>Blocked IPs</strong> from the toolbar and click <strong>Unblock</strong> to restore access immediately.
                    </p>
                </div>

                <div class="card p-3 bg-light border">
                    <h6 class="fw-bold text-primary mb-1"><i class="fa-solid fa-broom me-2"></i> Clean Expired Blocks</h6>
                    <p class="text-muted mb-0" style="font-size: 0.875rem;">
                        Superadmins can click <strong>Clean Expired</strong> to purge resolved lock records, or click <strong>Block IP Manually</strong> to enforce a custom administrative lock.
                    </p>
                </div>
            </div>
        </div>

        <!-- Section 4: Patron Self-Registration QR Kiosk -->
        <div class="guide-section">
            <div class="guide-section-title">
                <i class="fa-solid fa-qrcode"></i> Lobby Self-Registration QR Kiosk
            </div>
            <p>For walk-in patrons, new students, or library visitors, administrators can launch the <strong>Own Registration With QR</strong> screen on a tablet or monitor placed at the library entrance:</p>

            <div class="workflow-step">
                <div class="step-number">1</div>
                <div class="step-content">
                    <h5>Open Kiosk Screen</h5>
                    <p class="mb-0 text-muted">From the Users management page, click <strong>Own Registration With QR</strong>. A high-contrast dynamic QR code is displayed on the screen.</p>
                </div>
            </div>

            <div class="workflow-step">
                <div class="step-number">2</div>
                <div class="step-content">
                    <h5>Patron Scans with Smartphone</h5>
                    <p class="mb-0 text-muted">The walk-in patron scans the QR code with their phone camera. A mobile-friendly registration form opens automatically on their device.</p>
                </div>
            </div>

            <div class="workflow-step">
                <div class="step-number">3</div>
                <div class="step-content">
                    <h5>Instant Membership Onboarding</h5>
                    <p class="mb-0 text-muted">The patron enters their IC / Matric ID, Full Name, and Department / Faculty. Once submitted, their account is instantly created with default password <code>1</code>, ready to borrow books at the counter.</p>
                </div>
            </div>
        </div>

        <!-- Section 5: Printing Membership Cards & Barcode Labels -->
        <div class="guide-section">
            <div class="guide-section-title">
                <i class="fa-solid fa-id-card"></i> Printing Membership Cards &amp; Barcode Sheets
            </div>

            <ul class="text-muted mb-0" style="line-height: 1.65; font-size: 0.9rem;">
                <li><strong>Individual Plastic Patron ID Cards:</strong> In the User Accounts Management table, click the <strong>Card</strong> button next to any patron to generate a standard CR80 ISO plastic membership card with library branding, high-density barcode, patron details, and IC number.</li>
                <li><strong>Batch Barcode Label Sheets:</strong> Click <strong>Print Barcodes</strong> on the Users screen to print sheets of membership barcodes on standard label paper for quick distribution during orientation weeks.</li>
            </ul>
        </div>

        <?php include_once '../includes/footerbar.php';?>
    </div>
</body>
</html>
