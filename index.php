<?php
session_start();
require_once 'config/database.php';

// Redirection vers la page d'accueil des tickets
header('Location: tickets/index.php');
exit;