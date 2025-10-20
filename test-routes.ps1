# PowerShell Script to Test Routes
# Usage: .\test-routes.ps1

$baseUrl = "http://localhost:8000"

Write-Host "🚀 Testing Routes..." -ForegroundColor Cyan

# Test Connection
Write-Host "`n1. Testing Connection..." -ForegroundColor Yellow
try {
    $response = Invoke-RestMethod -Uri "$baseUrl/test-connection" -Method Get
    Write-Host "✅ Connection: $($response.message)" -ForegroundColor Green
} catch {
    Write-Host "❌ Connection Failed: $_" -ForegroundColor Red
}

# Test Auth Login (POST)
Write-Host "`n2. Testing Auth Login..." -ForegroundColor Yellow
$loginData = @{
    email = "test@example.com"
    password = "password"
} | ConvertTo-Json

try {
    $response = Invoke-RestMethod -Uri "$baseUrl/auth/login" -Method Post -Body $loginData -ContentType "application/json"
    Write-Host "✅ Login: Success" -ForegroundColor Green
    $token = $response.token
} catch {
    Write-Host "❌ Login Failed: $_" -ForegroundColor Red
}

# Test Organizations (GET)
Write-Host "`n3. Testing Organizations..." -ForegroundColor Yellow
try {
    $headers = @{
        "Authorization" = "Bearer $token"
    }
    $response = Invoke-RestMethod -Uri "$baseUrl/organizations" -Method Get -Headers $headers
    Write-Host "✅ Organizations: Success" -ForegroundColor Green
} catch {
    Write-Host "❌ Organizations Failed: $_" -ForegroundColor Red
}

# Test Restaurants (GET)
Write-Host "`n4. Testing Restaurants..." -ForegroundColor Yellow
try {
    $response = Invoke-RestMethod -Uri "$baseUrl/organizations/1/restaurants" -Method Get -Headers $headers
    Write-Host "✅ Restaurants: Success" -ForegroundColor Green
} catch {
    Write-Host "❌ Restaurants Failed: $_" -ForegroundColor Red
}

# Test Places (GET)
Write-Host "`n5. Testing Places..." -ForegroundColor Yellow
try {
    $response = Invoke-RestMethod -Uri "$baseUrl/organizations/1/restaurants/1/places" -Method Get -Headers $headers
    Write-Host "✅ Places: Success" -ForegroundColor Green
} catch {
    Write-Host "❌ Places Failed: $_" -ForegroundColor Red
}

# Test Tables (GET)
Write-Host "`n6. Testing Tables..." -ForegroundColor Yellow
try {
    $response = Invoke-RestMethod -Uri "$baseUrl/organizations/1/restaurants/1/tables" -Method Get -Headers $headers
    Write-Host "✅ Tables: Success" -ForegroundColor Green
} catch {
    Write-Host "❌ Tables Failed: $_" -ForegroundColor Red
}

# Test Reservations (GET)
Write-Host "`n7. Testing Reservations..." -ForegroundColor Yellow
try {
    $response = Invoke-RestMethod -Uri "$baseUrl/reservations" -Method Get -Headers $headers
    Write-Host "✅ Reservations: Success" -ForegroundColor Green
} catch {
    Write-Host "❌ Reservations Failed: $_" -ForegroundColor Red
}

# Test Booking Settings (GET)
Write-Host "`n8. Testing Booking Settings..." -ForegroundColor Yellow
try {
    $response = Invoke-RestMethod -Uri "$baseUrl/organizations/1/restaurants/1/booking/settings" -Method Get -Headers $headers
    Write-Host "✅ Booking Settings: Success" -ForegroundColor Green
} catch {
    Write-Host "❌ Booking Settings Failed: $_" -ForegroundColor Red
}

# Test Menu (GET)
Write-Host "`n9. Testing Menu..." -ForegroundColor Yellow
try {
    $response = Invoke-RestMethod -Uri "$baseUrl/organizations/1/restaurants/1/menu" -Method Get -Headers $headers
    Write-Host "✅ Menu: Success" -ForegroundColor Green
} catch {
    Write-Host "❌ Menu Failed: $_" -ForegroundColor Red
}

Write-Host "`n✨ Testing Complete!" -ForegroundColor Cyan
