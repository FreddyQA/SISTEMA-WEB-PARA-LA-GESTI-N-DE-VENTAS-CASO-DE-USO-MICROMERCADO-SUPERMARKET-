import * as Haptics from 'expo-haptics';

// Vibración sutil de confirmación. Se envuelve en catch porque no todos los
// dispositivos/emuladores la soportan, y una vibración que falla nunca debe
// romper la acción que el usuario acaba de hacer.
export const haptico = {
  toque: () => Haptics.selectionAsync().catch(() => {}),
  exito: () => Haptics.notificationAsync(Haptics.NotificationFeedbackType.Success).catch(() => {}),
  error: () => Haptics.notificationAsync(Haptics.NotificationFeedbackType.Error).catch(() => {}),
};
