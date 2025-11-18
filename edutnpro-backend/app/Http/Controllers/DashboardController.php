<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Teacher;
use App\Models\School;
use App\Models\Grade;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_students' => Student::count(),
            'total_teachers' => Teacher::count(),
            'total_schools' => School::count(),
            'total_grades' => Grade::count(),
        ];

        $recent_students = Student::with('class')->latest()->take(5)->get();
        $recent_grades = Grade::with(['student', 'subject'])->latest()->take(5)->get();

        return view('dashboard', compact('stats', 'recent_students', 'recent_grades'));
    }
}