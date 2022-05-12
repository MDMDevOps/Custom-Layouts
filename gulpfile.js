const gulp         = require('gulp');
const sass         = require('gulp-sass')(require('sass'));
const postcss      = require('gulp-postcss');
const autoprefixer = require('autoprefixer');
const cssnano      = require('cssnano');
const pxtorem      = require('postcss-pxtorem');
const gulpWebpack  = require('webpack-stream');
const uglify       = require("gulp-uglify");
const sourcemaps   = require('gulp-sourcemaps');
const rename       = require('gulp-rename');
const path         = require('path');
const glob         = require("glob")

/**
 * Helper function to glob all .js files in src directory
 */
var webpack_entries = glob.sync( 'src/scripts/*.js' ).reduce( ( files, file ) => {

	let filename =  path.basename( file );

	let name = path.parse( filename ).name;

	files[name] = './src/scripts/' + filename;

	return files;

}, {} );
/**
 * Helper function to retrieve wepack config
 */
const _getWebpackConfig = ( mode = 'production' ) => {

	let config = {
		entry : webpack_entries,
		output : {
			filename: '[name].js',
		},
		mode: mode,
        watch : false,
		externals: {
		  'jquery': 'jQuery'
		},
		module : {
			rules : [
				{
					test: /.js$/,
					exclude: /(node_modules)/,
					use : {
						loader : 'babel-loader',
						options: {
							presets: [ "@wordpress/babel-preset-default" ],
							plugins: ["@babel/plugin-proposal-object-rest-spread"]
						}
					}
				}
			]
		}
	}

	return config
}

gulp.task( 'buildcss', function(){
	return gulp.src( 'src/styles/**/*.scss' )
	.pipe( sourcemaps.init() )
	.pipe( sass().on( 'error', sass.logError ) )
	.pipe( sourcemaps.write() )
	.pipe( postcss( [ autoprefixer( { grid : 'autoplace' } ), pxtorem() ] ) )
	.pipe( gulp.dest( './assets/css' ) )
	.pipe( rename( { suffix: '.min' } ) )
	.pipe( postcss( [ cssnano() ] ) )
	.pipe( gulp.dest( './assets/css' ) );
} );

gulp.task( 'buildjs', function() {

	return gulp.src( 'src/scripts/*.js' )
	/**
	 * Build for development
	 * Build with webpack, with mode set to development and save unminified file w/sourcemap
	 */
    .pipe( gulpWebpack({ config: _getWebpackConfig('development') }, require('webpack') ) )
	// Recover from errors
    .on( 'error', function(err) {
        this.emit('end'); // Recover from errors
    })
	// Send to destination
	.pipe( gulp.dest( 'assets/js' ) )
	/**
	 * Build for production
	 * Build with webpack, with mode set to production, minify, rename, and do not produce a sourcemap
	 */
	.pipe( gulpWebpack( { config : _getWebpackConfig( 'production' ) }, require( 'webpack' ) ) )
	// Recover from errors
	.on( 'error', function() {
		this.emit( 'end' );
	})
	// Create a min version
	.pipe( rename( { suffix: '.min' } ) )
	// Uglify it
	.pipe( uglify() )
	// Save production version
	.pipe( gulp.dest( 'assets/js' ) )
} ) ;

gulp.task( 'watch', function(){
	gulp.watch( 'src/scripts/**/*.js', gulp.series( 'buildjs' ) );
	gulp.watch( 'src/styles/**/*.scss', gulp.series( 'buildcss' ) );
});

gulp.task('default', gulp.series( [ 'watch' ] ));