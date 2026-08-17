---
slug: eloquent-tables-2-0-0-released
title: Eloquent Tables 2.0.0 Released
authors: [bricknpc]
tags: [eloquent, laravel, table, release]
draft: true
---

Version `2.0.0` of Eloquent Tables is here 🎉. This is our first major release since `1.0.0`, and it does two things:
it rewrites actions from the ground up, and it gives every table an identity of its own.

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
    ->with(new Style(ButtonStyle::Danger))
    ->with(new Confirmation(__('Are you sure?')));
```

The type of an action is no longer baked into its class. It comes from the method you return it from. The same
`Action` can be a table action, a row action or a bulk action.

<!--truncate-->

## What's new

**Modals that actually work.** The `Modal` intent renders a modal with inline content; `HttpModal` loads a URL into the
modal body. In 1.x `TableAction::asModal()` existed but no view ever read the flag, so it rendered a plain link.

**Action collections.** Group related actions together, or collapse them into a dropdown, with `ActionCollection`.

**Extensibility.** Intents and capabilities are both open contracts, so you can write your own without touching the
package. The capability pipeline has `check`, `apply` and `contribute` hooks, letting a capability veto an action,
change its attributes, or render markup before, after, or inside it.

**Action styling.** The `Style` capability applies `ButtonStyle` values to any action, and adapts automatically inside a
dropdown, where a colour is used instead of a button class.

**Mass is now bulk.** `massActions()` is now `bulkActions()`, in the PHP API and in the rendered markup.

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

## Upgrading

**Every 2.0 upgrade needs at least one change.** Because query parameters are namespaced under the table name now,
any table URL you have hard-coded, linked to or bookmarked changes shape, and the `pageName` and `perPageName`
properties are gone.

On top of that, anyone using actions has more to do: every `tableActions()`, `rowActions()` and `massActions()`
definition needs to be rewritten, and closures now receive a single `ActionContext` instead of a model.

The [upgrade guide](/docs/upgrading) walks through it step by step, with a full before-and-after example and a list of
every removed and renamed API. Two things in particular are worth reading before you start: the closure signature
change, and the fact that `confirmValue` still exists but means something different.

If your tables only use columns, formatting, searching, sorting, filters or pagination, the actions rewrite passes you
by, but the query parameter change does not, so read that section before you upgrade.
