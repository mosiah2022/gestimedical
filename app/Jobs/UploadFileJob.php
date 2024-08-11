<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class UploadFileJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filePath;
    protected $patientId;
    protected $generated_new_name;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($filePath, $patientId, $generated_new_name)
    {
        $this->filePath = $filePath;
        $this->patientId = $patientId;
        $this->generated_new_name = $generated_new_name;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Obtener el contenido del archivo
        $content = Storage::get($this->filePath);

        // Subir el archivo a Google Cloud Storage
        $disk = Storage::disk('gcs');
        $disk->put('documents/' . $this->patientId . '/' . $this->generated_new_name, $content);

        // Eliminar el archivo temporal
        Storage::delete($this->filePath);
    }
}
