import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class', // toggled by JS — see resources/js/theme.js

    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                display: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                brand: {
                    50:  '#f5f3ff',
                    100: '#ede9fe',
                    200: '#ddd6fe',
                    300: '#c4b5fd',
                    400: '#a78bfa',
                    500: '#8b5cf6',
                    600: '#7c3aed',
                    700: '#6d28d9',
                    800: '#5b21b6',
                    900: '#4c1d95',
                },
            },
            backgroundImage: {
                // Reusable gradients used in hero banners, decorative headers, empty illustrations
                'gradient-brand':    'linear-gradient(135deg, #7c3aed 0%, #a855f7 50%, #c026d3 100%)',
                'gradient-hero':     'linear-gradient(135deg, #6d28d9 0%, #7c3aed 35%, #a855f7 100%)',
                'gradient-emerald':  'linear-gradient(135deg, #059669 0%, #10b981 50%, #34d399 100%)',
                'gradient-amber':    'linear-gradient(135deg, #d97706 0%, #f59e0b 100%)',
                'gradient-rose':     'linear-gradient(135deg, #e11d48 0%, #f43f5e 100%)',
                'gradient-sky':      'linear-gradient(135deg, #0284c7 0%, #0ea5e9 100%)',
                'gradient-sidebar':  'linear-gradient(180deg, #0f172a 0%, #1e293b 100%)',
                // Subtle backgrounds for body / sections
                'gradient-canvas':   'radial-gradient(at 20% 0%, rgba(167,139,250,0.10), transparent 60%), radial-gradient(at 95% 5%, rgba(244,114,182,0.06), transparent 60%)',
                'gradient-mesh':     'radial-gradient(at 0% 100%, rgba(124,58,237,0.04), transparent 50%), radial-gradient(at 100% 0%, rgba(168,85,247,0.06), transparent 50%)',
                // Dotted pattern used as subtle hero decoration
                'pattern-dots':      'radial-gradient(circle, rgba(255,255,255,0.18) 1px, transparent 1px)',
            },
            backgroundSize: {
                'dots-sm': '14px 14px',
                'dots-md': '22px 22px',
            },
            animation: {
                'fade-in':        'fadeIn 0.4s ease-out both',
                'slide-up':       'slideUp 0.4s cubic-bezier(0.16,1,0.3,1) both',
                'slide-in-right': 'slideInRight 0.3s cubic-bezier(0.16,1,0.3,1) both',
                'slide-in-left':  'slideInLeft 0.3s cubic-bezier(0.16,1,0.3,1) both',
                'scale-in':       'scaleIn 0.25s cubic-bezier(0.34,1.56,0.64,1) both',
                'shimmer':        'shimmer 2s linear infinite',
                'pulse-slow':     'pulse 3s cubic-bezier(0.4,0,0.6,1) infinite',
                'spin-slow':      'spin 1.5s linear infinite',
                'pop':            'pop 0.35s cubic-bezier(0.34,1.56,0.64,1) both',
                'float-slow':     'floatSlow 6s ease-in-out infinite',
                'gradient-shift': 'gradientShift 12s ease infinite',
            },
            keyframes: {
                fadeIn: {
                    '0%':   { opacity: '0' },
                    '100%': { opacity: '1' },
                },
                slideUp: {
                    '0%':   { opacity: '0', transform: 'translateY(20px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
                slideInRight: {
                    '0%':   { opacity: '0', transform: 'translateX(24px)' },
                    '100%': { opacity: '1', transform: 'translateX(0)' },
                },
                slideInLeft: {
                    '0%':   { opacity: '0', transform: 'translateX(-24px)' },
                    '100%': { opacity: '1', transform: 'translateX(0)' },
                },
                scaleIn: {
                    '0%':   { opacity: '0', transform: 'scale(0.9)' },
                    '100%': { opacity: '1', transform: 'scale(1)' },
                },
                shimmer: {
                    '0%':   { backgroundPosition: '-200% 0' },
                    '100%': { backgroundPosition: '200% 0' },
                },
                pop: {
                    '0%':   { opacity: '0', transform: 'scale(0.6)' },
                    '60%':  { opacity: '1', transform: 'scale(1.08)' },
                    '100%': { opacity: '1', transform: 'scale(1)' },
                },
                floatSlow: {
                    '0%, 100%': { transform: 'translateY(0px)' },
                    '50%':      { transform: 'translateY(-10px)' },
                },
                gradientShift: {
                    '0%, 100%': { backgroundPosition: '0% 50%' },
                    '50%':      { backgroundPosition: '100% 50%' },
                },
            },
            boxShadow: {
                // Refined shadow scale — softer and more layered
                'card':        '0 1px 3px 0 rgb(15 23 42 / 0.04), 0 1px 2px -1px rgb(15 23 42 / 0.04)',
                'card-hover':  '0 8px 24px -4px rgb(15 23 42 / 0.08), 0 4px 8px -4px rgb(15 23 42 / 0.04)',
                'card-pop':    '0 16px 40px -12px rgb(15 23 42 / 0.18), 0 8px 16px -8px rgb(15 23 42 / 0.10)',
                'brand-glow':  '0 8px 24px -8px rgb(124 58 237 / 0.35), 0 4px 12px -4px rgb(124 58 237 / 0.20)',
                'soft-ring':   '0 0 0 4px rgb(124 58 237 / 0.10)',
                'inset-soft':  'inset 0 1px 0 0 rgb(255 255 255 / 0.6)',
            },
        },
    },

    plugins: [forms],
};
