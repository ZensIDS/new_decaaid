// PostCSS dipakai Vite untuk memproses Tailwind CSS.
// Ekstensi .cjs dipakai karena package.json proyek Laravel memakai "type": "module".
module.exports = {
    plugins: {
        tailwindcss: { config: './tailwind.config.cjs' },
        autoprefixer: {},
    },
};
