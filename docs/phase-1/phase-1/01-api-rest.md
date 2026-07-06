# Spécification API REST — DDL Platform

Base URL : `/api/v1`  
Authentification : `Authorization: Bearer <JWT>` (sauf routes publiques)  
Format : JSON (`Content-Type: application/json`)  
Dates : ISO 8601 UTC

---

## Codes HTTP

| Code | Usage |
|------|-------|
| 200 | Succès GET / PATCH |
| 201 | Création |
| 204 | Suppression sans corps |
| 400 | Validation métier |
| 401 | Non authentifié |
| 403 | Rôle insuffisant |
| 404 | Ressource introuvable |
| 409 | Conflit (transition workflow invalide) |
| 500 | Erreur serveur |

### Format erreur

```json
{
  "statusCode": 400,
  "message": "Validation échouée",
  "errors": [
    { "field": "contexte", "message": "Le contexte est obligatoire à la soumission" }
  ]
}
```

---

## Auth (`/auth`)

| Méthode | Route | Auth | Description |
|---------|-------|------|-------------|
| POST | `/auth/login` | Non | Connexion email + mot de passe |
| POST | `/auth/refresh` | Refresh token | Renouveler access token |
| POST | `/auth/logout` | Oui | Invalider refresh token |
| GET | `/auth/me` | Oui | Profil utilisateur connecté |

### POST `/auth/login`

**Body**
```json
{ "email": "agent@ceni.cd", "password": "********" }
```

**Réponse 200**
```json
{
  "accessToken": "eyJ...",
  "refreshToken": "eyJ...",
  "expiresIn": 3600,
  "user": {
    "id": "uuid",
    "email": "agent@ceni.cd",
    "nom": "Kabila",
    "prenom": "Jean",
    "role": "AGENT",
    "service": "Direction des opérations"
  }
}
```

---

## Utilisateurs (`/users`) — Admin

| Méthode | Route | Rôles | Description |
|---------|-------|-------|-------------|
| GET | `/users` | ADMIN | Liste paginée (`?page=1&limit=20&role=&q=`) |
| GET | `/users/:id` | ADMIN | Détail utilisateur |
| POST | `/users` | ADMIN | Créer utilisateur |
| PATCH | `/users/:id` | ADMIN | Modifier (rôle, actif, profil) |
| DELETE | `/users/:id` | ADMIN | Désactiver (soft: `actif=false`) |

### POST `/users`

```json
{
  "email": "dev@ceni.cd",
  "password": "MotDePasseSecurise1!",
  "nom": "Mukendi",
  "prenom": "Paul",
  "role": "DEVELOPPEUR",
  "service": "Direction Informatique",
  "telephone": "+243..."
}
```

---

## Demandes (`/demandes`)

| Méthode | Route | Rôles | Description |
|---------|-------|-------|-------------|
| GET | `/demandes` | Tous* | Liste filtrée par périmètre rôle |
| GET | `/demandes/:id` | Tous* | Détail + relations |
| POST | `/demandes` | AGENT, ADMIN | Créer brouillon |
| PATCH | `/demandes/:id` | AGENT** | Modifier brouillon ou correction |
| DELETE | `/demandes/:id` | AGENT** | Supprimer brouillon uniquement |
| POST | `/demandes/:id/soumettre` | AGENT | Soumettre → SOUMISE + PDF |
| GET | `/demandes/:id/pdf` | Tous* | Télécharger cahier des charges |

\* Filtrage automatique : Agent = ses demandes ; Dev = affectées ; Secrétariat/DI/Admin = tout  
\** Uniquement auteur et statut `BROUILLON` ou `A_CORRIGER`

### GET `/demandes` — Query params

| Param | Type | Description |
|-------|------|-------------|
| `page` | int | Page (défaut 1) |
| `limit` | int | Taille page (défaut 20, max 100) |
| `statut` | enum | Filtre statut |
| `priorite` | enum | Filtre priorité |
| `q` | string | Recherche titre / numéro |
| `dateDebut` | date | Filtre soumission |
| `dateFin` | date | Filtre soumission |
| `developpeurId` | uuid | Filtre affectation (DI) |

### POST `/demandes` — Body (brouillon)

```json
{
  "titre": "Application de suivi des bureaux de vote",
  "serviceDemandeur": "Direction des opérations",
  "priorite": "HAUTE"
}
```

### PATCH `/demandes/:id` — Body (champs formulaire partiels)

Tous les champs du modèle `Demande` modifiables selon statut.

---

## Workflow (`/demandes/:id/workflow`)

| Méthode | Route | Rôles | Transition |
|---------|-------|-------|------------|
| POST | `.../recevoir` | SECRETARIAT | SOUMISE → RECUE_SECRETARIAT |
| POST | `.../transferer-di` | SECRETARIAT | RECUE_* → TRANSFEREE_DI |
| POST | `.../prendre-en-charge` | DI | TRANSFEREE_DI → EN_ANALYSE |
| POST | `.../valider` | DI | EN_ANALYSE → VALIDEE |
| POST | `.../rejeter` | DI | EN_ANALYSE → REJETEE |
| POST | `.../demander-correction` | DI | EN_ANALYSE → A_CORRIGER |
| POST | `.../affecter` | DI | VALIDEE → AFFECTEE |
| POST | `.../demarrer-dev` | DEVELOPPEUR | AFFECTEE → EN_DEVELOPPEMENT |
| POST | `.../passer-en-test` | DEVELOPPEUR | EN_DEVELOPPEMENT → EN_TEST |
| POST | `.../retour-dev` | DI | EN_TEST → EN_DEVELOPPEMENT |
| POST | `.../terminer` | DI, DEVELOPPEUR | EN_TEST → TERMINEE |
| POST | `.../cloturer` | DI | TERMINEE / REJETEE → CLOTUREE |

### POST `.../rejeter`

```json
{ "motifRejet": "Périmètre trop vague, préciser les intégrations SI existantes." }
```

### POST `.../affecter`

```json
{ "developpeurIds": ["uuid-1", "uuid-2"], "commentaire": "Priorité sprint Q3" }
```

Chaque transition retourne la `demande` mise à jour + entrée `historique`.

---

## Pièces jointes (`/demandes/:id/pieces-jointes`)

| Méthode | Route | Rôles | Description |
|---------|-------|-------|-------------|
| GET | `/demandes/:id/pieces-jointes` | Tous* | Liste |
| POST | `/demandes/:id/pieces-jointes` | AGENT** | Upload `multipart/form-data` |
| GET | `/pieces-jointes/:id/download` | Tous* | Téléchargement |
| DELETE | `/pieces-jointes/:id` | AGENT** | Supprimer (brouillon uniquement) |

---

## Commentaires (`/demandes/:id/commentaires`)

| Méthode | Route | Rôles | Description |
|---------|-------|-------|-------------|
| GET | `/demandes/:id/commentaires` | Tous* | Liste (`interne` filtré par rôle) |
| POST | `/demandes/:id/commentaires` | DI, DEV, SECRETARIAT, ADMIN | Ajouter commentaire |

```json
{ "contenu": "Merci de préciser le volume utilisateurs.", "interne": false }
```

---

## Historique (`/demandes/:id/historique`)

| Méthode | Route | Rôles | Description |
|---------|-------|-------|-------------|
| GET | `/demandes/:id/historique` | Tous* | Journal chronologique |

---

## Notifications (`/notifications`)

| Méthode | Route | Rôles | Description |
|---------|-------|-------|-------------|
| GET | `/notifications` | Oui | Liste (`?lue=false&page=1`) |
| PATCH | `/notifications/:id/lire` | Oui | Marquer comme lue |
| PATCH | `/notifications/lire-tout` | Oui | Tout marquer lu |
| GET | `/notifications/non-lues/count` | Oui | Compteur badge |

---

## Dashboard / KPI (`/dashboard`)

| Méthode | Route | Rôles | Description |
|---------|-------|-------|-------------|
| GET | `/dashboard/agent` | AGENT | Mes stats (brouillons, en cours, terminées) |
| GET | `/dashboard/secretariat` | SECRETARIAT | À recevoir, en attente transfert |
| GET | `/dashboard/di` | DI | KPI globaux, par statut, par priorité, délais moyens |
| GET | `/dashboard/developpeur` | DEVELOPPEUR | Affectées, en cours, à livrer |
| GET | `/dashboard/admin` | ADMIN | Vue synthèse système |

### Exemple GET `/dashboard/di`

```json
{
  "total": 142,
  "parStatut": { "EN_ANALYSE": 12, "EN_DEVELOPPEMENT": 8, "TERMINEE": 45 },
  "parPriorite": { "CRITIQUE": 3, "HAUTE": 28 },
  "delaiMoyenAnalyseJours": 4.2,
  "delaiMoyenRealisationJours": 18.5
}
```

---

## Admin — Paramètres (`/admin/parametres`)

| Méthode | Route | Rôles | Description |
|---------|-------|-------|-------------|
| GET | `/admin/parametres` | ADMIN | Liste paramètres |
| PATCH | `/admin/parametres/:cle` | ADMIN | Modifier (ex. notifications email — Phase 9) |

---

## PDF (`/pdf`) — interne

Génération déclenchée par `POST /demandes/:id/soumettre`.  
Service interne `PdfService` — pas d'endpoint public séparé en MVP.

---

## Sécurité

- Mots de passe : bcrypt, coût 12
- JWT access : 1 h ; refresh : 7 j
- Rate limit login : 5 tentatives / 15 min / IP
- Validation MIME upload : whitelist `application/pdf`, `image/*`, `application/msword`, …
- CORS : origine frontend dev `http://localhost:5174`

---

## Pagination standard

```json
{
  "data": [],
  "meta": {
    "page": 1,
    "limit": 20,
    "total": 87,
    "totalPages": 5
  }
}
```
