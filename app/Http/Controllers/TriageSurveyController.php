<?php

namespace App\Http\Controllers;

class TriageSurveyController extends Controller
{
    public function index()
    {
        return view('admin.reports.manage-triage-survey');
    }
}
