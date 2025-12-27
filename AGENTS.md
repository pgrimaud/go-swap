# 🤖 Agents Instructions - Go Swap V3

> **Documentation pour les AI Agents** travaillant sur le projet Go Swap V3  
> Date de création : 2025-12-19  
> Dernière mise à jour : 2025-12-27

---

## 📋 Contexte du Projet

**Go Swap V3** est une application web Symfony 8.0 pour gérer une collection complète de Pokémon GO :
- **Pokédex complet** : Track tous les variants possibles (Normal, Shiny, Shadow, Purified, Lucky, XXL, XXS, Perfect)
- **Collection PvP** : Pokémon optimisés pour les ligues Great/Ultra/Little Cup
- **Listes personnalisées** : Créer des listes custom pour organiser sa collection

---

## 🌐 Langue de l'application

**⚠️ IMPORTANT : L'application doit être ENTIÈREMENT en ANGLAIS**

- **Interface** : Tous les textes, boutons, labels en anglais
- **Base de données** : Colonnes et données en anglais
- **Code** : Variables, méthodes, commentaires en anglais
- **Exception** : Noms des Pokémon stockés en **FR et EN** (colonnes `name_fr` et `name_en`)

### Exemples de traduction :
- ❌ "Pokédex" → ✅ "Pokédex" (nom propre, reste tel quel)
- ❌ "Mes Listes" → ✅ "My Lists"
- ❌ "Se déconnecter" → ✅ "Logout"
- ❌ "Collection PvP" → ✅ "PvP Collection"
- ❌ "Chromatique" → ✅ "Shiny"
- ❌ "Obscur" → ✅ "Shadow"
- ❌ "Purifié" → ✅ "Purified"
- ❌ "Chanceux" → ✅ "Lucky"

---

## 🛠️ Stack Technique

- **Framework** : Symfony 8.0
- **PHP** : 8.4
- **Base de données** : MySQL 8.0 (dev) / SQLite (fallback)
- **Frontend** : Hotwire (Turbo + Stimulus) + TailwindCSS v4
- **Asset Management** : AssetMapper (pas de Webpack/Encore)
- **ORM** : Doctrine
- **Authentification** : Symfony Security Component

---

## 📁 Structure du Projet

```
go-swap/
├── assets/
│   ├── controllers/       # Stimulus controllers
│   ├── styles/            # TailwindCSS (app.css)
│   └── app.js             # Entry point
├── config/
│   ├── packages/
│   └── routes.yaml
├── migrations/            # Doctrine migrations
├── src/
│   ├── Command/           # Console commands (import data)
│   ├── Controller/
│   ├── Entity/
│   ├── Form/
│   ├── Repository/
│   └── Security/
├── templates/
│   ├── base.html.twig
│   ├── registration/
│   └── security/
├── tests/
│   ├── Controller/          # Tests fonctionnels des controllers
│   │   ├── SecurityControllerTest.php
│   │   └── RegistrationControllerTest.php
│   ├── Entity/              # Tests unitaires des entités
│   │   └── UserTest.php
│   └── bootstrap.php
├── _archive_v2/           # Code V2 (référence)
├── .env                   # Config versionnée (SQLite par défaut)
├── .env.local             # Config locale non versionnée (MySQL)
├── TODO_V3.md             # Roadmap complète du projet
└── agents.md              # Ce fichier
```

---

## 🎯 Conventions de Code

### PHP 8.4 / Symfony 8.0

#### Attributes (pas d'annotations)
```php
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[Route('/login', name: 'app_login')]
```

#### Contraintes de validation (arguments nommés)
```php
// ✅ Correct (Symfony 8.0)
new IsTrue(
    message: 'You should agree to our terms.',
)

// ❌ Ancien style (deprecated)
new IsTrue([
    'message' => 'You should agree to our terms.',
])
```

#### Types stricts
```php
declare(strict_types=1);
```

### Twig Templates

- Utiliser `path('route_name')` pour les liens
- Turbo Frames pour AJAX : `<turbo-frame id="...">`
- Stimulus controllers : `data-controller="name"`

### CSS / TailwindCSS v4

- Pas de fichier `tailwind.config.js` (Tailwind v4)
- Import direct : `@import "tailwindcss";`
- Classes Tailwind uniquement, éviter le CSS custom
- **⚠️ IMPORTANT** : Après chaque ajout ou modification de classes CSS, lancer `php bin/console tailwind:build` pour compiler les styles

---

## 📝 Commandes Utiles

### Qualité du Code

```bash
# PHP CS Fixer (fix coding standards)
composer cs-fix

# PHPStan (analyse statique)
composer phpstan

# PHPUnit (tests)
composer test
# ou
php bin/phpunit

# Lancer tout
composer cs-fix && composer phpstan && composer test
```

**⚠️ Important** : Toujours vérifier la qualité du code avant de commit !

### Développement

```bash
# Clear cache
php bin/console cache:clear

# Create entity
php bin/console make:entity EntityName

# Create migration
php bin/console make:migration
php bin/console doctrine:migrations:migrate

# Create controller
php bin/console make:controller ControllerName

# Debug routes
php bin/console debug:router

# Build Tailwind (compile les classes CSS)
php bin/console tailwind:build

# Build Tailwind en mode watch (auto-recompile)
php bin/console tailwind:build --watch
```

### Base de données

```bash
# Create database
php bin/console doctrine:database:create

# Validate schema
php bin/console doctrine:schema:validate

# Drop and recreate
php bin/console doctrine:database:drop --force
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate --no-interaction
```

### Fixtures

```bash
# Load fixtures (users de test)
php bin/console doctrine:fixtures:load --no-interaction

# Append fixtures (sans purge)
php bin/console doctrine:fixtures:load --append
```

**Users créés par les fixtures :**
- `admin@go-swap.com` / `admin123` (ROLE_ADMIN)

### Tests

**Organisation des tests** :
- `tests/Controller/` - Tests fonctionnels (WebTestCase)
- `tests/Entity/` - Tests unitaires (TestCase)

```bash
# Préparer la base de test (première fois)
php bin/console doctrine:database:create --env=test
php bin/console doctrine:schema:create --env=test
php bin/console doctrine:fixtures:load --env=test --no-interaction

# Reset complet de la base de test
php bin/console doctrine:database:drop --force --env=test --if-exists
php bin/console doctrine:database:create --env=test
php bin/console doctrine:schema:create --env=test
php bin/console doctrine:fixtures:load --env=test --no-interaction

# Lancer tous les tests
composer test
# ou
php bin/phpunit

# Lancer avec détails
composer test -- --testdox
# ou
php bin/phpunit --testdox

# Lancer un test spécifique
php bin/phpunit tests/Controller/SecurityControllerTest.php
php bin/phpunit tests/Controller/RegistrationControllerTest.php
php bin/phpunit tests/Entity/UserTest.php
```

**Base de données de test** : 
- Local : MySQL `go-swap-v3_test` (via `.env.test.local` non versionné)
- CI/CD : MySQL `go-swap-v3_test` (via service container GitHub Actions)

**Note** : Le fichier `.env.test.local` doit être créé localement avec les credentials MySQL :
```env
DATABASE_URL="mysql://root:sezane@127.0.0.1:3307/go-swap-v3_test?serverVersion=8.0.32&charset=utf8mb4"
```

### CI/CD - GitHub Actions

Le workflow CI est configuré dans `.github/workflows/ci.yml` et s'exécute automatiquement sur la branche `v3`.

**Services** :
- MySQL 8.0 (container Docker)

**Étapes du CI** :
1. ✅ Setup PHP 8.4
2. ✅ Install dependencies (`composer install`)
3. ✅ Build Tailwind CSS (`php bin/console tailwind:build`)
4. ✅ Audit dependencies (`composer audit`)
5. ✅ Run PHPStan (`composer phpstan`)
6. ✅ Run PHP CS Fixer (`composer cs-check`)
7. ✅ Setup test database (MySQL)
8. ✅ Run PHPUnit tests (`composer test`)

**Résultat** : Si toutes les étapes passent, le code est prêt pour le merge/deploy.

---

## 🚧 État Actuel du Projet (Phase 1)

### ✅ Complété

- [x] Symfony 8.0 installé
- [x] Symfony UX Bundle (Turbo + Stimulus)
- [x] TailwindCSS v4
- [x] Base de données MySQL configurée
- [x] Authentification (User, Login, Register)
- [x] Migration User avec `created_at`
- [x] Protection par authentification (tout le site)
- [x] Dashboard avec Tailwind CSS
- [x] UserFixtures (1 user admin)
- [x] PHPUnit + Tests (Authentication + Entity User)
- [x] PHPStan niveau max sans erreurs
- [x] PHP CS Fixer configuré
- [x] CI/CD GitHub Actions avec tests automatiques

### 🔄 En cours

- Phase 1 terminée ✅
- Prochaine étape : **Phase 2 - Data Import**

### 📋 Voir TODO_V3.md pour la roadmap complète

---

## 🎨 Design & UX

### Principes

- **Mobile-first** : Responsive par défaut
- **Dark mode** : À implémenter (Phase 7)
- **Animations** : Turbo transitions + hover effects
- **Loading states** : Spinner AJAX, skeleton loaders
- **Toast notifications** : Succès/Erreur (Stimulus controller)

### Couleurs (TailwindCSS)

- **Primary** : blue-600
- **Success** : green-500
- **Warning** : yellow-500
- **Danger** : red-600

---

### AssetMapper vs Webpack

⚠️ Le projet utilise **AssetMapper** (pas de Webpack/Encore)
- `php bin/console importmap:require package-name`
- Pas de `yarn install` ou `npm install` pour les assets

---

## 📚 Références

### Documentation Symfony 8.0
- [Security](https://symfony.com/doc/8.0/security.html)
- [Forms](https://symfony.com/doc/8.0/forms.html)
- [Doctrine](https://symfony.com/doc/8.0/doctrine.html)
- [AssetMapper](https://symfony.com/doc/current/frontend/asset_mapper.html)

### Hotwire
- [Turbo](https://turbo.hotwired.dev/)
- [Stimulus](https://stimulus.hotwired.dev/)

### TailwindCSS
- [Tailwind v4 Beta](https://tailwindcss.com/docs)

---

## ⛔ Règles Importantes

### ❌ Ne JAMAIS faire :

1. **Ne JAMAIS éditer `README.md`** - Ce fichier est géré manuellement par le propriétaire du projet
2. **Ne JAMAIS commiter de credentials** - Utiliser `.env.local` (non versionné)
3. **Ne JAMAIS créer de branches** - Travailler uniquement sur la branche actuelle
4. **Ne JAMAIS utiliser Webpack/Encore** - Le projet utilise AssetMapper
5. **Ne JAMAIS ignorer PHPStan/CS-Fixer** - Toujours lancer avant de terminer

### ⚠️ Fichiers à ne pas modifier (sauf demande explicite) :

- `README.md` - Documentation principale
- `composer.json` - Sauf ajout de dépendances
- `.gitignore` - Déjà configuré
- `symfony.lock` - Géré par Symfony Flex

---

## 🤝 Workflow Agent

### Avant chaque modification

1. **Lire `TODO_V3.md`** pour comprendre l'étape en cours
2. **Vérifier l'état actuel** : routes, entities, migrations
3. **Respecter les conventions** PHP 8.4 / Symfony 8.0

### Pendant le développement

1. **Modifications minimales** : Changer uniquement ce qui est nécessaire
2. **Tester** : Valider que ça fonctionne (cache clear, migrations)
3. **Mettre à jour TODO_V3.md** : Cocher les étapes terminées

### Après modification

1. **Clear cache** si nécessaire
2. **Build Tailwind** si classes CSS ajoutées/modifiées : `php bin/console tailwind:build`
3. **Vérifier** : routes, schema Doctrine, etc.
4. **Lancer les tests de qualité** :
   ```bash
   composer cs-fix
   composer phpstan
   composer test
   ```
5. **Commit message clair** : `feat:`, `fix:`, `chore:`

---

## 💡 Tips pour les Agents

- Le dossier `_archive_v2/` contient le code V2 en référence (Symfony 7.x)
- Les Commands d'import existent en V2 et doivent être adaptés
- Préférer les **Turbo Frames** aux recharges de page complètes
- Utiliser **Stimulus** pour les interactions JavaScript
- Le projet vise la **simplicité** : pas de sur-engineering

---

## 🗄️ Fixtures & Données de Test

### Créer une Fixture

Les fixtures permettent de charger des données de test en base.

**Exemple : UserFixtures.php**
```php
<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $admin = new User();
        $admin->setEmail('admin@go-swap.com');
        $admin->setRoles(['ROLE_USER', 'ROLE_ADMIN']);
        $admin->setPassword(
            $this->passwordHasher->hashPassword($admin, 'admin123')
        );
        $manager->persist($admin);
        $manager->flush();
    }
}
```

### Charger les Fixtures

```bash
# Purge DB + charge fixtures
php bin/console doctrine:fixtures:load --no-interaction

# Ajoute sans purger
php bin/console doctrine:fixtures:load --append
```

### Reset complet de la DB

```bash
# Script complet pour repartir à zéro
php bin/console doctrine:database:drop --force
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate --no-interaction
php bin/console doctrine:fixtures:load --no-interaction
```

**⚠️ Users de test disponibles :**
- `admin@go-swap.com` / `admin123` (ROLE_ADMIN)

---

## 🎯 Objectif Final

Une application Pokémon GO moderne, rapide et facile à utiliser pour :
- Tracker TOUS les variants de chaque Pokémon
- Gérer sa collection PvP optimisée
- Créer des listes personnalisées
- Partager ses listes publiquement

---

**Bon courage Agent ! 🚀**

_Si tu as des questions, consulte TODO_V3.md ou explore `_archive_v2/` pour des exemples._
