<?php
defined('includeExist') || die("Forbidden");

$help_topics = [
    'index.php' => ['title' => 'Overview & Portal', 'icon' => 'fa-gauge-high', 'role' => 'ALL'],
    'cataloging.php' => ['title' => 'Cataloging & MARC21', 'icon' => 'fa-book', 'role' => 'ALL'],
    'circulation.php' => ['title' => 'Circulation & Desk', 'icon' => 'fa-arrows-rotate', 'role' => 'ALL'],
    'fines_calendar.php' => ['title' => 'Fines & Calendar', 'icon' => 'fa-calendar-days', 'role' => 'ALL'],
    'serials.php' => ['title' => 'Serials & Kardex', 'icon' => 'fa-newspaper', 'role' => 'ALL'],
    'users_security.php' => ['title' => 'Users & Security', 'icon' => 'fa-shield-halved', 'role' => 'SUPER'],
    'reports_analytics.php' => ['title' => 'Reports & Analytics', 'icon' => 'fa-chart-pie', 'role' => 'SUPER'],
    'system_config.php' => ['title' => 'System & Policies', 'icon' => 'fa-sliders', 'role' => 'SUPER'],
    'quick_reference.php' => ['title' => 'Quick Reference', 'icon' => 'fa-bolt', 'role' => 'ALL'],
];

$active_file = basename($_SERVER['PHP_SELF'] ?? 'index.php');
?>
<style>
    .help-header-hero {
        background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
        color: #f8fafc;
        border-radius: var(--radius-md, 8px);
        padding: 1.75rem 2rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        position: relative;
        overflow: hidden;
    }
    .help-header-hero::after {
        content: "\f02d";
        font-family: "Font Awesome 6 Free";
        font-weight: 900;
        position: absolute;
        right: -15px;
        bottom: -25px;
        font-size: 8rem;
        color: rgba(255, 255, 255, 0.04);
        pointer-events: none;
    }
    .help-nav-pills {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        background: #ffffff;
        padding: 0.75rem;
        border-radius: var(--radius-md, 8px);
        border: 1px solid var(--border-color, #e2e8f0);
        margin-bottom: 1.5rem;
    }
    .help-nav-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 0.85rem;
        border-radius: 6px;
        font-size: 0.875rem;
        font-weight: 600;
        text-decoration: none;
        color: #475569;
        transition: all 0.15s ease-in-out;
        border: 1px solid transparent;
    }
    .help-nav-pill:hover {
        background: #f1f5f9;
        color: #0f172a;
        text-decoration: none;
    }
    .help-nav-pill.active {
        background: #2563eb;
        color: #ffffff;
        border-color: #1d4ed8;
        box-shadow: 0 2px 4px rgba(37, 99, 235, 0.2);
    }
    .help-nav-pill .badge-pill-role {
        font-size: 0.65rem;
        padding: 2px 5px;
        border-radius: 4px;
        font-weight: 700;
        text-transform: uppercase;
    }
    .help-nav-pill.active .badge-pill-role {
        background: rgba(255, 255, 255, 0.25);
        color: #ffffff;
    }
    .help-nav-pill:not(.active) .badge-pill-role-super {
        background: #fee2e2;
        color: #b91c1c;
    }
    .help-nav-pill:not(.active) .badge-pill-role-all {
        background: #e0f2fe;
        color: #0369a1;
    }
    .guide-section {
        background: #ffffff;
        border: 1px solid var(--border-color, #e2e8f0);
        border-radius: var(--radius-md, 8px);
        padding: 1.5rem 1.75rem;
        margin-bottom: 1.5rem;
    }
    .guide-section-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.65rem;
        border-bottom: 2px solid #f1f5f9;
        padding-bottom: 0.75rem;
    }
    .guide-section-title i {
        color: #2563eb;
    }
    .guide-callout {
        padding: 1rem 1.25rem;
        border-radius: 6px;
        margin: 1rem 0;
        border-left: 4px solid;
    }
    .guide-callout-info {
        background: #eff6ff;
        border-color: #3b82f6;
        color: #1e40af;
    }
    .guide-callout-warning {
        background: #fffbeb;
        border-color: #f59e0b;
        color: #92400e;
    }
    .guide-callout-success {
        background: #f0fdf4;
        border-color: #22c55e;
        color: #166534;
    }
    .guide-callout-danger {
        background: #fef2f2;
        border-color: #ef4444;
        color: #991b1b;
    }
    .workflow-step {
        display: flex;
        gap: 1.25rem;
        margin-bottom: 1.25rem;
        position: relative;
    }
    .step-number {
        flex-shrink: 0;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: #2563eb;
        color: #ffffff;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        box-shadow: 0 2px 5px rgba(37, 99, 235, 0.3);
    }
    .step-content {
        flex-grow: 1;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        padding: 1rem 1.25rem;
    }
    .step-content h5 {
        margin: 0 0 0.5rem 0;
        font-size: 1rem;
        font-weight: 700;
        color: #1e293b;
    }
    .table-guide {
        width: 100%;
        border-collapse: collapse;
        margin: 1rem 0;
        font-size: 0.9rem;
    }
    .table-guide th {
        background: #f1f5f9;
        color: #334155;
        padding: 0.65rem 0.85rem;
        text-align: left;
        border: 1px solid #e2e8f0;
        font-weight: 700;
    }
    .table-guide td {
        padding: 0.65rem 0.85rem;
        border: 1px solid #e2e8f0;
        vertical-align: top;
    }
    .table-guide tr:nth-child(even) td {
        background: #fafafa;
    }
    .ui-badge {
        font-family: inherit;
        background: #e2e8f0;
        color: #0f172a;
        padding: 3px 8px;
        border-radius: 4px;
        font-size: 0.85em;
        font-weight: 600;
        display: inline-block;
    }
    .quick-card-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.25rem;
        margin-bottom: 1.5rem;
    }
    .quick-card {
        background: #ffffff;
        border: 1px solid var(--border-color, #e2e8f0);
        border-radius: var(--radius-md, 8px);
        padding: 1.25rem;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
        text-decoration: none;
        color: inherit;
        display: flex;
        flex-direction: column;
    }
    .quick-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.06);
        text-decoration: none;
        border-color: #cbd5e1;
    }
    .quick-card-header {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 0.75rem;
    }
    .quick-card-icon {
        width: 42px;
        height: 42px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        color: #ffffff;
        flex-shrink: 0;
    }
    .quick-card-title {
        font-size: 1.05rem;
        font-weight: 700;
        color: #0f172a;
        margin: 0;
    }
    .quick-card-desc {
        color: #64748b;
        font-size: 0.875rem;
        line-height: 1.45;
        flex-grow: 1;
        margin-bottom: 0.75rem;
    }
    .quick-card-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.75rem;
        font-weight: 600;
        padding-top: 0.5rem;
        border-top: 1px solid #f1f5f9;
    }
</style>

<div class="help-header-hero">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="badge badge-primary px-2 py-1"><i class="fa-solid fa-book-bookmark me-1"></i> Admin User Manual</span>
                <span class="badge badge-secondary px-2 py-1"><?= htmlspecialchars($core_product ?? $product_name);?> <?= htmlspecialchars($version_num ?? 'Undetected Version');?></span>
            </div>
            <h1 style="font-size: 1.75rem; font-weight: 800; margin-bottom: 0.35rem; color: #ffffff;">Library Administration &amp; Staff Operations Manual</h1>
            <p style="color: #cbd5e1; margin-bottom: 0; font-size: 0.95rem;">Step-by-step user guide for library staff and administrators: cataloging, circulation desk, fines, serials, member accounts, and reports.</p>
        </div>
    </div>
</div>

<nav class="help-nav-pills" aria-label="Admin Guide Navigation">
    <?php foreach ($help_topics as $file => $info): ?>
        <?php $isActive = ($active_file === $file); ?>
        <a href="<?= $file;?>" class="help-nav-pill <?= $isActive ? 'active' : '';?>">
            <i class="fa-solid <?= $info['icon'];?>"></i>
            <span><?= $info['title'];?></span>
            <span class="badge-pill-role <?= $info['role'] === 'SUPER' ? 'badge-pill-role-super' : 'badge-pill-role-all';?>">
                <?= $info['role'];?>
            </span>
        </a>
    <?php endforeach; ?>
</nav>
