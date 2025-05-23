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
                sans: ['Poppins', ...defaultTheme.fontFamily.sans],
                heading: ['Poppins', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Primary colors
                'primary': '#00DEA3',
                'secondary': '#5A55D2',
                'background': '#EFF3FD',
                'card': '#FFFFFF',
                'sidebar': '#FFFFFF',

                // Text colors
                'text': {
                    'primary': '#11142D',
                    'secondary': '#808191',
                    'muted': '#A0AEC0',
                },

                // Status colors
                'success': '#00DEA3',
                'warning': '#F6AD55',
                'danger': '#F56565',
                'info': '#4299E1',

                // Chart colors
                'chart': {
                    'green': '#00DEA3',
                    'blue': '#5A55D2',
                    'orange': '#F6AD55',
                    'red': '#F56565',
                },
            },
            boxShadow: {
                'card': '0 4px 20px rgba(0, 0, 0, 0.04)',
                'card-hover': '0 8px 30px rgba(0, 0, 0, 0.08)',
                'dropdown': '0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05)',
                'sidebar': '0 0 20px rgba(0, 0, 0, 0.05)',
                'button': '0 2px 5px rgba(0, 0, 0, 0.1)',
            },
            borderRadius: {
                'card': '0.75rem',
                'button': '0.5rem',
                'tag': '9999px', // For pill-shaped tags
            },
            spacing: {
                '14.5': '3.625rem',
            },
            transitionDuration: {
                '400': '400ms',
                '600': '600ms',
            },
            keyframes: {
                fadeInUp: {
                    '0%': { opacity: 0, transform: 'translateY(20px)' },
                    '100%': { opacity: 1, transform: 'translateY(0)' },
                },
            },
            animation: {
                fadeInUp: 'fadeInUp 0.6s ease-out forwards',
            },
        },
    },

    plugins: [forms],
};