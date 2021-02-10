const path = require('path');
const webpack = require('webpack');
const fs =require('fs');
const publicPath = process.env.NODE_ENV === 'development' ? '/dist/' : './dist/';

const appVer = '1.0';
const appName = "cms.apk";
const downloadUrl = `http://cms.shixiongjiaxiao.com/download/${appName}`;

var version = JSON.stringify({
    version:appVer,
    vercode: new Date().getTime(),
    url: downloadUrl,
});

fs.writeFileSync('./src/config/version.js', `export default ${version}`);

module.exports = {
    entry: './src/main.js',
    output: {
      filename: 'build.js',
      path: path.resolve(__dirname, './dist'),
      publicPath: publicPath,
    },
    module: {
        rules: [{
                test: /\.css$/,
                use: [
                    'vue-style-loader',
                    'css-loader'
                ],
            }, {
                test: /\.vue$/,
                loader: 'vue-loader',
                options: {
                    loaders: {}
                    // other vue-loader options go here
                }
            },
            {
                test: /\.js$/,
                loader: 'babel-loader?cacheDirectory=true',
                include: [path.resolve(__dirname, 'src')],
                exclude: /node_modules/
            },
            {
                test: /\.(png|jpg|gif|svg|ttf|woff|woff2|eot)$/,
                loader: 'file-loader',
                options: {
                    name: '[name].[ext]?[hash]'
                }
            }
        ]
    },
    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'src'),
            'vue$': 'vue/dist/vue.esm.js',
        },
        extensions: ['*', '.js', '.vue', '.json']
    },
    devServer: {
        historyApiFallback: true,
        noInfo: true,
        overlay: true
    },
    performance: {
        hints: false
    },
    devtool: '#eval-source-map'
};

if (process.env.NODE_ENV === 'production') {
    module.exports.devtool = '#source-map';
        // http://vue-loader.vuejs.org/en/workflow/production.html
    module.exports.plugins = (module.exports.plugins || []).concat([
        new webpack.DefinePlugin({
            'process.env': {
                NODE_ENV: '"production"'
            }
        }),
        new webpack.optimize.UglifyJsPlugin({
            sourceMap: true,
            compress: {
                warnings: false
            }
        }),
        new webpack.LoaderOptionsPlugin({
            minimize: true
        })
    ])
}
