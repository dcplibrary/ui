/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        './resources/views/**/*.blade.php',
    ],
    theme: {
        extend: {
            colors: {
                // DCPL brand colors — mirrored from CSS custom properties
                // so Tailwind utility classes (bg-dcpl-blue, text-dcpl-orange, etc.)
                // are available in all consuming packages.
                'dcpl-blue':   '#0075A3',
                'dcpl-orange': '#F47920',
                'dcpl-green':  '#8BC53F',
                'dcpl-purple': '#8B5CF6',
                'dcpl-gold':   '#F4B942',
                'dcpl-text':   '#4d6375',
                'dcpl-gray':   '#516573',
            },
            fontFamily: {
                outfit: ['Outfit', 'ui-sans-serif', 'system-ui', 'sans-serif'],
            },
        },
    },
    plugins: [],
};
