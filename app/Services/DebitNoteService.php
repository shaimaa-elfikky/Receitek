<?php

namespace App\Services;

use App\Models\DebitNote;
use Illuminate\Support\Facades\Auth;

class DebitNoteService
{

    /**
     * Calculate the total outstanding debit notes for a client
     */
    public function getOutstandingAmountForClient(int $clientId): float
    {
        return DebitNote::where('client_id', $clientId)
            ->where('tenant_id', Auth::user()->id)
            ->sum('amount');
    }
} 