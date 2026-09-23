<aside class="document-sidebar gm-invoice-sidebar" aria-labelledby="document-sidebar-title">
    <span class="gm-slip-eyebrow">Source document</span>
    <h2 id="document-sidebar-title">Select a slip</h2>
    <p class="document-sidebar-hint">Klik No. SJ untuk mengisi detail payment.</p>
    <label for="invoiceDocumentSearch">Search No. SJ</label>
    <input type="search" id="invoiceDocumentSearch" placeholder="Search document number" autocomplete="off">
    <p id="invoiceDocumentCount" class="gm-invoice-document-count" aria-live="polite"></p>
    <div class="document-sidebar-list">
        <?php foreach ($sidebarDocuments as $documentNumber): ?>
        <button type="button" class="document-sidebar-button" data-document-number="<?= htmlspecialchars($documentNumber, ENT_QUOTES, 'UTF-8') ?>" aria-pressed="false"><?= htmlspecialchars($documentNumber, ENT_QUOTES, 'UTF-8') ?></button>
        <?php endforeach; ?>
    </div>
    <p class="document-sidebar-empty" id="invoiceDocumentEmpty" hidden>No matching documents.</p>
</aside>
