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
    <title><?= htmlspecialchars($product_name ?? 'My Library');?> : Cataloging &amp; MARC21 User Guide</title>
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
            <span class="fw-bold text-dark">Cataloging &amp; MARC21 Operations Guide</span>
        </div>

        <!-- Section 1: Overview -->
        <div class="guide-section">
            <div class="guide-section-title">
                <i class="fa-solid fa-book-open-reader"></i> Bibliographic Cataloging Overview
            </div>
            <p><?= htmlspecialchars($product_name ?? 'My Library');?> implements an industry-standard <strong>MARC 21 Bibliographic Format</strong> to organize, describe, and index all library materials. Staff can add new materials using the <strong>Add Record</strong> screen and modify existing catalog entries using the <strong>Edit Record</strong> screen.</p>

            <div class="guide-callout guide-callout-info">
                <strong><i class="fa-solid fa-lightbulb me-1"></i> Understanding Titles vs. Physical Copies:</strong><br/>
                Every resource consists of two levels:
                <ul class="mb-0 ps-3 mt-1" style="font-size: 0.9rem;">
                    <li><strong>The Bibliographic Record (Title):</strong> Contains the general book details such as Title, Author, Publisher, Dewey Call Number, Synopsis, Cover Image, and PDF document.</li>
                    <li><strong>Item Copies (Holdings):</strong> The physical books on your shelves, each tagged with its own unique 10-digit accession barcode number (e.g. Copy 1, Copy 2, Copy 3).</li>
                </ul>
            </div>
        </div>

        <!-- Section 2: Automated Metadata Lookup -->
        <div class="guide-section">
            <div class="guide-section-title">
                <i class="fa-solid fa-wand-magic-sparkles"></i> 1-Click Automated Metadata Lookup
            </div>
            <p>To accelerate cataloging, <?= htmlspecialchars($product_name ?? 'My Library');?> includes an automated online book search engine that fills in cataloging forms in seconds:</p>

            <div class="workflow-step">
                <div class="step-number">1</div>
                <div class="step-content">
                    <h5>Enter the ISBN or ISSN</h5>
                    <p class="mb-0 text-muted">Open the <strong>Add Record</strong> screen. Type or scan the 10-digit or 13-digit ISBN on the back of the book (or 8-digit ISSN for a journal).</p>
                </div>
            </div>

            <div class="workflow-step">
                <div class="step-number">2</div>
                <div class="step-content">
                    <h5>Click "Fetch ISBN" or "Fetch ISSN"</h5>
                    <p class="mb-1 text-muted">Click the <strong>Fetch ISBN</strong> (for books) or <strong>Fetch ISSN</strong> (for journals) button next to the identifier field. The system automatically searches global and national bibliographic repositories (including Google Books, Open Library, Perpustakaan Negara Malaysia Polaris, and Crossref):</p>
                    <ul class="text-muted mb-0" style="font-size: 0.9rem;">
                        <li><strong>Title &amp; Subtitle:</strong> Automatically extracted and placed into Title Statement (245).</li>
                        <li><strong>Author &amp; Responsibility:</strong> Filled into Author (100) and Statement of Responsibility.</li>
                        <li><strong>Publisher, Place &amp; Year:</strong> Populated in Publication (264).</li>
                        <li><strong>Dewey Call Number &amp; Cutter:</strong> Dewey classification (DDC) and local Cutter codes are calculated and placed in Call Number (090).</li>
                        <li><strong>Book Cover Artwork:</strong> High-resolution cover images are automatically fetched and attached.</li>
                        <li><strong>Summary Notes &amp; Description:</strong> Book descriptions and summaries are extracted and filled into Notes (500).</li>
                    </ul>
                </div>
            </div>

            <div class="workflow-step">
                <div class="step-number">3</div>
                <div class="step-content">
                    <h5>Review and Save</h5>
                    <p class="mb-0 text-muted">Review the pre-filled fields, make any custom adjustments for your local collection, choose a Material Type (e.g. Open Shelf, Red Spot), and click <strong>Register Record</strong> or <strong>Register Record and Auto Create 1 Copy</strong> (or <strong>Update Record</strong> when editing an existing entry).</p>
                </div>
            </div>
        </div>

        <!-- Section 3: MARC 21 Form Fields Guide -->
        <div class="guide-section">
            <div class="guide-section-title">
                <i class="fa-solid fa-tags"></i> MARC 21 Form Fields Reference
            </div>
            <p class="text-muted">The following table explains each field on the cataloging screen, what information to enter, and best practice examples:</p>

            <table class="table-guide">
                <thead>
                    <tr>
                        <th style="width: 140px;">Screen Field</th>
                        <th style="width: 110px;">MARC Tag</th>
                        <th>Description &amp; Librarian Guidance</th>
                        <th>Example Value</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>ISBN</strong></td>
                        <td><span class="ui-badge">020</span></td>
                        <td>International Standard Book Number (10 or 13 digits). Hyphens are cleaned automatically. Additional ISBNs (paperback, e-book) can be added below.</td>
                        <td><code>9789674608767</code></td>
                    </tr>
                    <tr>
                        <td><strong>ISSN</strong></td>
                        <td><span class="ui-badge">022</span></td>
                        <td>International Standard Serial Number for journals, magazines, and periodicals.</td>
                        <td><code>0128-0988</code></td>
                    </tr>
                    <tr>
                        <td><strong>Language</strong></td>
                        <td><span class="ui-badge">041</span></td>
                        <td>Primary language of the material (e.g. Bahasa Melayu, English, Arabic, Chinese, Tamil, Others).</td>
                        <td><code>Bahasa Malaysia (zsm)</code></td>
                    </tr>
                    <tr>
                        <td><strong>Call Number</strong></td>
                        <td><span class="ui-badge">090</span></td>
                        <td>
                            <strong>Classification number:</strong> Dewey Decimal Classification (DDC) code.<br/>
                            <strong>Local Cutter number:</strong> Author letter and number code plus publication year.
                        </td>
                        <td><code>371.334</code> (DDC)<br/><code>A38 2024</code> (Cutter)</td>
                    </tr>
                    <tr>
                        <td><strong>Author</strong></td>
                        <td><span class="ui-badge">100</span></td>
                        <td>Primary author surname, forename, followed by birth/death years if known.</td>
                        <td><code>Ahmad bin Faizal, 1978-</code></td>
                    </tr>
                    <tr>
                        <td><strong>Title Statement</strong></td>
                        <td><span class="ui-badge">245</span></td>
                        <td>
                            <strong>Title:</strong> Main title.<br/>
                            <strong>Subtitle:</strong> Remainder of title.<br/>
                            <strong>Statement of responsibility:</strong> Author, editor, or translator statement.
                        </td>
                        <td><code>Teknologi pendidikan : teori dan praktis / oleh Ahmad bin Faizal.</code></td>
                    </tr>
                    <tr>
                        <td><strong>Edition</strong></td>
                        <td><span class="ui-badge">250</span></td>
                        <td>Edition designation of the book.</td>
                        <td><code>2nd ed.</code> or <code>Edisi kedua</code></td>
                    </tr>
                    <tr>
                        <td><strong>Publication</strong></td>
                        <td><span class="ui-badge">264</span></td>
                        <td>Place of publication, name of publisher, and copyright or publication year.</td>
                        <td><code>Tanjong Malim : Penerbit UPSI, 2024.</code></td>
                    </tr>
                    <tr>
                        <td><strong>Physical Description</strong></td>
                        <td><span class="ui-badge">300</span></td>
                        <td>Page count (extent), illustration details, height in centimeters, and accompanying media.</td>
                        <td><code>xvi, 280 hlm. : il. berwarna ; 25 cm.</code></td>
                    </tr>
                    <tr>
                        <td><strong>Series Statement</strong></td>
                        <td><span class="ui-badge">490</span></td>
                        <td>Series title and volume or issue number in the series.</td>
                        <td><code>Siri Pengajian Pendidikan ; bil. 4</code></td>
                    </tr>
                    <tr>
                        <td><strong>General Notes</strong></td>
                        <td><span class="ui-badge">500</span></td>
                        <td>General notes such as bibliography references, index pages, or target audience.</td>
                        <td><code>Termasuk indeks dan bibliografi (hlm. 270-278).</code></td>
                    </tr>
                    <tr>
                        <td><strong>Contents Note</strong></td>
                        <td><span class="ui-badge">505</span></td>
                        <td>Complete chapter breakdown or formatted table of contents.</td>
                        <td><code>Bab 1. Pengenalan -- Bab 2. Reka Bentuk -- Bab 3. Penilaian.</code></td>
                    </tr>
                    <tr>
                        <td><strong>Subject Headings</strong></td>
                        <td><span class="ui-badge">650</span></td>
                        <td>Topical subject headings chosen from the library thesaurus.</td>
                        <td><code>Educational technology -- Malaysia.</code></td>
                    </tr>
                    <tr>
                        <td><strong>Corporate Name</strong></td>
                        <td><span class="ui-badge">710</span></td>
                        <td>Institutional, corporate, ministry, or university author name.</td>
                        <td><code>Universiti Pendidikan Sultan Idris</code></td>
                    </tr>
                    <tr>
                        <td><strong>Location</strong></td>
                        <td><span class="ui-badge">852</span></td>
                        <td>Library branch, collection type (e.g. Open Shelf, Reference), and shelf rack code.</td>
                        <td><code>Aras 2, Rak 14B</code></td>
                    </tr>
                    <tr>
                        <td><strong>Web Link (URL)</strong></td>
                        <td><span class="ui-badge">856</span></td>
                        <td>Direct link to external website, online repository, or journal DOI.</td>
                        <td><code>https://pustaka.upsi.edu.my/resource</code></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Section 4: Subject Selector & Media Attachments -->
        <div class="guide-section">
            <div class="guide-section-title">
                <i class="fa-solid fa-paperclip"></i> Subject Headings Selector &amp; File Attachments
            </div>
            
            <div class="row g-3">
                <div class="col-md-4 mb-3">
                    <div class="card p-3 h-100 bg-light border">
                        <h5 class="fw-bold text-primary mb-2"><i class="fa-solid fa-list-ul me-1"></i> Subject Headings</h5>
                        <p class="text-muted" style="font-size: 0.875rem;">
                            Click the <strong>[...]</strong> button next to the Subject Heading field to open the library's controlled vocabulary window. Click any approved heading to insert it into your record (or click <strong>+New</strong> to add one on the fly, or <strong>Clear</strong> to reset).
                        </p>
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <div class="card p-3 h-100 bg-light border">
                        <h5 class="fw-bold text-success mb-2"><i class="fa-solid fa-image me-1"></i> Book Cover Artwork</h5>
                        <p class="text-muted" style="font-size: 0.875rem;">
                            Attach a digital photo or scan of the book cover (<strong>JPG only</strong>, max 1MB). It will be displayed in the OPAC search results and virtual book carousel.
                        </p>
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <div class="card p-3 h-100 bg-light border">
                        <h5 class="fw-bold text-danger mb-2"><i class="fa-solid fa-file-pdf me-1"></i> PDF Full-Text Document</h5>
                        <p class="text-muted" style="font-size: 0.875rem;">
                            Attach electronic e-books, theses, or research papers in PDF format. Patrons and visitors can read the document using the secure inline viewer.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 5: Adding Physical Copies -->
        <div class="guide-section">
            <div class="guide-section-title">
                <i class="fa-solid fa-boxes-stacked"></i> Registering Physical Copies &amp; Accession Barcodes
            </div>
            <p>Once a title record is saved, you can add physical copies to make them available for borrowing:</p>

            <ol class="text-muted" style="line-height: 1.65; font-size: 0.9rem;">
                <li>Open the book's details page and click <strong>Add New Copy</strong> (or click <strong>Add Copies for this Record &rarr;</strong> immediately after creating a new title).</li>
                <li><strong>Number of Copies to Generate:</strong> Select how many physical copies were received (1 to 20 per batch).</li>
                <li><strong>Automatic Accession Numbering:</strong> The system automatically assigns sequential 10-digit accession barcodes (e.g. <code>0000000101</code> to <code>0000000105</code>).</li>
                <li><strong>Circulation Restriction:</strong> Select <code>Reference Only / Current Issue (Non-circulating)</code> if the copy is for In-Library Use Only (such as red-spot reserve books) or keep <code>Circulating (Standard Loan)</code> for normal circulation.</li>
                <li><strong>Invoice &amp; Vendor:</strong> Optionally record vendor, purchase invoice, and unit price for financial inventory.</li>
                <li><strong>Click Insert:</strong> The copies are immediately generated, assigned barcodes, and ready for circulation.</li>
            </ol>
        </div>

        <!-- Section 6: Duplicate Finder -->
        <div class="guide-section">
            <div class="guide-section-title">
                <i class="fa-solid fa-clone"></i> Identifying &amp; Cleaning Duplicate Titles
            </div>
            <p>To keep the library catalog clean and organized, administrators can use the <strong>Duplicates</strong> tool from the toolbar:</p>
            <ul class="text-muted mb-0" style="font-size: 0.9rem;">
                <li>The system groups and lists all titles that appear multiple times in the catalog along with duplicate counts.</li>
                <li>Click on any duplicate title link to instantly search and inspect the matching catalog records in the <strong>Admin Dashboard</strong>.</li>
                <li>Review the duplicate bibliographic entries, verify accession holdings, and delete redundant catalog entries to maintain accurate cataloging statistics.</li>
            </ul>
        </div>

        <?php include_once '../includes/footerbar.php';?>
    </div>
</body>
</html>
