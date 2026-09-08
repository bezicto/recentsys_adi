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
    <title><?= htmlspecialchars($product_name ?? 'My Library');?> : Serials &amp; Periodicals User Guide</title>
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
            <span class="fw-bold text-dark">Serials &amp; Periodicals Management Guide</span>
        </div>

        <!-- Section 1: Overview -->
        <div class="guide-section">
            <div class="guide-section-title">
                <i class="fa-solid fa-newspaper"></i> Serials &amp; Periodicals Management (Kardex)
            </div>
            <p><?= htmlspecialchars($product_name ?? 'My Library');?> provides a dedicated Kardex management dashboard for journals, magazines, periodicals, and ongoing serial publications through the <strong>Serials</strong> toolbar button.</p>

            <div class="quick-card-grid">
                <div class="card p-3 bg-light border">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="fa-solid fa-layer-group text-primary fa-lg"></i>
                        <h6 class="fw-bold mb-0">Serials Overview Dashboard</h6>
                    </div>
                    <p class="text-muted mb-0" style="font-size: 0.875rem;">Visual summary cards showing Total Serial Titles, Total Registered Issues, Current Reference Issues, and Circulating Issues.</p>
                </div>

                <div class="card p-3 bg-light border">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="fa-solid fa-bolt text-warning fa-lg"></i>
                        <h6 class="fw-bold mb-0">Instant ISSN Auto-Fill</h6>
                    </div>
                    <p class="text-muted mb-0" style="font-size: 0.875rem;">Enter an 8-digit ISSN to automatically retrieve journal titles, publishers, and standard serial classification numbers.</p>
                </div>

                <div class="card p-3 bg-light border">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="fa-solid fa-bookmark text-success fa-lg"></i>
                        <h6 class="fw-bold mb-0">Current vs. Bound Volumes</h6>
                    </div>
                    <p class="text-muted mb-0" style="font-size: 0.875rem;">Easily mark new arrivals as "Reference Only (In-Library Use)" for display racks, and release older bound volumes for general loan.</p>
                </div>
            </div>
        </div>

        <!-- Section 2: Adding a New Serial Title -->
        <div class="guide-section">
            <div class="guide-section-title">
                <i class="fa-solid fa-plus-circle"></i> Registering a New Serial Title
            </div>

            <div class="workflow-step">
                <div class="step-number">1</div>
                <div class="step-content">
                    <h5>Enter the ISSN in the Add Record Screen</h5>
                    <p class="mb-0 text-muted">Open <strong>Add Record</strong>, choose Material Type = <strong>Serial</strong>, and enter the 8-digit ISSN (e.g. <code>0028-0836</code> for <em>Nature</em> or <code>0036-8075</code> for <em>Science</em>).</p>
                </div>
            </div>

            <div class="workflow-step">
                <div class="step-number">2</div>
                <div class="step-content">
                    <h5>Click "Fetch ISSN"</h5>
                    <p class="mb-0 text-muted">The system automatically queries bibliographic repositories (including Crossref and Open Library) to pull the full journal title, publisher name, publication details, and subject categorization.</p>
                </div>
            </div>

            <div class="workflow-step">
                <div class="step-number">3</div>
                <div class="step-content">
                    <h5>Save the Serial Title</h5>
                    <p class="mb-0 text-muted">Click <strong>Register Record</strong> (or <strong>Register Record and Auto Create 1 Copy</strong>). The serial title will now appear in your Serials Dashboard ready for incoming issues to be checked in.</p>
                </div>
            </div>
        </div>

        <!-- Section 3: Registering Incoming Issues -->
        <div class="guide-section">
            <div class="guide-section-title">
                <i class="fa-solid fa-folder-plus"></i> Checking In &amp; Registering Incoming Issues
            </div>
            <p>Whenever a new periodical shipment arrives at the library, open the <strong>Serials</strong> dashboard, locate the title, and click <strong>Add Issue</strong> (or <strong>Check-in First Issue</strong> if no issues have been registered yet):</p>

            <table class="table-guide">
                <thead>
                    <tr>
                        <th style="width: 220px;">Form Field</th>
                        <th>Guidance for Periodicals Staff</th>
                        <th style="width: 200px;">Example Entry</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Number of Copies to Generate</strong></td>
                        <td>How many copies of this exact issue were received (dropdown 1 to 20). Sequential barcodes are assigned automatically.</td>
                        <td><code>1</code> (standard subscription)</td>
                    </tr>
                    <tr>
                        <td><strong>Volume / Jilid</strong></td>
                        <td>The volume designation of the serial.</td>
                        <td><code>Vol. 45</code> or <code>Jilid 12</code></td>
                    </tr>
                    <tr>
                        <td><strong>Issue / No. / Month</strong></td>
                        <td>The issue number, part name, or month.</td>
                        <td><code>No. 3</code>, <code>Isu 1</code>, or <code>March 2024</code></td>
                    </tr>
                    <tr>
                        <td><strong>Publication Year</strong></td>
                        <td>The 4-digit publication year.</td>
                        <td><code>2024</code></td>
                    </tr>
                    <tr>
                        <td><strong>Circulation Restriction</strong></td>
                        <td>
                            Choose from the dropdown:<br/>
                            &bull; <code>Reference Only / Current Issue (Non-circulating)</code> to protect current display issues.<br/>
                            &bull; <code>Circulating (Standard Loan)</code> for older bound issues permitted for patron borrowing.
                        </td>
                        <td><code>Reference Only...</code> (for new issues)<br/><code>Circulating...</code> (for back issues)</td>
                    </tr>
                    <tr>
                        <td><strong>Default Availability Status</strong></td>
                        <td>Status code (e.g. <code>AVAILABLE</code> or <code>REFERENCE</code>).</td>
                        <td><code>AVAILABLE</code></td>
                    </tr>
                    <tr>
                        <td><strong>Financial Details</strong></td>
                        <td>Price per unit, Supplier/Vendor name, and Purchase Invoice Number for inventory audit.</td>
                        <td><code>50.00</code>, <code>Kinokuniya</code>, <code>INV-2024-8841</code></td>
                    </tr>
                </tbody>
            </table>
            <p class="mt-2 text-muted small"><i class="fa-solid fa-circle-info text-primary me-1"></i> Click <strong>Insert</strong> to commit the generated issue copies and accession barcodes to the catalog.</p>
        </div>

        <!-- Section 4: Serials Filtering & Auditing -->
        <div class="guide-section">
            <div class="guide-section-title">
                <i class="fa-solid fa-filter"></i> Serials Dashboard Filtering Tools
            </div>
            <p>The Serials Dashboard provides a <strong>Filter Dropdown</strong> alongside text search and sorting controls:</p>
            <ul class="text-muted mb-0" style="font-size: 0.9rem;">
                <li><strong>Filter: All Serials:</strong> View the entire serials and periodicals collection.</li>
                <li><strong>Has Registered Issues:</strong> Displays serials that currently have physical issues on the shelves.</li>
                <li><strong>No Issues Yet (Pending Check-in):</strong> Highlights newly subscribed journal titles that are awaiting their first physical issue.</li>
                <li><strong>Has Available Issues:</strong> Shows titles with copies currently available for circulation.</li>
                <li><strong>Has Reference Only Issues:</strong> Displays all current uncirculated issues placed on the display racks.</li>
                <li><strong>Has On-Loan Issues:</strong> Shows serials that currently have issues borrowed by patrons.</li>
            </ul>
        </div>

        <?php include_once '../includes/footerbar.php';?>
    </div>
</body>
</html>
