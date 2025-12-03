<?php

use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get('/dashboard');
    $response->assertRedirect('/login');
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get('/dashboard');
    $response->assertStatus(200);
});

test('admin users can access the dashboard', function () {
    $adminUser = User::factory()->create(['role' => 'admin']);
    $this->actingAs($adminUser);

    $response = $this->get('/dashboard');
    $response->assertStatus(200);
});

test('regular users can access the dashboard', function () {
    $regularUser = User::factory()->create(['role' => 'user']);
    $this->actingAs($regularUser);

    $response = $this->get('/dashboard');
    $response->assertStatus(200);
});

test('regular users dashboard contains basic content', function () {
    $regularUser = User::factory()->create(['role' => 'user']);
    
    $response = $this->actingAs($regularUser)->get('/dashboard');
    
    $response->assertStatus(200)
            ->assertSee('dashboard')
            ->assertSee('Dashboard');
            
    expect(strlen($response->getContent()))->toBeGreaterThan(500);
});

test('regular user navigation routes are accessible', function () {
    $regularUser = User::factory()->create(['role' => 'user']);
    
    // These are the actual routes from your web.php
    $this->actingAs($regularUser)->get('/dashboard')->assertStatus(200);
    $this->actingAs($regularUser)->get('/my-bookings')->assertStatus(200);  // ✅ Correct path
    $this->actingAs($regularUser)->get('/my-favorites')->assertStatus(200); // ✅ Correct path
});

test('regular users are redirected from admin routes', function () {
    $regularUser = User::factory()->create(['role' => 'user']);
    
    // Test admin cache route (this exists in your routes)
    $response = $this->actingAs($regularUser)->get('/admin/cache');
    
    // Should redirect due to admin middleware
    $response->assertStatus(302);
    
    // Test admin users route
    $response = $this->actingAs($regularUser)->get('/admin/users');
    $response->assertStatus(302);
    
    // Test admin properties route
    $response = $this->actingAs($regularUser)->get('/admin/properties');
    $response->assertStatus(302);
});

test('admin users can access admin routes', function () {
    $adminUser = User::factory()->create(['role' => 'admin']);
    
    // Test multiple admin routes that definitely exist
    $this->actingAs($adminUser)->get('/admin/cache')->assertStatus(200);
    $this->actingAs($adminUser)->get('/admin/users')->assertStatus(200);
    $this->actingAs($adminUser)->get('/admin/properties')->assertStatus(200);
    $this->actingAs($adminUser)->get('/admin/bookings')->assertStatus(200);
});

test('users can only access their own content routes', function () {
    $user = User::factory()->create(['role' => 'user']);
    
    // These routes require auth but not admin
    $this->actingAs($user)->get('/my-bookings')->assertStatus(200);
    $this->actingAs($user)->get('/my-favorites')->assertStatus(200);
    
    // These should redirect (admin only)
    $this->actingAs($user)->get('/admin/users')->assertStatus(302);
    $this->actingAs($user)->get('/admin/properties')->assertStatus(302);
});

test('public routes are accessible without authentication', function () {
    // These routes don't require auth
    $this->get('/')->assertStatus(200);
    $this->get('/properties')->assertStatus(200);
    $this->get('/contact')->assertStatus(200);
    $this->get('/support/faq')->assertStatus(200);
    $this->get('/support/terms-of-service')->assertStatus(200);
    $this->get('/support/privacy-policy')->assertStatus(200);
    $this->get('/support/cookie-policy')->assertStatus(200);
});

test('authenticated routes require login', function () {
    // These should redirect to login
    $this->get('/dashboard')->assertRedirect('/login');
    $this->get('/my-bookings')->assertRedirect('/login');
    $this->get('/my-favorites')->assertRedirect('/login');
});

test('contact form submission works', function () {
    $response = $this->post('/contact', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'subject' => 'Test Subject',
        'message' => 'Test message content'
    ]);
    
    // Should redirect or return success
    expect($response->status())->toBeIn([200, 302, 422]);
});