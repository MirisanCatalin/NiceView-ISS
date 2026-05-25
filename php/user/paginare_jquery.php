<?php
$page_title = 'Paginare jQuery + JSON';
require_once dirname(__DIR__) . '/includes/header.php';

checkRememberMe();
redirectIfNotLoggedIn();
?>

<h2>Cerința 3: Paginare cu jQuery + JSON</h2>
<p>Afișare paginată a înregistrărilor <em>privelisti</em>, câte <strong>5 pe pagină</strong>, folosind apeluri AJAX realizate prin jQuery.</p>

<div id="error-container" style="display:none; color: #c0392b; background: #fdecea; padding: 15px; border-radius: 4px; border-left: 5px solid #c0392b; margin-bottom: 20px;"></div>

<div id="loading-indicator" style="display:none; text-align:center; padding:20px; font-weight:bold; color:#3498db;">
    Se încarcă datele...
</div>

<div class="table-container">
    <table id="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Titlu</th>
                <th>Județ</th>
                <th>Localitate</th>
                <th>Tip</th>
                <th>Altitudine (m)</th>
                <th>Status</th>
                <th>Autor</th>
            </tr>
        </thead>
        <tbody id="table-body">
            <tr><td colspan="8" style="text-align:center;">Se încarcă...</td></tr>
        </tbody>
    </table>
</div>

<div style="margin-top: 20px; display: flex; gap: 10px; align-items: center;">
    <button id="btn-prev" disabled>Previous 5</button>
    <span id="page-info" style="font-weight: bold;">Pagina 0 / 0</span>
    <button id="btn-next" disabled>Next 5</button>
</div>

<script>
$(document).ready(function() {
    var currentPage = 1;
    var totalPages = 1;
    var limit = 5;

    var $btnPrev = $('#btn-prev');
    var $btnNext = $('#btn-next');
    var $pageInfo = $('#page-info');
    var $tableBody = $('#table-body');
    var $errorContainer = $('#error-container');
    var $loadingIndicator = $('#loading-indicator');

    function showError(message) {
        $errorContainer.text('⚠️ ' + message).show();
    }

    function hideError() {
        $errorContainer.hide();
    }

    function showLoading() {
        $loadingIndicator.show();
    }

    function hideLoading() {
        $loadingIndicator.hide();
    }

    function updateButtons() {
        $btnPrev.prop('disabled', currentPage <= 1);
        $btnNext.prop('disabled', currentPage >= totalPages);
        $pageInfo.text('Pagina ' + currentPage + ' / ' + totalPages);
    }

    function loadPage(page) {
        hideError();
        showLoading();

        $.ajax({
            url: '../api/pagination_json.php',
            method: 'GET',
            data: { page: page, limit: limit },
            dataType: 'json',
            timeout: 10000
        })
        .done(function(response) {
            hideLoading();

            if (response.error) {
                showError(response.error);
                return;
            }

            currentPage = response.page;
            totalPages = response.total_pages;
            var data = response.data;

            // Populeaza tabelul folosind jQuery
            $tableBody.empty();

            if (data.length === 0) {
                $tableBody.append('<tr><td colspan="8" style="text-align:center;">Nu exista inregistrari.</td></tr>');
            } else {
                $.each(data, function(index, row) {
                    var $tr = $('<tr></tr>');
                    $tr.append($('<td></td>').text(row.id));
                    $tr.append($('<td></td>').text(row.titlu));
                    $tr.append($('<td></td>').text(row.judet));
                    $tr.append($('<td></td>').text(row.localitate));
                    $tr.append($('<td></td>').text(row.tip));
                    $tr.append($('<td></td>').text(row.altitudine));
                    $tr.append($('<td></td>').html('<strong>' + $('<div>').text(row.status).html() + '</strong>'));
                    $tr.append($('<td></td>').text(row.username));
                    $tableBody.append($tr);
                });
            }

            updateButtons();
        })
        .fail(function(jqXHR, textStatus, errorThrown) {
            hideLoading();

            if (textStatus === 'timeout') {
                showError('Timeout. Serverul nu raspunde. Verificati conexiunea la Internet.');
            } else if (jqXHR.status === 401) {
                showError('Neautorizat. Va rugam sa va autentificati.');
            } else if (textStatus === 'error' && jqXHR.status === 0) {
                showError('Eroare de retea. Verificati conexiunea la Internet. Serverul poate fi indisponibil.');
            } else {
                showError('Eroare server (HTTP ' + jqXHR.status + '): ' + errorThrown);
            }
        });
    }

    $btnPrev.on('click', function() {
        if (currentPage > 1) {
            loadPage(currentPage - 1);
        }
    });

    $btnNext.on('click', function() {
        if (currentPage < totalPages) {
            loadPage(currentPage + 1);
        }
    });

    // Incarca prima pagina
    loadPage(1);
});
</script>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
