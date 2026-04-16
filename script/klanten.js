const state = {
  isOpen: false,
  allergieën: [],
  origineleAllergieën: [],
  wensen:[],
  origineleWensen:[]
};

const modal = document.querySelector('.modal-overlay');
const openBtn = document.querySelector('.btn-add');
const closeBtn = document.querySelector('.close-btn');
const cancelBtn = document.querySelector('.btn-muted');

openBtn.onclick = () => {
  state.isOpen = true;
  render();
};

closeBtn.onclick = cancelBtn.onclick = () => {
  state.isOpen = false;
  reset();
  render();
};

function renderModal() {
  modal.style.display = state.isOpen ? 'flex' : 'none';
}

document.querySelectorAll('[data-allergie]').forEach(btn => {
  btn.onclick = () => {
    const val = btn.dataset.allergie;

    if (!state.allergieën.includes(val)) {
      state.allergieën.push(val);
      render();
    }
  };
});

function renderAllergieën() {
  const container = document.getElementById('allergieTags');
  const hidden = document.getElementById('allergieënInput');

  container.innerHTML = '';

  state.allergieën.forEach((a, i) => {
    const el = document.createElement('span');
    el.className = 'tag tag-red';
    el.innerHTML = `${a} <button data-i="${i}">x</button>`;
    container.appendChild(el);
  });

  container.querySelectorAll('button').forEach(btn => {
    btn.onclick = () => {
      state.allergieën.splice(btn.dataset.i, 1);
      render();
    };
  });

  hidden.value = JSON.stringify(state.allergieën);
}

const customBtn = document.getElementById('customBtn');
const customDiv = document.getElementById('customInput');
const customInput = document.getElementById('customAllergie');
const customCancel = document.getElementById('cancelCustom');

customBtn.onclick = () => {
  customDiv.style.display = 'block';
};

customCancel.onclick = () =>{
  customDiv.style.display = 'none';
}

document.getElementById('addCustom').onclick = () => {
  const val = customInput.value.trim();

  if (val) {
    state.allergieën.push(val);
    customInput.value = '';
    customDiv.style.display = 'none';
    render();
  }
};

document.querySelectorAll('.btn-edit').forEach(btn => {
  btn.onclick = () => {
    const klant = JSON.parse(btn.dataset.klant);

    state.isOpen = true;
    state.allergieën = klant.allergenen ? klant.allergenen.split(',') : [];
    state.origineleAllergieën = [...state.allergieën];

    document.querySelector('[name="voornaam"]').value = klant.voornaam || '';
    document.querySelector('[name="achternaam"]').value = klant.achternaam || '';
    document.querySelector('[name="adres"]').value = klant.adres || '';
    document.querySelector('[name="postcode"]').value = klant.postcode || '';
    document.querySelector('[name="woonplaats"]').value = klant.woonplaats || '';
    document.querySelector('[name="telefoonnummer"]').value = klant.telefoonnummer || '';
    document.querySelector('[name="email"]').value = klant['e-mailadres'] || '';
    document.querySelector('[name="volwassenen"]').value = klant['aantal_volwassen'] || '';
    document.querySelector('[name="kinderen"]').value = klant['aantal_kinderen'] || '';
    document.querySelector('[name="babys"]').value = klant["aantal_babies"] || '';


    const wensenIds = klant.wensen_ids ? klant.wensen_ids.split(',') : [];

    state.wensen = [...wensenIds];
    state.origineleWensen = [...wensenIds];

    document.querySelectorAll('[name="wensen[]"]').forEach(cb => {
      cb.checked = wensenIds.includes(cb.value);
    });

    state.allergieën = klant.allergenen
      ? klant.allergenen.split(',').map(a => a.trim())
      : [];


    document.getElementById('klantForm').dataset.id = klant.idKlanten;

    render();
  };
});

document.querySelectorAll('[name="wensen[]"]').forEach(cb => {
  cb.onchange = () => {
    if (cb.checked) {
      if (!state.wensen.includes(cb.value)) {
        state.wensen.push(cb.value);
      }
    } else {
      state.wensen = state.wensen.filter(w => w !== cb.value);
    }
  };
});

document.getElementById('klantForm').addEventListener('submit', e => {
  e.preventDefault();

  const formData = new FormData(e.target);

  const id = e.target.dataset.id;
  let url;
  if (id) {
    formData.append('id', id);
    const verwijderdAllergenen = state.origineleAllergieën.filter(a => !state.allergieën.includes(a));
    const toegevoegdAllergenen = state.allergieën.filter(a => !state.origineleAllergieën.includes(a));
    const verwijderdeWensen = state.origineleWensen.filter(w => !state.wensen.includes(w));
    const toegevoegdeWensen = state.wensen.filter(w => !state.origineleWensen.includes(w));

    formData.append('allergieën_toegevoegd', JSON.stringify(toegevoegdAllergenen));
    formData.append('allergieën_verwijderd', JSON.stringify(verwijderdAllergenen));
    formData.append('wensen_toegevoegd', JSON.stringify(toegevoegdeWensen));
    formData.append('wensen_verwijderd', JSON.stringify(verwijderdeWensen));
    url = 'actions/updateKlant.php';
  } else {
    url = 'actions/addKlant.php';
  }
  fetch(url, {
      method: 'POST',
      body: formData
    })
  .then(res => res.text())
  .then(() => {
    state.isOpen = false;
    reset();
    render();
    location.reload();
  });
});

function reset() {
  state.allergieën = [];
  state.origineleAllergieën = [];
  state.wensen = [];
  state.origineleWensen = [];
  document.getElementById('klantForm').reset();

  document.querySelectorAll('[name="wensen[]"]').forEach(cb => {
    cb.checked = false;
  });

  delete document.getElementById('klantForm').dataset.id;
}

function render() {
  renderModal();
  renderAllergieën();
}

render();