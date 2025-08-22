<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\HomeMember;
use App\Models\HomeSector;
use App\Models\HomeSlider;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\BookingIna;
use App\Models\HomeAds;
use App\Models\HomeDocumentation;
use App\Models\HomePartner;
use App\Models\HotelBooking;
use App\Models\MediaCorner;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $sliders = HomeSlider::whereIsActive(true)->get();
        $members = HomeMember::orderBy('order')->get();
        $sectors = HomeSector::orderBy('order')->get();
        $ads = HomeAds::whereIsActive(true)->orderBy('order')->get();
        $bookingIna = BookingIna::latest()->first();
        $hotelBookings = HotelBooking::whereIsActive(true)->inRandomOrder()->take(6)->get();
        $documentations = MediaCorner::whereIsActive(true)->orderBy('published_at', 'desc')->take(4)->get();
        $articles = Article::where('is_active', true)
            ->orderBy('published_at', 'desc')
            ->take(6)
            ->get();
        $partners = HomePartner::whereIsActive(true)->orderBy('order')->get();
        return $this->getSuccessResponse([
            'sliders' => $sliders,
            'members' => $members,
            'sectors' => $sectors,
            'ads' => $ads,
            'booking-ina' => $bookingIna,
            'hotel-booking' => $hotelBookings,
            'documentations' => $documentations,
            'articles' => $articles,
            'partners' => $partners
        ]);
    }
}
