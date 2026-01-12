<?php

namespace App\Services;

use App\Models\Attendance;

class AtendanceService
{
    private Attendance $model;

    public function __construct()
    {
        $this->model = new Attendance(); // Initialize the Attendance model
    }

    // Additional service methods for Attendance can be added here
} 