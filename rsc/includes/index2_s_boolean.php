<?php

    defined('includeExist') || die("<div class='auth-card'><span class='badge badge-danger mb-2'>WARNING</span><h2>Forbidden: Direct access prohibited</h2><em class='text-muted'>System Response Code</em></div>");
    
    $offset_int = (int)$offset;
    $rowsPerPage_int = (int)$rowsPerPage;
    if ($sctype2 == 'All Type') {
        if ($scstr2 == '') {
            $query1 = "select SQL_CALC_FOUND_ROWS * from eg_item order by id desc LIMIT $offset_int, $rowsPerPage_int";
        } else {
            include 'index2_s_rmvcommonword.php';
            $scstr2_esc = mysqli_real_escape_string($GLOBALS["conn"], $scstr2);
            $query1 = "select SQL_CALC_FOUND_ROWS *, match (`38title`,`38author`,`50search_cloud`) against ('$scstr2_esc' in boolean mode) as score from eg_item where `38title` <> '' and";
            $query1 .= " match (`38title`,`38author`,`50search_cloud`) against ('$scstr2_esc' in boolean mode)";
            $query1 .= " order by score desc LIMIT $offset_int, $rowsPerPage_int";
        }
    } elseif (isset($_SESSION['username']) && ($sctype2 == 'Control Number')) {
        include_once 'index2_s_ctrlnum.php';
    } elseif ($sctype2 == 'Author') {
        include_once 'index2_s_author.php';
    } else {
        $sctype2_esc = mysqli_real_escape_string($GLOBALS["conn"], $sctype2);
        if ($scstr2 == '') {
            $query1 = "select SQL_CALC_FOUND_ROWS * from eg_item where `39type` = '$sctype2_esc' order by id desc LIMIT $offset_int, $rowsPerPage_int";
        } else {
            include 'index2_s_rmvcommonword.php';
            $scstr2_esc = mysqli_real_escape_string($GLOBALS["conn"], $scstr2);
            $query1 = "select SQL_CALC_FOUND_ROWS *, match (`38title`,`38author`,`50search_cloud`) against ('$scstr2_esc' in boolean mode) as score from eg_item where `39type` = '$sctype2_esc' and";
            $query1 .= " match (`38title`,`38author`,`50search_cloud`) against ('$scstr2_esc' in boolean mode)";
            $query1 .= " order by score desc LIMIT $offset_int, $rowsPerPage_int";
        }
    }
