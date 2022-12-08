/**
 * Gulp file
 *
 * @author Takuto Yanagida
 * @version 2022-12-08
 */

const SRC_JS_RAW = ['src/**/*.js', '!src/**/*.min.js'];
const SRC_PHP    = ['src/**/*.php'];
const DEST       = './dist';

import gulp from 'gulp';

import { makeJsTask } from './gulp/task-js.mjs';
import { makeCopyTask } from './gulp/task-copy.mjs';

const js  = makeJsTask(SRC_JS_RAW, DEST, 'src');
const php = makeCopyTask(SRC_PHP, DEST);

const watch = done => {
	gulp.watch(SRC_JS_RAW, gulp.series(js));
	gulp.watch(SRC_PHP, gulp.series(php));
	done();
};

export const build = gulp.parallel(js, php);
export default gulp.series(build , watch);
