<?php
$page_title = 'Paginare Vanilla JS + XML';
require_once dirname(__DIR__) . '/includes/header.php';

checkRememberMe();
redirectIfNotLoggedIn();
?>

<h2>Cerința 2: Paginare cu Vanilla JS + XML</h2>
<p>Afișare paginată a înregistrărilor <em>privelisti</em>, câte <strong>5 pe pagină</strong>, folosind AJAX cu răspuns XML.</p>

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
(function() {
    'use strict';

    var currentPage = 1;
    var totalPages = 1;
    var limit = 5;

    var btnPrev = document.getElementById('btn-prev');
    var btnNext = document.getElementById('btn-next');
    var pageInfo = document.getElementById('page-info');
    var tableBody = document.getElementById('table-body');
    var errorContainer = document.getElementById('error-container');
    var loadingIndicator = document.getElementById('loading-indicator');

    function showError(message) {
        errorContainer.textContent = '⚠️ ' + message;
        errorContainer.style.display = 'block';
    }

    function hideError() {
        errorContainer.style.display = 'none';
    }

    function showLoading() {
        loadingIndicator.style.display = 'block';
    }

    function hideLoading() {
        loadingIndicator.style.display = 'none';
    }

    function updateButtons() {
        btnPrev.disabled = (currentPage <= 1);
        btnNext.disabled = (currentPage >= totalPages);
        pageInfo.textContent = 'Pagina ' + currentPage + ' / ' + totalPages;
    }

    function loadPage(page) {
        hideError();
        showLoading();

        var xhr = new XMLHttpRequest();
        xhr.open('GET', '../api/pagination_xml.php?page=' + page + '&limit=' + limit, true);
        // Nu setam Accept header explicit pentru XML - lasam */*

        xhr.onload = function() {
            hideLoading();

            if (xhr.status === 200) {
                try {
                    var xmlDoc = xhr.responseXML;

                    if (!xmlDoc) {
                        // In caz ca responseXML e null (parser error), incercam manual
                        var parser = new DOMParser();
                        xmlDoc = parser.parseFromString(xhr.responseText, 'application/xml');

                        // Verificam daca e eroare de parsare
                        var parseError = xmlDoc.querySelector('parsererror');
                        if (parseError) {
                            showError('Eroare la parsarea XML-ului: ' + parseError.textContent);
                            return;
                        }
                    }

                    // Verificam daca raspunsul contine o eroare
                    var errorNode = xmlDoc.querySelector('response > error');
                    if (errorNode) {
                        showError(errorNode.textContent);
                        return;
                    }

                    // Extragem metadata
                    var totalNode = xmlDoc.querySelector('response > total');
                    var pageNode = xmlDoc.querySelector('response > page');
                    var totalPagesNode = xmlDoc.querySelector('response > total_pages');

                    currentPage = totalNode ? parseInt(totalNode.textContent, 10) : 1;
                    totalPages = totalPagesNode ? parseInt(totalPagesNode.textContent, 10) : 1;

                    // Extragem inregistrarile
                    var records = xmlDoc.querySelectorAll('response > data > record');

                    tableBody.innerHTML = '';

                    if (records.length === 0) {
                        tableBody.innerHTML = '<tr><td colspan="8" style="text-align:center;">Nu exista inregistrari.</td></tr>';
                    } else {
                        for (var i = 0; i < records.length; i++) {
                            var record = records[i];

                            function getXmlValue(tagName) {
                                var el = record.querySelector(tagName);
                                return el ? el.textContent : '';
                            }

                            var tr = document.createElement('tr');
                            tr.innerHTML =
                                '<td>' + escapeHtml(getXmlValue('id')) + '</td>' +
                                '<td>' + escapeHtml(getXmlValue('titlu')) + '</td>' +
                                '<td>' + escapeHtml(getXmlValue('judet')) + '</td>' +
                                '<td>' + escapeHtml(getXmlValue('localitate')) + '</td>' +
                                '<td>' + escapeHtml(getXmlValue('tip')) + '</td>' +
                                '<td>' + escapeHtml(getXmlValue('altitudine')) + '</td>' +
                                '<td><strong>' + escapeHtml(getXmlValue('status')) + '</strong></td>' +
                                '<td>' + escapeHtml(getXmlValue('username')) + '</td>';
                            tableBody.appendChild(tr);
                        }
                    }

                    updateButtons();

                } catch (e) {
                    showError('Eroare la procesarea XML-ului: ' + e.message);
                }
            } else if (xhr.status === 401) {
                showError('Neautorizat. Va rugam sa va autentificati.');
            } else {
                showError('Eroare server (HTTP ' + xhr.status + '). Verificati conexiunea.');
            }
        };

        xhr.onerror = function() {
            hideLoading();
            showError('Eroare de retea. Verificati conexiunea la Internet. Serverul poate fi indisponibil.');
        };

        xhr.ontimeout = function() {
            hideLoading();
            showError('Timeout. Serverul nu raspunde. Verificati conexiunea la Internet.');
        };

        xhr.timeout = 10000;
        xhr.send();
    }

    function escapeHtml(text) {
        if (text === null || text === undefined) return '';
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(String(text)));
        return div.innerHTML;
    }

    btnPrev.addEventListener('click', function() {
        if (currentPage > 1) {
            loadPage(currentPage - 1);
        }
    });

    btnNext.addEventListener('click', function() {
        if (currentPage < totalPages) {
            loadPage(currentPage + 1);
        }
    });

    loadPage(1);
})();
</script>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
