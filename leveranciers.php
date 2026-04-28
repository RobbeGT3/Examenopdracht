<?php
session_start();
$currentPage = basename($_SERVER['PHP_SELF']);
require_once __DIR__ . '/common/dbconnection.php';

$stmt = $conn->prepare("SELECT * FROM Leverancier ORDER BY bedrijfsnaam ASC;");
$stmt->execute();
$result = $stmt->get_result();
$leveranciers = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="nl">
<head>
<meta charset="UTF-8">
<title>Leveranciers</title>
<link rel="stylesheet" href="styles/styles.css">

<style>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  font-family: 'Segoe UI', sans-serif;
}

body {
  display: flex;
  background-color: #f4f6f9;
}

/* MAIN */
.main {
  flex: 1;
  padding: 40px;
}

/* TOPBAR */
.topbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 25px;
  background: transparent;
}

.topbar h2 {
  color: #2c3e50;
}

/* BUTTON */
.btn-add {
  background: #2e7d32;
  color: white;
  padding: 12px 20px;
  border-radius: 10px;
  border: none;
  cursor: pointer;
}

/* TABLE */
.card {
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

table {
  width: 100%;
  border-collapse: collapse;
}

thead {
  background: #2c4a6b;
  color: white;
}

thead th {
  padding: 15px;
  text-align: left;
}

tbody td {
  padding: 15px;
  border-top: 1px solid #eee;
}

tbody tr:hover {
  background: #f9fafb;
}

/* ACTIONS */
.actions {
  display: flex;
  gap: 10px;
}

.edit { color: #2c4a6b; }
.delete { color: red; }


.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0,0,0,0.6);
  display: none;
  align-items: center;
  justify-content: center;
}

.modal {
  background: #fff;
  width: 650px;
  border-radius: 12px;
  padding: 25px;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  margin-bottom: 20px;
}

.close {
  cursor: pointer;
  font-size: 22px;
}

.form-row {
  display: flex;
  gap: 15px;
  margin-bottom: 15px;
}

.form-group {
  flex: 1;
}

.form-group label {
  display: block;
  margin-bottom: 5px;
}

.form-group input {
  width: 100%;
  padding: 10px;
  border-radius: 8px;
  border: 1px solid #ccc;
}

.form-group select {
  width: 100%;
  padding: 10px;
  border-radius: 8px;
  border: 1px solid #ccc;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 10px;
}

.btn-save {
  background: #2e7d32;
  color: white;
  padding: 10px 18px;
  border-radius: 8px;
  border: none;
  cursor: pointer;
}

.btn-cancel {
  background: #ccc;
  padding: 10px 18px;
  border-radius: 8px;
  border: none;
  cursor: pointer;
}
</style>
</head>

<body>

<!-- SIDEBAR -->
<?php include 'sidebar.php'; ?>

<!-- MAIN -->
<div class="main">

  <div class="topbar">
    <h2>Leveranciers</h2>
    <button class="btn-add" id="openModal">+ Nieuwe Leverancier</button>
  </div>

  <div class="card">
    <table>
      <thead>
        <tr>
          <th>Bedrijfsnaam</th>
          <th>Contactpersoon</th>
          <th>E-mail</th>
          <th>Telefoon</th>
          <th>Volgende Levering</th>
          <th>Frequentie</th>
          <th>Acties</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($leveranciers as $lev): ?>
        <tr data-id="<?= $lev['idLeverancier'] ?>" data-adres="<?= htmlspecialchars($lev['adres'] ?? '') ?>">
          <td><?= htmlspecialchars($lev['bedrijfsnaam']) ?></td>
          <td><?= htmlspecialchars($lev['contactpersoon']) ?></td>
          <td><?= htmlspecialchars($lev['email']) ?></td>
          <td><?= htmlspecialchars($lev['telefoon']) ?></td>
          <td><?= htmlspecialchars($lev['volgende_levering']) ?></td>
          <td><?= htmlspecialchars($lev['leverfrequentie']) ?></td>
          <td class="actions">
            <button class="btn-edit-lev" data-id="<?= $lev['idLeverancier'] ?>">✏️</button>
            <button class="btn-delete-lev" data-id="<?= $lev['idLeverancier'] ?>">🗑️</button>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

</div>

<!-- Nieuwe Leverancier Modal -->
<div class="modal-overlay" id="modal">
  <div class="modal">

    <div class="modal-header">
      <h3 id="modalTitle">Nieuwe Leverancier</h3>
      <span class="close" id="closeModal">&times;</span>
    </div>

    <form id="leverancierForm">
      <input type="hidden" id="leverancierId">
      <div class="form-row">
        <div class="form-group">
          <label>Bedrijfsnaam *</label>
          <input type="text" id="bedrijf" required>
        </div>

        <div class="form-group">
          <label>Adres *</label>
          <input type="text" id="adres" required>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Naam Contactpersoon *</label>
          <input type="text" id="contact" required>
        </div>

        <div class="form-group">
          <label>E-mailadres *</label>
          <input type="email" id="email" required>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Telefoonnummer *</label>
          <input type="text" id="telefoon" required>
        </div>

        <div class="form-group">
          <label>Eerstvolgende Levering *</label>
          <input type="datetime-local" id="levering" required>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Leverfrequentie *</label>
          <select id="frequentie" required>
            <option value="dagelijks">Dagelijks</option>
            <option value="wekelijks">Wekelijks</option>
            <option value="maandelijks">Maandelijks</option>
          </select>
        </div>
      </div>
      <div class="modal-actions">
        <button type="button" class="btn-cancel" id="cancelModal">Annuleren</button>
        <button type="submit" class="btn-save" id="submitBtn">Toevoegen</button>
      </div>
    </form>

  </div>
</div>

<script>
const modal = document.getElementById("modal");
const form = document.getElementById('leverancierForm');
let isEditMode = false;

// Open modal voor nieuwe leverancier
document.getElementById("openModal").onclick = () => {
  isEditMode = false;
  document.getElementById('modalTitle').textContent = 'Nieuwe Leverancier';
  document.getElementById('submitBtn').textContent = 'Toevoegen';
  document.getElementById('leverancierId').value = '';
  form.reset();
  modal.style.display = "flex";
};

document.getElementById("closeModal").onclick = () => modal.style.display = "none";
document.getElementById("cancelModal").onclick = () => modal.style.display = "none";

window.onclick = (e) => {
  if (e.target === modal) {
    modal.style.display = "none";
  }
};

// Form submit handler
form.addEventListener('submit', (e) => {
  e.preventDefault();

  const data = {
    id: document.getElementById('leverancierId').value || null,
    bedrijfsnaam: document.getElementById('bedrijf').value,
    adres: document.getElementById('adres').value,
    contactpersoon: document.getElementById('contact').value,
    email: document.getElementById('email').value,
    telefoon: document.getElementById('telefoon').value,
    volgende_levering: document.getElementById('levering').value,
    leverfrequentie: document.getElementById('frequentie').value
  };

  const url = isEditMode ? 'actions/updateLeverancier.php' : 'actions/createLeverancier.php';

  fetch(url, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(data)
  })
  .then(() => {
    modal.style.display = 'none';
    location.reload();
  })
  .catch(error => {
    console.error('Error:', error);
    alert('Er is een fout opgetreden.');
  });
});

// Bewerken
document.querySelectorAll('.btn-edit-lev').forEach(btn => {
  btn.addEventListener('click', function() {
    const row = this.closest('tr');
    const id = this.dataset.id;

    isEditMode = true;
    document.getElementById('modalTitle').textContent = 'Leverancier Bewerken';
    document.getElementById('submitBtn').textContent = 'Bijwerken';
    document.getElementById('leverancierId').value = id;

    // Vul formulier met huidige data
    const cells = row.querySelectorAll('td');
    document.getElementById('bedrijf').value = cells[0].textContent;
    document.getElementById('contact').value = cells[1].textContent;
    document.getElementById('email').value = cells[2].textContent;
    document.getElementById('telefoon').value = cells[3].textContent;
    document.getElementById('levering').value = cells[4].textContent.replace(' ', 'T');
    document.getElementById('frequentie').value = cells[5].textContent.toLowerCase();

    // Adres ophalen uit data-attribuut of hidden field
    document.getElementById('adres').value = row.dataset.adres || '';

    modal.style.display = "flex";
  });
});

// Verwijderen
document.querySelectorAll('.btn-delete-lev').forEach(btn => {
  btn.addEventListener('click', function() {
    const id = this.dataset.id;
    const row = this.closest('tr');
    const naam = row.querySelector('td').textContent;

    if (!confirm(`Weet je zeker dat je leverancier "${naam}" wilt verwijderen?`)) return;

    fetch('actions/deleteLeverancier.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: id })
    })
    .then(() => location.reload())
    .catch(error => {
      console.error('Error:', error);
      alert('Er is een fout opgetreden bij het verwijderen.');
    });
  });
});
</script>

</body>
</html>