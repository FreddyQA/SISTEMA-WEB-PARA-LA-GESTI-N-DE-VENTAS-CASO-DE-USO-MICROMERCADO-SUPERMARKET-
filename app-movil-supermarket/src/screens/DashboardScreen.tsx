/**
 * PANTALLA PRINCIPAL: PANEL DE CONTROL (DASHBOARD) MÓVIL
 * 
 * Propósito Académico / Proyecto de Grado:
 * Actúa como el centro neurálgico de la aplicación móvil. Ofrece al usuario una vista
 * ejecutiva e inmediata del estado financiero y operativo del negocio (KPIs),
 * accesos directos a las tareas más frecuentes (vender, escanear, inventario) y un
 * feed de actividad reciente en tiempo real.
 */

import React, { useCallback, useEffect, useState } from 'react';
import { View, StyleSheet, ScrollView, RefreshControl, Pressable } from 'react-native';
import { Text, ActivityIndicator, useTheme, Surface, Avatar, Chip } from 'react-native-paper';
import { LinearGradient } from 'expo-linear-gradient';
import { MaterialCommunityIcons } from '@expo/vector-icons';
import { useFocusEffect } from '@react-navigation/native';
import type { BottomTabNavigationProp } from '@react-navigation/bottom-tabs';
import { DashboardApi } from '../api/endpoints';
import type { Dashboard, DashboardVentaReciente } from '../api/types';
import { useAuth } from '../context/AuthContext';
import { statCardGradients } from '../theme/theme';
import { haptico } from '../utils/haptico';
import { syncEvents } from '../utils/syncEvents';

/**
 * Función auxiliar para dar formato de moneda boliviana a los valores numéricos.
 * @param valor Número flotante que representa el monto en dinero
 * @returns Cadena con prefijo "Bs" y dos decimales fijos (ej: "Bs 120.50")
 */
function formatoBs(valor: number): string {
  return `Bs ${valor.toFixed(2)}`;
}

/**
 * Genera un saludo dinámico y cordial según la hora del día del dispositivo.
 * @returns Cadena con el saludo correspondiente
 */
function saludoSegunHora(): string {
  const hora = new Date().getHours();
  if (hora < 12) return 'Buenos días';
  if (hora < 19) return 'Buenas tardes';
  return 'Buenas noches';
}

/**
 * Propiedades esperadas por el componente de Tarjeta Estadística.
 */
interface TarjetaProps {
  gradiente: readonly [string, string];                  // Par de colores en hexadecimal para el degradado de fondo
  icono: keyof typeof MaterialCommunityIcons.glyphMap;   // Nombre del icono vectorial Material Community Icons
  valor: string;                                          // Valor numérico o texto principal destacado
  etiqueta: string;                                       // Descripción o subtítulo de la métrica
  onPress?: () => void;                                   // Callback opcional ejecutado al pulsar la tarjeta
}

/**
 * Componente visual de tarjeta de métrica (KPI) con gradiente, icono translúcido y elevación.
 */
function TarjetaStat({ gradiente, icono, valor, etiqueta, onPress }: TarjetaProps) {
  return (
    <Pressable
      onPress={() => {
        if (onPress) {
          haptico.toque(); // Retroalimentación háptica (vibración sutil) para mejorar la UX móvil
          onPress();
        }
      }}
      disabled={!onPress}
      style={({ pressed }) => [styles.tarjetaWrap, pressed && { opacity: 0.9, transform: [{ scale: 0.98 }] }]}
    >
      <LinearGradient colors={gradiente} start={{ x: 0, y: 0 }} end={{ x: 1, y: 1 }} style={styles.tarjeta}>
        <View style={styles.filaSuperiorTarjeta}>
          <View style={styles.tarjetaIconoWrap}>
            <MaterialCommunityIcons name={icono} size={22} color="rgba(255,255,255,0.95)" />
          </View>
          {onPress && (
            <MaterialCommunityIcons name="arrow-top-right" size={18} color="rgba(255,255,255,0.7)" />
          )}
        </View>
        <View>
          <Text style={styles.tarjetaNumero}>{valor}</Text>
          <Text style={styles.tarjetaEtiqueta} numberOfLines={2}>{etiqueta}</Text>
        </View>
      </LinearGradient>
    </Pressable>
  );
}

// Tipo de navegación para interactuar con las pestañas principales (Tabs)
type Navegacion = BottomTabNavigationProp<Record<string, object | undefined>>;

/**
 * Componente principal de la pantalla de Dashboard.
 */
export default function DashboardScreen({ navigation }: { navigation: Navegacion }) {
  const theme = useTheme();                                // Acceso a la paleta de colores activa (claro u oscuro)
  const { usuario } = useAuth();                            // Datos de la sesión autenticada
  const esAdmin = usuario?.rol === 'administrador';        // Comprobación de rol de privilegios

  // Estado local para almacenar la información retornada por la API
  const [datos, setDatos] = useState<Dashboard | null>(null);
  // Indicador de solicitud en progreso (para spinners y pull-to-refresh)
  const [cargando, setCargando] = useState(true);
  // Indicador de error de red o de comunicación
  const [error, setError] = useState(false);

  /**
   * Petición HTTP asíncrona a la API de Laravel para obtener los datos del Dashboard.
   * useCallback asegura que la referencia de la función no cambie en cada re-render.
   */
  const cargar = useCallback(async (silencioso = false) => {
    if (!silencioso) {
      setError(false);
      setCargando(true);
    }
    try {
      const respuesta = await DashboardApi.resumen();
      setDatos(respuesta);
    } catch {
      if (!silencioso) setError(true);
    } finally {
      if (!silencioso) setCargando(false);
    }
  }, []);

  /**
   * useFocusEffect garantiza que cada vez que el usuario vuelva a esta pantalla
   * (por ejemplo, después de registrar una venta), los datos se actualicen automáticamente.
   */
  useFocusEffect(
    useCallback(() => {
      cargar();
    }, [cargar])
  );

  /**
   * Sincronización en tiempo real: al registrarse una venta o modificarse productos,
   * actualiza los KPIs del dashboard automáticamente.
   */
  useEffect(() => {
    const handler = () => cargar(true);
    syncEvents.on('sync:ventas', handler);
    syncEvents.on('sync:productos', handler);
    return () => {
      syncEvents.off('sync:ventas', handler);
      syncEvents.off('sync:productos', handler);
    };
  }, [cargar]);

  /**
   * Navega a una pantalla anidada dentro del Stack "Más".
   * @param pantalla Nombre de la ruta destino
   * @param params Parámetros opcionales para la ruta
   */
  function irAMas(pantalla: string, params?: object) {
    navigation.navigate('Más', { screen: pantalla, params });
  }

  return (
    <ScrollView
      style={{ flex: 1, backgroundColor: theme.colors.background }}
      contentContainerStyle={styles.contenido}
      refreshControl={
        <RefreshControl
          refreshing={cargando}
          onRefresh={cargar}
          tintColor={theme.colors.primary}
          colors={[theme.colors.primary]}
        />
      }
    >
      {/* ── ENCABEZADO DE BIENVENIDA Y PERFIL ──────────────────────── */}
      <Surface style={[styles.headerBanner, { backgroundColor: theme.colors.surface }]} elevation={1}>
        <View style={styles.filaHeader}>
          <View style={{ flex: 1 }}>
            <Text variant="labelMedium" style={{ color: theme.colors.primary, fontWeight: '700', letterSpacing: 0.5 }}>
              {saludoSegunHora().toUpperCase()}
            </Text>
            <Text variant="headlineSmall" style={{ fontWeight: '800', color: theme.colors.onSurface }}>
              {usuario?.nombre ?? 'Usuario'}
            </Text>
            <View style={styles.filaRol}>
              <Chip
                compact
                style={{
                  backgroundColor: esAdmin ? '#EEF2FF' : '#F0FDF4',
                  alignSelf: 'flex-start',
                  marginTop: 4,
                }}
                textStyle={{
                  color: esAdmin ? '#4F46E5' : '#15803D',
                  fontWeight: '700',
                  fontSize: 11,
                }}
              >
                {esAdmin ? '● Administrador' : '● Operador'}
              </Chip>
            </View>
          </View>

          {usuario?.foto_url ? (
            <Avatar.Image source={{ uri: usuario.foto_url }} size={48} style={{ backgroundColor: 'transparent' }} />
          ) : (
            <Avatar.Text
              label={(usuario?.nombre ?? 'S').charAt(0).toUpperCase()}
              size={48}
              style={{ backgroundColor: theme.colors.primary }}
              color="#FFFFFF"
            />
          )}
        </View>
      </Surface>

      {/* ── SPINNER DE CARGA INICIAL ───────────────────────────────── */}
      {!datos && cargando && (
        <View style={styles.centroCarga}>
          <ActivityIndicator size="large" color={theme.colors.primary} />
          <Text style={{ marginTop: 12, color: theme.colors.onSurfaceVariant }}>Cargando métricas...</Text>
        </View>
      )}

      {/* ── MENSAJE DE ERROR DE CONEXIÓN ───────────────────────────── */}
      {error && (
        <Surface style={[styles.tarjetaError, { backgroundColor: theme.colors.errorContainer ?? '#FEE2E2' }]} elevation={0}>
          <MaterialCommunityIcons name="cloud-off-outline" size={28} color={theme.colors.error} />
          <Text style={{ color: theme.colors.error, marginLeft: 12, flex: 1, fontWeight: '500' }}>
            No se pudo sincronizar la información con el servidor. Revisa tu conexión Wi-Fi.
          </Text>
        </Surface>
      )}

      {/* ── CUADRÍCULA DE MÉTRICAS (KPIS DEL SUPERMERCADO) ─────────── */}
      {datos && (
        <>
          <View style={styles.filaTituloConBoton}>
            <Text variant="titleMedium" style={[styles.seccionTitulo, { color: theme.colors.onSurface, marginBottom: 0 }]}>
              Resumen Operativo
            </Text>
            <Text variant="labelSmall" style={{ color: theme.colors.onSurfaceVariant }}>
              Actualizado en tiempo real
            </Text>
          </View>

          <View style={styles.grid}>
            {/* KPI 1: Ventas del día */}
            <TarjetaStat
              gradiente={statCardGradients.indigo}
              icono="cash-register"
              valor={formatoBs(datos.ventas_hoy)}
              etiqueta={`Ventas de hoy · ${datos.transacciones_hoy} transacc.`}
              onPress={() => irAMas('Historial')}
            />

            {/* KPI 2: Ventas acumuladas del mes */}
            <TarjetaStat
              gradiente={statCardGradients.emerald}
              icono="chart-line-variant"
              valor={formatoBs(datos.ventas_mes)}
              etiqueta="Ventas del mes actual"
              onPress={esAdmin ? () => irAMas('Reportes') : undefined}
            />

            {/* KPI 3: Catálogo y Stock Bajo */}
            <TarjetaStat
              gradiente={statCardGradients.amber}
              icono="package-variant-closed"
              valor={String(datos.total_productos)}
              etiqueta={`Productos · ${datos.stock_bajo} en crítico`}
              onPress={() => navigation.navigate('Productos')}
            />

            {/* KPI 4: Clientes Registrados */}
            <TarjetaStat
              gradiente={statCardGradients.rose}
              icono="account-group"
              valor={String(datos.total_clientes)}
              etiqueta="Clientes en cartera"
              onPress={() => irAMas('Clientes')}
            />
          </View>
        </>
      )}

      {/* ── ALERTA DE STOCK BAJO (SI CORRESPONDE) ───────────────────── */}
      {datos && datos.stock_bajo > 0 && (
        <Pressable
          onPress={() => {
            haptico.toque();
            navigation.navigate('Productos');
          }}
          style={({ pressed }) => [pressed && { opacity: 0.9 }]}
        >
          <Surface style={[styles.avisoStock, { backgroundColor: '#FEF3C7', borderColor: '#F59E0B' }]} elevation={1}>
            <View style={styles.avisoIconoWrap}>
              <MaterialCommunityIcons name="alert-octagon" size={22} color="#B45309" />
            </View>
            <View style={{ flex: 1, marginLeft: 10 }}>
              <Text style={{ color: '#92400E', fontWeight: '700', fontSize: 13 }}>
                Atención: {datos.stock_bajo} producto(s) con stock crítico
              </Text>
              <Text style={{ color: '#B45309', fontSize: 11, marginTop: 2 }}>
                Toca aquí para revisar y gestionar el reabastecimiento.
              </Text>
            </View>
            <MaterialCommunityIcons name="chevron-right" size={20} color="#92400E" />
          </Surface>
        </Pressable>
      )}

      {/* ── ACTIVIDAD RECIENTE: ÚLTIMAS VENTAS ──────────────────────── */}
      {datos?.ultimas_ventas && datos.ultimas_ventas.length > 0 && (
        <View style={styles.seccionRecientes}>
          <View style={styles.filaTituloConBoton}>
            <Text variant="titleMedium" style={[styles.seccionTitulo, { color: theme.colors.onSurface, marginBottom: 0 }]}>
              Últimas Ventas
            </Text>
            <Pressable onPress={() => irAMas('Historial')}>
              <Text variant="labelMedium" style={{ color: theme.colors.primary, fontWeight: '700' }}>
                Ver todas →
              </Text>
            </Pressable>
          </View>

          <Surface style={[styles.tarjetaRecientes, { backgroundColor: theme.colors.surface }]} elevation={1}>
            {datos.ultimas_ventas.map((venta: DashboardVentaReciente, idx: number) => {
              const esCompletado = venta.estado === 'completado';
              return (
                <Pressable
                  key={venta.id}
                  onPress={() => irAMas('VentaDetalle', { id: venta.id })}
                  style={({ pressed }) => [
                    styles.filaVenta,
                    idx < datos.ultimas_ventas!.length - 1 && styles.bordeInferiorVenta,
                    pressed && { backgroundColor: theme.colors.surfaceVariant, opacity: 0.8 },
                  ]}
                >
                  <View style={[styles.ventaIconoWrap, { backgroundColor: esCompletado ? '#DCFCE7' : '#FEE2E2' }]}>
                    <MaterialCommunityIcons
                      name={esCompletado ? 'check-circle-outline' : 'close-circle-outline'}
                      size={20}
                      color={esCompletado ? '#15803D' : '#DC2626'}
                    />
                  </View>

                  <View style={{ flex: 1, marginLeft: 12 }}>
                    <Text variant="titleSmall" style={{ fontWeight: '700', color: theme.colors.onSurface }}>
                      Venta #{venta.numero ?? venta.id}
                    </Text>
                    <Text variant="bodySmall" style={{ color: theme.colors.onSurfaceVariant }} numberOfLines={1}>
                      {venta.cliente?.nombre ?? 'Cliente casual'} · {venta.fecha}
                    </Text>
                  </View>

                  <View style={{ alignItems: 'flex-end', flexShrink: 0 }}>
                    <Text variant="titleSmall" style={{ fontWeight: '800', color: theme.colors.primary }}>
                      {formatoBs(venta.total)}
                    </Text>
                    <View
                      style={[
                        styles.badgeReciente,
                        {
                          backgroundColor: esCompletado ? '#ECFDF5' : '#FEF2F2',
                          borderColor: esCompletado ? '#10B981' : '#EF4444',
                        },
                      ]}
                    >
                      <Text
                        style={[
                          styles.textoBadgeReciente,
                          { color: esCompletado ? '#047857' : '#B91C1C' },
                        ]}
                      >
                        {esCompletado ? 'Completado' : 'Anulado'}
                      </Text>
                    </View>
                  </View>
                </Pressable>
              );
            })}
          </Surface>
        </View>
      )}
    </ScrollView>
  );
}

/**
 * Hoja de estilos con diseño adaptativo, bordes suaves y sombras elegantes.
 */
const styles = StyleSheet.create({
  contenido: {
    padding: 18,
    paddingBottom: 40,
  },
  headerBanner: {
    padding: 16,
    borderRadius: 18,
    marginBottom: 20,
  },
  filaHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
  },
  filaRol: {
    flexDirection: 'row',
    marginTop: 2,
  },
  seccionTitulo: {
    fontWeight: '800',
    marginBottom: 12,
  },
  filaTituloConBoton: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginTop: 18,
    marginBottom: 12,
  },
  scrollAcciones: {
    paddingVertical: 4,
    paddingHorizontal: 2,
    gap: 12,
  },
  accionCard: {
    alignItems: 'center',
    paddingVertical: 14,
    paddingHorizontal: 12,
    borderRadius: 20,
    borderWidth: 1,
    minWidth: 94,
    maxWidth: 104,
    shadowColor: '#4F46E5',
    shadowOffset: { width: 0, height: 3 },
    shadowOpacity: 0.06,
    shadowRadius: 8,
    elevation: 2,
  },
  accionIconoWrap: {
    width: 52,
    height: 52,
    borderRadius: 16,
    borderWidth: 1,
    alignItems: 'center',
    justifyContent: 'center',
  },
  centroCarga: {
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 40,
  },
  tarjetaError: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: 14,
    borderRadius: 14,
    marginVertical: 14,
  },
  grid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 12,
    justifyContent: 'space-between',
  },
  tarjetaWrap: {
    width: '48%',
  },
  tarjeta: {
    borderRadius: 18,
    padding: 16,
    minHeight: 120,
    justifyContent: 'space-between',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.12,
    shadowRadius: 8,
    elevation: 3,
  },
  filaSuperiorTarjeta: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  tarjetaIconoWrap: {
    width: 36,
    height: 36,
    borderRadius: 12,
    backgroundColor: 'rgba(255,255,255,0.22)',
    alignItems: 'center',
    justifyContent: 'center',
  },
  tarjetaNumero: {
    color: '#FFFFFF',
    fontSize: 20,
    fontWeight: '900',
    letterSpacing: -0.5,
  },
  tarjetaEtiqueta: {
    color: 'rgba(255,255,255,0.9)',
    fontSize: 12,
    fontWeight: '500',
    marginTop: 2,
  },
  avisoStock: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: 14,
    borderRadius: 14,
    borderWidth: 1,
    marginTop: 18,
  },
  avisoIconoWrap: {
    width: 34,
    height: 34,
    borderRadius: 10,
    backgroundColor: '#FDE68A',
    alignItems: 'center',
    justifyContent: 'center',
  },
  seccionRecientes: {
    marginTop: 10,
  },
  tarjetaRecientes: {
    borderRadius: 18,
    overflow: 'hidden',
  },
  filaVenta: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: 14,
    paddingHorizontal: 16,
  },
  bordeInferiorVenta: {
    borderBottomWidth: StyleSheet.hairlineWidth,
    borderBottomColor: 'rgba(100,116,139,0.15)',
  },
  ventaIconoWrap: {
    width: 38,
    height: 38,
    borderRadius: 12,
    alignItems: 'center',
    justifyContent: 'center',
  },
  badgeReciente: {
    paddingHorizontal: 7,
    paddingVertical: 2,
    borderRadius: 6,
    borderWidth: 1,
    marginTop: 3,
    flexShrink: 0,
  },
  textoBadgeReciente: {
    fontSize: 10,
    fontWeight: '800',
    letterSpacing: 0.2,
    includeFontPadding: false,
  },
});
