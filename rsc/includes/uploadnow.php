 <?php

    defined('includeExist') || die("<div class='auth-card'><span class='badge badge-danger mb-2'>WARNING</span><h2>Forbidden: Direct access prohibited</h2><em class='text-muted'>System Response Code</em></div>");

    if (is_uploaded_file($_FILES[$allowed_fieldname]['tmp_name'])) {
        move_uploaded_file($_FILES[$allowed_fieldname]['tmp_name'], $uploaddir.'/'.$idUpload.'_'.$timestampUpload.'.'.$extension);
    }
