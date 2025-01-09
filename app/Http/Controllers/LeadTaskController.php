<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLeadTaskRequest;
use App\Http\Requests\UpdateLeadTaskRequest;
use App\Repositories\LeadTaskRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LeadTaskController extends Controller
{
    protected $leadTaskRepository;

    /**
     * Constructor que inyecta el repositorio de tareas
     */
    public function __construct(LeadTaskRepository $leadTaskRepository)
    {
        $this->leadTaskRepository = $leadTaskRepository;
    }

    /**
     * Muestra la vista principal con todas las tareas
     */
    public function index()
    {
        $tasks = $this->leadTaskRepository->getAll();
        return view('lead.task', compact('tasks'));
    }

    /**
     * Retorna todas las tareas en formato JSON
     */
    public function getAll()
    {
        $tasks = $this->leadTaskRepository->getAll();
        return response()->json(['tasks' => $tasks]);
    }

    /**
     * Muestra el formulario de creación de tarea
     */
    public function create()
    {
        return view('lead.create');
    }

    /**
     * Almacena una nueva tarea en la base de datos
     */
    public function store(StoreLeadTaskRequest $request)
    {
        $task = $this->leadTaskRepository->create($request->validated());

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'leada creada con éxito.',
                'lead' => $task
            ]);
        }

        return redirect()->route('lead.index')->with('success', 'Lead creado con éxito.');
    }

    /**
     * Muestra la información de una tarea específica
     */
    public function show($id)
    {
        $lead = $this->leadTaskRepository->find($id);
        return view('lead.show', compact('lead'));
    }

    /**
     * Muestra el formulario de edición de una tarea
     */
    public function edit($id)
    {
        $lead = $this->leadTaskRepository->find($id);
        return view('lead.edit', compact('lead'));
    }

    /**
     * Actualiza una tarea específica en la base de datos
     */
    public function update(UpdateLeadTaskRequest $request, $id)
    {
        $this->leadTaskRepository->update($id, $request->validated());
        return redirect()->route('lead.index')->with('success', 'leada actualizada con éxito.');
    }

    /**
     * Actualiza el estado de una tarea
     */
    public function updateStatus($id, string $status)
    {
        $updated =   $this->leadTaskRepository->updateStatus($id, $status);
        if ($updated) {
            return response()->json(['success' => 'Tarea actualizada correctamente.'], 200);
        } else {
            return response()->json(['error' => 'Error al intentar eliminar la tarea.'], 500);
        }
    }

    /**
     * Elimina una tarea específica
     */
    public function destroy($id)
    {
        $deleted = $this->leadTaskRepository->delete($id);
        if ($deleted) {
            return response()->json(['success' => 'Lead task eliminada correctamente.'], 200);
        } else {
            return response()->json(['error' => 'Error al intentar eliminar el lead task.'], 500);
        }
    }

    /**
     * Asigna un usuario a una tarea
     */
    public function assignUser(Request $request, $taskId)
    {
        $userId = $request->input('user_id');
        $assignment = $this->leadTaskRepository->assignUser($taskId, $userId);
        return response()->json(['success' => true, 'assignment' => $assignment]);
    }

    /**
     * Elimina la asignación de un usuario a una tarea
     */
    public function removeAssignment($taskId, $userId)
    {
        $this->leadTaskRepository->removeAssignment($taskId, $userId);
        return response()->json(['success' => true]);
    }
}
