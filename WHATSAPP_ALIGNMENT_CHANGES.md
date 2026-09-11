# WhatsApp Template Alignment - Summary of Changes

## Overview
Aligned the application's WhatsApp sending logic with the working curl examples provided by the user. All three template types (student, parent, RSVP) now work consistently across all three languages (en, ku, ar).

## Changes Made

### 1. Button Parameter Logic (`app/Services/Messaging/MessageDispatcher.php`)
**Changed:** `badgeLinkParam()` method now differentiates between RSVP and fair registrations
- **RSVP (Conference)**: Returns `/{ticket_id}/badge.png` (WITH leading slash)
- **Student/Parent (Fair)**: Returns `{ticket_id}/badge.png` (WITHOUT leading slash)

```php
public function badgeLinkParam(Registration $registration): string
{
    $param = $registration->ticket_id.'/badge.png';
    
    return $registration->isConference() ? '/'.$param : $param;
}
```

### 2. RSVP Body Parameters Standardization
**Changed:** All three languages now use only `name` parameter (removed `ticket`)

#### Files Updated:
- **`config/whatsapp.php`**: Changed Kurdish RSVP from `['name', 'ticket']` to `['name']`
- **`app/Filament/Support/RegistrationActions.php`**: Removed `ticket` parameter in both approval and resend actions
- **`database/seeders/MessageTemplateSeeder.php`**: Added `rsvp_confirmed` with only `['name']`
- **`database/seeders/DemoDataSeeder.php`**: Removed `ticket` from RSVP preview
- **`tests/Feature/OtpiqWhatsAppTest.php`**: Updated test expectations

### 3. Final Parameter Structure

#### Student Registration (`registration_confirmed_student`)
```json
{
  "templateParameters": {
    "body": { "1": "HAMA" },
    "buttons": { "0": { "1": "6bdf0806.../badge.png" } },
    "header": { "imageUrl": "https://demi.nextstepfair.com/ticket/6bdf0806.../badge.png" }
  }
}
```

#### Parent Registration (`registration_confirmed_parent`)
```json
{
  "templateParameters": {
    "body": { "1": "Mohammed" },
    "buttons": { "0": { "1": "6bdf0806.../badge.png" } },
    "header": { "imageUrl": "https://demi.nextstepfair.com/ticket/6bdf0806.../badge.png" }
  }
}
```

#### RSVP Confirmation (`rsvp_confirmed`)
```json
{
  "templateParameters": {
    "body": { "1": "ALi" },
    "buttons": { "0": { "1": "/6bdf0806.../badge.png" } },
    "header": { "imageUrl": "https://demi.nextstepfair.com/ticket/6bdf0806.../badge.png" }
  }
}
```

## Sending Logic (Unchanged)
✅ **RSVP**: Sends after admin approval (confirm action)
✅ **Student/Parent**: Sends immediately after registration completion

## Deployment Instructions

### On the Server:

1. **Pull the changes**
   ```bash
   cd /var/www/html/demi.nextstepfair.com
   git pull
   ```

2. **Run the OtpiqTemplateSeeder** (to update Kurdish RSVP body_variables)
   ```bash
   php artisan db:seed --class=OtpiqTemplateSeeder
   ```

3. **Clear config cache**
   ```bash
   php artisan config:clear
   ```

4. **Restart the queue workers**
   ```bash
   php artisan queue:restart
   ```

5. **Verify in Filament Admin**
   - Go to OTPIQ Templates
   - Check that all three `rsvp_confirmed` templates show `body_variables: ["name"]`
   - Check that all student/parent templates show the same

## Tests
✅ All 18 tests in `OtpiqWhatsAppTest.php` pass
✅ All parameter structures match the working curl examples

## Notes
- All three languages (en, ku, ar) now use identical parameter structures for each template type
- Button parameter difference (leading slash for RSVP only) is intentional and matches approved OTPIQ templates
- Badge link always points to `.../badge.png` (not PDF)
