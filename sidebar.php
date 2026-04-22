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
      <a href="dashboard.php" class="menu-btn <?= ($currentPage == 'dashboard.php') ? 'active' : '' ?>">
        <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M3 10.5 12 3l9 7.5"></path>
          <path d="M5 9.5V21h14V9.5"></path>
          <path d="M9 21v-6h6v6"></path>
        </svg>
        <span>Dashboard</span>
      </a>

      <?php if ($_SESSION['userrole'] === 'Magazijnmedewerker' || $_SESSION['userrole'] === 'Directie'): ?>
      <a href="leverancier.php" class="menu-btn <?= ($currentPage == 'leverancier.php') ? 'active' : '' ?>">
        <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <rect x="2" y="7" width="11" height="10" rx="2"></rect>
          <path d="M13 10h4l3 3v4h-7"></path>
          <circle cx="7.5" cy="18" r="1.5"></circle>
          <circle cx="17.5" cy="18" r="1.5"></circle>
        </svg>
        <span>Leveranciers</span>
      </a>
          

      <a href="voorraad.php" class="menu-btn <?= ($currentPage == 'voorraad.php') ? 'active' : '' ?>" data-page="voorraad">
        <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="m12 3 8 4.5v9L12 21l-8-4.5v-9L12 3Z"></path>
          <path d="m12 12 8-4.5"></path>
          <path d="m12 12-8-4.5"></path>
          <path d="M12 12v9"></path>
        </svg>
        <span>Voorraad</span>
      </a>

      <?php endif; ?>
          
      <?php if ( $_SESSION['userrole'] === 'Directie'): ?>
      <a href="klanten2.php" class="menu-btn <?= ($currentPage == 'klanten2.php') ? 'active' : '' ?>" data-page="klanten">
        <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2"></path>
          <circle cx="9.5" cy="7" r="4"></circle>
          <path d="M20 8v6"></path>
          <path d="M23 11h-6"></path>
        </svg>
        <span>Klanten</span>
      </a>
      <?php endif; ?>
      <?php if ($_SESSION['userrole'] === 'Vrijwilliger' || $_SESSION['userrole'] === 'Directie'): ?>
      <a href="voedselpakketten.php" class="menu-btn <?= ($currentPage == 'voedselpakketten.php') ? 'active' : '' ?>" data-page="voedselpakketten">
        <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <rect x="4" y="4" width="16" height="16" rx="3"></rect>
          <path d="M9 4v3"></path>
          <path d="M15 4v3"></path>
          <path d="M4 10h16"></path>
          <path d="M9 14h6"></path>
        </svg>
        <span>Voedselpakketten</span>
      </a>
      <?php endif; ?>
      <?php if ($_SESSION['userrole'] === 'Directie'): ?>
      <a href="rapportages.php" class="menu-btn <?= ($currentPage == 'rapportages.php') ? 'active' : '' ?>" data-page="rapportages">
        <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M4 20V10"></path>
          <path d="M10 20V4"></path>
          <path d="M16 20v-7"></path>
          <path d="M22 20V8"></path>
          <path d="M2 20h20"></path>
        </svg>
        <span>Rapportages</span>
      </a>

      <a href="gebruikers.php" class="menu-btn <?= ($currentPage == 'gebruikers.php') ? 'active' : '' ?>" data-page="gebruikers">
        <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="9" cy="8" r="4"></circle>
          <path d="M17 11v-1"></path>
          <path d="M17 17v-1"></path>
          <path d="M14 14h1"></path>
          <path d="M19 14h1"></path>
          <path d="M3 21v-2a6 6 0 0 1 6-6"></path>
        </svg>
        <span>Gebruikers</span>
      </a>
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