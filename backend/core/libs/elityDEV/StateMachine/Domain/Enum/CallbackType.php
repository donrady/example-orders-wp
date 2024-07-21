<?php

namespace ElityDEV\StateMachine\Domain\Enum;

enum CallbackType: string
{
    case AFTER = 'after';
    case BEFORE = 'before';
    case GUARD = 'guard';
}
