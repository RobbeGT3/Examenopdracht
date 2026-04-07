const menuButtons = document.querySelectorAll('.menu-btn');
const logoutBtn = document.getElementById('logoutBtn');

// const currentPath = window.location.pathname;

// menuButtons.forEach(link => {
//   const href = link.getAttribute('href');

//   if (currentPath.includes(href)) {
//     link.classList.add('active');
//   }
// });

logoutBtn.addEventListener('click', () => {
  const confirmed = confirm('Weet je zeker dat je wilt uitloggen?');

  if (confirmed) {
    window.location.href = "actions/logout.php";
  }
});
