# Charte graphique — DDL Platform CENI RDC

## Principes

- **Fond principal** : blanc (`#FFFFFF`)
- **Couleur institutionnelle** : bleu CENI (`#003DA5`)
- **Style** : sobre, institutionnel, lisible (accessibilité WCAG 2.1 AA visée)
- **Typographie** : système sans-serif (`Inter`, `Segoe UI`, `system-ui`)

## Palette Tailwind (`ceni`)

| Token | Hex | Usage |
|-------|-----|-------|
| `ceni-blue` | `#003DA5` | Boutons primaires, liens, en-têtes, badges actifs |
| `ceni-blue-dark` | `#002D7A` | Hover boutons, texte accentué |
| `ceni-blue-light` | `#E8F0FE` | Fonds de cartes secondaires, lignes tableau alternées |
| `ceni-white` | `#FFFFFF` | Fond page, cartes |
| `ceni-gray-100` | `#F5F7FA` | Fond application (léger contraste) |
| `ceni-gray-500` | `#6B7280` | Texte secondaire |
| `ceni-gray-900` | `#111827` | Texte principal |
| `ceni-success` | `#059669` | Statut validé / terminé |
| `ceni-warning` | `#D97706` | En attente, à corriger |
| `ceni-danger` | `#DC2626` | Rejeté, erreur |

## Composants clés

### Bouton primaire
- Fond `bg-ceni-blue`, texte blanc
- Hover `bg-ceni-blue-dark`
- Coins `rounded-lg`, padding `px-4 py-2`

### Carte (login, formulaires)
- Fond blanc, ombre légère `shadow-md`
- Bordure optionnelle `border border-ceni-blue-light`

### En-tête application
- Fond blanc, bordure basse bleu clair
- Logo CENI à gauche, menu utilisateur à droite

### Tableaux de bord
- Fond page `bg-ceni-gray-100`
- Cartes KPI fond blanc, chiffres en `ceni-blue`

### Badges de statut

| Statut | Couleur fond | Couleur texte |
|--------|--------------|---------------|
| Brouillon | gray-200 | gray-700 |
| Soumise / En cours | blue-light | blue-dark |
| Validée / Terminée | green-100 | success |
| Rejetée | red-100 | danger |
| À corriger | amber-100 | warning |

## Logo

- Emplacement login : centré, hauteur max **80px**
- Emplacement header : hauteur **40px**
- Fichiers attendus : `logo-ceni-rdc.png` et/ou `logo-ceni-rdc.svg`
- Fond transparent recommandé

## Espacements

- Conteneur max login : `max-w-md`
- Padding page : `p-6` (mobile), `p-8` (desktop)
- Grille dashboard : 12 colonnes, gap `gap-6`

## Validation charte

| Élément | Décision |
|---------|----------|
| Couleurs | Blanc + bleu `#003DA5` |
| Logo | CENI RDC officiel |
| Ton visuel | Institutionnel, épuré |

Référence implémentation Phase 2 : `frontend/tailwind.config.js`, `frontend/src/styles/theme.css`.
