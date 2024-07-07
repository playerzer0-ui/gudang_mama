// JavaScript to populate the year options dynamically
const yearSelect = document.getElementById('year');
const currentYear = new Date().getFullYear();
const startYear = currentYear - 50; // 50 years back
const endYear = currentYear + 10; // 10 years ahead

for (let year = startYear; year <= endYear; year++) {
    const option = document.createElement('option');
    option.value = year;
    option.textContent = year;
    yearSelect.appendChild(option);
}