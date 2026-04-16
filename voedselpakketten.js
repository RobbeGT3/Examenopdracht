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

        renderSelected();
    }

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

            div.textContent = `${p.productnaam} (${p.product_categorie}) - voorraad: ${p.aantal}`;

            div.addEventListener("click", () => {
                selectedProduct = p;
                productSearch.value = p.productnaam;
                productOptions.classList.add("hidden");
            });

            productOptions.appendChild(div);
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
            allergyText.textContent = f.allergies;
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
                producten: selectedProducts
            })
        });

        // const result = await res.json();
        location.reload();

        // if (result.success) {
        //     alert("Pakket opgeslagen");
        //     location.reload();
        // }
    });

});