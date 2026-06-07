<?php
/**
 * Builds an AI Evidence Format v0.1 object from a Claim.
 *
 * Output conforms to evidence.schema.json (draft 2020-12). Every sub-object is
 * `additionalProperties: false`, so this builder emits ONLY spec keys — the
 * authorship layer (model, reviewer, confidence-as-label) lives in the parallel
 * schema.org CreativeWork node, never here.
 *
 * Conformance target: Level 2 (Verify) — a reproducible `content_hash` is
 * always present; no signature (Level 3) is emitted in v1.0.
 *
 * @package KineticGain\AIEvidence
 * @see https://github.com/mizcausevic-dev/ai-evidence-format-spec
 */

namespace KineticGain\AIEvidence;

defined( 'ABSPATH' ) || exit;

/**
 * Evidence object factory.
 */
class Evidence_Object {

	/**
	 * Build a spec-valid evidence object array from a claim.
	 *
	 * @param Claim $claim The normalized claim.
	 * @return array<string,mixed>|null Spec object, or null when the claim can't
	 *                                   produce a valid one (empty claim text).
	 */
	public static function build( Claim $claim ) {
		if ( ! $claim->is_valid() ) {
			return null;
		}

		$object = array(
			'evidence_version' => EVIDENCE_VERSION,
			'evidence_id'      => $claim->evidence_id(),
			'claim_text'       => $claim->claim_text,
			'source'           => self::source( $claim ),
			'span'             => self::span( $claim ),
			'retrieval'        => self::retrieval( $claim ),
			'verification'     => array(
				'content_hash' => $claim->content_hash(),
			),
			'synthesis_role'   => Provenance::synthesis_role( $claim->state ),
		);

		if ( '' !== $claim->notes ) {
			$object['notes'] = $claim->notes;
		}

		/**
		 * Filter a built evidence object before it is collected.
		 *
		 * @param array<string,mixed> $object The spec object.
		 * @param Claim               $claim  The source claim.
		 */
		return (array) apply_filters( 'kgaeb_evidence_object', $object, $claim );
	}

	/**
	 * Build the required `source` sub-object.
	 *
	 * Uses the author-supplied source URL when present; otherwise self-references
	 * the claim's own permalink so the required `uri` is always a valid URI.
	 *
	 * @param Claim $claim The claim.
	 * @return array<string,string> The source object.
	 */
	private static function source( Claim $claim ) {
		$has_external = ( '' !== $claim->source_url );
		$uri          = $has_external ? $claim->source_url : $claim->permalink();

		// Guarantee a non-empty, valid URI for the required field.
		if ( '' === $uri ) {
			$uri = home_url( '/' );
		}

		$fetched_at = to_datetime( $claim->date );
		if ( '' === $fetched_at ) {
			$fetched_at = gmdate( 'Y-m-d\TH:i:s\Z' );
		}

		$source = array(
			'uri'        => $uri,
			'type'       => 'webpage',
			'fetched_at' => $fetched_at,
		);

		// For self-referencing claims we know the title and publication time.
		if ( ! $has_external && $claim->post_id ) {
			$title = get_the_title( $claim->post_id );
			if ( $title ) {
				$source['title'] = wp_strip_all_tags( $title );
			}

			$published = get_post_time( 'Y-m-d\TH:i:s\Z', true, $claim->post_id );
			if ( $published ) {
				$source['published_at'] = $published;
			}

			$publisher = get_bloginfo( 'name' );
			if ( $publisher ) {
				$source['publisher'] = $publisher;
			}
		}

		return $source;
	}

	/**
	 * Build the required `span` sub-object using a W3C-style text-quote selector.
	 *
	 * @param Claim $claim The claim.
	 * @return array<string,string> The span object.
	 */
	private static function span( Claim $claim ) {
		return array(
			'selector_type'  => 'text_quote',
			'selector_value' => $claim->claim_text,
			'exact_text'     => $claim->claim_text,
		);
	}

	/**
	 * Build the required `retrieval` sub-object.
	 *
	 * `method` reflects how the claim's backing was obtained: human/cited content
	 * is a direct fetch; AI-originated content is model recall.
	 *
	 * @param Claim $claim The claim.
	 * @return array<string,mixed> The retrieval object.
	 */
	private static function retrieval( Claim $claim ) {
		$model_recall = array( 'ai-assisted', 'ai-generated', 'auto-detected' );
		$method       = in_array( $claim->state, $model_recall, true ) ? 'model_recall' : 'direct_fetch';

		$retrieval = array(
			'method'       => $method,
			'retriever_id' => 'ai-evidence-block/wp@' . VERSION,
		);

		$confidence = confidence_score( $claim->confidence );
		if ( null !== $confidence ) {
			$retrieval['confidence'] = $confidence;
		}

		return $retrieval;
	}
}
