# Client Requirement Coverage

| Client requirement | Implementation |
|---|---|
| Multiple funding sources | `funding_sources` table + transaction association |
| Validation | server-side validation + ledger invariant checks |
| Automatic ledger posting | `Ledger::post()` |
| Double-entry bookkeeping | `journal_entries` + debit/credit equality check |
| Accurate history | immutable-style posted records + reversal workflow |
| Search/filter | customer/type/date filters |
| Export | CSV report export |
| High-volume foundation | indexed MySQL tables + atomic transactions + idempotency |
| Modern security | prepared SQL, CSRF, password hashing, roles, session regeneration |
| API extensibility | JSON endpoints + OpenAPI |
| Auditability | `audit_logs` |
| Testing | manual acceptance checklist + ledger invariant scenarios |

## Production gaps to discuss with client

- Exact payment/funding providers and their webhooks
- Multi-currency and FX requirements
- Tax/VAT requirements
- Reconciliation against external providers/banks
- PCI DSS scope if card data is ever handled
- MFA/SSO requirements
- Fine-grained RBAC
- Formal immutable audit-log storage / SIEM
- Load target and SLA
- Backup retention and disaster recovery
- Independent security and accounting review
