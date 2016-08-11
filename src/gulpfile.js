// Include gulp
var gulp = require('gulp');

// Include Our Plugins
var sass = require('gulp-sass');
var concat = require('gulp-concat');
var coffee = require('gulp-coffee');
var uglify = require('gulp-uglify');
var rename = require('gulp-rename');

// Compile Our Sass
gulp.task('public-sass', function() {
    return gulp.src('../src/public/scss/*.scss')
        .pipe(sass())
        .pipe(rename('wp-discography-public.css'))
        .pipe(gulp.dest('../public/css'));
});

gulp.task('admin-sass', function() {
    return gulp.src('../src/admin/scss/*.scss')
        .pipe(sass())
        .pipe(rename('wp-discography-admin.css'))
        .pipe(gulp.dest('../admin/css'));
});

// Concatenate & Minify JS
gulp.task('public-coffee', function() {
  return gulp.src('../src/public/coffee/*.coffee')
    .pipe(coffee({bare: true}))
    .pipe(gulp.dest('../src/public/js'))
    .pipe(rename('wp-discography-public.min.js'))
    //.pipe(uglify())
    .pipe(gulp.dest('../public/js'));
});

gulp.task('admin-coffee', function() {
  return gulp.src('../src/admin/coffee/*.coffee')
    .pipe(coffee({bare: true}))
    .pipe(gulp.dest('../src/admin/js'));
});

gulp.task('public-js', ['public-coffee'], function() {
  return gulp.src(['../src/node_modules/plyr/dist/plyr.js', '../src/public/js/public.js'])
    .pipe(concat('wp-discography-public.js'))
    .pipe(rename('wp-discography-public.min.js'))
    //.pipe(uglify())
    .pipe(gulp.dest('../public/js'));
});

gulp.task('admin-js', ['admin-coffee'], function() {
  return gulp.src(['../src/node_modules/plyr/dist/plyr.js', '../src/admin/js/admin.js'])
    .pipe(concat('wp-discography-admin.js'))
    .pipe(rename('wp-discography-admin.min.js'))
    //.pipe(uglify())
    .pipe(gulp.dest('../admin/js'));
});

// Watch Files For Changes
gulp.task('watch', function() {
    gulp.watch('./public/coffee/*.coffee', ['public-js']);
    gulp.watch('./public/scss/**/*.scss', ['public-sass']);

    gulp.watch('./admin/coffee/*.coffee', ['admin-js']);
    gulp.watch('./admin/scss/**/*.scss', ['admin-sass']);
});

// Default Task
gulp.task('default', ['watch']);