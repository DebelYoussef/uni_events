<?php
/**
 * User Registration Page
 * Allows new users to create accounts with role selection
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
    <title>Inscription - Système de gestion des événements universitaires</title>
    <link rel="stylesheet" href="../../public/css/style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <h1>Créer un compte</h1>

            <?php if ($flash_message): ?>
                <div class="alert alert-<?php echo escape_output($flash_message['type']); ?> alert-dismissible fade show">
                    <?php echo escape_output($flash_message['message']); ?>
                    <button type="button" class="btn-close" onclick="this.parentElement.style.display='none';"></button>
                </div>
            <?php endif; ?>

            <form action="../../actions/register-user.php" method="POST" novalidate>
                <!-- CSRF Token -->
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">

                <!-- Full Name -->
                <div class="form-group">
                    <label for="name">Nom complet *</label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        placeholder="Entrez votre nom complet"
                        required
                        minlength="2"
                        maxlength="100"
                        value="<?php echo isset($_POST['name']) ? escape_output($_POST['name']) : ''; ?>"
                    >
                    <div class="form-error" id="name-error"></div>
                </div>

                <!-- Email -->
                <div class="form-group">
                    <label for="email">Adresse e-mail *</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        placeholder="Entrez votre e-mail"
                        required
                        value="<?php echo isset($_POST['email']) ? escape_output($_POST['email']) : ''; ?>"
                    >
                    <div class="form-error" id="email-error"></div>
                </div>

                <!-- Student ID (Optional) -->
                <div class="form-group">
                    <label for="student_id">ID étudiant (facultatif)</label>
                    <input 
                        type="text" 
                        id="student_id" 
                        name="student_id" 
                        placeholder="ex. STU2024001"
                        maxlength="20"
                        value="<?php echo isset($_POST['student_id']) ? escape_output($_POST['student_id']) : ''; ?>"
                    >
                    <div class="form-help">Requis pour le rôle étudiant</div>
                </div>

                <!-- Role Selection -->
                <div class="form-group">
                    <label for="role">Type de compte *</label>
                    <select id="role" name="role" required onchange="updateRoleInfo()">
                        <option value="">-- Sélectionnez le type de compte --</option>
                        <option value="student" <?php echo isset($_POST['role']) && $_POST['role'] === 'student' ? 'selected' : ''; ?>>
                            Étudiant
                        </option>
                        <option value="organizer" <?php echo isset($_POST['role']) && $_POST['role'] === 'organizer' ? 'selected' : ''; ?>>
                            Organisateur d'événements
                        </option>
                    </select>
                    <div class="form-help" id="role-info"></div>
                    <div class="form-error" id="role-error"></div>
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label for="password">Mot de passe *</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        placeholder="Entrez un mot de passe sécurisé"
                        required
                        minlength="8"
                    >
                    <div class="form-help">
                        Exigences du mot de passe :
                        <ul style="margin-top: 0.5rem; margin-left: 1.5rem; font-size: 0.875rem;">
                            <li id="req-length">Au moins 8 caractères</li>
                            <li id="req-uppercase">Au moins une lettre majuscule (A-Z)</li>
                            <li id="req-number">Au moins un chiffre (0-9)</li>
                            <li id="req-special">Au moins un caractère spécial (!@#$%^&*)</li>
                        </ul>
                    </div>
                    <div class="form-error" id="password-error"></div>
                </div>

                <!-- Confirm Password -->
                <div class="form-group">
                    <label for="confirm_password">Confirmez le mot de passe *</label>
                    <input 
                        type="password" 
                        id="confirm_password" 
                        name="confirm_password" 
                        placeholder="Saisissez à nouveau votre mot de passe"
                        required
                    >
                    <div class="form-error" id="confirm-password-error"></div>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn btn-primary btn-block" id="submit-btn">
                    Créer un compte
                </button>
            </form>

            <div class="auth-footer">
                <p>Vous avez déjà un compte ? <a href="login.php">Se connecter</a></p>
            </div>
        </div>
    </div>

    <script>
        // Password strength indicator
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('confirm_password');
        const roleSelect = document.getElementById('role');

        // Update password requirements feedback
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            
            document.getElementById('req-length').style.color = 
                password.length >= 8 ? '#10b981' : '#6b7280';
            document.getElementById('req-uppercase').style.color = 
                /[A-Z]/.test(password) ? '#10b981' : '#6b7280';
            document.getElementById('req-number').style.color = 
                /[0-9]/.test(password) ? '#10b981' : '#6b7280';
            document.getElementById('req-special').style.color = 
                /[!@#$%^&*()_+\-=\[\]{};:'",./?\\|`~]/.test(password) ? '#10b981' : '#6b7280';
        });

        // Validate confirm password
        confirmPasswordInput.addEventListener('input', function() {
            if (this.value !== passwordInput.value) {
                document.getElementById('confirm-password-error').textContent = 'Les mots de passe ne correspondent pas';
            } else {
                document.getElementById('confirm-password-error').textContent = '';
            }
        });

        // Update role-specific info
        function updateRoleInfo() {
            const role = roleSelect.value;
            const roleInfo = document.getElementById('role-info');
            
            if (role === 'student') {
                roleInfo.textContent = '🎓 Compte étudiant - Inscrivez-vous aux événements et téléchargez des certificats';
            } else if (role === 'organizer') {
                roleInfo.textContent = '📋 Compte organisateur - Créez et gérez des événements (nécessite l'approbation de l'administrateur)';
            } else {
                roleInfo.textContent = '';
            }
        }

        // Form validation before submit
        document.querySelector('form').addEventListener('submit', function(e) {
            let isValid = true;
            
            // Clear previous errors
            document.querySelectorAll('.form-error').forEach(el => el.textContent = '');
            
            // Validate name
            const name = document.getElementById('name').value.trim();
            if (!name || name.length < 2) {
                    document.getElementById('name-error').textContent = 'Le nom doit comporter au moins 2 caractères';
                    isValid = false;
                }
                
                // Validate email
                const email = document.getElementById('email').value.trim();
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!email || !emailRegex.test(email)) {
                    document.getElementById('email-error').textContent = 'Veuillez entrer une adresse e-mail valide';
                    isValid = false;
                }
                
                // Validate role
                const role = document.getElementById('role').value;
                if (!role) {
                    document.getElementById('role-error').textContent = 'Veuillez sélectionner un type de compte';
                    isValid = false;
                }
                
                // Validate password
                const password = document.getElementById('password').value;
                const confirmPassword = document.getElementById('confirm_password').value;
                
                if (!password || password.length < 8) {
                    document.getElementById('password-error').textContent = 'Le mot de passe doit comporter au moins 8 caractères';
                    isValid = false;
                } else if (!/[A-Z]/.test(password)) {
                    document.getElementById('password-error').textContent = 'Le mot de passe doit contenir au moins une lettre majuscule';
                    isValid = false;
                } else if (!/[0-9]/.test(password)) {
                    document.getElementById('password-error').textContent = 'Le mot de passe doit contenir au moins un chiffre';
                    isValid = false;
                } else if (!/[!@#$%^&*()_+\-=\[\]{};:'",./?\\|`~]/.test(password)) {
                    document.getElementById('password-error').textContent = 'Le mot de passe doit contenir au moins un caractère spécial';
                    isValid = false;
                }
                
                if (password !== confirmPassword) {
                    document.getElementById('confirm-password-error').textContent = 'Les mots de passe ne correspondent pas';
            }
            
            if (!isValid) {
                e.preventDefault();
            }
        });

        // Initialize role info
        updateRoleInfo();
    </script>
</body>
</html>
