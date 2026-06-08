=== AI Evidence Block ===
Contributors: mizcausevic
Tags: ai, provenance, gutenberg, schema, ai-disclosure
Requires at least: 6.6
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Mark AI provenance per claim — model, reviewer, confidence, source — as an evidence card, schema.org JSON-LD, and a verifiable evidence object.

== Description ==

AI Evidence Block treats the smallest unit of trust as the **claim**, not the post. Wrap a section — or mark a single sentence mid-paragraph — and record its provenance from the editor: which model was involved, who reviewed it, how confident you are, and the source that backs it.

On the published page that disclosure renders three ways at once, with **no external requests and no tracking**:

1. A quiet, theme-friendly **evidence card** (and, for inline marks, an accessible underline with a hover/focus popover).
2. Namespaced, machine-readable **`data-aeb-*` attributes** on the marked HTML.
3. A consolidated **schema.org `CreativeWork` JSON-LD graph** for search engines, plus a **verifiable AI Evidence Format object** for audit tooling — including a reproducible `sha256` content hash of the claim text.

It is the WordPress reference implementation of the **AI Evidence Format**, an open specification from the **Kinetic Gain Protocol Suite**.

= Provenance, per claim =

Six provenance states, each with its own treatment:

* **Verified** — author-written and self-attested.
* **Cited** — backed by a link, document, or record.
* **AI-assisted** — AI helped draft; a human reviewed and edited.
* **AI-generated** — produced by AI, disclosed for transparency.
* **Auto-detected** — flagged as likely AI-generated, awaiting confirmation.
* **Disputed** — flagged for review or contested.

= Standards-grade output =

* schema.org `CreativeWork` with `author`, `creator`/`contributor` (the AI model as a `SoftwareApplication`), `creditText`, and `dateCreated` — valid markup that won't fight your SEO plugin.
* The **IPTC DigitalSourceType** (the C2PA-adjacent provenance vocabulary) carried on each node via `additionalType`.
* A pristine, schema-valid **AI Evidence Format v0.1** object per claim, emitted in its own `application/ai-evidence+json` script so it never pollutes your page schema.
* A reproducible `content_hash` (Level 2 “Verify” conformance), so a third party can confirm exactly what text was disclosed.

= What it isn't =

It is not a watermark, not an AI detector for arbitrary text, and not a chatbot. It does not phone home.

== Installation ==

1. Upload the `ai-evidence-block` folder to `/wp-content/plugins/`, or install through the **Plugins** screen in your dashboard.
2. Activate the plugin through the **Plugins** screen.
3. In the editor, open the inserter and search for **AI Evidence** to wrap a section — or select text in any paragraph and choose **AI Evidence mark** from the formatting toolbar to mark a single claim.
4. Pick a provenance state and fill in whatever you know in the block sidebar (or the inline popover).

== Frequently Asked Questions ==

= Does this slow my site? =

No. The plugin makes no external requests and runs no front-end frameworks. It outputs static HTML plus two small inline JSON-LD scripts that are built server-side at render time. There are no web fonts loaded, no API calls, and no tracking.

= Is this compatible with Rank Math or Yoast? =

Yes. AI Evidence Block emits its own additive, block-level `CreativeWork` graph in a separate `application/ld+json` script. It does not modify or replace your SEO plugin's page-level schema (Article, WebPage, Organization, etc.), so the two coexist without conflict.

= Does this share any data with a third-party service? =

No. Everything runs inside your WordPress install. No provenance data leaves your server.

= What specification does the output follow? =

The published markup uses stable, namespaced `data-aeb-*` attributes plus a JSON object conforming to the **AI Evidence Format v0.1** (`evidence_version: "0.1"`). The spec, JSON Schema, and reference examples are public; see the plugin links.

= Will my theme display this correctly? =

The styles are scoped and conservative, designed to sit cleanly inside any well-built theme. Brand fonts are only used if your site already provides them — none are fetched.

= Can I use this without disclosing AI involvement? =

Yes. The block is just as useful for marking **Verified** and **Cited** human-written claims. Many publishers will reach for those states first.

= Can I change how a state maps to provenance standards? =

Yes. The state → `synthesis_role` and state → IPTC DigitalSourceType mapping is exposed via the `kgaeb_provenance_map` filter, and the model list via `kgaeb_models`, so you can adapt the output without editing the plugin.

== Screenshots ==

1. The block in the Gutenberg inserter.
2. The block settings sidebar — provenance state, model, reviewer, confidence, source, and notes.
3. The published post — the evidence card and an inline marked claim.
4. The reader hovers a marked claim and sees its full provenance.

== Changelog ==

= 1.0.0 =
* Initial release.
* Wrapping block (`kineticgain/ai-evidence`) and inline format (`kineticgain/ai-evidence-mark`).
* Six provenance states with the design system's evidence card and inline underlines.
* schema.org `CreativeWork` JSON-LD with IPTC DigitalSourceType, plus a verifiable AI Evidence Format v0.1 object with a reproducible content hash.
* Namespaced `data-aeb-*` attributes; JS-free accessible disclosure with a progressive-enhancement popover.
* Filters: `kgaeb_models`, `kgaeb_states`, `kgaeb_provenance_map`, `kgaeb_evidence_object`, `kgaeb_creativework_node`, `kgaeb_should_collect`.

== Upgrade Notice ==

= 1.0.0 =
First public release.
