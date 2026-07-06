# DDL — Plateforme de demande de développement logiciel (CENI RDC)

**DDL** = **D**emande de **D**éveloppement **L**ogiciel.
## Démarrage

```bash
# Configurer .env (DB_PASSWORD = mot de passe pgAdmin)
php artisan migrate:fresh --seed
php artisan serve --port=8001
```

Ouvrir **http://localhost:8001/login**

| Email | Mot de passe |
|-------|--------------|
| agent@ceni.cd | Password123! |

## Stack

- Laravel 13 (PHP)
- Blade + CSS (charte CENI blanc/bleu)
- PostgreSQL (`ddl_platform`)

## Documentation métier

Voir le dossier `docs/` (phases 0 et 1).

## Port

- DDL : `8001` (évite conflit avec immo sur `8000`)
