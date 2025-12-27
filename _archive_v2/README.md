# Archive V2

Ce dossier contient le code de référence de la V2 (branche `v2`).

## ✅ À réutiliser pour V3

### Complètement
- `src/Command/` - Commands d'import data (Pokémon, Moves, Types, Pictures)
- `src/Entity/` - Structure DB (Pokemon, Move, Type, User, UserPvPPokemon)
- `src/Repository/` - Requêtes custom
- `public/images/` - Toutes les ressources visuelles

### Partiellement (logique métier)
- `src/Controller/` - Extraire logique, réécrire avec Turbo
- `src/Service/` - Helpers réutilisables
- `templates/` - Composants modals, forms à adapter
- `assets/js/` - Logique filtres, selects dynamiques

## 🗑️ À supprimer en V3
- Alpine.js (remplacé par Stimulus)
- Tables HTML (remplacées par grilles cartes)
- JS inline dans Twig

## 📝 Notes
Archivé le : 2025-12-19
Voir `TODO_V3.md` pour la roadmap complète.
