var gulp = require('gulp'),
    gutil = require('gulp-util'),
    compass = require('gulp-compass'),
    gulpif = require('gulp-if'),
    uglify = require('gulp-uglify'),
    concat = require('gulp-concat'),
    notify = require('gulp-notify'),
    browsersync = require('browser-sync');

var env,
    jsSources,
    sassSources,
    sassSources_rtl,
    htmlSources,
    outputDir,
    sassStyle;

env = process.env.NODE_ENV || 'development';
if (env === 'development') {
    outputDir = 'builds/development/';
    sassStyle = 'expanded';
} else {
    outputDir = 'builds/production/';
    sassStyle = 'compressed';
}

jsSources = ['components/scripts/owl.carousel.min.js', 'components/scripts/jquery.magnific-popup.js', 'components/scripts/jquery.singlePageNav.min.js', 'components/scripts/customScript.js'];
sassSources = ['components/sass/app.scss'];
htmlSources = [outputDir + '*.html'];

//BrowserSync Function
gulp.task('browser-sync', function() {
    browsersync({
        // Fill This with proxy domain
        proxy: 'http://shakerhamdi',
        port: 3000
    });
});

gulp.task('browsersync-reload', function () {
    browsersync.reload();
});

// js function
gulp.task('js', function() {
    gulp.src(jsSources)
        .pipe(concat('script.js'))
        // .pipe(browserify())
        .on('error', gutil.log)
        .pipe(gulpif(env === 'production', uglify()))
        .pipe(gulp.dest(outputDir + 'js'))
        .pipe(notify({ message: 'JS task complete' }));
});

// compass function
gulp.task('compass', function() {
    gulp.src(sassSources)
        .pipe(compass({
                sass: 'components/sass/',
                image: outputDir + 'images',
                sourcemap: true,
                style: sassStyle,
                css: outputDir + 'css',
                require: ['susy', 'breakpoint']
            })
            .on('error', gutil.log))
        .pipe(browsersync.reload({ stream:true }))
        .pipe(notify({ message: 'Compass task complete' }));
});

// html function
gulp.task('html', function() {
    gulp.src('builds/development/*.html')
        .pipe(gulpif(env === 'production', gulp.dest(outputDir)));
});

// php function
gulp.task('php', function() {
    gulp.src('builds/development/*.php')
        .pipe(gulpif(env === 'production', gulp.dest(outputDir)));
});

// Copy images to production
gulp.task('move', function() {
    gulp.src('builds/development/images/**/*.*')
        .pipe(gulpif(env === 'production', gulp.dest(outputDir + 'images')));
});

// Copy videos to production
gulp.task('moveVideos', function () {
    gulp.src('builds/development/videos/**/*.*')
        .pipe(gulpif(env === 'production', gulp.dest(outputDir + 'videos')));
});

// BrowserSync Function and Watch Function
gulp.task('server', ['browser-sync'], function() {

    gulp.watch("components/sass/**/*.scss", ['compass']);
    gulp.watch("builds/development/*.html", ['browsersync-reload']);
    gulp.watch("builds/development/*.php", ['browsersync-reload']);
    gulp.watch("components/scripts/*.js", ['js', 'browsersync-reload']);
});

gulp.task('default', ['server', 'html', 'php', 'js', 'compass', 'move', 'moveVideos']);
