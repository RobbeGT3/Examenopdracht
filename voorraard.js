// Modal functionaliteit
function openModal() {
    const modal = document.getElementById('nieuwProductModal');
    modal.classList.add('active');
    document.body.style.overflow = 'hidden'; // Voorkom scrollen van achtergrond
}

function closeModal() {
    const modal = document.getElementById('nieuwProductModal');
    modal.classList.remove('active');
    document.body.style.overflow = 'auto'; // Herstel scrollen

    // Reset formulier
    const form = document.querySelector('.modal-form');
    if (form) {
        form.reset();
    }
}

function closeEditModal() {
    const modal = document.getElementById('productBewerkenModal');
    modal.classList.remove('active');
    document.body.style.overflow = 'auto'; // Herstel scrollen

    // Reset formulier
    const form = document.getElementById('edit-form');
    if (form) {
        form.reset();
    }
}

function openEditModal(rowIndex) {
    const table = document.querySelector('.inventory-table tbody');
    const rows = table.getElementsByTagName('tr');
    const row = rows[rowIndex];

    if (!row) return;

    // Haal huidige productgegevens op
    const cells = row.getElementsByTagName('td');
    const eanNummer = cells[0].textContent.trim();
    const productnaam = cells[1].textContent.trim();
    const categorie = cells[2].textContent.trim();
    const aantal = cells[3].textContent.trim();

    // Vul bewerkingsformulier
    document.getElementById('edit-row-index').value = rowIndex;
    document.getElementById('edit-ean-nummer').value = eanNummer;
    document.getElementById('edit-productnaam').value = productnaam;
    document.getElementById('edit-aantal').value = aantal;

    // Stel categorie in
    const categorySelect = document.getElementById('edit-categorie');
    const options = categorySelect.options;
    let categoryFound = false;

    for (let i = 0; i < options.length; i++) {
        if (options[i].textContent === categorie) {
            categorySelect.selectedIndex = i;
            categoryFound = true;
            break;
        }
    }

    // Als categorie niet gevonden in dropdown, voeg deze toe
    if (!categoryFound && categorie) {
        const newOption = document.createElement('option');
        newOption.value = categorie.toLowerCase().replace(/\s+/g, '-');
        newOption.textContent = categorie;
        newOption.selected = true;
        categorySelect.appendChild(newOption);
    }

    // Toon modal
    const modal = document.getElementById('productBewerkenModal');
    modal.classList.add('active');
    document.body.style.overflow = 'hidden'; // Voorkom scrollen van achtergrond
}

// Event listeners
document.addEventListener('DOMContentLoaded', function () {
    // Voeg click event toe aan "Nieuw Product" knop
    const newProductBtn = document.querySelector('.btn-new-product');
    if (newProductBtn) {
        newProductBtn.addEventListener('click', openModal);
    }

    // Voeg change event toe aan categorie selectie
    const categorySelect = document.getElementById('categorie');
    if (categorySelect) {
        categorySelect.addEventListener('change', handleCategoryChange);
    }

    // Sluit modal wanneer geklikt op overlay (buiten modal inhoud)
    const modalOverlay = document.getElementById('nieuwProductModal');
    if (modalOverlay) {
        modalOverlay.addEventListener('click', function (e) {
            if (e.target === modalOverlay) {
                closeModal();
            }
        });
    }

    // Behandel formulier verzending
    const modalForm = document.querySelector('.modal-form');
    if (modalForm) {
        modalForm.addEventListener('submit', function (e) {
            e.preventDefault();

            // Get form values
            const eanNummer = document.getElementById('ean-nummer').value;
            const productnaam = document.getElementById('productnaam').value;
            const categorie = document.getElementById('categorie').value;
            const aantal = document.getElementById('aantal').value;

            // Validate form - check if a new category is being entered
            const newCategoryContainer = document.getElementById('new-category-container');
            if (newCategoryContainer.style.display === 'block') {
                alert('Voer eerst een nieuwe categorie in of klik op Annuleren.');
                document.getElementById('new-category').focus();
                return;
            }

            // Validate form
            if (!eanNummer || !productnaam || !categorie || !aantal) {
                alert('Vul alle velden in a.u.b.');
                return;
            }

            // Get category display text
            let categoryText = categorie;
            if (categorie !== 'overig') {
                const categorySelect = document.getElementById('categorie');
                categoryText = categorySelect.options[categorySelect.selectedIndex].text;
            }

            // Hier zou je normaal de data naar de server sturen
            console.log('Nieuw product toegevoegd:', {
                eanNummer,
                productnaam,
                categorie: categoryText,
                aantal
            });

            // Voor demo doeleinden, voeg een nieuwe rij toe aan de tabel
            addNewProductToTable(eanNummer, productnaam, categoryText, aantal);

            // Close modal and reset form
            closeModal();
        });
    }

    // Voeg change event toe aan bewerk categorie selectie
    const editCategorySelect = document.getElementById('edit-categorie');
    if (editCategorySelect) {
        editCategorySelect.addEventListener('change', handleEditCategoryChange);
    }

    // Behandel bewerk modal overlay klik
    const editModalOverlay = document.getElementById('productBewerkenModal');
    if (editModalOverlay) {
        editModalOverlay.addEventListener('click', function (e) {
            if (e.target === editModalOverlay) {
                closeEditModal();
            }
        });
    }

    // Behandel bewerk formulier verzending
    const editForm = document.getElementById('edit-form');
    if (editForm) {
        editForm.addEventListener('submit', function (e) {
            e.preventDefault();

            // Get form values
            const rowIndex = document.getElementById('edit-row-index').value;
            const eanNummer = document.getElementById('edit-ean-nummer').value;
            const productnaam = document.getElementById('edit-productnaam').value;
            const categorie = document.getElementById('edit-categorie').value;
            const aantal = document.getElementById('edit-aantal').value;

            // Validate form - check if a new category is being entered
            const newCategoryContainer = document.getElementById('edit-new-category-container');
            if (newCategoryContainer.style.display === 'block') {
                alert('Voer eerst een nieuwe categorie in of klik op Annuleren.');
                document.getElementById('edit-new-category').focus();
                return;
            }

            // Validate form
            if (!eanNummer || !productnaam || !categorie || !aantal) {
                alert('Vul alle velden in a.u.b.');
                return;
            }

            // Get category display text
            let categoryText = categorie;
            if (categorie !== 'overig') {
                const categorySelect = document.getElementById('edit-categorie');
                categoryText = categorySelect.options[categorySelect.selectedIndex].text;
            }

            // Werk de tabel rij bij
            updateProductInTable(rowIndex, eanNummer, productnaam, categoryText, aantal);

            // Close modal and reset form
            closeEditModal();
        });
    }

    // Voeg click events toe aan bewerk knoppen
    function addEditButtonListeners() {
        const editButtons = document.querySelectorAll('.btn-edit');
        editButtons.forEach((button, index) => {
            button.addEventListener('click', function () {
                openEditModal(index);
            });
        });
    }

    // Voeg click events toe aan verwijder knoppen
    function addDeleteButtonListeners() {
        const deleteButtons = document.querySelectorAll('.btn-delete');
        deleteButtons.forEach((button, index) => {
            button.addEventListener('click', function () {
                deleteProduct(index);
            });
        });
    }

    // Initiële aanroep om listeners toe te voegen
    addEditButtonListeners();
    addDeleteButtonListeners();

    // Voeg ook listeners toe wanneer nieuwe producten worden toegevoegd
    const observer = new MutationObserver(function (mutations) {
        mutations.forEach(function (mutation) {
            if (mutation.type === 'childList') {
                addEditButtonListeners();
                addDeleteButtonListeners();
            }
        });
    });

    const tableBody = document.querySelector('.inventory-table tbody');
    if (tableBody) {
        observer.observe(tableBody, { childList: true });
    }

    // Sluit modal met Escape toets
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeModal();
            closeEditModal();
        }
    });
});

// Functie om nieuw product toe te voegen aan tabel (voor demo doeleinden)
function addNewProductToTable(eanNummer, productnaam, categorie, aantal) {
    const table = document.querySelector('.inventory-table tbody');
    if (!table) return;

    // Maak nieuwe rij
    const newRow = document.createElement('tr');
    newRow.innerHTML = `
        <td>
            <div class="ean-cell">
                ${eanNummer}
            </div>
        </td>
        <td>${productnaam}</td>
        <td>${categorie}</td>
        <td><span class="quantity-badge">${aantal}</span></td>
        <td>
            <div class="actions">
                <button class="btn-edit">
                    <i class="fas fa-pencil"></i>
                </button>
                <button class="btn-delete">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </td>
    `;

    // Voeg rij toe aan tabel
    table.appendChild(newRow);

    // Toon succesbericht (optioneel)
    showNotification('Product succesvol toegevoegd!');
}

// Functie om notificatie te tonen (optionele verbetering)
function showNotification(message) {
    // Maak notificatie element
    const notification = document.createElement('div');
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background-color: #2E7D32;
        color: white;
        padding: 12px 20px;
        border-radius: 6px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        z-index: 2000;
        animation: slideInRight 0.3s ease;
    `;
    notification.textContent = message;

    // Voeg toe aan pagina
    document.body.appendChild(notification);

    // Verwijder na 3 seconden
    setTimeout(() => {
        notification.style.animation = 'slideOutRight 0.3s ease';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 3000);
}

// Voeg slide animaties toe voor notificaties
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

// Categorie functionaliteit
function handleCategoryChange() {
    const categorySelect = document.getElementById('categorie');
    const newCategoryContainer = document.getElementById('new-category-container');

    if (categorySelect.value === 'overig') {
        // Show new category input
        categorySelect.style.display = 'none';
        newCategoryContainer.style.display = 'block';

        // Focus on the new category input
        setTimeout(() => {
            document.getElementById('new-category').focus();
        }, 100);
    } else {
        // Hide new category input
        categorySelect.style.display = 'block';
        newCategoryContainer.style.display = 'none';
    }
}

function addNewCategory() {
    const newCategoryInput = document.getElementById('new-category');
    const categorySelect = document.getElementById('categorie');
    const newCategoryContainer = document.getElementById('new-category-container');

    const newCategoryValue = newCategoryInput.value.trim();

    if (!newCategoryValue) {
        alert('Voer een categorie in a.u.b.');
        newCategoryInput.focus();
        return;
    }

    // Add new option to the select dropdown
    const newOption = document.createElement('option');
    newOption.value = newCategoryValue.toLowerCase().replace(/\s+/g, '-');
    newOption.textContent = newCategoryValue;
    newOption.selected = true;

    // Insert before the "overig" option
    const overigOption = categorySelect.querySelector('option[value="overig"]');
    categorySelect.insertBefore(newOption, overigOption);

    // Show dropdown and hide input
    categorySelect.style.display = 'block';
    newCategoryContainer.style.display = 'none';

    // Clear the input
    newCategoryInput.value = '';
}

function cancelNewCategory() {
    const categorySelect = document.getElementById('categorie');
    const newCategoryContainer = document.getElementById('new-category-container');
    const newCategoryInput = document.getElementById('new-category');

    // Reset to first option
    categorySelect.selectedIndex = 0;

    // Show dropdown and hide input
    categorySelect.style.display = 'block';
    newCategoryContainer.style.display = 'none';

    // Clear the input
    newCategoryInput.value = '';
}

function cancelNewCategory() {
    const categorySelect = document.getElementById('categorie');
    const newCategoryContainer = document.getElementById('new-category-container');
    const newCategoryInput = document.getElementById('new-category');

    // Reset to first option
    categorySelect.selectedIndex = 0;

    // Show dropdown and hide input
    categorySelect.style.display = 'block';
    newCategoryContainer.style.display = 'none';

    // Clear the input
    newCategoryInput.value = '';
}

// Bewerk modal categorie functionaliteit
function handleEditCategoryChange() {
    const categorySelect = document.getElementById('edit-categorie');
    const newCategoryContainer = document.getElementById('edit-new-category-container');

    if (categorySelect.value === 'overig') {
        // Show new category input
        categorySelect.style.display = 'none';
        newCategoryContainer.style.display = 'block';

        // Focus on the new category input
        setTimeout(() => {
            document.getElementById('edit-new-category').focus();
        }, 100);
    } else {
        // Hide new category input
        categorySelect.style.display = 'block';
        newCategoryContainer.style.display = 'none';
    }
}

function addEditNewCategory() {
    const newCategoryInput = document.getElementById('edit-new-category');
    const categorySelect = document.getElementById('edit-categorie');
    const newCategoryContainer = document.getElementById('edit-new-category-container');

    const newCategoryValue = newCategoryInput.value.trim();

    if (!newCategoryValue) {
        alert('Voer een categorie in a.u.b.');
        newCategoryInput.focus();
        return;
    }

    // Add new option to the select dropdown
    const newOption = document.createElement('option');
    newOption.value = newCategoryValue.toLowerCase().replace(/\s+/g, '-');
    newOption.textContent = newCategoryValue;
    newOption.selected = true;

    // Insert before the "overig" option
    const overigOption = categorySelect.querySelector('option[value="overig"]');
    categorySelect.insertBefore(newOption, overigOption);

    // Show dropdown and hide input
    categorySelect.style.display = 'block';
    newCategoryContainer.style.display = 'none';

    // Clear the input
    newCategoryInput.value = '';
}

function cancelEditNewCategory() {
    const categorySelect = document.getElementById('edit-categorie');
    const newCategoryContainer = document.getElementById('edit-new-category-container');
    const newCategoryInput = document.getElementById('edit-new-category');

    // Reset to first option
    categorySelect.selectedIndex = 0;

    // Show dropdown and hide input
    categorySelect.style.display = 'block';
    newCategoryContainer.style.display = 'none';

    // Clear the input
    newCategoryInput.value = '';
}

// Functie om product bij te werken in tabel
function updateProductInTable(rowIndex, eanNummer, productnaam, categorie, aantal) {
    const table = document.querySelector('.inventory-table tbody');
    const rows = table.getElementsByTagName('tr');
    const row = rows[rowIndex];

    if (!row) return;

    // Werk cellen bij
    const cells = row.getElementsByTagName('td');
    cells[0].innerHTML = `<div class="ean-cell">${eanNummer}</div>`;
    cells[1].textContent = productnaam;
    cells[2].textContent = categorie;
    cells[3].innerHTML = `<span class="quantity-badge">${aantal}</span>`;

    // Show success message
    showNotification('Product succesvol bijgewerkt!');
}

// Functie om product te verwijderen uit tabel
function deleteProduct(rowIndex) {
    const table = document.querySelector('.inventory-table tbody');
    const rows = table.getElementsByTagName('tr');
    const row = rows[rowIndex];

    if (!row) return;

    // Haal productnaam op voor bevestiging
    const cells = row.getElementsByTagName('td');
    const productnaam = cells[1].textContent.trim();

    // Toon bevestigingsdialoog
    if (confirm(`Weet u zeker dat u "${productnaam}" wilt verwijderen?`)) {
        // Verwijder de rij
        row.remove();

        // Toon succesbericht
        showNotification('Product succesvol verwijderd!');

        // Voeg event listeners opnieuw toe om correcte indexering te behouden
        setTimeout(() => {
            addEditButtonListeners();
            addDeleteButtonListeners();
        }, 100);
    }
}