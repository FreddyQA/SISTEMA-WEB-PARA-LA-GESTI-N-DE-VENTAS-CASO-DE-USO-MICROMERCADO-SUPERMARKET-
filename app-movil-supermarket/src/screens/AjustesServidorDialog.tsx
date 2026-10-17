import React, { useEffect, useState } from 'react';
import { View } from 'react-native';
import { Portal, Dialog, TextInput, Button, Text, useTheme } from 'react-native-paper';
import { MaterialCommunityIcons } from '@expo/vector-icons';
import { obtenerBaseUrl, guardarBaseUrl } from '../api/client';
import { haptico } from '../utils/haptico';

interface Props {
  visible: boolean;
  onDismiss: () => void;
}

type Prueba =
  | { estado: 'inactiva' }
  | { estado: 'probando' }
  | { estado: 'ok'; ms: number }
  | { estado: 'error'; detalle: string };

export function AjustesServidorDialog({ visible, onDismiss }: Props) {
  const theme = useTheme();
  const [url, setUrl] = useState('');
  const [prueba, setPrueba] = useState<Prueba>({ estado: 'inactiva' });

  useEffect(() => {
    if (visible) {
      obtenerBaseUrl().then(setUrl);
      setPrueba({ estado: 'inactiva' });
    }
  }, [visible]);

  async function guardar() {
    let limpia = url.trim().replace(/\/+$/, '');
    if (/:800$/i.test(limpia)) {
      limpia = limpia + '0';
    }
    await guardarBaseUrl(limpia);
    onDismiss();
  }

  // Consulta la ruta de salud de Laravel (/up) con la dirección escrita, SIN
  // guardarla todavía: así se puede comprobar antes de dar "Guardar".
  async function probarConexion() {
    let base = url.trim().replace(/\/+$/, '');
    if (/:800$/i.test(base)) {
      base = base + '0';
      setUrl(base);
    }
    if (!/^https?:\/\//i.test(base)) {
      setPrueba({ estado: 'error', detalle: 'La dirección debe empezar con http:// o https:// (ej: http://192.168.0.105:8000 o https://tunel.trycloudflare.com).' });
      return;
    }
    setPrueba({ estado: 'probando' });
    const inicio = Date.now();
    const control = new AbortController();
    const limite = setTimeout(() => control.abort(), 6000);
    try {
      const resp = await fetch(`${base}/up`, { signal: control.signal });
      if (!resp.ok) throw new Error(`El servidor respondió con el código ${resp.status}.`);
      haptico.exito();
      setPrueba({ estado: 'ok', ms: Date.now() - inicio });
    } catch (e) {
      haptico.error();
      const agotado = e instanceof Error && e.name === 'AbortError';
      setPrueba({
        estado: 'error',
        detalle: agotado
          ? 'No respondió a tiempo.'
          : e instanceof Error && e.message.startsWith('El servidor')
            ? e.message
            : 'No se pudo conectar.',
      });
    } finally {
      clearTimeout(limite);
    }
  }

  return (
    <Portal>
      <Dialog visible={visible} onDismiss={onDismiss}>
        <Dialog.Title>Dirección del servidor</Dialog.Title>
        <Dialog.Content>
          <Text variant="bodySmall" style={{ marginBottom: 12, color: theme.colors.onSurfaceVariant }}>
            Ingresa la URL donde corre el backend Laravel. Puede ser tu IP local si estás en la misma Wi-Fi
            (ej. http://192.168.0.105:8000), o un túnel público seguro (Cloudflare Tunnel, ngrok o hosting)
            para usar la app desde cualquier lugar con datos móviles sin depender de la misma red.
          </Text>
          <TextInput
            mode="outlined"
            value={url}
            onChangeText={(t) => { setUrl(t); setPrueba({ estado: 'inactiva' }); }}
            autoCapitalize="none"
            autoCorrect={false}
            keyboardType="url"
            style={{ backgroundColor: theme.colors.surface }}
            textColor={theme.colors.onSurface}
            placeholder="http://192.168.0.105:8000 o https://mi-tunel.trycloudflare.com"
          />

          <Button
            mode="outlined"
            icon="lan-connect"
            onPress={probarConexion}
            loading={prueba.estado === 'probando'}
            disabled={prueba.estado === 'probando'}
            style={{ marginTop: 12 }}
          >
            Probar conexión
          </Button>

          {prueba.estado === 'ok' && (
            <View style={{ flexDirection: 'row', alignItems: 'center', marginTop: 10 }}>
              <MaterialCommunityIcons name="check-circle" size={20} color="#16A34A" />
              <Text style={{ color: '#16A34A', marginLeft: 6 }}>Conectado ({prueba.ms} ms)</Text>
            </View>
          )}

          {prueba.estado === 'error' && (
            <View style={{ marginTop: 10 }}>
              <View style={{ flexDirection: 'row', alignItems: 'center' }}>
                <MaterialCommunityIcons name="close-circle" size={20} color={theme.colors.error} />
                <Text style={{ color: theme.colors.error, marginLeft: 6, flex: 1 }}>{prueba.detalle}</Text>
              </View>
              <Text variant="bodySmall" style={{ color: theme.colors.onSurfaceVariant, marginTop: 6 }}>
                Revisa que: 1) el servidor esté encendido y se haya iniciado con "iniciar-para-celular.bat"
                (con solo "php artisan serve" el celular no puede entrar), 2) el celular y la PC estén en la
                misma red Wi-Fi, 3) el Firewall de Windows permita el puerto 8000.
              </Text>
            </View>
          )}
        </Dialog.Content>
        <Dialog.Actions>
          <Button onPress={onDismiss}>Cancelar</Button>
          <Button onPress={guardar}>Guardar</Button>
        </Dialog.Actions>
      </Dialog>
    </Portal>
  );
}
