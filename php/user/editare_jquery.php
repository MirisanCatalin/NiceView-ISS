<?php
$page_title = 'Editare jQuery AJAX';
require_once dirname(__DIR__) . '/includes/header.php';

checkRememberMe();
redirectIfNotLoggedIn();
?>

<h2>Cerința 6: Editare cu jQuery AJAX</h2>
<p>
    Selectați o priveliște din listă pentru a-i edita detaliile.
    La schimbarea selecției, câmpurile se actualizează automat prin AJAX (jQuery).
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
$(document).ready(function() {

    // Elemente jQuery
    var $selectId      = $('#record-id');
    var $btnSave       = $('#btn-save');
    var $btnReset      = $('#btn-reset');
    var $errorContainer   = $('#error-container');
    var $successContainer = $('#success-container');
    var $loadingIndicator = $('#loading-indicator');
    var $authorDisplay    = $('#author-display');

    // Campuri jQuery
    var $fieldTitlu       = $('#titlu');
    var $fieldJudet       = $('#judet');
    var $fieldLocalitate  = $('#localitate');
    var $fieldTip         = $('#tip');
    var $fieldAltitudine  = $('#altitudine');
    var $fieldLat         = $('#lat');
    var $fieldLng         = $('#lng');
    var $fieldDescriere   = $('#descriere');
    var $fieldWebsite     = $('#website');

    // Stare
    var originalData = {};
    var isDirty = false;
    var currentRecordId = null;

    // Lista judetelor
    var judeteList = ['AB','AR','AG','BC','BH','BN','BT','BV','BR','BZ','CS','CL','CJ','CT','CV','DB','DJ','GL','GR','GJ','HR','HD','IL','IS','IF','MM','MH','MS','NT','OT','PH','SM','SJ','SB','SV','TR','TM','TL','VS','VL','VN','B'];

    function showError(message) {
        $errorContainer.text('⚠️ ' + message).show();
        $successContainer.hide();
    }

    function hideError() {
        $errorContainer.hide();
    }

    function showSuccess(message) {
        $successContainer.text('✓ ' + message).show();
        $errorContainer.hide();
    }

    function hideSuccess() {
        $successContainer.hide();
    }

    function showLoading() {
        $loadingIndicator.show();
    }

    function hideLoading() {
        $loadingIndicator.hide();
    }

    // Populeaza dropdown-ul de judete
    function populateJudete() {
        $fieldJudet.empty().append($('<option>').val('').text('-- Selectați județul --'));
        $.each(judeteList, function(index, code) {
            $fieldJudet.append($('<option>').val(code).text(code));
        });
    }

    // Incarca lista de ID-uri
    function loadIds() {
        showLoading();

        $.ajax({
            url: '../api/crud.php',
            method: 'GET',
            data: { action: 'get_all_ids' },
            dataType: 'json',
            timeout: 10000
        })
        .done(function(response) {
            hideLoading();

            if (response.error) {
                showError(response.error);
                return;
            }

            $selectId.empty().append($('<option>').val('').text('-- Selectați o priveliște --'));
            $.each(response.data, function(index, item) {
                $selectId.append($('<option>').val(item.id).text(item.id + ' - ' + item.titlu));
            });
        })
        .fail(function(jqXHR, textStatus, errorThrown) {
            hideLoading();

            if (textStatus === 'timeout') {
                showError('Timeout. Serverul nu raspunde. Verificati conexiunea la Internet.');
            } else if (jqXHR.status === 0) {
                showError('Eroare de retea. Verificati conexiunea la Internet.');
            } else {
                showError('Eroare la incarcarea listei de ID-uri (HTTP ' + jqXHR.status + ').');
            }
        });
    }

    // Incarca datele unei inregistrari
    function loadRecord(id) {
        hideError();
        hideSuccess();
        showLoading();

        $.ajax({
            url: '../api/crud.php',
            method: 'GET',
            data: { action: 'get_record', id: id },
            dataType: 'json',
            timeout: 10000
        })
        .done(function(response) {
            hideLoading();

            if (response.error) {
                showError(response.error);
                clearForm();
                return;
            }

            var data = response.data;

            // Populeaza campurile
            $fieldTitlu.val(data.titlu || '');
            $fieldJudet.val(data.judet || '');
            $fieldLocalitate.val(data.localitate || '');
            $fieldTip.val(data.tip || 'munte');
            $fieldAltitudine.val(data.altitudine || '');
            $fieldLat.val(data.lat || '');
            $fieldLng.val(data.lng || '');
            $fieldDescriere.val(data.descriere || '');
            $fieldWebsite.val(data.website || '');
            $authorDisplay.text(data.username || '');

            // Salveaza datele originale
            originalData = {
                titlu:      $fieldTitlu.val(),
                judet:      $fieldJudet.val(),
                localitate: $fieldLocalitate.val(),
                tip:        $fieldTip.val(),
                altitudine: $fieldAltitudine.val(),
                lat:        $fieldLat.val(),
                lng:        $fieldLng.val(),
                descriere:  $fieldDescriere.val(),
                website:    $fieldWebsite.val()
            };

            currentRecordId = id;
            isDirty = false;
            $btnSave.prop('disabled', true);
        })
        .fail(function(jqXHR, textStatus, errorThrown) {
            hideLoading();

            if (textStatus === 'timeout') {
                showError('Timeout. Serverul nu raspunde.');
            } else if (jqXHR.status === 0) {
                showError('Eroare de retea. Verificati conexiunea la Internet.');
            } else if (jqXHR.status === 404) {
                showError('Inregistrarea nu a fost gasita.');
                clearForm();
            } else {
                showError('Eroare server (HTTP ' + jqXHR.status + ').');
            }
        });
    }

    function clearForm() {
        $fieldTitlu.val('');
        $fieldJudet.val('');
        $fieldLocalitate.val('');
        $fieldTip.val('munte');
        $fieldAltitudine.val('');
        $fieldLat.val('');
        $fieldLng.val('');
        $fieldDescriere.val('');
        $fieldWebsite.val('');
        $authorDisplay.text('');
        originalData = {};
        currentRecordId = null;
        isDirty = false;
        $btnSave.prop('disabled', true);
    }

    function checkDirty() {
        if (!currentRecordId) {
            isDirty = false;
            $btnSave.prop('disabled', true);
            return;
        }

        isDirty = (
            $fieldTitlu.val()      !== originalData.titlu ||
            $fieldJudet.val()      !== originalData.judet ||
            $fieldLocalitate.val() !== originalData.localitate ||
            $fieldTip.val()        !== originalData.tip ||
            $fieldAltitudine.val() !== originalData.altitudine ||
            $fieldLat.val()        !== originalData.lat ||
            $fieldLng.val()        !== originalData.lng ||
            $fieldDescriere.val()  !== originalData.descriere ||
            $fieldWebsite.val()    !== originalData.website
        );

        $btnSave.prop('disabled', !isDirty);
    }

    function saveRecord() {
        if (!currentRecordId) return;

        hideError();
        hideSuccess();
        showLoading();

        var payload = {
            id:         currentRecordId,
            titlu:      $fieldTitlu.val(),
            descriere:  $fieldDescriere.val(),
            judet:      $fieldJudet.val(),
            localitate: $fieldLocalitate.val(),
            tip:        $fieldTip.val(),
            altitudine: $fieldAltitudine.val() ? (parseInt($fieldAltitudine.val(), 10) || 0) : 0,
            lat:        $fieldLat.val() ? parseFloat($fieldLat.val()) : null,
            lng:        $fieldLng.val() ? parseFloat($fieldLng.val()) : null,
            website:    $fieldWebsite.val()
        };

        $.ajax({
            url: '../api/crud.php?action=update',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(payload),
            dataType: 'json',
            timeout: 10000
        })
        .done(function(response) {
            hideLoading();

            if (response.success) {
                showSuccess(response.message || 'Salvat cu succes!');

                // Actualizeaza datele originale
                originalData = {
                    titlu:      $fieldTitlu.val(),
                    judet:      $fieldJudet.val(),
                    localitate: $fieldLocalitate.val(),
                    tip:        $fieldTip.val(),
                    altitudine: $fieldAltitudine.val(),
                    lat:        $fieldLat.val(),
                    lng:        $fieldLng.val(),
                    descriere:  $fieldDescriere.val(),
                    website:    $fieldWebsite.val()
                };
                isDirty = false;
                $btnSave.prop('disabled', true);
            } else {
                showError(response.error || 'Eroare necunoscuta la salvare.');
            }
        })
        .fail(function(jqXHR, textStatus, errorThrown) {
            hideLoading();

            if (textStatus === 'timeout') {
                showError('Timeout. Serverul nu raspunde.');
            } else if (jqXHR.status === 0) {
                showError('Eroare de retea. Verificati conexiunea la Internet.');
            } else {
                try {
                    var resp = JSON.parse(jqXHR.responseText);
                    showError(resp.error || 'Eroare la salvare.');
                } catch (e) {
                    showError('Eroare la salvare (HTTP ' + jqXHR.status + ').');
                }
            }
        });
    }

    // Event: schimbare selectie
    $selectId.on('change', function() {
        var newId = $(this).val();

        if (!newId) {
            if (isDirty) {
                if (confirm('Aveți modificări nesalvate. Doriți să salvați înainte de a continua?')) {
                    saveRecord();
                }
            }
            clearForm();
            return;
        }

        if (isDirty) {
            var shouldSave = confirm('Aveți modificări nesalvate pentru înregistrarea #' + currentRecordId + '. Doriți să salvați înainte de a continua?');
            if (shouldSave) {
                saveRecord();
            }
        }

        loadRecord(newId);
    });

    // Event: modificare campuri
    $('#edit-form').on('input change', 'input, select, textarea', checkDirty);

    // Event: buton Save
    $btnSave.on('click', function() {
        if (isDirty) {
            saveRecord();
        }
    });

    // Event: buton Reset
    $btnReset.on('click', function() {
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
});
</script>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
