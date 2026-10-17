/**
 * EMISOR DE EVENTOS DE SINCRONIZACIÓN EN TIEMPO REAL (syncEvents)
 * 
 * Permite que cualquier pantalla de la app móvil se suscriba a eventos
 * de cambios en el servidor ('sync:productos', 'sync:categorias', 'sync:ventas')
 * y se actualice automáticamente en segundo plano sin reiniciar la pantalla.
 */

type Listener = () => void;

class SyncEventEmitter {
  private listeners: Map<string, Set<Listener>> = new Map();

  on(event: string, callback: Listener): void {
    if (!this.listeners.has(event)) {
      this.listeners.set(event, new Set());
    }
    this.listeners.get(event)!.add(callback);
  }

  off(event: string, callback: Listener): void {
    const set = this.listeners.get(event);
    if (set) {
      set.delete(callback);
      if (set.size === 0) {
        this.listeners.delete(event);
      }
    }
  }

  emit(event: string): void {
    const set = this.listeners.get(event);
    if (set) {
      set.forEach((cb) => {
        try {
          cb();
        } catch (e) {
          console.warn(`[SyncEvent] Error en listener para ${event}:`, e);
        }
      });
    }
  }
}

export const syncEvents = new SyncEventEmitter();
