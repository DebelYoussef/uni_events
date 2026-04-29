<?php
/**
 * User Login Page
 * Allows existing users to authenticate and create sessions
 */

require_once '../../includes/auth.php';


// Redirect if already logged in
redirect_if_logged_in();

// Get flash message if redirected here
$flash_message = get_flash_message();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Système de gestion des événements universitaires</title>
    <link rel="stylesheet" href="../../public/css/style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <h1>Se connecter</h1>

            <?php if ($flash_message): ?>
                <div class="alert alert-<?php echo escape_output($flash_message['type']); ?> alert-dismissible fade show">
                    <?php echo escape_output($flash_message['message']); ?>
                    <button type="button" class="btn-close" onclick="this.parentElement.style.display='none';"></button>
                </div>
            <?php endif; ?>

            <form action="../../actions/login-user.php" method="POST" novalidate>
                <!-- CSRF Token -->
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">

                <!-- Email -->
                <div class="form-group">
                    <label for="email">Adresse e-mail *</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        placeholder="Entrez votre e-mail"
                        required
                        autocomplete="email"
                        value="<?php echo isset($_POST['email']) ? escape_output($_POST['email']) : ''; ?>"
                    >
                    <div class="form-error" id="email-error"></div>
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label for="password">Mot de passe *</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        placeholder="Entrez votre mot de passe"
                        required
                        autocomplete="current-password"
                    >
                    <div class="form-error" id="password-error"></div>
                </div>

                <!-- Remember Me (Optional) -->
                <div class="form-group" style="margin-bottom: calc(var(--spacing-unit) * 3);">
                    <label style="display: flex; align-items: center; margin-bottom: 0; cursor: pointer;">
                        <input 
                            type="checkbox" 
                            id="remember_me" 
                            name="remember_me"
                            style="width: auto; margin-right: 0.5rem;"
                        >
<span>Se souvenir de moi pendant 30 jours</span>
                    </label>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn btn-primary btn-block" id="submit-btn">
                    Se connecter
                </button>
            </form>

            <div class="auth-footer">
                <p>Vous n'avez pas de compte ? <a href="register.php">Créez-en un maintenant</a></p>
                <p style="margin-top: calc(var(--spacing-unit) * 2);">
                    <a href="#forgot-password" style="font-size: 0.875rem;">Mot de passe oublié ?</a>
                </p>
            </div>
        </div>
    </div>

    <script>
        // Form validation before submit
        document.querySelector('form').addEventListener('submit', function(e) {
            let isValid = true;
            
            // Clear previous errors
            document.querySelectorAll('.form-error').forEach(el => el.textContent = '');
            
            // Validate email
            const email = document.getElementById('email').value.trim();
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!email || !emailRegex.test(email)) {
                document.getElementById('email-error').textContent = 'Veuillez entrer une adresse e-mail valide';
                isValid = false;
            }
            
            // Validate password
            const password = document.getElementById('password').value;
            if (!password) {
                document.getElementById('password-error').textContent = 'Mot de passe requis';
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>
