# 🧪 QR Verification System - Testing Guide

## Quick Test Steps

### 1️⃣ Generate Test Receipt (2 minutes)

1. **Login to system**
   ```
   Navigate to: http://localhost/alchy_v2/login
   ```

2. **Go to Expenses Module**
   ```
   Click: Expenses in navigation
   ```

3. **Select a Project**
   ```
   - Find any project with expenses
   - Click "Manage" button
   - Go to "Printable Receipt" tab
   ```

4. **Download PDF**
   ```
   - Click "Download PDF" button
   - PDF should open in browser
   - Check for:
     ✅ Alchy logo in header
     ✅ QR code in top right
     ✅ Verification hash in security section
     ✅ Color-coded sections
     ✅ Professional styling
   ```

### 2️⃣ Test QR Scanner (3 minutes)

**Option A: Webcam Test**
1. Navigate to: `http://localhost/alchy_v2/receipt-scanner`
2. Click "Allow" when prompted for camera access
3. Hold QR code from PDF up to camera
4. Should auto-detect and redirect to verification page

**Option B: Manual Test**
1. Copy verification URL from PDF (under QR code)
2. Paste into "Manual Verification" input box
3. Click "Verify Receipt"
4. Should redirect to verification result

### 3️⃣ Verify Authenticity (1 minute)

**Expected Result:**
- ✅ Green success banner
- ✅ "Verified Authentic" message
- ✅ Project name displayed
- ✅ Client information shown
- ✅ Total expenses correct
- ✅ Verification count: 1
- ✅ Verification details shown

### 4️⃣ Test Tampering Detection (2 minutes)

1. **Invalid Hash Test**
   ```
   Navigate to: http://localhost/alchy_v2/verify-receipt/invalid-hash-123
   Expected: Red error banner "Verification Failed"
   ```

2. **Tampered Receipt Test**
   ```
   - Open PDF in editor
   - Change any project data
   - Try to verify QR code
   Expected: Hash won't match (if you could get past PDF protection)
   ```

## ✅ Checklist

### PDF Generation
- [ ] PDF generates without errors
- [ ] Alchy logo appears in header
- [ ] QR code is visible and clear
- [ ] Verification hash displayed
- [ ] Project data is accurate
- [ ] Material table shows expenses
- [ ] Watermark visible ("VERIFIED • AUTHENTIC")
- [ ] Footer security notice present
- [ ] Colors render correctly

### QR Scanner Page
- [ ] Page loads at `/receipt-scanner`
- [ ] Camera permission requested
- [ ] Scanner area shows video feed
- [ ] Instructions are clear
- [ ] Manual entry works
- [ ] "Verify Receipt" button on welcome page

### Verification Page
- [ ] Valid hashes show success (green)
- [ ] Invalid hashes show error (red)
- [ ] Project details display correctly
- [ ] Verification counter increments
- [ ] "Scan Another Receipt" button works
- [ ] Back to dashboard/home works

### Database
- [ ] `receipt_verifications` table exists
- [ ] Records created on PDF generation
- [ ] Hash is unique and 64 characters
- [ ] Receipt data stored as JSON
- [ ] Verification count updates
- [ ] Last verified timestamp updates

## 🐛 Common Issues & Solutions

### Issue: QR Code Doesn't Scan
**Solutions:**
1. Ensure camera permission granted
2. Try better lighting
3. Hold phone/receipt steady
4. Use manual entry as backup
5. Try different browser (Chrome recommended)

### Issue: Camera Not Working
**Solutions:**
1. Check browser permissions
2. Try HTTPS connection (camera requires secure context)
3. Use manual verification instead
4. Test on mobile device

### Issue: Verification Shows Invalid
**Possible Causes:**
1. Wrong URL/hash entered
2. Database record missing
3. Receipt from different environment
4. App key changed since generation

**Check:**
```sql
SELECT * FROM receipt_verifications 
WHERE verification_hash = 'your-hash-here';
```

### Issue: PDF Missing Logo
**Solutions:**
1. Verify file exists: `public/images/logos/alchy_logo.png`
2. Check file permissions
3. Clear PDF cache: `php artisan optimize:clear`

### Issue: Routes Not Found
**Solutions:**
```bash
php artisan route:clear
php artisan route:cache
php artisan optimize:clear
```

## 📊 Database Queries for Testing

### Check Latest Verifications
```sql
SELECT 
    rv.verification_hash,
    p.name as project_name,
    rv.generated_by,
    rv.verified_count,
    rv.generated_at,
    rv.last_verified_at
FROM receipt_verifications rv
JOIN projects p ON rv.project_id = p.id
ORDER BY rv.generated_at DESC
LIMIT 10;
```

### Check Most Verified Receipts
```sql
SELECT 
    p.name as project_name,
    c.name as client_name,
    rv.verified_count,
    rv.generated_by,
    rv.generated_at
FROM receipt_verifications rv
JOIN projects p ON rv.project_id = p.id
JOIN clients c ON p.client_id = c.id
ORDER BY rv.verified_count DESC
LIMIT 5;
```

### Check Verification Activity
```sql
SELECT 
    DATE(last_verified_at) as date,
    COUNT(*) as total_verifications
FROM receipt_verifications
WHERE last_verified_at IS NOT NULL
GROUP BY DATE(last_verified_at)
ORDER BY date DESC;
```

## 🔍 Manual Testing Script

### Test All URLs
```bash
# Logged-in routes
http://localhost/alchy_v2/expenses
http://localhost/alchy_v2/dashboard

# Public routes (no login required)
http://localhost/alchy_v2/receipt-scanner
http://localhost/alchy_v2/verify-receipt/test-hash
http://localhost/alchy_v2/
```

### Test QR Code Generation
```php
// Test in Tinker
php artisan tinker

// Generate sample QR
use SimpleSoftwareIO\QrCode\Facades\QrCode;
QrCode::size(100)->generate('https://example.com');
```

## 📱 Mobile Testing

### iOS Safari
1. Open camera app
2. Point at QR code
3. Tap notification banner
4. Should open verification page

### Android Chrome
1. Open Chrome
2. Navigate to scanner page
3. Grant camera permission
4. Scan QR code

## ✨ Success Criteria

Your implementation is successful if:

1. ✅ PDFs generate with QR codes
2. ✅ QR codes scan and redirect
3. ✅ Valid receipts show success page
4. ✅ Invalid hashes show error page
5. ✅ Database tracks verifications
6. ✅ Counter increments on each scan
7. ✅ No PHP/JavaScript errors
8. ✅ Mobile responsive design
9. ✅ Public access works (no login)
10. ✅ Professional appearance

## 🎉 Final Verification

**Complete this checklist:**

- [ ] Generated at least 3 test receipts
- [ ] Scanned QR codes successfully
- [ ] Verified receipts show correct data
- [ ] Tested invalid hash shows error
- [ ] Mobile scanning works
- [ ] Manual entry works
- [ ] Database records created
- [ ] Verification counter working
- [ ] No console errors
- [ ] Stakeholder approval

## 📞 Need Help?

If something isn't working:

1. Check browser console for JavaScript errors
2. Check Laravel logs: `storage/logs/laravel.log`
3. Verify database table exists
4. Clear all caches: `php artisan optimize:clear`
5. Restart web server
6. Check file permissions

**Common Commands:**
```bash
# Clear everything
php artisan optimize:clear

# Check routes
php artisan route:list

# Check database
php artisan migrate:status

# View logs
tail -f storage/logs/laravel.log
```

---

## ⏱️ Total Testing Time: ~10 minutes

Happy testing! 🚀
