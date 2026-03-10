# SMS Notification Troubleshooting Guide

## Problem: SMS not being sent when admin marks item as found

### Common Causes:

1. **Semaphore API Key not configured**
2. **receive_sms checkbox value not stored correctly**
3. **Phone number format incorrect**
4. **cURL not available on server**
5. **InfinityFree blocking outgoing connections**

---

## Step-by-Step Troubleshooting:

### 1. Run the Test Script

Upload and run `test_sms_config.php` in your browser:
```
https://ememhsguidance.infinityfreeapp.com/test_sms_config.php
```

This will show you:
- ✓ If Semaphore API is configured
- ✓ If cURL is available
- ✓ Which lost items have SMS enabled
- ✓ Phone number formats

### 2. Check Semaphore API Key

Open `config.php` and verify:
```php
define('SEMAPHORE_API_KEY', 'YOUR_ACTUAL_API_KEY_HERE'); // NOT the placeholder!
```

Get your API key from: https://semaphore.co/

### 3. Check Database Values

The issue might be that `receive_sms` is stored as "0" (string) instead of 1 (integer).

Run this SQL query in phpMyAdmin:
```sql
SELECT id, item_name, receive_sms, phone_number, status 
FROM lost_items 
ORDER BY id DESC 
LIMIT 10;
```

**What to look for:**
- `receive_sms` should be `1` (not `0`, not empty, not NULL)
- `phone_number` should be in format: `09XXXXXXXXX` or `639XXXXXXXXX`

### 4. Fix Existing Data (if needed)

If you have items with `receive_sms` = "0" that should be "1", run:
```sql
UPDATE lost_items 
SET receive_sms = 1 
WHERE id = YOUR_ITEM_ID;
```

### 5. Check Error Logs

On InfinityFree, check the `error_log.txt` file in your root directory.

Look for lines like:
```
SMS Configuration loaded - API Key present: YES
receive_sms value: 1
phone_number: 09123456789
Sending SMS to: 639123456789
SMS sent successfully via cURL!
```

### 6. Test with a New Lost Item Report

1. As a student, report a new lost item
2. **CHECK the "I want to receive SMS updates" checkbox**
3. Enter your phone number (09XXXXXXXXX format)
4. Submit the report
5. As admin, mark it as "found" and click "Notify Student"

### 7. Check InfinityFree Restrictions

InfinityFree may block outgoing HTTP connections. If cURL fails, you'll see:
```
cURL failed: HTTP 0 - Could not resolve host
```

**Solution:** You may need to upgrade to premium hosting or use a different SMS provider that InfinityFree allows.

---

## Common Issues & Solutions:

### Issue 1: "Student did not opt-in for SMS"
**Cause:** The checkbox wasn't checked OR the value is stored as "0"
**Solution:** 
- Make sure to check the checkbox when reporting
- Or update the database: `UPDATE lost_items SET receive_sms = 1 WHERE id = X`

### Issue 2: "Invalid phone number format"
**Cause:** Phone number not in correct format
**Solution:** Use format `09XXXXXXXXX` (11 digits starting with 09)

### Issue 3: "cURL not available"
**Cause:** Server doesn't have cURL extension
**Solution:** Contact InfinityFree support or upgrade hosting

### Issue 4: "Failed to send SMS" but API key is correct
**Cause:** InfinityFree blocking outgoing connections
**Solution:** 
- Check Semaphore account balance
- Verify API key is correct
- May need premium hosting for external API calls

### Issue 5: SMS sent but not received
**Cause:** Semaphore account issue or wrong phone number
**Solution:**
- Check Semaphore dashboard for delivery status
- Verify phone number is correct and active
- Check if you have SMS credits

---

## Testing Checklist:

- [ ] Semaphore API key is configured in config.php
- [ ] API key is NOT the placeholder "YOUR_SEMAPHORE_API_KEY_HERE"
- [ ] cURL is available on server
- [ ] Lost item has `receive_sms = 1` in database
- [ ] Phone number is in correct format (09XXXXXXXXX)
- [ ] Semaphore account has credits
- [ ] Error logs show "SMS sent successfully"
- [ ] InfinityFree is not blocking outgoing connections

---

## Still Not Working?

1. Check `error_log.txt` for detailed error messages
2. Run `test_sms_config.php` to see configuration status
3. Test Semaphore API directly: https://semaphore.co/docs
4. Consider upgrading from free hosting if InfinityFree blocks external APIs
