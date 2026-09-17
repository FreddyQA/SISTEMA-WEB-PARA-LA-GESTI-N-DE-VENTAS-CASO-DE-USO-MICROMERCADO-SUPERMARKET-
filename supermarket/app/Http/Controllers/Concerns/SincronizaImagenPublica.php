<?php

namespace App\Http\Controllers\Concerns;

/**
 * En este proyecto public/storage NO es un symlink (php artisan storage:link),
 * es una carpeta real. Este trait copia/borra manualmente el archivo desde
 * storage/app/public hacia public/storage para que las imágenes sean
 * visibles públicamente. Usado por ProductoController y UsuarioController.
 */
trait SincronizaImagenPublica
{
    private function syncImageToPublicStorage(string $path): void
    {
        $source = storage_path('app/public/' . $path);
        $target = public_path('storage/' . $path);

        if (!file_exists($source) || file_exists($target)) {
            return;
        }

        if (!is_dir(dirname($target))) {
            mkdir(dirname($target), 0755, true);
        }

        copy($source, $target);
    }

    private function removeImageFromPublicStorage(?string $path): void
    {
        if (!$path) {
            return;
        }

        $target = public_path('storage/' . $path);

        if (file_exists($target)) {
            unlink($target);
        }
    }
}
