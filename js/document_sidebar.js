(() => {
    const sidebar = document.querySelector('.document-sidebar');
    const input = document.getElementById('no_sj');
    if (!sidebar || !input) return;

    const buttons = sidebar.querySelectorAll('[data-document-number]');
    const updateSelection = () => {
        buttons.forEach(button => {
            const selected = button.dataset.documentNumber === input.value;
            button.classList.toggle('is-selected', selected);
            button.setAttribute('aria-pressed', String(selected));
        });
    };

    sidebar.addEventListener('click', event => {
        const button = event.target.closest('[data-document-number]');
        if (!button) return;
        input.value = button.dataset.documentNumber;
        // Reuse the same header/product/invoice lookups as typing the number.
        input.dispatchEvent(new Event('input', { bubbles: true }));
    });
    input.addEventListener('input', updateSelection);
    updateSelection();
    const search = document.getElementById('invoiceDocumentSearch');
    if (search) {
        const filterDocuments = () => {
            const query = search.value.trim().toLowerCase();
            let visible = 0;
            buttons.forEach(button => {
                button.hidden = !button.dataset.documentNumber.toLowerCase().includes(query);
                if (!button.hidden) visible++;
            });
            document.getElementById('invoiceDocumentCount').textContent = visible + ' of ' + buttons.length + ' documents';
            const empty = document.getElementById('invoiceDocumentEmpty');
            empty.hidden = visible > 0;
            empty.textContent = buttons.length ? 'No matching documents.' : 'Belum ada dokumen tersedia.';
        };
        search.addEventListener('input', filterDocuments);
        filterDocuments();
    }
})();
