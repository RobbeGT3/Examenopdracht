<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wachtwoord Vergeten - Voedselbank</title>
    <link rel="stylesheet" href="styles/styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"/>
    <style>
        .forgot-password-container {
            max-width: 400px;
            margin: 100px auto;
            padding: 40px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        .forgot-password-container h1 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }
        .forgot-password-container p {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
        }
        .forgot-password-container .form-group {
            margin-bottom: 20px;
        }
        .forgot-password-container label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }
        .forgot-password-container input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
        }
        .forgot-password-container button {
            width: 100%;
            padding: 12px;
            background: #2e7d32;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
        }
        .forgot-password-container button:hover {
            background: #25692a;
        }
        .forgot-password-container .back-link {
            text-align: center;
            margin-top: 20px;
        }
        .forgot-password-container .back-link a {
            color: #2e7d32;
            text-decoration: none;
        }
        .forgot-password-container .back-link a:hover {
            text-decoration: underline;
        }
        .message {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <header class="topbar">
        <div class="topbar-title">
            <span>Food Bank App</span>
            <i class="bi bi-caret-down-fill"></i>
        </div>
    </header>

    <main class="page">
        <div class="forgot-password-container">
            <h1>🔐 Wachtwoord Vergeten</h1>
            <p>Voer uw e-mailadres in om een reset link te ontvangen</p>

            <?php if (isset($_GET['message'])): ?>
                <?php if ($_GET['type'] === 'success'): ?>
                    <div class="message success">
                        <?php echo htmlspecialchars($_GET['message']); ?>
                    </div>
                <?php elseif ($_GET['type'] === 'error'): ?>
                    <div class="message error">
                        <?php echo htmlspecialchars($_GET['message']); ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <form id="forgotPasswordForm">
                <div class="form-group">
                    <label for="email">E-mailadres</label>
                    <input type="email" id="email" name="email" required placeholder="Uw e-mailadres">
                </div>
                <button type="submit">Reset Link Versturen</button>
            </form>

            <div class="back-link">
                <a href="index.php">← Terug naar inloggen</a>
            </div>
        </div>
    </main>

    <script>
        document.getElementById('forgotPasswordForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const email = document.getElementById('email').value;

            if (!email) {
                alert('Voer een e-mailadres in.');
                return;
            }

            const formData = new FormData();
            formData.append('email', email);

            fetch('actions/sendPasswordReset.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = 'forgot-password.php?type=success&message=' + encodeURIComponent(data.message);
                } else {
                    window.location.href = 'forgot-password.php?type=error&message=' + encodeURIComponent(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Er is een fout opgetreden. Probeer het opnieuw.');
            });
        });
    </script>
</body>
</html>
