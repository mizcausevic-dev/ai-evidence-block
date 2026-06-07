<?php
/**
 * Server-side render for the kineticgain/ai-evidence block.
 *
 * Receives $attributes, $content, $block in scope (WordPress dynamic block).
 * All real work lives in the Renderer; the returned string is assembled from
 * individually-escaped values plus trusted InnerBlocks output.
 *
 * @package KineticGain\AIEvidence
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    InnerBlocks HTML.
 * @var WP_Block $block      Block instance.
 */

defined( 'ABSPATH' ) || exit;

echo \KineticGain\AIEvidence\Renderer::instance()->render( $attributes, $content, $block ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Assembled from escaped values plus trusted InnerBlocks output.
