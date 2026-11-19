<?php

namespace App\Models;

use App\Core\Model;

class Semester extends Model
{
    protected string $table = 'semesters';

    public function allOrdered(): array
    {
        return $this->all('id ASC');
    }
}
