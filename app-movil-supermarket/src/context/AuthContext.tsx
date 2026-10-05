/**
 * CONTEXTO DE AUTENTICACIÓN GLOBAL (AuthContext)
 * 
 * Propósito Académico / Proyecto de Grado:
 * Implementa el patrón Provider/Consumer de React para compartir el estado de la sesión
 * del usuario en todo el árbol de componentes sin necesidad de "prop drilling" (pasar
 * props manualmente por múltiples niveles de componentes).
 * 
 * Flujo de Seguridad para el Jurado:
 * 1. Persistencia segura: El token Bearer se almacena en el almacenamiento seguro del móvil
 *    (Expo SecureStore en producción / AsyncStorage).
 * 2. Autenticación en el arranque: Al iniciar la aplicación, se recupera el token y se
 *    valida contra el endpoint `/api/me`. Si el token expiró o fue revocado en Laravel,
 *    se purga automáticamente y se redirige a la pantalla de Login.
 * 3. Cierre de sesión atómico: Notifica al backend (`/api/logout`) para invalidar el registro
 *    en la tabla `api_tokens` y purga las credenciales locales.
 */

import React, { createContext, useContext, useEffect, useState, useCallback } from 'react';
import { AuthApi } from '../api/endpoints';
import { ApiRequestError } from '../api/client';
import { guardarToken, borrarToken, obtenerToken, alVencerSesion } from '../api/client';
import type { Usuario } from '../api/types';

/**
 * Contrato de valores y funciones que expone el contexto de autenticación.
 */
interface AuthContextValue {
  usuario: Usuario | null;                                             // Datos del usuario con sesión activa (null si no está autenticado)
  cargando: boolean;                                                   // Bandera de verificación inicial del token en progreso
  errorInicial: string | null;                                         // Error al restaurar sesión por conexión u otro fallo del servidor
  iniciarSesion: (email: string, password: string) => Promise<void>;    // Función para autenticar con correo y clave
  cerrarSesion: () => Promise<void>;                                   // Función para destruir la sesión activa
}

// Creación del objeto Context con valor inicial indefinido
const AuthContext = createContext<AuthContextValue | undefined>(undefined);

/**
 * Proveedor de Autenticación que envuelve la jerarquía de la app.
 */
export function AuthProvider({ children }: { children: React.ReactNode }) {
  // Estado local del usuario logueado
  const [usuario, setUsuario] = useState<Usuario | null>(null);
  // Estado de comprobación de sesión al arrancar la app
  const [cargando, setCargando] = useState(true);
  const [errorInicial, setErrorInicial] = useState<string | null>(null);

  /**
   * Suscripción a eventos de sesión vencida (401 Unauthorized desde cualquier petición API):
   * Invalida el estado del usuario inmediatamente para que la navegación redirija a LoginScreen.
   */
  useEffect(() => {
    const desuscribir = alVencerSesion(() => {
      setUsuario(null);
    });
    return desuscribir;
  }, []);

  /**
   * Efecto de inicialización: Se ejecuta una única vez al montar el componente.
   * Verifica si existe un token guardado previamente en el dispositivo móvil.
   */
  useEffect(() => {
    (async () => {
      try {
        const token = await obtenerToken();
        if (token) {
          // Valida el token contra el backend solicitando los datos del usuario (/api/me)
          const { data } = await AuthApi.yo();
          setUsuario(data);
        }
      } catch (error) {
        // apiFetch elimina tokens rechazados (401); los errores de red conservan la sesión
        // para permitir corregir la conexión y volver a iniciar sesión sin bloquear la app.
        setUsuario(null);
        if (error instanceof ApiRequestError && error.status !== 401) {
          setErrorInicial(error.message);
        } else if (!(error instanceof ApiRequestError)) {
          setErrorInicial('No se pudo restaurar la sesión. Verifica la conexión del dispositivo e inténtalo de nuevo.');
        }
      } finally {
        setCargando(false);
      }
    })();
  }, []);

  /**
   * Ejecuta el inicio de sesión contra el endpoint /api/login.
   * Guarda el token recibido y almacena los datos del usuario en memoria.
   */
  const iniciarSesion = useCallback(async (email: string, password: string) => {
    const { token, usuario: datosUsuario } = await AuthApi.login(email, password);
    await guardarToken(token);
    setUsuario(datosUsuario);
  }, []);

  /**
   * Cierra la sesión activa:
   * 1. Solicita a Laravel revocar el token en la base de datos.
   * 2. Elimina el token del dispositivo móvil.
   * 3. Resetea el estado del usuario a null (disparando la navegación al Login).
   */
  const cerrarSesion = useCallback(async () => {
    try {
      await AuthApi.logout();
    } catch (e) {
      // Si el token ya había expirado (código HTTP 401), se ignora el error del servidor
      if (!(e instanceof ApiRequestError && e.status === 401)) throw e;
    } finally {
      await borrarToken();
      setUsuario(null);
    }
  }, []);

  return (
    <AuthContext.Provider value={{ usuario, cargando, errorInicial, iniciarSesion, cerrarSesion }}>
      {children}
    </AuthContext.Provider>
  );
}

/**
 * Hook personalizado para consumir el contexto de autenticación de forma segura y tipada.
 * Lanza un error explícito si se invoca fuera del árbol de un <AuthProvider>.
 */
export function useAuth(): AuthContextValue {
  const ctx = useContext(AuthContext);
  if (!ctx) throw new Error('useAuth debe usarse dentro de un componente envuelto por <AuthProvider>');
  return ctx;
}
