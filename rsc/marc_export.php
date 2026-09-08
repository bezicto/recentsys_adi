<?php
session_start();
define('includeExist', true);

include_once 'config.php';
include_once 'includes/functions.php';
include_once 'includes/marc_helper.php';

$id = isset($_GET['det']) && is_numeric($_GET['det']) ? (int)$_GET['det'] : 0;
if ($id <= 0) {
    http_response_code(400);
    die("Invalid record ID.");
}

$marc = get_marc_record($id);
if (!$marc) {
    http_response_code(404);
    die("Record not found.");
}

$format = strtolower(trim($_GET['format'] ?? 'mrc'));

// Generate safe filename slug based on title
$title_slug = preg_replace('/[^a-zA-Z0-9_-]/', '_', $marc['title']);
$title_slug = substr(trim($title_slug, '_'), 0, 40);
if (empty($title_slug)) {
    $title_slug = 'record';
}

if ($format === 'mrk' || $format === 'txt') {
    $content = marc_to_tagged_text($marc);
    $filename = "marc21_{$id}_{$title_slug}.mrk";
    header('Content-Description: File Transfer');
    header('Content-Type: text/plain; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . strlen($content));
    echo $content;
    exit;
} else {
    // Default: Binary ISO 2709 .mrc file
    $content = marc_to_iso2709($marc);
    $filename = "marc21_{$id}_{$title_slug}.mrc";
    header('Content-Description: File Transfer');
    header('Content-Type: application/marc');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . strlen($content));
    echo $content;
    exit;
}
