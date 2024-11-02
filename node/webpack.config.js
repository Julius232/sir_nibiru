// webpack.config.js
const path = require('path');
const webpack = require('webpack');

module.exports = {
    entry: './src/script.js', // Update with your actual script.js path
    output: {
        filename: 'bundle.js',
        path: path.resolve(__dirname, '../dist'), // Adjust the output path as needed
    },
    resolve: {
        fallback: {
            "buffer": require.resolve("buffer/"),
            "stream": require.resolve("stream-browserify"),
            "crypto": require.resolve("crypto-browserify"),
            "assert": require.resolve("assert/"),
            "util": require.resolve("util/"),
            "http": require.resolve("stream-http"),
            "https": require.resolve("https-browserify"),
            "os": require.resolve("os-browserify/browser"),
            "url": require.resolve("url/"),
            "zlib": require.resolve("browserify-zlib"),
        },
    },
    plugins: [
        new webpack.ProvidePlugin({
            Buffer: ['buffer', 'Buffer'],
            process: 'process/browser',
        }),
    ],
    module: {
        rules: [
            {
                test: /\.m?js$/,
                resolve: {
                    fullySpecified: false, // For webpack 5 compatibility
                },
            },
            {
                test: /\.js$/,
                exclude: /node_modules/,
                use: {
                    loader: 'babel-loader',
                },
            },
        ],
    },
    mode: 'production', // or 'development' as needed
};
