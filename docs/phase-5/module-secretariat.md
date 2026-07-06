# Phase 5 — Module Secrétariat

## Livrables

- [x] Tableau de bord Secrétariat (KPI + notifications)
- [x] Réception automatique des CDC soumis (notification in-app)
- [x] Liste à onglets : À recevoir / À transférer / Transférées
- [x] Accusé de réception (`SOUMISE` → `RECUE_SECRETARIAT`)
- [x] Transfert à la Direction Informatique (`RECUE_SECRETARIAT` → `TRANSFEREE_DI`)
- [x] Consultation du détail et téléchargement PDF
- [x] Modales de confirmation intégrées (pas de popup navigateur)
- [x] Filtres : recherche texte, priorité

## Workflow secrétariat

```
Agent soumet le CDC          → SOUMISE (+ notification secrétariat)
Secrétariat accuse réception → RECUE_SECRETARIAT
Secrétariat transfère à DI   → TRANSFEREE_DI (+ notification DI)
```

La réception est **automatique** au sens où le secrétariat est notifié dès la soumission. L'accusé de réception et le transfert restent des actions explicites.

## Routes

| Route | Description |
|-------|-------------|
| `GET /secretariat` | Tableau de bord |
| `GET /secretariat/demandes` | Liste (onglets) |
| `GET /secretariat/demandes/{id}` | Détail |
| `GET /secretariat/demandes/{id}/cahier-des-charges` | Télécharger PDF |
| `POST /secretariat/demandes/{id}/recevoir` | Accuser réception |
| `POST /secretariat/demandes/{id}/transferer-di` | Transférer à la DI |

## Compte test

| Email | Mot de passe |
|-------|--------------|
| `secretariat@ceni.cd` | `Password123!` |

## Fichiers principaux

```
app/Http/Controllers/Secretariat/
  SecretariatDashboardController.php
  DemandeController.php
app/Services/DemandeWorkflowService.php  → recevoir(), transfererDi()
resources/views/secretariat/
resources/views/layouts/secretariat.blade.php
```

## Scénario de test

1. Agent : soumettre un cahier des charges
2. Secrétariat : notification → onglet **À recevoir** → **Accuser réception**
3. Onglet **À transférer** → **Transférer à la DI**
4. DI (`di@ceni.cd`) reçoit une notification
