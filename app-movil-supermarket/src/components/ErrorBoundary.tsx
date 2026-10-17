import React from 'react';
import { View, Text, Pressable, StyleSheet } from 'react-native';

interface Estado {
  fallo: boolean;
}

/**
 * Último resguardo: si un error inesperado de código rompe una pantalla, en
 * vez de dejar la app en blanco (o cerrarla) se muestra un mensaje con botón
 * para reintentar.
 */
export class ErrorBoundary extends React.Component<{ children: React.ReactNode }, Estado> {
  state: Estado = { fallo: false };

  static getDerivedStateFromError(): Estado {
    return { fallo: true };
  }

  render() {
    if (!this.state.fallo) return this.props.children;

    return (
      <View style={styles.contenedor}>
        <Text style={styles.emoji}>⚠️</Text>
        <Text style={styles.titulo}>Algo salió mal</Text>
        <Text style={styles.mensaje}>
          La aplicación tuvo un problema inesperado. Tus datos están a salvo en el servidor.
        </Text>
        <Pressable style={styles.boton} onPress={() => this.setState({ fallo: false })}>
          <Text style={styles.botonTexto}>Reintentar</Text>
        </Pressable>
      </View>
    );
  }
}

const styles = StyleSheet.create({
  contenedor: { flex: 1, alignItems: 'center', justifyContent: 'center', padding: 32, backgroundColor: '#0F172A' },
  emoji: { fontSize: 44 },
  titulo: { color: 'white', fontSize: 22, fontWeight: 'bold', marginTop: 12 },
  mensaje: { color: '#CBD5E1', textAlign: 'center', marginTop: 8, lineHeight: 20 },
  boton: { backgroundColor: '#4F46E5', paddingHorizontal: 28, paddingVertical: 12, borderRadius: 12, marginTop: 24 },
  botonTexto: { color: 'white', fontWeight: '600' },
});
