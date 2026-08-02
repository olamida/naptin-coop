<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Services\DocumentService;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function show(PurchaseOrder $order): View
    {
        return app(DocumentService::class)->purchaseInvoice($order);
    }
}
