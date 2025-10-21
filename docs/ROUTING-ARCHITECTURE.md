# Routing Architecture

## Overview
The FOODLY Partner Panel uses a hybrid routing approach:
- **Laravel Routes** (`routes/web.php`) - Handle server-side routing, authentication, and SPA entry point
- **Vue Router** (`resources/js/router/index.js`) - Handle client-side navigation within the SPA

## Architecture

### Laravel Routes (Server-Side)

#### Public Routes
- `GET /login` - Returns login page Blade view

#### SPA Catch-All Route
- `GET /{any}` - Returns dashboard Blade view for all SPA routes
  - Protected by `partner.auth` middleware
  - Excludes: `/api/*`, `/auth/*`, `/test-connection`, `/organizations/*`
  - Allows Vue Router to handle client-side navigation

#### API Routes
All API endpoints are accessible but handled separately:
- `/auth/*` - Authentication endpoints
- `/organizations/*` - Organization management
- `/restaurants/*` - Restaurant management
- `/tables/*` - Table management
- `/reservations/*` - Reservation management
- `/bookings/*` - Booking settings
- `/menu/*` - Menu management

### Vue Router (Client-Side)

All routes within the SPA:

```javascript
/ (root)              → Dashboard
/bookings             → Bookings Management
/tables               → Tables Management
/menu                 → Menu Management
/profile              → User Profile
/settings             → Settings
```

## How It Works

### 1. Initial Load
```
User visits /profile
    ↓
Laravel catches request with /{any} route
    ↓
Checks partner.auth middleware
    ↓
Returns dashboard.blade.php
    ↓
Vue app mounts with AppLayout
    ↓
Vue Router navigates to /profile
    ↓
Profile component renders
```

### 2. Client-Side Navigation
```
User clicks on /bookings link
    ↓
Vue Router intercepts navigation
    ↓
No page reload
    ↓
Bookings component renders
```

### 3. API Calls
```
Component needs data
    ↓
Makes axios request to /auth/* or /organizations/*
    ↓
Laravel routes API request to controller
    ↓
Returns JSON response
    ↓
Component updates with data
```

## Route Protection

### Server-Side (Laravel)
- Middleware: `partner.auth`
- Checks for valid session token
- Redirects to `/login` if not authenticated

### Client-Side (Vue Router)
- Meta field: `requiresAuth: true`
- Can be used for additional client-side checks
- Currently not enforced (handled by Laravel)

## Important Files

### Laravel
- `routes/web.php` - Web routes configuration
- `routes/api.php` - API routes (if used)
- `app/Http/Middleware/PartnerAuthMiddleware.php` - Authentication middleware

### Vue
- `resources/js/router/index.js` - Vue Router configuration
- `resources/js/app.js` - Vue app initialization
- `resources/views/dashboard.blade.php` - SPA entry point

## API Endpoint Patterns

### Authentication
```
POST   /auth/login              - User login
GET    /auth/me                 - Get current user
POST   /auth/logout             - User logout
GET    /auth/initial-dashboard  - Get dashboard data
GET    /auth/profile            - Get user profile
PUT    /auth/profile            - Update user profile
POST   /auth/avatar             - Upload avatar
DELETE /auth/avatar             - Delete avatar
PUT    /auth/password           - Change password
```

### Organizations
```
GET    /organizations                              - List organizations
GET    /organizations/{id}                         - Get organization
PUT    /organizations/{id}                         - Update organization
GET    /organizations/{id}/restaurants             - List restaurants
GET    /organizations/{id}/restaurants/{rid}       - Get restaurant
```

### Reservations
```
GET    /organizations/{oid}/restaurants/{rid}/reservations     - List reservations
GET    /organizations/{oid}/restaurants/{rid}/reservations/{id} - Get reservation
PUT    /organizations/{oid}/restaurants/{rid}/reservations/{id} - Update reservation
```

## Blade Views

### login.blade.php
- Standalone login page
- Uses Login.vue component
- No Vue Router

### dashboard.blade.php
- SPA entry point
- Mounts AppLayout.vue
- Uses Vue Router for all navigation

## Common Issues & Solutions

### Issue: 404 on Page Refresh
**Cause:** Laravel route doesn't catch the URL
**Solution:** Ensure catch-all route `/{any}` is at the end of web.php

### Issue: API 401 Unauthorized
**Cause:** Token not sent with request
**Solution:** Check if token is in localStorage and included in Authorization header

### Issue: Middleware Redirect Loop
**Cause:** Middleware redirecting to protected route
**Solution:** Ensure `/login` is excluded from auth middleware

### Issue: Vue Router Not Working
**Cause:** Router not initialized or wrong mode
**Solution:** Check `createWebHistory()` is used, not hash mode

### Issue: Static Assets 404
**Cause:** Vite build not found
**Solution:** Run `npm run build` and check public/build directory

## Best Practices

### DO ✅
- Use catch-all route at the end of web.php
- Exclude API and auth routes from catch-all
- Use Vue Router for SPA navigation
- Store auth token in localStorage
- Include token in all API requests
- Use middleware for route protection

### DON'T ❌
- Don't create duplicate Laravel routes for SPA pages
- Don't mix Blade views with Vue Router pages
- Don't hardcode API URLs (use relative paths)
- Don't store sensitive data in localStorage
- Don't bypass authentication middleware
- Don't forget to build assets for production

## Development Workflow

### Starting Development Server
```bash
# Terminal 1 - Laravel server
php artisan serve

# Terminal 2 - Vite dev server
npm run dev
```

### Building for Production
```bash
npm run build
```

### Testing Routes
```bash
# Test API endpoint
curl http://localhost:8000/auth/me -H "Authorization: Bearer {token}"

# Test web route
curl http://localhost:8000/profile
```

## Route Testing Checklist

- [ ] `/login` loads login page
- [ ] Unauthenticated user redirected to `/login`
- [ ] Authenticated user can access `/` (dashboard)
- [ ] All SPA routes (`/bookings`, `/tables`, etc.) return dashboard view
- [ ] Client-side navigation works without page reload
- [ ] Page refresh on any SPA route maintains the view
- [ ] API endpoints return JSON (not HTML)
- [ ] Auth middleware protects all SPA routes
- [ ] Logout redirects to `/login`
- [ ] Token persists across page refreshes

## Future Improvements

### Planned Features
- Server-side rendering (SSR) with Inertia.js
- Route-level code splitting for better performance
- Progressive Web App (PWA) support
- Deep linking with query parameters
- Route transitions and animations
- Breadcrumb navigation
- Route guards for role-based access
- 404 and error pages within SPA

### Performance Optimizations
- Lazy load route components
- Prefetch critical routes
- Cache API responses
- Implement route-based data preloading
- Add loading states during navigation
