<?php

    defined('includeExist') || die("<div class='auth-card'><span class='badge badge-danger mb-2'>WARNING</span><h2>Forbidden: Direct access prohibited</h2><em class='text-muted'>System Response Code</em></div>");

    $rowsPerPage = 20;
    $pageNum = 1;
    if (isset($_GET['page'])) {
        $pageNum = $_GET['page'];
    }
    $offset = ($pageNum - 1) * $rowsPerPage;
