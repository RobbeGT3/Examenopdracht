const state = {
  isOpen: false,
  allergieën: []
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
    el.className = 'tag';
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
document.getElementById('klantForm').addEventListener('submit', e => {
  e.preventDefault();

  const formData = new FormData(e.target);

  fetch('addklant.php', {
    method: 'POST',
    body: formData
  })
  .then(res => {
    console.log("URL:", res.url);
    res.text()})
  .then(() => {
    state.isOpen = false;
    reset();
    render();
    // location.reload();
  });
});

function reset() {
  state.allergieën = [];
  document.getElementById('klantForm').reset();
}

function render() {
  renderModal();
  renderAllergieën();
}

render();