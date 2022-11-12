<?php

namespace Tests\Unit;

use App\Services\Settings\ApplicationSettings;
use App\Services\Settings\SettingsProvider;
use Tests\TestCase;

class FakeSettingsProvider implements SettingsProvider
{
    private array $store = [];

    public function get(string $key, int|string|bool $default): string|int|bool
    {
        return $this->store[$key] ?? $default;
    }

    public function set(string $key, string $value): void
    {
        $this->store[$key] = $value;
    }
}

class ApplicationSettingsTest extends TestCase
{
    public function testDefaults(): void
    {
        $applicationSettings = new ApplicationSettings(new FakeSettingsProvider);
        $this->assertEquals(1, $applicationSettings->getCourseStartMonth());
        $this->assertEquals(0, $applicationSettings->getCourseOverlapMonths());
        $this->assertEquals('', $applicationSettings->getPrivacyPolicy());
        $this->assertEquals(true, $applicationSettings->getShowMapOnCourseDetails());
        $this->assertEquals('', $applicationSettings->getMainMetaDescription());
        $this->assertEquals('', $applicationSettings->getAdminNotificationEmail());
    }

    public function testSet(): void
    {
        $applicationSettings = new ApplicationSettings(new FakeSettingsProvider);
        $applicationSettings->setCourseStartMonth(9);
        $applicationSettings->setCourseOverlapMonths(2);
        $applicationSettings->setPrivacyPolicy('<b>hi</b>');
        $applicationSettings->setShowMapOnCourseDetails(false);
        $applicationSettings->setMainMetaDescription('<script>hello</script>');
        $applicationSettings->setAdminNotificationEmail('test@example.com');
        $this->assertEquals(9, $applicationSettings->getCourseStartMonth());
        $this->assertEquals(2, $applicationSettings->getCourseOverlapMonths());
        $this->assertEquals('<b>hi</b>', $applicationSettings->getPrivacyPolicy());
        $this->assertEquals(false, $applicationSettings->getShowMapOnCourseDetails());
        $this->assertEquals('<script>hello</script>', $applicationSettings->getMainMetaDescription());
        $this->assertEquals('test@example.com', $applicationSettings->getAdminNotificationEmail());
        $applicationSettings->setCourseStartMonth(5);
        $applicationSettings->setCourseOverlapMonths(1);
        $applicationSettings->setPrivacyPolicy('<b>hi2</b>');
        $applicationSettings->setShowMapOnCourseDetails(true);
        $applicationSettings->setMainMetaDescription('123');
        $applicationSettings->setAdminNotificationEmail('other@example.com');
        $this->assertEquals(5, $applicationSettings->getCourseStartMonth());
        $this->assertEquals(1, $applicationSettings->getCourseOverlapMonths());
        $this->assertEquals('<b>hi2</b>', $applicationSettings->getPrivacyPolicy());
        $this->assertEquals('123', $applicationSettings->getShowMapOnCourseDetails());
        $this->assertEquals('other@example.com', $applicationSettings->getAdminNotificationEmail());
    }
}
