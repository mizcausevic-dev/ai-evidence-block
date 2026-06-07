<?php
/**
 * Inline-format scanner for the kineticgain/ai-evidence-mark RichText format.
 *
 * The format saves <mark class="aeb-mark" data-aeb-*>…</mark> into post content.
 * On the canonical front-end view we:
 *   1. derive each mark's claim text from its actual inner text (so the hash
 *      always reflects what is published — never a stale stored copy),
 *   2. register the claim with the collector for JSON-LD, and
 *   3. enrich the mark with a title + aria-label (JS-free disclosure) and the
 *      same computed data-aeb-* (hash, evidence-id, synthesis role, source type)
 *      the wrapping block carries.
 *
 * @package KineticGain\AIEvidence
 */

namespace KineticGain\AIEvidence;

defined( 'ABSPATH' ) || exit;

/**
 * Scans rendered content for inline provenance marks.
 */
class Inline_Format {

	/**
	 * Matches a single <mark …class="…aeb-mark…"…>inner</mark>.
	 *
	 * Group 1: opening-tag attribute string. Group 2: inner HTML.
	 */
	const MARK_PATTERN = '/<mark\b([^>]*\bclass="[^"]*\baeb-mark\b[^"]*"[^>]*)>(.*?)<\/mark>/is';

	/**
	 * the_content filter: collect and enrich inline marks.
	 *
	 * @param string $content Rendered post content.
	 * @return string Content with enriched marks.
	 */
	public function filter_content( $content ) {
		// Fast bail: nothing to do without our marks or on non-canonical views.
		if ( false === strpos( $content, 'aeb-mark' ) || ! should_collect() ) {
			return $content;
		}

		$post_id   = get_the_ID() ? (int) get_the_ID() : 0;
		$collector = Evidence_Collector::instance();

		return (string) preg_replace_callback(
			self::MARK_PATTERN,
			function ( $matches ) use ( $post_id, $collector ) {
				return $this->process_mark( $matches[1], $matches[2], $post_id, $collector );
			},
			$content
		);
	}

	/**
	 * Process one matched mark: build the claim, collect it, enrich the tag.
	 *
	 * @param string             $tag_attrs Opening-tag attribute string.
	 * @param string             $inner     Inner HTML of the mark.
	 * @param int                $post_id   Owning post ID.
	 * @param Evidence_Collector $collector The collector.
	 * @return string Rewritten <mark>…</mark>.
	 */
	private function process_mark( $tag_attrs, $inner, $post_id, Evidence_Collector $collector ) {
		$attrs = array(
			'state'        => $this->attr( $tag_attrs, 'data-aeb-state' ),
			'model'        => $this->attr( $tag_attrs, 'data-aeb-model' ),
			'custom-model' => $this->attr( $tag_attrs, 'data-aeb-custom-model' ),
			'reviewer'     => $this->attr( $tag_attrs, 'data-aeb-reviewer' ),
			'date'         => $this->attr( $tag_attrs, 'data-aeb-date' ),
			'confidence'   => $this->attr( $tag_attrs, 'data-aeb-confidence' ),
			'source'       => $this->attr( $tag_attrs, 'data-aeb-source' ),
			'notes'        => $this->attr( $tag_attrs, 'data-aeb-notes' ),
		);

		$text  = wp_strip_all_tags( $inner );
		$claim = Claim::from_inline( $attrs, $text, $post_id, 0 );

		if ( ! $claim->is_valid() ) {
			return '<mark' . $tag_attrs . '>' . $inner . '</mark>';
		}

		$collector->add( $claim );

		$summary  = $this->summary( $claim );
		$computed = array(
			'data-aeb-version'        => EVIDENCE_VERSION,
			'data-aeb-evidence-id'    => $claim->evidence_id(),
			'data-aeb-synthesis-role' => Provenance::synthesis_role( $claim->state ),
			'data-aeb-source-type'    => Provenance::digital_source_type( $claim->state ),
			'data-aeb-hash'           => $claim->content_hash(),
			'title'                   => $summary,
			'aria-label'              => $summary,
		);

		$extra = '';
		foreach ( $computed as $name => $value ) {
			// Don't duplicate an attribute the saved markup already carries.
			if ( '' === $value || $this->has_attr( $tag_attrs, $name ) ) {
				continue;
			}
			$extra .= sprintf( ' %s="%s"', $name, esc_attr( $value ) );
		}

		return '<mark' . $tag_attrs . $extra . '>' . $inner . '</mark>';
	}

	/**
	 * Read an attribute value from an opening-tag attribute string.
	 *
	 * @param string $tag_attrs Attribute string.
	 * @param string $name      Attribute name.
	 * @return string Decoded value, or ''.
	 */
	private function attr( $tag_attrs, $name ) {
		$pattern = '/\b' . preg_quote( $name, '/' ) . '="([^"]*)"/i';
		if ( preg_match( $pattern, $tag_attrs, $m ) ) {
			return html_entity_decode( $m[1], ENT_QUOTES, 'UTF-8' );
		}
		return '';
	}

	/**
	 * Whether an attribute already exists on the opening tag.
	 *
	 * @param string $tag_attrs Attribute string.
	 * @param string $name      Attribute name.
	 * @return bool
	 */
	private function has_attr( $tag_attrs, $name ) {
		return (bool) preg_match( '/\b' . preg_quote( $name, '/' ) . '=/i', $tag_attrs );
	}

	/**
	 * Compose the JS-free disclosure summary used for title/aria-label.
	 *
	 * @param Claim $claim The claim.
	 * @return string e.g. "AI-assisted · GPT-5 · Reviewed by Ada · 6 Jun 2026".
	 */
	private function summary( Claim $claim ) {
		$parts = array( States::label( $claim->state ) );

		$model = $claim->model_label();
		if ( '' !== $model && in_array( $claim->state, array( 'ai-assisted', 'ai-generated', 'auto-detected' ), true ) ) {
			$parts[] = $model;
		}

		if ( '' !== $claim->reviewer ) {
			$parts[] = sprintf(
				/* translators: %s: reviewer name. */
				__( 'Reviewed by %s', 'ai-evidence-block' ),
				$claim->reviewer
			);
		}

		if ( '' !== $claim->date ) {
			$timestamp = strtotime( $claim->date );
			if ( false !== $timestamp ) {
				$parts[] = date_i18n( (string) get_option( 'date_format' ), $timestamp );
			}
		}

		return implode( ' · ', $parts );
	}
}
