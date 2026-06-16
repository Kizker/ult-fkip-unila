/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js"
  ],
  darkMode: 'class',
  theme: {
    extend: {
      fontFamily: {
        sans: ['Poppins', 'ui-sans-serif', 'system-ui', 'sans-serif'],
      },
      colors: {
        bg: 'rgb(var(--c-bg) / <alpha-value>)',
        fg: 'rgb(var(--c-fg) / <alpha-value>)',
        card: 'rgb(var(--c-card) / <alpha-value>)',
        border: 'rgb(var(--c-border) / <alpha-value>)',
        primary: 'rgb(var(--c-primary) / <alpha-value>)',
        primary2: 'rgb(var(--c-primary2) / <alpha-value>)',
        danger: 'rgb(var(--c-danger) / <alpha-value>)',
        warning: 'rgb(var(--c-warning) / <alpha-value>)',
        success: 'rgb(var(--c-success) / <alpha-value>)',
        muted: 'rgb(var(--c-muted) / <alpha-value>)'
      },
      boxShadow: {
        soft: '0 12px 30px rgba(0,0,0,.08)',
      },
      borderRadius: {
        xl: '1rem',
        '2xl': '1.25rem',
      }
    },
  },
  plugins: [],
};
