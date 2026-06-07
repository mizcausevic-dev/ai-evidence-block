<?php
/**
 * Procedural helpers: the model vocabulary, sanitizers, and small utilities
 * shared by the block renderer, the inline-format scanner, and the editor.
 *
 * @package KineticGain\AIEvidence
 */

namespace KineticGain\AIEvidence;

defined( 'ABSPATH' ) || exit;

/**
 * The AI model vocabulary offered in the editor and resolved to labels at render.
 *
 * Slugs are stable, machine-readable identifiers; labels are human-facing. The
 * list is intentionally filterable so a site can keep it current without
 * touching plugin code, and so the Kinetic Gain Protocol Suite can later own a
 * canonical model registry.
 *
 * `other` is always appended and powers the free-text "specify a model" field.
 *
 * @return array<string,string> Map of model slug => display label.
 */
function models() {
	$models = array(
		'gpt-5'             => 'GPT-5',
		'gpt-5-mini'       => 'GPT-5 mini',
		'gpt-4-1'          => 'GPT-4.1',
		'o3'               => 'OpenAI o3',
		'claude-opus-4'    => 'Claude Opus 4',
		'claude-sonnet-4'  => 'Claude Sonnet 4',
		'claude-haiku-4'   => 'Claude Haiku 4',
		'gemini-2-5-pro'   => 'Gemini 2.5 Pro',
		'gemini-2-5-flash' => 'Gemini 2.5 Flash',
		'llama-4'          => 'Llama 4',
		'mistral-large'    => 'Mistral Large',
		'deepseek-r1'      => 'DeepSeek-R1',
		'perplexity-sonar' => 'Perplexity Sonar',
		'grok-3'           => 'Grok 3',
	);

	/**
	 * Filter the AI model vocabulary.
	 *
	 * @param array<string,string> $models Map of model slug => display label.
	 */
	$models = (array) apply_filters( 'kgaeb_models', $models );

	// `other` is reserved and always present for the free-text path.
	$models['other'] = __( 'Other (specify)', 'ai-evidence-block' );

	return $models;
}

/**
 * Resolve a stored model value to a human-readable label.
 *
 * When the value is `other`, the author-supplied custom name is used.
 *
 * @param string $model        Stored model slug (or a raw label for legacy data).
 * @param string $custom_model Author-supplied model name when $model is `other`.
 * @return string Display label, or '' when nothing usable is present.
 */
function model_label( $model, $custom_model = '' ) {
	$model = (string) $model;

	if ( 'other' === $model ) {
		return trim( (string) $custom_model );
	}

	$models = models();
	if ( isset( $models[ $model ] ) ) {
		return $models[ $model ];
	}

	// Tolerate free-text or unknown slugs rather than dropping provenance data.
	return trim( $model );
}

/**
 * Confidence levels offered for a claim.
 *
 * @return array<string,string> Map of confidence slug => display label.
 */
function confidence_levels() {
	return array(
		'high'    => __( 'High', 'ai-evidence-block' ),
		'medium'  => __( 'Medium', 'ai-evidence-block' ),
		'low'     => __( 'Low', 'ai-evidence-block' ),
		'unrated' => __( 'Unrated', 'ai-evidence-block' ),
	);
}

/**
 * Map a confidence level to the spec's optional 0.0–1.0 retrieval confidence.
 *
 * `unrated` returns null so the field is omitted rather than guessed.
 *
 * @param string $level Confidence slug.
 * @return float|null Numeric confidence, or null when unrated/unknown.
 */
function confidence_score( $level ) {
	$map = array(
		'high'   => 0.9,
		'medium' => 0.6,
		'low'    => 0.3,
	);

	return isset( $map[ $level ] ) ? $map[ $level ] : null;
}

/**
 * Sanitize a confidence level to a known slug.
 *
 * @param mixed $value Raw value.
 * @return string A valid confidence slug; defaults to `unrated`.
 */
function sanitize_confidence( $value ) {
	$value = sanitize_key( (string) $value );
	return array_key_exists( $value, confidence_levels() ) ? $value : 'unrated';
}

/**
 * Sanitize a model value: a known slug, `other`, or a trimmed free-text label.
 *
 * @param mixed $value Raw value.
 * @return string Sanitized model value.
 */
function sanitize_model( $value ) {
	$value = sanitize_text_field( (string) $value );
	$slug  = sanitize_key( $value );

	if ( array_key_exists( $slug, models() ) ) {
		return $slug;
	}

	return $value;
}

/**
 * Sanitize the evidence notes field: plain text, capped at 500 characters.
 *
 * @param mixed $value Raw value.
 * @return string Sanitized, length-limited notes.
 */
function sanitize_notes( $value ) {
	$value = sanitize_textarea_field( (string) $value );
	if ( function_exists( 'mb_substr' ) ) {
		return mb_substr( $value, 0, 500 );
	}
	return substr( $value, 0, 500 );
}

/**
 * The default human reviewer for a claim: the post author's display name.
 *
 * @param int $post_id Optional post ID. Defaults to the current post.
 * @return string Display name, or '' when unavailable.
 */
function default_reviewer( $post_id = 0 ) {
	$post = get_post( $post_id ? $post_id : null );
	if ( ! $post ) {
		return '';
	}

	$author = get_the_author_meta( 'display_name', (int) $post->post_author );
	return $author ? $author : '';
}

/**
 * Convert a Y-m-d date (or any parseable date) to an RFC 3339 UTC datetime.
 *
 * The AI Evidence Format expects date-time strings; the editor captures a date.
 * We anchor to 00:00:00Z so the value is deterministic and reproducible.
 *
 * @param string $date Date string from the editor (typically Y-m-d).
 * @return string RFC 3339 datetime, or '' when the input is empty/invalid.
 */
function to_datetime( $date ) {
	$date = trim( (string) $date );
	if ( '' === $date ) {
		return '';
	}

	$timestamp = strtotime( $date );
	if ( false === $timestamp ) {
		return '';
	}

	return gmdate( 'Y-m-d\TH:i:s\Z', $timestamp );
}

/**
 * Whether the current request is the canonical front-end view of a post where
 * provenance metadata should be collected and flushed.
 *
 * Gates collection to the main singular query so the_content running for
 * excerpts, secondary loops, feeds, or the admin never double-counts claims.
 *
 * @return bool
 */
function should_collect() {
	if ( is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		return false;
	}
	if ( is_feed() ) {
		return false;
	}

	$ok = is_singular() && is_main_query();

	/**
	 * Filter whether provenance metadata is collected for this request.
	 *
	 * @param bool $ok Whether to collect.
	 */
	return (bool) apply_filters( 'kgaeb_should_collect', $ok );
}

/**
 * Compute the AI Evidence Format `content_hash` over a claim's text.
 *
 * IMPORTANT: the bytes hashed are the claim text *after* Claim::normalize_text()
 * has already run (decode HTML entities, collapse every whitespace run to one
 * space, trim). That upstream step is the substantive canonicalization — by the
 * time text reaches here it carries no CR/LF and no leading/trailing space. A
 * third party reproducing this hash must apply the SAME normalization to the
 * disclosed `claim_text` (the evidence object emits it verbatim next to the
 * hash), NOT to raw page markup.
 *
 * The two transforms below (CRLF/CR -> LF, strip one trailing LF) are a
 * defensive tail for any caller that passes un-normalized text directly; on the
 * normal Claim path they are inert because normalize_text already removed all
 * newlines.
 *
 * Canonical form, end to end:
 *   1. UTF-8 text.
 *   2. Decode HTML entities (ENT_QUOTES).            [Claim::normalize_text]
 *   3. Collapse each whitespace run to one U+0020, trim. [Claim::normalize_text]
 *   4. CRLF/CR -> LF, strip one trailing LF.         [here; usually inert]
 *   5. SHA-256, lowercase hex, prefixed `sha256:`.
 *
 * No Unicode NFC/NFD normalization is applied, so visually identical text in
 * different normal forms hashes differently. The external AI Evidence Format
 * spec should document this exact canonicalization so any verifier can reproduce
 * it (see ai-evidence-format-spec — tracked as a spec-completeness follow-up).
 *
 * @see Claim::normalize_text() The upstream step that does the real canonicalization.
 * @param string $text The claim text (already plain text, typically pre-normalized).
 * @return string Hash in the form `sha256:<64 hex chars>`, or '' for empty input.
 */
function content_hash( $text ) {
	$text = (string) $text;
	if ( '' === $text ) {
		return '';
	}

	// Normalize CRLF and CR to LF.
	$text = str_replace( array( "\r\n", "\r" ), "\n", $text );

	// Strip exactly one trailing newline, if present.
	if ( "\n" === substr( $text, -1 ) ) {
		$text = substr( $text, 0, -1 );
	}

	return 'sha256:' . hash( 'sha256', $text );
}
