<?php
session_start();
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    die("Page not available");
}

$currentPage = basename($_SERVER['PHP_SELF']);
require_once __DIR__ . '/common/dbconnection.php';

// Filters ophalen uit URL
$maandNamen = [
    'Januari'=>'01','Februari'=>'02','Maart'=>'03','April'=>'04',
    'Mei'=>'05','Juni'=>'06','Juli'=>'07','Augustus'=>'08',
    'September'=>'09','Oktober'=>'10','November'=>'11','December'=>'12'
];
$maand       = $_GET['maand'] ?? 'Mei';
$jaar        = (int)($_GET['jaar']  ?? date('Y'));
$type        = $_GET['type']  ?? 'voorraad_per_categorie';
$maandNummer = $maandNamen[$maand] ?? date('m');
$startDatum  = "$jaar-$maandNummer-01";
$eindDatum   = date('Y-m-t', strtotime($startDatum));

$data    = [];
$summary = [];
$error   = null;

try {

// =====================================================
// RAPPORT: Voorraad per categorie (alle voorraad)
// =====================================================
if ($type === 'voorraad_per_categorie') {
    $stmt = $conn->prepare("
        SELECT
            c.product_categorie AS categorie,
            COUNT(p.idProducts)            AS aantal_producten,
            COALESCE(SUM(p.aantal), 0)     AS totaal_voorraad
        FROM Categories c
        LEFT JOIN Products p ON p.Categories_idCategories = c.idCategories
        GROUP BY c.idCategories, c.product_categorie
        ORDER BY c.product_categorie
    ");
    $stmt->execute();
    $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $summary['Totaal categorieën'] = count($data);
    $summary['Totaal producten']   = array_sum(array_column($data, 'aantal_producten'));
    $summary['Totaal voorraad']    = array_sum(array_column($data, 'totaal_voorraad'));
}

// =====================================================
// RAPPORT: Producten ontvangen per categorie (op maand)
// =====================================================
elseif ($type === 'ontvangen_per_maand') {
    $stmt = $conn->prepare("
        SELECT
            c.product_categorie AS categorie,
            COUNT(p.idProducts)        AS aantal_producten,
            COALESCE(SUM(p.aantal), 0) AS totaal_aantal
        FROM Products p
        INNER JOIN Categories c ON p.Categories_idCategories = c.idCategories
        WHERE p.ontvangst_datum BETWEEN ? AND ?
        GROUP BY c.idCategories, c.product_categorie
        ORDER BY totaal_aantal DESC
    ");
    $stmt->bind_param('ss', $startDatum, $eindDatum);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $summary['Categorieën']               = count($data);
    $summary['Verschillende producten']   = array_sum(array_column($data, 'aantal_producten'));
    $summary['Totaal eenheden ontvangen'] = array_sum(array_column($data, 'totaal_aantal'));
}

// =====================================================
// RAPPORT: Voedselpakketten per maand
// =====================================================
elseif ($type === 'pakketten_per_maand') {
    $stmt = $conn->prepare("
        SELECT
            v.idVoedselpakketten  AS pakket_id,
            v.samenstellings_datum,
            CONCAT(k.voornaam, ' ', k.achternaam) AS klant_naam,
            k.postcode,
            COUNT(vhp.Products_idProducts) AS aantal_producten
        FROM Voedselpakketten v
        LEFT JOIN Klanten k
            ON v.klanten_idKlanten = k.idKlanten
        LEFT JOIN Voedselpakketten_has_Products vhp
            ON vhp.Voedselpakketten_idVoedselpakketten = v.idVoedselpakketten
        WHERE v.samenstellings_datum BETWEEN ? AND ?
        GROUP BY v.idVoedselpakketten, v.samenstellings_datum,
                 k.voornaam, k.achternaam, k.postcode
        ORDER BY v.samenstellings_datum DESC
    ");
    $stmt->bind_param('ss', $startDatum, $eindDatum);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $summary['Pakketten uitgedeeld'] = count($data);
    $summary['Unieke klanten']       = count(array_unique(array_column($data, 'klant_naam')));
    $summary['Totaal productregels'] = array_sum(array_column($data, 'aantal_producten'));
}

// =====================================================
// RAPPORT: Klantenoverzicht
// =====================================================
elseif ($type === 'klantenoverzicht') {
    $stmt = $conn->prepare("
        SELECT
            k.idKlanten,
            CONCAT(k.voornaam, ' ', k.achternaam) AS naam,
            k.woonplaats,
            k.postcode,
            k.`status`,
            (k.aantal_volwassen + k.aantal_kinderen + k.aantal_babies) AS gezinsgrootte,
            COUNT(v.idVoedselpakketten) AS aantal_pakketten
        FROM Klanten k
        LEFT JOIN Voedselpakketten v ON v.klanten_idKlanten = k.idKlanten
        GROUP BY k.idKlanten
        ORDER BY k.achternaam, k.voornaam
    ");
    $stmt->execute();
    $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $goedgekeurd = array_filter($data, fn($r) => $r['status'] === 'Goedgekeurd');
    $summary['Totaal klanten']     = count($data);
    $summary['Goedgekeurd']        = count($goedgekeurd);
    $summary['Gem. gezinsgrootte'] = count($data) > 0
        ? round(array_sum(array_column($data, 'gezinsgrootte')) / count($data), 1)
        : 0;
}

// =====================================================
// RAPPORT: Leveringen per maand
// =====================================================
elseif ($type === 'leveringen_per_maand') {
    $stmt = $conn->prepare("
        SELECT
            lev.leverings_datum,
            l.bedrijfsnaam,
            l.contactpersoon,
            l.telefoonnummer
        FROM Leveringen lev
        INNER JOIN Leverancier l ON l.idLeverancier = lev.Leverancier_idLeverancier
        WHERE lev.leverings_datum BETWEEN ? AND ?
        ORDER BY lev.leverings_datum DESC
    ");
    $stmt->bind_param('ss', $startDatum, $eindDatum);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $summary['Leveringen']          = count($data);
    $summary['Unieke leveranciers'] = count(array_unique(array_column($data, 'bedrijfsnaam')));
}

} catch (Exception $e) {
    $error = "Database fout: " . $e->getMessage();
}

$conn->close();

$rapportTitels = [
    'voorraad_per_categorie' => 'Voorraad per Productcategorie',
    'ontvangen_per_maand'    => 'Producten Ontvangen per Maand',
    'pakketten_per_maand'    => 'Voedselpakketten per Maand',
    'klantenoverzicht'       => 'Klantenoverzicht',
    'leveringen_per_maand'   => 'Leveringen per Maand',
];
$rapportTitel = $rapportTitels[$type] ?? 'Rapportage';
?>
<!DOCTYPE html>
<html lang="nl">
<head>
<meta charset="UTF-8">
<title>Rapportages</title>
<link rel="stylesheet" href="styles/styles.css" />

<style>
body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #f4f6f9;
}

/* FILTER */
.filter-box {
    background: #fff;
    padding: 20px;
    border-radius: 10px;
    margin-bottom: 20px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
}

.filter-form {
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
    align-items: flex-end;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 4px;
    flex: 1;
    min-width: 160px;
}

.filter-group label {
    font-size: 13px;
    font-weight: 600;
    color: #555;
}

.filter-group select {
    padding: 8px 10px;
    border-radius: 6px;
    border: 1px solid #ccc;
    font-size: 14px;
}

.btn-filter {
    padding: 8px 20px;
    background: #2c4a6b;
    color: #fff;
    border: none;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    align-self: flex-end;
    white-space: nowrap;
}

.btn-filter:hover { background: #1e3550; }

/* SUMMARY CARDS */
.summary-cards {
    display: flex;
    gap: 16px;
    flex-wrap: wrap;
    margin-bottom: 20px;
}

.summary-card {
    background: #fff;
    border-radius: 10px;
    padding: 18px 24px;
    flex: 1;
    min-width: 140px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    text-align: center;
    border-top: 3px solid #2c4a6b;
}

.summary-card .sum-value {
    font-size: 28px;
    font-weight: 700;
    color: #2c4a6b;
}

.summary-card .sum-label {
    font-size: 13px;
    color: #666;
    margin-top: 4px;
}

/* REPORT */
.report-box {
    background: #fff;
    padding: 25px;
    border-radius: 10px;
    min-height: 200px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
}

.report-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.report-header h2 { margin: 0; }

.empty {
    text-align: center;
    color: #888;
    margin-top: 40px;
    font-size: 15px;
}

/* DATA TABLE */
.data-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}

.data-table th {
    background: #2c4a6b;
    color: white;
    padding: 12px;
    text-align: left;
    border-bottom: 2px solid #dee2e6;
    font-weight: 600;
    white-space: nowrap;
}

.data-table td {
    padding: 12px;
    border-bottom: 1px solid #dee2e6;
}

.data-table tr:hover { background: #f8f9fa; }

.total-row { background: #d4edda !important; }

.badge {
    display: inline-block;
    padding: 2px 10px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
}

.badge-green  { background: #dcfce7; color: #16a34a; }
.badge-yellow { background: #fef9c3; color: #ca8a04; }
.badge-gray   { background: #f1f5f9; color: #64748b; }

.error {
    color: #dc3545;
    padding: 15px;
    background: #f8d7da;
    border-radius: 5px;
}
</style>
</head>

<body>

<div class="app">

    <?php include 'sidebar.php' ?>

    <main class="main">
        <h1 class="page-title">Rapportages</h1>

        <!-- FILTER -->
        <div class="filter-box">
            <form method="GET" class="filter-form">
                <div class="filter-group">
                    <label>Rapport Type</label>
                    <select name="type">
                        <option value="voorraad_per_categorie" <?= $type === 'voorraad_per_categorie' ? 'selected' : '' ?>>Voorraad per Productcategorie</option>
                        <option value="ontvangen_per_maand"    <?= $type === 'ontvangen_per_maand'    ? 'selected' : '' ?>>Producten Ontvangen per Maand</option>
                        <option value="pakketten_per_maand"    <?= $type === 'pakketten_per_maand'    ? 'selected' : '' ?>>Voedselpakketten per Maand</option>
                        <option value="klantenoverzicht"       <?= $type === 'klantenoverzicht'       ? 'selected' : '' ?>>Klantenoverzicht</option>
                        <option value="leveringen_per_maand"  <?= $type === 'leveringen_per_maand'   ? 'selected' : '' ?>>Leveringen per Maand</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label>Maand</label>
                    <select name="maand">
                        <?php foreach (array_keys($maandNamen) as $m): ?>
                            <option value="<?= $m ?>" <?= $m === $maand ? 'selected' : '' ?>><?= $m ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label>Jaar</label>
                    <select name="jaar">
                        <?php for ($i = 2024; $i <= 2030; $i++): ?>
                            <option value="<?= $i ?>" <?= $i === $jaar ? 'selected' : '' ?>><?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                </div>

                <button type="submit" class="btn-filter">Toepassen</button>
            </form>
        </div>

        <!-- SUMMARY CARDS -->
        <?php if (!empty($summary)): ?>
        <div class="summary-cards">
            <?php foreach ($summary as $label => $value): ?>
            <div class="summary-card">
                <div class="sum-value"><?= htmlspecialchars((string)$value) ?></div>
                <div class="sum-label"><?= htmlspecialchars($label) ?></div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- REPORT -->
        <div class="report-box">
            <div class="report-header">
                <h2><?= htmlspecialchars($rapportTitel) ?></h2>
                <small style="color:#888"><?= htmlspecialchars($maand) ?> <?= $jaar ?></small>
            </div>

            <?php if ($error): ?>
                <p class="error"><?= htmlspecialchars($error) ?></p>

            <?php elseif (empty($data)): ?>
                <p class="empty">Geen gegevens beschikbaar voor de geselecteerde periode</p>

            <?php elseif ($type === 'voorraad_per_categorie'): ?>
                <table class="data-table">
                    <thead><tr>
                        <th>Categorie</th>
                        <th>Aantal Producten</th>
                        <th>Totaal Voorraad</th>
                    </tr></thead>
                    <tbody>
                        <?php foreach ($data as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['categorie']) ?></td>
                            <td><?= (int)$row['aantal_producten'] ?></td>
                            <td><?= (int)$row['totaal_voorraad'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot><tr class="total-row">
                        <td><strong>Totaal</strong></td>
                        <td><strong><?= array_sum(array_column($data, 'aantal_producten')) ?></strong></td>
                        <td><strong><?= array_sum(array_column($data, 'totaal_voorraad')) ?></strong></td>
                    </tr></tfoot>
                </table>

            <?php elseif ($type === 'ontvangen_per_maand'): ?>
                <?php $totaalAantal = array_sum(array_column($data, 'totaal_aantal')); ?>
                <table class="data-table">
                    <thead><tr>
                        <th>Categorie</th>
                        <th>Verschillende Producten</th>
                        <th>Totaal Eenheden</th>
                        <th>Percentage</th>
                    </tr></thead>
                    <tbody>
                        <?php foreach ($data as $row): ?>
                        <?php $pct = $totaalAantal > 0 ? round($row['totaal_aantal'] / $totaalAantal * 100, 1) : 0; ?>
                        <tr>
                            <td><?= htmlspecialchars($row['categorie']) ?></td>
                            <td><?= (int)$row['aantal_producten'] ?></td>
                            <td><?= (int)$row['totaal_aantal'] ?></td>
                            <td><?= $pct ?>%</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot><tr class="total-row">
                        <td><strong>Totaal</strong></td>
                        <td><strong><?= array_sum(array_column($data, 'aantal_producten')) ?></strong></td>
                        <td><strong><?= $totaalAantal ?></strong></td>
                        <td><strong>100%</strong></td>
                    </tr></tfoot>
                </table>

            <?php elseif ($type === 'pakketten_per_maand'): ?>
                <table class="data-table">
                    <thead><tr>
                        <th>#</th>
                        <th>Datum</th>
                        <th>Klant</th>
                        <th>Postcode</th>
                        <th>Producten</th>
                    </tr></thead>
                    <tbody>
                        <?php foreach ($data as $row): ?>
                        <tr>
                            <td><?= (int)$row['pakket_id'] ?></td>
                            <td><?= htmlspecialchars($row['samenstellings_datum']) ?></td>
                            <td><?= htmlspecialchars($row['klant_naam'] ?? '—') ?></td>
                            <td><?= htmlspecialchars($row['postcode']   ?? '—') ?></td>
                            <td><?= (int)$row['aantal_producten'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

            <?php elseif ($type === 'klantenoverzicht'): ?>
                <table class="data-table">
                    <thead><tr>
                        <th>Naam</th>
                        <th>Woonplaats</th>
                        <th>Postcode</th>
                        <th>Gezinsgrootte</th>
                        <th>Pakketten</th>
                        <th>Status</th>
                    </tr></thead>
                    <tbody>
                        <?php foreach ($data as $row):
                            $badgeClass = $row['status'] === 'Goedgekeurd'
                                ? 'badge-green'
                                : ($row['status'] === 'In behandeling' ? 'badge-yellow' : 'badge-gray');
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($row['naam']) ?></td>
                            <td><?= htmlspecialchars($row['woonplaats']) ?></td>
                            <td><?= htmlspecialchars($row['postcode']) ?></td>
                            <td><?= (int)$row['gezinsgrootte'] ?></td>
                            <td><?= (int)$row['aantal_pakketten'] ?></td>
                            <td><span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($row['status']) ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

            <?php elseif ($type === 'leveringen_per_maand'): ?>
                <table class="data-table">
                    <thead><tr>
                        <th>Datum</th>
                        <th>Leverancier</th>
                        <th>Contactpersoon</th>
                        <th>Telefoonnummer</th>
                    </tr></thead>
                    <tbody>
                        <?php foreach ($data as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['leverings_datum']) ?></td>
                            <td><?= htmlspecialchars($row['bedrijfsnaam']) ?></td>
                            <td><?= htmlspecialchars($row['contactpersoon']) ?></td>
                            <td><?= htmlspecialchars($row['telefoonnummer']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

            <?php endif; ?>
        </div>
    </main>

</div>

<script src="script/nav.js"></script>

</body>
</html>
