var indexCurent = 0;
var intervalCarousel = null;
var ruleaza = true;

function construiesteCarousel() {
    var container = $("#carousel-container");
    if (!container.length) return;

    var slide = $("<div>").attr("id", "carousel-slide");
    var img = $("<img>").attr("id", "carousel-img");
    var link = $("<a>").attr("id", "carousel-link");
    var text = $("<span>").attr("id", "carousel-text");

    link.append(text);

    var btnPrev = $("<button>").attr("id", "carousel-prev").text("<").click(function() {
        indexCurent = (indexCurent - 1 + carousel.length) % carousel.length;
        actualizeazaCarousel();
        resetInterval();
    });

    var btnNext = $("<button>").attr("id", "carousel-next").text(">").click(function() {
        indexCurent = (indexCurent + 1) % carousel.length;
        actualizeazaCarousel();
        resetInterval();
    });

    var btnPlayPause = $("<button>").attr("id", "carousel-playpause").text("Pause").click(function() {
        if (ruleaza) {
            clearInterval(intervalCarousel);
            btnPlayPause.text("Play");
        } else {
            pornesteInterval();
            btnPlayPause.text("Pause");
        }
        ruleaza = !ruleaza;
    });

    var indicatori = $("<div>").attr("id", "carousel-indicatori");

    for (var i = 0; i < carousel.length; i++) {
        var dot = $("<span>").addClass("carousel-dot").attr("data-index", i).click(function() {
            indexCurent = Number($(this).attr("data-index"));
            actualizeazaCarousel();
            resetInterval();
        });
        indicatori.append(dot);
    }

    slide.append(img).append(link);
    container.append(btnPrev, slide, btnNext, btnPlayPause, indicatori);

    actualizeazaCarousel();
    pornesteInterval();
}

// PORNIRE INTERVAL
function pornesteInterval() {
    intervalCarousel = setInterval(function() {
        indexCurent = (indexCurent + 1) % carousel.length;
        actualizeazaCarousel();
    }, 3000);
}

// RESET INTERVAL
function resetInterval() {
    if (ruleaza) {
        clearInterval(intervalCarousel);
        pornesteInterval();
    }
}

function actualizeazaCarousel() {
    var item = carousel[indexCurent];

    var img = $("#carousel-img");
    var link = $("#carousel-link");
    var text = $("#carousel-text");
    var dots = $(".carousel-dot");

    img.attr("src", item.imagine).attr("alt", item.text);
    link.attr("href", item.link);
    text.text(item.text);

    dots.removeClass("activ");
    if (dots.eq(indexCurent).length) {
        dots.eq(indexCurent).addClass("activ");
    }
}

$(function() {
    construiesteCarousel();
});
