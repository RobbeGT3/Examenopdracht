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
    k.aantal_volwassen,
    k.aantal_kinderen,
    k.`aantal_baby's`,
    k.`status`,
    GROUP_CONCAT(DISTINCT kw.idKlantenwensen) AS wensen_ids,
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
            <button class="btn-edit" data-klant='<?= htmlspecialchars(json_encode($klant), ENT_QUOTES, 'UTF-8')?>'>Bewerken</button>
            <button class="btn-delete" data-id="<?= $klant['idKlanten'] ?>">🗑</button>
          </div>
        </div>

      <?php endforeach; ?>

    </div>


  <div class="modal-overlay">
  <div class="modal">
    
    <div class="modal-header">
      <h2>Nieuwe Klant</h2>
      <button type="button" class="close-btn">✕</button>
    </div>

    <div class="modal-content">
      <form id="klantForm">
        <div class="card">
          <div class="grid-2">
            <div>
              <label>Voornaam *</label>
              <input name="voornaam" placeholder="Voornaam" required>
            </div>
            <div>
              <label>Achternaam *</label>
              <input name="achternaam" placeholder="Achternaam" maxlength="50" required>
            </div>
            <div>
              <label>Adres *</label>
              <input name="adres" placeholder="Adres" maxlength="100" required>
            </div>
            <div>
              <label>Postcode *</label>
              <input name="postcode" placeholder="Postcode" maxlength="10" required>
            </div>
            <div>
              <label>Woonplaats *</label>
              <input name="woonplaats" placeholder="Woonplaats" maxlength="50" required>
            </div>
            <div>
              <label>Telefoonnummer *</label>
              <input name="telefoonnummer" placeholder="Telefoon" maxlength="20" required>
            </div>
            <div>
              <label>E-mailadres *</label>
              <input name="email" placeholder="Email" maxlength="200" required>
            </div>

          </div>
          
        </div>
        <div class="card">
          <h3>Gezinssamenstelling</h3>
          <div class="grid-3">
            <div>
              <label>Aantal Volwassenen</label>
              <input type="number" name="volwassenen">
            </div>
            <div>
              <label>Aantal Kinderen</label>
              <input type="number" name="kinderen">
            </div>
            <div>
              <label>Aantal Baby's</label>
              <input type="number" name="babys">
            </div>
          </div>
        </div>
        <div class="card">
          <h3>Dieetwensen</h3>
          <div class="wensenContainer">
            <?php foreach ($opgeslagenWensen as $wens): ?>
              <label>
                <input type="checkbox" name="wensen[]" value="<?= $wens['idKlantenwensen'] ?>">
                <?= htmlspecialchars($wens['klantenwens']) ?>
              </label>
            <?php endforeach; ?>
          </div>
          
          <h3>Allergieën</h3>
          <div id="allergieTags"></div>
          <input type="hidden" name="allergieën" id="allergieënInput">

          <div class="button-group">
            <button type="button" data-allergie="Gluten">+ Gluten</button>
            <button type="button" data-allergie="Pinda's">+ Pinda's</button>
            <button type="button" data-allergie="Schaaldieren">+ Schaaldieren</button>
            <button type="button" id="customBtn">+ Andere...</button>
          </div>

          <div id="customInput" style="display:none;">
            <input type="text" id="customAllergie" maxlength="100">
            <button type="button" id="addCustom">Toevoegen</button>
            <button type="button" id="cancelCustom">cancel</button>
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