# Modèle du formulaire DDL et cahier des charges

## Structure du formulaire (écran Agent)

Le formulaire est découpé en **6 sections** alignées sur le PDF généré.

### Section 1 — Identification

| Champ | Type | Obligatoire | Notes |
|-------|------|-------------|-------|
| `titre` | texte (200 car.) | Oui | Intitulé court du projet |
| `service_demandeur` | texte (150 car.) | Oui | Service / direction demandeur |
| `nom_demandeur` | texte (100 car.) | Oui | Prérempli depuis le profil agent |
| `email_demandeur` | email | Oui | Prérempli depuis le profil |
| `telephone_demandeur` | texte (30 car.) | Non | Contact complémentaire |
| `date_souhaitee_livraison` | date | Non | Date cible souhaitée |

### Section 2 — Contexte et problématique

| Champ | Type | Obligatoire | Notes |
|-------|------|-------------|-------|
| `contexte` | textarea (2000 car.) | Oui | Situation actuelle, enjeux |
| `problematique` | textarea (2000 car.) | Oui | Problème à résoudre |

### Section 3 — Objectifs

| Champ | Type | Obligatoire | Notes |
|-------|------|-------------|-------|
| `objectif_general` | textarea (1500 car.) | Oui | Objectif principal |
| `objectifs_specifiques` | liste dynamique (texte × 10 max) | Oui (≥1) | Puces numérotées dans le PDF |

### Section 4 — Périmètre fonctionnel

| Champ | Type | Obligatoire | Notes |
|-------|------|-------------|-------|
| `description_fonctionnelle` | textarea (3000 car.) | Oui | Fonctionnalités attendues |
| `utilisateurs_cibles` | textarea (1000 car.) | Oui | Profils utilisateurs finaux |
| `hors_perimetre` | textarea (1500 car.) | Non | Exclusions explicites |

### Section 5 — Contraintes et priorités

| Champ | Type | Obligatoire | Notes |
|-------|------|-------------|-------|
| `priorite` | enum | Oui | `BASSE`, `MOYENNE`, `HAUTE`, `CRITIQUE` |
| `contraintes_techniques` | textarea (1500 car.) | Non | OS, navigateurs, intégrations |
| `contraintes_reglementaires` | textarea (1500 car.) | Non | RGPD, sécurité, électoral |
| `dependances` | textarea (1000 car.) | Non | Systèmes existants concernés |

### Section 6 — Pièces jointes

| Champ | Type | Obligatoire | Notes |
|-------|------|-------------|-------|
| `pieces_jointes` | fichiers multiples | Non | PDF, DOCX, PNG, JPG — max 10 Mo/fichier, 5 fichiers |

---

## Comportement brouillon / soumission

| Action | Statut résultant | Règles |
|--------|------------------|--------|
| Enregistrer brouillon | `BROUILLON` | Champs partiels autorisés ; seul `titre` obligatoire |
| Soumettre | `SOUMISE` | Tous les champs obligatoires validés côté client + serveur |
| Modifier après soumission | — | Interdit sauf retour `A_CORRIGER` par la DI |

À la soumission :
1. Attribution du numéro `DDL-AAAA-NNN`
2. Génération du PDF cahier des charges
3. Enregistrement dans `historique_actions`
4. Notification au secrétariat

---

## Modèle du document PDF (cahier des charges)

### En-tête (chaque page)

```
[Logo CENI RDC]          COMMISSION ÉLECTORALE NATIONALE INDÉPENDANTE
                         CAHIER DES CHARGES — DEMANDE DE DÉVELOPPEMENT LOGICIEL

Numéro : DDL-2026-001          Date de soumission : 25/06/2026
Statut : Soumise               Priorité : Haute
```

### Corps du document

1. **Identification du demandeur** — Section 1
2. **Contexte et problématique** — Section 2
3. **Objectifs** — Section 3 (liste numérotée des objectifs spécifiques)
4. **Périmètre fonctionnel** — Section 4
5. **Contraintes** — Section 5
6. **Annexes** — Liste des pièces jointes (nom, type, taille, date)

### Pied de page

```
Document généré automatiquement par la plateforme DDL — CENI RDC
Page X / Y
```

### Stockage

| Artefact | Emplacement |
|----------|-------------|
| PDF généré | `pieces_jointes` avec `type = CAHIER_DES_CHARGES` |
| Données source | Colonnes JSON ou champs dédiés sur `demandes` (voir schéma Prisma) |

---

## Validation métier (règles serveur)

```text
BROUILLON → titre non vide
SOUMISE   → toutes les sections 1–5 complètes + ≥1 objectif spécifique
```

Messages d'erreur en français, codes HTTP 400 avec détail par champ.
