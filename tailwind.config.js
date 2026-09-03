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
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
                // Public-site-only voices (ticket/coupon world) — never used
                // in the private area or admin, which keep Figtree.
                display: ['Anton', ...defaultTheme.fontFamily.sans],
                grotesk: ['Archivo', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                cral: {
                    red: '#E4002B',
                    navy: '#14213D',
                    'navy-soft': '#4A5878',
                    cream: '#F5F2EA',
                    paper: '#FFFDF9',
                },
            },
        },
    },

    plugins: [forms],
};
