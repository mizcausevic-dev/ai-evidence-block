# AI Evidence Block

> Mark the AI provenance of any claim you publish — model, reviewer, confidence, source — and render it as a clean evidence card, schema.org JSON-LD, and a verifiable AI Evidence Format object.

The WordPress reference implementation of the **[AI Evidence Format](https://github.com/mizcausevic-dev/ai-evidence-format-spec)**, an open specification from the **[Kinetic Gain Protocol Suite](https://kineticgain.com)**.

![WordPress 6.6+](https://img.shields.io/badge/WordPress-6.6%2B-21759b)
![PHP 7.4+](https://img.shields.io/badge/PHP-7.4%2B-777bb4)
![License: GPL-2.0-or-later](https://img.shields.io/badge/license-GPL--2.0--or--later-blue)
![No phone-home](https://img.shields.io/badge/network-zero%20external%20requests-2ea44f)

---

## Why

The smallest unit of trust is the **claim**, not the post. Wrap a section — or mark a single sentence mid-paragraph — and record its provenance right from the editor: which model was involved, who reviewed it, how confident you are, and the source that backs it.

There is no native Gutenberg block that emits structured, per-claim AI-provenance schema. This is that block, and the spec backing is the moat.

## What it renders

On the published page, one disclosure renders **three ways at once**, with no external requests and no tracking:

1. **A theme-friendly evidence card** (and, for inline marks, an accessible underline with a hover/focus popover).
2. **Namespaced `data-aeb-*` attributes** on the marked HTML — machine-readable without a parser.
3. **A consolidated schema.org `CreativeWork` JSON-LD graph** for search engines, plus a **verifiable AI Evidence Format v0.1 object** (`application/ai-evidence+json`) for audit tooling — including a reproducible `sha256` content hash of the claim text (Level 2 "Verify" conformance).

The AI model is expressed as a schema.org `SoftwareApplication` (`creator`/`contributor`), and each node carries the **IPTC DigitalSourceType** (the C2PA-adjacent provenance vocabulary) via `additionalType`.

## Provenance states

| State | Meaning |
| --- | --- |
| **Verified** | Author-written and self-attested. |
| **Cited** | Backed by a link, document, or record. |
| **AI-assisted** | AI helped draft; a human reviewed and edited. |
| **AI-generated** | Produced by AI, disclosed for transparency. |
| **Auto-detected** | Flagged as likely AI-generated, awaiting confirmation. |
| **Disputed** | Flagged for review or contested. |

## Requirements

- WordPress **6.6+**
- PHP **7.4+**

## Install

### From a release zip

1. Download the latest `ai-evidence-block.zip` from the [Releases](https://github.com/mizcausevic-dev/ai-evidence-block/releases) page.
2. In WordPress, go to **Plugins → Add New → Upload Plugin**, choose the zip, and activate.

### From source

This is a `@wordpress/scripts` project, so the block must be **compiled** before it will register (PHP registers `build/`, not `src/`):

```bash
git clone https://github.com/mizcausevic-dev/ai-evidence-block.git
cd ai-evidence-block
npm install
npm run build      # produces build/ — required for the block to work
```

Then symlink or copy the plugin folder into `wp-content/plugins/` and activate. To produce a distributable zip:

```bash
npm run plugin-zip # bundles build/ + PHP per .distignore
```

## Development

```bash
npm install
npm run start      # watch/rebuild on change
npm run build      # one-off production build
npm run format     # prettier (wp-scripts)
npm run lint:js    # ESLint (WordPress config)
npm run lint:css   # stylelint (WordPress config)
npm run plugin-zip # build a release zip
```

A `.wp-env.json` is included for a local WordPress dev environment via [`@wordpress/env`](https://www.npmjs.com/package/@wordpress/env) (`npx wp-env start`). PHP coding standards are pinned in `phpcs.xml.dist` (WordPress-Extra ruleset).

## Usage

- **Wrap a section:** in the inserter, search for **AI Evidence** and place the block around your content.
- **Mark a single claim:** select text in any paragraph and choose **AI Evidence mark** from the formatting toolbar.
- Pick a provenance state and fill in whatever you know (model, reviewer, confidence, source, notes) in the sidebar or the inline popover.

## Extensibility

Everything maps through filters, so you can adapt the output without editing the plugin:

| Filter | Customizes |
| --- | --- |
| `kgaeb_models` | The selectable AI model list. |
| `kgaeb_states` | The provenance state definitions. |
| `kgaeb_provenance_map` | State → `synthesis_role` and state → IPTC DigitalSourceType mapping. |
| `kgaeb_evidence_object` | The emitted AI Evidence Format object, per claim. |
| `kgaeb_creativework_node` | The schema.org `CreativeWork` node, per claim. |
| `kgaeb_should_collect` | Whether a given claim is collected into the page output. |

## Privacy

AI Evidence Block makes **no external requests**, loads **no web fonts**, runs **no front-end framework on render**, and performs **no tracking**. All provenance data lives inside your own WordPress install. It is not a watermark, not an AI detector for arbitrary text, and not a chatbot.

## Part of the Kinetic Gain Protocol Suite

This plugin is the WordPress surface of the open **[AI Evidence Format](https://github.com/mizcausevic-dev/ai-evidence-format-spec)** (`evidence_version` `0.1`). The spec, JSON Schema, and reference examples are public; the same object this plugin emits is what the rest of the [Kinetic Gain Protocol Suite](https://kineticgain.com) consumes.

## License

[GPL-2.0-or-later](LICENSE) © Kinetic Gain (Miz Causevic)
