# Deploying the LifeSites2021 theme

The theme runs on **dev.lifesites.nl** — which, despite the name, is the live
WordPress backend: the Angular app (life-frontend, maintained by someone else)
reads all its content from its REST API. Every deploy is a production deploy.

Route: build a zip from git, upload it in WP-admin. No FTP needed.

## 1. Prepare

1. All changes committed and merged into `main`; be on `main`, up to date:
   ```bash
   git switch main && git pull
   ```
2. Bump the version — **both** places, same value (the build script checks):
   - `style.css` → `Version: x.y.z`
   - `functions.php` → `define( '_S_VERSION', 'x.y.z' );`
     (this is the `?ver=` on the stylesheet and scripts, so browsers fetch the new CSS)
3. Commit the bump, push, and optionally tag it:
   ```bash
   git commit -am "Release x.y.z" && git tag vx.y.z && git push && git push --tags
   ```

## 2. Build the zip

```bash
bin/build-zip.sh
```

Produces `dist/lifesites2021-<version>-<commit>.zip` with a top-level
`lifesites2021/` folder. Only committed files go in (`git archive`);
dev files listed in `.gitattributes` (export-ignore) are left out.
The script refuses to build with uncommitted changes, and warns when you are
not on `main`. `bin/build-zip.sh <ref>` builds any branch, tag or commit.

## 3. Upload

1. Log in at https://dev.lifesites.nl/wp-admin/
2. Appearance → Themes → **Add New Theme** → **Upload Theme**
3. Choose the zip → **Install Now**
4. WordPress sees the theme already exists and shows a comparison
   (current vs. uploaded version). Check the version, then click
   **Replace active with uploaded**.

WordPress keeps a temporary backup of the old theme while replacing, and
restores it automatically if the install fails.

## 4. Check

- Front: https://dev.lifesites.nl/ loads, no PHP errors.
- Appearance → Themes shows the new version.
- Editor: open a page in the block editor, no "unexpected error".
- REST, as the app sees it (anonymous):
  ```bash
  curl -s https://dev.lifesites.nl/wp-json/wp/v2/frontpage | head -c 300
  curl -s https://dev.lifesites.nl/wp-json/tribe/events/v1/events | head -c 300
  ```

## Rollback

Build the previous version and upload it the same way:

```bash
bin/build-zip.sh v<previous-version>   # or a commit sha
```

## Code that also lives in Code Snippets

Some fixes were first applied live as snippets (WP-admin → Snippets) and
are now also in this theme. Running both is harmless; after a deploy that
contains them, deactivate the snippet:

| Snippet | Theme file |
|---|---|
| #7 Gutenberg blocks in rest calls | `inc/rest-blocks.php` |
| #8 Agenda: kale `<p>` in REST description | `inc/rest-events.php` |

## Not used anymore

`.github/workflows/master.yml` (zip on GitHub release) uses
`actions/upload-artifact@v2`, which GitHub has retired; it no longer runs.
