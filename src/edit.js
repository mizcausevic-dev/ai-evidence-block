/**
 * Editor UI for the kineticgain/ai-evidence wrapping block.
 */
import { __ } from '@wordpress/i18n';
import { useEffect } from '@wordpress/element';
import { dateI18n } from '@wordpress/date';
import {
	useBlockProps,
	useInnerBlocksProps,
	InspectorControls,
} from '@wordpress/block-editor';
import { PanelBody, ExternalLink } from '@wordpress/components';
import ProvenanceFields from './components/fields';
import Pill from './components/pill';
import {
	STATES,
	MODELS,
	CONFIDENCE,
	AI_STATES,
	DEFAULT_REVIEWER,
	SPEC_URL,
	EVIDENCE_VERSION,
	modelLabel,
} from './lib/vocab';

/**
 * Compose the editor preview meta line (credit · date · confidence).
 *
 * @param {Object} a Block attributes.
 * @return {string} Meta line.
 */
function previewMeta( a ) {
	const parts = [];
	if ( AI_STATES.includes( a.state ) ) {
		const label = modelLabel( a.model, a.customModel );
		if ( label ) {
			parts.push( label );
		}
	}
	if ( a.reviewer ) {
		parts.push( __( 'Reviewed by', 'ai-evidence-block' ) + ' ' + a.reviewer );
	}
	if ( a.date ) {
		const stamp = Date.parse( a.date );
		if ( ! Number.isNaN( stamp ) ) {
			parts.push( dateI18n( 'M j, Y', a.date ) );
		}
	}
	if ( a.confidence && a.confidence !== 'unrated' && CONFIDENCE[ a.confidence ] ) {
		parts.push( __( 'Confidence:', 'ai-evidence-block' ) + ' ' + CONFIDENCE[ a.confidence ] );
	}
	return parts.join( ' · ' );
}

/**
 * @param {Object}   props               Block props.
 * @param {Object}   props.attributes    Block attributes.
 * @param {Function} props.setAttributes Attribute setter.
 * @return {JSX.Element} The edit component.
 */
export default function Edit( { attributes, setAttributes } ) {
	const { state } = attributes;

	// Default the reviewer to the post author on first insert.
	useEffect( () => {
		if ( ! attributes.reviewer && DEFAULT_REVIEWER ) {
			setAttributes( { reviewer: DEFAULT_REVIEWER } );
		}
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [] );

	const blockProps = useBlockProps( {
		className: 'aeb-block aeb-state-' + state,
		'data-aeb-state': state,
	} );

	const innerBlocksProps = useInnerBlocksProps(
		{ className: 'aeb-block__content' },
		{ template: [ [ 'core/paragraph', { placeholder: __( 'Write the claim you want to disclose…', 'ai-evidence-block' ) } ] ] }
	);

	const descriptor = STATES[ state ] || STATES.verified;

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'AI Evidence', 'ai-evidence-block' ) } initialOpen>
					<ProvenanceFields
						value={ attributes }
						onChange={ ( update ) => setAttributes( update ) }
					/>
					<p className="aeb-spec-note">
						{ descriptor.description || '' }
					</p>
					<ExternalLink href={ SPEC_URL }>
						{ /* translators: %s: spec version */ }
						{ __( 'AI Evidence Format', 'ai-evidence-block' ) + ' v' + EVIDENCE_VERSION }
					</ExternalLink>
				</PanelBody>
			</InspectorControls>

			<div { ...blockProps }>
				<div { ...innerBlocksProps } />
				<Pill state={ state } meta={ previewMeta( attributes ) } />
			</div>
		</>
	);
}
