<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wachtwoord Resetten - Voedselbank</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 450px;
            width: 100%;
            padding: 40px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h1 {
            font-size: 28px;
            color: #333;
            margin-bottom: 10px;
        }

        .header p {
            color: #666;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
            font-size: 14px;
        }

        .form-group input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }

        .btn-submit {
            width: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 14px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .message {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
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

        .login-link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
        }

        .login-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }

        .login-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔐 Wachtwoord Resetten</h1>
            <p>Voer uw nieuwe wachtwoord in</p>
        </div>

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

        <form id="resetPasswordForm">
            <input type="hidden" id="token" name="token" value="<?php echo htmlspecialchars($_GET['token'] ?? ''); ?>">

            <div class="form-group">
                <label for="newPassword">Nieuw Wachtwoord</label>
                <input type="password" id="newPassword" name="newPassword" required minlength="6" placeholder="Minimaal 6 tekens">
            </div>

            <div class="form-group">
                <label for="confirmPassword">Bevestig Wachtwoord</label>
                <input type="password" id="confirmPassword" name="confirmPassword" required minlength="6" placeholder="Herhaal uw wachtwoord">
            </div>

            <button type="submit" class="btn-submit">Wachtwoord Wijzigen</button>
        </form>

        <div class="login-link">
            <a href="index.php">← Terug naar inloggen</a>
        </div>
    </div>

    <script>
        document.getElementById('resetPasswordForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            const token = document.getElementById('token').value;

            if (newPassword.length < 6) {
                alert('Wachtwoord moet minimaal 6 tekens bevatten.');
                return;
            }

            if (newPassword !== confirmPassword) {
                alert('Wachtwoorden komen niet overeen.');
                return;
            }

            if (!token) {
                alert('Ongeldige reset link. Vraag een nieuwe link aan.');
                return;
            }

            const formData = new FormData();
            formData.append('token', token);
            formData.append('newPassword', newPassword);

            fetch('actions/processPasswordReset.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = 'reset-password.php?type=success&message=' + encodeURIComponent(data.message);
                } else {
                    window.location.href = 'reset-password.php?type=error&message=' + encodeURIComponent(data.message);
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
