<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Position;
use Illuminate\Http\Request;
use App\Models\AboutUsHistory;
use App\Models\HonoraryCouncil;
use App\Models\AboutUsInformation;
use App\Models\DirectionCommitment;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrgNodeResource;
use App\Models\RegionalCoordinator;

class AboutUsController extends Controller
{

    public function index(Request $request)
    {
        $aboutUsInformation  = AboutUsInformation::latest()->first();
        $aboutUsHistory      = AboutUsHistory::latest()->first();
        $directionCommitment = DirectionCommitment::orderBy('order', 'desc')->get();
        $honoraryCouncil     = HonoraryCouncil::orderBy('order', 'desc')->get();
        $regionalCoordinators = RegionalCoordinator::orderBy('order', 'desc')->get();

        $withMember = true; // FE butuh nama orang
        $query = Position::roots()
            ->orderBy('order')
            ->with([
                'childrenRecursive' => fn($q) => $q->orderBy('order'),
                'member'
            ]);

        $tree = $query->get();

        return $this->getSuccessResponse([
            'information'          => $aboutUsInformation,
            'history'              => $aboutUsHistory,
            'direction_commitments' => $directionCommitment,
            'honorary_councils'     => $honoraryCouncil,
            'organizations'         => OrgNodeResource::collection($tree),
            'regional_coordinators' => $regionalCoordinators,
        ]);
    }
}
