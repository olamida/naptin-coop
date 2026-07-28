<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;

class InvoiceController extends Controller
{
    public function show(PurchaseOrder $order): \Illuminate\View\View
    {
        $order->load(['member.region', 'product', 'approvedBy']);

        return view('invoices.purchase', ['order' => $order]);
    }
}
