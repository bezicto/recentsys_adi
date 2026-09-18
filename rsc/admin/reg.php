<?php
    session_start();
    define('includeExist', true);
    include_once '../includes/access_logged.php';
    include_once '../config.php';
    include_once '../includes/functions.php';
    include_once '../includes/token_validate.php';

    // Context parameters for return navigation
    $ctx_scstr = $_REQUEST["scstr"] ?? '';
    $ctx_page = isset($_REQUEST["page"]) && is_numeric($_REQUEST["page"]) ? (int)$_REQUEST["page"] : 0;
    $ctx_sctype = $_REQUEST["sctype"] ?? '';
    $ctx_scflag = $_REQUEST["scflag"] ?? '';
    $ctx_delr = $_REQUEST["delr"] ?? '';
    $ctx_subid = isset($_REQUEST["subid"]) && is_numeric($_REQUEST["subid"]) ? (int)$_REQUEST["subid"] : 0;
    $ctx_inf = $_REQUEST["inf"] ?? '';
    $ctx_infname = $_REQUEST["infname"] ?? '';
    $ctx_ser_scstr = $_REQUEST["ser_scstr"] ?? '';
    $ctx_ser_filter = $_REQUEST["ser_filter"] ?? '';
    $ctx_ser_sort = $_REQUEST["ser_sort"] ?? '';
    $ctx_ser_page = isset($_REQUEST["ser_page"]) && is_numeric($_REQUEST["ser_page"]) ? (int)$_REQUEST["ser_page"] : 0;

    $ctxArray = [];
    if (!empty($ctx_scstr)) $ctxArray['scstr'] = $ctx_scstr;
    if (!empty($ctx_page)) $ctxArray['page'] = $ctx_page;
    if (!empty($ctx_sctype)) $ctxArray['sctype'] = $ctx_sctype;
    if (!empty($ctx_scflag)) $ctxArray['scflag'] = $ctx_scflag;
    if (!empty($ctx_delr)) $ctxArray['delr'] = $ctx_delr;
    if (!empty($ctx_subid)) $ctxArray['subid'] = $ctx_subid;
    if (!empty($ctx_inf)) $ctxArray['inf'] = $ctx_inf;
    if (!empty($ctx_infname)) $ctxArray['infname'] = $ctx_infname;
    if (!empty($ctx_ser_scstr)) $ctxArray['ser_scstr'] = $ctx_ser_scstr;
    if (!empty($ctx_ser_filter)) $ctxArray['ser_filter'] = $ctx_ser_filter;
    if (!empty($ctx_ser_sort)) $ctxArray['ser_sort'] = $ctx_ser_sort;
    if (!empty($ctx_ser_page)) $ctxArray['ser_page'] = $ctx_ser_page;

    $ctxQuery = !empty($ctxArray) ? '&' . http_build_query($ctxArray) : '';

    // Handle Form Submission (POST)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submitted']) && $proceedAfterToken) {
        $edit_id = (int)($_POST["id"] ?? ($_POST["upd"] ?? ($_POST["id3"] ?? 0)));
        $is_update = ($edit_id > 0);

        // Standardized field extraction with legacy fallbacks and safe bounds
        $tajuk = mb_substr(trim($_POST["tajuk"] ?? ($_POST["tajuk1"] ?? ($_POST["tajuk3"] ?? ''))), 0, 255);
        $jenis = $_POST["jenis"] ?? ($_POST["jenis1"] ?? ($_POST["jenis3"] ?? ''));
        $lokasi = mb_substr(trim($_POST["lokasi"] ?? ($_POST["lokasi1"] ?? ($_POST["lokasi3"] ?? ''))), 0, 50);
        $link = mb_substr(trim($_POST["link"] ?? ($_POST["link1"] ?? ($_POST["link3"] ?? ''))), 0, 255);
        $callnum = mb_substr(trim($_POST["callnum"] ?? ($_POST["callnum1"] ?? ($_POST["callnum3"] ?? ''))), 0, 70);
        $pengarang = mb_substr(trim($_POST["pengarang"] ?? ($_POST["pengarang1"] ?? ($_POST["pengarang3"] ?? ''))), 0, 150);
        $sumber = mb_substr(trim($_POST["sumber"] ?? ($_POST["sumber0"] ?? ($_POST["sumber3"] ?? ''))), 0, 255);
        $subjectheading = mb_substr(trim($_POST["subjectheading"] ?? ($_POST["subjectheading1"] ?? ($_POST["subjectheading3"] ?? ''))), 0, 100);
        $isbn = mb_substr(str_replace(['-', '–', '—'], '', trim($_POST["isbn"] ?? ($_POST["isbn1"] ?? ($_POST["isbn3"] ?? '')))), 0, 60);
        $issn = mb_substr(str_replace(['-', '–', '—'], '', trim($_POST["issn"] ?? ($_POST["issn1"] ?? ($_POST["issn3"] ?? '')))), 0, 60);
        $edition = mb_substr(trim($_POST["edition"] ?? ($_POST["edition1"] ?? ($_POST["edition3"] ?? ''))), 0, 255);
        $publication = mb_substr(trim($_POST["publication"] ?? ($_POST["publication1"] ?? ($_POST["publication3"] ?? ''))), 0, 255);
        $physicaldesc = mb_substr(trim($_POST["physicaldesc"] ?? ($_POST["physicaldesc1"] ?? ($_POST["physicaldesc3"] ?? ''))), 0, 255);
        $series = mb_substr(trim($_POST["series"] ?? ($_POST["series1"] ?? ($_POST["series3"] ?? ''))), 0, 150);
        $notes = mb_substr(trim($_POST["notes"] ?? ($_POST["notes1"] ?? ($_POST["notes3"] ?? ''))), 0, 255);
        $fcnotes = mb_substr(trim($_POST["fcnotes"] ?? ($_POST["fcnotes1"] ?? ($_POST["fcnotes3"] ?? ''))), 0, 255);
        $language = mb_substr(trim($_POST["language"] ?? ($_POST["language1"] ?? ($_POST["language3"] ?? ''))), 0, 3);

        // Subfields
        $callnum_b = mb_substr(trim($_POST["callnum_b"] ?? ($_POST["callnum1_b"] ?? ($_POST["callnum3_b"] ?? ''))), 0, 70);
        $pengarang_d = mb_substr(trim($_POST["pengarang_d"] ?? ($_POST["pengarang1_d"] ?? ($_POST["pengarang3_d"] ?? ''))), 0, 150);
        $tajuk_b = mb_substr(trim($_POST["tajuk_b"] ?? ($_POST["tajuk1_b"] ?? ($_POST["tajuk3_b"] ?? ''))), 0, 255);
        $tajuk_c = mb_substr(trim($_POST["tajuk_c"] ?? ($_POST["tajuk1_c"] ?? ($_POST["tajuk3_c"] ?? ''))), 0, 255);
        $publication_b = mb_substr(trim($_POST["publication_b"] ?? ($_POST["publication1_b"] ?? ($_POST["publication3_b"] ?? ''))), 0, 255);
        $publication_c = mb_substr(trim($_POST["publication_c"] ?? ($_POST["publication1_c"] ?? ($_POST["publication3_c"] ?? ''))), 0, 255);
        $physicaldesc_b = mb_substr(trim($_POST["physicaldesc_b"] ?? ($_POST["physicaldesc1_b"] ?? ($_POST["physicaldesc3_b"] ?? ''))), 0, 255);
        $physicaldesc_c = mb_substr(trim($_POST["physicaldesc_c"] ?? ($_POST["physicaldesc1_c"] ?? ($_POST["physicaldesc3_c"] ?? ''))), 0, 255);
        $physicaldesc_e = mb_substr(trim($_POST["physicaldesc_e"] ?? ($_POST["physicaldesc1_e"] ?? ($_POST["physicaldesc3_e"] ?? ''))), 0, 255);
        $series_v = mb_substr(trim($_POST["series_v"] ?? ($_POST["series1_v"] ?? ($_POST["series3_v"] ?? ''))), 0, 150);
        $sumber_b = mb_substr(trim($_POST["sumber_b"] ?? ($_POST["sumber0_b"] ?? ($_POST["sumber3_b"] ?? ''))), 0, 255);
        $sumber_e = mb_substr(trim($_POST["sumber_e"] ?? ($_POST["sumber0_e"] ?? ($_POST["sumber3_e"] ?? ''))), 0, 255);
        $lokasi_b = mb_substr(trim($_POST["lokasi_b"] ?? ($_POST["lokasi1_b"] ?? ($_POST["lokasi3_b"] ?? ''))), 0, 50);
        $lokasi_c = mb_substr(trim($_POST["lokasi_c"] ?? ($_POST["lokasi1_c"] ?? ($_POST["lokasi3_c"] ?? ''))), 0, 50);

        // Indicators
        $pengarang_in = mb_substr(trim($_POST["pengarang_in"] ?? ($_POST["pengarang1_in"] ?? ($_POST["pengarang3_in"] ?? ''))), 0, 2);
        $tajuk_in = mb_substr(trim($_POST["tajuk_in"] ?? ($_POST["tajuk1_in"] ?? ($_POST["tajuk3_in"] ?? ''))), 0, 2);
        $fcnotes_in = mb_substr(trim($_POST["fcnotes_in"] ?? ($_POST["fcnotes1_in"] ?? ($_POST["fcnotes3_in"] ?? ''))), 0, 2);
        $sumber_in = mb_substr(trim($_POST["sumber_in"] ?? ($_POST["sumber0_in"] ?? ($_POST["sumber3_in"] ?? ''))), 0, 2);

        // Extra ISBNs (1-20)
        $isbns = [];
        for ($i = 1; $i <= 20; $i++) {
            $isbns[$i] = mb_substr(str_replace(['-', '–', '—'], '', trim($_POST["isbn_$i"] ?? ($_POST["isbn1_$i"] ?? ($_POST["isbn3_$i"] ?? '')))), 0, 60);
        }

        $link_encoded = !empty($link) ? urlencode($link) : '';
        $search_cloud = $tajuk . " " . $tajuk_b . " " . $tajuk_c . " \\ " . $pengarang . " " . $pengarang_d . " \\ $isbn $issn \\ $callnum " . $callnum_b;
        $flash_alerts = [];

        if (!empty($tajuk) && !empty($jenis) && $jenis !== '0') {
            try {
                if ($is_update) {
                    // UPDATE RECORD
                    $timestamp = preg_replace('/[^0-9]/', '', (string)($_POST["instimestamp"] ?? ($_POST["timestamp3"] ?? '')));
                    $inputdate = $_POST["inputdate"] ?? ($_POST["inputdate3"] ?? '');
                    $lastupdateby = $_SESSION['username'] ?? '';
                    $unlink_pdf = $_POST["unlink_pdf"] ?? ($_POST["unlink3"] ?? '');
                    $unlink_cover = $_POST["unlink_cover"] ?? ($_POST["unlinkimage3"] ?? '');

                    // 1. Update eg_item
                    $stmt = mysqli_prepare($GLOBALS["conn"], "UPDATE eg_item SET 
                        `38isbn`=?, `38issn`=?, `38localcallnum`=?, `38localcallnum_b`=?, 
                        `38author`=?, `38author_d`=?, `38title`=?, `38title_b`=?, `38title_c`=?, 
                        `38edition`=?, `38publication`=?, `38publication_b`=?, `38publication_c`=?, 
                        `38physicaldesc`=?, `38physicaldesc_b`=?, `38physicaldesc_c`=?, `38physicaldesc_e`=?, 
                        `38series`=?, `38series_v`=?, `38notes`=?, `38fcnotes`=?, 
                        `38source`=?, `38source_b`=?, `38source_e`=?, `38location`=?, `38location_b`=?, `38location_c`=?, 
                        `38link`=?, `39type`=?, `39subjectheading`=?, `39language`=?, 
                        `40lastupdateby`=?, `50search_cloud`=? 
                    WHERE id=?");

                    mysqli_stmt_bind_param($stmt, "sssssssssssssssssssssssssssssssssi",
                        $isbn,
                        $issn,
                        $callnum,
                        $callnum_b,
                        $pengarang,
                        $pengarang_d,
                        $tajuk,
                        $tajuk_b,
                        $tajuk_c,
                        $edition,
                        $publication,
                        $publication_b,
                        $publication_c,
                        $physicaldesc,
                        $physicaldesc_b,
                        $physicaldesc_c,
                        $physicaldesc_e,
                        $series,
                        $series_v,
                        $notes,
                        $fcnotes,
                        $sumber,
                        $sumber_b,
                        $sumber_e,
                        $lokasi,
                        $lokasi_b,
                        $lokasi_c,
                        $link_encoded,
                        $jenis,
                        $subjectheading,
                        $language,
                        $lastupdateby,
                        $search_cloud,
                        $edit_id
                    );
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_close($stmt);

                    // 3. Update eg_item_indicator
                    $stmt = mysqli_prepare($GLOBALS["conn"], "DELETE FROM eg_item_indicator WHERE eg_item_id=?");
                    mysqli_stmt_bind_param($stmt, "i", $edit_id);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_close($stmt);

                    $stmt = mysqli_prepare($GLOBALS["conn"], "INSERT INTO eg_item_indicator VALUES(null, ?, ?, ?, ?, ?)");
                    mysqli_stmt_bind_param($stmt, "issss",
                        $edit_id,
                        $pengarang_in,
                        $tajuk_in,
                        $fcnotes_in,
                        $sumber_in
                    );
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_close($stmt);

                    // 4. Update eg_item_isbn
                    $stmt = mysqli_prepare($GLOBALS["conn"], "DELETE FROM eg_item_isbn WHERE eg_item_id=?");
                    mysqli_stmt_bind_param($stmt, "i", $edit_id);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_close($stmt);

                    $stmt = mysqli_prepare($GLOBALS["conn"], "INSERT INTO eg_item_isbn VALUES(null, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    mysqli_stmt_bind_param($stmt, "issssssssssssssssssss",
                        $edit_id,
                        $isbns[1], $isbns[2], $isbns[3], $isbns[4], $isbns[5],
                        $isbns[6], $isbns[7], $isbns[8], $isbns[9], $isbns[10],
                        $isbns[11], $isbns[12], $isbns[13], $isbns[14], $isbns[15],
                        $isbns[16], $isbns[17], $isbns[18], $isbns[19], $isbns[20]
                    );
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_close($stmt);

                    // 5. Handle File Unlinks
                    $dir_yearTemp = substr((string)$inputdate, -4);
                    if (!preg_match('/^[0-9]{4}$/', $dir_yearTemp)) {
                        $dir_yearTemp = date('Y');
                    }

                    if ($unlink_pdf === 'unlink' || $unlink_pdf === '1' || $unlink_pdf === 'TRUE') {
                        $pdfPath = "../$pdf_upload_directory/$dir_yearTemp/{$edit_id}_{$timestamp}.pdf";
                        $realPdf = realpath($pdfPath);
                        $baseDir = realpath("../$pdf_upload_directory");
                        if ($realPdf && $baseDir && strpos($realPdf, $baseDir) === 0 && is_file($realPdf)) {
                            unlink($realPdf);
                        }

                        $stmt = mysqli_prepare($GLOBALS["conn"], "UPDATE eg_item SET `39pdfattach`='FALSE' WHERE id=?");
                        mysqli_stmt_bind_param($stmt, "i", $edit_id);
                        mysqli_stmt_execute($stmt);
                        mysqli_stmt_close($stmt);
                    }

                    if ($unlink_cover === 'unlink' || $unlink_cover === '1' || $unlink_cover === 'TRUE') {
                        $jpgPath = "../$cover_upload_directory/$dir_yearTemp/{$edit_id}_{$timestamp}.jpg";
                        $realJpg = realpath($jpgPath);
                        $baseJpg = realpath("../$cover_upload_directory");
                        if ($realJpg && $baseJpg && strpos($realJpg, $baseJpg) === 0 && is_file($realJpg)) {
                            unlink($realJpg);
                        }

                        $stmt = mysqli_prepare($GLOBALS["conn"], "UPDATE eg_item SET `39imageatt`='FALSE' WHERE id=?");
                        mysqli_stmt_bind_param($stmt, "i", $edit_id);
                        mysqli_stmt_execute($stmt);
                        mysqli_stmt_close($stmt);
                    }

                    $flash_alerts[] = ['type' => 'success', 'msg' => 'The catalog record has been successfully updated!'];

                    // 6. Handle New / Replacement Uploads
                    $idUpload = $edit_id;
                    $timestampUpload = $timestamp;
                    $inputdateUpload = $inputdate;

                    $max_size = (int)$pdf_upload_maxsize * 1024 * 1024;
                    if (isset($_FILES['file1']['name']) && !empty($_FILES['file1']['name']) && $_FILES['file1']['error'] !== UPLOAD_ERR_NO_FILE) {
                        $allowed_uploaddir = "../" . $pdf_upload_directory;
                        $allowed_ext = "pdf";
                        $allowed_fieldname = "file1";
                        include '../includes/uploadcheck.php';
                        if ($successupload == 'TRUE') {
                            include '../includes/uploadnow.php';
                            $flash_alerts[] = ['type' => 'success', 'msg' => 'PDF Attachment uploaded successfully!'];
                            $stmt = mysqli_prepare($GLOBALS["conn"], "UPDATE eg_item SET `39pdfattach`='TRUE' WHERE id=?");
                            mysqli_stmt_bind_param($stmt, "i", $idUpload);
                            mysqli_stmt_execute($stmt);
                            mysqli_stmt_close($stmt);
                        } elseif ($successupload == 'FALSE SIZE') {
                            $flash_alerts[] = ['type' => 'danger', 'msg' => 'Upload aborted: PDF size exceeds maximum allowed.'];
                        } else {
                            $flash_alerts[] = ['type' => 'danger', 'msg' => 'Upload aborted: Incorrect file type for PDF.'];
                        }
                    }

                    $uploadedCover = false;
                    $max_size = (int)$cover_upload_maxsize * 1024 * 1024;
                    if (isset($_FILES['imageatt1']['name']) && !empty($_FILES['imageatt1']['name']) && $_FILES['imageatt1']['error'] !== UPLOAD_ERR_NO_FILE) {
                        $allowed_uploaddir = "../" . $cover_upload_directory;
                        $allowed_ext = "jpg";
                        $allowed_fieldname = "imageatt1";
                        include '../includes/uploadcheck.php';
                        if ($successupload == 'TRUE') {
                            include '../includes/uploadnow.php';
                            $flash_alerts[] = ['type' => 'success', 'msg' => 'Cover image uploaded successfully!'];
                            $stmt = mysqli_prepare($GLOBALS["conn"], "UPDATE eg_item SET `39imageatt`='TRUE' WHERE id=?");
                            mysqli_stmt_bind_param($stmt, "i", $idUpload);
                            mysqli_stmt_execute($stmt);
                            mysqli_stmt_close($stmt);
                            $uploadedCover = true;
                        } elseif ($successupload == 'FALSE SIZE') {
                            $flash_alerts[] = ['type' => 'danger', 'msg' => 'Upload aborted: Cover image size exceeds maximum allowed.'];
                        } else {
                            $flash_alerts[] = ['type' => 'danger', 'msg' => 'Upload aborted: Incorrect image type.'];
                        }
                    }

                    // Auto-download remote cover if no manual file uploaded
                    if (!$uploadedCover && !empty($_POST['remote_cover_url'])) {
                        $remoteUrl = trim($_POST['remote_cover_url']);
                        if (filter_var($remoteUrl, FILTER_VALIDATE_URL) && preg_match('/^https?:\/\//i', $remoteUrl)) {
                            $dir_year = ($inputdateUpload == null) ? date("Y") : substr((string)$inputdateUpload, -4);
                            if (!preg_match('/^[0-9]{4}$/', $dir_year)) $dir_year = date("Y");
                            $coverTargetDir = "../" . $cover_upload_directory . "/" . $dir_year;
                            if (!is_dir($coverTargetDir)) {
                                mkdir($coverTargetDir, 0755, true);
                                file_put_contents("$coverTargetDir/index.php", "<html lang='en'><head><title>403 Forbidden</title></head><body><h1>Forbidden</h1></body></html>");
                            }
                            $targetCoverPath = $coverTargetDir . "/" . $idUpload . "_" . $timestampUpload . ".jpg";

                            $chCover = curl_init($remoteUrl);
                            curl_setopt($chCover, CURLOPT_RETURNTRANSFER, true);
                            curl_setopt($chCover, CURLOPT_FOLLOWLOCATION, true);
                            curl_setopt($chCover, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)');
                            curl_setopt($chCover, CURLOPT_TIMEOUT, 10);
                            curl_setopt($chCover, CURLOPT_SSL_VERIFYPEER, false);
                            $imgData = curl_exec($chCover);
                            $httpCode = curl_getinfo($chCover, CURLINFO_HTTP_CODE);

                            if ($httpCode === 200 && !empty($imgData) && strlen($imgData) > 100) {
                                $imgResource = @imagecreatefromstring($imgData);
                                if ($imgResource !== false) {
                                    imagejpeg($imgResource, $targetCoverPath, 90);
                                    $stmt = mysqli_prepare($GLOBALS["conn"], "UPDATE eg_item SET `39imageatt`='TRUE' WHERE id=?");
                                    mysqli_stmt_bind_param($stmt, "i", $idUpload);
                                    mysqli_stmt_execute($stmt);
                                    mysqli_stmt_close($stmt);
                                    $flash_alerts[] = ['type' => 'success', 'msg' => 'Cover image auto-downloaded and attached successfully!'];
                                }
                            }
                        }
                    }

                    $_SESSION['flash_alerts'] = $flash_alerts;
                    header("Location: ../details.php?det=" . $edit_id . $ctxQuery);
                    exit;

                } else {
                    // INSERT NEW RECORD
                    $tarikh_masuk = date("d/m/Y");
                    $input_oleh = $_SESSION['username'] ?? '';
                    $instimestamp = time();

                    // 1. Insert into eg_item (explicit column mapping)
                    $stmt = mysqli_prepare($GLOBALS["conn"], "INSERT INTO eg_item (
                        `38isbn`, `38issn`, `38localcallnum`, `38localcallnum_b`, `38author`, `38author_d`,
                        `38title`, `38title_b`, `38title_c`, `38edition`, `38publication`, `38publication_b`,
                        `38publication_c`, `38physicaldesc`, `38physicaldesc_b`, `38physicaldesc_c`,
                        `38physicaldesc_e`, `38series`, `38series_v`, `38notes`, `38fcnotes`,
                        `38source`, `38source_b`, `38source_e`, `38location`, `38location_b`,
                        `38location_c`, `38link`, `39type`, `39subjectheading`, `39pdfattach`,
                        `39imageatt`, `39language`, `40inputby`, `40inputdate`, `40proposedelete`,
                        `40lastupdateby`, `40instimestamp`, `41hits`, `50search_cloud`
                    ) VALUES (
                        ?, ?, ?, ?, ?, ?,
                        ?, ?, ?, ?, ?, ?,
                        ?, ?, ?, ?,
                        ?, ?, ?, ?, ?,
                        ?, ?, ?, ?, ?,
                        ?, ?, ?, ?, 'FALSE',
                        'FALSE', ?, ?, ?, 'FALSE',
                        null, ?, 0, ?
                    )");

                    mysqli_stmt_bind_param($stmt, "sssssssssssssssssssssssssssssssssss",
                        $isbn,
                        $issn,
                        $callnum,
                        $callnum_b,
                        $pengarang,
                        $pengarang_d,
                        $tajuk,
                        $tajuk_b,
                        $tajuk_c,
                        $edition,
                        $publication,
                        $publication_b,
                        $publication_c,
                        $physicaldesc,
                        $physicaldesc_b,
                        $physicaldesc_c,
                        $physicaldesc_e,
                        $series,
                        $series_v,
                        $notes,
                        $fcnotes,
                        $sumber,
                        $sumber_b,
                        $sumber_e,
                        $lokasi,
                        $lokasi_b,
                        $lokasi_c,
                        $link_encoded,
                        $jenis,
                        $subjectheading,
                        $language,
                        $input_oleh,
                        $tarikh_masuk,
                        $instimestamp,
                        $search_cloud
                    );
                    mysqli_stmt_execute($stmt);
                    $new_id = mysqli_insert_id($GLOBALS["conn"]);
                    mysqli_stmt_close($stmt);

                    // 3. Insert into eg_item_indicator
                    $stmt = mysqli_prepare($GLOBALS["conn"], "INSERT INTO eg_item_indicator VALUES(null, ?, ?, ?, ?, ?)");
                    mysqli_stmt_bind_param($stmt, "issss",
                        $new_id,
                        $pengarang_in,
                        $tajuk_in,
                        $fcnotes_in,
                        $sumber_in
                    );
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_close($stmt);

                    // 4. Insert into eg_item_isbn
                    $stmt = mysqli_prepare($GLOBALS["conn"], "INSERT INTO eg_item_isbn VALUES(null, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    mysqli_stmt_bind_param($stmt, "issssssssssssssssssss",
                        $new_id,
                        $isbns[1], $isbns[2], $isbns[3], $isbns[4], $isbns[5],
                        $isbns[6], $isbns[7], $isbns[8], $isbns[9], $isbns[10],
                        $isbns[11], $isbns[12], $isbns[13], $isbns[14], $isbns[15],
                        $isbns[16], $isbns[17], $isbns[18], $isbns[19], $isbns[20]
                    );
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_close($stmt);

                    $flash_alerts[] = ['type' => 'success', 'msg' => "Catalog record <strong>" . htmlspecialchars($tajuk, ENT_QUOTES, 'UTF-8') . "</strong> has been successfully registered!"];

                    // 5. Handle Uploads
                    $idUpload = $new_id;
                    $timestampUpload = $instimestamp;
                    $inputdateUpload = null;

                    $max_size = (int)$pdf_upload_maxsize * 1024 * 1024;
                    if (isset($_FILES['file1']['name']) && !empty($_FILES['file1']['name']) && $_FILES['file1']['error'] !== UPLOAD_ERR_NO_FILE) {
                        $allowed_uploaddir = "../" . $pdf_upload_directory;
                        $allowed_ext = "pdf";
                        $allowed_fieldname = "file1";
                        include '../includes/uploadcheck.php';
                        if ($successupload == 'TRUE') {
                            include '../includes/uploadnow.php';
                            $flash_alerts[] = ['type' => 'success', 'msg' => 'PDF Attachment uploaded successfully!'];
                            $stmt = mysqli_prepare($GLOBALS["conn"], "UPDATE eg_item SET `39pdfattach`='TRUE' WHERE id=?");
                            mysqli_stmt_bind_param($stmt, "i", $idUpload);
                            mysqli_stmt_execute($stmt);
                            mysqli_stmt_close($stmt);
                        } elseif ($successupload == 'FALSE SIZE') {
                            $flash_alerts[] = ['type' => 'danger', 'msg' => 'Upload aborted: PDF size exceeds maximum allowed.'];
                        } else {
                            $flash_alerts[] = ['type' => 'danger', 'msg' => 'Upload aborted: Incorrect file type for PDF.'];
                        }
                    }

                    $uploadedCover = false;
                    $max_size = (int)$cover_upload_maxsize * 1024 * 1024;
                    if (isset($_FILES['imageatt1']['name']) && !empty($_FILES['imageatt1']['name']) && $_FILES['imageatt1']['error'] !== UPLOAD_ERR_NO_FILE) {
                        $allowed_uploaddir = "../" . $cover_upload_directory;
                        $allowed_ext = "jpg";
                        $allowed_fieldname = "imageatt1";
                        include '../includes/uploadcheck.php';
                        if ($successupload == 'TRUE') {
                            include '../includes/uploadnow.php';
                            $flash_alerts[] = ['type' => 'success', 'msg' => 'Cover image uploaded successfully!'];
                            $stmt = mysqli_prepare($GLOBALS["conn"], "UPDATE eg_item SET `39imageatt`='TRUE' WHERE id=?");
                            mysqli_stmt_bind_param($stmt, "i", $idUpload);
                            mysqli_stmt_execute($stmt);
                            mysqli_stmt_close($stmt);
                            $uploadedCover = true;
                        } elseif ($successupload == 'FALSE SIZE') {
                            $flash_alerts[] = ['type' => 'danger', 'msg' => 'Upload aborted: Cover image size exceeds maximum allowed.'];
                        } else {
                            $flash_alerts[] = ['type' => 'danger', 'msg' => 'Upload aborted: Incorrect image type.'];
                        }
                    }

                    // Auto-download remote cover if no manual file uploaded
                    if (!$uploadedCover && !empty($_POST['remote_cover_url'])) {
                        $remoteUrl = trim($_POST['remote_cover_url']);
                        if (filter_var($remoteUrl, FILTER_VALIDATE_URL) && preg_match('/^https?:\/\//i', $remoteUrl)) {
                            $dir_year = ($inputdateUpload == null) ? date("Y") : substr((string)$inputdateUpload, -4);
                            if (!preg_match('/^[0-9]{4}$/', $dir_year)) $dir_year = date("Y");
                            $coverTargetDir = "../" . $cover_upload_directory . "/" . $dir_year;
                            if (!is_dir($coverTargetDir)) {
                                mkdir($coverTargetDir, 0755, true);
                                file_put_contents("$coverTargetDir/index.php", "<html lang='en'><head><title>403 Forbidden</title></head><body><h1>Forbidden</h1></body></html>");
                            }
                            $targetCoverPath = $coverTargetDir . "/" . $idUpload . "_" . $timestampUpload . ".jpg";

                            $chCover = curl_init($remoteUrl);
                            curl_setopt($chCover, CURLOPT_RETURNTRANSFER, true);
                            curl_setopt($chCover, CURLOPT_FOLLOWLOCATION, true);
                            curl_setopt($chCover, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)');
                            curl_setopt($chCover, CURLOPT_TIMEOUT, 10);
                            curl_setopt($chCover, CURLOPT_SSL_VERIFYPEER, false);
                            $imgData = curl_exec($chCover);
                            $httpCode = curl_getinfo($chCover, CURLINFO_HTTP_CODE);

                            if ($httpCode === 200 && !empty($imgData) && strlen($imgData) > 100) {
                                $imgResource = @imagecreatefromstring($imgData);
                                if ($imgResource !== false) {
                                    imagejpeg($imgResource, $targetCoverPath, 90);
                                    $stmt = mysqli_prepare($GLOBALS["conn"], "UPDATE eg_item SET `39imageatt`='TRUE' WHERE id=?");
                                    mysqli_stmt_bind_param($stmt, "i", $idUpload);
                                    mysqli_stmt_execute($stmt);
                                    mysqli_stmt_close($stmt);
                                    $flash_alerts[] = ['type' => 'success', 'msg' => 'Cover image auto-downloaded and attached successfully!'];
                                }
                            }
                        }
                    }

                    // 6. Handle Auto-Creation of 1 Physical Copy if requested
                    $auto_create_copy = !empty($_POST['btn_auto_copy']);
                    $created_accession_num = null;
                    if ($auto_create_copy) {
                        $last_accession_num = 0;
                        $query_last = "SELECT `39accessnum` FROM eg_item_copies ORDER BY id DESC LIMIT 1";
                        $result_last = mysqli_query($GLOBALS["conn"], $query_last);
                        if ($result_last && mysqli_num_rows($result_last) > 0) {
                            $row_last = mysqli_fetch_assoc($result_last);
                            if (!empty($row_last["39accessnum"]) && is_numeric($row_last["39accessnum"])) {
                                $last_accession_num = (int)$row_last["39accessnum"];
                            }
                        }

                        $query_max = "SELECT MAX(CAST(`39accessnum` AS UNSIGNED)) AS max_acc FROM eg_item_copies WHERE `39accessnum` REGEXP '^[0-9]+$'";
                        $result_max = mysqli_query($GLOBALS["conn"], $query_max);
                        if ($result_max && mysqli_num_rows($result_max) > 0) {
                            $row_max = mysqli_fetch_assoc($result_max);
                            if (!empty($row_max["max_acc"]) && (int)$row_max["max_acc"] > $last_accession_num) {
                                $last_accession_num = (int)$row_max["max_acc"];
                            }
                        }

                        $created_accession_num = sprintf("%010d", $last_accession_num + 1);
                        $copy_status = 'AVAILABLE';
                        $copy_volume = '';
                        $copy_issue = '';
                        $copy_year = '';
                        $copy_is_reference = 'NO';
                        $copy_invoice_a = '';
                        $copy_invoice_b = '';
                        $copy_invoice_c = '';
                        $copy_time = time();

                        $stmt_copy = mysqli_prepare($GLOBALS["conn"], "INSERT INTO eg_item_copies (
                            `eg_item_id`, `39accessnum`, `39status`, `39volume`, `39issue`, `39year`, `39is_reference`,
                            `39invoice_a`, `39invoice_b`, `39invoice_c`, `39addedon`, `39lastchange`
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

                        if ($stmt_copy) {
                            mysqli_stmt_bind_param($stmt_copy, "isssssssssii",
                                $new_id,
                                $created_accession_num,
                                $copy_status,
                                $copy_volume,
                                $copy_issue,
                                $copy_year,
                                $copy_is_reference,
                                $copy_invoice_a,
                                $copy_invoice_b,
                                $copy_invoice_c,
                                $copy_time,
                                $copy_time
                            );
                            mysqli_stmt_execute($stmt_copy);
                            mysqli_stmt_close($stmt_copy);

                            $flash_alerts[] = ['type' => 'success', 'msg' => "1 physical copy (Accession No: <strong>" . htmlspecialchars($created_accession_num, ENT_QUOTES, 'UTF-8') . "</strong>) has been automatically created with status <strong>AVAILABLE</strong>."];
                        }
                    }

                    $is_created_serial = ($ctx_delr === 'serials' || $jenis === '4' || strcasecmp((string)$jenis, 'Serial') === 0 || strcasecmp(idToType($jenis), 'Serial') === 0);
                    $_SESSION['flash_created_record'] = [
                        'id' => $new_id,
                        'title' => $tajuk,
                        'alerts' => $flash_alerts,
                        'is_serial' => $is_created_serial,
                        'auto_copy_created' => $auto_create_copy,
                        'accession_num' => $created_accession_num
                    ];
                    header("Location: reg.php?created=" . $new_id . ($is_created_serial ? "&delr=serials" : ""));
                    exit;
                }
            } catch (Throwable $e) {
                $_SESSION['flash_alerts'] = [['type' => 'danger', 'msg' => 'Database operation error: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8')]];
                header("Location: reg.php" . ($is_update ? "?upd={$edit_id}{$ctxQuery}" : ""));
                exit;
            }
        } else {
            if ($is_update) {
                $_SESSION['flash_alerts'] = [['type' => 'danger', 'msg' => 'Error: Required fields (Title and Material Type) cannot be empty. Record restored.']];
                header("Location: reg.php?upd=" . $edit_id . $ctxQuery);
            } else {
                $_SESSION['flash_reg_error'] = 'Input cancelled. Please ensure required fields (Title and Material Type) are filled.';
                header("Location: reg.php");
            }
            exit;
        }
    }

    // Determine Mode & Load Data if Editing
    $req_id = $_GET["upd"] ?? ($_GET["id"] ?? 0);
    $is_edit = false;
    $edit_id = 0;

    // Field default variables
    $val_tajuk = '';
    $val_lokasi = '';
    $val_link = '';
    $val_pengarang = '';
    $val_sumber = '';
    $val_callnum = '';
    $val_isbn = '';
    $val_issn = '';
    $val_edition = '';
    $val_publication = '';
    $val_physicaldesc = '';
    $val_series = '';
    $val_notes = '';
    $val_fcnotes = '';
    $val_jenis = $_GET["jenis1"] ?? ($_GET["jenis"] ?? 0);
    $val_pdfattach = 'FALSE';
    $val_imageatt = 'FALSE';
    $val_subjectheading = '';
    $val_language = $tag_041_selectable_default ?? 'zsm';
    $val_inputdate = '';
    $val_timestamp = '';

    // Subfield defaults
    $val_callnum_b = '';
    $val_pengarang_d = '';
    $val_tajuk_b = '';
    $val_tajuk_c = '';
    $val_publication_b = '';
    $val_publication_c = '';
    $val_physicaldesc_b = '';
    $val_physicaldesc_c = '';
    $val_physicaldesc_e = '';
    $val_series_v = '';
    $val_sumber_b = '';
    $val_sumber_e = '';
    $val_lokasi_b = '';
    $val_lokasi_c = '';

    // Indicator defaults
    $val_tajuk_in = '';
    $val_pengarang_in = '';
    $val_fcnotes_in = '';
    $val_sumber_in = '';

    // Extra ISBNs
    $val_isbns = array_fill(1, 20, '');

    if (!empty($req_id) && is_numeric($req_id)) {
        $edit_id = (int)$req_id;
        $stmt = mysqli_prepare($GLOBALS["conn"], "SELECT * FROM eg_item WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $edit_id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($res);
        mysqli_stmt_close($stmt);

        if ($row) {
            $is_edit = true;
            $val_tajuk = $row["38title"] ?? '';
            $val_lokasi = $row["38location"] ?? '';
            $val_link = !empty($row["38link"]) ? urldecode($row["38link"]) : '';
            $val_pengarang = $row["38author"] ?? '';
            $val_sumber = $row["38source"] ?? '';
            $val_callnum = $row["38localcallnum"] ?? '';
            $val_isbn = $row["38isbn"] ?? '';
            $val_issn = $row["38issn"] ?? '';
            $val_edition = $row["38edition"] ?? '';
            $val_publication = $row["38publication"] ?? '';
            $val_physicaldesc = $row["38physicaldesc"] ?? '';
            $val_series = $row["38series"] ?? '';
            $val_notes = $row["38notes"] ?? '';
            $val_fcnotes = $row["38fcnotes"] ?? '';
            $val_jenis = $row["39type"] ?? 0;
            $val_pdfattach = $row["39pdfattach"] ?? 'FALSE';
            $val_subjectheading = $row["39subjectheading"] ?? '';
            $val_imageatt = $row["39imageatt"] ?? 'FALSE';
            $val_language = !empty($row["39language"]) ? $row["39language"] : ($tag_041_selectable_default ?? 'zsm');
            $val_inputdate = $row["40inputdate"] ?? '';
            $val_timestamp = $row["40instimestamp"] ?? '';

            // Extended subfields (loaded directly from eg_item)
            $val_callnum_b = $row["38localcallnum_b"] ?? '';
            $val_pengarang_d = $row["38author_d"] ?? '';
            $val_tajuk_b = $row["38title_b"] ?? '';
            $val_tajuk_c = $row["38title_c"] ?? '';
            $val_publication_b = $row["38publication_b"] ?? '';
            $val_publication_c = $row["38publication_c"] ?? '';
            $val_physicaldesc_b = $row["38physicaldesc_b"] ?? '';
            $val_physicaldesc_c = $row["38physicaldesc_c"] ?? '';
            $val_physicaldesc_e = $row["38physicaldesc_e"] ?? '';
            $val_series_v = $row["38series_v"] ?? '';
            $val_sumber_b = $row["38source_b"] ?? '';
            $val_sumber_e = $row["38source_e"] ?? '';
            $val_lokasi_b = $row["38location_b"] ?? '';
            $val_lokasi_c = $row["38location_c"] ?? '';

            // Indicators
            $stmt_ind = mysqli_prepare($GLOBALS["conn"], "SELECT * FROM eg_item_indicator WHERE eg_item_id = ?");
            mysqli_stmt_bind_param($stmt_ind, "i", $edit_id);
            mysqli_stmt_execute($stmt_ind);
            $res_ind = mysqli_stmt_get_result($stmt_ind);
            if ($res_ind && $row_ind = mysqli_fetch_assoc($res_ind)) {
                $val_tajuk_in = $row_ind["38title_i"] ?? '';
                $val_pengarang_in = $row_ind["38author_i"] ?? '';
                $val_fcnotes_in = $row_ind["38fcnotes_i"] ?? '';
                $val_sumber_in = $row_ind["38source_i"] ?? '';
            }
            mysqli_stmt_close($stmt_ind);

            // Additional ISBNs
            $stmt_isbn = mysqli_prepare($GLOBALS["conn"], "SELECT * FROM eg_item_isbn WHERE eg_item_id = ?");
            mysqli_stmt_bind_param($stmt_isbn, "i", $edit_id);
            mysqli_stmt_execute($stmt_isbn);
            $res_isbn = mysqli_stmt_get_result($stmt_isbn);
            if ($res_isbn && $row_isbn = mysqli_fetch_assoc($res_isbn)) {
                for ($i = 1; $i <= 20; $i++) {
                    $val_isbns[$i] = $row_isbn["isbn$i"] ?? '';
                }
            }
            mysqli_stmt_close($stmt_isbn);
        }
    }

    $page_title = $is_edit ? "Update Catalog Record (#$edit_id)" : "Add Catalog Record";
    $header_title = $is_edit ? "Update Catalog Record (#$edit_id)" : "Insert New MARC Bibliographic Record (* Mandatory)";
    $submit_text = $is_edit ? "Update Record" : "Register Record";
?>
<!DOCTYPE HTML>
<html lang='en'>

<head>
    <meta charset="utf-8">
    <title><?php echo htmlspecialchars($product_name, ENT_QUOTES, 'UTF-8');?> : <?php echo htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8');?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="../<?php echo htmlspecialchars($mini_icon_path, ENT_QUOTES, 'UTF-8');?>" rel="icon" type="image/png" />
    <link href="../assets/styles/style.css?v=3" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
    <script type="text/javascript">
        function openAppModal(url, titleText, sizeClass) {
            var modal = document.getElementById('appModal');
            var dialog = document.getElementById('appModalDialog');
            var title = document.getElementById('appModalTitle');
            var iframe = document.getElementById('appModalIframe');
            
            title.textContent = titleText || 'Action';
            dialog.className = 'modal-dialog ' + (sizeClass || 'modal-md');
            iframe.src = url;
            
            modal.classList.add('show');
            document.body.style.overflow = 'hidden';
            return false;
        }

        function closeAppModal(shouldReload) {
            var modal = document.getElementById('appModal');
            var iframe = document.getElementById('appModalIframe');
            if (modal) {
                modal.classList.remove('show');
                document.body.style.overflow = '';
                iframe.src = 'about:blank';
            }
            if (shouldReload) {
                window.location.reload();
            }
        }

        function closeDuplicateModal() {
            var modal = document.getElementById('duplicateItemModal');
            if (modal) {
                modal.classList.remove('show');
                document.body.style.overflow = '';
            }
        }

        function showList() {
            openAppModal("shselector.php?clr=reg", "Select Subject Heading", "modal-md");
        }

        function openSHAdd() {
            openAppModal("addsubject.php?fr=1", "Add Subject Heading", "modal-md");
        }

        function pickSubjectHeading(symbol) {
            var field = document.someform.subjectheading || document.someform.subjectheading1 || document.someform.subjectheading3;
            if (field) {
                if (field.value === '') {
                    field.value = symbol + '|';
                } else {
                    field.value += symbol + '|';
                }
            }
            closeAppModal(false);
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeAppModal(false);
            }
        });

        function ValidateForm() {
            var tajukInput = document.someform.tajuk || document.someform.tajuk1 || document.someform.tajuk3;
            var jenisInput = document.someform.jenis || document.someform.jenis1 || document.someform.jenis3;
            
            var tajuk = tajukInput ? tajukInput.value : '';
            var jenis = jenisInput ? jenisInput.value : '';

            if (tajuk.trim() === '') {
                alert('Title (245) cannot be empty.');
                if (tajukInput) tajukInput.focus();
                return false;
            }
            if (jenis === '' || jenis === '0') {
                alert('Material Type must be selected.');
                if (jenisInput) jenisInput.focus();
                return false;
            }
            return true;
        }
    </script>
</head>

<body>
    <?php include_once '../includes/loggedinfo.php';?>
            
    <div class="app-container">
        <?php
            if (!empty($_SESSION['flash_reg_error'])) {
                echo "<div class='alert alert-danger'>" . htmlspecialchars($_SESSION['flash_reg_error'], ENT_QUOTES, 'UTF-8') . "</div>";
                unset($_SESSION['flash_reg_error']);
            }

            if (!empty($_SESSION['flash_alerts'])) {
                foreach ($_SESSION['flash_alerts'] as $al) {
                    $atype = htmlspecialchars($al['type'] ?? 'info', ENT_QUOTES, 'UTF-8');
                    $amsg = $al['msg'] ?? '';
                    echo "<div class='alert alert-{$atype}'>{$amsg}</div>";
                }
                unset($_SESSION['flash_alerts']);
            }

            if (isset($_GET['created']) && !empty($_SESSION['flash_created_record'])) {
                $created_rec = $_SESSION['flash_created_record'];
                $id_created = (int)$created_rec['id'];
                $is_serial_rec = !empty($created_rec['is_serial']) || (isset($_GET['delr']) && $_GET['delr'] === 'serials');
                if (!empty($created_rec['alerts'])) {
                    foreach ($created_rec['alerts'] as $al) {
                        $atype = htmlspecialchars($al['type'] ?? 'info', ENT_QUOTES, 'UTF-8');
                        $amsg = $al['msg'] ?? '';
                        echo "<div class='alert alert-{$atype}'>{$amsg}</div>";
                    }
                }
                echo "<div class='card text-center p-3 my-3'><div class='d-flex justify-content-center gap-2 flex-wrap'>";
                if (!empty($created_rec['auto_copy_created'])) {
                    echo "<a class='btn btn-primary' href='../details.php?det=$id_created'><i class='fa-solid fa-circle-info me-1'></i> View Record Details &rarr;</a> ";
                    echo "<a class='btn btn-success' href='reg_copies.php?id=$id_created" . ($is_serial_rec ? "&delr=serials" : "") . "'><i class='fa-solid fa-layer-group me-1'></i> Manage / Add More Copies</a> ";
                } else {
                    echo "<a class='btn btn-primary' href='reg_copies.php?id=$id_created" . ($is_serial_rec ? "&delr=serials" : "") . "'><i class='fa-solid fa-layer-group me-1'></i> Add Copies for this Record &rarr;</a> ";
                }
                if ($is_serial_rec) {
                    echo "<a class='btn btn-secondary' href='reg.php?jenis=4&delr=serials'><i class='fa-solid fa-plus me-1'></i> Add Another Serial Record</a> ";
                    echo "<a class='btn btn-secondary' href='serials.php'><i class='fa-solid fa-arrow-left me-1'></i> Back to Serial Management</a>";
                } else {
                    echo "<a class='btn btn-secondary' href='reg.php'><i class='fa-solid fa-plus me-1'></i> Add Another New Record</a>";
                }
                echo "</div></div>";
                unset($_SESSION['flash_created_record']);
            } else {
        ?>
            <div class="card mb-3">
                <div class="card-header">
                    <strong><?php echo htmlspecialchars($header_title, ENT_QUOTES, 'UTF-8'); ?></strong>
                </div>
                <div class="card-body">
                    <form name="someform" action="reg.php" method="post" enctype="multipart/form-data" onSubmit="return ValidateForm()">
                        <input type="hidden" name="token" value="<?php echo htmlspecialchars($_SESSION['token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" />
                        <input type="hidden" name="submitted" value="TRUE" />
                        <?php if ($is_edit) { ?>
                            <input type="hidden" name="id" value="<?php echo (int)$edit_id; ?>" />
                            <input type="hidden" name="instimestamp" value="<?php echo htmlspecialchars($val_timestamp, ENT_QUOTES, 'UTF-8'); ?>" />
                            <input type="hidden" name="inputdate" value="<?php echo htmlspecialchars($val_inputdate, ENT_QUOTES, 'UTF-8'); ?>" />
                            <?php if (!empty($ctx_scstr)) { ?><input type="hidden" name="scstr" value="<?php echo htmlspecialchars($ctx_scstr, ENT_QUOTES, 'UTF-8');?>" /><?php } ?>
                            <?php if (!empty($ctx_page)) { ?><input type="hidden" name="page" value="<?php echo (int)$ctx_page;?>" /><?php } ?>
                            <?php if (!empty($ctx_sctype)) { ?><input type="hidden" name="sctype" value="<?php echo htmlspecialchars($ctx_sctype, ENT_QUOTES, 'UTF-8');?>" /><?php } ?>
                            <?php if (!empty($ctx_scflag)) { ?><input type="hidden" name="scflag" value="<?php echo htmlspecialchars($ctx_scflag, ENT_QUOTES, 'UTF-8');?>" /><?php } ?>
                            <?php if (!empty($ctx_delr)) { ?><input type="hidden" name="delr" value="<?php echo htmlspecialchars($ctx_delr, ENT_QUOTES, 'UTF-8');?>" /><?php } ?>
                            <?php if (!empty($ctx_subid)) { ?><input type="hidden" name="subid" value="<?php echo (int)$ctx_subid;?>" /><?php } ?>
                            <?php if (!empty($ctx_inf)) { ?><input type="hidden" name="inf" value="<?php echo htmlspecialchars($ctx_inf, ENT_QUOTES, 'UTF-8');?>" /><?php } ?>
                            <?php if (!empty($ctx_infname)) { ?><input type="hidden" name="infname" value="<?php echo htmlspecialchars($ctx_infname, ENT_QUOTES, 'UTF-8');?>" /><?php } ?>
                            <?php if (!empty($ctx_ser_scstr)) { ?><input type="hidden" name="ser_scstr" value="<?php echo htmlspecialchars($ctx_ser_scstr, ENT_QUOTES, 'UTF-8');?>" /><?php } ?>
                            <?php if (!empty($ctx_ser_filter)) { ?><input type="hidden" name="ser_filter" value="<?php echo htmlspecialchars($ctx_ser_filter, ENT_QUOTES, 'UTF-8');?>" /><?php } ?>
                            <?php if (!empty($ctx_ser_sort)) { ?><input type="hidden" name="ser_sort" value="<?php echo htmlspecialchars($ctx_ser_sort, ENT_QUOTES, 'UTF-8');?>" /><?php } ?>
                            <?php if (!empty($ctx_ser_page)) { ?><input type="hidden" name="ser_page" value="<?php echo (int)$ctx_ser_page;?>" /><?php } ?>
                        <?php } ?>

                        <div id="fetch_status_alert" class="mb-3" style="display:none;"></div>

                        <table class="table-modern">
                            <thead>
                                <tr>
                                    <th style="width:20%;">Tags</th>
                                    <th style="width:8%;" class="text-center">Indi.</th>
                                    <th>Field Value</th>
                                </tr>
                            </thead>
                            <tbody>

                            <tr>
                                <td><strong><?php echo $tag_020;?></strong></td>
                                <td class="text-center">**</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="text-muted font-bold">|a</span>
                                        <input type="text" name="isbn" id="isbn_input" maxlength="60" value="<?php echo htmlspecialchars($val_isbn, ENT_QUOTES, 'UTF-8'); ?>" placeholder="<?php echo $tag_020ph;?>"/>
                                        <button type="button" id="btn_fetch_isbn" class="btn btn-primary btn-sm" title="Auto-populate metadata from ISBN"><i class="fa-solid fa-cloud-arrow-down me-1"></i> Fetch ISBN</button>
                                        <a id="al0" class="btn btn-secondary btn-sm" style="display:<?php echo (!empty($val_isbns[1]) ? 'none' : 'inline-flex'); ?>" onclick="document.getElementById('a1').style.display='';document.getElementById('al0').style.display='none';">[+]</a>
                                    </div>
                                </td>
                            </tr>
                            <?php
                                for ($x=1; $x<=20; $x++) {
                                    $y = $x+1;
                                    $has_curr = !empty($val_isbns[$x]);
                                    $has_next = ($y <= 20) ? !empty($val_isbns[$y]) : false;
                                    echo "<tr id='a$x' style='display:" . ($has_curr ? 'table-row' : 'none') . "'>
                                        <td><strong>$tag_020</strong></td>
                                        <td class=\"text-center\">**</td>
                                        <td>
                                            <div class=\"d-flex align-items-center gap-2\">
                                                <span class=\"text-muted font-bold\">|a</span>
                                                <input type='text' name='isbn_$x' maxlength='60' value='" . htmlspecialchars($val_isbns[$x], ENT_QUOTES, 'UTF-8') . "' placeholder='$tag_020ph'/>";
                                    if ($y <= 20) {
                                        echo "<a id='al$x' class=\"btn btn-secondary btn-sm\" style='display:" . ($has_next ? 'none' : 'inline-flex') . "' onclick=\"document.getElementById('a$y').style.display='';document.getElementById('al$x').style.display='none';\">[+]</a>";
                                    }
                                    echo "</div></td></tr>";
                                }
                            ?>

                            <tr>
                                <td><strong><?php echo $tag_022;?></strong></td>
                                <td class="text-center">**</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="text-muted font-bold">|a</span>
                                        <input type="text" name="issn" id="issn_input" maxlength="60" value="<?php echo htmlspecialchars($val_issn, ENT_QUOTES, 'UTF-8'); ?>" placeholder="<?php echo $tag_022ph;?>"/>
                                        <button type="button" id="btn_fetch_issn" class="btn btn-primary btn-sm" title="Auto-populate metadata from ISSN"><i class="fa-solid fa-cloud-arrow-down me-1"></i> Fetch ISSN</button>
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td><strong>Material Type *</strong></td>
                                <td class="text-center">-</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="text-muted font-bold">|a</span>
                                        <select name="jenis" required>
                                        <?php
                                            $queryB = "select `38typeid`, `38type` from eg_type";
                                            $resultB = mysqli_query($GLOBALS["conn"], $queryB);
                                                                
                                            while ($myrow=mysqli_fetch_array($resultB)) {
                                                $typeB=$myrow["38type"];
                                                $typeBid=$myrow["38typeid"];
                                                $selected = ($val_jenis == "$typeBid") ? "selected" : "";
                                                echo "<option value='$typeBid' $selected>$typeB</option>";
                                            }
                                        ?>
                                        </select>
                                    </div>
                                </td>
                            </tr>
                            
                            <tr>
                                <td><strong>Subject Heading</strong></td>
                                <td class="text-center">-</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="text-muted font-bold">|a</span>
                                        <input type="text" name="subjectheading" readonly="readonly" maxlength="150" value="<?php echo htmlspecialchars($val_subjectheading, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Select using the [...] button"/>
                                        <input type="button" class="btn btn-secondary btn-sm" name="subjectheadingButton" value="..." onClick="showList()" />
                                        <input type="button" class="btn btn-primary btn-sm" name="gotoSHButton" value="+New" onClick="openSHAdd()" />
                                        <input type="button" class="btn btn-danger btn-sm" name="clearSH" value="Clear" onClick="document.someform.subjectheading.value='';">
                                    </div>
                                </td>
                            </tr>
                        
                            <tr>
                                <td><strong><?php echo $tag_041;?></strong></td>
                                <td class="text-center">**</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="text-muted font-bold">|a</span>
                                        <?php
                                        if ($tag_041_inputtype == "select") {
                                            $selectable_041 = explode("|", $tag_041_selectable);
                                            $selectable_041_def = explode("|", $tag_041_selectable_def);
                                            echo "<select name='language'>";
                                            for ($x = 0; $x < sizeof($selectable_041); $x++) {
                                                $selected = ($selectable_041[$x] == $val_language) ? "selected" : "";
                                                echo "<option value='".$selectable_041[$x]."' $selected>".$selectable_041_def[$x]."</option>";
                                            }
                                            echo "</select>";
                                        } else {
                                            echo "<input type=\"text\" name=\"language\" maxlength=\"3\" value=\"" . htmlspecialchars($val_language, ENT_QUOTES, 'UTF-8') . "\" placeholder=\"$tag_041ph\">";
                                        }
                                        ?>
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td><strong><?php echo $tag_090;?></strong></td>
                                <td class="text-center">00</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="text-muted font-bold">|a</span>
                                        <input type="text" name="callnum" maxlength="70" value="<?php echo htmlspecialchars($val_callnum, ENT_QUOTES, 'UTF-8'); ?>" placeholder="<?php echo $tag_090aph;?>"/>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td></td>
                                <td></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="text-muted font-bold">|b</span>
                                        <input type="text" name="callnum_b" maxlength="70" value="<?php echo htmlspecialchars($val_callnum_b, ENT_QUOTES, 'UTF-8'); ?>" placeholder="<?php echo $tag_090bph;?>"/>
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td><strong><?php echo $tag_100;?></strong></td>
                                <td class="text-center"><input type="text" style="width:45px; text-align:center;" maxlength="2" name="pengarang_in" value="<?php echo htmlspecialchars($val_pengarang_in, ENT_QUOTES, 'UTF-8'); ?>" placeholder="1_" title="Indicator (Default: 1_ for surname entry)"></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="text-muted font-bold">|a</span>
                                        <input type="text" name="pengarang" maxlength="150" value="<?php echo htmlspecialchars($val_pengarang, ENT_QUOTES, 'UTF-8'); ?>" placeholder="<?php echo $tag_100aph;?>"/>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td></td>
                                <td></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="text-muted font-bold">|d</span>
                                        <input type="text" name="pengarang_d" maxlength="150" value="<?php echo htmlspecialchars($val_pengarang_d, ENT_QUOTES, 'UTF-8'); ?>" placeholder="<?php echo $tag_100dph;?>"/>
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td><strong><?php echo $tag_245;?> *</strong></td>
                                <td class="text-center"><input type="text" style="width:45px; text-align:center;" maxlength="2" name="tajuk_in" value="<?php echo htmlspecialchars($val_tajuk_in, ENT_QUOTES, 'UTF-8'); ?>" placeholder="10" title="Indicator (Default: 10 with author, 00 without author)"></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="text-muted font-bold">|a</span>
                                        <input type="text" name="tajuk" maxlength="255" value="<?php echo htmlspecialchars($val_tajuk, ENT_QUOTES, 'UTF-8'); ?>" placeholder="<?php echo $tag_245aph;?>" required />
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td></td>
                                <td></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="text-muted font-bold">|b</span>
                                        <input type="text" name="tajuk_b" maxlength="255" value="<?php echo htmlspecialchars($val_tajuk_b, ENT_QUOTES, 'UTF-8'); ?>" placeholder="<?php echo $tag_245bph;?>"/>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td></td>
                                <td></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="text-muted font-bold">|c</span>
                                        <input type="text" name="tajuk_c" maxlength="255" value="<?php echo htmlspecialchars($val_tajuk_c, ENT_QUOTES, 'UTF-8'); ?>" placeholder="<?php echo $tag_245cph;?>"/>
                                    </div>
                                </td>
                            </tr>
                                        
                            <tr>
                                <td><strong><?php echo $tag_250;?></strong></td>
                                <td class="text-center">**</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="text-muted font-bold">|a</span>
                                        <input type="text" name="edition" maxlength="255" value="<?php echo htmlspecialchars($val_edition, ENT_QUOTES, 'UTF-8'); ?>" placeholder="<?php echo $tag_250aph;?>"/>
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td><strong><?php echo $tag_264;?></strong></td>
                                <td class="text-center">**</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="text-muted font-bold">|a</span>
                                        <input type="text" name="publication" maxlength="255" value="<?php echo htmlspecialchars($val_publication, ENT_QUOTES, 'UTF-8'); ?>" placeholder="<?php echo $tag_264aph;?>"/>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td></td>
                                <td></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="text-muted font-bold">|b</span>
                                        <input type="text" name="publication_b" maxlength="255" value="<?php echo htmlspecialchars($val_publication_b, ENT_QUOTES, 'UTF-8'); ?>" placeholder="<?php echo $tag_264bph;?>"/>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td></td>
                                <td></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="text-muted font-bold">|c</span>
                                        <input type="text" name="publication_c" maxlength="255" value="<?php echo htmlspecialchars($val_publication_c, ENT_QUOTES, 'UTF-8'); ?>" placeholder="<?php echo $tag_264cph;?>"/>
                                    </div>
                                </td>
                            </tr>
                            
                            <tr>
                                <td><strong><?php echo $tag_300;?></strong></td>
                                <td class="text-center">**</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="text-muted font-bold">|a</span>
                                        <input type="text" name="physicaldesc" maxlength="255" value="<?php echo htmlspecialchars($val_physicaldesc, ENT_QUOTES, 'UTF-8'); ?>" placeholder="<?php echo $tag_300aph;?>"/>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td></td>
                                <td></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="text-muted font-bold">|b</span>
                                        <input type="text" name="physicaldesc_b" maxlength="255" value="<?php echo htmlspecialchars($val_physicaldesc_b, ENT_QUOTES, 'UTF-8'); ?>" placeholder="<?php echo $tag_300bph;?>"/>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td></td>
                                <td></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="text-muted font-bold">|c</span>
                                        <input type="text" name="physicaldesc_c" maxlength="255" value="<?php echo htmlspecialchars($val_physicaldesc_c, ENT_QUOTES, 'UTF-8'); ?>" placeholder="<?php echo $tag_300cph;?>"/>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td></td>
                                <td></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="text-muted font-bold">|e</span>
                                        <input type="text" name="physicaldesc_e" maxlength="255" value="<?php echo htmlspecialchars($val_physicaldesc_e, ENT_QUOTES, 'UTF-8'); ?>" placeholder="<?php echo $tag_300eph;?>"/>
                                    </div>
                                </td>
                            </tr>
                            
                            <tr>
                                <td><strong><?php echo $tag_490;?></strong></td>
                                <td class="text-center">0*</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="text-muted font-bold">|a</span>
                                        <input type="text" name="series" maxlength="150" value="<?php echo htmlspecialchars($val_series, ENT_QUOTES, 'UTF-8'); ?>" placeholder="<?php echo $tag_490aph;?>"/>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td></td>
                                <td></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="text-muted font-bold">|v</span>
                                        <input type="text" name="series_v" maxlength="150" value="<?php echo htmlspecialchars($val_series_v, ENT_QUOTES, 'UTF-8'); ?>" placeholder="<?php echo $tag_490vph;?>"/>
                                    </div>
                                </td>
                            </tr>
                            
                            <tr>
                                <td><strong><?php echo $tag_500;?></strong></td>
                                <td class="text-center">**</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="text-muted font-bold">|a</span>
                                        <input type="text" name="notes" maxlength="255" value="<?php echo htmlspecialchars($val_notes, ENT_QUOTES, 'UTF-8'); ?>" placeholder="<?php echo $tag_500aph;?>"/>
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td><strong><?php echo $tag_505;?></strong></td>
                                <td class="text-center"><input type="text" style="width:45px; text-align:center;" maxlength="2" name="fcnotes_in" value="<?php echo htmlspecialchars($val_fcnotes_in, ENT_QUOTES, 'UTF-8'); ?>" placeholder="0_" title="Indicator (Default: 0_ for complete contents)"></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="text-muted font-bold">|a</span>
                                        <input type="text" name="fcnotes" maxlength="255" value="<?php echo htmlspecialchars($val_fcnotes, ENT_QUOTES, 'UTF-8'); ?>" placeholder="<?php echo $tag_505aph;?>"/>
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td><strong><?php echo $tag_710;?></strong></td>
                                <td class="text-center"><input type="text" style="width:45px; text-align:center;" maxlength="2" name="sumber_in" value="<?php echo htmlspecialchars($val_sumber_in, ENT_QUOTES, 'UTF-8'); ?>" placeholder="2_" title="Indicator (Default: 2_ for name in direct order)"></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="text-muted font-bold">|a</span>
                                        <input type='text' name='sumber' maxlength='255' value="<?php echo htmlspecialchars($val_sumber, ENT_QUOTES, 'UTF-8'); ?>" placeholder="<?php echo $tag_710aph;?>"/>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td></td>
                                <td></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="text-muted font-bold">|b</span>
                                        <input type='text' name='sumber_b' maxlength='255' value="<?php echo htmlspecialchars($val_sumber_b, ENT_QUOTES, 'UTF-8'); ?>" placeholder="<?php echo $tag_710bph;?>"/>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td></td>
                                <td></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="text-muted font-bold">|e</span>
                                        <input type='text' name='sumber_e' maxlength='255' value="<?php echo htmlspecialchars($val_sumber_e, ENT_QUOTES, 'UTF-8'); ?>" placeholder="<?php echo $tag_710eph;?>"/>
                                    </div>
                                </td>
                            </tr>
                            
                            <tr>
                                <td><strong><?php echo $tag_852;?></strong></td>
                                <td class="text-center">**</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="text-muted font-bold">|a</span>
                                        <input type="text" name="lokasi" maxlength="50" value="<?php echo htmlspecialchars($val_lokasi, ENT_QUOTES, 'UTF-8'); ?>" placeholder="<?php echo $tag_852aph;?>"/>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td></td>
                                <td></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="text-muted font-bold">|b</span>
                                        <input type="text" name="lokasi_b" maxlength="50" value="<?php echo htmlspecialchars($val_lokasi_b, ENT_QUOTES, 'UTF-8'); ?>" placeholder="<?php echo $tag_852bph;?>"/>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td></td>
                                <td></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="text-muted font-bold">|c</span>
                                        <input type="text" name="lokasi_c" maxlength="50" value="<?php echo htmlspecialchars($val_lokasi_c, ENT_QUOTES, 'UTF-8'); ?>" placeholder="<?php echo $tag_852cph;?>"/>
                                    </div>
                                </td>
                            </tr>
                            
                            <tr>
                                <td><strong><?php echo $tag_856;?></strong></td>
                                <td class="text-center">**</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="text-muted font-bold">|u</span>
                                        <input type="text" name="link" maxlength="255" value="<?php echo htmlspecialchars($val_link, ENT_QUOTES, 'UTF-8'); ?>" placeholder="<?php echo $tag_856uph;?>"/>
                                    </div>
                                </td>
                            </tr>
                            
                            <tr>
                                <td><strong>PDF Attachment</strong><br/><small class="text-muted">Max <?php $ini_max = return_bytes(ini_get('upload_max_filesize')); echo min((int)$pdf_upload_maxsize, (int)round($ini_max / 1048576, 1)); ?> MB</small></td>
                                <td class="text-center">-</td>
                                <td>
                                    <?php
                                        if ($is_edit && $val_pdfattach === 'TRUE') {
                                            echo "<div class='d-flex align-items-center gap-3 mb-2'>";
                                            echo "<span class='badge badge-secondary'><i class='fa-solid fa-file-pdf me-1'></i>" . htmlspecialchars("{$edit_id}_{$val_timestamp}.pdf", ENT_QUOTES, 'UTF-8') . "</span>";
                                            echo "<label class='form-check-label'><input type='checkbox' name='unlink_pdf' value='unlink'> <small class='text-danger'>Unlink/Delete existing PDF on update</small></label>";
                                            echo "</div>";
                                            echo "<div><small class='text-muted d-block mb-1'>Or select a new file to replace:</small><input type='file' name='file1'></div>";
                                        } else {
                                            echo "<input type='file' name='file1'>";
                                        }
                                    ?>
                                </td>
                            </tr>
                            
                            <tr>
                                <td><strong>Cover JPG</strong><br/><small class="text-muted">Max <?php $ini_max = return_bytes(ini_get('upload_max_filesize')); echo min((int)$cover_upload_maxsize, (int)round($ini_max / 1048576, 1)); ?> MB</small></td>
                                <td class="text-center">-</td>
                                <td>
                                    <?php
                                        if ($is_edit && $val_imageatt === 'TRUE') {
                                            echo "<div class='d-flex align-items-center gap-3 mb-2'>";
                                            echo "<span class='badge badge-secondary'><i class='fa-solid fa-image me-1'></i>" . htmlspecialchars("{$edit_id}_{$val_timestamp}.jpg", ENT_QUOTES, 'UTF-8') . "</span>";
                                            echo "<label class='form-check-label'><input type='checkbox' name='unlink_cover' value='unlink'> <small class='text-danger'>Unlink/Delete existing cover on update</small></label>";
                                            echo "</div>";
                                            echo "<div><small class='text-muted d-block mb-1'>Or select a new image to replace:</small><input type='file' name='imageatt1'></div>";
                                        } else {
                                            echo "<input type='file' name='imageatt1'>";
                                        }
                                    ?>
                                    <input type="hidden" name="remote_cover_url" id="remote_cover_url" value="" />
                                    <div id="fetch_cover_preview_wrap" class="mt-2" style="display:none;">
                                        <div class="d-flex align-items-center justify-content-between p-2 border rounded shadow-sm" style="background:#f0fdf4; border-color:#bbf7d0 !important; max-width: 480px;">
                                            <div class="d-flex align-items-center gap-3">
                                                <img id="fetch_cover_img" src="" alt="Book Cover" style="max-height: 85px; width: auto; border-radius: 4px; box-shadow: 0 2px 4px rgba(0,0,0,0.12);" />
                                                <div>
                                                    <small class="text-success font-bold d-block mb-1"><i class="fa-solid fa-circle-check me-1"></i>Cover Auto-Attached</small>
                                                    <small class="text-muted d-block" id="fetch_cover_info">Will automatically save to catalog upon Submit.</small>
                                                </div>
                                            </div>
                                            <button type="button" class="btn btn-outline-danger btn-sm ms-2" id="btn_clear_auto_cover" title="Remove auto-attached cover"><i class="fa-solid fa-xmark me-1"></i>Clear</button>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                                            
                        <?php if ($is_edit) { ?>
                            <div class="mt-3 form-action-buttons">
                                <button type="submit" class="btn btn-primary" name="Submit1" value="Update Record">
                                    <i class="fa-solid fa-floppy-disk me-1"></i> Update Record
                                </button>
                            </div>
                        <?php } else { ?>
                            <div class="mt-3 form-action-buttons">
                                <button type="submit" class="btn btn-primary" name="Submit1" value="1">
                                    <i class="fa-solid fa-floppy-disk me-1"></i> Register Record
                                </button>
                                <button type="submit" class="btn btn-success font-weight-bold" name="btn_auto_copy" value="1">
                                    <i class="fa-solid fa-circle-plus me-1"></i> Register Record and Auto Create 1 Copy
                                </button>
                            </div>
                        <?php } ?>
                    </form>
                </div>
            </div>
        <?php
            } // if not created
        ?>
        
        <div class="text-center my-3">
            <?php
                if ($is_edit && $edit_id > 0) {
                    echo "<a class='btn btn-secondary btn-sm' href='../details.php?det=" . (int)$edit_id . htmlspecialchars($ctxQuery, ENT_QUOTES, 'UTF-8') . "'>&larr; Back to Record Details</a>";
                } elseif ($ctx_delr === 'serials' || $val_jenis == '4') {
                    $serParams = [];
                    if (!empty($ctx_ser_scstr)) $serParams['scstr'] = $ctx_ser_scstr;
                    elseif (!empty($ctx_scstr)) $serParams['scstr'] = $ctx_scstr;
                    if (!empty($ctx_ser_filter) && $ctx_ser_filter !== 'all') $serParams['filter_status'] = $ctx_ser_filter;
                    if (!empty($ctx_ser_sort) && $ctx_ser_sort !== 'title_asc') $serParams['sort_by'] = $ctx_ser_sort;
                    if (!empty($ctx_ser_page) && (int)$ctx_ser_page > 1) $serParams['page'] = (int)$ctx_ser_page;
                    $serUrl = 'serials.php' . (!empty($serParams) ? '?' . http_build_query($serParams) : '');
                    echo "<a class='btn btn-secondary btn-sm' href='" . htmlspecialchars($serUrl, ENT_QUOTES, 'UTF-8') . "'>&larr; Back to Serial Management</a>";
                } else {
                    echo "<a class='btn btn-secondary btn-sm' href='../index2.php'>&larr; Back to start page</a>";
                }
            ?>
        </div>
        
        <?php include_once '../includes/footerbar.php';?>
    </div>

    <!-- Application Modal Dialog -->
    <div id="appModal" class="modal-backdrop" onclick="if(event.target===this) closeAppModal(false);">
        <div id="appModalDialog" class="modal-dialog modal-md">
            <div class="modal-header">
                <h5 class="modal-title" id="appModalTitle">Action</h5>
                <button type="button" class="modal-close-btn" onclick="closeAppModal(false);" title="Close modal">&times;</button>
            </div>
            <div class="modal-body p-0">
                <iframe id="appModalIframe" class="modal-iframe" src="about:blank"></iframe>
            </div>
        </div>
    </div>

    <!-- Duplicate Item / Cardex Serial Detection Modal -->
    <div id="duplicateItemModal" class="modal-backdrop" onclick="if(event.target===this) closeDuplicateModal();">
        <div class="modal-dialog modal-md" style="max-width: 580px; overflow: hidden; border-radius: var(--radius-lg, 12px); box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.25);">
            <div id="dupModalHeader" class="modal-header" style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border-bottom: 1px solid #fcd34d; padding: 1rem 1.25rem;">
                <div class="d-flex align-items-center gap-2">
                    <div id="dupModalIconWrap" style="width: 36px; height: 36px; border-radius: 50%; background: #d97706; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0;">
                        <i id="dupModalIcon" class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                    <div>
                        <h5 class="modal-title" id="dupModalTitle" style="color: #92400e; font-weight: 700; font-size: 1.05rem; margin: 0;">Item Already Exists in Database</h5>
                        <small id="dupModalSubtitle" style="color: #b45309; font-size: 0.8rem; font-weight: 500;">Duplicate Record Prevention Alert</small>
                    </div>
                </div>
                <button type="button" class="modal-close-btn" onclick="closeDuplicateModal();" title="Close modal" style="font-size: 1.5rem; line-height: 1; border: none; background: transparent; cursor: pointer; color: #92400e;">&times;</button>
            </div>
            
            <div class="modal-body" style="padding: 1.25rem; background: #ffffff; max-height: 75vh; overflow-y: auto;">
                <!-- Alert explanation box -->
                <div id="dupNoticeBanner" class="p-3 mb-3 rounded" style="background: #fffbeb; border: 1px solid #fef3c7; color: #92400e; font-size: 0.88rem; line-height: 1.45;">
                    <span id="dupNoticeText">A title matching this identifier is already registered in your library catalog.</span>
                </div>

                <!-- Existing Item Summary Card -->
                <div class="card p-3 mb-3" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px;">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span id="dupTypeBadge" class="badge" style="background: #0284c7; color: #fff; font-size: 0.75rem; padding: 4px 8px; border-radius: 4px; font-weight: 600;">
                            <i class="fa-solid fa-book me-1"></i> Book / Monograph
                        </span>
                        <span class="text-muted small font-monospace">Record ID: #<strong id="dupItemId" class="text-primary">--</strong></span>
                    </div>
                    <h4 id="dupItemTitle" style="font-size: 1.1rem; font-weight: 700; color: #0f172a; margin-bottom: 0.35rem; line-height: 1.35;">Item Title</h4>
                    <div class="text-muted small mb-2" id="dupItemAuthorWrap">
                        <i class="fa-solid fa-user-pen me-1 text-secondary"></i> Author: <span id="dupItemAuthor" class="fw-semibold text-dark">--</span>
                    </div>
                    
                    <div class="d-flex flex-wrap gap-2 align-items-center mt-2 pt-2 border-top border-light-subtle small">
                        <div style="background: #ffffff; padding: 4px 10px; border-radius: 6px; border: 1px solid #e2e8f0;">
                            <i class="fa-solid fa-barcode me-1 text-primary"></i> <span id="dupIdLabel">ISBN</span>: <strong id="dupItemIdentifier">--</strong>
                        </div>
                        <div style="background: #ffffff; padding: 4px 10px; border-radius: 6px; border: 1px solid #e2e8f0;">
                            <i class="fa-solid fa-tag me-1 text-secondary"></i> Call No: <strong id="dupItemCallNum">--</strong>
                        </div>
                        <div style="background: #ffffff; padding: 4px 10px; border-radius: 6px; border: 1px solid #e2e8f0;">
                            <i class="fa-solid fa-layer-group me-1 text-success"></i> Registered Copies: <strong id="dupItemCopiesCount" class="text-success">0</strong>
                        </div>
                    </div>
                </div>

                <!-- Cardex Serials Explanatory Note -->
                <div id="dupCardexInfoBox" style="display: none; background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; padding: 0.85rem; border-radius: 8px; margin-bottom: 1rem; font-size: 0.85rem; line-height: 1.45;">
                    <div class="d-flex align-items-start gap-2">
                        <i class="fa-solid fa-newspaper fa-lg text-primary mt-1"></i>
                        <div>
                            <strong>Cardex Issue Check-in Required:</strong> In serials &amp; periodical management, each physical copy corresponds to an issue designation (Volume, Issue/No., Month/Year, Reference Status). Clicking below opens the Issue Check-in interface with these fields ready for input.
                        </div>
                    </div>
                </div>

                <!-- Action Prompt -->
                <div class="text-muted small mb-2 fw-semibold" id="dupActionPrompt">What would you like to do?</div>
                
                <!-- ISBN Actions (Books) -->
                <div id="dupIsbnActions" style="display: flex; flex-direction: column; gap: 0.5rem;">
                    <button type="button" id="btn_dup_add_one_copy" class="btn btn-success d-flex align-items-center justify-content-center gap-2 py-2" style="font-weight: 600;">
                        <i class="fa-solid fa-circle-plus fa-lg"></i>
                        <span id="btn_dup_add_one_text">Add 1 Copy to This Record Instantly</span>
                    </button>
                </div>

                <!-- ISSN Actions (Serials) -->
                <div id="dupIssnActions" style="display: none; flex-direction: column; gap: 0.5rem;">
                    <button type="button" id="btn_dup_checkin_serial" class="btn btn-primary d-flex align-items-center justify-content-center gap-2 py-2" style="font-weight: 600; background: #0f766e; border-color: #0f766e;">
                        <i class="fa-solid fa-file-import fa-lg"></i>
                        <span>Check-in Serial Issue (Cardex)</span>
                    </button>
                </div>
                
                <!-- Quick Add Loading Alert -->
                <div id="dupQuickAddLoading" class="alert alert-info mt-3 mb-0 p-3" style="display: none;">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fa-solid fa-spinner fa-spin fa-xl text-primary"></i>
                        <div>
                            <strong class="d-block">Registering Copy...</strong>
                            <span class="small text-muted">Please wait while the copy is added.</span>
                        </div>
                    </div>
                </div>

                <!-- Quick Add Success Alert -->
                <div id="dupQuickAddSuccess" class="alert alert-success mt-3 mb-0 p-3" style="display: none;">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fa-solid fa-circle-check fa-xl text-success"></i>
                        <div>
                            <strong class="d-block" id="dupSuccessTitle">Copy Added Successfully!</strong>
                            <span id="dupSuccessMsg" class="small">Accession barcode generated.</span>
                        </div>
                    </div>
                    <div class="mt-2 pt-2 border-top border-success-subtle d-flex gap-2 justify-content-end align-items-center flex-wrap">
                        <a id="btn_dup_view_details" href="#" class="btn btn-sm btn-primary"><i class="fa-solid fa-circle-info me-1"></i> View Record Details</a>
                        <button type="button" id="btn_dup_view_copies" class="btn btn-sm btn-outline-secondary">View All Copies</button>
                        <button type="button" class="btn btn-sm btn-secondary" onclick="closeDuplicateModal();">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bibliographic Auto-Population Client Engine -->
    <script type="text/javascript">
    (function() {
        var currentEditId = <?php echo (int)$edit_id; ?>;
        var btnIsbn = document.getElementById('btn_fetch_isbn');
        var btnIssn = document.getElementById('btn_fetch_issn');
        var inputIsbn = document.getElementById('isbn_input');
        var inputIssn = document.getElementById('issn_input');
        var alertBox = document.getElementById('fetch_status_alert');

        // Duplicate item modal elements
        var currentDuplicateMatch = null;
        var dupModal = document.getElementById('duplicateItemModal');
        var dupHeader = document.getElementById('dupModalHeader');
        var dupIconWrap = document.getElementById('dupModalIconWrap');
        var dupIcon = document.getElementById('dupModalIcon');
        var dupTitle = document.getElementById('dupModalTitle');
        var dupSubtitle = document.getElementById('dupModalSubtitle');
        var dupNoticeText = document.getElementById('dupNoticeText');
        var dupTypeBadge = document.getElementById('dupTypeBadge');
        var dupItemId = document.getElementById('dupItemId');
        var dupItemTitle = document.getElementById('dupItemTitle');
        var dupItemAuthor = document.getElementById('dupItemAuthor');
        var dupIdLabel = document.getElementById('dupIdLabel');
        var dupItemIdentifier = document.getElementById('dupItemIdentifier');
        var dupItemCallNum = document.getElementById('dupItemCallNum');
        var dupItemCopiesCount = document.getElementById('dupItemCopiesCount');
        var dupCardexInfoBox = document.getElementById('dupCardexInfoBox');
        var dupActionPrompt = document.getElementById('dupActionPrompt');
        var dupIsbnActions = document.getElementById('dupIsbnActions');
        var dupIssnActions = document.getElementById('dupIssnActions');
        var dupQuickAddLoading = document.getElementById('dupQuickAddLoading');
        var dupQuickAddSuccess = document.getElementById('dupQuickAddSuccess');
        var btnAddOneCopy = document.getElementById('btn_dup_add_one_copy');
        var btnAddOneText = document.getElementById('btn_dup_add_one_text');
        var btnCheckinSerial = document.getElementById('btn_dup_checkin_serial');
        var btnViewCopies = document.getElementById('btn_dup_view_copies');

        function showDuplicateModal(type, identifier, localMatch) {
            if (!dupModal || !localMatch) return;
            currentDuplicateMatch = localMatch;

            // Reset quick add success and prompt state
            if (dupQuickAddSuccess) dupQuickAddSuccess.style.display = 'none';
            if (dupQuickAddLoading) dupQuickAddLoading.style.display = 'none';
            if (dupActionPrompt) dupActionPrompt.style.setProperty('display', 'block', 'important');
            if (btnAddOneCopy) {
                btnAddOneCopy.disabled = false;
                btnAddOneCopy.style.display = 'flex';
                if (btnAddOneText) btnAddOneText.textContent = 'Add 1 Copy to This Record Instantly';
            }

            // If triggered by ISBN -> strictly Book mode (never show Cardex check-in)
            // If triggered by ISSN -> strictly Serial mode (show only Cardex check-in)
            var isSerial = (type === 'issn');

            if (isSerial) {
                // Serial / Periodical Presentation
                if (dupHeader) {
                    dupHeader.style.background = 'linear-gradient(135deg, #ccfbf1 0%, #99f6e4 100%)';
                    dupHeader.style.borderBottom = '1px solid #5eead4';
                }
                if (dupIconWrap) dupIconWrap.style.background = '#0f766e';
                if (dupIcon) dupIcon.className = 'fa-solid fa-newspaper';
                if (dupTitle) {
                    dupTitle.textContent = 'Serial Publication Already Exists in Catalog';
                    dupTitle.style.color = '#115e59';
                }
                if (dupSubtitle) {
                    dupSubtitle.textContent = 'Cardex Serial Master Record Detected';
                    dupSubtitle.style.color = '#0f766e';
                }
                if (dupNoticeText) {
                    dupNoticeText.textContent = 'This serial publication already exists in the catalog database. In serials management, new arrivals require Cardex issue check-in (Volume, Issue, Year) before copies can be registered.';
                }
                if (dupTypeBadge) {
                    dupTypeBadge.style.background = '#0f766e';
                    dupTypeBadge.innerHTML = '<i class="fa-solid fa-newspaper me-1"></i> Serial / Periodical';
                }
                if (dupIdLabel) dupIdLabel.textContent = 'ISSN';
                if (dupCardexInfoBox) {
                    dupCardexInfoBox.style.setProperty('display', 'block', 'important');
                }
                if (dupIsbnActions) {
                    dupIsbnActions.style.setProperty('display', 'none', 'important');
                }
                if (dupIssnActions) {
                    dupIssnActions.style.setProperty('display', 'flex', 'important');
                }
            } else {
                // Book / Monograph Presentation
                if (dupHeader) {
                    dupHeader.style.background = 'linear-gradient(135deg, #fef3c7 0%, #fde68a 100%)';
                    dupHeader.style.borderBottom = '1px solid #fcd34d';
                }
                if (dupIconWrap) dupIconWrap.style.background = '#d97706';
                if (dupIcon) dupIcon.className = 'fa-solid fa-book-bookmark';
                if (dupTitle) {
                    dupTitle.textContent = 'Book Already Exists in Library Catalog';
                    dupTitle.style.color = '#92400e';
                }
                if (dupSubtitle) {
                    dupSubtitle.textContent = 'Duplicate Record Prevention Alert';
                    dupSubtitle.style.color = '#b45309';
                }
                if (dupNoticeText) {
                    dupNoticeText.textContent = 'A book matching this ISBN already exists in your library database. Would you like to add another copy to this record instead of creating a duplicate?';
                }
                if (dupTypeBadge) {
                    dupTypeBadge.style.background = '#0284c7';
                    dupTypeBadge.innerHTML = '<i class="fa-solid fa-book me-1"></i> ' + escapeHtml(localMatch.type_name || 'Book / Monograph');
                }
                if (dupIdLabel) dupIdLabel.textContent = 'ISBN';
                if (dupCardexInfoBox) {
                    dupCardexInfoBox.style.setProperty('display', 'none', 'important');
                }
                if (dupIsbnActions) {
                    dupIsbnActions.style.setProperty('display', 'flex', 'important');
                }
                if (dupIssnActions) {
                    dupIssnActions.style.setProperty('display', 'none', 'important');
                }
            }

            // Fill Record Details
            if (dupItemId) dupItemId.textContent = localMatch.id || '--';
            if (dupItemTitle) dupItemTitle.textContent = localMatch.title || '(Untitled Record)';
            if (dupItemAuthor) dupItemAuthor.textContent = localMatch.author || 'N/A';
            if (dupItemIdentifier) {
                var dispId = (type === 'isbn') ? (localMatch.isbn || identifier) : (localMatch.issn || identifier);
                dupItemIdentifier.textContent = dispId || '--';
            }
            if (dupItemCallNum) dupItemCallNum.textContent = localMatch.callnum || 'Not assigned';
            if (dupItemCopiesCount) {
                var cCount = parseInt(localMatch.copies_count, 10) || 0;
                dupItemCopiesCount.textContent = cCount + (cCount === 1 ? ' copy registered' : ' copies registered');
            }

            dupModal.classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        // Attach Duplicate Modal Action Listeners
        if (btnAddOneCopy) {
            btnAddOneCopy.addEventListener('click', function(e) {
                e.preventDefault();
                if (!currentDuplicateMatch || !currentDuplicateMatch.id) return;
                
                // Immediately hide "What would you like to do?" prompt and action button
                if (dupActionPrompt) dupActionPrompt.style.setProperty('display', 'none', 'important');
                if (dupIsbnActions) dupIsbnActions.style.setProperty('display', 'none', 'important');
                if (dupQuickAddLoading) dupQuickAddLoading.style.display = 'block';

                var addUrl = 'api/quick-add-copy.php?bahan_id=' + encodeURIComponent(currentDuplicateMatch.id);
                fetch(addUrl, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json' }
                })
                .then(function(res) {
                    return res.json().then(function(data) {
                        return { ok: res.ok, data: data };
                    });
                })
                .then(function(result) {
                    if (dupQuickAddLoading) dupQuickAddLoading.style.display = 'none';

                    if (!result.ok || result.data.status !== 'success') {
                        alert(result.data.message || 'Error registering copy.');
                        if (dupActionPrompt) dupActionPrompt.style.setProperty('display', 'block', 'important');
                        if (dupIsbnActions) dupIsbnActions.style.setProperty('display', 'flex', 'important');
                        return;
                    }

                    var newCount = result.data.copies_count;
                    currentDuplicateMatch.copies_count = newCount;
                    if (dupItemCopiesCount) {
                        dupItemCopiesCount.textContent = newCount + (newCount === 1 ? ' copy registered' : ' copies registered');
                    }
                    
                    if (dupQuickAddSuccess) {
                        var msgEl = document.getElementById('dupSuccessMsg');
                        if (msgEl) {
                            msgEl.innerHTML = '<strong>' + escapeHtml(result.data.message) + '</strong> &bull; Current Total Copies: <span class="badge badge-success">' + escapeHtml(newCount) + '</span>';
                        }
                        var btnViewDet = document.getElementById('btn_dup_view_details');
                        if (btnViewDet && currentDuplicateMatch.id) {
                            btnViewDet.href = '../details.php?det=' + encodeURIComponent(currentDuplicateMatch.id);
                        }
                        dupQuickAddSuccess.style.display = 'block';
                    }
                    showAlert('success', '<strong>Copy Added!</strong> ' + escapeHtml(result.data.message));
                })
                .catch(function(err) {
                    if (dupQuickAddLoading) dupQuickAddLoading.style.display = 'none';
                    alert('Network error while adding copy: ' + err.message);
                    if (dupActionPrompt) dupActionPrompt.style.setProperty('display', 'block', 'important');
                    if (dupIsbnActions) dupIsbnActions.style.setProperty('display', 'flex', 'important');
                });
            });
        }

        if (btnCheckinSerial) {
            btnCheckinSerial.addEventListener('click', function(e) {
                e.preventDefault();
                if (!currentDuplicateMatch || !currentDuplicateMatch.id) return;
                var id = currentDuplicateMatch.id;
                closeDuplicateModal();
                openAppModal('copies_add.php?bahan_id=' + id, 'Check-in Serial Issue / Add Copies (#' + id + ')', 'modal-md');
            });
        }

        if (btnViewCopies) {
            btnViewCopies.addEventListener('click', function(e) {
                e.preventDefault();
                if (!currentDuplicateMatch || !currentDuplicateMatch.id) return;
                var id = currentDuplicateMatch.id;
                closeDuplicateModal();
                openAppModal('reg_copies.php?id=' + id, 'Manage Copies (#' + id + ')', 'modal-lg');
            });
        }

        function checkDuplicateOnBlur(type, inputEl) {
            if (currentEditId > 0 || !inputEl) return;
            var val = (inputEl.value || '').replace(/[-\u2013\u2014]/g, '').trim();
            if (val.length < 8) return;
            if (inputEl.dataset.lastCheckedVal === val) return;
            inputEl.dataset.lastCheckedVal = val;

            var checkUrl = 'api/fetch-metadata.php?action=check_local&type=' + encodeURIComponent(type) + '&identifier=' + encodeURIComponent(val) + '&exclude_id=' + encodeURIComponent(currentEditId);
            fetch(checkUrl, {
                headers: { 'Accept': 'application/json' }
            })
            .then(function(res) { return res.json(); })
            .then(function(json) {
                if (json && json.status === 'success' && json.local_match && json.local_match.exists) {
                    showDuplicateModal(type, val, json.local_match);
                }
            })
            .catch(function() {});
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeDuplicateModal();
            }
        });

        function escapeHtml(str) {
            if (!str) return '';
            var div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }

        function showAlert(type, message) {
            if (!alertBox) return;
            var icon = (type === 'success') ? 'fa-circle-check' : 'fa-triangle-exclamation';
            var alertClass = (type === 'success') ? 'alert-success' : 'alert-danger';
            alertBox.className = 'alert ' + alertClass + ' d-flex align-items-center justify-content-between p-3 mb-3';
            alertBox.innerHTML = '<div class="d-flex align-items-center gap-2"><i class="fa-solid ' + icon + ' fa-lg"></i><div>' + message + '</div></div>' +
                '<button type="button" class="btn btn-sm btn-secondary" onclick="document.getElementById(\'fetch_status_alert\').style.display=\'none\';" title="Dismiss">&times;</button>';
            alertBox.style.display = 'flex';
            alertBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        function highlightElement(el) {
            if (!el) return;
            var origTransition = el.style.transition;
            var origBg = el.style.backgroundColor;
            el.style.transition = 'background-color 0.4s ease';
            el.style.backgroundColor = '#ecfdf5';
            setTimeout(function() {
                el.style.backgroundColor = origBg;
                setTimeout(function() {
                    el.style.transition = origTransition;
                }, 400);
            }, 1800);
        }

        function setFieldValue(form, fieldName, value) {
            if (!form || !fieldName || value === undefined || value === null) return;
            var field = form.elements[fieldName];
            if (field) {
                field.value = value;
                highlightElement(field);
            }
        }

        function selectMatchingOption(selectElement, keywords, fallbackValue) {
            if (!selectElement || !selectElement.options) return;
            var opts = selectElement.options;
            for (var i = 0; i < opts.length; i++) {
                var optText = opts[i].text.toLowerCase();
                var optVal = opts[i].value.toLowerCase();
                for (var k = 0; k < keywords.length; k++) {
                    var kw = keywords[k].toLowerCase();
                    if (optText.indexOf(kw) !== -1 || optVal === kw) {
                        selectElement.selectedIndex = i;
                        highlightElement(selectElement);
                        return;
                    }
                }
            }
            if (fallbackValue) {
                for (var i = 0; i < opts.length; i++) {
                    var optVal = opts[i].value.toLowerCase();
                    var optText = opts[i].text.toLowerCase();
                    if (optVal === fallbackValue.toLowerCase() || optText.indexOf(fallbackValue.toLowerCase()) !== -1 || optText.indexOf('other') !== -1) {
                        selectElement.selectedIndex = i;
                        highlightElement(selectElement);
                        return;
                    }
                }
            }
        }

        function populateFormWithData(data) {
            var form = document.someform;
            if (!form || !data) return;

            // Authors
            var authorsVal = data.authors || data.author_main || '';
            if (authorsVal) {
                setFieldValue(form, 'pengarang', authorsVal);
                if (form.elements['pengarang_in'] && (!form.elements['pengarang_in'].value || form.elements['pengarang_in'].value.trim() === '')) {
                    setFieldValue(form, 'pengarang_in', '1 ');
                }
            }

            // Title statement
            if (data.title) {
                setFieldValue(form, 'tajuk', data.title);
                if (form.elements['tajuk_in'] && (!form.elements['tajuk_in'].value || form.elements['tajuk_in'].value.trim() === '')) {
                    setFieldValue(form, 'tajuk_in', authorsVal ? '10' : '00');
                }
            }
            if (data.title_b) setFieldValue(form, 'tajuk_b', data.title_b);
            if (data.title_c) setFieldValue(form, 'tajuk_c', data.title_c);

            // Publication Details (Place, Publisher, Date)
            if (data.publication_place) setFieldValue(form, 'publication', data.publication_place);
            if (data.publisher) setFieldValue(form, 'publication_b', data.publisher);
            if (data.year) setFieldValue(form, 'publication_c', data.year);

            // Edition
            if (data.edition) setFieldValue(form, 'edition', data.edition);

            // Physical Description (Extent & Dimensions)
            if (data.physical_desc) setFieldValue(form, 'physicaldesc', data.physical_desc);
            if (data.physical_desc_c) setFieldValue(form, 'physicaldesc_c', data.physical_desc_c);

            // Series
            if (data.series) setFieldValue(form, 'series', data.series);

            // Call Number (Classification and Cutter)
            if (data.callnum_a || data.ddc) setFieldValue(form, 'callnum', data.callnum_a || data.ddc);
            if (data.callnum_b || data.cutter) setFieldValue(form, 'callnum_b', data.callnum_b || data.cutter);

            // Notes / Summary
            if (data.notes || data.description) {
                var notesText = data.notes || (data.description ? data.description.substring(0, 255) : '');
                setFieldValue(form, 'notes', notesText);
            }

            // Language (Predefined: zsm, eng, chi, tam, ara; Default: oth)
            if (form.elements['language']) {
                var langEl = form.elements['language'];
                var langCode = (data.language || 'oth').toLowerCase();
                if (langEl.tagName === 'SELECT') {
                    var langKeywords = [langCode];
                    if (langCode === 'en' || langCode === 'eng') langKeywords.push('english', 'eng');
                    else if (langCode === 'ms' || langCode === 'may' || langCode === 'zsm' || langCode === 'malay') langKeywords.push('bahasa malaysia', 'melayu', 'zsm', 'may');
                    else if (langCode === 'zh' || langCode === 'chi' || langCode === 'zho') langKeywords.push('chinese', 'chi');
                    else if (langCode === 'ta' || langCode === 'tam') langKeywords.push('chinese', 'tam');
                    else if (langCode === 'ar' || langCode === 'ara') langKeywords.push('arabic', 'ara');
                    selectMatchingOption(langEl, langKeywords, 'oth');
                } else {
                    setFieldValue(form, 'language', langCode);
                }
            }

            // ISBN and ISSN (store and display without hyphens)
            if (data.isbn) {
                var cleanIsbn = String(data.isbn).replace(/[-\u2013\u2014]/g, '').trim();
                setFieldValue(form, 'isbn', cleanIsbn);
            }
            if (data.issn) {
                var cleanIssn = String(data.issn).replace(/[-\u2013\u2014]/g, '').trim();
                setFieldValue(form, 'issn', cleanIssn);
            }

            // Cover Auto-Attachment & Preview
            var coverWrap = document.getElementById('fetch_cover_preview_wrap');
            var coverImg = document.getElementById('fetch_cover_img');
            var coverInfo = document.getElementById('fetch_cover_info');
            var remoteCoverInput = document.getElementById('remote_cover_url');
            var fileInput = form.elements['imageatt1'];

            if (data.thumbnail && coverWrap && coverImg) {
                coverImg.src = data.thumbnail;
                if (remoteCoverInput) {
                    remoteCoverInput.value = data.thumbnail;
                }
                if (coverInfo) {
                    coverInfo.textContent = 'Auto-attached from web. Will save to catalog on Submit.';
                }
                coverWrap.style.display = 'block';

                try {
                    fetch(data.thumbnail)
                    .then(function(res) { return res.ok ? res.blob() : null; })
                    .then(function(blob) {
                        if (blob && fileInput) {
                            var filename = 'cover_' + (data.isbn || 'auto') + '.jpg';
                            var file = new File([blob], filename, { type: blob.type || 'image/jpeg' });
                            var dt = new DataTransfer();
                            dt.items.add(file);
                            fileInput.files = dt.files;
                        }
                    })
                    .catch(function() {});
                } catch(e) {}
            } else if (coverWrap) {
                if (remoteCoverInput) remoteCoverInput.value = '';
                coverWrap.style.display = 'none';
            }
        }

        function executeFetch(type, identifier, triggerButton) {
            var trimmed = (identifier || '').trim();
            if (!trimmed) {
                showAlert('danger', 'Please enter a valid ' + type.toUpperCase() + ' number before fetching.');
                var targetInput = (type === 'isbn') ? inputIsbn : inputIssn;
                if (targetInput) targetInput.focus();
                return;
            }

            // Button loading state
            var originalBtnHtml = triggerButton.innerHTML;
            triggerButton.disabled = true;
            triggerButton.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Fetching...';

            var apiUrl = 'api/fetch-metadata.php?type=' + encodeURIComponent(type) + '&identifier=' + encodeURIComponent(trimmed) + '&exclude_id=' + encodeURIComponent(currentEditId);

            fetch(apiUrl, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                }
            })
            .then(function(response) {
                return response.json().then(function(json) {
                    return { ok: response.ok, status: response.status, data: json };
                });
            })
            .then(function(res) {
                var localMatch = (res.data && res.data.local_match && res.data.local_match.exists) ? res.data.local_match : null;

                if (!res.ok || res.data.status !== 'success') {
                    if (localMatch) {
                        showDuplicateModal(type, trimmed, localMatch);
                    }
                    var errorMsg = (res.data && res.data.message) ? res.data.message : 'Error fetching metadata (HTTP ' + res.status + ')';
                    showAlert(localMatch ? 'warning' : 'danger', escapeHtml(errorMsg));
                    return;
                }

                var item = res.data.data;
                if (item) {
                    populateFormWithData(item);
                }

                if (localMatch) {
                    showDuplicateModal(type, trimmed, localMatch);
                }

                var providerBadge = '';
                if (item && item.provider === 'google_books') {
                    providerBadge = ' <span class="badge" style="background:#1a73e8;color:#fff;font-size:0.75rem;padding:3px 8px;border-radius:4px;font-weight:600;"><i class="fa-brands fa-google me-1"></i>Google Books API</span>';
                } else if (item && item.provider === 'open_library') {
                    providerBadge = ' <span class="badge" style="background:#0284c7;color:#fff;font-size:0.75rem;padding:3px 8px;border-radius:4px;font-weight:600;"><i class="fa-solid fa-book-open me-1"></i>Open Library</span>';
                } else if (item && item.provider === 'pnm_polaris') {
                    providerBadge = ' <span class="badge" style="background:#0f766e;color:#fff;font-size:0.75rem;padding:3px 8px;border-radius:4px;font-weight:600;"><i class="fa-solid fa-landmark me-1"></i>PNM Polaris</span>';
                } else if (item && item.provider === 'crossref') {
                    providerBadge = ' <span class="badge" style="background:#7c3aed;color:#fff;font-size:0.75rem;padding:3px 8px;border-radius:4px;font-weight:600;"><i class="fa-solid fa-newspaper me-1"></i>Crossref API</span>';
                } else if (item && item.provider) {
                    providerBadge = ' <span class="badge badge-secondary ms-1" style="font-size:0.75rem;padding:3px 8px;border-radius:4px;">' + escapeHtml(item.provider) + '</span>';
                }

                if (item && item.title) {
                    var successMsg = '<strong>Metadata Auto-Populated!</strong> Successfully retrieved record for <em>' + escapeHtml(item.title) + '</em>';
                    if (providerBadge) {
                        successMsg += ' &bull; Source: ' + providerBadge;
                    }
                    if (item.call_number) {
                        successMsg += ' &bull; Call Number: <span class="badge badge-primary font-bold ms-1">' + escapeHtml(item.call_number) + '</span>';
                    }
                    showAlert('success', successMsg);
                }
            })
            .catch(function(err) {
                showAlert('danger', 'Network request failed: ' + escapeHtml(err.message || 'Unable to connect to metadata service.'));
            })
            .finally(function() {
                triggerButton.disabled = false;
                triggerButton.innerHTML = originalBtnHtml;
            });
        }

        // Attach Event Listeners
        if (btnIsbn && inputIsbn) {
            btnIsbn.addEventListener('click', function(e) {
                e.preventDefault();
                executeFetch('isbn', inputIsbn.value, btnIsbn);
            });

            inputIsbn.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    executeFetch('isbn', inputIsbn.value, btnIsbn);
                }
            });
        }

        if (btnIssn && inputIssn) {
            btnIssn.addEventListener('click', function(e) {
                e.preventDefault();
                executeFetch('issn', inputIssn.value, btnIssn);
            });

            inputIssn.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    executeFetch('issn', inputIssn.value, btnIssn);
                }
            });
        }

        function cleanHyphensInput(el) {
            if (!el) return;
            var clean = el.value.replace(/[-\u2013\u2014]/g, '');
            if (el.value !== clean) {
                el.value = clean;
            }
        }

        // Live sanitize ISBN and ISSN inputs on blur and form submit
        if (inputIsbn) {
            inputIsbn.addEventListener('blur', function() {
                cleanHyphensInput(inputIsbn);
                checkDuplicateOnBlur('isbn', inputIsbn);
            });
        }
        if (inputIssn) {
            inputIssn.addEventListener('blur', function() {
                cleanHyphensInput(inputIssn);
                checkDuplicateOnBlur('issn', inputIssn);
            });
        }

        var extraIsbnInputs = document.querySelectorAll('input[name^="isbn_"], input[name^="isbn1_"], input[name^="isbn3_"]');
        for (var idx = 0; idx < extraIsbnInputs.length; idx++) {
            (function(el) {
                el.addEventListener('blur', function() {
                    cleanHyphensInput(el);
                    checkDuplicateOnBlur('isbn', el);
                });
            })(extraIsbnInputs[idx]);
        }

        if (document.someform) {
            document.someform.addEventListener('submit', function() {
                if (inputIsbn) cleanHyphensInput(inputIsbn);
                if (inputIssn) cleanHyphensInput(inputIssn);
                for (var idx = 0; idx < extraIsbnInputs.length; idx++) {
                    cleanHyphensInput(extraIsbnInputs[idx]);
                }
            });
        }

        var btnClearCover = document.getElementById('btn_clear_auto_cover');
        if (btnClearCover) {
            btnClearCover.addEventListener('click', function(e) {
                e.preventDefault();
                var remoteInput = document.getElementById('remote_cover_url');
                if (remoteInput) remoteInput.value = '';
                var fileInput = document.someform && document.someform.imageatt1;
                if (fileInput) fileInput.value = '';
                var wrap = document.getElementById('fetch_cover_preview_wrap');
                if (wrap) wrap.style.display = 'none';
            });
        }
    })();
    </script>
</body>
</html>
