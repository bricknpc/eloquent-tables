---
slug: eloquent-tables-2-0-0-released
title: Eloquent Tables 2.0.0 Released
authors: [bricknpc]
tags: [eloquent, laravel, table, release]
---

Version `2.0.0` of Eloquent Tables is here 🎉. This is our first major release since `1.0.0`, and it is a big one. It
rewrites actions from the ground up, gives every table an identity of its own, replaces every styling method with one
consistent shape, and adds footers that can total a column.

It also widens the requirements: PHP `^8.4|^8.5` and Laravel `^12.0|^13.0`.

This release is a breaking change from the previous version, so make sure you read the [upgrade guide](/docs/upgrading).

## What's new

**One action to rule them all.** The actions are rebuilt from the ground up, giving you even more control over what they
do and how they look.

**Action collections.** Group related actions together, or collapse them into a dropdown, with `ActionCollection`.

**Extensibility.** The new action system is extensible, it allows you to write your own intents and capabilities. So are
the new aggregates.

**Modals that actually work.** The `Modal` intent renders a modal with inline content; `HttpModal` loads a URL into the
modal body. In 1.x `TableAction::asModal()` existed but no view ever read the flag, so it rendered a plain link.

**User preferences.** Tables remember what you chose.

**Footers that add up.** Add a footer row that can aggregate a column.

**More Styling.** Styling is now a single concept across pages, tables, actions, rows, and cells.

<!--truncate-->

## One action to rule them all

In 1.x there were three action classes (`TableAction`, `RowAction` and `MassAction`), each with its own constructor,
its own options and its own view builder. Adding a feature meant adding it three times, and the three had quietly
drifted apart: a row action tooltip accepted a closure, a mass action tooltip did not.

2.0 replaces all three with a single `Action`, composed from two pieces:

- an **intent**, set with `->as()`, that decides what the action does and how it renders
- any number of **capabilities**, added with `->with()`, that decide whether it renders and what it looks like

```php
new Action()
    ->label(__('Delete'))
    ->as(new Http(route('users.destroy'), Method::Delete))
    ->style(ButtonStyle::DangerOutline)
    ->with(new Confirmation(__('Are you sure?')));
```

The type of an action is no longer baked into its class. It comes from the method you return it from. The same
`Action` can be a table action, a row action or a bulk action.

## Tables remember what you chose

Set a table to 50 rows per page, sort it by last login, then click into a record and come back. In 1.x you were back to
15 rows in the default order. In 2.0 the table is exactly as you left it.

**Per-page and sort are remembered.** Including the order of a multi-column sort, so a view you built up by clicking
three headers comes back the way you built it. Search terms and filters are deliberately not remembered, because those are
usually a one-off question, and finding an old search still applied on your next visit is more annoying than useful.

**It is stored on the visitor's own device**, in a single cookie shared by every table on the site, and nothing is
written to your database. If you would rather store nothing at all, one config flag turns it off and tables fall back
to their defaults.

**A link still wins.** Following a URL that carries a per-page value or a sort shows that view, and makes it the
visitor's new saved choice. The URL is the source of truth.

## Every table has a name

Underneath that sits the change that made it possible. In 1.x a table had no identity that survived a request, and
every table on a page read the same `?search=`, `?sort[...]` and `?filter[...]`. Two tables side by side fought over
them: sorting one sorted both.

In 2.0 every table has a name, derived from the class name or from whatever you return from `name()`, and everything it
reads is nested under it:

```
?user[search]=ada&user[sort][email]=asc&invoices[page]=2
```

So two tables on a page are finally independent, and the browser console tells you when two of them are accidentally
sharing a name.

**Multi-column sort follows your clicks.** Previously the click order was faithfully recorded in the URL and then
thrown away: the sort came out in whatever order the columns happened to be declared. Now the order you clicked is
the order you get.

**The controls stop eating each other.** Changing the page size, searching, or changing one filter used to replace the
whole query string, silently discarding everything else, including your other filters. Each control now carries the
rest of the table's state with it.

## One way to style anything

In 1.x styling was four unrelated methods with four different shapes: `styles()`, `cellStyles()`, `tableStyles()` and
`pageStyle()`. 2.0 replaces all of them with a single idea. Whatever you are styling, you say `style()`, and you pass
cases from that thing's own vocabulary.

```php
// A table.
public function style(): array
{
    return [TableStyle::Striped, TableStyle::Hover];
}

// A column, and every cell in it.
new Column('total')->style(CellStyle::AlignRight, CellStyle::FontBold);

// An action.
new Action()->style(ButtonStyle::DangerOutline);
```

Each level has its own enum, so the type system stops you putting `AlignRight` on a table or `Striped` on a cell.

**Anything that depends on the row takes a closure instead.** A cell can colour itself from its own value, and a row
can colour itself from its model:

```php
new Column('balance')->style(
    CellStyle::AlignRight,
    fn (CellContext $context) => $context->model?->balance < 0 ? CellStyle::TextDanger : null,
);
```

Static cases and whatever the closure returns are both applied. Nothing is resolved or de-duplicated: if you declare two
styles that fight, both classes are emitted and CSS decides. That is deliberate, because the package guessing which one
you meant is worse than you seeing both.

The same shape reaches actions and action collections, so a delete button can turn grey when a record is locked, and a
dropdown's toggle button can be styled at all, which it never could before.

**A fix rode along with it.** Cell alignment used to apply on sortable columns and quietly do nothing on the rest,
because the alignment classes came in two variants and the non-sortable header used the wrong one. Alignment now works
the same everywhere.

## Footers that add up

A table full of amounts invites the obvious question. 2.0 answers it.

```php
public function columns(): array
{
    return [
        new Column('number'),
        new Column('total')->currency()->aggregate(new Sum()),
    ];
}

public function footer(): array
{
    return [
        new FooterRow(new Sum(), AggregateScope::Page, __('This page')),
        new FooterRow(new Sum(), AggregateScope::Total, __('All invoices')),
    ];
}
```

That renders two footer rows: the total of what is on screen, and the total of everything matching the current search
and filters. On a paginated table those are different numbers, and rather than guess which one you meant, you declare
the ones you want.

**A column opts in.** A footer row only fills a cell where the column declared that aggregate, so an id, a year or a
postcode is never summed by accident.

**Six aggregates ship**: sum, average, median, count, min and max. A sum of a currency column renders as currency,
because the aggregate says whether its result is still in the column's unit; a count of the same column renders as a
plain number, because it is not.

**And you can write your own.** `Aggregate` is an open contract with one method per scope. Return `null` from a scope
you cannot answer and that cell simply stays empty, which is exactly how the built-in median declines to compute across
a whole result set: there is no median function that works on every database Laravel supports, and loading every
matching row to find out would defeat the point of paginating.

## Upgrading

**Every 2.0 upgrade needs at least one change.** Because query parameters are namespaced under the table name now,
any table URL you have hard-coded, linked to or bookmarked changes shape, and the `pageName` and `perPageName`
properties are gone.

On top of that, anyone using actions has more to do: every `tableActions()`, `rowActions()` and `massActions()`
definition needs to be rewritten, and closures now receive a single `ActionContext` instead of a model. Anyone who
styled anything has `styles()`, `cellStyles()`, `tableStyles()` and `pageStyle()` to convert, and anyone who wrote a
custom formatter has one signature to widen.

The [upgrade guide](/docs/upgrading) walks through it step by step, with a full before-and-after example and a list of
every removed and renamed API. Two things in particular are worth reading before you start: the closure signature
change, and the fact that `confirmValue` still exists but means something different.

If your tables only use columns, formatting, searching, sorting, filters or pagination, the actions rewrite passes you
by, but the query parameter change does not, so read that section before you upgrade.
