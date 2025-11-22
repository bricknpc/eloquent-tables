---
sidebar_position: 7
---

# Formatting

Formatters in Eloquent Tables allow you to transform raw model data into display-ready output. They offer a clean, 
reusable, and testable way to control how values appear in your table columns, such as formatting dates, numbers, 
currency, or any other custom representation.

Formatters always receive the raw model value as their first argument, and the model the value came from. A formatter 
must return a Stringable value.

## Built-in Formatters

The Eloquent Tables package ships with several commonly used formatters: a DateFormatter, a DateTimeFormatter, a 
NumberFormatter, and a CurrencyFormatter. You can see how to use them in the [Column](columns.md#formatter) section.

## Custom Formatters

Creating your own formatter is straightforward. Implement the `BrickNPC\EloquentTables\Contracts\Formatter` interface, 
and you're ready to go.