<?php
session_start();
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    die("Page not available");
}

require_once __DIR__ . '/common/dbconnection.php';

$message = "";

/* KLANTEN */
$klanten = [];
$q = $conn->query("SELECT idKlanten, naam, klantnummer, volwassenen, kinderen, babys, dieetwensen FROM Klanten WHERE status='Goedgekeurd'");
if ($q) {
    $klanten = $q->fetch_all(MYSQLI_ASSOC);
}

/* PRODUCTEN */
$producten = [];
$q = $conn->query("SELECT idProduct, naam, categorie, aantal FROM Products WHERE aantal > 0");
if ($q) {
    $producten = $q->fetch_all(MYSQLI_ASSOC);
}

/* PAKKET OPSLAAN */
if (isset($_POST['create_package'])) {
    $idKlant = isset($_POST['idKlant']) ? (int)$_POST['idKlant'] : 0;

    if ($idKlant > 0) {
        $conn->query("
            INSERT INTO Voedselpakketten (idKlant, samenstellings_datum, status)
            VALUES ($idKlant, NOW(), 'In afwachting')
        ");
        header("Location: voedselpakketten.php?success=1");
        exit;
    } else {
        $message = "Selecteer eerst een klant.";
    }
}

/* UITGEVEN */
if (isset($_POST['give_package'])) {
    $id = isset($_POST['idPakket']) ? (int)$_POST['idPakket'] : 0;

    if ($id > 0) {
        $conn->query("
            UPDATE Voedselpakketten
            SET status='Uitgegeven', uitgegeven_datum=NOW()
            WHERE idVoedselpakketten=$id
        ");
        header("Location: voedselpakketten.php?issued=1");
        exit;
    }
}

/* PAKKETTEN */
$pakketten = [];
$q = $conn->query("
    SELECT v.*, k.naam, k.klantnummer
    FROM Voedselpakketten v
    JOIN Klanten k ON v.idKlant = k.idKlanten
    ORDER BY v.idVoedselpakketten DESC
");
if ($q) {
    $pakketten = $q->fetch_all(MYSQLI_ASSOC);
}

if (isset($_GET['success'])) {
    $message = "Pakket aangemaakt";
}

if (isset($_GET['issued'])) {
    $message = "Pakket uitgegeven";
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
  <meta charset="UTF-8">
  <title>Voedselpakketten</title>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="voedselpakketten.css">
</head>
<body>

<?php include 'sidebar.php'; ?>

<main class="main">

  <div class="page-header">
    <h1 class="page-title">Voedselpakketten</h1>
    <button type="button" class="new-package-btn" onclick="openModal()">+ Nieuw pakket</button>
  </div>

  <?php if ($message): ?>
    <div class="message-box"><?php echo htmlspecialchars($message); ?></div>
  <?php endif; ?>

  <?php if ($pakketten): ?>
    <div class="packages-grid">
      <?php foreach ($pakketten as $p): ?>
        <div class="package-card">
          <h2>Pakket #<?php echo (int)$p['idVoedselpakketten']; ?></h2>
          <p><?php echo htmlspecialchars($p['naam']); ?></p>
          <p><?php echo htmlspecialchars($p['klantnummer']); ?></p>

          <form method="POST">
            <input type="hidden" name="idPakket" value="<?php echo (int)$p['idVoedselpakketten']; ?>">

            <?php if ($p['status'] != 'Uitgegeven'): ?>
              <button type="submit" name="give_package" class="btn-give">Uitgeven</button>
            <?php else: ?>
              <button type="button" class="btn-give btn-disabled" disabled>Uitgegeven</button>
            <?php endif; ?>
          </form>
        </div>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <div class="empty-state">Nog geen pakketten</div>
  <?php endif; ?>

</main>

<div id="modal" class="modal-backdrop">
  <div class="modal-box">
    <h2>Nieuw pakket</h2>

    <form method="POST">
      <label for="idKlant">Klant</label>
      <select name="idKlant" id="idKlant" required>
        <option value="">-- kies --</option>
        <?php foreach ($klanten as $k): ?>
          <option value="<?php echo (int)$k['idKlanten']; ?>">
            <?php echo htmlspecialchars($k['naam']) . ' - ' . htmlspecialchars($k['klantnummer']); ?>
          </option>
        <?php endforeach; ?>
      </select>

      <br><br>

      <button type="submit" name="create_package" class="new-package-btn">Opslaan</button>
      <button type="button" onclick="closeModal()">Sluiten</button>
    </form>
  </div>
</div>

<script>
function openModal() {
  document.getElementById('modal').classList.add('active');
}

function closeModal() {
  document.getElementById('modal').classList.remove('active');
}
</script>

</body>
</html>