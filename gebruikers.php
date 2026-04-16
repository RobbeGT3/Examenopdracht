<?php
session_start();
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    die("Page not available");
}
$currentPage = 'gebruikers.php';
require_once  __DIR__. '/common/dbconnection.php';

$stmt1 = $conn->prepare("SELECT * FROM Gebruiker g
INNER JOIN Gebruikerrollen gr ON g.Gebruikerrollen_idGebruikerrollen = gr.idGebruikerrollen;");
$stmt1->execute();
$result1 = $stmt1->get_result();
$gebruikers = $result1->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Gebruikersbeheer</title>

    <link rel="stylesheet" href="styles/styles.css">
    <link rel="stylesheet" href="gebruiker.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
</head>

<body>
    <div class="app">

        <?php include 'sidebar.php' ?>

        <!-- MAIN -->
        <main class="main">

            <div class="header">
                <h1>Gebruikersbeheer</h1>
                <button class="btn">+ Nieuwe Gebruiker</button>
            </div>

            <!-- Rollen -->
            <section class="card">
                <h3>Rechten per Rol</h3>

                <div class="roles">
                    <div class="box purple">
                        <h4>Directeur</h4>
                        <ul>
                            <li>Volledige toegang tot alle pagina's</li>
                            <li>Kan gebruikers beheren</li>
                            <li>Kan klanten goedkeuren</li>
                            <li>Toegang tot rapportages</li>
                        </ul>
                    </div>

                    <div class="box blue">
                        <h4>Magazijnmedewerker</h4>
                        <ul>
                            <li>Toegang tot producten</li>
                            <li>Toegang tot leveranciers</li>
                            <li>Kan voorraad beheren</li>
                        </ul>
                    </div>

                    <div class="box green">
                        <h4>Vrijwilliger</h4>
                        <ul>
                            <li>Kan voedselpakketten samenstellen</li>
                            <li>Kan producten selecteren</li>
                            <li>Kan pakketten uitgeven</li>
                        </ul>
                    </div>
                </div>
            </section>

            <!-- Table -->
            <section class="card">
                <table>
                    <thead>
                        <tr>
                            <th>Gebruikersnaam</th>
                            <th>Rol</th>
                            <th>Status</th>
                            <th>Acties</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($gebruikers as $gebruiker): ?>
                            <tr>
                                <td><?= htmlspecialchars($gebruiker['username']) ?></td>

                                <td>
                                    <?php
                                    $rol = $gebruiker['rolnaam'];

                                    $class = '';
                                    if ($rol === 'Directie') $class = 'purple';
                                    elseif ($rol === 'Magazijnmedewerker') $class = 'blue';
                                    elseif ($rol === 'Vrijwilliger') $class = 'green';
                                    ?>

                                    <span class="badge <?= $class ?>">
                                        <?= htmlspecialchars($rol) ?>
                                    </span>
                                </td>

                                <td class="status-cell">
                                    <?php
                                    $status = $gebruiker['status'];

                                    $dotClass = ($status === 'Actief') ? 'green' : 'red';
                                    ?>
                                    <span class="dot <?= $dotClass ?>"></span><?= ucfirst(htmlspecialchars($status)) ?>
                                </td>

                                <td class="actions">
                                    <button class="edit"></button>
                                    <button class="password"></button>
                                    <button class="delete"></button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>

        </main>
    </div>

    <!-- Popup Modal for New User -->
    <div id="userModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Nieuwe Gebruiker Aanmaken</h2>
                <span class="close">&times;</span>
            </div>
            <form id="userForm">
                <div class="form-group">
                    <label for="username">Gebruikersnaam</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="password">Wachtwoord</label>
                    <div class="password-input-group">
                        <input type="password" id="password" name="password" required>
                        <button type="button" class="btn-toggle" id="togglePassword"
                            title="Toon/Wachtwoord verbergen">o</button>
                        <button type="button" class="btn-generate" id="generatePassword">Genereer</button>
                    </div>
                </div>
                <div class="form-group">
                    <label for="email">E-mail</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="role">Rol</label>
                    <select id="role" name="role" required>
                        <option value="">Selecteer een rol</option>
                        <option value="directeur">Directeur</option>
                        <option value="magazijnmedewerker">Magazijnmedewerker</option>
                        <option value="vrijwilliger">Vrijwilliger</option>
                    </select>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn-cancel">Annuleren</button>
                    <button type="submit" class="btn-submit">Gebruiker Aanmaken</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Password Change Modal -->
    <div id="passwordModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Wachtwoord Wijzigen</h2>
                <span class="close-password">&times;</span>
            </div>
            <form id="passwordForm">
                <div class="form-group">
                    <label for="passwordUsername">Gebruiker</label>
                    <input type="text" id="passwordUsername" name="passwordUsername" readonly>
                </div>
                <div class="form-group">
                    <label for="newPassword">Nieuw Wachtwoord</label>
                    <div class="password-input-group">
                        <input type="password" id="newPassword" name="newPassword" required>
                        <button type="button" class="btn-toggle" id="toggleNewPassword"
                            title="Toon/Wachtwoord verbergen">o</button>
                        <button type="button" class="btn-generate" id="generateNewPassword">Genereer</button>
                    </div>
                </div>
                <div class="form-group">
                    <label for="confirmPassword">Bevestig Wachtwoord</label>
                    <div class="password-input-group">
                        <input type="password" id="confirmPassword" name="confirmPassword" required>
                        <button type="button" class="btn-toggle" id="toggleConfirmPassword"
                            title="Toon/Wachtwoord verbergen">o</button>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn-cancel-password">Annuleren</button>
                    <button type="submit" class="btn-submit">Wachtwoord Wijzigen</button>
                </div>
            </form>
        </div>
    </div>

    <script src="gebruiker.js"></script>
</body>

</html>