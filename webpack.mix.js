const mix = require('laravel-mix');

mix.disableNotifications();

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel applications. By default, we are compiling the CSS
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.js('resources/js/app.js', 'public/js')
    .copy('node_modules/matter-js/build/matter.min.js', 'public/js/matter.min.js')
    .postCss('resources/css/app.css', 'public/css', [
        //
    ])
    .override(config => {
        config.plugins = (config.plugins || []).filter(
            plugin => plugin && plugin.constructor && plugin.constructor.name !== 'WebpackBarPlugin'
        );
    });
