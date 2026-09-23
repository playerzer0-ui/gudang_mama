/* Presentation shared by Storage, Hutang and Piutang reports. */
window.gmReportUI = (() => {
    const page = document.querySelector('.gm-report-page');
    const results = document.getElementById('reportResults');
    const status = document.getElementById('reportStatus');
    const table = document.getElementById('reporttable');
    const button = page.querySelector('.gm-report-view');
    let period = '';
    function selected(id) {
        const field = document.getElementById(id);
        return field ? field.selectedOptions[0]?.textContent || '' : '';
    }
    function busy(value) {
        results.setAttribute('aria-busy', String(value));
        button.disabled = value;
        button.textContent = value ? 'Loading…' : 'View report';
        page.querySelectorAll('.gm-report-filters select').forEach(field => field.disabled = value);
    }
    function layout() {
        if (!page.classList.contains('gm-report-storage') || !table.tHead) return;
        let left = 0;
        Array.from(table.tHead.rows[0].cells).slice(0, 3).forEach((cell, index) => {
            table.querySelectorAll('.gm-pin-' + index).forEach(item => item.style.left = left + 'px');
            left += cell.getBoundingClientRect().width;
        });
    }
    function decorate() {
        if (page.classList.contains('gm-report-storage')) {
            [table.tHead.rows[0], ...table.tBodies[0].rows].forEach(row => {
                Array.from(row.cells).slice(0, 3).forEach((cell, index) => cell.classList.add('gm-pinned', 'gm-pin-' + index));
            });
            Array.from(table.tBodies[0].rows).forEach(row => {
                Array.from(row.cells).slice(3).forEach(cell => cell.classList.add('gm-number'));
            });
        } else {
            // Account for merged invoice cells when assigning numeric column styling.
            const spans = [];
            Array.from(table.tBodies[0].rows).forEach(row => {
                let column = 0;
                Array.from(row.cells).forEach(cell => {
                    while (spans[column] > 0) column++;
                    if (cell.colSpan === 1 && [5,6,7,8,9,10,12,13].includes(column)) cell.classList.add('gm-number');
                    for (let i = 0; i < cell.colSpan; i++) spans[column + i] = cell.rowSpan;
                    column += cell.colSpan;
                });
                for (let i = 0; i < spans.length; i++) spans[i] = Math.max(0, (spans[i] || 0) - 1);
            });
        }
        requestAnimationFrame(layout);
    }
    if (window.ResizeObserver) new ResizeObserver(layout).observe(table);
    window.addEventListener('resize', layout);
    return {
        start() {
            period = [selected('storageCode'), selected('month') + ' ' + selected('year')].filter(Boolean).join(' · ');
            busy(true);
            status.textContent = 'Loading report…';
            results.classList.add('is-empty');
            results.classList.remove('has-error');
            document.getElementById('excel').replaceChildren();
        },
        finish(count) {
            busy(false);
            results.classList.toggle('is-empty', count === 0);
            status.textContent = period + ' · ' + (count ? count + (page.classList.contains('gm-report-storage') ? ' materials' : ' invoices') : 'No records found.');
            decorate();
        },
        error() {
            busy(false);
            results.classList.add('is-empty', 'has-error');
            status.textContent = 'Unable to load the report. Please try again.';
            document.getElementById('excel').replaceChildren();
        },
        export(url) {
            const link = document.createElement('a');
            link.href = url;
            link.target = '_blank';
            link.rel = 'noopener';
            link.className = 'gm-report-export';
            link.textContent = 'Export Excel';
            document.getElementById('excel').replaceChildren(link);
        }
    };
})();
