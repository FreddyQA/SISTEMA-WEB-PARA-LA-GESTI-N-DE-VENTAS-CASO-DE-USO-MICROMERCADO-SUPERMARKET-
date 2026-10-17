import React, { createContext, useContext, useEffect, useRef } from 'react';
import { AppState, type AppStateStatus } from 'react-native';
import { SyncApi, type SyncEstado } from '../api/endpoints';
import { syncEvents } from '../utils/syncEvents';
import { useAuth } from './AuthContext';

interface SyncContextType {
  forzarSincronizacion: () => Promise<void>;
}

const SyncContext = createContext<SyncContextType>({
  forzarSincronizacion: async () => {},
});

export function SyncProvider({ children }: { children: React.ReactNode }) {
  const { usuario } = useAuth();
  const ultimoSyncRef = useRef<SyncEstado | null>(null);
  const esPrimerChequeoRef = useRef<boolean>(true);
  const sincronizandoRef = useRef<boolean>(false);

  async function chequearSincronizacion() {
    // Solo sincronizar si hay sesión activa y la app está en primer plano
    if (!usuario || sincronizandoRef.current || AppState.currentState !== 'active') {
      return;
    }

    sincronizandoRef.current = true;
    try {
      const data = await SyncApi.estado();

      if (esPrimerChequeoRef.current) {
        ultimoSyncRef.current = data;
        esPrimerChequeoRef.current = false;
        return;
      }

      const anterior = ultimoSyncRef.current;
      if (anterior && data.global > anterior.global) {
        const cambioProductos = data.productos > (anterior.productos || 0);
        const cambioCategorias = data.categorias > (anterior.categorias || 0);
        const cambioVentas = data.ventas > (anterior.ventas || 0);

        ultimoSyncRef.current = data;

        if (cambioProductos) {
          syncEvents.emit('sync:productos');
        }
        if (cambioCategorias) {
          syncEvents.emit('sync:categorias');
        }
        if (cambioVentas) {
          syncEvents.emit('sync:ventas');
        }
      }
    } catch {
      // Ignorar fallos transitorios de red para no interrumpir al usuario
    } finally {
      sincronizandoRef.current = false;
    }
  }

  useEffect(() => {
    if (!usuario) {
      ultimoSyncRef.current = null;
      esPrimerChequeoRef.current = true;
      return;
    }

    // Primer chequeo inmediato al iniciar sesión
    const timerInicial = setTimeout(chequearSincronizacion, 1000);

    // Intervalo de sondeo regular cada 3.5 segundos
    const intervalo = setInterval(chequearSincronizacion, 3500);

    // Pausar sondeo al pasar a segundo plano y reanudar al volver
    const suscripcionAppState = AppState.addEventListener('change', (estado: AppStateStatus) => {
      if (estado === 'active') {
        chequearSincronizacion();
      }
    });

    return () => {
      clearTimeout(timerInicial);
      clearInterval(intervalo);
      suscripcionAppState.remove();
    };
  }, [usuario]);

  return (
    <SyncContext.Provider value={{ forzarSincronizacion: chequearSincronizacion }}>
      {children}
    </SyncContext.Provider>
  );
}

export function useSync() {
  return useContext(SyncContext);
}
