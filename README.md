# TicketMaster - Plateforme de Revente de Tickets

## Description

TicketMaster est une application web PHP permettant aux utilisateurs de mettre en vente et d'acheter des tickets pour divers événements. L'application dispose d'un système d'authentification, d'une gestion des tickets et d'un panneau d'administration pour les modérateurs.

## Fonctionnalités

- **Authentification des utilisateurs** : Inscription, connexion et déconnexion
- **Gestion des tickets** : 
  - Affichage de tous les tickets disponibles
  - Filtrage par type (National/International)
  - Ajout, modification et suppression de tickets par les utilisateurs
- **Profil modérateur** :
  - Tableau de bord avec statistiques
  - Gestion de tous les tickets (visualisation, modification, suppression)

## Structure de la base de données

La base de données comprend les tables suivantes :

- **users** : Stocke les informations des utilisateurs (identifiants, emails, mots de passe)
- **tickets** : Contient les détails des tickets mis en vente
- **orders** : Enregistre les achats de tickets
- **messages** : Permet la communication entre utilisateurs

## Installation

1. **Prérequis** :
   - Serveur web (Apache, Nginx)
   - PHP 7.4 ou supérieur
   - MySQL/MariaDB
   - MAMP, XAMPP ou équivalent pour un environnement de développement local

2. **Configuration** :
   - Clonez ou téléchargez ce dépôt dans votre répertoire web
   - Importez le fichier `ticketmaster.sql` dans votre base de données MySQL
   - Configurez les paramètres de connexion à la base de données dans `config/database.php`

3. **Démarrage** :
   - Accédez à l'application via votre navigateur
   - Utilisez les identifiants suivants pour tester :
     - Administrateur : `admin` / `admin`
     - Utilisateur : `user` / `user`

## Structure du projet

```
/
├── admin/                  # Pages d'administration
│   ├── dashboard.php       # Tableau de bord
│   └── tickets.php         # Gestion des tickets
├── assets/                 # Ressources statiques
│   └── css/                # Feuilles de style
│       └── style.css       # Style principal
├── auth/                   # Authentification
│   ├── login.php           # Connexion
│   ├── logout.php          # Déconnexion
│   └── register.php        # Inscription
├── config/                 # Configuration
│   └── database.php        # Connexion à la base de données
├── includes/               # Éléments réutilisables
│   ├── footer.php          # Pied de page
│   └── header.php          # En-tête
├── tickets/                # Gestion des tickets
│   ├── add.php             # Ajout de ticket
│   ├── delete.php          # Suppression de ticket
│   ├── edit.php            # Modification de ticket
│   ├── index.php           # Liste des tickets
│   ├── my_tickets.php      # Tickets de l'utilisateur
│   └── view.php            # Détails d'un ticket
├── index.php               # Page d'accueil
├── README.md               # Documentation
└── ticketmaster.sql        # Structure de la base de données
```

## Sécurité

- Les mots de passe sont hachés avec bcrypt
- Protection contre les injections SQL avec PDO et requêtes préparées
- Validation des entrées utilisateur
- Vérification des permissions pour les actions sensibles

## Améliorations possibles

- Ajout d'un système de paiement
- Implémentation d'un système de notation des vendeurs
- Ajout de catégories pour les tickets
- Système de recherche avancée
- Ajout d'images pour les tickets
- Notifications par email

## Licence

Ce projet est sous licence MIT. Voir le fichier LICENSE pour plus de détails.