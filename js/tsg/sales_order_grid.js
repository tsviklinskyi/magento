(function () {
    window.onload = function() {
        if(document.getElementsByClassName("reload-page-5").length > 0){
            setTimeout(function(){ location.reload(); }, 5000);
        }
    };
}());