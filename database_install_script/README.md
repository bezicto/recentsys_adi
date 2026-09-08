# Database Installation Guide (`recentsys_db.sql`)

This directory contains the initial database schema dump (`recentsys_db.sql`) for **ReCentSYS ADI** (MariaDB / MySQL).

> [!CAUTION]
> ### ⚠️ CRITICAL SECURITY NOTICE: DELETE THIS DIRECTORY AFTER IMPORT!
> Once you have successfully imported `recentsys_db.sql` into your MariaDB/MySQL server, **you MUST permanently delete this entire `database_install_script/` directory** from your web server or project directory.
> 
> Leaving raw SQL dump files in a publicly accessible web root exposes your database schema, table definitions, and initial administrative records to unauthorized access.
> 
> **Linux / macOS:**
> ```bash
> rm -rf database_install_script
> ```
> **Windows (PowerShell):**
> ```powershell
> Remove-Item -Recurse -Force database_install_script
> ```
> Or manually delete the `database_install_script` folder in Windows File Explorer or via your FTP/SFTP client.

---

## 🗄️ Database Requirements

| Component | Specification |
| :--- | :--- |
| **Supported Database** | MariaDB 10.3+ (Recommended: **MariaDB 10.6+**) or MySQL 5.7+ / 8.0+ |
| **Database Name** | `recentsys_db` |
| **Default Character Set** | `utf8mb4` |
| **Default Collation** | `utf8mb4_general_ci` or `utf8mb4_unicode_ci` |
| **SQL Dump File** | `recentsys_db.sql` (located in this directory) |

---

## 📥 Import Instructions

Choose one of the methods below to import the database schema into your MariaDB/MySQL instance:

### Method 1: Command Line Interface (CLI) — Linux / macOS / Windows Terminal (Recommended for Production)

1. **Log in to MariaDB as root:**
   ```bash
   sudo mysql -u root
   # or: sudo mariadb -u root
   ```

2. **Create the database and database user (if not already created):**
   ```sql
   CREATE DATABASE recentsys_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   CREATE USER 'recentsys_user'@'localhost' IDENTIFIED BY 'YourStrongPassword123!';
   GRANT ALL PRIVILEGES ON recentsys_db.* TO 'recentsys_user'@'localhost';
   FLUSH PRIVILEGES;
   EXIT;
   ```

3. **Import `recentsys_db.sql` into MariaDB:**
   Execute the import from inside the `database_install_script/` directory:
   ```bash
   mysql -u recentsys_user -p recentsys_db < recentsys_db.sql
   ```
   *(Or as root user:)*
   ```bash
   mysql -u root -p recentsys_db < recentsys_db.sql
   ```

---

### Method 2: HeidiSQL (Laragon on Windows)

Laragon includes **HeidiSQL**, an intuitive database management tool:

1. In Laragon, click the **Database** button on the bottom toolbar.
2. In the HeidiSQL connection window, click **Open** (the default settings connect to `localhost` as `root` with no password).
3. In HeidiSQL's left-hand panel:
   * Right-click any blank area (or your session root) &rarr; select **Create new** &rarr; **Database**.
   * Set the name to **`recentsys_db`**.
   * Set Collation to `utf8mb4_general_ci` or `utf8mb4_unicode_ci`.
   * Click **OK**.
4. Click once on the newly created `recentsys_db` database in the left panel to highlight it.
5. In the top menu, go to **File** &rarr; **Load SQL file...** (or press `Ctrl + O`).
6. Select **`recentsys_db.sql`** from this directory.
7. Click the blue **Execute SQL** / Run button (or press `F9`).
8. All tables and initial data will be imported within a few seconds.

---

### Method 3: phpMyAdmin (Web GUI)

1. Open **phpMyAdmin** in your browser (e.g. `http://localhost/phpmyadmin` or via Laragon: Right-click &rarr; **MySQL** &rarr; **phpMyAdmin**).
2. Click **New** in the left sidebar to create a database.
3. Enter database name **`recentsys_db`**, select collation `utf8mb4_general_ci` or `utf8mb4_unicode_ci`, and click **Create**.
4. Click on `recentsys_db` in the left sidebar to open it.
5. Click on the **Import** tab in the top navigation bar.
6. Under **File to import**, click **Choose File** / **Browse** and select **`recentsys_db.sql`**.
7. Scroll down and click **Import** (or **Go**).

---

## 🔑 Default Administrator Credentials

After completing the import, the default system superadmin account is available:

* **Staff Login URL:** `http://localhost/recentsys/rsc/` (or `http://recentsys.test/rsc/`)
* **Username:** `admin`
* **Password:** `1`

---

## ⚙️ Post-Installation Checklist

1. **Verify Database Configuration (`site/config.php`):**
   Ensure your database credentials in `site/config.php` match the database you just created:
   ```php
   $dbhost = "localhost";
   $dbname = "recentsys_db";
   $dbuser = "root";              // or 'recentsys_user'
   $dbpass = "";                  // or 'YourStrongPassword123!'
   ```

2. **Rotate Cryptographic Passphrase & Admin Password:**
   * Log in to the administrative dashboard as `admin` *first*.
   * Update `$ppaeskeysys` in `site/config.php` with a strong 32-character secret key.
   * Go to **Admin Dashboard &rarr; Accounts &rarr; Change Password** to save a new password (encrypting it with your new key).

3. **Delete this directory:**
   Delete the `database_install_script/` folder immediately to secure your installation.
