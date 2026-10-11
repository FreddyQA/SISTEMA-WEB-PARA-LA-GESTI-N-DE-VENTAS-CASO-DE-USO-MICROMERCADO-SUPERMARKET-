/**
 * PANTALLA DE GESTIÓN DE CLIENTES Y CONTACTO COMERCIAL
 * 
 * Propósito Académico / Proyecto de Grado:
 * Administra el padrón de clientes del supermercado. Integra capacidades nativas
 * del dispositivo móvil como marcado telefónico directo y enlaces a chats de WhatsApp
 * con normalización automática de prefijo internacional (+591 para Bolivia).
 * 
 * Aspectos de seguridad y reglas de negocio:
 * 1. Control de Integridad Referencial: No permite eliminar clientes que posean historial de ventas.
 * 2. Validación en cliente de correos electrónicos y teléfonos antes de la petición REST.
 * 3. Soft Deletes y restauración transparente en la base de datos Laravel.
 */

import React, { useCallback, useEffect, useRef, useState } from 'react';
import { View, StyleSheet, FlatList, RefreshControl, Modal, ScrollView, Linking, Alert, Pressable, KeyboardAvoidingView, Platform } from 'react-native';
import {
  Text,
  Searchbar,
  Card,
  Avatar,
  IconButton,
  FAB,
  TextInput,
  Button,
  Portal,
  Dialog,
  Snackbar,
  ActivityIndicator,
  useTheme,
  HelperText,
  Surface,
} from 'react-native-paper';
import { MaterialCommunityIcons } from '@expo/vector-icons';
import { useFocusEffect } from '@react-navigation/native';
import { ClientesApi } from '../api/endpoints';
import { ApiRequestError } from '../api/client';
import { useAuth } from '../context/AuthContext';
import { usePaginado } from '../hooks/usePaginado';
import { EstadoVacio } from '../components/EstadoVacio';
import { SkeletonLista } from '../components/SkeletonLista';
import { haptico } from '../utils/haptico';
import type { Cliente } from '../api/types';

/**
 * Adaptador para listar clientes con paginación y búsqueda.
 */
const fetchClientes = (pagina: number, q?: string) => ClientesApi.listar(q, pagina);

/**
 * Normaliza números locales bolivianos (8 dígitos) anteponiendo el código de país 591.
 * Si ya tiene signo +, preserva el formato internacional.
 */
function numeroInternacional(telefono: string): string {
  const digitos = telefono.replace(/[^\d]/g, '');
  if (telefono.trim().startsWith('+')) return digitos;
  return digitos.length === 8 ? `591${digitos}` : digitos;
}

export default function ClientesScreen({ route, navigation }: any) {
  const theme = useTheme();                                // Paleta del sistema activo
  const { usuario } = useAuth();                            // Sesión activa
  const esAdmin = usuario?.rol === 'administrador';        // Control de permisos de Administrador

  // Paginación y control de listado
  const { items, cargando, cargandoMas, error, recargar, cargarMas } = usePaginado<Cliente>(fetchClientes);
  const [busqueda, setBusqueda] = useState('');
  const busquedaRef = useRef('');
  const primeraVez = useRef(true);

  // Estados de formularios y acciones
  const [editando, setEditando] = useState<Cliente | 'nuevo' | null>(null);
  const [aEliminar, setAEliminar] = useState<Cliente | null>(null);
  const [mensaje, setMensaje] = useState<string | null>(null);
  const [errorMsg, setErrorMsg] = useState<string | null>(null);

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

  useFocusEffect(
    useCallback(() => {
      recargar(busquedaRef.current || undefined);
    }, [recargar])
  );

  /**
   * Debounce en la búsqueda de clientes (350 ms).
   */
  useEffect(() => {
    busquedaRef.current = busqueda;
    if (primeraVez.current) {
      primeraVez.current = false;
      return;
    }
    const t = setTimeout(() => recargar(busqueda || undefined), 350);
    return () => clearTimeout(t);
  }, [busqueda, recargar]);

  /**
   * Elimina un cliente previa confirmación.
   */
  async function eliminar() {
    if (!aEliminar) return;
    try {
      await ClientesApi.eliminar(aEliminar.id);
      haptico.exito();
      setMensaje(`Cliente "${aEliminar.nombre}" eliminado correctamente.`);
      setAEliminar(null);
      recargar(busqueda || undefined);
    } catch (e) {
      haptico.error();
      setErrorMsg(e instanceof ApiRequestError ? e.message : 'No se pudo eliminar al cliente.');
      setAEliminar(null);
    }
  }

  /**
   * Dispara el marcador nativo del smartphone (tel:...).
   */
  function llamar(telefono: string) {
    haptico.toque();
    Linking.openURL(`tel:${telefono.replace(/[^\d+]/g, '')}`).catch(() =>
      Alert.alert('Llamada no disponible', 'Este dispositivo no cuenta con aplicación de telefonía instalada.')
    );
  }

  /**
   * Abre la conversación de WhatsApp con el número del cliente (wa.me/...).
   */
  function whatsapp(telefono: string) {
    haptico.toque();
    Linking.openURL(`https://wa.me/${numeroInternacional(telefono)}`).catch(() =>
      Alert.alert('WhatsApp no disponible', 'Verifica que la aplicación de WhatsApp esté instalada en el dispositivo.')
    );
  }

  return (
    <View style={[styles.contenedor, { backgroundColor: theme.colors.background }]}>
      {/* ── BARRA DE BÚSQUEDA ───────────────────────────────────────── */}
      <Searchbar
        placeholder="Buscar por nombre, correo o teléfono..."
        value={busqueda}
        onChangeText={setBusqueda}
        style={[styles.buscador, { backgroundColor: theme.colors.surface }]}
        inputStyle={{ fontSize: 14 }}
      />

      {/* ── SKELETON DE CARGA ──────────────────────────────────────── */}
      {cargando && items.length === 0 && <SkeletonLista />}

      {/* ── MENSAJE DE ERROR ───────────────────────────────────────── */}
      {error && items.length === 0 && (
        <EstadoVacio
          esError
          icono="wifi-off"
          titulo="Error al cargar clientes"
          mensaje={error}
          textoAccion="Reintentar"
          onAccion={() => recargar(busqueda || undefined)}
        />
      )}

      {/* ── LISTADO PRINCIPAL DE CLIENTES ──────────────────────────── */}
      <FlatList
        data={items}
        keyExtractor={(c) => String(c.id)}
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
              icono="account-group-outline"
              titulo={busqueda ? 'Sin clientes encontrados' : 'No hay clientes registrados'}
              mensaje={
                busqueda
                  ? `No encontramos coincidencias para "${busqueda}".`
                  : 'Presiona "+" para registrar a tu primer cliente.'
              }
            />
          ) : null
        }
        renderItem={({ item }) => (
          <Surface style={[styles.tarjeta, { backgroundColor: theme.colors.surface }]} elevation={1}>
            <View style={styles.filaTarjeta}>
              <Avatar.Text
                label={item.nombre.charAt(0).toUpperCase()}
                size={48}
                style={{ backgroundColor: theme.colors.primaryContainer }}
                color={theme.colors.primary}
              />

              <View style={{ flex: 1, marginLeft: 14 }}>
                <Text variant="titleMedium" style={{ fontWeight: '700', color: theme.colors.onSurface }} numberOfLines={1}>
                  {item.nombre}
                </Text>
                <Text variant="bodySmall" style={{ color: theme.colors.onSurfaceVariant }} numberOfLines={1}>
                  {item.email}
                </Text>
                {item.direccion && (
                  <Text variant="bodySmall" style={{ color: theme.colors.onSurfaceVariant, opacity: 0.8, marginTop: 2 }} numberOfLines={1}>
                    Dir: {item.direccion}
                  </Text>
                )}
              </View>

              {/* Botones de llamada y WhatsApp directo si posee teléfono */}
              {item.telefono && (
                <View style={styles.filaContacto}>
                  <IconButton
                    icon="phone-outline"
                    size={20}
                    mode="contained-tonal"
                    onPress={() => llamar(item.telefono!)}
                    style={styles.btnContacto}
                  />
                  <IconButton
                    icon="whatsapp"
                    size={20}
                    mode="contained-tonal"
                    iconColor="#15803D"
                    style={[styles.btnContacto, { backgroundColor: '#DCFCE7' }]}
                    onPress={() => whatsapp(item.telefono!)}
                  />
                </View>
              )}
            </View>

            {/* Opciones de edición y borrado exclusivas para Administrador */}
            {esAdmin && (
              <View style={styles.filaAccionesAdmin}>
                <Button
                  compact
                  mode="text"
                  icon="pencil-outline"
                  onPress={() => {
                    haptico.toque();
                    setEditando(item);
                  }}
                >
                  Editar
                </Button>
                <Button
                  compact
                  mode="text"
                  textColor={theme.colors.error}
                  icon="trash-can-outline"
                  onPress={() => {
                    haptico.toque();
                    setAEliminar(item);
                  }}
                >
                  Eliminar
                </Button>
              </View>
            )}
          </Surface>
        )}
      />

      {/* Botón flotante para dar de alta nuevo cliente */}
      <FAB
        icon="plus"
        style={[styles.fab, { backgroundColor: theme.colors.primary }]}
        color="#FFFFFF"
        label="Nuevo"
        onPress={() => {
          haptico.toque();
          setEditando('nuevo');
        }}
      />

      {/* Modal de formulario para crear/editar cliente */}
      <FormularioClienteModal
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
          setErrorMsg(m);
        }}
      />

      {/* Diálogo de confirmación para eliminar cliente */}
      <Portal>
        <Dialog visible={!!aEliminar} onDismiss={() => setAEliminar(null)}>
          <Dialog.Title>¿Eliminar cliente?</Dialog.Title>
          <Dialog.Content>
            <Text variant="bodyMedium">
              ¿Estás seguro de que deseas eliminar a "{aEliminar?.nombre}"? El sistema no permitirá la eliminación
              si tiene ventas asociados.
            </Text>
          </Dialog.Content>
          <Dialog.Actions>
            <Button onPress={() => setAEliminar(null)}>Cancelar</Button>
            <Button textColor={theme.colors.error} onPress={eliminar}>
              Eliminar
            </Button>
          </Dialog.Actions>
        </Dialog>
      </Portal>

      <Snackbar visible={!!mensaje} onDismiss={() => setMensaje(null)} duration={3000}>
        {mensaje}
      </Snackbar>
      <Snackbar
        visible={!!errorMsg}
        onDismiss={() => setErrorMsg(null)}
        duration={5000}
        style={{ backgroundColor: theme.colors.error }}
      >
        {errorMsg}
      </Snackbar>
    </View>
  );
}

/**
 * Modal para dar de alta o actualizar un cliente con validación en cliente.
 */
function FormularioClienteModal({
  valor,
  onClose,
  onGuardado,
  onError,
}: {
  valor: Cliente | 'nuevo' | null;
  onClose: () => void;
  onGuardado: (msg: string) => void;
  onError: (msg: string) => void;
}) {
  const theme = useTheme();
  const [nombre, setNombre] = useState('');
  const [email, setEmail] = useState('');
  const [telefono, setTelefono] = useState('');
  const [direccion, setDireccion] = useState('');
  const [guardando, setGuardando] = useState(false);

  // Errores de validación
  const [errorNombre, setErrorNombre] = useState<string | null>(null);
  const [errorEmail, setErrorEmail] = useState<string | null>(null);

  useEffect(() => {
    if (!valor) return;
    setErrorNombre(null);
    setErrorEmail(null);

    if (valor === 'nuevo') {
      setNombre('');
      setEmail('');
      setTelefono('');
      setDireccion('');
    } else {
      setNombre(valor.nombre);
      setEmail(valor.email);
      setTelefono(valor.telefono ?? '');
      setDireccion(valor.direccion ?? '');
    }
  }, [valor]);

  function validar(): boolean {
    let valido = true;
    setErrorNombre(null);
    setErrorEmail(null);

    if (!nombre.trim()) {
      setErrorNombre('El nombre del cliente es obligatorio.');
      valido = false;
    } else if (nombre.trim().length < 2) {
      setErrorNombre('El nombre debe tener al menos 2 caracteres.');
      valido = false;
    }

    const regexEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!email.trim()) {
      setErrorEmail('El correo electrónico es obligatorio.');
      valido = false;
    } else if (!regexEmail.test(email.trim())) {
      setErrorEmail('Ingresa un formato de correo electrónico válido.');
      valido = false;
    }

    return valido;
  }

  async function guardar() {
    if (!validar()) {
      onError('Corrige los campos marcados en rojo.');
      return;
    }

    setGuardando(true);
    const datos = {
      nombre: nombre.trim(),
      email: email.trim(),
      telefono: telefono.trim() || undefined,
      direccion: direccion.trim() || undefined,
    };

    try {
      if (valor === 'nuevo') {
        await ClientesApi.crear(datos);
      } else if (valor) {
        await ClientesApi.actualizar(valor.id, datos);
      }
      onGuardado(valor === 'nuevo' ? 'Cliente creado exitosamente.' : 'Cliente actualizado exitosamente.');
    } catch (e) {
      onError(e instanceof ApiRequestError ? e.message : 'Error al guardar el cliente.');
    } finally {
      setGuardando(false);
    }
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
            <Text variant="headlineSmall" style={{ fontWeight: '800', marginBottom: 20 }}>
              {valor === 'nuevo' ? 'Nuevo Cliente' : 'Editar Cliente'}
            </Text>

            <TextInput
              mode="outlined"
              label="Nombre o Razón Social *"
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
              label="Correo Electrónico *"
              value={email}
              onChangeText={(v) => {
                setEmail(v);
                if (errorEmail) setErrorEmail(null);
              }}
              keyboardType="email-address"
              autoCapitalize="none"
              error={!!errorEmail}
              style={[styles.campo, { backgroundColor: theme.colors.surface }]}
              textColor={theme.colors.onSurface}
            />
            {errorEmail && <HelperText type="error">{errorEmail}</HelperText>}

            <TextInput
              mode="outlined"
              label="Teléfono / WhatsApp (ej: 70123456)"
              value={telefono}
              onChangeText={setTelefono}
              keyboardType="phone-pad"
              style={[styles.campo, { backgroundColor: theme.colors.surface }]}
              textColor={theme.colors.onSurface}
            />

            <TextInput
              mode="outlined"
              label="Dirección de Domicilio o Entrega"
              value={direccion}
              onChangeText={setDireccion}
              multiline
              numberOfLines={2}
              style={[styles.campo, { backgroundColor: theme.colors.surface }]}
              textColor={theme.colors.onSurface}
            />

            <View style={styles.filaBotones}>
              <Button onPress={onClose} disabled={guardando}>
                Cancelar
              </Button>
              <Button mode="contained" onPress={guardar} loading={guardando} disabled={guardando}>
                Guardar Cliente
              </Button>
            </View>
          </ScrollView>
        </KeyboardAvoidingView>
      </View>
    </Modal>
  );
}

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
    paddingHorizontal: 16,
    paddingBottom: 90,
  },
  tarjeta: {
    marginBottom: 12,
    borderRadius: 18,
    padding: 14,
  },
  filaTarjeta: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  filaContacto: {
    flexDirection: 'row',
    gap: 4,
  },
  btnContacto: {
    margin: 0,
    width: 36,
    height: 36,
  },
  filaAccionesAdmin: {
    flexDirection: 'row',
    justifyContent: 'flex-end',
    borderTopWidth: StyleSheet.hairlineWidth,
    borderTopColor: 'rgba(100,116,139,0.12)',
    marginTop: 10,
    paddingTop: 4,
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
  campo: {
    marginBottom: 10,
  },
  filaBotones: {
    flexDirection: 'row',
    justifyContent: 'flex-end',
    gap: 10,
    marginTop: 18,
  },
});
