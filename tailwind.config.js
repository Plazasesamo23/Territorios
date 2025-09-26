/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
      colors: {
        'territorio': {
          'activo': '#10b981',
          'libre': '#6b7280',
          'archivo': '#3b82f6',
          'pendiente': '#f59e0b',
          'atrasado': '#ef4444',
        }
      }
    },
  },
  plugins: [],
} 