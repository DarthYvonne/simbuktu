# Persona Redesign Plan — Simbuktu

A standalone reference for building the modular handwritten-dimension blueprint system that replaces shitstormlab's statistical persona sampler.

---

## Context

Simbuktu (live at `https://simbuktu.dk`, repo `github.com/DarthYvonne/simbuktu`) is a fork of shitstormlab. The persona system inherited from shitstormlab uses a broad statistical sampler (`AttributeSampler.php`) over ~30 dimensions — Big 5, demographics, subcultures, conflict styles, etc. — that *infers* communication style.

We are **replacing** that with a modular, handwritten-dimension blueprint system. The user explicitly rejected statistical sampling: "I lose control."

---

## Core concepts

### 1. Dimension (the reusable unit)
A named parameter with **N levels** (N ≥ 2, no upper cap). Each level carries **handwritten psychology/communication-consequence text** — the actual snippet that will land in the persona prompt.

Example:
```
name: empathy
levels:
  - name: lav
    text: "Afhumaniserer modparten. Kalder dem 'de der typer'. Ser ikke nuancer..."
  - name: middel
    text: "..."
  - name: høj
    text: "..."
```

### 2. Blueprint (course-level personality definition)
An **ordered list of parameters** that defines what a persona is in a given course. Each parameter in a blueprint is a **full snapshot** — either authored from scratch in the blueprint, or inserted prefilled from the library and freely edited afterwards.

A blueprint can have 3 parameters or 15. It's whatever the user authors.

### 3. Library (template pool)
A reusable pool of dimension templates. Two operations connect blueprint ↔ library:
- **Insert from library** → copies the dimension's current state into the blueprint as a fresh snapshot
- **Save to library** → from a blueprint parameter, either "save as new" (creates a new library entry) or "update existing" (overwrites the canonical template)

**Critical invariant:** library updates do **NOT** retroactively modify blueprints that already inserted that dimension. Each blueprint owns its own snapshots. No silent drift into existing courses.

### 4. Persona generation
For each parameter in a blueprint, sample one level (uniform by default; optional per-blueprint skews later). Concatenate the sampled levels' handwritten text into the persona prompt. **Dimensions are independent — no interaction effects.** (Interactions explode combinatorially and reintroduce the authoring burden the user is escaping.)

---

## Data model

```
library_parameters
  - id
  - name           (unique, e.g. "empathy", "verbosity")
  - description    (optional, for the picker UI)
  - levels         JSON: [{ name: "lav", text: "..." }, ...]
  - timestamps

blueprints
  - id
  - name           (e.g. "Klima-shitstorm", "Boomer-Facebook")
  - description    (optional)
  - parameters     JSON: [{ name, levels: [{name, text}], skews?: {lav: 60, middel: 30, ...} }, ...]
                   ↑ FULL SNAPSHOT, not FK to library_parameters
  - timestamps

courses
  - existing table — add column: blueprint_id (nullable FK to blueprints)
```

`parameters` JSON shape inside a blueprint mirrors a library entry but is independent. Adding `skews` per parameter is optional and can be deferred to phase 2.

---

## UI flow

### Blueprint editor (admin)
```
[Blueprint: "Klima-shitstorm"]                   [Save]

  ▸ Parameter 1: empathy            [from library]   [edit] [↑ promote] [×]
      lav:    "Afhumaniserer modparten..."
      middel: "..."
      høj:    "..."

  ▸ Parameter 2: argumentationsstil [custom]         [edit] [↑ promote] [×]
      anekdotisk: "..."
      moralsk:    "..."
      statistisk: "..."
      [+ add level]

  [+ Add parameter]
       ↳ modal: [Define new] | [Pick from library ▾]
```

- **Edit**: inline edit of name, levels, text per level. Add/remove levels.
- **Promote** (↑): opens dialog "Save to library as new" / "Update existing entry: <name>".
- **Pick from library**: modal showing all library entries with name, description, level count. Click → snapshot inserted into blueprint, fully editable from then on.

### Library manager (admin)
- List all library parameters with name, description, level count, last updated.
- Edit a library entry directly (changes only affect future inserts, never existing blueprints).
- Delete (with confirmation; doesn't affect existing blueprints).

### Course → blueprint linking
- Course settings get a "Personality blueprint" select (dropdown of blueprints).
- Existing populations/personas system is replaced or coexists during transition (decide at implementation time).

---

## Implementation phases

### Phase 1 — Data + library CRUD
- Migration: `library_parameters`, `blueprints`, `courses.blueprint_id`
- Models: `LibraryParameter`, `Blueprint` (JSON casts for `levels` / `parameters`)
- Library admin pages: list, create, edit, delete
- Routes under `/simulation/admin/blueprint-library/`

### Phase 2 — Blueprint editor
- Blueprint list + create/edit/delete pages
- Editor UI with the parameter cards described above
- "Pick from library" modal (server-rendered or Alpine — match the rest of the codebase)
- "Promote to library" dialog (save as new / update existing)
- Routes under `/simulation/admin/blueprints/`

### Phase 3 — Persona generation
- New service `BlueprintPersonaGenerator` that takes a blueprint and produces N personas
- For each persona: for each parameter, pick a level (uniform), assemble the prompt
- Replace the `AttributeSampler`-based flow OR add as alternative — to be decided when we get there
- Course → blueprint wiring so a course-bound generation uses the linked blueprint

### Phase 4 — Skews (optional)
- Per-parameter, per-blueprint weighted sampling: `{lav: 60, middel: 30, høj: 10}`
- UI: weight inputs next to each level in the blueprint editor
- Sampler picks weighted instead of uniform

### Phase 5 — Predefined library seed
- Seeder with starter dimensions: `empathy`, `verbosity`, `partisanship_dk`, `conflict_style`, `religiosity`, `tech_savviness`, `rhetorical_mode`, `language_register`
- Each with handwritten levels in Danish (matching the existing app's Danish UX)
- User authors these once; they become the boilerplate for future blueprints

---

## Decisions already locked

- **Dimensions are independent** — no interaction effects. Snippets concatenate.
- **Free level count** (≥2, no upper cap) per parameter.
- **Library updates do not propagate** to existing blueprints — snapshots are immutable on the blueprint side.
- **Two save-to-library operations**: "save as new" and "update existing".
- **Statistical sampler is dead** in simbuktu. Don't bring it back.

## Decisions deferred (revisit when relevant)

- Whether the new system fully replaces the existing `populations`/`personas` schema or runs alongside during a migration period.
- Skews UX (phase 4) — flat weight inputs vs. sliders vs. presets.
- Whether persona generation should be deterministic (seed-based) so the same blueprint + seed produces the same personas — useful for course reproducibility.
- Whether to track which library version a blueprint was inserted from (purely informational; doesn't affect behavior).

---

## Reference points in code

- Existing sampler to be replaced: `app/Services/Personas/AttributeSampler.php`
- Existing persona controllers: `app/Http/Controllers/Admin/PersonaController.php`, `PopulationController.php`, `PersonalityController.php`
- Existing course model: `app/Models/Course.php`
- Routes are under `/simulation/...` (renamed from `/slophub/`)
- Deploy: `ssh root@157.180.91.162 "bash /var/www/deploy-simbuktu.sh"`
