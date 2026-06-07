/**
 * The provenance pill — used as a live preview in the editor (block + format).
 */
import { STATES } from '../lib/vocab';
import { StateIcon } from './icon';

/**
 * @param {Object} props        Component props.
 * @param {string} props.state  State slug.
 * @param {string} [props.meta] Optional meta line shown next to the pill.
 * @return {JSX.Element} The pill.
 */
export default function Pill( { state, meta } ) {
	const descriptor = STATES[ state ] || STATES.verified;
	return (
		<span className="aeb-card" role="note">
			<span className={ 'aeb-pill aeb-pill--' + state } style={ { color: descriptor.color } }>
				<StateIcon state={ state } />
				<span className="aeb-pill__label">{ descriptor.label }</span>
			</span>
			{ meta ? <span className="aeb-card__meta">{ meta }</span> : null }
		</span>
	);
}
