<?php
session_start();
// if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
//     die("Page not available");
// }

require_once __DIR__ . '/common/dbconnection.php';

// Filters ophalen
$maand = $_GET['maand'] ?? date('F');
$jaar = $_GET['jaar'] ?? date('Y');
$type = $_GET['type'] ?? 'productcategorie';

$currentPage = 'rapportages.php';

// Converteer maand naam naar nummer
$maandNummers = [
    'Januari' => '01', 'Februari' => '02', 'Maart' => '03', 'April' => '04',
    'Mei' => '05', 'Juni' => '06', 'Juli' => '07', 'Augustus' => '08',
    'September' => '09', 'Oktober' => '10', 'November' => '11', 'December' => '12'
];
$maandNummer = $maandNummers[$maand] ?? date('m');

// Maak datum bereik voor de geselecteerde maand
$startDatum = "$jaar-$maandNummer-01";
$eindDatum = date('Y-m-t', strtotime($startDatum));

// Haal data op uit database - Productcategorie rapport
$data = [];
$totaalProducten = 0;
$totaalAantal = 0;

try {
    $sql = "
        SELECT 
            c.product_categorie as categorie,
            COUNT(p.idProducts) as aantal_producten,
            SUM(p.aantal) as totaal_aantal
        FROM Products p
        INNER JOIN Categories c ON p.Categories_idCategories = c.idCategories
        WHERE p.ontvangst_datum BETWEEN ? AND ?
        GROUP BY c.idCategories, c.product_categorie
        ORDER BY totaal_aantal DESC
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ss', $startDatum, $eindDatum);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_all(MYSQLI_ASSOC);
    
    // Bereken totalen
    foreach ($data as $row) {
        $totaalProducten += $row['aantal_producten'];
        $totaalAantal += $row['totaal_aantal'];
    }
    
    $stmt->close();
} catch (Exception $e) {
    $error = "Database fout: " . $e->getMessage();
}

// Voedselpakketten statistieken voor deze maand
$pakkettenCount = 0;
try {
    $sqlPakketten = "SELECT COUNT(*) as aantal FROM Voedselpakketten 
                     WHERE samenstellings_datum BETWEEN ? AND ?";
    $stmtPakketten = $conn->prepare($sqlPakketten);
    $stmtPakketten->bind_param('ss', $startDatum, $eindDatum);
    $stmtPakketten->execute();
    $resultPakketten = $stmtPakketten->get_result();
    $pakkettenCount = $resultPakketten->fetch_assoc()['aantal'] ?? 0;
    $stmtPakketten->close();
} catch (Exception $e) {
    // Stil falen
}

$conn->close();
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
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin-bottom: 20px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
}

.labels-row {
    display: flex;
    gap: 20px;
}

.labels-row label {
    font-size: 14px;
    color: #555;
    flex: 1;
}

.selects-row {
    display: flex;
    gap: 20px;
}

.selects-row select {
    padding: 6px 10px;
    border-radius: 6px;
    border: 1px solid #ccc;
    min-width: 220px;
    flex: 1;
}

/* REPORT */
.report-box {
    background: #fff;
    padding: 25px;
    border-radius: 10px;
    min-height: 200px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
}

.report-box h2 {
    margin-top: 0;
    margin-bottom: 30px;
}

.empty {
    text-align: center;
    color: #888;
    margin-top: 40px;
}

/* REPORT TABLE */
.report-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

.report-table th,
.report-table td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

.report-table th {
    background: #2c4a6b;
    color: white;
    font-weight: 600;
}

.report-table tbody tr:hover {
    background: #f5f5f5;
}

.report-table tfoot {
    background: #e8f4f8;
    font-weight: bold;
}

.total-row {
    background: #d4edda !important;
}

/* SUMMARY BOX */
.summary-box {
    margin-top: 30px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
    border-left: 4px solid #2c4a6b;
}

.summary-box h3 {
    margin-top: 0;
    color: #2c4a6b;
}

.summary-box p {
    margin: 8px 0;
}

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
            <form method="GET">
                
                <div class="labels-row">
                    <label>Rapport Type</label>
                    <label>Maand</label>
                    <label>Jaar</label>
                </div>

                <div class="selects-row">
                    <select name="type">
                        <option value="productcategorie">Maandoverzicht per Productcategorie</option>
                    </select>

                    <select name="maand">
                        <?php
                        $maanden = ["Januari","Februari","Maart","April","Mei","Juni","Juli","Augustus","September","Oktober","November","December"];
                        foreach ($maanden as $m) {
                            $selected = ($m == $maand) ? 'selected' : '';
                            echo "<option $selected>$m</option>";
                        }
                        ?>
                    </select>

                    <select name="jaar">
                        <?php
                        for ($i = 2024; $i <= 2030; $i++) {
                            $selected = ($i == $jaar) ? 'selected' : '';
                            echo "<option $selected>$i</option>";
                        }
                        ?>
                    </select>
                </div>

            </form>
        </div>

        <!-- REPORT -->
        <div class="report-box">
            <h2>Maandoverzicht per Productcategorie - <?= htmlspecialchars($maand) ?> <?= htmlspecialchars($jaar) ?></h2>
            
            <?php if (isset($error)): ?>
                <p class="error"><?= htmlspecialchars($error) ?></p>
            <?php elseif (empty($data)): ?>
                <p class="empty">Geen gegevens beschikbaar voor de geselecteerde periode</p>
            <?php else: ?>
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>Product Categorie</th>
                            <th>Aantal Producten</th>
                            <th>Totaal Aantal</th>
                            <th>Percentage</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data as $row): ?>
                            <?php 
                                $percentage = $totaalAantal > 0 
                                    ? round(($row['totaal_aantal'] / $totaalAantal) * 100, 1) 
                                    : 0;
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($row['categorie']) ?></td>
                                <td><?= htmlspecialchars($row['aantal_producten']) ?></td>
                                <td><?= htmlspecialchars($row['totaal_aantal']) ?></td>
                                <td><?= $percentage ?>%</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="total-row">
                            <td><strong>Totaal</strong></td>
                            <td><strong><?= $totaalProducten ?></strong></td>
                            <td><strong><?= $totaalAantal ?></strong></td>
                            <td><strong>100%</strong></td>
                        </tr>
                    </tfoot>
                </table>
                
                <div class="summary-box">
                    <h3>Samenvatting <?= htmlspecialchars($maand) ?> <?= htmlspecialchars($jaar) ?></h3>
                    <p><strong>Totaal producten ontvangen:</strong> <?= $totaalAantal ?></p>
                    <p><strong>Aantal verschillende producten:</strong> <?= $totaalProducten ?></p>
                    <p><strong>Voedselpakketten samengesteld:</strong> <?= $pakkettenCount ?></p>
                </div>
            <?php endif; ?>
        </div>
    </main>

</div>

<script>
// Auto refresh bij wijzigen
document.querySelectorAll("select").forEach(select => {
    select.addEventListener("change", () => {
        select.form.submit();
    });
});
</script>

</body>
</html>