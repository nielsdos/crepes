<?php

namespace App\Http\Controllers;

use App\Services\Settings\ApplicationSettings;

class PrivacyPolicyController extends Controller
{
    /**
     * Display a listing of the privacy policy.
     *
     * @param  ApplicationSettings  $settings
     * @return \Illuminate\View\View
     */
    public function index(ApplicationSettings $settings): \Illuminate\View\View
    {
        return view('privacy.index', ['privacy_policy_html' => $settings->getPrivacyPolicy()]);
    }
}
