# Changelog

All notable changes to `iprote/tcb-cms` will be documented in this file.

## [1.0.0] - 2026-06-28

### Added

- Initial production-ready release for TCB Bank CMS API integration
- Reference number creation and cancellation
- Instant payment notification (IPN) webhook at `POST /webhooks/tcb`
- Reconciliation API support (daily and date range)
- Multi-branch and multi-account database-driven configuration
- Automatic account resolution (collection, disbursement, loan collection)
- Loan module: application fee, repayment, disbursement tracking
- `TCB` facade with fluent branch builder
- Queue support for API calls and webhook processing
- Event-driven architecture with default listeners
- API request/response logging and failed request tracking
- Laravel 11, 12, and 13 support
- Unit and feature test suite

[1.0.0]: https://github.com/iprote-Technologies/tcb/releases/tag/v1.0.0
