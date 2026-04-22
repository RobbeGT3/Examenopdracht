document.addEventListener("DOMContentLoaded", function () {

    let families = {};
    let products = [];
    let pakketten = [];

    let selectedFamilyName = "";
    let selectedProduct = null;
    let selectedProducts = [];

    let qty = 1;
    const qtyInput = document.getElementById("qtyInput");
    document.getElementById("plusBtn").addEventListener("click", () => {
        qty++;
        qtyInput.value = qty;
    });

    document.getElementById("minusBtn").addEventListener("click", () => {
        if (qty > 1) {
            qty--;
            qtyInput.value = qty;
        }
    });
    const familyOptions = document.getElementById("familyOptions");
    const productOptions = document.getElementById("productOptions");

    const familySearch = document.getElementById("familySearch");
    const productSearch = document.getElementById("productSearch");

    const searchInput = document.getElementById("searchInput");

    const clientInfoBox = document.getElementById("clientInfoBox");
    const productStepBox = document.getElementById("productStepBox");
    const selectedProductsBox = document.getElementById("selectedProductsBox");

    const adultCount = document.getElementById("adultCount");
    const childCount = document.getElementById("childCount");
    const babyCount = document.getElementById("babyCount");
    const dietText = document.getElementById("dietText");

    const selectedProductsList = document.getElementById("selectedProductsList");
    const productCount = document.getElementById("productCount");

    const openModalBtn = document.getElementById("openModalBtn");
    const closeModalBtn = document.getElementById("closeModalBtn");
    const cancelBtn = document.getElementById("cancelBtn");
    const modalOverlay = document.getElementById("modalOverlay");

    function openModal() {
        modalOverlay.classList.remove("hidden");
    }

    function closeModal() {
        modalOverlay.classList.add("hidden");

        selectedFamilyName = "";
        selectedProduct = null;
        selectedProducts = [];
        familySearch.value = "";
        productSearch.value = "";

        clientInfoBox.classList.add("hidden");
        productStepBox.classList.add("hidden");
        selectedProductsBox.classList.add("hidden");

        document.getElementById("allergyBox").classList.add("hidden");
        document.getElementById("allergyText").textContent = "";

        qty = 1;
        qtyInput.value = 1;

        renderSelected();
    }

    searchInput.addEventListener("input", function () {
        const searchTerm = this.value.toLowerCase();

        filterPakketten(searchTerm);
    });

    openModalBtn?.addEventListener("click", openModal);
    closeModalBtn?.addEventListener("click", closeModal);
    cancelBtn?.addEventListener("click", closeModal);

    modalOverlay?.addEventListener("click", (e) => {
        if (e.target === modalOverlay) closeModal();
    });

    loadFamilies();
    loadProducts();
    loadPakketten();

    async function loadFamilies() {
        const res = await fetch("/actions/voedselpakketten/getActiveKlanten.php");
        const data = await res.json();

        families = {};

        data.forEach(k => {
            // const label = `Familie ${k.achternaam}`;
            // const key = `${label} - ${k.postcode}`;

            const key = `${k.gezinsnaam}`;

            families[key] = {
                id: k.idKlanten,
                name: key,
                adults: k.aantal_volwassen,
                children: k.aantal_kinderen,
                babies: k.aantal_babies,
                diet: k.wensen,
                allergies: k.allergenen
            };
        });

        renderFamilies();
    }

    async function loadProducts() {
        const res = await fetch("/actions/voedselpakketten/getProductVoorraad.php");
        products = await res.json();

        renderProducts();
    }

    async function loadPakketten() {
        const res = await fetch("/actions/voedselpakketten/getVoedselpakketten.php");
        pakketten = await res.json();

        renderPakketten();
    }

    familySearch.addEventListener("input", () => {
        const term = familySearch.value.toLowerCase();
        const items = familyOptions.querySelectorAll(".dropdown-item");

        items.forEach(item => {
            const text = item.textContent.toLowerCase();
            item.style.display = text.includes(term) ? "block" : "none";
        });

        familyOptions.classList.remove("hidden");
    });

    productSearch.addEventListener("input", () => {
        const term = productSearch.value.toLowerCase();
        const items = productOptions.querySelectorAll(".dropdown-item");

        items.forEach(item => {
            const text = item.textContent.toLowerCase();
            item.style.display = text.includes(term) ? "block" : "none";
        });

        productOptions.classList.remove("hidden");
    });

    document.addEventListener("click", (e) => {
        if (!e.target.closest(".searchable-dropdown")) {
            familyOptions.classList.add("hidden");
            productOptions.classList.add("hidden");
        }
    });
    function renderFamilies() {
        familyOptions.innerHTML = "";

        Object.keys(families).forEach(key => {
            const div = document.createElement("div");
            div.className = "dropdown-item";
            div.textContent = key;

            div.addEventListener("click", () => selectFamily(key));

            familyOptions.appendChild(div);
        });
    }

    document.getElementById("dropdownControl").addEventListener("click", () => {
        familyOptions.classList.toggle("hidden");
    });

    document.getElementById("productDropdownControl").addEventListener("click", () => {
        productOptions.classList.toggle("hidden");
    });

    function renderProducts() {
        productOptions.innerHTML = "";

        products.forEach(p => {
            const div = document.createElement("div");
            div.className = "dropdown-item";
            const displayName = `${p.productnaam} (${p.product_categorie}) - voorraad: ${p.aantal}`
            div.textContent = displayName;

            div.addEventListener("click", () => {
                selectedProduct = p;
                productSearch.value = `${p.productnaam} (${p.product_categorie})`;

                productOptions.classList.add("hidden");
            });

            productOptions.appendChild(div);
        });
    }

    document.getElementById("searchInput").addEventListener("input", function () {
        const query = this.value.toLowerCase();

        const filtered = pakketten.filter(p =>
            p.gezinsnaam.toLowerCase().includes(query)
        );

        renderPakketten(filtered);
    });

    function renderPakketten(list = pakketten) {
        const container = document.getElementById("pakketList");
        const emptyState = document.getElementById("emptyState");

        container.innerHTML = "";

        if (!list || list.length === 0) {
            emptyState.style.display = "block";
            return;
        }

        emptyState.style.display = "none";

        list.forEach((p) => {
            const div = document.createElement("div");

            div.className = "package-card";

            const statusTag = !p.uitgiftedatum
                ? `<span class="status-tag pending">In wachtrij</span>`
                : `<span class="status-tag done">Uitgegeven</span>`;

            div.innerHTML = `
                <div class="card-header">
                    <h3>Pakket #${p.id}</h3>
                    ${statusTag}
                </div>

                <p><strong>${p.gezinsnaam}</strong></p>
                <p>${p.postcode}</p>
                <p>Samengesteld: ${p.samenstellings_datum || '-'}</p>
                <p>Producten: ${p.producten_totaal || 0}</p>

                <div class="pakket-actions">
                    <button class="btn-view" onclick="bekijkPakket(${p.id})">
                        Bekijken
                    </button>

                    ${
                        !p.uitgiftedatum
                            ? `<button class="btn-give" onclick="markeerUitgegeven(${p.id})">
                                Uitgeven
                            </button>`
                            : ""
                    }
                </div>
            `;

            container.appendChild(div);
        });
    }

    function selectFamily(key) {
        selectedFamilyName = key;
        const f = families[key];

        familySearch.value = key;
        familyOptions.classList.add("hidden");

        selectedProducts = [];
        renderSelected();

        adultCount.textContent = f.adults;
        childCount.textContent = f.children;
        babyCount.textContent = f.babies;
        dietText.textContent = f.diet 

        clientInfoBox.classList.remove("hidden");
        productStepBox.classList.remove("hidden");
        selectedProductsBox.classList.remove("hidden");

        const allergyBox = document.getElementById("allergyBox");
        const allergyText = document.getElementById("allergyText");

        if (f.allergies && f.allergies !== "") {
            allergyText.textContent = f.allergies.replaceAll(",", ", ");
            allergyBox.classList.remove("hidden");
        } else {
            allergyBox.classList.add("hidden");
        }
    }
    document.getElementById("addProductBtn").addEventListener("click", () => {

        if (!selectedProduct) return alert("Kies product");

        const existing = selectedProducts.find(p => p.id === selectedProduct.idProducts);

        const newAmount = existing ? existing.amount + qty : qty;

        if (newAmount > selectedProduct.aantal) {
            return alert("Niet genoeg voorraad");
        }

        if (existing) {
            existing.amount = newAmount;
        } else {
            selectedProducts.push({
                id: selectedProduct.idProducts,
                name: selectedProduct.productnaam,
                category: selectedProduct.product_categorie,
                stock: selectedProduct.aantal,
                amount: qty
            });
        }

        renderSelected();
        qty = 1;
        document.getElementById("qtyInput").value = 1;
    });

    function renderSelected() {
        selectedProductsList.innerHTML = "";

        productCount.textContent = selectedProducts.length;

        selectedProducts.forEach((p, i) => {
            const div = document.createElement("div");
            div.className = "product-item-row";

            div.innerHTML = `
                <div>
                    <h4>${p.name}</h4>
                    <p>${p.category}</p>
                </div>
                <div>
                    <span>${p.amount}x</span>
                    <button onclick="removeProduct(${i})">x</button>
                </div>
            `;

            selectedProductsList.appendChild(div);
        });
    }

    window.removeProduct = function (i) {
        selectedProducts.splice(i, 1);
        renderSelected();
    };

    document.getElementById("createPackageBtn").addEventListener("click", async () => {

        const family = families[selectedFamilyName];

        if (!family) return alert("Selecteer klant");
        const test = JSON.stringify({klantId: family.id, producten: selectedProducts})
        console.log(test)

        const res = await fetch("/actions/voedselpakketten/createVoedselpakket.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                klantId: family.id,
                producten: selectedProducts,
                // add: TRUE
            })
        });

        location.reload();
    });

    window.bekijkPakket = function (id) {
        const pakket = pakketten.find(p => p.id == id);
        if (!pakket) return;
        document.getElementById("viewTitle").textContent = `Pakket #${id} - Details`;

        const content = document.getElementById("viewContent");

        content.innerHTML = `
            <div class="detail-label">Klant</div>
            <div class="detail-value">${pakket.gezinsnaam}</div>

            <div class="detail-label">Datum Samenstelling</div>
            <div class="detail-value">${pakket.samenstellings_datum || '-'}</div>

            <div class="detail-label">Datum Uitgave</div>
            <div class="detail-value">${pakket.uitgiftedatum || '-'}</div>

            <div class="detail-label">Producten</div>

            ${
                pakket.producten && pakket.producten.length > 0
                    ? pakket.producten.map(p => `
                        <div class="product-box">
                            <div class="product-row">
                                <div>
                                    <div class="product-name">${p.naam}</div>
                                    <div class="product-category">${p.categorie}</div>
                                </div>
                                <div class="product-amount">${p.aantal}x</div>
                            </div>
                        </div>
                    `).join("")
                    : "<p>Geen producten</p>"
            }
        `;

        document.getElementById("viewModal").classList.remove("hidden");
    };

    window.closeViewModal = function () {
        document.getElementById("viewModal").classList.add("hidden");
    };

    window.markeerUitgegeven = async function (id) {
        if (!confirm("Weet je zeker dat je dit pakket wilt uitgeven?")) return;

        const res = await fetch("/actions/voedselpakketten/markeerUitgegeven.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({ id: id })
        });

        location.reload();
    };

});