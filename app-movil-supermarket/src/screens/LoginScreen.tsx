/**
 * PANTALLA DE INICIO DE SESIÓN (LOGIN) MÓVIL
 * 
 * Propósito Académico / Proyecto de Grado:
 * Punto de entrada y autenticación de los usuarios a la aplicación móvil.
 * 
 * Concepto Clave para el Jurado Examinador:
 * A diferencia del administrador Web que utiliza sesiones basadas en cookies y protección CSRF,
 * la aplicación móvil consume una API REST sin estado (Stateless) empleando autenticación
 * por Tokens Bearer (tabla `api_tokens` en Laravel). Esto desacopla la aplicación del navegador,
 * evita problemas de almacenamiento de cookies entre plataformas y permite revocar tokens
 * de manera remota por dispositivo.
 */

import React, { useEffect, useState } from 'react';
import { View, StyleSheet, KeyboardAvoidingView, Platform, ScrollView } from 'react-native';
import { Text, TextInput, Button, HelperText, useTheme, Surface } from 'react-native-paper';
import { LinearGradient } from 'expo-linear-gradient';
import { MaterialCommunityIcons } from '@expo/vector-icons';
import { useAuth } from '../context/AuthContext';
import { ApiRequestError } from '../api/client';
import { haptico } from '../utils/haptico';
import { AjustesServidorDialog } from './AjustesServidorDialog';
import { sidebarGradient } from '../theme/theme';

export default function LoginScreen() {
  const theme = useTheme();                                // Paleta del sistema activo
  const { iniciarSesion, errorInicial } = useAuth();        // Función de autenticación y error al restaurar sesión

  // Estados locales para los campos del formulario
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [verPassword, setVerPassword] = useState(false);   // Alternador de visibilidad de contraseña (ojo abierto/cerrado)
  const [error, setError] = useState<string | null>(null);   // Mensajes de error devueltos por validación o API
  const [cargando, setCargando] = useState(false);          // Spinner de solicitud en proceso
  const [mostrarAjustes, setMostrarAjustes] = useState(false); // Diálogo para cambiar la IP del servidor Laravel

  useEffect(() => {
    if (errorInicial) setError(errorInicial);
  }, [errorInicial]);

  /**
   * Valida las credenciales y ejecuta el inicio de sesión.
   */
  async function entrar() {
    setError(null);

    // Validación preventiva en el cliente antes de solicitar la red
    if (!email.trim() || !password) {
      haptico.error();
      setError('Por favor, ingresa tu correo electrónico y tu contraseña.');
      return;
    }

    setCargando(true);
    try {
      // Envía la petición POST /api/login a Laravel
      await iniciarSesion(email.trim(), password);
      haptico.exito();
    } catch (e) {
      haptico.error();
      setError(
        e instanceof ApiRequestError
          ? e.message
          : 'No se pudo conectar con el servidor. Verifica que esté encendido y en la misma red Wi-Fi.'
      );
    } finally {
      setCargando(false);
    }
  }

  return (
    <KeyboardAvoidingView
      style={[styles.flex, { backgroundColor: theme.colors.background }]}
      behavior={Platform.OS === 'ios' ? 'padding' : undefined}
      keyboardVerticalOffset={Platform.OS === 'ios' ? 0 : 0}
    >
      <ScrollView
        style={{ flex: 1, backgroundColor: theme.colors.background }}
        contentContainerStyle={[styles.scrollContenedor, { backgroundColor: theme.colors.background }]}
        keyboardShouldPersistTaps="handled"
        showsVerticalScrollIndicator={false}
        bounces={false}
      >
        {/* Orbes de fondo ambientales con gradientes */}
        <LinearGradient
          colors={['rgba(79, 70, 229, 0.22)', 'rgba(124, 58, 237, 0.05)', 'transparent']}
          style={styles.orbeSuperior}
          pointerEvents="none"
        />
        <LinearGradient
          colors={['rgba(14, 165, 233, 0.18)', 'rgba(16, 185, 129, 0.04)', 'transparent']}
          style={styles.orbeInferior}
          pointerEvents="none"
        />

        {/* ── LOGOTIPO Y MARCA DEL SISTEMA COMPATIBLE CON LA WEB ───────────── */}
        <View style={styles.logoWrap}>
          <LinearGradient
            colors={['#4F46E5', '#6D28D9', '#7C3AED']}
            start={{ x: 0, y: 0 }}
            end={{ x: 1, y: 1 }}
            style={styles.logoCirculo}
          >
            <View style={styles.logoBrilloInterno}>
              <MaterialCommunityIcons name="storefront" size={44} color="#FFFFFF" />
            </View>
          </LinearGradient>
          <Text variant="headlineMedium" style={{ fontWeight: '900', color: theme.colors.onSurface, letterSpacing: -0.6, marginTop: 4 }}>
            Supermarket
          </Text>
          <View style={styles.subtituloPill}>
            <MaterialCommunityIcons name="shield-check" size={13} color="#4F46E5" style={{ marginRight: 5 }} />
            <Text style={styles.subtituloPillTexto}>
              SISTEMA DE GESTIÓN DE VENTAS
            </Text>
          </View>
        </View>

        {/* ── TARJETA DEL FORMULARIO DE ACCESO ───────────────────────── */}
        <Surface style={[styles.tarjetaLogin, { backgroundColor: theme.colors.surface }]} elevation={3}>
          <View style={{ marginBottom: 18 }}>
            <Text variant="titleLarge" style={{ fontWeight: '800', color: theme.colors.onSurface }}>
              Iniciar Sesión
            </Text>
            <Text variant="bodySmall" style={{ color: theme.colors.onSurfaceVariant, marginTop: 2 }}>
              Ingresa tus credenciales para acceder al inventario de ventas
            </Text>
          </View>

          {/* Campo de Correo Electrónico */}
          <TextInput
            label="Correo electrónico"
            value={email}
            onChangeText={(v) => {
              setEmail(v);
              if (error) setError(null);
            }}
            autoCapitalize="none"
            keyboardType="email-address"
            mode="outlined"
            style={[styles.input, { backgroundColor: theme.colors.surface }]}
            outlineStyle={{ borderRadius: 14 }}
            textColor={theme.colors.onSurface}
            left={<TextInput.Icon icon="email-outline" />}
          />

          {/* Campo de Contraseña con toggle de visibilidad */}
          <TextInput
            label="Contraseña"
            value={password}
            onChangeText={(v) => {
              setPassword(v);
              if (error) setError(null);
            }}
            secureTextEntry={!verPassword}
            mode="outlined"
            style={[styles.input, { backgroundColor: theme.colors.surface }]}
            outlineStyle={{ borderRadius: 14 }}
            textColor={theme.colors.onSurface}
            left={<TextInput.Icon icon="lock-outline" />}
            right={
              <TextInput.Icon
                icon={verPassword ? 'eye-off-outline' : 'eye-outline'}
                onPress={() => setVerPassword((v) => !v)}
              />
            }
            onSubmitEditing={entrar}
          />

          {/* Mensaje de error formateado */}
          {error && (
            <View style={styles.cajaError}>
              <MaterialCommunityIcons name="alert-circle-outline" size={18} color="#DC2626" style={{ marginRight: 6 }} />
              <Text style={styles.cajaErrorTexto}>{error}</Text>
            </View>
          )}

          {/* Botón de envío */}
          <Button
            mode="contained"
            onPress={entrar}
            loading={cargando}
            disabled={cargando}
            style={styles.botonEntrar}
            contentStyle={{ paddingVertical: 6 }}
            labelStyle={{ fontSize: 15, fontWeight: '700', letterSpacing: 0.3 }}
          >
            Entrar al Sistema
          </Button>

          {/* Acceso a configuración de IP del servidor */}
          <Button
            mode="text"
            onPress={() => setMostrarAjustes(true)}
            style={styles.ajustesBtn}
            icon="server-network"
            labelStyle={{ fontSize: 13, fontWeight: '600' }}
          >
            Configurar IP del servidor
          </Button>
        </Surface>
      </ScrollView>

      {/* Diálogo emergente para probar ping y guardar la IP de Laravel */}
      <AjustesServidorDialog visible={mostrarAjustes} onDismiss={() => setMostrarAjustes(false)} />
    </KeyboardAvoidingView>
  );
}

const styles = StyleSheet.create({
  flex: {
    flex: 1,
  },
  scrollContenedor: {
    flexGrow: 1,
    justifyContent: 'center',
    paddingHorizontal: 22,
    paddingVertical: 24,
    position: 'relative',
  },
  orbeSuperior: {
    position: 'absolute',
    top: -80,
    left: -80,
    width: 320,
    height: 320,
    borderRadius: 160,
  },
  orbeInferior: {
    position: 'absolute',
    bottom: -100,
    right: -80,
    width: 340,
    height: 340,
    borderRadius: 170,
  },
  logoWrap: {
    alignItems: 'center',
    marginBottom: 20,
  },
  logoCirculo: {
    width: 86,
    height: 86,
    borderRadius: 26,
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 10,
    shadowColor: '#4F46E5',
    shadowOffset: { width: 0, height: 10 },
    shadowOpacity: 0.42,
    shadowRadius: 20,
    elevation: 10,
    borderWidth: 1.5,
    borderColor: 'rgba(255, 255, 255, 0.3)',
  },
  logoBrilloInterno: {
    width: '100%',
    height: '100%',
    borderRadius: 24,
    alignItems: 'center',
    justifyContent: 'center',
  },
  subtituloPill: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 14,
    paddingVertical: 5,
    borderRadius: 999,
    backgroundColor: 'rgba(79, 70, 229, 0.08)',
    borderWidth: 1,
    borderColor: 'rgba(79, 70, 229, 0.18)',
    marginTop: 8,
  },
  subtituloPillTexto: {
    fontSize: 10.5,
    fontWeight: '800',
    color: '#4F46E5',
    letterSpacing: 0.8,
  },
  tarjetaLogin: {
    padding: 24,
    borderRadius: 24,
    borderWidth: 1,
    borderColor: 'rgba(255, 255, 255, 0.6)',
  },
  input: {
    marginBottom: 12,
  },
  cajaError: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#FEE2E2',
    paddingHorizontal: 12,
    paddingVertical: 8,
    borderRadius: 10,
    marginBottom: 10,
    borderWidth: 1,
    borderColor: '#FCA5A5',
  },
  cajaErrorTexto: {
    color: '#B91C1C',
    fontSize: 12,
    fontWeight: '600',
    flex: 1,
  },
  botonEntrar: {
    borderRadius: 14,
    marginTop: 4,
    elevation: 2,
  },
  ajustesBtn: {
    marginTop: 10,
    alignSelf: 'center',
  },
  badgeSeguridad: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    marginTop: 20,
  },
});
