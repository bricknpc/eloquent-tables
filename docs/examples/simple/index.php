<?php

namespace App\Tables;

use App\Models\User;
use BrickNPC\EloquentTables\Table;
use BrickNPC\EloquentTables\Column;
use Illuminate\Contracts\Database\Query\Builder;

class UserTable extends Table
{
    public function query(): Builder
    {
        return User::query();
    }

    /**
     * @return Column[]
     */
    public function columns(): array
    {
        return [
            new Column('name')
                ->sortable()
                ->searchable(),
            new Column('email')
                ->sortable()
                ->searchable(),
            new Column('email_verified')
                ->label('Has verified email')
                ->valueUsing(fn (User $model) => $model->email_verified_at !== null)
                ->boolean(),
            new Column('email_verified_at')
                ->label('Verified email At')
                ->dateTime(),
        ];
    }
}