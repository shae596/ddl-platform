# Phase 8 — Module Administrateur

## Livrables

- [x] Tableau de bord synthèse (utilisateurs, demandes, activité récente)
- [x] Gestion des comptes utilisateurs (CRUD, rôles, activation/désactivation)
- [x] Historique global des actions (filtres date, action, recherche)
- [x] Paramètres de notifications in-app par type d'événement
- [x] Intégration workflow : notifications respectent les paramètres admin

## Routes

| Route | Description |
|-------|-------------|
| `GET /admin` | Tableau de bord |
| `GET /admin/users` | Liste utilisateurs |
| `GET /admin/users/create` | Créer |
| `POST /admin/users` | Enregistrer |
| `GET /admin/users/{id}/edit` | Modifier |
| `PUT /admin/users/{id}` | Mettre à jour |
| `DELETE /admin/users/{id}` | Désactiver |
| `GET /admin/historique` | Journal global |
| `GET /admin/parametres` | Config notifications |
| `PUT /admin/parametres/notifications` | Enregistrer toggles |

## Compte test

| Email | Mot de passe |
|-------|--------------|
| `sharonemulembweng@gmail.com` | `Password123!` |

## Rôles assignables

`AGENT`, `SECRETARIAT`, `DIRECTION_INFORMATIQUE`, `DEVELOPPEUR`, `ADMIN`

## Notifications configurables

| Paramètre | Événement |
|-----------|-----------|
| `notif_soumission` | Soumission CDC → secrétariat |
| `notif_transfert_di` | Transfert → DI |
| `notif_validation` | Validation → agent |
| `notif_rejet` | Rejet → agent |
| `notif_correction` | Correction demandée → agent |
| `notif_affectation` | Affectation → développeur |
| `notif_statut_dev` | Statut dev → DI |
| `notif_commentaire` | Commentaires DI / dev |

## Fichiers principaux

```
app/Http/Controllers/Admin/
app/Http/Requests/Admin/UserRequest.php
app/Models/Parametre.php
app/Services/ParametreService.php
resources/views/admin/
resources/views/layouts/admin.blade.php
```

## Sécurité

- Impossible de désactiver son propre compte admin
- Impossible de désactiver le dernier administrateur actif
- Mot de passe : min. 8 caractères, lettres + chiffres (création / changement)

## Plateforme complète (MVP)

Les 5 rôles disposent chacun de leur module :

| Phase | Rôle | Route |
|-------|------|-------|
| 3–4 | Agent | `/agent` |
| 5 | Secrétariat | `/secretariat` |
| 6 | DI | `/di` |
| 7 | Développeur | `/developpeur` |
| 8 | Admin | `/admin` |
