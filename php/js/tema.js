function initTema() {
    var temaSalvata = localStorage.getItem("tema");
    if (temaSalvata === "dark") {
        $("body").addClass("dark");
    }

    var btn = $("#btn-tema");
    if (!btn.length) return;

    actualizeazaTextBtn(btn);

    btn.click(function() {
        $("body").toggleClass("dark");
        if ($("body").hasClass("dark")) {
            localStorage.setItem("tema", "dark");
        } else {
            localStorage.setItem("tema", "light");
        }
        actualizeazaTextBtn(btn);
    });
}

function actualizeazaTextBtn(btn) {
    if ($("body").hasClass("dark")) {
        btn.text("Tema deschisa");
    } else {
        btn.text("Tema închisa");
    }
}

$(function() {
    initTema();
});
