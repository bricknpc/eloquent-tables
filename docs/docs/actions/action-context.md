---
sidebar_position: 3
---

# Action context

The `ActionContext` is an object that is passed to all actions and all callbacks defined on actions. It knows the 
context of where the action is rendered, for instance if the action is rendered as a row action it will contain the 
model of the current row. And if an action is rendered inside a dropdown list, the context will know this as well.

## Action context properties

| Property      | Type                                      | Description                                               | On table actions                         | On bulk actions                          | On row actions                           |
|---------------|-------------------------------------------|-----------------------------------------------------------|------------------------------------------|------------------------------------------|------------------------------------------|
| `$request`    | `Illuminate\Http\Request`                 | The current request                                       | <span style={{ color: 'green'}}>✓</span> | <span style={{ color: 'green'}}>✓</span> | <span style={{ color: 'green'}}>✓</span> |
| `$config`     | `BrickNPC\EloquentTables\Services\Config` | The configuration of the package                          | <span style={{ color: 'green'}}>✓</span> | <span style={{ color: 'green'}}>✓</span> | <span style={{ color: 'green'}}>✓</span> |
| `$model`      | `Illuminate\Database\Eloquent\Model`      | The model of the current row                              | <span style={{ color: 'red'}}>✗</span>   | <span style={{ color: 'red'}}>✗</span>   | <span style={{ color: 'green'}}>✓</span> |
| `$asDropdown` | bool                                      | Indicates if the action is rendered as part of a dropdown | <span style={{ color: 'green'}}>✓</span> | <span style={{ color: 'green'}}>✓</span> | <span style={{ color: 'green'}}>✓</span> |
| `$isBulk`     | bool                                      | Indicates if the action is rendered as a bulk action      | <span style={{ color: 'green'}}>✓</span> | <span style={{ color: 'green'}}>✓</span> | <span style={{ color: 'green'}}>✓</span> |
