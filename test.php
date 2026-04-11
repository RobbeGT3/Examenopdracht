<?php
session_start();
// if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
//     die("Page not available");
// }


$currentPage = basename($_SERVER['PHP_SELF']);
require_once  __DIR__. '/common/dbconnection.php';


$stmt1 = $conn->prepare("SELECT 
    k.idKlanten,
    k.voornaam,
    k.achternaam,
    k.adres,
    k.postcode,
    k.woonplaats,
    k.telefoonnummer,
    k.`e-mailadres`,
    GROUP_CONCAT(DISTINCT kw.klantenwens) AS wensen,
    GROUP_CONCAT(DISTINCT ka.omschrijving) AS allergenen
FROM Klanten k
LEFT JOIN Klanten_has_Klantenwensen khkw 
    ON k.idKlanten = khkw.Klanten_idKlanten
LEFT JOIN Klantenwensen kw 
    ON khkw.Klantenwensen_idKlantenwensen = kw.idKlantenwensen
LEFT JOIN Klanten_allergenen ka 
    ON k.idKlanten = ka.Klanten_idKlanten
GROUP BY k.idKlanten;
");
$stmt1->execute();
$result1 = $stmt1->get_result();
$geregistreedeklanten = $result1->fetch_all(MYSQLI_ASSOC);


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
  <title>Voedselbank Dashboard</title>
  <link rel="stylesheet" href="styles/styles.css"/>
  <link rel="stylesheet" href="styles/klanten.css"/>
  <style>
    body {
      font-family: Arial, sans-serif;
    }

    /* Overlay */
    .modal-overlay {
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,0.5);
      display: none;
      align-items: center;
      justify-content: center;
      padding: 16px;
    }

    .modal-overlay.active {
      display: flex;
    }

    /* Modal */
    .modal {
      background: white;
      width: 100%;
      max-width: 900px;
      max-height: 85vh;
      border-radius: 10px;
      display: flex;
      flex-direction: column;
      overflow: hidden;
    }

    /* Header */
    .modal-header {
      display: flex;
      justify-content: space-between;
      padding: 20px;
      border-bottom: 1px solid #ddd;
      position: sticky;
      top: 0;
      background: white;
    }

    .close-btn {
      background: none;
      border: none;
      font-size: 18px;
      cursor: pointer;
    }

    /* Content */
    .modal-content {
      overflow-y: auto;
      padding: 20px;
    }

    /* Cards */
    .card {
      background: #f9f9f9;
      padding: 20px;
      border-radius: 10px;
      margin-bottom: 20px;
    }

    .card h3 {
      margin-bottom: 15px;
    }

    /* Grid */
    .grid-2 {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 15px;
    }

    .grid-3 {
      display: grid;
      grid-template-columns: 1fr 1fr 1fr;
      gap: 15px;
    }

    .full {
      grid-column: span 2;
    }

    /* Inputs */
    input {
      width: 100%;
      padding: 10px;
      border-radius: 6px;
      border: 1px solid #ccc;
    }

    /* Checkbox */
    .checkbox-group {
      display: flex;
      gap: 15px;
      margin-bottom: 15px;
    }

    /* Tags */
    .tags {
      margin: 10px 0;
    }

    .tag {
      display: inline-block;
      background: #ffdddd;
      padding: 5px 10px;
      border-radius: 20px;
      margin-right: 5px;
    }

    /* Buttons */
    .button-group button {
      margin: 5px;
      padding: 6px 10px;
      border: none;
      background: #eee;
      border-radius: 6px;
      cursor: pointer;
    }

    /* Actions */
    .actions {
      display: flex;
      justify-content: flex-end;
      gap: 10px;
    }

    .btn-muted {
      background: #eee;
      padding: 10px 20px;
      border: none;
      border-radius: 6px;
    }

    .btn-primary {
      background: #333;
      color: white;
      padding: 10px 20px;
      border: none;
      border-radius: 6px;
    }
  </style>
</head>
<body>
  <div class="app">
    <?php include 'sidebar.php' ?>
    <main class="main">

    <div class="klantenHeader">
      <h1 class="klantenPage-title">Klanten Beheer</h1>
      <button class="btn-add">+ Nieuwe Klant</button>
    </div>

    <div class="klantenCard-container">

      <?php foreach ($geregistreedeklanten as $klant): ?>

        <?php
          $wensen = !empty($klant['wensen']) ? explode(',', $klant['wensen']) : [];
          $allergenen = !empty($klant['allergenen']) ? explode(',', $klant['allergenen']) : [];

          $gefilterdeWensen = array_filter($wensen, function($wens) use ($allergenen) {
            $wens = trim($wens);

            if (!empty($allergenen) && str_starts_with(strtolower($wens), 'allergisch')) {
                return false; 
            }

            return true;
        });
        ?>

        <div class="klantenCard">
          <div class="klantenCard-header">
            <div class="klantenCard-title">
              Familie <?= htmlspecialchars($klant['achternaam']) ?>
            </div>
            <div class="check">✔</div>
          </div>

          <div class="klantenCard-sub">
            <?= htmlspecialchars($klant['postcode']) ?>
          </div>

          <div class="klantenCard-info">
            Adres: <?= htmlspecialchars($klant['adres']) ?>
          </div>

          <div class="klantenCard-info">
            Plaats: <?= htmlspecialchars($klant['woonplaats']) ?>
          </div>

          <div class="klantenCard-info">
            Tel: <?= htmlspecialchars($klant['telefoonnummer']) ?>
          </div>

          <div class="klantenCard-info">
            Email: <?= htmlspecialchars($klant['e-mailadres']) ?>
          </div>

          <?php if (!empty($wensen) || !empty($allergenen)): ?>
            <div class="tags">
              <?php foreach ($wensen as $wens): ?>
                <span class="tag tag-gray">
                  <?= htmlspecialchars(trim($wens)) ?>
                </span>
              <?php endforeach; ?>
              <?php foreach ($allergenen as $allergie): ?>
                <span class="tag tag-red">
                  <?= htmlspecialchars(trim($allergie)) ?>
                </span>
              <?php endforeach; ?>

            </div>
          <?php endif; ?>

          <div class="klantenCard-actions">
            <button class="btn-edit">Bewerken</button>
            <button class="btn-delete">🗑</button>
          </div>
        </div>

      <?php endforeach; ?>

    </div>


    <!-- popup element -->
    <div class="modal-overlay">
      <div class="modal">

        <div class="modal-header">
          <h2>Nieuwe Klant</h2>
          <button class="close-btn">✕</button>
        </div>

        <div class="modal-content">
          <form>

            <div class="card">
              <h3>Basis Informatie</h3>
              <div class="grid-2">
                <div>
                  <label>Voornaam *</label>
                  <input type="text" name="voornaam" required>
                </div>

                <div>
                  <label>Achternaam *</label>
                  <input type="text" name="achternaa" required>
                </div>

                <div>
                  <label>Adres *</label>
                  <input type="text" name="adres" required>
                </div>

                <div>
                  <label>Postcode *</label>
                  <input type="text" name="postcode" required>
                </div>

                <div>
                  <label>Woonplaats *</label>
                  <input type="text" name="woonplaats" required>
                </div>

                <div>
                  <label>Telefoonnummer *</label>
                  <input type="tel" name="Telefoonnummer" required>
                </div>

                <div class="full">
                  <label>E-mailadres *</label>
                  <input type="email" name="email" required>
                </div>
              </div>
            </div>

            <div class="card">
              <h3>Gezinssamenstelling</h3>
              <div class="grid-3">
                <div>
                  <label>Aantal Volwassenen</label>
                  <input type="number" min="0">
                </div>

                <div>
                  <label>Aantal Kinderen</label>
                  <input type="number" min="0">
                </div>

                <div>
                  <label>Aantal Baby's</label>
                  <input type="number" min="0">
                </div>
              </div>
            </div>
            <div class="card">
              <h3>Wensen en Beperkingen</h3>
              <label>Dieetwensen</label>
              <!-- <div class="checkbox-group">
                <label><input type="checkbox"> Geen varkensvlees</label>
                <label><input type="checkbox"> Vegetarisch</label>
                <label><input type="checkbox"> Veganistisch</label>
              </div> -->

              <div class="checkbox-group">
                <?php foreach ($opgeslagenWensen as $wens): ?>
                  <label>
                    <input type="checkbox" name="wensen[]" value="<?= $wens['idKlantenwensen'] ?>">
                    <?= htmlspecialchars($wens['klantenwens']) ?>
                  </label>
                <?php endforeach; ?>
              </div>

              <label>Allergieën</label>

              <div class="button-group">
                <button type="button">+ Gluten</button>
                <button type="button">+ Pinda's</button>
                <button type="button">+ Schaaldieren</button>
                <button type="button">+ Andere...</button>
              </div>
            </div>

            <div class="actions">
              <button type="button" class="btn-muted">Annuleren</button>
              <button type="submit" class="btn-primary">Toevoegen</button>
            </div>

          </form>
        </div>

      </div>
    </div>

        
    </main>
  </div>
  <script src="script/klanten.js" defer></script>
  <script src="script/nav.js" defer></script>

  
  
</body>
</html>