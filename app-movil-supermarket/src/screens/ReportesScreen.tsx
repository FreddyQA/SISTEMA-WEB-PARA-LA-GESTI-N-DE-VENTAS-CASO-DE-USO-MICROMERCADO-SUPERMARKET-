/**
 * PANTALLA DE REPORTES ANALÍTICOS Y ESTADÍSTICAS FINANCIERAS
 * 
 * Propósito Académico / Proyecto de Grado:
 * Módulo de Inteligencia de Negocios (BI) para la alta gerencia. Permite evaluar
 * el rendimiento comercial del supermercado filtrando por rangos de fecha flexibles,
 * estados de transacción (completados vs anulados) y clientes específicos.
 * 
 * Métricas calculadas por el backend y renderizadas aquí:
 * 1. Total Ingresos (Bs): Suma de dinero recaudado por ventas completadas.
 * 2. Transacciones / Ventas: volumen de ventas emitidas.
 * 3. Unidades Vendidas: Conteo físico de artículos despachados.
 * 4. Ticket Promedio (Bs): Valor promedio de compra por cliente (Total / Transacciones).
 */

import React, { useCallback, useEffect, useState } from 'react';
import { View, StyleSheet, FlatList, Platform, ScrollView } from 'react-native';
import {
  Text,
  Button,
  Chip,
  ActivityIndicator,
  useTheme,
  SegmentedButtons,
  Menu,
  Surface,
} from 'react-native-paper';
import { MaterialCommunityIcons } from '@expo/vector-icons';
import DateTimePicker from '@react-native-community/datetimepicker';
import { useFocusEffect } from '@react-navigation/native';
import { ClientesApi, ReportesApi } from '../api/endpoints';
import type { ReporteMetricas, FiltrosReporte } from '../api/endpoints';
import type { Cliente, Venta } from '../api/types';
import { haptico } from '../utils/haptico';

export default function ReportesScreen() {
  const theme = useTheme();                                // Paleta del sistema activo

  // Datos financieros y transacciones filtradas
  const [metricas, setMetricas] = useState<ReporteMetricas | null>(null);
  const [ventas, setVentas] = useState<Venta[]>([]);
  const [cargando, setCargando] = useState(true);
  const [error, setError] = useState<string | null>(null);

  // Estados de los filtros
  const [estado, setEstado] = useState<'todos' | 'completado' | 'anulado'>('todos');
  const [periodoRapido, setPeriodoRapido] = useState<string>('personalizado');
  const [fechaInicio, setFechaInicio] = useState<string | null>(null);
  const [fechaFin, setFechaFin] = useState<string | null>(null);
  const [mostrarPickerInicio, setMostrarPickerInicio] = useState(false);
  const [mostrarPickerFin, setMostrarPickerFin] = useState(false);

  // Filtro por cliente
  const [cliente, setCliente] = useState<Cliente | null>(null);
  const [clientes, setClientes] = useState<Cliente[]>([]);
  const [menuClienteAbierto, setMenuClienteAbierto] = useState(false);

  /**
   * Carga la cartera de clientes para el selector desplegable.
   */
  useEffect(() => {
    ClientesApi.listar()
      .then((r) => setClientes(r.data))
      .catch(() => {});
  }, []);

  const filtros: FiltrosReporte = {
    estado,
    fecha_inicio: fechaInicio ?? undefined,
    fecha_fin: fechaFin ?? undefined,
    id_cliente: cliente?.id,
  };

  /**
   * Solicita el cálculo consolidado a ReporteApiController@ventas.
   */
  const cargar = useCallback(async () => {
    setCargando(true);
    setError(null);
    try {
      const resp = await ReportesApi.ventas(filtros);
      setMetricas(resp.metricas ?? null);
      const lista = Array.isArray(resp.ventas)
        ? resp.ventas
        : (Array.isArray(resp.ventas?.data) ? resp.ventas.data : []);
      setVentas(lista);
    } catch (e: any) {
      setError(e?.message ?? 'No se pudo generar el reporte financiero.');
    } finally {
      setCargando(false);
    }
  }, [estado, fechaInicio, fechaFin, cliente]);

  useFocusEffect(
    useCallback(() => {
      cargar();
    }, [cargar])
  );

  /**
   * Restablece todos los filtros a sus valores predeterminados.
   */
  function limpiarFiltros() {
    haptico.toque();
    setEstado('todos');
    setPeriodoRapido('personalizado');
    setFechaInicio(null);
    setFechaFin(null);
    setCliente(null);
  }

  return (
    <View style={[styles.contenedor, { backgroundColor: theme.colors.background }]}>
      <FlatList
        data={ventas}
        keyExtractor={(p) => String(p.id)}
        contentContainerStyle={styles.lista}
        ListHeaderComponent={
          <View style={styles.cabeceraFiltros}>
            {/* ── SELECTOR DE ESTADO DE PEDIDO ────────────────────────── */}
            <Text variant="labelLarge" style={[styles.seccionTitulo, { color: theme.colors.primary }]}>
              FILTRO POR ESTADO DE PEDIDO
            </Text>
            <SegmentedButtons
              value={estado}
              onValueChange={(v) => {
                haptico.toque();
                setEstado(v as 'todos' | 'completado' | 'anulado');
              }}
              buttons={[
                { value: 'todos', label: 'Todos' },
                { value: 'completado', label: 'Completados' },
                { value: 'anulado', label: 'Anulados' },
              ]}
              style={styles.segmento}
            />

            {/* ── SELECTORES DE RANGO DE FECHAS ───────────────────────── */}
            <Text variant="labelLarge" style={[styles.seccionTitulo, { color: theme.colors.primary, marginTop: 12 }]}>
              RANGO DE FECHAS
            </Text>

            {/* Filtros rápidos de período */}
            <ScrollView horizontal showsHorizontalScrollIndicator={false} style={{ marginBottom: 10 }} contentContainerStyle={{ gap: 6 }}>
              {[
                { key: 'hoy', label: 'Hoy' },
                { key: 'ayer', label: 'Ayer' },
                { key: 'semana', label: '1 Semana' },
                { key: 'mes', label: '1 Mes' },
                { key: 'personalizado', label: 'Personalizado' },
              ].map((p) => {
                const activo = periodoRapido === p.key;
                return (
                  <Chip
                    key={p.key}
                    selected={activo}
                    onPress={() => {
                      haptico.toque();
                      setPeriodoRapido(p.key);
                      const hoy = new Date();
                      const fmt = (d: Date) => {
                        return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
                      };
                      if (p.key === 'hoy') {
                        const f = fmt(hoy);
                        setFechaInicio(f);
                        setFechaFin(f);
                      } else if (p.key === 'ayer') {
                        const ayer = new Date(hoy);
                        ayer.setDate(ayer.getDate() - 1);
                        const f = fmt(ayer);
                        setFechaInicio(f);
                        setFechaFin(f);
                      } else if (p.key === 'semana') {
                        const inicio = new Date(hoy);
                        inicio.setDate(inicio.getDate() - 6);
                        setFechaInicio(fmt(inicio));
                        setFechaFin(fmt(hoy));
                      } else if (p.key === 'mes') {
                        const inicio = new Date(hoy);
                        inicio.setDate(inicio.getDate() - 29);
                        setFechaInicio(fmt(inicio));
                        setFechaFin(fmt(hoy));
                      } else {
                        setFechaInicio(null);
                        setFechaFin(null);
                      }
                    }}
                    style={{
                      backgroundColor: activo ? theme.colors.primaryContainer : theme.colors.surfaceVariant,
                    }}
                    textStyle={{
                      color: activo ? theme.colors.primary : theme.colors.onSurfaceVariant,
                      fontWeight: activo ? '700' : '500',
                      fontSize: 12,
                    }}
                  >
                    {p.label}
                  </Chip>
                );
              })}
            </ScrollView>

            <View style={styles.filaFechas}>
              <Button
                mode="outlined"
                icon="calendar-start"
                style={styles.botonFecha}
                onPress={() => {
                  setPeriodoRapido('personalizado');
                  setMostrarPickerInicio(true);
                }}
              >
                {fechaInicio ? `Desde: ${fechaInicio}` : 'Fecha Inicial'}
              </Button>
              <Button
                mode="outlined"
                icon="calendar-end"
                style={styles.botonFecha}
                onPress={() => {
                  setPeriodoRapido('personalizado');
                  setMostrarPickerFin(true);
                }}
              >
                {fechaFin ? `Hasta: ${fechaFin}` : 'Fecha Final'}
              </Button>
            </View>

            {/* Pickers nativos de fecha para iOS / Android */}
            {mostrarPickerInicio && (
              <DateTimePicker
                value={fechaInicio ? new Date(fechaInicio + 'T12:00:00') : new Date()}
                mode="date"
                display={Platform.OS === 'ios' ? 'spinner' : 'default'}
                onChange={(_, d) => {
                  setMostrarPickerInicio(false);
                  if (d) {
                    const anio = d.getFullYear();
                    const mes = String(d.getMonth() + 1).padStart(2, '0');
                    const dia = String(d.getDate()).padStart(2, '0');
                    setFechaInicio(`${anio}-${mes}-${dia}`);
                  }
                }}
              />
            )}
            {mostrarPickerFin && (
              <DateTimePicker
                value={fechaFin ? new Date(fechaFin + 'T12:00:00') : new Date()}
                mode="date"
                display={Platform.OS === 'ios' ? 'spinner' : 'default'}
                onChange={(_, d) => {
                  setMostrarPickerFin(false);
                  if (d) {
                    const anio = d.getFullYear();
                    const mes = String(d.getMonth() + 1).padStart(2, '0');
                    const dia = String(d.getDate()).padStart(2, '0');
                    setFechaFin(`${anio}-${mes}-${dia}`);
                  }
                }}
              />
            )}

            {/* ── SELECTOR DE CLIENTE ESPECÍFICO ─────────────────────── */}
            <View style={styles.filaClienteFiltro}>
              <Menu
                visible={menuClienteAbierto}
                onDismiss={() => setMenuClienteAbierto(false)}
                anchor={
                  <Button
                    mode="outlined"
                    icon="account-outline"
                    onPress={() => setMenuClienteAbierto(true)}
                    style={{ flex: 1 }}
                  >
                    {cliente ? `Cliente: ${cliente.nombre}` : 'Todos los clientes'}
                  </Button>
                }
              >
                <Menu.Item
                  title="Todos los clientes"
                  onPress={() => {
                    setCliente(null);
                    setMenuClienteAbierto(false);
                  }}
                />
                {clientes.map((c) => (
                  <Menu.Item
                    key={c.id}
                    title={c.nombre}
                    onPress={() => {
                      setCliente(c);
                      setMenuClienteAbierto(false);
                    }}
                  />
                ))}
              </Menu>

              {(fechaInicio || fechaFin || cliente || estado !== 'todos') && (
                <Button mode="text" onPress={limpiarFiltros} textColor={theme.colors.error} style={{ marginLeft: 6 }}>
                  Restablecer
                </Button>
              )}
            </View>

            {/* ── TARJETAS DE MÉTRICAS / KPIS DEL REPORTE ─────────────── */}
            {metricas && (
              <View style={styles.gridMetricas}>
                {/* Métrica 1: Total Facturado */}
                <Surface style={[styles.tarjetaMetrica, { backgroundColor: '#EEF2FF' }]} elevation={1}>
                  <View style={styles.filaMetricaIcono}>
                    <MaterialCommunityIcons name="cash-multiple" size={20} color="#4F46E5" />
                    <Text variant="labelSmall" style={{ color: '#4F46E5', fontWeight: '800' }}>
                      TOTAL VENTAS
                    </Text>
                  </View>
                  <Text variant="headlineSmall" style={{ fontWeight: '900', color: '#312E81', marginTop: 6 }}>
                    Bs {(metricas.total_ventas ?? 0).toFixed(2)}
                  </Text>
                </Surface>

                {/* Métrica 2: Cantidad de Ventas */}
                <Surface style={[styles.tarjetaMetrica, { backgroundColor: '#ECFDF5' }]} elevation={1}>
                  <View style={styles.filaMetricaIcono}>
                    <MaterialCommunityIcons name="receipt" size={20} color="#059669" />
                    <Text variant="labelSmall" style={{ color: '#059669', fontWeight: '800' }}>
                      TRANSACCIONES
                    </Text>
                  </View>
                  <Text variant="headlineSmall" style={{ fontWeight: '900', color: '#064E3B', marginTop: 6 }}>
                    {metricas.cantidad_ventas ?? 0} transacc.
                  </Text>
                </Surface>

                {/* Métrica 3: Unidades Vendidas */}
                <Surface style={[styles.tarjetaMetrica, { backgroundColor: '#FFFBEB' }]} elevation={1}>
                  <View style={styles.filaMetricaIcono}>
                    <MaterialCommunityIcons name="package-variant" size={20} color="#D97706" />
                    <Text variant="labelSmall" style={{ color: '#D97706', fontWeight: '800' }}>
                      ARTÍCULOS
                    </Text>
                  </View>
                  <Text variant="headlineSmall" style={{ fontWeight: '900', color: '#78350F', marginTop: 6 }}>
                    {metricas.total_unidades ?? 0} unids.
                  </Text>
                </Surface>

                {/* Métrica 4: Promedio por Venta (Ticket Promedio) */}
                <Surface style={[styles.tarjetaMetrica, { backgroundColor: '#FFF1F2' }]} elevation={1}>
                  <View style={styles.filaMetricaIcono}>
                    <MaterialCommunityIcons name="chart-bell-curve-cumulative" size={20} color="#E11D48" />
                    <Text variant="labelSmall" style={{ color: '#E11D48', fontWeight: '800' }}>
                      PROMEDIO / TICKET
                    </Text>
                  </View>
                  <Text variant="headlineSmall" style={{ fontWeight: '900', color: '#881337', marginTop: 6 }}>
                    Bs {(metricas.promedio_venta ?? 0).toFixed(2)}
                  </Text>
                </Surface>
              </View>
            )}

            <Text variant="titleMedium" style={{ fontWeight: '800', marginTop: 20, marginBottom: 8 }}>
              Listado de Ventas Filtradas ({ventas.length})
            </Text>
          </View>
        }
        ListEmptyComponent={
          !cargando ? (
            <View style={styles.vacioWrap}>
              <MaterialCommunityIcons name="file-search-outline" size={48} color={theme.colors.onSurfaceVariant} />
              <Text variant="titleMedium" style={{ fontWeight: '700', marginTop: 12 }}>
                Sin ventas para estos filtros
              </Text>
              <Text variant="bodySmall" style={{ color: theme.colors.onSurfaceVariant, marginTop: 4 }}>
                Ajusta las fechas o el estado seleccionado para ampliar la búsqueda.
              </Text>
            </View>
          ) : (
            <ActivityIndicator style={{ marginVertical: 30 }} />
          )
        }
        renderItem={({ item }) => {
          const esCompletado = item.estado === 'completado';
          return (
            <Surface style={[styles.tarjetaVenta, { backgroundColor: theme.colors.surface }]} elevation={1}>
              <View style={styles.filaVenta}>
                <View style={{ flex: 1, marginRight: 8 }}>
                  <Text variant="titleSmall" style={{ fontWeight: '700' }}>
                    Venta #{item.numero ?? item.id} · {item.cliente?.nombre ?? 'Cliente casual'}
                  </Text>
                  <Text variant="bodySmall" style={{ color: theme.colors.onSurfaceVariant }}>
                    Fecha: {item.fecha}
                  </Text>
                </View>

                <View style={{ alignItems: 'flex-end' }}>
                  <Text variant="titleMedium" style={{ fontWeight: '800', color: theme.colors.primary }}>
                    Bs {Number(item.total ?? 0).toFixed(2)}
                  </Text>
                  <View
                    style={[
                      styles.badgeEstado,
                      {
                        backgroundColor: esCompletado ? '#ECFDF5' : '#FEF2F2',
                        borderColor: esCompletado ? '#10B981' : '#EF4444',
                      },
                    ]}
                  >
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
              </View>
            </Surface>
          );
        }}
      />
    </View>
  );
}

const styles = StyleSheet.create({
  contenedor: {
    flex: 1,
  },
  lista: {
    padding: 16,
    paddingBottom: 40,
  },
  cabeceraFiltros: {
    marginBottom: 6,
  },
  seccionTitulo: {
    fontWeight: '800',
    fontSize: 11,
    letterSpacing: 0.8,
    marginBottom: 6,
  },
  segmento: {
    marginBottom: 12,
  },
  filaFechas: {
    flexDirection: 'row',
    gap: 8,
    marginBottom: 10,
  },
  botonFecha: {
    flex: 1,
    borderRadius: 12,
  },
  filaClienteFiltro: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 16,
  },
  gridMetricas: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 10,
    justifyContent: 'space-between',
  },
  tarjetaMetrica: {
    width: '48%',
    padding: 14,
    borderRadius: 16,
  },
  filaMetricaIcono: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
  },
  tarjetaVenta: {
    marginBottom: 10,
    borderRadius: 16,
    padding: 14,
  },
  filaVenta: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
  },
  vacioWrap: {
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 40,
  },
  badgeEstado: {
    paddingHorizontal: 8,
    paddingVertical: 3,
    borderRadius: 8,
    borderWidth: 1.2,
    marginTop: 4,
  },
  textoBadgeEstado: {
    fontSize: 11,
    fontWeight: '800',
    letterSpacing: 0.3,
  },
});
