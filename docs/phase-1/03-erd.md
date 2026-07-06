# Diagramme entités — PostgreSQL

```mermaid
erDiagram
    User ||--o{ Demande : "crée (auteur)"
    User ||--o{ AffectationDev : "est développeur"
    User ||--o{ Commentaire : "écrit"
    User ||--o{ HistoriqueAction : "effectue"
    User ||--o{ Notification : "reçoit"
    User ||--o{ PieceJointe : "upload"

    Demande ||--o{ PieceJointe : "contient"
    Demande ||--o{ Commentaire : "a"
    Demande ||--o{ HistoriqueAction : "trace"
    Demande ||--o{ AffectationDev : "affecte"
    Demande ||--o{ Notification : "référence"

    User {
        uuid id PK
        string email UK
        string password_hash
        string nom
        string prenom
        enum role
        boolean actif
    }

    Demande {
        uuid id PK
        string numero UK
        enum statut
        enum priorite
        string titre
        json objectifs_specifiques
        uuid auteur_id FK
        datetime date_soumission
    }

    PieceJointe {
        uuid id PK
        uuid demande_id FK
        string chemin_stockage
        enum type
    }

    Commentaire {
        uuid id PK
        uuid demande_id FK
        uuid auteur_id FK
        boolean interne
    }

    HistoriqueAction {
        uuid id PK
        uuid demande_id FK
        enum ancien_statut
        enum nouveau_statut
        string action
    }

    AffectationDev {
        uuid id PK
        uuid demande_id FK
        uuid developpeur_id FK
    }

    Notification {
        uuid id PK
        uuid user_id FK
        uuid demande_id FK
        enum type
        boolean lue
    }

    NumerotationDdl {
        int annee PK
        int dernier_numero
    }

    Parametre {
        string cle PK
        string valeur
    }
```

## Index principaux

| Table | Index | Raison |
|-------|-------|--------|
| `demandes` | `statut`, `auteur_id`, `date_soumission` | Filtres listes / KPI |
| `notifications` | `(user_id, lue)` | Badge non lues |
| `historique_actions` | `demande_id`, `created_at` | Timeline |
| `affectations_dev` | `developpeur_id` | Dashboard dev |

## Volumes estimés (année 1)

| Table | Estimation |
|-------|------------|
| users | 50–200 |
| demandes | 200–500 |
| historique_actions | 2 000–5 000 |
| notifications | 5 000–15 000 |
