<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index()
    {
        $students = Student::with(['school', 'class', 'user'])
            ->where('status', 'active')
            ->paginate(20);

        return response()->json($students);
    }

    public function show(Student $student)
    {
        $student->load(['school', 'class', 'user', 'grades.subject', 'grades.term']);
        return response()->json($student);
    }
}