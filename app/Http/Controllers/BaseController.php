<?php

namespace App\Http\Controllers;

use App\Mail\ContactEnquiryMail;
use App\Models\Booking;
use App\Models\Property;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
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

        $featured = $all->where('is_featured', true)->values();
        $premium  = $all->where('is_premium', true)->values();

        // Dynamic SEO based on current data
        $seoData = [
            'title' => $this->generateHomeTitle($featured->count()),
            'description' => $this->generateHomeDescription($featured, $premium),
            'keywords' => $this->generateHomeKeywords(),
            'canonicalUrl' => url('/'),
            'ogImage' => $this->selectBestOgImage($featured),
        ];

        return Inertia::render('Welcome', [
            'featured' => $featured->toArray(),
            'premium' => $premium->toArray(),
            'businessPhone' => config('app.business_phone'),
            'businessEmail' => config('app.business_email'),
            'seoData' => $seoData,
        ]);
    }

    private function generateHomeTitle(int $featuredCount): string
    {
        $base = 'Bali Villa Rentals & Land Sales';
        if ($featuredCount > 0) {
            return "{$base} | {$featuredCount}+ Premium Properties Available";
        }
        return "{$base} | Villas & Investment Land in Bali";
    }

    private function generateHomeDescription($featured, $premium): string
    {
        $locations = $featured->pluck('district')->unique()->take(3)->implode(', ');
        $totalCount = $featured->count() + $premium->count();
        
        $villaCount = $featured->where('property_type', 'villa')->count() + $premium->where('property_type', 'villa')->count();
        $landCount = $featured->where('property_type', 'land')->count() + $premium->where('property_type', 'land')->count();
        
        $description = "Discover {$totalCount}+ handpicked properties in {$locations}. ";
        
        if ($villaCount > 0) {
            $description .= "Rent luxury villas from our trusted partners. ";
        }
        
        if ($landCount > 0) {
            $description .= "Buy premium land for investment. ";
        }
        
        $description .= "Commission-based service with expert local knowledge and full support.";
        
        return $description;
    }

    private function generateHomeKeywords(): string
    {
        return 'bali villa rental, bali land for sale, villa rental commission, ' .
            'bali property investment, canggu villa rental, seminyak villa, ' .
            'ubud land sale, bali property partner, villa booking bali, ' .
            'land investment bali, bali real estate agent';
    }

    private function selectBestOgImage($featured): string
    {
        // Look for the best villa image first (most attractive for social sharing)
        $bestProperty = $featured->where('property_type', 'villa')
                                ->sortByDesc('view_count')
                                ->first();
        
        // If no villas, try any property with good images
        if (!$bestProperty) {
            $bestProperty = $featured->sortByDesc('view_count')->first();
        }
        
        if ($bestProperty && $bestProperty->attachments) {
            // Find the first image type attachment
            $imageAttachment = collect($bestProperty->attachments)
                ->firstWhere('type', 'image');
                
            if ($imageAttachment) {
                return asset($imageAttachment->path);
            }
            // Fallback to first attachment if no specific image type
            $firstAttachment = collect($bestProperty->attachments)->first();
            if ($firstAttachment) {
                return asset($firstAttachment->file_path);
            }
        }
        return asset('images/og-homepage.jpg');
    }

    /**
     * Dashboard with comprehensive analytics and monitoring
     */
    public function dashboard()
    {
        // Cache key for dashboard data
        $cacheKey = 'dashboard_data_' . now()->format('Y-m-d-H');
        $cacheDuration = 60 * 60; // 1 hour

        if (auth()->user()->role === 'admin') {
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
            return Inertia::render('Dashboard', [...$dashboardData, 
                'seoData' => [
                    'title' => 'Admin Dashboard',
                    'description' => 'Comprehensive overview of property and booking analytics.',
                    'keywords' => 'admin dashboard, property analytics, booking statistics',
                    'canonicalUrl' => url('/dashboard'),
                    'ogImage' => asset('images/logo/Logo.png'),
                ],
            ]);
        }
        // Regular user dashboard
        return Inertia::render('Dashboard', [
            'seoData' => [
                'title' => 'User Dashboard',
                'description' => 'Overview of your account and activities.',
                'keywords' => 'user dashboard, account overview',
                'canonicalUrl' => url('/dashboard'),
                'ogImage' => asset('images/logo/Logo.png'),
            ],
        ]);
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

    public function contact()
    {
        return Inertia::render('Contact', [
            'seoData' => [
                'title' => 'Contact Us - Bali Villa Spot',
                'description' => 'Get in touch with Bali Villa Spot for inquiries, support, or assistance with your property needs.',
                'keywords' => 'contact bali villa spot, property inquiries, customer support, villa rental help',
                'canonicalUrl' => url('/contact'),
                'ogImage' => asset('images/logo/Logo.png'),
            ],
            'businessPhone' => config('app.business_phone'),
            'businessEmail' => config('app.business_email'),
        ]);
    }

    public function submitContactForm(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'subject' => 'required|string|max:255',
            'inquiry_type' => 'required|string|in:general,villa_rental,property_sale,property_listing,investment,management',
            'property_interest' => 'nullable|string|max:255',
            'budget' => 'nullable|string|in:under_100,100_300,300_500,500_1000,over_1000,purchase_under_100k,purchase_100k_500k,purchase_over_500k',
            'travel_dates' => 'nullable|string|max:100',
            'guests' => 'nullable|integer|min:1',
            'message' => 'required|string|max:2000',
        ]);

        // Queue email to support team
        Mail::to(config('app.business_email'))->queue(new ContactEnquiryMail($validated));

        // Log the enquiry for now
        Log::info('Contact Enquiry Received:', $validated);

        return back()->with('success', 'Thank you for your message! We\'ll get back to you within 24 hours.');
    }
}
