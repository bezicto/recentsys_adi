<?php

    defined('includeExist') || die("<div class='auth-card'><span class='badge badge-danger mb-2'>WARNING</span><h2>Forbidden: Direct access prohibited</h2><em class='text-muted'>System Response Code</em></div>");

    $row = mysqli_fetch_row(mysqli_query($GLOBALS["conn"], "SELECT FOUND_ROWS()"));
    $num_results_affected = $row[0];
    $maxPage = ceil($num_results_affected/$rowsPerPage);
    $self = $_SERVER['PHP_SELF'];
