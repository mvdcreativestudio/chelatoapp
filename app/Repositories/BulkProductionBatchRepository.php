<?php

namespace App\Repositories;

use App\Models\BulkProductionBatch;

class BulkProductionBatchRepository
{
    public function getAll()
    {
        return BulkProductionBatch::all();
    }

    public function find($id)
    {
        return BulkProductionBatch::findOrFail($id);
    }

    public function create(array $data)
    {
        return BulkProductionBatch::create($data);
    }

    public function update($id, array $data)
    {
        $bulkProductionBatch = $this->find($id);
        $bulkProductionBatch->update($data);
        return $bulkProductionBatch;
    }

    public function delete($id)
    {
        $bulkProductionBatch = BulkProductionBatch::find($id);
        return $bulkProductionBatch->delete();
    }
}
