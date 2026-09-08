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
    <title><?= htmlspecialchars($product_name ?? 'My Library');?> : Circulation &amp; Desk User Guide</title>
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
            <span class="fw-bold text-dark">Circulation &amp; Desk Operations Guide</span>
        </div>

        <!-- Section 1: Overview -->
        <div class="guide-section">
            <div class="guide-section-title">
                <i class="fa-solid fa-arrows-spin"></i> Circulation Desk Operations
            </div>
            <p>The Circulation Desk module enables counter staff to handle daily borrowing (Charging), returning (Discharging), renewal requests, borrowing limit checks, and overdue alerts.</p>

            <div class="quick-card-grid">
                <div class="card p-3 border">
                    <h5 class="fw-bold text-primary mb-1"><i class="fa-solid fa-arrow-up-right-from-square me-2"></i> Charge / Loan</h5>
                    <p class="text-muted mb-2" style="font-size: 0.875rem;">Check out reading materials to registered patrons with instant quota checks and holiday-aware due date calculation.</p>
                    <a href="../admin/charge.php" class="btn btn-primary btn-sm mt-auto"><i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Open Charge Screen</a>
                </div>

                <div class="card p-3 border">
                    <h5 class="fw-bold text-success mb-1"><i class="fa-solid fa-inbox me-2"></i> Discharge (Return)</h5>
                    <p class="text-muted mb-2" style="font-size: 0.875rem;">Check in returned materials, release copy status back to Available, and identify overdue items.</p>
                    <a href="../admin/discharge.php" class="btn btn-success btn-sm mt-auto"><i class="fa-solid fa-inbox me-1"></i> Open Discharge Screen</a>
                </div>

                <div class="card p-3 border">
                    <h5 class="fw-bold text-warning mb-1"><i class="fa-solid fa-money-bill-wave me-2"></i> Fines &amp; Payments</h5>
                    <p class="text-muted mb-2" style="font-size: 0.875rem;">Collect overdue fines, apply authorized fee waivers, calculate change, and confirm official digital receipt numbers.</p>
                    <a href="../admin/paysearch.php" class="btn btn-warning btn-sm mt-auto text-dark"><i class="fa-solid fa-money-bill-wave me-1"></i> Open Fines Cashier</a>
                </div>
            </div>
        </div>

        <!-- Section 2: Circulation Barcode Modes -->
        <div class="guide-section">
            <div class="guide-section-title">
                <i class="fa-solid fa-barcode"></i> Circulation Barcode Options
            </div>
            <p><?= htmlspecialchars($product_name ?? 'My Library');?> supports two barcode workflows configured to match your library's operations:</p>

            <table class="table-guide">
                <thead>
                    <tr>
                        <th style="width: 200px;">Circulation Mode</th>
                        <th style="width: 160px;">Barcode Scanned</th>
                        <th>Operational Workflow</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Accession Number Mode</strong><br/><span class="ui-badge">Standard Library Labels</span></td>
                        <td>10-digit Accession Barcode (e.g. <code>0000000045</code>)</td>
                        <td>
                            Every physical copy on the shelf has an accession barcode label printed and pasted on its spine or cover. When scanned, the system tracks that exact physical copy. <em>(Recommended for standard academic and public libraries)</em>.
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Publisher Barcode Mode</strong><br/><span class="ui-badge">Printed ISBN / ISSN</span></td>
                        <td>Manufacturer ISBN Barcode (e.g. <code>9789834900123</code>)</td>
                        <td>
                            Staff scans the publisher's printed barcode on the back cover of the book. The system automatically selects any available copy of that title. On return, if a patron holds multiple copies of that ISBN, staff is prompted for the patron ID.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Section 3: Charging Workflow -->
        <div class="guide-section">
            <div class="guide-section-title">
                <i class="fa-solid fa-arrow-right-to-bracket"></i> Step-by-Step Borrowing (Charging) Procedure
            </div>

            <div class="workflow-step">
                <div class="step-number">1</div>
                <div class="step-content">
                    <h5>Scan or Enter Patron ID / IC</h5>
                    <p class="mb-1 text-muted">Open the <strong>Charge / Loan</strong> screen. The cursor is automatically positioned in the Patron ID box. You can:</p>
                    <ul class="text-muted mb-0" style="font-size: 0.875rem;">
                        <li>Scan the patron's physical membership card barcode using a handheld scanner.</li>
                        <li>Or click the <strong>Scan</strong> button to activate the camera/webcam scanner.</li>
                        <li>Or manually type the patron's IC / Matric number and press Enter.</li>
                    </ul>
                </div>
            </div>

            <div class="workflow-step">
                <div class="step-number">2</div>
                <div class="step-content">
                    <h5>Automatic Borrowing Quota Verification</h5>
                    <p class="mb-1 text-muted">The system verifies the patron's account in real-time:</p>
                    <ul class="text-muted mb-0" style="font-size: 0.875rem;">
                        <li><strong>Active Limit:</strong> Checks how many books the patron currently has on loan against the maximum allowed for their category (e.g. Students: 4 books, Faculty: 10 books).</li>
                        <li><strong>Quota Exceeded Alert:</strong> If the patron has reached their limit, the screen warns: <em>"The patron has exceeded max number of loan items."</em></li>
                    </ul>
                </div>
            </div>

            <div class="workflow-step">
                <div class="step-number">3</div>
                <div class="step-content">
                    <h5>Scan Material Barcode</h5>
                    <p class="mb-1 text-muted">Scan the barcode on the library book. The system immediately performs safety checks:</p>
                    <ul class="text-muted mb-0" style="font-size: 0.875rem;">
                        <li><strong>Reference Only Protection:</strong> If the item is marked Reference Only or Red Spot, borrowing is blocked with an alert: <em>"Loan restricted: Item is designated as Reference Only / Current Issue (In-Library Use Only)."</em></li>
                        <li><strong>Availability Check:</strong> Ensures the item is currently on the shelf and not already on loan or missing.</li>
                    </ul>
                </div>
            </div>

            <div class="workflow-step">
                <div class="step-number">4</div>
                <div class="step-content">
                    <h5>Loan Confirmation &amp; Return Due Date</h5>
                    <p class="mb-0 text-muted">Click <strong>Complete Charge</strong> (or let the scanner auto-submit). A green confirmation banner displays the book title and patron name. The system calculates the return due date based on the patron's category duration (e.g. 14 days) and automatically advances the date if it lands on a weekend or public holiday.</p>
                </div>
            </div>
        </div>

        <!-- Section 4: Discharging Workflow -->
        <div class="guide-section">
            <div class="guide-section-title">
                <i class="fa-solid fa-inbox"></i> Step-by-Step Return (Discharging) Procedure
            </div>

            <div class="workflow-step">
                <div class="step-number">1</div>
                <div class="step-content">
                    <h5>Scan the Returned Book Barcode</h5>
                    <p class="mb-0 text-muted">Open the <strong>Discharge</strong> screen. Simply scan the book's barcode using your handheld scanner and click <strong>Complete Discharge</strong> (or auto-submit). The patron does not need to be present for book returns.</p>
                </div>
            </div>

            <div class="workflow-step">
                <div class="step-number">2</div>
                <div class="step-content">
                    <h5>Instant Status Update</h5>
                    <p class="mb-1 text-muted">The system immediately releases the item copy back to <strong>AVAILABLE</strong> status so other patrons can borrow it.</p>
                </div>
            </div>

            <div class="workflow-step">
                <div class="step-number">3</div>
                <div class="step-content">
                    <h5>Overdue Check &amp; Fine Information</h5>
                    <p class="mb-0 text-muted">If the book is returned past its due date, the screen displays an overdue notification showing the overdue days and fine amount. Staff can then proceed to the <strong>Fines</strong> screen (<code>paysearch.php</code>) to collect the outstanding fine.</p>
                </div>
            </div>
        </div>

        <!-- Section 5: Online Loan Renewals & Digital Patron Cards -->
        <div class="guide-section">
            <div class="guide-section-title">
                <i class="fa-solid fa-repeat"></i> Patron Self-Service: Online Renewals &amp; Digital Cards
            </div>
            <p>Library members can manage their loans and access digital membership services from home or on mobile devices:</p>

            <div class="workflow-step">
                <div class="step-number">1</div>
                <div class="step-content">
                    <h5>Log in to "My Account"</h5>
                    <p class="mb-0 text-muted">The patron clicks <strong>Login to My Account</strong> on the home page and enters their IC / Matric number and password.</p>
                </div>
            </div>

            <div class="workflow-step">
                <div class="step-number">2</div>
                <div class="step-content">
                    <h5>View Current Loans &amp; Digital Card</h5>
                    <p class="mb-0 text-muted">The patron portal lists all currently borrowed items, borrowing dates, due dates, and remaining renewal allowances. Patrons can also tap <strong>Digital Library Card</strong> to display an on-screen barcode for instant contactless checkout at the circulation desk.</p>
                </div>
            </div>

            <div class="workflow-step">
                <div class="step-number">3</div>
                <div class="step-content">
                    <h5>Click "Renew"</h5>
                    <p class="mb-1 text-muted">The patron clicks the <strong>Renew</strong> button next to an eligible book:</p>
                    <ul class="text-muted mb-0" style="font-size: 0.875rem;">
                        <li><strong>Renewal Limit:</strong> Patrons can renew up to the maximum permitted count (usually 2 consecutive times).</li>
                        <li><strong>Overdue Restriction:</strong> If an item is already overdue, online renewal is blocked, and the patron must bring the book to the counter.</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Section 6: Copy Status Guide -->
        <div class="guide-section">
            <div class="guide-section-title">
                <i class="fa-solid fa-list-check"></i> Copy Status Quick Reference
            </div>

            <table class="table-guide">
                <thead>
                    <tr>
                        <th style="width: 140px;">Status Label</th>
                        <th style="width: 140px;">Visual Badge</th>
                        <th>Operational Meaning</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Available</strong></td>
                        <td><span class="badge badge-success">AVAILABLE</span></td>
                        <td>Item is on the shelf and ready to be checked out at the counter.</td>
                    </tr>
                    <tr>
                        <td><strong>Circulated</strong></td>
                        <td><span class="badge badge-warning">CIRCULATED</span></td>
                        <td>Item is currently on loan. OPAC displays the return due date.</td>
                    </tr>
                    <tr>
                        <td><strong>Reference</strong></td>
                        <td><span class="badge badge-secondary">REFERENCE ONLY</span></td>
                        <td>Item is for in-library reading only (Red Spot / Current Periodicals). Counter checkout is blocked.</td>
                    </tr>
                    <tr>
                        <td><strong>Lost</strong></td>
                        <td><span class="badge badge-danger">LOST</span></td>
                        <td>Item was reported lost. It is excluded from active loans and shelf availability.</td>
                    </tr>
                    <tr>
                        <td><strong>Damaged</strong></td>
                        <td><span class="badge badge-danger">DAMAGED</span></td>
                        <td>Item is undergoing repair, conservation, or rebinding.</td>
                    </tr>
                    <tr>
                        <td><strong>Weeded</strong></td>
                        <td><span class="badge badge-secondary">WEEDED</span></td>
                        <td>Item has been officially discarded or withdrawn from the collection.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <?php include_once '../includes/footerbar.php';?>
    </div>
</body>
</html>
