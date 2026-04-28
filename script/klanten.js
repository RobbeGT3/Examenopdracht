let clients = [];
let products = [];
let currentClient = null;
let isEditMode = false;   

async function loadKlanten() {
  const res = await fetch("/actions/klanten/getKlanten.php");
  clients = await res.json();

  applyFiltersAndSort();
}


async function loadProducts() {
  const res = await fetch("/actions/klanten/getProductVoorraad.php");
  products = await res.json();

  fillProductSelect();
}


function fillProductSelect() {
  productSelect.innerHTML = '<option value="">-- Kies een product --</option>';

  products.forEach(product => {
    const option = document.createElement("option");
    option.value = product.idProducts; 
    const displayName = `${product.productnaam} (${product.product_categorie}) - voorraad: ${product.aantal}`;
    option.textContent = displayName;

    productSelect.appendChild(option);
  });
}

loadKlanten();
loadProducts();

document.addEventListener("click", (e) => {

  if (e.target.classList.contains("family-link")) {
    const id = e.target.dataset.id;

    const client = clients.find(c => c.idKlanten == id);

    fillClientDetail(client);
    openModal(clientDetailModal);
  }

  if (e.target.classList.contains("openPackageModal")) {
    const id = e.target.dataset.id;

    const client = clients.find(c => c.idKlanten == id);
    currentClient = client; 


    fillPackageModal(client);
    selectedProducts = [];
    qty = 1;
    qtyInput.value = 1;
    renderProducts();

    openModal(packageModal);
  }

  if (e.target.classList.contains("givePackage")) {
    const id = e.target.dataset.id;
    if (!id) {
    alert("Geen voedselpakket gevonden");
    return;
  }

  givePackage(id);
    
  }

});

async function givePackage(voedselpakketId) {
  if (!voedselpakketId) return;

  await fetch("/actions/klanten/markeerUitgegeven.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({
      idVoedselpakketten: voedselpakketId
    })
  });

  loadKlanten();
}

const overlay = document.getElementById("modalOverlay");
const clientDetailModal = document.getElementById("clientDetailModal");
const packageModal = document.getElementById("packageModal");
const newClientModal = document.getElementById("newClientModal");

const openClientDetailButtons = document.querySelectorAll(".openClientDetail");
const openPackageButtons = document.querySelectorAll(".openPackageModal");
const openNewClientModal = document.getElementById("openNewClientModal");
const closeButtons = document.querySelectorAll("[data-close]");
const searchInput = document.getElementById("searchInput");

const detailTitle = document.getElementById("detailTitle");
const detailVoornaam = document.getElementById("detailVoornaam");
const detailAchternaam = document.getElementById("detailAchternaam");
const detailAdres = document.getElementById("detailAdres");
const detailPostcode = document.getElementById("detailPostcode");
const detailWoonplaats = document.getElementById("detailWoonplaats");
const detailTelefoon = document.getElementById("detailTelefoon");
const detailEmail = document.getElementById("detailEmail");
const detailAdults = document.getElementById("detailAdults");
const detailChildren = document.getElementById("detailChildren");
const detailBabies = document.getElementById("detailBabies");
const detailDiet = document.getElementById("detailDiet");
const detailAllergy = document.getElementById("detailAllergy");
const detailPackages = document.getElementById("detailPackages");
const detailLastPackage = document.getElementById("detailLastPackage");
const detailStatus = document.getElementById("detailStatus");
const approveBtn = document.getElementById("approveClientBtn");
const deleteBtn = document.getElementById("deleteClientBtn");


const packageForText = document.getElementById("packageForText");
const packageAdults = document.getElementById("packageAdults");
const packageChildren = document.getElementById("packageChildren");
const packageBabies = document.getElementById("packageBabies");
const packageDiet = document.getElementById("packageDiet");

const productSelect = document.getElementById("productSelect");
const minusQty = document.getElementById("minusQty");
const plusQty = document.getElementById("plusQty");
const qtyInput = document.getElementById("qtyInput");
const addProductBtn = document.getElementById("addProductBtn");
const productList = document.getElementById("productList");
const productCount = document.getElementById("productCount");
const savePackageBtn = document.getElementById("savePackageBtn");


const editClientBtn = document.getElementById("editClientBtn");
editClientBtn.addEventListener("click", () => {
  openEditClientModal(currentClient);
});

const saveClientBtn = document.getElementById("saveClientBtn");

saveClientBtn.addEventListener("click", async () => {

  const data = {
    voornaam: document.getElementById("voornaam").value.trim(),
    achternaam: document.getElementById("achternaam").value.trim(),
    adres: document.getElementById("adres").value.trim(),
    postcode: document.getElementById("postcode").value.trim(),
    woonplaats: document.getElementById("woonplaats").value.trim(),
    telefoon: document.getElementById("telefoonnummer").value.trim(),
    email: document.getElementById("email").value.trim(),
    volwassenen: document.getElementById("volwassenen").value,
    kinderen: document.getElementById("kinderen").value,
    babies: document.getElementById("babies").value,
    wensen: Array.from(document.querySelectorAll('input[name="wensen[]"]:checked'))
                  .map(cb => cb.value),
    allergieen: getAllergies()
  };

  if (
    !data.voornaam ||
    !data.achternaam ||
    !data.adres ||
    !data.postcode ||
    !data.woonplaats ||
    !data.telefoon ||
    !data.email
  ) {
    alert("Vul alle verplichte velden in.");
    return;
  }
  const url = isEditMode
    ? "/actions/klanten/updateKlant.php"
    : "/actions/klanten/createKlant.php";

  await fetch(url, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({
      id: currentClient?.idKlanten, 
      ...data
    })
  });

  closeAllModals();
  loadKlanten(); 
});


function getAllergies() {
  if (!hasAllergy.checked) return [];

  const value = document.getElementById("allergyInput").value;

  return value
    .split(",")
    .map(a => a.trim())
    .filter(a => a.length > 0);
}

const hasAllergy = document.getElementById("hasAllergy");
const allergyContainer = document.getElementById("allergyContainer");

hasAllergy.addEventListener("change", () => {
  if (hasAllergy.checked) {
    allergyContainer.classList.remove("hidden");
  } else {
    allergyContainer.classList.add("hidden");
  }
});

let qty = 1;
let selectedProducts = [];

function getRemainingStock() {
  const productId = productSelect.value;
  if (!productId) return 0;

  const product = products.find(p => p.idProducts == productId);
  if (!product) return 0;

  const existing = selectedProducts.find(p => p.id == product.idProducts);
  const alreadySelected = existing ? existing.amount : 0;

  return product.aantal - alreadySelected;
}

function updateQtyButtons() {
  const remaining = getRemainingStock();

  if (qty >= remaining || remaining === 0) {
    plusQty.disabled = true;
    plusQty.style.opacity = "0.5";
  } else {
    plusQty.disabled = false;
    plusQty.style.opacity = "1";
  }

  if (qty <= 1) {
    minusQty.disabled = true;
    minusQty.style.opacity = "0.5";
  } else {
    minusQty.disabled = false;
    minusQty.style.opacity = "1";
  }
}

function closeAllModals() {
  overlay.classList.add("hidden");
  clientDetailModal.classList.add("hidden");
  packageModal.classList.add("hidden");
  newClientModal.classList.add("hidden");

  resetNewClientModal();
  resetPackageModal();
}

function openModal(modal) {
  overlay.classList.remove("hidden");
  modal.classList.remove("hidden");
}

function resetNewClientModal() {
  document.querySelectorAll("#newClientModal input").forEach(input => {
    if (input.type === "checkbox") {
      input.checked = false;
    } else {
      input.value = "";
    }
  });

  allergyContainer.classList.add("hidden");
}

function resetPackageModal() {
  selectedProducts = [];
  qty = 1;

  qtyInput.value = 1;
  productSelect.value = "";

  renderProducts();
}


function fillClientDetail(client) {
  currentClient = client;

  detailTitle.textContent = client.gezinsnaam;
  detailVoornaam.textContent = client.voornaam;
  detailAchternaam.textContent = client.achternaam;
  detailAdres.textContent = client.adres;
  detailPostcode.textContent = client.postcode;
  detailWoonplaats.textContent = client.woonplaats;
  detailTelefoon.textContent = client.telefoonnummer;
  detailEmail.textContent = client.email;
  detailAdults.textContent = client.volwassenen;
  detailChildren.textContent = client.kinderen;
  detailBabies.textContent = client.babies;
  detailDiet.textContent = client.wensen;
  detailAllergy.textContent = client.allergenen;
  detailPackages.textContent = client.aantal_pakketten;
  detailLastPackage.textContent = client.laatste_samenstelling || "Nog geen pakket";
  detailStatus.innerHTML = client.status === "Actief"
  ? '<span class="status-success">✓ Actief</span>'
  : '<span class="status-error">✗ Inactief</span>';

  const approveBtn = document.getElementById("approveClientBtn");
  if (client.status === "Inactief") {
    approveBtn.classList.remove("hidden");
  } else {
    approveBtn.classList.add("hidden");
  }
}

function openEditClientModal(client) {

  isEditMode = true;
  document.getElementById("clientModalTitle").textContent = "Klant Bewerken"; 
  document.getElementById("voornaam").value = client.voornaam;
  document.getElementById("achternaam").value = client.achternaam;
  document.getElementById("adres").value = client.adres;
  document.getElementById("postcode").value = client.postcode;
  document.getElementById("woonplaats").value = client.woonplaats;
  document.getElementById("telefoonnummer").value = client.telefoonnummer;
  document.getElementById("email").value = client.email;

  document.getElementById("volwassenen").value = client.volwassenen;
  document.getElementById("kinderen").value = client.kinderen;
  document.getElementById("babies").value = client.babies;

  document.querySelectorAll('input[name="wensen[]"]').forEach(cb => {
    cb.checked = client.wensen_ids?.includes(Number(cb.value));
  });

  if (client.allergenen && client.allergenen.length > 0) {
    hasAllergy.checked = true;
    allergyContainer.classList.remove("hidden");
    let allergieen = client.allergenen;

    if (typeof allergieen === "string") {
      allergieen = allergieen.split(",");
    }
    if (!Array.isArray(allergieen)) {
      allergieen = [];
    }

    if (allergieen.length > 0) {
      hasAllergy.checked = true;
      allergyContainer.classList.remove("hidden");
      document.getElementById("allergyInput").value = allergieen.join(", ");
    }
  }

  openModal(newClientModal);
}

function fillPackageModal(client) {
  packageForText.textContent = `Voor: ${client.gezinsnaam}`;
  packageAdults.textContent = client.volwassenen;
  packageChildren.textContent = client.kinderen;
  packageBabies.textContent = client.babies;
  packageDiet.textContent = client.wensen;
}

function renderProducts() {
  productCount.textContent = selectedProducts.length;

  if (selectedProducts.length === 0) {
    productList.className = "empty-products";
    productList.innerHTML = "Nog geen producten toegevoegd. Selecteer hierboven een product.";
    return;
  }

  productList.className = "";
  productList.innerHTML = "";

  selectedProducts.forEach((product, index) => {
    const row = document.createElement("div");
    row.className = "product-row";
    row.innerHTML = `
      <div class="product-row-left">
        <span class="product-row-name">${product.name}</span>
        <span class="product-row-qty">Aantal: ${product.amount}</span>
      </div>
      <button class="remove-product" data-index="${index}">&times;</button>
    `;
    productList.appendChild(row);
  });

  document.querySelectorAll(".remove-product").forEach(button => {
    button.addEventListener("click", () => {
      const index = Number(button.dataset.index);
      selectedProducts.splice(index, 1);
      renderProducts();
    });
  });
}

approveBtn.addEventListener("click", async () => {
  if (!currentClient) return;

  await fetch("/actions/klanten/approveKlant.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({
      id: currentClient.idKlanten
    })
  });

  closeAllModals();
  loadKlanten();
});

deleteBtn.addEventListener("click", async () => {
  if (!currentClient) return;

  if (!confirm("Weet je zeker dat je deze klant wil verwijderen?")) return;

  await fetch("/actions/klanten/deleteKlant.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({
      id: currentClient.idKlanten
    })
  });

  closeAllModals();
  loadKlanten();
});

searchInput.addEventListener("input", applyFiltersAndSort)

const sortSelect = document.getElementById("sortSelect");

sortSelect.addEventListener("change", () => {
  applyFiltersAndSort();
});

function applyFiltersAndSort() {
  const searchValue = searchInput.value.toLowerCase();
  const sortValue = sortSelect.value;

  let filtered = clients.filter(c =>
    (c.voornaam || "").toLowerCase().includes(searchValue) ||
    (c.achternaam || "").toLowerCase().includes(searchValue) ||
    (c.gezinsnaam || "").toLowerCase().includes(searchValue)
  );

  switch (sortValue) {
    case "name_az":
      filtered.sort((a, b) => a.achternaam.localeCompare(b.achternaam));
      break;

    case "name_za":
      filtered.sort((a, b) => b.achternaam.localeCompare(a.achternaam));
      break;

    case "date_old":
      filtered.sort((a, b) =>
        new Date(a.laatste_samenstelling || 0) -
        new Date(b.laatste_samenstelling || 0)
      );
      break;

    case "date_new":
      filtered.sort((a, b) =>
        new Date(b.laatste_samenstelling || 0) -
        new Date(a.laatste_samenstelling || 0)
      );
      break;
  }

  renderTable(filtered);
}


function renderTable(data = clients) {
  const tableBody = document.getElementById("klantenTableBody");
  tableBody.innerHTML = "";

  data.forEach(client => {
    const row = document.createElement("tr");

    row.innerHTML = `
      <td>
        <button class="family-link" data-id="${client.idKlanten}">
          Familie ${client.achternaam}
        </button>
      </td>
      <td>${client.postcode}</td>
      <td>${client.telefoonnummer}</td>
      <td>
        ${client.laatste_samenstelling
          ? client.laatste_samenstelling
          : '<span class="status-warning">Nog geen pakket</span>'}
      </td>
      <td>
        ${client.uitgifte_datum || '-'}
      </td>
      
      <td>
        ${
          client.status !== "Actief" || client.uitgifte_datum
            ? ''
            : client.laatste_samenstelling
              ? `<button class="btn-green-round givePackage" data-id="${client.idVoedselpakketten}">
                  ✓ Uitgeven
                </button>`
              : `<button class="btn-blue openPackageModal" data-id="${client.idKlanten}">
                  + Aanmaken
                </button>`
        }
      </td>
      <td>
        ${
          client.status == 'Actief'
            ? '<span class="status-success">✓ Actief</span>'
            : '<span class="status-error">✗ Inactief</span>'
        }
      </td>
    `;

    tableBody.appendChild(row);
  });
}

savePackageBtn.addEventListener("click", async () => {

  if (!currentClient) {
    alert("Geen klant geselecteerd");
    return;
  }

  if (selectedProducts.length === 0) {
    alert("Voeg eerst producten toe");
    return;
  }

  await fetch("/actions/klanten/createVoedselpakket.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json"
    },
    body: JSON.stringify({
      klantId: currentClient.idKlanten,
      producten: selectedProducts
    })
  });

  closeAllModals();
  loadKlanten();
});
openClientDetailButtons.forEach(button => {
  button.addEventListener("click", () => {
    const clientKey = button.dataset.client;
    fillClientDetail(clients[clientKey]);
    openModal(clientDetailModal);
  });
});

openPackageButtons.forEach(button => {
  button.addEventListener("click", () => {
    const clientKey = button.dataset.client;
    fillPackageModal(clients[clientKey]);
    selectedProducts = [];
    qty = 1;
    qtyInput.value = 1;
    productSelect.value = "";
    renderProducts();
    openModal(packageModal);
  });
});

openNewClientModal.addEventListener("click", () => {
  isEditMode = false;
  document.getElementById("clientModalTitle").textContent = "Nieuwe Klant"; // NIEUW
  openModal(newClientModal);
});

closeButtons.forEach(button => {
  button.addEventListener("click", closeAllModals);
});

overlay.addEventListener("click", closeAllModals);

minusQty.addEventListener("click", () => {
  if (qty > 1) {
    qty--;
    qtyInput.value = qty;
    updateQtyButtons();
  }
});

plusQty.addEventListener("click", () => {
  const remaining = getRemainingStock();

  if (qty < remaining) {
    qty++;
    qtyInput.value = qty;
    updateQtyButtons();
  }
});

addProductBtn.addEventListener("click", () => {
  const productId = productSelect.value;

  if (!productId) {
    alert("Kies eerst een product.");
    return;
  }

  const product = products.find(p => p.idProducts == productId);
  const existing = selectedProducts.find(p => p.id == product.idProducts);

  if (existing) {
    existing.amount += qty; 
  } else {
    selectedProducts.push({
      id: product.idProducts,
      name: product.productnaam,
      amount: qty
    });
  }

  qty = 1;
  qtyInput.value = 1;
  productSelect.value = "";

  updateQtyButtons();
  renderProducts();
});

renderProducts();