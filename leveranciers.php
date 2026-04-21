<?php
session_start();
$currentPage = basename($_SERVER['PHP_SELF']);
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
        <tr>
          <td>Albert Heijn</td>
          <td>Jan de Vries</td>
          <td>jan@ah.nl</td>
          <td>020-1234567</td>
          <td>28-3-2026, 10:00</td>
          <td>Wekelijks</td>
          <td class="actions">
            <span class="edit"></span>
            <span class="delete"></span>
          </td>
        </tr>

        <tr>
          <td>Jumbo</td>
          <td>Maria Jansen</td>
          <td>maria@jumbo.nl</td>
          <td>030-9876543</td>
          <td>30-3-2026, 14:00</td>
          <td>Maandelijks</td>
          <td class="actions">
            <span class="edit"></span>
            <span class="delete"></span>
          </td>
        </tr>
      </tbody>
    </table>
  </div>

</div>

<!-- MODAL -->
<div class="modal-overlay" id="modal">
  <div class="modal">

    <div class="modal-header">
      <h3>Nieuwe Leverancier</h3>
      <span class="close" id="closeModal">&times;</span>
    </div>

    <form>
      <div class="form-row">
        <div class="form-group">
          <label>Bedrijfsnaam *</label>
          <input type="text" id="bedrijf">
        </div>

        <div class="form-group">
          <label>Adres *</label>
          <input type="text" id="adres">
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Naam Contactpersoon *</label>
          <input type="text" id="contact">
        </div>

        <div class="form-group">
          <label>E-mailadres *</label>
          <input type="email" id="email">
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Telefoonnummer *</label>
          <input type="text" id="telefoon">
        </div>

        <div class="form-group">
          <label>Eerstvolgende Levering *</label>
          <input type="datetime-local" id="levering">
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Leverfrequentie *</label>
          <select id="frequentie">
            <option value="dagelijks">Dagelijks</option>
            <option value="wekelijks">Wekelijks</option>
            <option value="maandelijks">Maandelijks</option>
          </select>
        </div>
      </div>
        <button type="button" class="btn-cancel" id="cancelModal">Annuleren</button>
        <button type="submit" class="btn-save">Toevoegen</button>
      </div>
    </form>

  </div>
</div>

<script>
const modal = document.getElementById("modal");
document.getElementById("openModal").onclick = () => modal.style.display = "flex";
document.getElementById("closeModal").onclick = () => modal.style.display = "none";
document.getElementById("cancelModal").onclick = () => modal.style.display = "none";

window.onclick = (e) => {
  if (e.target === modal) {
    modal.style.display = "none";
  }
};

// Form submit handler
const form = document.querySelector('form');
form.addEventListener('submit', (e) => {
  e.preventDefault();
  const leverancier = {
    bedrijf: document.getElementById('bedrijf').value,
    adres: document.getElementById('adres').value,
    contact: document.getElementById('contact').value,
    email: document.getElementById('email').value,
    telefoon: document.getElementById('telefoon').value,
    levering: document.getElementById('levering').value,
    frequentie: document.getElementById('frequentie').value
  };
  let leveranciers = JSON.parse(localStorage.getItem('leveranciers')) || [];
  leveranciers.push(leverancier);
  localStorage.setItem('leveranciers', JSON.stringify(leveranciers));
  addRowToTable(leverancier);
  modal.style.display = 'none';
  form.reset();
});

function addRowToTable(lev) {
  const tbody = document.querySelector('tbody');
  const tr = document.createElement('tr');
  tr.innerHTML = `
    <td>${lev.bedrijf}</td>
    <td>${lev.contact}</td>
    <td>${lev.email}</td>
    <td>${lev.telefoon}</td>
    <td>${lev.levering}</td>
    <td>${lev.frequentie}</td>
    <td class="actions"><span class="edit"></span><span class="delete"></span></td>
  `;
  tbody.appendChild(tr);
}

// Load saved leveranciers on page load
window.addEventListener('load', () => {
  const leveranciers = JSON.parse(localStorage.getItem('leveranciers')) || [];
  leveranciers.forEach(addRowToTable);
});
</script>

</body>
</html>