# Polypdf

Application web de conversion de documents en PDF, construite avec Symfony 7.4. Elle permet de convertir des URLs, fichiers Office, HTML, Markdown, images et bien d'autres formats en PDF via une interface moderne, avec un système d'abonnement par plans (FREE, BASIC, PREMIUM).

---

## Stack technique

| Composant | Technologie |
|---|---|
| Backend | PHP 8.3 · Symfony 7.4 |
| Base de données | MariaDB 10.8 |
| Moteur PDF | Gotenberg 8 (Chromium + LibreOffice) |
| CSS | Tailwind CSS v4 via `symfonycasts/tailwind-bundle` |
| Assets | Symfony Asset Mapper (sans Webpack/Vite) |
| Emails | Maildev (dev) |
| Tests E2E | Cypress 15 |
| Tests unitaires | PHPUnit |

---

## Prérequis

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) avec les containers du projet démarrés
- PHP 8.3 accessible en ligne de commande
- Composer
- Node.js + npm (pour Cypress)

---

## Installation

### 1. Cloner le projet

```bash
git clone https://github.com/ewenlogiciel/wr602d.git
cd wr602d
```

### 2. Installer les dépendances PHP

```bash
composer install
```

### 3. Configurer les variables d'environnement

Copier le fichier d'environnement et l'adapter si besoin :

```bash
cp .env .env.local
```

Les valeurs par défaut fonctionnent avec les containers Docker du projet (`/home/ewen/buts6/`) :

```env
DATABASE_URL="mysql://symfony:PASSWORD@symfony-db/symfony?serverVersion=10.8.8-MariaDB&charset=utf8mb4"
GOTENBERG_URL=http://gotenberg:3000
MAILER_DSN=smtp://symfony-mail-2026:1025
MESSENGER_TRANSPORT_DSN=doctrine://default?auto_setup=0
```

> Pour Stripe, ajouter dans `.env.local` :
> ```env
> STRIPE_SECRET_KEY=sk_test_...
> STRIPE_WEBHOOK_SECRET=whsec_...
> ```

### 4. Démarrer les containers Docker

Depuis `/home/ewen/buts6/` :

```bash
cd /home/ewen/buts6
docker compose up -d
```

Cela démarre :
- `symfony-web-2026` — serveur Apache + PHP (port **8319**)
- `symfony-db-2026` — MariaDB
- `symfony-adminsql-2026` — phpMyAdmin (port **8082**)
- `symfony-mail-2026` — Maildev (port **1080**)
- `gotenberg` — moteur PDF (port **3000**)

### 5. Créer la base de données et exécuter les migrations

```bash
php bin/console doctrine:migrations:migrate
```

### 6. Charger les données initiales (fixtures)

Les fixtures créent les **plans** (FREE, BASIC, PREMIUM), les **outils** de conversion et un **compte de test**.

```bash
php bin/console doctrine:fixtures:load
```

> ⚠️ Cette commande **vide la base** avant d'insérer les données. Pour ajouter les fixtures sans supprimer les données existantes :
> ```bash
> php bin/console doctrine:fixtures:load --append
> ```
> Attention aux doublons si la commande est relancée plusieurs fois.

**Compte de test créé par les fixtures :**

| Champ | Valeur |
|---|---|
| Email | `test@gmail.com` |
| Mot de passe | `123456` |
| Plan | BASIC |

---

## Lancer l'application

L'application tourne via Apache dans le container Docker et est accessible à :

**[http://localhost:8319](http://localhost:8319)**

Interfaces annexes :
- phpMyAdmin : [http://localhost:8082](http://localhost:8082)
- Maildev (emails) : [http://localhost:1080](http://localhost:1080)

---

## Développement

### Vider le cache Symfony

```bash
php bin/console cache:clear
```

### Tailwind CSS

Recompilation à la volée pendant le développement (surveille les changements) :

```bash
php bin/console tailwind:build --watch
```

Compilation unique :

```bash
php bin/console tailwind:build
```

Compilation pour la production (copie les assets dans `public/`) :

```bash
php bin/console asset-map:compile
```

---

## Tests

### Tests unitaires (PHPUnit)

```bash
# Tous les tests
./vendor/bin/phpunit

# Un fichier spécifique
./vendor/bin/phpunit tests/PdfGeneratorServiceTest.php

# Avec sortie détaillée
./vendor/bin/phpunit --testdox
```

### Tests E2E (Cypress)

Les tests Cypress se lancent depuis la machine hôte (Ubuntu), pas depuis le container Docker.

#### Installation (première fois)

```bash
npm install
npx cypress install
```

#### Configuration des credentials de test

Créer le fichier `cypress.env.json` à la racine du projet (déjà dans `.gitignore`) :

```json
{
  "USER_EMAIL": "test@gmail.com",
  "USER_PASSWORD": "123456"
}
```

#### Lancer les tests en mode headless

```bash
npx cypress run
```

#### Lancer un seul fichier de test

```bash
npx cypress run --spec cypress/e2e/login.cy.js
```

#### Fichiers de tests disponibles

| Fichier | Fonctionnalités testées |
|---|---|
| `login.cy.js` | Connexion réussie / échouée |
| `register.cy.js` | Formulaire d'inscription, validations |
| `home.cy.js` | Page d'accueil, affichage des plans |
| `tools.cy.js` | Liste des outils, filtres, contrôle d'accès |
| `convert.cy.js` | Conversion URL, WYSIWYG, accès PREMIUM |
| `profile.cy.js` | Édition du profil, changement de mot de passe |
| `history.cy.js` | Historique des générations |

---

## Structure du projet

```
src/
├── Controller/        # Contrôleurs Symfony (PDF, Auth, Payment, Profile…)
├── Entity/            # Entités Doctrine (User, Plan, Tool, Generation…)
├── Form/              # Formulaires Symfony
├── Repository/        # Repositories Doctrine
├── Security/          # Authentification, voters
└── Service/           # Services métier (PdfGeneratorService, StripeService)

templates/             # Templates Twig
assets/                # JS et CSS source (Tailwind, Stimulus)
cypress/e2e/           # Tests E2E Cypress
migrations/            # Migrations Doctrine
```

---

## Plans et accès aux outils

| Plan | Prix | Générations/jour | Outils accessibles |
|---|---|---|---|
| FREE | Gratuit | 2 | URL, HTML, Markdown, WYSIWYG |
| BASIC | 9,90 €/mois | 20 | + Word, Excel, PowerPoint, Texte |
| PREMIUM | 45 €/mois | 200 | Tous les outils |

---

## Emails en développement

Les emails (confirmation d'inscription, réinitialisation de mot de passe) sont interceptés par **Maildev** et consultables à [http://localhost:1080](http://localhost:1080). Aucun email n'est envoyé réellement en développement.
