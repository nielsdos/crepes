<?php

namespace App\Services\Settings;

use App\Services\CacheUtility;
use App\Services\CourseDependentCache;

class ApplicationSettings
{
    public const DefaultStartMonth = 1;

    public const DefaultOverlapMonths = 0;

    public const DefaultShowMapOnCourseDetails = true;

    public function __construct(private readonly SettingsProvider $settingsProvider)
    {
    }

    private function invalidateViewSettingsDependentCache(): void
    {
        CacheUtility::flushTagIfMayCache(['view-dependent', CourseDependentCache::TAG]);
    }

    public function setCourseStartMonth(int $month): void
    {
        $this->settingsProvider->set('course_start_month', "{$month}");
        $this->invalidateViewSettingsDependentCache();
    }

    public function getCourseStartMonth(): int
    {
        return (int) $this->settingsProvider->get('course_start_month', self::DefaultStartMonth);
    }

    public function setCourseOverlapMonths(int $month): void
    {
        $this->settingsProvider->set('course_overlap_months', "{$month}");
        $this->invalidateViewSettingsDependentCache();
    }

    public function getCourseOverlapMonths(): int
    {
        return (int) $this->settingsProvider->get('course_overlap_months', self::DefaultOverlapMonths);
    }

    public function setPrivacyPolicy(string $html): void
    {
        $this->settingsProvider->set('privacy_policy', $html);
    }

    public function getPrivacyPolicy(): string
    {
        return $this->settingsProvider->get('privacy_policy', '');
    }

    public function getShowMapOnCourseDetails(): bool
    {
        return (bool) $this->settingsProvider->get('show_map_on_course_details', self::DefaultShowMapOnCourseDetails);
    }

    public function setShowMapOnCourseDetails(bool $show): void
    {
        $this->settingsProvider->set('show_map_on_course_details', $show ? '1' : '0');
    }

    public function getMainMetaDescription(): string
    {
        return $this->settingsProvider->get('main_meta_description', '');
    }

    public function setMainMetaDescription(string $description): void
    {
        $this->settingsProvider->set('main_meta_description', $description);
    }

    public function getAdminNotificationEmail(): string
    {
        return $this->settingsProvider->get('admin_notification_email', '');
    }

    public function setAdminNotificationEmail(string $email): void
    {
        $this->settingsProvider->set('admin_notification_email', $email);
    }
}
