<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLeadAttachedFileRequest;
use App\Http\Requests\UpdateLeadAttachedFileRequest;
use App\Repositories\LeadAttachedFileRepository;
use Illuminate\Http\Request;

class LeadAttachedFileController extends Controller
{
    protected $leadAttachedFileRepository;

    /**
     * Constructor que inyecta el repositorio de archivos adjuntos
     */
    public function __construct(LeadAttachedFileRepository $leadAttachedFileRepository)
    {
        $this->leadAttachedFileRepository = $leadAttachedFileRepository;
    }

    /**
     * Muestra la vista principal con todos los archivos adjuntos
     */
    public function index()
    {
        $files = $this->leadAttachedFileRepository->getAll();
        return view('lead.files.index', compact('files'));
    }

    /**
     * Retorna todos los archivos adjuntos en formato JSON
     */
    public function getAll()
    {
        $files = $this->leadAttachedFileRepository->getAll();
        return response()->json(['files' => $files]);
    }

    /**
     * Muestra el formulario de creación de archivo adjunto
     */
    public function create()
    {
        return view('lead.files.create');
    }

    /**
     * Almacena un nuevo archivo adjunto en el sistema
     */
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240', // 10MB max
            'lead_id' => 'required|exists:leads,id'
        ]);

        $file = $request->file('file');
        $path = $file->store('lead_files', 'public');

        $attachedFile = $this->leadAttachedFileRepository->create([
            'lead_id' => $request->lead_id,
            'file' => $path
        ]);

        return response()->json(['success' => true, 'file' => $attachedFile]);
    }

    /**
     * Muestra la información de un archivo adjunto específico
     */
    public function show($id)
    {
        $file = $this->leadAttachedFileRepository->find($id);
        return view('lead.files.show', compact('file'));
    }

    /**
     * Muestra el formulario de edición de un archivo adjunto
     */
    public function edit($id)
    {
        $file = $this->leadAttachedFileRepository->find($id);
        return view('lead.files.edit', compact('file'));
    }

    /**
     * Actualiza un archivo adjunto específico
     */
    public function update(UpdateLeadAttachedFileRequest $request, $id)
    {
        $updated = $this->leadAttachedFileRepository->update($id, $request->validated());
        if ($updated) {
            return response()->json(['success' => 'Archivo adjunto eliminado correctamente.'], 200);
        } else {
            return response()->json(['error' => 'Error al intentar eliminar el archivo adjunto.'], 500);
        }
    }

    /**
     * Elimina un archivo adjunto del sistema
     */
    public function destroy($id)
    {
        $deleted = $this->leadAttachedFileRepository->delete($id);
        if ($deleted) {
            return response()->json(['success' => 'Archivo adjunto eliminado correctamente.'], 200);
        } else {
            return response()->json(['error' => 'Error al intentar eliminar el archivo adjunto.'], 500);
        }
    }

    /**
     * Obtiene todos los archivos adjuntos asociados a un lead específico
     */
    public function getFilesByLead($leadId)
    {
        $files = $this->leadAttachedFileRepository->getByLeadId($leadId);
        return response()->json($files);
    }
}
