<header class="bg-dark text-white py-3">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-4">
                <h1 class="mb-0"><a href="/Ticket/tickets/index.php" class="text-white text-decoration-none">TicketMaster</a></h1>
            </div>
            <div class="col-md-8">
                <nav class="d-flex justify-content-end">
                    <ul class="nav">
                        <li class="nav-item">
                            <a class="nav-link text-white" href="/Ticket/tickets/index.php">Accueil</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="/Ticket/contact.php">Contact</a>
                        </li>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    Mon compte
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                                    <li><a class="dropdown-item" href="/Ticket/tickets/my_tickets.php">Mes tickets</a></li>
                                    <li><a class="dropdown-item" href="/Ticket/tickets/add.php">Ajouter un ticket</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="/Ticket/tickets/my_messages.php">Messages reçus (vendeur)</a></li>
                                    <li><a class="dropdown-item" href="/Ticket/tickets/sent_messages.php">Mes conversations</a></li>
                                    <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item" href="/Ticket/admin/dashboard.php">Administration</a></li>
                                    <?php endif; ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="/Ticket/auth/logout.php">Déconnexion</a></li>
                                </ul>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a class="nav-link text-white" href="/Ticket/auth/login.php">Connexion</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-white" href="/Ticket/auth/register.php">Inscription</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</header>