<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Property;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
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

    /**
     * Dashboard with comprehensive analytics and monitoring
     */
    public function dashboard()
    {
        // Cache key for dashboard data
        $cacheKey = 'dashboard_data_' . now()->format('Y-m-d-H');
        $cacheDuration = 60 * 60; // 1 hour

        $dashboardData = Cache::remember($cacheKey, $cacheDuration, function () {
                // Get dashboard statistics
                $stats = $this->getDashboardStats();
                
                // Get top properties by view count
                $topProperties = $this->getTopProperties();
                
                // Get recent bookings for dashboard
                $recentBookings = $this->getRecentBookings();
                return [
                    'stats' => $stats,
                    'topProperties' => $topProperties,
                    'recentBookings' => $recentBookings
                ];
        });
        return Inertia::render('Dashboard', $dashboardData);
    }

    /**
     * Get comprehensive dashboard statistics
     */
    private function getDashboardStats(): array
    {
        $now = Carbon::now();
        $sixMonthsFromNow = $now->copy()->addMonths(6);
        $currentMonth = $now->format('Y-m');

        // Properties needing pricing attention (within 6 months or no future pricing)
        $propertiesNeedingPricing = Property::with(['pricing' => function ($query) use ($sixMonthsFromNow) {
                $query->where('end_date', '<=', $sixMonthsFromNow)
                      ->orWhereNull('end_date');
            }])
            ->whereDoesntHave('pricing', function ($query) use ($sixMonthsFromNow) {
                $query->where('end_date', '>', $sixMonthsFromNow)
                      ->orWhereNull('end_date');
            })
            ->orWhereHas('pricing', function ($query) use ($sixMonthsFromNow) {
                $query->where('end_date', '<=', $sixMonthsFromNow)
                      ->whereNotExists(function ($subQuery) use ($sixMonthsFromNow) {
                          $subQuery->select(DB::raw(1))
                                   ->from('property_pricing as pp2')
                                   ->whereColumn('pp2.property_id', 'property_pricing.property_id')
                                   ->where('pp2.end_date', '>', $sixMonthsFromNow)
                                   ->orWhere('pp2.end_date', null);
                      });
            })
            ->select('id', 'title', 'property_id', 'slug')
            ->get();

        // Active bookings (confirmed and currently staying)
        $activeBookings = Booking::where('status', 'confirmed')
            ->where('check_in_date', '<=', $now->toDateString())
            ->where('check_out_date', '>', $now->toDateString())
            ->count();

        // Pending bookings requiring attention
        $pendingBookings = Booking::where('status', 'pending')->count();

        // Monthly revenue (completed bookings in current month)
        $monthlyRevenue = Booking::where('status', 'completed')
            ->whereYear('check_out_date', $now->year)
            ->whereMonth('check_out_date', $now->month)
            ->sum('total_price');

        // Total active properties
        $totalProperties = Property::where('status', 'active')->count();

        return [
            'total_properties' => $totalProperties,
            'active_bookings' => $activeBookings,
            'pending_bookings' => $pendingBookings,
            'monthly_revenue' => $monthlyRevenue,
            'properties_needing_pricing' => $propertiesNeedingPricing->toArray()
        ];
    }

    /**
     * Get top performing properties by views and other metrics
     */
    private function getTopProperties(): array
    {
        return Property::where('status', 'active')
            ->select([
                'id', 'title', 'property_id', 'view_count', 'district', 'regency',
                'listing_type', 'price', 'bedrooms', 'bathrooms', 'property_type'
            ])
            ->orderByDesc('view_count')
            ->limit(15)
            ->get()
            ->toArray();
    }

    /**
     * Get recent bookings with property information for dashboard
     */
    private function getRecentBookings(): array
    {
        return Booking::with(['property:id,title,property_id'])
            ->select([
                'id', 'property_id', 'first_name', 'last_name', 'email',
                'check_in_date', 'check_out_date', 'status', 'source',
                'total_price', 'number_of_guests', 'created_at'
            ])
            ->orderByDesc('created_at')
            ->limit(50) // Get more for filtering
            ->get()
            ->toArray();
    }

    /**
     * Get properties with pricing alerts for monitoring
     */
    public function getPricingAlerts(): array
    {
        $sixMonthsFromNow = Carbon::now()->addMonths(6);
        
        // Properties with no pricing beyond 6 months
        $noPricing = Property::whereDoesntHave('pricing', function ($query) use ($sixMonthsFromNow) {
                $query->where('end_date', '>', $sixMonthsFromNow)
                      ->orWhereNull('end_date');
            })
            ->with('pricing')
            ->get();

        // Properties with pricing ending soon
        $endingSoon = Property::whereHas('pricing', function ($query) use ($sixMonthsFromNow) {
                $query->where('end_date', '<=', $sixMonthsFromNow)
                      ->where('end_date', '>', Carbon::now());
            })
            ->with(['pricing' => function ($query) use ($sixMonthsFromNow) {
                $query->where('end_date', '<=', $sixMonthsFromNow)
                      ->where('end_date', '>', Carbon::now())
                      ->orderBy('end_date');
            }])
            ->get();

        return [
            'no_pricing' => $noPricing->toArray(),
            'ending_soon' => $endingSoon->toArray()
        ];
    }

    /**
     * Get booking analytics for admin dashboard
     */
    public function getBookingAnalytics(): array
    {
        $now = Carbon::now();
        $thirtyDaysAgo = $now->copy()->subDays(30);

        return [
            'bookings_by_status' => Booking::select('status', DB::raw('count(*) as count'))
                ->where('created_at', '>=', $thirtyDaysAgo)
                ->groupBy('status')
                ->get()
                ->toArray(),
            
            'bookings_by_source' => Booking::select('source', DB::raw('count(*) as count'))
                ->where('created_at', '>=', $thirtyDaysAgo)
                ->groupBy('source')
                ->get()
                ->toArray(),
                
            'daily_bookings' => Booking::select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('count(*) as count')
                )
                ->where('created_at', '>=', $thirtyDaysAgo)
                ->groupBy(DB::raw('DATE(created_at)'))
                ->orderBy('date')
                ->get()
                ->toArray(),
                
            'revenue_by_month' => Booking::select(
                    DB::raw('YEAR(check_out_date) as year'),
                    DB::raw('MONTH(check_out_date) as month'),
                    DB::raw('SUM(total_price) as revenue')
                )
                ->where('status', 'completed')
                ->where('check_out_date', '>=', $now->copy()->subMonths(12))
                ->groupBy('year', 'month')
                ->orderBy('year', 'month')
                ->get()
                ->toArray()
        ];
    }
}
