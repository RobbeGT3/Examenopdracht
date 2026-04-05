<?php
session_start();
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    die("Page not available");
}

require_once  __DIR__. '/common/dbconnection.php';

$aantalKlantenQuery = $conn->prepare("SELECT count(k.idKlanten) FROM Klanten k WHERE k.`status` = 'Goedgekeurd';");
$aantalKlantenQuery->execute();
$aantalKlantenResult = $aantalKlantenQuery->get_result();

$aantalProductenQuery = $conn->prepare("SELECT COUNT(p.idProducts) FROM Products p;");
$aantalProductenQuery->execute();
$aantalProductenResult = $aantalProductenQuery->get_result();

$aantalLeveranciersQuery = $conn->prepare("SELECT COUNT(l.idLeverancier) FROM Leverancier l;");
$aantalLeveranciersQuery->execute();
$aantalLeveranciersResult = $aantalLeveranciersQuery->get_result();

$aantalVoedselpakkettenQuery = $conn->prepare("SELECT COUNT(v.idVoedselpakketten) FROM Voedselpakketten v;");
$aantalVoedselpakkettenQuery->execute();
$aantalVoedselpakkettenResult = $aantalVoedselpakkettenQuery->get_result();

$aantalProducten = $aantalProductenResult->fetch_row()[0];
$aantalKlanten = $aantalKlantenResult->fetch_row()[0];
$aantalLeveranciers = $aantalLeveranciersResult->fetch_row()[0];
$aantalVoedselpaketten = $aantalVoedselpakkettenResult->fetch_row()[0];

$today = new DateTime();
$sevenDaysAgo = new DateTime();
$sevenDaysAgo->modify('-7 days');
$sevenDaysAgoFormatted = $sevenDaysAgo->format('Y-m-d');
$todayFormatted = $today->format('Y-m-d');


$stmt1 = $conn->prepare("SELECT * FROM Voedselpakketten v WHERE v.samenstellings_datum between ? AND ? ORDER BY v.samenstellings_datum DESC LIMIT 5;
");
$stmt1->bind_param('ss', $sevenDaysAgoFormatted, $todayFormatted);
$stmt1->execute();
$result1 = $stmt1->get_result();
$recenteVoedselpakketten = $result1->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="nl">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Voedselbank Dashboard</title>
  <link rel="stylesheet" href="styles/styles.css" />
</head>
<body>
  <div class="app">
    <aside class="sidebar">
      <div class="sidebar-top">
        <div class="brand">
          <div class="brand-icon"></div>
          <div class="brand-text">
            <h1>Voedselbank</h1>
            <p>
              <?php
                echo $_SESSION['userrole']
              ?>
            </p>
          </div>
        </div>

        <nav class="menu">
          <button class="menu-btn active" data-page="dashboard">
            <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M3 10.5 12 3l9 7.5"></path>
              <path d="M5 9.5V21h14V9.5"></path>
              <path d="M9 21v-6h6v6"></path>
            </svg>
            <span>Dashboard</span>
          </button>

          <?php if ($_SESSION['userrole'] === 'Magazijnmedewerker' || $_SESSION['userrole'] === 'Directie'): ?>
          <button class="menu-btn" data-page="leveranciers">
            <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect x="2" y="7" width="11" height="10" rx="2"></rect>
              <path d="M13 10h4l3 3v4h-7"></path>
              <circle cx="7.5" cy="18" r="1.5"></circle>
              <circle cx="17.5" cy="18" r="1.5"></circle>
            </svg>
            <span>Leveranciers</span>
          </button>
          

          <button class="menu-btn" data-page="voorraad">
            <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="m12 3 8 4.5v9L12 21l-8-4.5v-9L12 3Z"></path>
              <path d="m12 12 8-4.5"></path>
              <path d="m12 12-8-4.5"></path>
              <path d="M12 12v9"></path>
            </svg>
            <span>Voorraad</span>
          </button>

          <?php endif; ?>
          
          <?php if ( $_SESSION['userrole'] === 'Directie'): ?>
          <button class="menu-btn" data-page="klanten">
            <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2"></path>
              <circle cx="9.5" cy="7" r="4"></circle>
              <path d="M20 8v6"></path>
              <path d="M23 11h-6"></path>
            </svg>
            <span>Klanten</span>
          </button>
          <?php endif; ?>
          <?php if ($_SESSION['userrole'] === 'Vrijwilliger' || $_SESSION['userrole'] === 'Directie'): ?>
          <button class="menu-btn" data-page="voedselpakketten">
            <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect x="4" y="4" width="16" height="16" rx="3"></rect>
              <path d="M9 4v3"></path>
              <path d="M15 4v3"></path>
              <path d="M4 10h16"></path>
              <path d="M9 14h6"></path>
            </svg>
            <span>Voedselpakketten</span>
          </button>
          <?php endif; ?>
          <?php if ($_SESSION['userrole'] === 'Directie'): ?>
          <button class="menu-btn" data-page="rapportages">
            <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M4 20V10"></path>
              <path d="M10 20V4"></path>
              <path d="M16 20v-7"></path>
              <path d="M22 20V8"></path>
              <path d="M2 20h20"></path>
            </svg>
            <span>Rapportages</span>
          </button>

          <button class="menu-btn" data-page="gebruikers">
            <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="9" cy="8" r="4"></circle>
              <path d="M17 11v-1"></path>
              <path d="M17 17v-1"></path>
              <path d="M14 14h1"></path>
              <path d="M19 14h1"></path>
              <path d="M3 21v-2a6 6 0 0 1 6-6"></path>
            </svg>
            <span>Gebruikers</span>
          </button>
          <?php endif; ?>
        </nav>
      </div>

      <div class="sidebar-bottom">
        <button class="logout-btn" id="logoutBtn">
          <svg class="logout-icon" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
            <path d="M10 17l5-5-5-5"></path>
            <path d="M15 12H3"></path>
          </svg>
          <span>Uitloggen</span>
        </button>
      </div>
    </aside>

    <main class="main">
      <div id="dashboardView" class="dashboard-view">
        <h1 class="page-title">Dashboard</h1>

        <section class="stats">
          <div class="card stat-card">
            <div>
              <h3>Totaal Producten</h3>
              <div class="value" id="totalProducts"><?php echo $aantalProducten  ?></div>
            </div>
            <div class="stat-icon blue">
              <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="m12 3 8 4.5v9L12 21l-8-4.5v-9L12 3Z"></path>
                <path d="m12 12 8-4.5"></path>
                <path d="m12 12-8-4.5"></path>
                <path d="M12 12v9"></path>
              </svg>
            </div>
          </div>

          <div class="card stat-card">
            <div>
              <h3>Actieve Klanten</h3>
              <div class="value" id="activeClients"><?php echo $aantalKlanten ?></div>
            </div>
            <div class="stat-icon green">
              <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="9" cy="8" r="4"></circle>
                <path d="M17 11c1.7 0 3-1.3 3-3s-1.3-3-3-3"></path>
                <path d="M3 20v-1a6 6 0 0 1 6-6 6 6 0 0 1 6 6v1"></path>
                <path d="M17 20v-1c0-1.9-.9-3.6-2.3-4.7"></path>
              </svg>
            </div>
          </div>

          <div class="card stat-card">
            <div>
              <h3>Leveranciers</h3>
               <div class="value" id="suppliers"><?php echo $aantalLeveranciers ?></div>
            </div>
            <div class="stat-icon purple">
              <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="2" y="7" width="11" height="10" rx="2"></rect>
                <path d="M13 10h4l3 3v4h-7"></path>
                <circle cx="7.5" cy="18" r="1.5"></circle>
                <circle cx="17.5" cy="18" r="1.5"></circle>
              </svg>
            </div>
          </div>

          <div class="card stat-card">
            <div>
              <h3>Uitgegeven Pakketten</h3>
              <div class="value" id="packagesGiven"><?php echo $aantalVoedselpaketten ?></div>
            </div>
            <div class="stat-icon orange">
              <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="4" y="4" width="16" height="16" rx="3"></rect>
                <path d="M9 4v3"></path>
                <path d="M15 4v3"></path>
                <path d="M4 10h16"></path>
              </svg>
            </div>
          </div>
        </section>

        <section class="double-cards">
          <div class="card info-card">
            <h2>Recente Voedselpakketten</h2>
            
            <?php if (count($recenteVoedselpakketten) > 0): ?>
              <ul>
                <?php foreach ($recenteVoedselpakketten as $pakket): ?>
                    <li>
                        Pakket #<?php echo $pakket['idVoedselpakketten']; ?> - 
                        <?php echo $pakket['samenstellings_datum']; ?>
                    </li>
                <?php endforeach; ?>
              </ul>
            <?php else: ?>
              <p id="recentPackagesText">Nog geen pakketten samengesteld</p>
            <?php endif; ?>
          </div>
          <div class="card info-card">
            <h2>Lage Voorraad Waarschuwing</h2>
            <p id="stockWarningText">Alle producten hebben voldoende voorraad</p>
          </div>
        </section>

        <section class="welcome-card">
          <h2>Welkom!</h2>
          <p>U heeft volledige toegang tot alle functionaliteiten van het systeem.</p>
        </section>
      </div>
      <?php if ($_SESSION['userrole'] === 'Magazijnmedewerker' || $_SESSION['userrole'] === 'Directie'): ?>
      <section id="leveranciers" class="placeholder-page">
        <h2>Leveranciers</h2>
        <p>Hier kun je straks leveranciers beheren, toevoegen en bewerken.</p>
      </section>

      <section id="voorraad" class="placeholder-page">
        <h2>Voorraad</h2>
        <p>Hier komt de voorraadlijst met producten, aantallen en meldingen bij lage voorraad.</p>
      </section>
      <?php endif; ?>
      <?php if ( $_SESSION['userrole'] === 'Directie'): ?>
      <section id="klanten" class="placeholder-page">
        <h2>Klanten</h2>
        <p>Hier kun je klantgegevens bekijken en beheren.</p>
      </section>
      <?php endif; ?>
      <?php if ($_SESSION['userrole'] === 'Vrijwilliger' || $_SESSION['userrole'] === 'Directie'): ?>
      <section id="voedselpakketten" class="placeholder-page">
        <h2>Voedselpakketten</h2>
        <p>Hier kun je voedselpakketten samenstellen en uitgeven.</p>
      </section>
      <?php endif; ?>
      <?php if ($_SESSION['userrole'] === 'Directie'): ?>
      <section id="rapportages" class="placeholder-page">
        <h2>Rapportages</h2>
        <p>Hier kun je rapportages en overzichten bekijken.</p>
      </section>

      <section id="gebruikers" class="placeholder-page">
        <!-- <h2>Gebruikers</h2>
        <p>Hier kun je gebruikers toevoegen, rollen instellen en accounts beheren.</p> -->
        <?php include 'pages/user.php'?>
      </section>
      <?php endif; ?>
    </main>
  </div>

  <!-- <script src="script.js"></script> -->
  <script src="dashboard.js"></script>
</body>
</html>