document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const clearSearch = document.getElementById('clearSearch');
    const amendItems = document.querySelectorAll('.amend-item');
    
    // Search functionality
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase().trim();
        
        amendItems.forEach(item => {
            const docNumber = item.getAttribute('data-doc-number').toLowerCase();
            
            if (docNumber.includes(searchTerm)) {
                item.style.display = 'flex';
            } else {
                item.style.display = 'none';
            }
        });
    });
    
    // Clear search functionality
    clearSearch.addEventListener('click', function() {
        searchInput.value = '';
        amendItems.forEach(item => {
            item.style.display = 'flex';
        });
        searchInput.focus();
    });
    
    // Add some styling for better UX
    searchInput.addEventListener('focus', function() {
        this.parentElement.classList.add('focus');
    });
    
    searchInput.addEventListener('blur', function() {
        this.parentElement.classList.remove('focus');
    });
});

function setHref(button){
    let state = document.getElementById("state").value;
    document.getElementById("deleteButton").href = "../controller/index.php?action=amendDelete&no_id=" + button.value + "&state=" + state;
}