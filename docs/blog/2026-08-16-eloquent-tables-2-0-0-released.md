---
slug: eloquent-tables-2-0-0-released
title: Eloquent Tables 2.0.0 Released
authors: [bricknpc]
tags: [eloquent, laravel, table, release]
draft: true
---

Version `2.0.0` of Eloquent Tables is here 🎉. This is our first major release since `1.0.0`, and it is focused on one
thing: actions.

## One action to rule them all

In 1.x there were three action classes — `TableAction`, `RowAction` and `MassAction` — each with its own constructor,
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

The type of an action is no longer baked into its class — it comes from the method you return it from. The same
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

## Upgrading

2.0 is a breaking release for anyone using actions. Every `tableActions()`, `rowActions()` and `massActions()`
definition needs to be rewritten, and closures now receive a single `ActionContext` instead of a model.

The [upgrade guide](/docs/upgrading) walks through it step by step, with a full before-and-after example and a list of
every removed and renamed API. Two things in particular are worth reading before you start: the closure signature
change, and the fact that `confirmValue` still exists but means something different.

If your tables only use columns, formatting, searching, sorting, filters or pagination, you can upgrade without
changing anything.
