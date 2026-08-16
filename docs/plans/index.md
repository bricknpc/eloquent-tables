---
title: Agent Plans
sidebar_position: 0
slug: /
---

# Agent Plans

This section publishes the planning documents behind Eloquent Tables. Every plan the project works from is here, listed in the sidebar.

They are written by the [compound engineering skills](https://github.com/EveryInc/compound-engineering-plugin) vendored into the repository, and reviewed by a human before any code is written. Publishing them is deliberate: if you use this package, the reasoning behind a change should be as available to you as the change itself.

## What a plan contains

A plan is one document that grows through two stages.

**Requirements** come first — the problem, who it affects, what must be true when it ships, and what was deliberately left out. Every requirement carries a stable `R` number so later sections can point at it.

**Implementation** is added on top — the technical decisions and their reasoning, the work broken into units with a `U` number each, the tests that prove it, and the quality gates it must pass.

The `artifact_readiness` field at the top of each plan says which stage it has reached:

| Readiness | Meaning |
|---|---|
| `requirements-only` | The problem and scope are settled. How to build it is not. |
| `implementation-ready` | Both stages are complete, and the work can begin. |

## What a plan is not

A plan records what was decided and why, at the moment it was decided. It is not a changelog and not a progress tracker — whether the work shipped is answered by the repository's history, not by the document.

That means a plan can describe behaviour that has not been released yet, and an older plan can describe a decision that a later plan revised. For what the current release actually does, read the [documentation](/docs/intro). For what changed between versions, read the [blog](/blog).

## Reading them

Each plan is written to be read on its own, so you can open the one you care about without reading the others. The most useful entry points are the **Goal Capsule** at the top, which states the objective in a few lines, and **Scope Boundaries**, which is usually the fastest way to find out whether something you were hoping for is in or out.
