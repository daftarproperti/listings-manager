import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

const colors = require('tailwindcss/colors')

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.tsx',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
        colors: {
            ribbon: {
                50: '#EEF8FF',
                100: '#D8EEFF',
                200: '#B9E0FF',
                300: '#89CFFF',
                400: '#52B4FF',
                500: '#2A91FF',
                600: '#0D6EFD',
                700: '#0C5AE9',
                800: '#1149BC',
                900: '#144194',
                950: '#11295A',
            },
            ...colors
        },
    },

    plugins: [forms],
};
