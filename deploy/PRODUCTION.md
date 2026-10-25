# Production deployment

The live domain and hosting credentials must be supplied before deployment. This
checkout's local database and administrator password are development resources.

1. Install PHP 8.2+ with PDO MySQL, fileinfo, session and ZipArchive, and MySQL or
   MariaDB. Apply `deploy/php-production.ini` through your host's PHP settings.
   Disable Xdebug on the host. Configure private PHP error logs and log rotation.
2. Create a dedicated database and database user scoped to that database.
   Upload `app/`, `bin/`, `database/`, and `public/` into a private application
   directory. Create private `storage/sessions/` and `storage/backups/` directories.
   Copy existing uploads only when intentionally migrating content. Do not upload
   local `app/config.php`, `storage/local-admin.txt`, test data, local logs, source
   briefs, or development backups.
3. Set the domain document root to `public/`. Enable Apache `mod_rewrite`,
   `mod_headers` and AllowOverride for the shipped `.htaccess` files. Provision
   a valid TLS certificate and redirect HTTP to HTTPS at the host/load balancer.
   For Nginx configure equivalent rules, including denying script execution in
   uploads and routing `/robots.txt` to `/robots.php`; `.htaccess` is ignored.
4. Set these private environment variables in the web PHP service **and CLI/cron**:

   ```text
   GRAPHITE_CONFIG_FILE=/absolute/path/to/graphite/app/config.production.php
   GRAPHITE_BASE_URL=https://YOUR_LIVE_DOMAIN
   GRAPHITE_DB_DSN=mysql:host=localhost;dbname=YOUR_DATABASE;charset=utf8mb4
   GRAPHITE_DB_USER=YOUR_DATABASE_USER
   GRAPHITE_DB_PASSWORD=YOUR_PRIVATE_PASSWORD
   GRAPHITE_RATE_LIMIT_KEY=YOUR_64_CHARACTER_RANDOM_HEX_KEY
   ```

   Generate the key with `php -r "echo bin2hex(random_bytes(32)), PHP_EOL;"`.
   This application does not automatically load `.env` files. If the host cannot
   pass environment variables, create private `app/config.php` using
   `app/config.example.php`, fill in the real values and keep secure cookies on.
5. Grant the PHP account write access to `public/uploads/`, `storage/sessions/`
   and `storage/backups/` only. Use private ownership and permissions, never 777.
6. For an empty database run `php bin/install.php`, then `php bin/user.php` to
   create a fresh administrator. For an existing database, take a verified backup
   first, transfer approved content through the documented restore procedure,
   and run `php bin/migrate.php`. Rotate administrator credentials with
   `php bin/user.php` when migrating a local account; this revokes its sessions.
7. Run `php bin/check-production.php` under the production PHP configuration.
   Resolve every failure. Verify the web PHP settings separately through hosting
   tooling; CLI PHP settings can differ. Do not publish a `phpinfo()` page.
8. Schedule daily `php /absolute/path/to/graphite/bin/backup.php` and
   `php /absolute/path/to/graphite/bin/maintenance.php` with the same configuration
   environment. Monitor failures, copy backups to private offsite storage and
   test restoring a backup on a separate database before launch.
9. On the HTTPS domain test login/logout, publishing, uploads, enquiry submission,
   mobile navigation, sitemap and robots. Confirm HTTP redirects to HTTPS,
   cookies have Secure/HttpOnly/SameSite, directory listing is disabled, and
   `/../app/config.php`, `/storage/local-admin.txt`, `/app/config.php`,
   `/uploads/test.php` and hidden files cannot expose files or execute uploads.
   Inspect the server configuration as well as HTTP responses.
10. Approve company contacts, legal notices, project claims and image permissions.
    Staff must monitor the dashboard inbox; email delivery is not implemented.

Keep the prior application release and an independently verified database/media
backup for rollback. Stop writes before rollback or restoration. This repository
does not provision DNS, TLS, hosting, cron or offsite storage automatically.
