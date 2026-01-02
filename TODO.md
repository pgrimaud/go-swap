# 📋 TODO V3 - Go Swap

> **Objectif** : Application Pokémon GO pour tracker TOUS mes Pokémon (Pokédex complet avec variants) + Collection PvP optimisée  
> **Stack** : Symfony 8.0 + PHP 8.4 + Hotwire (Turbo/Stimulus) + TailwindCSS

---

## 🎯 **Fonctionnalités Core**

### 1. **Pokédex Complet** (comme dans Pokémon GO)
Pour chaque Pokémon, je dois pouvoir marquer :
- ✅ **Normal** - Version standard
- ✅ **Shiny** - Version chromatique
- ✅ **Shadow** - Version obscur
- ✅ **Purified** - Version purifiée
- ✅ **Lucky** - Pokémon chanceux (trade)
- ✅ **XXL** - Taille XXL
- ✅ **XXS** - Taille XXS
- ✅ **100%** - IVs 15/15/15 parfait

### 2. **Collection PvP**
Pour mes Pokémon optimisés PvP :
- Pokémon + Ligue (Great/Ultra/Little)
- IVs (Attack/Defense/Stamina)
- Rank dans la ligue (1-4096)
- Moves (Fast + Charged1 + Charged2)
- Type (Normal/Shadow/Purified)

### 3. **Listes Personnalisées**
Créer des listes custom pour organiser mes Pokémon :
- Nom de la liste (ex: "À transférer", "Favourites", "Trade List")
- Associer N Pokémon à une liste
- 1 Pokémon peut être dans plusieurs listes
- Partage public optionnel (URL unique)

---

## 🏗️ **Phase 1 : Setup & Infrastructure** ✅ COMPLÈTE

### 1.1 Projet de base
- [x] Créer branche `v3` depuis `main` (fresh start) - **Merged dans main le 2026-01-01**
- [x] **Installer Symfony 8.0** (nouveau projet)
  ```bash
  composer create-project symfony/skeleton:"8.0.*" .
  composer require webapp
  ```
- [x] Setup PHP 8.4 (déjà installé ✓)
- [x] Installer **Symfony UX Bundle** (Turbo + Stimulus)
  ```bash
  composer require symfony/ux-turbo symfony/stimulus-bundle
  php bin/console importmap:require @hotwired/turbo @hotwired/stimulus
  ```
- [x] Setup TailwindCSS
  ```bash
  composer require symfonycasts/tailwind-bundle
  php bin/console tailwind:init
  ```
- [x] Créer DB + .env config
  ```bash
  php bin/console doctrine:database:create
  ```

### 1.2 Authentification
- [x] Entité `User` (id, email, password, roles, created_at)
  ```bash
  php bin/console make:user
  ```
- [x] Login/Register forms
  ```bash
  php bin/console make:auth
  php bin/console make:registration-form
  ```
- [x] SecurityController + templates
- [x] Tester auth flow

---

## 📦 **Phase 2 : Data Import (Foundation)** ✅ COMPLÈTE

### 2.1 Commands d'import
**Ref : `_archive_v2/src/Command/`** - À récupérer et adapter pour Symfony 8.0

- [x] `UpdateTypesCommand` - Import types Pokémon depuis Gamemaster
- [x] `UpdatePokemonCommand` - Import tous les Pokémon depuis Gamemaster
- [x] `UpdateMovesCommand` - Import attaques PvP (fast + charged)
- [x] `UpdatePicturesCommand` - Download images Pokémon (normal + shiny)

**Note** : ✅ Commands migrées avec PHP 8.4 attributes + Toutes les images téléchargées (normal + shiny)

### 2.2 Entities de base
- [x] **`Type`** (id, name, slug, icon, timestamps)
  - ✅ Migration `Version20251220165952`
- [x] **`TypeEffectiveness`** (source_type_id, target_type_id, multiplier)
  - ✅ Migration `Version20251220165952`
- [x] **`Pokemon`** 
  - ✅ id, number, name, picture, shiny_picture
  - ✅ types (ManyToMany avec Type)
  - ✅ generation, attack, defense, stamina
  - ✅ hash, shadow, slug, timestamps
  - ✅ Migration `Version20251220171029`
- [x] **`Move`** 
  - ✅ id, name, slug, type_id
  - ✅ move_type (fast/charged), power, energy, duration
  - ✅ buff_target, buff_activation_chance
  - ✅ Migration `Version20251220170531`
- [x] **`PokemonMove`** (relation Pokemon ↔ Move - quels moves un Pokémon peut apprendre)
  - ✅ pokemon_id, move_id, elite
  - ✅ Migration `Version20251220171029`
- [ ] **`CustomList`** (nouvelles listes perso)
  - id, user_id, name, description (nullable)
  - is_public (bool), slug (pour partage)
  - created_at, updated_at
- [ ] **`CustomListPokemon`** (relation ManyToMany)
  - list_id, pokemon_id
  - added_at

**Note** : ✅ Entities utilisent PHP 8.4 attributes + TimestampTrait

### 2.3 Helpers & Services
- [x] `TimestampTrait` - Gestion automatique created_at/updated_at
- [x] `GameMasterService` - Service de récupération des données Gamemaster
- [x] `HashHelper` - Génération de hash pour Pokémon
- [x] `GenerationHelper` - Détection génération par numéro

### 2.4 Import initial des données
```bash
php bin/console app:update:types
php bin/console app:update:pokemon
php bin/console app:update:moves
php bin/console app:update:pictures
```
**Note** : ✅ Toutes les données importées avec succès

---

## 📚 **Phase 3 : Pokédex Complet (comme Pokémon GO)** ✅ COMPLÈTE

### 3.1 Entity UserPokemon
- [x] **Structure** : Table qui stocke TOUS les variants possédés par user
  - ✅ user_id, pokemon_id
  - ✅ has_normal, has_shiny, has_shadow, has_purified
  - ✅ has_lucky, has_xxl, has_xxs, has_perfect
  - ✅ first_caught_at, created_at, updated_at
  - ✅ Migration créée

### 3.2 Page Pokédex - Listing
- [x] Route `/pokedex` + `/api/pokedex`
- [x] Controller `PokedexController::index()` + API
- [x] Template : **Grille de cartes Pokémon**
- [x] Design carte avec 8 badges variants (icônes)
- [x] Pagination AJAX (50 Pokémon par page)

### 3.3 Filtres
- [x] Par variant (All, Normal, Shiny, Shadow, Purified, Lucky, XXL, XXS, Perfect)
- [x] Search bar (nom/numéro) en temps réel
- [x] Filtrage AJAX avec Stimulus controller
- [x] Gestion état actif des filtres

### 3.4 Modal Détails + Toggle Variants
- [x] Modal avec image grande + nom + numéro + types
- [x] **8 checkboxes interactives** pour toggle chaque variant
- [x] AJAX update en temps réel
- [x] Affichage date de première capture
- [x] Gestion état visuel (vert si possédé)

### 3.5 Dashboard / Stats
- [ ] Route `/pokedex/stats` (TODO - Phase 6)
- [ ] Compteurs globaux de completion
- [ ] Stats par génération
- [ ] Stats par variant

---

## ⚔️ **Phase 4 : Collection PvP Optimisée**

### 4.1 Entity UserPvPPokemon
**Structure** : Pokémon optimisés pour le PvP
```php
UserPvPPokemon:
- user_id (relation User)
- pokemon_id (relation Pokemon)
- league (enum: great_league, ultra_league, little_cup)
- iv_attack (0-15)
- iv_defense (0-15)
- iv_stamina (0-15)
- league_rank (1-4096) // Position dans le classement
- fast_move_id (relation Move)
- charged_move_1_id (relation Move)
- charged_move_2_id (relation Move, nullable)
- variant_type (enum: normal, shadow, purified)
- created_at
- updated_at
```

### 4.2 Page Collection PvP - Grille de cartes
- [ ] Route `/pvp/pokemon`
- [ ] Controller `PvPController::pokemon()`
- [ ] Template : **Grille de cartes moderne**

**Design carte** :
```
┌─────────────────┐
│ 🏆 [League]     │ <- Badge ligue coin haut gauche
│                 │    👤 [Shadow] <- Badge variant coin haut droit
│   [Image XXL]   │
│                 │
│  Pokémon Name   │
│                 │
│    [#1]         │ <- Badge rank (couleur selon position)
└─────────────────┘
```

**Couleurs rank** :
- #1 → 🟢 Vert (emerald-500)
- #2-10 → 🟡 Jaune (yellow-500)
- #11-30 → 🟠 Orange (orange-500)
- #31+ → ⚪ Gris (gray-400)

### 4.3 Filtres & Search
- [ ] **Boutons filtres ligues** :
  - All (X)
  - Great League 🏆 (X)
  - Ultra League 🏆 (X)
  - Little Cup 🏆 (X)
- [ ] **Search bar** (nom/numéro)
- [ ] **Tri** (dropdown) :
  - Rank (croissant/décroissant)
  - Nom (A-Z)
  - Récemment ajouté
- [ ] **Empty state** si aucun résultat
- [ ] Turbo Frame pour filtrage temps réel

### 4.4 Modal Détails Pokémon PvP
Au clic sur carte :
- [ ] **Header** : Image + Nom + #Numéro
- [ ] **Ligue + Rank** : Grande badge coloré
- [ ] **Variant** : Normal/Shadow/Purified
- [ ] **IVs** en gros :
  - 🔴 Attack : 15
  - 🔵 Defense : 14
  - 🟢 Stamina : 13
- [ ] **Moves** avec icône type :
  - ⚡ Fast : Thunder Shock
  - 💧 Charged1 : Surf
  - 🔥 Charged2 : Flamethrower (optionnel)
- [ ] **Actions** :
  - Bouton **Edit** → ouvre form édition
  - Bouton **Delete** → confirmation + suppression

### 4.5 Form Ajout Pokémon PvP
- [ ] Bouton "Add" dans header → ouvre modal
- [ ] **Form avec Stimulus controller** (`pokemon-form_controller.js`)

**Champs** :
1. **Select Pokémon** (autocomplete avec choices.js)
   - Liste tous les Pokémon
   - Search par nom/numéro
2. **Select Ligue** (radio buttons visuels)
   - Great / Ultra / Little
3. **IVs** (3 inputs number 0-15)
   - Attack, Defense, Stamina
   - Validation min/max
4. **League Rank** (input number 1-4096)
5. **Variant Type** (radio buttons)
   - Normal, Shadow, Purified
6. **Moves** (AJAX dynamique) :
   - Au changement Pokémon → fetch moves disponibles
   - **Fast Move** (select)
   - **Charged Move 1** (select)
   - **Charged Move 2** (select, optionnel)

**Actions** :
- [ ] Submit → `POST /pvp/pokemon/add` (Turbo Stream)
- [ ] Validation :
  - Tous champs requis sauf charged2
  - Pas de doublon exact (même Pokémon + ligue + IVs + moves)
- [ ] Success : ferme modal + refresh liste (Turbo)
- [ ] Error : affiche messages validation

### 4.6 Form Édition
- [ ] Bouton Edit dans modal détails
- [ ] Même form que Add, pré-rempli
- [ ] `PUT /pvp/pokemon/{id}/edit`
- [ ] Success : update carte en place (Turbo Stream)

### 4.7 Suppression
- [ ] Bouton Delete dans modal détails
- [ ] Confirmation : "Supprimer ce Pokémon PvP ?"
- [ ] `DELETE /pvp/pokemon/{id}`
- [ ] Success : retire carte de la grille (Turbo Stream)

### 4.8 Endpoint AJAX Moves
- [ ] Route API : `GET /api/pokemon/{id}/moves`
- [ ] Retourne JSON :
```json
{
  "fast_moves": [
    {"id": 1, "name": "Thunder Shock", "type": "electric"},
    ...
  ],
  "charged_moves": [
    {"id": 10, "name": "Surf", "type": "water"},
    ...
  ]
}
```
- [ ] Utilisé par Stimulus pour populate selects dynamiquement

---

## 📝 **Phase 5 : Listes Personnalisées**

### 5.1 Entities (déjà créées en Phase 2)
- [x] `CustomList` (nom, user, public/privé, slug)
- [x] `CustomListPokemon` (ManyToMany List ↔ Pokemon)

### 5.2 Page Mes Listes
- [x] Route `/lists`
- [x] Controller `CustomListController::index()`
- [x] Template : **Grille de cartes listes**

### 5.3 Créer une Liste
- [x] Bouton "Nouvelle liste" → page dédiée
- [x] Form : Nom (requis), Description (optionnel), Public/Privé (toggle)
- [x] Submit → `POST /lists/new`
- [x] Validation : nom unique par user

### 5.4 Vue Détails d'une Liste
- [x] Route `/lists/{id}`
- [x] Afficher : Header + Compteur + Grille Pokémon
- [x] Badge privé/public

### 5.5 Ajouter des Pokémon à une Liste ✅ COMPLÉTÉ (2026-01-02)
- [x] Route API : `POST /api/custom-lists/{listId}/pokemon/{pokemonId}`
- [x] Controller API : `CustomListApiController::addPokemon()`
- [x] Stimulus controller : `add_pokemon_controller.js`
- [x] Interface de recherche avec autocomplete
- [x] Validation : pas de doublons
- [x] Tests : `CustomListApiControllerTest`

### 5.6 Retirer Pokémon d'une Liste
- [x] Bouton trash sur chaque carte
- [x] Confirmation : "Retirer ce Pokémon de la liste ?"
- [x] `DELETE /api/custom-lists/pokemon/{id}`
- [x] Stimulus controller : `remove_pokemon_controller.js`
- [x] Update grille dynamique

### 5.7 Éditer une Liste
- [x] Route `/lists/{id}/edit`
- [x] Form pré-rempli
- [x] `POST /lists/{id}/edit`
- [x] Update nom/description/visibilité

### 5.8 Supprimer une Liste
- [x] Route `POST /lists/{id}/delete`
- [x] Redirect vers `/lists`

### 5.9 Partage Public (bonus)
- [ ] Si liste publique → générer slug unique
- [ ] Route publique : `/lists/public/{slug}`
- [ ] Page visible sans login :
  - Nom + Description
  - Grille Pokémon (read-only)
  - "Créé par {username}"
- [ ] Bouton "Copier lien" (clipboard)

### 5.10 Associer Pokémon depuis Pokédex
- [ ] Dans page Pokédex, sur modal détails Pokémon :
  - Bouton "Ajouter à une liste"
  - Dropdown : sélection liste existante
  - Ou "Créer nouvelle liste"
- [ ] AJAX : ajout rapide sans recharger

---

## 🛠️ **Phase 6 : Outils & Features Additionnelles**

### 9.1 Type Effectiveness Chart
- [ ] Route `/pvp/types`
- [ ] Tableau interactif (Ref: `_archive_v2/templates/pvp/types.html.twig`)
- [ ] Design : matrice types attaquants vs défenseurs
- [ ] Couleurs :
  - Vert : Super efficace (x2)
  - Rouge : Peu efficace (x0.5)
  - Noir : Inefficace (x0)
  - Blanc : Normal (x1)
- [ ] Clic sur type → highlight row/column
- [ ] Responsive mobile (scroll horizontal)

### 9.2 Page Détails PvP Avancée
- [ ] Route `/pvp/pokemon/details`
- [ ] Table détaillée avec tous les Pokémon PvP
- [ ] Colonnes :
  - Pokémon + Image
  - Ligue
  - Rank
  - IVs (A/D/S)
  - Moves
  - Actions
- [ ] Sortable par colonne (Stimulus)
- [ ] Export CSV (bonus)

### 9.3 Dashboard Global
- [ ] Route `/dashboard` (homepage après login)
- [ ] **Widgets** :
  - 📚 Pokédex : X% completion
  - ⚔️ PvP Collection : X Pokémon
  - 📝 Listes Perso : X listes créées
  - 🏆 Par ligue : Great (X), Ultra (Y), Little (Z)
- [ ] **Quick stats** :
  - Dernier Pokémon ajouté (Pokédex)
  - Top 5 PvP par rank
  - Dernière liste modifiée
- [ ] **Quick links** :
  - Ajouter Pokémon PvP
  - Voir Pokédex complet
  - Créer nouvelle liste
  - Type Chart

### 9.4 Profile & Settings (bonus)
- [ ] Route `/profile`
- [ ] Afficher stats user :
  - Membre depuis X
  - Total Pokémon Pokédex
  - Total Pokémon PvP
- [ ] Settings :
  - Changer email/password
  - Dark mode toggle (save preference)
- [ ] Export data (JSON backup complet)

---

## 🎨 **Phase 7 : Design & UX Polish**

### 9.1 Layout Global
- [ ] **Header** :
  - Logo Go Swap (lien vers dashboard)
  - Menu : Pokédex | PvP | Tools
  - User dropdown : Profile | Logout
  - Dark mode toggle
- [ ] **Breadcrumbs** sur toutes les pages
- [ ] **Footer** :
  - GitHub link
  - Version v3.x
  - Copyright

### 9.2 Design System
- [ ] **Couleurs cohérentes** :
  - Primary : blue-600
  - Success : green-500
  - Warning : yellow-500
  - Danger : red-600
- [ ] **Dark mode** :
  - Toggle dans header
  - Persister choix (localStorage + cookie)
  - Toutes les pages compatibles
- [ ] **Responsive** :
  - Mobile-first
  - Tester sur iPhone/Android
  - Burger menu si besoin

### 9.3 Animations & Transitions
- [ ] Turbo page transitions (fade)
- [ ] Modal open/close animations
- [ ] Card hover effects (scale + shadow)
- [ ] Loading states :
  - Spinner pendant AJAX
  - Skeleton loaders pour listes
- [ ] Toast notifications (succès/erreur)
  - Stimulus controller `toast_controller.js`

### 9.4 Accessibilité
- [ ] Contraste couleurs WCAG AA
- [ ] Alt text sur toutes images
- [ ] Labels sur tous inputs
- [ ] Keyboard navigation (Tab, Escape, Enter)
- [ ] ARIA labels sur modals

### 7.5 Performance
- [ ] Lazy loading images Pokémon
- [ ] Pagination si > 100 résultats
- [ ] Cache HTTP pour images statiques
- [ ] Minify CSS/JS en prod

---

## 🚀 **Phase 8 : Déploiement & CI/CD**

### 9.1 Tests & Quality
- [ ] PHPStan niveau max OK
- [ ] PHP CS Fixer OK
- [ ] Tests fonctionnels (optionnel) :
  - Login/Register
  - Ajout Pokémon PvP
  - Toggle Pokédex variants

### 9.2 GitHub Actions CI/CD
- [ ] Créer workflow `.github/workflows/v3.yml`
- [ ] **Triggers** : push sur branche `v3`
- [ ] **Steps CI** :
  - Checkout code
  - Setup PHP 8.4
  - Composer install
  - PHPStan analyze (niveau max)
  - PHP CS Fixer check (compatible PHP 8.4)
- [ ] **Steps CD** (si CI OK) :
  - SSH deploy sur serveur
  - Script deploy :
    ```bash
    git pull origin v3
    composer install --no-dev --optimize-autoloader
    php bin/console doctrine:migrations:migrate --no-interaction
    php bin/console cache:clear --env=prod
    php bin/console tailwind:build --minify
    php bin/console importmap:install
    ```
  - Cloudflare cache purge

### 9.3 Production Setup
- [ ] Serveur config :
  - **PHP 8.4** + Extensions (gd, pdo_mysql, opcache, etc.)
  - MySQL 8.0+
  - Nginx/Apache + HTTPS (Let's Encrypt)
- [ ] Environment variables (.env.prod)
  - `APP_ENV=prod`
  - `APP_SECRET=...`
  - `DATABASE_URL=...`
- [ ] Cron jobs (si besoin) :
  - Update Pokémon data (hebdomadaire)
- [ ] Monitoring :
  - Logs Symfony
  - Alertes erreurs
- [ ] **OPcache** activé (performance PHP 8.4)

### 9.4 Migration v2 → v3 (si data existante)
- [ ] Script SQL pour migrer :
  - Users (garder)
  - UserPvPPokemon (adapter colonnes)
  - Pokédex v2 → UserPokemon v3 (si existait)
- [ ] Backup DB avant migration
- [ ] Tester migration en local
- [ ] Rollback plan si problème

---

## 💎 **Phase 9 : Nice to Have (Future)**

### 9.1 PWA (Progressive Web App)
- [ ] Manifest.json
- [ ] Service Worker (cache offline)
- [ ] Icônes app (512x512, 192x192)
- [ ] Installable sur mobile (Add to Home Screen)

### 9.2 API REST
- [ ] Endpoints JSON pour app mobile future
- [ ] Auth JWT
- [ ] API Platform ou controllers custom
- [ ] Rate limiting

### 9.3 Features Communauté
- [ ] System de "friends" (ajouter amis)
- [ ] Comparer collections (qui a quoi)
- [ ] Trading suggestions (basé sur manquants)
- [ ] Leaderboard : top collectors

### 9.4 Features Avancées
- [ ] Intégration PvPoke API (ranks automatiques)
- [ ] Notifications :
  - Nouveau meta PvP (email)
  - Nouveau Pokémon released
- [ ] Import/Export :
  - CSV import bulk
  - JSON export backup
- [ ] Team Builder PvP (composer équipe de 3)
- [ ] Type coverage analyzer

### 9.5 Analytics
- [ ] Track usage (pages vues, features utilisées)
- [ ] Stats admin : users actifs, Pokémon les plus trackés
- [ ] Insights : Pokémon les plus populaires en PvP

---

## 🔥 **Quick Wins (Priorité Immédiate)**

### Sprint 1 (Setup)
1. [x] Créer branche v3
2. [x] Setup Turbo/Stimulus
3. [x] Auth (login/register)
4. [x] Layout de base (header/footer)

### Sprint 2 (Data) ✅ COMPLÉTÉ
5. [x] Copier Commands v2 → v3
6. [x] Entities : Pokemon, Move, Type, User
7. [x] Run import data
8. [x] Vérifier images OK

### Sprint 3 (Pokédex) ✅ COMPLÉTÉ
9. [x] Entity UserPokemon (8 variants)
10. [x] Page listing grille cartes
11. [x] Modal + toggle variants (AJAX)
12. [x] Filtres basiques (variant, search)

### Sprint 4 (PvP) 🎯 EN COURS
13. [ ] Entity UserPvPPokemon
14. [ ] Page grille cartes PvP
15. [ ] Form ajout (avec moves AJAX)
16. [ ] Modal détails + edit/delete

### Sprint 5 (Listes Perso)
17. [ ] Entities CustomList + CustomListPokemon
18. [ ] Page mes listes (grille)
19. [ ] Créer/éditer/supprimer liste
20. [ ] Ajouter/retirer Pokémon

### Sprint 6 (Polish)
21. [ ] Dashboard avec stats
22. [ ] Type effectiveness chart
23. [ ] Dark mode
24. [ ] Deploy v3 en prod 🚀

---

## 📁 **Structure Projet V3 (Finale)**

```
go-swap/
├── .github/
│   └── workflows/
│       └── v3.yml                  # CI/CD
├── assets/
│   ├── controllers/                # Stimulus controllers
│   │   ├── pokemon-form_controller.js
│   │   ├── filter_controller.js
│   │   ├── modal_controller.js
│   │   └── toast_controller.js
│   ├── styles/
│   │   └── app.css                 # TailwindCSS
│   └── app.js
├── config/
│   ├── packages/
│   ├── routes.yaml
│   └── services.yaml
├── migrations/                      # Doctrine migrations
├── public/
│   ├── images/
│   │   ├── pokemon/
│   │   ├── league/
│   │   ├── type/
│   │   └── icons/
│   └── index.php
├── src/
│   ├── Command/
│   │   ├── UpdateTypesCommand.php
│   │   ├── UpdatePokemonCommand.php
│   │   ├── UpdateMovesCommand.php
│   │   └── UpdatePicturesCommand.php
│   ├── Controller/
│   │   ├── DashboardController.php
│   │   ├── PokedexController.php
│   │   ├── PvPController.php
│   │   ├── SecurityController.php
│   │   └── API/
│   │       └── PokemonController.php
│   ├── Entity/
│   │   ├── User.php
│   │   ├── Pokemon.php
│   │   ├── Move.php
│   │   ├── Type.php
│   │   ├── TypeEffectiveness.php
│   │   ├── PokemonMove.php
│   │   ├── UserPokemon.php          # Pokédex variants
│   │   └── UserPvPPokemon.php       # Collection PvP
│   ├── Repository/
│   ├── Service/                     # Helpers
│   │   └── PokemonDataService.php
│   └── Kernel.php
├── templates/
│   ├── base.html.twig               # Layout principal
│   ├── dashboard/
│   │   └── index.html.twig
│   ├── pokedex/
│   │   ├── index.html.twig          # Grille + filtres
│   │   ├── _card.html.twig          # Partial carte
│   │   ├── _modal.html.twig         # Partial modal
│   │   └── stats.html.twig          # Dashboard stats
│   ├── pvp/
│   │   ├── pokemon.html.twig        # Grille collection
│   │   ├── types.html.twig          # Effectiveness chart
│   │   └── details.html.twig        # Table détaillée
│   ├── security/
│   │   ├── login.html.twig
│   │   └── register.html.twig
│   └── partials/
│       ├── header.html.twig
│       ├── footer.html.twig
│       └── breadcrumb.html.twig
├── _archive_v2/                     # Ref code v2
├── .env
├── .gitignore
├── composer.json
├── phpstan.neon
├── tailwind.config.js
└── TODO_V3.md                       # Ce fichier
```

---

## 📝 **Notes de Développement**

### Référence V2
Le dossier `_archive_v2/` contient :
- Controllers : logique métier à extraire
- Templates : composants à adapter
- JS : filtres, selects dynamiques

### Conventions Code
- **Controllers** : 1 action = 1 méthode claire
- **Entities** : annotations Doctrine standard
- **Templates** : composants réutilisables (_partials)
- **Stimulus** : 1 controller = 1 fonctionnalité isolée
- **CSS** : classes TailwindCSS, pas de CSS custom sauf exception

### Commandes Utiles
```bash
# Entities
php bin/console make:entity Pokemon
php bin/console make:migration
php bin/console doctrine:migrations:migrate

# Controllers
php bin/console make:controller PokedexController

# Stimulus
php bin/console make:stimulus-controller filter

# Assets
php bin/console tailwind:build --watch
php bin/console importmap:install

# Import data
php bin/console app:update-types
php bin/console app:update-pokemon
php bin/console app:update-moves
php bin/console app:update-pictures

# Quality
vendor/bin/phpstan analyze src --level=max
vendor/bin/php-cs-fixer fix

# Deploy
git push origin main  # Trigger CI/CD
```

---

## 🎯 **Definition of Done**

Une feature est complète quand :
- [ ] Code écrit et testé manuellement
- [ ] PHPStan niveau max : aucune erreur
- [ ] PHP CS Fixer : code formaté
- [ ] Responsive : testé mobile + desktop
- [ ] Dark mode : fonctionne dans les 2 thèmes
- [ ] Turbo : pas de rechargement full page
- [ ] Commit : message clair (feat/fix/refactor)
- [ ] Push : code sur branche main

---

## 🚦 **Statut Global**

| Phase | Status | Priorité |
|-------|--------|----------|
| Phase 1 - Setup | ✅ DONE | P0 |
| Phase 2 - Data | ✅ DONE | P0 |
| Phase 3 - Pokédex | ✅ DONE | P1 |
| Phase 4 - PvP | 🔄 TODO | P1 (maintenant) |
| Phase 5 - Listes Perso | 🔄 TODO | P1 (ensuite) |
| Phase 6 - Tools | 📅 LATER | P2 |
| Phase 7 - Polish | 📅 LATER | P2 |
| Phase 8 - Deploy | 📅 LATER | P3 |
| Phase 9 - Future | 💡 IDEAS | P4 |

---

**Dernière mise à jour** : 2026-01-01  
**Auteur** : @pgrimaud  
**Version** : V3 Roadmap Complete - Symfony 8.0 + PHP 8.4  
**Phase 1, 2 & 3 complètes ✅ - Phase 4 (PvP) à démarrer 🎯**
