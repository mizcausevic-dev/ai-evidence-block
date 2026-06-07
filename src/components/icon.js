/**
 * The EvidenceMark (shield + check) block icon, and small per-state pill icons.
 * Geometry matches the brand system: flat-topped shield, short point, one
 * decisive check drawn as a single geometric gesture.
 */
import { SVG, Path, G, Circle, Line } from '@wordpress/primitives';

const SHIELD = 'M14 10 H50 A4 4 0 0 1 54 14 V32 C54 44 44 52 32 56 C20 52 10 44 10 32 V14 A4 4 0 0 1 14 10 Z';
const CHECK = 'M22 32 L29.5 39.5 L43 26';

/** The Gutenberg inserter / block icon. */
export const EvidenceIcon = (
	<SVG viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg">
		<Path d={ SHIELD } fill="none" stroke="currentColor" strokeWidth="4.8" strokeLinejoin="miter" />
		<Path d={ CHECK } fill="none" stroke="currentColor" strokeWidth="5" strokeLinecap="square" strokeLinejoin="miter" />
	</SVG>
);

/**
 * A 14x14 state pill icon, drawn with currentColor.
 *
 * @param {Object} props       Component props.
 * @param {string} props.state Provenance state slug.
 * @return {JSX.Element} The icon.
 */
export function StateIcon( { state } ) {
	const common = {
		width: 13,
		height: 13,
		viewBox: '0 0 14 14',
		'aria-hidden': true,
		focusable: false,
	};

	switch ( state ) {
		case 'verified':
			return (
				<SVG { ...common }>
					<Path d="M3 7 L6 10 L11 4" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="square" />
				</SVG>
			);
		case 'cited':
			return (
				<SVG { ...common }>
					<Path d="M2 11 V4 a1 1 0 0 1 1-1 h3 v4 H3 M8 11 V4 a1 1 0 0 1 1-1 h3 v4 H9" fill="none" stroke="currentColor" strokeWidth="1.4" />
				</SVG>
			);
		case 'ai-assisted':
			return (
				<SVG { ...common }>
					<G>
						<Circle cx="7" cy="7" r="4.5" fill="none" stroke="currentColor" strokeWidth="1.4" />
						<Circle cx="7" cy="7" r="1.4" fill="currentColor" />
					</G>
				</SVG>
			);
		case 'ai-generated':
			return (
				<SVG { ...common }>
					<G>
						<Circle cx="7" cy="7" r="4.5" fill="none" stroke="currentColor" strokeWidth="1.4" />
						<Circle cx="7" cy="7" r="2.4" fill="currentColor" />
					</G>
				</SVG>
			);
		case 'auto-detected':
			return (
				<SVG { ...common }>
					<G>
						<Path d="M3 4 V3 h2 M9 3 h2 v1 M3 10 v1 h2 M9 11 h2 v-1" fill="none" stroke="currentColor" strokeWidth="1.4" />
						<Line x1="2.5" y1="7" x2="11.5" y2="7" stroke="currentColor" strokeWidth="1.4" />
					</G>
				</SVG>
			);
		case 'disputed':
			return (
				<SVG { ...common }>
					<G>
						<Path d="M7 2 L12 11 H2 Z" fill="none" stroke="currentColor" strokeWidth="1.4" strokeLinejoin="miter" />
						<Line x1="7" y1="6" x2="7" y2="8.5" stroke="currentColor" strokeWidth="1.4" />
						<Circle cx="7" cy="9.8" r="0.7" fill="currentColor" />
					</G>
				</SVG>
			);
		default:
			return (
				<SVG { ...common }>
					<Circle cx="7" cy="7" r="4.5" fill="none" stroke="currentColor" strokeWidth="1.4" />
				</SVG>
			);
	}
}
