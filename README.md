# CarLocation - Système de Gestion de Location de Voitures

## Description
CarLocation est une application web moderne pour la gestion de location de voitures. Elle permet aux gérants de gérer efficacement leur flotte de véhicules, les réservations et les clients.

## Fonctionnalités Principales
- Gestion des véhicules (ajout, modification, suppression)
- Gestion des réservations
- Gestion des clients
- Système de facturation
- Gestion des offres spéciales
- Interface d'administration complète
- Gestion des images de véhicules
- Système de notification

## Prérequis
- PHP 8.0 ou supérieur
- MySQL/MariaDB
- Composer
- Node.js et npm
- Serveur web (Apache/Nginx)

## Installation

1. Cloner le dépôt :
```bash
git clone [URL_DU_REPO]
cd CarLocation
```

2. Installer les dépendances PHP :
```bash
composer install
```

3. Installer les dépendances JavaScript :
```bash
npm install
```

4. Configurer l'environnement :
- Copier le fichier `.env.example` en `.env`
- Configurer les variables d'environnement dans `.env`

5. Configurer la base de données :
- Créer une base de données MySQL
- Importer le fichier de structure de la base de données

6. Configurer le serveur web :
- Pointer le document root vers le dossier `Public/`
- Configurer les permissions des dossiers `uploads/` et `temp/`

## Structure du Projet
```
CarLocation/
├── Controller/     # Contrôleurs de l'application
├── Database/       # Configuration et migrations de la base de données
├── Models/         # Modèles de données
├── Public/         # Fichiers publics (assets, index.php)
├── src/            # Code source principal
├── Utils/          # Utilitaires et helpers
├── Views/          # Vues de l'application
├── vendor/         # Dépendances Composer
└── node_modules/   # Dépendances npm
```

## Dépendances

### PHP (via Composer)
- setasign/fpdf (^1.8) - Génération de PDF
- vlucas/phpdotenv (^5.6) - Gestion des variables d'environnement
- phpmailer/phpmailer (^6.9) - Envoi d'emails

### Frontend
- Bootstrap 5.3.0
- jQuery 3.6.0
- Font Awesome 6.4.0
- DataTables 1.11.5

## Configuration
1. Copier `.env.example` en `.env`
2. Configurer les variables suivantes :
   - DB_HOST
   - DB_NAME
   - DB_USER
   - DB_PASS
   - SMTP_HOST
   - SMTP_PORT
   - SMTP_USER
   - SMTP_PASS

## Sécurité
- Authentification des utilisateurs
- Protection CSRF
- Validation des entrées
- Gestion sécurisée des fichiers uploadés

## Contribution
Les contributions sont les bienvenues ! Pour contribuer :
1. Fork le projet
2. Créer une branche pour votre fonctionnalité
3. Commiter vos changements
4. Pousser vers la branche
5. Ouvrir une Pull Request

## Licence
Ce projet est sous licence MIT. Voir le fichier `LICENSE` pour plus de détails.

## Contact
- Email : abdouaziz583@gmail.com
- Auteur : AbdouAziz
