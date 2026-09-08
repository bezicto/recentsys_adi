<?php
session_start();
define('includeExist', true);

include_once 'config.php';
include_once 'includes/functions.php';
include_once 'includes/marc_helper.php';

$id = isset($_GET['det']) && is_numeric($_GET['det']) ? (int)$_GET['det'] : 0;
$marc = get_marc_record($id);

if (!$marc) {
    echo "<!DOCTYPE HTML><html lang='en'><head><link href='./assets/styles/style.css' rel='stylesheet' type='text/css'></head><body><div class='alert alert-danger m-3'>Error: MARC Record #$id not found.</div></body></html>";
    exit;
}

$raw_marc = marc_to_tagged_text($marc);
$material_type_name = function_exists('idToType') ? idToType($marc['material_type']) : $marc['material_type'];
?>
<!DOCTYPE HTML>
<html lang='en'>

<head>
    <meta charset="utf-8">
    <title><?php echo htmlspecialchars($product_name, ENT_QUOTES, 'UTF-8');?> : MARC21 Record #<?php echo $id; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="<?php echo htmlspecialchars($mini_icon_path, ENT_QUOTES, 'UTF-8');?>" rel="icon" type="image/png" />
    <link href="./assets/styles/style.css?v=3" rel="stylesheet" type="text/css">
    <link href="./assets/styles/marc_view_styles.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="./assets/fontawesome/css/all.min.css">
</head>

<body>
    <div class="marc-view-wrapper">
        <!-- Header & Action Bar -->
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div>
                <h3 class="m-0 font-bold" style="font-size: 1.15rem;">
                    <i class="fa-solid fa-file-code text-primary me-2"></i>MARC21 Bibliographic Record #<?php echo $id; ?>
                </h3>
                <div class="text-muted font-size-sm mt-1">
                    <?php echo htmlspecialchars($marc['title'], ENT_QUOTES, 'UTF-8'); ?>
                    <?php if (!empty($material_type_name)): ?>
                        &bull; <span class="badge badge-info"><?php echo htmlspecialchars($material_type_name, ENT_QUOTES, 'UTF-8'); ?></span>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="d-flex flex-wrap gap-2">
                <a href="marc_export.php?det=<?php echo $id; ?>&format=mrc" class="btn btn-primary btn-sm" title="Download standard binary MARC21 (.mrc) file">
                    <i class="fa-solid fa-download me-1"></i> Export .MRC
                </a>
                <a href="marc_export.php?det=<?php echo $id; ?>&format=mrk" class="btn btn-secondary btn-sm" title="Download MarcEdit tagged text (.mrk) file">
                    <i class="fa-solid fa-file-lines me-1"></i> Export .MRK
                </a>
                <button type="button" id="btnCopyMarc" class="btn btn-secondary btn-sm" onclick="copyMarcText()" title="Copy MarcEdit formatted text to clipboard">
                    <i class="fa-regular fa-copy me-1"></i> <span id="copyBtnLabel">Copy</span>
                </button>
            </div>
        </div>

        <!-- View Tabs -->
        <div class="d-flex gap-1" style="border-bottom: 1px solid var(--border-color); margin-bottom: -1px; position: relative; z-index: 2;">
            <button type="button" class="tab-btn active" onclick="switchTab('table', this)">
                <i class="fa-solid fa-table-list me-1"></i> Tagged Display
            </button>
            <button type="button" class="tab-btn" onclick="switchTab('raw', this)">
                <i class="fa-solid fa-code me-1"></i> MarcEdit / Raw Text
            </button>
        </div>

        <!-- Tab 1: Tagged Table View -->
        <div id="tab-table" class="tab-content active">
            <div class="card p-0" style="border-top-left-radius: 0;">
                <div class="card-body p-0">
                    <table class="table-modern m-0">
                        <thead>
                            <tr>
                                <th style="width: 70px;" class="text-center">Tag</th>
                                <th style="width: 60px;" class="text-center">Ind</th>
                                <th>Field Content / Subfields</th>
                                <th style="width: 220px;" class="d-none d-md-table-cell">MARC Field Definition</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Leader -->
                            <tr class="table-row">
                                <td class="text-center font-bold text-primary"><code>LDR</code></td>
                                <td class="text-center text-muted">--</td>
                                <td><code><?php echo htmlspecialchars($marc['leader'], ENT_QUOTES, 'UTF-8'); ?></code></td>
                                <td class="text-muted font-size-sm d-none d-md-table-cell">Record Leader</td>
                            </tr>
                            
                            <?php foreach ($marc['fields'] as $f): ?>
                                <tr class="table-row">
                                    <td class="text-center font-bold text-primary">
                                        <code><?php echo htmlspecialchars($f['tag'], ENT_QUOTES, 'UTF-8'); ?></code>
                                    </td>
                                    
                                    <?php if (!empty($f['is_control'])): ?>
                                        <td class="text-center text-muted">--</td>
                                        <td>
                                            <code><?php echo htmlspecialchars($f['value'], ENT_QUOTES, 'UTF-8'); ?></code>
                                        </td>
                                    <?php else: ?>
                                        <td class="text-center font-bold" style="font-family: monospace;">
                                            <?php
                                                $i1 = ($f['ind1'] === ' ' || $f['ind1'] === '' || $f['ind1'] === null) ? '_' : $f['ind1'];
                                                $i2 = ($f['ind2'] === ' ' || $f['ind2'] === '' || $f['ind2'] === null) ? '_' : $f['ind2'];
                                                echo htmlspecialchars("$i1 $i2", ENT_QUOTES, 'UTF-8');
                                            ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($f['subfields'])): ?>
                                                <div class="d-flex flex-wrap align-items-center gap-1">
                                                    <?php foreach ($f['subfields'] as $sub): ?>
                                                        <span class="d-inline-flex align-items-center me-2 my-1">
                                                            <span class="marc-subfield-pill">$<?php echo htmlspecialchars($sub['code'], ENT_QUOTES, 'UTF-8'); ?></span>
                                                            <span class="marc-subfield-val"><?php echo htmlspecialchars($sub['value'], ENT_QUOTES, 'UTF-8'); ?></span>
                                                        </span>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                    <?php endif; ?>
                                    
                                    <td class="text-muted font-size-sm d-none d-md-table-cell">
                                        <?php echo htmlspecialchars($f['label'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Tab 2: Raw MarcEdit Text View -->
        <div id="tab-raw" class="tab-content">
            <div class="card p-0" style="border-top-left-radius: 0;">
                <div class="card-body p-0">
                    <pre class="marc-raw-container m-0" id="rawMarcContent"><?php echo htmlspecialchars($raw_marc, ENT_QUOTES, 'UTF-8'); ?></pre>
                </div>
            </div>
        </div>

        <!-- Modal Footer Actions -->
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="text-muted font-size-sm">
                Standard ISO 2709 / MARC21 Bibliographic Structure
            </div>
            <div>
                <button type="button" class="btn btn-secondary btn-sm" onclick="if(window.parent && window.parent.closeAppModal) { window.parent.closeAppModal(false); } else { window.close(); }">
                    Close
                </button>
            </div>
        </div>
    </div>

    <script>
        function switchTab(tabId, btn) {
            document.querySelectorAll('.tab-btn').forEach(function(b) { b.classList.remove('active'); });
            document.querySelectorAll('.tab-content').forEach(function(c) { c.classList.remove('active'); });
            
            btn.classList.add('active');
            var target = document.getElementById('tab-' + tabId);
            if (target) {
                target.classList.add('active');
            }
        }

        function copyMarcText() {
            var rawText = document.getElementById('rawMarcContent').textContent;
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(rawText).then(onCopySuccess).catch(fallbackCopy);
            } else {
                fallbackCopy();
            }
        }

        function fallbackCopy() {
            var rawText = document.getElementById('rawMarcContent').textContent;
            var textArea = document.createElement('textarea');
            textArea.value = rawText;
            textArea.style.position = 'fixed';
            textArea.style.left = '-999999px';
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            try {
                document.execCommand('copy');
                onCopySuccess();
            } catch (err) {
                alert('Could not copy automatically. Please select text manually.');
            }
            document.body.removeChild(textArea);
        }

        function onCopySuccess() {
            var btn = document.getElementById('btnCopyMarc');
            var label = document.getElementById('copyBtnLabel');
            var origHtml = btn.innerHTML;
            btn.classList.remove('btn-secondary');
            btn.classList.add('btn-success');
            btn.innerHTML = '<i class="fa-solid fa-check me-1"></i> Copied!';
            setTimeout(function() {
                btn.classList.remove('btn-success');
                btn.classList.add('btn-secondary');
                btn.innerHTML = origHtml;
            }, 2000);
        }
    </script>
</body>
</html>
