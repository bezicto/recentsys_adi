<?php
/**
 * MARC21 Bibliographic Helper for RC8 Library Management System
 * 
 * Provides extraction, mapping, visual rendering, MarcEdit tagged text,
 * and ISO 2709 binary export capabilities.
 */

if (!defined('includeExist')) {
    define('includeExist', true);
}

/**
 * Fetch and construct a standardized MARC21 record array from database
 *
 * @param int $id The catalog item ID (eg_item.id)
 * @return array|null Returns structured MARC record array or null if not found
 */
function get_marc_record($id)
{
    $id = (int)$id;
    if ($id <= 0) {
        return null;
    }

    // 1. Fetch primary catalog row
    $stmt = mysqli_prepare($GLOBALS["conn"], "SELECT * FROM eg_item WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $res1 = mysqli_stmt_get_result($stmt);
    $row1 = mysqli_fetch_assoc($res1);
    mysqli_stmt_close($stmt);

    if (!$row1) {
        return null;
    }

    // 2. Fetch indicators
    $row_ind = [];
    $stmt_ind = mysqli_prepare($GLOBALS["conn"], "SELECT * FROM eg_item_indicator WHERE eg_item_id = ?");
    mysqli_stmt_bind_param($stmt_ind, "i", $id);
    mysqli_stmt_execute($stmt_ind);
    $res_ind = mysqli_stmt_get_result($stmt_ind);
    if ($res_ind && $rind = mysqli_fetch_assoc($res_ind)) {
        $row_ind = $rind;
    }
    mysqli_stmt_close($stmt_ind);

    // 3. Fetch additional ISBNs
    $extra_isbns = [];
    $stmt_isbn = mysqli_prepare($GLOBALS["conn"], "SELECT * FROM eg_item_isbn WHERE eg_item_id = ?");
    mysqli_stmt_bind_param($stmt_isbn, "i", $id);
    mysqli_stmt_execute($stmt_isbn);
    $res_isbn = mysqli_stmt_get_result($stmt_isbn);
    if ($res_isbn && $risbn = mysqli_fetch_assoc($res_isbn)) {
        for ($i = 1; $i <= 20; $i++) {
            $val = trim($risbn["isbn$i"] ?? ($risbn["isbn3_$i"] ?? ''));
            if (!empty($val)) {
                $extra_isbns[] = str_replace(['-', '–', '—'], '', $val);
            }
        }
    }
    mysqli_stmt_close($stmt_isbn);

    // 4. Fetch copies / holdings
    $copies = [];
    $stmt_copies = mysqli_prepare($GLOBALS["conn"], "SELECT `39accessnum`, `39status`, `39volume`, `39issue`, `39year`, `39is_reference`, `39invoice_a`, `39invoice_b`, `39invoice_c` FROM eg_item_copies WHERE eg_item_id = ? ORDER BY id ASC");
    mysqli_stmt_bind_param($stmt_copies, "i", $id);
    mysqli_stmt_execute($stmt_copies);
    $res_copies = mysqli_stmt_get_result($stmt_copies);
    while ($res_copies && $rcopy = mysqli_fetch_assoc($res_copies)) {
        $copies[] = $rcopy;
    }
    mysqli_stmt_close($stmt_copies);

    // Extract values
    $title = trim($row1['38title'] ?? '');
    $title_b = trim($row1['38title_b'] ?? '');
    $title_c = trim($row1['38title_c'] ?? '');

    $author = trim($row1['38author'] ?? '');
    $author_d = trim($row1['38author_d'] ?? '');

    $isbn = str_replace(['-', '–', '—'], '', trim($row1['38isbn'] ?? ''));
    $issn = str_replace(['-', '–', '—'], '', trim($row1['38issn'] ?? ''));

    $callnum = trim($row1['38localcallnum'] ?? '');
    $callnum_b = trim($row1['38localcallnum_b'] ?? '');

    $edition = trim($row1['38edition'] ?? '');

    $pub_place = trim($row1['38publication'] ?? '');
    $pub_name = trim($row1['38publication_b'] ?? '');
    $pub_date = trim($row1['38publication_c'] ?? '');

    $phys_extent = trim($row1['38physicaldesc'] ?? '');
    $phys_details = trim($row1['38physicaldesc_b'] ?? '');
    $phys_dim = trim($row1['38physicaldesc_c'] ?? '');
    $phys_accomp = trim($row1['38physicaldesc_e'] ?? '');

    $series = trim($row1['38series'] ?? '');
    $series_v = trim($row1['38series_v'] ?? '');

    $notes = trim($row1['38notes'] ?? '');
    $fcnotes = trim($row1['38fcnotes'] ?? '');

    $source = trim($row1['38source'] ?? '');
    $source_b = trim($row1['38source_b'] ?? '');
    $source_e = trim($row1['38source_e'] ?? '');

    $location = trim($row1['38location'] ?? '');
    $location_b = trim($row1['38location_b'] ?? '');
    $location_c = trim($row1['38location_c'] ?? '');

    $link = trim($row1['38link'] ?? '');
    if (!empty($link)) {
        $link = urldecode($link);
    }

    $subjectheading = trim($row1['39subjectheading'] ?? '');
    $language = trim($row1['39language'] ?? 'zsm');
    if (empty($language)) {
        $language = 'zsm';
    }

    $inputdate = trim($row1['40inputdate'] ?? '');
    $instimestamp = trim($row1['40instimestamp'] ?? '');

    // Extract year from publication date or input date for 008
    $pub_year = '2026';
    if (preg_match('/\b(19\d{2}|20\d{2})\b/', $pub_date, $matches)) {
        $pub_year = $matches[1];
    } elseif (preg_match('/\b(19\d{2}|20\d{2})\b/', $inputdate, $matches)) {
        $pub_year = $matches[1];
    }

    // Build 005 Date and Time stamp
    $timestamp_005 = date('YmdHis.0');
    if (!empty($instimestamp) && is_numeric($instimestamp)) {
        $timestamp_005 = date('YmdHis.0', (int)$instimestamp);
    }

    // Build 008 string (40 characters)
    $date_entered = date('ymd');
    if (!empty($inputdate) && preg_match('/(\d{2})\/(\d{2})\/(\d{4})/', $inputdate, $dparts)) {
        $date_entered = substr($dparts[3], 2, 2) . $dparts[2] . $dparts[1];
    }
    $lang_code = str_pad(substr($language, 0, 3), 3, ' ');
    $field_008 = sprintf("%-6s%s%s%s%-3s%s", $date_entered, 's', $pub_year, '    xx ||||| |||| 00| 0 ', $lang_code, ' d');
    if (strlen($field_008) < 40) {
        $field_008 = str_pad($field_008, 40, ' ');
    } else {
        $field_008 = substr($field_008, 0, 40);
    }

    // Indicators helper
    $get_ind = function($raw, $default1 = ' ', $default2 = ' ') {
        $raw = trim((string)$raw);
        $char1 = (strlen($raw) > 0 && !in_array($raw[0], ['*', '-', '\\', '_'])) ? $raw[0] : $default1;
        $char2 = (strlen($raw) > 1 && !in_array($raw[1], ['*', '-', '\\', '_'])) ? $raw[1] : $default2;
        $i1 = strlen($char1) > 0 ? $char1[0] : ' ';
        $i2 = strlen($char2) > 0 ? $char2[0] : ' ';
        return [$i1, $i2];
    };

    $fields = [];

    // 001 Control Number
    $fields[] = [
        'tag' => '001',
        'is_control' => true,
        'value' => sprintf('%08d', $id),
        'label' => 'Control Number'
    ];

    // 005 Date and Time of Latest Transaction
    $fields[] = [
        'tag' => '005',
        'is_control' => true,
        'value' => $timestamp_005,
        'label' => 'Date & Time of Transaction'
    ];

    // 008 Fixed-Length Data Elements
    $fields[] = [
        'tag' => '008',
        'is_control' => true,
        'value' => $field_008,
        'label' => 'Fixed-Length Data Elements'
    ];

    // 020 ISBN
    if (!empty($isbn)) {
        $fields[] = [
            'tag' => '020',
            'ind1' => ' ',
            'ind2' => ' ',
            'label' => 'International Standard Book Number (ISBN)',
            'subfields' => [
                ['code' => 'a', 'value' => $isbn]
            ]
        ];
    }
    foreach ($extra_isbns as $e_isbn) {
        if (!empty($e_isbn)) {
            $fields[] = [
                'tag' => '020',
                'ind1' => ' ',
                'ind2' => ' ',
                'label' => 'Additional ISBN',
                'subfields' => [
                    ['code' => 'a', 'value' => $e_isbn]
                ]
            ];
        }
    }

    // 022 ISSN
    if (!empty($issn)) {
        $fields[] = [
            'tag' => '022',
            'ind1' => ' ',
            'ind2' => ' ',
            'label' => 'International Standard Serial Number (ISSN)',
            'subfields' => [
                ['code' => 'a', 'value' => $issn]
            ]
        ];
    }

    // 041 Language
    if (!empty($language)) {
        $fields[] = [
            'tag' => '041',
            'ind1' => '0',
            'ind2' => ' ',
            'label' => 'Language Code',
            'subfields' => [
                ['code' => 'a', 'value' => $language]
            ]
        ];
    }

    // 090 Local Call Number
    if (!empty($callnum) || !empty($callnum_b)) {
        $sub090 = [];
        if (!empty($callnum)) $sub090[] = ['code' => 'a', 'value' => $callnum];
        if (!empty($callnum_b)) $sub090[] = ['code' => 'b', 'value' => $callnum_b];
        $fields[] = [
            'tag' => '090',
            'ind1' => ' ',
            'ind2' => ' ',
            'label' => 'Local Call Number',
            'subfields' => $sub090
        ];
    }

    // 100 Main Entry - Personal Name (Author)
    if (!empty($author)) {
        [$i1, $i2] = $get_ind($row_ind['38author_i'] ?? $row_ind['38author_in'] ?? '', '1', ' ');
        $sub100 = [['code' => 'a', 'value' => $author]];
        if (!empty($author_d)) {
            $sub100[] = ['code' => 'd', 'value' => $author_d];
        }
        $fields[] = [
            'tag' => '100',
            'ind1' => $i1,
            'ind2' => $i2,
            'label' => 'Main Entry - Personal Name',
            'subfields' => $sub100
        ];
    }

    // 245 Title Statement
    if (!empty($title)) {
        [$i1, $i2] = $get_ind($row_ind['38title_i'] ?? $row_ind['38title_in'] ?? '', (!empty($author) ? '1' : '0'), '0');
        $sub245 = [['code' => 'a', 'value' => $title]];
        if (!empty($title_b)) $sub245[] = ['code' => 'b', 'value' => $title_b];
        if (!empty($title_c)) $sub245[] = ['code' => 'c', 'value' => $title_c];
        $fields[] = [
            'tag' => '245',
            'ind1' => $i1,
            'ind2' => $i2,
            'label' => 'Title Statement',
            'subfields' => $sub245
        ];
    }

    // 250 Edition Statement
    if (!empty($edition)) {
        $fields[] = [
            'tag' => '250',
            'ind1' => ' ',
            'ind2' => ' ',
            'label' => 'Edition Statement',
            'subfields' => [
                ['code' => 'a', 'value' => $edition]
            ]
        ];
    }

    // 264 Publication, Distribution, Manufacture
    if (!empty($pub_place) || !empty($pub_name) || !empty($pub_date)) {
        $sub264 = [];
        if (!empty($pub_place)) $sub264[] = ['code' => 'a', 'value' => $pub_place];
        if (!empty($pub_name)) $sub264[] = ['code' => 'b', 'value' => $pub_name];
        if (!empty($pub_date)) $sub264[] = ['code' => 'c', 'value' => $pub_date];
        $fields[] = [
            'tag' => '264',
            'ind1' => ' ',
            'ind2' => '1',
            'label' => 'Publication, Distribution, etc. (Imprint)',
            'subfields' => $sub264
        ];
    }

    // 300 Physical Description
    if (!empty($phys_extent) || !empty($phys_details) || !empty($phys_dim) || !empty($phys_accomp)) {
        $sub300 = [];
        if (!empty($phys_extent)) $sub300[] = ['code' => 'a', 'value' => $phys_extent];
        if (!empty($phys_details)) $sub300[] = ['code' => 'b', 'value' => $phys_details];
        if (!empty($phys_dim)) $sub300[] = ['code' => 'c', 'value' => $phys_dim];
        if (!empty($phys_accomp)) $sub300[] = ['code' => 'e', 'value' => $phys_accomp];
        $fields[] = [
            'tag' => '300',
            'ind1' => ' ',
            'ind2' => ' ',
            'label' => 'Physical Description',
            'subfields' => $sub300
        ];
    }

    // 490 Series Statement
    if (!empty($series)) {
        $sub490 = [['code' => 'a', 'value' => $series]];
        if (!empty($series_v)) $sub490[] = ['code' => 'v', 'value' => $series_v];
        $fields[] = [
            'tag' => '490',
            'ind1' => '0',
            'ind2' => ' ',
            'label' => 'Series Statement',
            'subfields' => $sub490
        ];
    }

    // 500 General Note
    if (!empty($notes)) {
        $fields[] = [
            'tag' => '500',
            'ind1' => ' ',
            'ind2' => ' ',
            'label' => 'General Note',
            'subfields' => [
                ['code' => 'a', 'value' => $notes]
            ]
        ];
    }

    // 505 Formatted Contents Note
    if (!empty($fcnotes)) {
        [$i1, $i2] = $get_ind($row_ind['38fcnotes_i'] ?? $row_ind['38fcnotes_in'] ?? '', '0', ' ');
        $fields[] = [
            'tag' => '505',
            'ind1' => $i1,
            'ind2' => $i2,
            'label' => 'Formatted Contents Note',
            'subfields' => [
                ['code' => 'a', 'value' => $fcnotes]
            ]
        ];
    }

    // 650 Subject Added Entry
    if (!empty($subjectheading)) {
        $sh_parts = explode('|', $subjectheading);
        foreach ($sh_parts as $shp) {
            $shp = trim($shp);
            if (!empty($shp)) {
                $sh_full = function_exists('subjectFromAcronym') ? (subjectFromAcronym($shp) ?? $shp) : $shp;
                $fields[] = [
                    'tag' => '650',
                    'ind1' => ' ',
                    'ind2' => '4',
                    'label' => 'Subject Added Entry - Topical Term',
                    'subfields' => [
                        ['code' => 'a', 'value' => $sh_full]
                    ]
                ];
            }
        }
    }

    // 710 Added Entry - Corporate Name
    if (!empty($source)) {
        [$i1, $i2] = $get_ind($row_ind['38source_i'] ?? $row_ind['38source_in'] ?? '', '2', ' ');
        $sub710 = [['code' => 'a', 'value' => $source]];
        if (!empty($source_b)) $sub710[] = ['code' => 'b', 'value' => $source_b];
        if (!empty($source_e)) $sub710[] = ['code' => 'e', 'value' => $source_e];
        $fields[] = [
            'tag' => '710',
            'ind1' => $i1,
            'ind2' => $i2,
            'label' => 'Added Entry - Corporate Name',
            'subfields' => $sub710
        ];
    }

    // 852 Location
    if (!empty($location) || !empty($location_b) || !empty($location_c)) {
        $sub852 = [];
        if (!empty($location)) $sub852[] = ['code' => 'a', 'value' => $location];
        if (!empty($location_b)) $sub852[] = ['code' => 'b', 'value' => $location_b];
        if (!empty($location_c)) $sub852[] = ['code' => 'c', 'value' => $location_c];
        $fields[] = [
            'tag' => '852',
            'ind1' => ' ',
            'ind2' => ' ',
            'label' => 'Location / Shelf Placement',
            'subfields' => $sub852
        ];
    }

    // 856 Electronic Location and Access
    if (!empty($link)) {
        $fields[] = [
            'tag' => '856',
            'ind1' => '4',
            'ind2' => '0',
            'label' => 'Electronic Location and Access (URL)',
            'subfields' => [
                ['code' => 'u', 'value' => $link]
            ]
        ];
    }

    // 949 Local Holdings (Accession / Copy barcodes & issue enumeration)
    foreach ($copies as $copy) {
        $sub949 = [];
        if (!empty($copy['39accessnum'])) $sub949[] = ['code' => 'a', 'value' => $copy['39accessnum']];
        if (!empty($copy['39volume'])) $sub949[] = ['code' => 'v', 'value' => $copy['39volume']];
        if (!empty($copy['39issue'])) $sub949[] = ['code' => 'n', 'value' => $copy['39issue']];
        if (!empty($copy['39year'])) $sub949[] = ['code' => 'y', 'value' => $copy['39year']];
        if (!empty($copy['39status'])) $sub949[] = ['code' => 's', 'value' => $copy['39status']];
        if (!empty($copy['39is_reference']) && $copy['39is_reference'] === 'YES') $sub949[] = ['code' => 'r', 'value' => 'Reference Only'];
        if (!empty($copy['39invoice_a'])) $sub949[] = ['code' => 'i', 'value' => $copy['39invoice_a']];
        if (!empty($sub949)) {
            $fields[] = [
                'tag' => '949',
                'ind1' => ' ',
                'ind2' => ' ',
                'label' => 'Local Copy / Accession Number',
                'subfields' => $sub949
            ];
        }
    }

    $leader = '00000nam a2200000 i 4500';

    return [
        'id' => $id,
        'title' => $title,
        'leader' => $leader,
        'fields' => $fields,
        'material_type' => $row1['39type'] ?? ''
    ];
}

/**
 * Convert structured MARC record to standard MarcEdit tagged text format (.mrk)
 *
 * @param array $marc Structured MARC record
 * @return string Formatted MarcEdit text
 */
function marc_to_tagged_text($marc)
{
    if (!$marc || !isset($marc['fields'])) {
        return '';
    }

    $lines = [];
    $lines[] = '=LDR  ' . ($marc['leader'] ?? '00000nam a2200000 i 4500');

    foreach ($marc['fields'] as $f) {
        $tag = $f['tag'];
        if (!empty($f['is_control'])) {
            $lines[] = "=$tag  " . $f['value'];
        } else {
            $ind1 = ($f['ind1'] === ' ' || $f['ind1'] === '' || $f['ind1'] === null) ? '\\' : $f['ind1'];
            $ind2 = ($f['ind2'] === ' ' || $f['ind2'] === '' || $f['ind2'] === null) ? '\\' : $f['ind2'];
            $sub_str = '';
            if (!empty($f['subfields'])) {
                foreach ($f['subfields'] as $sub) {
                    $sub_str .= '$' . $sub['code'] . $sub['value'];
                }
            }
            $lines[] = "=$tag  $ind1$ind2$sub_str";
        }
    }

    return implode("\r\n", $lines) . "\r\n";
}

/**
 * Convert structured MARC record into standard binary MARC21 stream (ISO 2709 standard)
 *
 * @param array $marc Structured MARC record
 * @return string Binary ISO 2709 MARC21 file content
 */
function marc_to_iso2709($marc)
{
    if (!$marc || !isset($marc['fields'])) {
        return '';
    }

    $SUB_DELIM = "\x1F"; // Subfield Delimiter (ASCII 31)
    $FIELD_TERM = "\x1E"; // Field Terminator (ASCII 30)
    $REC_TERM = "\x1D";  // Record Terminator (ASCII 29)

    $field_data_stream = '';
    $directory_entries = [];

    $current_offset = 0;

    foreach ($marc['fields'] as $f) {
        $tag = sprintf('%03s', $f['tag']);
        if (!empty($f['is_control'])) {
            $fdata = (string)$f['value'] . $FIELD_TERM;
        } else {
            $ind1 = (isset($f['ind1']) && strlen($f['ind1']) > 0 && $f['ind1'] !== '\\') ? $f['ind1'][0] : ' ';
            $ind2 = (isset($f['ind2']) && strlen($f['ind2']) > 0 && $f['ind2'] !== '\\') ? $f['ind2'][0] : ' ';
            $sub_str = '';
            if (!empty($f['subfields'])) {
                foreach ($f['subfields'] as $sub) {
                    $sub_str .= $SUB_DELIM . $sub['code'] . $sub['value'];
                }
            }
            $fdata = $ind1 . $ind2 . $sub_str . $FIELD_TERM;
        }

        $flen = strlen($fdata);
        $directory_entries[] = sprintf('%03s%04d%05d', $tag, $flen, $current_offset);
        $field_data_stream .= $fdata;
        $current_offset += $flen;
    }

    $directory_str = implode('', $directory_entries) . $FIELD_TERM;
    $leader_len = 24;
    $base_address = $leader_len + strlen($directory_str);
    $total_rec_len = $base_address + strlen($field_data_stream) + 1; // +1 for REC_TERM

    // Form 24-byte leader
    // Bytes 00-04: Total record length
    // Bytes 05: Record status ('n')
    // Bytes 06: Type of record ('a')
    // Bytes 07: Bibliographic level ('m')
    // Bytes 08: Type of control (' ')
    // Bytes 09: Character coding scheme ('a' for UTF-8)
    // Bytes 10: Indicator count ('2')
    // Bytes 11: Subfield code count ('2')
    // Bytes 12-16: Base address of data
    // Bytes 17: Encoding level (' ')
    // Bytes 18: Descriptive cataloging form ('i')
    // Bytes 19: Multipart resource record level (' ')
    // Bytes 20: Length of length-of-field ('4')
    // Bytes 21: Length of starting-character-position ('5')
    // Bytes 22: Length of implementation-defined portion ('0')
    // Bytes 23: Undefined ('0')
    $leader = sprintf('%05d', $total_rec_len) . 'nam a22' . sprintf('%05d', $base_address) . ' i 4500';

    return $leader . $directory_str . $field_data_stream . $REC_TERM;
}
