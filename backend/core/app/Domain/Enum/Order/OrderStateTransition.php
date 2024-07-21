<?php

namespace App\Domain\Enum\Order;

use ElityDEV\StateMachine\Domain\Enum\Transition;

enum OrderStateTransition: string implements Transition
{
    case CONFIRM = 'confirm';
    case COMPLETE = 'complete';
//  TODO: implement all transitions
//    case REJECT = 'reject';
//    case CANCEL = 'cancel';
//    case FAIL = 'fail';
//    case EXPIRE = 'expire';
}
