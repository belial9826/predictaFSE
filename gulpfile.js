const { src, dest, watch, series, parallel } = require('gulp');

const sass = require('gulp-sass')(require('sass'));
const cleanCSS = require('gulp-clean-css');
const uglify = require('gulp-uglify');
const rename = require('gulp-rename');
const sourcemaps = require('gulp-sourcemaps');

const styleEntries = [
  'source/scss/main.scss',
  'source/scss/home-entry.scss',
  'source/scss/contact-entry.scss',
  'source/scss/faq-entry.scss',
  'source/scss/pronosticos-entry.scss',
  'source/scss/partido-entry.scss',
  'source/scss/woocommerce-entry.scss',
];

const paths = {
  js: 'source/js/**/*.js',
  cssDest: 'assets/css',
  jsDest: 'assets/js',
};

function styles() {
  return src(styleEntries)
    .pipe(sourcemaps.init())
    .pipe(sass().on('error', sass.logError))
    .pipe(cleanCSS())
    .pipe(rename(function (path) {
      path.basename = path.basename.replace('-entry', '');
    }))
    .pipe(rename({ suffix: '.min' }))
    .pipe(sourcemaps.write('.'))
    .pipe(dest(paths.cssDest));
}

function scripts() {
  return src(paths.js)
    .pipe(sourcemaps.init())
    .pipe(uglify())
    .pipe(rename({ suffix: '.min' }))
    .pipe(sourcemaps.write('.'))
    .pipe(dest(paths.jsDest));
}

function watcher() {
  watch('source/scss/**/*.scss', styles);
  watch(paths.js, scripts);
}

exports.styles = styles;
exports.scripts = scripts;
exports.watch = watcher;
exports.default = series(
  parallel(styles, scripts),
  watcher
);
