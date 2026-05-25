var directieSortare = {};

function construiesteTabel() {
    var container = $("#tabel-privelisti");
    if (!container.length) return;

    var tabel = $("<table>").attr("id", "tabel-sortabil");

    var thead = $("<thead>");
    var trHead = $("<tr>");
    var coloane = ["Titlu", "Judet", "Tip", "Altitudine", "Sezon", "Dificultate"];
    var chei = ["titlu", "judet", "tip", "altitudine", "sezon", "dificultate"];

    for (var i = 0; i < coloane.length; i++) {
        var th = $("<th>").text(coloane[i] + " ↕").addClass("sortabil").attr("data-cheie", chei[i]).click(sorteazaDupa);
        trHead.append(th);
    }

    thead.append(trHead);
    tabel.append(thead);

    var tbody = $("<tbody>").attr("id", "tabel-body");
    tabel.append(tbody);
    container.append(tabel);

    umpleTabel(privelisti);
}

function umpleTabel(date) {
    var tbody = $("#tabel-body");
    if (!tbody.length) return;
    tbody.empty();

    for (var i = 0; i < date.length; i++) {
        var p = date[i];
        var tr = $("<tr>");

        var celule = [p.titlu, judete[p.judet] || p.judet, p.tip, p.altitudine + " m", p.sezon, p.dificultate];
        for (var j = 0; j < celule.length; j++) {
            var td = $("<td>").text(celule[j]);
            tr.append(td);
        }
        tbody.append(tr);
    }
}

function sorteazaDupa() {
    var cheie = $(this).attr("data-cheie");
    var toate = $(".sortabil");
    toate.each(function() {
        var text = $(this).text().replace(" ↑", "").replace(" ↓", "").replace(" ↕", "");
        $(this).text(text + " ↕").removeClass("sortat");
    });

    directieSortare[cheie] = !directieSortare[cheie];
    var crescator = directieSortare[cheie];

    var copie = privelisti.slice();
    copie.sort(function(a, b) {
        var valA = a[cheie];
        var valB = b[cheie];
        if (typeof valA === "number" && typeof valB === "number") {
            return crescator ? valA - valB : valB - valA;
        }
        valA = String(valA).toLowerCase();
        valB = String(valB).toLowerCase();
        if (valA < valB) return crescator ? -1 : 1;
        if (valA > valB) return crescator ? 1 : -1;
        return 0;
    });

    var sageat = crescator ? " ↑" : " ↓";
    var coloaneNume = ["Titlu", "Judet", "Tip", "Altitudine", "Sezon", "Dificultate"];
    var chei = ["titlu", "judet", "tip", "altitudine", "sezon", "dificultate"];
    var idx = chei.indexOf(cheie);
    $(this).text(coloaneNume[idx] + sageat).addClass("sortat");

    umpleTabel(copie);
}

function construiesteTabelVertical() {
    var container = $("#tabel-vertical");
    if (!container.length) return;

    var tabel = $("<table>").attr("id", "tabel-sortabil-vertical");

    var coloane = ["Titlu", "Judet", "Tip", "Altitudine", "Sezon", "Dificultate"];
    var chei = ["titlu", "judet", "tip", "altitudine", "sezon", "dificultate"];

    for (var i = 0; i < coloane.length; i++) {
        var tr = $("<tr>");

        var th = $("<th>").text(coloane[i] + " ↕").addClass("sortabil-v").attr("data-cheie", chei[i]).attr("data-index", i).click(sorteazaVertical);
        tr.append(th);

        for (var j = 0; j < privelisti.length; j++) {
            var td = $("<td>");
            var val = privelisti[j][chei[i]];
            if (chei[i] === "judet") val = judete[val] || val;
            if (chei[i] === "altitudine") val = val + " m";
            td.text(val);
            tr.append(td);
        }

        tabel.append(tr);
    }

    container.append(tabel);
}

var directieSortareV = {};

function sorteazaVertical() {
    var cheie = $(this).attr("data-cheie");
    var toate = $(".sortabil-v");
    toate.each(function() {
        var text = $(this).text().replace(" ↑", "").replace(" ↓", "").replace(" ↕", "");
        $(this).text(text + " ↕").removeClass("sortat");
    });

    directieSortareV[cheie] = !directieSortareV[cheie];
    var crescator = directieSortareV[cheie];

    var indecsiSortati = privelisti.map(function(p, idx) { return idx; });
    indecsiSortati.sort(function(a, b) {
        var valA = privelisti[a][cheie];
        var valB = privelisti[b][cheie];
        if (typeof valA === "number" && typeof valB === "number") {
            return crescator ? valA - valB : valB - valA;
        }
        valA = String(valA).toLowerCase();
        valB = String(valB).toLowerCase();
        if (valA < valB) return crescator ? -1 : 1;
        if (valA > valB) return crescator ? 1 : -1;
        return 0;
    });

    var sageat = crescator ? " ↑" : " ↓";
    var coloaneNume = ["Titlu", "Judet", "Tip", "Altitudine", "Sezon", "Dificultate"];
    var chei2 = ["titlu", "judet", "tip", "altitudine", "sezon", "dificultate"];
    var idx = chei2.indexOf(cheie);
    $(this).text(coloaneNume[idx] + sageat).addClass("sortat");

    var randuri = $("#tabel-sortabil-vertical tr");
    randuri.each(function(r) {
        var celule = $(this).find("td");
        var cheieRand = chei2[r];
        for (var c = 0; c < indecsiSortati.length; c++) {
            var p = privelisti[indecsiSortati[c]];
            var val = p[cheieRand];
            if (cheieRand === "judet") val = judete[val] || val;
            if (cheieRand === "altitudine") val = val + " m";
            celule.eq(c).text(val);
        }
    });
}

$(function() {
    construiesteTabel();
    construiesteTabelVertical();
});
