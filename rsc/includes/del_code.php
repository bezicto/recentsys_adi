<?php

defined('includeExist') || die("<div class='auth-card'><span class='badge badge-danger mb-2'>WARNING</span><h2>Forbidden: Direct access prohibited</h2><em class='text-muted'>System Response Code</em></div>");

include_once __DIR__ . '/token_validate.php';

// Only SUPER user is allowed to delete catalog records via POST with valid CSRF token
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST["del"]) && is_numeric($_POST["del"]) && !empty($_POST["del"])) {
    if (isset($_SESSION['editmode']) && $_SESSION['editmode'] === 'SUPER' && !empty($proceedAfterToken)) {
        $get_id_del = (int)$_POST["del"];
        
        // 1. Fetch metadata before deleting database records to properly remove files
        $stmt = mysqli_prepare($GLOBALS["conn"], "SELECT `38title`, `40inputdate`, `40instimestamp`, `39pdfattach`, `39imageatt` FROM eg_item WHERE id=?");
        mysqli_stmt_bind_param($stmt, "i", $get_id_del);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $rec = mysqli_fetch_assoc($res);
        mysqli_stmt_close($stmt);

        $del_title = $rec['38title'] ?? "Record #$get_id_del";

        // 2. Delete physical files from disk
        $rootDir = dirname(dirname(__DIR__));
        $parentDir = $_SESSION['parent_dir'] ?? 'site';

        if ($rec) {
            $inputdate = $rec['40inputdate'] ?? '';
            $instimestamp = $rec['40instimestamp'] ?? '';
            $dir_year = substr((string)$inputdate, -4);
            if (!preg_match('/^[0-9]{4}$/', $dir_year)) {
                $dir_year = date('Y');
            }

            if (!empty($instimestamp)) {
                $pdfPath = $rootDir . '/' . $parentDir . '/docs/' . $dir_year . '/' . $get_id_del . '_' . $instimestamp . '.pdf';
                if (is_file($pdfPath)) {
                    @unlink($pdfPath);
                }

                $jpgPath = $rootDir . '/' . $parentDir . '/albums/' . $dir_year . '/' . $get_id_del . '_' . $instimestamp . '.jpg';
                if (is_file($jpgPath)) {
                    @unlink($jpgPath);
                }
            }
        }

        // Also check if delfilename was explicitly passed
        if (isset($_POST["delfilename"]) && is_string($_POST["delfilename"])) {
            $get_delfilename = trim($_POST["delfilename"]);
            if (preg_match('/^[0-9]{4}\/[0-9]+_[0-9]+$/', $get_delfilename)) {
                $extraPdf = $rootDir . '/' . $parentDir . '/docs/' . $get_delfilename . '.pdf';
                if (is_file($extraPdf)) {
                    @unlink($extraPdf);
                }
                $extraJpg = $rootDir . '/' . $parentDir . '/albums/' . $get_delfilename . '.jpg';
                if (is_file($extraJpg)) {
                    @unlink($extraJpg);
                }
            }
        }

        // 3. Delete database records from all relational tables
        $tables = [
            "DELETE FROM eg_item WHERE id=?",
            "DELETE FROM eg_item_indicator WHERE eg_item_id=?",
            "DELETE FROM eg_item_isbn WHERE eg_item_id=?",
            "DELETE FROM eg_item_det WHERE eg_item_id=?",
            "DELETE FROM eg_item_copies WHERE eg_item_id=?"
        ];

        foreach ($tables as $sql) {
            $stmt = mysqli_prepare($GLOBALS["conn"], $sql);
            mysqli_stmt_bind_param($stmt, "i", $get_id_del);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }

        $_SESSION['flash_delete_msg'] = [
            'type' => 'success',
            'text' => "Catalog record <strong>" . htmlspecialchars($del_title, ENT_QUOTES, 'UTF-8') . "</strong> (#$get_id_del) and its associated files have been permanently deleted."
        ];
    } else {
        $_SESSION['flash_delete_msg'] = [
            'type' => 'danger',
            'text' => 'Access restricted: Only super administrators may delete catalog records.'
        ];
    }
}
