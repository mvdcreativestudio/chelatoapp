<?php

namespace App\Repositories;

use App\Models\Vendor;
use Illuminate\Support\Facades\DB;

class VendorRepository
{
    public function getAll()
    {
        return Vendor::all();
    }

    public function create(array $data)
    {
        try {
            DB::beginTransaction();

            $vendor = Vendor::create([
                'name' => $data['name'],
                'lastname' => $data['lastname'],
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
                'user_id' => $data['user_id'] ?? null,
            ]);

            DB::commit();
            return $vendor;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function find($id)
    {
        return Vendor::findOrFail($id);
    }

    public function update($id, array $data)
    {
        $vendor = $this->find($id);
        $vendor->update($data);
        return $vendor;
    }

    public function delete($id)
    {
        return Vendor::destroy($id);
    }
}
