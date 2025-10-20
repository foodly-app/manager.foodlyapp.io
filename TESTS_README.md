# Laravel Tests Documentation

## Overview

ეს პროექტი შეიცავს სრულყოფილ Unit და Feature ტესტებს ყველა სერვისისა და კონტროლერისთვის.

## Test Structure

### Unit Tests (`tests/Unit/`)

Unit ტესტები ამოწმებენ ცალკეულ კომპონენტებს იზოლირებულად:

1. **TokenStorageTest** - ტოკენის შენახვის ლოგიკა
2. **TokenServiceTest** - ტოკენის გენერაცია და ვალიდაცია
3. **HttpClientTest** - HTTP მოთხოვნების დამუშავება
4. **AuthServiceTest** - ავტორიზაციის სერვისი
5. **OrganizationServiceTest** - ორგანიზაციების მენეჯმენტი
6. **RestaurantServiceTest** - რესტორნების მენეჯმენტი
7. **ReservationServiceTest** - რეზერვაციების მენეჯმენტი

### Feature Tests (`tests/Feature/`)

Feature ტესტები ამოწმებენ API endpoints-ებს:

1. **AuthControllerTest** - Authentication endpoints
   - POST `/api/login` - მომხმარებლის ავტორიზაცია
   - GET `/api/me` - მიმდინარე მომხმარებლის ინფორმაცია
   - POST `/api/logout` - გასვლა

2. **OrganizationControllerTest** - Organization endpoints
   - GET `/api/organizations` - ორგანიზაციების სია
   - GET `/api/organizations/{id}` - ორგანიზაციის დეტალები
   - PUT `/api/organizations/{id}` - ორგანიზაციის განახლება
   - GET `/api/organizations/{id}/statistics` - სტატისტიკა

3. **RestaurantControllerTest** - Restaurant endpoints
   - GET `/api/restaurants` - რესტორნების სია
   - GET `/api/restaurants/{id}` - რესტორნის დეტალები
   - PUT `/api/restaurants/{id}` - რესტორნის განახლება
   - GET `/api/restaurants/{id}/tables` - მაგიდების სია
   - GET `/api/restaurants/{id}/statistics` - სტატისტიკა

4. **ReservationControllerTest** - Reservation endpoints
   - GET `/api/restaurants/{restaurantId}/reservations` - რეზერვაციების სია
   - GET `/api/restaurants/{restaurantId}/reservations/today` - დღევანდელი რეზერვაციები
   - GET `/api/restaurants/{restaurantId}/reservations/upcoming` - მომავალი რეზერვაციები
   - PUT `/api/restaurants/{restaurantId}/reservations/{id}/status` - სტატუსის განახლება
   - POST `/api/restaurants/{restaurantId}/reservations/{id}/notes` - შენიშვნის დამატება
   - POST `/api/restaurants/{restaurantId}/reservations/{id}/cancel` - რეზერვაციის გაუქმება
   - GET `/api/restaurants/{restaurantId}/reservations/statistics` - სტატისტიკა

## Running Tests

### ყველა ტესტის გაშვება

```bash
php artisan test
```

ან Pest-ით:

```bash
./vendor/bin/pest
```

### მხოლოდ Unit ტესტების გაშვება

```bash
php artisan test --testsuite=Unit
```

ან:

```bash
./vendor/bin/pest tests/Unit
```

### მხოლოდ Feature ტესტების გაშვება

```bash
php artisan test --testsuite=Feature
```

ან:

```bash
./vendor/bin/pest tests/Feature
```

### კონკრეტული ფაილის ტესტირება

```bash
php artisan test tests/Unit/TokenStorageTest.php
```

ან:

```bash
./vendor/bin/pest tests/Unit/TokenStorageTest.php
```

### კონკრეტული ტესტის გაშვება

```bash
php artisan test --filter test_can_save_token
```

### Code Coverage

Code coverage-ის გენერაცია:

```bash
php artisan test --coverage
```

დეტალური coverage ანგარიში:

```bash
php artisan test --coverage --min=80
```

HTML coverage რეპორტის გენერაცია:

```bash
./vendor/bin/pest --coverage-html coverage
```

## Test Configuration

### Environment Setup

ტესტები იყენებენ `.env.testing` ფაილს (თუ არსებობს) ან `.env` ფაილს.

### Mocking

პროექტში გამოყენებულია Mockery library მოქების (mocking) შესაქმნელად:

```php
$this->httpClient = $this->mock(HttpClient::class);

$this->httpClient
    ->shouldReceive('get')
    ->once()
    ->with('/endpoint')
    ->andReturn(['data' => 'test']);
```

### HTTP Testing

Laravel-ის HTTP testing helpers გამოყენებულია:

```php
$response = $this->getJson('/api/endpoint');
$response->assertStatus(200)
    ->assertJson(['success' => true]);
```

## Writing New Tests

### Unit Test Example

```php
<?php

namespace Tests\Unit;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_example_function(): void
    {
        // Arrange
        $expectedResult = 'expected';
        
        // Act
        $result = exampleFunction();
        
        // Assert
        $this->assertEquals($expectedResult, $result);
    }
}
```

### Feature Test Example

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleControllerTest extends TestCase
{
    public function test_endpoint_returns_success(): void
    {
        $response = $this->getJson('/api/endpoint');
        
        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);
    }
}
```

## Best Practices

1. **Test Naming** - გამოიყენეთ აღწერითი სახელები: `test_can_save_token`, `test_login_validation_fails_without_email`

2. **Arrange-Act-Assert** - დაიცავით AAA პატერნი თითოეულ ტესტში

3. **One Assertion per Test** - შეეცადეთ თითოეულ ტესტში მხოლოდ ერთი ლოგიკური კონცეფცია შეამოწმოთ

4. **Mock External Dependencies** - ყოველთვის მოქეთ გარე დამოკიდებულებები (HTTP calls, Database, etc.)

5. **Test Edge Cases** - ტესტირება უნდა მოიცავდეს როგორც წარმატებულ, ისე შეცდომის შემთხვევებს

## Continuous Integration

ტესტების ინტეგრაცია CI/CD პროცესში:

```yaml
# .github/workflows/tests.yml
name: Tests

on: [push, pull_request]

jobs:
  tests:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - name: Install Dependencies
        run: composer install
      - name: Run Tests
        run: php artisan test
```

## Troubleshooting

### Common Issues

1. **Mock შეცდომები** - დარწმუნდით რომ Mockery სწორად არის დაინსტალირებული
2. **HTTP Fake** - Laravel HTTP Facade fake გამოიყენეთ API მოთხოვნების მოქებისთვის
3. **Database** - გამოიყენეთ in-memory SQLite database ტესტებისთვის

### Debug Mode

დეტალური ინფორმაციის მისაღებად:

```bash
php artisan test --verbose
```

## Test Statistics

- **Unit Tests**: 7 ფაილი (~60+ ტესტი)
- **Feature Tests**: 4 ფაილი (~50+ ტესტი)
- **Total Coverage**: ყველა მთავარი სერვისი და კონტროლერი

## Additional Resources

- [Laravel Testing Documentation](https://laravel.com/docs/testing)
- [Pest PHP Documentation](https://pestphp.com/)
- [Mockery Documentation](http://docs.mockery.io/)
