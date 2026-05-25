
function marcheazaEroare(camp) {
    $(camp).css({
        "border": "2px solid red",
        "background-color": "#fff0f0"
    });
}

function stergeEroare(camp) {
    $(camp).css({
        "border": "",
        "background-color": ""
    });
}

function stergeToateErorile(form) {
    $(form).find("input, textarea, select").each(function() {
        stergeEroare(this);
    });
}

function valideazaFormularPriveliste(e) {
    e.preventDefault();
    var $form = $("form[name='formular_priveliste']");
    stergeToateErorile($form);
    var valid = true;

    var $nume = $form.find("[name='nume']");
    if ($nume.val().trim() === "") {
        marcheazaEroare($nume);
        valid = false;
    }

    var $email = $form.find("[name='email']");
    var regexEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!regexEmail.test($email.val().trim())) {
        marcheazaEroare($email);
        valid = false;
    }

    var $titlu = $form.find("[name='titlu']");
    if ($titlu.val().trim().length < 3) {
        marcheazaEroare($titlu);
        valid = false;
    }

    var $altitudine = $form.find("[name='altitudine']");
    var tipSelectat = $form.find("[name='tip']").val();
    var maxAlt = altitudineMaxima[tipSelectat] || 3000;
    var altVal = Number($altitudine.val());
    if ($altitudine.val() === "" || isNaN(altVal) || altVal < 0 || altVal > maxAlt) {
        marcheazaEroare($altitudine);
        valid = false;
    }

    var $descriere = $form.find("[name='descriere']");
    if ($descriere.val().trim().length < 10) {
        marcheazaEroare($descriere);
        valid = false;
    }

    var $regulament = $form.find("[name='regulament']");
    if (!$regulament.prop("checked")) {
        marcheazaEroare($regulament);
        valid = false;
    }

    if (valid) {
        alert("Formularul a fost trimis cu succes!");
        $form[0].reset();
    }
}

function valideazaFormularLogin(e) {
    e.preventDefault();
    var $form = $("form[name='formular_login']");
    stergeToateErorile($form);
    var valid = true;

    var $username = $form.find("[name='username']");
    if ($username.val().trim().length < 3) {
        marcheazaEroare($username);
        valid = false;
    }

    var $parola = $form.find("[name='parola']");
    if ($parola.val().length < 6) {
        marcheazaEroare($parola);
        valid = false;
    }

    if (valid) {
        alert("Autentificare reusita!");
        $form[0].reset();
    }
}

function valideazaFormularContact(e) {
    e.preventDefault();
    var $form = $("form[name='formular_contact']");
    stergeToateErorile($form);
    var valid = true;

    var regexNumeComplet = /^[A-Z\u00C0-\u017Ea-z\u00E0-\u017E]{2,}(\s[A-Z\u00C0-\u017Ea-z\u00E0-\u017E]{2,})+$/;
    var $nume = $form.find("[name='nume']");
    if (!regexNumeComplet.test($nume.val().trim())) {
        marcheazaEroare($nume);
        valid = false;
    }

    var regexEmail = /^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$/;
    var $email = $form.find("[name='email']");
    if (!regexEmail.test($email.val().trim())) {
        marcheazaEroare($email);
        valid = false;
    }

    var regexTelefon = /^(\+40|0040|0)[0-9]{9}$/;
    var $telefon = $form.find("[name='telefon']");
    if ($telefon.val().trim() !== "" && !regexTelefon.test($telefon.val().trim())) {
        marcheazaEroare($telefon);
        valid = false;
    }

    var $subiect = $form.find("[name='subiect']");
    if ($subiect.val() === "") {
        marcheazaEroare($subiect);
        valid = false;
    }

    var $mesaj = $form.find("[name='mesaj']");
    if ($mesaj.val().trim().length < 10) {
        marcheazaEroare($mesaj);
        valid = false;
    }

    if (valid) {
        alert("Mesajul a fost trimis cu succes!");
        $form[0].reset();
    }
}

function initDependente() {
    var $selectJudet = $("#judet");
    var $selectLocalitate = $("#localitate");
    var $selectTip = $("#tip_select");
    var $inputAltitudine = $("#altitudine");

    if ($selectJudet.length && $selectLocalitate.length) {
        $selectJudet.change(function() {
            var codJudet = $(this).val();
            $selectLocalitate.html("<option value=''>-- Selectati localitatea --</option>");
            if (localitati[codJudet]) {
                for (var i = 0; i < localitati[codJudet].length; i++) {
                    var opt = $("<option></option>")
                        .val(localitati[codJudet][i])
                        .text(localitati[codJudet][i]);
                    $selectLocalitate.append(opt);
                }
            }
        });
    }

    if ($selectTip.length && $inputAltitudine.length) {
        $selectTip.change(function() {
            var tip = $(this).val();
            var maxAlt = altitudineMaxima[tip] || 3000;
            $inputAltitudine.attr("max", maxAlt);
            $inputAltitudine.attr("placeholder", "max " + maxAlt + " m");
        });
    }
}

$(function() {
    var $formPriveliste = $("form[name='formular_priveliste']");
    if ($formPriveliste.length) {
        $formPriveliste.submit(valideazaFormularPriveliste);
    }

    var $formLogin = $("form[name='formular_login']");
    if ($formLogin.length) {
        $formLogin.submit(valideazaFormularLogin);
    }

    var $formContact = $("form[name='formular_contact']");
    if ($formContact.length) {
        $formContact.submit(valideazaFormularContact);
    }

    initDependente();
});
