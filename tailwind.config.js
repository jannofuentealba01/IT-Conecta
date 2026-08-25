import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            colors: {
                brand: { blue: 'var(--brand-blue)', 'blue-dark': 'var(--brand-blue-dark)', 'blue-light': 'var(--brand-blue-light)' },
                sustainability: { DEFAULT: 'var(--brand-green)', dark: 'var(--brand-green-dark)' },
                game: { DEFAULT: 'var(--brand-purple)', dark: 'var(--brand-purple-dark)' },
                impact: { low: 'var(--impact-low)', medium: 'var(--impact-medium)', high: 'var(--impact-high)' },
                ui: { text: 'var(--text-primary)', muted: 'var(--text-secondary)', surface: 'var(--surface)', background: 'var(--surface-muted)', border: 'var(--border)' },
            },
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms],
};
