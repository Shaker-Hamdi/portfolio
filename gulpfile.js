var gulp = require('gulp'),
	gutil = require('gulp-util'),
	browserify = require('gulp-browserify'),
	compass = require('gulp-compass'),
	connect = require('gulp-connect'),
	gulpif = require('gulp-if'),
	uglify = require('gulp-uglify'),
	concat = require('gulp-concat');

var env,
	jsSources,
	sassSources,
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

jsSources = ['components/scripts/owl.carousel.min.js', 'components/scripts/customScript.js'];
sassSources = ['components/sass/style.scss'];
htmlSources = [outputDir + '*.html'];

// js function
gulp.task('js', function() {
	gulp.src(jsSources)
		.pipe(concat('script.js'))
		// .pipe(browserify())
		.on('error', gutil.log)
		.pipe(gulpif(env === 'production', uglify()))
		.pipe(gulp.dest(outputDir + 'js'))
		.pipe(connect.reload());
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
		// .pipe(gulp.dest(outputDir + 'css'))
		.pipe(connect.reload());
});

// html function
gulp.task('html', function() {
	gulp.src('builds/development/*.html')
		.pipe(connect.reload())
		.pipe(gulpif(env === 'production', gulp.dest(outputDir)));
});

// autoreload function
gulp.task('connect', function() {
	connect.server({
		root: 'builds/development/',
		livereload: true
	});
});

// watch function
gulp.task('watch', function() {
	gulp.watch(jsSources, ['js']);
	gulp.watch('components/sass/**/*.scss', ['compass']);
	gulp.watch('builds/development/*.html', ['html']);
});

// Copy images to production
gulp.task('move', function() {
	gulp.src('builds/development/images/**/*.*')
		.pipe(gulpif(env === 'production', gulp.dest(outputDir + 'images')))
});

gulp.task('default', ['watch', 'html', 'js', 'compass', 'move', 'connect']);