/**
 * CLIENTE HTTP Y GESTOR DE COMUNICACIÓN CON LA API REST (apiFetch)
 * 
 * Propósito Académico / Proyecto de Grado:
 * Capa de red centralizada que encapsula la API nativa `fetch` de JavaScript.
 * Maneja de forma transparente:
 * 1. Base URL Dinámica: Permite cambiar la dirección IP del servidor local (ej: http://192.168.0.105:8000)
 *    directamente desde la interfaz móvil sin recompilar la app, facilitando pruebas en dispositivos físicos.
 * 2. Inyección de Cabecera de Autorización: Agrega automáticamente `Authorization: Bearer <token>`
 *    en todas las peticiones autenticadas.
 * 3. Soporte Híbrido JSON / Multipart: Detecta si la carga útil es `FormData` (para subir fotos de productos)
 *    o un objeto plano, asignando los headers y límites (boundaries) correspondientes.
 * 4. Manejo Robusto de Excepciones: Transforma respuestas de error 4xx y 5xx en instancias de la clase
 *    personalizada `ApiRequestError`, capturando los mensajes de validación generados por Laravel.
 */

import AsyncStorage from '@react-native-async-storage/async-storage';
import * as SecureStore from 'expo-secure-store';
import type { ApiError } from './types';

// Configuración por defecto: IP de red local para teléfono físico
const URL_POR_DEFECTO = 'http://192.168.100.201:8000';
const CLAVE_URL = '@supermarket/api_url';
const CLAVE_TOKEN = 'supermarket_auth_token';
const TIEMPO_MAXIMO_PETICION_MS = 10000;

// Listeners globales para invalidar reactivamente la sesión cuando el servidor responde 401
type SesionVencidaCallback = () => void;
const listenersSesionVencida: SesionVencidaCallback[] = [];

export function alVencerSesion(callback: SesionVencidaCallback): () => void {
  listenersSesionVencida.push(callback);
  return () => {
    const idx = listenersSesionVencida.indexOf(callback);
    if (idx !== -1) listenersSesionVencida.splice(idx, 1);
  };
}

async function guardarSeguro(clave: string, valor: string): Promise<void> {
  try {
    await SecureStore.setItemAsync(clave, valor);
  } catch {
    await AsyncStorage.setItem(clave, valor);
  }
}

async function obtenerSeguro(clave: string): Promise<string | null> {
  try {
    return await SecureStore.getItemAsync(clave);
  } catch {
    return await AsyncStorage.getItem(clave);
  }
}

async function borrarSeguro(clave: string): Promise<void> {
  try {
    await SecureStore.deleteItemAsync(clave);
  } catch {
    await AsyncStorage.removeItem(clave);
  }
}

// Caché en memoria para evitar accesos redundantes al almacenamiento asíncrono
let baseUrlCache: string | null = null;
let tokenCache: string | null = null;

/**
 * Recupera la URL base configurada para comunicarse con el servidor Laravel.
 * @returns Promesa con la URL base activa
 */
export async function obtenerBaseUrl(): Promise<string> {
  if (baseUrlCache) return baseUrlCache;
  let guardada = await AsyncStorage.getItem(CLAVE_URL);
  if (guardada) {
    let corregida = guardada.trim().replace(/\/+$/, '');
    // Auto-corrección si se guardó accidentalmente con puerto :800 en lugar de :8000
    if (/:800$/i.test(corregida)) {
      corregida = corregida + '0';
      await AsyncStorage.setItem(CLAVE_URL, corregida);
    }
    baseUrlCache = corregida;
    return baseUrlCache;
  }
  baseUrlCache = URL_POR_DEFECTO;
  return baseUrlCache;
}

/**
 * Guarda y actualiza la URL base del backend (ej: tras cambiar la IP en ajustes).
 * @param url Dirección HTTP del servidor
 */
export async function guardarBaseUrl(url: string): Promise<void> {
  let limpia = url.trim().replace(/\/+$/, '');
  // Auto-corrección si el usuario escribió :800 en vez de :8000
  if (/:800$/i.test(limpia)) {
    limpia = limpia + '0';
  }
  baseUrlCache = limpia;
  await AsyncStorage.setItem(CLAVE_URL, limpia);
}

/**
 * Resuelve una URL de imagen almacenada en el backend.
 * Si el backend retorna una URL con "localhost" o "127.0.0.1", la reemplaza
 * automáticamente por la base URL activa en el dispositivo para que la imagen cargue.
 */
export function resolverImagenUrl(url: string | null | undefined): string | null {
  if (!url) return null;
  const baseActual = baseUrlCache || URL_POR_DEFECTO;
  if (/^https?:\/\/(localhost|127\.0\.0\.1)(:\d+)?/i.test(url)) {
    return url.replace(/^https?:\/\/(localhost|127\.0\.0\.1)(:\d+)?/i, baseActual);
  }
  return url;
}

/**
 * Obtiene el token de autenticación Bearer almacenado de forma segura en el teléfono.
 */
export async function obtenerToken(): Promise<string | null> {
  if (tokenCache !== null) return tokenCache;
  tokenCache = await obtenerSeguro(CLAVE_TOKEN);
  return tokenCache;
}

/**
 * Almacena el token recibido tras un login exitoso en el almacenamiento seguro.
 */
export async function guardarToken(token: string): Promise<void> {
  tokenCache = token;
  await guardarSeguro(CLAVE_TOKEN, token);
}

/**
 * Elimina el token del dispositivo de forma segura al cerrar sesión o por expiración.
 */
export async function borrarToken(): Promise<void> {
  tokenCache = null;
  await borrarSeguro(CLAVE_TOKEN);
}

/**
 * Clase de excepción personalizada para errores provenientes de la API REST.
 * Permite a la interfaz de usuario mostrar el mensaje amigable y los errores por campo.
 */
export class ApiRequestError extends Error {
  constructor(
    message: string,
    public status: number,                        // Código de estado HTTP (ej: 401, 403, 404, 422, 500)
    public errors?: Record<string, string[]>      // Mapa de validación de Laravel (ej: { email: ['El correo ya existe'] })
  ) {
    super(message);
    this.name = 'ApiRequestError';
  }
}

/**
 * Opciones configurables para la petición HTTP.
 */
interface Opciones {
  method?: 'GET' | 'POST' | 'PUT' | 'DELETE';     // Verbo HTTP
  body?: unknown;                                 // Carga útil enviada en la petición
  sinToken?: boolean;                             // Si es true, omite la cabecera Authorization (usado en /login)
}

/**
 * Función genérica tipada para invocar los endpoints de la API de Laravel.
 * @template T Tipo de datos esperado en la respuesta JSON
 * @param ruta Ruta relativa al endpoint (ej: '/productos', '/ventas')
 * @param opciones Configuración de método, cuerpo y autenticación
 * @returns Promesa que resuelve los datos tipados de la respuesta
 */
export async function apiFetch<T>(ruta: string, opciones: Opciones = {}): Promise<T> {
  const base = await obtenerBaseUrl();
  const esFormData = Boolean(
    opciones.body &&
    (
      (typeof FormData !== 'undefined' && opciones.body instanceof FormData) ||
      typeof (opciones.body as any).append === 'function' ||
      (opciones.body as any)._parts !== undefined
    )
  );

  // Construcción de cabeceras HTTP estándar
  const headers: Record<string, string> = {
    Accept: 'application/json',
    // Si es multipart/form-data, el navegador/React Native debe calcular el Content-Type con boundary
    ...(esFormData ? {} : { 'Content-Type': 'application/json' }),
  };

  // Inyección del token Bearer si la ruta requiere autenticación
  if (!opciones.sinToken) {
    const token = await obtenerToken();
    if (token) headers.Authorization = `Bearer ${token}`;
  }

  let respuesta: Response;
  let cuerpo: unknown;
  const controlador = new AbortController();
  const temporizador = setTimeout(() => controlador.abort(), TIEMPO_MAXIMO_PETICION_MS);

  try {
    respuesta = await fetch(`${base}/api${ruta}`, {
      method: opciones.method ?? 'GET',
      headers,
      signal: controlador.signal,
      body: opciones.body
        ? esFormData
          ? (opciones.body as any)
          : JSON.stringify(opciones.body)
        : undefined,
    });
    const esJson = respuesta.headers.get('content-type')?.includes('application/json');
    cuerpo = esJson ? await respuesta.json() : null;
  } catch (errorReal: unknown) {
    const agotado = errorReal instanceof Error && errorReal.name === 'AbortError';
    const detalle = errorReal instanceof Error ? ` (${errorReal.message})` : '';
    throw new ApiRequestError(
      agotado
        ? `El servidor (${base}) no respondió en ${TIEMPO_MAXIMO_PETICION_MS / 1000} segundos. Verifica la conexión y la dirección del servidor.`
        : `No se pudo conectar con el servidor (${base})${detalle}. Verifica que Laravel esté iniciado con --host=0.0.0.0 y en la misma red Wi-Fi.`,
      0
    );
  } finally {
    clearTimeout(temporizador);
  }

  // Verificación del código de estado HTTP
  if (!respuesta.ok) {
    const error = cuerpo as ApiError | null;

    // Si el servidor responde 401 Unauthorized, se invalida la sesión local y se notifica al contexto
    if (respuesta.status === 401) {
      await borrarToken();
      listenersSesionVencida.forEach((fn) => {
        try {
          fn();
        } catch {}
      });
    }

    // Extracción del primer mensaje de error para presentarlo al usuario
    const primerMensajeValidacion = error?.errors
      ? Object.values(error.errors)[0]?.[0]
      : undefined;

    throw new ApiRequestError(
      primerMensajeValidacion || error?.message || `Error del servidor (${respuesta.status}).`,
      respuesta.status,
      error?.errors
    );
  }

  return cuerpo as T;
}
