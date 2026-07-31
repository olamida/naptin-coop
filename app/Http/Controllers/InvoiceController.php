<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Services\DocumentService;

class InvoiceController extends Controller
{
    public function show(PurchaseOrder $order): \Illuminate\View\View
    {
        return app(DocumentService::class)->purchaseInvoice($order);
    }
}
