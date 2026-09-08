# ReCentSYS ADI
### Integrated Library Management & Resource Centre Automation System
**Version 8 (Build 1)** &bull; **Open Source &bull; MIT License**

[![PHP Version](https://img.shields.io/badge/PHP-7.4%20|%208.0%20|%208.1%20|%208.2%20|%208.3+-777BB4?style=flat&logo=php&logoColor=white)](https://www.php.net/)
[![Database](https://img.shields.io/badge/Database-MariaDB%2010.3+%20|%20MySQL%205.7+-003545?style=flat&logo=mariadb&logoColor=white)](https://mariadb.org/)
[![Standards](https://img.shields.io/badge/Standard-MARC21%20Compliant-0284c7?style=flat)](https://www.loc.gov/marc/)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)
[![Interface](https://img.shields.io/badge/UI-Responsive%20%7C%20Mobile%20Ready-success)](#system-features)

---

## 📖 About ReCentSYS ADI

**ReCentSYS ADI** (Resource Centre System &ndash; Automated Digital Interface) is a comprehensive, lightweight, and modern web-based **Integrated Library System (ILS)** designed for schools, colleges, universities, research institutions, and corporate resource centres.

Engineered with simplicity, speed, and standard library science principles in mind, ReCentSYS ADI enables institutions to automate cataloging, circulation desk operations, patron management, serials tracking, and accreditation reporting without requiring expensive proprietary software or complex infrastructure.

> **Origins & Provenance:**  
> Developed and maintained by **Khairul Asyrani Sulaiman** ([GitHub: @bezicto](https://github.com/bezicto)) at **Perpustakaan Tuanku Bainun, Universiti Pendidikan Sultan Idris (UPSI)**, with MARC21 standard record definitions and library operational guidance contributed by **Mohd Hizam Samin**.

---

## 🌟 Key System Modules & Features

### 1. 🔍 Modern OPAC (Online Public Access Catalog)
* **Smart Search:** Fast search by Keyword, Title, Author, Subject Heading, Control Number, or Call Number with stopword filtering and boolean operators.
* **Live Copy Availability:** Instant indicators showing whether copies are Available, On Loan, In-Library Reference Only, or Damaged/Under Repair.
* **Digital Media & E-Resources:** In-browser viewing and downloading of full-text PDF research documents, theses, reports, and articles.
* **Subject Thesaurus Browser:** Explore items by standardized subject headings and classifications.
* **Patron "My Account" Portal:** Patrons can view their active borrowings, loan history, fine balances, and perform 1-click self-service loan renewals.

### 2. 📚 Cataloging & Standard MARC21 Engine
* **MARC21 Tag Structure:** Native support for standard bibliographic fields including `020` (ISBN), `022` (ISSN), `041` (Language), `090` (Call Number / DDC + Cutter), `100` (Author), `245` (Title statement), `250` (Edition), `264` (Publication), `300` (Physical description), `490` (Series), `500` (Notes), `505` (Formatted contents), `710` (Corporate body), `852` (Shelf location), and `856` (Electronic link/URI).
* **1-Click Auto-Metadata Fetching:**
  * **ISBN Fetcher:** Fetches title, authors, publisher, synopsis, and book cover images via the **Google Books API** and **Open Library API**. Automatically derives Dewey Decimal Classification (DDC) and 3-letter Cutter numbers.
  * **ISSN Fetcher:** Automatically parses periodical metadata and serial publication history via the **Crossref API**.
  * **PNM Polaris Integration:** Optional direct integration with Perpustakaan Negara Malaysia (PNM) library web services.
* **Multiple Physical Copies:** Manage unlimited accessioned copies under a single master bibliographic title.
* **Spine Labels & Barcodes:** Generate printable book barcodes and spine call-number stickers.
* **Data Portability:** Full MARC21 export (`.mrc` records) and duplicate title detection tool.

### 3. 🔄 Circulation & Front-Desk Counter Operations
* **Charge (Check-out):** Issue loans rapidly using barcode scanners or device cameras. Supports both Accession Number mode and Publisher ISBN/ISSN mode.
* **Discharge (Check-in):** Instant 1-scan return with automatic fine computation and return confirmation.
* **Patron Borrowing Quotas:** Dynamic rule enforcement based on patron tiers (maximum concurrent loan items and borrowing durations).
* **Reference & Red Spot Protection:** Automated restrictions preventing the borrowing of reference items, audio-visual materials, or current periodical issues.
* **Self-Service Online Renewals:** Configurable maximum online renewals per item (default: 2 times).

### 4. 👥 Patron Management & Lobby Self-Registration Kiosk
* **Multi-Tier Patron Accounts:** Role-based borrowing policies for `PATRON`, `POSTGRAD`, `FACULTY`, `STAFF`, and custom user types.
* **Lobby Self-Registration QR Kiosk:** Display a dynamic QR kiosk screen in your library lobby. Visitors scan the QR code with their mobile smartphones, complete registration, and receive their member account immediately.
* **Printable ID Cards:** Generate professional plastic membership cards complete with patron portrait photo, barcode, and QR code.

### 5. 📰 Serials & Periodicals Management (Kardex)
* **Kardex Dashboard:** Specialized management for continuing resources (journals, magazines, periodicals).
* **Issue Check-in:** Log volume numbers, issue numbers, publication seasons, and supplier invoice details.
* **Current Issue Locking:** Automatically marks latest issues as "Reference Only" until succeeding issues arrive, then frees previous issues for regular circulation.

### 6. 💰 Fines, Cashier & Dynamic Holiday Calendar
* **Tiered Fine Rates:** Set specific overdue fine amounts per day based on collection type (e.g. Open Shelf vs Red Spot).
* **Holiday-Aware Due Dates:** Integrated library calendar shifts due dates past weekends and gazetted public holidays so patrons are never charged for days the library is closed.
* **Cashier Settlements:** Accept overdue payments at the desk, record waivers or discounts with audit notes, and generate official digital payment receipt IDs.

### 7. 📊 Institutional Reports & Accreditation Analytics
* **6+ Executive Analytics Reports:**
  * Cataloging productivity by staff member.
  * Collection breakdown by material format and classification.
  * Most popular titles and highest-circulated items.
  * Patron search keyword trend tracking to assess collection demand.
  * Loan velocity and circulation history.
  * Fine revenue and cashier audit trails.
* **High-Performance Caching:** Built-in file-based statistics cache ensures instant report loading even with large databases.

### 8. 🛡️ Security & Enterprise Protection
* **Anti-Brute-Force IP Guard:** Automatically detects repeated failed logins and temporarily blocks suspicious IP addresses to prevent credential stuffing.
* **Administrative IP Management:** Superadmin console to view, audit, and unblock locked IP addresses.
* **AES Password Encryption:** Passwords are encrypted directly using MariaDB/MySQL `AES_ENCRYPT` functions.
* **CSRF & Token Validation:** Sensitive administrative operations are protected against Cross-Site Request Forgery.

---

## 💻 System Requirements

ReCentSYS ADI is designed to be lightweight and runs smoothly on standard low-cost shared hosting, dedicated servers, virtual private servers (VPS), or local office computers.

| Component | Minimum Requirement | Recommended |
| :--- | :--- | :--- |
| **Operating System** | Windows, Linux (Ubuntu, Debian, CentOS), or macOS | Ubuntu 22.04 LTS or Debian 12 |
| **Web Server** | Apache 2.4+ (with `mod_rewrite`) or Nginx | Apache 2.4+ |
| **PHP Version** | PHP 7.4 | **PHP 8.1, 8.2, or 8.3** |
| **Database** | MariaDB 10.3+ or MySQL 5.7+ | **MariaDB 10.6+** |
| **Required PHP Extensions** | `mysqli`, `curl`, `gd`, `mbstring`, `json`, `session` | All enabled |
| **Web Browser** | Any modern browser (Chrome, Firefox, Edge, Safari) | Latest evergreen version |

---

## 🚀 Beginner-Friendly Installation Guide

This guide is written especially for new users who may have limited web development experience. Follow the steps below using **Laragon** (a fast, modern, and lightweight all-in-one local development environment for Windows with built-in Apache and MariaDB/MySQL), or follow the Linux server instructions if deploying to a remote server.

### Method A: Local Setup on Windows (Using Laragon — Recommended)

[Laragon](https://laragon.org/) is the modern, hassle-free alternative to outdated server stacks. It starts instantly, requires zero configuration, and automatically sets up virtual hosts.

#### Step 1: Download & Install Laragon
1. Download **Laragon (Full edition with PHP 8.x)** from [laragon.org/download](https://laragon.org/download/).
2. Run the installer and keep the default installation folder (usually `C:\laragon\`).
3. Launch Laragon after installation.

#### Step 2: Copy ReCentSYS ADI to Laragon's Web Root (`www`)
1. Open Windows File Explorer and navigate to Laragon's web folder:
   ```text
   C:\laragon\www\
   ```
2. Create a folder named `recentsys` (or extract this repository here).
3. The folder structure inside `C:\laragon\www\recentsys\` should look like:
   ```text
   recentsys/
   ├── index.php
   ├── rsc/
   └── site/
   ```

#### Step 3: Start Laragon Services
1. In the Laragon window, click the big **"Start All"** button.
2. Apache and MySQL/MariaDB will start immediately.

#### Step 4: Import Database Schema (`recentsys_db_install.sql`)
Laragon includes **HeidiSQL**, a fast and intuitive database management tool that connects with 1 click:

1. In Laragon, click the **Database** button on the bottom toolbar.
2. A HeidiSQL connection window appears. Click **Open** (the default settings connect to `localhost` as `root` with no password).
3. In HeidiSQL's left panel, right-click any blank area (or your session name) &rarr; select **Create new** &rarr; **Database**.
4. Name the database **`recentsys_db`**, ensure the collation is `utf8mb4_general_ci` or `utf8mb4_unicode_ci`, and click **OK**.
5. Click once on your newly created `recentsys_db` in the left panel to highlight it.
6. In the top menu, go to **File** &rarr; **Load SQL file...** (or press `Ctrl + O`).
7. Select the database schema file:  
   👉 **`recentsys_db_install.sql`**
8. Click the blue **Execute SQL** / Run button (or press `F9`).  
   *All tables and schema will be imported in a few seconds!*

> **Alternative via phpMyAdmin:** If you prefer phpMyAdmin, simply right-click anywhere in Laragon &rarr; **MySQL** &rarr; **phpMyAdmin**, create `recentsys_db`, and import `recentsys_db_install.sql` under the **Import** tab.

#### Step 5: Configure Database Connection & Settings
1. Open the file `C:\laragon\www\recentsys\site\config.php` in your favorite text editor (Notepad++, VS Code, etc.).
2. Confirm the database credentials around line 9:
   ```php
   // Database connection settings
   $dbhost = "localhost";
   $dbname = "recentsys_db";          // The database you created in Step 4
   $dbuser = "root";                  // Default Laragon user is 'root'
   $dbpass = "";                      // Default Laragon password is empty (leave blank)
   ```
3. Set your library branding and contact details in the same file:
   ```php
   $product_name = "Perpustakaan Seri Budi";   // Your library name
   $licensed_info = "SMK Seri Budi Resource Centre";
   $about_text = "Welcome to the online library portal of SMK Seri Budi.";
   $staff_contact_email = "library@school.edu.my";
   ```
4. Save the file (`Ctrl + S`).

#### Step 6: Launch & Access ReCentSYS ADI
1. Open your web browser and navigate to either:
   * **`http://recentsys.test/`** *(Laragon's automatic pretty local domain)*, OR
   * **`http://localhost/recentsys/`**
2. You will be automatically directed to the public OPAC search portal!
3. To open the administration and circulation desk, click **Admin Login** (or go to `http://recentsys.test/rsc/`).
4. Log in using the default administrator credentials:
   * **Username:** `admin`
   * **Password:** `1` *(or the default password defined in your `recentsys_db_install.sql`)*

---

### Method B: Production Setup on Linux (Ubuntu / Debian LAMP)

For system administrators deploying on a Linux cloud server or campus intranet:

#### 1. Install Apache, MariaDB, PHP, and Extensions
```bash
sudo apt update
sudo apt install -y apache2 mariadb-server php php-cli php-mysql php-curl php-gd php-mbstring php-json
```

#### 2. Create the Database & User in MariaDB
```bash
sudo mysql -u root
```
In the MariaDB prompt, run:
```sql
CREATE DATABASE recentsys_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'recentsys_user'@'localhost' IDENTIFIED BY 'YourStrongPassword123!';
GRANT ALL PRIVILEGES ON recentsys_db.* TO 'recentsys_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

#### 3. Import the Database Schema
Import `recentsys_db_install.sql` into the newly created database:
```bash
mysql -u recentsys_user -p recentsys_db < /path/to/recentsys_db_install.sql
```

#### 4. Deploy Files & Set Folder Permissions
Copy the project folder to `/var/www/html/recentsys` and set permissions so Apache can write to upload directories:
```bash
sudo cp -r /path/to/recentsys /var/www/html/recentsys
cd /var/www/html/recentsys

# Grant ownership to the web server user
sudo chown -R www-data:www-data /var/www/html/recentsys

# Ensure storage directories are writable
sudo chmod -R 775 site/albums site/avatars site/blocks site/docs site/stats
```

#### 5. Update `site/config.php`
Edit the configuration:
```bash
sudo nano site/config.php
```
Update `$dbhost`, `$dbname`, `$dbuser`, and `$dbpass` with your credentials:
```php
$dbhost = "localhost";
$dbname = "recentsys_db";
$dbuser = "recentsys_user";
$dbpass = "YourStrongPassword123!";
```
Save and exit (`Ctrl+O`, `Enter`, `Ctrl+X`).

---

## ⚙️ Configuration Reference (`site/config.php`)

All operational and visual parameters of your library instance are cleanly organized inside [`site/config.php`](site/config.php).

### Core Parameters Explained

```php
// ==========================================
// 1. DATABASE CONFIGURATION
// ==========================================
$dbhost = "localhost";             // Database server hostname or IP
$dbname = "recentsys_db";          // Name of the imported database
$dbuser = "recentsys_user";        // Database username
$dbpass = "secret_password";       // Database password

// ==========================================
// 2. CRYPTOGRAPHIC SECURITY KEYS
// ==========================================
// Secret key used for session, CSRF, and kiosk token validation
$tokenaeskeysys = "insert_a_random_32_character_token_here";

// Secret passphrase for MariaDB AES_ENCRYPT password storage.
// IMPORTANT: Change this during initial setup before registering real users!
$ppaeskeysys = "YourSecretEncryptionKey32Chars!";

// ==========================================
// 3. BRANDING & CONTACT INFO
// ==========================================
$product_name = "Perpustakaan Tun Seri Lanang"; // Display name in header and title tags
$licensed_info = "Universiti Pendidikan Sultan Idris"; // Institution subtitle
$about_text = "Welcome to our digital resource centre.";
$staff_contact_email = "library@upsi.edu.my";

// ==========================================
// 4. CIRCULATION & DESK POLICIES
// ==========================================
// How many times a patron can self-renew a loaned book online
$max_renew_count = 2;

// Identifier scanned during check-out & check-in:
// 'accessnum' = standard library accession number barcode on book copies (Recommended)
// 'isbnissn'  = publisher-printed commercial ISBN/ISSN barcode
$circulation_mode = "accessnum";

// Currency code displayed on fine receipts and cashier screens
$currency_SHORT = "MYR";

// ==========================================
// 5. UPLOAD LIMITS (Megabytes)
// ==========================================
$pdf_upload_maxsize = 10;          // Max PDF e-book / document upload size in MB
$cover_upload_maxsize = 1;         // Max book cover image size in MB
$avatar_upload_maxsize = 2;        // Max patron profile photo size in MB

// ==========================================
// 6. AUTOMATED BIBLIOGRAPHIC APIS
// ==========================================
// Enables automated ISBN cataloging from Google Books
$enable_googlebookapi = true;
$googlebook_api_key = "";          // Optional: Add Google Books API key to avoid rate limits

// Enables automated metadata, DDC classification, and Cutter from Open Library
$enable_openlibraryapi = true;

// Optional: Perpustakaan Negara Malaysia (PNM) Polaris Integration
$enable_pnm_polaris_integration = false;
$pnm_polaris_access_key = "";
$pnm_polaris_access_id = "";
$pnm_polaris_base_url = "https://example.pnm.gov.my/PAPIService/REST/public/v1/1033/1/1";
```

---

## 🔒 Post-Installation Security & First-Time Setup Checklist

Complete these 5 quick steps immediately after your first successful login:

1. **Log In to the Administrative Dashboard First:**
   * Log in using the default credentials (**Username:** `admin` &bull; **Password:** `1`).
   * *Ensure you are actively logged in before proceeding to rotate the encryption key in the next step.*
2. **Rotate the AES Encryption Passphrase (`$ppaeskeysys`) & Update Password:**
   * **Crucial:** You must be inside / logged into the system as `admin` *before* rotating the key. If the key is changed before logging in, the system will be unable to decrypt and verify the existing default password.
   * With your admin session currently active:
     1. Open `site/config.php` in a text editor and replace `$ppaeskeysys` with your own strong, custom secret key.
     2. Return to your logged-in session, navigate to **Admin Dashboard &rarr; Accounts &rarr; Change Password** (or User Accounts Management).
     3. Update the admin password to a strong new password and save. This re-encrypts your password in MariaDB using your newly configured AES key.
     4. Log out and test logging in with the new password to confirm the encryption key is active and working properly.
3. **Configure Library Working Days & Holidays:**
   * Navigate to **Admin Dashboard &rarr; Holidays** (`addholiday.php`).
   * Select your library's weekly weekend closure days (e.g. Saturday & Sunday, or Friday & Saturday).
   * Add upcoming academic semester breaks and national public holidays to ensure overdue fine calculations remain accurate.
4. **Set Loan Eligibility Quotas:**
   * Navigate to **Users &rarr; Loan Eligibility** (`chan_loandays.php`).
   * Customize the borrowing rules (maximum loan days and item limits) for each patron group. Note that these default patron tiers (`PATRON`, `SUPER`, `TRUE (library staff)`, `FALSE (deactivated users)`) are fixed system categories that cannot be added or deleted, but their borrowing quotas can be tailored to match your library's policy.
5. **Verify File Upload Directories:**
   * Ensure the following directories inside `site/` have write permissions enabled:
     * `site/albums/` &ndash; Book cover images
     * `site/avatars/` &ndash; Patron identification photos
     * `site/blocks/` &ndash; CMS and announcement blocks
     * `site/docs/` &ndash; Full-text PDF documents & e-books
     * `site/stats/` &ndash; Cached reporting analytics

---

## 📂 Architecture & Directory Structure

ReCentSYS ADI utilizes a clean separation between the central application engine (`rsc/`) and the instance-specific data and branding (`site/`).

```text
recentsys/
│
├── index.php                 # Root application gateway (redirects to active site)
├── recentsys_db_install.sql  # MariaDB / MySQL database installation schema
│
├── rsc/                      # Core Application Engine
│   ├── index.php             # Staff login and patron portal gateway
│   ├── opac.php              # OPAC catalog search interface
│   ├── details.php           # Bibliographic title detail and copies view
│   ├── viewdoc.php           # In-browser protected PDF document reader
│   ├── patron_card.php       # Printable plastic patron card generator
│   │
│   ├── admin/                # Circulation & Administration Desk
│   │   ├── charge.php        # Book check-out / borrow screen
│   │   ├── discharge.php     # Book check-in / return screen
│   │   ├── reg.php           # MARC21 cataloging & record entry screen
│   │   ├── serials.php       # Kardex serials and periodicals management
│   │   ├── qr_selfreg.php    # Dynamic QR Lobby Self-Registration Kiosk
│   │   ├── addholiday.php    # Library calendar and holiday management
│   │   ├── addtype.php       # Material types configuration
│   │   ├── chan_loandays.php # Patron loan eligibility quotas
│   │   └── blocked_ips.php   # IP Guard anti-brute-force management
│   │
│   ├── classes/              # Core PHP Service Classes
│   │   └── BibliographicService.php # ISBN/ISSN auto-fetch, DDC & Cutter engine
│   │
│   ├── includes/             # Shared Utilities & Helpers
│   │   ├── functions.php     # Common database, string, and cataloging utilities
│   │   ├── ip_guard.php      # Brute-force detection and lockout logic
│   │   ├── marc_helper.php   # MARC21 parsing and export helpers
│   │   └── stat_cache.php    # Reporting cache accelerator
│   │
│   ├── reports/              # Accreditation & Statistical Analytics
│   │   └── adsreport.php     # Executive reporting dashboard
│   │
│   └── helps/                # Built-in Interactive Staff Documentation
│       ├── index.php         # Admin guide home & topic search filter
│       ├── cataloging.php    # MARC21 cataloging step-by-step tutorial
│       ├── circulation.php   # Desk charging & discharging procedures
│       └── quick_reference.php # DDC 1000 and MARC fast lookup cheat sheet
│
└── site/                     # Instance Configuration & Uploaded Assets
    ├── config.php            # Main database, branding, and policy configuration
    ├── index.php             # Site instance entry point
    ├── albums/               # Uploaded book cover graphics
    ├── avatars/              # Uploaded patron portrait photographs
    ├── blocks/               # Dynamic portal content blocks
    ├── docs/                 # Uploaded digital PDF e-resources
    ├── images/               # Institutional logo and site favicon
    └── stats/                # Auto-generated statistics cache files
```

---

## ❓ Frequently Asked Questions (FAQ)

<details>
<summary><strong>1. I see "Please check database connection" when opening the page. What should I do?</strong></summary>

This means PHP was unable to connect to your MariaDB/MySQL server. Check the following:
* Verify that MySQL/MariaDB is running in your Laragon control panel (click **"Start All"**).
* Open `site/config.php` and verify that `$dbhost`, `$dbname`, `$dbuser`, and `$dbpass` match your database credentials.
* If you are using Laragon on Windows, the default user is `root` with an **empty** password: `$dbpass = "";`.
</details>

<details>
<summary><strong>2. Why am I getting an error when uploading PDF documents or book covers?</strong></summary>

Check these two common causes:
1. **Folder Permissions:** Ensure the folders `site/albums/`, `site/avatars/`, and `site/docs/` exist and have write permissions (`chmod 775` on Linux).
2. **PHP Upload Limits:** By default, PHP restricts upload sizes to 2MB. Open your `php.ini` file and increase the limits:
   ```ini
   upload_max_filesize = 32M
   post_max_size = 32M
   memory_limit = 128M
   ```
   Restart Apache after saving `php.ini`.
</details>

<details>
<summary><strong>3. The "Fetch ISBN" or "Fetch ISSN" button is not finding book data.</strong></summary>

* Verify that your server has an active internet connection.
* Confirm that the PHP `curl` extension is enabled in `php.ini` (remove the semicolon before `;extension=curl`).
* If you are cataloging high volumes of books, consider creating a free Google Books API key and adding it to `$googlebook_api_key` in `site/config.php` to prevent unauthenticated IP rate limiting.
</details>

<details>
<summary><strong>4. "Your IP address has been blocked for 1 week due to 3 consecutive failed login attempts." How do I unlock it?</strong></summary>

ReCentSYS ADI's built-in **IP Guard** temporarily blocks IP addresses that fail multiple logins consecutively.
* **If another admin is logged in:** They can navigate to **Admin &rarr; Blocked IPs** (`blocked_ips.php`) and click **Unblock** next to your IP address.
* **Direct Database Method:** Log into phpMyAdmin, open `recentsys_db`, view the table `eg_blocked_ips`, and delete the row containing your IP address.
</details>

<details>
<summary><strong>5. Can I use barcode scanners and receipt printers?</strong></summary>

Yes! ReCentSYS ADI is designed to work seamlessly with:
* Any standard USB or Bluetooth 1D/2D handheld barcode scanner (operating in standard keyboard wedge emulation mode).
* Integrated mobile smartphone camera scanning (using the HTML5 barcode camera scanner in the desk interface).
* Standard thermal receipt printers or laser printers for loan slips, payment receipts, and patron ID cards.
</details>

---

## 📜 License

ReCentSYS ADI is released as open-source software under the **MIT License**.

```text
MIT License

Copyright (c) 2026 Khairul Asyrani Sulaiman

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
```

---

## 🙏 Acknowledgements & Credits

* **Core Development & Architecture:** [Khairul Asyrani Sulaiman](https://github.com/bezicto)
* **Institutional Support:** [Perpustakaan Tuanku Bainun, Universiti Pendidikan Sultan Idris (UPSI)](https://perpustakaan.upsi.edu.my/)
* **Library Science & MARC21 Workflows:** Mohd Hizam Samin
* **Open Source Libraries & APIs:** [Google Books API](https://developers.google.com/books), [Open Library](https://openlibrary.org/), [Crossref](https://www.crossref.org/), [Font Awesome](https://fontawesome.com/)
