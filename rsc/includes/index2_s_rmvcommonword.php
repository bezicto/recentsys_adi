<?php

defined('includeExist') || die("<div class='auth-card'><span class='badge badge-danger mb-2'>WARNING</span><h2>Forbidden: Direct access prohibited</h2><em class='text-muted'>System Response Code</em></div>");

//remove common words on works boolean and index searches
$scstr2 = str_replace("'", ' ', $scstr2);//remove '
$scstr2 = str_replace('"', ' ', $scstr2);//remove "
$scstr2 = str_replace('<', '', $scstr2);//remove <
$scstr2 = str_replace('>', ' ', $scstr2);//remove >
$scstr2 = str_replace('/', '', $scstr2);//remove /
$scstr2 = str_replace('(', '', $scstr2);//remove (
$scstr2 = str_replace(')', '', $scstr2);//remove )
$scstr2 = str_replace(';', '', $scstr2);//remove ;
$scstr2 = " ".$scstr2." ";//we put space at the beginning and end to allow the filter to also apply to words at the start and end
$remove = array(
            "and","as","at","an","a","about","all","after","also","among",
            "by","be","before","between","because",
            "could","can","cause",
            "delete","do","day","drop",
            "each","even","ever",
            "first","for","from",
            "go","get",
            "have","has","had","how",
            "i","if","in","if","is","its",
            "me","may","most","more",
            "no","nope","none","null",
            "or","out","over","on","of",
            "the","to","there","their","they","then","than",
            "up",
            "we","with","would","which","who","whom","whose","what","want","where",
            "yes","yet",
            "ada","apa","akan",
            "bagaimana","bentuk",
            "di","dan","dalam","dari","daripada","dapat",
            "pada",
            "ini","ia","itu",
            "kalangan","ke","kepada","kerana",
            "lagi",
            "mana","menerusi",
            "oleh",
            "perlu",
            "siapa","sana","sini","select",
            "tetapi",
            "untuk",
            "yang","ya"
            );
            
foreach ($remove as $word) {
    $scstr2 = preg_replace("/\s". $word ."\s/i", " ", $scstr2);// 'i' added on 17 August to remove all noise words above in a case insensitive manner. to disable, just remove 'i'.
}
$scstr2 = trim($scstr2);//trim to remove all whitespaces
$scstr2 = str_replace('\'s', '', $scstr2);//remove all single upper quotes with s
