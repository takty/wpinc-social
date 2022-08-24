/**
 * Gulp file
 *
 * @author Takuto Yanagida
 * @version 2022-08-23
 */

/* eslint-disable no-undef */
'use strict';

const SRC_JS_RAW = ['src/**/*.js', '!src/**/*.min.js'];
const SRC_PHP    = ['src/**/*.php'];
const DEST       = './dist';

const gulp = require('gulp');

const { makeCopyTask } = require('./task-copy');
const { makeJsTask }   = require('./task-js');


// -----------------------------------------------------------------------------


const js  = makeJsTask(SRC_JS_RAW, DEST, 'src');
const php = makeCopyTask(SRC_PHP, DEST);

const watch = done => {
	gulp.watch(SRC_JS_RAW, gulp.series(js));
	gulp.watch(SRC_PHP, gulp.series(php));
	done();
};

exports.build   = gulp.parallel(js, php);
exports.default = gulp.series(exports.build , watch);
