# Semaphore SMS Integration

This document describes the SMS notification system using Semaphore API for the EMEMHS Guidance System.

## Configuration

**API Key:** `4f13582c3b12408500a7195239a591b7`  
**Sender Name:** `EMEMHS`  
**API Endpoint:** `https://api.semaphore.co/api/v4/messages`

## Features

### 1. Admin SMS Notifications

The system sends automatic SMS notifications to administrators for:

- **Daily Reminders** (Afternoon - 2 PM)
  - Unscheduled complaints/concerns
  - Unclaimed found items
  
- **Session Notifications** (Morning - 8 AM)
  - Today's scheduled counseling sessions
  
- **Urgent Notifications** (Real-time)
  - High priority or urgent complaints

**Files:**
- `logic/admin_sms_notif.php` - Manual SMS trigger for admin
- `logic/admin_sms_notifications.php` - Automatic SMS notification system

### 2. Student SMS Notifications

Students receive SMS notifications when:

- Their lost item is found (if they opted in for SMS)
- Their counseling session is scheduled (if phone number is provided)

**Files:**
- `logic/student_sms_notifications.php` - Student SMS notification handler
- `pages/notify_student.php` - Triggers SMS when admin marks item as found

## Testing

### Test Semaphore API Connection

Access the test file to verify the API is working:

```
http://your-domain/logic/test_semaphore.php
```

**Note:** Update the test phone number in `logic/test_semaphore.php` before testing.

### Test Admin SMS

Access the admin dashboard and click the "Send SMS Notification" button to manually trigger an SMS.

## Phone Number Format

Semaphore API accepts Philippine mobile numbers in the following formats:
- `09XXXXXXXXX` (11 digits starting with 09)
- `639XXXXXXXXX` (12 digits starting with 639)

The system stores numbers as entered by users.

## SMS Message Limits

- Maximum 160 characters per SMS (standard)
- Messages longer than 160 characters are automatically split
- Semaphore charges per 160-character segment

## Rate Limits

According to Semaphore documentation:
- `/api/v4/messages` endpoint: 120 calls per minute
- Priority and OTP endpoints: Not rate limited

## Error Handling

The system logs all SMS errors to PHP error log:
- Connection failures
- API errors
- Invalid phone numbers
- Missing configuration

Check your server's error log for debugging.

## Files Modified

1. `logic/admin_sms_notif.php` - Changed from TextBee to Semaphore
2. `logic/admin_sms_notifications.php` - Changed from TextBee to Semaphore
3. `logic/student_sms_notifications.php` - New file for student SMS
4. `pages/notify_student.php` - Added SMS notification when item is found

## Security Notes

- API key is stored in PHP files (not exposed to client-side)
- Consider moving API key to environment variables for production
- Validate phone numbers before sending SMS
- Implement rate limiting to prevent abuse

## Future Enhancements

- Move API credentials to environment variables
- Add SMS delivery status tracking
- Implement SMS templates for easier management
- Add SMS history/logs in database
- Support for bulk SMS to multiple students
