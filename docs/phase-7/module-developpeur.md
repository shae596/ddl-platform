# Phase 7 — Module Développeur

## Livrables

- [x] Tableau de bord (KPI + affectations récentes + notifications)
- [x] Liste des demandes affectées (onglets + filtres)
- [x] Consultation du cahier des charges (détail + PDF)
- [x] Démarrer le développement (`AFFECTEE` → `EN_DEVELOPPEMENT`) — notification DI
- [x] Passer en test (`EN_DEVELOPPEMENT` → `EN_TEST`) — notification DI
- [x] Commentaires (notification DI)

## Workflow développeur

```
DI affecte un dev     → AFFECTEE (+ notification dev)
Dev démarre           → EN_DEVELOPPEMENT (+ notification DI)
Dev passe en test     → EN_TEST (+ notification DI)
DI valide livraison   → TERMINEE (Phase 6 — à compléter côté DI)
```

Seules les demandes avec une **affectation active** (`affectations_dev.actif = true`) sont visibles par le développeur concerné.

## Routes

| Route | Description |
|-------|-------------|
| `GET /developpeur` | Tableau de bord |
| `GET /developpeur/demandes` | Liste (onglets) |
| `GET /developpeur/demandes/{id}` | Détail |
| `GET /developpeur/demandes/{id}/cahier-des-charges` | PDF |
| `POST /developpeur/demandes/{id}/demarrer` | Démarrer |
| `POST /developpeur/demandes/{id}/passer-en-test` | Passer en test |
| `POST /developpeur/demandes/{id}/commentaires` | Commentaire |

## Onglets liste

| Onglet | Statut |
|--------|--------|
| À démarrer | `AFFECTEE` |
| En développement | `EN_DEVELOPPEMENT` |
| En test | `EN_TEST` |
| Terminées | `TERMINEE`, `CLOTUREE` |

## Compte test

| Email | Mot de passe |
|-------|--------------|
| `dev@ceni.cd` | `Password123!` |

## Fichiers principaux

```
app/Http/Controllers/Developpeur/
  DeveloppeurDashboardController.php
  DemandeController.php
app/Services/DemandeWorkflowService.php  → demarrerDeveloppement(), passerEnTest(), ajouterCommentaireDeveloppeur()
resources/views/developpeur/
resources/views/layouts/developpeur.blade.php
```

## Scénario de test

1. DI : valider une demande → affecter `dev@ceni.cd`
2. Dev : notification → **Mes affectations** → **Démarrer le développement**
3. Dev : **Passer en test** (commentaire optionnel)
4. DI reçoit les notifications de changement de statut

## Prochaine phase

**Phase 8 — Administrateur** : voir `docs/phase-8/module-admin.md`.
