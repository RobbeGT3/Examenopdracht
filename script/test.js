const modal = document.querySelector('.modal-overlay');
const openBtn = document.querySelector('.btn-add');
const closeBtn = document.querySelector('.close-btn');
const cancelBtn = document.querySelector('.btn-muted');
const form = document.querySelector('form');


openBtn.addEventListener('click', () => {
  modal.classList.add('active');
});

closeBtn.addEventListener('click', closeModal);
cancelBtn.addEventListener('click', closeModal);

modal.addEventListener('click', (e) => {
  if (e.target === modal) {
    closeModal();
  }
});

function closeModal() {
  modal.classList.remove('active');
}

function addAllergie(name) {
  allergieën.push(name);
  renderAllergieën();
}

function removeAllergie(index) {
  allergieën.splice(index, 1);
  renderAllergieën();
}

function renderAllergieën() {
  allergieContainer.innerHTML = '';

  allergieën.forEach((a, i) => {
    const tag = document.createElement('span');
    tag.className = 'tag';
    tag.innerHTML = `${a} <button onclick="removeAllergie(${i})">x</button>`;
    allergieContainer.appendChild(tag);
  });

}

document.querySelectorAll('.button-group button').forEach(btn => {
  btn.addEventListener('click', () => {
    addAllergie(btn.innerText.replace('+ ', ''));
  });
});



form.addEventListener('submit', function(e) {
  e.preventDefault();

  const formData = new FormData(form);

  fetch('actions/addKlant.php', {
    method: 'POST',
    body: formData
  })
  .then(res => res.text())
  .then(data => {
    console.log(data);

    closeModal();
    form.reset();
    location.reload();
  });
});