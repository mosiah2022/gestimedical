<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Patient;

class UpdatePatientsFromExcelSeeder extends Seeder
{
    public function run()
    {
        $file = storage_path('app/patients_restructured_clean_php.csv');

        if (!file_exists($file)) {
            $this->command->error("❌ Archivo no encontrado: $file");
            return;
        }

        // Leer el archivo y limpiar líneas vacías
        $lines = array_filter(array_map('trim', file($file)));

        // Parsear las líneas en arrays usando ';' como delimitador
        $rows = array_map(fn($line) => str_getcsv($line, ';'), $lines);

        // Separar encabezado y datos
        $header = array_map('trim', array_shift($rows));

        $this->command->info("👀 Encabezados detectados: " . implode(', ', $header));

        foreach ($rows as $index => $row) {
            if (count($row) !== count($header)) {
                $this->command->warn("⚠️ Fila inválida en la línea " . ($index + 2) . ", se esperaban " . count($header) . " columnas y se encontraron " . count($row));
                continue;
            }

            $data = array_combine($header, $row);
            $data = array_map('trim', $data);

            // Normalizar ID
            $personalId = preg_replace('/\D/', '', $data['personal_id']);

            if ($personalId === '9877169') {
                $this->command->info("📌 Detectado paciente 9877169 en línea " . ($index + 2) . ": " . json_encode($data));
            }

            $patient = Patient::where('personal_id', $personalId)->first();

            if (!$patient) {
                $this->command->warn("⚠️ No se encontró paciente con ID: {$data['personal_id']}");
                continue;
            }

            $updated = $patient->update([
                'type_document' => $data['type_document'] ?: null,
                'sex'           => $data['sex'] ?: null,
                'birthday'      => $data['birthday'] ?: null,
                'address'       => $data['address'] ?: null,
                'phone'         => $data['phone'] ?: null,
                'email'         => $data['email'] ?: null,
                'type_user'     => $data['type_user'] ?: null,
                'disability'    => $data['disability'] ?: null,
            ]);

            if ($updated) {
                $this->command->info("✅ Paciente actualizado: {$data['personal_id']}");
            } else {
                $this->command->warn("⚠️ No se pudo actualizar: {$data['personal_id']}");
            }
        }

        $this->command->info('✔ Actualización de pacientes completada.');
    }
}
