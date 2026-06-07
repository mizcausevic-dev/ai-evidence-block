<?php
/**
 * Builds a schema.org CreativeWork node from a Claim — the authorship layer.
 *
 * This is what search engines and structured-data tools read. It uses ONLY
 * valid schema.org properties (so it won't trip Rich Results validation or
 * conflict with Yoast/Rank Math page-level schema), and it carries the
 * provenance standards as first-class, blessed extension points:
 *
 *   - `additionalType` -> the IPTC DigitalSourceType URI (C2PA-adjacent).
 *   - `schemaVersion`  -> the AI Evidence Format version it pairs with.
 *   - `publisher`      -> Kinetic Gain (the suite signal).
 *
 * @package KineticGain\AIEvidence
 */

namespace KineticGain\AIEvidence;

defined( 'ABSPATH' ) || exit;

/**
 * schema.org CreativeWork factory.
 */
class CreativeWork {

	/**
	 * Build a CreativeWork graph node from a claim.
	 *
	 * @param Claim $claim The normalized claim.
	 * @return array<string,mixed>|null Node, or null when the claim is empty.
	 */
	public static function build( Claim $claim ) {
		if ( ! $claim->is_valid() ) {
			return null;
		}

		$model_label = $claim->model_label();
		$is_ai       = in_array( $claim->state, array( 'ai-assisted', 'ai-generated', 'auto-detected' ), true );

		$node = array(
			'@type'          => 'CreativeWork',
			'@id'            => $claim->permalink(),
			'identifier'     => $claim->evidence_id(),
			'text'           => $claim->claim_text,
			'additionalType' => Provenance::digital_source_type( $claim->state ),
			'creditText'     => Provenance::credit_line( $claim->state, $claim->reviewer, $model_label ),
			'schemaVersion'  => 'AI Evidence Format ' . EVIDENCE_VERSION,
			'publisher'      => array(
				'@type' => 'Organization',
				'name'  => 'Kinetic Gain',
				'url'   => SUITE_URL,
			),
		);

		// Date the claim was produced.
		$created = to_datetime( $claim->date );
		if ( '' !== $created ) {
			$node['dateCreated'] = $created;
		}

		// The human reviewer/verifier.
		if ( '' !== $claim->reviewer ) {
			$node['author'] = array(
				'@type' => 'Person',
				'name'  => $claim->reviewer,
			);
		}

		// The AI model, as a software creator/contributor.
		if ( $is_ai && '' !== $model_label ) {
			$software = array(
				'@type'               => 'SoftwareApplication',
				'name'                => $model_label,
				'applicationCategory' => 'AI model',
			);
			// AI-generated => the model is the creator; AI-assisted => contributor.
			if ( 'ai-generated' === $claim->state ) {
				$node['creator'] = $software;
			} else {
				$node['contributor'] = $software;
			}
		}

		// The cited/verification source.
		if ( '' !== $claim->source_url ) {
			$node['isBasedOn'] = $claim->source_url;
		}

		// The page the claim lives on.
		if ( $claim->post_id ) {
			$permalink = get_permalink( $claim->post_id );
			if ( $permalink ) {
				$node['isPartOf'] = array(
					'@type' => 'WebPage',
					'@id'   => $permalink,
				);
			}
		}

		// Drop an unasserted source type (e.g. auto-detected, where origin is only
		// suspected) rather than emit a blank or overclaiming additionalType.
		if ( '' === $node['additionalType'] ) {
			unset( $node['additionalType'] );
		}

		// Drop an empty creditText rather than emit a blank property.
		if ( '' === $node['creditText'] ) {
			unset( $node['creditText'] );
		}

		/**
		 * Filter a built CreativeWork node before it is collected.
		 *
		 * @param array<string,mixed> $node  The schema.org node.
		 * @param Claim               $claim The source claim.
		 */
		return (array) apply_filters( 'kgaeb_creativework_node', $node, $claim );
	}
}
