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
    <title><?= htmlspecialchars($product_name ?? 'My Library');?> : Library Staff &amp; Admin User Guide</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="../<?= htmlspecialchars($mini_icon_path ?? '', ENT_QUOTES, 'UTF-8');?>" rel="icon" type="image/png" />
    <link href="../assets/styles/style.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
    <script>
        function filterGuideCards() {
            var input = document.getElementById('searchGuide');
            var filter = input.value.toLowerCase();
            var cards = document.getElementsByClassName('quick-card');
            var foundCount = 0;
            for (var i = 0; i < cards.length; i++) {
                var text = cards[i].innerText.toLowerCase();
                if (text.indexOf(filter) > -1) {
                    cards[i].style.display = "flex";
                    foundCount++;
                } else {
                    cards[i].style.display = "none";
                }
            }
            var noRes = document.getElementById('noSearchResults');
            if (noRes) {
                noRes.style.display = (foundCount === 0) ? 'block' : 'none';
            }
        }
    </script>
</head>
<body>
    <?php include_once '../includes/loggedinfo.php';?>

    <div class="app-container">
        <?php include_once 'nav_header.php';?>

        <!-- Search Bar -->
        <div class="card mb-4">
            <div class="card-body p-3">
                <div class="d-flex align-items-center gap-3">
                    <i class="fa-solid fa-magnifying-glass text-muted fa-lg"></i>
                    <input type="text" id="searchGuide" onkeyup="filterGuideCards();" class="form-control border-0 shadow-none" placeholder="Search guide topics, desk procedures, MARC cataloging, fines, reports..." autofocus style="font-size: 1.05rem;" />
                    <span class="badge badge-secondary">Quick Filter</span>
                </div>
            </div>
        </div>

        <div id="noSearchResults" style="display:none;" class="alert alert-warning text-center mb-4">
            <i class="fa-solid fa-triangle-exclamation me-2"></i> No documentation topics found matching your search. Try different keywords.
        </div>

        <!-- Quick Access Module Cards -->
        <h2 class="h5 fw-bold mb-3 text-dark"><i class="fa-solid fa-compass me-2 text-primary"></i>Admin Documentation Modules</h2>
        <div class="quick-card-grid">

            <a href="cataloging.php" class="quick-card">
                <div class="quick-card-header">
                    <div class="quick-card-icon" style="background: linear-gradient(135deg, #0284c7, #0369a1);">
                        <i class="fa-solid fa-book"></i>
                    </div>
                    <div>
                        <h3 class="quick-card-title">Cataloging &amp; MARC21</h3>
                        <span class="badge badge-primary" style="font-size: 0.7rem;">Add &amp; Edit Records</span>
                    </div>
                </div>
                <div class="quick-card-desc">
                    Learn how to register reading materials, use the 1-click Fetch ISBN / Fetch ISSN buttons, fill standard MARC cataloging fields, select approved subject headings, upload book covers and PDF documents, manage physical copies, and print spine labels.
                </div>
                <div class="quick-card-footer text-primary">
                    <span>Open Module Guide &rarr;</span>
                    <span class="badge badge-secondary">All Staff</span>
                </div>
            </a>

            <a href="circulation.php" class="quick-card">
                <div class="quick-card-header">
                    <div class="quick-card-icon" style="background: linear-gradient(135deg, #059669, #047857);">
                        <i class="fa-solid fa-arrows-rotate"></i>
                    </div>
                    <div>
                        <h3 class="quick-card-title">Circulation &amp; Desk</h3>
                        <span class="badge badge-primary" style="font-size: 0.7rem;">Charge &amp; Discharge</span>
                    </div>
                </div>
                <div class="quick-card-desc">
                    Daily counter operations: Checking out books (Charging) with barcode and camera scanners, returning books (Discharging), handling borrowing quotas, managing Reference-Only protections, and patron self-service online loan renewals.
                </div>
                <div class="quick-card-footer text-success">
                    <span>Open Module Guide &rarr;</span>
                    <span class="badge badge-secondary">All Staff</span>
                </div>
            </a>

            <a href="fines_calendar.php" class="quick-card">
                <div class="quick-card-header">
                    <div class="quick-card-icon" style="background: linear-gradient(135deg, #d97706, #b45309);">
                        <i class="fa-solid fa-calendar-days"></i>
                    </div>
                    <div>
                        <h3 class="quick-card-title">Fines, Calendar &amp; Cashier</h3>
                        <span class="badge badge-primary" style="font-size: 0.7rem;">Fines &amp; Holidays</span>
                    </div>
                </div>
                <div class="quick-card-desc">
                    Configuring tiered fine rates per material type, setting open working days and gazetted holidays, understanding automatic due date calendar shifting, collecting overdue payments at the desk, applying approved waivers/discounts, and generating official digital receipt IDs.
                </div>
                <div class="quick-card-footer text-warning">
                    <span>Open Module Guide &rarr;</span>
                    <span class="badge badge-secondary">All Staff</span>
                </div>
            </a>

            <a href="serials.php" class="quick-card">
                <div class="quick-card-header">
                    <div class="quick-card-icon" style="background: linear-gradient(135deg, #7c3aed, #6d28d9);">
                        <i class="fa-solid fa-newspaper"></i>
                    </div>
                    <div>
                        <h3 class="quick-card-title">Serials &amp; Periodicals</h3>
                        <span class="badge badge-primary" style="font-size: 0.7rem;">Kardex Dashboard</span>
                    </div>
                </div>
                <div class="quick-card-desc">
                    Managing journals, magazines, and periodicals: Using ISSN auto-fill, checking in incoming issues (Volume, Issue number, Year/Season, and Invoice), designating Current Issues as In-Library Reference, and releasing bound volumes for borrowing.
                </div>
                <div class="quick-card-footer" style="color: #7c3aed;">
                    <span>Open Module Guide &rarr;</span>
                    <span class="badge badge-secondary">All Staff</span>
                </div>
            </a>

            <a href="users_security.php" class="quick-card">
                <div class="quick-card-header">
                    <div class="quick-card-icon" style="background: linear-gradient(135deg, #dc2626, #b91c1c);">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                    <div>
                        <h3 class="quick-card-title">Users &amp; Security Controls</h3>
                        <span class="badge badge-primary" style="font-size: 0.7rem;">Accounts &amp; Security</span>
                    </div>
                </div>
                <div class="quick-card-desc">
                    Registering staff and patron accounts, resetting forgotten passwords, managing member categories, unblocking IP addresses locked out by the security system, setting up the Lobby Self-Registration QR Kiosk, and printing plastic membership cards.
                </div>
                <div class="quick-card-footer text-danger">
                    <span>Open Module Guide &rarr;</span>
                    <span class="badge badge-danger">Superadmin</span>
                </div>
            </a>

            <a href="reports_analytics.php" class="quick-card">
                <div class="quick-card-header">
                    <div class="quick-card-icon" style="background: linear-gradient(135deg, #4f46e5, #4338ca);">
                        <i class="fa-solid fa-chart-pie"></i>
                    </div>
                    <div>
                        <h3 class="quick-card-title">Reports &amp; Analytics</h3>
                        <span class="badge badge-primary" style="font-size: 0.7rem;">6 Analytics Reports</span>
                    </div>
                </div>
                <div class="quick-card-desc">
                    Exploring institutional statistics: Staff cataloging productivity, material collection breakdown, search keyword analytics, circulation and loan usage trends, fine revenue audits, and printing executive reports for accreditation.
                </div>
                <div class="quick-card-footer" style="color: #4f46e5;">
                    <span>Open Module Guide &rarr;</span>
                    <span class="badge badge-danger">Superadmin</span>
                </div>
            </a>

            <a href="system_config.php" class="quick-card">
                <div class="quick-card-header">
                    <div class="quick-card-icon" style="background: linear-gradient(135deg, #475569, #334155);">
                        <i class="fa-solid fa-sliders"></i>
                    </div>
                    <div>
                        <h3 class="quick-card-title">System &amp; Policies</h3>
                        <span class="badge badge-primary" style="font-size: 0.7rem;">Settings &amp; Rules</span>
                    </div>
                </div>
                <div class="quick-card-desc">
                    Configuring library material types, managing the controlled subject headings thesaurus, setting borrowing durations and quotas in Loan Eligibility, and routine administrative checklists for backup and library maintenance.
                </div>
                <div class="quick-card-footer text-dark">
                    <span>Open Module Guide &rarr;</span>
                    <span class="badge badge-danger">Superadmin</span>
                </div>
            </a>

            <a href="quick_reference.php" class="quick-card">
                <div class="quick-card-header">
                    <div class="quick-card-icon" style="background: linear-gradient(135deg, #0284c7, #2563eb);">
                        <i class="fa-solid fa-bolt"></i>
                    </div>
                    <div>
                        <h3 class="quick-card-title">Quick Reference Cards</h3>
                        <span class="badge badge-primary" style="font-size: 0.7rem;">Cheat Sheets &amp; FAQs</span>
                    </div>
                </div>
                <div class="quick-card-desc">
                    Printable circulation desk cheat sheet, Dewey Decimal Classification 1000 summary table, standard MARC tag fast lookup matrix, and answers to frequently asked administrative questions.
                </div>
                <div class="quick-card-footer text-primary">
                    <span>Open Module Guide &rarr;</span>
                    <span class="badge badge-secondary">All Staff</span>
                </div>
            </a>

        </div>

        <!-- Role Permissions Matrix -->
        <div class="guide-section">
            <div class="guide-section-title">
                <i class="fa-solid fa-user-lock"></i> User Roles &amp; Responsibilities Matrix
            </div>
            <p class="text-muted"><?= htmlspecialchars($product_name ?? 'My Library');?> provides distinct role levels to ensure appropriate staff and patron access across the system:</p>
            
            <table class="table-guide">
                <thead>
                    <tr>
                        <th>Library Functional Area</th>
                        <th class="text-center">Superadmin</th>
                        <th class="text-center">Staff Librarian</th>
                        <th class="text-center">Patron / Member</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>OPAC Search &amp; Document Viewing</strong></td>
                        <td class="text-center text-success"><i class="fa-solid fa-circle-check"></i> Full Access</td>
                        <td class="text-center text-success"><i class="fa-solid fa-circle-check"></i> Full Access</td>
                        <td class="text-center text-success"><i class="fa-solid fa-circle-check"></i> Full Access</td>
                    </tr>
                    <tr>
                        <td><strong>Patron Portal (My Account)</strong></td>
                        <td class="text-center text-success"><i class="fa-solid fa-circle-check"></i> Yes</td>
                        <td class="text-center text-success"><i class="fa-solid fa-circle-check"></i> Yes</td>
                        <td class="text-center text-success"><i class="fa-solid fa-circle-check"></i> View loans &amp; renew</td>
                    </tr>
                    <tr>
                        <td><strong>Add &amp; Edit Catalog Records</strong></td>
                        <td class="text-center text-success"><i class="fa-solid fa-circle-check"></i> Full Access</td>
                        <td class="text-center text-success"><i class="fa-solid fa-circle-check"></i> Full Access</td>
                        <td class="text-center text-danger"><i class="fa-solid fa-circle-xmark"></i> No Access</td>
                    </tr>
                    <tr>
                        <td><strong>Item Copies &amp; Status Changes</strong></td>
                        <td class="text-center text-success"><i class="fa-solid fa-circle-check"></i> Full Access</td>
                        <td class="text-center text-success"><i class="fa-solid fa-circle-check"></i> Add &amp; Edit Status</td>
                        <td class="text-center text-danger"><i class="fa-solid fa-circle-xmark"></i> No Access</td>
                    </tr>
                    <tr>
                        <td><strong>Circulation: Borrow &amp; Return</strong></td>
                        <td class="text-center text-success"><i class="fa-solid fa-circle-check"></i> Full Access</td>
                        <td class="text-center text-success"><i class="fa-solid fa-circle-check"></i> Full Access</td>
                        <td class="text-center text-danger"><i class="fa-solid fa-circle-xmark"></i> No Access</td>
                    </tr>
                    <tr>
                        <td><strong>Fine Payments &amp; Fee Waivers</strong></td>
                        <td class="text-center text-success"><i class="fa-solid fa-circle-check"></i> Full (with discount)</td>
                        <td class="text-center text-success"><i class="fa-solid fa-circle-check"></i> Collect Payments</td>
                        <td class="text-center text-danger"><i class="fa-solid fa-circle-xmark"></i> No Access</td>
                    </tr>
                    <tr>
                        <td><strong>Serials &amp; Periodicals (Kardex)</strong></td>
                        <td class="text-center text-success"><i class="fa-solid fa-circle-check"></i> Full Access</td>
                        <td class="text-center text-success"><i class="fa-solid fa-circle-check"></i> Full Access</td>
                        <td class="text-center text-danger"><i class="fa-solid fa-circle-xmark"></i> No Access</td>
                    </tr>
                    <tr>
                        <td><strong>Barcode &amp; Label Range Printing</strong></td>
                        <td class="text-center text-success"><i class="fa-solid fa-circle-check"></i> Full Access</td>
                        <td class="text-center text-success"><i class="fa-solid fa-circle-check"></i> Full Access</td>
                        <td class="text-center text-danger"><i class="fa-solid fa-circle-xmark"></i> No Access</td>
                    </tr>
                    <tr>
                        <td><strong>Statistical Reports &amp; Analytics</strong></td>
                        <td class="text-center text-success"><i class="fa-solid fa-circle-check"></i> All 6 Reports</td>
                        <td class="text-center text-muted"><i class="fa-solid fa-circle-info"></i> "My Input" Only</td>
                        <td class="text-center text-danger"><i class="fa-solid fa-circle-xmark"></i> No Access</td>
                    </tr>
                    <tr>
                        <td><strong>User Accounts &amp; Password Resets</strong></td>
                        <td class="text-center text-success"><i class="fa-solid fa-circle-check"></i> Full Access</td>
                        <td class="text-center text-danger"><i class="fa-solid fa-circle-xmark"></i> No Access</td>
                        <td class="text-center text-danger"><i class="fa-solid fa-circle-xmark"></i> No Access</td>
                    </tr>
                    <tr>
                        <td><strong>Security Lockout &amp; IP Unblocking</strong></td>
                        <td class="text-center text-success"><i class="fa-solid fa-circle-check"></i> Full Access</td>
                        <td class="text-center text-danger"><i class="fa-solid fa-circle-xmark"></i> No Access</td>
                        <td class="text-center text-danger"><i class="fa-solid fa-circle-xmark"></i> No Access</td>
                    </tr>
                    <tr>
                        <td><strong>Library Holidays &amp; Loan Rules</strong></td>
                        <td class="text-center text-success"><i class="fa-solid fa-circle-check"></i> Full Access</td>
                        <td class="text-center text-danger"><i class="fa-solid fa-circle-xmark"></i> No Access</td>
                        <td class="text-center text-danger"><i class="fa-solid fa-circle-xmark"></i> No Access</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- High-Level Operational Overview -->
        <div class="guide-section">
            <div class="guide-section-title">
                <i class="fa-solid fa-diagram-project"></i> Core Operational Workflows
            </div>
            
            <div class="row g-3">
                <div class="col-md-6 mb-3">
                    <div class="card h-100 p-3 bg-light border">
                        <h5 class="fw-bold text-primary mb-2"><i class="fa-solid fa-book-medical me-1"></i> Cataloging Workflow</h5>
                        <ol class="mb-0 ps-3 text-muted" style="line-height: 1.65; font-size: 0.9rem;">
                            <li><strong>Enter ISBN / ISSN:</strong> In the Add Record form, input the identifier and click <strong>Fetch ISBN</strong> (for books) or <strong>Fetch ISSN</strong> (for serials) to automatically retrieve title, author, publisher, and call numbers.</li>
                            <li><strong>Verify MARC Fields:</strong> Review Title, Author, Call Number (DDC), and Publication details.</li>
                            <li><strong>Select Subject Headings:</strong> Click the <strong>[...]</strong> button next to the subject field to choose approved headings from the library thesaurus (or <strong>+New</strong> to add one on the fly).</li>
                            <li><strong>Attach Media:</strong> Upload a book cover image (JPG) and full-text PDF document if available.</li>
                            <li><strong>Add Inventory Copies:</strong> Enter how many physical copies were acquired to generate accession barcodes.</li>
                            <li><strong>Print Labels:</strong> Use the Print Labels screen to print spine labels for the shelves.</li>
                        </ol>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <div class="card h-100 p-3 bg-light border">
                        <h5 class="fw-bold text-success mb-2"><i class="fa-solid fa-arrow-right-arrow-left me-1"></i> Circulation &amp; Counter Workflow</h5>
                        <ol class="mb-0 ps-3 text-muted" style="line-height: 1.65; font-size: 0.9rem;">
                            <li><strong>Identify Patron:</strong> Scan the patron's membership card barcode in the <strong>Charge / Loan</strong> screen.</li>
                            <li><strong>Verify Quota:</strong> The system automatically verifies the patron is eligible and has not exceeded their borrowing limit.</li>
                            <li><strong>Scan Book:</strong> Scan the item barcode. The loan is registered and the return due date is automatically set past any closed days or holidays.</li>
                            <li><strong>Return Book:</strong> When returned, scan the book barcode in the <strong>Discharge</strong> screen to immediately mark it available.</li>
                            <li><strong>Settle Overdue Fines:</strong> If returned late, open <strong>Fines</strong> to collect payment, apply any authorized discount, and confirm the official digital receipt.</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <?php include_once '../includes/footerbar.php';?>
    </div>
</body>
</html>
