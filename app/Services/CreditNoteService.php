<?php

namespace App\Services;

use App\Models\CreditNote;
use Illuminate\Support\Facades\Auth;

class CreditNoteService
{
    /**
     * Generate a unique credit note number
     */
    public function generateCreditNoteNumber(): string
    {
        $year = date('Y');
        $lastCreditNote = CreditNote::where('tenant_id', Auth::user()->id)
            ->whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastCreditNote ? (int)substr($lastCreditNote->credit_note_number, -4) + 1 : 1;

        return 'CN-' . $year . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

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