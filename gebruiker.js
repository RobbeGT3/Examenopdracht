// Modal functionality
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('userModal');
    const btn = document.querySelector('.btn');
    const span = document.querySelector('.close');
    const cancelBtn = document.querySelector('.btn-cancel');
    const form = document.getElementById('userForm');
    const generateBtn = document.getElementById('generatePassword');
    const toggleBtn = document.getElementById('togglePassword');

    // Password modal variables
    const passwordModal = document.getElementById('passwordModal');
    const passwordForm = document.getElementById('passwordForm');
    const closePasswordBtn = document.querySelector('.close-password');
    const cancelPasswordBtn = document.querySelector('.btn-cancel-password');
    const generateNewPasswordBtn = document.getElementById('generateNewPassword');
    const toggleNewPasswordBtn = document.getElementById('toggleNewPassword');
    const toggleConfirmPasswordBtn = document.getElementById('toggleConfirmPassword');

    // Edit modal variables
    const editModal = document.getElementById('editModal');
    const editForm = document.getElementById('editForm');
    const closeEditBtn = document.querySelector('.close-edit');
    const cancelEditBtn = document.querySelector('.btn-cancel-edit');
    const toggleEditPasswordBtn = document.getElementById('toggleEditPassword');
    const generateEditPasswordBtn = document.getElementById('generateEditPassword');

    // Open modal when clicking the "Nieuwe Gebruiker" button
    btn.addEventListener('click', function () {
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden'; // Prevent background scrolling
    });

    // Close modal when clicking the X button
    span.addEventListener('click', closeModal);

    // Close modal when clicking the Cancel button
    cancelBtn.addEventListener('click', closeModal);

    // Close modal when clicking outside of it
    window.addEventListener('click', function (event) {
        if (event.target === modal) {
            closeModal();
        }
    });

    // Password visibility toggle
    toggleBtn.addEventListener('click', function () {
        const passwordInput = document.getElementById('password');
        const type = passwordInput.type === 'password' ? 'text' : 'password';
        passwordInput.type = type;

        // Change button text
        this.textContent = type === 'password' ? 'o' : 'o';
        this.style.fontStyle = type === 'password' ? 'normal' : 'italic';
    });

    // Password generation
    generateBtn.addEventListener('click', function () {
        const password = generatePassword();
        document.getElementById('password').value = password;
    });

    // Password modal event listeners
    closePasswordBtn.addEventListener('click', closePasswordModal);
    cancelPasswordBtn.addEventListener('click', closePasswordModal);

    // Close password modal when clicking outside
    window.addEventListener('click', function (event) {
        if (event.target === passwordModal) {
            closePasswordModal();
        }
    });

    // Close password modal when pressing Escape
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            if (modal.style.display === 'block') {
                closeModal();
            } else if (passwordModal.style.display === 'block') {
                closePasswordModal();
            } else if (editModal.style.display === 'block') {
                closeEditModal();
            }
        }
    });

    // Password generation for password modal
    generateNewPasswordBtn.addEventListener('click', function () {
        const password = generatePassword();
        document.getElementById('newPassword').value = password;
        document.getElementById('confirmPassword').value = password;
    });

    // Password visibility toggles for password modal
    toggleNewPasswordBtn.addEventListener('click', function () {
        const passwordInput = document.getElementById('newPassword');
        const type = passwordInput.type === 'password' ? 'text' : 'password';
        passwordInput.type = type;
        this.style.fontStyle = type === 'password' ? 'normal' : 'italic';
    });

    toggleConfirmPasswordBtn.addEventListener('click', function () {
        const passwordInput = document.getElementById('confirmPassword');
        const type = passwordInput.type === 'password' ? 'text' : 'password';
        passwordInput.type = type;
        this.style.fontStyle = type === 'password' ? 'normal' : 'italic';
    });

    // Edit modal event listeners
    closeEditBtn.addEventListener('click', closeEditModal);
    cancelEditBtn.addEventListener('click', closeEditModal);

    // Edit password toggle
    toggleEditPasswordBtn.addEventListener('click', function () {
        const passwordInput = document.getElementById('editPassword');
        const type = passwordInput.type === 'password' ? 'text' : 'password';
        passwordInput.type = type;
        this.style.fontStyle = type === 'password' ? 'normal' : 'italic';
    });

    // Edit password generation
    generateEditPasswordBtn.addEventListener('click', function () {
        const password = generatePassword();
        document.getElementById('editPassword').value = password;
    });

    // Password form submission
    passwordForm.addEventListener('submit', function (e) {
        e.preventDefault();

        const newPassword = document.getElementById('newPassword').value;
        const confirmPassword = document.getElementById('confirmPassword').value;
        const username = document.getElementById('passwordUsername').value;

        if (newPassword.length < 6) {
            alert('Wachtwoord moet minimaal 6 tekens bevatten.');
            return;
        }

        if (newPassword !== confirmPassword) {
            alert('Wachtwoorden komen niet overeen.');
            return;
        }

        // Send data to backend
        const formData = new FormData();
        formData.append('username', username);
        formData.append('newPassword', newPassword);

        fetch('actions/changePassword.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Wachtwoord succesvol gewijzigd!');
                    closePasswordModal();
                } else {
                    alert('Er is een fout opgetreden: ' + (data.message || 'Onbekende fout'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Er is een fout opgetreden bij het wijzigen van het wachtwoord.');
            });
    });

    // Close modal when pressing Escape key
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            if (modal.style.display === 'block') {
                closeModal();
            } else if (editModal.style.display === 'block') {
                closeEditModal();
            }
        }
    });

    function closeModal() {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto'; // Restore scrolling
        form.reset(); // Clear form fields
    }

    function closePasswordModal() {
        passwordModal.style.display = 'none';
        document.body.style.overflow = 'auto'; // Restore scrolling
        passwordForm.reset(); // Clear form fields
    }

    function closeEditModal() {
        editModal.style.display = 'none';
        document.body.style.overflow = 'auto'; // Restore scrolling
        editForm.reset(); // Clear form fields
    }

    function openEditModal(row) {
        const username = row.dataset.username;
        const email = row.dataset.email;
        const role = row.dataset.role;

        document.getElementById('originalUsername').value = username;
        document.getElementById('editUsername').value = username;
        document.getElementById('editEmail').value = email;
        document.getElementById('editRole').value = role;
        document.getElementById('editPassword').value = ''; // Always empty

        editModal.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }

    // Handle form submission
    form.addEventListener('submit', function (e) {
        e.preventDefault();

        const username = document.getElementById('username').value.trim();
        const password = document.getElementById('password').value;
        const email = document.getElementById('email').value.trim();
        const role = document.getElementById('role').value;

        // Basic validation
        if (!username || !password || !email || !role) {
            alert('Vul alle verplichte velden in.');
            return;
        }

        if (username.length < 3) {
            alert('Gebruikersnaam moet minimaal 3 tekens bevatten.');
            return;
        }

        if (password.length < 6) {
            alert('Wachtwoord moet minimaal 6 tekens bevatten.');
            return;
        }

        // Email validation
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            alert('Voer een geldig e-mailadres in.');
            return;
        }

        // Send data to backend
        const formData = new FormData();
        formData.append('username', username);
        formData.append('password', password);
        formData.append('email', email);
        formData.append('role', role);

        fetch('actions/createUser.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Gebruiker succesvol aangemaakt!');
                    // Create new user (add to table)
                    addUserToTable(username, role, 'actief');
                    // Close modal and reset form
                    closeModal();
                } else {
                    alert('Er is een fout opgetreden: ' + (data.message || 'Onbekende fout'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Er is een fout opgetreden bij het aanmaken van de gebruiker.');
            });
    });

    // Handle edit form submission
    editForm.addEventListener('submit', function (e) {
        e.preventDefault();

        const originalUsername = document.getElementById('originalUsername').value;
        const username = document.getElementById('editUsername').value.trim();
        const email = document.getElementById('editEmail').value.trim();
        const role = document.getElementById('editRole').value;
        const password = document.getElementById('editPassword').value;

        // Basic validation
        if (!username || !email || !role) {
            alert('Vul alle verplichte velden in.');
            return;
        }

        if (username.length < 3) {
            alert('Gebruikersnaam moet minimaal 3 tekens bevatten.');
            return;
        }

        // Email validation
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            alert('Voer een geldig e-mailadres in.');
            return;
        }

        // Password validation (only if provided)
        if (password && password.length < 6) {
            alert('Wachtwoord moet minimaal 6 tekens bevatten.');
            return;
        }

        // Send data to backend
        const formData = new FormData();
        formData.append('originalUsername', originalUsername);
        formData.append('username', username);
        formData.append('email', email);
        formData.append('role', role);
        if (password) {
            formData.append('password', password);
        }

        fetch('actions/updateUser.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Gebruiker succesvol bijgewerkt!');
                    // Reload page to show updated data
                    window.location.reload();
                } else {
                    alert('Er is een fout opgetreden: ' + (data.message || 'Onbekende fout'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Er is een fout opgetreden bij het bijwerken van de gebruiker.');
            });
    });

    function addUserToTable(username, role, status) {
        const table = document.querySelector('tbody');
        const newRow = document.createElement('tr');

        // Determine badge color based on role
        let badgeClass = '';
        let roleText = '';
        switch (role) {
            case 'directeur':
                badgeClass = 'purple';
                roleText = 'Directeur';
                break;
            case 'magazijnmedewerker':
                badgeClass = 'blue';
                roleText = 'Magazijnmedewerker';
                break;
            case 'vrijwilliger':
                badgeClass = 'green';
                roleText = 'Vrijwilliger';
                break;
        }

        // Determine status display
        const statusDisplay = status === 'actief' ?
            '<span class="dot green"></span>Actief' :
            '<span class="dot red"></span>Inactief';

        newRow.innerHTML = `
            <td>${username}</td>
            <td><span class="badge ${badgeClass}">${roleText}</span></td>
            <td class="status-cell ${status === 'inactief' ? 'inactive' : ''}">${statusDisplay}</td>
            <td class="actions">
                <button class="edit"></button>
                <button class="password"></button>
                <button class="delete"></button>
            </td>
        `;

        table.appendChild(newRow);

        // Add event listeners to new buttons
        const editBtn = newRow.querySelector('.edit');
        const deleteBtn = newRow.querySelector('.delete');
        const passwordBtn = newRow.querySelector('.password');
        const statusCell = newRow.querySelector('.status-cell');
        editBtn.addEventListener('click', function () {
            openEditModal(newRow);
        });

        deleteBtn.addEventListener('click', function () {
            if (confirm(`Gebruiker "${username}" verwijderen?`)) {
                const formData = new FormData();
                formData.append('username', username);

                fetch('actions/deleteUser.php', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Gebruiker succesvol verwijderd!');
                            newRow.remove();
                        } else {
                            alert('Er is een fout opgetreden: ' + (data.message || 'Onbekende fout'));
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Er is een fout opgetreden bij het verwijderen van de gebruiker.');
                    });
            }
        });

        passwordBtn.addEventListener('click', function () {
            openPasswordModal(username);
        });

        statusCell.addEventListener('click', function () {
            toggleUserStatus(this);
        });
    }

    function openPasswordModal(username) {
        document.getElementById('passwordUsername').value = username;
        passwordModal.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }

    function toggleUserStatus(statusCell) {
        const row = statusCell.closest('tr');
        const username = row.cells[0].textContent;
        const status = statusCell.classList.contains('inactive') ? 'actief' : 'inactief';

        // Update status display
        const statusDisplay = status === 'actief' ?
            '<span class="dot green"></span>Actief' :
            '<span class="dot red"></span>Inactief';
        statusCell.innerHTML = statusDisplay;

        // Toggle inactive class
        statusCell.classList.toggle('inactive');

        console.log(`User "${username}" status toggled to ${status}`);
    }

    // Add event listeners to existing edit and delete buttons
    document.querySelectorAll('.edit').forEach(btn => {
        btn.addEventListener('click', function () {
            const row = this.closest('tr');
            openEditModal(row);
        });
    });

    document.querySelectorAll('.delete').forEach(btn => {
        btn.addEventListener('click', function () {
            const row = this.closest('tr');
            const username = row.cells[0].textContent;
            if (confirm(`Gebruiker "${username}" verwijderen?`)) {
                // Send data to backend
                const formData = new FormData();
                formData.append('username', username);

                fetch('actions/deleteUser.php', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Gebruiker succesvol verwijderd!');
                            row.remove();
                        } else {
                            alert('Er is een fout opgetreden: ' + (data.message || 'Onbekende fout'));
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Er is een fout opgetreden bij het verwijderen van de gebruiker.');
                    });
            }
        });
    });

    // Add event listeners to existing status cells
    document.querySelectorAll('.status-cell').forEach(cell => {
        cell.addEventListener('click', function () {
            toggleUserStatus(this);
        });
    });

    // Add event listeners to existing password buttons
    document.querySelectorAll('.password').forEach(btn => {
        btn.addEventListener('click', function () {
            const row = this.closest('tr');
            const username = row.cells[0].textContent;
            openPasswordModal(username);
        });
    });
});

// Standalone helper function for password generation
function generatePassword() {
    const length = 12;
    const charset = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
    let password = '';

    // Ensure at least one of each type
    const lowercase = 'abcdefghijklmnopqrstuvwxyz';
    const uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    const numbers = '0123456789';
    const symbols = '!@#$%^&*';

    password += lowercase[Math.floor(Math.random() * lowercase.length)];
    password += uppercase[Math.floor(Math.random() * uppercase.length)];
    password += numbers[Math.floor(Math.random() * numbers.length)];
    password += symbols[Math.floor(Math.random() * symbols.length)];

    // Fill the rest
    for (let i = 4; i < length; i++) {
        password += charset[Math.floor(Math.random() * charset.length)];
    }

    // Shuffle the password
    return password.split('').sort(() => Math.random() - 0.5).join('');
}

console.log("UI geladen");