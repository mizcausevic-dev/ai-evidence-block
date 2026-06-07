/**
 * Provenance vocabulary, sourced from the server (window.kgAEB) with safe
 * fallbacks so the editor still works if localization is unavailable.
 */
import { __ } from '@wordpress/i18n';

const data = ( typeof window !== 'undefined' && window.kgAEB ) || {};

/** @type {Record<string, {label: string, description?: string, color?: string, underline?: string}>} */
export const STATES = data.states || {
	verified: { label: __( 'Verified', 'ai-evidence-block' ), color: '#0F5132', underline: 'solid' },
	cited: { label: __( 'Cited', 'ai-evidence-block' ), color: '#1E3A8A', underline: 'solid' },
	'ai-assisted': { label: __( 'AI-assisted', 'ai-evidence-block' ), color: '#B45309', underline: 'dashed' },
	'ai-generated': { label: __( 'AI-generated', 'ai-evidence-block' ), color: '#B45309', underline: 'dashed' },
	'auto-detected': { label: __( 'Auto-detected', 'ai-evidence-block' ), color: '#B45309', underline: 'dotted' },
	disputed: { label: __( 'Disputed', 'ai-evidence-block' ), color: '#9F1239', underline: 'wavy' },
};

/** @type {Record<string, string>} */
export const MODELS = data.models || {
	'gpt-5': 'GPT-5',
	'claude-opus-4': 'Claude Opus 4',
	'gemini-2-5-pro': 'Gemini 2.5 Pro',
	other: __( 'Other (specify)', 'ai-evidence-block' ),
};

/** @type {Record<string, string>} */
export const CONFIDENCE = data.confidence || {
	high: __( 'High', 'ai-evidence-block' ),
	medium: __( 'Medium', 'ai-evidence-block' ),
	low: __( 'Low', 'ai-evidence-block' ),
	unrated: __( 'Unrated', 'ai-evidence-block' ),
};

export const DEFAULT_REVIEWER = data.defaultReviewer || '';
export const EVIDENCE_VERSION = data.evidenceVersion || '0.1';
export const SPEC_URL = data.specUrl || 'https://github.com/mizcausevic-dev/ai-evidence-format-spec';
export const SUITE_URL = data.suiteUrl || 'https://kineticgain.com';
export const BLOCK_NAME = data.blockName || 'kineticgain/ai-evidence';
export const FORMAT_NAME = data.formatName || 'kineticgain/ai-evidence-mark';

/** The state slugs treated as AI-originated (model field becomes relevant). */
export const AI_STATES = [ 'ai-assisted', 'ai-generated', 'auto-detected' ];

/**
 * Turn an options map into SelectControl options.
 *
 * @param {Record<string,string|{label:string}>} map Options map.
 * @return {{label:string,value:string}[]} Select options.
 */
export function toOptions( map ) {
	return Object.keys( map ).map( ( value ) => {
		const entry = map[ value ];
		const label = typeof entry === 'string' ? entry : entry.label;
		return { label, value };
	} );
}

/**
 * Resolve a model value to a display label.
 *
 * @param {string} model       Model slug or free text.
 * @param {string} customModel Custom name when model is "other".
 * @return {string} Display label.
 */
export function modelLabel( model, customModel ) {
	if ( model === 'other' ) {
		return ( customModel || '' ).trim();
	}
	if ( MODELS[ model ] ) {
		return MODELS[ model ];
	}
	return ( model || '' ).trim();
}
