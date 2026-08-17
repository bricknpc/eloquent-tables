# Security Policy

## Reporting a vulnerability

Please do not open a public issue, discussion or pull request for a security problem.

Report it privately through GitHub:

**[Report a vulnerability](https://github.com/bricknpc/eloquent-tables/security/advisories/new)**

That opens a private advisory visible only to you and the maintainers. If you cannot use it, open a normal issue
saying only that you have a security report and asking for a private channel. Do not include details in it.

Helpful things to include, as far as you have them:

- The package version, and the PHP and Laravel versions you are running.
- What an attacker can do, and what they need in order to do it.
- The smallest table definition or request that reproduces the problem.

## What to expect

This is a small project maintained by one person, so please treat these as intentions rather than guarantees.

- An acknowledgement within a few days.
- An assessment of whether it is a package problem or an application one, with reasoning either way.
- A fix released for the supported versions listed below, and a GitHub Security Advisory published with credit to
  you unless you would rather stay anonymous.

Please give us a reasonable window to release a fix before disclosing publicly.

## Supported versions

| Version | Supported                                            |
|---------|------------------------------------------------------|
| 1.2.x   | Yes                                                  |
| < 1.2   | No, please upgrade                                   |
| 2.0.x   | Pre-release. Reports are welcome, but 2.0 is not yet released and carries no security support |

## Scope

Things worth reporting:

- Reading data or triggering an action that the `Authorize` or `When` capability should have prevented.
- Injection through anything the package parses from the request: the sort, filter, page and per-page parameters,
  or the saved preferences cookie.
- Model data reaching the page unescaped through a column, a formatter or a column type.
- Anything that lets one table on a page read or change another table's state.

Things that are working as designed, and are an application concern rather than a package one:

- **Labels, tooltips, modal titles and capability contributions are rendered unescaped.** This is deliberate, so they
  can carry icons and markup. They are meant to be developer-authored. Passing unsanitised user input into them is an
  application bug.
- **`sortUsing`, `searchUsing`, `filterUsing` and formatter closures.** Your application supplies the query logic
  there, so what it does with the request is yours to validate.
- **The saved preferences cookie is deliberately exempt from Laravel's cookie encryption** so it can be read before
  the table renders. It holds only a visitor's own per-page and sort choices, both validated against the table's
  declared columns before use. Tampering with it changes what that visitor sees and nothing else.

If you are unsure which side of that line something falls on, report it. A wrong guess in that direction costs
nothing.
