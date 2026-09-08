<!DOCTYPE HTML>
<?php
    session_start();
    define('includeExist', true);
    include_once '../includes/access_logged.php';
    include_once '../config.php';
?>

<html lang='en'>

<head>
    <meta charset="utf-8">
    <title><?php echo htmlspecialchars($product_name, ENT_QUOTES, 'UTF-8');?> : Subject Heading Selector</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="../assets/styles/style.css" rel="stylesheet" type="text/css">
    <link href="../<?php echo htmlspecialchars($mini_icon_path, ENT_QUOTES, 'UTF-8');?>" rel="icon" type="image/png" />
    <script>
        function pick(symbol) {
            try {
                if (window.parent && window.parent !== window && window.parent.pickSubjectHeading) {
                    window.parent.pickSubjectHeading(symbol);
                    return;
                }
            } catch(e) {}
            try {
                if (window.opener && !window.opener.closed && window.opener.document && window.opener.document.someform) {
                    var shField = window.opener.document.someform.subjectheading || 
                                  window.opener.document.someform.subjectheading1 || 
                                  window.opener.document.someform.subjectheading3;
                    if (shField) {
                        if (shField.value === '') {
                            shField.value = symbol + '|';
                        } else {
                            shField.value = shField.value + symbol + '|';
                        }
                    }
                }
            } catch(e) {}
            window.close();
        }
    </script>
</head>

<body>
    <div class="container-narrow mt-3">
        <div class="card mb-3">
            <div class="card-header">
                <strong>Select Subject Heading</strong>
            </div>
            <div class="card-body p-0">
                <table class="table-modern m-0">
                <thead>
                    <tr>
                        <th style="width:100px;">Code</th>
                        <th>Subject Title</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                    $queryC = "select `43subjectid`, `43subject`, `43acronym` from eg_subjectheading order by `43acronym`";
                    $resultC = mysqli_query($GLOBALS["conn"], $queryC);
                    $has_sh = false;
                    while ($myrow=mysqli_fetch_array($resultC)) {
                        $has_sh = true;
                        $subjectC = $myrow["43subject"] ?? '';
                        $subjectAcr = $myrow["43acronym"] ?? '';
                        $safe_acr_attr = htmlspecialchars(json_encode((string)$subjectAcr), ENT_QUOTES, 'UTF-8');
                        $safe_acr_text = htmlspecialchars($subjectAcr, ENT_QUOTES, 'UTF-8');
                        $safe_sub_text = htmlspecialchars($subjectC, ENT_QUOTES, 'UTF-8');
                        echo "<tr class='table-row'>";
                        echo "<td><code>$safe_acr_text</code></td>";
                        echo "<td><a href='#' onclick=\"pick($safe_acr_attr); return false;\" class='font-bold'>$safe_sub_text</a></td>";
                        echo "</tr>";
                    }
                    if (!$has_sh) {
                        echo "<tr><td colspan='2' class='text-center text-muted p-3'>No subject headings found.</td></tr>";
                    }
                ?>
                </tbody>
                </table>
            </div>
        </div>
        
        <div class="text-center my-3">
            <button type="button" class="btn btn-secondary btn-sm" onclick="if(window.parent && window.parent.closeAppModal) { window.parent.closeAppModal(false); } else { window.close(); }">&times; Cancel &amp; Close</button>
        </div>
    </div>
</body>
</html>
