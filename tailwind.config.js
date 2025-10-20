import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],
    darkMode: 'class',

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: {
                    DEFAULT: '#FB2C36',
                    50: '#FFF1F2',
                    100: '#FFE0E2',
                    200: '#FFC0C3',
                    300: '#FF99A0',
                    400: '#FF6A76',
                    500: '#FB2C36',
                    600: '#E0242F',
                    700: '#C61F2B',
                    800: '#A01F2C',
                    900: '#7D1A24',
                },
                emphasize: '#FB2C36',
                indigo: {
                    50: '#FFF1F2',
                    100: '#FFE0E2',
                    200: '#FFC0C3',
                    300: '#FF99A0',
                    400: '#FF6A76',
                    500: '#FB2C36',
                    600: '#E0242F',
                    700: '#C61F2B',
                    800: '#A01F2C',
                    900: '#7D1A24',
                },
                darkbg: '#101828',
            },
        },
    },

    plugins: [forms],
};
