/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{js,jsx,ts,tsx}",
  ],
  theme: {
    extend: {
      colors: {
        // Dark mode palette (mastervacationhomes.com inspired)
        'dark': {
          900: '#0a0a0a',  // Deepest dark for navs/footers
          800: '#1a1a1a',  // Section backgrounds
          700: '#2a2a2a',  // Card backgrounds
          600: '#3a3a3a',  // Borders
        },
      },
      fontFamily: {
        sans: ['Poppins', 'sans-serif'],
        heading: ['Montserrat', 'sans-serif'],
      },
    },
  },
  plugins: [],
} 