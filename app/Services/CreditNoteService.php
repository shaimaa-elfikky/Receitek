<?php

namespace App\Services;

use App\Models\CreditNote;
use Illuminate\Support\Facades\Auth;

class CreditNoteService
{


    /**
     * Calculate the total available credit for a client
     */
    public function getAvailableCreditForClient(int $clientId): float
    {
        return CreditNote::where('client_id', $clientId)
            ->where('tenant_id', Auth::user()->id)
            ->sum('amount');
    }

    /**
     * Get credit notes that can be applied to invoices
     */
    public function getApplicableCreditNotes(int $clientId): \Illuminate\Database\Eloquent\Collection
    {
        return CreditNote::where('client_id', $clientId)
            ->where('tenant_id', Auth::user()->id)
            ->orderBy('issue_date', 'asc')
            ->get();
    }
} 