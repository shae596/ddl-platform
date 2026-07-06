# Phase 2 — Setup Laravel + PostgreSQL (DDL — CENI RDC)

> **DDL** = Demande de Développement Logiciel

## Prérequis

- PHP 8.2+ et Composer
- PostgreSQL (base `ddl_platform`)

## Installation

```bash
cp .env.example .env   # si besoin
php artisan key:generate
# Éditer DB_PASSWORD dans .env
php artisan migrate:fresh --seed
php artisan serve --port=8001
```

## Comptes test

| Email | Rôle | Mot de passe |
|-------|------|--------------|
| agent@ceni.cd ou `0892905498` | Agent | Password123! |
| secretariat@ceni.cd | Secrétariat | Password123! |
| di@ceni.cd | Direction Informatique | Password123! |
| dev@ceni.cd | Développeur | Password123! |
| sharonemulembweng@gmail.com | Admin (Sharone Mulembwe) | Password123! |

## Fichiers supprimés (ancien stack Node)

- `backend/` (NestJS + Prisma)
- `frontend/` (React + Vite)

Conservés : `docs/`, schéma archivé dans `docs/reference/schema.prisma`
