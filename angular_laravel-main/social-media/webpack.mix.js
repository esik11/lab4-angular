const mix = require('laravel-mix');

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

// Compile the CSS file
mix.css('resources/css/app.css', 'public/css')
    .postCss('resources/css/app.css', 'public/css', [
        require('tailwindcss'),
    ]);

// Compile the JavaScript file
mix.js('resources/js/app.js', 'public/js'); // Add this line to compile app.js

// Optionally, you can also add versioning for cache busting in production
if (mix.inProduction()) {
    mix.version();
}
