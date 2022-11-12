<?php

namespace App\Http\Controllers;

use App\Services\Settings\ApplicationSettings;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SettingsController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(private readonly ApplicationSettings $settings)
    {
        $this->middleware(['auth', 'verified']);
        $this->middleware('can:create,App\Models\Setting');
    }

    public function edit(): \Illuminate\View\View
    {
        $course_start_month = $this->settings->getCourseStartMonth();
        $course_overlap_months = $this->settings->getCourseOverlapMonths();
        $show_map_on_course_details = $this->settings->getShowMapOnCourseDetails();
        $privacy_policy_html = $this->settings->getPrivacyPolicy();
        $main_meta_description = $this->settings->getMainMetaDescription();
        $admin_notification_email = $this->settings->getAdminNotificationEmail();

        return view('settings.edit', compact('course_start_month', 'course_overlap_months', 'show_map_on_course_details', 'privacy_policy_html', 'main_meta_description', 'admin_notification_email'));
    }

    public function updateView(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'course_start_month' => 'required|integer|min:1|max:12',
            'course_overlap_months' => 'required|integer|min:0|max:11',
            'main_meta_description' => 'nullable|string',
        ]);

        $this->settings->setCourseStartMonth($validated['course_start_month']);
        $this->settings->setCourseOverlapMonths($validated['course_overlap_months']);
        $this->settings->setShowMapOnCourseDetails($request->has('show_map_on_course_details'));
        $this->settings->setMainMetaDescription($validated['main_meta_description'] ?? '');

        return redirect()->to(url()->previous().'#view')->with('success', __('acts.saved'));
    }

    public function updatePrivacyPolicy(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'privacy_policy_html' => 'string|nullable',
        ]);

        $htmlPurifierConfig = \HTMLPurifier_Config::createDefault();
        $htmlPurifierConfig->set('HTML.Doctype', 'HTML 4.01 Transitional');
        $htmlPurifierConfig->set('HTML.Allowed', 'a[href|title|rel|target],br,p,i,u,em,strong,h1,h2,h3,h4,h5,h6,ul,ol,li');
        $htmlPurifierConfig->set('Attr.AllowedClasses', []);
        $htmlPurifierConfig->set('Attr.AllowedFrameTargets', ['_blank']);
        $htmlPurifierConfig->set('Attr.AllowedRel', ['noopener', 'nofollow', 'noreferrer']);
        $htmlPurifierConfig->set('HTML.TargetBlank', true);
        $htmlPurifierConfig->set('HTML.TargetNoopener', true);
        $htmlPurifierConfig->set('HTML.TargetNoreferrer', true);
        $htmlPurifierConfig->set('Output.FixInnerHTML', false);
        $htmlPurifier = new \HTMLPurifier($htmlPurifierConfig);
        $html = $htmlPurifier->purify($validated['privacy_policy_html'] ?? '');
        $this->settings->setPrivacyPolicy($html);

        return redirect()->to(url()->previous().'#privacy-policy')->with('success', __('acts.saved'));
    }

    /**
     * @throws ValidationException
     */
    public function updateOptions(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = validateRequestWithHashFailureRedirect($request, [
            'admin_notification_email' => 'nullable|email',
        ], 'options');

        $this->settings->setAdminNotificationEmail($validated['admin_notification_email'] ?? '');

        return redirect()->to(url()->previous().'#options')->with('success', __('acts.saved'));
    }
}
