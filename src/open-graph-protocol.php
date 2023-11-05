<?php
/**
 * Open Graph Protocol
 *
 * @package Wpinc Socio
 * @author Takuto Yanagida
 * @version 2023-11-04
 */

declare(strict_types=1);

namespace wpinc\socio;

require_once __DIR__ . '/assets/url.php';
require_once __DIR__ . '/site-meta.php';

const OGP_NS = 'prefix="og:http://ogp.me/ns#"';

/** phpcs:ignore
 * Outputs the open graph protocol meta tags.
 *
 * phpcs:ignore
 * @param array{
 *     default_image_url?  : string,
 *     do_append_site_name?: bool,
 *     separator?          : string,
 *     excerpt_length?     : int,
 *     alt_description?    : string,
 *     image_size?         : string,
 *     image_meta_key?     : string,
 *     alt_image_url?      : string,
 * } $args (Optional) Options.
 *
 * $args {
 *     (Optional) Options.
 *
 *     @type string 'default_image_url'   Default image URL.
 *     @type bool   'do_append_site_name' Whether the site name is appended.
 *     @type string 'separator'           Separator between the page title and the site name.
 *     @type int    'excerpt_length'      The length of excerpt.
 *     @type string 'alt_description'     Alternative description.
 *     @type string 'image_size'          The image size.
 *     @type string 'image_meta_key'      Meta key of image.
 *     @type string 'alt_image_url'       Alternative image URL.
 * }
 */
function the_ogp( array $args = array() ): void {
	$args += array(
		'do_append_site_name' => true,
		'separator'           => ' - ',
		'excerpt_length'      => 100,
		'alt_description'     => '',
		'default_image_url'   => '',
		'image_size'          => 'large',
		'image_meta_key'      => '',
		'alt_image_url'       => '',
	);

	$prop_vs = array(
		'type'        => is_single() ? 'article' : 'website',
		'url'         => (string) \wpinc\get_current_url(),
		'title'       => \wpinc\socio\get_the_title( $args['do_append_site_name'], $args['separator'] ),
		'description' => _get_the_description( $args['excerpt_length'], $args['alt_description'] ),
		'site_name'   => \wpinc\socio\get_site_name(),
	);
	$img_url = _get_the_image( $args['default_image_url'], $args['image_size'], $args['image_meta_key'], $args['alt_image_url'] );
	$tw_card = empty( $img_url ) ? 'summary' : 'summary_large_image';

	foreach ( $prop_vs as $prop => $val ) {
		echo '<meta property="og:' . esc_attr( $prop ) . '" content="' . esc_attr( $val ) . '">' . "\n";
	}
	if ( ! empty( $img_url ) ) {
		echo '<meta property="og:image" content="' . esc_attr( $img_url ) . '">' . "\n";
		if ( class_exists( 'Simply_Static\Plugin' ) ) {
			echo '<link href="' . esc_attr( $img_url ) . '"><!-- for simply static -->' . "\n";
		}
	}
	echo '<meta name="twitter:card" content="' . esc_attr( $tw_card ) . '">' . "\n";
}

/**
 * Retrieves the description of the current page.
 *
 * @access private
 * @global \WP_Post $post
 *
 * @param int    $excerpt_length  The length of excerpt.
 * @param string $alt_description Alternative description.
 * @return string The description.
 */
function _get_the_description( int $excerpt_length, string $alt_description ): string {
	if ( ! empty( $alt_description ) ) {
		return $alt_description;
	}
	$desc = '';
	global $post;
	if ( ! is_front_page() && is_singular() ) {
		$text = get_the_content( '', false, $post );
		$text = strip_shortcodes( $text );
		$text = excerpt_remove_blocks( $text );
		$text = apply_filters( 'the_content', $text );
		$text = str_replace( ']]>', ']]&gt;', $text );
		$text = str_replace( array( "\r\n", "\r", "\n" ), ' ', $text );

		$desc = wp_strip_all_tags( $text, true );  // @phpstan-ignore-line
		$desc = preg_replace( '/\A[\p{C}\p{Z}]++|[\p{C}\p{Z}]++\z/u', '', $desc ) ?? $desc;  // Multi-byte trim.
		if ( mb_strlen( $desc ) > $excerpt_length ) {
			$desc = mb_substr( $desc, 0, $excerpt_length - 3 ) . '...';
		}
	}
	if ( empty( $desc ) ) {
		$desc = \wpinc\socio\get_site_description();
	}
	if ( empty( $desc ) ) {
		$desc = \wpinc\socio\get_site_name();
	}
	return $desc;
}

/**
 * Retrieves the image of the current page.
 *
 * @access private
 * @global \WP_Post $post
 *
 * @param string $default_image_url Default image URL.
 * @param string $size              The image size.
 * @param string $meta_key          Meta key of image.
 * @param string $alt_image_url     Alternative image URL.
 * @return string The image URL.
 */
function _get_the_image( string $default_image_url, string $size, string $meta_key, string $alt_image_url ): string {
	if ( ! empty( $alt_image_url ) ) {
		return $alt_image_url;
	}
	global $post;
	if ( is_singular() ) {
		$src = _get_thumbnail_src( $size, $post->ID, $meta_key );
		if ( ! empty( $src ) ) {
			return $src;
		}
	}
	return $default_image_url;
}


// -----------------------------------------------------------------------------


/**
 * Retrieves the thumbnail image source.
 *
 * @access private
 * @global \WP_Post|null $post
 *
 * @param string   $size     (Optional) The image size.
 * @param int|null $post_id  (Optional) Post ID.
 * @param string   $meta_key (Optional) Meta key of image.
 * @return string The url of the image.
 */
function _get_thumbnail_src( string $size = 'large', ?int $post_id = null, string $meta_key = '' ): string {
	global $post;
	if ( empty( $post_id ) ) {
		if ( ! $post ) {
			return '';
		}
		$post_id = $post->ID;
	}
	if ( empty( $meta_key ) ) {
		$tid = get_post_thumbnail_id( $post_id );
	} else {
		$tid = get_post_meta( $post_id, $meta_key, true );
		$tid = is_numeric( $tid ) ? $tid : '';
	}
	if ( empty( $tid ) ) {
		return '';
	}
	$url = wp_get_attachment_image_url( (int) $tid, $size );
	return $url ? $url : '';
}
