const colors = require('tailwindcss/colors');
colors.transparent = 'transparent';
colors.current = 'currentColor';

module.exports = {
    mode: 'jit',

    purge: [
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue'
    ],

    theme: {
        colors: colors,
    },

    variants: {
        extend: {},
    },

    plugins: [],
};
