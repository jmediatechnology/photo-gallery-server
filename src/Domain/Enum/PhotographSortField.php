<?php

namespace App\Domain\Enum;

enum PhotographSortField: string
{
    case CreatedAt = 'createdAt';
    case UpdatedAt = 'updatedAt';
    case Title = 'title';
}
