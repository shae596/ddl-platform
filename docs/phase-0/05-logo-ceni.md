# Logo CENI RDC — consignes

## Fichiers attendus

Déposer le logo officiel fourni par la communication CENI dans :

```
frontend/public/assets/logo-ceni-rdc.png   (recommandé, fond transparent)
frontend/public/assets/logo-ceni-rdc.svg   (optionnel, vectoriel)
```

## Placeholder de développement

En attendant le fichier officiel, un SVG placeholder est fourni :

```
frontend/public/assets/logo-ceni-rdc-placeholder.svg
```

Remplacer par le logo officiel avant mise en production.

## Spécifications techniques

| Critère | Valeur |
|---------|--------|
| Formats acceptés | PNG, SVG |
| Fond | Transparent de préférence |
| Résolution PNG min | 400×400 px (affichage max 80px hauteur) |
| Poids max | 500 Ko |

## Usage dans l'application

| Contexte | Fichier | Taille affichée |
|----------|---------|-----------------|
| Page connexion | `logo-ceni-rdc.png` | h-20 (80px) |
| En-tête application | `logo-ceni-rdc.png` | h-10 (40px) |
| PDF cahier des charges | PNG embarqué | 120px largeur |
| Favicon | dérivé du logo | 32×32 |

## Action requise (métier)

- [ ] Obtenir le logo officiel auprès de la communication CENI RDC
- [ ] Valider l'usage sur fond blanc et bleu clair
- [ ] Remplacer le placeholder dans `public/assets/`
