# Maquettes interface — DDL Platform CENI RDC

Maquettes textuelles pour implémentation Figma / React (charte blanc / bleu).

---

## 1. Page de connexion (`/login`)

### Layout

```
┌─────────────────────────────────────────────────────────────┐
│                    fond: ceni-gray-100                       │
│                                                              │
│         ┌─────────────────────────────────────┐             │
│         │         fond: blanc, shadow-md       │             │
│         │                                      │             │
│         │         [Logo CENI RDC — 80px]       │             │
│         │                                      │             │
│         │   Plateforme DDL                     │             │
│         │   Gestion des demandes de            │             │
│         │   développement logiciel             │             │
│         │                                      │             │
│         │   Email                              │             │
│         │   ┌──────────────────────────────┐  │             │
│         │   │ vous@ceni.cd                 │  │             │
│         │   └──────────────────────────────┘  │             │
│         │                                      │             │
│         │   Mot de passe                       │             │
│         │   ┌──────────────────────────────┐  │             │
│         │   │ ••••••••                     │  │             │
│         │   └──────────────────────────────┘  │             │
│         │                                      │             │
│         │   ┌──────────────────────────────┐  │             │
│         │   │      Se connecter            │  │  bg-ceni-blue│
│         │   └──────────────────────────────┘  │             │
│         │                                      │             │
│         │   © CENI RDC — 2026                  │             │
│         └─────────────────────────────────────┘             │
│                    max-w-md, centré verticalement            │
└─────────────────────────────────────────────────────────────┘
```

### Composants React prévus

- `LoginPage` → `src/pages/login/LoginPage.tsx`
- `LogoCeni` → `src/components/LogoCeni.tsx`
- États : loading bouton, message erreur rouge sous formulaire

### Redirection post-login

| Rôle | Route |
|------|-------|
| AGENT | `/agent` |
| SECRETARIAT | `/secretariat` |
| DIRECTION_INFORMATIQUE | `/di` |
| DEVELOPPEUR | `/developpeur` |
| ADMIN | `/admin` |

---

## 2. Layout application (tous rôles)

```
┌──────────────────────────────────────────────────────────────┐
│ HEADER blanc, border-b ceni-blue-light                        │
│ [Logo 40px]  DDL Platform     [🔔 3]  Jean K. ▼            │
├────────────┬─────────────────────────────────────────────────┤
│ SIDEBAR    │  CONTENU PRINCIPAL bg-ceni-gray-100             │
│ bg-blanc   │                                                  │
│            │  ┌─────────┐ ┌─────────┐ ┌─────────┐            │
│ • Tableau  │  │ KPI 1   │ │ KPI 2   │ │ KPI 3   │            │
│ • Demandes │  └─────────┘ └─────────┘ └─────────┘            │
│ • Notif.   │                                                  │
│            │  ┌──────────────────────────────────────────┐   │
│            │  │ Tableau / Formulaire / Détail             │   │
│            │  └──────────────────────────────────────────┘   │
└────────────┴─────────────────────────────────────────────────┘
```

Sidebar : liens selon rôle, item actif `bg-ceni-blue-light text-ceni-blue`.

---

## 3. Tableau de bord Agent (`/agent`)

### KPI (3 cartes)

| Carte | Valeur exemple | Couleur accent |
|-------|----------------|----------------|
| Brouillons | 2 | gray |
| En cours | 5 | blue |
| Terminées | 12 | green |

### Tableau « Mes demandes »

Colonnes : Numéro | Titre | Statut (badge) | Priorité | Date soumission | Actions

Actions : Voir | Modifier (si brouillon) | Télécharger PDF

Bouton primaire en haut à droite : **+ Nouvelle demande**

---

## 4. Formulaire demande Agent (`/agent/demandes/nouvelle`)

- Stepper horizontal 6 étapes (sections formulaire)
- Boutons bas : **Enregistrer brouillon** (outline) | **Soumettre** (primary, dernière étape)
- Zone upload drag & drop section 6
- Barre progression complétion (% champs obligatoires)

---

## 5. Tableau de bord Secrétariat (`/secretariat`)

### Onglets

1. **À recevoir** — statut SOUMISE
2. **À transférer** — RECUE_SECRETARIAT
3. **Transférées** — historique récent

Action ligne : **Accuser réception** | **Transférer à la DI**

---

## 6. Tableau de bord DI (`/di`)

### KPI (4 cartes)

- Total actives
- En analyse
- En développement
- Délai moyen (jours)

### Filtres

Statut (multi-select) | Priorité | Période | Développeur | Recherche texte

### Tableau demandes

Actions contextuelles : Analyser | Valider | Rejeter | Affecter | Clôturer

### Panneau détail (drawer droit)

- Résumé demande + timeline historique vertical
- Onglet Commentaires
- Onglet Pièces jointes

---

## 7. Tableau de bord Développeur (`/developpeur`)

Colonnes : Numéro | Titre | Priorité | Statut | Date affectation

Actions : **Démarrer** | **Passer en test** | Mettre à jour statut

---

## 8. Administration (`/admin`)

### Sous-pages

- `/admin/utilisateurs` — CRUD tableau
- `/admin/historique` — journal global filtrable
- `/admin/parametres` — toggles notifications

---

## 9. Composants réutilisables

| Composant | Usage |
|-----------|-------|
| `StatutBadge` | Couleur selon statut |
| `PrioriteBadge` | CRITIQUE = rouge, etc. |
| `DemandeTable` | Liste paginée |
| `HistoriqueTimeline` | Vertical avec icônes |
| `NotificationBell` | Header + dropdown |
| `EmptyState` | Illustration + CTA |

---

## Figma (recommandation)

Créer un fichier Figma avec frames :
1. Login — Desktop 1440×900
2. Dashboard Agent — Desktop
3. Formulaire DDL — Desktop (étape 1 et étape 6)
4. Dashboard DI — Desktop avec drawer ouvert
5. Mobile login — 390×844 (responsive Phase 10)

Variables Figma alignées sur tokens `ceni-blue`, `ceni-blue-light`.
