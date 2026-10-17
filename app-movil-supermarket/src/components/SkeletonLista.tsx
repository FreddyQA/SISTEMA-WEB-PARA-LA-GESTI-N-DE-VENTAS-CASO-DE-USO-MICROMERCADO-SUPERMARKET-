import React, { useEffect, useRef } from 'react';
import { View, Animated, StyleSheet } from 'react-native';
import { useTheme } from 'react-native-paper';

/** Filas "fantasma" con pulso suave mientras carga — se siente más pulido que un spinner suelto. */
export function SkeletonLista({ filas = 6 }: { filas?: number }) {
  const theme = useTheme();
  const opacidad = useRef(new Animated.Value(0.45)).current;

  useEffect(() => {
    const animacion = Animated.loop(
      Animated.sequence([
        Animated.timing(opacidad, { toValue: 1, duration: 700, useNativeDriver: true }),
        Animated.timing(opacidad, { toValue: 0.45, duration: 700, useNativeDriver: true }),
      ])
    );
    animacion.start();
    return () => animacion.stop();
  }, [opacidad]);

  const bloque = theme.colors.surfaceVariant;

  return (
    <View style={{ paddingHorizontal: 12 }}>
      {Array.from({ length: filas }).map((_, i) => (
        <Animated.View key={i} style={[styles.fila, { backgroundColor: theme.colors.surface, opacity: opacidad }]}>
          <View style={[styles.avatar, { backgroundColor: bloque }]} />
          <View style={{ flex: 1, marginLeft: 12 }}>
            <View style={[styles.linea, { width: '65%', backgroundColor: bloque }]} />
            <View style={[styles.linea, { width: '40%', height: 10, marginTop: 8, backgroundColor: bloque }]} />
            <View style={[styles.linea, { width: '30%', marginTop: 10, backgroundColor: bloque }]} />
          </View>
        </Animated.View>
      ))}
    </View>
  );
}

const styles = StyleSheet.create({
  fila: { flexDirection: 'row', alignItems: 'center', padding: 16, borderRadius: 12, marginTop: 8 },
  avatar: { width: 44, height: 44, borderRadius: 22 },
  linea: { height: 14, borderRadius: 7 },
});
