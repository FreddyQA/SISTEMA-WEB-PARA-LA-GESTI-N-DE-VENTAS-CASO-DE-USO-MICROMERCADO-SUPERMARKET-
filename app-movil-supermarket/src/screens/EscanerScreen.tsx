/**
 * PANTALLA DE ESCANEO DE CÓDIGOS QR Y BARRAS (CÁMARA FÍSICA)
 * 
 * Propósito Académico / Proyecto de Grado:
 * Demuestra la ventaja fundamental de la plataforma móvil frente a una aplicación web:
 * el acceso directo al hardware del dispositivo (sensor de cámara) para lectura óptica
 * en tiempo real de etiquetas impresas (códigos QR y códigos de barras Code128 / EAN-13).
 * 
 * Integración con el Backend:
 * Al escanear una etiqueta (ej: PRD-000005), la app invoca el endpoint GET /api/productos/codigo/{sku},
 * el cual resuelve el producto en la base de datos de Laravel y retorna instantáneamente su
 * precio actualizado y stock disponible.
 */

import React, { useState, useCallback } from 'react';
import { View, StyleSheet, Dimensions } from 'react-native';
import { CameraView, useCameraPermissions, BarcodeScanningResult } from 'expo-camera';
import { Text, Button, Card, Chip, ActivityIndicator, useTheme, Surface } from 'react-native-paper';
import { MaterialCommunityIcons } from '@expo/vector-icons';
import { useFocusEffect } from '@react-navigation/native';
import { ProductosApi } from '../api/endpoints';
import { ApiRequestError } from '../api/client';
import { haptico } from '../utils/haptico';
import type { Producto } from '../api/types';

export default function EscanerScreen() {
  const theme = useTheme();                                // Paleta del sistema activo
  const [permiso, solicitarPermiso] = useCameraPermissions(); // Hook de permisos nativos de Expo Camera

  const [activo, setActivo] = useState(true);              // Alternador del visor de cámara
  const [buscando, setBuscando] = useState(false);          // Indicador de consulta HTTP en progreso
  const [resultado, setResultado] = useState<Producto | null>(null); // Producto encontrado
  const [error, setError] = useState<string | null>(null);   // Mensajes de error o código no encontrado

  /**
   * Ciclo de vida de la cámara: Reactiva la cámara al enfocar la pestaña
   * y la apaga al desenfocar para ahorrar batería y memoria del móvil.
   */
  useFocusEffect(
    useCallback(() => {
      setActivo(true);
      return () => setActivo(false);
    }, [])
  );

  /**
   * Callback ejecutado automáticamente cuando el motor de visión por computadora
   * de Expo Camera detecta un código QR o de barras válido en el encuadre.
   */
  async function alEscanear({ data }: BarcodeScanningResult) {
    if (buscando) return; // Evita disparar múltiples peticiones por el mismo frame de video

    haptico.toque();
    setActivo(false);       // Congela la cámara durante la consulta
    setBuscando(true);
    setError(null);
    setResultado(null);

    try {
      // Normaliza el código leído: si es el JSON generado por el código QR de la web, extrae el SKU
      let codigoABuscar = data ? data.trim() : '';
      if (codigoABuscar.startsWith('{')) {
        try {
          const parsed = JSON.parse(codigoABuscar);
          codigoABuscar = parsed.sku || parsed.idProducto || parsed.id || codigoABuscar;
        } catch {
          // Si no es JSON válido, envía el texto tal cual
        }
      }

      // Envía el código leído hacia el endpoint de Laravel
      const { data: producto } = await ProductosApi.porCodigo(codigoABuscar);
      haptico.exito();
      setResultado(producto);
    } catch (e) {
      haptico.error();
      setError(
        e instanceof ApiRequestError
          ? e.message
          : 'El código escaneado no coincide con ningún producto en el catálogo.'
      );
    } finally {
      setBuscando(false);
    }
  }

  /**
   * Reinicia el escáner para procesar un nuevo artículo.
   */
  function escanearOtro() {
    haptico.toque();
    setResultado(null);
    setError(null);
    setActivo(true);
  }

  // Vista de espera mientras se inicializa el estado de permisos
  if (!permiso) return <View style={styles.contenedor} />;

  // Vista amigable si el usuario aún no ha concedido acceso a la cámara
  if (!permiso.granted) {
    return (
      <View style={[styles.contenedor, styles.centrado, { backgroundColor: theme.colors.surface }]}>
        <Surface style={styles.iconoPermisoWrap} elevation={1}>
          <MaterialCommunityIcons name="camera-off-outline" size={48} color={theme.colors.primary} />
        </Surface>
        <Text variant="titleMedium" style={{ fontWeight: '800', marginTop: 16, textAlign: 'center' }}>
          Permiso de Cámara Requerido
        </Text>
        <Text style={{ textAlign: 'center', marginVertical: 10, color: theme.colors.onSurfaceVariant, paddingHorizontal: 30 }}>
          Supermarket requiere acceso al sensor de la cámara para escanear las etiquetas QR y códigos de barras de los productos.
        </Text>
        <Button mode="contained" onPress={solicitarPermiso} style={{ marginTop: 8, borderRadius: 12 }}>
          Conceder Permiso
        </Button>
      </View>
    );
  }

  return (
    <View style={styles.contenedor}>
      {/* ── VISOR DE CÁMARA CON OVERLAY TIPO HUD ────────────────────── */}
      <View style={styles.camaraWrap}>
        {activo && (
          <CameraView
            style={StyleSheet.absoluteFill}
            facing="back"
            barcodeScannerSettings={{ barcodeTypes: ['qr', 'code128', 'ean13'] }}
            onBarcodeScanned={alEscanear}
          />
        )}

        {/* Marco de enfoque óptico con esquinas resaltadas */}
        <View pointerEvents="none" style={styles.marcoOverlay}>
          <View style={styles.reticula} />
          <Text style={styles.guiaTexto}>Apunta la cámara al código QR o de barras</Text>
        </View>
      </View>

      {/* ── PANEL DE RESULTADOS / INFORMACIÓN DEL PRODUCTO ─────────── */}
      <View style={[styles.resultadoWrap, { backgroundColor: theme.colors.background }]}>
        {buscando && (
          <View style={styles.buscandoWrap}>
            <ActivityIndicator size="large" color={theme.colors.primary} />
            <Text style={{ marginTop: 10, color: theme.colors.onSurfaceVariant }}>Buscando producto en almacén...</Text>
          </View>
        )}

        {/* Tarjeta de error si el código no existe */}
        {error && (
          <Surface style={[styles.tarjeta, { backgroundColor: '#FEF2F2' }]} elevation={1}>
            <View style={{ flexDirection: 'row', alignItems: 'center' }}>
              <MaterialCommunityIcons name="alert-circle-outline" size={26} color="#DC2626" />
              <Text style={{ color: '#991B1B', fontWeight: '700', marginLeft: 10, flex: 1 }}>
                {error}
              </Text>
            </View>
            <Button
              mode="contained-tonal"
              onPress={escanearOtro}
              style={{ marginTop: 12, borderRadius: 10 }}
              textColor="#DC2626"
            >
              Volver a escanear
            </Button>
          </Surface>
        )}

        {/* Tarjeta con los datos del producto reconocido */}
        {resultado && (
          <Surface style={[styles.tarjeta, { backgroundColor: theme.colors.surface }]} elevation={2}>
            <Text variant="titleLarge" style={{ fontWeight: '800' }}>
              {resultado.nombre}
            </Text>
            <Text variant="bodySmall" style={{ color: theme.colors.onSurfaceVariant, marginTop: 2 }}>
              Código: {resultado.sku} · {resultado.categoria?.nombre ?? 'Sin categoría'}
            </Text>

            <View style={styles.filaPrecio}>
              <Text variant="headlineMedium" style={{ color: theme.colors.primary, fontWeight: '900' }}>
                Bs {resultado.precio.toFixed(2)}
              </Text>

              <Chip
                compact
                style={{
                  backgroundColor: resultado.stock > 5 ? '#DCFCE7' : resultado.stock > 0 ? '#FEF3C7' : '#FEE2E2',
                }}
                textStyle={{
                  fontSize: 12,
                  fontWeight: '700',
                  color: resultado.stock > 5 ? '#15803D' : resultado.stock > 0 ? '#B45309' : '#DC2626',
                }}
              >
                {resultado.stock > 0 ? `Stock actual: ${resultado.stock}` : 'Agotado'}
              </Chip>
            </View>

            <Button mode="contained" onPress={escanearOtro} icon="barcode-scan" style={{ marginTop: 14, borderRadius: 12 }}>
              Escanear otro producto
            </Button>
          </Surface>
        )}

        {!buscando && !error && !resultado && (
          <View style={styles.esperandoWrap}>
            <MaterialCommunityIcons name="line-scan" size={32} color={theme.colors.onSurfaceVariant} />
            <Text style={{ color: theme.colors.onSurfaceVariant, marginTop: 8 }}>
              Listo para escanear. Coloca el código dentro del visor.
            </Text>
          </View>
        )}
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  contenedor: {
    flex: 1,
    backgroundColor: '#000000',
  },
  centrado: {
    alignItems: 'center',
    justifyContent: 'center',
    padding: 24,
  },
  iconoPermisoWrap: {
    width: 80,
    height: 80,
    borderRadius: 24,
    alignItems: 'center',
    justifyContent: 'center',
  },
  camaraWrap: {
    flex: 6,
    position: 'relative',
    overflow: 'hidden',
  },
  marcoOverlay: {
    ...StyleSheet.absoluteFill as object,
    alignItems: 'center',
    justifyContent: 'center',
  },
  reticula: {
    width: Dimensions.get('window').width * 0.75,
    height: Dimensions.get('window').width * 0.75,
    borderWidth: 2,
    borderColor: '#4F46E5',
    borderRadius: 24,
    backgroundColor: 'rgba(79, 70, 229, 0.05)',
  },
  guiaTexto: {
    color: '#FFFFFF',
    fontWeight: '600',
    marginTop: 18,
    textShadowColor: 'rgba(0,0,0,0.8)',
    textShadowOffset: { width: 0, height: 1 },
    textShadowRadius: 4,
    fontSize: 13,
  },
  resultadoWrap: {
    flex: 4,
    padding: 18,
    borderTopLeftRadius: 24,
    borderTopRightRadius: 24,
    justifyContent: 'center',
  },
  buscandoWrap: {
    alignItems: 'center',
    justifyContent: 'center',
  },
  esperandoWrap: {
    alignItems: 'center',
    justifyContent: 'center',
  },
  tarjeta: {
    padding: 18,
    borderRadius: 18,
  },
  filaPrecio: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginTop: 12,
  },
});
