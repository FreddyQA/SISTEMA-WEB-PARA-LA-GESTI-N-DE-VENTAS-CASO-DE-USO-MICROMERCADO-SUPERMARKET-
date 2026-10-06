import React from 'react';
import { StatusBar } from 'expo-status-bar';
import { SafeAreaProvider } from 'react-native-safe-area-context';
import { PaperProvider } from 'react-native-paper';
import { AuthProvider } from './src/context/AuthContext';
import { SyncProvider } from './src/context/SyncContext';
import { AppThemeProvider, useAppTheme } from './src/context/ThemeContext';
import RootNavigator from './src/navigation/RootNavigator';
import { ErrorBoundary } from './src/components/ErrorBoundary';

function Contenido() {
  const { tema, esOscuro } = useAppTheme();

  return (
    <PaperProvider theme={tema}>
      <AuthProvider>
        <SyncProvider>
          <StatusBar style={esOscuro ? 'light' : 'dark'} />
          <RootNavigator />
        </SyncProvider>
      </AuthProvider>
    </PaperProvider>
  );
}

export default function App() {
  return (
    <ErrorBoundary>
      <SafeAreaProvider>
        <AppThemeProvider>
          <Contenido />
        </AppThemeProvider>
      </SafeAreaProvider>
    </ErrorBoundary>
  );
}
