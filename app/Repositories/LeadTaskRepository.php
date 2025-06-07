<?php

namespace App\Repositories;

use App\Models\LeadTask;

class LeadTaskRepository
{
    public function getAll()
    {
        return LeadTask::get();
    }

    public function find($id)
    {
        return LeadTask::findOrFail($id);
    }

    public function updateStatus($id, $status)
    {
        $task = LeadTask::findOrFail($id);
        $task->status = $status;
        $task->save();
        return $task;
    }


    public function create(array $data)
    {      
        return LeadTask::create($data);
    }

    public function update($id, array $data)
    {
        $task = $this->find($id);
        $task->update($data);
        return $task;
    }

    public function delete($id)
    {
        $task = LeadTask::find($id);
        return $task->delete();
    }

    public function assignUser($taskId, $userId)
    {
        $task = $this->find($taskId);
        return $task->assignments()->create(['user_id' => $userId]);
    }

    public function removeAssignment($taskId, $userId)
    {
        $task = $this->find($taskId);
        return $task->assignments()->where('user_id', $userId)->delete();
    }
}
