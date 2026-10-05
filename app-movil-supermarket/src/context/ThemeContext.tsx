/**
 * CONTEXTO DE TEMA Y APARIENCIA VISUAL (ThemeContext)
 * 
 * Propósito Académico / Proyecto de Grado:
 * Gestiona el sistema de temas dinámico (Modo Claro / Modo Oscuro / Modo Sistema)
 * de la aplicación móvil, garantizando paridad visual idéntica con el panel web
 * de Laravel (que utiliza variables CSS y almacenamiento en localStorage).
 * 
 * Características técnicas:
 * 1. Persistencia: Guarda la elección del usuario en AsyncStorage bajo la clave `@supermarket/tema`.
 * 2. Detección automática: Si la preferencia es 'sistema', responde al sensor y configuración
 *    global del sistema operativo mediante el hook nativo `useColorScheme()`.
 * 3. Integración con React Native Paper: Provee los tokens de diseño (colores primarios,
 *    superficies, bordes y tipografías) adaptados a Material Design 3.
 */

import React, { createContext, useContext, useEffect, useState, useCallback } from 'react';
import { useColorScheme } from 'react-native';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { lightTheme, darkTheme } from '../theme/theme';

// Tipo unión que restringe las tres opciones válidas de visualización
type Preferencia = 'claro' | 'oscuro' | 'sistema';

// Clave utilizada para persistir la preferencia en el almacenamiento del dispositivo
const CLAVE_ALMACENAMIENTO = '@supermarket/tema';

/**
 * Interfaz que define las propiedades y funciones del contexto de tema.
 */
interface ThemeContextValue {
  esOscuro: boolean;                                             // Verdadero si el tema actual resultante es oscuro
  preferencia: Preferencia;                                      // Configuración elegida por el usuario ('claro' | 'oscuro' | 'sistema')
  establecerPreferencia: (p: Preferencia) => void;               // Función para actualizar y guardar la preferencia
  tema: typeof lightTheme | typeof darkTheme;                    // Objeto de tokens de tema para React Native Paper
}

const ThemeContext = createContext<ThemeContextValue | undefined>(undefined);

/**
 * Proveedor de tema que envuelve la aplicación móvil.
 */
export function AppThemeProvider({ children }: { children: React.ReactNode }) {
  // Hook de React Native que detecta si el SO del teléfono está en modo noche o día
  const esquemaDispositivo = useColorScheme();
  
  // Preferencia seleccionada por el usuario (por defecto 'sistema')
  const [preferencia, setPreferencia] = useState<Preferencia>('sistema');
  
  // Indicador de lectura completada de AsyncStorage para evitar parpadeos visuales (FOUC)
  const [listo, setListo] = useState(false);

  /**
   * Carga la preferencia previamente guardada al iniciar la aplicación.
   */
  useEffect(() => {
    AsyncStorage.getItem(CLAVE_ALMACENAMIENTO).then((guardada) => {
      if (guardada === 'claro' || guardada === 'oscuro' || guardada === 'sistema') {
        setPreferencia(guardada);
      }
      setListo(true);
    });
  }, []);

  /**
   * Cambia la preferencia del usuario y la persiste de forma asíncrona.
   */
  const establecerPreferencia = useCallback((p: Preferencia) => {
    setPreferencia(p);
    AsyncStorage.setItem(CLAVE_ALMACENAMIENTO, p);
  }, []);

  /**
   * Lógica de resolución:
   * Si es 'oscuro' -> Activo
   * Si es 'sistema' -> Activo solo si el teléfono tiene el modo noche activado
   */
  const esOscuro = preferencia === 'oscuro' || (preferencia === 'sistema' && esquemaDispositivo === 'dark');

  // Espera a que AsyncStorage lea la preferencia antes de renderizar la UI
  if (!listo) return null;

  return (
    <ThemeContext.Provider
      value={{
        esOscuro,
        preferencia,
        establecerPreferencia,
        tema: esOscuro ? darkTheme : lightTheme,
      }}
    >
      {children}
    </ThemeContext.Provider>
  );
}

/**
 * Hook para consumir el contexto de tema en cualquier pantalla o componente.
 */
export function useAppTheme(): ThemeContextValue {
  const ctx = useContext(ThemeContext);
  if (!ctx) throw new Error('useAppTheme debe invocarse dentro de un componente envuelto por <AppThemeProvider>');
  return ctx;
}
