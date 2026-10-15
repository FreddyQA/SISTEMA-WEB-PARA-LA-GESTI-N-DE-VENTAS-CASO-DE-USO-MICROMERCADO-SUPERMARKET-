/**
 * PANTALLA DE GESTIÓN Y ADMINISTRACIÓN DE USUARIOS DEL SISTEMA
 * 
 * Propósito Académico / Proyecto de Grado:
 * Control de acceso y asignación de credenciales (RBAC). Permite al Administrador
 * dar de alta empleados, asignar roles (Administrador General vs Operador/Invitado)
 * y revocar accesos.
 * 
 * Reglas de seguridad implementadas:
 * 1. Protección de auto-eliminación: Un administrador no puede eliminarse a sí mismo para evitar bloqueos del sistema.
 * 2. Cifrado de contraseñas: En el backend las claves se almacenan usando hash Bcrypt/Argon2.
 * 3. Confirmación de clave: Validación en cliente de coincidencia exacta de contraseñas.
 */

import React, { useCallback, useEffect, useState } from 'react';
import { View, StyleSheet, FlatList, Modal, ScrollView, Image, Alert, KeyboardAvoidingView, Platform } from 'react-native';
import * as ImagePicker from 'expo-image-picker';
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
  SegmentedButtons,
  Avatar,
  Surface,
  Chip,
  HelperText,
} from 'react-native-paper';
import { MaterialCommunityIcons } from '@expo/vector-icons';
import { useFocusEffect } from '@react-navigation/native';
import { UsuariosApi } from '../api/endpoints';
import { ApiRequestError } from '../api/client';
import { useAuth } from '../context/AuthContext';
import { haptico } from '../utils/haptico';
import type { Usuario } from '../api/types';

export default function UsuariosScreen() {
  const theme = useTheme();                                // Paleta del sistema activo
  const { usuario: yo } = useAuth();                        // Usuario actualmente logueado

  const [usuarios, setUsuarios] = useState<Usuario[]>([]);
  const [cargando, setCargando] = useState(true);
  const [editando, setEditando] = useState<Usuario | 'nuevo' | null>(null);
  const [aEliminar, setAEliminar] = useState<Usuario | null>(null);
  const [mensaje, setMensaje] = useState<string | null>(null);
  const [error, setError] = useState<string | null>(null);

  /**
   * Carga el padrón de usuarios registrados.
   */
  const cargar = useCallback(async () => {
    setCargando(true);
    try {
      const { data } = await UsuariosApi.listar();
      setUsuarios(data);
    } catch (e) {
      setError(e instanceof ApiRequestError ? e.message : 'No se pudieron sincronizar los usuarios.');
    } finally {
      setCargando(false);
    }
  }, []);

  useFocusEffect(
    useCallback(() => {
      cargar();
    }, [cargar])
  );

  /**
   * Ejecuta la eliminación del usuario.
   */
  async function eliminar() {
    if (!aEliminar) return;
    try {
      await UsuariosApi.eliminar(aEliminar.id);
      haptico.exito();
      setMensaje(`Usuario "${aEliminar.nombre}" eliminado del sistema.`);
      setAEliminar(null);
      cargar();
    } catch (e) {
      haptico.error();
      setError(e instanceof ApiRequestError ? e.message : 'No se pudo eliminar al usuario.');
      setAEliminar(null);
    }
  }

  return (
    <View style={[styles.contenedor, { backgroundColor: theme.colors.background }]}>
      {cargando && usuarios.length === 0 && (
        <View style={styles.contenedorCarga}>
          <ActivityIndicator size="large" color={theme.colors.primary} />
          <Text style={{ marginTop: 12, color: theme.colors.onSurfaceVariant }}>Cargando usuarios...</Text>
        </View>
      )}

      {/* ── LISTADO DE USUARIOS ────────────────────────────────────── */}
      <FlatList
        data={usuarios}
        keyExtractor={(u) => String(u.id)}
        contentContainerStyle={styles.lista}
        ListEmptyComponent={
          !cargando ? (
            <View style={styles.vacioWrap}>
              <MaterialCommunityIcons name="account-group-outline" size={48} color={theme.colors.onSurfaceVariant} />
              <Text variant="titleMedium" style={{ marginTop: 12, fontWeight: '700' }}>
                Sin usuarios registrados
              </Text>
            </View>
          ) : null
        }
        renderItem={({ item }) => {
          const esAdmin = item.rol === 'administrador';
          const esMiCuenta = item.id === yo?.id;

          return (
            <Surface style={[styles.tarjeta, { backgroundColor: theme.colors.surface }]} elevation={1}>
              <View style={styles.filaTarjeta}>
                {item.foto_url ? (
                  <Avatar.Image source={{ uri: item.foto_url }} size={46} style={{ backgroundColor: 'transparent' }} />
                ) : (
                  <Avatar.Text
                    label={item.nombre.charAt(0).toUpperCase()}
                    size={46}
                    style={{ backgroundColor: esAdmin ? '#EEF2FF' : '#ECFDF5' }}
                    color={esAdmin ? '#4F46E5' : '#059669'}
                  />
                )}

                <View style={{ flex: 1, marginLeft: 14 }}>
                  <View style={styles.filaNombre}>
                    <Text variant="titleMedium" style={{ fontWeight: '700', color: theme.colors.onSurface, flexShrink: 1 }} numberOfLines={1}>
                      {item.nombre}
                    </Text>
                    {esMiCuenta && (
                      <View style={styles.badgeTu}>
                        <Text style={styles.textoBadgeTu}>Tú</Text>
                      </View>
                    )}
                  </View>

                  <Text variant="bodySmall" style={{ color: theme.colors.onSurfaceVariant }}>
                    {item.email}
                  </Text>

                  {/* Badge de Rol: Prominente, de Alto Contraste y Sin Recortes */}
                  <View
                    style={[
                      styles.badgeRol,
                      {
                        backgroundColor: esAdmin ? '#EEF2FF' : '#ECFDF5',
                        borderColor: esAdmin ? '#6366F1' : '#10B981',
                      },
                    ]}
                  >
                    <MaterialCommunityIcons
                      name={esAdmin ? 'shield-check' : 'account-tie'}
                      size={13}
                      color={esAdmin ? '#4F46E5' : '#059669'}
                      style={{ marginRight: 4 }}
                    />
                    <Text
                      style={[
                        styles.textoBadgeRol,
                        { color: esAdmin ? '#3730A3' : '#065F46' },
                      ]}
                    >
                      {esAdmin ? 'Administrador' : 'Operador'}
                    </Text>
                  </View>
                </View>

                {/* Acciones de edición y baja */}
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
                    disabled={esMiCuenta}
                    onPress={() => {
                      haptico.toque();
                      setAEliminar(item);
                    }}
                  />
                </View>
              </View>
            </Surface>
          );
        }}
      />

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

      {/* Modal de formulario para usuario */}
      <FormularioUsuarioModal
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

      {/* Diálogo de confirmación para eliminar usuario */}
      <Portal>
        <Dialog visible={!!aEliminar} onDismiss={() => setAEliminar(null)}>
          <Dialog.Title>¿Eliminar cuenta de usuario?</Dialog.Title>
          <Dialog.Content>
            <Text variant="bodyMedium">
              ¿Estás seguro de que deseas retirar los accesos a "{aEliminar?.nombre}"? Esta persona ya no podrá
              iniciar sesión en la aplicación ni en el panel web.
            </Text>
          </Dialog.Content>
          <Dialog.Actions>
            <Button onPress={() => setAEliminar(null)}>Cancelar</Button>
            <Button textColor={theme.colors.error} onPress={eliminar}>
              Eliminar Usuario
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
 * Modal con validaciones preventivas de clave y correo para crear/editar usuarios.
 */
function FormularioUsuarioModal({
  valor,
  onClose,
  onGuardado,
  onError,
}: {
  valor: Usuario | 'nuevo' | null;
  onClose: () => void;
  onGuardado: (msg: string) => void;
  onError: (msg: string) => void;
}) {
  const theme = useTheme();
  const [name, setName] = useState('');
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [passwordConfirmation, setPasswordConfirmation] = useState('');
  const [rol, setRol] = useState<'administrador' | 'invitado'>('invitado');
  const [guardando, setGuardando] = useState(false);

  // Estados para gestión de foto de perfil
  const [imagenNueva, setImagenNueva] = useState<{ uri: string; mimeType?: string | null; base64?: string | null } | null>(null);
  const [quitarImagen, setQuitarImagen] = useState(false);
  const [imagenActual, setImagenActual] = useState<string | null>(null);

  // Errores de validación
  const [errorNombre, setErrorNombre] = useState<string | null>(null);
  const [errorEmail, setErrorEmail] = useState<string | null>(null);
  const [errorPass, setErrorPass] = useState<string | null>(null);

  useEffect(() => {
    if (!valor) return;
    setErrorNombre(null);
    setErrorEmail(null);
    setErrorPass(null);

    if (valor === 'nuevo') {
      setName('');
      setEmail('');
      setPassword('');
      setPasswordConfirmation('');
      setRol('invitado');
      setImagenNueva(null);
      setQuitarImagen(false);
      setImagenActual(null);
    } else {
      setName(valor.nombre);
      setEmail(valor.email);
      setPassword('');
      setPasswordConfirmation('');
      setRol(valor.rol);
      setImagenNueva(null);
      setQuitarImagen(false);
      setImagenActual(valor.foto_url ?? null);
    }
  }, [valor]);

  const vistaPrevia = imagenNueva ? imagenNueva.uri : (!quitarImagen ? imagenActual : null);

  async function elegirDeGaleria() {
    const permiso = await ImagePicker.requestMediaLibraryPermissionsAsync();
    if (!permiso.granted) {
      Alert.alert('Permiso necesario', 'Permite el acceso a tus fotos para elegir una foto de perfil.');
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

  async function tomarFoto() {
    const permiso = await ImagePicker.requestCameraPermissionsAsync();
    if (!permiso.granted) {
      Alert.alert('Permiso necesario', 'Permite el acceso a la cámara para capturar la fotografía del usuario.');
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

  function validar(): boolean {
    let valido = true;
    setErrorNombre(null);
    setErrorEmail(null);
    setErrorPass(null);

    if (!name.trim()) {
      setErrorNombre('El nombre de usuario es obligatorio.');
      valido = false;
    }

    const regexEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!email.trim() || !regexEmail.test(email.trim())) {
      setErrorEmail('Ingresa un correo electrónico válido.');
      valido = false;
    }

    if (valor === 'nuevo' && (!password || password.length < 6)) {
      setErrorPass('La contraseña debe tener al menos 6 caracteres.');
      valido = false;
    } else if (password && password !== passwordConfirmation) {
      setErrorPass('Las contraseñas no coinciden.');
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
    const datos: {
      name: string;
      email: string;
      rol: 'administrador' | 'invitado';
      password?: string;
      password_confirmation?: string;
    } = {
      name: name.trim(),
      email: email.trim(),
      rol,
    };

    if (password) {
      datos.password = password;
      datos.password_confirmation = passwordConfirmation;
    }

    let idUsuario: number | null = null;
    try {
      if (valor === 'nuevo') {
        const { data } = await UsuariosApi.crear(datos);
        idUsuario = data.id;
      } else if (valor) {
        await UsuariosApi.actualizar(valor.id, datos);
        idUsuario = valor.id;
      }
    } catch (e) {
      onError(e instanceof ApiRequestError ? e.message : 'Error al guardar usuario.');
      setGuardando(false);
      return;
    }

    // Gestión asíncrona de la foto física (base64 JSON o multipart)
    try {
      if (idUsuario && imagenNueva) {
        await UsuariosApi.subirImagen(idUsuario, imagenNueva.uri, imagenNueva.mimeType, imagenNueva.base64);
      } else if (idUsuario && quitarImagen && imagenActual) {
        await UsuariosApi.quitarImagen(idUsuario);
      }
    } catch (eImg) {
      onError(`Usuario guardado, pero ocurrió un problema con la foto: ${eImg instanceof ApiRequestError ? eImg.message : 'error de red'}`);
    }

    setGuardando(false);
    onGuardado(valor === 'nuevo' ? 'Usuario creado exitosamente.' : 'Usuario actualizado exitosamente.');
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
              {valor === 'nuevo' ? 'Nuevo Usuario' : 'Editar Usuario'}
            </Text>

            {/* ── SELECTOR Y VISTA PREVIA DE FOTO DE PERFIL ────────────── */}
            <View style={styles.avatarSeccion}>
              <View style={[styles.avatarWrap, { borderColor: theme.colors.outlineVariant }]}>
                {vistaPrevia ? (
                  <Image source={{ uri: vistaPrevia }} style={styles.avatarFoto} />
                ) : (
                  <View style={[styles.avatarFoto, styles.avatarPlaceholder, { backgroundColor: theme.colors.surfaceVariant }]}>
                    <MaterialCommunityIcons name="account" size={48} color={theme.colors.onSurfaceVariant} />
                  </View>
                )}
              </View>
              <View style={styles.avatarBotones}>
                <Button mode="outlined" compact icon="camera" onPress={tomarFoto} style={styles.btnFoto}>
                  Cámara
                </Button>
                <Button mode="outlined" compact icon="image" onPress={elegirDeGaleria} style={styles.btnFoto}>
                  Galería
                </Button>
                {vistaPrevia && (
                  <Button
                    mode="text"
                    compact
                    icon="trash-can-outline"
                    textColor={theme.colors.error}
                    onPress={() => {
                      setImagenNueva(null);
                      setQuitarImagen(true);
                    }}
                  >
                    Quitar foto
                  </Button>
                )}
              </View>
            </View>

            <TextInput
              mode="outlined"
              label="Nombre y Apellidos *"
              value={name}
              onChangeText={(v) => {
                setName(v);
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

            <Text variant="labelMedium" style={{ marginTop: 10, marginBottom: 6, fontWeight: '700' }}>
              Rol en el sistema:
            </Text>
            <SegmentedButtons
              value={rol}
              onValueChange={(v) => setRol(v as typeof rol)}
              style={{ marginBottom: 14 }}
              buttons={[
                { value: 'invitado', label: 'Operador / Ventas' },
                { value: 'administrador', label: 'Administrador' },
              ]}
            />

            <TextInput
              mode="outlined"
              label={valor === 'nuevo' ? 'Contraseña *' : 'Nueva contraseña (dejar vacío para no cambiar)'}
              value={password}
              onChangeText={(v) => {
                setPassword(v);
                if (errorPass) setErrorPass(null);
              }}
              secureTextEntry
              error={!!errorPass}
              style={[styles.campo, { backgroundColor: theme.colors.surface }]}
              textColor={theme.colors.onSurface}
            />

            {(valor === 'nuevo' || password.length > 0) && (
              <TextInput
                mode="outlined"
                label="Confirmar contraseña *"
                value={passwordConfirmation}
                onChangeText={(v) => {
                  setPasswordConfirmation(v);
                  if (errorPass) setErrorPass(null);
                }}
                secureTextEntry
                error={!!errorPass}
                style={[styles.campo, { backgroundColor: theme.colors.surface }]}
                textColor={theme.colors.onSurface}
              />
            )}
            {errorPass && <HelperText type="error">{errorPass}</HelperText>}

            <View style={styles.filaBotones}>
              <Button onPress={onClose} disabled={guardando}>
                Cancelar
              </Button>
              <Button mode="contained" onPress={guardar} loading={guardando} disabled={guardando}>
                Guardar Usuario
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
  filaNombre: {
    flexDirection: 'row',
    alignItems: 'center',
    flexWrap: 'wrap',
    gap: 6,
    paddingRight: 4,
  },
  badgeTu: {
    backgroundColor: '#E0E7FF',
    paddingHorizontal: 8,
    paddingVertical: 3,
    borderRadius: 8,
    minHeight: 22,
    justifyContent: 'center',
    flexShrink: 0,
  },
  textoBadgeTu: {
    fontSize: 11,
    color: '#3730A3',
    fontWeight: '800',
    lineHeight: 14,
  },
  badgeRol: {
    flexDirection: 'row',
    alignItems: 'center',
    alignSelf: 'flex-start',
    paddingHorizontal: 9,
    paddingVertical: 3.5,
    borderRadius: 8,
    borderWidth: 1.5,
    marginTop: 6,
  },
  textoBadgeRol: {
    fontSize: 12,
    fontWeight: '800',
    letterSpacing: 0.2,
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
    paddingBottom: 60,
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
  avatarSeccion: {
    alignItems: 'center',
    marginBottom: 20,
  },
  avatarWrap: {
    width: 96,
    height: 96,
    borderRadius: 48,
    overflow: 'hidden',
    borderWidth: 2,
    marginBottom: 10,
  },
  avatarFoto: {
    width: '100%',
    height: '100%',
  },
  avatarPlaceholder: {
    alignItems: 'center',
    justifyContent: 'center',
  },
  avatarBotones: {
    flexDirection: 'row',
    gap: 8,
    alignItems: 'center',
    flexWrap: 'wrap',
    justifyContent: 'center',
  },
  btnFoto: {
    borderRadius: 20,
  },
});
