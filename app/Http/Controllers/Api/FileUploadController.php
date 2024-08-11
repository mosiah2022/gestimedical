<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\UploadFileJob;
use App\Models\Photo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileUploadController extends Controller
{
    public function listFiles($patient_id) {
        $photos = Photo::where('patient_id', $patient_id)->get();

        $files = $photos->map(function($photo) {
            return [
                'id' => $photo->id,
                'name' => $photo->name,
                'uri' => Storage::disk('gcs')->temporaryUrl($photo->uri, now()->addMinutes(15))
            ];
        });

        return response()->json(['files' => $files]);
    }

    public function fileStore(Request $request) {
        if ($request->hasFile('files')) {
            $files = $request->file('files');
            $patietnId = $request->patient_id;
            $count = 0;

            foreach ($files as $file) {
                $count++;
                $path = $file->store('uploads/temp');

                $file_name = $file->getClientOriginalName();
                $insert['title'] = $file_name;
                $generated_new_name = time().$count.'.'.$file->getClientOriginalExtension();

                UploadFileJob::dispatchSync($path, $patietnId, $generated_new_name);
                sleep(3);

                Photo::create([
                    'name'       => $generated_new_name,
                    'title'      => $insert['title'],
                    'patient_id' => $request->patient_id,
                    'uri'        => 'documents/'.$request->patient_id.'/'.$generated_new_name
                ]);
            }
            return response()->json(['message' => 'Files uploaded successfully'], 200);
        }
        
        return response()->json(['message' => 'Error on upload file'], 400); 
    }

    public function delete(Request $request)
    {
        $id        = $request->input('id'); 
        $patientId = $request->input('patient_id');
        $fileName  = $request->input('name');
        $filePath  = 'documents/'.$patientId. '/'. $fileName;

        try {
            $deleted = Storage::disk('gcs')->delete($filePath);
            if ($deleted) {
                $photo = Photo::findOrFail($id);
                $photo->delete();
                return response()->json(['success' => true, 'message' => 'Archivo eliminado']);
            } else {
                return response()->json(['success' => false, 'message' => 'Archivo no se pudo eliminar'], 500);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al eliminar el archivo: ' . $e->getMessage()], 500);
        }
    }
}
