/**
 * Front-end progressive enhancement for inline provenance marks.
 *
 * Baseline (no JS): each mark carries a native `title` and `aria-label`, so the
 * disclosure is already available. This script upgrades the native tooltip into
 * a single styled popover on hover/focus. No dependencies, no network requests.
 */
( function () {
	'use strict';

	var marks = document.querySelectorAll( '.aeb-mark[title]' );
	if ( ! marks.length ) {
		return;
	}

	var tip = document.createElement( 'div' );
	tip.className = 'aeb-tip';
	tip.setAttribute( 'role', 'tooltip' );
	tip.hidden = true;
	document.body.appendChild( tip );

	var active = null;

	function show( mark ) {
		var text = mark.getAttribute( 'data-aeb-tip' );
		if ( ! text ) {
			return;
		}
		tip.textContent = text;
		tip.hidden = false;
		var rect = mark.getBoundingClientRect();
		var top = rect.bottom + window.scrollY + 6;
		var left = rect.left + window.scrollX;
		// Keep the tip within the viewport horizontally.
		var maxLeft = window.scrollX + document.documentElement.clientWidth - tip.offsetWidth - 8;
		if ( left > maxLeft ) {
			left = Math.max( window.scrollX + 8, maxLeft );
		}
		tip.style.top = top + 'px';
		tip.style.left = left + 'px';
		active = mark;
	}

	function hide() {
		tip.hidden = true;
		active = null;
	}

	Array.prototype.forEach.call( marks, function ( mark ) {
		// Move the native title into a data attribute so we own the presentation
		// and avoid a duplicate browser tooltip.
		var title = mark.getAttribute( 'title' );
		if ( title ) {
			mark.setAttribute( 'data-aeb-tip', title );
			mark.removeAttribute( 'title' );
		}
		if ( ! mark.hasAttribute( 'tabindex' ) ) {
			mark.setAttribute( 'tabindex', '0' );
		}

		mark.addEventListener( 'mouseenter', function () {
			show( mark );
		} );
		mark.addEventListener( 'mouseleave', hide );
		mark.addEventListener( 'focus', function () {
			show( mark );
		} );
		mark.addEventListener( 'blur', hide );
	} );

	document.addEventListener( 'keydown', function ( event ) {
		if ( 'Escape' === event.key && active ) {
			hide();
		}
	} );

	window.addEventListener(
		'scroll',
		function () {
			if ( active ) {
				hide();
			}
		},
		{ passive: true }
	);
}() );
