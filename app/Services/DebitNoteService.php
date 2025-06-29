<?php

namespace App\Services;

use App\Models\DebitNote;
use Illuminate\Support\Facades\Auth;

class DebitNoteService
{
    /**
     * Generate a unique debit note number
     */
    public function generateDebitNoteNumber(): string
    {
        $year = date('Y');
        $lastDebitNote = DebitNote::where('tenant_id', Auth::user()->id)
            ->whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastDebitNote ? (int)substr($lastDebitNote->debit_note_number, -4) + 1 : 1;

        return 'DN-' . $year . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

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