<?php
// Deze pagina toont alle leveranciers uit de database
// en laat je nieuwe leveranciers toevoegen

// Start de PHP sessie (nodig voor login)
session_start();

// Bepaal welke pagina dit is (voor het menu)
$currentPage = basename($_SERVER['PHP_SELF']);

// Verbind met de database
require_once __DIR__ . '/common/dbconnection.php';

$sql = "
    SELECT 
        l.idLeverancier,
        l.bedrijfsnaam,
        l.contactpersoon,
        l.`e-mailadres` as email,
        l.telefoonnummer,
        l.adres,
        l.postcode,
        l.plaats,
        -- Subquery: vind de eerstvolgende levering die nog niet is geweest
        (SELECT MIN(lev.leverings_datum) 
         FROM Leveringen lev 
         WHERE lev.Leverancier_idLeverancier = l.idLeverancier 
         AND lev.leverings_datum > NOW()
        ) as eerstvolgende_levering
    FROM Leverancier l
    ORDER BY l.bedrijfsnaam
";

// Voer de query uit
$result = mysqli_query($conn, $sql);

// Zet de resultaten in een array
$leveranciers = [];
while ($row = mysqli_fetch_assoc($result)) {
    $leveranciers[] = $row;
}

// Sluit de database verbinding
$conn->close();

function formatDatum($datum) {
    // Als er geen datum is, toon "Geen gepland"
    if (!$datum) return 'Geen gepland';
    
    // Maak een DateTime object en formatteer het
    $dt = new DateTime($datum);
    return $dt->format('d-m-Y H:i'); // bv: 15-05-2025 14:30
}
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

.btn-edit, .btn-delete {
    background: none;
    border: none;
    cursor: pointer;
    font-size: 18px;
    padding: 5px;
}

.btn-edit:hover {
    background: #e3f2fd;
    border-radius: 4px;
}

.btn-delete:hover {
    background: #ffebee;
    border-radius: 4px;
}


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
          <th>Adres</th>
          <th>Postcode</th>
          <th>Plaats</th>
          <th>Eerstvolgende Levering</th>
          <th>Acties</th>
        </tr>
      </thead>
      <tbody id="leveranciersTableBody">
        <?php foreach ($leveranciers as $lev): ?>
        <tr data-id="<?= $lev['idLeverancier'] ?>">
          <td><?= htmlspecialchars($lev['bedrijfsnaam'] ?? '') ?></td>
          <td><?= htmlspecialchars($lev['contactpersoon'] ?? '') ?></td>
          <td><?= htmlspecialchars($lev['email'] ?? '') ?></td>
          <td><?= htmlspecialchars($lev['telefoonnummer'] ?? '') ?></td>
          <td><?= htmlspecialchars($lev['adres'] ?? '') ?></td>
          <td><?= htmlspecialchars($lev['postcode'] ?? '') ?></td>
          <td><?= htmlspecialchars($lev['plaats'] ?? '') ?></td>
          <td><?= formatDatum($lev['eerstvolgende_levering']) ?></td>
          <td class="actions">
            <button class="btn-edit" onclick="editLeverancier(<?= $lev['idLeverancier'] ?>)" title="Bewerken">✏️</button>
            <button class="btn-delete" onclick="deleteLeverancier(<?= $lev['idLeverancier'] ?>)" title="Verwijderen">🗑️</button>
          </td>
        </tr>
        <?php endforeach; ?>
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
          <label>Postcode *</label>
          <input type="text" id="postcode">
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Plaats *</label>
          <input type="text" id="plaats">
        </div>
        <div class="form-group">
          <label>Leverfrequentie *</label>
          <select id="frequentie">
            <option value="dagelijks">Dagelijks</option>
            <option value="wekelijks" selected>Wekelijks</option>
            <option value="maandelijks">Maandelijks</option>
          </select>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Eerste Levering *</label>
          <input type="datetime-local" id="eersteLevering" value="2025-01-01T09:00">
        </div>
      </div>
      <div class="modal-actions">
        <button type="button" class="btn-cancel" id="cancelModal">Annuleren</button>
        <button type="submit" class="btn-save">Toevoegen</button>
      </div>
    </form>

  </div>
</div>

<script>

// Haal HTML elementen op die we nodig hebben
const modal = document.getElementById("modal");          // Het popup venster
const form = document.querySelector('form');                // Het formulier

// Open de modal als je op "+ Nieuwe Leverancier" klikt
document.getElementById("openModal").onclick = () => {
    // Reset het formulier voor een nieuwe leverancier
    form.reset();
    delete form.dataset.editId; // Verwijder edit ID zodat we weten dat dit nieuw is
    document.querySelector('.modal-header h3').textContent = 'Nieuwe Leverancier';
    document.querySelector('.btn-save').textContent = 'Toevoegen';
    modal.style.display = "flex";
};

// Sluit de modal als je op het xje klikt
document.getElementById("closeModal").onclick = () => {
    modal.style.display = "none";
};

// Sluit de modal als je op Annuleren klikt
document.getElementById("cancelModal").onclick = () => {
    modal.style.display = "none";
};

// Sluit de modal als je ergens buiten de model zelf klikt
window.onclick = (e) => {
    if (e.target === modal) {
        modal.style.display = "none";
    }
};

form.addEventListener('submit', async (e) => {
    // Voorkom dat het formulier op de normale manier wordt verstuurd
    e.preventDefault();
    
    // Verzamel alle waarden uit het formulier
    // Haal datum+tijd op uit het datetime-local veld (format: YYYY-MM-DDTHH:MM)
    const eersteLevering = document.getElementById('eersteLevering').value.replace('T', ' ');
    
    // Check of we een bestaande leverancier bewerken (dan hebben we een ID)
    const isEditing = form.dataset.editId ? true : false;
    const leverancierId = form.dataset.editId || null;
    
    const leverancier = {
        bedrijfsnaam: document.getElementById('bedrijf').value,
        adres: document.getElementById('adres').value,
        contactpersoon: document.getElementById('contact').value,
        email: document.getElementById('email').value,
        telefoonnummer: document.getElementById('telefoon').value,
        postcode: document.getElementById('postcode').value,
        plaats: document.getElementById('plaats').value,
        leverfrequentie: document.getElementById('frequentie').value,
        eersteLevering: eersteLevering
    };
    
    // Als we bewerken, voeg het ID toe
    if (isEditing) {
        leverancier.id = leverancierId;
    }
    
    try {
        // Kies de juiste URL: edit voor bewerken, add voor nieuw
        const url = isEditing 
            ? 'actions/leverancier/editLeverancier.php' 
            : 'actions/addLeverancier.php';
        
        // Stuur de data naar de server via AJAX (fetch API)
        const response = await fetch(url, {
            method: 'POST',                           // POST = verstuur data
            headers: { 'Content-Type': 'application/json' },  // We sturen JSON
            body: JSON.stringify(leverancier)         // Zet het object om naar JSON string
        });
        
        // Haal het resultaat op als JSON
        const result = await response.json();
        
        // Controleer of het gelukt is
        if (result.success) {
            alert('Leverancier opgeslagen in database!');
            location.reload();  // Herlaad de pagina om de nieuwe leverancier te zien
        } else {
            alert('Fout: ' + result.message);
        }
    } catch (error) {
        alert('Er is een fout opgetreden: ' + error.message);
    }
});

async function deleteLeverancier(id) {
    // Vraag eerst om bevestiging
    if (!confirm('Weet je zeker dat je deze leverancier wilt verwijderen?')) return;
    
    try {
        // Stuur verwijder request naar de server (juiste URL)
        const response = await fetch('actions/leverancier/deleteLeverancier.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id })  // Alleen het ID is nodig
        });
        
        // Debug: toon de raw response in console
        const responseText = await response.text();
        console.log('Server response:', responseText);
        
        // Parse de JSON response
        let result;
        try {
            result = JSON.parse(responseText);
        } catch (e) {
            alert('Ongeldige response van server:\n' + responseText.substring(0, 200));
            return;
        }
        
        if (result.success) {
            // Verwijder de rij uit de tabel (zonder pagina te herladen)
            document.querySelector(`tr[data-id="${id}"]`).remove();
            alert('Leverancier verwijderd!');
        } else {
            alert('Fout: ' + result.message);
        }
    } catch (error) {
        alert('Er is een fout opgetreden: ' + error.message);
        console.error('Delete error:', error);
    }
}

function editLeverancier(id) {
    // Vind de juiste rij in de tabel
    const row = document.querySelector(`tr[data-id="${id}"]`);
    const cells = row.querySelectorAll('td');
    
    // Vul het formulier met de bestaande waarden
    document.getElementById('bedrijf').value = cells[0].textContent.trim();   // Bedrijfsnaam
    document.getElementById('contact').value = cells[1].textContent.trim();  // Contactpersoon
    document.getElementById('email').value = cells[2].textContent.trim();    // Email
    document.getElementById('telefoon').value = cells[3].textContent.trim(); // Telefoon
    document.getElementById('adres').value = cells[4].textContent.trim();    // Adres
    document.getElementById('postcode').value = cells[5].textContent.trim(); // Postcode
    document.getElementById('plaats').value = cells[6].textContent.trim();   // Plaats
    
    // Converteer de getoonde datum (bv "15-05-2025 14:30") naar datetime-local formaat ("2025-05-15T14:30")
    const datumText = cells[7].textContent.trim(); // Eerstvolgende levering kolom
    const datetimeInput = document.getElementById('eersteLevering');
    
    if (datumText && datumText !== 'Geen' && datumText !== 'Geen gepland' && datumText !== '') {
        const parts = datumText.split(' ');
        if (parts.length === 2) {
            const [dag, maand, jaar] = parts[0].split('-');
            const tijd = parts[1];
            // Zorg dat dag/maand/jaar 2 cijfers hebben voor het formaat
            const dagFormat = dag.padStart(2, '0');
            const maandFormat = maand.padStart(2, '0');
            const jaarFormat = jaar.length === 2 ? '20' + jaar : jaar;
            const datetimeLocal = `${jaarFormat}-${maandFormat}-${dagFormat}T${tijd}`;
            datetimeInput.value = datetimeLocal;
            console.log('Datum gezet:', datetimeLocal);
        } else {
            datetimeInput.value = '';
        }
    } else {
        datetimeInput.value = '';
    }
    
    // Reset frequentie naar default (wekelijks) - kan aangepast worden door gebruiker
    document.getElementById('frequentie').value = 'wekelijks';
    
    // Sla het ID op zodat we weten dat we bewerken, niet toevoegen
    form.dataset.editId = id;
    
    // Verander de titel en knoptekst
    document.querySelector('.modal-header h3').textContent = 'Leverancier Bewerken';
    document.querySelector('.btn-save').textContent = 'Opslaan';
    
    // Open de modal
    modal.style.display = "flex";
}
</script>

</body>
</html>