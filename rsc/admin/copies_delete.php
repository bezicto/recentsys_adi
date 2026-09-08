<?php
    session_start();define('includeExist', true);
    include_once '../includes/access_super.php';
    include_once '../config.php';
    include_once '../includes/token_validate.php';
?>
<!DOCTYPE HTML>
<html lang='en'>

<head>
    <meta charset="utf-8">
    <title><?php echo htmlspecialchars($product_name, ENT_QUOTES, 'UTF-8');?> : Delete Copy</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="../<?php echo htmlspecialchars($mini_icon_path, ENT_QUOTES, 'UTF-8');?>" rel="icon" type="image/png" />
    <link href="../assets/styles/style.css" rel="stylesheet" type="text/css">
    <script>
        window.onunload = refreshParent;
        function refreshParent() {
            try {
                if (window.opener && !window.opener.closed) {
                    window.opener.location.reload();
                }
            } catch(e) {}
            try {
                if (window.parent && window.parent !== window && window.parent.closeAppModal) {
                    window.parent.location.reload();
                }
            } catch(e) {}
        }
    </script>
</head>

<body>
    <div class="container-narrow mt-3">
        <?php
            if (isset($_POST["submitted"]) && $_POST["submitted"] == 'Delete' && (isset($_POST["id"]) && is_numeric($_POST["id"])) && $proceedAfterToken) {
                $id = (int)$_POST["id"];
                
                if ($id > 0) {
                    $stmt = mysqli_prepare($GLOBALS["conn"], "DELETE FROM eg_item_copies WHERE id=?");
                    mysqli_stmt_bind_param($stmt, "i", $id);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_close($stmt);
                    echo "<div class='alert alert-success'>The copy has been deleted from the database!</div>";
                } else {
                    echo "<div class='alert alert-danger'>Action cancelled.</div>";
                }
                echo "<script>setTimeout(function(){ if(window.parent && window.parent.closeAppModal) { window.parent.closeAppModal(true); } else { window.close(); } }, 600);</script>";
            }
            $target_copy_id = isset($_REQUEST["id"]) && is_numeric($_REQUEST["id"]) ? (int)$_REQUEST["id"] : 0;
        ?>

        <div class="card">
            <div class="card-header">
                <strong>Confirm Delete Copy</strong>
            </div>
            <div class="card-body text-center">
                <form action="copies_delete.php" method="post" enctype="multipart/form-data">
                    <p class="text-danger mb-3"><strong>Are you sure you want to permanently delete this copy?</strong></p>
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($_SESSION['token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" />
                    <input type="hidden" name="id" value="<?php echo $target_copy_id;?>" />
                    <div class="d-flex gap-2 justify-content-center">
                        <input type="submit" class="btn btn-danger" name="submitted" value="Delete" />
                        <input type="button" class="btn btn-secondary" value="Cancel and Close" onclick="if(window.parent && window.parent.closeAppModal) { window.parent.closeAppModal(false); } else { window.close(); }" />
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
