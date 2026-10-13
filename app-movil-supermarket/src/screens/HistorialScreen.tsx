/**
 * PANTALLA DE HISTORIAL Y CONSULTA DE VENTAS
 * 
 * Propósito Académico / Proyecto de Grado:
 * Ofrece la bitácora histórica de todas las transacciones de venta registradas
 * en el supermercado. Permite auditar qué vendedor realizó la operación, a qué
 * cliente se le facturó, el estado actual de la venta (Completado o Anulado) y
 * acceder al desglose pormenorizado de cada venta.
 */

import React, { useCallback, useEffect, useMemo, useState } from 'react';
import { View, StyleSheet, FlatList, RefreshControl, Pressable } from 'react-native';
import { Text, ActivityIndicator, useTheme, Surface, Searchbar } from 'react-native-paper';
import { MaterialCommunityIcons } from '@expo/vector-icons';
import { useFocusEffect } from '@react-navigation/native';
import type { NativeStackScreenProps } from '@react-navigation/native-stack';
import { VentasApi } from '../api/endpoints';
import { usePaginado } from '../hooks/usePaginado';
import { EstadoVacio } from '../components/EstadoVacio';
import { SkeletonLista } from '../components/SkeletonLista';
import { haptico } from '../utils/haptico';
import { syncEvents } from '../utils/syncEvents';
import type { Venta } from '../api/types';
import type { MasStackParamList } from '../navigation/RootNavigator';

type Props = NativeStackScreenProps<MasStackParamList, 'Historial'>;

/**
 * Adaptador para la llamada paginada a la API de ventas.
 */
const fetchVentas = (pagina: number) => VentasApi.listar(undefined, pagina);

export default function HistorialScreen({ navigation }: Props) {
  const theme = useTheme();                                // Paleta del sistema activo
  const { items, cargando, cargandoMas, error, recargar, cargarMas } = usePaginado<Venta>(fetchVentas);

  // Filtro de búsqueda local por folio o nombre de cliente
  const [filtro, setFiltro] = useState('');

  /**
   * Refresca la lista de ventas al abrir o volver a la pantalla.
   */
  useFocusEffect(
    useCallback(() => {
      recargar();
    }, [recargar])
  );

  /**
   * Sincronización en tiempo real: al registrarse o anularse una venta, actualiza el historial.
   */
  useEffect(() => {
    const handler = () => recargar(undefined, true);
    syncEvents.on('sync:ventas', handler);
    return () => syncEvents.off('sync:ventas', handler);
  }, [recargar]);

  /**
   * Filtra las ventas en memoria por ID o nombre del cliente.
   */
  const ventasFiltrados = useMemo(() => {
    if (!filtro.trim()) return items;
    const termino = filtro.toLowerCase().trim();
    return (items || []).filter((ped) => {
      const idCoincide = String(ped.numero ?? ped.id).includes(termino) || String(ped.id).includes(termino);
      const clienteCoincide = ped.cliente?.nombre
        ? ped.cliente.nombre.toLowerCase().includes(termino)
        : false;
      return idCoincide || clienteCoincide;
    });
  }, [items, filtro]);

  return (
    <View style={[styles.contenedor, { backgroundColor: theme.colors.background }]}>
      {/* ── BARRA DE BÚSQUEDA DE HISTORIAL ─────────────────────────── */}
      <Searchbar
        placeholder="Buscar por # venta o cliente..."
        value={filtro}
        onChangeText={setFiltro}
        style={[styles.buscador, { backgroundColor: theme.colors.surface }]}
        inputStyle={{ fontSize: 14 }}
      />

      {/* ── ESTADO DE CARGA CON SKELETON ───────────────────────────── */}
      {cargando && items.length === 0 && <SkeletonLista />}

      {/* ── ESTADO DE ERROR DE COMUNICACIÓN ────────────────────────── */}
      {error && items.length === 0 && (
        <EstadoVacio
          esError
          icono="wifi-off"
          titulo="Error al cargar historial"
          mensaje={error}
          textoAccion="Reintentar"
          onAccion={() => recargar()}
        />
      )}

      {/* ── LISTADO DE TRANSACCIONES HISTÓRICAS ────────────────────── */}
      <FlatList
        data={ventasFiltrados}
        keyExtractor={(p) => String(p.id)}
        contentContainerStyle={styles.lista}
        refreshControl={
          <RefreshControl
            refreshing={cargando && items.length > 0}
            onRefresh={() => recargar()}
            tintColor={theme.colors.primary}
            colors={[theme.colors.primary]}
          />
        }
        onEndReached={cargarMas}
        onEndReachedThreshold={0.4}
        ListFooterComponent={cargandoMas ? <ActivityIndicator style={{ marginVertical: 16 }} /> : null}
        ListEmptyComponent={
          !cargando && !error ? (
            <EstadoVacio
              icono="receipt-text-outline"
              titulo={filtro ? 'Sin ventas coincidentes' : 'Aún no hay ventas registradas'}
              mensaje={
                filtro
                  ? `No se encontró ninguna venta con el criterio "${filtro}".`
                  : 'Las ventas realizadas desde la app o la web aparecerán aquí.'
              }
            />
          ) : null
        }
        renderItem={({ item }) => {
          const esCompletado = item.estado === 'completado';

          return (
            <Surface style={[styles.tarjeta, { backgroundColor: theme.colors.surface }]} elevation={1}>
              <Pressable
                onPress={() => {
                  haptico.toque();
                  navigation.navigate('VentaDetalle', { id: item.id });
                }}
                style={styles.contenidoTarjeta}
              >
                {/* Cabecera de la tarjeta: Folio y Badge de estado */}
                <View style={styles.filaCabecera}>
                  <View style={styles.filaFolio}>
                    <View style={[styles.iconoFolio, { backgroundColor: esCompletado ? '#DCFCE7' : '#FEE2E2' }]}>
                      <MaterialCommunityIcons
                        name={esCompletado ? 'check' : 'close'}
                        size={16}
                        color={esCompletado ? '#15803D' : '#DC2626'}
                      />
                    </View>
                    <Text variant="titleMedium" style={{ fontWeight: '800', marginLeft: 8 }}>
                      Venta #{item.numero ?? item.id}
                    </Text>
                  </View>

                  {/* Badge de Estado: Prominente, de Alto Contraste y Sin Recortes */}
                  <View
                    style={[
                      styles.badgeEstado,
                      {
                        backgroundColor: esCompletado ? '#ECFDF5' : '#FEF2F2',
                        borderColor: esCompletado ? '#10B981' : '#EF4444',
                      },
                    ]}
                  >
                    <MaterialCommunityIcons
                      name={esCompletado ? 'check-circle' : 'close-circle'}
                      size={14}
                      color={esCompletado ? '#059669' : '#DC2626'}
                      style={{ marginRight: 5 }}
                    />
                    <Text
                      style={[
                        styles.textoBadgeEstado,
                        { color: esCompletado ? '#047857' : '#B91C1C' },
                      ]}
                    >
                      {esCompletado ? 'Completado' : 'Anulado'}
                    </Text>
                  </View>
                </View>

                {/* Datos del cliente y fecha */}
                <View style={styles.filaDatos}>
                  <MaterialCommunityIcons name="account-outline" size={16} color={theme.colors.onSurfaceVariant} />
                  <Text variant="bodyMedium" style={{ marginLeft: 6, color: theme.colors.onSurface, fontWeight: '600' }}>
                    {item.cliente?.nombre ?? 'Cliente casual'}
                  </Text>
                  <Text variant="bodySmall" style={{ color: theme.colors.onSurfaceVariant, marginLeft: 'auto' }}>
                    {item.fecha}
                  </Text>
                </View>

                {/* Vendedor y Monto Total */}
                <View style={styles.filaInferior}>
                  <Text variant="bodySmall" style={{ color: theme.colors.onSurfaceVariant }}>
                    {item.vendedor ? `Atendido por: ${item.vendedor.nombre}` : 'Venta directa'}
                  </Text>

                  <Text variant="titleLarge" style={[styles.totalTexto, { color: theme.colors.primary }]}>
                    Bs {Number(item.total ?? 0).toFixed(2)}
                  </Text>
                </View>
              </Pressable>
            </Surface>
          );
        }}
      />
    </View>
  );
}

/**
 * Hoja de estilos del historial de ventas.
 */
const styles = StyleSheet.create({
  contenedor: {
    flex: 1,
  },
  buscador: {
    marginHorizontal: 16,
    marginTop: 12,
    marginBottom: 8,
    borderRadius: 14,
    elevation: 1,
  },
  lista: {
    padding: 16,
    paddingTop: 6,
    paddingBottom: 40,
  },
  tarjeta: {
    marginBottom: 12,
    borderRadius: 16,
    overflow: 'hidden',
  },
  contenidoTarjeta: {
    padding: 14,
  },
  filaCabecera: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 8,
  },
  filaFolio: {
    flexDirection: 'row',
    alignItems: 'center',
    flexShrink: 1,
    marginRight: 8,
  },
  badgeEstado: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 10,
    paddingVertical: 5,
    borderRadius: 10,
    borderWidth: 1.5,
    flexShrink: 0,
  },
  textoBadgeEstado: {
    fontSize: 12,
    fontWeight: '800',
    letterSpacing: 0.3,
    includeFontPadding: false,
  },
  iconoFolio: {
    width: 26,
    height: 26,
    borderRadius: 8,
    alignItems: 'center',
    justifyContent: 'center',
  },
  filaDatos: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 8,
  },
  filaInferior: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-end',
    borderTopWidth: StyleSheet.hairlineWidth,
    borderTopColor: 'rgba(100,116,139,0.15)',
    paddingTop: 8,
    marginTop: 4,
  },
  totalTexto: {
    fontWeight: '900',
    letterSpacing: -0.5,
  },
});
