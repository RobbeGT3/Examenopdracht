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

function openEditModal(rowIndex, productId) {
    const table = document.querySelector('.inventory-table tbody');
    const rows = table.getElementsByTagName('tr');
    const row = rows[rowIndex];
    document.getElementById('edit-row-index').value = rowIndex;
document.getElementById('edit-product-id').value = productId;

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

let currentSortedColumn = null;
let sortDirection = {};

function sortTable(columnIndex) {
    const table = document.querySelector('.inventory-table tbody');
    const rows = Array.from(table.querySelectorAll('tr'));
    const headers = document.querySelectorAll('.inventory-table th');

    // Reset alle icons
    headers.forEach((th, index) => {
        const icon = th.querySelector('i');
        if (!icon) return;

        icon.classList.remove('fa-sort-up', 'fa-sort-down');
    });

    // Toggle direction
    sortDirection[columnIndex] = !sortDirection[columnIndex];
    const isAscending = sortDirection[columnIndex];

    // Sorteer data
    rows.sort((a, b) => {
        let valA = a.children[columnIndex].textContent.trim();
        let valB = b.children[columnIndex].textContent.trim();

        if (!isNaN(valA) && !isNaN(valB)) {
            return isAscending ? valA - valB : valB - valA;
        }

        return isAscending
            ? valA.localeCompare(valB)
            : valB.localeCompare(valA);
    });

    rows.forEach(row => table.appendChild(row));

    // Zet juiste icon op actieve kolom
    const activeIcon = headers[columnIndex].querySelector('i');

    if (isAscending) {
        activeIcon.classList.remove('fa-sort');
        activeIcon.classList.add('fa-sort-up');
    } else {
        activeIcon.classList.remove('fa-sort');
        activeIcon.classList.add('fa-sort-down');
    }

    currentSortedColumn = columnIndex;
}

// Event listeners
document.addEventListener('DOMContentLoaded', function () {
     // Voeg click events toe aan bewerk knoppen
    function addEditButtonListeners() {
        const editButtons = document.querySelectorAll('.btn-edit');
        editButtons.forEach((button, index) => {
            // button.addEventListener('click', function () {
            //     openEditModal(index);
            // });
            button.addEventListener('click', function () {
                const productId = this.dataset.id;
                openEditModal(index, productId);
            });
        });
    }

    // Voeg click events toe aan verwijder knoppen
    function addDeleteButtonListeners() {
        const deleteButtons = document.querySelectorAll('.btn-delete');

        deleteButtons.forEach(button => {
        if (button.disabled) return;

        button.addEventListener('click', function () {
            const productId = this.dataset.id;

            const row = this.closest('tr');
            const productnaam = row.children[1].textContent.trim();

            deleteProduct(productId, productnaam);
        });
    });
    }

    const searchInput = document.querySelector('.search-bar input');
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const filter = this.value.toLowerCase();
            const rows = document.querySelectorAll('.inventory-table tbody tr');

            rows.forEach(row => {
                const ean = row.children[0].textContent.toLowerCase();

                if (ean.includes(filter)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }
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

                const eanNummer = document.getElementById('ean-nummer').value;
                const productnaam = document.getElementById('productnaam').value;
                const categorieSelect = document.getElementById('categorie');
                const aantal = document.getElementById('aantal').value;

                const isNewCategory = document.getElementById('is-new-category').value === "1";
                const newCategory = document.getElementById('new-category').value.trim();

                // Validatie
                if (!eanNummer || !productnaam || !aantal) {
                    alert('Vul alle velden in a.u.b.');
                    return;
                }

                if (!isNewCategory && !categorieSelect.value) {
                    alert('Selecteer een categorie.');
                    return;
                }

                if (isNewCategory && !newCategory) {
                    alert('Voer een nieuwe categorie in.');
                    return;
                }

                // if (categorieSelect.value === 'overig') {
                //     alert('Kies een bestaande categorie of voeg een nieuwe toe.');
                //     return;
                // }

                fetch('actions/productToevoegen.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        ean: eanNummer,
                        productnaam: productnaam,
                        categorie_id: isNewCategory ? null : categorieSelect.value,
                        new_categorie: isNewCategory ? newCategory : null,
                        aantal: aantal
                    })
                })
                // .then(res => res.json())
                .then(()=>{location.reload(); });

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

            const productId = document.getElementById('edit-product-id').value;
            const eanNummer = document.getElementById('edit-ean-nummer').value;
            const productnaam = document.getElementById('edit-productnaam').value;
            const categorieSelect = document.getElementById('edit-categorie');
            const aantal = document.getElementById('edit-aantal').value;

            const newCategoryContainer = document.getElementById('edit-new-category-container');
            const isNewCategory = newCategoryContainer.style.display === 'block';
            const newCategory = document.getElementById('edit-new-category').value.trim();

            // 🔴 Validatie
            if (!eanNummer || !productnaam || !aantal) {
                alert('Vul alle velden in a.u.b.');
                return;
            }

            if (!isNewCategory && !categorieSelect.value) {
                alert('Selecteer een categorie.');
                return;
            }

            if (isNewCategory && !newCategory) {
                alert('Voer een nieuwe categorie in.');
                return;
            }

            // if (categorieSelect.value === 'overig') {
            //     alert('Kies een bestaande categorie of voeg een nieuwe toe.');
            //     return;
            // }

            fetch('actions/productUpdate.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    id: productId,
                    ean: eanNummer,
                    productnaam: productnaam,
                    categorie_id: isNewCategory ? null : categorieSelect.value,
                    new_categorie: isNewCategory ? newCategory : null,
                    aantal: aantal
                })
            })
            .then(() => {
                location.reload();
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
// function handleCategoryChange() {
//     const categorySelect = document.getElementById('categorie');
//     const newCategoryContainer = document.getElementById('new-category-container');

//     if (categorySelect.value === 'overig') {
//         // Show new category input
//         categorySelect.style.display = 'none';
//         newCategoryContainer.style.display = 'block';

//         // Focus on the new category input
//         setTimeout(() => {
//             document.getElementById('new-category').focus();
//         }, 100);
//     } else {
//         // Hide new category input
//         categorySelect.style.display = 'block';
//         newCategoryContainer.style.display = 'none';
//     }
// }


function handleCategoryChange() {
    const categorySelect = document.getElementById('categorie');
    const newCategoryContainer = document.getElementById('new-category-container');
    const isNewCategoryInput = document.getElementById('is-new-category');

    if (categorySelect.value === 'overig') {
        categorySelect.style.display = 'none';
        newCategoryContainer.style.display = 'block';

        setTimeout(() => {
            document.getElementById('new-category').focus();
        }, 100);
    } else {
        // 👉 BELANGRIJK: reset new category status
        isNewCategoryInput.value = "0";

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
        return;
    }

    document.getElementById('is-new-category').value = "1";

    const newOption = document.createElement('option');
    newOption.value = newCategoryValue;
    newOption.textContent = newCategoryValue;
    newOption.selected = true;

    categorySelect.appendChild(newOption);

    categorySelect.style.display = 'block';
    newCategoryContainer.style.display = 'none';
}

function cancelNewCategory() {
    document.getElementById('is-new-category').value = "0";

    const categorySelect = document.getElementById('categorie');
    const newCategoryContainer = document.getElementById('new-category-container');

    categorySelect.value = "";

    // 👉 UI herstellen
    categorySelect.style.display = 'block';
    newCategoryContainer.style.display = 'none';

    document.getElementById('new-category').value = '';
}

// Bewerk modal categorie functionaliteit
// function handleEditCategoryChange() {
//     const categorySelect = document.getElementById('edit-categorie');
//     const newCategoryContainer = document.getElementById('edit-new-category-container');

//     if (categorySelect.value === 'overig') {
//         // Show new category input
//         categorySelect.style.display = 'none';
//         newCategoryContainer.style.display = 'block';

//         // Focus on the new category input
//         setTimeout(() => {
//             document.getElementById('edit-new-category').focus();
//         }, 100);
//     } else {
//         // Hide new category input
//         categorySelect.style.display = 'block';
//         newCategoryContainer.style.display = 'none';
//     }
// }

function handleEditCategoryChange() {
    const categorySelect = document.getElementById('edit-categorie');
    const newCategoryContainer = document.getElementById('edit-new-category-container');
    const isNewCategoryInput = document.getElementById('edit-is-new-category');

    if (categorySelect.value === 'overig') {
        categorySelect.style.display = 'none';
        newCategoryContainer.style.display = 'block';

        setTimeout(() => {
            document.getElementById('edit-new-category').focus();
        }, 100);
    } else {
        // 👉 BELANGRIJK: reset new category status
        isNewCategoryInput.value = "0";

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
        return;
    }

    document.getElementById('edit-is-new-category').value = "1";

    const newOption = document.createElement('option');
    newOption.value = newCategoryValue;
    newOption.textContent = newCategoryValue;
    newOption.selected = true;

    categorySelect.appendChild(newOption);

    categorySelect.style.display = 'block';
    newCategoryContainer.style.display = 'none';
}

function cancelNewCategory() {
    document.getElementById('is-new-category').value = "0";

    const categorySelect = document.getElementById('categorie');
    const newCategoryContainer = document.getElementById('new-category-container');

    categorySelect.value = "";

    // 👉 UI herstellen
    categorySelect.style.display = 'block';
    newCategoryContainer.style.display = 'none';

    document.getElementById('new-category').value = '';
}

// function addEditNewCategory() {
//     const newCategoryInput = document.getElementById('edit-new-category');
//     const categorySelect = document.getElementById('edit-categorie');
//     const newCategoryContainer = document.getElementById('edit-new-category-container');

//     const newCategoryValue = newCategoryInput.value.trim();

//     if (!newCategoryValue) {
//         alert('Voer een categorie in a.u.b.');
//         newCategoryInput.focus();
//         return;
//     }

//     // Add new option to the select dropdown
//     const newOption = document.createElement('option');
//     newOption.value = newCategoryValue.toLowerCase().replace(/\s+/g, '-');
//     newOption.textContent = newCategoryValue;
//     newOption.selected = true;

//     // Insert before the "overig" option
//     const overigOption = categorySelect.querySelector('option[value="overig"]');
//     categorySelect.insertBefore(newOption, overigOption);

//     // Show dropdown and hide input
//     categorySelect.style.display = 'block';
//     newCategoryContainer.style.display = 'none';

//     // Clear the input
//     newCategoryInput.value = '';
// }

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
// function updateProductInTable(rowIndex, eanNummer, productnaam, categorie, aantal) {
//     const table = document.querySelector('.inventory-table tbody');
//     const rows = table.getElementsByTagName('tr');
//     const row = rows[rowIndex];

//     if (!row) return;

//     // Werk cellen bij
//     const cells = row.getElementsByTagName('td');
//     cells[0].innerHTML = `<div class="ean-cell">${eanNummer}</div>`;
//     cells[1].textContent = productnaam;
//     cells[2].textContent = categorie;
//     cells[3].innerHTML = `<span class="quantity-badge">${aantal}</span>`;

//     // Show success message
//     showNotification('Product succesvol bijgewerkt!');
// }

// Functie om product te verwijderen uit tabel

function deleteProduct(productId, productnaam) {

    if (!confirm(`Weet u zeker dat u "${productnaam}" wilt verwijderen?`)) {
        return;
    }

    fetch('actions/productDelete.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ id: productId })
    })
    .then(()=>{location.reload(); });
}