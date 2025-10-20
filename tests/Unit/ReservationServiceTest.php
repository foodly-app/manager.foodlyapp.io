<?php

namespace Tests\Unit;

use App\Services\HttpClient;
use App\Services\ReservationService;
use Mockery;
use Tests\TestCase;

class ReservationServiceTest extends TestCase
{
    private ReservationService $reservationService;
    private $httpClient;

    protected function setUp(): void
    {
        parent::setUp();
        $this->httpClient = Mockery::mock(HttpClient::class);
        $this->reservationService = new ReservationService($this->httpClient);
    }
    
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_list_reservations(): void
    {
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
            ->with("/partner/reservations", $query)
            ->andReturn($expectedResponse);

        $result = $this->reservationService->list($query);

        $this->assertEquals($expectedResponse, $result);
    }

    public function test_get_reservation_by_id(): void
    {
        $organizationId = 1;
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
            ->with("/partner/organizations/{$organizationId}/restaurants/{$restaurantId}/reservations/{$reservationId}")
            ->andReturn($expectedResponse);

        $result = $this->reservationService->get($organizationId, $restaurantId, $reservationId);

        $this->assertEquals($expectedResponse, $result);
        $this->assertEquals($reservationId, $result['id']);
    }

    public function test_update_reservation_status(): void
    {
        $organizationId = 1;
        $restaurantId = 1;
        $reservationId = 10;
        $status = ['status' => 'confirmed'];
        $expectedResponse = [
            'id' => 10,
            'status' => 'confirmed',
            'updated_at' => '2025-10-20 10:00:00'
        ];

        $this->httpClient
            ->shouldReceive('put')
            ->once()
            ->with(
                "/partner/organizations/{$organizationId}/restaurants/{$restaurantId}/reservations/{$reservationId}/status",
                $status
            )
            ->andReturn($expectedResponse);

        $result = $this->reservationService->updateStatus($organizationId, $restaurantId, $reservationId, $status);

        $this->assertEquals($expectedResponse, $result);
        $this->assertEquals('confirmed', $result['status']);
    }

    public function test_add_note_to_reservation(): void
    {
        $organizationId = 1;
        $restaurantId = 1;
        $reservationId = 10;
        $noteData = ['note' => 'Customer requested window seat'];
        $expectedResponse = [
            'id' => 10,
            'note' => 'Customer requested window seat'
        ];

        $this->httpClient
            ->shouldReceive('post')
            ->once()
            ->with(
                "/partner/organizations/{$organizationId}/restaurants/{$restaurantId}/reservations/{$reservationId}/notes",
                $noteData
            )
            ->andReturn($expectedResponse);

        $result = $this->reservationService->addNote($organizationId, $restaurantId, $reservationId, $noteData);

        $this->assertEquals($expectedResponse, $result);
    }

    public function test_get_reservation_statistics(): void
    {
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
            ->with("/partner/reservations/statistics", $query)
            ->andReturn($expectedResponse);

        $result = $this->reservationService->statistics($query);

        $this->assertEquals($expectedResponse, $result);
        $this->assertArrayHasKey('total', $result);
    }

    public function test_cancel_reservation(): void
    {
        $organizationId = 1;
        $restaurantId = 1;
        $reservationId = 10;
        $cancelData = ['reason' => 'Customer requested cancellation'];
        $expectedResponse = [
            'id' => 10,
            'status' => 'cancelled',
            'cancellation_reason' => 'Customer requested cancellation'
        ];

        $this->httpClient
            ->shouldReceive('put')
            ->once()
            ->with(
                "/partner/organizations/{$organizationId}/restaurants/{$restaurantId}/reservations/{$reservationId}/cancel",
                $cancelData
            )
            ->andReturn($expectedResponse);

        $result = $this->reservationService->cancel($organizationId, $restaurantId, $reservationId, $cancelData);

        $this->assertEquals($expectedResponse, $result);
        $this->assertEquals('cancelled', $result['status']);
    }

    public function test_throws_exception_when_reservation_not_found(): void
    {
        $organizationId = 1;
        $restaurantId = 1;
        $reservationId = 999;

        $this->httpClient
            ->shouldReceive('get')
            ->once()
            ->with("/partner/organizations/{$organizationId}/restaurants/{$restaurantId}/reservations/{$reservationId}")
            ->andThrow(new \Exception('Not Found: Reservation not found'));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Not Found');

        $this->reservationService->get($organizationId, $restaurantId, $reservationId);
    }
}
