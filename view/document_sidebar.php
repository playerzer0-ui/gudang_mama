<aside class="document-sidebar" aria-labelledby="document-sidebar-title">
    <h2 id="document-sidebar-title">No. SJ</h2>
    <p class="document-sidebar-hint">Klik nomor untuk mengisi detail.</p>
    <div class="document-sidebar-list">
        <?php foreach ($sidebarDocuments as $documentNumber): ?>
            <button type="button" class="document-sidebar-button"
                data-document-number="<?= htmlspecialchars($documentNumber, ENT_QUOTES, 'UTF-8') ?>"
                aria-pressed="false"><?= htmlspecialchars($documentNumber, ENT_QUOTES, 'UTF-8') ?></button>
        <?php endforeach; ?>
        <?php if (!$sidebarDocuments): ?>
            <p class="document-sidebar-empty">Belum ada dokumen tersedia.</p>
        <?php endif; ?>
    </div>
</aside>
