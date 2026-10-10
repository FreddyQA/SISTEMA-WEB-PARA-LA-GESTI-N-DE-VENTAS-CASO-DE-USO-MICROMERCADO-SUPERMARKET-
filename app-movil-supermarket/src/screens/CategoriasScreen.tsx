/**
 * PANTALLA DE GESTIÓN DE CATEGORÍAS DE PRODUCTOS
 * 
 * Propósito Académico / Proyecto de Grado:
 * Permite clasificar la mercadería del supermercado en rubros ordenados.
 * 
 * Reglas de integridad aplicadas:
 * 1. RBAC: Los usuarios invitados solo pueden consultar las categorías; la creación,
 *    edición y eliminación son exclusivas del Administrador.
 * 2. Protección de datos: No se puede eliminar una categoría si aún posee productos vinculados.
 * 3. Nombres únicos: Se valida que no existan duplicados tanto en cliente como en base de datos.
 */

import React, { useCallback, useEffect, useState } from 'react';
import { View, StyleSheet, FlatList, Modal, ScrollView, KeyboardAvoidingView, Platform } from 'react-native';
import {
  Text,
  FAB,
  IconButton,
  TextInput,
  Button,
  Portal,
  Dialog,
  Snackbar,
  ActivityIndicator,
  useTheme,
  Surface,
  HelperText,
} from 'react-native-paper';
import { MaterialCommunityIcons } from '@expo/vector-icons';
import { useFocusEffect } from '@react-navigation/native';
import { CategoriasApi } from '../api/endpoints';
import { ApiRequestError } from '../api/client';
import { useAuth } from '../context/AuthContext';
import { haptico } from '../utils/haptico';
import { syncEvents } from '../utils/syncEvents';
import type { Categoria } from '../api/types';

export default function CategoriasScreen({ route, navigation }: any) {
  const theme = useTheme();                                // Paleta activa del sistema
  const { usuario } = useAuth();                            // Sesión activa
  const esAdmin = usuario?.rol === 'administrador';        // Permiso de administrador

  const [categorias, setCategorias] = useState<Categoria[]>([]);
  const [cargando, setCargando] = useState(true);
  const [editando, setEditando] = useState<Categoria | 'nueva' | null>(null);
  const [aEliminar, setAEliminar] = useState<Categoria | null>(null);
  const [mensaje, setMensaje] = useState<string | null>(null);
  const [error, setError] = useState<string | null>(null);

  /**
   * Apertura reactiva desde Acciones Rápidas (Dashboard) cuando se pasa abrirNueva: true
   */
  useEffect(() => {
    if (route?.params?.abrirNueva || route?.params?.abrirNuevo) {
      setEditando('nueva');
      if (navigation?.setParams) {
        navigation.setParams({ abrirNueva: undefined, abrirNuevo: undefined });
      }
    }
  }, [route?.params?.abrirNueva, route?.params?.abrirNuevo, navigation]);

  /**
   * Carga el listado completo de categorías desde Laravel.
   */
  const cargar = useCallback(async (silencioso = false) => {
    if (!silencioso) setCargando(true);
    try {
      const { data } = await CategoriasApi.listar();
      setCategorias(data);
    } catch {
      if (!silencioso) setError('No se pudieron sincronizar las categorías con el servidor.');
    } finally {
      if (!silencioso) setCargando(false);
    }
  }, []);

  useFocusEffect(
    useCallback(() => {
      cargar();
    }, [cargar])
  );

  /**
   * Sincronización en vivo: al crearse, editarse o eliminarse una categoría en la web,
   * actualiza la lista en segundo plano automáticamente.
   */
  useEffect(() => {
    const handler = () => cargar(true);
    syncEvents.on('sync:categorias', handler);
    return () => syncEvents.off('sync:categorias', handler);
  }, [cargar]);

  /**
   * Elimina una categoría seleccionada.
   */
  async function eliminar() {
    if (!aEliminar) return;
    try {
      await CategoriasApi.eliminar(aEliminar.id);
      haptico.exito();
      setMensaje(`Categoría "${aEliminar.nombre}" eliminada exitosamente.`);
      setAEliminar(null);
      cargar();
    } catch (e) {
      haptico.error();
      setError(e instanceof ApiRequestError ? e.message : 'No se pudo eliminar la categoría.');
      setAEliminar(null);
    }
  }

  return (
    <View style={[styles.contenedor, { backgroundColor: theme.colors.background }]}>
      {cargando && categorias.length === 0 && (
        <View style={styles.contenedorCarga}>
          <ActivityIndicator size="large" color={theme.colors.primary} />
          <Text style={{ marginTop: 12, color: theme.colors.onSurfaceVariant }}>Cargando categorías...</Text>
        </View>
      )}

      <FlatList
        data={categorias}
        keyExtractor={(c) => String(c.id)}
        contentContainerStyle={styles.lista}
        ListEmptyComponent={
          !cargando ? (
            <View style={styles.vacioWrap}>
              <MaterialCommunityIcons name="shape-outline" size={48} color={theme.colors.onSurfaceVariant} />
              <Text variant="titleMedium" style={{ marginTop: 12, fontWeight: '700' }}>
                Sin categorías registradas
              </Text>
              <Text variant="bodySmall" style={{ color: theme.colors.onSurfaceVariant, marginTop: 4 }}>
                Crea una categoría para clasificar tus productos.
              </Text>
            </View>
          ) : null
        }
        renderItem={({ item }) => (
          <Surface style={[styles.tarjeta, { backgroundColor: theme.colors.surface }]} elevation={1}>
            <View style={styles.filaTarjeta}>
              <View style={[styles.iconoCatWrap, { backgroundColor: theme.colors.primaryContainer }]}>
                <MaterialCommunityIcons name="tag-outline" size={22} color={theme.colors.primary} />
              </View>

              <View style={{ flex: 1, marginLeft: 14 }}>
                <Text variant="titleMedium" style={{ fontWeight: '700', color: theme.colors.onSurface }}>
                  {item.nombre}
                </Text>
                <Text variant="bodySmall" style={{ color: theme.colors.onSurfaceVariant, marginTop: 2 }}>
                  {item.descripcion || 'Sin descripción adicional'}
                </Text>
              </View>

              {esAdmin && (
                <View style={styles.accionesWrap}>
                  <IconButton
                    icon="pencil-outline"
                    size={20}
                    onPress={() => {
                      haptico.toque();
                      setEditando(item);
                    }}
                  />
                  <IconButton
                    icon="trash-can-outline"
                    size={20}
                    iconColor={theme.colors.error}
                    onPress={() => {
                      haptico.toque();
                      setAEliminar(item);
                    }}
                  />
                </View>
              )}
            </View>
          </Surface>
        )}
      />

      {esAdmin && (
        <FAB
          icon="plus"
          style={[styles.fab, { backgroundColor: theme.colors.primary }]}
          color="#FFFFFF"
          onPress={() => {
            haptico.toque();
            setEditando('nueva');
          }}
          label="Nueva"
        />
      )}

      {/* Modal para formulario de categoría */}
      <FormularioCategoriaModal
        valor={editando}
        onClose={() => setEditando(null)}
        onGuardado={(msg) => {
          haptico.exito();
          setMensaje(msg);
          setEditando(null);
          cargar();
        }}
        onError={(m) => {
          haptico.error();
          setError(m);
        }}
      />

      {/* Diálogo de confirmación para eliminar */}
      <Portal>
        <Dialog visible={!!aEliminar} onDismiss={() => setAEliminar(null)}>
          <Dialog.Title>¿Eliminar categoría?</Dialog.Title>
          <Dialog.Content>
            <Text variant="bodyMedium">
              ¿Deseas eliminar la categoría "{aEliminar?.nombre}"? El servidor rechazará la operación si tiene
              productos asignados en el catálogo.
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
      <Snackbar visible={!!error} onDismiss={() => setError(null)} duration={4000} style={{ backgroundColor: theme.colors.error }}>
        {error}
      </Snackbar>
    </View>
  );
}

/**
 * Modal con validación preventiva de datos para categorías.
 */
function FormularioCategoriaModal({
  valor,
  onClose,
  onGuardado,
  onError,
}: {
  valor: Categoria | 'nueva' | null;
  onClose: () => void;
  onGuardado: (msg: string) => void;
  onError: (msg: string) => void;
}) {
  const theme = useTheme();
  const [nombre, setNombre] = useState('');
  const [descripcion, setDescripcion] = useState('');
  const [guardando, setGuardando] = useState(false);
  const [errorNombre, setErrorNombre] = useState<string | null>(null);

  React.useEffect(() => {
    if (!valor) return;
    setErrorNombre(null);
    if (valor === 'nueva') {
      setNombre('');
      setDescripcion('');
    } else {
      setNombre(valor.nombre);
      setDescripcion(valor.descripcion ?? '');
    }
  }, [valor]);

  function validar(): boolean {
    if (!nombre.trim()) {
      setErrorNombre('El nombre de la categoría es obligatorio.');
      return false;
    }
    if (nombre.trim().length < 2) {
      setErrorNombre('Debe tener al menos 2 caracteres.');
      return false;
    }
    return true;
  }

  async function guardar() {
    if (!validar()) {
      onError('Corrige el nombre de la categoría.');
      return;
    }

    setGuardando(true);
    const datos = {
      nombre: nombre.trim(),
      descripcion: descripcion.trim() || undefined,
    };

    try {
      if (valor === 'nueva') {
        await CategoriasApi.crear(datos);
      } else if (valor) {
        await CategoriasApi.actualizar(valor.id, datos);
      }
      onGuardado(valor === 'nueva' ? 'Categoría registrada exitosamente.' : 'Categoría actualizada exitosamente.');
    } catch (e) {
      onError(e instanceof ApiRequestError ? e.message : 'Error al guardar la categoría.');
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
              {valor === 'nueva' ? 'Nueva Categoría' : 'Editar Categoría'}
            </Text>

            <TextInput
              mode="outlined"
              label="Nombre de la categoría *"
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
              label="Descripción o notas (opcional)"
              value={descripcion}
              onChangeText={setDescripcion}
              multiline
              numberOfLines={3}
              style={[styles.campo, { backgroundColor: theme.colors.surface }]}
              textColor={theme.colors.onSurface}
            />

            <View style={styles.filaBotones}>
              <Button onPress={onClose} disabled={guardando}>
                Cancelar
              </Button>
              <Button mode="contained" onPress={guardar} loading={guardando} disabled={guardando}>
                Guardar Categoría
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
  contenedorCarga: {
    paddingVertical: 50,
    alignItems: 'center',
  },
  lista: {
    padding: 16,
    paddingBottom: 90,
  },
  tarjeta: {
    marginBottom: 10,
    borderRadius: 16,
    padding: 14,
  },
  filaTarjeta: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  iconoCatWrap: {
    width: 44,
    height: 44,
    borderRadius: 12,
    alignItems: 'center',
    justifyContent: 'center',
  },
  accionesWrap: {
    flexDirection: 'row',
  },
  vacioWrap: {
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 50,
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
  },
  campo: {
    marginBottom: 10,
  },
  filaBotones: {
    flexDirection: 'row',
    justifyContent: 'flex-end',
    gap: 10,
    marginTop: 20,
  },
});
