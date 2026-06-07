<?php
/**
 * Evidence collector — the singleton both entry points feed.
 *
 * The wrapping block (render callback) and the inline format (content scan) each
 * add their claims here. At wp_footer we flush exactly two scripts per page:
 *
 *   1. <script type="application/ld+json">       — a schema.org @graph (SEO).
 *   2. <script type="application/ai-evidence+json"> — pristine, spec-valid
 *      evidence objects for audit tooling. (Browsers ignore the unknown type.)
 *
 * One consolidated graph keeps us additive and conflict-free alongside
 * Yoast/Rank Math, which emit their own page-level schema.
 *
 * @package KineticGain\AIEvidence
 */

namespace KineticGain\AIEvidence;

defined( 'ABSPATH' ) || exit;

/**
 * Accumulates and flushes per-page provenance metadata.
 */
class Evidence_Collector {

	/**
	 * Singleton instance.
	 *
	 * @var Evidence_Collector|null
	 */
	private static $instance = null;

	/**
	 * schema.org CreativeWork nodes, keyed by evidence_id (deduped).
	 *
	 * @var array<string,array<string,mixed>>
	 */
	private $nodes = array();

	/**
	 * Spec-valid evidence objects, keyed by evidence_id (deduped).
	 *
	 * @var array<string,array<string,mixed>>
	 */
	private $evidence = array();

	/**
	 * Monotonic ordinal assigned to each claim for stable IDs.
	 *
	 * @var int
	 */
	private $counter = 0;

	/**
	 * Whether the page output has already been flushed.
	 *
	 * @var bool
	 */
	private $flushed = false;

	/**
	 * Get the singleton.
	 *
	 * @return Evidence_Collector
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Private constructor (singleton).
	 */
	private function __construct() {}

	/**
	 * Add a claim. Assigns its stable ordinal, then builds both representations.
	 *
	 * @param Claim $claim The normalized claim.
	 * @return string The evidence_id assigned, or '' when the claim was invalid.
	 */
	public function add( Claim $claim ) {
		if ( ! $claim->is_valid() ) {
			return '';
		}

		$claim->index = $this->counter;
		++$this->counter;

		$id   = $claim->evidence_id();
		$node = CreativeWork::build( $claim );
		$obj  = Evidence_Object::build( $claim );

		if ( null !== $node ) {
			$this->nodes[ $id ] = $node;
		}
		if ( null !== $obj ) {
			$this->evidence[ $id ] = $obj;
		}

		return $id;
	}

	/**
	 * Whether anything has been collected.
	 *
	 * @return bool
	 */
	public function has_items() {
		return ! empty( $this->nodes ) || ! empty( $this->evidence );
	}

	/**
	 * Flush both scripts to the page. Idempotent.
	 *
	 * @return void
	 */
	public function flush() {
		if ( $this->flushed || ! $this->has_items() ) {
			return;
		}
		$this->flushed = true;

		$graph = array(
			'@context' => 'https://schema.org',
			'@graph'   => array_values( $this->nodes ),
		);

		$evidence_doc = array(
			'spec'    => 'AI Evidence Format',
			'version' => EVIDENCE_VERSION,
			'spec_url' => SPEC_URL,
			'evidence' => array_values( $this->evidence ),
		);

		/*
		 * wp_json_encode escapes forward slashes by default, which neutralizes
		 * any "</script>" sequence inside a value — safe to print in a script
		 * element. The strings below are therefore pre-escaped JSON.
		 */
		echo "\n<script type=\"application/ld+json\" class=\"aeb-jsonld\">";
		echo wp_json_encode( $graph ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_json_encode returns slash-escaped JSON, safe in a script element.
		echo "</script>\n";

		echo '<script type="application/ai-evidence+json" class="aeb-evidence">';
		echo wp_json_encode( $evidence_doc ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_json_encode returns slash-escaped JSON, safe in a script element.
		echo "</script>\n";
	}
}
