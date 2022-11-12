<?php

namespace App\Services;

use App\Services\Settings\ApplicationSettings;
use Illuminate\Notifications\Notification;

final class AdminNotifier
{
    public function __construct(private readonly ApplicationSettings $settings)
    {
    }

    public function notify(Notification $notification): void
    {
        $to = $this->settings->getAdminNotificationEmail();
        if (! empty($to)) {
            \Notification::route('mail', $to)->notify($notification);
        }
    }
}
