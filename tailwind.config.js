/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./public/**/*.php",
    "./resources/views/**/*.php",
    "./app/Utils/**/*.php",
    "./app/Helpers/**/*.php",
    "./public/assets/js/**/*.js",
  ],
  theme: {
    extend: {},
  },
  plugins: [require("@tailwindcss/forms")],
};
