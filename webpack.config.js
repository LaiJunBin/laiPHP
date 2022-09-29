const path = require('path');
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const CssMinimizerPlugin = require("css-minimizer-webpack-plugin");

module.exports = {
    plugins: [new MiniCssExtractPlugin({
        filename: '../css/app.css'
    })],
    entry: './resources/js/app.js',
    output: {
        filename: 'app.js',
        path: path.resolve(__dirname, 'public/assets/js'),
        libraryTarget: 'umd',
    },
    module: {
        rules: [
            {
                test: /\.css$/i,
                use: [MiniCssExtractPlugin.loader, "css-loader"],
            },
        ],
    },
    optimization: {
        minimizer: [
            '...',
            new CssMinimizerPlugin(),
        ],
    },
};