# LedgerFlow Financial Transaction System

A self-contained PHP 8.2+ / MySQL financial transaction management application designed around the client's requested workflow: funding-source inputs, double-entry bookkeeping, transaction history, reporting/export, API access, audit logging and duplicate protection.

## Included

- Secure session authentication and role checks
- Customer management
- Funding-source records
- Chart of accounts
- Double-entry journal posting
- Atomic DB transactions / rollback
- Idempotency keys to prevent duplicate postings
- Reversal workflow (posted entries are not edited in place)
- Audit logs
- Dashboard
- Transaction search and filters
- CSV export
- JSON REST endpoints
- OpenAPI specification
- Prepared SQL statements and CSRF protection
- Password hashing with PHP password_hash/password_verify

## Requirements

- PHP 8.2+
- MySQL 8+ or MariaDB 10.4+
- Apache/XAMPP or another PHP web server
- PDO MySQL extension

No Composer or Node installation is required for this demo build.

## XAMPP setup

1. Copy the project folder into `C:\xampp\htdocs\financial-transaction-system`.
2. Start Apache and MySQL.
3. Open phpMyAdmin and import `database/schema.sql`.
4. If your MySQL credentials differ from the defaults, edit `config/config.php`.
5. Open `http://localhost/financial-transaction-system/public/login.php`.
6. Demo login: `admin@example.com` / `password`.
7. Change the demo password before any real deployment.

## Accounting example

For a $500 customer payment:

- Debit Cash / Bank: $500
- Credit Revenue: $500

The application rejects a journal if total debits and total credits do not match. Posting occurs inside one database transaction so partial postings are rolled back.

## API

- `POST /public/../api/transactions.php` — post transaction JSON
- `GET /public/../api/transactions.php` — search transaction history
- `GET /public/../api/reports.php` — dashboard/report data
- `docs/openapi.yaml` — API contract

In an Apache deployment, configure a clean `/api` route or reverse proxy rather than exposing parent-directory traversal paths.

## Security notes

This project is a client-demo / internal-system baseline. A production system that moves or settles real money should additionally undergo independent penetration testing, accounting review, secrets management, HTTPS enforcement, rate limiting/WAF, centralized monitoring, backups and restore drills, provider reconciliation, compliance assessment, and environment hardening.

Do not represent the author as holding security certifications or prior financial-system implementations unless those claims are independently true.

## Login troubleshooting

The current demo credentials are:

- Email: `admin@example.com`
- Password: `password`

If login still fails:

1. Confirm Apache and MySQL are running.
2. Confirm the database is named `ledgerflow`.
3. Confirm `PDO MySQL` is enabled in PHP.
4. Clear the browser cookies for `localhost` and retry.
5. Check `config/config.php` if your MySQL username/password is different.
