<?php

namespace App\Filament\App\Widgets;

use App\Models\DebitNote;
use App\Models\CreditNote;
use App\Models\Invoice;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class BillingOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $tenantId = Auth::user()->id;

        $totalInvoices = Invoice::where('tenant_id', $tenantId)->count();
        $totalDebitNotes = DebitNote::where('tenant_id', $tenantId)->count();
        $totalCreditNotes = CreditNote::where('tenant_id', $tenantId)->count();

        $totalOutstandingAmount = DebitNote::where('tenant_id', $tenantId)->sum('amount');
        $totalAvailableCredit = CreditNote::where('tenant_id', $tenantId)->sum('amount');

        return [
            Stat::make('Total Invoices', $totalInvoices)
                ->description('All time invoices')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('primary'),

            Stat::make('Total Debit Notes', $totalDebitNotes)
                ->description('All debit notes')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('warning'),

            Stat::make('Total Credit Notes', $totalCreditNotes)
                ->description('All credit notes')
                ->descriptionIcon('heroicon-m-credit-card')
                ->color('success'),

            Stat::make('Total Debit Amount', '$' . number_format($totalOutstandingAmount, 2))
                ->description('Total debit amount')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('danger'),

            Stat::make('Total Credit Amount', '$' . number_format($totalAvailableCredit, 2))
                ->description('Total credit amount')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
        ];
    }
} 