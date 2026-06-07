<?php
/**
 * Inline SVG icons for the provenance pills, drawn with `currentColor` so the
 * stylesheet owns each state's color. All paths are static, developer-authored
 * markup (no user data), escaped on output via the kses allowlist below.
 *
 * @package KineticGain\AIEvidence
 */

namespace KineticGain\AIEvidence;

defined( 'ABSPATH' ) || exit;

/**
 * Provenance icon set.
 */
class Icons {

	/**
	 * Get the inline SVG for a state's pill icon.
	 *
	 * @param string $state State slug.
	 * @return string SVG markup (escape with self::allowed_svg() on output).
	 */
	public static function pill( $state ) {
		$inner = self::path_for( $state );

		return '<svg class="aeb-pill__icon" width="13" height="13" viewBox="0 0 14 14" '
			. 'aria-hidden="true" focusable="false" role="img">' . $inner . '</svg>';
	}

	/**
	 * The path/group markup for a state.
	 *
	 * @param string $state State slug.
	 * @return string SVG inner markup.
	 */
	private static function path_for( $state ) {
		switch ( $state ) {
			case 'verified':
				return '<path d="M3 7 L6 10 L11 4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="square"/>';

			case 'cited':
				return '<path d="M2 11 V4 a1 1 0 0 1 1-1 h3 v4 H3 M8 11 V4 a1 1 0 0 1 1-1 h3 v4 H9" fill="none" stroke="currentColor" stroke-width="1.4"/>';

			case 'ai-assisted':
				return '<g><circle cx="7" cy="7" r="4.5" fill="none" stroke="currentColor" stroke-width="1.4"/><circle cx="7" cy="7" r="1.4" fill="currentColor"/></g>';

			case 'ai-generated':
				return '<g><circle cx="7" cy="7" r="4.5" fill="none" stroke="currentColor" stroke-width="1.4"/><circle cx="7" cy="7" r="2.4" fill="currentColor"/></g>';

			case 'auto-detected':
				return '<g><path d="M3 4 V3 h2 M9 3 h2 v1 M3 10 v1 h2 M9 11 h2 v-1" fill="none" stroke="currentColor" stroke-width="1.4"/><line x1="2.5" y1="7" x2="11.5" y2="7" stroke="currentColor" stroke-width="1.4"/></g>';

			case 'disputed':
				return '<g><path d="M7 2 L12 11 H2 Z" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linejoin="miter"/><line x1="7" y1="6" x2="7" y2="8.5" stroke="currentColor" stroke-width="1.4"/><circle cx="7" cy="9.8" r="0.7" fill="currentColor"/></g>';

			default:
				return '<circle cx="7" cy="7" r="4.5" fill="none" stroke="currentColor" stroke-width="1.4"/>';
		}
	}

	/**
	 * kses allowlist for safely echoing the inline SVGs.
	 *
	 * @return array<string,array<string,bool>> Allowed tags and attributes.
	 */
	public static function allowed_svg() {
		$attrs = array(
			'class'           => true,
			'width'           => true,
			'height'          => true,
			'viewbox'         => true,
			'fill'            => true,
			'stroke'          => true,
			'stroke-width'    => true,
			'stroke-linecap'  => true,
			'stroke-linejoin' => true,
			'd'               => true,
			'cx'              => true,
			'cy'              => true,
			'r'               => true,
			'x1'              => true,
			'y1'              => true,
			'x2'              => true,
			'y2'              => true,
			'aria-hidden'     => true,
			'focusable'       => true,
			'role'            => true,
		);

		return array(
			'svg'    => $attrs,
			'g'      => $attrs,
			'path'   => $attrs,
			'circle' => $attrs,
			'line'   => $attrs,
		);
	}
}
