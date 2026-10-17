import React from 'react';
import { View, StyleSheet } from 'react-native';
import { Text, Button, useTheme } from 'react-native-paper';
import { MaterialCommunityIcons } from '@expo/vector-icons';

interface Props {
  icono: keyof typeof MaterialCommunityIcons.glyphMap;
  titulo: string;
  mensaje?: string;
  textoAccion?: string;
  onAccion?: () => void;
  esError?: boolean;
}

/** Pantalla de "no hay nada" / "algo falló" con ícono grande y botón opcional. */
export function EstadoVacio({ icono, titulo, mensaje, textoAccion, onAccion, esError }: Props) {
  const theme = useTheme();
  const color = esError ? theme.colors.error : theme.colors.primary;

  return (
    <View style={styles.contenedor}>
      <View style={[styles.circulo, { backgroundColor: color + '1A' }]}>
        <MaterialCommunityIcons name={icono} size={40} color={color} />
      </View>
      <Text variant="titleMedium" style={{ marginTop: 16, textAlign: 'center' }}>
        {titulo}
      </Text>
      {mensaje && (
        <Text variant="bodyMedium" style={{ color: theme.colors.onSurfaceVariant, marginTop: 6, textAlign: 'center' }}>
          {mensaje}
        </Text>
      )}
      {textoAccion && onAccion && (
        <Button mode="contained-tonal" onPress={onAccion} style={{ marginTop: 18 }}>
          {textoAccion}
        </Button>
      )}
    </View>
  );
}

const styles = StyleSheet.create({
  contenedor: { alignItems: 'center', paddingHorizontal: 32, paddingTop: 56 },
  circulo: { width: 84, height: 84, borderRadius: 42, alignItems: 'center', justifyContent: 'center' },
});
