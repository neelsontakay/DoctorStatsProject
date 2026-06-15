import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],
    theme: {
        extend: {
            colors: {
                background: '#ffffff',
                foreground: '#0f172a',
                card: {
                    DEFAULT: '#ffffff',
                    foreground: '#0f172a',
                },
                muted: {
                    DEFAULT: '#f1f5f9',
                    foreground: '#64748b',
                },
                border: '#e2e8f0',
                input: '#f8fafc',
                ring: '#1e6cff',
                destructive: '#dc2626',
                primary: {
                    DEFAULT: '#1e6cff',
                    foreground: '#ffffff',
                    50: '#eff6ff',
                    100: '#dbeafe',
                    200: '#bfdbfe',
                    300: '#93c5fd',
                    700: '#1d4ed8',
                    800: '#1e40af',
                    900: '#1e3a8a',
                },
                accent: {
                    DEFAULT: '#10b981',
                    foreground: '#ffffff',
                    50: '#ecfdf5',
                    100: '#d1fae5',
                    200: '#a7f3d0',
                    700: '#047857',
                    800: '#065f46',
                },
                secondary: {
                    DEFAULT: '#eab308',
                    foreground: '#000000',
                    50: '#fefce8',
                    100: '#fef9c3',
                    700: '#a16207',
                },
            },
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                mono: ['JetBrains Mono', ...defaultTheme.fontFamily.mono],
            },
            borderRadius: {
                lg: '0.625rem',
            },
        },
    },
    plugins: [],
};
