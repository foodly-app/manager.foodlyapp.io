<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RouteTest extends TestCase
{
    /**
     * Test that all routes are registered and accessible
     */
    public function test_all_routes_are_registered(): void
    {
        $routes = [
            // Auth routes
            'auth.login' => ['method' => 'POST', 'uri' => '/auth/login'],
            'auth.me' => ['method' => 'GET', 'uri' => '/auth/me'],
            'auth.profile.get' => ['method' => 'GET', 'uri' => '/auth/profile'],
            'auth.initial-dashboard' => ['method' => 'GET', 'uri' => '/auth/initial-dashboard'],
            
            // Organization routes
            'organizations.index' => ['method' => 'GET', 'uri' => '/organizations'],
            'organizations.team.index' => ['method' => 'GET', 'uri' => '/organizations/1/team'],
            'organizations.analytics.reservations' => ['method' => 'GET', 'uri' => '/organizations/1/analytics/reservations'],
            
            // Restaurant routes
            'restaurants.index' => ['method' => 'GET', 'uri' => '/organizations/1/restaurants'],
            'restaurants.store' => ['method' => 'POST', 'uri' => '/organizations/1/restaurants'],
            
            // Place routes
            'places.index' => ['method' => 'GET', 'uri' => '/organizations/1/restaurants/1/places'],
            
            // Table routes
            'tables.index' => ['method' => 'GET', 'uri' => '/organizations/1/restaurants/1/tables'],
            
            // Reservation routes
            'reservations.index' => ['method' => 'GET', 'uri' => '/reservations'],
            'reservations.restaurant.store' => ['method' => 'POST', 'uri' => '/organizations/1/restaurants/1/reservations'],
            
            // Booking routes
            'booking.settings.get' => ['method' => 'GET', 'uri' => '/organizations/1/restaurants/1/booking/settings'],
            
            // Menu routes
            'menu.index' => ['method' => 'GET', 'uri' => '/organizations/1/restaurants/1/menu'],
            'menu.categories.index' => ['method' => 'GET', 'uri' => '/organizations/1/restaurants/1/menu/categories'],
            'menu.items.index' => ['method' => 'GET', 'uri' => '/organizations/1/restaurants/1/menu/items'],
        ];

        foreach ($routes as $name => $route) {
            $this->assertTrue(
                \Illuminate\Support\Facades\Route::has($name),
                "Route [{$name}] is not registered"
            );
        }
    }

    /**
     * Test welcome page
     */
    public function test_welcome_page_loads(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    /**
     * Test connection endpoint
     */
    public function test_connection_endpoint_returns_json(): void
    {
        $response = $this->get('/test-connection');
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message'
            ]);
    }
}
