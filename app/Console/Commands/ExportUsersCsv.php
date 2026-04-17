<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Support\Facades\File;
use function Laravel\Prompts\text;
use function Laravel\Prompts\select;

#[Signature('app:export-users-to-csv {--since= : Fecha inicial} {--status= : Estatus del usuario}')]
#[Description('Exporta usuarios a un archivo CSV solicitando datos si faltan')]
class ExportUsersCsv extends Command
{
    public function handle()
    {
        // 1. Obtener opciones o preguntar si no existen
        $since = $this->option('since') ?: text(
            label: '¿Desde qué fecha quieres filtrar?',
            placeholder: 'YYYY-MM-DD (Ej: 2024-01-01)',
            validate: fn (string $value) => match (true) {
                !empty($value) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) => 'El formato debe ser YYYY-MM-DD',
                default => null
            }
        );

        $status = $this->option('status') ?: select(
            label: '¿Qué estatus de usuario deseas exportar?',
            options: ['active' => 'Activo', 'inactive' => 'Inactivo', 'pending' => 'Pendiente'],
            default: 'active'
        );

        // 2. Preparar el archivo y directorio
        $directory = storage_path('app/exports');
        File::ensureDirectoryExists($directory);

        $fileName = "exports/users_{$status}_" . now()->format('Ymd_His') . ".csv";
        $path = storage_path('app/' . $fileName);
        
        $handle = fopen($path, 'w');
        fputcsv($handle, ['ID', 'Nombre', 'Email', 'Fecha Creación']);

        // 3. Consulta y exportación en chunks
        $query = User::query()
            ->when($since, fn($q) => $q->where('created_at', '>=', $since))
            ->when($status, fn($q) => $q->where('status', $status));

        $this->info("Exportando usuarios...");

        $query->chunk(500, function ($users) use ($handle) {
            foreach ($users as $user) {
                fputcsv($handle, [
                    $user->id,
                    $user->name,
                    $user->email,
                    $user->created_at->format('Y-m-d H:i:s'),
                ]);
            }
        });

        fclose($handle);

        $this->components->success("Archivo generado exitosamente en: {$path}");
    }
}