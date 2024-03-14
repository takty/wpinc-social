<?php
/**
 * Analytics
 *
 * @package Wpinc Socio
 * @author Takuto Yanagida
 * @version 2024-03-14
 */

declare(strict_types=1);

namespace wpinc\socio;

require_once __DIR__ . '/assets/asset-url.php';

/** phpcs:ignore
 * Outputs google analytics code.
 *
 * phpcs:ignore
 * @param array{
 *     url_to?           : string,
 *     site_verification?: string,
 *     tag_id?           : string,
 *     do_show_dialog?   : bool,
 *     expired_day?      : int,
 *     id_dialog?        : string,
 *     id_accept?        : string,
 *     id_reject?        : string,
 * } $args Arguments.
 *
 * $args {
 *     Arguments.
 *
 *     @type string 'url_to'            URL to this script.
 *     @type string 'site_verification' The site verification code.
 *     @type string 'tag_id'            The google tag ID.
 *     @type bool   'do_show_dialog'    Whether to show the dialog.
 *     @type int    'expired_day'       The length of keeping settings.
 *     @type string 'id_dialog'         Element ID of the dialog. Defaults 'wpinc-socio-analytics-dialog'.
 *     @type string 'id_accept'         Element ID of the accept button. Defaults 'wpinc-socio-analytics-accept'.
 *     @type string 'id_reject'         Element ID of the reject button. Defaults 'wpinc-socio-analytics-reject'.
 * }
 */
function the_google_analytics_code( array $args = array() ): void {
	$args += array(
		'site_verification' => null,
		'tag_id'            => null,
		'do_show_dialog'    => false,
	);

	$url_to   = untrailingslashit( $args['url_to'] ?? \wpinc\get_file_uri( __DIR__ ) );
	$site_ver = $args['site_verification'];

	unset( $args['url_to'] );
	unset( $args['site_verification'] );

	if ( ! is_string( $args['tag_id'] ) || '' === $args['tag_id'] ) {  // Check for non-empty-string.
		if ( is_user_logged_in() ) {
			_echo_analytics_warning();
		}
	} else {
		_echo_google_analytics_code( $url_to, $args );
		if ( is_string( $site_ver ) && '' !== $site_ver ) {  // Check for non-empty-string.
			_echo_google_site_verification( $site_ver );
		}
	}
}

/**
 * Outputs warning that indicates any analytics codes are assigned.
 *
 * @access private
 */
function _echo_analytics_warning(): void {
	?>
	<script>
	window.addEventListener('load', ()=>{
		const e=document.body.appendChild(document.createElement('div'));
		console.log(e.innerText='No google tag ID is set!');
		e.style='position:fixed;z-index:9999;inset:auto auto 0 0;padding:4px;line-height:1;background:red;color:#fff;';
	});
	</script>
	<?php
}

/** phpcs:ignore
 * Outputs google analytics code.
 *
 * @access private
 *
 * @param string $url_to URL to this script.
 * phpcs:ignore
 * @param array{
 *     tag_id        : string,
 *     do_show_dialog: bool,
 *     expired_day?  : int,
 *     id_dialog?    : string,
 *     id_accept?    : string,
 *     id_reject?    : string,
 * } $args Arguments or google tag ID.
 */
function _echo_google_analytics_code( string $url_to, array $args ): void {
	?>
	<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_attr( $args['tag_id'] ); ?>"></script><?php // phpcs:ignore ?>
	<?php
	wp_enqueue_script(
		'wpinc-socio-analytics',
		\wpinc\abs_url( $url_to, './assets/js/analytics.min.js' ),
		array(),
		(string) filemtime( __DIR__ . '/assets/js/analytics.min.js' ),
		true
	);
	$json = wp_json_encode( $args );
	$data = "wpinc_socio_analytics_initialize($json);";
	wp_add_inline_script( 'wpinc-socio-analytics', $data, 'after' );
}

/**
 * Outputs site verification.
 *
 * @access private
 *
 * @param string $site_ver The verification code.
 */
function _echo_google_site_verification( string $site_ver ): void {
	?>
	<meta name="google-site-verification" content="<?php echo esc_attr( $site_ver ); ?>">
	<?php
}
