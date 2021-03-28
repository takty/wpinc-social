<?php
/**
 * Favicon
 *
 * @package Wpinc Social
 * @author Takuto Yanagida
 * @version 2021-03-28
 */

namespace wpinc\social\favicon;

/**
 * Output favicon images.
 *
 * @param string $dir_url The url to image directory.
 */
function the_favicon( string $dir_url ) {
	$dir_url = trailingslashit( $dir_url );
	?>
	<link rel="icon" href="<?php echo esc_attr( $dir_url . 'favicon.ico' ); ?>">
	<link rel="icon" type="image/png" href="<?php echo esc_attr( $dir_url . 'icon-192.png' ); ?>">
	<link rel="apple-touch-icon" type="image/png" href="<?php echo esc_attr( $dir_url . 'icon-180.png' ); ?>">
	<?php
}
