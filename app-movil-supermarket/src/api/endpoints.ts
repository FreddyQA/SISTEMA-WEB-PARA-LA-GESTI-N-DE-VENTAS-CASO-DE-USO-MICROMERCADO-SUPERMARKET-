import { apiFetch } from './client';
import type {
  Categoria,
  Cliente,
  Dashboard,
  DatosCliente,
  DatosVenta,
  DatosProducto,
  DatosUsuario,
  Paginado,
  Venta,
  Producto,
  Usuario,
} from './types';

export const AuthApi = {
  login: (email: string, password: string) =>
    apiFetch<{ token: string; usuario: Usuario }>('/login', {
      method: 'POST',
      body: { email, password, dispositivo: 'app-movil' },
      sinToken: true,
    }),

  logout: () => apiFetch<{ message: string }>('/logout', { method: 'POST' }),

  yo: () => apiFetch<{ data: Usuario }>('/me'),
};

export const DashboardApi = {
  resumen: () => apiFetch<Dashboard>('/dashboard'),
};

export const ProductosApi = {
  listar: (q?: string, pagina = 1) =>
    apiFetch<Paginado<Producto>>(`/productos?page=${pagina}${q ? `&q=${encodeURIComponent(q)}` : ''}`),

  ver: (id: number) => apiFetch<{ data: Producto }>(`/productos/${id}`),

  porCodigo: (sku: string) =>
    apiFetch<{ data: Producto }>(`/productos/codigo/${encodeURIComponent(sku)}`),

  crear: (datos: DatosProducto) =>
    apiFetch<{ data: Producto }>('/productos', { method: 'POST', body: datos }),

  actualizar: (id: number, datos: DatosProducto) =>
    apiFetch<{ data: Producto }>(`/productos/${id}`, { method: 'PUT', body: datos }),

  // `uri` es la ruta local que entrega el selector de imágenes del celular.
  // Soporta `base64` para envío transparente vía JSON, eliminando fallos de red multipart en Android.
  subirImagen: (id: number, uri: string, mimeType?: string | null, base64?: string | null) => {
    const extension = (uri.split('.').pop() ?? 'jpg').toLowerCase().split('?')[0];

    if (base64) {
      return apiFetch<{ data: Producto }>(`/productos/${id}/imagen`, {
        method: 'POST',
        body: {
          imagen_base64: base64,
          extension,
        },
      });
    }

    const tipo = mimeType ?? (extension === 'png' ? 'image/png' : extension === 'webp' ? 'image/webp' : 'image/jpeg');
    const form = new FormData();
    // React Native acepta este objeto { uri, name, type } como "archivo" en FormData.
    form.append('imagen', { uri, name: `producto-${id}.${extension}`, type: tipo } as unknown as Blob);
    return apiFetch<{ data: Producto }>(`/productos/${id}/imagen`, { method: 'POST', body: form });
  },

  quitarImagen: (id: number) =>
    apiFetch<{ data: Producto }>(`/productos/${id}/imagen`, { method: 'DELETE' }),

  eliminar: (id: number) =>
    apiFetch<{ message: string }>(`/productos/${id}`, { method: 'DELETE' }),
};

export const CategoriasApi = {
  listar: () => apiFetch<{ data: Categoria[] }>('/categorias'),

  crear: (datos: { nombre: string; descripcion?: string }) =>
    apiFetch<{ data: Categoria }>('/categorias', { method: 'POST', body: datos }),

  actualizar: (id: number, datos: { nombre: string; descripcion?: string }) =>
    apiFetch<{ data: Categoria }>(`/categorias/${id}`, { method: 'PUT', body: datos }),

  eliminar: (id: number) =>
    apiFetch<{ message: string }>(`/categorias/${id}`, { method: 'DELETE' }),
};

export interface ReporteMetricas {
  cantidad_ventas: number;
  total_ventas: number;
  total_unidades: number;
  promedio_venta: number;
}

export interface FiltrosReporte {
  fecha_inicio?: string;
  fecha_fin?: string;
  id_cliente?: number;
  estado?: 'todos' | 'completado' | 'anulado';
}

export const ReportesApi = {
  ventas: (filtros: FiltrosReporte = {}) => {
    const params = new URLSearchParams();
    Object.entries(filtros).forEach(([k, v]) => {
      if (v !== undefined && v !== '') params.set(k, String(v));
    });
    const qs = params.toString();
    return apiFetch<{ metricas: ReporteMetricas; ventas: Paginado<Venta> }>(
      `/reportes/ventas${qs ? `?${qs}` : ''}`
    );
  },
};

export const ClientesApi = {
  listar: (q?: string, pagina = 1) =>
    apiFetch<Paginado<Cliente>>(`/clientes?page=${pagina}${q ? `&q=${encodeURIComponent(q)}` : ''}`),

  crear: (datos: DatosCliente) =>
    apiFetch<{ data: Cliente }>('/clientes', { method: 'POST', body: datos }),

  actualizar: (id: number, datos: DatosCliente) =>
    apiFetch<{ data: Cliente }>(`/clientes/${id}`, { method: 'PUT', body: datos }),

  eliminar: (id: number) => apiFetch<{ message: string }>(`/clientes/${id}`, { method: 'DELETE' }),
};

export interface NuevaVentaItem {
  idProducto: number;
  cantidad: number;
}

export const VentasApi = {
  listar: (idCliente?: number, pagina = 1) =>
    apiFetch<Paginado<Venta>>(`/ventas?page=${pagina}${idCliente ? `&cliente=${idCliente}` : ''}`),

  ver: (id: number) => apiFetch<{ data: Venta }>(`/ventas/${id}`),

  crear: (idCliente: number, items: NuevaVentaItem[]) =>
    apiFetch<{ data: Venta }>('/ventas', {
      method: 'POST',
      body: { idCliente, items },
    }),

  actualizar: (id: number, datos: DatosVenta) =>
    apiFetch<{ data: Venta }>(`/ventas/${id}`, { method: 'PUT', body: datos }),

  eliminar: (id: number) => apiFetch<{ message: string }>(`/ventas/${id}`, { method: 'DELETE' }),
};

export const UsuariosApi = {
  listar: () => apiFetch<{ data: Usuario[] }>('/usuarios'),

  crear: (datos: DatosUsuario) => apiFetch<{ data: Usuario }>('/usuarios', { method: 'POST', body: datos }),

  actualizar: (id: number, datos: Omit<DatosUsuario, 'password' | 'password_confirmation'> & { password?: string; password_confirmation?: string }) =>
    apiFetch<{ data: Usuario }>(`/usuarios/${id}`, { method: 'PUT', body: datos }),

  eliminar: (id: number) => apiFetch<{ message: string }>(`/usuarios/${id}`, { method: 'DELETE' }),

  subirImagen: (id: number, uri: string, mimeType?: string | null, base64?: string | null) => {
    const extension = (uri.split('.').pop() ?? 'jpg').toLowerCase().split('?')[0];

    if (base64) {
      return apiFetch<{ data: Usuario }>(`/usuarios/${id}/imagen`, {
        method: 'POST',
        body: {
          imagen_base64: base64,
          extension,
        },
      });
    }

    const tipo = mimeType ?? (extension === 'png' ? 'image/png' : extension === 'webp' ? 'image/webp' : 'image/jpeg');
    const form = new FormData();
    form.append('imagen', { uri, name: `usuario-${id}.${extension}`, type: tipo } as unknown as Blob);
    return apiFetch<{ data: Usuario }>(`/usuarios/${id}/imagen`, { method: 'POST', body: form });
  },

  quitarImagen: (id: number) =>
    apiFetch<{ data: Usuario }>(`/usuarios/${id}/imagen`, { method: 'DELETE' }),
};

export interface SyncEstado {
  productos: number;
  categorias: number;
  ventas: number;
  global: number;
}

export const SyncApi = {
  estado: () => apiFetch<SyncEstado>('/sync/estado'),
};
