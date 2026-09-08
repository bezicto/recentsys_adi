<?php

    defined('includeExist') || die("<div class='auth-card'><span class='badge badge-danger mb-2'>WARNING</span><h2>Forbidden: Direct access prohibited</h2><em class='text-muted'>System Response Code</em></div>");
    
    $offset_int = (int)$offset;
    $rowsPerPage_int = (int)$rowsPerPage;
    if ($scstr2 <> '') {
        $scstr2_esc = mysqli_real_escape_string($GLOBALS["conn"], $scstr2);
        $query1 = "select SQL_CALC_FOUND_ROWS * from eg_item where `38author` like '%$scstr2_esc%' LIMIT $offset_int, $rowsPerPage_int";
    } else {
        $query1 = "select SQL_CALC_FOUND_ROWS * from eg_item LIMIT $offset_int, $rowsPerPage_int";
    }
