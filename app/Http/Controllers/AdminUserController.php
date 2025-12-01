<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

class AdminUserController extends Controller
{
    /**
     * Display a paginated listing of all users with search and filtering capabilities.
     * 
     * Supports filtering by:
     * - Search term (name, email)
     * - Role (admin, user)
     * - Email verification status
     * - Account creation date range
     * 
     * @param Request $request
     * @return \Inertia\Response
     */
    public function index(Request $request)
    {
        // Build query with eager loading for performance
        $query = User::with(['bookings'])
            ->withCount(['bookings'])
            ->orderBy('created_at', 'desc');

        // Apply search filter across name and email fields
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Apply role filter
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Apply email verification filter
        if ($request->filled('email_verified')) {
            if ($request->email_verified === 'verified') {
                $query->whereNotNull('email_verified_at');
            } elseif ($request->email_verified === 'unverified') {
                $query->whereNull('email_verified_at');
            }
        }

        // Apply date range filter for account creation
        if ($request->filled('created_from')) {
            $query->whereDate('created_at', '>=', $request->created_from);
        }

        if ($request->filled('created_to')) {
            $query->whereDate('created_at', '<=', $request->created_to);
        }

        // Paginate results
        $users = $query->paginate(15)->withQueryString();

        return Inertia::render('admin/users/Index', [
            'users' => $users,
            'filters' => $request->only(['search', 'role', 'email_verified', 'created_from', 'created_to']),
            'roleOptions' => $this->getRoleOptions(),
            'emailVerificationOptions' => $this->getEmailVerificationOptions(),
        ]);
    }

    /**
     * Show the form for creating a new user.
     * 
     * @return \Inertia\Response
     */
    public function create()
    {
        return Inertia::render('admin/users/Create', [
            'roleOptions' => $this->getRoleOptions(),
        ]);
    }

    /**
     * Store a newly created user in the database.
     * 
     * Handles:
     * - User creation with validation
     * - Password hashing
     * - Role assignment
     * - Email verification status
     * - Database transactions for data integrity
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Validate user input
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => ['required', 'in:admin,user'],
            'email_verified' => ['boolean'],
        ]);

        // Use database transaction to ensure data integrity
        DB::beginTransaction();
        
        try {
            // Hash the password
            $validated['password'] = Hash::make($validated['password']);
            
            // Set email verification timestamp if marked as verified
            if ($request->boolean('email_verified')) {
                $validated['email_verified_at'] = now();
            }

            // Create the user record
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'role' => $validated['role'],
                'email_verified_at' => $validated['email_verified'] ? now() : null,
            ]);

            DB::commit();

            Log::info('Admin created new user', [
                'admin_id' => auth()->id(),
                'user_id' => $user->id,
                'user_email' => $user->email,
                'user_role' => $user->role,
                'email_verified' => $user->email_verified_at,
            ]);

            return redirect()->route('admin.users.show', $user)
                ->with('success', 'User created successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Failed to create user', [
                'admin_id' => auth()->id(),
                'error' => $e->getMessage(),
                'email' => $request->email,
            ]);
            
            return back()->withErrors(['error' => 'Failed to create user: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Display the specified user with all related data.
     * 
     * @param User $user
     * @return \Inertia\Response
     */
    public function show(User $user)
    {
        // Load all relationships for complete user view
        $user->load([
            'bookings' => function($query) {
                $query->with('property:id,title,slug')
                      ->orderBy('created_at', 'desc')
                      ->take(10);
            }
        ]);

        // Get additional statistics
        $stats = [
            'total_bookings' => $user->bookings()->count(),
            'confirmed_bookings' => $user->bookings()->where('status', 'confirmed')->count(),
            'pending_bookings' => $user->bookings()->where('status', 'pending')->count(),
            'member_since' => $user->created_at->format('F Y'),
            'last_login' => $user->updated_at, // Approximate, you might want to add a last_login_at field
        ];

        return Inertia::render('admin/users/Show', [
            'user' => $user,
            'stats' => $stats,
            'roleOptions' => $this->getRoleOptions(),
        ]);
    }

    /**
     * Show the form for editing the specified user.
     * 
     * @param User $user
     * @return \Inertia\Response
     */
    public function edit(User $user)
    {
        return Inertia::render('admin/users/Edit', [
            'user' => $user,
            'roleOptions' => $this->getRoleOptions(),
        ]);
    }

    /**
     * Update the specified user in the database.
     * 
     * Handles:
     * - User information updates
     * - Role changes
     * - Password updates (optional)
     * - Email verification status changes
     * - Prevents admin from demoting themselves
     * 
     * @param Request $request
     * @param User $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, User $user)
    {
        // Validate user input
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'role' => ['required', 'in:admin,user'],
            'email_verified' => ['boolean'],
        ]);

        // Prevent admin from demoting themselves
        if ($user->id === auth()->id() && $request->role !== 'admin') {
            return back()->withErrors(['role' => 'You cannot change your own role.']);
        }

        DB::beginTransaction();
        
        try {
            // Remove password from validated array if not provided
            if (empty($validated['password'])) {
                unset($validated['password']);
            } else {
                $validated['password'] = Hash::make($validated['password']);
            }

            // Handle email verification status
            if ($request->boolean('email_verified')) {
                $validated['email_verified_at'] = $user->email_verified_at ?? now();
            } else {
                $validated['email_verified_at'] = null;
            }

            // Remove the email_verified boolean from validated array
            unset($validated['email_verified']);

            // Update the user
            $user->update($validated);

            DB::commit();

            Log::info('Admin updated user', [
                'admin_id' => auth()->id(),
                'user_id' => $user->id,
                'user_email' => $user->email,
                'changes' => $request->only(['name', 'email', 'role', 'email_verified']),
            ]);

            return redirect()->route('admin.users.show', $user)
                ->with('success', 'User updated successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Failed to update user', [
                'admin_id' => auth()->id(),
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            
            return back()->withErrors(['error' => 'Failed to update user: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Remove the specified user from the database.
     * 
     * Handles:
     * - Prevents admin from deleting themselves
     * - Checks for associated data (properties, bookings)
     * - Provides options for handling associated data
     * - Logs deletion for audit trail
     * 
     * @param User $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(User $user)
    {
        // Prevent admin from deleting themselves
        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => 'You cannot delete your own account.']);
        }

        DB::beginTransaction();
        
        try {
            // Check for associated data
            $bookingsCount = $user->bookings()->count();

            if ($bookingsCount > 0) {
                $message = "Cannot delete user. User has {$bookingsCount} bookings. Please transfer or delete associated data first.";
                return back()->withErrors(['error' => $message]);
            }

            // Store user info for logging before deletion
            $userInfo = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ];

            // Delete the user
            $user->delete();

            DB::commit();

            Log::warning('Admin deleted user', [
                'admin_id' => auth()->id(),
                'deleted_user' => $userInfo,
            ]);

            return redirect()->route('admin.users.index')
                ->with('success', 'User deleted successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Failed to delete user', [
                'admin_id' => auth()->id(),
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            
            return back()->withErrors(['error' => 'Failed to delete user: ' . $e->getMessage()]);
        }
    }

    /**
     * Toggle the email verification status of a user.
     * 
     * @param User $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function toggleEmailVerification(User $user)
    {
        try {
            $isVerified = $user->email_verified_at !== null;
            
            $user->update([
                'email_verified_at' => $isVerified ? null : now()
            ]);

            $status = $isVerified ? 'unverified' : 'verified';
            
            Log::info('Admin toggled user email verification', [
                'admin_id' => auth()->id(),
                'user_id' => $user->id,
                'new_status' => $status,
            ]);

            return back()->with('success', "User email has been marked as {$status}.");

        } catch (\Exception $e) {
            Log::error('Failed to toggle email verification', [
                'admin_id' => auth()->id(),
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            
            return back()->withErrors(['error' => 'Failed to update email verification status.']);
        }
    }

    /**
     * Resend email verification notification to a user.
     * 
     * @param User $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function resendEmailVerification(User $user)
    {
        Log::info('Admin attempting to resend email verification', [
            'admin_id' => auth()->id(),
            'user_id' => $user->id,
            'user_email' => $user->email,
        ]);
        try {
            if ($user->hasVerifiedEmail()) {
                return back()->withErrors(['error' => 'User email is already verified.']);
            }

            $user->sendEmailVerificationNotification();

            Log::info('Admin resent email verification', [
                'admin_id' => auth()->id(),
                'user_id' => $user->id,
            ]);

            return back()->with('success', 'Email verification notification sent successfully.');

        } catch (\Exception $e) {
            Log::error('Failed to resend email verification', [
                'admin_id' => auth()->id(),
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            
            return back()->withErrors(['error' => 'Failed to send email verification.']);
        }
    }

    /**
     * Impersonate a user (login as them).
     * Be very careful with this feature - ensure proper logging and restrictions.
     * 
     * @param User $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function impersonate(User $user)
    {
        // Prevent impersonating other admins
        if ($user->role === 'admin') {
            return back()->withErrors(['error' => 'Cannot impersonate other administrators.']);
        }

        // Store the original admin ID in session
        session(['impersonating_admin_id' => auth()->id()]);
        
        // Log the impersonation
        Log::warning('Admin started impersonation', [
            'admin_id' => auth()->id(),
            'target_user_id' => $user->id,
            'target_user_email' => $user->email,
        ]);

        // Login as the target user
        auth()->login($user);

        return redirect()->route('dashboard')
            ->with('warning', 'You are now impersonating ' . $user->name . '. Click here to stop impersonation.');
    }

    /**
     * Stop impersonating and return to admin account.
     * 
     * @return \Illuminate\Http\RedirectResponse
     */
    public function stopImpersonation()
    {
        $adminId = session('impersonating_admin_id');
        
        if (!$adminId) {
            return redirect()->route('dashboard');
        }

        $admin = User::find($adminId);
        
        if (!$admin) {
            session()->forget('impersonating_admin_id');
            return redirect()->route('login');
        }

        Log::info('Admin stopped impersonation', [
            'admin_id' => $adminId,
            'impersonated_user_id' => auth()->id(),
        ]);

        session()->forget('impersonating_admin_id');
        auth()->login($admin);

        return redirect()->route('admin.users.index')
            ->with('success', 'Impersonation stopped successfully.');
    }

    /**
     * Get role options for forms.
     * 
     * @return array
     */
    private function getRoleOptions(): array
    {
        return [
            'user' => 'User',
            'admin' => 'Administrator',
        ];
    }

    /**
     * Get email verification options for filtering.
     * 
     * @return array
     */
    private function getEmailVerificationOptions(): array
    {
        return [
            '' => 'All Users',
            'verified' => 'Verified',
            'unverified' => 'Unverified',
        ];
    }

    /**
     * Export users data to CSV.
     * 
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function export(Request $request)
    {
        $query = User::withCount(['bookings']);

        // Apply same filters as index method
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('email_verified')) {
            if ($request->email_verified === 'verified') {
                $query->whereNotNull('email_verified_at');
            } elseif ($request->email_verified === 'unverified') {
                $query->whereNull('email_verified_at');
            }
        }

        $users = $query->orderBy('created_at', 'desc')->get();

        Log::info('Admin exported users data', [
            'admin_id' => auth()->id(),
            'filters' => $request->only(['search', 'role', 'email_verified']),
            'exported_count' => $users->count(),
        ]);

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="users-export-' . date('Y-m-d') . '.csv"',
        ];

        return response()->stream(function() use ($users) {
            $handle = fopen('php://output', 'w');
            
            // Add CSV headers
            fputcsv($handle, [
                'ID', 'Name', 'Email', 'Role', 'Email Verified', 
                'Properties Count', 'Bookings Count', 'Created At', 'Updated At'
            ]);

            // Add user data
            foreach ($users as $user) {
                fputcsv($handle, [
                    $user->id,
                    $user->name,
                    $user->email,
                    $user->role,
                    $user->email_verified_at ? 'Yes' : 'No',
                    $user->properties_count ?? 0,
                    $user->bookings_count ?? 0,
                    $user->created_at->format('Y-m-d H:i:s'),
                    $user->updated_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }
}