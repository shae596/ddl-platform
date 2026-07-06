# Phase 0 — Cadrage DDL (CENI RDC)

> **DDL** — **D**emande de **D**éveloppement **L**ogiciel : plateforme de demande de développement logiciel à la Commission Électorale Nationale Indépendante de la République Démocratique du Congo.

## Objectif du projet

Centraliser le cycle de vie des demandes de développement logiciel : dépôt par les agents, traitement par le secrétariat et la Direction Informatique (DI), réalisation par les développeurs, traçabilité complète et génération automatique du cahier des charges (PDF).

## Périmètre fonctionnel (MVP)

| Module | Acteurs | Fonctions clés |
|--------|---------|----------------|
| Authentification | Tous | Connexion JWT, rôles |
| Agent | Agent métier | Créer / modifier brouillon, soumettre, consulter ses demandes |
| Secrétariat | Secrétaire | Lister, recevoir, transférer vers la DI |
| Direction Informatique | Chef DI, adjoint | KPI, valider / rejeter, affecter développeurs, commenter |
| Développeur | Dev | Consulter affectations, mettre à jour statuts techniques |
| Administrateur | Admin | Utilisateurs, rôles, paramètres, historique global |
| Transversal | Tous | Notifications, pièces jointes, historique des actions |

## Livrables Phase 0

| # | Livrable | Fichier | Statut |
|---|----------|---------|--------|
| 1 | Modèle formulaire + cahier des charges | [02-formulaire-cahier-des-charges.md](./02-formulaire-cahier-des-charges.md) | Validé (proposition) |
| 2 | Statuts et workflow | [03-workflow-statuts.md](./03-workflow-statuts.md) | Validé (proposition) |
| 3 | Charte graphique blanc / bleu | [04-charte-graphique.md](./04-charte-graphique.md) | Validé (proposition) |
| 4 | Logo CENI RDC | [05-logo-ceni.md](./05-logo-ceni.md) | En attente fichier officiel |

## Hypothèses de cadrage

1. **Une demande = un projet logiciel** identifié par un numéro unique `DDL-AAAA-NNN` (ex. `DDL-2026-001`).
2. **Un agent** ne voit que ses propres demandes ; les autres rôles voient selon leur périmètre.
3. **Le cahier des charges PDF** est généré automatiquement à la soumission (snapshot des champs du formulaire).
4. **PostgreSQL** en local pour le développement ; Docker optionnel en production.
5. **Langue interface** : français (messages API en français lorsque pertinent).

## Rôles utilisateur

| Rôle | Code | Description |
|------|------|-------------|
| Agent | `AGENT` | Crée et soumet des DDL depuis son service |
| Secrétariat | `SECRETARIAT` | Point d'entrée administratif, transfert DI |
| Direction Informatique | `DIRECTION_INFORMATIQUE` | Validation métier technique, affectation |
| Développeur | `DEVELOPPEUR` | Exécution et mise à jour des statuts de réalisation |
| Administrateur | `ADMIN` | Gestion des comptes, paramètres système |

Un utilisateur peut avoir **un seul rôle principal** en MVP (simplification auth).

## Critères de validation Phase 0

- [ ] Formulaire et sections du cahier des charges approuvés par le métier CENI
- [ ] Workflow et statuts validés par Secrétariat + DI
- [ ] Logo officiel déposé dans `frontend/public/assets/logo-ceni-rdc.png` (ou `.svg`)
- [ ] Couleurs bleu `#003DA5` et blanc confirmées par la communication CENI

## Prochaine étape

Phase 1 : schéma PostgreSQL, spécification API REST, maquettes interface (voir `docs/phase-1/`).
