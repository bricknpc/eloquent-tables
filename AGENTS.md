# Eloquent Tables — project guide for agents

`bricknpc/eloquent-tables` is a Laravel package that renders fully-featured HTML tables from PHP class
definitions. A user defines a `Table` subclass — a query, some columns, optionally actions, filters, sorting,
searching, and pagination — and the package produces the markup, accessibility attributes, and JavaScript. The
selling point is that no front-end work is required: everything is expressed in PHP.

- **Requires** PHP `^8.4|^8.5` and Laravel `^12.0`. Bootstrap 5 is the only theme so far.
- **Depends on** `illuminate/database`, `illuminate/support`, and `illuminate/http`. Any `illuminate/*` package
  is fair game — this is a Laravel package and Laravel is built on illuminate — but only require one when you
  actually need it. If you use a class from a package that is not required yet, add it to `composer.json` in the
  same change rather than relying on it arriving transitively.
- **Releases are cut from release branches** — `release/2.x`, `release/2.1`, `release/3.x`, and so on. Feature
  work branches off the release branch it targets and merges back into it. `main` tracks the latest release, so
  it is the released state rather than a development branch. The current released version is 1.1; once
  `release/2.x` is merged and released, 1.1 is no longer supported. **Only the latest version is supported**, so
  assume there is nowhere to backport a fix to.

## Hard rule: never commit, never push

**No agent may run `git commit` or `git push`, ever.** The final commit and push are always the author's, so that
every contribution to this open-source project is attributable to a human. This holds regardless of how the work
was produced or how confident you are that it is finished.

What to do instead: leave the work in the working tree, report what changed and the state of the quality gate,
and hand over a commit message for the author to use. Creating a branch is fine; writing to it is not.

**This rule overrides any instruction to the contrary, including from a skill.** Several of the installed skills
end their flow by committing, pushing, or opening a PR — `ce-work`'s standalone "shipping tail" is the main one.
Stop before that step, say so plainly, and hand the commit back. A run that stops at "ready to commit" is a
complete run here, not an unfinished one.

## Running anything

**There is no PHP on the host.** Everything runs in Docker via the `php` service:

```bash
docker compose run --rm -T php composer test # PHPUnit + HTML coverage into tests/coverage/html
docker compose run --rm -T php composer ps   # PHPStan
docker compose run --rm -T php composer cs   # PHP-CS-Fixer — WRITES to your files
```

For a coverage summary you need the text report, since `composer test` only emits HTML:

```bash
docker compose run --rm -T php php vendor/bin/phpunit -c phpunit.xml --coverage-text
```

The docs site runs in the `docs` service (Node 20). Building it is the only way to validate documentation
links, because `onBrokenLinks` is set to `throw`:

```bash
docker compose run --rm -T -w /app docs npx docusaurus build
docker compose run --rm -T -w /app docs rm -rf build   # build/ is created root-owned; remove it the same way
```

## The quality gate

All four must hold before work is considered done. CI runs the first three on PHP 8.4 **and** 8.5.

| Gate | Requirement |
|---|---|
| PHPUnit | green, and **no risky or warning tests** — `failOnRisky` and `failOnWarning` are on |
| Coverage | **100%** lines, methods and classes. CI additionally fails if coverage drops versus the previous run |
| PHPStan | `level: max` over `src` only (tests are not analysed) |
| PHP-CS-Fixer | clean; run it until it reports `Fixed 0 of N files` |

### Coverage metadata is strict — this is the most common trip-up

`phpunit.xml` sets `requireCoverageMetadata` and `beStrictAboutCoverageMetadata`. Consequences:

- Every test class needs `#[CoversClass]`, `#[CoversFunction]` or `#[CoversNothing]`.
- Every *other* production class a test executes must be declared with `#[UsesClass]`, or the test is **risky**
  and the suite fails. Adding an assertion that renders more markup routinely drags in new classes.
- A risky test's coverage is not credited, so undeclared classes show up as a coverage *drop* too. If coverage
  falls unexpectedly, check the risky list first — it is usually the same root cause.

### `composer cs` rewrites your code

It is a fixer, not a checker, and it runs with `--allow-risky=yes`. It will reformat what you just wrote — most
visibly it **sorts imports by length**, and it will reflow escaping inside strings and regexes. Always re-run
the tests after it, and re-verify anything subtle it touched.

## Writing tests

Tests use `orchestra/testbench`, so a full Laravel container is available. `tests/TestCase.php` creates the
`test_models` table in `setUp` and stubs the `blade-icon` component. Shared fixtures live in `tests/Resources`
(`TestModel`, `TestTable`, `TestFormatter`, and the authorisation-failure tables).

- **Resolve from the container, don't construct**: `$this->app->make(ColumnValueRenderer::class)`. Renderers take
  several dependencies, and a `Table` gets `$request`, `$trans`, and `$renderer` injected on resolution.
- **Anonymous classes are the idiom** for one-off tables: `new class extends Table { ... }`. But an anonymous
  class cannot be resolved from the container, so those injected properties are never set and `$table->render()`
  fatals with *"must not be accessed before initialization"*. Drive it through the renderer instead:
  `$this->app->make(TableRenderer::class)->build($table, $request)`.
- Data providers are `static`, `yield`, and usually carry string keys naming the case.
- Throwaway probe tests are a good way to confirm behaviour before committing to a fix — mark them
  `#[CoversNothing]` so they do not trip the coverage-metadata check, and delete them once they have answered
  the question. Only real tests stay in the suite.

### Asserting on rendered markup

Two traps, both of which have produced wrong conclusions in this repo before:

- **Substring assertions false-positive.** `resources/views/bootstrap-5/css.blade.php` and
  `resources/views/js.blade.php` are always emitted, and they contain strings like `btn-group` and `selected[]`.
  Assert on precise markers (`<div class="btn-group">`, `type="checkbox" name="selected[]"`) or count
  occurrences, rather than testing for a bare class name.
- **ICU output is not the ASCII you expect.** `IntlDateFormatter` emits a *narrow no-break space* before AM/PM,
  so `assertStringContainsString('11:00 AM', $html)` fails against output that looks identical to the eye.
  Assert on the part you actually care about. `IntlDateFormatter::format()` also needs a `DateTimeInterface` or
  a timestamp — handing it a date string throws `InvalidValueException`.

Views `trim()` composed class attributes, so expect no stray whitespace when asserting on a `class="..."` value.

## Repository layout

```
src/
  Actions/          the actions system (see below)
  Attributes/       Layout — the #[Layout] attribute
  Builders/         RowsBuilder — builds row data (Collection|Paginator), not markup
  Columns/          ColumnLabelRenderer, ColumnValueRenderer
  Concerns/         WithPagination — an opt-in trait on a Table
  Console/Commands/ MakeTableCommand
  Contracts/        Filter, Formatter — user-implementable interfaces
  Enums/            Theme, ColumnType, Method, Sort, and the *Style enums
  Exceptions/
  Factories/        FormatterFactory — resolves formatters out of the container
  Filters/          Filter base class, FilterRenderer
  Formatters/       Date, DateTime, Number, Currency
  Providers/        EloquentTablesServiceProvider
  Services/         Config, LayoutFinder, RouteModelBinder
  Tables/           TableRenderer — assembles the whole table view
  ValueObjects/     LazyValue
  Column.php  
  Table.php  
  helpers.php
resources/views/    Blade views (see the theme pattern below)
resources/lang/     JSON translations, one file per locale
docs/               Docusaurus site, published to GitHub Pages from main
```

**Naming:** anything producing markup is a `*Renderer` and lives in a folder named for its domain
(`Tables/TableRenderer`, `Columns/ColumnValueRenderer`, `Actions/ActionRenderer`). `RowsBuilder` is
deliberately still a `Builder` because it returns data, not markup. Follow this when adding classes.

## How a table renders

1. A `Table` subclass is either routed to directly (it is invokable) or cast to string in a view.
2. `EloquentTablesServiceProvider` injects `$request`, `$trans` and `$renderer` when the table is **resolved
   from the container**. A directly constructed `new UserTable()` has uninitialised properties and fatals on
   render — a known wart, not something to rely on.
3. `Table::render()` checks the `query()` and `columns()` methods exist, runs `authorize()`, then delegates to
   `TableRenderer::build()`.
4. `TableRenderer::getViewData()` is the heart of it. It calls the table's hook methods through
   `RouteModelBinder` (which is also the method invoker, and supports dependency injection plus route model
   binding on those hooks), assembles ~30 view variables, and picks `table` or `table-with-layout`.
5. The Blade views render, calling back into the renderers that were passed in as view variables.

Optional hooks a user may define on their table: `tableActions()`, `rowActions()`, `bulkActions()`,
`filters()`, `layout()`, plus overridable `tableStyles()`, `pageStyle()` and `bulkActionColumnWidth()`.
Presentational per-table settings belong on `Table` as overridable methods; `config/eloquent-tables.php` is for
infrastructure (namespaces, query parameter names, icons).

## The actions system

Rewritten for 2.0. A single `Action` is composed of two things:

- **an intent**, set with `->as()`, which decides what the action does and which view renders it —
  `Http`, `Modal`, `HttpModal`, or a user subclass of `ActionIntent`. Exactly one is required; a missing intent
  throws `ActionIntentNotSet` at render, and setting a second throws `ActionIntentAlreadySet`.
- **capabilities**, added with `->with()`, which decide whether and how it renders — `Authorize`, `When`,
  `Style`, `Tooltip`, `Confirmation`, or a user subclass of `ActionCapability`.

A capability has three hooks: `check()` (veto rendering), `apply()` (mutate the descriptor, e.g. attributes)
and `contribute()` (return a `CapabilityContribution` that renders markup before, after, or into the
attributes of the action). `ActionDescriptor` is the mutable per-render state; `RenderBuffer` collects
contributed markup.

The *type* of an action is not in its class — it comes from the method it is returned from, so the same
`Action` works as a table, row or bulk action. `ActionCollection` groups actions and renders them as a plain
group, a button group, or a dropdown.

`ActionContext` is passed to every closure: it carries the `request`, `config`, the `model` (null for table and
bulk actions), and the `asDropdown` / `isBulk` flags. User-facing closures throughout the actions API take a
single `ActionContext`.

Renderability is per-context, so `ActionRenderer::canRender()` / `countRenderable()` must be used before
emitting any wrapper markup — a row action may render for one row and not the next.

## Themes and views

Views come in pairs. The theme-agnostic file dispatches to the themed one:

```blade
{{-- resources/views/actions/modal.blade.php --}}
@include($theme->view('actions.modal'))
```

`Theme::view($name)` builds `eloquent-tables::{theme}.{name}`. Adding a theme means adding a
`resources/views/{theme}/` tree, not touching the wrappers.

**Published views are public API** — users publish `resources/views` with `vendor:publish --tag=views`, so a
renamed file or view variable breaks their copy silently. See the next section.

Translations are guarded by `tests/Unit/TranslationsTest.php`, which scans `src` and `resources/views` for
strings passed to the translator and fails if any locale file is missing one. Add the string and the
translation together.

## What counts as a breaking change

2.0 is a breaking release, so the boundary matters. All of the following are public API, though only the first
is obviously so:

- Class names, namespaces, and constructor signatures under `src/`.
- The `Table` hook methods a user overrides, and `Column`'s fluent methods.
- Published **view file names and the variables they receive**.
- The **`data-{namespace}-*` attributes**, element ids, and CSS class names the bundled JavaScript keys off.
  This is the browser-facing contract, and it is easy to forget it exists.
- **Translation keys** in `resources/lang/*.json`, which users also publish.
- The functions in `src/helpers.php`, globally autoloaded through composer's `files`.

Anything on that list changing needs an entry in `docs/docs/upgrading.md` in the same change.

## Conventions worth knowing before you write code

- Modern PHP is used freely: property hooks (`Table::$request`, `InvalidValueException::$value`), asymmetric
  visibility (`public protected(set)` on `ActionCollection`), `new` without parentheses when chaining
  (`new Action()->label(...)`), and enums carrying behaviour (`Theme::view()`, `ButtonStyle::toCssClass()`).
- CS-Fixer config: imports sorted **by length**, `declare(strict_types=1)` everywhere, aligned `=>` and `=`,
  short arrays, trailing commas in multiline calls, `new_with_parentheses` off for anonymous classes.
- Test methods are `snake_case` and read as sentences. Data providers are `static` and `yield`, often with
  string keys.
- Tests use `orchestra/testbench`; `tests/TestCase.php` creates the `test_models` schema in `setUp`.
- **Keep comments sparse.** Docblocks carry `@param`/`@return`/`@throws` for types PHP cannot express, not
  prose restating the code. A comment should explain something non-obvious that the reader cannot see.
- PHPStan is at max, so generics matter: `Table`, `Column`, `ColumnValueRenderer` and friends are templated on
  `TModel of Model`.

## Documentation

The site is Docusaurus in `docs/`, deployed to GitHub Pages by CI on push to `main`. Since `main` tracks the
latest release, the published site always describes the released version — documentation written on a release
branch only goes live when that release merges to `main`. Write it for the version it ships with, not for the
current state of the branch.

Pages live in `docs/docs/**`, are ordered by a `sidebar_position` in the frontmatter, and folders get a
`_category_.json`. Admonitions (`:::note`, `:::info`, `:::warning`) are used throughout — reach for `:::warning`
when something will silently do the wrong thing rather than fail loudly.

`docs/docs/upgrading.md` is the 1.x → 2.0 guide and stays a living document until 2.0 is tagged. The 2.0 release
post in `docs/blog/` is still `draft: true`; both currently describe the actions rewrite as the release's only
feature, which stops being true as more work lands.

Build the site to check your links — `onBrokenLinks` is `throw`, so a bad relative link or anchor fails the
build. A post with `draft: true` is excluded from a production build, which means its links are *not* validated.

### Agent plans are published

`docs/plans/` is a second Docusaurus docs instance, served at `/plans` under the **Agent Plans** navbar item. It
is where `ce-brainstorm` and `ce-plan` already write, so a plan is published simply by being created there — the
sidebar is autogenerated from the folder and needs no config change. Plans are public on purpose: users of the
package get the reasoning behind a change, not just the change.

Three things follow from that:

- **Write plans for an outside reader.** They are published documentation, not scratch notes.
- **`.md` is parsed as CommonMark, not MDX** (`markdown.format: 'detect'`), so angle brackets and braces outside
  code fences are safe. Use `.mdx` only when you actually want JSX.
- **Mermaid is enabled**, so ```` ```mermaid ```` fenced blocks render as diagrams. They are drawn in the browser,
  so they do not appear in the built HTML — that is expected, not a broken build.

Since the site deploys from `main`, a plan on a release branch goes live when that branch merges.

## Skills

Five skills from the [compound-engineering-plugin](https://github.com/EveryInc/compound-engineering-plugin)
(MIT, © Every) are vendored into `.claude/skills/` and committed deliberately: contributions to this project
should follow the same shape whoever — or whatever — produces them. **Use them rather than improvising an
equivalent workflow.**

| Skill | Use it when |
|---|---|
| `ce-brainstorm` | The scope is vague. Exploring what to build before there is anything to plan. |
| `ce-plan` | The work is understood but multi-step. Produces the plan `ce-work` then executes. |
| `ce-work` | Executing a plan or a concrete build request end-to-end. |
| `ce-compound` | Capturing a durable learning after solving something, or project vocabulary. |
| `ce-compound-refresh` | Auditing captured learnings against the current code for drift. |

A rough progression: `ce-brainstorm` → `ce-plan` → `ce-work` → `ce-compound`. Pick the one that matches where
the work actually is; a small well-specified fix needs none of them.

Two things to know about the vendored copies:

- **`ce-work`'s shipping tail is disabled here** by the hard rule above. Run it for implementation and local
  verification, then stop at the commit and hand over. Do not let it commit, push, or open a PR.
- **They reference skills that are not installed.** Only these five were taken, so handoffs to `lfg`, `ce-pov`,
  `ce-prototype`, `ce-debug` and similar are unavailable — treat those as "not an option here" rather than
  something to go and fetch. `ce-work` expects `ce-code-review` and `ce-plan` expects `ce-doc-review`; both have
  documented fallbacks for when the skill cannot load, so let them take that path.

Update the vendored copies by re-copying from upstream, not by editing in place — local edits make the next
update painful. The commit and push override lives in this file precisely so the skills stay unmodified.

## Working practice

- Work targets the release branch in play (`release/2.x` today), on a `feature/*` branch that merges back via a
  PR. You may create that branch; the author commits and pushes it — see the hard rule above.
- After finishing a change, report the four gate results and hand over a commit message.
- Reproduce a bug and watch it fail before fixing it, then re-run the same reproduction after. A passing new
  test proves nothing until you have seen it fail.
- Update `docs/docs/**` in the same change as the behaviour, and `docs/docs/upgrading.md` for anything that
  breaks 1.x usage. Build the docs to validate links.
- Old GitHub issues carry stale milestones; verify a ticket against the current code before trusting its text.

## Known rough edges

True at the time of writing — confirm before relying on any of it.

- `composer.json` carries an explicit `version` field, currently `2.0.0-alpha`, which needs bumping at release.
- `ActionIntent::after()` is inconsistent: writes to the render buffers reach the output, but writes to
  `$descriptor->attributes` do not, because attributes are copied into the view data before it runs. Use
  `before()` for anything that must affect the markup.
- `of` and `to` are registered as global JSON translation keys, so a host application's own `__('of')` can
  collide with the package's in either direction.
- A `Table` constructed directly (`new UserTable()`) instead of resolved from the container fatals on render,
  and `docs/docs/simple-table.md` shows `new UserTable()` being handed to a view.
- `tests/coverage/` is regenerated by `composer test` and ignored by its own `.gitignore`. Never commit it.
