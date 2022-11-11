<?php

namespace App\Http\Controllers;

class CourseIndexController extends Controller
{
    public function index(): \Illuminate\Http\RedirectResponse
    {
        return redirect(url('/'));
    }
}
