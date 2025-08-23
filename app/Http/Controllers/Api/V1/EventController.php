<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Event;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;

class EventController extends Controller
{

    public function index(Request $request)
    {
        $today = Carbon::today();

        $sliderEvents = Event::where('is_active', true)
            ->where('is_approved', true)
            ->whereNotNull('start_date')
            ->where('start_date', '>=', $today) // pakai Carbon instance
            ->orderBy('start_date', 'asc')
            ->take(5)
            ->get();

        $upcomingEvents = Event::where('is_active', true)
            ->where('is_approved', true)
            ->whereNotNull('start_date')
            ->where('start_date', '>=', $today)
            ->orderBy('start_date', 'asc')
            ->take(10)
            ->get();

        $allEvents = Event::where('is_active', true)
            ->where('is_approved', true)
            ->whereNotNull('start_date')
            // Search digrup supaya OR tidak "membatalkan" filter lain
            ->when($request->filled('search'), function ($query) use ($request) {
                $term = '%' . $request->search . '%';
                $query->where(function ($q) use ($term) {
                    $q->where('name', 'like', $term)
                        ->orWhere('location', 'like', $term);
                });
            })
            // Rentang tanggal (pakai whereBetween agar jelas)
            ->when($request->filled('start_date') && $request->filled('end_date'), function ($query) use ($request) {
                $start = Carbon::parse($request->start_date)->startOfDay();
                $end   = Carbon::parse($request->end_date)->endOfDay();
                $query->whereBetween('start_date', [$start, $end]);
            })
            // Tipe event: UPCOMING vs PAST
            ->when($request->filled('type'), function ($query) use ($request, $today) {
                if ($request->type === Event::TYPE_UPCOMING) {
                    $query->where('start_date', '>=', $today);
                } elseif ($request->type === Event::TYPE_PAST) {
                    $query->where('start_date', '<', $today);
                }
            })
            ->orderBy('start_date', 'desc')
            ->get();

        return $this->getSuccessResponse([
            'slider_events'   => $sliderEvents,
            'upcoming_events' => $upcomingEvents,
            'all_events'      => $allEvents,
        ]);
    }

    public function show($id)
    {
        $event = Event::findOrFail($id);
        return $this->getSuccessResponse($event);
    }
}
