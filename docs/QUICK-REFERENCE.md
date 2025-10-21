# Quick Reference Guide - FOODLY Partner Panel

## 🚀 Getting Started

### Start Development Servers
```bash
# Terminal 1 - Laravel Server
php artisan serve

# Terminal 2 - Vite Dev Server (Hot reload)
npm run dev
```

### Build for Production
```bash
npm run build
```

---

## 📁 Project Structure

```
manager.foodlyapp.io/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php          # Authentication
│   │   │   ├── OrganizationController.php  # Organizations
│   │   │   ├── RestaurantController.php    # Restaurants
│   │   │   ├── ReservationController.php   # Reservations
│   │   │   ├── TableController.php         # Tables
│   │   │   └── MenuController.php          # Menu
│   │   └── Middleware/
│   │       └── PartnerAuth.php             # Auth Middleware
│   └── Services/
│       ├── AuthService.php                 # Auth Business Logic
│       └── TokenService.php                # Token Management
├── resources/
│   ├── js/
│   │   ├── components/
│   │   │   ├── AppLayout.vue              # Main SPA Layout
│   │   │   ├── Login.vue                  # Login Page
│   │   │   ├── Dashboard.vue              # Dashboard
│   │   │   ├── Bookings.vue               # Bookings Management
│   │   │   ├── Tables.vue                 # Tables Management
│   │   │   ├── Menu.vue                   # Menu Management
│   │   │   ├── Profile.vue                # User Profile ⭐ NEW
│   │   │   └── Settings.vue               # Settings
│   │   ├── router/
│   │   │   └── index.js                   # Vue Router Config
│   │   ├── locales/
│   │   │   ├── ka.json                    # Georgian Translations
│   │   │   └── en.json                    # English Translations
│   │   ├── app.js                         # Vue App Entry
│   │   └── i18n.js                        # i18n Config
│   └── views/
│       ├── login.blade.php                # Login Blade View
│       └── dashboard.blade.php            # SPA Entry Point
├── routes/
│   ├── web.php                            # Web Routes
│   └── api.php                            # API Routes (if used)
└── docs/
    ├── PROFILE-COMPONENT.md               # Profile Component Doc
    ├── ROUTING-ARCHITECTURE.md            # Routing Guide
    └── PROFILE-IMPLEMENTATION-SUMMARY.md  # Implementation Summary
```

---

## 🛣️ Routes Quick Reference

### Laravel Routes (Server-Side)

#### Public Routes
```
GET  /login              → Login page
```

#### Protected Routes (SPA)
```
GET  /{any}              → Dashboard SPA (catch-all)
```

#### API Routes (via /auth prefix)
```
POST   /auth/login                    → Login
GET    /auth/me                       → Current user
POST   /auth/logout                   → Logout
GET    /auth/initial-dashboard        → Dashboard data
GET    /auth/profile                  → Get profile
PUT    /auth/profile                  → Update profile
POST   /auth/avatar                   → Upload avatar
DELETE /auth/avatar                   → Delete avatar
PUT    /auth/password                 → Change password
```

### Vue Router (Client-Side)
```
/              → Dashboard
/bookings      → Bookings Management
/tables        → Tables Management
/menu          → Menu Management
/profile       → User Profile ⭐ NEW
/settings      → Settings
/*             → 404 → Redirect to /
```

---

## 🔐 Authentication Flow

### Login
```
1. User visits /login
2. Enters email + password
3. POST /auth/login
4. Token stored in localStorage + session
5. Redirect to / (dashboard)
```

### Access Protected Route
```
1. User visits /profile
2. Laravel catches with /{any} route
3. partner.auth middleware checks session
4. If authenticated: returns dashboard.blade.php
5. Vue app mounts & router navigates to /profile
```

### Logout
```
1. User clicks logout
2. POST /auth/logout
3. Clear localStorage + session
4. Redirect to /login
```

---

## 🎨 UI Components (PrimeVue)

### Common Components
- `Button` - Action buttons
- `Card` - Container cards
- `InputText` - Text inputs
- `Password` - Password inputs with toggle
- `Dropdown` - Select dropdowns
- `DataTable` - Data tables
- `Dialog` - Modal dialogs
- `Toast` - Notifications
- `Avatar` - User avatars
- `Tag` - Status badges

### Usage Example
```vue
<template>
  <Card>
    <template #title>Title</template>
    <template #content>
      <InputText v-model="value" />
      <Button label="Save" @click="save" />
    </template>
  </Card>
</template>
```

---

## 🌍 Internationalization (i18n)

### Usage in Components
```vue
<template>
  <h1>{{ $t('profile.title') }}</h1>
  <p>{{ $t('common.email') }}</p>
</template>

<script setup>
import { useI18n } from 'vue-i18n';
const { t } = useI18n();

console.log(t('profile.title'));
</script>
```

### Available Locales
- `ka` - Georgian (ქართული)
- `en` - English

### Translation Files
- `resources/js/locales/ka.json`
- `resources/js/locales/en.json`

---

## 📡 API Calls

### Using Axios
```javascript
import axios from 'axios';

// GET Request
const response = await axios.get('/auth/profile', {
  headers: {
    'Authorization': `Bearer ${token}`,
    'Accept': 'application/json'
  }
});

// POST Request
const response = await axios.post('/auth/login', {
  email: 'user@example.com',
  password: 'password'
});

// PUT Request
const response = await axios.put('/auth/profile', {
  name: 'New Name',
  email: 'new@email.com'
});

// DELETE Request
const response = await axios.delete('/auth/avatar');
```

### Token Management
```javascript
// Store token
localStorage.setItem('partner_token', token);

// Get token
const token = localStorage.getItem('partner_token');

// Remove token
localStorage.removeItem('partner_token');
```

---

## 🔧 Common Tasks

### Add New Page
1. Create component: `resources/js/components/NewPage.vue`
2. Add route: `resources/js/router/index.js`
3. Add translations: `resources/js/locales/*.json`
4. Add nav link: `resources/js/components/AppLayout.vue`

### Add New API Endpoint
1. Add route: `routes/web.php` or `routes/api.php`
2. Create controller method: `app/Http/Controllers/`
3. Create service method: `app/Services/`
4. Test endpoint

### Add Translation
1. Edit `resources/js/locales/ka.json`
2. Edit `resources/js/locales/en.json`
3. Use in component: `{{ $t('key.name') }}`

### Clear Cache
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

---

## 🐛 Debugging

### Check Laravel Logs
```bash
tail -f storage/logs/laravel.log
```

### Check Browser Console
- F12 → Console tab
- Look for Vue errors, API errors

### Check Network Tab
- F12 → Network tab
- Check API requests/responses
- Verify token in headers

### Common Issues

#### Issue: 404 on Page Refresh
**Solution:** Check catch-all route in `routes/web.php`

#### Issue: 401 Unauthorized
**Solution:** Check token in localStorage and API headers

#### Issue: Vue Router Not Working
**Solution:** Verify router is initialized in `app.js`

#### Issue: Assets Not Loading
**Solution:** Run `npm run build`

---

## 📋 Testing Checklist

### Before Deploying
- [ ] All routes work
- [ ] Authentication works
- [ ] Profile CRUD works
- [ ] Translations work (ka/en)
- [ ] Responsive on mobile
- [ ] API endpoints respond correctly
- [ ] Error handling works
- [ ] Build succeeds: `npm run build`
- [ ] No console errors
- [ ] Clear cache

---

## 🎯 Key Files to Know

### Backend
- `routes/web.php` - All web routes
- `app/Http/Middleware/PartnerAuth.php` - Auth middleware
- `app/Http/Controllers/AuthController.php` - Auth controller
- `app/Services/AuthService.php` - Auth business logic

### Frontend
- `resources/js/app.js` - Vue app entry
- `resources/js/router/index.js` - Vue Router
- `resources/js/components/AppLayout.vue` - Main layout
- `resources/js/locales/*.json` - Translations

### Views
- `resources/views/login.blade.php` - Login page
- `resources/views/dashboard.blade.php` - SPA entry

---

## 🚨 Emergency Commands

### Restart Everything
```bash
# Stop servers (Ctrl+C)
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Restart
php artisan serve
npm run dev
```

### Fix Node Modules
```bash
rm -rf node_modules package-lock.json
npm install
npm run dev
```

### Fix Composer
```bash
rm -rf vendor composer.lock
composer install
```

---

## 📚 Documentation Files

1. **PROFILE-COMPONENT.md** - Profile component documentation
2. **ROUTING-ARCHITECTURE.md** - Routing system explained
3. **PROFILE-IMPLEMENTATION-SUMMARY.md** - Implementation summary
4. **PARTNER-PANEL-API-DOCUMENTATION.md** - API documentation
5. **PARTNER-PANEL-TEST-RESULTS-2025-10-20.md** - Test results

---

## 💡 Tips & Tricks

1. **Hot Module Replacement (HMR)**
   - Vite automatically reloads on changes
   - Save Vue file → instant update

2. **Vue DevTools**
   - Install browser extension
   - Inspect components, router, state

3. **API Testing**
   - Use Postman or Thunder Client
   - Test endpoints before frontend integration

4. **Git Workflow**
   ```bash
   git status
   git add .
   git commit -m "message"
   git push
   ```

5. **Code Quality**
   - Use ESLint for JavaScript
   - Use PHP CS Fixer for PHP
   - Keep components small and focused

---

## 🎓 Learning Resources

- **Vue 3:** https://vuejs.org/
- **PrimeVue:** https://primevue.org/
- **Laravel:** https://laravel.com/docs
- **Vue Router:** https://router.vuejs.org/
- **Tailwind CSS:** https://tailwindcss.com/

---

## 📞 Support

For issues or questions, check:
1. Documentation files in `/docs`
2. Laravel logs: `storage/logs/laravel.log`
3. Browser console (F12)
4. Network tab (F12)

---

*Last Updated: October 21, 2025*
