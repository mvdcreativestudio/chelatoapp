<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CompanySettings;
use App\Models\CFE;
use App\Models\Client;


class InvoiceController extends Controller
{
    public function index()
    {
        return view('content.accounting.invoices.index');
    }

    public function create()
    {
      $companySettings = CompanySettings::first();
      $clients = Client::all();

      return view('content.accounting.invoices.add', compact('companySettings', 'clients'));
    }
}
