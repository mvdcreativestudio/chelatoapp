<?php

namespace App\Repositories;

use App\Models\Lead;
use App\Models\Client;
use Illuminate\Support\Facades\DB;
use Exception;

class LeadRepository
{
    public function getAll()
    {
        $query = Lead::with([
            'store',
            'creator',
            'tasks' => function($query) {
                $query->orderBy('due_date', 'asc');
            },
            'companyInformation',
            'assignments.user'
        ]);

        $user = auth()->user();

        // Si es administrador, ve todos los leads
        if ($user->hasRole('Administrador')) {
            // No aplicar filtros
        }
        // Si tiene permiso de ver todos los leads de la tienda
        elseif ($user->can('view_all_leads')) {
            $query->where('store_id', $user->store_id);
        }
        // Si es un usuario normal
        else {
            $query->where(function($q) use ($user) {
                $q->where('user_creator_id', $user->id)  // Leads creados por el usuario
                  ->orWhereHas('assignments', function($q) use ($user) {
                      $q->where('user_id', $user->id);   // Leads asignados al usuario
                  });
            });
        }

        return $query->orderBy('position')->get();
    }

    public function find($id)
    {
        $query = Lead::with([
            'companyInformation',
            'assignments.user',
            'tasks'
        ]);

        $user = auth()->user();

        if (!$user->hasRole('Administrador')) {
            if ($user->can('view_all_leads')) {
                $query->where('store_id', $user->store_id);
            } else {
                $query->where(function($q) use ($user) {
                    $q->where('user_creator_id', $user->id)
                      ->orWhereHas('assignments', function($q) use ($user) {
                          $q->where('user_id', $user->id);
                      });
                });
            }
        }

        return $query->findOrFail($id);
    }

    public function create(array $data)
    {
        $data['user_creator_id'] = auth()->id();
        $data['store_id'] = auth()->user()->store_id;
        
        $lead = Lead::create($data);
        
        // Crear el registro en lead_company_information
        $lead->companyInformation()->create([
            'lead_id' => $lead->id
        ]);
        
        return $lead;
    }

    public function update($id, array $data)
    {
        $lead = $this->find($id);
        $lead->update($data);
        return $lead;
    }

    public function delete($id)
    {
        $lead = Lead::find($id);
        return $lead->delete();
    }

    public function updateCategory($id, $categoryId, $position)
    {
        $lead = $this->find($id);

        $lead->category_id = $categoryId;
        $lead->position = $position;
        $lead->save();

        return $lead;
    }

    public function updateCompanyInformation($id, array $data)
    {
        $lead = $this->find($id);
        
        $companyInformation = $lead->companyInformation()->updateOrCreate(
            ['lead_id' => $id],
            $data
        );
        
        return $companyInformation;
    }

    public function convertToClient($id)
    {
        DB::beginTransaction();
        try {
            $lead = $this->find($id);
            
            // Crear el cliente
            $client = Client::create([
                'store_id' => $lead->store_id,
                'name' => $lead->name,
                'email' => $lead->email,
                'phone' => $lead->phone,
                'type' => $lead->type,
            ]);

            // Si hay información de compañía, actualizar los datos del cliente
            if ($lead->companyInformation) {
                $client->update([
                    'company_name' => $lead->companyInformation->name,
                    'address' => $lead->companyInformation->address,
                    'city' => $lead->companyInformation->city,
                    'state' => $lead->companyInformation->state,
                    'country' => $lead->companyInformation->country,
                    'website' => $lead->companyInformation->webpage,
                ]);
            }

            // Actualizar el lead con el client_id
            $lead->update(['client_id' => $client->id]);

            DB::commit();
            return $client;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function assignUser($leadId, $userId)
    {
        $lead = $this->find($leadId);
        $assignment = $lead->assignments()->create(['user_id' => $userId]);
        $assignment->load('user'); // Cargar la relación de usuario
        return $assignment;
    }

    public function removeUserAssignment($leadId, $userId)
    {
        $lead = $this->find($leadId);
        return $lead->assignments()->where('user_id', $userId)->delete();
    }
}
