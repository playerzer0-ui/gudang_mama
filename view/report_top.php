<?php
$reportMonthNames = [
    '01' => 'January', '02' => 'February', '03' => 'March', '04' => 'April',
    '05' => 'May', '06' => 'June', '07' => 'July', '08' => 'August',
    '09' => 'September', '10' => 'October', '11' => 'November', '12' => 'December',
];
?>
<main class="gm-report-page gm-report-<?php echo htmlspecialchars($reportKind, ENT_QUOTES, 'UTF-8'); ?>">
    <div class="gm-report-inner">
        <header class="gm-report-heading">
            <span class="gm-report-eyebrow">Reports</span>
            <h1><?php echo htmlspecialchars($reportTitle, ENT_QUOTES, 'UTF-8'); ?></h1>
            <p><?php echo htmlspecialchars($reportDescription, ENT_QUOTES, 'UTF-8'); ?></p>
        </header>

        <section class="gm-report-filters" aria-label="Report filters">
            <?php if ($reportKind === 'storage'): ?>
            <input type="hidden" id="userType" value="<?php echo (int) $userType; ?>">
            <?php endif; ?>
            <?php if ($reportKind !== 'piutang'): ?>
            <div class="gm-report-field gm-report-storage-field">
                <label for="storageCode">Storage</label>
                <select name="storageCode" id="storageCode">
                    <?php foreach (getAllStorages() as $storage): ?>
                    <option value="<?php echo htmlspecialchars((string) $storage['storageCode'], ENT_QUOTES, 'UTF-8'); ?>"<?php echo $storage['storageCode'] === 'NON' ? ' selected' : ''; ?>><?php echo htmlspecialchars((string) $storage['storageName'], ENT_QUOTES, 'UTF-8'); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
            <div class="gm-report-field">
                <label for="month">Month</label>
                <select id="month" name="month">
                    <?php foreach ($reportMonthNames as $monthValue => $monthName): ?>
                    <option value="<?php echo $monthValue; ?>"<?php echo (string) $monthValue === date('m') ? ' selected' : ''; ?>><?php echo $monthName; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="gm-report-field">
                <label for="year">Year</label>
                <select id="year" name="year"></select>
            </div>
            <button class="gm-report-view" type="button" onclick="generateReport()">View report</button>
        </section>

        <section class="gm-report-results is-empty" id="reportResults" aria-label="Report results">
            <div class="gm-report-results-bar">
                <div>
                    <h2><?php echo htmlspecialchars($reportResultTitle, ENT_QUOTES, 'UTF-8'); ?></h2>
                    <p id="reportStatus" role="status" aria-live="polite">Choose a period, then view the report.</p>
                </div>
                <div id="excel" class="gm-report-export-slot"></div>
            </div>
            <div class="table-container gm-report-table-scroll">
