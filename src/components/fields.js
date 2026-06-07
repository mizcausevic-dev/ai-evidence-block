/**
 * Shared provenance fields — rendered in the block sidebar (InspectorControls)
 * and in the inline format's popover. One source of UI truth for both entries.
 */
import { __, sprintf } from '@wordpress/i18n';
import { SelectControl, TextControl, TextareaControl } from '@wordpress/components';
import StatePicker from './state-picker';
import { MODELS, CONFIDENCE, AI_STATES, toOptions } from '../lib/vocab';

const NOTES_MAX = 500;

/**
 * @param {Object}   props          Component props.
 * @param {Object}   props.value    Current field values.
 * @param {Function} props.onChange Receives a partial update object.
 * @return {JSX.Element} The fields.
 */
export default function ProvenanceFields( { value, onChange } ) {
	const set = ( key ) => ( next ) => onChange( { [ key ]: next } );
	const isAI = AI_STATES.includes( value.state );
	const remaining = NOTES_MAX - ( value.notes || '' ).length;

	return (
		<div className="aeb-fields">
			<StatePicker value={ value.state } onChange={ set( 'state' ) } />

			<SelectControl
				__next40pxDefaultSize
				__nextHasNoMarginBottom
				label={ __( 'AI model', 'ai-evidence-block' ) }
				help={ isAI ? '' : __( 'Optional unless the claim is AI-assisted or AI-generated.', 'ai-evidence-block' ) }
				value={ value.model }
				options={ [ { label: __( '— None —', 'ai-evidence-block' ), value: '' }, ...toOptions( MODELS ) ] }
				onChange={ set( 'model' ) }
			/>

			{ value.model === 'other' && (
				<TextControl
					__next40pxDefaultSize
					__nextHasNoMarginBottom
					label={ __( 'Model name', 'ai-evidence-block' ) }
					value={ value.customModel }
					onChange={ set( 'customModel' ) }
					placeholder={ __( 'e.g. Llama 4 Maverick', 'ai-evidence-block' ) }
				/>
			) }

			<TextControl
				__next40pxDefaultSize
				__nextHasNoMarginBottom
				label={ __( 'Reviewed by', 'ai-evidence-block' ) }
				value={ value.reviewer }
				onChange={ set( 'reviewer' ) }
				placeholder={ __( 'Human verifier', 'ai-evidence-block' ) }
			/>

			<TextControl
				__next40pxDefaultSize
				__nextHasNoMarginBottom
				type="date"
				label={ __( 'Generation date', 'ai-evidence-block' ) }
				value={ value.date }
				onChange={ set( 'date' ) }
			/>

			<SelectControl
				__next40pxDefaultSize
				__nextHasNoMarginBottom
				label={ __( 'Confidence', 'ai-evidence-block' ) }
				value={ value.confidence }
				options={ toOptions( CONFIDENCE ) }
				onChange={ set( 'confidence' ) }
			/>

			<TextControl
				__next40pxDefaultSize
				__nextHasNoMarginBottom
				type="url"
				label={ __( 'Verification source URL', 'ai-evidence-block' ) }
				value={ value.source }
				onChange={ set( 'source' ) }
				placeholder="https://"
			/>

			<TextareaControl
				__nextHasNoMarginBottom
				label={ __( 'Evidence notes', 'ai-evidence-block' ) }
				value={ value.notes }
				onChange={ ( next ) => onChange( { notes: next.slice( 0, NOTES_MAX ) } ) }
				rows={ 3 }
				help={ sprintf(
					/* translators: %d: number of characters remaining. */
					__( '%d characters remaining.', 'ai-evidence-block' ),
					Math.max( 0, remaining )
				) }
			/>
		</div>
	);
}
