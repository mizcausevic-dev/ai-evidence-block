<?php
/**
 * Plugin bootstrap — registers the block, wires the inline-format scanner and
 * JSON-LD flush, enqueues front-end assets for inline-only posts, and localizes
 * the editor vocabulary.
 *
 * @package KineticGain\AIEvidence
 */

namespace KineticGain\AIEvidence;

defined( 'ABSPATH' ) || exit;

/**
 * Main plugin controller.
 */
class Plugin {

	/**
	 * Block name (namespace signals the Kinetic Gain Protocol Suite).
	 */
	const BLOCK_NAME = 'kineticgain/ai-evidence';

	/**
	 * Singleton instance.
	 *
	 * @var Plugin|null
	 */
	private static $instance = null;

	/**
	 * Get the singleton.
	 *
	 * @return Plugin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Register all hooks.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'init', array( $this, 'register_block' ) );
		add_action( 'init', array( $this, 'load_textdomain' ) );
		add_action( 'enqueue_block_editor_assets', array( $this, 'localize_editor' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend' ) );

		// Scan for inline marks after blocks have rendered (do_blocks is at 9).
		add_filter( 'the_content', array( new Inline_Format(), 'filter_content' ), 20 );

		// Flush the consolidated JSON-LD + evidence scripts once per page.
		add_action( 'wp_footer', array( Evidence_Collector::instance(), 'flush' ), 20 );
	}

	/**
	 * Register the wrapping block from its compiled metadata.
	 *
	 * The inline RichText format registers itself in the editor (index.js); no
	 * server-side registration is required for it.
	 *
	 * @return void
	 */
	public function register_block() {
		$build = DIR . 'build';
		if ( is_dir( $build ) && is_readable( $build . '/block.json' ) ) {
			register_block_type( $build );
		}
	}

	/**
	 * Load translations.
	 *
	 * @return void
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'ai-evidence-block', false, dirname( plugin_basename( FILE ) ) . '/languages' );
	}

	/**
	 * Provide the editor with the provenance vocabulary and suite links.
	 *
	 * @return void
	 */
	public function localize_editor() {
		$current = wp_get_current_user();

		$data = array(
			'states'          => States::all(),
			'models'          => models(),
			'confidence'      => confidence_levels(),
			'defaultReviewer' => $current ? $current->display_name : '',
			'evidenceVersion' => EVIDENCE_VERSION,
			'specUrl'         => SPEC_URL,
			'suiteUrl'        => SUITE_URL,
			'blockName'       => self::BLOCK_NAME,
			'formatName'      => 'kineticgain/ai-evidence-mark',
		);

		$handle = generate_block_asset_handle( self::BLOCK_NAME, 'editorScript' );

		wp_add_inline_script(
			$handle,
			'window.kgAEB = ' . wp_json_encode( $data ) . ';',
			'before'
		);

		wp_set_script_translations( $handle, 'ai-evidence-block' );
	}

	/**
	 * Ensure inline-only posts still load the front-end style and popover script.
	 *
	 * When the wrapping block is present, core enqueues these automatically; this
	 * covers posts that use only the inline mark format.
	 *
	 * @return void
	 */
	public function enqueue_frontend() {
		if ( ! is_singular() ) {
			return;
		}

		$post = get_post();
		if ( ! $post || false === strpos( (string) $post->post_content, 'aeb-mark' ) ) {
			return;
		}

		$style = generate_block_asset_handle( self::BLOCK_NAME, 'style' );
		if ( wp_style_is( $style, 'registered' ) ) {
			wp_enqueue_style( $style );
		}

		$view = generate_block_asset_handle( self::BLOCK_NAME, 'viewScript' );
		if ( wp_script_is( $view, 'registered' ) ) {
			wp_enqueue_script( $view );
		}
	}
}
