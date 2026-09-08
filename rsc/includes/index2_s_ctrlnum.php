<?php

    defined('includeExist') || die("<div class='auth-card'><span class='badge badge-danger mb-2'>WARNING</span><h2>Forbidden: Direct access prohibited</h2><em class='text-muted'>System Response Code</em></div>");

    $offset_int = (int)$offset;
    $rowsPerPage_int = (int)$rowsPerPage;
    if (is_numeric($scstr2)) {
        $scstr2_int = (int)$scstr2;
        if ($scstr2 <> '') {
            $query1 = "select SQL_CALC_FOUND_ROWS * from eg_item where id=$scstr2_int LIMIT $offset_int, $rowsPerPage_int";
        } else {
            $query1 = "select SQL_CALC_FOUND_ROWS *  from eg_item LIMIT $offset_int, $rowsPerPage_int";
        }
    } else {
        echo "<script language=\"javascript\" type=\"text/javascript\">";
        echo "alert('The input you have typed is not numerical. Please retype.');";
        echo "</script>";
        $query1 = "select SQL_CALC_FOUND_ROWS *  from eg_item LIMIT $offset_int, $rowsPerPage_int";
    }
