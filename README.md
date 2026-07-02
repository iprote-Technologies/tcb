# iprote/tcb-cms

Production-ready Laravel package for **TCB Bank CMS API** integration.

**Developer:** Dr Constantino Msigwa  
**Company:** Iprote Technologies Limited

Supports payment collection, instant payment notifications (IPN), reconciliation, reference cancellation, loan disbursement, and extensible architecture for future TCB CMS services.

## Requirements

- PHP 8.2+
- Laravel 11+, 12+, or 13+

## Installation

```bash
composer require iprote/tcb-cms
```

Or, before Packagist publication, require directly from GitHub:

```bash
composer require iprote/tcb-cms:dev-main
```

### Publish on Packagist

1. Create a Packagist account at [packagist.org](https://packagist.org)
2. Submit package URL: `https://github.com/iprote-Technologies/tcb`
3. Enable the GitHub webhook for automatic updates on push/tag

After publication:

```bash
composer require iprote/tcb-cms
```

Publish configuration and migrations:

```bash
php artisan vendor:publish --tag=tcb-config
php artisan vendor:publish --tag=tcb-migrations
php artisan migrate
```

## Environment Configuration

Only API credentials belong in `.env`. Bank accounts are stored in the database.

```env
TCB_API_KEY=your-api-key
TCB_PARTNER_CODE=PART-ABC
TCB_CLIENT_ID=partner-xyz
TCB_CLIENT_SECRET=your-client-secret
TCB_BASE_URL=https://partners.tcbbank.co.tz
TCB_RECONCILIATION_URL=https://partners.tcbbank.co.tz:8444
TCB_WEBHOOK_SECRET=your-webhook-secret
TCB_TIMEOUT=30
TCB_VERIFY_SSL=true
TCB_LOGGING=true
TCB_QUEUE_CONNECTION=database
```

## Branch & Account Setup

```php
use Iprote\TcbCms\Facades\TCB;
use Iprote\TcbCms\Enums\AccountType;

$branch = TCB::branches()->create([
    'name' => 'Branch A',
    'code' => 'BR-A',
]);

TCB::branches()->addAccount($branch, [
    'account_name' => 'Collection Account',
    'account_number' => '173200000001',
    'profile_id' => '173200000001',
    'account_type' => AccountType::Collection,
    'is_default' => true,
]);

TCB::branches()->addAccount($branch, [
    'account_name' => 'Disbursement Account',
    'account_number' => '173200000002',
    'profile_id' => '173200000002',
    'account_type' => AccountType::Disbursement,
    'is_default' => true,
]);
```

## Usage

### Create Reference Number

```php
$reference = TCB::createReference('BR-A', [
    'reference' => '999ABC123456789',
    'name' => 'John Doe',
    'mobile' => '255713999934',
    'message' => 'TUITION FEE',
]);
```

### Fluent Builder

```php
TCB::branch('BR-A')
    ->collectionAccount()
    ->createReference([
        'reference' => '999ABC123456789',
        'name' => 'John Doe',
        'mobile' => '255713999934',
        'message' => 'COLLECTION',
    ]);

TCB::branch('BR-A')
    ->disbursementAccount()
    ->disburse([
        'amount' => 500000,
        'reference' => 'LOAN-001',
        'description' => 'Loan disbursement',
    ]);
```

### Cancel Reference

```php
TCB::cancelReference('BR-A', '999ABC123456789');
```

### Reconciliation

```php
TCB::reconciliation('BR-A', '2026-01-01', '2026-01-31');
```

### Verify Payment

```php
$transaction = TCB::verifyPayment('999ABC123456789');
```

### TCB Partners APIs (v1.2)

```php
$token = TCB::authenticate();
$fsps = TCB::botFsps();
$lookup = TCB::accountLookup([
    'accountNo' => '110210001001',
    'institutionCode' => '048',
]);
$transfer = TCB::aggregatorPayment([
    'reference' => '9099959804450',
    'msisdn' => '2556578717069',
    'amount' => '390.00',
    'description' => 'TIPS transfer payment',
]);
```

## Webhook (IPN)

TCB sends payment notifications to:

```
POST /webhooks/tcb
```

Configure your callback URL with TCB Bank to point to this endpoint.

The package verifies signatures, prevents duplicates, queues processing, stores payloads, and fires events.

### Listen for Events

```php
use Iprote\TcbCms\Events\PaymentReceived;

Event::listen(PaymentReceived::class, function (PaymentReceived $event) {
    // Update order, loan, invoice, etc.
});
```

Available events: `ReferenceCreated`, `ReferenceCancelled`, `PaymentReceived`, `PaymentFailed`, `PaymentVerified`, `LoanDisbursed`, `DisbursementCompleted`, `DisbursementFailed`, `ReconciliationCompleted`, `WebhookReceived`, `WebhookProcessed`.

## API Endpoints Implemented

| Operation | TCB Endpoint |
|-----------|--------------|
| Create Reference | `POST /public/api/reference/{API_KEY}` |
| Cancel Reference | `POST /public/api/reference/decline/{API_KEY}` |
| Reconciliation | `POST /public/api/reconciliation/{API_KEY}` |
| Auth (Bearer token) | `POST /tcb/partners/auth/authenticate` |
| BOT FSP list | `GET /tcb/partners/tips/fsps` |
| Account lookup | `POST /tcb/partners/tips-lookup` |
| Aggregator payment | `POST /tcb/partners/aggregator/payment` |
| Utility payment | `POST /tcb/partners/utility/payment` |
| Utility airtime | `POST /tcb/partners/utility/airtime` |
| GePG lookup | `POST /tcb/partners/utility/gepgLookUp` |
| GePG payment | `POST /tcb/partners/gepg/payment` |
| Deposit | `POST /tcb/partners/deposit` |
| Withdrawal | `POST /tcb/partners/withdrawal` |
| Transaction inquiry | `GET /tcb/partners/tqs?reference=...` |

## Architecture

- **Database-driven accounts** — no hardcoded profile IDs
- **Multi-branch** — unlimited branches with multiple accounts each
- **Account auto-resolution** — collection/disbursement accounts resolved by type
- **Extensible endpoints** — new TCB APIs via `EndpointInterface`
- **Queue support** — sync, database, redis, SQS
- **Full API logging** — requests, responses, webhooks, retries

## Testing

```bash
composer install
./vendor/bin/phpunit
```

## License

MIT
