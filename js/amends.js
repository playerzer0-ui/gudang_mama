function setHref(button){
    document.getElementById("deleteButton").href = "../controller/index.php?action=deleteNo&no_id=" + button.value;
}