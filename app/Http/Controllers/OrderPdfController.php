<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PDF;
use App\Models\Order;
use App\Models\CompanySettings;

class OrderPdfController extends Controller
{
    public function generatePdf(Request $request, Order $order)
    {
        $products = $order->products;
        $clientOrdersCount = $order->client
        ? Order::where('client_id', $order->client->id)->count()
        : 0;

        $clientName = $order->client->name ?? 'Consumidor Final';
            $companySettings = CompanySettings::first();

        $data = [
            'order' => $order,
            'products' => $products,
            'clientOrdersCount' => $clientOrdersCount,
            'companySettings' => $companySettings
        ];

        $pdf = PDF::loadView('content.e-commerce.backoffice.orders.order-pdf', $data);

        if ($request->query('action') === 'print') {
            return $pdf->stream('order-pdf.pdf');
        } else {
            return $pdf->download('order-pdf.pdf');
        }
    }
}
