//gulp-less gulp-minify-css gulp-autoprefixer gulp-rename gulp-concat gulp-uglify gulp-notify gulp-imagemin gulp-jshint gulp-clean gulp-cache gulp-util

var gulp = require('gulp'),
	less = require('gulp-less'),
	cssmin = require('gulp-minify-css'),
	autoprefixer = require('gulp-autoprefixer'),
	rename = require('gulp-rename'),
	concat = require('gulp-concat'),
	uglify = require('gulp-uglify'),
	notify = require('gulp-notify'),
	imagemin = require('gulp-imagemin'),
	jshint = require('gulp-jshint'),
	clean = require('gulp-clean'),
	cache = require('gulp-cache'),
	gutil = require('gulp-util');


//less
gulp.task('testLess' , function () {
	return gulp.src('res/less/**/*.less')
	.pipe(less())
	.on('error', function(err) {
		gutil.log('Less Error!', err.message);
		this.end();
	})
	.pipe(autoprefixer())
	.pipe(cssmin())
	.pipe(gulp.dest('dist/assets/css'));
});

//js
gulp.task('testJs' , function () {
	return gulp.src('src/**/*.js')
	.pipe(jshint())
	.on('error', function(err) {
		gutil.log('Js Error!', err.message);
		this.end();
	})
	.pipe(jshint.reporter('default'))
	.pipe(concat('main.js'))
	.pipe(gulp.dest('dist/assets/js'))
	.pipe(rename({suffix:'.min'}))
	.pipe(uglify())
	.pipe(gulp.dest('dist/assets/js'))
	.pipe(notify({message:'Script task complete' }));
});

//images
gulp.task('testImg' , function () {
	return gulp.src('res/images/**/*')
	.pipe(cache(imagemin({ optimizationLevel: 5, progressive: true, interlaced: true })))
	.pipe(gulp.dest('dist/assets/images'))
	.pipe(notify({ message: 'Images task complete' }));
});

//clean
gulp.task('clean' , function (){
	return gulp.src(['dist/assests/css','dist/assests/js','dist/assests/images'],{read : false})
	.pipe(clean());
});

//default
gulp.task('default' , ['clean'] ,function (){
	gulp.start('testLess','testJs','testImg','watch');
});

//watch
gulp.task('watch' , function () {
	gulp.watch('res/less/**/*.less',['testLess']);	
	gulp.watch('src/**/*.js',['testJs']);	
	gulp.watch('res/images/**/*',['testImg']);	
});
