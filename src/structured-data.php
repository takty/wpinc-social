<?php
/**
 * Structured Data
 *
 * @package Wpinc Social
 * @author Takuto Yanagida
 * @version 2021-03-28
 */

namespace wpinc\social\structured_data;

require_once __DIR__ . '/site-info.php';

/**
 * Output the structured data.
 *
 * @param array $args {
 *     The data of the website.
 *
 *     @type string   $url         (Optional) The URL.
 *     @type string   $name        (Optional) The name.
 *     @type string   $inLanguage  (Optional) The locale.
 *     @type string   $description (Optional) The description.
 *     @type string[] $sameAs      (Optional) An array of URLs.
 *     @type string   $logo        The URL of the logo image.
 *     @type string[] $publisher {
 *         @type string $name (Optional) The name of the publisher.
 *     }
 * }
 */
function the_structured_data( array $args = array() ) {
	$args = array_replace_recursive(
		array(
			'@context'    => 'http://schema.org',
			'@type'       => 'WebSite',
			'url'         => home_url(),
			'name'        => \wpinc\social\get_site_name(),
			'inLanguage'  => get_locale(),
			'description' => \wpinc\social\get_site_description(),
			'sameAs'      => array(),
			'publisher'   => array(
				'@type' => 'Organization',
				'name'  => \wpinc\social\get_site_name(),
				'logo'  => '',
			),
		),
		$args
	);
	if ( isset( $args['logo'] ) ) {
		$args['publisher']['logo'] = $args['logo'];
		unset( $args['logo'] );
	}
	$args = _remove_empty_entry( $args );
	$json = wp_json_encode( $args, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT );
	?>
	<script type="application/ld+json">
	<?php echo esc_html( $json ); ?>
	</script>
	<?php
}

/**
 * Remove empty entries from an array.
 *
 * @access private
 *
 * @param array $array An array.
 * @return array Filtered array.
 */
function _remove_empty_entry( array $array ): array {
	$ret = array();
	foreach ( $array as $key => $val ) {
		if ( is_array( $val ) ) {
			$val = _remove_empty_entry( $val );
		}
		if ( ! empty( $val ) ) {
			if ( is_int( $key ) ) {
				$ret[] = $val;
			} else {
				$ret[ $key ] = $val;
			}
		}
	}
	return $ret;
}