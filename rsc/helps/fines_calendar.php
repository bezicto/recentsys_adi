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
    <title><?= htmlspecialchars($product_name ?? 'My Library');?> : Fines &amp; Calendar User Guide</title>
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
            <span class="fw-bold text-dark">Fines, Calendar &amp; Cashiering Operations Guide</span>
        </div>

        <!-- Section 1: Overview -->
        <div class="guide-section">
            <div class="guide-section-title">
                <i class="fa-solid fa-calendar-check"></i> Fines, Calendar &amp; Cashiering Overview
            </div>
            <p><?= htmlspecialchars($product_name ?? 'My Library');?> includes an automated, calendar-integrated overdue fines system. Fines are calculated fairly based on material types, automatically skipping weekends and official public holidays when the library is closed.</p>

            <div class="guide-callout guide-callout-info">
                <strong><i class="fa-solid fa-shield-halved me-1"></i> Superadmin Privileges:</strong><br/>
                Setting fine rates, modifying library operating days, managing holiday dates, and granting discretionary fee waivers at the cashier counter require <strong>Superadmin</strong> privileges.
            </div>
        </div>

        <!-- Section 2: Working Days & Holiday Calendar -->
        <div class="guide-section">
            <div class="guide-section-title">
                <i class="fa-solid fa-calendar-days"></i> Managing Operating Days &amp; Public Holidays
            </div>
            <p>Configured in the <strong>Holidays</strong> screen from the top toolbar:</p>

            <div class="row g-3">
                <div class="col-md-6 mb-3">
                    <div class="card p-3 h-100 bg-light border">
                        <h5 class="fw-bold text-primary mb-2"><i class="fa-solid fa-calendar-week me-1"></i> 1. Weekly Operating Days</h5>
                        <p class="text-muted" style="font-size: 0.875rem;">
                            Tick the checkboxes for days the library is open for regular circulation (Monday, Tuesday, Wednesday, Thursday, Friday, Saturday, Sunday).
                        </p>
                        <ul class="text-muted ps-3 mb-0" style="font-size: 0.85rem;">
                            <li><strong>Unchecked Days (Closed Days):</strong> If a day is unchecked (e.g. Sunday), the system will never set a return due date on that day.</li>
                            <li><strong>Automatic Due Date Shift:</strong> Due dates landing on closed days automatically shift forward to the next open working day.</li>
                        </ul>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <div class="card p-3 h-100 bg-light border">
                        <h5 class="fw-bold text-danger mb-2"><i class="fa-solid fa-calendar-xmark me-1"></i> 2. Gazetted Holidays &amp; Special Closures</h5>
                        <p class="text-muted" style="font-size: 0.875rem;">
                            Enter national holidays, state holidays, or semester break closures.
                        </p>
                        <ul class="text-muted ps-3 mb-0" style="font-size: 0.85rem;">
                            <li>Enter the <strong>Holiday Description</strong> (e.g. <em>Hari Kebangsaan</em>) and enter the date in <strong>dd/mm/yyyy</strong> format (e.g. <code>31/08/2026</code>).</li>
                            <li>Click <strong>Add Holiday</strong>. You can update or remove holiday dates at any time.</li>
                            <li><strong>Due Date Protection:</strong> Due dates falling on scheduled holidays automatically advance to the next open business day upon checkout.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 3: Automatic Due Date Calendar Shift -->
        <div class="guide-section">
            <div class="guide-section-title">
                <i class="fa-solid fa-clock-rotate-left"></i> How Automatic Due Date Shifting Works
            </div>
            <p>When an item is borrowed at the counter or renewed online, the system performs a calendar check:</p>

            <div class="workflow-step">
                <div class="step-number">1</div>
                <div class="step-content">
                    <h5>Standard Loan Period</h5>
                    <p class="mb-0 text-muted">The system adds the borrowing duration for that patron category (e.g. 14 days) to the checkout date.</p>
                </div>
            </div>

            <div class="workflow-step">
                <div class="step-number">2</div>
                <div class="step-content">
                    <h5>Holiday &amp; Weekend Inspection</h5>
                    <p class="mb-0 text-muted">If the calculated due date falls on a closed day (e.g. Sunday) or a holiday, the system automatically advances the return due date forward to the next open business day.</p>
                </div>
            </div>

            <div class="workflow-step">
                <div class="step-number">3</div>
                <div class="step-content">
                    <h5>Overdue Calculation on Return</h5>
                    <p class="mb-0 text-muted">When an overdue book is returned, the system calculates overdue days from the final shifted due date to determine the penalty fee.</p>
                </div>
            </div>
        </div>

        <!-- Section 4: Tiered Fines Policy Configuration -->
        <div class="guide-section">
            <div class="guide-section-title">
                <i class="fa-solid fa-coins"></i> Setting Tiered Fine Policies
            </div>
            <p>To configure fine rates for different types of materials (e.g. Open Shelf vs. Red Spot Reserve), open the <strong>Types</strong> screen and click the badge link under the <strong>Current Enforced Fines</strong> column (e.g. <code>Initial : 3 days (0.20/0.50)</code> or <code>Unset</code>) next to any material type:</p>

            <table class="table-guide">
                <thead>
                    <tr>
                        <th style="width: 250px;">Fine Setting</th>
                        <th>Purpose &amp; Operational Meaning</th>
                        <th style="width: 200px;">Example Setting</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Number of days for initial fine:</strong></td>
                        <td>The initial period (in days) during which overdue items are charged at the base initial rate.</td>
                        <td><code>3 Days</code></td>
                    </tr>
                    <tr>
                        <td><strong>Initial Fine Amount (MYR/day):</strong></td>
                        <td>The base daily rate charged during the initial days.</td>
                        <td><code><?= htmlspecialchars($currency_SHORT ?? 'MYR');?> 0.20 per day</code></td>
                    </tr>
                    <tr>
                        <td><strong>Subsequent Fine Amount (MYR/day):</strong></td>
                        <td>The escalated daily penalty charged for each day after the initial period has elapsed.</td>
                        <td><code><?= htmlspecialchars($currency_SHORT ?? 'MYR');?> 0.50 per day</code></td>
                    </tr>
                </tbody>
            </table>
            <p class="mt-2 text-muted small"><i class="fa-solid fa-circle-info text-primary me-1"></i> Click <strong>Enforce New</strong> to activate the updated fine policy rates.</p>

            <div class="guide-callout guide-callout-success">
                <strong><i class="fa-solid fa-calculator me-1"></i> Example Overdue Calculation:</strong><br/>
                If a book with a 3-day initial period (at RM 0.20/day) and subsequent rate of RM 0.50/day is returned <strong>5 days overdue</strong>:<br/>
                &bull; First 3 days: $3 \times \text{RM } 0.20 = \text{RM } 0.60$<br/>
                &bull; Remaining 2 days: $2 \times \text{RM } 0.50 = \text{RM } 1.00$<br/>
                &bull; <strong>Total Fine Payable = RM 1.60</strong>
            </div>
        </div>

        <!-- Section 5: Fine Cashiering & Receipts -->
        <div class="guide-section">
            <div class="guide-section-title">
                <i class="fa-solid fa-receipt"></i> Collecting Payments, Waivers &amp; Digital Receipts
            </div>
            <p>Fine settlements are handled at the counter through the <strong>Fines</strong> screen from the top toolbar:</p>

            <div class="workflow-step">
                <div class="step-number">1</div>
                <div class="step-content">
                    <h5>Search Patron</h5>
                    <p class="mb-0 text-muted">Scan the patron's membership barcode or type their IC / Matric number and click <strong>Search Patron Fines</strong>. The screen displays all returned items with unpaid fines.</p>
                </div>
            </div>

            <div class="workflow-step">
                <div class="step-number">2</div>
                <div class="step-content">
                    <h5>Select Items &amp; Apply Approved Discounts</h5>
                    <p class="mb-1 text-muted">Check the items the patron is paying for today:</p>
                    <ul class="text-muted mb-0" style="font-size: 0.875rem;">
                        <li><strong>Discretionary Discount / Waiver:</strong> Superadmins can enter an approved waiver amount (e.g. for approved appeals). The final payable amount recalculates automatically.</li>
                        <li><strong>Cash Tendered:</strong> Enter the physical cash received from the patron to see the exact change to return.</li>
                    </ul>
                </div>
            </div>

            <div class="workflow-step">
                <div class="step-number">3</div>
                <div class="step-content">
                    <h5>Click "Submit Payment"</h5>
                    <p class="mb-0 text-muted">Click <strong>Submit Payment</strong>. The transaction is finalized, marked as paid in the database, and an official receipt ID is confirmed on screen and logged in patron transaction history.</p>
                </div>
            </div>
        </div>

        <?php include_once '../includes/footerbar.php';?>
    </div>
</body>
</html>
