

// project variables

var themePathCss = './css/';
var themePathJs = './js/';

var themeSassIncludePaths = [
	themePathCss + 'scss'
];


// required variables

var gulp = require('gulp');
var $ = require('gulp-load-plugins')();


// theme tasks

gulp.task('theme:css:dev', function() {
	return gulp.src(themePathCss + 'scss/style.scss')
		.pipe($.sourcemaps.init())
		.pipe($.sass({ includePaths: themeSassIncludePaths })
			.on('error', $.notify.onError({ title: 'SASS Compilation Error', message: '<%= error.message %>' })))
		.pipe($.autoprefixer({ browsers: [ 'last 2 versions', 'ie >= 9' ] }))
		// .pipe($.cssnano())
		.pipe($.sourcemaps.write('../'))
		.pipe(gulp.dest(themePathCss))
		.pipe($.notify({ title: 'CSS Compiled Successfully', message: '<%= file.relative %>', onLast: true }))
});

gulp.task('theme:css:prod', ['theme:css:dev'], function() {
	return gulp.src(themePathCss + 'style.css')
		.pipe($.bless())
		.pipe(gulp.dest(themePathCss));
});

gulp.task('theme:js:prod', function() {
	return gulp.src([ themePathJs + '**/*.js', '!' + themePathJs + '**/*.min.js' ])
		.pipe($.uglify())
		.on('error', $.notify.onError({ title: 'JS Minification Error', message: '<%= error.message %>' }))
		.pipe($.rename({ extname: '.min.js' }))
		.pipe(gulp.dest(themePathJs + '/'))
		.pipe($.notify({ title: 'JS Minified Successfully', message: '<%= file.relative %>' }));
});


// watch tasks

gulp.task('watch:dev', function() {
	gulp.watch(themePathCss + 'scss/**/*.scss', ['theme:css:dev']);
	gulp.watch([ themePathJs + '**/*.js', '!' + themePathJs + '**/*.min.js' ], ['theme:js:prod']);
});

gulp.task('watch:prod', function() {
	gulp.watch(themePathCss + 'scss/style.scss', ['theme:css:prod']);
	gulp.watch([ themePathJs + '**/*.js', '!' + themePathJs + '**/*.min.js' ], ['theme:js:prod']);
});


// default task

gulp.task('default', ['watch:dev']);