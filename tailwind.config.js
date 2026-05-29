module.exports = {
  content: [
    './resources/**/*.blade.php',
    './resources/**/*.js',
    './resources/**/*.vue',
    './resources/**/*.php',
    './resources/**/*.html',
  ],
  theme: {
    extend: {
      borderRadius: {
        '24': '24px',
      },
      boxShadow: {
        'custom': '0 8px 30px rgba(0,0,0,0.04)',
      },
      colors: {
        primary: {
          600: '#2563EB', // Accent Primary
        },
        success: {
          600: '#10B981', // Accent Success
        },
        error: {
          600: '#DC2626', // Accent Error
        },
      },
    },
  },
  plugins: [],
};
