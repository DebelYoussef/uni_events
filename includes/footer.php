<?php
/**
 * Footer Partial
 * Included at the bottom of all pages
 */
?>
    </main>

    <footer>
        <div class="container">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem; margin-bottom: 2rem;">
                <!-- About Section -->
                <div>
                    <h4 style="color: white; margin-bottom: 1rem;">UniEvents</h4>
                    <p style="margin-bottom: 0;">
                        Un système complet de gestion des événements universitaires conçu pour simplifier l'organisation, l'inscription et le suivi de la participation.
                    </p>
                </div>

                <!-- Quick Links -->
                <div>
                    <h5 style="color: white; margin-bottom: 1rem;">Liens rapides</h5>
                    <ul style="list-style: none;">
                        <li><a href="<?php echo BASE_URL; ?>index.php">Accueil</a></li>
                        <li><a href="<?php echo BASE_URL; ?>pages/auth/login.php">Connexion</a></li>
                        <li><a href="<?php echo BASE_URL; ?>pages/auth/register.php">Inscription</a></li>
                    </ul>
                </div>

                <!-- Support -->
                <div>
                    <h5 style="color: white; margin-bottom: 1rem;">Assistance</h5>
                    <ul style="list-style: none;">
                        <li><a href="#">Aide & FAQ</a></li>
                        <li><a href="#">Contactez-nous</a></li>
                        <li><a href="#">Signaler un problème</a></li>
                    </ul>
                </div>

                <!-- Information -->
                <div>
                    <h5 style="color: white; margin-bottom: 1rem;">Informations</h5>
                    <ul style="list-style: none;">
                        <li><a href="#">Politique de confidentialité</a></li>
                        <li><a href="#">Conditions d'utilisation</a></li>
                        <li><a href="#">Code de conduite</a></li>
                    </ul>
                </div>
            </div>

            <div style="border-top: 1px solid #374151; padding-top: 2rem; text-align: center;">
                <p style="margin-bottom: 0.5rem;">
                    &copy; <?php echo date('Y'); ?> Système de gestion des événements universitaires. Tous droits réservés.
                </p>
                <p style="margin-bottom: 0; font-size: 0.875rem; color: #9ca3af;">
                    Conçu pour faciliter la gestion et la participation aux événements universitaires
                </p>
            </div>
        </div>
    </footer>

    <script src="<?php echo BASE_URL; ?>public/js/main.js"></script>
    <?php if (isset($additional_js)): ?>
        <?php echo $additional_js; ?>
    <?php endif; ?>
</body>
</html>
