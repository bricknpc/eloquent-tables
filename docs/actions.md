# Actions

Eloquent Tables support different actions on your tables. Each type of action has its own purpose and configuration.

## Table Actions

Table actions are essentially just buttons or links that can be added to a table that link to a related page or open 
a modal. These actions are the least functional because they are basically just links to other pages.

You can add as many table actions as you want, though keep in mind that the space in the header is limited. If you have 
a lot of table actions, you may want to consider publishing the blade views so you can customise them.

To add table actions, use the `tableActions` method on your Table instance.

```php
<?php
// app/Tables/UserTable.php

namespace App\Tables;

use BrickNPC\EloquentTables\Table;
use BrickNPC\EloquentTables\Enums\ButtonStyle;
use BrickNPC\EloquentTables\Actions\TableAction;

class UserTable extends Table
{
    //... Other methods
    
    /**
     * @return TableAction[]
     */
    public function tableActions(): array
    {
        return [
            new TableAction(route('users.create'), 'Create User')->style(ButtonStyle::Success),
        ];
    }
}
```

### Action

Like every action, a table action requires at least an action. The action is a URL. When the table action is a simple 
link, this is the URL that is used. When the table action is configured to open a modal, this URL is used to load the 
modal.

### Label

Like every action, a table action has a label. This is the text displayed on the button or link. The label is either 
a string or a Stringable object, making it easier to add things like icons.

### Styles

Just like tables, every type of action can have styles. You can add the styles by adding an array of `ButtonStyle` 
enums to the `styles` property in the constructor, or by using the `styles` method.

### As modal

Table actions can be configured to open a modal instead of a simple link. To do this, set the `asModal` property to 
`true` in the constructor or use the `asModal` method.

## Mass Actions

## Row Actions