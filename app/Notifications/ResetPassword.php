<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as Base;

class ResetPassword extends Base
{
    use AddGreetingTrait;
}
