<?php
session_start();

// if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
//     die("Page not available");
// }

$currentPage = basename($_SERVER['PHP_SELF']);
require_once  __DIR__. '/common/dbconnection.php';
$stmt = $conn->prepare("
    SELECT 
        k.idKlanten,
        k.achternaam,
        k.postcode,
        k.telefoonnummer,
        MAX(v.samenstellings_datum) AS laatste_samenstelling,
        MAX(v.uitgifte_datum) AS laatste_uitgifte
    FROM Klanten k
    LEFT JOIN Voedselpakketten v 
        ON v.Klanten_idKlanten = k.idKlanten
    GROUP BY k.idKlanten
    ORDER BY k.achternaam ASC
");

$stmt->execute();
$klanten = $stmt->get_result();

$stmt2 = $conn->prepare("SELECT * FROM Klantenwensen;");
$stmt2->execute();
$result2 = $stmt2->get_result();
$opgeslagenWensen = $result2->fetch_all(MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html lang="nl">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Klanten Beheer</title>
  <link rel="stylesheet" href="styles/styles.css" />
  <link rel="stylesheet" href="styles/klanten.css" />
</head>
<body>
  <div class="app">
    <?php include 'sidebar.php' ?>
    <main class="main">
      <div class="klanten-page">
        <div class="klanten-topbar">
          <h1>Klanten Beheer</h1>
          <button class="btn-green" id="openNewClientModal">+ Nieuwe Klant</button>
        </div>

        <div class="toolbar-card">
          <div class="toolbar-grid">
            <div class="toolbar-field">
              <label>Zoeken op naam</label>
              <input type="text" id="searchInput" placeholder="Zoek op gezinsnaam, voornaam of achternaam..." />
            </div>

            <div class="toolbar-field">
              <label>Sorteren op</label>
              <select id="sortSelect">
                <option value="date_old">Samenstelling (oud naar nieuw)</option>
                <option value="date_new">Samenstelling (nieuw naar oud)</option>
                <option value="name_az">Familienaam A-Z</option>
                <option value="name_za">Familienaam Z-A</option>
              </select>
            </div>
          </div>
        </div>

        <div class="table-card">
          <table class="klanten-table">
            <thead>
              <tr>
                <th>Familienaam</th>
                <th>Postcode</th>
                <th>Telefoonnummer</th>
                <th>Laatst samengesteld</th>
                <th>Laatste uitgifte</th>
                <th>Voedselpakket</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody id="klantenTableBody">
                
            </tbody>
          </table>
        </div>
      </div>
    </main>
  </div>

  <div class="modal-overlay hidden" id="modalOverlay"></div>

  <div class="modal hidden" id="clientDetailModal">
    <div class="modal-header">
      <h2 id="detailTitle">Familie Bakker</h2>
      <div class="modal-header-actions">
        <button class="btn-blue-outline" id="editClientBtn">Bewerken</button>
        <button class="close-btn" data-close>&times;</button>
      </div>
    </div>

    <div class="modal-body scroll-body">
      <div class="info-section">
        <h3>Basis Informatie</h3>
        <div class="info-grid two">
          <div>
            <span class="label">Voornaam</span>
            <p id="detailVoornaam">Jan</p>
          </div>
          <div>
            <span class="label">Achternaam</span>
            <p id="detailAchternaam">Bakker</p>
          </div>
          <div>
            <span class="label">Adres</span>
            <p id="detailAdres">Dorpsstraat 12</p>
          </div>
          <div>
            <span class="label">Postcode</span>
            <p id="detailPostcode">1234AB</p>
          </div>
          <div>
            <span class="label">Woonplaats</span>
            <p id="detailWoonplaats">Amsterdam</p>
          </div>
          <div>
            <span class="label">Telefoonnummer</span>
            <p id="detailTelefoon">06-12345678</p>
          </div>
          <div class="full">
            <span class="label">E-mailadres</span>
            <p id="detailEmail">bakker@example.nl</p>
          </div>
        </div>
      </div>

      <div class="info-section">
        <h3>Gezinssamenstelling</h3>
        <div class="stats-grid">
          <div>
            <span class="label">Volwassenen (&gt;18 jaar)</span>
            <p id="detailAdults">2</p>
          </div>
          <div>
            <span class="label">Kinderen (&gt;2 jaar)</span>
            <p id="detailChildren">2</p>
          </div>
          <div>
            <span class="label">Baby's (&le;2 jaar)</span>
            <p id="detailBabies">0</p>
          </div>
        </div>
      </div>

      <div class="info-section">
        <h3>Wensen en Beperkingen</h3>
        <div class="stack-info">
          <div>
            <span class="label">Dieetwensen</span>
            <p id="detailDiet">Geen dieetwensen</p>
          </div>
          <div>
            <span class="label">Allergieën</span>
            <p id="detailAllergy">Geen allergieën</p>
          </div>
        </div>
      </div>

      <div class="info-section">
        <h3>Voedselpakketten</h3>
        <div class="stack-info">
          <div>
            <span class="label">Totaal aantal pakketten</span>
            <p id="detailPackages">0</p>
          </div>
          <div>
            <span class="label">Laatst samengesteld</span>
            <p class="status-warning" id="detailLastPackage">Nog geen pakket</p>
          </div>
          <div>
            <span class="label">Status</span>
            <p><span id="detailStatus"></span></p>
          </div>
        </div>
      </div>
    </div>

    <div class="modal-footer between">
      <button class="btn-green hidden" id="approveClientBtn">✓ Goedkeuren</button>
      <button class="btn-red" id="deleteClientBtn">🗑 Verwijderen</button>
    </div>
  </div>

  <div class="modal hidden modal-medium" id="packageModal">
    <div class="modal-header">
      <div>
        <h2>Nieuw Pakket Samenstellen</h2>
        <p class="subtext" id="packageForText"></p>
      </div>
      <button class="close-btn" data-close>&times;</button>
    </div>

    <div class="modal-body">
      <div class="info-section">
        <h3>Klant Informatie</h3>

        <div class="info-grid two">
          <div class="mini-box">
            <span class="label">Gezinssamenstelling</span>
            <div class="mini-stats">
              <div><strong id="packageAdults">2</strong><span>Volwassenen</span></div>
              <div><strong id="packageChildren">2</strong><span>Kinderen</span></div>
              <div><strong id="packageBabies">0</strong><span>Baby's</span></div>
            </div>
          </div>

          <div class="mini-box">
            <span class="label">Dieetwensen</span>
            <p id="packageDiet">Geen beperkingen</p>
          </div>
        </div>
      </div>

      <div class="info-section">
        <h3>Voeg Producten Toe</h3>
        <div class="form-group">
          <label>Selecteer Product</label>
          <select id="productSelect">
            <option value="">-- Kies een product --</option>
          </select>
        </div>

        <div class="form-group">
          <label>Aantal</label>
          <div class="quantity-row">
            <button type="button" class="qty-btn" id="minusQty">-</button>
            <input type="text" id="qtyInput" value="1" readonly />
            <button type="button" class="qty-btn" id="plusQty">+</button>
            <button type="button" class="btn-green wide-btn" id="addProductBtn">Product Toevoegen</button>
          </div>
        </div>
      </div>

      <div class="info-section">
        <h3>Toegevoegde Producten (<span id="productCount">0</span>)</h3>
        <div id="productList" class="empty-products">
          Nog geen producten toegevoegd. Selecteer hierboven een product.
        </div>
      </div>
    </div>

    <div class="modal-footer right">
      <button class="btn-light" data-close>Annuleren</button>
      <button class="btn-green-light" id="savePackageBtn">Pakket Samenstellen</button>
    </div>
  </div>

  <div class="modal hidden" id="newClientModal">
    <div class="modal-header">
      <h2 id="clientModalTitle">Nieuwe Klant</h2>
      <button class="close-btn" data-close>&times;</button>
    </div>

    <div class="modal-body scroll-body">
      <div class="info-section">
        <h3>Basis Informatie</h3>

        <div class="form-grid two">
          <div class="form-group">
            <label>Voornaam *</label>
            <input type="text" id="voornaam" name="voornaam" placeholder="Voornaam" required/>
          </div>

          <div class="form-group">
            <label>Achternaam *</label>
            <input type="text" id="achternaam" name="achternaam" placeholder="Achternaam" maxlength="50" required />
          </div>

          <div class="form-group">
            <label>Adres *</label>
            <input type="text" id="adres" name="adres" placeholder="Adres" maxlength="100" required/>
          </div>

          <div class="form-group">
            <label>Postcode *</label>
            <input type="text" id="postcode" name="postcode" placeholder="Postcode" maxlength="10" required/>
          </div>

          <div class="form-group">
            <label>Woonplaats *</label>
            <input type="text" id="woonplaats" name="woonplaats" placeholder="Woonplaats" maxlength="50" required/>
          </div>

          <div class="form-group">
            <label>Telefoonnummer *</label>
            <input type="text" id="telefoonnummer" name="telefoonnummer" placeholder="Telefoon" maxlength="20" required/>
          </div>

          <div class="form-group full">
            <label>E-mailadres *</label>
            <input type="email" id="email" name="email" placeholder="Email" maxlength="200" required/>
          </div>
        </div>
      </div>

      <div class="info-section">
        <h3>Gezinssamenstelling</h3>

        <div class="form-grid three">
          <div class="form-group">
            <label>Aantal Volwassenen (&gt;18 jaar)</label>
            <input type="number" id="volwassenen" min="0" value="0" name="volwassenen"/>
          </div>

          <div class="form-group">
            <label>Aantal Kinderen (&gt;2 jaar)</label>
            <input type="number" id="kinderen" min="0" value="0" name="kinderen"/>
          </div>

          <div class="form-group">
            <label>Aantal Baby's (&le;2 jaar)</label>
            <input type="number" id="babies" min="0" value="0" name="babies"/>
          </div>
        </div>
      </div>

      <div class="info-section">
        <h3>Wensen en Beperkingen</h3>

        <div class="form-group">
          <label>Dieetwensen</label>
          <div class="checkbox-row">
            <?php foreach ($opgeslagenWensen as $wens): ?>
            <label class="check-item"><input  type="checkbox" value="<?= $wens['idKlantenwensen'] ?>" name="wensen[]"/> <?= htmlspecialchars($wens['klantenwens']) ?></label>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="form-group">
          <label>Allergieën</label>
          <label class="check-item"><input type="checkbox" id="hasAllergy"/> Heeft de klant allergieën?</label>

          <div id="allergyContainer" class="hidden">
            <input type="text" id="allergyInput" placeholder="Bijv: pinda, gluten, lactose" />
          </div>
        </div>
      </div>
    </div>

    <div class="modal-footer right">
      <button class="btn-light" data-close>Annuleren</button>
      <button class="btn-green" id="saveClientBtn">Toevoegen</button>
    </div>
  </div>

  <script src="script/klanten.js"></script>
</body>
</html>