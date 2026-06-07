/**
 * Save for the kineticgain/ai-evidence block.
 *
 * This is a dynamic block: the wrapper, visible card, data attributes, and
 * JSON-LD are produced server-side (render.php) so the provenance markup stays
 * fresh even if the post is not re-saved. We persist only the inner blocks here;
 * render.php receives them as $content.
 */
import { InnerBlocks } from '@wordpress/block-editor';

/**
 * @return {JSX.Element} The saved inner block content.
 */
export default function save() {
	return <InnerBlocks.Content />;
}
