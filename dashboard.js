const menuButtons = document.querySelectorAll('.menu-btn');
const dashboardView = document.getElementById('dashboardView');
const pages = document.querySelectorAll('.placeholder-page');
const logoutBtn = document.getElementById('logoutBtn');

const appData = {
  totalProducts: 500,
  activeClients: 2,
  suppliers: 2,
  packagesGiven: 0,
  recentPackagesText: 'Nog geen pakketten samengesteld',
  stockWarningText: 'Alle producten hebben voldoende voorraad'
};

function renderDashboard() {
  document.getElementById('totalProducts').textContent = appData.totalProducts;
  document.getElementById('activeClients').textContent = appData.activeClients;
  document.getElementById('suppliers').textContent = appData.suppliers;
  document.getElementById('packagesGiven').textContent = appData.packagesGiven;
  document.getElementById('recentPackagesText').textContent = appData.recentPackagesText;
  document.getElementById('stockWarningText').textContent = appData.stockWarningText;
}

function showPage(pageName) {
  menuButtons.forEach((btn) => btn.classList.remove('active'));

  const currentBtn = document.querySelector(`[data-page="${pageName}"]`);
  if (currentBtn) {
    currentBtn.classList.add('active');
  }

  if (pageName === 'dashboard') {
    dashboardView.classList.remove('hidden');
    pages.forEach((page) => page.classList.remove('active'));
  } else {
    dashboardView.classList.add('hidden');
    pages.forEach((page) => page.classList.remove('active'));

    const activePage = document.getElementById(pageName);
    if (activePage) {
      activePage.classList.add('active');
    }
  }
}

menuButtons.forEach((button) => {
  button.addEventListener('click', () => {
    const page = button.dataset.page;
    showPage(page);
  });
});

logoutBtn.addEventListener('click', () => {
  const confirmed = confirm('Weet je zeker dat je wilt uitloggen?');

  if (confirmed) {
    // alert('Je bent uitgelogd.');
    // showPage('dashboard');
    window.location.href = "actions/logout.php";
  }
});

// renderDashboard();
// showPage('dashboard');
