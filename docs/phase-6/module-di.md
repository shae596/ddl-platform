# Phase 6 — Module Direction Informatique

## Livrables

- [x] Tableau de bord DI (4 KPI + notifications)
- [x] Liste des cahiers des charges avec onglets et filtres
- [x] Consultation du CDC (détail + PDF)
- [x] Prise en charge (`TRANSFEREE_DI` → `EN_ANALYSE`)
- [x] Mise en attente / reprise (`EN_ATTENTE`)
- [x] Validation (`VALIDEE`) — notification agent
- [x] Rejet avec motif obligatoire (`REJETEE`) — notification agent
- [x] Demande de corrections (`A_CORRIGER`) — notification agent
- [x] Délai prévisionnel de traitement (date)
- [x] Commentaires (publics ou internes)
- [x] Affectation à un ou plusieurs développeurs (`AFFECTEE`) — notification dev

## Workflow DI

```
TRANSFEREE_DI  → Prendre en charge     → EN_ANALYSE
EN_ANALYSE     → Mettre en attente     → EN_ATTENTE
EN_ATTENTE     → Reprendre l'analyse   → EN_ANALYSE
EN_ANALYSE     → Valider               → VALIDEE
EN_ANALYSE     → Rejeter (+ motif)     → REJETEE
EN_ANALYSE     → Demander correction   → A_CORRIGER
VALIDEE        → Affecter développeur  → AFFECTEE
```

Statut ajouté : `EN_ATTENTE` (En attente).

## Filtres liste

| Filtre | Champ |
|--------|-------|
| Recherche | numéro, titre, service |
| Direction / service | `service_demandeur` |
| Priorité | `priorite` |
| Statut | `statut` |
| Période | `date_soumission` (début / fin) |

## Routes

| Route | Description |
|-------|-------------|
| `GET /di` | Tableau de bord |
| `GET /di/demandes` | Liste |
| `GET /di/demandes/{id}` | Examen / détail |
| `GET /di/demandes/{id}/cahier-des-charges` | PDF |
| `POST /di/demandes/{id}/prendre-en-charge` | Prise en charge |
| `POST /di/demandes/{id}/mettre-en-attente` | Mise en attente |
| `POST /di/demandes/{id}/reprendre` | Reprise analyse |
| `POST /di/demandes/{id}/valider` | Validation |
| `POST /di/demandes/{id}/rejeter` | Rejet |
| `POST /di/demandes/{id}/demander-correction` | Retour agent |
| `POST /di/demandes/{id}/delai` | Délai prévisionnel |
| `POST /di/demandes/{id}/affecter` | Affectation dev |
| `POST /di/demandes/{id}/commentaires` | Commentaire |

## Compte test

| Email | Mot de passe |
|-------|--------------|
| `di@ceni.cd` | `Password123!` |

## Migration

Champ `delai_previsionnel` (date, nullable) sur la table `demandes`.

## Fichiers principaux

```
app/Http/Controllers/Di/
  DiDashboardController.php
  DemandeController.php
app/Http/Requests/Di/
app/Models/Commentaire.php
app/Models/AffectationDev.php
app/Services/DemandeWorkflowService.php  → méthodes DI
resources/views/di/
resources/views/layouts/di.blade.php
```

## Prochaine phase

**Phase 7 — Module Développeur** : recevoir les affectations, consulter le CDC, mettre à jour le statut, commentaires.

## Scénario de test

1. Secrétariat transfère un CDC à la DI
2. DI : **À examiner** → **Prendre en charge**
3. **Valider** ou **Rejeter** / **Demander correction**
4. Si validé : définir délai → **Affecter** `dev@ceni.cd`
5. Développeur reçoit une notification → module Phase 7 (`/developpeur`)
