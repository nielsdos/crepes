<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as Base;

class VerifyEmail extends Base
{
    use AddGreetingTrait;
}
