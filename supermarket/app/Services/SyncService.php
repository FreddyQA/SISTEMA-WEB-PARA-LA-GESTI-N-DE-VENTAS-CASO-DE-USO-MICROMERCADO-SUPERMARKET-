<?php

namespace App\Services;

/**
 * SERVICIO CENTRAL DE SINCRONIZACIÓN EN TIEMPO REAL (SyncService)
 * 
 * Propósito:
 * Permite que tanto la aplicación Web (Blade) como la aplicación Móvil (React Native/Expo)
 * sincronicen cualquier cambio de datos (creación, edición, eliminación lógica o definitiva)
 * en tiempo real sin requerir recargar la página ni reiniciar la app.
 * 
 * Arquitectura:
 * - Almacena marcas de tiempo (timestamps en milisegundos) de la última mutación por entidad:
 *   'productos', 'categorias', 'ventas' y 'global'.
 * - Es ultra ligero (< 0.1ms por consulta), sin consultas pesadas a la base de datos.
 * - Es compatible tanto con entornos locales como con el APK compilado para Android.
 */
class SyncService
{
    private static function filePath(): string
    {
        return storage_path('framework/cache/sync_state.json');
    }

    /**
     * Registra una modificación en un módulo específico.
     * Actualiza la marca de tiempo de dicho módulo y la marca global.
     *
     * @param string $modulo Nombre del módulo ('productos', 'categorias', 'ventas')
     */
    public static function touch(string $modulo): void
    {
        try {
            $file = self::filePath();
            $dir = dirname($file);
            if (!is_dir($dir)) {
                @mkdir($dir, 0755, true);
            }

            $state = file_exists($file) ? (json_decode(@file_get_contents($file), true) ?: []) : [];
            $now = (int) round(microtime(true) * 1000);

            $state[$modulo] = $now;
            $state['global'] = $now;

            @file_put_contents($file, json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        } catch (\Throwable $e) {
            // Silencioso ante problemas de concurrencia de archivo
        }
    }

    /**
     * Retorna el estado actual de las marcas de tiempo de sincronización.
     *
     * @return array<string, int>
     */
    public static function getState(): array
    {
        try {
            $file = self::filePath();
            if (file_exists($file)) {
                $content = @file_get_contents($file);
                $state = json_decode($content, true);
                if (is_array($state) && !empty($state)) {
                    $ventas = (int) ($state['ventas'] ?? 0);

                    return [
                        'productos'  => (int) ($state['productos'] ?? 0),
                        'categorias' => (int) ($state['categorias'] ?? 0),
                        'ventas'     => $ventas,
                        'pedidos'    => $ventas,
                        'global'     => (int) ($state['global'] ?? 0),
                    ];
                }
            }
        } catch (\Throwable $e) {}

        $now = (int) round(microtime(true) * 1000);
        return [
            'productos'  => $now,
            'categorias' => $now,
            'ventas'     => $now,
            'pedidos'    => $now,
            'global'     => $now,
        ];
    }
}
