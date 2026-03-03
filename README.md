# BookIt for Cal.com — Starter Kit

Ce dossier contient tout ce qu'il faut pour lancer la génération du plugin
avec Claude Code dans VSCode.

## Contenu

| Fichier | Rôle |
|---|---|
| `CLAUDE.md` | Spécifications complètes lues par Claude Code à chaque session |
| `PROMPT_GENERATION.md` | Prompt à coller dans Claude Code pour lancer la génération |
| `.vscode/settings.json` | Config VSCode optimisée pour le dev WordPress |
| `.vscode/extensions.json` | Extensions recommandées |

## Instructions

### 1. Préparer l'environnement

```bash
# Copier ce dossier à l'emplacement de ton plugin WordPress
cp -r bookit-starter/ /ton/wordpress/wp-content/plugins/bookit-for-calcom/

# Ouvrir dans VSCode
code /ton/wordpress/wp-content/plugins/bookit-for-calcom/
```

### 2. Installer les extensions VSCode recommandées

VSCode proposera automatiquement d'installer les extensions recommandées
(popup en bas à droite). Accepte.

Extensions clés :
- **Intelephense** — IntelliSense PHP avec stubs WordPress
- **Prettier** — formatage automatique
- **WordPress Hooks** — autocomplétion des hooks WP

### 3. Lancer Claude Code

```bash
# Dans le terminal VSCode (racine du plugin)
claude
```

### 4. Coller le prompt de génération

Ouvre `PROMPT_GENERATION.md`, copie tout le contenu et colle-le dans
Claude Code. Il lira d'abord `CLAUDE.md` puis générera le plugin
fichier par fichier.

### 5. Build du bloc Gutenberg

Une fois les fichiers générés :

```bash
npm install
npm run build
```

### 6. Activer le plugin

Dans WordPress admin → Extensions → Activer "BookIt for Cal.com".

---

## Dev workflow quotidien

```bash
# Watch mode (recompile le bloc à chaque sauvegarde)
npm run start

# Générer le .pot après ajout de nouvelles chaînes
wp i18n make-pot . languages/bookit-for-calcom.pot
```

## Variables d'environnement utiles

Ajouter dans `wp-config.php` pour le dev :

```php
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'SCRIPT_DEBUG', true ); // Force le chargement des scripts non-minifiés
```
