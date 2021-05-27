const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

// Compile sass file
mix.sass("resources/sass/app.scss", "public/dist/css");
mix.sass("resources/sass/custom.scss", "public/dist/css");
mix.sass("resources/sass/detail.scss", "public/dist/css");
mix.sass("resources/sass/pdf_style.scss", "public/dist/css");
mix.sass("resources/sass/pdf_style_a6.scss", "public/dist/css");
//
// // Combine local Javascript file.
mix.copy("resources/js/custom.js", "public/dist/js");
mix.copy("resources/js/detail/job/joborder.js", "public/dist/js/detail/job");
mix.copy("resources/js/detail/job/trucking/hppcalculator.js", "public/dist/js/detail/job/trucking");
mix.combine([
    "resources/js/app.js",
    "resources/js/html_dom.js"], "public/dist/js/app.js");
mix.version();
