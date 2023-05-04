/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./assets/**/*.js",
    "./templates/**/*.html.twig",
  ],
  safelist: [
    'from-purple-400',
    'from-green-400',
    'from-pink-400',
    'to-purple-600',
    'to-green-600',
    'to-pink-600',
  ],
  theme: {
    extend: {},
  },
  plugins: [],
}

