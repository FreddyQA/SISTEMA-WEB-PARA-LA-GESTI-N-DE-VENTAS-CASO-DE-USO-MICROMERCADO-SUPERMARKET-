/**
 * DEFINICIÓN DE TIPOS Y CONTRATOS DE DATOS (TypeScript)
 * 
 * Propósito para Proyecto de Grado:
 * Garantiza la integridad de tipos (Type Safety) en toda la aplicación móvil.
 * Cada interfaz aquí declarada mapea de manera estricta los JSON generados por
 * las clases App\Http\Resources\* del backend Laravel. Esto previene errores de
 * ejecución en tiempo de compilación y documenta el contrato de la API REST.
 */

/**
 * Representa al usuario que ha iniciado sesión en el sistema.
 */
export interface Usuario {
  id: number;                          // Identificador único del usuario (PK en la tabla users)
  nombre: string;                      // Nombre completo del usuario
  email: string;                       // Correo electrónico utilizado para la autenticación
  rol: 'administrador' | 'invitado';   // Rol del usuario: 'administrador' (acceso total) o 'invitado' (vendedor/cajero)
  foto_url: string | null;             // URL completa de la foto de perfil del usuario alojada en el servidor
}

/**
 * Categoría para clasificar los productos del inventario.
 */
export interface Categoria {
  id: number;                          // ID de la categoría (idCategoria)
  nombre: string;                      // Nombre único de la categoría (ej: "Lácteos", "Bebidas")
  descripcion: string | null;          // Breve descripción o notas adicionales de la categoría
}

/**
 * Producto individual dentro del catálogo de inventario.
 */
export interface Producto {
  id: number;                          // ID único del producto (idProducto)
  nombre: string;                      // Nombre comercial del producto
  precio: number;                      // Precio de venta unitario en Bolivianos (Bs)
  stock: number;                       // Cantidad actual disponible en almacén
  stock_disponible?: number;           // Unidades no vencidas que pueden venderse
  sku: string;                         // Código único de barras / SKU (ej: PRD-000001)
  imagen_url: string | null;           // URL completa de la imagen del producto alojada en el servidor
  categoria?: { id: number; nombre: string };               // Relación con la categoría asignada
  lotes?: { id: number; cantidad: number; fecha_vencimiento: string | null }[];
}

/**
 * Cliente al cual se le registran las ventas.
 */
export interface Cliente {
  id: number;                          // ID único del cliente (idCliente)
  nombre: string;                      // Nombre o razón social del cliente
  email: string;                       // Correo electrónico del cliente
  telefono: string | null;              // Número telefónico para llamadas o WhatsApp
  direccion: string | null;             // Dirección de entrega o domicilio fiscal
}

/**
 * Renglón o ítem individual que compone el detalle de una venta.
 */
export interface VentaItem {
  producto_id: number;                 // ID del producto vendido
  producto_nombre: string;             // Nombre histórico del producto al momento de la venta
  cantidad: number;                    // Cantidad de unidades vendidas
  precio_unitario: number;             // Precio al que fue vendido en esta transacción
  subtotal: number;                    // Subtotal de la línea (cantidad * precio_unitario)
}

/**
 * Transacción de venta registrada en el sistema.
 */
export interface Venta {
  id: number;                          // ID de clave primaria de la venta (idVenta)
  numero?: number;                     // Folio secuencial sin huecos (1, 2, 3...)
  fecha: string;                       // Fecha de la venta en formato YYYY-MM-DD
  estado: 'completado' | 'anulado';    // Estado de la venta: 'completado' (vigente) o 'anulado' (cancelado y stock restituido)
  total: number;                       // Monto total en Bolivianos (Bs)
  cliente?: { id: number; nombre: string };                 // Cliente receptor de la venta
  vendedor?: { id: number; nombre: string } | null;         // Usuario del sistema que registró la transacción
  items?: VentaItem[];                // Productos incluidos en la venta
}

/**
 * Resumen de venta reciente para mostrar en el feed del Dashboard.
 */
export interface DashboardVentaReciente {
  id: number;                          // ID de clave primaria de la venta
  numero?: number;                     // Folio secuencial sin huecos (1, 2, 3...)
  fecha: string;                       // Fecha de registro
  estado: 'completado' | 'anulado';    // Estado de la transacción
  total: number;                       // Monto total
  cliente?: { id: number; nombre: string } | null;          // Nombre del cliente
}

/**
 * Resumen consolidado de indicadores del negocio para el Dashboard.
 */
export interface Dashboard {
  total_productos: number;             // Conteo total de productos activos
  total_clientes: number;              // Conteo total de clientes registrados
  stock_bajo: number;                  // Cantidad de productos con stock <= 5
  ventas_hoy: number;                  // Total en dinero vendido hoy (excluye anulados)
  transacciones_hoy: number;           // Número de transacciones completadas hoy
  ventas_mes: number;                  // Total en dinero vendido durante el mes
  ultimas_ventas?: DashboardVentaReciente[]; // Últimas 5 ventas para vista rápida
}

/**
 * Estructura de respuesta paginada devuelta por Laravel Eloquent / Paginator.
 */
export interface Paginado<T> {
  data: T[];                           // Colección de elementos de la página actual
  meta?: { current_page: number; last_page: number; total: number }; // Metadatos de paginación
  links?: { next: string | null; prev: string | null };              // Enlaces a página siguiente/previa
}

/**
 * Estructura de error estándar devuelta por la API ante excepciones o fallos 422.
 */
export interface ApiError {
  message: string;                     // Mensaje principal explicativo del error
  errors?: Record<string, string[]>;   // Diccionario de errores por campo devuelto por FormRequest
}

/**
 * Carga útil para crear o actualizar un producto.
 */
export interface DatosProducto {
  nombre: string;                      // Nombre del producto (obligatorio)
  precio: number;                      // Precio numérico positivo
  stock: number;                       // Cantidad entera no negativa
  idCategoria: number;                 // ID foráneo de la categoría
  fecha_vencimiento?: string | null;   // Vencimiento opcional del lote de stock agregado
}

/**
 * Carga útil para registrar o editar una venta.
 */
export interface DatosVenta {
  idCliente: number;                   // ID del cliente
  fecha: string;                       // Fecha de emisión
  estado: 'completado' | 'anulado';    // Estado deseado
  items: { idProducto: number; cantidad: number }[]; // Lista de productos y cantidades
}

/**
 * Carga útil para dar de alta o modificar un usuario del sistema.
 */
export interface DatosUsuario {
  name: string;                        // Nombre completo del usuario
  email: string;                       // Correo electrónico
  password?: string;                   // Contraseña (opcional en edición si no se desea cambiar)
  password_confirmation?: string;      // Confirmación idéntica de contraseña
  rol: 'administrador' | 'invitado';   // Nivel de permisos en el sistema
}

/**
 * Carga útil para dar de alta o modificar un cliente.
 */
export interface DatosCliente {
  nombre: string;                      // Nombre completo o razón social
  email: string;                       // Correo de facturación / contacto
  telefono?: string;                   // Teléfono móvil o fijo
  direccion?: string;                  // Dirección física
}
