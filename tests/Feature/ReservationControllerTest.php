<?php

namespace Tests\Feature;

use App\Services\ReservationService;
use Tests\TestCase;

class ReservationControllerTest extends TestCase
{
    private ReservationService $reservationService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reservationService = $this->mock(ReservationService::class);
    }

    public function test_index_returns_reservations_list(): void
    {
        $restaurantId = 1;
        $mockResponse = [
            'data' => [
                ['id' => 1, 'customer_name' => 'John Doe', 'status' => 'confirmed'],
                ['id' => 2, 'customer_name' => 'Jane Smith', 'status' => 'pending'],
            ]
        ];

        $this->reservationService
            ->shouldReceive('list')
            ->once()
            ->andReturn($mockResponse);

        $response = $this->getJson("/api/restaurants/{$restaurantId}/reservations");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => $mockResponse
            ]);
    }

    public function test_index_handles_exception(): void
    {
        $restaurantId = 1;

        $this->reservationService
            ->shouldReceive('list')
            ->once()
            ->andThrow(new \Exception('Service error'));

        $response = $this->getJson("/api/restaurants/{$restaurantId}/reservations");

        $response->assertStatus(500);
    }

    public function test_today_returns_todays_reservations(): void
    {
        $restaurantId = 1;
        $mockResponse = [
            'data' => [
                ['id' => 1, 'time' => '12:00', 'customer_name' => 'John Doe'],
                ['id' => 2, 'time' => '14:00', 'customer_name' => 'Jane Smith'],
            ]
        ];

        $this->reservationService
            ->shouldReceive('getToday')
            ->once()
            ->with($restaurantId)
            ->andReturn($mockResponse);

        $response = $this->getJson("/api/restaurants/{$restaurantId}/reservations/today");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => $mockResponse
            ]);
    }

    public function test_today_handles_exception(): void
    {
        $restaurantId = 1;

        $this->reservationService
            ->shouldReceive('getToday')
            ->once()
            ->with($restaurantId)
            ->andThrow(new \Exception('Error'));

        $response = $this->getJson("/api/restaurants/{$restaurantId}/reservations/today");

        $response->assertStatus(500);
    }

    public function test_upcoming_returns_upcoming_reservations(): void
    {
        $restaurantId = 1;
        $mockResponse = [
            'data' => [
                ['id' => 1, 'date' => '2025-10-21', 'customer_name' => 'John Doe'],
            ]
        ];

        $this->reservationService
            ->shouldReceive('getUpcoming')
            ->once()
            ->andReturn($mockResponse);

        $response = $this->getJson("/api/restaurants/{$restaurantId}/reservations/upcoming");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => $mockResponse
            ]);
    }

    public function test_update_status_successful(): void
    {
        $restaurantId = 1;
        $reservationId = 10;
        $statusData = ['status' => 'confirmed'];

        $mockResponse = [
            'id' => 10,
            'status' => 'confirmed'
        ];

        $this->reservationService
            ->shouldReceive('updateStatus')
            ->once()
            ->with($restaurantId, $reservationId, 'confirmed')
            ->andReturn($mockResponse);

        $response = $this->putJson(
            "/api/restaurants/{$restaurantId}/reservations/{$reservationId}/status",
            $statusData
        );

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => $mockResponse
            ]);
    }

    public function test_update_status_validates_status_field(): void
    {
        $restaurantId = 1;
        $reservationId = 10;

        $response = $this->putJson(
            "/api/restaurants/{$restaurantId}/reservations/{$reservationId}/status",
            ['status' => 'invalid-status']
        );

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    public function test_update_status_requires_status_field(): void
    {
        $restaurantId = 1;
        $reservationId = 10;

        $response = $this->putJson(
            "/api/restaurants/{$restaurantId}/reservations/{$reservationId}/status",
            []
        );

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    public function test_add_notes_successful(): void
    {
        $restaurantId = 1;
        $reservationId = 10;
        $notesData = ['notes' => 'Customer requested window seat'];

        $mockResponse = [
            'id' => 10,
            'notes' => 'Customer requested window seat'
        ];

        $this->reservationService
            ->shouldReceive('addNotes')
            ->once()
            ->with($restaurantId, $reservationId, 'Customer requested window seat')
            ->andReturn($mockResponse);

        $response = $this->postJson(
            "/api/restaurants/{$restaurantId}/reservations/{$reservationId}/notes",
            $notesData
        );

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => $mockResponse
            ]);
    }

    public function test_add_notes_requires_notes_field(): void
    {
        $restaurantId = 1;
        $reservationId = 10;

        $response = $this->postJson(
            "/api/restaurants/{$restaurantId}/reservations/{$reservationId}/notes",
            []
        );

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['notes']);
    }

    public function test_cancel_reservation_successful(): void
    {
        $restaurantId = 1;
        $reservationId = 10;
        $cancelData = ['reason' => 'Customer requested cancellation'];

        $mockResponse = [
            'id' => 10,
            'status' => 'cancelled',
            'cancellation_reason' => 'Customer requested cancellation'
        ];

        $this->reservationService
            ->shouldReceive('cancel')
            ->once()
            ->with($restaurantId, $reservationId, 'Customer requested cancellation')
            ->andReturn($mockResponse);

        $response = $this->postJson(
            "/api/restaurants/{$restaurantId}/reservations/{$reservationId}/cancel",
            $cancelData
        );

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => $mockResponse
            ]);
    }

    public function test_cancel_requires_reason_field(): void
    {
        $restaurantId = 1;
        $reservationId = 10;

        $response = $this->postJson(
            "/api/restaurants/{$restaurantId}/reservations/{$reservationId}/cancel",
            []
        );

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['reason']);
    }

    public function test_statistics_returns_reservation_stats(): void
    {
        $restaurantId = 1;
        $mockResponse = [
            'total' => 100,
            'confirmed' => 70,
            'cancelled' => 15,
            'no_show' => 5
        ];

        $this->reservationService
            ->shouldReceive('getStatistics')
            ->once()
            ->andReturn($mockResponse);

        $response = $this->getJson("/api/restaurants/{$restaurantId}/reservations/statistics");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => $mockResponse
            ]);
    }

    public function test_statistics_handles_query_parameters(): void
    {
        $restaurantId = 1;
        $query = ['start_date' => '2025-10-01', 'end_date' => '2025-10-31'];
        $mockResponse = ['total' => 50];

        $this->reservationService
            ->shouldReceive('getStatistics')
            ->once()
            ->andReturn($mockResponse);

        $response = $this->getJson(
            "/api/restaurants/{$restaurantId}/reservations/statistics?" . http_build_query($query)
        );

        $response->assertStatus(200);
    }

    public function test_statistics_handles_exception(): void
    {
        $restaurantId = 1;

        $this->reservationService
            ->shouldReceive('getStatistics')
            ->once()
            ->andThrow(new \Exception('Statistics error'));

        $response = $this->getJson("/api/restaurants/{$restaurantId}/reservations/statistics");

        $response->assertStatus(500);
    }
}
