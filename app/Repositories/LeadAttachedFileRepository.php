<?php

namespace App\Repositories;

use App\Models\LeadAttachedFile;

class LeadAttachedFileRepository
{
    public function getAll()
    {
        return LeadAttachedFile::all();
    }

    public function find($id)
    {
        return LeadAttachedFile::findOrFail($id);
    }

    public function create(array $data)
    {
        return LeadAttachedFile::create($data);
    }

    public function update($id, array $data)
    {
        $file = $this->find($id);
        $file->update($data);
        return $file;
    }

    public function delete($id)
    {
        $file = $this->find($id);
        return $file->delete();
    }

    public function getByLeadId($leadId)
    {
        return LeadAttachedFile::where('lead_id', $leadId)->get();
    }
}
