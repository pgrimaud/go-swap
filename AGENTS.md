# 🤖 Agents Instructions - Go Swap V3

> **Documentation pour les AI Agents** travaillant sur le projet Go Swap V3  
> Date de création : 2025-12-19  
> Dernière mise à jour : 2025-12-19

---

## 📋 Contexte du Projet

**Go Swap V3** est une application web Symfony 8.0 pour gérer une collection complète de Pokémon GO :
- **Pokédex complet** : Track tous les variants possibles (Normal, Shiny, Shadow, Purified, Lucky, XXL, XXS, Hundo)
- **Collection PvP** : Pokémon optimisés pour les ligues Great/Ultra/Little Cup
- **Listes personnalisées** : Créer des listes custom pour organiser sa collection

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

---

## 📝 Commandes Utiles

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

# Build Tailwind
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

---

## 🚧 État Actuel du Projet (Phase 1.1)

### ✅ Complété

- [x] Symfony 8.0 installé
- [x] Symfony UX Bundle (Turbo + Stimulus)
- [x] TailwindCSS v4
- [x] Base de données MySQL configurée
- [x] Authentification (User, Login, Register)
- [x] Migration User avec `created_at`

### 🔄 En cours

- Phase 1.2 Authentification terminée
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
2. **Vérifier** : routes, schema Doctrine, etc.
3. **Commit message clair** : `feat:`, `fix:`, `chore:`

---

## 💡 Tips pour les Agents

- Le dossier `_archive_v2/` contient le code V2 en référence (Symfony 7.x)
- Les Commands d'import existent en V2 et doivent être adaptés
- Préférer les **Turbo Frames** aux recharges de page complètes
- Utiliser **Stimulus** pour les interactions JavaScript
- Le projet vise la **simplicité** : pas de sur-engineering

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
