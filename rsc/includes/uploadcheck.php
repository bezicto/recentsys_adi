 <?php

defined('includeExist') || die("<div class='auth-card'><span class='badge badge-danger mb-2'>WARNING</span><h2>Forbidden: Direct access prohibited</h2><em class='text-muted'>System Response Code</em></div>");

if ($inputdateUpload == null) {
    $dir_year = date("Y");
} else {
    $dir_year = substr("$inputdateUpload", -4);
}

if (is_dir("$allowed_uploaddir/$dir_year")) {
    $uploaddir = "$allowed_uploaddir/$dir_year";
} else {
    mkdir("$allowed_uploaddir/$dir_year", 0755, true);
    $uploaddir = "$allowed_uploaddir/$dir_year";
    file_put_contents("$allowed_uploaddir/$dir_year/index.php", "<html lang='en'><head><title>403 Forbidden</title></head><body><h1>Forbidden</h1><div>You don't have permission to access this directory on this server.</div></body></html>");
}

// Check Extension
$extension = strtolower(pathinfo($_FILES[$allowed_fieldname]['name'] ?? '', PATHINFO_EXTENSION));
$allowed_paths = array_filter(array_map('trim', array_map('strtolower', explode(",", $allowed_ext))));
$ok = "0";
if (!empty($extension) && in_array($extension, $allowed_paths, true)) {
    $ok = "1";
}

// Check File Extension
if ($ok === "1") {
    if ($_FILES[$allowed_fieldname]['size'] > $max_size) {
        $successupload = 'FALSE SIZE';
    } else {
        $successupload = 'TRUE';
    }
} else {
    $successupload = 'FALSE EXTENSION';
}
