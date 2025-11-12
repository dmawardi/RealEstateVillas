<?php

namespace App\Http\Controllers;

use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class BaseController extends Controller
{
    public function home()
    {
        $all = Cache::get('properties:featured_premium');

        if (!$all) {
            $all = Property::where('is_featured', true)
            ->orWhere('is_premium', true)
            ->with([
                'attachments',
                'features',
                'pricing' => function ($query) {
                    $query->where('start_date', '<=', now())
                          ->where(function ($q) {
                              $q->where('end_date', '>=', now())
                                ->orWhereNull('end_date');
                          });
                },
            ])
            ->get();

            Cache::put('properties:featured_premium', $all, 60);
        }

        $featured = $all->where('is_featured', true)->values()->toArray();
        $premium  = $all->where('is_premium', true)->values()->toArray();

        return Inertia::render('Welcome', [
            'featured' => $featured,
            'premium' => $premium,
            'businessPhone' => config('app.business_phone'),
            'businessEmail' => config('app.business_email'),
        ]);
    }
}
