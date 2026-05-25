var harta = null;
var markeri = [];

var culoriTip = {
    munte: "#27ae60",
    lac:   "#2980b9",
    mare:  "#1abc9c",
    oras:  "#e67e22"
};

function culoareCerc(tip) {
    return culoriTip[tip] || "#999";
}

function initHarta() {
    harta = L.map("harta-map").setView([45.9, 24.9], 7);

    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
        attribution: "&copy; OpenStreetMap contributors"
    }).addTo(harta);

    for (var i = 0; i < privelisti.length; i++) {
        adaugaMarker(privelisti[i], i);
    }

    var filtru = $("#filtru-tip");
    if (filtru.length) {
        filtru.change(function() {
            filtreaza(this.value);
        });
    }
}

function adaugaMarker(p, idx) {
    var cerc = L.circleMarker([p.lat, p.lng], {
        radius: 12,
        fillColor: culoareCerc(p.tip),
        color: "#fff",
        weight: 2,
        opacity: 1,
        fillOpacity: 0.9
    });

    var continutPopup =
        "<div style='font-family: Georgia, serif; min-width: 180px;'>" +
        "<img src='" + p.imagine + "' alt='" + p.titlu + "' style='width:100%; height:110px; object-fit:cover; border-radius:4px; margin-bottom:8px;'>" +
        "<strong style='font-size:1rem;'>" + p.titlu + "</strong><br>" +
        "<span style='font-size:0.85rem; color:#555;'>" + (judete[p.judet] || p.judet) + " &mdash; " + p.tip + "</span><br><br>" +
        "<span style='font-size:0.85rem;'>⛰ " + p.altitudine + " m &nbsp;|&nbsp; " + p.sezon + " &nbsp;|&nbsp; " + p.dificultate + "</span><br><br>" +
        "<span style='font-size:0.85rem; color:#333;'>" + p.descriere + "</span>" +
        "</div>";

    cerc.bindPopup(continutPopup, { maxWidth: 220 });

    cerc.on("mouseover", function() {
        this.setStyle({ radius: 16 });
    });

    cerc.on("mouseout", function() {
        if (!this.isPopupOpen()) {
            this.setStyle({ radius: 12 });
        }
    });

    cerc.addTo(harta);
    markeri.push({ marker: cerc, tip: p.tip });
}

function filtreaza(tip) {
    for (var i = 0; i < markeri.length; i++) {
        if (tip === "" || markeri[i].tip === tip) {
            markeri[i].marker.addTo(harta);
        } else {
            harta.removeLayer(markeri[i].marker);
        }
    }
}

$(function() {
    initHarta();
});
