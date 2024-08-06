function setHref(button){
    let state = document.getElementById("state").value;
    document.getElementById("deleteButton").href = "../controller/index.php?action=amendDelete&no_id=" + button.value + "&state=" + state;
}