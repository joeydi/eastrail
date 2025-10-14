const defaults = require('@wordpress/scripts/config/webpack.config');
const path = require('path');

module.exports = {
  ...defaults,
  externals: {
    react: 'React',
    'react-dom': 'ReactDOM',
  },
  entry: {
      admin: './src/admin/index.js',
      gutenberg_checkout_block: './assets/js/gutenberg_checkout_block/index.js',
    //   index: './src/index.js'
      // frontend: './src/frontend/index.js',
      // appointments: './src/appointments/index.js'
  },
  output: {
      path: path.resolve(__dirname, 'build'),
      filename: (pathData) => {
        if (pathData.chunk.name === 'gutenberg_checkout_block') {
          return 'checkout_block/index.js'; // Place Gutenberg block files in the 'checkout_block' folder
        }
        return '[name].bundle.js'; // Default output for other entries
      },
  },
  module: {
    rules: [
      ...defaults.module.rules,
      {
        test: /.svg$/,
        use: ['@svgr/webpack', 'url-loader'],
      },
    ],
  },
};