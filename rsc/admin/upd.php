<?php
/**
 * Legacy upd.php forwarder
 *
 * Catalog record update functionality has been unified into reg.php.
 * This file forwards all requests to reg.php preserving query parameters.
 */
session_start();
define('includeExist', true);

$queryString = !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '';
header("Location: reg.php" . $queryString, true, 302);
exit;
