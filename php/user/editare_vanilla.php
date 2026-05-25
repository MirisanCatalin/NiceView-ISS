<?php
$page_title = 'Editare Vanilla JS AJAX';
require_once dirname(__DIR__) . '/includes/header.php';

checkRememberMe();
redirectIfNotLoggedIn();
?>

<h2>Cerința 5: Editare cu Vanilla JS AJAX (plain JavaScript)</h2>
<p>
    Selectați o priveliște din listă pentru a-i edita detaliile.
    La schimbarea selecției, câmpurile se actualizează automat prin AJAX.
    Butonul <strong>Save</strong> se activează doar după modificarea datelor.
    Dacă încercați să schimbați selecția fără a salva, veți fi avertizat.
</p>

<div id="error-container" style="display:none; color: #c0392b; background: #fdecea; padding: 15px; border-radius: 4px; border-left: 5px solid #c0392b; margin-bottom: 20px;"></div>
<div id="success-container" style="display:none; color: #27ae60; background: #eafaf1; padding: 15px; border-radius: 4px; border-left: 5px solid #27ae60; margin-bottom: 20px;"></div>

<div id="loading-indicator" style="display:none; text-align:center; padding:20px; font-weight:bold; color:#3498db;">
    Se încarcă datele...
</div>

<form id="edit-form" style="max-width: 600px;">
    <fieldset>
        <legend><strong>Selectare priveliște</strong></legend>

        <p>
            <label for="record-id">Priveliște (ID):</label>
            <select id="record-id" name="id" style="padding: 8px; width: 100%; border-radius: 4px; border: 1px solid #ccc;">
                <option value="">-- Selectați o priveliște --</option>
            </select>
        </p>
    </fieldset>

    <fieldset>
        <legend><strong>Detalii priveliște</strong></legend>

        <p>
            <label for="titlu">Titlu:</label>
            <input type="text" id="titlu" name="titlu" maxlength="100" style="width: 100%; padding: 8px; border-radius: 4px; border: 1px solid #ccc;">
        </p>

        <p>
            <label for="judet">Județ:</label>
            <select id="judet" name="judet" style="padding: 8px; width: 100%; border-radius: 4px; border: 1px solid #ccc;">
                <option value="">-- Selectați județul --</option>
            </select>
        </p>

        <p>
            <label for="localitate">Localitate:</label>
            <input type="text" id="localitate" name="localitate" style="width: 100%; padding: 8px; border-radius: 4px; border: 1px solid #ccc;">
        </p>

        <p>
            <label for="tip">Tip:</label>
            <select id="tip" name="tip" style="padding: 8px; width: 100%; border-radius: 4px; border: 1px solid #ccc;">
                <option value="munte">Munte</option>
                <option value="mare">Mare</option>
                <option value="lac">Lac</option>
                <option value="oras">Oraș</option>
            </select>
        </p>

        <p>
            <label for="altitudine">Altitudine (m):</label>
            <input type="number" id="altitudine" name="altitudine" min="0" max="3000" style="width: 100%; padding: 8px; border-radius: 4px; border: 1px solid #ccc;">
        </p>

        <p>
            <label for="lat">Latitudine:</label>
            <input type="number" id="lat" name="lat" step="0.000001" min="-90" max="90" style="width: 100%; padding: 8px; border-radius: 4px; border: 1px solid #ccc;">
        </p>

        <p>
            <label for="lng">Longitudine:</label>
            <input type="number" id="lng" name="lng" step="0.000001" min="-180" max="180" style="width: 100%; padding: 8px; border-radius: 4px; border: 1px solid #ccc;">
        </p>

        <p>
            <label for="descriere">Descriere:</label>
            <textarea id="descriere" name="descriere" cols="40" rows="4" style="width: 100%; padding: 8px; border-radius: 4px; border: 1px solid #ccc;"></textarea>
        </p>

        <p>
            <label for="website">Website:</label>
            <input type="text" id="website" name="website" placeholder="https://..." style="width: 100%; padding: 8px; border-radius: 4px; border: 1px solid #ccc;">
        </p>

        <p>
            <label>Autor:</label>
            <span id="author-display" style="font-weight: bold; color: #2c3e50;"></span>
        </p>
    </fieldset>

    <div class="form-buttons" style="margin-top: 20px;">
        <button type="button" id="btn-save" disabled style="padding: 10px 25px; background: #27ae60; color: #fff; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;">Save</button>
        <button type="button" id="btn-reset" style="padding: 10px 25px; background: #e67e22; color: #fff; border: none; border-radius: 4px; cursor: pointer;">Resetează</button>
    </div>
</form>

<script>
(function() {
    'use strict';

    // Elemente DOM
    var selectId = document.getElementById('record-id');
    var btnSave = document.getElementById('btn-save');
    var btnReset = document.getElementById('btn-reset');
    var errorContainer = document.getElementById('error-container');
    var successContainer = document.getElementById('success-container');
    var loadingIndicator = document.getElementById('loading-indicator');
    var authorDisplay = document.getElementById('author-display');

    // Campuri formular
    var fieldTitlu      = document.getElementById('titlu');
    var fieldJudet      = document.getElementById('judet');
    var fieldLocalitate = document.getElementById('localitate');
    var fieldTip        = document.getElementById('tip');
    var fieldAltitudine = document.getElementById('altitudine');
    var fieldLat        = document.getElementById('lat');
    var fieldLng        = document.getElementById('lng');
    var fieldDescriere  = document.getElementById('descriere');
    var fieldWebsite    = document.getElementById('website');

    // Stare
    var originalData = {};   // Datele originale (la incarcare)
    var isDirty = false;     // Exista modificari nesalvate?
    var currentRecordId = null;
    var isLoadingRecord = false; // Flag pentru a preveni warning-ul in timpul incarcarii

    // Lista judetelor
    var judeteList = ['AB','AR','AG','BC','BH','BN','BT','BV','BR','BZ','CS','CL','CJ','CT','CV','DB','DJ','GL','GR','GJ','HR','HD','IL','IS','IF','MM','MH','MS','NT','OT','PH','SM','SJ','SB','SV','TR','TM','TL','VS','VL','VN','B'];

    function showError(message) {
        errorContainer.textContent = '⚠️ ' + message;
        errorContainer.style.display = 'block';
        successContainer.style.display = 'none';
    }

    function hideError() {
        errorContainer.style.display = 'none';
    }

    function showSuccess(message) {
        successContainer.textContent = '✓ ' + message;
        successContainer.style.display = 'block';
        errorContainer.style.display = 'none';
    }

    function hideSuccess() {
        successContainer.style.display = 'none';
    }

    function showLoading() {
        loadingIndicator.style.display = 'block';
    }

    function hideLoading() {
        loadingIndicator.style.display = 'none';
    }

    // Populeaza dropdown-ul de judete
    function populateJudete() {
        fieldJudet.innerHTML = '<option value="">-- Selectați județul --</option>';
        for (var i = 0; i < judeteList.length; i++) {
            var option = document.createElement('option');
            option.value = judeteList[i];
            option.textContent = judeteList[i];
            fieldJudet.appendChild(option);
        }
    }

    // Incarca lista de ID-uri
    function loadIds() {
        showLoading();

        var xhr = new XMLHttpRequest();
        xhr.open('GET', '../api/crud.php?action=get_all_ids', true);
        xhr.setRequestHeader('Accept', 'application/json');
        xhr.timeout = 10000;

        xhr.onload = function() {
            hideLoading();

            if (xhr.status === 200) {
                try {
                    var response = JSON.parse(xhr.responseText);
                    if (response.error) {
                        showError(response.error);
                        return;
                    }

                    selectId.innerHTML = '<option value="">-- Selectați o priveliște --</option>';
                    var data = response.data;
                    for (var i = 0; i < data.length; i++) {
                        var option = document.createElement('option');
                        option.value = data[i].id;
                        option.textContent = data[i].id + ' - ' + data[i].titlu;
                        selectId.appendChild(option);
                    }
                } catch (e) {
                    showError('Eroare la parsarea raspunsului: ' + e.message);
                }
            } else {
                showError('Eroare la incarcarea listei de ID-uri (HTTP ' + xhr.status + ').');
            }
        };

        xhr.onerror = function() {
            hideLoading();
            showError('Eroare de retea. Verificati conexiunea la Internet.');
        };

        xhr.ontimeout = function() {
            hideLoading();
            showError('Timeout. Serverul nu raspunde.');
        };

        xhr.send();
    }

    // Incarca datele unei inregistrari
    function loadRecord(id) {
        hideError();
        hideSuccess();
        showLoading();

        var xhr = new XMLHttpRequest();
        xhr.open('GET', '../api/crud.php?action=get_record&id=' + id, true);
        xhr.setRequestHeader('Accept', 'application/json');
        xhr.timeout = 10000;

        xhr.onload = function() {
            hideLoading();

            if (xhr.status === 200) {
                try {
                    var response = JSON.parse(xhr.responseText);
                    if (response.error) {
                        showError(response.error);
                        clearForm();
                        return;
                    }

                    var data = response.data;

                    // Populeaza campurile
                    fieldTitlu.value      = data.titlu || '';
                    fieldJudet.value      = data.judet || '';
                    fieldLocalitate.value = data.localitate || '';
                    fieldTip.value        = data.tip || 'munte';
                    fieldAltitudine.value = data.altitudine || '';
                    fieldLat.value        = data.lat || '';
                    fieldLng.value        = data.lng || '';
                    fieldDescriere.value  = data.descriere || '';
                    fieldWebsite.value    = data.website || '';
                    authorDisplay.textContent = data.username || '';

                    // Salveaza datele originale pentru comparatie
                    originalData = {
                        titlu:      fieldTitlu.value,
                        judet:      fieldJudet.value,
                        localitate: fieldLocalitate.value,
                        tip:        fieldTip.value,
                        altitudine: fieldAltitudine.value,
                        lat:        fieldLat.value,
                        lng:        fieldLng.value,
                        descriere:  fieldDescriere.value,
                        website:    fieldWebsite.value
                    };

                    currentRecordId = id;
                    isDirty = false;
                    btnSave.disabled = true;

                } catch (e) {
                    showError('Eroare la parsarea raspunsului: ' + e.message);
                }
            } else if (xhr.status === 404) {
                showError('Inregistrarea nu a fost gasita.');
                clearForm();
            } else {
                showError('Eroare server (HTTP ' + xhr.status + ').');
            }
        };

        xhr.onerror = function() {
            hideLoading();
            showError('Eroare de retea. Verificati conexiunea la Internet.');
        };

        xhr.ontimeout = function() {
            hideLoading();
            showError('Timeout. Serverul nu raspunde.');
        };

        xhr.send();
    }

    function clearForm() {
        fieldTitlu.value      = '';
        fieldJudet.value      = '';
        fieldLocalitate.value = '';
        fieldTip.value        = 'munte';
        fieldAltitudine.value = '';
        fieldLat.value        = '';
        fieldLng.value        = '';
        fieldDescriere.value  = '';
        fieldWebsite.value    = '';
        authorDisplay.textContent = '';
        originalData = {};
        currentRecordId = null;
        isDirty = false;
        btnSave.disabled = true;
    }

    // Verifica daca exista modificari
    function checkDirty() {
        if (!currentRecordId) {
            isDirty = false;
            btnSave.disabled = true;
            return;
        }

        isDirty = (
            fieldTitlu.value      !== originalData.titlu ||
            fieldJudet.value      !== originalData.judet ||
            fieldLocalitate.value !== originalData.localitate ||
            fieldTip.value        !== originalData.tip ||
            fieldAltitudine.value !== originalData.altitudine ||
            fieldLat.value        !== originalData.lat ||
            fieldLng.value        !== originalData.lng ||
            fieldDescriere.value  !== originalData.descriere ||
            fieldWebsite.value    !== originalData.website
        );

        btnSave.disabled = !isDirty;
    }

    // Salveaza modificarile
    function saveRecord() {
        if (!currentRecordId) return;

        hideError();
        hideSuccess();
        showLoading();

        var payload = {
            id:         currentRecordId,
            titlu:      fieldTitlu.value,
            descriere:  fieldDescriere.value,
            judet:      fieldJudet.value,
            localitate: fieldLocalitate.value,
            tip:        fieldTip.value,
            altitudine: fieldTitlu.value ? (parseInt(fieldAltitudine.value, 10) || 0) : 0,
            lat:        fieldLat.value ? parseFloat(fieldLat.value) : null,
            lng:        fieldLng.value ? parseFloat(fieldLng.value) : null,
            website:    fieldWebsite.value
        };

        var xhr = new XMLHttpRequest();
        xhr.open('POST', '../api/crud.php?action=update', true);
        xhr.setRequestHeader('Content-Type', 'application/json');
        xhr.setRequestHeader('Accept', 'application/json');
        xhr.timeout = 10000;

        xhr.onload = function() {
            hideLoading();

            try {
                var response = JSON.parse(xhr.responseText);

                if (xhr.status === 200 && response.success) {
                    showSuccess(response.message || 'Salvat cu succes!');

                    // Actualizeaza datele originale
                    originalData = {
                        titlu:      fieldTitlu.value,
                        judet:      fieldJudet.value,
                        localitate: fieldLocalitate.value,
                        tip:        fieldTip.value,
                        altitudine: fieldAltitudine.value,
                        lat:        fieldLat.value,
                        lng:        fieldLng.value,
                        descriere:  fieldDescriere.value,
                        website:    fieldWebsite.value
                    };
                    isDirty = false;
                    btnSave.disabled = true;
                } else {
                    showError(response.error || 'Eroare necunoscuta la salvare.');
                }
            } catch (e) {
                showError('Eroare la parsarea raspunsului: ' + e.message);
            }
        };

        xhr.onerror = function() {
            hideLoading();
            showError('Eroare de retea. Verificati conexiunea la Internet.');
        };

        xhr.ontimeout = function() {
            hideLoading();
            showError('Timeout. Serverul nu raspunde.');
        };

        xhr.send(JSON.stringify(payload));
    }

    // Event: schimbare selectie
    selectId.addEventListener('change', function() {
        var newId = this.value;

        if (!newId) {
            // A fost selectata optiunea goala
            if (isDirty) {
                if (confirm('Aveți modificări nesalvate. Doriți să salvați înainte de a continua?')) {
                    saveRecord();
                }
            }
            clearForm();
            isLoadingRecord = false;
            return;
        }

        // Daca exista modificari nesalvate si se incearca schimbarea selectiei
        if (isDirty) {
            var shouldSave = confirm('Aveți modificări nesalvate pentru înregistrarea #' + currentRecordId + '. Doriți să salvați înainte de a continua?');
            if (shouldSave) {
                // Salveaza si apoi incarca noua inregistrare
                saveRecord();
            }
            // Oricum, continuam cu incarcarea noii inregistrari
        }

        isLoadingRecord = true;
        loadRecord(newId);
    });

    // Event: modificare campuri -> verifica dirty
    var allFields = [fieldTitlu, fieldJudet, fieldLocalitate, fieldTip, fieldAltitudine, fieldLat, fieldLng, fieldDescriere, fieldWebsite];
    for (var i = 0; i < allFields.length; i++) {
        allFields[i].addEventListener('input', checkDirty);
        allFields[i].addEventListener('change', checkDirty);
    }

    // Event: buton Save
    btnSave.addEventListener('click', function() {
        if (isDirty) {
            saveRecord();
        }
    });

    // Event: buton Reset
    btnReset.addEventListener('click', function() {
        if (currentRecordId) {
            if (isDirty && confirm('Aveți modificări nesalvate. Doriți să resetați oricum?')) {
                loadRecord(currentRecordId);
            } else if (!isDirty) {
                loadRecord(currentRecordId);
            }
        }
    });

    // Initializare
    populateJudete();
    loadIds();
})();
</script>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
