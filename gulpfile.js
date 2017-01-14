var gulp = require('gulp'),
	sass = require('gulp-sass'),
	sourcemaps = require('gulp-sourcemaps'),
	prefix = require('gulp-autoprefixer'),
	gutil = require('gulp-util'),
	gulpif = require('gulp-if'),
	minify = require('gulp-minify'),
	concat = require('gulp-concat'),
    notify = require('gulp-notify'),
	browsersync = require('browser-sync'),
	fileinclude = require('gulp-file-include'),
	cssnano = require('gulp-cssnano');

var env,
	jsSources,
	sassSources,
    sassSources_rtl,
	htmlSources,
	outputDir;

env = process.env.NODE_ENV || 'development';
if (env === 'development') {
	outputDir = 'builds/development/';
} else {
	outputDir = 'builds/production/';
}

jsSources = ['components/scripts/owl.carousel.min.js', 'components/scripts/jquery.magnific-popup.js', 'components/scripts/jquery.singlePageNav.min.js', 'components/scripts/customScript.js'];
sassSources = ['components/sass/app.scss'];
htmlSources = [outputDir + '*.html'];

//BrowserSync Function
gulp.task('browser-sync', function() {
    browsersync({
        // Change the director name for static site
		server: {
            baseDir: "./builds/development"
        }
    });
});

// Swallow Error Function to prevent error from breaking the task running
function swallowError (error) {
  // If you want details of the error in the console
  console.log(error.toString())
  this.emit('end')
}

// Browser Sync reload function
gulp.task('browsersync-reload', function () {
    browsersync.reload();
});

// SASS function
gulp.task('sass', function () {
	gulp.src(sassSources)
		.pipe(sourcemaps.init())
		.pipe(sass({
			includePaths: ['components/sass/**/*']
		}).on('error', sass.logError))
		.pipe(prefix(['last 15 versions', '> 1%', 'ie 8', 'ie 7'], { cascade: true }))
		.pipe(sourcemaps.write())
		.pipe(gulp.dest('builds/development/css'))
		.pipe(browsersync.reload({ stream:true }))
		.pipe(notify({ message: 'SASS task complete' }));
});

// Minify CSS using "CSSNano" package
gulp.task('minifyCSS', function () {
  return gulp.src('builds/development/css/app.css')
    .pipe(cssnano())
    .pipe(gulp.dest('builds/production/css'))
    .pipe(notify({ message: 'MINIFY CSS COMPLETE' }));
});

// js function
gulp.task('js', function() {
	gulp.src(jsSources)
		.pipe(concat('script.js'))
		.on('error', gutil.log)
		.pipe(gulpif(env === 'production', minify({
			ext: {
				min:".js"
			},
			noSource: true
		})))
		.pipe(gulp.dest(outputDir + 'js'))
		.pipe(browsersync.reload({ stream:true }))
		.pipe(notify({ message: 'JS task complete' }));
});

// Copy images to production
gulp.task('move', function() {
	gulp.src('builds/development/images/**/*.*')
		.pipe(gulpif(env === 'production', gulp.dest(outputDir + 'images')));
});

// BrowserSync Function and Watch Function
gulp.task('server', ['browser-sync'], function() {

    gulp.watch("components/sass/**/*.scss", ['sass']);
    gulp.watch("builds/development/*.html", ['browsersync-reload']);
    gulp.watch("components/scripts/**/*.js", ['js']);
});

gulp.task('default', ['server', 'js', 'sass', 'minifyCSS', 'move']);
