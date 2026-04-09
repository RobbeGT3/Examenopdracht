<?php
session_start();
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    die("Page not available");
}


$currentPage = basename($_SERVER['PHP_SELF']);
require_once  __DIR__. '/common/dbconnection.php';


$stmt1 = $conn->prepare("SELECT 
    k.idKlanten,
    k.voornaam,
    k.achternaam,
    k.adres,
    k.postcode,
    k.woonnplaats,
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
?>

<!DOCTYPE html>
<html lang="nl">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Voedselbank Dashboard</title>
  <link rel="stylesheet" href="styles/styles.css"/>
  <style>
    .klantenHeader {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 25px;
    }

    .klantenPage-title {
      font-size: 28px;
      font-weight: 600;
      color: #1f2d3d;
    }

    .btn-add {
      background-color: #2e7d32;
      color: white;
      border: none;
      padding: 10px 18px;
      border-radius: 8px;
      font-size: 14px;
      cursor: pointer;
    }

    .klantenCard-container {
      display: flex;
      gap: 25px;
      flex-wrap: wrap;
    }

    .klantenCard {
      background: white;
      border-radius: 12px;
      padding: 20px;
      width: 320px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.08);

      display: flex;
      flex-direction: column;
    }

    .klantenCard-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .klantenCard-title {
      font-size: 18px;
      font-weight: 600;
      color: #1f2d3d;
    }

    .check {
      color: green;
      font-size: 18px;
    }

    .klantenCard-sub {
      color: #6c757d;
      margin-bottom: 10px;
    }

    .klantenCard-info {
      font-size: 14px;
      margin-bottom: 5px;
    }

    .tags {
      margin-top: 10px;
      margin-bottom: 15px;
    }

    .tag {
      display: inline-block;
      padding: 4px 10px;
      border-radius: 6px;
      font-size: 12px;
      margin-right: 5px;
    }

    .tag-gray {
      background: #e9ecef;
    }

    .tag-red {
      background: #f8d7da;
      color: #842029;
    }

    .klantenCard-actions {
      margin-top: auto;
      display: flex;
      display: flex;
      gap: 10px;
    }

    .btn-edit {
      flex: 1;
      background: #2c4c73;
      color: white;
      border: none;
      padding: 10px;
      border-radius: 6px;
      cursor: pointer;
    }

    .btn-delete {
      background: #dc3545;
      color: white;
      border: none;
      padding: 10px 12px;
      border-radius: 6px;
      cursor: pointer;
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
            Plaats: <?= htmlspecialchars($klant['woonnplaats']) ?>
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

        
    </main>
  </div>
  <script src="script/nav.js"></script>
</body>
</html>