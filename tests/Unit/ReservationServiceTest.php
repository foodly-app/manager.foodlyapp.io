<?php

namespace Tests\Unit;

use App\Services\HttpClient;
use App\Services\ReservationService;
use Tests\TestCase;

class ReservationServiceTest extends TestCase
{
    private ReservationService $reservationService;
    private HttpClient $httpClient;

    protected function setUp(): void
    {
        parent::setUp();
        $this->httpClient = $this->mock(HttpClient::class);
        $this->reservationService = new ReservationService($this->httpClient);
    }

    public function test_list_reservations(): void
    {
        $restaurantId = 1;
        $query = ['date' => '2025-10-20'];
        $expectedResponse = [
            'data' => [
                ['id' => 1, 'customer_name' => 'John Doe', 'status' => 'confirmed'],
                ['id' => 2, 'customer_name' => 'Jane Smith', 'status' => 'pending'],
            ]
        ];

        $this->httpClient
            ->shouldReceive('get')
            ->once()
            ->with("/partner/restaurants/{$restaurantId}/reservations", $query)
            ->andReturn($expectedResponse);

        $result = $this->reservationService->list($restaurantId, $query);

        $this->assertEquals($expectedResponse, $result);
    }

    public function test_get_reservation_by_id(): void
    {
        $restaurantId = 1;
        $reservationId = 10;
        $expectedResponse = [
            'id' => 10,
            'customer_name' => 'John Doe',
            'status' => 'confirmed'
        ];

        $this->httpClient
            ->shouldReceive('get')
            ->once()
            ->with("/partner/restaurants/{$restaurantId}/reservations/{$reservationId}")
            ->andReturn($expectedResponse);

        $result = $this->reservationService->get($restaurantId, $reservationId);

        $this->assertEquals($expectedResponse, $result);
        $this->assertEquals($reservationId, $result['id']);
    }

    public function test_update_reservation_status(): void
    {
        $restaurantId = 1;
        $reservationId = 10;
        $status = 'confirmed';
        $expectedResponse = [
            'id' => 10,
            'status' => 'confirmed',
            'updated_at' => '2025-10-20 10:00:00'
        ];

        $this->httpClient
            ->shouldReceive('put')
            ->once()
            ->with(
                "/partner/restaurants/{$restaurantId}/reservations/{$reservationId}/status",
                ['status' => $status]
            )
            ->andReturn($expectedResponse);

        $result = $this->reservationService->updateStatus($restaurantId, $reservationId, $status);

        $this->assertEquals($expectedResponse, $result);
        $this->assertEquals($status, $result['status']);
    }

    public function test_add_notes_to_reservation(): void
    {
        $restaurantId = 1;
        $reservationId = 10;
        $notes = 'Customer requested window seat';
        $expectedResponse = [
            'id' => 10,
            'notes' => $notes
        ];

        $this->httpClient
            ->shouldReceive('post')
            ->once()
            ->with(
                "/partner/restaurants/{$restaurantId}/reservations/{$reservationId}/notes",
                ['notes' => $notes]
            )
            ->andReturn($expectedResponse);

        $result = $this->reservationService->addNotes($restaurantId, $reservationId, $notes);

        $this->assertEquals($expectedResponse, $result);
    }

    public function test_get_reservation_statistics(): void
    {
        $restaurantId = 1;
        $query = ['start_date' => '2025-10-01', 'end_date' => '2025-10-31'];
        $expectedResponse = [
            'total' => 100,
            'confirmed' => 70,
            'cancelled' => 15,
            'no_show' => 5
        ];

        $this->httpClient
            ->shouldReceive('get')
            ->once()
            ->with("/partner/restaurants/{$restaurantId}/reservations/statistics", $query)
            ->andReturn($expectedResponse);

        $result = $this->reservationService->getStatistics($restaurantId, $query);

        $this->assertEquals($expectedResponse, $result);
        $this->assertArrayHasKey('total', $result);
    }

    public function test_get_today_reservations(): void
    {
        $restaurantId = 1;
        $expectedResponse = [
            'data' => [
                ['id' => 1, 'time' => '12:00', 'customer_name' => 'John Doe'],
                ['id' => 2, 'time' => '14:00', 'customer_name' => 'Jane Smith'],
            ]
        ];

        $this->httpClient
            ->shouldReceive('get')
            ->once()
            ->with("/partner/restaurants/{$restaurantId}/reservations/today")
            ->andReturn($expectedResponse);

        $result = $this->reservationService->getToday($restaurantId);

        $this->assertEquals($expectedResponse, $result);
    }

    public function test_get_upcoming_reservations(): void
    {
        $restaurantId = 1;
        $query = ['days' => 7];
        $expectedResponse = [
            'data' => [
                ['id' => 1, 'date' => '2025-10-21', 'customer_name' => 'John Doe'],
                ['id' => 2, 'date' => '2025-10-22', 'customer_name' => 'Jane Smith'],
            ]
        ];

        $this->httpClient
            ->shouldReceive('get')
            ->once()
            ->with("/partner/restaurants/{$restaurantId}/reservations/upcoming", $query)
            ->andReturn($expectedResponse);

        $result = $this->reservationService->getUpcoming($restaurantId, $query);

        $this->assertEquals($expectedResponse, $result);
    }

    public function test_cancel_reservation(): void
    {
        $restaurantId = 1;
        $reservationId = 10;
        $reason = 'Customer requested cancellation';
        $expectedResponse = [
            'id' => 10,
            'status' => 'cancelled',
            'cancellation_reason' => $reason
        ];

        $this->httpClient
            ->shouldReceive('post')
            ->once()
            ->with(
                "/partner/restaurants/{$restaurantId}/reservations/{$reservationId}/cancel",
                ['reason' => $reason]
            )
            ->andReturn($expectedResponse);

        $result = $this->reservationService->cancel($restaurantId, $reservationId, $reason);

        $this->assertEquals($expectedResponse, $result);
        $this->assertEquals('cancelled', $result['status']);
    }

    public function test_throws_exception_when_reservation_not_found(): void
    {
        $restaurantId = 1;
        $reservationId = 999;

        $this->httpClient
            ->shouldReceive('get')
            ->once()
            ->with("/partner/restaurants/{$restaurantId}/reservations/{$reservationId}")
            ->andThrow(new \Exception('Not Found: Reservation not found'));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Not Found');

        $this->reservationService->get($restaurantId, $reservationId);
    }
}
