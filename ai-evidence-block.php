<?php
/**
 * Plugin Name:       AI Evidence Block
 * Plugin URI:        https://kineticgain.com/ai-evidence-block/
 * Description:       Mark the AI provenance of any claim you publish — model, reviewer, confidence, source — and render it as a clean evidence card, namespaced data attributes, schema.org JSON-LD, and a verifiable AI Evidence Format object. The WordPress reference implementation of the AI Evidence Format (Kinetic Gain Protocol Suite).
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            Kinetic Gain
 * Author URI:        https://kineticgain.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       ai-evidence-block
 * Domain Path:       /languages
 *
 * @package KineticGain\AIEvidence
 */

namespace KineticGain\AIEvidence;

defined( 'ABSPATH' ) || exit;

/**
 * Plugin version. Kept in sync with the header above and readme.txt "Stable tag".
 */
const VERSION = '1.0.0';

/**
 * The AI Evidence Format specification version this plugin emits.
 *
 * Matches `evidence_version` in evidence.schema.json (const "0.1").
 *
 * @see https://github.com/mizcausevic-dev/ai-evidence-format-spec
 */
const EVIDENCE_VERSION = '0.1';

/**
 * Canonical URL of the AI Evidence Format specification.
 */
const SPEC_URL = 'https://github.com/mizcausevic-dev/ai-evidence-format-spec';

/**
 * The Kinetic Gain Protocol Suite home — the moat this plugin signals back to.
 */
const SUITE_URL = 'https://kineticgain.com';

if ( ! defined( __NAMESPACE__ . '\\FILE' ) ) {
	define( __NAMESPACE__ . '\\FILE', __FILE__ );
}
if ( ! defined( __NAMESPACE__ . '\\DIR' ) ) {
	define( __NAMESPACE__ . '\\DIR', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( __NAMESPACE__ . '\\URL' ) ) {
	define( __NAMESPACE__ . '\\URL', plugin_dir_url( __FILE__ ) );
}

/**
 * Lightweight, dependency-free autoloader for the plugin's own classes.
 *
 * Maps `KineticGain\AIEvidence\Foo_Bar` to `includes/class-foo-bar.php`.
 *
 * @param string $class_name Fully qualified class name being loaded.
 * @return void
 */
spl_autoload_register(
	function ( $class_name ) {
		$prefix = __NAMESPACE__ . '\\';
		if ( 0 !== strpos( $class_name, $prefix ) ) {
			return;
		}

		$relative = substr( $class_name, strlen( $prefix ) );
		$file     = 'class-' . strtolower( str_replace( '_', '-', $relative ) ) . '.php';
		$path     = DIR . 'includes/' . $file;

		if ( is_readable( $path ) ) {
			require_once $path;
		}
	}
);

// Procedural helpers (sanitization, defaults, model vocabulary).
require_once DIR . 'includes/helpers.php';

/**
 * Boot the plugin once all plugins are loaded.
 *
 * @return void
 */
function bootstrap() {
	Plugin::instance()->init();
}
add_action( 'plugins_loaded', __NAMESPACE__ . '\\bootstrap' );
