---
title: Action Style as a Descriptor Property - Plan
type: refactor
date: 2026-08-17
topic: action-style-descriptor-property
artifact_contract: ce-unified-plan/v1
artifact_readiness: implementation-ready
product_contract_source: ce-plan-bootstrap
execution: code
---

# Action Style as a Descriptor Property - Plan

Extends `docs/plans/2026-08-17-0045-feat-action-styling-parity-plan.md`, which gave actions a context-aware style but
kept it inside the `Style` capability. This removes the capability and makes style a property of the action, the way
`label` already is.

## Goal Capsule

- **Objective:** Style an action with `->style(...)` instead of `->with(new Style(...))`, extend the same method to
  action collections so a dropdown toggle can finally be styled, and move the default `btn-primary` out of the Blade
  views into PHP. Release 2.0.
- **Authority hierarchy:** Key Technical Decisions below outrank unit approaches. `AGENTS.md` outranks this plan on
  repo process. The styling plan's shipped API defines what "the same" means and is not re-litigated here.
- **Execution profile:** A `feature/*` branch off `release/2.x`. One commit per implementation unit, and every unit
  leaves the tree green on its own. No PHP on the host, every command runs through Docker.
- **Stop conditions:**
  - **Never run `git commit` or `git push`.** `AGENTS.md` makes this absolute and it overrides any skill instruction.
  - Stop the `docs` compose service before any Docusaurus build.
  - Stop and ask if any rendered HTML changes. This refactor is behaviour-preserving by design, see R7.
  - Stop and ask before touching `check()` or `contribute()`. This plan removes one capability, not the capability
    system.
- **Tail ownership:** The author commits, pushes and opens the PR. A run ending at "ready to commit, gate green" is
  complete.
- **Open blockers:** None.

---

## Product Contract

### Summary

An action's style stops being a capability and becomes a property of its descriptor, set with `Action::style()` and
merged across calls the way `Column::style()` merges. `ActionCollection::style()` gains the same method so the dropdown
toggle button can be styled, which is impossible today. The default button classes move from six Blade views into PHP,
where the rest of the package's style defaults already live.

### Problem Frame

`Style` is the only capability in the package that implements `apply()`. `Authorize` and `When` gate through `check()`;
`Tooltip` and `Confirmation` contribute markup through `contribute()`. So one third of the capability protocol exists to
serve one class, and that class does nothing but write a string into `ActionDescriptor::$attributes['class']`.

That routing has three visible consequences.

**The action collection cannot be styled at all.** `ActionCollection` extends `Illuminate\Support\Collection`. It has no
`with()`, no descriptor and no capabilities, so the capability route was never open to it. The dropdown toggle button is
hardcoded as `btn btn-primary dropdown-toggle` in
`resources/views/bootstrap-5/actions/collection/dropdown.blade.php:11`, and publishing the views is the only way to
change it. This is the decisive point: a convenience method wrapping the capability could not reach the toggle, because
there is no capability list to append to.

**The default lives in the views.** Six Blade files carry a variant of
`trim('btn ' . ($attributes['class'] ?? 'btn-primary'))`, and `resources/views/actions/attributes.blade.php` carries a
special case that skips `class` because style is smuggled through the generic attributes bag. Cell styling resolves its
defaults in PHP through `StyleResolver::withDefaults()`. Same package, two answers to the same question.

**Two `Style` capabilities do not merge.** `Column::style()` called twice merges through `StyleSet::with()`.
`->with(new Style(A))->with(new Style(B))` does not: the second assigns `attributes['class']` and the first is lost,
pinned by `test_apply_overwrites_a_class_of_an_earlier_capability`. Within one `Style` the cases combine; across two
they do not. That contradicts the rule set for this release, that styles always merge and a conflict is the author's to
resolve.

Style is `label`-shaped, not `authorize`-shaped. `label` and `intent` are already descriptor properties with fluent
setters. Every action renders with a class whether or not anything was declared, so the concept is always present and
the capability only overrides a default that lives elsewhere.

### Key Decisions

- KD1. **Style becomes a first-class property of an action, not a capability.** (session-settled: user-approved, chosen
  over a convenience method wrapping the capability: `ActionCollection` has no capability list, so the wrapper could not
  reach the dropdown toggle, and the split default in the views would survive either way.) Governs R1, R2, R6.
- KD2. **The `Style` capability is deleted, not deprecated.** (session-settled: user-directed, carried from the styling
  plan's "hard break in 2.0, no deprecations". `composer.json` is `2.0.0-alpha` and the release post is still
  `draft: true`, so there is no installed base to break.) Governs R8.
- KD3. **Collection styling covers the dropdown toggle only.** (session-settled: user-approved, chosen over styling
  every collection type: the toggle is the only button. `Grouped` renders `<div class="btn-group">` and `Normal` renders
  a bare `<div>`, and a `ButtonStyle` on a wrapper is meaningless. Giving wrappers their own vocabulary is speculative
  until someone asks.) Governs R6, and see Scope Boundaries.
- KD4. **Repeated `style()` calls merge.** (session-settled: user-directed, carried from "Always merge all styles, if
  there are duplicates or overwrites, then that is how they wrote it and it's up to them to fix".) Governs R3.
- KD5. **No rendered output changes.** (session-settled: user-approved. This is a refactor of where styling is declared
  and resolved, not of what it produces.) Governs R7.

### Requirements

**Authoring**

- R1. An action's style is declared with `Action::style(\Closure|ButtonStyle ...$styles)`, matching `Column::style()`.
- R2. The style lives on `ActionDescriptor` alongside `label` and `intent`, not in the capability list.
- R3. Repeated `style()` calls on one action merge into a single set. Nothing is dropped or overwritten.
- R4. Closures still receive the `ActionContext` and may return a case, a list of cases, or `null`, unchanged from the
  parity plan.

**Collections**

- R5. `ActionCollection::style(\Closure|ButtonStyle ...$styles)` exists and takes the same shape.
- R6. A dropdown collection's toggle button renders with the declared style. It renders the button variant, not the
  dropdown variant, because the toggle is a button that sits outside the menu.

**Rendering**

- R7. Every rendered action and collection produces exactly the HTML it produces today when the same styles are
  declared. Existing render assertions carry over unchanged.
- R8. The `Style` capability no longer exists. No class in `src/` implements `apply()`.
- R9. The base classes (`btn`, `dropdown-item`, `dropdown-toggle`) and the default variant (`btn-primary`) are decided
  in PHP. A theme view receives a finished class string.
- R10. `resources/views/actions/attributes.blade.php` no longer special-cases `class`.

**Documentation**

- R11. `action-styling.md` documents `->style()`, collection styling, and no longer mentions the `Style` capability.
- R12. `upgrading.md` records the authoring change, and `action-definition.md` no longer lists `Style` as a capability.

### Acceptance Examples

- AE1. `new Action()->style(ButtonStyle::Danger)` renders `class="btn btn-danger"`.
- AE2. `new Action()` with no style renders `class="btn btn-primary"`, as today.
- AE3. `new Action()->style(ButtonStyle::Default)` renders `class="btn btn-primary"`. `Default` means "the default", and
  that is what it means today by falling through to the view.
- AE4. `new Action()->style(ButtonStyle::Danger)` inside a dropdown renders `class="dropdown-item text-danger"`.
- AE5. `new Action()` with no style inside a dropdown renders `class="dropdown-item"`, with no trailing space.
- AE6. `->style(ButtonStyle::Danger)->style(ButtonStyle::Link)` renders both, `class="btn btn-danger btn-link"`. Today's
  two-capability equivalent renders only `btn-link`, and that difference is the point of KD4.
- AE7. `->style(fn (ActionContext $c) => $c->model?->is_locked ? ButtonStyle::Secondary : ButtonStyle::Danger)` renders
  per row.
- AE8. `new ActionCollection([...])->dropdown()->style(ButtonStyle::Danger)` renders the toggle as
  `class="btn btn-danger dropdown-toggle"`, and the items inside are unaffected.
- AE9. A dropdown collection with no style renders the toggle as `class="btn btn-primary dropdown-toggle"`, as today.
- AE10. A style declared on a `Grouped` or `Normal` collection changes nothing. See KD3 and Scope Boundaries.

### Scope Boundaries

Not in this work:

- **A style vocabulary for collection wrappers.** `btn-group` and the plain `<div>` are not buttons. If wrapper styling
  is wanted later it needs its own vocabulary, which is the styling plan's KD9 all over again.
- **The confirmation modal's buttons.** `resources/views/bootstrap-5/actions/contribution/confirmation-modal.blade.php`
  hardcodes `btn btn-primary` for its confirm button. That is modal chrome belonging to a contribution, not an action
  style, and it stays hardcoded.
- **Removing `apply()` from the capability contract.** See KTD3.
- **`ActionCollection`'s clone-versus-mutate inconsistency.** `type()` and `asType()` return clones while `label()`
  mutates. Pre-existing, worth fixing, not fixed here. See KTD5 for how `style()` sidesteps it.
- **Table, row, column, cell and accent styling.** Shipped and unchanged.

### Sources / Research

- `src/Actions/Action.php:57` (`with()`), `:63` (`descriptor()`), the capability loop at `:70`.
- `src/Actions/ActionCapability.php` and `src/Actions/Contracts/ActionCapability.php`, the three-hook protocol.
- Hook usage census: `Style::apply()` is the only `apply()` implementation in `src/`; `Authorize` and `When` implement
  `check()`; `Tooltip` and `Confirmation` implement `contribute()`.
- `src/Actions/ActionDescriptor.php`, showing `label` and `intent` as descriptor properties.
- `src/Actions/Collections/ActionCollection.php`, no `with()`, no descriptor, `label()` mutates and `type()` clones.
- `src/Actions/ActionRenderer.php:47` (`renderActionCollection`), where the collection's `label` is already resolved
  through `LazyValue` against the context, so the context is in hand for a style set too.
- `resources/views/bootstrap-5/actions/collection/dropdown.blade.php:11`, the hardcoded toggle.
- `src/Column.php:206`, the `style()` shape being matched.
- `src/ValueObjects/StyleSet.php` and `src/Styles/StyleResolver.php`, the shipped resolution machinery.

---

## Planning Contract

### Key Technical Decisions

- KTD1. **An `ActionRegion` enum replaces the `bool $inDropdown` flag on `ButtonStyle::toCssClass()`.** Three render
  targets now exist, not two: a button, a dropdown item, and a dropdown toggle. A boolean cannot express three. The enum
  also carries the base class and the default variant per region, which is what makes R9 possible. The parameter stays
  optional, so `ButtonStyle` still satisfies `Contracts\Style::toCssClass(Theme $theme): string`.
  - Rejected: keeping the boolean and leaving base classes in the views. The toggle needs the button variant with a
    different base class, which the boolean cannot say, and R9 would be unmet.
- KTD2. **`ActionRegion` carries `baseCssClass(Theme)` and `defaultStyle()`.** This mirrors `CellStyle` carrying
  `toCssClass()`, `target()` and `family()`, and keeps the resolver thin and the knowledge testable in isolation.
- KTD3. **`apply()` stays on the capability contract.** It loses its only in-package user, not its reason to exist. It
  is the documented hook for a custom capability that mutates the descriptor before render, which is genuinely
  different from `contribute()`, and removing it would take capability away from users to tidy an interface.
  Consequence: the custom-capability docs need an `apply()` example that is not `Style`.
- KTD4. **The descriptor stores, the renderer resolves.** `ActionDescriptor::$style` holds an unresolved `?StyleSet`.
  `ActionRenderer` resolves it against the context and passes a finished `$classes` string into the view. Resolution
  must not happen in `Action::descriptor()`, because a single `Action` instance renders once per row and the descriptor
  is reused, so a resolved string would leak between rows.
- KTD5. **`ActionCollection::style()` mutates and returns `$this`, matching `label()`.** The class is inconsistent
  already (`type()` clones), and `label()` is the closer analogue: both are presentational properties set in a fluent
  chain. Matching the nearer neighbour keeps `->dropdown()->style(...)->label(...)` behaving uniformly.
- KTD6. **U3 is one commit, larger than the others.** The switch from `attributes['class']` to a resolved style set
  cannot be half-applied: the moment the views stop reading `attributes['class']`, the `Style` capability silently stops
  working. Renderer, views, defaults and the capability's removal land together or the tree is not green.

### High-Level Technical Design

```
Action::style(...)  ──> ActionDescriptor::$style : ?StyleSet
                                   │
ActionCollection::style(...) ──> ActionCollection::$style : ?StyleSet
                                   │
                                   ▼
                        ActionRenderer
                                   │  resolve(ActionContext) -> Style[]
                                   │  narrow to ButtonStyle[]
                                   ▼
                        ActionStyleResolver::classes($set, $context, ActionRegion)
                                   │  region default when no variant declared
                                   │  region base class
                                   ▼
                        'btn btn-danger'  ──> view: class="{{ $classes }}"
```

`ActionRegion` cases and what they carry:

| Case             | Base class (Bootstrap 5) | Default variant        | Variant form |
|------------------|--------------------------|------------------------|--------------|
| `Button`         | `btn`                    | `ButtonStyle::Primary` | `btn-danger` |
| `DropdownItem`   | `dropdown-item`          | none                   | `text-danger`|
| `DropdownToggle` | `btn dropdown-toggle`    | `ButtonStyle::Primary` | `btn-danger` |

The `DropdownItem` row having no default is what preserves AE5: today's dropdown view falls back to `''`, not to a
variant.

### Assumptions

- A1. `ButtonStyle::Default` resolving to an empty string, then the region default filling in `btn-primary`, reproduces
  today's fall-through exactly. Verified against `test_apply_does_nothing_for_the_default_style` plus the view's
  `?? 'btn-primary'`. If this proves wrong, AE3 is the failing case.
- A2. No consumer outside the package reads `ActionDescriptor::$attributes['class']`. It is only ever written by `Style`
  and read by the three action views.
- A3. The `feature/*` branch is cut from `release/2.x` with the parity plan's work already merged.

### Sequencing

U1 → U2 → U3 → U4 → U5. U1 and U2 are independent of each other in principle but U3 needs both, and doing U1 first keeps
U2's tests able to assert the final rendering shape. U4 depends on U3 having established the resolver. U5 is
documentation and depends on everything.

### System-Wide Impact

| Area | Change |
|---|---|
| `src/Enums/ActionRegion.php` | New |
| `src/Enums/ButtonStyle.php` | Signature of `toCssClass()` |
| `src/Styles/ActionStyleResolver.php` | New |
| `src/Actions/ActionDescriptor.php` | New `$style` property |
| `src/Actions/Action.php` | New `style()` method |
| `src/Actions/Collections/ActionCollection.php` | New `$style` property and `style()` method |
| `src/Actions/ActionRenderer.php` | Resolves style, passes `$classes` |
| `src/Actions/Capabilities/Style.php` | Deleted |
| 4 Bootstrap 5 action views | Consume `$classes` |
| `resources/views/actions/attributes.blade.php` | Drops the `class` special case |
| `tests/Unit/Actions/Capabilities/StyleTest.php` | Deleted, content redistributed |
| 4 documentation pages | Rewritten sections |

### Risks & Dependencies

- **R-1. Silent HTML drift.** The refactor is only safe if output is identical. Mitigation: R7 makes the existing render
  assertions the contract, and they are carried over verbatim rather than rewritten. Any diff in them is a stop
  condition, not a test to adjust.
- **R-2. Descriptor reuse across rows.** `Action::descriptor()` mutates and returns the same descriptor object every
  render, which is why `emptyBuffers()` exists. Storing a resolved class string on it would leak a row's style into the
  next row. KTD4 addresses this; U2's tests must include a two-context case that would catch it.
- **R-3. Doubling back on freshly written docs.** `action-styling.md` was rewritten two commits ago. This rewrites it
  again. Accepted: 2.0 is unreleased, so the cost is documentation churn now rather than a breaking change later.
- **R-4. `ActionCollection` extends a framework class.** Adding a property to an `Illuminate\Support\Collection`
  subclass is safe for `map`/`filter`, which construct new instances and would drop `$style`. The collection is styled
  where it is declared and rendered, not after a transform, so this is acceptable; U4's tests should pin what happens
  across a `filter()` so the behaviour is known rather than discovered.

---

## Implementation Units

### U1. An action style knows which region it renders into

- **Goal:** `ButtonStyle` can render for a button, a dropdown item, or a dropdown toggle.
- **Requirements:** R6, R9
- **Files:** `src/Enums/ActionRegion.php`, `src/Enums/ButtonStyle.php`, `tests/Unit/Enums/ActionRegionTest.php`,
  `tests/Unit/Enums/ButtonStyleTest.php`
- **Approach:**
  1. Add `ActionRegion` with `Button`, `DropdownItem` and `DropdownToggle`, carrying `baseCssClass(Theme)` and
     `defaultStyle(): ?ButtonStyle` per the design table.
  2. Change `ButtonStyle::toCssClass(Theme $theme, bool $inDropdown = false)` to
     `toCssClass(Theme $theme, ActionRegion $region = ActionRegion::Button)`. `DropdownToggle` and `Button` produce the
     same variant.
  3. Keep the parameter optional so `Contracts\Style` is still satisfied.
- **Execution note:** The existing `ButtonStyleTest` data providers are the safety net. Convert their boolean argument
  to a region rather than rewriting the expectations, so the expected strings stay untouched.
- **Test scenarios:**
  - Every case renders the same button class it renders today.
  - Every case renders the same dropdown class it renders today.
  - A toggle renders the button variant, not the dropdown variant.
  - Each region reports its base class and its default variant.
  - `ButtonStyle` still satisfies `Contracts\Style` with the parameter omitted.
- **Verification:** `ButtonStyleTest`'s expected strings are unchanged from the current file.

### U2. The descriptor carries a style set

- **Goal:** `Action::style()` exists and merges, and the capability still works.
- **Requirements:** R1, R2, R3, R4
- **Dependencies:** U1
- **Files:** `src/Actions/ActionDescriptor.php`, `src/Actions/Action.php`,
  `tests/Unit/Actions/ActionDescriptorTest.php`, `tests/Unit/Actions/ActionTest.php`
- **Approach:**
  1. Add `public ?StyleSet $style = null` to `ActionDescriptor`.
  2. Add `Action::style(\Closure|ButtonStyle ...$styles)` using `Column::style()`'s exact merge line.
  3. Change nothing about rendering yet. The capability keeps working, so the tree stays green.
- **Execution note:** This unit adds an API that is not yet read by the renderer. That is deliberate, it keeps U3
  smaller. Assert on the descriptor, not on HTML.
- **Test scenarios:**
  - `style()` returns the action, so it chains.
  - A first `style()` creates the set; a second merges into it rather than replacing it.
  - Static cases and closures may be interleaved in any order.
  - The descriptor's style survives a `descriptor()` call, which empties the render buffers.
  - Two renders of the same action against different contexts each resolve the set fresh. Covers R-2.
- **Patterns to follow:** `src/Column.php:206`.

### U3. Rendering moves to the style set

- **Goal:** The resolved set produces the class string, and the capability is gone.
- **Requirements:** R7, R8, R9, R10
- **Dependencies:** U1, U2
- **Files:** `src/Styles/ActionStyleResolver.php`, `src/Actions/ActionRenderer.php`,
  `resources/views/bootstrap-5/actions/http.blade.php`, `resources/views/bootstrap-5/actions/modal.blade.php`,
  `resources/views/bootstrap-5/actions/http-modal.blade.php`, `resources/views/actions/attributes.blade.php`,
  delete `src/Actions/Capabilities/Style.php`, delete `tests/Unit/Actions/Capabilities/StyleTest.php`,
  `tests/Unit/Styles/ActionStyleResolverTest.php`, `tests/Unit/Actions/ActionRendererTest.php`
- **Approach:**
  1. Add `ActionStyleResolver::classes(?StyleSet $styles, ActionContext $context, ActionRegion $region): string`. It
     resolves, narrows to `ButtonStyle`, maps with the region, filters empties, substitutes the region's default when
     no variant survives, and prefixes the region's base class.
  2. `ActionRenderer::renderAction()` picks the region from `$context->asDropdown` and passes `$classes`.
  3. The three action views render `class="{{ $classes }}"` and drop their `trim(...)` expressions.
  4. `attributes.blade.php` drops the `class` skip and its comment.
  5. Delete the capability and its test file. Redistribute its assertions: the render assertions move to
     `ActionRendererTest`, the closure-resolution assertions are already covered by `StyleSetTest` and U2.
- **Execution note:** This is the one large commit, per KTD6. Carry the render assertions over verbatim. If any needs
  its expected string edited, stop: that means output changed and KD5 is violated.
- **Test scenarios:**
  - Covers AE1, AE2, AE3. A styled, an unstyled and a `Default`-styled action each render today's class string.
  - Covers AE4, AE5. The same three inside a dropdown, including no trailing space on the unstyled case.
  - Covers AE6. Two `style()` calls render both variants, which is the one intended behaviour change.
  - Covers AE7. A closure styles per row.
  - A style set containing a non-`ButtonStyle` case is ignored rather than rendered.
  - `attributes` no longer contains a `class` key for any action.
  - No class in `src/` implements `apply()`.
- **Verification:** Every render assertion carried from `StyleTest` passes with its original expected string.

### U4. Action collections can be styled

- **Goal:** A dropdown's toggle button can be styled.
- **Requirements:** R5, R6
- **Dependencies:** U3
- **Files:** `src/Actions/Collections/ActionCollection.php`, `src/Actions/ActionRenderer.php`,
  `resources/views/bootstrap-5/actions/collection/dropdown.blade.php`,
  `tests/Unit/Actions/Collections/ActionCollectionTest.php`, `tests/Unit/Actions/ActionRendererTest.php`
- **Approach:**
  1. Add `public protected(set) ?StyleSet $style` with a property hook, matching how `type` and `label` are declared.
  2. Add `style(\Closure|ButtonStyle ...$styles)` that merges and returns `$this`, per KTD5.
  3. `renderActionCollection()` resolves it with `ActionRegion::DropdownToggle` and passes `$toggleClasses`.
  4. The dropdown view renders `class="{{ $toggleClasses }}"`.
- **Execution note:** The toggle sits outside the menu, so resolve it against `$context`, not `$context->asDropdown()`.
  Getting this wrong renders `text-danger` on the toggle and is the single most likely defect in this unit.
- **Test scenarios:**
  - Covers AE8. A styled dropdown renders `btn btn-danger dropdown-toggle` on the toggle.
  - Covers AE9. An unstyled dropdown renders `btn btn-primary dropdown-toggle`, exactly as today.
  - The items inside a styled dropdown are unaffected by the collection's style.
  - A closure on a collection receives the context.
  - Covers AE10. A style on a `Grouped` or `Normal` collection changes nothing, and does not throw.
  - Repeated `style()` calls merge.
  - Covers R-4. The documented outcome of styling a collection and then calling `filter()` on it.

### U5. Documentation

- **Goal:** The docs describe `->style()` and stop describing a capability that no longer exists.
- **Requirements:** R11, R12
- **Dependencies:** U1, U2, U3, U4
- **Files:** `docs/docs/styling/action-styling.md`, `docs/docs/actions/action-definition.md`,
  `docs/docs/upgrading.md`, `docs/docs/actions/action-collections.md`
- **Approach:**
  1. Rewrite `action-styling.md` around `->style()`, keeping the conditional-styling section and adding collection
     styling with its dropdown-toggle-only scope stated plainly.
  2. Remove `Style` from the capability list in `action-definition.md`, and replace the custom-capability `apply()`
     example, which currently leans on `Style`, per KTD3.
  3. In `upgrading.md`, change the `styles:` row of the migration table to `->style(...)` and note under the 2.0 entries
     that action styling is a method rather than a capability.
  4. Cross-link `action-collections.md` to the collection styling section.
  5. No em dashes, per the documentation style rule.
- **Test scenarios:** `Test expectation: none, documentation only. Link validity is covered by the docs build.`
- **Verification:** The Docusaurus build passes with `onBrokenLinks: throw`.

---

## Verification Contract

Every unit ends with the full gate, run through Docker:

| Gate | Command | Bar |
|---|---|---|
| Tests | `docker compose run --rm -T php php vendor/bin/phpunit -c phpunit.xml` | Green, no risky tests |
| Coverage | `--coverage-text` | 100% classes, methods and lines |
| Static analysis | `docker compose run --rm -T php composer ps` | No errors |
| Style | `docker compose run --rm -T php composer cs` | `Fixed 0 of N files` on the second run |
| Docs (U5) | `docker compose run --rm -T docs npm run build` | Success, `docs` service stopped first |

Coverage note: deleting `Style` removes a class from the report. Every `#[UsesClass(Style::class)]` and
`#[CoversClass(Style::class)]` attribute must go with it, or PHPUnit reports "not a valid target for code coverage" and
zeroes the whole report.

The behaviour-preservation check that matters most: after U3, `git diff` on the expected strings inside the carried-over
render assertions must be empty.

## Definition of Done

- `Action::style()` and `ActionCollection::style()` exist, merge across calls, and accept closures.
- A dropdown toggle renders the style declared on its collection.
- `src/Actions/Capabilities/Style.php` no longer exists, and no class in `src/` implements `apply()`.
- No Blade view decides a default button class.
- Every pre-existing render assertion passes with its original expected string, except the two-`Style` overwrite case,
  which is removed because KD4 replaces the behaviour it pinned.
- The four gates are green at every unit boundary, and the docs build passes at U5.
- The author has a one-line commit message per unit and has committed nothing on the agent's behalf.
