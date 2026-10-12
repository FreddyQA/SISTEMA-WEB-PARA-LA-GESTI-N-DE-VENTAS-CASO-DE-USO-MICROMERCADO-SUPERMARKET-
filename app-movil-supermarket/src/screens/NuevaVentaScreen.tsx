/**
 * PANTALLA DE REGISTRO DE NUEVA VENTA / PEDIDO (PUNTO DE VENTA MÓVIL - POS)
 * 
 * Propósito Académico / Proyecto de Grado:
 * Transforma el teléfono celular en un terminal de punto de venta (POS) portátil.
 * Permite seleccionar un cliente de la base de datos, armar un carrito dinámico
 * agregando productos del catálogo, controlar en tiempo real que no se exceda el
 * inventario disponible y emitir la transacción atómica hacia el backend.
 * 
 * Principios de arquitectura destacados:
 * 1. Control estricto de concurrencia: El stock se valida en el cliente y de forma
 *    atómica con bloqueo pesimista (lockForUpdate) en el servidor (VentaService).
 * 2. Cálculo en tiempo real de subtotales y total general acumulado.
 * 3. Experiencia táctil con retroalimentación háptica en cada interacción del carrito.
 */

import React, { useState, useCallback, useEffect } from 'react';
import { View, StyleSheet, FlatList, Modal, Pressable, Image, KeyboardAvoidingView, Platform } from 'react-native';
import {
  Text,
  Button,
  Searchbar,
  List,
  Card,
  IconButton,
  ActivityIndicator,
  Divider,
  Snackbar,
  useTheme,
  Surface,
  Avatar,
  Chip,
} from 'react-native-paper';
import { MaterialCommunityIcons } from '@expo/vector-icons';
import { useFocusEffect } from '@react-navigation/native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { ClientesApi, VentasApi, ProductosApi } from '../api/endpoints';
import { ApiRequestError, resolverImagenUrl } from '../api/client';
import { haptico } from '../utils/haptico';
import type { Cliente, Producto } from '../api/types';

/**
 * Estructura interna de una línea en el carrito de compras temporal.
 */
interface LineaCarrito {
  producto: Producto;  // Entidad del producto seleccionado
  cantidad: number;    // Unidades que el cliente desea comprar
}

function stockDisponible(producto: Producto): number {
  return producto.stock_disponible ?? producto.stock;
}

export default function NuevaVentaScreen() {
  const theme = useTheme();                                // Paleta del sistema activo

  // Estado del cliente seleccionado para la venta
  const [cliente, setCliente] = useState<Cliente | null>(null);

  // Carrito de compras: lista de productos y sus respectivas cantidades
  const [carrito, setCarrito] = useState<LineaCarrito[]>([]);

  // Control de visibilidad de modales de selección
  const [modalCliente, setModalCliente] = useState(false);
  const [modalProducto, setModalProducto] = useState(false);

  // Estados de proceso y feedback
  const [enviando, setEnviando] = useState(false);
  const [mensaje, setMensaje] = useState<string | null>(null);
  const [error, setError] = useState<string | null>(null);
  const [busqueda, setBusqueda] = useState('');
  const [sugerencias, setSugerencias] = useState<Producto[]>([]);
  const [buscando, setBuscando] = useState(false);

  /**
   * Limpia o mantiene el formulario al cambiar de pestaña según sea necesario.
   */
  useFocusEffect(
    useCallback(() => {
      return () => {
        // Al abandonar la pantalla tras un proceso exitoso se garantiza estado limpio
      };
    }, [])
  );

  /**
   * Agrega un producto al carrito de compras. Si ya existe, incrementa su cantidad
   * sin exceder el stock disponible en almacén.
   * @param producto Producto seleccionado desde el modal
   */
  function agregarProducto(producto: Producto) {
    haptico.toque();
    setCarrito((actual) => {
      const existe = actual.find((l) => l.producto.id === producto.id);
      if (existe) {
        return actual.map((l) =>
          l.producto.id === producto.id
            ? { ...l, cantidad: Math.min(l.cantidad + 1, stockDisponible(producto)) }
            : l
        );
      }
      return [...actual, { producto, cantidad: 1 }];
    });
    setModalProducto(false);
    setBusqueda('');
    setSugerencias([]);
  }

  useEffect(() => {
    const termino = busqueda.trim();
    if (termino.length < 2) {
      setSugerencias([]);
      return;
    }
    const temporizador = setTimeout(async () => {
      setBuscando(true);
      try {
        const resp = await ProductosApi.listar(termino);
        setSugerencias(resp.data.filter((p) => stockDisponible(p) > 0).slice(0, 8));
      } catch {
        setSugerencias([]);
      } finally {
        setBuscando(false);
      }
    }, 250);
    return () => clearTimeout(temporizador);
  }, [busqueda]);

  /**
   * Modifica la cantidad de unidades de un producto en el carrito.
   * Si la cantidad llega a 0, la línea se elimina automáticamente del carrito.
   * @param idProducto Identificador del producto a modificar
   * @param delta Variación (+1 para sumar, -1 para restar)
   */
  function cambiarCantidad(idProducto: number, delta: number) {
    haptico.toque();
    setCarrito((actual) =>
      actual
        .map((l) =>
          l.producto.id === idProducto
            ? { ...l, cantidad: Math.max(0, Math.min(l.cantidad + delta, stockDisponible(l.producto))) }
            : l
        )
        .filter((l) => l.cantidad > 0)
    );
  }

  // Cálculo acumulado del monto total de la venta en Bolivianos
  const total = carrito.reduce((acc, l) => acc + l.producto.precio * l.cantidad, 0);

  // Conteo total de piezas/artículos en el carrito
  const totalArticulos = carrito.reduce((acc, l) => acc + l.cantidad, 0);

  /**
   * Envía la orden de venta a la API de Laravel de forma atómica.
   */
  async function registrarVenta() {
    if (!cliente) {
      haptico.error();
      setError('Debes seleccionar un cliente para registrar la venta.');
      return;
    }
    if (carrito.length === 0) {
      haptico.error();
      setError('Agrega al menos un producto al carrito de venta.');
      return;
    }

    setEnviando(true);
    setError(null);
    try {
      // Llamada POST a /api/ventas
      const venta = await VentasApi.crear(
        cliente.id,
        carrito.map((l) => ({ idProducto: l.producto.id, cantidad: l.cantidad }))
      );

      haptico.exito();
      const folio = venta.data.numero ?? venta.data.id;
      setMensaje(`¡Venta #${folio} completada exitosamente! Total: Bs ${venta.data.total.toFixed(2)}`);

      // Reiniciar estado tras venta exitosa
      setCliente(null);
      setCarrito([]);
    } catch (e) {
      haptico.error();
      setError(e instanceof ApiRequestError ? e.message : 'No se pudo registrar la venta en el servidor.');
    } finally {
      setEnviando(false);
    }
  }

  return (
    <View style={[styles.contenedor, { backgroundColor: theme.colors.background }]}>
      <FlatList
        data={carrito}
        keyExtractor={(l) => String(l.producto.id)}
        contentContainerStyle={styles.lista}
        ListHeaderComponent={
          <View style={styles.cabeceraSeccion}>
            {/* ── SELECCIÓN DE CLIENTE ─────────────────────────────────── */}
            <View style={styles.filaTituloSeccion}>
              <Text variant="titleMedium" style={{ fontWeight: '800', color: theme.colors.onSurface }}>
                Cliente de la Venta
              </Text>
              {cliente && (
                <Pressable onPress={() => setModalCliente(true)}>
                  <Text variant="labelMedium" style={{ color: theme.colors.primary, fontWeight: '700' }}>
                    Cambiar
                  </Text>
                </Pressable>
              )}
            </View>

            {cliente ? (
              <Surface style={[styles.tarjetaCliente, { backgroundColor: theme.colors.surface }]} elevation={1}>
                <View style={styles.filaCliente}>
                  <Avatar.Text
                    label={cliente.nombre.charAt(0).toUpperCase()}
                    size={46}
                    style={{ backgroundColor: theme.colors.primaryContainer }}
                    color={theme.colors.primary}
                  />
                  <View style={{ flex: 1, marginLeft: 14 }}>
                    <Text variant="titleMedium" style={{ fontWeight: '700' }}>
                      {cliente.nombre}
                    </Text>
                    <Text variant="bodySmall" style={{ color: theme.colors.onSurfaceVariant }}>
                      {cliente.email}
                    </Text>
                    {cliente.telefono && (
                      <Text variant="labelSmall" style={{ color: '#10B981', fontWeight: '600', marginTop: 2 }}>
                        Tel: {cliente.telefono}
                      </Text>
                    )}
                  </View>
                  <IconButton
                    icon="pencil-outline"
                    size={20}
                    onPress={() => setModalCliente(true)}
                  />
                </View>
              </Surface>
            ) : (
              <Pressable
                onPress={() => {
                  haptico.toque();
                  setModalCliente(true);
                }}
                style={styles.selectorClienteVacio}
              >
                <MaterialCommunityIcons name="account-plus-outline" size={28} color={theme.colors.primary} />
                <View style={{ marginLeft: 12 }}>
                  <Text variant="titleSmall" style={{ color: theme.colors.primary, fontWeight: '700' }}>
                    Seleccionar Cliente
                  </Text>
                  <Text variant="bodySmall" style={{ color: theme.colors.onSurfaceVariant }}>
                    Toca aquí para asignar el cliente de la venta
                  </Text>
                </View>
              </Pressable>
            )}

            {/* ── ENCABEZADO DE PRODUCTOS EN CARRITO ──────────────────── */}
            <View style={[styles.filaTituloSeccion, { marginTop: 20 }]}>
              <Text variant="titleMedium" style={{ fontWeight: '800', color: theme.colors.onSurface }}>
                Productos en Carrito ({totalArticulos})
              </Text>
              <Button
                mode="contained-tonal"
                compact
                icon="plus"
                onPress={() => {
                  haptico.toque();
                  setModalProducto(true);
                }}
              >
                Catálogo
              </Button>
            </View>
            <Searchbar
              placeholder="Buscar producto y añadir..."
              value={busqueda}
              onChangeText={setBusqueda}
              style={{ marginBottom: 8, borderRadius: 14, backgroundColor: theme.colors.surface }}
              inputStyle={{ fontSize: 14 }}
            />
            {buscando && <ActivityIndicator style={{ marginBottom: 8 }} />}
            {sugerencias.map((item) => (
              <Surface key={item.id} style={[styles.itemPicker, { backgroundColor: theme.colors.surface }]} elevation={1}>
                <Pressable onPress={() => agregarProducto(item)} style={styles.filaPicker}>
                  <View style={{ flex: 1 }}>
                    <Text variant="titleSmall" style={{ fontWeight: '700' }}>{item.nombre}</Text>
                    <Text variant="bodySmall" style={{ color: theme.colors.onSurfaceVariant }}>
                      Bs {item.precio.toFixed(2)} · {stockDisponible(item)} disp.
                    </Text>
                  </View>
                  <MaterialCommunityIcons name="plus-circle" size={22} color={theme.colors.primary} />
                </Pressable>
              </Surface>
            ))}
          </View>
        }
        ListEmptyComponent={
          <View style={styles.contenedorVacio}>
            <Surface style={[styles.iconoVacioWrap, { backgroundColor: theme.colors.surfaceVariant }]} elevation={0}>
              <MaterialCommunityIcons name="cart-outline" size={48} color={theme.colors.onSurfaceVariant} />
            </Surface>
            <Text variant="titleMedium" style={{ fontWeight: '700', marginTop: 14, color: theme.colors.onSurface }}>
              El carrito está vacío
            </Text>
            <Text variant="bodySmall" style={{ color: theme.colors.onSurfaceVariant, textAlign: 'center', marginTop: 4, paddingHorizontal: 30 }}>
              Presiona el botón "Agregar" para seleccionar los productos que el cliente desea comprar.
            </Text>
            <Button
              mode="contained"
              icon="plus"
              onPress={() => setModalProducto(true)}
              style={{ marginTop: 18, borderRadius: 12 }}
            >
              Buscar Productos
            </Button>
          </View>
        }
        renderItem={({ item }) => {
          const maximoAlcanzado = item.cantidad >= stockDisponible(item.producto);
          const subtotalLinea = item.producto.precio * item.cantidad;

          return (
            <Surface style={[styles.lineaCarrito, { backgroundColor: theme.colors.surface }]} elevation={1}>
              <View style={styles.filaLinea}>
                {/* Miniatura de la imagen del producto */}
                {item.producto.imagen_url ? (
                  <Image
                    source={{ uri: resolverImagenUrl(item.producto.imagen_url) || item.producto.imagen_url }}
                    style={styles.fotoCarrito}
                  />
                ) : (
                  <View style={[styles.fotoCarrito, styles.fotoCarritoPlaceholder, { backgroundColor: theme.colors.surfaceVariant }]}>
                    <MaterialCommunityIcons name="package-variant-closed" size={20} color={theme.colors.onSurfaceVariant} />
                  </View>
                )}

                <View style={{ flex: 1, marginLeft: 12 }}>
                  <Text variant="titleMedium" numberOfLines={1} style={{ fontWeight: '700' }}>
                    {item.producto.nombre}
                  </Text>
                  <Text variant="bodySmall" style={{ color: theme.colors.onSurfaceVariant }}>
                    Bs {item.producto.precio.toFixed(2)} c/u · Stock restante: {stockDisponible(item.producto) - item.cantidad}
                  </Text>
                </View>

                {/* Stepper de cantidad (+ / -) */}
                <View style={styles.stepperWrap}>
                  <IconButton
                    icon="minus"
                    size={16}
                    mode="contained-tonal"
                    style={styles.botonStepper}
                    onPress={() => cambiarCantidad(item.producto.id, -1)}
                  />
                  <Text variant="titleMedium" style={styles.cantidadTexto}>
                    {item.cantidad}
                  </Text>
                  <IconButton
                    icon="plus"
                    size={16}
                    mode="contained-tonal"
                    style={styles.botonStepper}
                    disabled={maximoAlcanzado}
                    onPress={() => cambiarCantidad(item.producto.id, 1)}
                  />
                </View>

                {/* Subtotal del ítem */}
                <View style={{ alignItems: 'flex-end', minWidth: 70, marginLeft: 10 }}>
                  <Text variant="titleMedium" style={{ fontWeight: '800', color: theme.colors.primary }}>
                    Bs {subtotalLinea.toFixed(2)}
                  </Text>
                </View>
              </View>
            </Surface>
          );
        }}
      />

      {/* ── PANEL INFERIOR FIJO: RESUMEN DE TOTAL Y BOTÓN DE COBRO ─── */}
      <Surface style={[styles.panelCobro, { backgroundColor: theme.colors.surface }]} elevation={4}>
        <Divider />
        <View style={styles.filaDesglose}>
          <Text variant="bodyMedium" style={{ color: theme.colors.onSurfaceVariant }}>
            Artículos seleccionados:
          </Text>
          <Text variant="bodyMedium" style={{ fontWeight: '700', color: theme.colors.onSurface }}>
            {totalArticulos} unidad(es)
          </Text>
        </View>

        <View style={styles.filaTotal}>
          <Text variant="titleLarge" style={{ fontWeight: '800' }}>
            Total a Cobrar
          </Text>
          <Text variant="headlineMedium" style={{ color: theme.colors.primary, fontWeight: '900' }}>
            Bs {total.toFixed(2)}
          </Text>
        </View>

        <Button
          mode="contained"
          icon="check-circle-outline"
          onPress={registrarVenta}
          loading={enviando}
          disabled={enviando || carrito.length === 0}
          style={styles.botonCobro}
          contentStyle={{ paddingVertical: 6 }}
        >
          Confirmar Venta
        </Button>
      </Surface>

      {/* ── MODAL PARA SELECCIONAR CLIENTE ─────────────────────────── */}
      <ClientePickerModal
        visible={modalCliente}
        onClose={() => setModalCliente(false)}
        onSeleccionar={(c) => {
          haptico.toque();
          setCliente(c);
          setModalCliente(false);
        }}
      />

      {/* ── MODAL PARA SELECCIONAR PRODUCTO ────────────────────────── */}
      <ProductoPickerModal
        visible={modalProducto}
        onClose={() => setModalProducto(false)}
        onSeleccionar={agregarProducto}
      />

      {/* ── NOTIFICACIONES FLOTANTES ───────────────────────────────── */}
      <Snackbar visible={!!mensaje} onDismiss={() => setMensaje(null)} duration={3500}>
        {mensaje}
      </Snackbar>
      <Snackbar
        visible={!!error}
        onDismiss={() => setError(null)}
        duration={4500}
        style={{ backgroundColor: theme.colors.error }}
      >
        {error}
      </Snackbar>
    </View>
  );
}

/**
 * Modal buscador de clientes en tiempo real.
 */
export function ClientePickerModal({
  visible,
  onClose,
  onSeleccionar,
}: {
  visible: boolean;
  onClose: () => void;
  onSeleccionar: (c: Cliente) => void;
}) {
  const theme = useTheme();
  const insets = useSafeAreaInsets();
  const [q, setQ] = useState('');
  const [clientes, setClientes] = useState<Cliente[]>([]);
  const [cargando, setCargando] = useState(false);

  const buscar = useCallback(async (texto: string) => {
    setCargando(true);
    try {
      const resp = await ClientesApi.listar(texto || undefined);
      setClientes(resp.data);
    } finally {
      setCargando(false);
    }
  }, []);

  React.useEffect(() => {
    if (visible) buscar(q);
  }, [visible]);

  React.useEffect(() => {
    if (!visible) return;
    const t = setTimeout(() => buscar(q), 300);
    return () => clearTimeout(t);
  }, [q, visible, buscar]);

  return (
    <Modal visible={visible} animationType="slide" statusBarTranslucent onRequestClose={onClose}>
      <View style={[styles.modalContenedor, {
        paddingTop: insets.top + 8,
        paddingBottom: insets.bottom,
        backgroundColor: theme.colors.background,
      }]}>
        <KeyboardAvoidingView
          behavior={Platform.OS === 'ios' ? 'padding' : 'height'}
          style={{ flex: 1, backgroundColor: theme.colors.background }}
        >
          <View style={styles.modalCabecera}>
            <Text variant="titleLarge" style={{ fontWeight: '800' }}>
              Seleccionar Cliente
            </Text>
            <IconButton icon="close" onPress={onClose} />
          </View>

          <Searchbar
            placeholder="Buscar por nombre o correo..."
            value={q}
            onChangeText={setQ}
            style={{ marginHorizontal: 16, marginBottom: 10, borderRadius: 12 }}
          />

          <FlatList
            data={clientes}
            keyExtractor={(c) => String(c.id)}
            style={{ flex: 1, backgroundColor: theme.colors.background }}
            contentContainerStyle={{ flexGrow: 1, paddingHorizontal: 16 }}
            keyboardShouldPersistTaps="handled"
            keyboardDismissMode={Platform.OS === 'ios' ? 'interactive' : 'on-drag'}
            renderItem={({ item }) => (
              <Surface style={[styles.itemPicker, { backgroundColor: theme.colors.surface }]} elevation={1}>
                <Pressable
                  onPress={() => onSeleccionar(item)}
                  style={styles.filaPicker}
                >
                  <Avatar.Text
                    label={item.nombre.charAt(0).toUpperCase()}
                    size={40}
                    style={{ backgroundColor: theme.colors.primaryContainer }}
                    color={theme.colors.primary}
                  />
                  <View style={{ marginLeft: 12, flex: 1 }}>
                    <Text variant="titleSmall" style={{ fontWeight: '700' }}>
                      {item.nombre}
                    </Text>
                    <Text variant="bodySmall" style={{ color: theme.colors.onSurfaceVariant }}>
                      {item.email}
                    </Text>
                  </View>
                  <MaterialCommunityIcons name="chevron-right" size={20} color={theme.colors.onSurfaceVariant} />
                </Pressable>
              </Surface>
            )}
            ListEmptyComponent={
              cargando ? (
                <ActivityIndicator style={{ marginVertical: 20 }} />
              ) : (
                <Text style={{ textAlign: 'center', marginTop: 30, color: theme.colors.onSurfaceVariant }}>
                  No se encontraron clientes registrados.
                </Text>
              )
            }
          />
        </KeyboardAvoidingView>
      </View>
    </Modal>
  );
}

/**
 * Modal buscador de productos con stock disponible en tiempo real.
 */
export function ProductoPickerModal({
  visible,
  onClose,
  onSeleccionar,
}: {
  visible: boolean;
  onClose: () => void;
  onSeleccionar: (p: Producto) => void;
}) {
  const theme = useTheme();
  const insets = useSafeAreaInsets();
  const [q, setQ] = useState('');
  const [productos, setProductos] = useState<Producto[]>([]);
  const [cargando, setCargando] = useState(false);

  const buscar = useCallback(async (texto: string) => {
    setCargando(true);
    try {
      const resp = await ProductosApi.listar(texto || undefined);
      // Solo permite vender productos que tengan al menos 1 unidad en stock
      setProductos(resp.data.filter((p) => (p.stock_disponible ?? p.stock) > 0));
    } finally {
      setCargando(false);
    }
  }, []);

  React.useEffect(() => {
    if (visible) buscar(q);
  }, [visible]);

  React.useEffect(() => {
    if (!visible) return;
    const t = setTimeout(() => buscar(q), 300);
    return () => clearTimeout(t);
  }, [q, visible, buscar]);

  return (
    <Modal visible={visible} animationType="slide" statusBarTranslucent onRequestClose={onClose}>
      <View style={[styles.modalContenedor, {
        paddingTop: insets.top + 8,
        paddingBottom: insets.bottom,
        backgroundColor: theme.colors.background,
      }]}>
        <KeyboardAvoidingView
          behavior={Platform.OS === 'ios' ? 'padding' : 'height'}
          style={{ flex: 1, backgroundColor: theme.colors.background }}
        >
          <View style={styles.modalCabecera}>
            <Text variant="titleLarge" style={{ fontWeight: '800' }}>
              Agregar al Carrito
            </Text>
            <IconButton icon="close" onPress={onClose} />
          </View>

          <Searchbar
            placeholder="Buscar producto por nombre..."
            value={q}
            onChangeText={setQ}
            style={{ marginHorizontal: 16, marginBottom: 10, borderRadius: 12 }}
          />

          <FlatList
            data={productos}
            keyExtractor={(p) => String(p.id)}
            style={{ flex: 1, backgroundColor: theme.colors.background }}
            contentContainerStyle={{ flexGrow: 1, paddingHorizontal: 16 }}
            keyboardShouldPersistTaps="handled"
            keyboardDismissMode={Platform.OS === 'ios' ? 'interactive' : 'on-drag'}
            renderItem={({ item }) => (
              <Surface style={[styles.itemPicker, { backgroundColor: theme.colors.surface }]} elevation={1}>
                <Pressable
                  onPress={() => onSeleccionar(item)}
                  style={styles.filaPicker}
                >
                  {/* Imagen del producto en el catálogo / buscador */}
                  {item.imagen_url ? (
                    <Image
                      source={{ uri: resolverImagenUrl(item.imagen_url) || item.imagen_url }}
                      style={styles.fotoPicker}
                    />
                  ) : (
                    <View style={[styles.fotoPicker, styles.fotoPickerPlaceholder, { backgroundColor: theme.colors.surfaceVariant }]}>
                      <MaterialCommunityIcons name="image-outline" size={24} color={theme.colors.onSurfaceVariant} />
                    </View>
                  )}
                  <View style={{ marginLeft: 12, flex: 1 }}>
                    <Text variant="titleSmall" style={{ fontWeight: '700' }}>
                      {item.nombre}
                    </Text>
                    <Text variant="bodySmall" style={{ color: theme.colors.onSurfaceVariant }}>
                      Stock disponible: {item.stock_disponible ?? item.stock} unids.
                    </Text>
                  </View>
                  <View style={{ alignItems: 'flex-end' }}>
                    <Text variant="titleMedium" style={{ fontWeight: '800', color: theme.colors.primary }}>
                      Bs {item.precio.toFixed(2)}
                    </Text>
                    <View style={{
                      marginTop: 4,
                      paddingHorizontal: 8,
                      paddingVertical: 2,
                      borderRadius: 8,
                      backgroundColor: (item.stock_disponible ?? item.stock) <= 5 ? '#FEF3C7' : '#DCFCE7',
                    }}>
                      <Text style={{
                        fontSize: 10,
                        fontWeight: '700',
                        color: (item.stock_disponible ?? item.stock) <= 5 ? '#B45309' : '#15803D',
                      }}>
                        {item.stock_disponible ?? item.stock} disponibles
                      </Text>
                    </View>
                  </View>
                </Pressable>
              </Surface>
            )}
            ListEmptyComponent={
              cargando ? (
                <ActivityIndicator style={{ marginVertical: 20 }} />
              ) : (
                <Text style={{ textAlign: 'center', marginTop: 30, color: theme.colors.onSurfaceVariant }}>
                  No hay productos disponibles con stock.
                </Text>
              )
            }
          />
        </KeyboardAvoidingView>
      </View>
    </Modal>
  );
}

/**
 * Hoja de estilos del Punto de Venta.
 */
const styles = StyleSheet.create({
  contenedor: {
    flex: 1,
  },
  lista: {
    padding: 16,
    paddingBottom: 160,
  },
  cabeceraSeccion: {
    marginBottom: 8,
  },
  filaTituloSeccion: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 8,
  },
  tarjetaCliente: {
    padding: 14,
    borderRadius: 16,
  },
  filaCliente: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  selectorClienteVacio: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: 16,
    borderRadius: 16,
    borderWidth: 1.5,
    borderStyle: 'dashed',
    borderColor: '#4F46E5',
    backgroundColor: '#EEF2FF',
  },
  contenedorVacio: {
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 36,
  },
  iconoVacioWrap: {
    width: 80,
    height: 80,
    borderRadius: 24,
    alignItems: 'center',
    justifyContent: 'center',
  },
  lineaCarrito: {
    marginBottom: 10,
    borderRadius: 16,
    padding: 12,
  },
  filaLinea: {
    flexDirection: 'row',
    alignItems: 'center',
    flexWrap: 'wrap',
    gap: 6,
  },
  stepperWrap: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  botonStepper: {
    margin: 0,
    width: 30,
    height: 30,
  },
  cantidadTexto: {
    fontWeight: '800',
    minWidth: 26,
    textAlign: 'center',
  },
  panelCobro: {
    position: 'absolute',
    bottom: 0,
    left: 0,
    right: 0,
    paddingHorizontal: 20,
    paddingTop: 12,
    paddingBottom: 24,
    borderTopLeftRadius: 22,
    borderTopRightRadius: 22,
  },
  filaDesglose: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginBottom: 4,
  },
  filaTotal: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginVertical: 8,
  },
  botonCobro: {
    borderRadius: 14,
    marginTop: 4,
  },
  modalContenedor: {
    flex: 1,
  },
  modalCabecera: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingHorizontal: 18,
    marginBottom: 10,
  },
  itemPicker: {
    borderRadius: 14,
    marginBottom: 8,
    overflow: 'hidden',
  },
  filaPicker: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: 12,
  },
  iconoProductoWrap: {
    width: 42,
    height: 42,
    borderRadius: 12,
    alignItems: 'center',
    justifyContent: 'center',
  },
  fotoCarrito: {
    width: 46,
    height: 46,
    borderRadius: 10,
  },
  fotoCarritoPlaceholder: {
    alignItems: 'center',
    justifyContent: 'center',
  },
  fotoPicker: {
    width: 52,
    height: 52,
    borderRadius: 12,
  },
  fotoPickerPlaceholder: {
    alignItems: 'center',
    justifyContent: 'center',
  },
});
