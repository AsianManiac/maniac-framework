/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./resources/**/*.niac.php", // Scan all .niac.php files
    "./resources/**/*.js", // Scan JS files if you use JS to add classes
  ],
  theme: {
    extend: {
      // Add custom theme extensions here if needed
      fontFamily: {
        sans: ["Inter", "sans-serif"], // Example font override
      },
    },
  },
  plugins: [
    // Add Tailwind plugins here if needed (e.g., @tailwindcss/forms)
  ],
};
