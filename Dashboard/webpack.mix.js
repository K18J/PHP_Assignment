const mix = require('laravel-mix');

mix.js('resources/js/dashboard.js', 'public/js')
   .sass('resources/sass/dashboard.scss', 'public/css')
   .options({
      processCssUrls: false
   })
   .sourceMaps()
   .version();

if (mix.inProduction()) {
   mix.version();
} else {
   mix.sourceMaps();
}
