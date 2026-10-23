<?php

namespace App\Modules\Subscription\Repositories;

use App\Models\SubscriptionPayment;
use Illuminate\Database\Eloquent\Collection;

class PaymentRepository
{
    public function createInvoice(array $data): SubscriptionPayment
    {
        return SubscriptionPayment::create($data);
    }

    public function findByInvoiceNumber(string $invoiceNumber): ?SubscriptionPayment
    {
        return SubscriptionPayment::where('invoice_number', $invoiceNumber)->first();
    }

    public function getFamilyInvoices(string $familyId): Collection
    {
        return SubscriptionPayment::with(['user', 'plan'])
            ->where('family_id', $familyId)
            ->orderBy('id', 'desc')
            ->get();
    }
}
