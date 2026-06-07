/**
 * Accessible 2-column provenance state picker (the design's grid).
 * Implemented as a radiogroup so keyboard and screen-reader users get proper
 * single-select semantics.
 */
import { __ } from '@wordpress/i18n';
import { BaseControl, useBaseControlProps } from '@wordpress/components';
import { STATES } from '../lib/vocab';
import { StateIcon } from './icon';

/**
 * @param {Object}   props          Component props.
 * @param {string}   props.value    Selected state slug.
 * @param {Function} props.onChange Change handler.
 * @return {JSX.Element} The picker.
 */
export default function StatePicker( { value, onChange } ) {
	const { baseControlProps } = useBaseControlProps( {
		label: __( 'Provenance', 'ai-evidence-block' ),
	} );

	return (
		<BaseControl { ...baseControlProps } __nextHasNoMarginBottom>
			<div className="aeb-state-picker" role="radiogroup" aria-label={ __( 'Provenance state', 'ai-evidence-block' ) }>
				{ Object.keys( STATES ).map( ( slug ) => {
					const selected = slug === value;
					const state = STATES[ slug ];
					return (
						<button
							type="button"
							key={ slug }
							role="radio"
							aria-checked={ selected }
							className={ 'aeb-state-picker__option' + ( selected ? ' is-selected' : '' ) }
							style={ selected ? { '--aeb-state-color': state.color } : undefined }
							onClick={ () => onChange( slug ) }
						>
							<span className="aeb-state-picker__icon" style={ { color: state.color } }>
								<StateIcon state={ slug } />
							</span>
							<span className="aeb-state-picker__label">{ state.label }</span>
						</button>
					);
				} ) }
			</div>
		</BaseControl>
	);
}
