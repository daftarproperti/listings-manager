/** @type {import('tailwindcss').Config} */

import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import colors from 'tailwindcss/colors';
import withMT from '@material-tailwind/react/utils/withMT';

// Remove old colors to suppress warnings.
delete colors.lightBlue;
delete colors.warmGray;
delete colors.trueGray;
delete colors.coolGray;
delete colors.blueGray;

export default withMT({
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
                newsreader: ['Newsreader', 'serif'],
                newsreader: ['Newsreader', 'serif'],
                inter: ['Inter', 'serif'],
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
            },
            typography: ({ theme }) => ({
                DEFAULT: {
                    css: {
                        h1: {
                            marginTop: theme('spacing.8'),
                        },
                        h2: {
                            marginTop: theme('spacing.8'),
                        },
                    }
                }
            }),
        },
        colors: colors,
    },

    plugins: [forms, require('@tailwindcss/typography')],
});
