/**
 * PANTALLA DE DETALLE DE PEDIDO / FACTURA DIGITAL
 * 
 * Propósito Académico / Proyecto de Grado:
 * Muestra el desglose formal de una venta registrada, actuando como un comprobante
 * digital o ticket de venta. Además, si el usuario posee el rol de Administrador,
 * permite la anulación con restitución automática de stock en el almacén, o la
 * edición transaccional de los productos y clientes.
 */

import React, { useCallback, useState } from 'react';
import { View, StyleSheet, ScrollView, Alert } from 'react-native';
import {
  Text,
  Card,
  Chip,
  Button,
  IconButton,
  Divider,
  ActivityIndicator,
  Portal,
  Dialog,
  Snackbar,
  useTheme,
  Surface,
} from 'react-native-paper';
import { MaterialCommunityIcons } from '@expo/vector-icons';
import type { NativeStackScreenProps } from '@react-navigation/native-stack';
import { useFocusEffect } from '@react-navigation/native';
import { VentasApi } from '../api/endpoints';
import { ApiRequestError } from '../api/client';
import { haptico } from '../utils/haptico';
import { useAuth } from '../context/AuthContext';
import type { Venta, Producto } from '../api/types';
import type { MasStackParamList } from '../navigation/RootNavigator';
import { ClientePickerModal, ProductoPickerModal } from './NuevaVentaScreen';

type Props = NativeStackScreenProps<MasStackParamList, 'VentaDetalle'>;

/**
 * Estructura de línea de venta en modo edición.
 */
interface LineaEditable {
  idProducto: number;
  nombre: string;
  precio: number;
  stockDisponible: number;
  cantidad: number;
}

export default function VentaDetalleScreen({ route, navigation }: Props) {
  const theme = useTheme();                                // Paleta del sistema activo
  const { usuario } = useAuth();                            // Sesión activa del usuario
  const esAdmin = usuario?.rol === 'administrador';        // Comprobación de privilegios de Administrador
  const { id } = route.params;                             // ID de la venta recibido como parámetro de navegación

  // Estados de datos y peticiones
  const [venta, setVenta] = useState<Venta | null>(null);
  const [cargando, setCargando] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [mensaje, setMensaje] = useState<string | null>(null);

  // Estados del flujo de edición administrativa
  const [editando, setEditando] = useState(false);
  const [lineas, setLineas] = useState<LineaEditable[]>([]);
  const [modalProducto, setModalProducto] = useState(false);
  const [modalCliente, setModalCliente] = useState(false);
  const [clienteId, setClienteId] = useState<number | null>(null);
  const [clienteNombre, setClienteNombre] = useState('');
  const [guardando, setGuardando] = useState(false);

  // Control de cuadros de diálogo de confirmación de seguridad
  const [confirmarAnular, setConfirmarAnular] = useState(false);
  const [confirmarEliminar, setConfirmarEliminar] = useState(false);

  /**
   * Carga desde la API de Laravel la venta específica con sus líneas de detalle.
   */
  const cargar = useCallback(async () => {
    setCargando(true);
    setError(null);
    try {
      const { data } = await VentasApi.ver(id);
      setVenta(data);
      setClienteId(data.cliente?.id ?? null);
      setClienteNombre(data.cliente?.nombre ?? '');
    } catch (e) {
      setError(e instanceof ApiRequestError ? e.message : 'No se pudo cargar la venta.');
    } finally {
      setCargando(false);
    }
  }, [id]);

  useFocusEffect(
    useCallback(() => {
      cargar();
    }, [cargar])
  );

  /**
   * Prepara el estado para iniciar la edición de la venta.
   */
  function iniciarEdicion() {
    if (!venta?.items) return;
    setLineas(
      venta.items.map((it) => ({
        idProducto: it.producto_id,
        nombre: it.producto_nombre,
        precio: it.precio_unitario,
        stockDisponible: 99999,
        cantidad: it.cantidad,
      }))
    );
    setEditando(true);
  }

  /**
   * Agrega un producto a la lista en modo edición.
   */
  function agregarProductoAlCarrito(producto: Producto) {
    setLineas((actual) => {
      const existe = actual.find((l) => l.idProducto === producto.id);
      if (existe) {
        return actual.map((l) => (l.idProducto === producto.id ? { ...l, cantidad: l.cantidad + 1 } : l));
      }
      return [
        ...actual,
        {
          idProducto: producto.id,
          nombre: producto.nombre,
          precio: producto.precio,
          stockDisponible: producto.stock,
          cantidad: 1,
        },
      ];
    });
    setModalProducto(false);
  }

  /**
   * Cambia la cantidad en modo edición.
   */
  function cambiarCantidadEdicion(idProducto: number, delta: number) {
    setLineas((actual) =>
      actual
        .map((l) => (l.idProducto === idProducto ? { ...l, cantidad: Math.max(0, l.cantidad + delta) } : l))
        .filter((l) => l.cantidad > 0)
    );
  }

  /**
   * Guarda los cambios modificados en la venta.
   */
  async function guardarCambios() {
    if (!clienteId || lineas.length === 0 || !venta) return;
    setGuardando(true);
    try {
      const resp = await VentasApi.actualizar(venta.id, {
        idCliente: clienteId,
        fecha: venta.fecha,
        estado: venta.estado,
        items: lineas.map((l) => ({ idProducto: l.idProducto, cantidad: l.cantidad })),
      });
      haptico.exito();
      setVenta(resp.data);
      setEditando(false);
      setMensaje('Venta actualizada correctamente.');
    } catch (e) {
      haptico.error();
      setError(e instanceof ApiRequestError ? e.message : 'Error al actualizar la venta.');
    } finally {
      setGuardando(false);
    }
  }

  /**
   * Anula la venta y restituye el stock en inventario a través de VentaService.
   */
  async function anularVenta() {
    if (!venta) return;
    setGuardando(true);
    setConfirmarAnular(false);
    try {
      const resp = await VentasApi.actualizar(venta.id, {
        idCliente: venta.cliente?.id ?? clienteId ?? 1,
        fecha: venta.fecha,
        estado: 'anulado',
        items: (venta.items ?? []).map((it) => ({
          idProducto: it.producto_id,
          cantidad: it.cantidad,
        })),
      });
      haptico.exito();
      setVenta(resp.data);
      setMensaje('Venta anulada y stock restituido al almacén.');
    } catch (e) {
      haptico.error();
      setError(e instanceof ApiRequestError ? e.message : 'Error al anular la venta.');
    } finally {
      setGuardando(false);
    }
  }

  /**
   * Elimina lógicamente la venta de la base de datos (Soft Delete).
   */
  async function eliminarVenta() {
    if (!venta) return;
    setGuardando(true);
    setConfirmarEliminar(false);
    try {
      await VentasApi.eliminar(venta.id);
      haptico.exito();
      navigation.goBack();
    } catch (e) {
      haptico.error();
      setError(e instanceof ApiRequestError ? e.message : 'Error al eliminar la venta.');
      setGuardando(false);
    }
  }

  if (cargando) {
    return (
      <View style={[styles.contenedorCentro, { backgroundColor: theme.colors.background }]}>
        <ActivityIndicator size="large" color={theme.colors.primary} />
        <Text style={{ marginTop: 12, color: theme.colors.onSurfaceVariant }}>Cargando comprobante...</Text>
      </View>
    );
  }

  if (error && !venta) {
    return (
      <View style={[styles.contenedorCentro, { backgroundColor: theme.colors.background }]}>
        <MaterialCommunityIcons name="alert-circle-outline" size={48} color={theme.colors.error} />
        <Text style={{ color: theme.colors.error, marginTop: 12, textAlign: 'center', paddingHorizontal: 30 }}>
          {error}
        </Text>
        <Button mode="contained" onPress={cargar} style={{ marginTop: 16 }}>
          Reintentar
        </Button>
      </View>
    );
  }

  if (!venta) return null;

  const esCompletado = venta.estado === 'completado';
  const totalEdicion = lineas.reduce((acc, l) => acc + l.precio * l.cantidad, 0);

  return (
    <ScrollView style={{ flex: 1, backgroundColor: theme.colors.background }} contentContainerStyle={styles.contenido}>
      {/* ── COMPROBANTE FISCAL / TICKET DIGITAL ──────────────────────── */}
      <Surface style={[styles.ticketCard, { backgroundColor: theme.colors.surface }]} elevation={2}>
        {/* Cabecera del ticket */}
        <View style={styles.ticketCabecera}>
          <View>
            <Text variant="headlineSmall" style={{ fontWeight: '900', color: theme.colors.onSurface }}>
              Venta #{venta.numero ?? venta.id}
            </Text>
            <Text variant="bodySmall" style={{ color: theme.colors.onSurfaceVariant, marginTop: 2 }}>
              Fecha de emisión: {venta.fecha}
            </Text>
          </View>

          <View
            style={[
              styles.badgeDetalle,
              {
                backgroundColor: esCompletado ? '#ECFDF5' : '#FEF2F2',
                borderColor: esCompletado ? '#10B981' : '#EF4444',
              },
            ]}
          >
            <MaterialCommunityIcons
              name={esCompletado ? 'check-circle' : 'close-circle'}
              size={15}
              color={esCompletado ? '#059669' : '#DC2626'}
              style={{ marginRight: 6 }}
            />
            <Text
              style={[
                styles.textoBadgeDetalle,
                { color: esCompletado ? '#047857' : '#B91C1C' },
              ]}
            >
              {esCompletado ? 'COMPLETADO' : 'ANULADO'}
            </Text>
          </View>
        </View>

        <Divider style={{ marginVertical: 14 }} />

        {/* Datos de los involucrados en la transacción */}
        <View style={styles.filaInfo}>
          <Text variant="bodySmall" style={styles.etiquetaGris}>Cliente:</Text>
          <Text variant="bodyMedium" style={{ fontWeight: '700', flex: 1, textAlign: 'right' }}>
            {venta.cliente?.nombre ?? 'Cliente casual'}
          </Text>
        </View>

        <View style={styles.filaInfo}>
          <Text variant="bodySmall" style={styles.etiquetaGris}>Atendido por:</Text>
          <Text variant="bodyMedium" style={{ fontWeight: '600', flex: 1, textAlign: 'right' }}>
            {venta.vendedor?.nombre ?? 'Sistema / Admin'}
          </Text>
        </View>

        <Divider style={{ marginVertical: 14 }} />

        {/* Desglose de productos */}
        <Text variant="titleSmall" style={{ fontWeight: '800', marginBottom: 10, color: theme.colors.primary }}>
          DETALLE DE PRODUCTOS FACTURADOS
        </Text>

        {!editando ? (
          (venta.items ?? []).map((it, idx) => (
            <View key={idx} style={styles.filaItem}>
              <View style={{ flex: 1 }}>
                <Text variant="bodyMedium" style={{ fontWeight: '700' }}>
                  {it.producto_nombre}
                </Text>
                <Text variant="bodySmall" style={{ color: theme.colors.onSurfaceVariant }}>
                  {it.cantidad} unidad(es) × Bs {Number(it.precio_unitario ?? 0).toFixed(2)}
                </Text>
              </View>
              <Text variant="titleSmall" style={{ fontWeight: '800' }}>
                Bs {Number(it.subtotal ?? 0).toFixed(2)}
              </Text>
            </View>
          ))
        ) : (
          <View>
            {lineas.map((l) => (
              <View key={l.idProducto} style={styles.filaItemEditable}>
                <View style={{ flex: 1 }}>
                  <Text variant="bodyMedium" style={{ fontWeight: '700' }}>
                    {l.nombre}
                  </Text>
                  <Text variant="bodySmall" style={{ color: theme.colors.onSurfaceVariant }}>
                    Bs {Number(l.precio ?? 0).toFixed(2)} c/u
                  </Text>
                </View>
                <View style={styles.stepperWrap}>
                  <IconButton icon="minus" size={16} onPress={() => cambiarCantidadEdicion(l.idProducto, -1)} />
                  <Text variant="titleMedium" style={{ fontWeight: '800', minWidth: 24, textAlign: 'center' }}>
                    {l.cantidad}
                  </Text>
                  <IconButton icon="plus" size={16} onPress={() => cambiarCantidadEdicion(l.idProducto, 1)} />
                </View>
                <Text variant="titleSmall" style={{ fontWeight: '800', minWidth: 70, textAlign: 'right' }}>
                  Bs {(l.precio * l.cantidad).toFixed(2)}
                </Text>
              </View>
            ))}
            <Button mode="outlined" icon="plus" onPress={() => setModalProducto(true)} style={{ marginTop: 10 }}>
              Agregar otro producto
            </Button>
          </View>
        )}

        <Divider style={{ marginVertical: 16 }} />

        {/* Resumen Total */}
        <View style={styles.filaTotal}>
          <Text variant="titleLarge" style={{ fontWeight: '800' }}>
            TOTAL
          </Text>
          <Text variant="headlineMedium" style={{ fontWeight: '900', color: theme.colors.primary }}>
            Bs {Number(editando ? totalEdicion : (venta.total ?? 0)).toFixed(2)}
          </Text>
        </View>
      </Surface>

      {/* ── BOTONES DE ACCIÓN PARA EL ADMINISTRADOR ────────────────── */}
      {esAdmin && (
        <View style={styles.panelAcciones}>
          {!editando ? (
            <>
              {esCompletado && (
                <Button
                  mode="contained-tonal"
                  icon="pencil-outline"
                  onPress={iniciarEdicion}
                  style={styles.botonAccion}
                >
                  Editar Venta
                </Button>
              )}

              {esCompletado && (
                <Button
                  mode="outlined"
                  icon="cancel"
                  textColor={theme.colors.error}
                  onPress={() => setConfirmarAnular(true)}
                  style={styles.botonAccion}
                >
                  Anular Venta
                </Button>
              )}

              <Button
                mode="text"
                icon="trash-can-outline"
                textColor={theme.colors.error}
                onPress={() => setConfirmarEliminar(true)}
                style={styles.botonAccion}
              >
                Eliminar Registro
              </Button>
            </>
          ) : (
            <View style={{ flexDirection: 'row', gap: 10 }}>
              <Button mode="outlined" onPress={() => setEditando(false)} style={{ flex: 1 }}>
                Cancelar
              </Button>
              <Button
                mode="contained"
                onPress={guardarCambios}
                loading={guardando}
                disabled={guardando}
                style={{ flex: 1 }}
              >
                Guardar
              </Button>
            </View>
          )}
        </View>
      )}

      {/* ── DIÁLOGO: CONFIRMAR ANULACIÓN ───────────────────────────── */}
      <Portal>
        <Dialog visible={confirmarAnular} onDismiss={() => setConfirmarAnular(false)}>
          <Dialog.Title>¿Anular Venta #{venta.numero ?? venta.id}?</Dialog.Title>
          <Dialog.Content>
            <Text variant="bodyMedium">
              Al anular esta venta, todas las cantidades se restituirán automáticamente al stock del
              supermercado y la venta dejará de computarse en las métricas de ingresos.
            </Text>
          </Dialog.Content>
          <Dialog.Actions>
            <Button onPress={() => setConfirmarAnular(false)}>Cancelar</Button>
            <Button textColor={theme.colors.error} onPress={anularVenta} loading={guardando}>
              Sí, Anular Venta
            </Button>
          </Dialog.Actions>
        </Dialog>
      </Portal>

      {/* ── DIÁLOGO: CONFIRMAR ELIMINACIÓN ─────────────────────────── */}
      <Portal>
        <Dialog visible={confirmarEliminar} onDismiss={() => setConfirmarEliminar(false)}>
          <Dialog.Title>¿Eliminar Venta #{venta.numero ?? venta.id}?</Dialog.Title>
          <Dialog.Content>
            <Text variant="bodyMedium">
              Esta acción enviará la venta a la papelera del sistema. ¿Estás seguro de continuar?
            </Text>
          </Dialog.Content>
          <Dialog.Actions>
            <Button onPress={() => setConfirmarEliminar(false)}>Cancelar</Button>
            <Button textColor={theme.colors.error} onPress={eliminarVenta} loading={guardando}>
              Sí, Eliminar
            </Button>
          </Dialog.Actions>
        </Dialog>
      </Portal>

      {/* Modales auxiliares de selección */}
      <ProductoPickerModal
        visible={modalProducto}
        onClose={() => setModalProducto(false)}
        onSeleccionar={agregarProductoAlCarrito}
      />

      <ClientePickerModal
        visible={modalCliente}
        onClose={() => setModalCliente(false)}
        onSeleccionar={(c) => {
          setClienteId(c.id);
          setClienteNombre(c.nombre);
          setModalCliente(false);
        }}
      />

      <Snackbar visible={!!mensaje} onDismiss={() => setMensaje(null)} duration={3500}>
        {mensaje}
      </Snackbar>
      <Snackbar visible={!!error} onDismiss={() => setError(null)} duration={4000} style={{ backgroundColor: theme.colors.error }}>
        {error}
      </Snackbar>
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  contenido: {
    padding: 16,
    paddingBottom: 40,
  },
  contenedorCentro: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    padding: 20,
  },
  ticketCard: {
    padding: 20,
    borderRadius: 20,
  },
  ticketCabecera: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-start',
  },
  filaInfo: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 6,
  },
  etiquetaGris: {
    color: '#64748B',
    minWidth: 100,
  },
  filaItem: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingVertical: 8,
    borderBottomWidth: StyleSheet.hairlineWidth,
    borderBottomColor: 'rgba(100,116,139,0.12)',
  },
  filaItemEditable: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: 6,
  },
  stepperWrap: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  filaTotal: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginTop: 6,
  },
  panelAcciones: {
    marginTop: 20,
    gap: 10,
  },
  botonAccion: {
    borderRadius: 12,
  },
  badgeDetalle: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderRadius: 10,
    borderWidth: 1.5,
    flexShrink: 0,
  },
  textoBadgeDetalle: {
    fontSize: 12,
    fontWeight: '800',
    letterSpacing: 0.5,
    includeFontPadding: false,
  },
});
