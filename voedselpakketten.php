<?php
session_start();
// if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
//     die("Page not available");
// }

$currentPage = basename($_SERVER['PHP_SELF']);
require_once  __DIR__. '/common/dbconnection.php';

?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voedselpakketten</title>
    <link rel="stylesheet" href="styles/styles.css">
    <link rel="stylesheet" href="voedselpakketten.css">
</head>
<body>

<div class="app">
    <?php include 'sidebar.php' ?>

    <main class="main">
        <div class="voedselHeader">
            <h1 class="voedselPage-title">Voedselpakketten</h1>
            <button id="openModalBtn" class="btn-add-package">+ Nieuw Pakket Samenstellen</button>
        </div>

        <div id="emptyState" class="empty-state-card">
            Nog geen pakketten samengesteld
        </div>

        <div id="pakketList" class="pakketCard-container"></div>
    </main>
</div>

<div id="modalOverlay" class="modal-overlay hidden">
    <div class="modal-package">
        <div class="modal-header">
            <h2>Pakket samenstellen</h2>
            <button id="closeModalBtn" class="close-btn" type="button">✕</button>
        </div>

        <div class="modal-content">
            <div class="package-section">
                <h3>Stap 1: Selecteer klant</h3>

                <div class="searchable-dropdown" id="familyDropdown">
                    <label for="familySearch">Voor wie is dit pakket?</label>

                    <div class="dropdown-control" id="dropdownControl">
                        <input
                            type="text"
                            id="familySearch"
                            placeholder="-- Selecteer een klant --"
                            autocomplete="off"
                        >
                        <span class="dropdown-arrow">⌄</span>
                    </div>
                    <!-- dropdown met zoekfunctie met klanten data. bestaat uit string family + achternaam - Postcode -->
                    <div class="dropdown-list hidden" id="familyOptions">
                        <div class="dropdown-item family-item" data-value="Familie Bakker - 1234AB">Familie Bakker - 1234AB</div>
                        <div class="dropdown-item family-item" data-value="Familie Visser - 1234CD">Familie Visser - 1234CD</div>
                        <div class="dropdown-item family-item" data-value="Familie Yilmaz - 5678EF">Familie Yilmaz - 5678EF</div>
                    </div>

                    <!-- <div class="dropdown-list hidden" id="familyOptions"></div> -->
                </div>
            </div>

            <div id="clientInfoBox" class="package-section hidden">
                <h3>Klant informatie</h3>
                informatie van de gekozen klant
                <div class="info-grid">
                    <div class="info-card">
                        <p class="small-title">Gezinssamenstelling</p>
                        <div class="family-stats">
                            <div>
                                <strong id="adultCount">0</strong>
                                <span>Volwassenen</span>
                            </div>
                            <div>
                                <strong id="childCount">0</strong>
                                <span>Kinderen</span>
                            </div>
                            <div>
                                <strong id="babyCount">0</strong>
                                <span>Baby's</span>
                            </div>
                        </div>
                    </div>

                    <div class="info-card">
                        <p class="small-title">Dieetwensen</p>
                        <p id="dietText">Geen beperkingen</p>
                    </div>
                </div>
            </div>
            <div id="allergyBox" class="package-section hidden">
                <h3>⚠️ Allergieën</h3>
                <p id="allergyText"></p>
            </div>

            <div id="productStepBox" class="package-section hidden">
                <h3>Stap 2: Voeg producten toe</h3>
                <div id="allergyWarning" class="warning hidden"></div>
                <div class="searchable-dropdown" id="productDropdown">
                    <label for="productSearch">Selecteer product</label>

                    <div class="dropdown-control" id="productDropdownControl">
                        <input
                            type="text"
                            id="productSearch"
                            placeholder="-- Kies een product --"
                            autocomplete="off"
                        >
                        <span class="dropdown-arrow">⌄</span>
                    </div>

                    <div class="dropdown-list hidden" id="productOptions">
                        <!-- producten opties -->
                        <div class="dropdown-item product-item-option"
                             data-name="Aardappelen (1kg)"
                             data-category="Aardappelen, Groente, Fruit">
                            Aardappelen (1kg) - Aardappelen, Groente, Fruit (Voorraad: 100)
                    </div>
                </div>

                <label class="amount-label">Aantal</label>

                <div class="amount-row">
                    <button type="button" id="minusBtn" class="qty-btn">-</button>
                    <input type="text" id="qtyInput" value="1" readonly>
                    <button type="button" id="plusBtn" class="qty-btn">+</button>
                    <button type="button" id="addProductBtn" class="add-btn">Product toevoegen</button>
                </div>
            </div>

            <div id="selectedProductsBox" class="package-section hidden">
                <h3>Producten in pakket (<span id="productCount">0</span>)</h3>
                <div id="selectedProductsList"></div>

                <div class="bottom-actions">
                    <button type="button" id="createPackageBtn" class="create-btn">✓ Pakket aanmaken</button>
                    <button type="button" id="cancelBtn" class="cancel-btn">Annuleren</button>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<div id="viewModal" class="modal-overlay hidden">
    <div class="modal-package view-modal">
        <div class="modal-header">
            <h2 id="viewTitle">Pakket #1 - Details</h2>
            <button onclick="closeViewModal()" class="close-btn">✕</button>
        </div>

        <div class="modal-content" id="viewContent"></div>
    </div>
</div>

<script src="voedselpakketten.js"></script>
<!-- <script src="test2.js"></script> -->
</body>
</html>