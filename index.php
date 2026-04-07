<?php

// if (isset($_GET['error'])) {
//     print($_SESSION['userrole']);
// } 
?>


<!DOCTYPE html>
<html lang="nl">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Voedselbank Systeem</title>

  <link rel="stylesheet" href="styles/styles.css" />

  <link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
  />
</head>
<body>
  <header class="topbar">
    <div class="topbar-title">
      <span>Food Bank App</span>
      <i class="bi bi-caret-down-fill"></i>
    </div>
  </header>

  <main class="page">
    <section class="login-card">
      <div class="login-header">
        <i class="bi bi-box-seam icon"></i>
        <h1>Voedselbank Systeem</h1>
      </div>

      <form action="actions/login.php" method="POST" class="login-form">
        <?php 
                if (isset($_GET['error'])) {
                    echo "<div class='error'>".htmlspecialchars($_GET['error'])."</div>";
                } 
                ?>
        <div class="form-group">
          <label for="username">Gebruikersnaam</label>
          <input type="text" id="username" name="username" required/>
        </div>

        <div class="form-group">
          <label for="password">Wachtwoord</label>
          <input type="password" id="password" name="password" required/>
        </div>

        <button type="submit" class="login-btn">Inloggen</button>
      </form>
    </section>
  </main>
</body>
</html>