/**
 * Editor entry point.
 *
 * Registers the wrapping block and the inline provenance format, and pulls in
 * the front-end (style.scss -> style-index.css) and editor (editor.scss ->
 * index.css) styles for the build.
 */
import { registerBlockType } from '@wordpress/blocks';

import metadata from './block.json';
import Edit from './edit';
import save from './save';
import { EvidenceIcon } from './components/icon';

import './format';

import './style.scss';
import './editor.scss';

registerBlockType( metadata.name, {
	icon: EvidenceIcon,
	edit: Edit,
	save,
} );
