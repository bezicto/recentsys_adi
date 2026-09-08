<?php

    defined('includeExist') || die("<div class='auth-card'><span class='badge badge-danger mb-2'>WARNING</span><h2>Forbidden: Direct access prohibited</h2><em class='text-muted'>System Response Code</em></div>");

    if ($_GET['scstr'] <> null) {
        $arrays_T = (explode(" ", $scstr2));
        $dotarrays_T = sizeof($arrays_T) - 1;
        
        if ($dotarrays_T <> 0) {
            echo "<div class='card mb-3 p-3 bg-card'>";
            echo "<div class='d-flex align-items-center flex-wrap gap-2'>";
            echo "<span class='font-bold small text-muted'>Exploded Search Terms:</span>";
            echo "<div class='keyword-cloud'>";
            for ($numbering_scheme_T=0;$numbering_scheme_T<=$dotarrays_T;$numbering_scheme_T++) {
                $currentarray = just_clean(stripslashes($arrays_T[$numbering_scheme_T]));
                $extra_params = "";
                if (isset($_GET['scflag']) && ($_GET['scflag'] != '')) {$extra_params .= "&scflag=".$_GET['scflag'];}
                if (isset($_GET['sctype']) && ($_GET['sctype'] != '')) {$extra_params .= "&sctype=".$_GET['sctype'];}
                echo "<a href='".$_SESSION['ref']."?scstr=".urlencode($currentarray)."$extra_params' class='keyword-tag'>$currentarray</a>";
            }
            echo "</div></div></div>";
        }
    }

