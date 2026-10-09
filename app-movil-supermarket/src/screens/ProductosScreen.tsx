/**
 * PANTALLA DE GESTIÓN Y CATÁLOGO DE PRODUCTOS (INVENTARIO)
 * 
 * Propósito Académico / Proyecto de Grado:
 * Implementa la interfaz móvil para la visualización, filtrado dinámico, búsqueda
 * en tiempo real y administración completa (CRUD) de los productos del supermercado.
 * 
 * Aspectos de ingeniería de software destacados:
 * 1. Búsqueda con debounce (retardo controlado de 350ms) para minimizar peticiones HTTP innecesarias.
 * 2. Paginación infinita (Infinite Scroll) con carga por lotes para alta eficiencia de memoria.
 * 3. Filtro interactivo por categorías con chips horizontales desplazables.
 * 4. Manejo de imágenes mediante Expo ImagePicker (cámara y galería) con compresión optimizada.
 * 5. Validación preventiva de datos en cliente antes del envío a la API REST.
 */

import React, { useCallback, useEffect, useMemo, useRef, useState } from 'react';
import {
  View,
  StyleSheet,
  FlatList,
  RefreshControl,
  Modal,
  ScrollView,
  Image,
  Alert,
  Pressable,
  KeyboardAvoidingView,
  Platform,
} from 'react-native';
import {
  Text,
  Searchbar,
  Card,
  Chip,
  ActivityIndicator,
  Avatar,
  useTheme,
  FAB,
  IconButton,
  TextInput,
  Button,
  Snackbar,
  HelperText,
  Surface,
} from 'react-native-paper';
import { MaterialCommunityIcons } from '@expo/vector-icons';
import * as ImagePicker from 'expo-image-picker';
import { useFocusEffect } from '@react-navigation/native';
import { CategoriasApi, ProductosApi } from '../api/endpoints';
import { ApiRequestError } from '../api/client';
import { useAuth } from '../context/AuthContext';
import { usePaginado } from '../hooks/usePaginado';
import { EstadoVacio } from '../components/EstadoVacio';
import { SkeletonLista } from '../components/SkeletonLista';
import { haptico } from '../utils/haptico';
import { syncEvents } from '../utils/syncEvents';
import type { Categoria, Producto } from '../api/types';

/**
 * Función adaptadora para vincular el hook genérico de paginación con el endpoint de productos.
 * Soporta búsqueda de texto y número de página.
 */
const fetchProductos = (pagina: number, q?: string) => ProductosApi.listar(q, pagina);

export default function ProductosScreen({ route, navigation }: any) {
  const theme = useTheme();                                // Tema visual actual (paleta clara / oscura)
  const { usuario } = useAuth();                            // Sesión del usuario actual
  const esAdmin = usuario?.rol === 'administrador';        // Control de acceso basado en roles (RBAC)

  // Hook personalizado de paginación que encapsula el estado de carga, acumulación de páginas y scroll infinito
  const { items, cargando, cargandoMas, error, recargar, cargarMas } = usePaginado<Producto>(fetchProductos);
  
  // Estado local del campo de búsqueda textual
  const [busqueda, setBusqueda] = useState('');
  const busquedaRef = useRef('');
  const primeraVez = useRef(true);

  // Lista de categorías para el filtro horizontal superior
  const [categoriasLista, setCategoriasLista] = useState<Categoria[]>([]);
  const [categoriaSeleccionada, setCategoriaSeleccionada] = useState<number | null>(null);

  // Mensajes de notificación flotantes (Snackbar)
  const [mensaje, setMensaje] = useState<string | null>(null);
  const [errorForm, setErrorForm] = useState<string | null>(null);

  // Control del modal de creación/edición: null (cerrado), 'nuevo' (crear), o instancia de Producto (editar)
  const [editando, setEditando] = useState<Producto | 'nuevo' | null>(null);

  /**
   * Apertura reactiva desde Acciones Rápidas (Dashboard) cuando se pasa abrirNuevo: true
   */
  useEffect(() => {
    if (route?.params?.abrirNuevo) {
      setEditando('nuevo');
      if (navigation?.setParams) {
        navigation.setParams({ abrirNuevo: undefined });
      }
    }
  }, [route?.params?.abrirNuevo, navigation]);

  /**
   * Carga inicial de categorías para alimentar la barra de filtros horizontales.
   */
  useEffect(() => {
    CategoriasApi.listar()
      .then((res) => setCategoriasLista(res.data))
      .catch(() => {});
  }, []);

  /**
   * Sincronización en tiempo real sin recargar manualmente:
   * Al crearse, modificarse o eliminarse un producto o categoría desde la página web,
   * se actualiza la lista y las categorías en segundo plano sin parpadeos.
   */
  useEffect(() => {
    const alCambiarProductos = () => {
      recargar(busquedaRef.current || undefined, true);
    };
    const alCambiarCategorias = () => {
      CategoriasApi.listar()
        .then((res) => setCategoriasLista(res.data))
        .catch(() => {});
      recargar(busquedaRef.current || undefined, true);
    };

    syncEvents.on('sync:productos', alCambiarProductos);
    syncEvents.on('sync:categorias', alCambiarCategorias);

    return () => {
      syncEvents.off('sync:productos', alCambiarProductos);
      syncEvents.off('sync:categorias', alCambiarCategorias);
    };
  }, [recargar]);

  /**
   * Refresca los productos cada vez que la pantalla recibe el foco de la navegación.
   * Evita discrepancias de stock si se realizó una venta en otra pestaña.
   */
  useFocusEffect(
    useCallback(() => {
      recargar(busquedaRef.current || undefined);
    }, [recargar])
  );

  /**
   * Implementación de técnica Debounce: Espera 350ms tras la última pulsación de tecla
   * antes de solicitar los datos al backend, optimizando el rendimiento de la red.
   */
  useEffect(() => {
    busquedaRef.current = busqueda;
    if (primeraVez.current) {
      primeraVez.current = false;
      return;
    }
    const temporizador = setTimeout(() => recargar(busqueda || undefined), 350);
    return () => clearTimeout(temporizador);
  }, [busqueda, recargar]);

  /**
   * Filtra los productos localmente si se ha seleccionado un chip de categoría.
   */
  const itemsFiltrados = useMemo(() => {
    return categoriaSeleccionada !== null
      ? items.filter((p) => p.categoria?.id === categoriaSeleccionada)
      : items;
  }, [items, categoriaSeleccionada]);

  return (
    <View style={[styles.contenedor, { backgroundColor: theme.colors.background }]}>
      {/* ── BARRA DE BÚSQUEDA ───────────────────────────────────────── */}
      <Searchbar
        placeholder="Buscar por nombre o descripción..."
        value={busqueda}
        onChangeText={setBusqueda}
        style={[styles.buscador, { backgroundColor: theme.colors.surface }]}
        inputStyle={{ fontSize: 14 }}
      />

      {/* ── BARRA HORIZONTAL DE FILTROS POR CATEGORÍA ──────────────── */}
      {categoriasLista.length > 0 && (
        <View style={styles.contenedorFiltros}>
          <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.scrollFiltros}>
            {/* Chip para ver todas las categorías */}
            <Chip
              selected={categoriaSeleccionada === null}
              onPress={() => {
                haptico.toque();
                setCategoriaSeleccionada(null);
              }}
              style={[
                styles.chipFiltro,
                categoriaSeleccionada === null && { backgroundColor: theme.colors.primaryContainer },
              ]}
              textStyle={{
                color: categoriaSeleccionada === null ? theme.colors.primary : theme.colors.onSurfaceVariant,
                fontWeight: categoriaSeleccionada === null ? '700' : '500',
              }}
            >
              Categorías: Todas
            </Chip>

            {/* Chips por cada categoría registrada */}
            {categoriasLista.map((cat) => {
              const activa = categoriaSeleccionada === cat.id;
              return (
                <Chip
                  key={cat.id}
                  selected={activa}
                  onPress={() => {
                    haptico.toque();
                    setCategoriaSeleccionada(activa ? null : cat.id);
                  }}
                  style={[
                    styles.chipFiltro,
                    activa && { backgroundColor: theme.colors.primaryContainer },
                  ]}
                  textStyle={{
                    color: activa ? theme.colors.primary : theme.colors.onSurfaceVariant,
                    fontWeight: activa ? '700' : '500',
                  }}
                >
                  {cat.nombre}
                </Chip>
              );
            })}
          </ScrollView>
        </View>
      )}

      {/* ── SKELETON DE CARGA INICIAL ──────────────────────────────── */}
      {cargando && items.length === 0 && <SkeletonLista />}

      {/* ── ESTADO DE ERROR DE CONEXIÓN ────────────────────────────── */}
      {error && items.length === 0 && (
        <EstadoVacio
          esError
          icono="wifi-off"
          titulo="Error al cargar productos"
          mensaje={error}
          textoAccion="Reintentar"
          onAccion={() => recargar(busqueda || undefined)}
        />
      )}

      {/* ── LISTADO PRINCIPAL DE PRODUCTOS (FLATLIST) ──────────────── */}
      <FlatList
        data={itemsFiltrados}
        keyExtractor={(p) => String(p.id)}
        contentContainerStyle={styles.lista}
        refreshControl={
          <RefreshControl
            refreshing={cargando && items.length > 0}
            onRefresh={() => recargar(busqueda || undefined)}
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
              icono="package-variant-closed"
              titulo={busqueda || categoriaSeleccionada ? 'Sin resultados coincidentes' : 'Aún no hay productos'}
              mensaje={
                busqueda
                  ? `No encontramos productos para "${busqueda}".`
                  : categoriaSeleccionada
                  ? 'No hay productos en esta categoría.'
                  : esAdmin
                  ? 'Presiona el botón "+" para registrar el primer producto.'
                  : 'El inventario está actualmente vacío.'
              }
            />
          ) : null
        }
        renderItem={({ item }) => {
          // Determina el estado del inventario para estilizar el badge de stock
          const stockDisponible = item.stock_disponible ?? item.stock;
          const agotado = stockDisponible <= 0;
          const stockBajo = stockDisponible > 0 && stockDisponible <= 5;
          const loteProximo = item.lotes?.find((lote) => lote.cantidad > 0 && lote.fecha_vencimiento);
          const fechaHoy = new Date();
          fechaHoy.setHours(0, 0, 0, 0);
          const loteVencido = loteProximo?.fecha_vencimiento
            ? new Date(`${loteProximo.fecha_vencimiento}T00:00:00`) < fechaHoy
            : false;

          return (
            <Card
              style={[styles.tarjeta, { backgroundColor: theme.colors.surface }]}
              mode="contained"
              onPress={
                esAdmin
                  ? () => {
                      haptico.toque();
                      setEditando(item);
                    }
                  : undefined
              }
            >
              <Card.Content style={styles.fila}>
                {/* Imagen del producto o avatar por defecto */}
                <View style={styles.contenedorFoto}>
                  {item.imagen_url ? (
                    <Image source={{ uri: item.imagen_url }} style={styles.fotoProducto} />
                  ) : (
                    <View style={[styles.fotoProducto, styles.fotoPlaceholder, { backgroundColor: theme.colors.surfaceVariant }]}>
                      <MaterialCommunityIcons name="image-outline" size={26} color={theme.colors.onSurfaceVariant} />
                    </View>
                  )}
                  {/* Badge flotante de estado agotado sobre la foto */}
                  {agotado && (
                    <View style={styles.badgeAgotado}>
                      <Text style={styles.textoBadgeAgotado}>AGOTADO</Text>
                    </View>
                  )}
                </View>

                {/* Información textual del producto */}
                <View style={styles.info}>
                  <Text variant="titleMedium" numberOfLines={2} style={{ fontWeight: '700', color: theme.colors.onSurface }}>
                    {item.nombre}
                  </Text>
                  
                  <View style={styles.filaDetalle}>
                    <Text variant="bodySmall" style={{ color: theme.colors.onSurfaceVariant }}>
                      {item.categoria?.nombre ?? 'Sin categoría'}
                    </Text>
                    <Text variant="bodySmall" style={{ color: theme.colors.onSurfaceVariant, marginHorizontal: 4 }}>•</Text>
                    <Text variant="labelSmall" style={{ color: theme.colors.primary, fontWeight: '700' }}>
                      {item.sku}
                    </Text>
                  </View>

                  <View style={styles.filaInferior}>
                    <Text variant="titleMedium" style={styles.precioTexto}>
                      Bs {item.precio.toFixed(2)}
                    </Text>

                    {/* Badge de Stock de Alto Contraste y Claridad */}
                    <View
                      style={[
                        styles.badgeStock,
                        {
                          backgroundColor: agotado ? '#FEF2F2' : stockBajo ? '#FFFBEB' : '#ECFDF5',
                          borderColor: agotado ? '#EF4444' : stockBajo ? '#F59E0B' : '#10B981',
                        },
                      ]}
                    >
                      <MaterialCommunityIcons
                        name={agotado ? 'close-circle' : stockBajo ? 'alert' : 'check-circle'}
                        size={13}
                        color={agotado ? '#DC2626' : stockBajo ? '#D97706' : '#059669'}
                        style={{ marginRight: 4 }}
                      />
                      <Text
                        style={[
                          styles.textoBadgeStock,
                          {
                            color: agotado ? '#B91C1C' : stockBajo ? '#B45309' : '#047857',
                          },
                        ]}
                      >
                        {agotado ? 'Sin stock vigente' : `Stock: ${item.stock} · Vendible: ${stockDisponible}`}
                      </Text>
                    </View>
                  </View>
                  {loteProximo?.fecha_vencimiento && (
                    <Text variant="bodySmall" style={{ color: loteVencido ? '#DC2626' : theme.colors.onSurfaceVariant, marginTop: 5 }}>
                      {loteVencido ? 'Lote vencido' : 'Próximo vencimiento'}: {loteProximo.fecha_vencimiento}
                    </Text>
                  )}
                </View>

                {/* Botones de acción para el Administrador */}
                {esAdmin && (
                  <View style={{ justifyContent: 'center' }}>
                    <IconButton
                      icon="pencil-outline"
                      size={20}
                      onPress={() => {
                        haptico.toque();
                        setEditando(item);
                      }}
                    />
                    <IconButton
                      icon="delete-outline"
                      size={20}
                      iconColor="#EF4444"
                      onPress={() => {
                        haptico.toque();
                        Alert.alert(
                          'Eliminar producto',
                          `¿Estás seguro de enviar "${item.nombre}" a la papelera?`,
                          [
                            { text: 'Cancelar', style: 'cancel' },
                            {
                              text: 'Eliminar',
                              style: 'destructive',
                              onPress: async () => {
                                try {
                                  await ProductosApi.eliminar(item.id);
                                  haptico.exito();
                                  setMensaje('Producto enviado a la papelera.');
                                  recargar(busquedaRef.current || undefined);
                                } catch (e: any) {
                                  haptico.error();
                                  Alert.alert('Error', e?.message ?? 'No se pudo eliminar el producto.');
                                }
                              },
                            },
                          ]
                        );
                      }}
                    />
                  </View>
                )}
              </Card.Content>
            </Card>
          );
        }}
      />

      {/* ── BOTÓN FLOTANTE (FAB) PARA REGISTRAR PRODUCTO ───────────── */}
      {esAdmin && (
        <FAB
          icon="plus"
          style={[styles.fab, { backgroundColor: theme.colors.primary }]}
          label="Nuevo"
          color="#FFFFFF"
          onPress={() => {
            haptico.toque();
            setEditando('nuevo');
          }}
        />
      )}

      {/* ── MODAL DEL FORMULARIO DE PRODUCTO ──────────────────────── */}
      <FormularioProductoModal
        valor={editando}
        onClose={() => setEditando(null)}
        onGuardado={(msg) => {
          haptico.exito();
          setMensaje(msg);
          setEditando(null);
          recargar(busqueda || undefined);
        }}
        onError={(m) => {
          haptico.error();
          setErrorForm(m);
        }}
      />

      {/* ── NOTIFICACIONES SNACKBAR ────────────────────────────────── */}
      <Snackbar visible={!!mensaje} onDismiss={() => setMensaje(null)} duration={3000}>
        {mensaje}
      </Snackbar>
      <Snackbar
        visible={!!errorForm}
        onDismiss={() => setErrorForm(null)}
        duration={5000}
        style={{ backgroundColor: theme.colors.error }}
      >
        {errorForm}
      </Snackbar>
    </View>
  );
}

/**
 * Estructura de la imagen seleccionada por el usuario desde galería o cámara.
 */
interface ImagenElegida {
  uri: string;
  mimeType?: string | null;
  base64?: string | null;
}

/**
 * Modal reutilizable de selección interactiva (Bottom Sheet modal) para categorías.
 * 
 * Concepto Académico:
 * El componente Menu de react-native-paper depende de un Portal montado en la raíz y falla
 * al calcular coordenadas y z-index dentro de un Modal nativo de React Native.
 * Este SelectorModal independiente garantiza renderizado en primer plano, scroll fluido,
 * búsqueda rápida en tiempo real y objetivos táctiles accesibles con respuesta háptica.
 */
function SelectorModal<T extends { id: number; nombre: string }>({
  visible,
  titulo,
  icono,
  items,
  seleccionadoId,
  onSeleccionar,
  onClose,
  opcionNinguno,
}: {
  visible: boolean;
  titulo: string;
  icono: string;
  items: T[];
  seleccionadoId?: number | null;
  onSeleccionar: (item: T | null) => void;
  onClose: () => void;
  opcionNinguno?: string;
}) {
  const theme = useTheme();
  const [busqueda, setBusqueda] = useState('');

  // Limpiar filtro al abrir el modal
  useEffect(() => {
    if (visible) setBusqueda('');
  }, [visible]);

  const filtrados = useMemo(() => {
    if (!busqueda.trim()) return items;
    const q = busqueda.toLowerCase().trim();
    return items.filter((i) => i.nombre.toLowerCase().includes(q));
  }, [items, busqueda]);

  return (
    <Modal visible={visible} animationType="fade" transparent onRequestClose={onClose}>
      <KeyboardAvoidingView
        behavior={Platform.OS === 'ios' ? 'padding' : undefined}
        style={styles.modalFondoOverlay}
      >
        <Pressable style={styles.modalBackdropPressable} onPress={onClose} />
        <View style={[styles.modalSheetCard, { backgroundColor: theme.colors.surface }]}>
          {/* Indicador superior de arrastre */}
          <View style={styles.modalHandleWrap}>
            <View style={[styles.modalHandle, { backgroundColor: theme.colors.outlineVariant }]} />
          </View>

          {/* Cabecera con título e icono temático */}
          <View style={styles.modalSheetHeader}>
            <View style={{ flexDirection: 'row', alignItems: 'center', flex: 1 }}>
              <View style={[styles.modalSheetIconWrap, { backgroundColor: theme.colors.primaryContainer }]}>
                <MaterialCommunityIcons name={icono as any} size={22} color={theme.colors.primary} />
              </View>
              <Text variant="titleMedium" style={{ fontWeight: '800', marginLeft: 12 }}>
                {titulo}
              </Text>
            </View>
            <IconButton icon="close" size={22} onPress={onClose} />
          </View>

          {/* Buscador reactivo cuando la lista tiene más de 3 opciones */}
          {items.length > 3 && (
            <View style={{ paddingHorizontal: 16, marginBottom: 8 }}>
              <Searchbar
                placeholder={`Buscar en ${titulo.toLowerCase()}...`}
                value={busqueda}
                onChangeText={setBusqueda}
                style={{ borderRadius: 12, height: 44, backgroundColor: theme.colors.surfaceVariant }}
                inputStyle={{ minHeight: 0, fontSize: 14 }}
              />
            </View>
          )}

          {/* Lista desplazable de opciones */}
          <FlatList
            data={filtrados}
            keyExtractor={(i) => String(i.id)}
            contentContainerStyle={{ paddingHorizontal: 16, paddingBottom: 24, paddingTop: 4 }}
            ListHeaderComponent={
              opcionNinguno ? (
                <Pressable
                  onPress={() => {
                    haptico.toque();
                    onSeleccionar(null);
                    onClose();
                  }}
                  style={[
                    styles.selectorFilaItem,
                    {
                      backgroundColor: !seleccionadoId ? theme.colors.primaryContainer : theme.colors.surfaceVariant,
                    },
                  ]}
                >
                  <MaterialCommunityIcons
                    name="close-circle-outline"
                    size={22}
                    color={!seleccionadoId ? theme.colors.primary : theme.colors.onSurfaceVariant}
                    style={{ marginRight: 12 }}
                  />
                  <Text
                    variant="bodyMedium"
                    style={{
                      flex: 1,
                      fontWeight: !seleccionadoId ? '800' : '500',
                      color: !seleccionadoId ? theme.colors.primary : theme.colors.onSurface,
                    }}
                  >
                    {opcionNinguno}
                  </Text>
                  {!seleccionadoId && (
                    <MaterialCommunityIcons name="check-circle" size={20} color={theme.colors.primary} />
                  )}
                </Pressable>
              ) : null
            }
            renderItem={({ item }) => {
              const seleccionado = item.id === seleccionadoId;
              return (
                <Pressable
                  onPress={() => {
                    haptico.toque();
                    onSeleccionar(item);
                    onClose();
                  }}
                  style={[
                    styles.selectorFilaItem,
                    {
                      backgroundColor: seleccionado ? theme.colors.primaryContainer : theme.colors.surfaceVariant,
                    },
                  ]}
                >
                  <MaterialCommunityIcons
                    name={icono as any}
                    size={22}
                    color={seleccionado ? theme.colors.primary : theme.colors.onSurfaceVariant}
                    style={{ marginRight: 12 }}
                  />
                  <Text
                    variant="bodyMedium"
                    style={{
                      flex: 1,
                      fontWeight: seleccionado ? '800' : '500',
                      color: seleccionado ? theme.colors.primary : theme.colors.onSurface,
                    }}
                  >
                    {item.nombre}
                  </Text>
                  {seleccionado && (
                    <MaterialCommunityIcons name="check-circle" size={20} color={theme.colors.primary} />
                  )}
                </Pressable>
              );
            }}
            ListEmptyComponent={
              <View style={{ paddingVertical: 36, alignItems: 'center' }}>
                <MaterialCommunityIcons name="database-off-outline" size={36} color={theme.colors.onSurfaceVariant} />
                <Text variant="bodySmall" style={{ color: theme.colors.onSurfaceVariant, marginTop: 8 }}>
                  No se encontraron elementos disponibles.
                </Text>
              </View>
            }
          />
        </View>
      </KeyboardAvoidingView>
    </Modal>
  );
}

/**
 * Componente Modal para Crear o Editar Productos.
 * Incluye validaciones previas de formulario y selector de fotos con recorte 1:1.
 */
function FormularioProductoModal({
  valor,
  onClose,
  onGuardado,
  onError,
}: {
  valor: Producto | 'nuevo' | null;
  onClose: () => void;
  onGuardado: (mensaje: string) => void;
  onError: (msg: string) => void;
}) {
  const theme = useTheme();

  // Campos de formulario
  const [nombre, setNombre] = useState('');
  const [precio, setPrecio] = useState('');
  const [stock, setStock] = useState('');
  const [fechaVencimiento, setFechaVencimiento] = useState('');
  const [categorias, setCategorias] = useState<Categoria[]>([]);
  const [categoria, setCategoria] = useState<Categoria | null>(null);

  // Modal interactivo de selección de categoría
  const [mostrarPickerCategoria, setMostrarPickerCategoria] = useState(false);
  const [guardando, setGuardando] = useState(false);
  const [imagenNueva, setImagenNueva] = useState<ImagenElegida | null>(null);
  const [quitarImagen, setQuitarImagen] = useState(false);

  // Errores de validación en tiempo real
  const [errorNombre, setErrorNombre] = useState<string | null>(null);
  const [errorPrecio, setErrorPrecio] = useState<string | null>(null);
  const [errorStock, setErrorStock] = useState<string | null>(null);
  const [errorCat, setErrorCat] = useState<string | null>(null);
  const [errorFecha, setErrorFecha] = useState<string | null>(null);

  const imagenActual = valor && valor !== 'nuevo' ? valor.imagen_url : null;
  const vistaPrevia = imagenNueva?.uri ?? (quitarImagen ? null : imagenActual);

  /**
   * Sincroniza el formulario cuando cambia el valor de edición ('nuevo' o producto existente).
   */
  useEffect(() => {
    if (!valor) return;
    CategoriasApi.listar().then((r) => setCategorias(r.data)).catch(() => {});
    setImagenNueva(null);
    setQuitarImagen(false);
    setErrorNombre(null);
    setErrorPrecio(null);
    setErrorStock(null);
    setErrorCat(null);
    setErrorFecha(null);

    if (valor === 'nuevo') {
      setNombre('');
      setPrecio('');
      setStock('');
      setFechaVencimiento('');
      setCategoria(null);
    } else {
      setNombre(valor.nombre);
      setPrecio(String(valor.precio));
      setStock(String(valor.stock));
      setFechaVencimiento('');
      setCategoria(
        valor.categoria
          ? { id: valor.categoria.id, nombre: valor.categoria.nombre, descripcion: null }
          : null
      );
    }
  }, [valor]);

  /**
   * Abre la galería del teléfono para escoger una imagen con proporción cuadrada.
   */
  async function elegirDeGaleria() {
    const permiso = await ImagePicker.requestMediaLibraryPermissionsAsync();
    if (!permiso.granted) {
      Alert.alert('Permiso necesario', 'Permite el acceso a tus fotos para elegir una imagen del producto.');
      return;
    }
    const r = await ImagePicker.launchImageLibraryAsync({
      mediaTypes: ['images'],
      allowsEditing: true,
      aspect: [1, 1],
      quality: 0.7,
      base64: true,
    });
    if (!r.canceled && r.assets[0]) {
      setImagenNueva({
        uri: r.assets[0].uri,
        mimeType: r.assets[0].mimeType,
        base64: r.assets[0].base64,
      });
      setQuitarImagen(false);
    }
  }

  /**
   * Abre la cámara del dispositivo para tomar una fotografía al producto físico.
   */
  async function tomarFoto() {
    const permiso = await ImagePicker.requestCameraPermissionsAsync();
    if (!permiso.granted) {
      Alert.alert('Permiso necesario', 'Permite el acceso a la cámara para capturar la fotografía del producto.');
      return;
    }
    const r = await ImagePicker.launchCameraAsync({
      allowsEditing: true,
      aspect: [1, 1],
      quality: 0.7,
      base64: true,
    });
    if (!r.canceled && r.assets[0]) {
      setImagenNueva({
        uri: r.assets[0].uri,
        mimeType: r.assets[0].mimeType,
        base64: r.assets[0].base64,
      });
      setQuitarImagen(false);
    }
  }

  /**
   * Ejecuta la validación exhaustiva de los campos antes de disparar la petición HTTP.
   */
  function validarFormulario(): boolean {
    let valido = true;
    setErrorNombre(null);
    setErrorPrecio(null);
    setErrorStock(null);
    setErrorCat(null);
    setErrorFecha(null);

    // Validación de Nombre
    if (!nombre.trim()) {
      setErrorNombre('El nombre del producto es obligatorio.');
      valido = false;
    } else if (nombre.trim().length < 2) {
      setErrorNombre('El nombre debe tener al menos 2 caracteres.');
      valido = false;
    }

    // Validación de Precio
    const precioNum = parseFloat(precio.replace(',', '.'));
    if (!precio.trim() || isNaN(precioNum)) {
      setErrorPrecio('Ingresa un precio válido (ej: 15.50).');
      valido = false;
    } else if (precioNum < 0) {
      setErrorPrecio('El precio no puede ser negativo.');
      valido = false;
    }

    // Validación de Stock
    const stockNum = parseInt(stock, 10);
    if (!stock.trim() || isNaN(stockNum)) {
      setErrorStock('Ingresa una cantidad de stock entera.');
      valido = false;
    } else if (stockNum < 0) {
      setErrorStock('El stock no puede ser un valor negativo.');
      valido = false;
    }

    if (fechaVencimiento.trim()) {
      const [anio, mes, dia] = fechaVencimiento.split('-').map(Number);
      const fecha = new Date(anio, mes - 1, dia);
      const hoy = new Date();
      const fechaValida = /^\d{4}-\d{2}-\d{2}$/.test(fechaVencimiento)
        && !Number.isNaN(fecha.getTime())
        && fecha.getFullYear() === anio
        && fecha.getMonth() === mes - 1
        && fecha.getDate() === dia;
      if (!fechaValida || fecha < new Date(hoy.getFullYear(), hoy.getMonth(), hoy.getDate())) {
        setErrorFecha('La fecha debe ser válida y no puede estar en el pasado.');
        valido = false;
      }
    }

    // Validación de Categoría
    if (!categoria) {
      setErrorCat('Debes seleccionar una categoría obligatoria.');
      valido = false;
    }

    return valido;
  }

  /**
   * Procesa el guardado del producto y la posterior subida/eliminación de su imagen.
   */
  async function guardar() {
    if (!validarFormulario()) {
      onError('Corrige los campos marcados en rojo.');
      return;
    }

    setGuardando(true);
    let idProducto: number | null = null;
    const precioNum = parseFloat(precio.replace(',', '.'));

    try {
      const payload = {
        nombre: nombre.trim(),
        precio: precioNum,
        stock: parseInt(stock, 10),
        idCategoria: categoria!.id,
        fecha_vencimiento: fechaVencimiento.trim() || null,
      };

      if (valor === 'nuevo') {
        const { data } = await ProductosApi.crear(payload);
        idProducto = data.id;
      } else if (valor) {
        await ProductosApi.actualizar(valor.id, payload);
        idProducto = valor.id;
      }
    } catch (e) {
      onError(e instanceof ApiRequestError ? e.message : 'Error al guardar el producto.');
      setGuardando(false);
      return;
    }

    // Gestión asíncrona de la imagen física (base64 JSON o multipart)
    try {
      if (idProducto && imagenNueva) {
        await ProductosApi.subirImagen(idProducto, imagenNueva.uri, imagenNueva.mimeType, imagenNueva.base64);
      } else if (idProducto && quitarImagen && imagenActual) {
        await ProductosApi.quitarImagen(idProducto);
      }
    } catch (e) {
      onError(`Producto guardado, pero ocurrió un problema con la foto: ${e instanceof ApiRequestError ? e.message : 'error de red'}`);
    }

    setGuardando(false);
    onGuardado(valor === 'nuevo' ? 'Producto creado exitosamente.' : 'Producto actualizado exitosamente.');
  }

  return (
    <Modal visible={!!valor} animationType="slide" onRequestClose={onClose}>
      <View style={{ flex: 1, backgroundColor: theme.colors.background }}>
        <KeyboardAvoidingView
          behavior={Platform.OS === 'ios' ? 'padding' : undefined}
          style={{ flex: 1, backgroundColor: theme.colors.background }}
          keyboardVerticalOffset={Platform.OS === 'ios' ? 0 : 0}
        >
          <ScrollView
            style={{ flex: 1, backgroundColor: theme.colors.background }}
            contentContainerStyle={[styles.modalPantalla, { flexGrow: 1, backgroundColor: theme.colors.background }]}
            keyboardShouldPersistTaps="handled"
          >
          <Text variant="headlineSmall" style={styles.modalTitulo}>
            {valor === 'nuevo' ? 'Nuevo Producto' : 'Editar Producto'}
          </Text>

          {/* ── SELECCIÓN Y VISTA PREVIA DE IMAGEN ─────────────────────── */}
          <Surface style={[styles.imagenWrap, { backgroundColor: theme.colors.surface }]} elevation={1}>
            {vistaPrevia ? (
              <Image source={{ uri: vistaPrevia }} style={styles.imagenPrevia} />
            ) : (
              <View style={[styles.imagenPrevia, styles.imagenVacia, { backgroundColor: theme.colors.surfaceVariant }]}>
                <MaterialCommunityIcons name="image-plus" size={40} color={theme.colors.onSurfaceVariant} />
                <Text variant="bodySmall" style={{ color: theme.colors.onSurfaceVariant, marginTop: 4 }}>
                  Sin fotografía
                </Text>
              </View>
            )}

            <View style={styles.botonesImagen}>
              <Button compact mode="outlined" icon="image-multiple" onPress={elegirDeGaleria}>
                Galería
              </Button>
              <Button compact mode="outlined" icon="camera" onPress={tomarFoto}>
                Cámara
              </Button>
              {vistaPrevia && (
                <Button
                  compact
                  textColor={theme.colors.error}
                  icon="close"
                  onPress={() => {
                    setImagenNueva(null);
                    setQuitarImagen(true);
                  }}
                >
                  Quitar
                </Button>
              )}
            </View>
          </Surface>

          {/* ── CAMPOS DE TEXTO CON VALIDACIÓN VISUAL ──────────────────── */}
          <TextInput
            mode="outlined"
            label="Nombre del producto *"
            value={nombre}
            onChangeText={(v) => {
              setNombre(v);
              if (errorNombre) setErrorNombre(null);
            }}
            error={!!errorNombre}
            style={[styles.campo, { backgroundColor: theme.colors.surface }]}
            textColor={theme.colors.onSurface}
          />
          {errorNombre && <HelperText type="error">{errorNombre}</HelperText>}

          <TextInput
            mode="outlined"
            label="Precio en Bolivianos (Bs) *"
            value={precio}
            onChangeText={(v) => {
              setPrecio(v);
              if (errorPrecio) setErrorPrecio(null);
            }}
            keyboardType="decimal-pad"
            error={!!errorPrecio}
            style={[styles.campo, { backgroundColor: theme.colors.surface }]}
            textColor={theme.colors.onSurface}
          />
          {errorPrecio && <HelperText type="error">{errorPrecio}</HelperText>}

          <TextInput
            mode="outlined"
            label="Cantidad en Stock *"
            value={stock}
            onChangeText={(v) => {
              setStock(v);
              if (errorStock) setErrorStock(null);
            }}
            keyboardType="number-pad"
            error={!!errorStock}
            style={[styles.campo, { backgroundColor: theme.colors.surface }]}
            textColor={theme.colors.onSurface}
          />
          {errorStock && <HelperText type="error">{errorStock}</HelperText>}

          {/* ── SELECTOR DE CATEGORÍA CON MODAL INTERACTIVO ────────────── */}
          <Pressable
          onPress={() => {
            haptico.toque();
            // Carga preventiva si aún no han llegado
            if (categorias.length === 0) {
              CategoriasApi.listar().then((r) => setCategorias(r.data)).catch(() => {});
            }
            setMostrarPickerCategoria(true);
          }}
          style={[
            styles.campoSelector,
            {
              borderColor: errorCat ? theme.colors.error : theme.colors.outline,
              backgroundColor: theme.colors.surface,
            },
          ]}
        >
          <View style={[styles.selectorIcono, { backgroundColor: theme.colors.primaryContainer }]}>
            <MaterialCommunityIcons name="shape-outline" size={22} color={theme.colors.primary} />
          </View>
          <View style={{ flex: 1, marginLeft: 12 }}>
            <Text variant="labelSmall" style={{ color: errorCat ? theme.colors.error : theme.colors.onSurfaceVariant }}>
              Categoría del producto *
            </Text>
            <Text
              variant="bodyMedium"
              style={{
                fontWeight: categoria ? '700' : '500',
                color: categoria ? theme.colors.onSurface : theme.colors.onSurfaceVariant,
                marginTop: 2,
              }}
            >
              {categoria ? categoria.nombre : 'Toca para seleccionar categoría'}
            </Text>
          </View>
          <MaterialCommunityIcons name="chevron-down" size={22} color={theme.colors.onSurfaceVariant} />
        </Pressable>
        {errorCat && <HelperText type="error" visible={true}>{errorCat}</HelperText>}

          <TextInput
            mode="outlined"
            label="Vencimiento del lote (opcional)"
            value={fechaVencimiento}
            onChangeText={setFechaVencimiento}
            placeholder="AAAA-MM-DD"
            keyboardType="numbers-and-punctuation"
            style={[styles.campo, { backgroundColor: theme.colors.surface }]}
            textColor={theme.colors.onSurface}
          />
          <HelperText type="info">
            Si agregas stock, se registra como un lote con esta fecha. Déjalo vacío para productos sin vencimiento.
          </HelperText>
          {errorFecha && <HelperText type="error">{errorFecha}</HelperText>}

        {/* ── BOTONES DE ACCIÓN (CANCELAR / GUARDAR) ────────────────── */}
        <View style={styles.filaBotones}>
          <Button onPress={onClose} disabled={guardando}>
            Cancelar
          </Button>
          <Button mode="contained" onPress={guardar} loading={guardando} disabled={guardando}>
            Guardar Producto
          </Button>
        </View>

        {/* ── DIÁLOGO DE SELECCIÓN DE CATEGORÍA ── */}
        <SelectorModal
          visible={mostrarPickerCategoria}
          titulo="Seleccionar Categoría"
          icono="shape-outline"
          items={categorias}
          seleccionadoId={categoria?.id}
          onSeleccionar={(cat) => {
            setCategoria(cat);
            if (cat) setErrorCat(null);
          }}
          onClose={() => setMostrarPickerCategoria(false)}
        />

        </ScrollView>
      </KeyboardAvoidingView>
    </View>
  </Modal>
);
}

/**
 * Hoja de estilos de la pantalla de catálogo.
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
  contenedorFiltros: {
    marginBottom: 6,
  },
  scrollFiltros: {
    paddingHorizontal: 16,
    gap: 8,
    paddingVertical: 4,
  },
  chipFiltro: {
    borderRadius: 20,
  },
  lista: {
    paddingHorizontal: 16,
    paddingBottom: 90,
  },
  tarjeta: {
    marginTop: 10,
    borderRadius: 16,
    elevation: 1,
  },
  fila: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: 10,
    paddingHorizontal: 12,
  },
  contenedorFoto: {
    position: 'relative',
  },
  fotoProducto: {
    width: 68,
    height: 68,
    borderRadius: 14,
  },
  fotoPlaceholder: {
    alignItems: 'center',
    justifyContent: 'center',
  },
  badgeAgotado: {
    position: 'absolute',
    bottom: 0,
    left: 0,
    right: 0,
    backgroundColor: 'rgba(220, 38, 38, 0.9)',
    borderBottomLeftRadius: 14,
    borderBottomRightRadius: 14,
    paddingVertical: 2,
    alignItems: 'center',
  },
  textoBadgeAgotado: {
    color: '#FFFFFF',
    fontSize: 9,
    fontWeight: '900',
  },
  info: {
    marginLeft: 14,
    flex: 1,
  },
  filaDetalle: {
    flexDirection: 'row',
    alignItems: 'center',
    marginTop: 2,
  },
  filaInferior: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    marginTop: 6,
    flexWrap: 'wrap',
    gap: 6,
  },
  precioTexto: {
    fontWeight: '800',
    color: '#4F46E5',
    fontSize: 16,
  },
  badgeStock: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 8,
    paddingVertical: 3.5,
    borderRadius: 8,
    borderWidth: 1.5,
  },
  textoBadgeStock: {
    fontSize: 12,
    fontWeight: '800',
    letterSpacing: 0.2,
  },
  fab: {
    position: 'absolute',
    right: 18,
    bottom: 20,
    borderRadius: 16,
    elevation: 4,
  },
  modalPantalla: {
    padding: 22,
    paddingTop: 50,
    paddingBottom: 60,
  },
  modalTitulo: {
    fontWeight: '800',
    marginBottom: 16,
  },
  imagenWrap: {
    alignItems: 'center',
    padding: 16,
    borderRadius: 18,
    marginBottom: 18,
  },
  imagenPrevia: {
    width: 140,
    height: 140,
    borderRadius: 16,
  },
  imagenVacia: {
    alignItems: 'center',
    justifyContent: 'center',
  },
  botonesImagen: {
    flexDirection: 'row',
    gap: 8,
    marginTop: 12,
    flexWrap: 'wrap',
    justifyContent: 'center',
  },
  campo: {
    marginBottom: 10,
  },
  filaBotones: {
    flexDirection: 'row',
    justifyContent: 'flex-end',
    gap: 10,
    marginTop: 18,
  },
  campoSelector: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: 12,
    paddingHorizontal: 14,
    borderRadius: 12,
    borderWidth: 1,
    marginTop: 6,
    marginBottom: 6,
  },
  selectorIcono: {
    width: 38,
    height: 38,
    borderRadius: 10,
    alignItems: 'center',
    justifyContent: 'center',
  },
  modalFondoOverlay: {
    flex: 1,
    backgroundColor: 'rgba(0, 0, 0, 0.55)',
    justifyContent: 'flex-end',
  },
  modalBackdropPressable: {
    flex: 1,
  },
  modalSheetCard: {
    borderTopLeftRadius: 24,
    borderTopRightRadius: 24,
    maxHeight: '75%',
    paddingBottom: 20,
    elevation: 8,
  },
  modalHandleWrap: {
    alignItems: 'center',
    paddingVertical: 10,
  },
  modalHandle: {
    width: 40,
    height: 4,
    borderRadius: 2,
  },
  modalSheetHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: 18,
    paddingBottom: 10,
  },
  modalSheetIconWrap: {
    width: 36,
    height: 36,
    borderRadius: 10,
    alignItems: 'center',
    justifyContent: 'center',
  },
  selectorFilaItem: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: 14,
    paddingHorizontal: 16,
    borderRadius: 14,
    marginBottom: 6,
  },
});
