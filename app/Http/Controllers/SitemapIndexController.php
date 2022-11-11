<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Services\Settings\ApplicationSettings;
use Carbon\Carbon;

class SitemapIndexController extends Controller
{
    private readonly int $courseStartMonth;

    private readonly int $currentYear;

    public function __construct(ApplicationSettings $settings)
    {
        $this->courseStartMonth = $settings->getCourseStartMonth();
        $now = Carbon::now();
        $this->currentYear = CourseController::calcYearRaw($now, $this->courseStartMonth);
    }

    public function index(): \Illuminate\Http\Response
    {
        $courses = Course::orderBy('updated_at', 'desc')->get();
        $now = Carbon::now();
        $lastUpdated = ($courses->count() > 0) ? $courses->first()->updated_at : $now;
        $lastHomePageUpdate = $lastUpdated->tz('UTC')->toAtomString();
        $controller = $this;

        return response()
                ->view('sitemap.index', compact('courses', 'lastHomePageUpdate', 'controller'))
                ->header('Content-Type', 'text/xml');
    }

    public function calculatePriority(Course $course): float
    {
        $courseYear = CourseController::calcYearRaw($course->last_date, $this->courseStartMonth);
        if ($this->currentYear === $courseYear) {
            return 0.90;
        } elseif ($this->currentYear - 1 === $courseYear) {
            return 0.80;
        } else {
            return 0.50;
        }
    }
}
