<?php
/**
 * Uninstall handler for AI Evidence Block.
 *
 * Fired by WordPress when the plugin is deleted from the Plugins screen.
 *
 * AI Evidence Block stores all of its data *inside post content* — block
 * attributes on the wrapping block and `data-aeb-*` markup on inline marks. It
 * registers no options, no custom tables, no post meta, no transients, and no
 * scheduled events of its own. There is therefore nothing to remove here:
 * deleting the plugin simply stops the disclosure markup from rendering, while
 * the authored content remains untouched in each post.
 *
 * This file exists so the uninstall path is explicit and auditable rather than
 * implicit, in keeping with the plugin's "no surprises, no phone-home" stance.
 *
 * @package KineticGain\AIEvidence
 */

// Bail unless WordPress is genuinely running this as an uninstall hook.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Intentionally a no-op: there is no persistent plugin data to clean up.
