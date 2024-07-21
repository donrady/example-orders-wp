<?php
declare(strict_types=1);

namespace App\Entity\Columns;

interface ColumnsGroups
{
    public const string ALL = 'attributes:all';
    public const string ID = 'attributes:identified';
    public const string TIMESTAMPS = 'attributes:timestamps';
    public const string CREATED_AT = 'attributes:created_at';
    public const string UPDATED_AT = 'attributes:updated_at';
}

