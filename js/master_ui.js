(() => {
    const search = document.getElementById('masterSearch');
    if (!search) return;
    const rows = Array.from(document.querySelectorAll('#masterTable tbody tr'));
    const searchable = rows.map(row => Array.from(row.querySelectorAll('[data-master-value]')).map(cell => cell.textContent).join(' ').toLowerCase());
    const filter = () => {
        const query = search.value.trim().toLowerCase();
        let visible = 0;
        rows.forEach((row, index) => { row.hidden = !searchable[index].includes(query); if (!row.hidden) visible++; });
        document.getElementById('masterRecordCount').textContent = visible + ' of ' + rows.length + ' records';
        const empty = document.getElementById('masterEmpty');
        empty.hidden = visible > 0;
        empty.textContent = rows.length ? 'No matching records. Try a different search.' : 'No records yet. Use + Create new to begin.';
    };
    search.addEventListener('input', filter);
    filter();
})();
