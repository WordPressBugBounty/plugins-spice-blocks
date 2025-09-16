const defaultConfig = require("@wordpress/scripts/config/webpack.config");

module.exports = {
    ...defaultConfig,
    entry: {
        'free-blocks': './src/free-blocks/index.js',  // Path to free blocks JS
        'premium-blocks': './src/premium-blocks/index.js' // Path to premium blocks JS
    },
    output: {
        path: __dirname + '/build',
        filename: '[name].bundle.js'  // Output different files for each entry point
    }
};