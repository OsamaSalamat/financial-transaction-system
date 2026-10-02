# Stage 4 — API, Postman & Security

## API authentication
The API uses short-lived bearer tokens. Tokens are stored only as SHA-256 hashes in `api_tokens`; the raw token is returned once at login.

## Endpoints
- `POST /api/login.php`
- `GET /api/auth.php`
- `DELETE /api/auth.php`
- `GET /api/customers.php`
- `GET /api/accounts.php`
- `GET /api/funding_sources.php`
- `GET /api/transactions.php`
- `POST /api/transactions.php`
- `DELETE /api/transactions.php?id={id}`

## Roles
- `admin`: full application/API transaction privileges
- `accountant`: transaction posting/reversal and read access
- `viewer`: read-only API/application access

## Testing
Import `docs/postman_collection.json` into Postman. Login first, copy the returned token into the collection variable `token`, then run the read and transaction requests.

For a production deployment, replace the demo URL, force HTTPS, configure a non-root DB account, rotate credentials, add centralized rate limiting, backups, monitoring, and independent security/accounting review.
