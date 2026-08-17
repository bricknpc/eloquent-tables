---
sidebar_position: 17
---

# Supported versions

## Version matrix

| Eloquent Tables | PHP      | Laravel | Status      |
|-----------------|----------|---------|-------------|
| 2.0             | 8.4, 8.5 | 12, 13  | Active      |
| 1.2             | 8.4, 8.5 | 12      | End of life |
| 1.1             | 8.4      | 12      | End of life |
| 1.0             | 8.4      | 12      | End of life |

Every version also needs the `intl` PHP extension and Bootstrap 5 as the front-end framework.

PHP 8.5 arrived in 1.2. Versions 1.0 and 1.1 declare `php: ^8.4`, which does permit installing under PHP 8.5, but
they were never tested against it.

## What support means

At most three major versions exist at any one time, and each has a different role:

- **The active major** gets new features and bug fixes, released as new minor versions. This is the version the
  documentation you are reading describes.
- **The previous major**, if it is still supported, gets urgent fixes only, released as patch versions. Support for
  it ends when it is announced to end, after which no further pull requests are accepted against it.
- **The next major**, once work on it has started, gets the new features that would break existing code.

There is never more than one of each. That keeps the number of branches a fix might need to reach small enough to
manage.

:::info

Version 1.x was not carried forward. Rather than staying on as a supported previous major, 1.2 reached end of life
when 2.0 was released, so 2.0 is currently the only supported version and there is no 1.x branch to target.

:::

## Branching

Every long-lived branch is named after a version. There is no `main` and there are no `release/*` branches.

The default branch is the current major version, so it is `2.x` today, and it tracks what is released. Nothing
lands on it except a hotfix, and a hotfix is tagged the moment it merges, so the branch and the latest release stay
in step. The version number lives only in that tag, so nothing inside the repository needs bumping.

Everything else is built on a **next release branch**, named after the version it will become. That is `2.1` for a
minor or `3.x` for a major, created from the current major branch the first time work is proposed for it. Only one
next release branch exists at a time.

| Branch           | Purpose                                                                      |
|------------------|------------------------------------------------------------------------------|
| `2.x`            | The current major, and the default branch. Tracks the released state.        |
| `2.1` or `3.x`   | The next release. Features and bug fixes go here. Created when first needed. |
| `feature/<name>` | One feature, branched from the next release branch.                          |
| `bugfix/<name>`  | One bug fix, also branched from the next release branch.                     |
| `hotfix/<name>`  | One urgent fix, branched from the current major branch.                      |

Short-lived branches are named `feature/<issue-number>-descriptive-name`, and the same shape applies to `bugfix/`
and `hotfix/`. The issue number is optional, so `feature/tailwind-theme` is fine when there is no issue behind the
work.

### Which branch to target

**Features and bug fixes** both target the next release branch. If there is not one yet, say so on your issue or
pull request and it will be created. Bug fixes are included here on purpose: an ordinary fix rides along with the
next release rather than triggering one of its own.

**A hotfix** is the exception and targets the current major branch directly. Use it when a problem cannot wait for
the next release. Merging it is what cuts the release, because the branch is tagged as soon as the fix lands, so a
hotfix on top of 2.0 ships as 2.0.1. Cherry-pick it into the next release branch as well, otherwise the fix is
missing from the branch everything else is being built on.

### How a release is cut

**A minor release** is cut by merging its branch into the current major branch and tagging that. So `2.1` merges
into `2.x`, `2.x` is tagged `2.1.0`, and the `2.1` branch has done its job.

**A major release** keeps its own branch. `3.x` is created from `2.x`, and when it is ready it becomes the default
branch and is tagged `3.0.0`. From then on `2.x` is the previous major: it takes hotfixes for as long as it is
supported, each tagged as a further patch on its own line, and once support ends it accepts nothing more.

One consequence worth knowing if you write documentation: this site is published from the default branch, so pages
written on a next release branch go live at the moment that release is cut, not when they merge.
