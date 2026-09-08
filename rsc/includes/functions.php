<?php
    
    function countOnlineDuration($timenow, $lastlogin)
    {
        if (empty($timenow) || empty($lastlogin)) {
            return "";
        }
        $now_array=explode(' ', (string)$timenow);
        $lasttime_array=explode(' ', (string)$lastlogin);
        
        if (count($now_array) < 4 || count($lasttime_array) < 4) {
            return "";
        }
        
        $now_ampm=explode(':', $now_array[2] ?? '0:0');
        if (isset($now_array[3]) && $now_array[3] == 'pm' && ($now_ampm[0] ?? 0) <> 12) {
            $now_ampm[0]=((int)($now_ampm[0] ?? 0))+12;
        }

        $now_array[2]=($now_ampm[0] ?? '0').":".($now_ampm[1] ?? '0');
    
        $lasttime_ampm=explode(':', $lasttime_array[2] ?? '0:0');
        if (isset($lasttime_array[3]) && $lasttime_array[3] == 'pm' && ($lasttime_ampm[0] ?? 0) <> 12) {
            $lasttime_ampm[0]=((int)($lasttime_ampm[0] ?? 0))+12;
        }

        $lasttime_array[2]=($lasttime_ampm[0] ?? '0').":".($lasttime_ampm[1] ?? '0');
                                                    
        $now_days=explode('/', $now_array[1] ?? '');
        $now_days=implode('-', $now_days);
        $lasttime_days=explode('/', $lasttime_array[1] ?? '');
        $lasttime_days=implode('-', $lasttime_days);
        
        $now = strtotime(date($now_days." ".$now_array[2]));
        $lasttime = strtotime($lasttime_days." ".$lasttime_array[2]);
        
        if (!$now || !$lasttime) {
            return "";
        }
        
        $dateDiff = $now-$lasttime;
        $fullDays = floor($dateDiff/(60*60*24));
        $fullHours = floor(($dateDiff-($fullDays*60*60*24))/(60*60));
        $fullMinutes = floor(($dateDiff-($fullDays*60*60*24)-($fullHours*60*60))/60);
        return "<b>Logged:</b> $fullDays days, $fullHours hrs, $fullMinutes mins.";
    }
    
    function getmicrotime()
    {
        list($usec, $sec) = explode(" ", microtime());
        return (float)$usec + (float)$sec;
    }

    function just_clean($string)
    {
        $specialCharacters = array(
        '#' => '',
        '$' => '',
        '%' => '',
        '&' => '',
        '@' => '',
        '.' => '',
        '�' => '',
        '+' => '',
        '=' => '',
        '�' => '',
        '\\' => '',
        '/' => ''
        );

        foreach ($specialCharacters as $character => $replacement) {
            $string = str_replace($character, '' . $replacement . '', $string);
        }

        // Remove all remaining other unknown characters
        $string = preg_replace('/[^a-zA-Z0-9\-]/', '', $string);
        $string = preg_replace('/^-+/', '', $string);
        $string = preg_replace('/-+$/', '', $string);
        $string = preg_replace('/-{2,}/', '', $string);

        return $string;
    }

    function highlight($text, $words)
    {
        $words = trim($words);
        $wordsArray = explode(' ', $words);
        foreach ($wordsArray as $word) {
            if (strlen(trim($word)) > 2) {
                $word = just_clean($word);//remove unwanted special characters, replace it with nothing
                
                //mekanisma kawalan untuk buang sekiranya penggguna terlebih space
                if ($word != '') {
                        $hlstart = '<mark class="bg-highlight">';
                        $hlend = '</mark>';
                        $text = preg_replace("/$word/i", $hlstart.'\\0'.$hlend, $text);//php7
                    }
            }
        }
        return $text;
    }

    function displayFines($typeid)
    {
        if (empty($typeid) || !is_numeric($typeid)) {
            return "Unset";
        }
        $stmt = mysqli_prepare($GLOBALS["conn"], "SELECT * FROM eg_type_fines WHERE `38typeid` = ? ORDER BY id DESC LIMIT 1");
        if ($stmt) {
            $tid = (int)$typeid;
            mysqli_stmt_bind_param($stmt, "i", $tid);
            mysqli_stmt_execute($stmt);
            $result3 = mysqli_stmt_get_result($stmt);
            $myrow3 = mysqli_fetch_array($result3);
            mysqli_stmt_close($stmt);
            if (!isset($myrow3["39fines_initdays"])) {
                return "Unset";
            } else {
                return "Initial : ".$myrow3["39fines_initdays"]." days (".$myrow3["39fines_initamount"]."/".$myrow3["39fines_subsequenceamount"].")";
            }
        }
        return "Unset";
    }
    
    function getIDCurrentEnforcedFine($typeid)
    {
        if (empty($typeid) || !is_numeric($typeid)) {
            return 0;
        }
        $stmt = mysqli_prepare($GLOBALS["conn"], "SELECT id FROM eg_type_fines WHERE `38typeid` = ? ORDER BY id DESC LIMIT 1");
        if ($stmt) {
            $tid = (int)$typeid;
            mysqli_stmt_bind_param($stmt, "i", $tid);
            mysqli_stmt_execute($stmt);
            $resultEF = mysqli_stmt_get_result($stmt);
            if ($resultEF && $myrowEF = mysqli_fetch_array($resultEF)) {
                mysqli_stmt_close($stmt);
                return $myrowEF["id"] ?? 0;
            }
            mysqli_stmt_close($stmt);
        }
        return 0;
    }
    
    function getTypeID($accessnum)
    {
        if (empty($accessnum)) {
            return 0;
        }
        $stmt = mysqli_prepare($GLOBALS["conn"], "SELECT eg_item.`39type` FROM eg_item_copies, eg_item WHERE eg_item_copies.eg_item_id=eg_item.id AND eg_item_copies.`39accessnum`=?");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $accessnum);
            mysqli_stmt_execute($stmt);
            $resultJenis = mysqli_stmt_get_result($stmt);
            if ($resultJenis && $myrowJenis = mysqli_fetch_array($resultJenis)) {
                mysqli_stmt_close($stmt);
                return $myrowJenis["39type"] ?? 0;
            }
            mysqli_stmt_close($stmt);
        }
        return 0;
    }

    // Unified shiftDueDate checking both weekly working days schedule and public holidays
    function shiftDueDate($duedate)
    {
        if (empty($duedate) || !is_numeric($duedate)) {
            return $duedate;
        }

        $queryW = "select * from eg_working_days LIMIT 1";
        $resultW = mysqli_query($GLOBALS["conn"], $queryW);
        $myrowW = $resultW ? mysqli_fetch_array($resultW) : null;
        
        $dayMap = [
            'Mon' => 'mon',
            'Tue' => 'tue',
            'Wed' => 'wed',
            'Thu' => 'thu',
            'Fri' => 'fri',
            'Sat' => 'sat',
            'Sun' => 'sun'
        ];
        
        $queryH = "select UNIX_TIMESTAMP(STR_TO_DATE(`38hol_date`,'%d/%m/%Y')) as `38hol_date2` from eg_holiday";
        $resultH = mysqli_query($GLOBALS["conn"], $queryH);
        
        $holidays = [];
        if ($resultH) {
            while ($myrowH = mysqli_fetch_array($resultH)) {
                if (!empty($myrowH["38hol_date2"])) {
                    $holidays[] = (int)$myrowH["38hol_date2"];
                }
            }
        }
        
        $maxIterations = 365; // Prevent infinite loop
        $iterations = 0;
        
        while ($iterations < $maxIterations) {
            $duedateDayName = date('D', $duedate);
            $isNonWorkingDay = $myrowW && isset($dayMap[$duedateDayName]) && isset($myrowW[$dayMap[$duedateDayName]]) && $myrowW[$dayMap[$duedateDayName]] === '0';
            
            $duedateNormalized = strtotime(date('Y-m-d', $duedate));
            $isHoliday = in_array($duedateNormalized, $holidays, true);
            
            if ($isNonWorkingDay || $isHoliday) {
                $duedate += 86400; // Add 1 day
                $iterations++;
            } else {
                break;
            }
        }
        
        return $duedate;
    }

    function calculateOverdueDays($returnTimestamp, $dueTimestamp)
    {
        if (empty($returnTimestamp) || empty($dueTimestamp)) {
            return 0;
        }
        
        $returnDateSec = strtotime(date('Y-m-d', (int)$returnTimestamp));
        $dueDateSec = strtotime(date('Y-m-d', (int)$dueTimestamp));
        
        if ($returnDateSec <= $dueDateSec) {
            return 0;
        }
        
        return (int)round(($returnDateSec - $dueDateSec) / 86400);
    }

    function formatAccessionNumber($accessnum, $length = 10)
    {
        $trimmed = trim((string)$accessnum);
        if ($trimmed === '') {
            return '';
        }
        if (ctype_digit($trimmed) || is_numeric($trimmed)) {
            return str_pad($trimmed, $length, '0', STR_PAD_LEFT);
        }
        return $trimmed;
    }
    
    function calculatedFines($days, $accessnum, $enforcedfine_id)
    {
        $typeId = getTypeID($accessnum);
        if (empty($typeId) || empty($enforcedfine_id)) {
            return 0;
        }
        $stmt = mysqli_prepare($GLOBALS["conn"], "SELECT * FROM eg_type_fines WHERE id=? AND `38typeid`=?");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "is", $enforcedfine_id, $typeId);
            mysqli_stmt_execute($stmt);
            $resultFines = mysqli_stmt_get_result($stmt);
            if ($resultFines && mysqli_num_rows($resultFines) > 0) {
                $myrowFines = mysqli_fetch_array($resultFines);
                mysqli_stmt_close($stmt);
                $fines_initdays = $myrowFines["39fines_initdays"] ?? 0;
                $fines_initamount = $myrowFines["39fines_initamount"] ?? 0;
                $fines_subsequenceamount = $myrowFines["39fines_subsequenceamount"] ?? 0;

                $after_init = $days - $fines_initdays;
                if ($after_init <= 0) {
                    $fines_pay = $days * $fines_initamount;
                } else {
                    $fines_pay = $fines_initdays * $fines_initamount;
                    $fines_pay = $fines_pay + ($after_init * $fines_subsequenceamount);
                }
                return $fines_pay;
            }
            mysqli_stmt_close($stmt);
        }
        return 0;
    }
    function getTitle($accessnum)
    {
        if (empty($accessnum)) {
            return 'Unknown Item';
        }
        $stmtB = mysqli_prepare($GLOBALS["conn"], "SELECT eg_item_id FROM eg_item_copies WHERE `39accessnum`=?");
        if ($stmtB) {
            mysqli_stmt_bind_param($stmtB, "s", $accessnum);
            mysqli_stmt_execute($stmtB);
            $resultB = mysqli_stmt_get_result($stmtB);
            if ($resultB && $myrowB = mysqli_fetch_array($resultB)) {
                mysqli_stmt_close($stmtB);
                $bahanId = (int)($myrowB["eg_item_id"] ?? 0);
                if (!empty($bahanId)) {
                    $stmtC = mysqli_prepare($GLOBALS["conn"], "SELECT `38title` FROM eg_item WHERE id=?");
                    if ($stmtC) {
                        mysqli_stmt_bind_param($stmtC, "i", $bahanId);
                        mysqli_stmt_execute($stmtC);
                        $resultC = mysqli_stmt_get_result($stmtC);
                        if ($resultC && $myrowC = mysqli_fetch_array($resultC)) {
                            mysqli_stmt_close($stmtC);
                            return $myrowC["38title"] ?? 'Unknown Title';
                        }
                        mysqli_stmt_close($stmtC);
                    }
                }
            } else {
                mysqli_stmt_close($stmtB);
            }
        }
        return 'Unknown Item';
    }

    function getPatronAvatarPath($username, $avatar_dir = null)
    {
        if (empty($username)) {
            return null;
        }
        $safe_user = preg_replace('/[^a-zA-Z0-9_-]/', '_', $username);
        $parent_dir = $_SESSION["parent_dir"] ?? 'site';
        
        // Physical disk directory
        $base_root = dirname(__DIR__, 2);
        $physical_dir = $base_root . DIRECTORY_SEPARATOR . $parent_dir . DIRECTORY_SEPARATOR . 'avatars';
        $allowed_exts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        
        $found_ext = null;
        $found_mtime = 0;
        foreach ($allowed_exts as $ext) {
            $disk_file = $physical_dir . DIRECTORY_SEPARATOR . $safe_user . '.' . $ext;
            if (is_file($disk_file)) {
                $found_ext = $ext;
                $found_mtime = filemtime($disk_file);
                break;
            }
        }
        
        if ($found_ext === null) {
            return null;
        }
        
        if ($avatar_dir !== null) {
            $web_path = rtrim($avatar_dir, '/') . '/' . $safe_user . '.' . $found_ext;
        } else {
            $script_dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
            if (str_ends_with($script_dir, '/admin') || str_ends_with($script_dir, '/reports') || str_ends_with($script_dir, '/browsers')) {
                $web_path = '../../' . $parent_dir . '/avatars/' . $safe_user . '.' . $found_ext;
            } else {
                $web_path = '../' . $parent_dir . '/avatars/' . $safe_user . '.' . $found_ext;
            }
        }
        
        return $web_path . '?v=' . $found_mtime;
    }


    function getUserType($patron_id)
    {
        if (empty($patron_id)) {
            return '';
        }
        $stmt = mysqli_prepare($GLOBALS["conn"], "SELECT allowed FROM eg_auth WHERE username=?");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $patron_id);
            mysqli_stmt_execute($stmt);
            $resultT = mysqli_stmt_get_result($stmt);
            if ($resultT && $myrowT = mysqli_fetch_array($resultT)) {
                mysqli_stmt_close($stmt);
                return $myrowT["allowed"] ?? '';
            }
            mysqli_stmt_close($stmt);
        }
        return '';
    }

    function maxday($patron_id)
    {
        $usertype = getUserType($patron_id);
        $stmt = mysqli_prepare($GLOBALS["conn"], "SELECT max_day FROM eg_auth_allowedloan WHERE usertype=?");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $usertype);
            mysqli_stmt_execute($stmt);
            $resultS = mysqli_stmt_get_result($stmt);
            $myrowS = mysqli_fetch_array($resultS);
            mysqli_stmt_close($stmt);
            return $myrowS["max_day"] ?? 0;
        }
        return 0;
    }

    function maxloanitem($patron_id)
    {
        $usertype = getUserType($patron_id);
        $stmt = mysqli_prepare($GLOBALS["conn"], "SELECT max_loanitem FROM eg_auth_allowedloan WHERE usertype=?");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $usertype);
            mysqli_stmt_execute($stmt);
            $resultS = mysqli_stmt_get_result($stmt);
            $myrowS = mysqli_fetch_array($resultS);
            mysqli_stmt_close($stmt);
            return (int)($myrowS["max_loanitem"] ?? 0);
        }
        return 0;
    }

    function countCurrentLoans($patron_id)
    {
        if (empty($patron_id)) {
            return 0;
        }
        $stmt = mysqli_prepare($GLOBALS["conn"], "SELECT count(id) as current_loans FROM eg_item_charge WHERE `39patron`=? AND (`40dc` IS NULL OR `40dc` != 'DC')");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $patron_id);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_array($res);
            mysqli_stmt_close($stmt);
            return (int)($row["current_loans"] ?? 0);
        }
        return 0;
    }


    function existPatron($patron_id)
    {
        $stmt = mysqli_prepare($GLOBALS["conn"], "SELECT count(id) as exist1 FROM eg_auth WHERE username=?");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $patron_id);
            mysqli_stmt_execute($stmt);
            $resultT = mysqli_stmt_get_result($stmt);
            $myrowT = mysqli_fetch_array($resultT);
            mysqli_stmt_close($stmt);
            return $myrowT["exist1"] ?? 0;
        }
        return 0;
    }
    
    function existCopies($accessnum)
    {
        $stmt = mysqli_prepare($GLOBALS["conn"], "SELECT count(id) as exist1 FROM eg_item_copies WHERE `39accessnum`=?");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $accessnum);
            mysqli_stmt_execute($stmt);
            $resultT = mysqli_stmt_get_result($stmt);
            $myrowT = mysqli_fetch_array($resultT);
            mysqli_stmt_close($stmt);
            return $myrowT["exist1"] ?? 0;
        }
        return 0;
    }
    
    function isAvailable($accessnum)
    {
        $stmt = mysqli_prepare($GLOBALS["conn"], "SELECT `39status`, `39is_reference` FROM eg_item_copies WHERE `39accessnum`=?");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $accessnum);
            mysqli_stmt_execute($stmt);
            $resultT = mysqli_stmt_get_result($stmt);
            $myrowT = mysqli_fetch_array($resultT);
            mysqli_stmt_close($stmt);
            $status = $myrowT["39status"] ?? '';
            $is_ref = $myrowT["39is_reference"] ?? 'NO';
            if ($status === 'AVAILABLE' && $is_ref !== 'YES') {
                return "TRUE";
            } else {
                return "FALSE";
            }
        }
        return "FALSE";
    }

    function isReferenceCopy($accessnum)
    {
        $stmt = mysqli_prepare($GLOBALS["conn"], "SELECT `39status`, `39is_reference` FROM eg_item_copies WHERE `39accessnum`=?");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $accessnum);
            mysqli_stmt_execute($stmt);
            $resultT = mysqli_stmt_get_result($stmt);
            $myrowT = mysqli_fetch_array($resultT);
            mysqli_stmt_close($stmt);
            if (($myrowT["39is_reference"] ?? 'NO') === 'YES' || ($myrowT["39status"] ?? '') === 'REFERENCE') {
                return "TRUE";
            }
        }
        return "FALSE";
    }
        
    function isPatronEligibility($patron_id)
    {
        $stmtT = mysqli_prepare($GLOBALS["conn"], "SELECT count(`39patron`) as jumlahMasihDiPinjam FROM eg_item_charge WHERE `39patron`=? AND `40dc`!='DC'");
        $jumlahMasihDiPinjam = 0;
        if ($stmtT) {
            mysqli_stmt_bind_param($stmtT, "s", $patron_id);
            mysqli_stmt_execute($stmtT);
            $resultT = mysqli_stmt_get_result($stmtT);
            $myrowT = mysqli_fetch_array($resultT);
            $jumlahMasihDiPinjam = $myrowT["jumlahMasihDiPinjam"] ?? 0;
            mysqli_stmt_close($stmtT);
        }
        
        $usertype = getUserType($patron_id);
        $stmtS = mysqli_prepare($GLOBALS["conn"], "SELECT max_loanitem FROM eg_auth_allowedloan WHERE usertype=?");
        $maxLoanItem = 0;
        if ($stmtS) {
            mysqli_stmt_bind_param($stmtS, "s", $usertype);
            mysqli_stmt_execute($stmtS);
            $resultS = mysqli_stmt_get_result($stmtS);
            $myrowS = mysqli_fetch_array($resultS);
            $maxLoanItem = $myrowS["max_loanitem"] ?? 0;
            mysqli_stmt_close($stmtS);
        }
        
        if ($jumlahMasihDiPinjam < $maxLoanItem) {
            return "TRUE";
        } else {
            return "FALSE";
        }
    }
    
    function getTypeName($accessnum)
    {
        $typeId = getTypeID($accessnum);
        if (!empty($typeId)) {
            $stmt = mysqli_prepare($GLOBALS["conn"], "SELECT `38type` FROM eg_type WHERE `38typeid`=?");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "s", $typeId);
                mysqli_stmt_execute($stmt);
                $resultJenis = mysqli_stmt_get_result($stmt);
                if ($resultJenis && $myrowJenis = mysqli_fetch_array($resultJenis)) {
                    mysqli_stmt_close($stmt);
                    return $myrowJenis["38type"] ?? 'Unknown Type';
                }
                mysqli_stmt_close($stmt);
            }
        }
        return 'Unknown Type';
    }

    function existCharge($accessnum)
    {
        if (empty($accessnum)) {
            return 0;
        }
        $stmt = mysqli_prepare($GLOBALS["conn"], "SELECT count(id) as exist1 FROM eg_item_charge WHERE `39accessnum`=? AND (`40dc` IS NULL OR `40dc` != 'DC')");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $accessnum);
            mysqli_stmt_execute($stmt);
            $resultT = mysqli_stmt_get_result($stmt);
            if ($resultT && $myrowT = mysqli_fetch_array($resultT)) {
                mysqli_stmt_close($stmt);
                return $myrowT["exist1"] ?? 0;
            }
            mysqli_stmt_close($stmt);
        }
        return 0;
    }
    
    function idToType($typeid)
    {
        if (empty($typeid) || !is_numeric($typeid)) {
            return 'Unknown';
        }
        $stmt = mysqli_prepare($GLOBALS["conn"], "SELECT `38type` FROM eg_type WHERE `38typeid` = ?");
        if ($stmt) {
            $tid = (int)$typeid;
            mysqli_stmt_bind_param($stmt, "i", $tid);
            mysqli_stmt_execute($stmt);
            $result3 = mysqli_stmt_get_result($stmt);
            if ($result3 && $myrow3 = mysqli_fetch_array($result3)) {
                mysqli_stmt_close($stmt);
                return $myrow3["38type"] ?? 'Unknown';
            }
            mysqli_stmt_close($stmt);
        }
        return 'Unknown';
    }

    function namePatron($pid)
    {
        $stmt = mysqli_prepare($GLOBALS["conn"], "SELECT name FROM eg_auth WHERE id=?");
        if ($stmt) {
            $id = (int)$pid;
            mysqli_stmt_bind_param($stmt, "i", $id);
            mysqli_stmt_execute($stmt);
            $resultT = mysqli_stmt_get_result($stmt);
            $myrowT = mysqli_fetch_array($resultT);
            mysqli_stmt_close($stmt);
            return $myrowT["name"] ?? '';
        }
        return '';
    }

    function patronIdToUsername($pid)
    {
        $stmt = mysqli_prepare($GLOBALS["conn"], "SELECT username FROM eg_auth WHERE id=?");
        if ($stmt) {
            $id = (int)$pid;
            mysqli_stmt_bind_param($stmt, "i", $id);
            mysqli_stmt_execute($stmt);
            $resultT = mysqli_stmt_get_result($stmt);
            $myrowT = mysqli_fetch_array($resultT);
            mysqli_stmt_close($stmt);
            return $myrowT["username"] ?? '';
        }
        return '';
    }

    function patronUsernametoName($pname)
    {
        $stmt = mysqli_prepare($GLOBALS["conn"], "SELECT name FROM eg_auth WHERE username=?");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $pname);
            mysqli_stmt_execute($stmt);
            $resultT = mysqli_stmt_get_result($stmt);
            $myrowT = mysqli_fetch_array($resultT);
            mysqli_stmt_close($stmt);
            return $myrowT["name"] ?? '';
        }
        return '';
    }
    
    function patronUsernameToID($patron_id)
    {
        $stmt = mysqli_prepare($GLOBALS["conn"], "SELECT id FROM eg_auth WHERE username=?");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $patron_id);
            mysqli_stmt_execute($stmt);
            $resultT = mysqli_stmt_get_result($stmt);
            $myrowT = mysqli_fetch_array($resultT);
            mysqli_stmt_close($stmt);
            if (isset($myrowT["id"])) {
                return $myrowT["id"];
            }
        }
        return 0;
    }


    function subjectFromAcronym($acronym)
    {
        $stmt = mysqli_prepare($GLOBALS["conn"], "SELECT `43subject` FROM eg_subjectheading WHERE `43acronym`=?");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $acronym);
            mysqli_stmt_execute($stmt);
            $resultT = mysqli_stmt_get_result($stmt);
            $myrowT = mysqli_fetch_array($resultT);
            mysqli_stmt_close($stmt);
            if (isset($myrowT["43subject"])) {
                return $myrowT["43subject"];
            }
        }
        return '';
    }

    function return_bytes($val)
    {
        $val = trim((string)$val);
        if ($val === '') {
            return 0;
        }
        $last = strtolower($val[strlen($val)-1]);
        $val = (int)$val;
        
        $multipliers = [
            'g' => 1024 * 1024 * 1024,
            'm' => 1024 * 1024,
            'k' => 1024
        ];
        
        if (isset($multipliers[$last])) {
            $val *= $multipliers[$last];
        }
        
        return $val;
    }

    /**
     * Clean and strip formatting from ISBN or ISSN string
     */
    function cleanIsbnIssn($code)
    {
        return strtoupper(preg_replace('/[^0-9X]/i', '', trim((string)$code)));
    }

    /**
     * Get variants (ISBN-10 and ISBN-13) for a given ISBN or ISSN
     */
    function getIsbnIssnVariants($code)
    {
        $clean = cleanIsbnIssn($code);
        if (empty($clean)) {
            return [];
        }
        $variants = [$clean];

        if (strlen($clean) === 13 && str_starts_with($clean, '978')) {
            $core = substr($clean, 3, 9);
            $sum = 0;
            for ($i = 0; $i < 9; $i++) {
                $sum += (int)$core[$i] * (10 - $i);
            }
            $remainder = $sum % 11;
            $check = 11 - $remainder;
            $checkChar = ($check === 10) ? 'X' : (($check === 11) ? '0' : (string)$check);
            $v10 = $core . $checkChar;
            if (!in_array($v10, $variants, true)) {
                $variants[] = $v10;
            }
        } elseif (strlen($clean) === 10) {
            $core = '978' . substr($clean, 0, 9);
            $sum = 0;
            for ($i = 0; $i < 12; $i++) {
                $sum += (int)$core[$i] * (($i % 2 === 0) ? 1 : 3);
            }
            $check = (10 - ($sum % 10)) % 10;
            $v13 = $core . (string)$check;
            if (!in_array($v13, $variants, true)) {
                $variants[] = $v13;
            }
        }

        return $variants;
    }

    /**
     * Find item IDs matching an ISBN or ISSN code
     */
    function getItemIdsByIsbnIssn($code)
    {
        $variants = getIsbnIssnVariants($code);
        if (empty($variants)) {
            return [];
        }

        $itemIds = [];

        // 1. Search in eg_item for 38isbn and 38issn
        foreach ($variants as $var) {
            $stmt = mysqli_prepare($GLOBALS["conn"], "SELECT id FROM eg_item WHERE REPLACE(REPLACE(REPLACE(`38isbn`, '-', ''), ' ', ''), '–', '') = ? OR REPLACE(REPLACE(REPLACE(`38issn`, '-', ''), ' ', ''), '–', '') = ?");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "ss", $var, $var);
                mysqli_stmt_execute($stmt);
                $res = mysqli_stmt_get_result($stmt);
                while ($row = mysqli_fetch_assoc($res)) {
                    $itemIds[] = (int)$row['id'];
                }
                mysqli_stmt_close($stmt);
            }
        }

        // 2. Search in eg_item_isbn table (isbn1 .. isbn20)
        foreach ($variants as $var) {
            for ($i = 1; $i <= 20; $i++) {
                $col = "isbn" . $i;
                $stmt = mysqli_prepare($GLOBALS["conn"], "SELECT eg_item_id FROM eg_item_isbn WHERE REPLACE(REPLACE(REPLACE(`$col`, '-', ''), ' ', ''), '–', '') = ?");
                if ($stmt) {
                    mysqli_stmt_bind_param($stmt, "s", $var);
                    mysqli_stmt_execute($stmt);
                    $res = mysqli_stmt_get_result($stmt);
                    while ($row = mysqli_fetch_assoc($res)) {
                        $itemIds[] = (int)$row['eg_item_id'];
                    }
                    mysqli_stmt_close($stmt);
                }
            }
        }

        return array_values(array_unique(array_filter($itemIds)));
    }

    /**
     * Resolve an available copy for charging by ISBN or ISSN
     */
    function getAvailableCopyByIsbnIssn($code)
    {
        $itemIds = getItemIdsByIsbnIssn($code);
        if (empty($itemIds)) {
            return [
                'status' => 'NOT_FOUND',
                'message' => 'No bibliographic record found for ISBN/ISSN <strong>' . htmlspecialchars($code, ENT_QUOTES, 'UTF-8') . '</strong>.'
            ];
        }

        $idList = implode(',', array_map('intval', $itemIds));

        // 1. Check for available non-reference copy
        $queryAvail = "SELECT c.id, c.`39accessnum`, c.`39is_reference`, c.`39status`, i.id as item_id, i.`38title`
                       FROM eg_item_copies c
                       INNER JOIN eg_item i ON c.eg_item_id = i.id
                       WHERE c.eg_item_id IN ($idList)
                         AND c.`39status` = 'AVAILABLE'
                         AND (c.`39is_reference` IS NULL OR c.`39is_reference` = 'NO' OR c.`39is_reference` = '0' OR c.`39is_reference` = '')
                       ORDER BY c.id ASC LIMIT 1";
        $resAvail = mysqli_query($GLOBALS["conn"], $queryAvail);
        if ($resAvail && $rowAvail = mysqli_fetch_assoc($resAvail)) {
            return [
                'status' => 'AVAILABLE',
                'accessnum' => $rowAvail['39accessnum'],
                'title' => $rowAvail['38title'],
                'item_id' => $rowAvail['item_id']
            ];
        }

        // 2. If not found, diagnose reason
        $queryTotal = "SELECT count(id) as total_copies,
                              SUM(CASE WHEN `39is_reference` = 'YES' OR `39status` = 'REFERENCE' THEN 1 ELSE 0 END) as ref_copies,
                              SUM(CASE WHEN `39status` = 'CIRCULATED' THEN 1 ELSE 0 END) as circ_copies
                       FROM eg_item_copies
                       WHERE eg_item_id IN ($idList)";
        $resTotal = mysqli_query($GLOBALS["conn"], $queryTotal);
        $totalRow = $resTotal ? mysqli_fetch_assoc($resTotal) : null;
        $totalCopies = (int)($totalRow['total_copies'] ?? 0);
        $refCopies = (int)($totalRow['ref_copies'] ?? 0);
        $circCopies = (int)($totalRow['circ_copies'] ?? 0);

        if ($totalCopies === 0) {
            return [
                'status' => 'NO_COPIES',
                'message' => 'No physical copies are registered in the catalog for this ISBN/ISSN.'
            ];
        }

        if ($refCopies === $totalCopies) {
            return [
                'status' => 'REFERENCE_ONLY',
                'message' => 'Loan restricted: All copies of this title are designated as <strong>Reference Only / In-Library Use</strong>.'
            ];
        }

        return [
            'status' => 'ALL_LOANED',
            'message' => 'All available copies of this title (' . $totalCopies . ' registered) are currently on loan or unavailable.'
        ];
    }

    /**
     * Find active loans by ISBN/ISSN (optionally filtered by patron ID)
     */
    function getActiveLoansByIsbnIssn($code, $patron_id = null)
    {
        $itemIds = getItemIdsByIsbnIssn($code);
        if (empty($itemIds)) {
            return [
                'status' => 'NOT_FOUND',
                'message' => 'No bibliographic record found for ISBN/ISSN <strong>' . htmlspecialchars($code, ENT_QUOTES, 'UTF-8') . '</strong>.',
                'loans' => []
            ];
        }

        $idList = implode(',', array_map('intval', $itemIds));
        $patronClean = trim((string)$patron_id);

        if (!empty($patronClean)) {
            $stmt = mysqli_prepare($GLOBALS["conn"], "SELECT ch.id, ch.`39accessnum`, ch.`39patron`, ch.`39charged_on`, ch.`39duedate`, i.`38title`, i.`39type`
                                                      FROM eg_item_charge ch
                                                      INNER JOIN eg_item_copies cp ON ch.`39accessnum` = cp.`39accessnum`
                                                      INNER JOIN eg_item i ON cp.eg_item_id = i.id
                                                      WHERE cp.eg_item_id IN ($idList)
                                                        AND (ch.`40dc` IS NULL OR ch.`40dc` != 'DC')
                                                        AND ch.`39patron` = ?
                                                      ORDER BY ch.id ASC");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "s", $patronClean);
                mysqli_stmt_execute($stmt);
                $res = mysqli_stmt_get_result($stmt);
                $loans = [];
                while ($row = mysqli_fetch_assoc($res)) {
                    $loans[] = $row;
                }
                mysqli_stmt_close($stmt);

                if (count($loans) === 0) {
                    return [
                        'status' => 'NO_PATRON_LOAN',
                        'message' => 'Patron <strong>' . htmlspecialchars($patronClean, ENT_QUOTES, 'UTF-8') . '</strong> does not currently have this title on loan.',
                        'loans' => []
                    ];
                }

                return [
                    'status' => 'MATCH',
                    'loans' => $loans,
                    'loan' => $loans[0]
                ];
            }
        } else {
            $queryLoans = "SELECT ch.id, ch.`39accessnum`, ch.`39patron`, ch.`39charged_on`, ch.`39duedate`, i.`38title`, i.`39type`
                           FROM eg_item_charge ch
                           INNER JOIN eg_item_copies cp ON ch.`39accessnum` = cp.`39accessnum`
                           INNER JOIN eg_item i ON cp.eg_item_id = i.id
                           WHERE cp.eg_item_id IN ($idList)
                             AND (ch.`40dc` IS NULL OR ch.`40dc` != 'DC')
                           ORDER BY ch.id ASC";
            $res = mysqli_query($GLOBALS["conn"], $queryLoans);
            $loans = [];
            if ($res) {
                while ($row = mysqli_fetch_assoc($res)) {
                    $loans[] = $row;
                }
            }

            if (count($loans) === 0) {
                return [
                    'status' => 'NO_ACTIVE_LOANS',
                    'message' => 'This item is not currently charged out to any patron.',
                    'loans' => []
                ];
            }

            if (count($loans) === 1) {
                return [
                    'status' => 'MATCH',
                    'loans' => $loans,
                    'loan' => $loans[0]
                ];
            }

            // Multiple active loans found
            $patronList = [];
            foreach ($loans as $l) {
                $patronList[] = htmlspecialchars($l['39patron'] ?? '', ENT_QUOTES, 'UTF-8');
            }
            return [
                'status' => 'MULTIPLE',
                'message' => 'Multiple active loans found for this title (Patrons: <strong>' . implode(', ', array_unique($patronList)) . '</strong>). Please scan or enter the <strong>Patron ID / IC</strong> to specify which copy is being returned.',
                'loans' => $loans
            ];
        }

        return [
            'status' => 'ERROR',
            'message' => 'An unexpected error occurred while resolving loans.',
            'loans' => []
        ];
    }
