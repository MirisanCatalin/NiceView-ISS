function initListeColapsabile() {
    var lista = $("#lista-colapsabila");
    if (!lista.length) return;

    var itemuri = lista.find("li.expandabil");
    itemuri.each(function() {
        var sublista = $(this).find("ul, ol");
        if (sublista.length) {
            sublista.hide();
            $(this).addClass("inchis");

            $(this).click(function(e) {
                e.stopPropagation();
                var sub = $(this).find("ul, ol");
                if (!sub.length) return;
                if (sub.is(":hidden")) {
                    sub.show();
                    $(this).removeClass("inchis");
                    $(this).addClass("deschis");
                } else {
                    sub.hide();
                    $(this).removeClass("deschis");
                    $(this).addClass("inchis");
                }
            });
        }
    });
}

$(function() {
    initListeColapsabile();
});
