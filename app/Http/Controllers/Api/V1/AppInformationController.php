<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ApplicationSetting;
use Illuminate\Http\Request;

class AppInformationController extends Controller
{
    public function index()
    {
        $appSetting = ApplicationSetting::latest()->first();
        return $this->getSuccessResponse($appSetting);
    }
}
