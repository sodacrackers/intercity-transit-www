const path = require('path');
const isDevMode = process.env.NODE_ENV !== 'production';

const config = {
    entry: {
        main: ["./js/src/index.jsx"]
    },
    devtool: (isDevMode) ? 'source-map' : false,
    mode: (isDevMode) ? 'development' : 'production',
    output: {
        path: isDevMode ? path.resolve(__dirname, "js/dist_dev") : path.resolve(__dirname, "js/dist"),
        filename: '[name].min.js'
    },
    resolve: {
        extensions: ['.js', '.jsx'],
    },
    module: {
        rules: [
            {
                test: /\.jsx?$/,
                loader: 'babel-loader',
                exclude: /node_modules/,
                include: path.join(__dirname, 'js/src'),
            },
            {
              test: /\.(sass|less|css)$/,
              use: ["style-loader", "css-loader", 'sass-loader'],
            },
            {
              test: /\.svg$/,
              use: [
                {
                  loader: 'svg-url-loader',
                  options: {
                    limit: 10000,
                  },
                },
              ],
            },
        ],
    },
};

module.exports = config;