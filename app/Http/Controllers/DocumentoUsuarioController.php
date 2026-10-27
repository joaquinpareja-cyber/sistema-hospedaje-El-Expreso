<?php

namespace App\Http\Controllers;

use App\Models\DocumentoUsuario;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Documentación del personal. Los archivos van al disco "local" (storage/app),
 * que no es accesible por URL: solo se ven a través de este controlador.
 */
class DocumentoUsuarioController extends Controller
{
    /** El empleado sube (o reemplaza) uno de sus documentos desde "Mi Perfil" */
    public function subir(Request $request, string $tipo)
    {
        $user = auth()->user();
        abort_unless($user->requiereDocumentacion(), 403);
        abort_unless(isset(DocumentoUsuario::TIPOS[$tipo]), 404);

        $request->validate([
            'archivo' => 'required|file|mimes:' . implode(',', DocumentoUsuario::EXTENSIONES) . '|max:' . DocumentoUsuario::MAX_KB,
        ], [
            'archivo.required' => 'Selecciona un archivo.',
            'archivo.mimes'    => 'Solo se aceptan archivos PDF, JPG o PNG.',
            'archivo.max'      => 'El archivo no debe superar ' . (DocumentoUsuario::MAX_KB / 1024) . ' MB.',
        ]);

        $archivo = $request->file('archivo');
        $ruta = $archivo->storeAs(
            "documentos/{$user->id}",
            $tipo . '_' . now()->format('YmdHis') . '.' . $archivo->extension(),  // extensión según el contenido real
            'local'
        );

        $anterior = $user->documentos()->where('tipo', $tipo)->first();
        if ($anterior) {
            Storage::disk('local')->delete($anterior->archivo);
        }

        $user->documentos()->updateOrCreate(['tipo' => $tipo], [
            'archivo'         => $ruta,
            'nombre_original' => mb_substr($archivo->getClientOriginalName(), 0, 255),
            'mime'            => $archivo->getMimeType(),
            'tamano'          => $archivo->getSize(),
        ]);

        $label = DocumentoUsuario::TIPOS[$tipo]['label'];

        return back()->with('success', "{$label}: documento " . ($anterior ? 'reemplazado' : 'subido') . ' correctamente.')
                     ->with('tab', 'documentacion');
    }

    /** Ver el documento: solo su dueño o un administrador */
    public function ver(User $usuario, string $tipo)
    {
        abort_unless(auth()->id() === $usuario->id || auth()->user()->isAdmin(), 403);

        $doc = $usuario->documentos()->where('tipo', $tipo)->firstOrFail();
        abort_unless(Storage::disk('local')->exists($doc->archivo), 404);

        // inline: el navegador lo abre (PDF o imagen) en vez de descargarlo
        return Storage::disk('local')->response($doc->archivo, $doc->nombre_original, [
            'Content-Type'           => $doc->mime,
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    /** El administrador elimina un documento (por ejemplo, si es ilegible) */
    public function eliminar(User $usuario, string $tipo)
    {
        $doc = $usuario->documentos()->where('tipo', $tipo)->firstOrFail();
        Storage::disk('local')->delete($doc->archivo);
        $doc->delete();

        return back()->with('success', DocumentoUsuario::TIPOS[$tipo]['label'] . ' eliminado. El empleado puede volver a subirlo.');
    }
}
