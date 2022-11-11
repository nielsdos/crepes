<?php

namespace App\Http\Controllers;

use App\Services\Settings\ApplicationSettings;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(['auth', 'verified']);
        $this->middleware('can:create,App\Models\Setting');
    }

    public function edit(ApplicationSettings $settings): \Illuminate\View\View
    {
        $course_start_month = $settings->getCourseStartMonth();
        $course_overlap_months = $settings->getCourseOverlapMonths();
        $show_map_on_course_details = $settings->getShowMapOnCourseDetails();
        $privacy_policy_html = $settings->getPrivacyPolicy();
        $main_meta_description = $settings->getMainMetaDescription();

        return view('settings.edit', compact('course_start_month', 'course_overlap_months', 'show_map_on_course_details', 'privacy_policy_html', 'main_meta_description'));
    }

    public function updateView(Request $request, ApplicationSettings $settings): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'course_start_month' => 'required|integer|min:1|max:12',
            'course_overlap_months' => 'required|integer|min:0|max:11',
            'main_meta_description' => 'nullable|string',
        ]);

        $settings->setCourseStartMonth($validated['course_start_month']);
        $settings->setCourseOverlapMonths($validated['course_overlap_months']);
        $settings->setShowMapOnCourseDetails($request->has('show_map_on_course_details'));
        $settings->setMainMetaDescription($validated['main_meta_description'] ?? '');

        return redirect()->to(url()->previous().'#view')->with('success', __('acts.saved'));
    }

    public function updatePrivacyPolicy(Request $request, ApplicationSettings $settings): \Illuminate\Http\RedirectResponse
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
        $settings->setPrivacyPolicy($html);

        return redirect()->to(url()->previous().'#privacy-policy')->with('success', __('acts.saved'));
    }
}
