<?php
session_start();
// Filters ophalen
$maand = $_GET['maand'] ?? 'Mei';
$jaar = $_GET['jaar'] ?? '2026';
$type = $_GET['type'] ?? 'productcategorie';

$currentPage = 'rapportages.php';

// Dummy data (nu leeg zoals screenshot)
$data = [];
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
            <h2>Maandoverzicht per Productcategorie</h2>

            <?php if (empty($data)): ?>
                <p class="empty">Geen gegevens beschikbaar voor de geselecteerde periode</p>
            <?php else: ?>
                <!-- toekomstige tabel -->
            <?php endif; ?>
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
