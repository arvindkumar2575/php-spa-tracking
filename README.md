PHP Single‑Page App (SPA) - Small demo

Overview
- Minimal PHP project that serves a frontend from `public/` and includes simple visitor tracking.
- Tracks visitors in a `test_visitors` table via functions in `config/db_functions.php` and `config/tracking.php`.
- Uses `.htaccess` rules to forward requests into `public/` and to control access to sensitive folders.

Project structure (important files)
- `config/db.php` - PDO database connection.
- `config/db_functions.php` - Database helper functions (`dbInsert`, `dbSelect`, `dbCount`) and small callable aliases used by tracking.
- `config/tracking.php` - Visitor tracking: obtains IP/device/browser, calls the insert function, and sets a daily cookie.
- `public/index.php` - Main entry for the app.
- `public/page.php` - Example public page.
- `public/401.php` - Custom 401 error page.
- `public/.htaccess` & `/.htaccess` - Apache rewrite rules and access controls.

Routing and 401 behavior
- The root `.htaccess` forwards requests into `/public/` while blocking direct access to sensitive folders (returns 401).
- `public/.htaccess` currently: if the requested file or directory exists, it is served; otherwise the server returns 401 and shows `public/401.php`.
- If you want SPA-style fallback (serve `public/index.php` for unknown routes instead), modify the rewrite rules in `/.htaccess` or `public/.htaccess` accordingly.

Local setup (XAMPP on Windows)
1. Place this project inside your Apache document root (e.g., `C:\xampp\htdocs\php-spa`).
2. Configure database settings in `config/db.php` and create the `test_visitors` table.
3. Start Apache + MySQL in XAMPP.
4. Visit `http://localhost/` to view the app.

Testing 401 behavior
- Visit a non-existent public route (e.g. `http://localhost/nonexistent`) — currently this will return the 401 page.
- Requests that target sensitive folders (e.g. `/config/` or `/vendor/`) are intentionally blocked and will return 401.

Notes & suggestions
- Avoid using globals for the insert callable; you can call `dbInsert()` directly from `tracking.php` or expose a properly namespaced function to simplify testing.
- Ensure `php` is in your PATH if you want to run `php -l` lint checks from terminal.

If you want, I can: add a SQL schema snippet for `test_visitors`, switch the tracking code to call `dbInsert()` directly, or enable SPA fallback routing. Tell me which you'd like next.

