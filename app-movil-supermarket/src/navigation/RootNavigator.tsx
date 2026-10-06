/**
 * NAVEGADOR PRINCIPAL DE LA APLICACIÓN (RootNavigator)
 * 
 * Propósito Académico / Proyecto de Grado:
 * Define el enrutamiento y la arquitectura de pantallas de la app mediante React Navigation.
 * 
 * Patrones de Diseño de Navegación Aplicados:
 * 1. Flujo de Autenticación Condicional: Si no existe un usuario autenticado en `AuthContext`,
 *    el navegador renderiza exclusivamente la pantalla de `LoginScreen`. Al autenticarse,
 *    la pantalla conmuta atómicamente a `TabsPrincipales` sin dejar historial retrocedible.
 * 2. Navegación por Pestañas Inferiores (Bottom Tabs): Provee acceso con un solo pulgar
 *    a las 5 funciones clave: Inicio, Productos, Escanear, Vender y Más.
 * 3. Stack Navigator Anidado (`MasStack`): Encapsula las pantallas secundarias dentro de
 *    la pestaña "Más" (Categorías, Clientes, Reportes e historial de ventas),
 *    manteniendo la barra de pestañas coherente y soportando transiciones nativas de tarjeta.
 * 4. Tipado Estricto de Rutas (Type-Safe Navigation): Define `MasStackParamList` para
 *    evitar errores de nombres de ruta o parámetros faltantes en tiempo de desarrollo.
 */

import React from 'react';
import { NavigationContainer, DefaultTheme, DarkTheme } from '@react-navigation/native';
import { createBottomTabNavigator } from '@react-navigation/bottom-tabs';
import { createNativeStackNavigator } from '@react-navigation/native-stack';
import { ActivityIndicator, View } from 'react-native';
import { MaterialCommunityIcons } from '@expo/vector-icons';
import { useAuth } from '../context/AuthContext';
import { useAppTheme } from '../context/ThemeContext';
import LoginScreen from '../screens/LoginScreen';
import DashboardScreen from '../screens/DashboardScreen';
import ProductosScreen from '../screens/ProductosScreen';
import EscanerScreen from '../screens/EscanerScreen';
import NuevaVentaScreen from '../screens/NuevaVentaScreen';
import HistorialScreen from '../screens/HistorialScreen';
import MasScreen from '../screens/MasScreen';
import CategoriasScreen from '../screens/CategoriasScreen';
import ReportesScreen from '../screens/ReportesScreen';
import UsuariosScreen from '../screens/UsuariosScreen';
import ClientesScreen from '../screens/ClientesScreen';
import VentaDetalleScreen from '../screens/VentaDetalleScreen';

// Instanciación del navegador de pestañas inferiores
const Tab = createBottomTabNavigator();

/**
 * Diccionario de parámetros tipados para el Stack secundario "Más".
 */
export type MasStackParamList = {
  MasInicio: undefined;                  // Menú raíz de la sección Más
  Categorias: undefined;                 // Módulo CRUD de Categorías
  Clientes: undefined;                   // Módulo CRUD de Clientes
  Historial: undefined;                  // Bitácora histórica de ventas
  Reportes: undefined;                   // Módulo analítico y financiero
  Usuarios: undefined;                   // Administración de cuentas y roles
  VentaDetalle: { id: number };         // Pantalla de detalle que exige el ID de la venta
};

// Instanciación del stack con tipado estricto
const MasStack = createNativeStackNavigator<MasStackParamList>();

type IconName = keyof typeof MaterialCommunityIcons.glyphMap;

/**
 * Resuelve el icono Material Community Icons correspondiente según la ruta y estado activo.
 * @param ruta Nombre de la pestaña actual
 * @param enfocado Booleano que indica si la pestaña está actualmente seleccionada
 */
function iconoPara(ruta: string, enfocado: boolean): IconName {
  switch (ruta) {
    case 'Inicio':
      return enfocado ? 'view-dashboard' : 'view-dashboard-outline';
    case 'Productos':
      return enfocado ? 'package-variant' : 'package-variant-closed';
    case 'Escanear':
      return 'barcode-scan';
    case 'Vender':
      return enfocado ? 'cart' : 'cart-outline';
    case 'Más':
      return 'menu';
    default:
      return 'help-circle-outline';
  }
}

/**
 * Stack anidado para las pantallas secundarias de la pestaña "Más".
 */
/**
 * Stack anidado para las pantallas secundarias de la pestaña "Más".
 */
function MasNavigator() {
  const { tema } = useAppTheme();
  return (
    <MasStack.Navigator
      screenOptions={{
        headerTitleAlign: 'left',
        headerStyle: { backgroundColor: tema.colors.surface },
        headerTintColor: tema.colors.onSurface,
        contentStyle: { backgroundColor: tema.colors.background },
      }}
    >
      <MasStack.Screen name="MasInicio" component={MasScreen} options={{ title: 'Más Opciones' }} />
      <MasStack.Screen name="Categorias" component={CategoriasScreen} options={{ title: 'Categorías' }} />
      <MasStack.Screen name="Historial" component={HistorialScreen} options={{ title: 'Historial de Ventas' }} />
      <MasStack.Screen name="Reportes" component={ReportesScreen} options={{ title: 'Reportes de Ventas' }} />
      <MasStack.Screen name="Clientes" component={ClientesScreen} options={{ title: 'Clientes' }} />
      <MasStack.Screen name="Usuarios" component={UsuariosScreen} options={{ title: 'Usuarios del Sistema' }} />
      <MasStack.Screen name="VentaDetalle" component={VentaDetalleScreen} options={{ title: 'Comprobante de Venta' }} />
    </MasStack.Navigator>
  );
}

/**
 * Barra de pestañas inferiores principales (Tabs).
 */
function TabsPrincipales() {
  const { tema } = useAppTheme();
  return (
    <Tab.Navigator
      screenOptions={({ route }) => ({
        headerTitleAlign: 'left',
        headerStyle: { backgroundColor: tema.colors.surface },
        headerTintColor: tema.colors.onSurface,
        tabBarActiveTintColor: '#4F46E5',
        tabBarInactiveTintColor: '#64748B',
        tabBarStyle: {
          height: 60,
          paddingBottom: 8,
          paddingTop: 6,
          backgroundColor: tema.colors.surface,
          borderTopColor: tema.colors.outlineVariant,
        },
        tabBarLabelStyle: {
          fontSize: 11,
          fontWeight: '600',
        },
        tabBarIcon: ({ focused, color, size }) => (
          <MaterialCommunityIcons name={iconoPara(route.name, focused)} size={size} color={color} />
        ),
      })}
    >
      <Tab.Screen name="Inicio" component={DashboardScreen} options={{ title: 'Supermarket' }} />
      <Tab.Screen name="Productos" component={ProductosScreen} options={{ title: 'Inventario' }} />
      <Tab.Screen name="Escanear" component={EscanerScreen} options={{ headerShown: false, title: 'Escanear' }} />
      <Tab.Screen name="Vender" component={NuevaVentaScreen} options={{ title: 'Nueva Venta' }} />
      <Tab.Screen name="Más" component={MasNavigator} options={{ headerShown: false, title: 'Más' }} />
    </Tab.Navigator>
  );
}

/**
 * Contenedor raíz de navegación con inyección de tema global.
 */
export default function RootNavigator() {
  const { usuario, cargando } = useAuth();
  const { esOscuro, tema } = useAppTheme();

  // Sincronización del tema de navegación con los colores de Material Design
  const navTheme = {
    ...(esOscuro ? DarkTheme : DefaultTheme),
    colors: {
      ...(esOscuro ? DarkTheme.colors : DefaultTheme.colors),
      primary: tema.colors.primary,
      background: tema.colors.background,
      card: tema.colors.surface,
      text: tema.colors.onSurface,
      border: tema.colors.outline,
    },
  };

  // Espera la verificación de sesión sin mostrar la pantalla splash de marca.
  if (cargando) {
    return (
      <View style={{ flex: 1, alignItems: 'center', justifyContent: 'center', backgroundColor: tema.colors.background }}>
        <ActivityIndicator color={tema.colors.primary} />
      </View>
    );
  }

  return (
    <NavigationContainer theme={navTheme}>
      {/* Si hay usuario autenticado entra a los Tabs, de lo contrario muestra el Login */}
      {usuario ? <TabsPrincipales /> : <LoginScreen />}
    </NavigationContainer>
  );
}
