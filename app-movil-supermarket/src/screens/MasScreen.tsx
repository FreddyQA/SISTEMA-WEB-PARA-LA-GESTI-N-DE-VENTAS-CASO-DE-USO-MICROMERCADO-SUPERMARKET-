/**
 * PANTALLA DE CONFIGURACIÓN Y MÓDULOS SECUNDARIOS ("MÁS")
 * 
 * Propósito Académico / Proyecto de Grado:
 * Ofrece acceso a los catálogos secundarios (Clientes y Categorías),
 * a las herramientas exclusivas del Administrador (Reportes analíticos, Gestión de Usuarios),
 * y a la personalización de la experiencia del usuario (cambio de tema Claro/Oscuro y
 * configuración de red del servidor).
 */

import React, { useState } from 'react';
import { View, StyleSheet, ScrollView, Pressable, Alert } from 'react-native';
import { Text, Avatar, Button, Divider, useTheme, SegmentedButtons, Surface } from 'react-native-paper';
import { MaterialCommunityIcons } from '@expo/vector-icons';
import type { NativeStackNavigationProp } from '@react-navigation/native-stack';
import { useAuth } from '../context/AuthContext';
import { useAppTheme } from '../context/ThemeContext';
import { AjustesServidorDialog } from './AjustesServidorDialog';
import { haptico } from '../utils/haptico';
import type { MasStackParamList } from '../navigation/RootNavigator';

interface OpcionMenuProps {
  icono: keyof typeof MaterialCommunityIcons.glyphMap;
  colorIcono: string;
  titulo: string;
  subtitulo?: string;
  onPress: () => void;
  esUltimo?: boolean;
}

/**
 * Fila interactiva de menú estilo tarjeta agrupada (Grouped Inset).
 */
function OpcionMenu({ icono, colorIcono, titulo, subtitulo, onPress, esUltimo }: OpcionMenuProps) {
  const theme = useTheme();
  return (
    <Pressable
      onPress={() => {
        haptico.toque();
        onPress();
      }}
      style={({ pressed }) => [
        styles.filaMenu,
        !esUltimo && styles.bordeInferiorFila,
        pressed && { backgroundColor: theme.colors.surfaceVariant, opacity: 0.8 },
      ]}
    >
      <View style={[styles.iconoMenuWrap, { backgroundColor: colorIcono + '18' }]}>
        <MaterialCommunityIcons name={icono} size={22} color={colorIcono} />
      </View>
      <View style={{ flex: 1, marginLeft: 14 }}>
        <Text variant="titleMedium" style={{ fontWeight: '700', color: theme.colors.onSurface }}>
          {titulo}
        </Text>
        {subtitulo && (
          <Text variant="bodySmall" style={{ color: theme.colors.onSurfaceVariant }}>
            {subtitulo}
          </Text>
        )}
      </View>
      <MaterialCommunityIcons name="chevron-right" size={22} color={theme.colors.onSurfaceVariant} />
    </Pressable>
  );
}

export default function MasScreen({
  navigation,
}: {
  navigation: NativeStackNavigationProp<MasStackParamList, 'MasInicio'>;
}) {
  const { usuario, cerrarSesion } = useAuth();
  const { preferencia, establecerPreferencia } = useAppTheme();
  const theme = useTheme();
  const [mostrarAjustes, setMostrarAjustes] = useState(false);
  const [saliendo, setSaliendo] = useState(false);
  const esAdmin = usuario?.rol === 'administrador';

  /**
   * Cierra la sesión activa revocando el token Bearer en Laravel.
   */
  async function salir() {
    Alert.alert(
      'Cerrar Sesión',
      '¿Estás seguro de que deseas salir del sistema?',
      [
        { text: 'Cancelar', style: 'cancel' },
        {
          text: 'Cerrar Sesión',
          style: 'destructive',
          onPress: async () => {
            setSaliendo(true);
            try {
              await cerrarSesion();
            } finally {
              setSaliendo(false);
            }
          },
        },
      ]
    );
  }

  return (
    <ScrollView style={{ flex: 1, backgroundColor: theme.colors.background }} contentContainerStyle={styles.contenido}>
      {/* ── TARJETA DE PERFIL DEL USUARIO ACTIVO ───────────────────── */}
      <Surface style={[styles.tarjetaPerfil, { backgroundColor: theme.colors.surface }]} elevation={2}>
        {usuario?.foto_url ? (
          <Avatar.Image source={{ uri: usuario.foto_url }} size={58} style={{ backgroundColor: 'transparent' }} />
        ) : (
          <Avatar.Text
            label={(usuario?.nombre ?? '?').charAt(0).toUpperCase()}
            size={58}
            style={{ backgroundColor: theme.colors.primary }}
            color="#FFFFFF"
          />
        )}
        <View style={{ marginLeft: 16, flex: 1 }}>
          <Text variant="titleLarge" style={{ fontWeight: '800', color: theme.colors.onSurface }}>
            {usuario?.nombre}
          </Text>
          <Text variant="bodySmall" style={{ color: theme.colors.onSurfaceVariant }}>
            {usuario?.email}
          </Text>
          <View style={styles.badgeRolWrap}>
            <Text
              variant="labelSmall"
              style={{
                color: esAdmin ? '#4F46E5' : '#059669',
                fontWeight: '800',
              }}
            >
              {esAdmin ? '● ROL: ADMINISTRADOR GENERAL' : '● ROL: OPERADOR / VENDEDOR'}
            </Text>
          </View>
        </View>
      </Surface>

      {/* ── GRUPO: CATÁLOGO Y GESTIÓN OPERATIVA ────────────────────── */}
      <Text variant="labelLarge" style={[styles.tituloSeccion, { color: theme.colors.primary }]}>
        CATÁLOGO Y VENTAS
      </Text>
      <Surface style={[styles.grupoTarjetas, { backgroundColor: theme.colors.surface }]} elevation={1}>
        <OpcionMenu
          icono="shape-outline"
          colorIcono="#4F46E5"
          titulo="Categorías"
          subtitulo="Clasificación de productos del inventario"
          onPress={() => navigation.navigate('Categorias')}
        />
        <OpcionMenu
          icono="account-group-outline"
          colorIcono="#0D9488"
          titulo="Clientes"
          subtitulo="Cartera de clientes y contactos directos"
          onPress={() => navigation.navigate('Clientes')}
        />
        <OpcionMenu
          icono="receipt-text-outline"
          colorIcono="#E11D48"
          titulo="Historial de Ventas"
          subtitulo="Bitácora de ventas y tickets emitidos"
          onPress={() => navigation.navigate('Historial')}
          esUltimo
        />
      </Surface>

      {/* ── GRUPO: ADMINISTRACIÓN EXCLUSIVA DE DUEÑO/ADMIN ─────────── */}
      {esAdmin && (
        <>
          <Text variant="labelLarge" style={[styles.tituloSeccion, { color: theme.colors.primary }]}>
            ADMINISTRACIÓN GENERAL
          </Text>
          <Surface style={[styles.grupoTarjetas, { backgroundColor: theme.colors.surface }]} elevation={1}>
            <OpcionMenu
              icono="chart-box-outline"
              colorIcono="#8B5CF6"
              titulo="Reportes de Ventas"
              subtitulo="Métricas financieras, filtros y promedios"
              onPress={() => navigation.navigate('Reportes')}
            />
            <OpcionMenu
              icono="account-cog-outline"
              colorIcono="#EC4899"
              titulo="Gestión de Usuarios"
              subtitulo="Cuentas de empleados y asignación de roles"
              onPress={() => navigation.navigate('Usuarios')}
              esUltimo
            />
          </Surface>
        </>
      )}

      {/* ── GRUPO: APARIENCIA Y PREFERENCIAS ───────────────────────── */}
      <Text variant="labelLarge" style={[styles.tituloSeccion, { color: theme.colors.primary }]}>
        APARIENCIA VISUAL
      </Text>
      <Surface style={[styles.grupoTarjetas, { backgroundColor: theme.colors.surface, padding: 14 }]} elevation={1}>
        <Text variant="bodySmall" style={{ color: theme.colors.onSurfaceVariant, marginBottom: 10 }}>
          Modo de visualización de la interfaz:
        </Text>
        <SegmentedButtons
          value={preferencia}
          onValueChange={(v) => establecerPreferencia(v as 'claro' | 'oscuro' | 'sistema')}
          buttons={[
            { value: 'claro', label: 'Claro', icon: 'white-balance-sunny' },
            { value: 'oscuro', label: 'Oscuro', icon: 'weather-night' },
            { value: 'sistema', label: 'Sistema', icon: 'theme-light-dark' },
          ]}
        />
      </Surface>

      {/* ── GRUPO: CONFIGURACIÓN DE RED DEL SERVIDOR ───────────────── */}
      <Text variant="labelLarge" style={[styles.tituloSeccion, { color: theme.colors.primary }]}>
        CONECTIVIDAD
      </Text>
      <Surface style={[styles.grupoTarjetas, { backgroundColor: theme.colors.surface }]} elevation={1}>
        <OpcionMenu
          icono="server-network"
          colorIcono="#10B981"
          titulo="Dirección del Servidor"
          subtitulo="Verificar o cambiar la IP del backend Laravel"
          onPress={() => setMostrarAjustes(true)}
          esUltimo
        />
      </Surface>

      {/* ── BOTÓN DE CIERRE DE SESIÓN ──────────────────────────────── */}
      <Button
        mode="outlined"
        icon="logout"
        onPress={salir}
        loading={saliendo}
        style={styles.salirBtn}
        textColor={theme.colors.error}
        contentStyle={{ paddingVertical: 4 }}
      >
        Cerrar Sesión
      </Button>

      <AjustesServidorDialog visible={mostrarAjustes} onDismiss={() => setMostrarAjustes(false)} />
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  contenido: {
    padding: 16,
    paddingBottom: 40,
  },
  tarjetaPerfil: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: 18,
    borderRadius: 20,
    marginBottom: 20,
  },
  badgeRolWrap: {
    marginTop: 4,
    backgroundColor: 'rgba(79, 70, 229, 0.08)',
    paddingHorizontal: 8,
    paddingVertical: 2,
    borderRadius: 6,
    alignSelf: 'flex-start',
  },
  tituloSeccion: {
    fontWeight: '800',
    fontSize: 11,
    letterSpacing: 0.8,
    marginBottom: 8,
    marginLeft: 6,
  },
  grupoTarjetas: {
    borderRadius: 18,
    overflow: 'hidden',
    marginBottom: 20,
  },
  filaMenu: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: 16,
  },
  bordeInferiorFila: {
    borderBottomWidth: StyleSheet.hairlineWidth,
    borderBottomColor: 'rgba(100,116,139,0.14)',
  },
  iconoMenuWrap: {
    width: 40,
    height: 40,
    borderRadius: 12,
    alignItems: 'center',
    justifyContent: 'center',
  },
  salirBtn: {
    borderRadius: 14,
    borderColor: '#F87171',
    marginTop: 10,
    marginBottom: 20,
  },
});
