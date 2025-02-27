<?php

namespace App\Repositories;

use App\Models\LeadCategories;

class LeadCategoriesRepository
{
    public function getAll()
    {
        return LeadCategories::orderBy('order')->get();
    }

    public function find($id)
    {
        return LeadCategories::findOrFail($id);
    }

    public function create(array $data)
    {
        return LeadCategories::create($data);
    }

    public function update($id, array $data)
    {
        $category = $this->find($id);
        $category->update($data);
        return $category;
    }

    public function delete($id)
    {
        $category = $this->find($id);
        return $category->delete();
    }
}