import { useCallback, useRef, useState } from 'react';
import type { Paginado } from '../api/types';

/**
 * Lista con paginación infinita: carga la página 1, y al llegar al final de
 * la lista el componente llama a `cargarMas()` para traer la siguiente (el
 * backend de Laravel ya pagina de a 20, la app antes solo mostraba la primera
 * página — con más de 20 productos/clientes/ventas el resto no aparecía).
 */
export function usePaginado<T>(
  fetcher: (pagina: number, q?: string) => Promise<Paginado<T>>
) {
  const [items, setItems] = useState<T[]>([]);
  const [cargando, setCargando] = useState(true);
  const [cargandoMas, setCargandoMas] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [hayMas, setHayMas] = useState(false);

  const paginaRef = useRef(1);
  const consultaRef = useRef<string | undefined>(undefined);
  // Contador de peticiones: si el usuario escribe rápido en el buscador, solo
  // cuenta la respuesta de la última petición (las anteriores se descartan
  // para que un resultado viejo no pise a uno más reciente).
  const peticionRef = useRef(0);

  const recargar = useCallback(
    async (q?: string, silencioso = false) => {
      const id = ++peticionRef.current;
      consultaRef.current = q;
      paginaRef.current = 1;
      if (!silencioso) {
        setCargando(true);
        setError(null);
      }
      try {
        const resp = await fetcher(1, q);
        if (id !== peticionRef.current) return;
        const lista = Array.isArray(resp?.data) ? resp.data : (Array.isArray(resp) ? (resp as unknown as T[]) : []);
        setItems(lista);
        setHayMas((resp?.meta?.current_page ?? 1) < (resp?.meta?.last_page ?? 1));
      } catch (e) {
        if (id !== peticionRef.current) return;
        if (!silencioso) {
          setError(e instanceof Error ? e.message : 'No se pudo cargar la lista.');
        }
      } finally {
        if (id === peticionRef.current && !silencioso) {
          setCargando(false);
        }
      }
    },
    [fetcher]
  );

  const cargarMas = useCallback(async () => {
    if (cargando || cargandoMas || !hayMas) return;
    const id = peticionRef.current;
    const siguiente = paginaRef.current + 1;
    setCargandoMas(true);
    try {
      const resp = await fetcher(siguiente, consultaRef.current);
      if (id !== peticionRef.current) return;
      paginaRef.current = siguiente;
      const masItems = Array.isArray(resp?.data) ? resp.data : (Array.isArray(resp) ? (resp as unknown as T[]) : []);
      setItems((previos) => [...previos, ...masItems]);
      setHayMas((resp?.meta?.current_page ?? siguiente) < (resp?.meta?.last_page ?? siguiente));
    } catch {
      // Un fallo al traer "más" no debe borrar lo ya cargado; el usuario
      // puede volver a deslizar para reintentar.
    } finally {
      setCargandoMas(false);
    }
  }, [cargando, cargandoMas, hayMas, fetcher]);

  return { items, cargando, cargandoMas, error, hayMas, recargar, cargarMas };
}
