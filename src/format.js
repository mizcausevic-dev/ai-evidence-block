/**
 * The inline provenance format (kineticgain/ai-evidence-mark).
 *
 * Marks a sentence or phrase mid-paragraph — the design's "smallest unit of
 * trust" — storing the same provenance fields the wrapping block uses. Saved as
 * <mark class="aeb-mark" data-aeb-*>…</mark>; the server enriches and collects it.
 */
import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import {
	registerFormatType,
	applyFormat,
	removeFormat,
	getActiveFormat,
	useAnchor,
} from '@wordpress/rich-text';
import { RichTextToolbarButton } from '@wordpress/block-editor';
import { Popover, Button, Flex } from '@wordpress/components';
import ProvenanceFields from './components/fields';
import { EvidenceIcon } from './components/icon';
import { FORMAT_NAME, DEFAULT_REVIEWER } from './lib/vocab';

/** Optional attributes copied into the mark when non-empty. */
const OPTIONAL_KEYS = [ 'model', 'customModel', 'reviewer', 'date', 'confidence', 'source', 'notes' ];

const settings = {
	title: __( 'AI Evidence mark', 'ai-evidence-block' ),
	tagName: 'mark',
	className: 'aeb-mark',
	attributes: {
		state: 'data-aeb-state',
		model: 'data-aeb-model',
		customModel: 'data-aeb-custom-model',
		reviewer: 'data-aeb-reviewer',
		date: 'data-aeb-date',
		confidence: 'data-aeb-confidence',
		source: 'data-aeb-source',
		notes: 'data-aeb-notes',
	},
	edit: Edit,
};

/**
 * Seed the popover fields from the active format, falling back to defaults.
 *
 * @param {Object|undefined} active Active format object.
 * @return {Object} Field values.
 */
function seedFields( active ) {
	const a = ( active && active.attributes ) || {};
	return {
		state: a.state || 'verified',
		model: a.model || '',
		customModel: a.customModel || '',
		reviewer: a.reviewer || DEFAULT_REVIEWER || '',
		date: a.date || '',
		confidence: a.confidence || 'unrated',
		source: a.source || '',
		notes: a.notes || '',
	};
}

/**
 * Toolbar button + editing popover for the inline format.
 *
 * @param {Object}   props            Format edit props.
 * @param {boolean}  props.isActive   Whether the format is on the selection.
 * @param {Object}   props.value      RichText value.
 * @param {Function} props.onChange   RichText change handler.
 * @param {Object}   props.contentRef Ref to the editable element.
 * @return {JSX.Element} The format UI.
 */
function Edit( { isActive, value, onChange, contentRef } ) {
	const [ isOpen, setOpen ] = useState( false );
	const [ fields, setFields ] = useState( () => seedFields( getActiveFormat( value, FORMAT_NAME ) ) );

	const popoverAnchor = useAnchor( {
		editableContentElement: contentRef && contentRef.current ? contentRef.current : null,
		settings,
	} );

	const openPanel = () => {
		setFields( seedFields( getActiveFormat( value, FORMAT_NAME ) ) );
		setOpen( true );
	};

	const apply = () => {
		const attributes = { state: fields.state };
		OPTIONAL_KEYS.forEach( ( key ) => {
			if ( fields[ key ] ) {
				attributes[ key ] = fields[ key ];
			}
		} );
		onChange( applyFormat( value, { type: FORMAT_NAME, attributes } ) );
		setOpen( false );
	};

	const clear = () => {
		onChange( removeFormat( value, FORMAT_NAME ) );
		setOpen( false );
	};

	return (
		<>
			<RichTextToolbarButton
				icon={ EvidenceIcon }
				title={ __( 'AI Evidence mark', 'ai-evidence-block' ) }
				onClick={ openPanel }
				isActive={ isActive }
				role="menuitemcheckbox"
			/>
			{ isOpen && (
				<Popover
					anchor={ popoverAnchor }
					placement="bottom-start"
					onClose={ () => setOpen( false ) }
					className="aeb-format-popover"
					focusOnMount="firstElement"
				>
					<div className="aeb-format-popover__inner">
						<ProvenanceFields
							value={ fields }
							onChange={ ( update ) => setFields( { ...fields, ...update } ) }
						/>
						<Flex justify="flex-end" className="aeb-format-popover__actions">
							{ isActive && (
								<Button variant="tertiary" isDestructive onClick={ clear }>
									{ __( 'Remove', 'ai-evidence-block' ) }
								</Button>
							) }
							<Button variant="primary" onClick={ apply }>
								{ isActive ? __( 'Update', 'ai-evidence-block' ) : __( 'Mark claim', 'ai-evidence-block' ) }
							</Button>
						</Flex>
					</div>
				</Popover>
			) }
		</>
	);
}

registerFormatType( FORMAT_NAME, settings );
