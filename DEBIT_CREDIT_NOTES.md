# Debit and Credit Notes System

This document describes the debit and credit notes functionality added to the Receitek application.

## Overview

The system now supports three types of billing documents:

1. **Invoices** - Original bills for goods/services
2. **Debit Notes** - Additional charges or corrections that increase the amount owed
3. **Credit Notes** - Adjustments that decrease the amount owed

## Database Structure

### Debit Notes Table
- `id` - Primary key
- `tenant_id` - Foreign key to tenants table
- `client_id` - Foreign key to clients table
- `invoice_id` - Foreign key to invoices table (the original invoice)
- `debit_note_number` - Unique identifier (format: DN-YYYY-XXXX)
- `issue_date` - Date when the debit note was issued
- `due_date` - Payment due date
- `amount` - Decimal amount
- `reason` - Text explaining why the debit note was issued
- `description` - Detailed description
- `notes` - Internal notes
- `terms` - Payment terms

### Credit Notes Table
- `id` - Primary key
- `tenant_id` - Foreign key to tenants table
- `client_id` - Foreign key to clients table
- `invoice_id` - Foreign key to invoices table (the original invoice)
- `credit_note_number` - Unique identifier (format: CN-YYYY-XXXX)
- `issue_date` - Date when the credit note was issued
- `amount` - Decimal amount
- `reason` - Text explaining why the credit note was issued
- `description` - Detailed description
- `notes` - Internal notes
- `terms` - Terms and conditions

## Models

### DebitNote Model
- Relationships: tenant, client, invoice
- Methods:
  - `isOverdue()` - Check if overdue based on due date
- Scopes: None (removed status-based scopes)

### CreditNote Model
- Relationships: tenant, client, invoice
- Methods: None (removed status-based methods)
- Scopes: None (removed status-based scopes)

## Services

### DebitNoteService
- `generateDebitNoteNumber()` - Generate unique number
- `getOutstandingAmountForClient()` - Calculate total amount for client

### CreditNoteService
- `generateCreditNoteNumber()` - Generate unique number
- `getAvailableCreditForClient()` - Calculate total credit for client
- `getApplicableCreditNotes()` - Get all credit notes for a client

## Filament Resources

### DebitNoteResource
- Full CRUD operations
- Filters by date range
- Automatic tenant filtering

### CreditNoteResource
- Full CRUD operations
- Filters by date range
- Automatic tenant filtering

## Dashboard Widget

### BillingOverviewWidget
Displays key metrics:
- Total invoices
- Total debit notes count
- Total credit notes count
- Total debit amount
- Total credit amount

## Usage Examples

### Creating a Debit Note
```php
$debitNote = DebitNote::create([
    'tenant_id' => Auth::user()->id,
    'client_id' => $clientId,
    'invoice_id' => $invoiceId,
    'debit_note_number' => app(DebitNoteService::class)->generateDebitNoteNumber(),
    'issue_date' => now(),
    'due_date' => now()->addDays(30),
    'amount' => 100.00,
    'reason' => 'Additional shipping charges',
    'description' => 'Express shipping fee for urgent delivery',
]);
```

### Creating a Credit Note
```php
$creditNote = CreditNote::create([
    'tenant_id' => Auth::user()->id,
    'client_id' => $clientId,
    'invoice_id' => $invoiceId,
    'credit_note_number' => app(CreditNoteService::class)->generateCreditNoteNumber(),
    'issue_date' => now(),
    'amount' => 50.00,
    'reason' => 'Return of damaged goods',
    'description' => 'Credit for returned damaged items',
]);
```

### Checking Client Balances
```php
$totalDebitAmount = app(DebitNoteService::class)->getOutstandingAmountForClient($clientId);
$totalCreditAmount = app(CreditNoteService::class)->getAvailableCreditForClient($clientId);
$netBalance = $totalDebitAmount - $totalCreditAmount;
```

## Security Features

- Tenant isolation (users can only see their own data)
- Automatic tenant_id assignment on creation
- Proper authorization checks

## Future Enhancements

- Email notifications
- PDF generation for debit/credit notes
- Integration with payment gateways
- Bulk operations
- Advanced reporting and analytics 