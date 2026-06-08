# WordPress.org submission checklist — AI Evidence Block

Everything here is excluded from the distributed plugin zip (`.distignore` skips `/.wordpress-org`).
The plugin itself is **submission-grade**: it passes the official `plugin-check` clean on the
distributed build, was smoke-tested in real WordPress (wp-env, WP 7.1-alpha), and ships no dev files.

## ⛔ ONE BLOCKER — only you can clear it

**`Contributors: mizcausevic` in `readme.txt` is not a registered wordpress.org account**
(`https://profiles.wordpress.org/mizcausevic/` returns 404). The Contributors field must list
real WordPress.org logins, and the account that submits must appear there.

Do ONE of:
- **Register** the username `mizcausevic` at https://wordpress.org/ (it's free and currently available), then keep `readme.txt` as-is; **or**
- If your WordPress.org login is different, replace line 2 of `readme.txt` with that exact login.

Verify it resolves at `https://profiles.wordpress.org/<login>/` before submitting.

## ✅ Already done (this pack)

- `Tested up to: 7.0` (was 6.7 — plugin-check no longer flags it).
- `Domain Path: /languages` now resolves — `languages/ai-evidence-block.pot` generated.
- Removed discouraged `load_plugin_textdomain()` (WP auto-loads translations for hosted plugins).
- Plugin URI repointed to the GitHub repo (the old `kineticgain.com/ai-evidence-block/` 404'd).
- Short description trimmed under 150 chars; tags tuned.
- Screenshots section trimmed to the 4 captured images.
- **Assets staged in this folder** (these go to SVN `/assets`, NOT into the plugin zip):
  - `icon-128x128.png`, `icon-256x256.png`
  - `banner-772x250.png`, `banner-1544x500.png`
  - `screenshot-1.png` (inserter) · `screenshot-2.png` (sidebar) · `screenshot-3.png` (published card + inline) · `screenshot-4.png` (hover popover)

## 📤 Submit (your steps — needs your wordpress.org account)

1. Clear the blocker above.
2. Build a fresh zip: `npm run build && npm run plugin-zip` → `ai-evidence-block.zip`
   (already excludes `/src`, `/.github`, `/.wordpress-org`, node_modules, and dev configs).
3. Go to **https://wordpress.org/plugins/developers/add/**, sign in, and **upload the zip**.
4. Automated checks run, then a human reviewer looks at it. This takes **days to a few weeks** — be patient; respond to any reviewer email.
5. **On approval** you get an SVN repo at `https://plugins.svn.wordpress.org/ai-evidence-block/`:
   - Copy the plugin files into **`/trunk`**, then tag: copy `/trunk` → **`/tags/1.0.0`** (must match `Stable tag`).
   - Copy everything from this `.wordpress-org/` folder into the SVN **`/assets`** directory (icon, banner, screenshots).
   - `svn ci` to publish. The listing goes live shortly after.

## 🔁 Re-validate locally any time

```
# from the plugin dir, with Docker running:
export PATH="/c/Program Files/Docker/Docker/resources/bin:$PATH"
npx --yes @wordpress/env start
npx --yes @wordpress/env run cli wp plugin install plugin-check --activate
npx --yes @wordpress/env run cli wp plugin check ai-evidence-block --format=csv
```

Remaining plugin-check flags on the **repo** (`.editorconfig`, `.wp-env.json`, `phpcs.xml.dist`,
`.gitignore`, `.distignore`, `.github`, the build zip) are dev files that the **shipped zip excludes** —
confirm with `unzip -l ai-evidence-block.zip` (only `ai-evidence-block.php`, `uninstall.php`,
`readme.txt`, `LICENSE`, `includes/`, `build/`, `languages/` are present).

## Facts for the listing
- License: GPL-2.0-or-later · Requires WP 6.6+ · Requires PHP 7.4+ · Stable tag 1.0.0
- No external requests, no tracking, no options/tables (verified). Reference implementation of the
  [AI Evidence Format](https://github.com/mizcausevic-dev/ai-evidence-format-spec).
