<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveCourse;
use App\Models\Course;
use App\Models\Session;
use App\Models\SessionDescription;
use App\Models\SessionGroup;
use App\Notifications\CourseDestroyed;
use App\Notifications\CourseEdited;
use App\Services\AdminNotifier;
use App\Services\CourseDependentCache;
use App\Services\Exports\SubscribersExportable;
use App\Services\Settings\ApplicationSettings;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CourseController extends Controller
{
    use ExportResponseCreator;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(private readonly ApplicationSettings $settings)
    {
        $this->middleware(['auth', 'verified'])->except(['index', 'show']);
        $this->authorizeResource(Course::class, 'course', ['except' => ['index', 'show']]);
    }

    /**
     * Calculates the course year of a date.
     *
     * @param  Carbon  $c
     * @param  int  $start
     * @return int
     */
    public static function calcYearRaw(Carbon $c, int $start): int
    {
        $year = $c->year - 1;
        if ($c->month >= $start) {
            $year++;
        }

        return $year;
    }

    /**
     * Get the visual representation of the provided year (range).
     *
     * @param  int  $year
     * @return string
     */
    private function getYearDisplay(int $year): string
    {
        if ($this->settings->getCourseStartMonth() === 1) {
            return "{$year}";
        } else {
            return "{$year} - ".($year + 1);
        }
    }

    /**
     * Calculates the course year of a date.
     *
     * @param  Carbon  $c
     * @return int
     */
    private function calcYear(Carbon $c): int
    {
        return self::calcYearRaw($c, $this->settings->getCourseStartMonth());
    }

    /**
     * Internal export function.
     *
     * @param  Course  $course
     * @param  ExportFileType  $exportFileType
     * @return RedirectResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    private function exportInternal(Course $course, ExportFileType $exportFileType): \Symfony\Component\HttpFoundation\BinaryFileResponse|RedirectResponse
    {
        $this->authorize('update', $course);
        $response = $this->createExportResponse(new SubscribersExportable($course, Auth::user()->isAdmin()), 'subscribers', $exportFileType);

        return $response ?? redirect(route('course.index'))->with('fail', __('acts.export_failed'));
    }

    /**
     * Exports an excel file of the users.
     *
     * @param  Course  $course
     * @return RedirectResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function exportExcel(Course $course): RedirectResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        return $this->exportInternal($course, ExportFileType::Excel);
    }

    /**
     * Exports a CSV file of the users.
     *
     * @param  Course  $course
     * @return RedirectResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function exportCSV(Course $course): RedirectResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        return $this->exportInternal($course, ExportFileType::CSV);
    }

    /**
     * Display a listing of the resource.
     *
     * @param  Request  $request
     * @return RedirectResponse|\Illuminate\View\View
     */
    public function index(Request $request): RedirectResponse|\Illuminate\View\View
    {
        $yearStartMonth = $this->settings->getCourseStartMonth();
        $yearOverlapMonths = $this->settings->getCourseOverlapMonths();

        $start = CourseDependentCache::rememberTaggedForeverIfMayCache('db.course_min_year', function () use ($yearStartMonth) {
            return self::calcYearRaw(new Carbon(Session::min('start')), $yearStartMonth);
        });
        $now = Carbon::now();
        $currentYear = self::calcYearRaw($now, $yearStartMonth);

        // Validate year
        $year = (int) $request->query('y', "{$currentYear}");
        if ($year < $start || $year > 9999) {
            return redirect(route('course.index'));
        }

        $yearDisplay = $this->getYearDisplay($year);
        $years = array_map(fn ($year) => [$year, $this->getYearDisplay($year)], range($currentYear, $start, -1));

        $subTimeInfo = DB::table('session_groups')
            ->selectRaw('course_id, MIN(start) min_start, MAX(TIMESTAMP(CAST(start AS DATE), end)) last_session_date')
            ->join('sessions', 'session_groups.id', '=', 'session_group_id')
            ->groupBy('course_id');

        $startDateWithoutOverlapCalculation = new Carbon("{$year}-{$yearStartMonth}-01");
        $startDate = (clone $startDateWithoutOverlapCalculation)->subMonths($yearOverlapMonths);
        $endDate = $startDateWithoutOverlapCalculation->addYear()->subDay(); // Okay to reuse original object

        $courses = Course::selectRaw('courses.*, a.last_session_date')
                            ->joinSub($subTimeInfo, 'a', function ($join) {
                                $join->on('courses.id', '=', 'course_id');
                            })
                            ->with(['owner' => function ($query) {
                                $query->select('id', 'firstname', 'lastname');
                            }, 'sessionGroups', 'sessions', 'subscriptions' => function ($query) {
                                $query->where('user_id', Auth::id());
                            }])
                            ->whereBetween('last_session_date', [$startDate, $endDate])
                            ->orderBy('min_start', 'desc')
                            ->get();

        $pastCourses = $courses->where('last_session_date', '<', $now);
        $futureCourses = $courses->where('last_session_date', '>=', $now);
        $metaTagDescription = $this->settings->getMainMetaDescription();

        return view('course.index', compact('pastCourses', 'futureCourses', 'years', 'year', 'yearDisplay', 'metaTagDescription'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create(): \Illuminate\View\View
    {
        return view('course.create');
    }

    /**
     * Creates a redirect to the given course.
     *
     * @param  Course  $course
     * @return RedirectResponse
     */
    private static function createCourseRedirect(Course $course): RedirectResponse
    {
        return redirect(route('course.show', [$course, $course->slug]));
    }

    /**
     * Sanitizes multiline input
     *
     * @param  string  $str
     * @return string
     */
    private static function sanitizeMultiline(string $str): string
    {
        return preg_replace("/[\t ]*\r?\n[\t ]*/", "\n", $str);
    }

    /**
     * Creates a slug from a course name.
     *
     * @param  string  $name
     * @return string
     */
    private static function sluggify(string $name): string
    {
        return Str::limit(Str::slug($name), 80, '');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  SaveCourse  $request
     * @return RedirectResponse
     *
     * @throws \Throwable
     */
    public function store(SaveCourse $request): RedirectResponse
    {
        $validated = $request->validated();

        return DB::transaction(function () use ($request, $validated) {
            // 1. Create course itself
            $course = Course::create([
                'title' => $validated['course_name'],
                'slug' => self::sluggify($validated['course_name']),
                'description' => self::sanitizeMultiline($validated['description']),
                'last_date' => $validated['last_date'],
                'owner_id' => $request->user()->id,
                'notify_me' => $request->has('notify_me'),
            ]);

            // 2. Session descriptions
            $descriptions = array_map(function ($desc) {
                return SessionDescription::create(['description' => self::sanitizeMultiline($desc)])->id;
            }, $validated['desc']);

            // 3. Session groups
            $groups = array_map(function ($max) use ($course) {
                return SessionGroup::create(['course_id' => $course->id, 'max_ppl' => $max])->id;
            }, $validated['group_max_ppl']);

            // 4. Sessions
            $sessionCount = (int) $validated['session_count'];
            $groupCount = (int) $validated['times'];
            for ($g = 0; $g < $groupCount; $g++) {
                for ($s = 0; $s < $sessionCount; $s++) {
                    Session::create([
                        'session_group_id' => $groups[$g],
                        'session_description_id' => $descriptions[$s],
                        'location' => $validated['session_location'][$g][$s],
                        'start' => $validated['session_date'][$g][$s].' '.$validated['session_starttime'][$g][$s].':00',
                        'end' => $validated['session_endtime'][$g][$s],
                    ]);
                }
            }

            return self::createCourseRedirect($course);
        });
    }

    public static function couldBeAValidStreetAddress(string $addressText): bool
    {
        $separator = '[\s,-]+';
        $streetAndNumber = '[\.\w\s,-]+'; // NOTE: People sometimes use separators in their street names
        $regex = '/^'.$streetAndNumber.'(\d{3,}'.$separator.'\w+|\w+'.$separator.'\d{3,})$/';
        // dd($regex);
        return preg_match($regex, $addressText) === 1;
    }

    /**
     * Display the specified resource.
     *
     * @param  Course  $course
     * @param  string  $slug
     * @return RedirectResponse|\Illuminate\View\View
     */
    public function show(Course $course, string $slug = ''): \Illuminate\View\View|RedirectResponse
    {
        if ($course->slug !== $slug) {
            return self::createCourseRedirect($course);
        }

        $subscription = null;
        if (Auth::check()) {
            $subscription = Auth::user()->subscriptionFor($course->id);
        }

        $year = self::calcYear($course->last_date);
        $yearDisplay = $this->getYearDisplay($year);
        $showMapOnCourseDetails = $this->settings->getShowMapOnCourseDetails();

        $metaTagDescription = Str::limit(
            str_replace("\n", ' ', strstr($course->description, "\n\n", true) ?: $course->description), 290
        );

        $course->sessionGroups->load('sessions.sessionDescription');
        if (Auth::check()) {
            $course->sessionGroups->load('subscriptions');
        }

        return view('course.show', compact('course', 'subscription', 'year', 'yearDisplay', 'metaTagDescription', 'showMapOnCourseDetails'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Course  $course
     * @return \Illuminate\View\View
     */
    public function edit(Course $course): \Illuminate\View\View
    {
        $course->load('sessionGroups.sessions.sessionDescription');
        $year = self::calcYear($course->last_date);
        $yearDisplay = $this->getYearDisplay($year);
        $sessionGroupCount = $course->sessionGroups()->count();
        $sessionCount = $course->sessionGroups[0]->sessions()->count();

        return view('course.edit', compact('course', 'year', 'yearDisplay', 'sessionGroupCount', 'sessionCount'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  SaveCourse  $request
     * @param  Course  $course
     * @param  AdminNotifier  $adminNotifier
     * @return RedirectResponse
     *
     * @throws \Throwable
     */
    public function update(SaveCourse $request, Course $course, AdminNotifier $adminNotifier): RedirectResponse
    {
        $validated = $request->validated();

        // User could do something sneaky like change the amounts
        // That is a problem because the user may pass invalid data because of the validation relying on these values
        if ($validated['times'] != $course->sessionGroups()->count()
            || $validated['session_count'] != $course->sessionGroups[0]->sessions()->count()) {
            return back();
        }

        DB::transaction(function () use ($validated, $request, &$course) {
            // 1. Update course itself
            $course->title = $validated['course_name'];
            $course->slug = self::sluggify($validated['course_name']);
            $course->description = self::sanitizeMultiline($validated['description']);
            $course->last_date = $validated['last_date'];
            $course->notify_me = $request->has('notify_me');
            $course->touch();
            $course->save();

            // 2. Session descriptions
            foreach ($course->sessionGroups[0]->sessions as $i => $session) {
                $desc = &$session->sessionDescription;
                $desc->description = self::sanitizeMultiline($validated['desc'][$i]);
                $desc->save();
            }

            // 3. Sessions
            foreach ($course->sessionGroups as $g => $sg) {
                $sg->max_ppl = $validated['group_max_ppl'][$g];
                $sg->save();

                foreach ($sg->sessions as $s => $session) {
                    $session->location = $validated['session_location'][$g][$s];
                    $session->start = $validated['session_date'][$g][$s].' '.$validated['session_starttime'][$g][$s].':00';
                    $session->end = $validated['session_endtime'][$g][$s];
                    $session->save();
                }
            }
        });

        $adminNotifier->notify(new CourseEdited($request->user(), $course));

        if ($request->input('save')) {
            return self::createCourseRedirect($course)->with('success', __('acts.saved'));
        } else {
            return back()->with('success', __('acts.saved'));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Request  $request
     * @param  Course  $course
     * @param  AdminNotifier  $adminNotifier
     * @return RedirectResponse
     *
     * @throws \Throwable
     */
    public function destroy(Request $request, Course $course, AdminNotifier $adminNotifier): RedirectResponse
    {
        DB::transaction(function () use ($course) {
            // Can't cascade descriptions...
            $tmp = [];
            foreach ($course->sessionGroups as $sg) {
                foreach ($sg->sessions as $session) {
                    $tmp[] = $session->sessionDescription;
                }
            }

            $course->forceDelete();
            foreach ($tmp as $desc) {
                $desc->forceDelete();
            }
        });

        $adminNotifier->notify(new CourseDestroyed($request->user(), $course->title));

        return redirect(route('course.index'))->with('success', __('acts.course_deleted', ['course' => $course->title]));
    }
}
