# 🔧 Messaging System - Quick Troubleshooting

## Quick Checks ✓

### 1. Is Pusher Connected?
```bash
php artisan pusher:test
```
**Expected:** All checkmarks (✓) with no errors

### 2. Is Vite Running?
```bash
npm run dev
# OR for production:
npm run build
```
**Expected:** Assets compiled without errors

### 3. Check Browser Console (F12)
**Look for:**
- `[Echo] ✓ Connected to Pusher` - Good!
- `[Chat] ✓ Subscribed to user channel` - Good!
- Any red errors - Need to fix!

---

## Common Problems & Quick Fixes 🚑

### Problem: "Echo not initialized"
**Fix:**
```bash
# Stop and restart Vite
npm run dev
# Refresh browser (Ctrl+F5)
```

### Problem: Messages not sending
**Check:**
1. Are you logged in? (Check top-right corner)
2. Did you select a user to chat with?
3. Browser console errors? (F12)

**Fix:**
```bash
# Clear cache
php artisan optimize:clear
# Restart everything
```

### Problem: "Subscription failed" or "403" errors
**Fix:**
```bash
# Check if broadcasting routes are registered
php artisan route:list | grep broadcasting
# Should show: POST broadcasting/auth
```

If missing, verify `routes/web.php` has:
```php
Broadcast::routes(['middleware' => ['auth']]);
```

### Problem: Messages delayed or not appearing
**Check Pusher credentials:**
```bash
# View current config
php artisan tinker
>>> config('broadcasting.connections.pusher')
```

**Verify in `.env`:**
- BROADCAST_CONNECTION=pusher
- PUSHER_APP_ID (set)
- PUSHER_APP_KEY (set)
- PUSHER_APP_SECRET (set)
- PUSHER_APP_CLUSTER=ap1

### Problem: Browser notifications not working
**Steps:**
1. Click the 🔔 icon in browser address bar
2. Allow notifications
3. Refresh page (F5)
4. Try again

---

## Emergency Reset 🔄

If nothing works, do a complete reset:

```bash
# 1. Clear all caches
php artisan optimize:clear

# 2. Rebuild assets
npm run build

# 3. Clear browser cache
# Press Ctrl+Shift+Delete, clear cache

# 4. Restart browser

# 5. Test connection
php artisan pusher:test

# 6. Try messaging again
```

---

## Logs & Debugging 📝

### Check Laravel Logs
```bash
# Windows
type storage\logs\laravel.log

# View last 50 lines
Get-Content storage\logs\laravel.log -Tail 50
```

### Enable Debug Mode (Temporarily)
In `.env`:
```env
APP_DEBUG=true
```
**Remember:** Turn off after debugging!

### Test Direct Broadcast
```bash
php artisan tinker
```
```php
$user = \App\Models\User::first();
$chat = \App\Models\Chat::create([
    'user_id' => $user->id,
    'recipient_id' => $user->id,
    'message' => 'Test'
]);
broadcast(new \App\Events\MessageSent($chat));
```

---

## Performance Issues 🐌

### Chat widget slow to open?
**Check:**
1. Too many users? (Add pagination)
2. Slow database? Run: `php artisan optimize`
3. Check database indexes: `SHOW INDEX FROM chats`

### Messages loading slowly?
- Check internet connection
- Pusher might be experiencing issues: https://status.pusher.com
- Too many messages? Clear old ones

---

## Getting Help 🆘

### Before asking for help, collect this info:

1. **Error message** (exact text)
2. **Browser console log** (F12 → Console tab, copy errors)
3. **Laravel log** (last 20 lines from `storage/logs/laravel.log`)
4. **Test result:** Output of `php artisan pusher:test`
5. **Browser used:** Chrome/Firefox/Safari/Edge + version
6. **What you were doing** when the error occurred

### Useful Commands

```bash
# Show all routes
php artisan route:list

# Check queue status
php artisan queue:work --once

# View config
php artisan config:show broadcasting

# Clear everything
php artisan optimize:clear && npm run build
```

---

## Still Not Working? ⚠️

### Nuclear Option (Last Resort):

```bash
# 1. Backup database
php artisan db:backup

# 2. Clear everything
php artisan optimize:clear
composer dump-autoload
npm ci
npm run build

# 3. Restart services
# Stop PHP server (Ctrl+C)
# Stop Vite (Ctrl+C)

# 4. Start fresh
php artisan serve
# In another terminal:
npm run dev

# 5. Test
php artisan pusher:test
```

If this doesn't work, check:
- Firewall blocking port 443?
- Antivirus blocking WebSocket?
- Corporate network restrictions?

---

## Success Indicators ✅

You know it's working when:

1. **Test command passes:**
   ```
   ✅ Pusher is properly configured!
   ```

2. **Browser console shows:**
   ```
   [Echo] ✓ Connected to Pusher
   [Chat] ✓ Subscribed to user channel
   ```

3. **Send a message:**
   - Message appears immediately
   - Other user sees it in real-time
   - No errors in console

---

**Quick Access:**
- Full Guide: `MESSAGING_GUIDE.md`
- Test Command: `php artisan pusher:test`
- Laravel Logs: `storage/logs/laravel.log`
