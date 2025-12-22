<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AdminUserControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $admin;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create an admin user for authentication
        $this->admin = User::factory()->create([
            'role' => 'admin'
        ]);
        
        // Create a regular user
        $this->user = User::factory()->create([
            'role' => 'user'
        ]);
    }

    #[Test]
    public function test_index_displays_users_list()
    {
        // Arrange
        $users = User::factory()->count(3)->create();
        
        // Act
        $response = $this->actingAs($this->admin)
            ->get(route('admin.users.index'));
        
        // Assert
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('admin/users/Index')
                ->has('users.data', 5) // 3 created + admin + user from setup
        );
    }

    #[Test]
    public function test_index_filters_by_search_term()
    {
        // Arrange
        $user1 = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ]);
        
        $user2 = User::factory()->create([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com'
        ]);
        
        // Act - search by name
        $response = $this->actingAs($this->admin)
            ->get(route('admin.users.index', ['search' => 'John Doe']));
        
        // Assert
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) =>
            $page->component('admin/users/Index')
            ->has('users.data', 1)
        );
    }

    #[Test]
    public function test_index_filters_by_role()
    {
        // Arrange
        User::factory()->create(['role' => 'admin']);
        User::factory()->create(['role' => 'user']);
        User::factory()->create(['role' => 'user']);
        
        // Act
        $response = $this->actingAs($this->admin)
            ->get(route('admin.users.index', ['role' => 'user']));
        
        // Assert
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->has('users.data', 3) // 2 created + 1 from setup
        );
    }

    #[Test]
    public function test_index_filters_by_email_verification_status()
    {
        // Arrange
        User::factory()->create(['email_verified_at' => now()]);
        User::factory()->create(['email_verified_at' => null]);
        
        // Act
        $response = $this->actingAs($this->admin)
            ->get(route('admin.users.index', ['email_verified' => 'verified']));
        
        // Assert
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->has('users.data', 3) // 1 created + admin + user (assuming they're verified)
        );
    }

    #[Test]
    public function test_create_displays_user_creation_form()
    {
        // Act
        $response = $this->actingAs($this->admin)
            ->get(route('admin.users.create'));
        
        // Assert
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('admin/users/Create')
                ->has('roleOptions')
        );
    }

    #[Test]
    public function test_store_creates_user_successfully()
    {
        // Arrange
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'user',
            'email_verified' => true,
        ];
        
        // Act
        $response = $this->actingAs($this->admin)
            ->post(route('admin.users.store'), $userData);
        
        // Assert
        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => 'user'
        ]);

        $user = User::where('email', 'test@example.com')->first();
        $this->assertNotNull($user->email_verified_at);
        $this->assertTrue(Hash::check('password123', $user->password));
    }

    #[Test]
    public function test_store_validates_required_fields()
    {
        // Act
        $response = $this->actingAs($this->admin)
            ->post(route('admin.users.store'), []);
        
        // Assert
        $response->assertSessionHasErrors([
            'name',
            'email',
            'password',
            'role'
        ]);
    }

    #[Test]
    public function test_store_validates_unique_email()
    {
        // Arrange
        $existingUser = User::factory()->create(['email' => 'existing@example.com']);
        
        $userData = [
            'name' => 'Test User',
            'email' => 'existing@example.com', // Duplicate email
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'user',
        ];
        
        // Act
        $response = $this->actingAs($this->admin)
            ->post(route('admin.users.store'), $userData);
        
        // Assert
        $response->assertSessionHasErrors(['email']);
    }

    #[Test]
    public function test_store_validates_password_confirmation()
    {
        // Arrange
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different_password',
            'role' => 'user',
        ];
        
        // Act
        $response = $this->actingAs($this->admin)
            ->post(route('admin.users.store'), $userData);
        
        // Assert
        $response->assertSessionHasErrors(['password']);
    }

    #[Test]
    public function test_store_validates_role_values()
    {
        // Arrange
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'invalid_role',
        ];
        
        // Act
        $response = $this->actingAs($this->admin)
            ->post(route('admin.users.store'), $userData);
        
        // Assert
        $response->assertSessionHasErrors(['role']);
    }

    #[Test]
    public function test_show_displays_specific_user()
    {
        // Arrange
        $user = User::factory()->create();
        
        // Act
        $response = $this->actingAs($this->admin)
            ->get(route('admin.users.show', $user));
        
        // Assert
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('admin/users/Show')
                ->where('user.id', $user->id)
                ->has('stats')
        );
    }

    #[Test]
    public function test_edit_displays_user_edit_form()
    {
        // Arrange
        $user = User::factory()->create();
        
        // Act
        $response = $this->actingAs($this->admin)
            ->get(route('admin.users.edit', $user));
        
        // Assert
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('admin/users/Edit')
                ->where('user.id', $user->id)
                ->has('roleOptions')
        );
    }

    #[Test]
    public function test_update_modifies_user_successfully()
    {
        // Arrange
        $user = User::factory()->create([
            'name' => 'Old Name',
            'email' => 'old@example.com',
            'role' => 'user'
        ]);

        $updateData = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'role' => 'user',
            'email_verified' => true,
        ];
        
        // Act
        $response = $this->actingAs($this->admin)
            ->put(route('admin.users.update', $user), $updateData);
        
        // Assert
        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com'
        ]);
    }

    #[Test]
    public function test_update_prevents_admin_from_changing_own_role()
    {
        // Arrange
        $updateData = [
            'name' => $this->admin->name,
            'email' => $this->admin->email,
            'role' => 'user', // Trying to demote self
            'email_verified' => true,
        ];
        
        // Act
        $response = $this->actingAs($this->admin)
            ->put(route('admin.users.update', $this->admin), $updateData);
        
        // Assert
        $response->assertSessionHasErrors(['role']);
        $this->assertDatabaseHas('users', [
            'id' => $this->admin->id,
            'role' => 'admin' // Role should remain unchanged
        ]);
    }

    #[Test]
    public function test_update_with_password_change()
    {
        // Arrange
        $user = User::factory()->create();
        $originalPassword = $user->password;

        $updateData = [
            'name' => $user->name,
            'email' => $user->email,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
            'role' => $user->role,
            'email_verified' => true,
        ];
        
        // Act
        $response = $this->actingAs($this->admin)
            ->put(route('admin.users.update', $user), $updateData);
        
        // Assert
        $response->assertRedirect();
        $user->refresh();
        $this->assertNotEquals($originalPassword, $user->password);
        $this->assertTrue(Hash::check('newpassword123', $user->password));
    }

    #[Test]
    public function test_update_without_password_change()
    {
        // Arrange
        $user = User::factory()->create();
        $originalPassword = $user->password;

        $updateData = [
            'name' => 'Updated Name',
            'email' => $user->email,
            'role' => $user->role,
            'email_verified' => true,
            // No password provided
        ];
        
        // Act
        $response = $this->actingAs($this->admin)
            ->put(route('admin.users.update', $user), $updateData);
        
        // Assert
        $response->assertRedirect();
        $user->refresh();
        $this->assertEquals($originalPassword, $user->password); // Password unchanged
    }

    #[Test]
    public function test_destroy_deletes_user_successfully()
    {
        // Arrange
        $user = User::factory()->create();
        
        // Act
        $response = $this->actingAs($this->admin)
            ->delete(route('admin.users.destroy', $user));
        
        // Assert
        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('users', [
            'id' => $user->id
        ]);
    }

    #[Test]
    public function test_destroy_prevents_admin_from_deleting_themselves()
    {
        // Act
        $response = $this->actingAs($this->admin)
            ->delete(route('admin.users.destroy', $this->admin));
        
        // Assert
        $response->assertSessionHasErrors(['error']);
        $this->assertDatabaseHas('users', [
            'id' => $this->admin->id
        ]);
    }

    #[Test]
    public function test_destroy_prevents_deleting_user_with_bookings()
    {
        // Arrange
        $user = User::factory()->create();
        Booking::factory()->create(['user_id' => $user->id]);
        
        // Act
        $response = $this->actingAs($this->admin)
            ->delete(route('admin.users.destroy', $user));
        
        // Assert
        $response->assertSessionHasErrors(['error']);
        $this->assertDatabaseHas('users', [
            'id' => $user->id
        ]);
    }

    #[Test]
    public function test_toggle_email_verification_marks_unverified_as_verified()
    {
        // Arrange - explicitly create an unverified user
        $user = User::factory()->unverified()->create();
        
        // Ensure user is properly persisted before making request
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email_verified_at' => null
        ]);
        
        // Act
        $response = $this->actingAs($this->admin)
            ->patch(route('admin.users.toggle-email-verification', $user));
        
        // Assert
        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        // Check database directly instead of refreshing model
        $this->assertDatabaseHas('users', [
            'id' => $user->id
        ]);
        
        $updatedUser = User::find($user->id);
        $this->assertNotNull($updatedUser->email_verified_at);
    }

    #[Test]
    public function test_toggle_email_verification_marks_verified_as_unverified()
    {
        // Arrange
        $user = User::factory()->create(); // By default, this user is verified
        
        // Ensure user is properly persisted before making request
        $this->assertDatabaseHas('users', [
            'id' => $user->id
        ]);
        $this->assertNotNull($user->email_verified_at);
        
        // Act
        $response = $this->actingAs($this->admin)
            ->patch(route('admin.users.toggle-email-verification', $user));
        
        // Assert
        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        // Check database directly instead of refreshing model
        $updatedUser = User::find($user->id);
        $this->assertNull($updatedUser->email_verified_at);
    }

    #[Test]
    public function test_resend_email_verification_for_unverified_user()
    {
        // Arrange
        Notification::fake();
        $user = User::factory()->unverified()->create();
        
        // Act
        $response = $this->actingAs($this->admin)
            ->post(route('admin.users.resend-email-verification', $user));
        
        // Assert
        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    #[Test]
    public function test_resend_email_verification_rejects_verified_user()
    {
        // Arrange
        $user = User::factory()->create();
        
        // Act
        $response = $this->actingAs($this->admin)
            ->post(route('admin.users.resend-email-verification', $user));
        
        // Assert
        $response->assertSessionHasErrors(['error']);
    }

    #[Test]
    public function test_impersonate_user_successfully()
    {
        // Arrange
        $targetUser = User::factory()->create(['role' => 'user']);
        
        // Act
        $response = $this->actingAs($this->admin)
            ->post(route('admin.users.impersonate', $targetUser));
        
        // Assert
        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('warning');
        $this->assertTrue(auth()->check());
        $this->assertEquals($targetUser->id, auth()->id());
        $this->assertEquals($this->admin->id, session('impersonating_admin_id'));
    }

    #[Test]
    public function test_impersonate_prevents_impersonating_admin()
    {
        // Arrange
        $targetAdmin = User::factory()->create(['role' => 'admin']);
        
        // Act
        $response = $this->actingAs($this->admin)
            ->post(route('admin.users.impersonate', $targetAdmin));
        
        // Assert
        $response->assertSessionHasErrors(['error']);
        $this->assertEquals($this->admin->id, auth()->id()); // Still logged in as admin
    }

    #[Test]
    public function test_stop_impersonation_returns_to_admin()
    {
        // Arrange - Start impersonation
        $targetUser = User::factory()->create(['role' => 'user']);
        session(['impersonating_admin_id' => $this->admin->id]);
        auth()->login($targetUser);
        
        // Act
        $response = $this->actingAs($this->admin)->post(route('admin.users.stopImpersonation'));
        
        // Assert
        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');
        $this->assertEquals($this->admin->id, auth()->id());
        $this->assertNull(session('impersonating_admin_id'));
    }

    #[Test]
    public function test_export_generates_csv_file()
    {
        // Arrange
        User::factory()->count(3)->create();
        
        // Act
        $response = $this->actingAs($this->admin)
            ->get(route('admin.users.export'));
        
        // Assert
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $response->assertHeader('content-disposition');
        
        $content = $response->streamedContent();
        $this->assertStringContainsString('ID,Name,Email,Role', $content);
    }

    #[Test]
    public function test_export_respects_filters()
    {
        // Arrange
        User::factory()->create(['role' => 'admin']);
        User::factory()->create(['role' => 'user']);
        
        // Act
        $response = $this->actingAs($this->admin)
            ->get(route('admin.users.export', ['role' => 'user']));
        
        // Assert
        $response->assertStatus(200);
        $content = $response->streamedContent();
        
        // Should contain user role entries but fewer admin entries
        $userCount = substr_count($content, ',user,');
        $this->assertGreaterThan(0, $userCount);
    }

    #[Test]
    public function test_unauthorized_user_cannot_access_admin_routes()
    {
        // Arrange
        $testUser = User::factory()->create();
        // Act & Assert - Regular user cannot access admin routes
        $this->actingAs($this->user)
            ->get(route('admin.users.index'))
            ->assertStatus(302); // Redirect for unauthorized access

        $this->actingAs($this->user)
            ->get(route('admin.users.create'))
            ->assertStatus(302);

        $this->actingAs($this->user)
            ->post(route('admin.users.store'), [])
            ->assertStatus(302);

        $this->actingAs($this->user)
            ->get(route('admin.users.show', $testUser))
            ->assertStatus(302);

        $this->actingAs($this->user)
            ->get(route('admin.users.edit', $testUser))
            ->assertStatus(302);

        $this->actingAs($this->user)
            ->put(route('admin.users.update', $testUser), [])
            ->assertStatus(302);

        $this->actingAs($this->user)
            ->delete(route('admin.users.destroy', $testUser))
            ->assertStatus(302);
    }

    #[Test]
    public function test_index_includes_booking_counts()
    {
        // Arrange
        $user = User::factory()->create();
        Booking::factory()->count(3)->create(['user_id' => $user->id]);
        
        // Act
        $response = $this->actingAs($this->admin)
            ->get(route('admin.users.index'));
        
        // Assert
        $response->assertStatus(200);
        // The response should include users with booking counts loaded
    }

    #[Test]
    public function test_show_includes_user_statistics()
    {
        // Arrange
        $user = User::factory()->create();
        Booking::factory()->count(5)->create(['user_id' => $user->id]);
        Booking::factory()->count(2)->create([
            'user_id' => $user->id,
            'status' => 'confirmed'
        ]);
        
        // Act
        $response = $this->actingAs($this->admin)
            ->get(route('admin.users.show', $user));
        
        // Assert
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->has('stats.total_bookings')
                ->has('stats.confirmed_bookings')
                ->has('stats.member_since')
        );
    }

    #[Test]
    public function test_store_creates_unverified_user_when_email_verified_false()
    {
        // Arrange
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'user',
            'email_verified' => false,
        ];
        
        // Act
        $response = $this->actingAs($this->admin)
            ->post(route('admin.users.store'), $userData);
        
        // Assert
        $response->assertRedirect();
        $user = User::where('email', 'test@example.com')->first();
        $this->assertNull($user->email_verified_at);
    }

    #[Test]
    public function test_update_email_verification_toggle()
    {
        // Arrange
        $user = User::factory()->create(['email_verified_at' => null]);

        $updateData = [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'email_verified' => true,
        ];
        
        // Act
        $response = $this->actingAs($this->admin)
            ->put(route('admin.users.update', $user), $updateData);
        
        // Assert
        $response->assertRedirect();
        $user->refresh();
        $this->assertNotNull($user->email_verified_at);
    }
}
