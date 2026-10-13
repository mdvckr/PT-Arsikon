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
            fontFamily: {
                sans: ['Inter', 'Figtree', ...defaultTheme.fontFamily.sans],
            },
            boxShadow: {
                'glass': '0 8px 32px rgba(0, 0, 0, 0.12), inset 0 1px 0 rgba(255, 255, 255, 0.6)',
                'glass-pill': '0 12px 36px -4px rgba(0, 0, 0, 0.18), 0 4px 12px rgba(0, 0, 0, 0.08), inset 0 1px 1.5px 0 rgba(255, 255, 255, 0.85)',
                'glass-glow-info': '0 12px 36px -4px rgba(2, 132, 199, 0.25), inset 0 1px 1.5px 0 rgba(255, 255, 255, 0.85)',
                'glass-glow-success': '0 12px 36px -4px rgba(5, 150, 105, 0.25), inset 0 1px 1.5px 0 rgba(255, 255, 255, 0.85)',
                'glass-glow-warning': '0 12px 36px -4px rgba(217, 119, 6, 0.25), inset 0 1px 1.5px 0 rgba(255, 255, 255, 0.85)',
                'glass-glow-danger': '0 12px 36px -4px rgba(225, 29, 72, 0.25), inset 0 1px 1.5px 0 rgba(255, 255, 255, 0.85)',
            },
            colors: {
                glass: {
                    base: 'rgba(255, 255, 255, 0.68)',
                    card: 'rgba(255, 255, 255, 0.78)',
                    dark: 'rgba(15, 23, 42, 0.72)',
                    border: 'rgba(255, 255, 255, 0.45)',
                },
            },
        },
    },

    plugins: [forms],
};
