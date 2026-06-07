<?php
/**
 * Claim — a normalized value object for a single marked claim.
 *
 * Both entry points (the wrapping block's render callback and the inline-format
 * content scanner) build a Claim, so the evidence-object and schema.org
 * builders have exactly one shape to consume.
 *
 * @package KineticGain\AIEvidence
 */

namespace KineticGain\AIEvidence;

defined( 'ABSPATH' ) || exit;

/**
 * Immutable-ish container for one claim's provenance data.
 */
class Claim {

	/**
	 * Provenance state slug.
	 *
	 * @var string
	 */
	public $state = States::DEFAULT_STATE;

	/**
	 * AI model slug (or free text), or '' when not applicable.
	 *
	 * @var string
	 */
	public $model = '';

	/**
	 * Author-supplied custom model name when $model is `other`.
	 *
	 * @var string
	 */
	public $custom_model = '';

	/**
	 * Human reviewer / verifier display name.
	 *
	 * @var string
	 */
	public $reviewer = '';

	/**
	 * Generation date (Y-m-d as captured in the editor).
	 *
	 * @var string
	 */
	public $date = '';

	/**
	 * Confidence level slug.
	 *
	 * @var string
	 */
	public $confidence = 'unrated';

	/**
	 * Verification / cited source URL, or '' when none.
	 *
	 * @var string
	 */
	public $source_url = '';

	/**
	 * Free-form evidence notes.
	 *
	 * @var string
	 */
	public $notes = '';

	/**
	 * The exact, plain-text claim being marked.
	 *
	 * @var string
	 */
	public $claim_text = '';

	/**
	 * Post the claim belongs to.
	 *
	 * @var int
	 */
	public $post_id = 0;

	/**
	 * Ordinal index of this claim within the post (for stable IDs).
	 *
	 * @var int
	 */
	public $index = 0;

	/**
	 * Build a Claim from the wrapping block's attributes and inner content.
	 *
	 * @param array<string,mixed> $attributes Block attributes.
	 * @param string              $content    Rendered InnerBlocks HTML.
	 * @param int                 $post_id    Owning post ID.
	 * @param int                 $index      Ordinal index within the post.
	 * @return self
	 */
	public static function from_block( array $attributes, $content, $post_id, $index ) {
		$claim = new self();

		$claim->state        = States::sanitize( isset( $attributes['state'] ) ? $attributes['state'] : '' );
		$claim->model        = sanitize_model( isset( $attributes['model'] ) ? $attributes['model'] : '' );
		$claim->custom_model = sanitize_text_field( isset( $attributes['customModel'] ) ? $attributes['customModel'] : '' );
		$claim->reviewer     = sanitize_text_field( isset( $attributes['reviewer'] ) ? $attributes['reviewer'] : '' );
		$claim->date         = sanitize_text_field( isset( $attributes['date'] ) ? $attributes['date'] : '' );
		$claim->confidence   = sanitize_confidence( isset( $attributes['confidence'] ) ? $attributes['confidence'] : '' );
		$claim->source_url   = esc_url_raw( isset( $attributes['source'] ) ? $attributes['source'] : '' );
		$claim->notes        = sanitize_notes( isset( $attributes['notes'] ) ? $attributes['notes'] : '' );
		$claim->post_id     = (int) $post_id;
		$claim->index       = (int) $index;
		$claim->claim_text  = self::normalize_text( wp_strip_all_tags( (string) $content ) );

		return $claim;
	}

	/**
	 * Build a Claim from inline-format data attributes and the marked text.
	 *
	 * @param array<string,string> $attrs   Data attributes read from the mark.
	 * @param string               $text    The marked plain text.
	 * @param int                  $post_id Owning post ID.
	 * @param int                  $index   Ordinal index within the post.
	 * @return self
	 */
	public static function from_inline( array $attrs, $text, $post_id, $index ) {
		$claim = new self();

		$get = static function ( $key ) use ( $attrs ) {
			return isset( $attrs[ $key ] ) ? $attrs[ $key ] : '';
		};

		$claim->state       = States::sanitize( $get( 'state' ) );
		$claim->model       = sanitize_model( $get( 'model' ) );
		$claim->custom_model = sanitize_text_field( $get( 'custom-model' ) );
		$claim->reviewer    = sanitize_text_field( $get( 'reviewer' ) );
		$claim->date        = sanitize_text_field( $get( 'date' ) );
		$claim->confidence  = sanitize_confidence( $get( 'confidence' ) );
		$claim->source_url  = esc_url_raw( $get( 'source' ) );
		$claim->notes       = sanitize_notes( $get( 'notes' ) );
		$claim->post_id     = (int) $post_id;
		$claim->index       = (int) $index;
		$claim->claim_text  = self::normalize_text( (string) $text );

		return $claim;
	}

	/**
	 * Reproducible plain-text normalization for a claim span.
	 *
	 * Collapses any run of whitespace (including newlines) to a single space and
	 * trims the ends. This is the deterministic pre-step before the SPEC.md §5
	 * content-hash canonicalization, so a third party extracting the same visible
	 * text derives the same hash.
	 *
	 * @param string $text Raw text.
	 * @return string Normalized text.
	 */
	public static function normalize_text( $text ) {
		$text = (string) $text;
		// Decode entities so the hash is over readable text, not markup escapes.
		$text = wp_specialchars_decode( $text, ENT_QUOTES );
		$text = preg_replace( '/\s+/u', ' ', $text );
		return trim( (string) $text );
	}

	/**
	 * Whether this claim carries enough to emit a spec-valid evidence object.
	 *
	 * The AI Evidence Format requires a non-empty `claim_text`.
	 *
	 * @return bool
	 */
	public function is_valid() {
		return '' !== $this->claim_text;
	}

	/**
	 * The resolved, human-readable model label.
	 *
	 * @return string
	 */
	public function model_label() {
		return model_label( $this->model, $this->custom_model );
	}

	/**
	 * A stable, post-scoped evidence identifier.
	 *
	 * Stable across renders while the claim text is unchanged, so audit tooling
	 * can track a citation over time.
	 *
	 * @return string e.g. `aeb-42-0-1a2b3c4d`.
	 */
	public function evidence_id() {
		$digest = substr( sha1( $this->claim_text . '|' . $this->index ), 0, 8 );
		return sprintf( 'aeb-%d-%d-%s', $this->post_id, $this->index, $digest );
	}

	/**
	 * The content hash over the already-normalized claim text.
	 *
	 * The real canonicalization runs in normalize_text() at construction; see
	 * content_hash() for the full, reproducible end-to-end algorithm.
	 *
	 * @see Claim::normalize_text()
	 * @see content_hash()
	 * @return string `sha256:<hex>`.
	 */
	public function content_hash() {
		return content_hash( $this->claim_text );
	}

	/**
	 * The page-anchored canonical URL for this claim.
	 *
	 * @return string Permalink with an evidence-id fragment.
	 */
	public function permalink() {
		$base = $this->post_id ? get_permalink( $this->post_id ) : home_url( '/' );
		return $base ? $base . '#' . $this->evidence_id() : '';
	}
}
