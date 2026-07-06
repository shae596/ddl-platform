# Phase 4 — Génération PDF

## Livrables

- [x] Template HTML du cahier des charges (`resources/views/pdf/cahier-des-charges.blade.php`)
- [x] Logo CENI dans l'en-tête PDF
- [x] Génération à la demande (action « Générer le cahier des charges »)
- [x] Numérotation `DDL-AAAA-NNN` à la génération
- [x] Stockage en `pieces_jointes` (`type = CAHIER_DES_CHARGES`)
- [x] Téléchargement depuis la fiche demande
- [x] Soumission séparée : le PDF doit exister avant envoi au secrétariat

## Stack

- [barryvdh/laravel-dompdf](https://github.com/barryvdh/laravel-dompdf)

## Service

- `App\Services\CahierDesChargesPdfService` — génération du PDF
- `App\Services\DemandeWorkflowService::genererCahierDesCharges()` — déclenchement
- `App\Services\DemandeWorkflowService::soumettre()` — envoi au secrétariat (sans regénérer le PDF)

## Routes téléchargement

```
GET /agent/demandes/{demande}/cahier-des-charges       → agent.demandes.cahier
GET /secretariat/demandes/{demande}/cahier-des-charges → secretariat.demandes.cahier
GET /di/demandes/{demande}/cahier-des-charges          → di.demandes.cahier
```

## Contenu PDF

Sections 1 à 4 uniquement (identification, contexte, objectifs, périmètre). Pas de contraintes ni pièces jointes.

## Fichiers générés

Stockés dans `storage/app/demandes/{id}/DDL-2026-001-cahier-des-charges.pdf`
