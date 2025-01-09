<?php

namespace App\Repositories;

use App\Models\LeadConversation;

class LeadConversationRepository
{
    public function getAllByLead($leadId)
    {
        return LeadConversation::with(['creator'])
            ->where('lead_id', $leadId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function find($id)
    {
        return LeadConversation::with(['creator'])->findOrFail($id);
    }

    public function create($leadId, array $data)
    {
        $data['lead_id'] = $leadId;
        $data['user_id'] = auth()->id();
        
        return LeadConversation::create($data);
    }

    public function update($id, array $data)
    {
        $conversation = $this->find($id);
        $conversation->update($data);
        return $conversation;
    }

    public function delete($id)
    {
        $conversation = $this->find($id);
        return $conversation->delete();
    }

    public function getRecentByLead($leadId, $limit = 5)
    {
        return LeadConversation::with(['creator'])
            ->where('lead_id', $leadId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getByType($leadId, $type)
    {
        return LeadConversation::with(['creator'])
            ->where('lead_id', $leadId)
            ->where('type', $type)
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
