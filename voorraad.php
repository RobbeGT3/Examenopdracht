<?php
session_start();
// if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
//     die("Page not available");
// }


$currentPage = basename($_SERVER['PHP_SELF']);
require_once  __DIR__. '/common/dbconnection.php';

// $stmt1 = $conn->prepare("SELECT p.idProducts, p.`EAN-nummer`, p.productnaam, p.aantal, p.eenheid, p.ontvangst_datum, c.product_categorie FROM Products p INNER JOIN Categories c ON p.Categories_idCategories = c.idCategories;");
$stmt1 = $conn->prepare("SELECT 
        p.idProducts, 
        p.`EAN-nummer`, 
        p.productnaam, 
        p.aantal, 
        c.product_categorie,

        EXISTS (
            SELECT 1 
            FROM Voedselpakketten_has_Products vhp 
            WHERE vhp.Products_idProducts = p.idProducts
        ) AS in_use

    FROM Products p 
    INNER JOIN Categories c 
        ON p.Categories_idCategories = c.idCategories");
$stmt1->execute();
$result1 = $stmt1->get_result();
$voorraad = $result1->fetch_all(MYSQLI_ASSOC);

$stmtCat = $conn->prepare("SELECT c.idCategories, c.product_categorie FROM Categories c;");
$stmtCat->execute();
$resultCat = $stmtCat->get_result();
$categories = $resultCat->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voedselbank - Voorraad Beheer</title>
    <link rel="stylesheet" href="voorraard.css">
    <link rel="stylesheet" href="styles/styles.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="voorraard.js"></script>
</head>
<body>
    <div class="container">
        <?php include 'sidebar.php' ?>
        <!-- Hoofd Inhoud -->
        <main class="main-content">
            <header class="content-header">
                <h1>Voorraad Beheer</h1>
                <button class="btn-new-product">
                    <i class="fas fa-plus"></i>
                    Nieuw Product
                </button>
            </header>

            <div class="search-container">
                <div class="search-bar">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Zoek op EAN-nummer...">
                </div>
            </div>

            <div class="table-container">
                <table class="inventory-table">
                    <thead>
                        <tr>
                            <th onclick="sortTable(0)">EAN-nummer <i class="fas sort-icon"></i></th>
                            <th onclick="sortTable(1)">Productnaam  <i class="fas sort-icon"></i></th>
                            <th onclick="sortTable(2)">Categorie <i class="fas sort-icon"></i></th>
                            <th onclick="sortTable(3)">Aantal <i class="fas sort-icon"></th>
                            <th>Acties</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($voorraad as $row): ?>
                        <tr>
                            <td>
                                <div class="ean-cell">
                                    <?= htmlspecialchars($row['EAN-nummer']) ?>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($row['productnaam']) ?></td>
                            <td><?= htmlspecialchars($row['product_categorie']) ?></td>
                            <td><span class="quantity-badge"><?= htmlspecialchars($row['aantal']) ?></span></td>
                            <td>
                                <div class="actions">
                                    <button class="btn-edit" data-id="<?= $row['idProducts'] ?>">
                                        <i class="fas fa-pencil"></i>
                                    </button>
                                    <button class="btn-delete" <?= $row['in_use'] ? 'disabled style="opacity:0.5; cursor:not-allowed;"' : '' ?>data-id="<?= $row['idProducts'] ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Nieuw Product Modal -->
    <div id="nieuwProductModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Nieuw Product</h2>
                <button class="modal-close" onclick="closeModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form class="modal-form">
                <div class="form-group">
                    <label for="ean-nummer">EAN-nummer</label>
                    <input type="text" id="ean-nummer" name="ean-nummer" placeholder="Voer EAN-nummer in...">
                </div>
                
                <div class="form-group">
                    <label for="productnaam">Productnaam</label>
                    <input type="text" id="productnaam" name="productnaam" placeholder="Voer productnaam in...">
                </div>
                
                <div class="form-group">
                    <label for="categorie">Categorie</label>
                    <div id="category-container">

                        <select id="categorie" name="categorie">
                            <option value="">Selecteer categorie...</option>

                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['idCategories'] ?>">
                                    <?= htmlspecialchars($cat['product_categorie']) ?>
                                </option>
                            <?php endforeach; ?>

                            <option value="overig">Anders namelijk...</option>
                        </select>
                        
                        <div id="new-category-container" class="new-category-container" style="display: none;">
                            <input type="text" id="new-category" name="new-category" placeholder="Voer nieuwe categorie in...">
                            <div class="new-category-buttons">
                                <button type="button" class="btn-add-category" onclick="addNewCategory()">Toevoegen</button>
                                <button type="button" class="btn-cancel-category" onclick="cancelNewCategory()">Annuleren</button>
                            </div>
                            <input type="hidden" id="is-new-category" value="0">
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="aantal">Aantal</label>
                    <input type="number" id="aantal" name="aantal" placeholder="Voer aantal in..." min="0">
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn-cancel" onclick="closeModal()">
                        Annuleren
                    </button>
                    <button type="submit" class="btn-submit">
                        Toevoegen
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Product Bewerken Modal -->
    <div id="productBewerkenModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Product Bewerken</h2>
                <button class="modal-close" onclick="closeEditModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form class="modal-form" id="edit-form">
                <input type="hidden" id="edit-row-index" name="edit-row-index">
                <input type="hidden" id="edit-product-id" name="edit-product-id">
                
                <div class="form-group">
                    <label for="edit-ean-nummer">EAN-nummer</label>
                    <input type="text" id="edit-ean-nummer" name="edit-ean-nummer" placeholder="Voer EAN-nummer in...">
                </div>
                
                <div class="form-group">
                    <label for="edit-productnaam">Productnaam</label>
                    <input type="text" id="edit-productnaam" name="edit-productnaam" placeholder="Voer productnaam in...">
                </div>
                
                <div class="form-group">
                    <label for="edit-categorie">Categorie</label>
                    <div id="edit-category-container">
                        <select id="edit-categorie" name="edit-categorie">
                            <option value="">Selecteer categorie...</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['idCategories'] ?>">
                                    <?= htmlspecialchars($cat['product_categorie']) ?>
                                </option>
                            <?php endforeach; ?>

                            <option value="overig">Anders namelijk...</option>
                        </select>
                        
                        <div id="edit-new-category-container" class="new-category-container" style="display: none;">
                            <input type="text" id="edit-new-category" name="edit-new-category" placeholder="Voer nieuwe categorie in...">
                            <div class="new-category-buttons">
                                <button type="button" class="btn-add-category" onclick="addEditNewCategory()">Toevoegen</button>
                                <button type="button" class="btn-cancel-category" onclick="cancelEditNewCategory()">Annuleren</button>
                                <input type="hidden" id="edit-is-new-category" value="0">
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="edit-aantal">Aantal</label>
                    <input type="number" id="edit-aantal" name="edit-aantal" placeholder="Voer aantal in..." min="0">
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn-cancel" onclick="closeEditModal()">
                        Annuleren
                    </button>
                    <button type="submit" class="btn-submit">
                        Bijwerken
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>