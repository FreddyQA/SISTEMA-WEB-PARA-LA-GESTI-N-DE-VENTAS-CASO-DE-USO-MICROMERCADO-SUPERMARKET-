import { MD3LightTheme, MD3DarkTheme } from 'react-native-paper';

// Paleta extraída literalmente del CSS real del admin web
// (resources/views/layouts/app.blade.php de supermarket_con_api.zip),
// no inventada — para que la app móvil se sienta como el mismo sistema.
// Mejorada con matices más refinados y tokens de diseño adicionales.

export const lightTheme = {
  ...MD3LightTheme,
  colors: {
    ...MD3LightTheme.colors,
    primary: '#4F46E5',
    primaryContainer: '#E0E7FF',
    secondary: '#0D9488',
    secondaryContainer: '#CCFBF1',
    tertiary: '#7C3AED',
    tertiaryContainer: '#EDE9FE',
    error: '#DC2626',
    errorContainer: '#FEE2E2',
    background: '#F8FAFC',
    surface: '#FFFFFF',
    surfaceVariant: '#F1F5F9',
    onSurface: '#0F172A',
    onSurfaceVariant: '#64748B',
    outline: '#CBD5E1',
    elevation: {
      ...MD3LightTheme.colors.elevation,
      level1: '#FFFFFF',
      level2: '#F8FAFC',
      level3: '#F1F5F9',
    },
  },
  roundness: 3,
};

export const darkTheme = {
  ...MD3DarkTheme,
  colors: {
    ...MD3DarkTheme.colors,
    primary: '#818CF8', // mismo tono que .text-primary / enlaces en dark-mode
    primaryContainer: '#312E81',
    secondary: '#2DD4BF',
    secondaryContainer: '#134E4A',
    tertiary: '#A78BFA',
    tertiaryContainer: '#4C1D95',
    error: '#F87171', // .text-danger dark-mode
    errorContainer: '#450A0A',
    background: '#030712', // --bg-base dark-mode
    surface: '#071026', // --surface dark-mode
    surfaceVariant: '#0B1530', // --surface-soft dark-mode
    onSurface: '#E6EEF8', // --text dark-mode
    onSurfaceVariant: '#9FB6CF', // --muted dark-mode
    outline: 'rgba(255,255,255,0.08)',
    elevation: {
      ...MD3DarkTheme.colors.elevation,
      level1: '#0A1628',
      level2: '#0D1B30',
      level3: '#101F38',
    },
  },
  roundness: 3,
};

// El sidebar del admin web es SIEMPRE este degradado navy, en claro y
// oscuro — es parte de la identidad de marca, no del tema de color.
export const sidebarGradient = ['#1A1A2E', '#16213E', '#0F3460'] as const;

// Degradados de las 4 tarjetas del dashboard — exactamente
// .card-indigo / .card-emerald / .card-amber / .card-rose del CSS real.
export const statCardGradients = {
  indigo: ['#4F46E5', '#7C3AED'] as const,
  emerald: ['#10B981', '#059669'] as const,
  amber: ['#F59E0B', '#D97706'] as const,
  rose: ['#F43F5E', '#E11D48'] as const,
};

export const estadoColor = {
  completadoLight: '#15803D',
  completadoDark: '#4ADE80',
  anuladoLight: '#DC2626',
  anuladoDark: '#F87171',
  stockBajoLight: '#D97706',
  stockBajoDark: '#FBBF24',
};

// ── Tokens de diseño adicionales para UI premium ──
export const designTokens = {
  cardBorderRadius: 20,
  buttonBorderRadius: 14,
  chipBorderRadius: 24,
  inputBorderRadius: 14,
  spacing: {
    xs: 4,
    sm: 8,
    md: 16,
    lg: 24,
    xl: 32,
  },
  shadow: {
    light: {
      shadowColor: '#4F46E5',
      shadowOffset: { width: 0, height: 4 },
      shadowOpacity: 0.08,
      shadowRadius: 12,
      elevation: 3,
    },
    medium: {
      shadowColor: '#4F46E5',
      shadowOffset: { width: 0, height: 6 },
      shadowOpacity: 0.12,
      shadowRadius: 16,
      elevation: 5,
    },
    heavy: {
      shadowColor: '#4F46E5',
      shadowOffset: { width: 0, height: 8 },
      shadowOpacity: 0.18,
      shadowRadius: 24,
      elevation: 8,
    },
  },
};
