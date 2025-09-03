<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\HomePartner;
use Illuminate\Http\Request;

class PartnerController extends Controller
{
    public function index()
    {
        $partners = HomePartner::where('is_active', true)
            ->orderBy('order')
            ->get();

        return $this->getSuccessResponse($partners);
    }
}
