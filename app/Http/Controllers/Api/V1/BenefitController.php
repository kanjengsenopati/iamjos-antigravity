<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Benefit;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BenefitController extends Controller
{
    public function index()
    {
        $benefits = Benefit::orderBy('order')->get();
        return $this->getSuccessResponse($benefits);
    }
}
