# README.md – Guide de collaboration GitHub pour Laravel 12

##Ce guide explique comment collaborer correctement sur ce dépôt GitHub pour **éviter les conflits** et garantir un développement fluide. **Seul Diègue fusionnera les rendu sur la branche `main`**.


5. Branche `develop` (pour le développement concerne uniquement :

```bash
git checkout -b develop
git push -u origin develop
```

---

## 2 Branches Git et leur rôle

| Branche         | Rôle                                                                                            |
| --------------- | ----------------------------------------------------------------------------------------------- |
| `main`          | Version **stable** pour production. Seul Diègue fusionne ici.                          |
| `develop`       | Version **en cours de développement**. Toutes les fonctionnalités passent par ici avant `main`. |
| `feature/<nom>` | Chaque nouvelle **fonctionnalité** (ex: `feature/auth`).                                        |
| `hotfix/<nom>`  | Correction rapide d’un **bug en production**.                                                   |

---

## Règles pour les collaborateurs

1. **Ne jamais coder directement sur `main` ou `develop`.**
2. **Créer votre branche de fonctionnalité à partir de `develop`** :

```bash
git checkout develop
git pull origin develop
git checkout -b feature/nom-de-fonctionnalité
```

3. **Commits clairs et fréquents** :

```bash
git add .
git commit -m "Description claire de la modification que vous avez apporté"
```

4. **Pousser sa branche sur GitHub** :

```bash
git push origin feature/nom-de-fonctionnalité
```

5. **Créer une Pull Request (PR) vers `develop`**.

   * Seul Diègue fait ceci pour fusionner après validation.
6. **Toujours mettre à jour sa branche avec `develop` avant de fusionner** :

```bash
git checkout develop
git pull origin develop
git checkout feature/nom-de-fonctionnalite
git merge develop
```

---

##Fusion dans `main` (à faire uniquement par Diègue)

1. Vérifier que `develop` est stable et testé.
2. Passer sur `main` :

```bash
git checkout main
```

3. Fusionner `develop` :

```bash
git merge develop
git push origin main
```

> Seul Diègue du projet effectue cette étape pour éviter tout conflit sur `main`.

---


* **Supprimer les branches de fonctionnalité après fusion** pour garder le dépôt propre :

```bash
git branch -d feature/nom-de-fonctionnalite   # local
git push origin --delete feature/nom-de-fonctionnalite   # distant
```

* Toujours **pull avant de commencer à coder** :

```bash
git checkout develop
git pull origin develop
```

* Écrire des **messages de commit clairs et explicites**.
* **Pas de modification directe de la branche `main`.**

