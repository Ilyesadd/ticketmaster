<footer class="bg-dark text-white py-4 mt-5">
    <div class="container">
        <div class="row">
            <div class="col-md-4">
                <h5>TicketMaster</h5>
                <p>Votre plateforme de revente de tickets en toute sécurité.</p>
            </div>
            <div class="col-md-4">
                <h5>Liens utiles</h5>
                <ul class="list-unstyled">
                    <li><a href="/Ticket/tickets/index.php" class="text-white">Accueil</a></li>
                    <li><a href="/Ticket/tickets/add.php" class="text-white">Vendre un ticket</a></li>
                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <li><a href="/Ticket/auth/login.php" class="text-white">Connexion</a></li>
                        <li><a href="/Ticket/auth/register.php" class="text-white">Inscription</a></li>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="col-md-4">
                <h5>Contact</h5>
                <p>Email: contact@ticketmaster.fr</p>
                <p>Téléphone: +33 1 23 45 67 89</p>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-12 text-center">
                <p class="mb-0">&copy; <?php echo date('Y'); ?> TicketMaster. Tous droits réservés.</p>
            </div>
        </div>
    </div>
</footer>