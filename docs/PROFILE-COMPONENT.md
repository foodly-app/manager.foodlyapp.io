# Profile Component Documentation

## Overview
User profile management component for the FOODLY Partner Panel. Allows users to view and edit their personal information, manage their avatar, and change their password.

## Features

### 1. Profile Information Display
- User avatar (with initials fallback)
- User name and email
- Organization affiliation with status badge
- Account creation date
- Organization join date

### 2. Profile Editing
- **Editable Fields:**
  - Name (required)
  - Email (required)
  - Phone (optional)
  - Language preference (Georgian/English)

- **Edit Mode:**
  - Click "Edit" button to enter edit mode
  - Form validation on all fields
  - Cancel button to discard changes
  - Save button to submit changes

### 3. Avatar Management
- **Upload Avatar:**
  - Click camera icon to upload
  - Supported formats: image files
  - Maximum size: 2MB
  - Automatic validation

- **Delete Avatar:**
  - Click trash icon to remove
  - Confirmation dialog before deletion
  - Returns to initials display

### 4. Password Management
- Change password dialog
- Required fields:
  - Current password
  - New password (minimum 8 characters)
  - Password confirmation
- Real-time validation
- Password mismatch detection

### 5. Account Details Section
- User ID display
- Member since date
- Organization name and status
- Joined organization date
- Status badge (active/inactive/suspended)

## API Endpoints Used

### GET `/api/partner/profile`
Fetches user profile data including:
```json
{
  "success": true,
  "user": {
    "id": 12,
    "name": "Tamar | Manager",
    "email": "manager@exodusrestaurant.com",
    "phone": null,
    "avatar": null,
    "created_at": "2025-10-09T20:31:46.000000Z",
    "organizations": [
      {
        "id": 1,
        "name": "EXODUS LLC",
        "status": "active",
        "joined_at": "2025-10-20 21:10:19"
      }
    ]
  }
}
```

### PUT `/api/partner/profile`
Updates user profile with fields:
- `name` (string, max 255)
- `email` (email, max 255)
- `phone` (string, max 20, optional)
- `language` (string, 'ka' or 'en', optional)

### POST `/api/partner/avatar`
Uploads user avatar:
- Field: `avatar` (file)
- Max size: 2MB
- Returns: `avatar_url`

### DELETE `/api/partner/avatar`
Deletes user avatar

### PUT `/api/partner/password`
Changes user password with fields:
- `current_password` (required)
- `new_password` (required, min 8)
- `new_password_confirmation` (required, must match)

## Components Used

### PrimeVue Components
- `Card` - Container cards for different sections
- `Button` - Action buttons
- `InputText` - Text input fields
- `Dropdown` - Language selector
- `Avatar` - User avatar display
- `Tag` - Status badges
- `Dialog` - Modal dialogs
- `Password` - Password input with toggle
- `Toast` - Notification messages

### Custom Components
- `FlagGeorgia` - Georgian flag icon
- `FlagUK` - UK/English flag icon

## Styling

### Layout
- Maximum width: 1200px
- Responsive grid layout
- Mobile-friendly (stacks on small screens)

### Color Scheme
- Avatar card: Purple gradient background (`#667eea` → `#764ba2`)
- Info cards: White with subtle shadows
- Status badges: Color-coded (success/warning/danger)

### Responsive Breakpoints
- Desktop: Grid layout with multiple columns
- Mobile (<768px): Single column layout, centered content

## Internationalization (i18n)

### Translation Keys
All text is internationalized with keys under:
- `profile.*` - Profile-specific translations
- `common.*` - Common UI elements
- `validation.*` - Form validation messages

### Supported Languages
- Georgian (ka)
- English (en)

## State Management

### Reactive State
- `user` - User profile data
- `form` - Editable form data
- `errors` - Form validation errors
- `passwordForm` - Password change form data
- `passwordErrors` - Password validation errors

### Loading States
- `isEditing` - Edit mode toggle
- `isUpdating` - Profile update in progress
- `uploadingAvatar` - Avatar upload in progress
- `deletingAvatar` - Avatar deletion in progress
- `isChangingPassword` - Password change in progress

### Dialog States
- `showPasswordDialog` - Password change modal
- `showDeleteAvatarDialog` - Avatar deletion confirmation

## Validation

### Profile Form
- Name: Required, non-empty
- Email: Required, valid email format
- Phone: Optional, max 20 characters
- Language: Optional, must be 'ka' or 'en'

### Password Form
- Current password: Required
- New password: Required, minimum 8 characters
- Confirmation: Required, must match new password

## Error Handling
- API errors displayed via toast notifications
- Field-level validation errors shown inline
- Network error handling with user-friendly messages
- Automatic error clearing on form submission

## Usage

### Navigation
Access the profile page via:
1. User icon in top header
2. Profile link in sidebar navigation
3. Direct route: `/profile`

### Workflow
1. Page loads and fetches profile data
2. User views their information
3. Click "Edit" to modify profile
4. Make changes and click "Save"
5. Or click "Cancel" to discard changes

### Avatar Upload Workflow
1. Click camera icon
2. Select image file (max 2MB)
3. Automatic upload and preview
4. Toast notification on success/error

### Password Change Workflow
1. Click "Change Password" button
2. Dialog opens with password form
3. Enter current and new passwords
4. Click "Save" to submit
5. Success notification and dialog closes

## Best Practices

### Security
- Passwords never displayed in plain text
- Current password required for password change
- Token-based authentication (Bearer token)
- Secure API communication

### UX
- Immediate visual feedback on actions
- Loading states prevent duplicate submissions
- Confirmation dialogs for destructive actions
- Auto-populated form fields from user data
- Clear error messages

### Performance
- Single API call on mount
- Optimized form state management
- Efficient reactive updates
- Lazy-loaded components

## Future Enhancements

### Potential Features
- Two-factor authentication setup
- Email notification preferences
- Session management (view active sessions)
- Activity log
- Profile completion percentage
- Social media links
- Bio/description field
- Profile visibility settings

### Improvements
- Avatar cropper before upload
- Drag-and-drop avatar upload
- Password strength indicator
- Email verification status
- Phone number verification
- Advanced security settings
- Export personal data feature
