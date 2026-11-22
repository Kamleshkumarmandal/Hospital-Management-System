# Original PHP / MySQL application

This is the original PHP appointment-booking application included in the repository.

## Local setup (XAMPP)

1. Start Apache and MySQL from XAMPP.
2. Copy this folder into XAMPP's `htdocs` directory.
3. Create a MySQL database named `edoc`.
4. Import `SQL_Database_edoc.sql` in phpMyAdmin.
5. Configure database environment variables as described in the repository root README, or use the default local XAMPP settings.
6. Open this folder's `index.html` through Apache.

## Demo accounts

The imported SQL file contains historical demo accounts with the shared password `123`. These credentials are for a local-only sample database. The updated login code upgrades a matching legacy password to a PHP `password_hash` hash after successful login. Change or remove these demo credentials before exposing the PHP app outside a local development environment.

## Important

This is an educational legacy PHP/MySQL project, not a production-ready healthcare record system. Do not use real patient information. The Streamlit app at the repository root is a separate synthetic-data demo; it does not expose this PHP/MySQL backend.
