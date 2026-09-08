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
    <title><?= htmlspecialchars($product_name ?? 'My Library');?> : Quick Reference Cards &amp; Cheat Sheets</title>
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
            <span class="fw-bold text-dark">Quick Reference Cards &amp; Cheat Sheets</span>
        </div>

        <!-- Section 1: Circulation Desk Cheat Sheet -->
        <div class="guide-section">
            <div class="guide-section-title">
                <i class="fa-solid fa-desktop"></i> Circulation Desk Daily Cheat Sheet
            </div>

            <table class="table-guide">
                <thead>
                    <tr>
                        <th style="width: 200px;">Counter Task</th>
                        <th style="width: 220px;">Toolbar Screen</th>
                        <th>Standard Operating Procedure (SOP)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Borrow Book (Charge)</strong></td>
                        <td><a href="../admin/charge.php" class="ui-badge">Charge / Loan</a></td>
                        <td>1. Scan Patron Barcode/IC &rarr; 2. Scan Book Accession Barcode &rarr; 3. Verify on-screen confirmation banner.</td>
                    </tr>
                    <tr>
                        <td><strong>Return Book (Discharge)</strong></td>
                        <td><a href="../admin/discharge.php" class="ui-badge">Discharge</a></td>
                        <td>1. Scan Book Accession Barcode &rarr; 2. If late, note fine and proceed to Fines settlement.</td>
                    </tr>
                    <tr>
                        <td><strong>Collect Overdue Fine</strong></td>
                        <td><a href="../admin/paysearch.php" class="ui-badge">Fines</a></td>
                        <td>1. Scan Patron IC &rarr; 2. Check items being paid &rarr; 3. Enter cash received &rarr; 4. Click "Submit Payment".</td>
                    </tr>
                    <tr>
                        <td><strong>Register Walk-In Member</strong></td>
                        <td><a href="../admin/adduser.php" class="ui-badge">Users &rarr; Add User</a></td>
                        <td>1. Enter IC / Matric &rarr; 2. Fill Name &amp; Address / Dept &rarr; 3. Choose Role (PATRON) &rarr; 4. Click "Add User Record" (default password is 1).</td>
                    </tr>
                    <tr>
                        <td><strong>Lobby Self-Registration</strong></td>
                        <td><a href="../admin/qr_selfreg.php" class="ui-badge">Users &rarr; Own Registration With QR</a></td>
                        <td>1. Open screen on counter tablet &rarr; 2. Patron scans with phone &rarr; 3. Instant membership onboarding.</td>
                    </tr>
                    <tr>
                        <td><strong>Print Spine Barcode Labels</strong></td>
                        <td><a href="../admin/qr_bprintrange.php" class="ui-badge">Print Labels</a></td>
                        <td>1. Enter Accession Start &amp; End numbers &rarr; 2. Click "Display &amp; Print Barcodes" &rarr; 3. Print on label sheets.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Section 2: MARC21 Fast Field Matrix -->
        <div class="guide-section">
            <div class="guide-section-title">
                <i class="fa-solid fa-tags"></i> MARC 21 Fast Field Reference
            </div>

            <table class="table-guide">
                <thead>
                    <tr>
                        <th style="width: 100px;">Tag</th>
                        <th style="width: 180px;">Field Name</th>
                        <th style="width: 200px;">What to Enter</th>
                        <th>Example Value</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><span class="ui-badge">020</span></td>
                        <td>ISBN</td>
                        <td>International Standard Book Number</td>
                        <td><code>9789674608767</code></td>
                    </tr>
                    <tr>
                        <td><span class="ui-badge">022</span></td>
                        <td>ISSN</td>
                        <td>International Standard Serial Number</td>
                        <td><code>0128-0988</code></td>
                    </tr>
                    <tr>
                        <td><span class="ui-badge">041</span></td>
                        <td>Language</td>
                        <td>Primary language code</td>
                        <td><code>Bahasa Malaysia (zsm)</code>, <code>English (eng)</code></td>
                    </tr>
                    <tr>
                        <td><span class="ui-badge">090</span></td>
                        <td>Call Number</td>
                        <td>Dewey Classification &amp; Local Cutter</td>
                        <td><code>371.334 A38 2024</code></td>
                    </tr>
                    <tr>
                        <td><span class="ui-badge">100</span></td>
                        <td>Personal Author</td>
                        <td>Author Name, Dates</td>
                        <td><code>Ahmad bin Faizal, 1978-</code></td>
                    </tr>
                    <tr>
                        <td><span class="ui-badge">245</span></td>
                        <td>Title Statement</td>
                        <td>Title, Subtitle, Statement of responsibility</td>
                        <td><code>Teknologi pendidikan : teori dan praktis / oleh Ahmad bin Faizal.</code></td>
                    </tr>
                    <tr>
                        <td><span class="ui-badge">250</span></td>
                        <td>Edition</td>
                        <td>Edition statement</td>
                        <td><code>Edisi kedua</code> or <code>2nd ed.</code></td>
                    </tr>
                    <tr>
                        <td><span class="ui-badge">264</span></td>
                        <td>Publication</td>
                        <td>Place, Publisher, Year</td>
                        <td><code>Tanjong Malim : Penerbit UPSI, 2024.</code></td>
                    </tr>
                    <tr>
                        <td><span class="ui-badge">300</span></td>
                        <td>Physical Description</td>
                        <td>Pages, Illustrations, Height</td>
                        <td><code>xvi, 280 hlm. : il. berwarna ; 25 cm.</code></td>
                    </tr>
                    <tr>
                        <td><span class="ui-badge">490</span></td>
                        <td>Series</td>
                        <td>Series title, Volume</td>
                        <td><code>Siri Pengajian Pendidikan ; bil. 4</code></td>
                    </tr>
                    <tr>
                        <td><span class="ui-badge">500</span></td>
                        <td>General Notes</td>
                        <td>General bibliographic note</td>
                        <td><code>Termasuk indeks dan bibliografi (hlm. 270-278).</code></td>
                    </tr>
                    <tr>
                        <td><span class="ui-badge">505</span></td>
                        <td>Contents Note</td>
                        <td>Table of contents / chapter breakdown</td>
                        <td><code>Bab 1. Pengenalan -- Bab 2. Reka Bentuk -- Bab 3. Penilaian.</code></td>
                    </tr>
                    <tr>
                        <td><span class="ui-badge">650</span></td>
                        <td>Subject Headings</td>
                        <td>Topical subject headings</td>
                        <td><code>Educational technology -- Malaysia.</code></td>
                    </tr>
                    <tr>
                        <td><span class="ui-badge">710</span></td>
                        <td>Corporate Author</td>
                        <td>Organization or university author</td>
                        <td><code>Universiti Pendidikan Sultan Idris</code></td>
                    </tr>
                    <tr>
                        <td><span class="ui-badge">852</span></td>
                        <td>Location</td>
                        <td>Location, Collection, Shelf number</td>
                        <td><code>Perpustakaan Tuanku Bainun, Aras 2, Rak 14B</code></td>
                    </tr>
                    <tr>
                        <td><span class="ui-badge">856</span></td>
                        <td>Electronic Access</td>
                        <td>Web URL or DOI link</td>
                        <td><code>https://pustaka.upsi.edu.my/resource</code></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Section 3: Dewey Decimal 1000 Summary -->
        <div class="guide-section">
            <div class="guide-section-title">
                <i class="fa-solid fa-sitemap"></i> Dewey Decimal Classification (DDC) 1000 Summary
            </div>

            <div class="row g-2">
                <div class="col-md-4 mb-2">
                    <div class="p-2 border rounded bg-light" style="font-size: 0.85rem;">
                        <strong>000 - Computer Science, Information &amp; General Works</strong><br/>
                        <span class="text-muted">004 Computer science, 020 Library &amp; info sciences.</span>
                    </div>
                </div>
                <div class="col-md-4 mb-2">
                    <div class="p-2 border rounded bg-light" style="font-size: 0.85rem;">
                        <strong>100 - Philosophy &amp; Psychology</strong><br/>
                        <span class="text-muted">150 Psychology, 170 Ethics &amp; moral philosophy.</span>
                    </div>
                </div>
                <div class="col-md-4 mb-2">
                    <div class="p-2 border rounded bg-light" style="font-size: 0.85rem;">
                        <strong>200 - Religion</strong><br/>
                        <span class="text-muted">297 Islam, 220 Bible &amp; Christianity, 290 Other religions.</span>
                    </div>
                </div>
                <div class="col-md-4 mb-2">
                    <div class="p-2 border rounded bg-light" style="font-size: 0.85rem;">
                        <strong>300 - Social Sciences</strong><br/>
                        <span class="text-muted">320 Political science, 330 Economics, 370 Education.</span>
                    </div>
                </div>
                <div class="col-md-4 mb-2">
                    <div class="p-2 border rounded bg-light" style="font-size: 0.85rem;">
                        <strong>400 - Language</strong><br/>
                        <span class="text-muted">420 English, 499.28 Bahasa Melayu, 492.7 Arabic.</span>
                    </div>
                </div>
                <div class="col-md-4 mb-2">
                    <div class="p-2 border rounded bg-light" style="font-size: 0.85rem;">
                        <strong>500 - Pure Sciences</strong><br/>
                        <span class="text-muted">510 Mathematics, 530 Physics, 570 Biology.</span>
                    </div>
                </div>
                <div class="col-md-4 mb-2">
                    <div class="p-2 border rounded bg-light" style="font-size: 0.85rem;">
                        <strong>600 - Technology &amp; Applied Sciences</strong><br/>
                        <span class="text-muted">610 Medicine &amp; Health, 620 Engineering, 650 Management.</span>
                    </div>
                </div>
                <div class="col-md-4 mb-2">
                    <div class="p-2 border rounded bg-light" style="font-size: 0.85rem;">
                        <strong>700 - Arts &amp; Recreation</strong><br/>
                        <span class="text-muted">720 Architecture, 780 Music, 796 Sports &amp; Games.</span>
                    </div>
                </div>
                <div class="col-md-4 mb-2">
                    <div class="p-2 border rounded bg-light" style="font-size: 0.85rem;">
                        <strong>800 - Literature</strong><br/>
                        <span class="text-muted">820 English literature, 899.283 Sastera Melayu.</span>
                    </div>
                </div>
                <div class="col-md-12 mb-2">
                    <div class="p-2 border rounded bg-light" style="font-size: 0.85rem;">
                        <strong>900 - History &amp; Geography</strong> &mdash; <span class="text-muted">910 Geography &amp; Travel, 920 Biography &amp; Genealogy, 959.5 Malaysian History.</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 4: Operational Troubleshooting FAQ -->
        <div class="guide-section">
            <div class="guide-section-title">
                <i class="fa-solid fa-circle-question"></i> Frequent Administrative Questions &amp; Troubleshooting
            </div>

            <div class="accordion" id="faqAccordion">
                <div class="mb-3 p-3 bg-light border rounded">
                    <h6 class="fw-bold text-primary mb-1">Q: A patron is locked out with an "Access blocked: Your IP is locked for 1 week" message. How to resolve?</h6>
                    <p class="text-muted mb-0" style="font-size: 0.875rem;">
                        <strong>Solution:</strong> A Superadmin opens the admin dashboard &rarr; clicks <strong>Blocked IPs</strong> from the toolbar &rarr; finds the patron's row &rarr; clicks <strong>Unblock</strong>. The patron can immediately log in again.
                    </p>
                </div>

                <div class="mb-3 p-3 bg-light border rounded">
                    <h6 class="fw-bold text-primary mb-1">Q: The counter screen warns: "Loan restricted: Item is designated as Reference Only". How to allow borrowing?</h6>
                    <p class="text-muted mb-0" style="font-size: 0.875rem;">
                        <strong>Solution:</strong> Open the book's details page &rarr; click <strong>Edit Copy</strong> on that specific copy &rarr; change <strong>Loan Restriction</strong> from <code>Reference Only / Current Issue (Non-circulating)</code> to <code>Circulating (Standard Loan Allowed)</code> &rarr; click <strong>Save Changes</strong>.
                    </p>
                </div>

                <div class="mb-3 p-3 bg-light border rounded">
                    <h6 class="fw-bold text-primary mb-1">Q: A patron forgot their password and cannot access My Account. How to reset it?</h6>
                    <p class="text-muted mb-0" style="font-size: 0.875rem;">
                        <strong>Solution:</strong> Superadmins open <strong>Users</strong> from the toolbar &rarr; find the patron's row &rarr; click <strong>Reset Pwd</strong>. The password resets to default <code>1</code>. The patron logs in with <code>1</code> and sets their own new password in My Account.
                    </p>
                </div>

                <div class="mb-0 p-3 bg-light border rounded">
                    <h6 class="fw-bold text-primary mb-1">Q: New catalog entries or fine payments are not showing up in statistical reports. How to fix?</h6>
                    <p class="text-muted mb-0" style="font-size: 0.875rem;">
                        <strong>Solution:</strong> Superadmins open <strong>Reports</strong> from the toolbar &rarr; go to <strong>Tab 6 (Cache Manager)</strong> &rarr; click the red <strong>Purge All Stats Cache</strong> button. The latest real-time numbers will recompile instantly.
                    </p>
                </div>
            </div>
        </div>

        <?php include_once '../includes/footerbar.php';?>
    </div>
</body>
</html>
