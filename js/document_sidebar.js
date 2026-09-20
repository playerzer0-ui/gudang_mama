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
})();
