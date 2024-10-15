<?php

namespace App\Repositories;

use App\Models\CFE;
use Illuminate\Support\Facades\Storage;

class InvoiceRepository
{
  public function create()
  {
    return CFE::create();
  }

  public function update(CFE $invoice, array $data): bool
  {
    return $invoice->update($data);
  }

  public function delete(CFE $invoice): void
  {
    $invoice->delete();
  }

  public function getAll()
  {
    return CFE::all();
  }

  public function findById($id): ?CFE
  {
    return CFE::find($id);
  }


}
