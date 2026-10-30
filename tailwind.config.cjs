/** @type {import('tailwindcss').Config} */
module.exports = {
    // Semua file yang memuat nama kelas Tailwind (supaya CSS yang tidak terpakai dibuang)
    content: [
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
        './app/Support/**/*.php', // Content.php bisa memuat nama kelas
        './app/View/**/*.php',
    ],

    theme: {
        // Container: tengah, dengan padding samping yang responsif
        container: {
            center: true,
            padding: {
                DEFAULT: '1.25rem',
                sm: '1.5rem',
                lg: '2rem',
            },
            screens: {
                sm: '640px',
                md: '768px',
                lg: '1024px',
                xl: '1200px',
                '2xl': '1280px',
            },
        },

        extend: {
            // Token awal (final di tahap A1)
            colors: {
                // Biru brand DECAA.ID: #1B3590 = brand-700 (utama)
                // 900 dan 950 = navy tua untuk footer dan area gelap
                brand: {
                    50: '#EFF3FD',
                    100: '#DFE7FA',
                    200: '#C3D0F5',
                    300: '#98ADEC',
                    400: '#6A83DE',
                    500: '#4560CC',
                    600: '#2F48AD',
                    700: '#1B3590',
                    800: '#162B75',
                    900: '#12235D',
                    950: '#0B1538',
                },
                // Merah logo #E5352E = accent-500, dipakai sebagai aksen kecil
                accent: {
                    50: '#FEF2F1',
                    100: '#FDE3E1',
                    200: '#FBC9C6',
                    300: '#F5A09B',
                    400: '#EE6A63',
                    500: '#E5352E',
                    600: '#C92721',
                    700: '#A61D18',
                    800: '#871B17',
                    900: '#6F1B18',
                },
                // Warna judul (heading), navy sangat tua yang senada dengan brand
                ink: '#0E1A38',
            },

            // Font di-self-host lewat @fontsource-variable/plus-jakarta-sans
            fontFamily: {
                sans: [
                    '"Plus Jakarta Sans Variable"',
                    'ui-sans-serif',
                    'system-ui',
                    '-apple-system',
                    'Segoe UI',
                    'Roboto',
                    'Helvetica Neue',
                    'Arial',
                    'sans-serif',
                ],
            },

            boxShadow: {
                card: '0 1px 2px rgb(14 26 56 / 0.04), 0 4px 16px rgb(14 26 56 / 0.06)',
                'card-hover': '0 2px 4px rgb(14 26 56 / 0.06), 0 12px 32px rgb(14 26 56 / 0.12)',
            },

            // Animasi reusable (A1): muncul saat scroll dan strip logo klien (4E)
            keyframes: {
                'fade-up': {
                    '0%': { opacity: '0', transform: 'translateY(16px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
                marquee: {
                    '0%': { transform: 'translateX(0)' },
                    '100%': { transform: 'translateX(-50%)' },
                },
            },
            animation: {
                'fade-up': 'fade-up 0.6s ease-out both',
                marquee: 'marquee 40s linear infinite',
            },
        },
    },

    plugins: [],
};
