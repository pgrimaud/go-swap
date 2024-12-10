/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./assets/**/*.js",
    "./templates/**/*.html.twig"
  ],
  safelist: [
    'from-purple-400',
    'from-green-400',
    'from-pink-400',
    'to-purple-600',
    'to-green-600',
    'to-pink-600',
    'border-green-500',
    'bg-green-700',
    'border-yellow-500',
    'bg-yellow-700',
    'border-red-500',
    'bg-red-700',
    'border-white',
    'border-2',
    'bg-slate-600',
    'border-gray-500',
    'text-red-500',
    'text-orange-500',
    'text-green-500',
    'w-[25px]'
  ],
  theme: {
    extend: {},
  },
  plugins: [],
}

