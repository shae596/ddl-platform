# Phase 3 — Module Agent

## Livrables

- [x] Tableau de bord Agent (KPI + demandes récentes + notifications)
- [x] Formulaire DDL 4 sections (identification, contexte, objectifs, périmètre)
- [x] Brouillon / Générer CDC / Soumettre CDC (workflow en 3 étapes)
- [x] Liste « Mes demandes » avec filtres
- [x] Détail demande + historique
- [x] Numérotation `DDL-AAAA-NNN` à la génération du PDF
- [x] Notification secrétariat à la soumission du CDC
- [x] Téléchargement PDF (`agent.demandes.cahier`)
- [x] Modales de confirmation intégrées

## Workflow agent

```
Remplir formulaire → Enregistrer brouillon (titre seul suffit)
                  → Générer cahier des charges (PDF, sections 1–4 complètes)
                  → Télécharger et vérifier le PDF
                  → Soumettre le cahier des charges au secrétariat
```

## Routes Agent

| Route | Description |
|-------|-------------|
| `/agent` | Tableau de bord |
| `/agent/demandes` | Liste |
| `/agent/demandes/create` | Nouvelle demande |
| `/agent/demandes/{id}` | Détail |
| `/agent/demandes/{id}/cahier-des-charges` | Télécharger PDF |

## Logo

Fichier officiel : `public/assets/logo-ceni-rdc.png`
