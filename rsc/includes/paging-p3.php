<?php
    
    defined('includeExist') || die("<div class='auth-card'><span class='badge badge-danger mb-2'>WARNING</span><h2>Forbidden: Direct access prohibited</h2><em class='text-muted'>System Response Code</em></div>");
    
    $urlappender = "";
    if (isset($_GET['scflag']) && ($_GET['scflag'] != '')) {$urlappender .= "&scflag=".$_GET['scflag'];}
    if (isset($_GET['sctype']) && ($_GET['sctype'] != '')) {$urlappender .= "&sctype=".$_GET['sctype'];}
    
    $scstr2 = urlencode($scstr2);//fix anything to do with boolean operators
    
    if ($maxPage > 1 && $num_results_affected > $rowsPerPage) {
        echo "<div class='pagination-bar'>";
        if ($pageNum > 1) {
            $page = $pageNum - 1;
            echo "<a class='pagination-link' href=\"$self?scstr=$scstr2$urlappender&page=1\">&laquo; First</a> ";
            echo "<a class='pagination-link' href=\"$self?scstr=$scstr2$urlappender&page=$page\">&lsaquo; Prev</a> ";
        }

        echo "<span class='pagination-current'>Page $pageNum of $maxPage</span>";

        if ($pageNum < $maxPage) {
            $page = $pageNum + 1;
            echo " <a class='pagination-link' href=\"$self?scstr=$scstr2$urlappender&page=$page\">Next &rsaquo;</a> ";
            echo " <a class='pagination-link' href=\"$self?scstr=$scstr2$urlappender&page=$maxPage\">Last &raquo;</a>";
        }
        echo "</div>";
    }
