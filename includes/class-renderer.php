<?php
/**
 * Server-side renderer for the wrapping block (kineticgain/ai-evidence).
 *
 * Produces the InnerBlocks content plus a visible "evidence card" (the design's
 * provenance pill + meta line), stamps namespaced data-aeb-* attributes on the
 * wrapper, and registers the claim with the collector for JSON-LD output.
 *
 * @package KineticGain\AIEvidence
 */

namespace KineticGain\AIEvidence;

defined( 'ABSPATH' ) || exit;

/**
 * Block renderer.
 */
class Renderer {

	/**
	 * Singleton instance.
	 *
	 * @var Renderer|null
	 */
	private static $instance = null;

	/**
	 * Get the singleton.
	 *
	 * @return Renderer
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Render the block.
	 *
	 * @param array<string,mixed> $attributes Block attributes.
	 * @param string              $content    InnerBlocks HTML.
	 * @param \WP_Block|null      $block      Block instance (unused; for parity).
	 * @return string Block HTML.
	 */
	public function render( $attributes, $content, $block = null ) {
		unset( $block );

		$post_id = get_the_ID() ? (int) get_the_ID() : 0;
		$claim   = Claim::from_block( (array) $attributes, (string) $content, $post_id, 0 );

		// Collect for JSON-LD only on the canonical front-end view of the post.
		if ( should_collect() ) {
			Evidence_Collector::instance()->add( $claim );
		} else {
			// Still assign an index so data-attrs/anchors are coherent in previews.
			$claim->index = 0;
		}

		$wrapper = get_block_wrapper_attributes(
			array_merge(
				array( 'class' => 'aeb-block aeb-state-' . $claim->state ),
				$this->data_attributes( $claim )
			)
		);

		$card = $this->card( $claim );

		// $content is trusted InnerBlocks output; $card is assembled from escaped
		// values; $wrapper is escaped by get_block_wrapper_attributes().
		return sprintf(
			'<div %1$s><div class="aeb-block__content">%2$s</div>%3$s</div>',
			$wrapper,
			$content,
			$card
		);
	}

	/**
	 * Build the namespaced data-aeb-* attribute map for the wrapper.
	 *
	 * @param Claim $claim The claim.
	 * @return array<string,string> Attribute name => value (empties omitted).
	 */
	private function data_attributes( Claim $claim ) {
		$attrs = array(
			'data-aeb-version'        => EVIDENCE_VERSION,
			'data-aeb-evidence-id'    => $claim->evidence_id(),
			'data-aeb-state'          => $claim->state,
			'data-aeb-synthesis-role' => Provenance::synthesis_role( $claim->state ),
			'data-aeb-source-type'    => Provenance::digital_source_type( $claim->state ),
			'data-aeb-hash'           => $claim->content_hash(),
			'data-aeb-model'          => $claim->model_label(),
			'data-aeb-reviewer'       => $claim->reviewer,
			'data-aeb-date'           => $claim->date,
			'data-aeb-confidence'     => $claim->confidence,
			'data-aeb-source'         => $claim->source_url,
		);

		return array_filter(
			$attrs,
			static function ( $value ) {
				return '' !== $value && null !== $value;
			}
		);
	}

	/**
	 * Build the visible evidence card.
	 *
	 * @param Claim $claim The claim.
	 * @return string Card HTML (fully escaped).
	 */
	private function card( Claim $claim ) {
		$label = States::label( $claim->state );
		$icon  = wp_kses( Icons::pill( $claim->state ), Icons::allowed_svg() );

		$pill = sprintf(
			'<span class="aeb-pill aeb-pill--%1$s">%2$s<span class="aeb-pill__label">%3$s</span></span>',
			esc_attr( $claim->state ),
			$icon,
			esc_html( $label )
		);

		$meta = $this->meta_line( $claim );
		$meta = '' !== $meta
			? '<span class="aeb-card__meta">' . esc_html( $meta ) . '</span>'
			: '';

		$notes = '' !== $claim->notes
			? '<span class="aeb-card__notes">' . esc_html( $claim->notes ) . '</span>'
			: '';

		$source = '';
		if ( '' !== $claim->source_url ) {
			$source = sprintf(
				'<a class="aeb-card__source" href="%1$s" rel="nofollow noopener">%2$s</a>',
				esc_url( $claim->source_url ),
				esc_html__( 'Source', 'ai-evidence-block' )
			);
		}

		$spec = sprintf(
			'<a class="aeb-card__spec" href="%1$s" target="_blank" rel="noopener nofollow">%2$s</a>',
			esc_url( SPEC_URL ),
			esc_html(
				sprintf(
					/* translators: %s: AI Evidence Format version. */
					__( 'AI Evidence · v%s', 'ai-evidence-block' ),
					EVIDENCE_VERSION
				)
			)
		);

		return sprintf(
			'<div class="aeb-card" role="note" aria-label="%1$s">%2$s%3$s%4$s%5$s%6$s</div>',
			esc_attr__( 'AI provenance disclosure', 'ai-evidence-block' ),
			$pill,
			$meta,
			$notes,
			$source,
			$spec
		);
	}

	/**
	 * Compose the human-readable meta line: credit · date · confidence.
	 *
	 * @param Claim $claim The claim.
	 * @return string Joined meta line (may be empty).
	 */
	private function meta_line( Claim $claim ) {
		$parts = array();

		$credit = Provenance::credit_line( $claim->state, $claim->reviewer, $claim->model_label() );
		if ( '' !== $credit ) {
			$parts[] = $credit;
		}

		if ( '' !== $claim->date ) {
			$timestamp = strtotime( $claim->date );
			if ( false !== $timestamp ) {
				$parts[] = date_i18n( (string) get_option( 'date_format' ), $timestamp );
			}
		}

		if ( 'unrated' !== $claim->confidence ) {
			$levels = confidence_levels();
			if ( isset( $levels[ $claim->confidence ] ) ) {
				$parts[] = sprintf(
					/* translators: %s: confidence level label. */
					__( 'Confidence: %s', 'ai-evidence-block' ),
					$levels[ $claim->confidence ]
				);
			}
		}

		return implode( ' · ', $parts );
	}
}
