# Detailed Plan: Fix Dashboard Trust Display Issues

## Overview
After completing onboarding, users are redirected to the dashboard but encounter multiple issues with trust display, data loading, and UI consistency. This plan addresses all identified problems systematically.

## Problems Identified

### 1. Dashboard Shows Zero Counts
- **Issue**: Dashboard overview shows "0" for Active Trusts and Beneficiaries
- **Root Cause**: 
  - API response structure mismatch - `handleListUserTrusts()` doesn't add back-compat fields
  - Status filtering is case-sensitive (`status === 'active'` should be case-insensitive)
  - Beneficiaries counting accesses wrong field path

### 2. "My Trusts" Stuck on Loading
- **Issue**: Trust list page shows "Loading trusts..." indefinitely
- **Root Cause**: API response format mismatch - frontend expects `trust_name` at top level but API only provides it nested in `trust_data`

### 3. "Untitled Trust" Display Issue
- **Issue**: Trusts show as "Untitled Trust" even when they have a service name
- **Root Cause**: 
  - `trust_name` is null/empty in `trust_data`
  - Frontend falls back to "Untitled Trust" instead of using `service_name` as fallback
  - Redundant display: shows both "Untitled Trust" AND "Revocable Living Trust" badge

### 4. Missing Action Buttons
- **Issue**: Only "Manage" button exists, no View/Edit/Delete buttons
- **Root Cause**: Action buttons not implemented in list view

### 5. Design Inconsistency
- **Issue**: `manage-trust.php` has completely different header/navigation than `dashboard.php`
- **Root Cause**: Different header implementation, missing navigation links, different styling

## Implementation Details

### Phase 1: Fix API Response Structure

**File**: `api/user/trusts.php`

**Location**: `handleListUserTrusts()` function (lines 214-248)

**Changes**:
1. After decoding `trust_data` JSON (line 242), add back-compat fields like `handleGetUserTrust()` does:
   ```php
   // Add after line 246 (after foreach loop)
   foreach ($trusts as &$trust) {
       $trustData = $trust['trust_data'] ?? [];
       $trust['trust_name'] = $trustData['trust_name'] ?? null;
       $trust['trust_type'] = $trustData['trust_type'] ?? ($trust['service_key'] ?? null);
       $trust['beneficiaries'] = $trustData['beneficiaries'] ?? [];
   }
   ```

**Expected Result**: API returns `trust_name`, `trust_type`, and `beneficiaries` at top level for each trust

---

### Phase 2: Fix Dashboard Trust Counting

**File**: `dashboard/user/dashboard.php`

**Location**: `loadDashboardData()` function (lines 292-337)

**Changes**:
1. **Fix status filtering** (line 300):
   ```javascript
   // Change from:
   const activeTrusts = trustsData.trusts.filter(t => (t.status || '').toLowerCase() === 'active');
   // To (already correct, but ensure it works):
   const activeTrusts = trustsData.trusts.filter(t => {
       const status = (t.status || '').toLowerCase();
       return status === 'active';
   });
   ```

2. **Fix beneficiaries counting** (lines 304-309):
   ```javascript
   // Change from:
   const totalBeneficiaries = trustsData.trusts.reduce((sum, t) => {
       const bens = t.trust_data && Array.isArray(t.trust_data.beneficiaries) ? t.trust_data.beneficiaries.length : 0;
       return sum + bens;
   }, 0);
   // To:
   const totalBeneficiaries = trustsData.trusts.reduce((sum, t) => {
       // Use top-level beneficiaries array (from API fix) or fallback to nested
       const bens = Array.isArray(t.beneficiaries) ? t.beneficiaries.length : 
                    (Array.isArray(t.trust_data?.beneficiaries) ? t.trust_data.beneficiaries.length : 0);
       return sum + bens;
   }, 0);
   ```

3. **Fix renderTrusts() function** (lines 355-390):
   - Update to use `t.trust_name` instead of `t.trust_data?.trust_name`
   - Use `t.beneficiaries` for beneficiary count
   - Ensure trust name fallback logic: `t.trust_name || t.service_name || 'Untitled Trust'`

**Expected Result**: Dashboard correctly displays trust count and beneficiary count

---

### Phase 3: Fix Manage Trust List Page

**File**: `dashboard/user/manage-trust.php`

**Location**: `loadTrusts()` function (lines 52-92)

**Changes**:
1. **Fix trust name display** (line 66):
   ```javascript
   // Change from:
   const trustName = t.trust_data?.trust_name || 'Untitled Trust';
   // To:
   const trustName = t.trust_name || t.service_name || 'Untitled Trust';
   ```

2. **Remove redundant service name badge** (line 77):
   - Only show service name badge if `trust_name` exists and is different from `service_name`
   - Or remove badge entirely and show service name as subtitle

3. **Fix beneficiaries count** (line 70):
   ```javascript
   // Change from:
   const bens = Array.isArray(t.trust_data?.beneficiaries) ? t.trust_data.beneficiaries.length : 0;
   // To:
   const bens = Array.isArray(t.beneficiaries) ? t.beneficiaries.length : 
                (Array.isArray(t.trust_data?.beneficiaries) ? t.trust_data.beneficiaries.length : 0);
   ```

4. **Add action buttons** (replace line 82):
   ```javascript
   // Replace single "Manage" button with:
   <div class="flex gap-2">
       <a href="manage-trust.php?id=${t.id}" class="px-4 py-2 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white font-semibold hover:bg-slate-50 dark:hover:bg-slate-800">View</a>
       <a href="manage-trust.php?id=${t.id}&edit=1" class="px-4 py-2 rounded-lg bg-primary text-white font-bold hover:opacity-90">Edit</a>
       <button onclick="deleteTrust(${t.id})" class="px-4 py-2 rounded-lg bg-red-600 text-white font-bold hover:bg-red-700">Delete</button>
   </div>
   ```

5. **Add deleteTrust() function** (after line 94):
   ```javascript
   async function deleteTrust(trustId) {
       if (!confirm('Are you sure you want to delete this trust? This action cannot be undone.')) {
           return;
       }
       try {
           const res = await fetch(`../../api/user/trusts.php?id=${trustId}`, {
               method: 'DELETE'
           });
           const data = await res.json();
           if (data.success) {
               loadTrusts(); // Reload list
           } else {
               alert(data.message || 'Failed to delete trust');
           }
       } catch (e) {
           console.error(e);
           alert('Error deleting trust');
       }
   }
   ```

**Expected Result**: Trust list loads correctly, shows proper names, and has View/Edit/Delete buttons

---

### Phase 4: Standardize Design - Replace Header

**File**: `dashboard/user/manage-trust.php`

**Location**: Header section (lines 16-162)

**Changes**:
1. **Replace entire header section** (lines 27-162) with header from `dashboard.php`:
   - Copy header HTML structure from `dashboard.php` lines 48-127
   - Update navigation links to include "Trusts" as active
   - Ensure mobile menu includes all navigation items
   - Match styling exactly (colors, spacing, layout)

2. **Update page wrapper** to match dashboard structure:
   - Use same container classes and layout structure
   - Match padding and spacing

3. **Ensure consistent styling**:
   - Use same Tailwind config colors
   - Match font sizes and weights
   - Use same border and shadow styles

**Expected Result**: `manage-trust.php` has identical header/navigation to `dashboard.php`

---

### Phase 5: Add DELETE API Endpoint

**File**: `api/user/trusts.php`

**Location**: Method switch statement (lines 7-24)

**Changes**:
1. **Add DELETE case**:
   ```php
   case 'DELETE':
       handleDeleteUserTrust();
       break;
   ```

2. **Add handleDeleteUserTrust() function** (after `handleUpdateUserTrust()`):
   ```php
   function handleDeleteUserTrust() {
       $userId = require_user_auth();
       $trustId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
       
       if ($trustId <= 0) {
           send_json(['success' => false, 'message' => 'Invalid trust ID'], 400);
       }
       
       $db = getDatabase();
       
       // Verify trust belongs to user
       $stmt = $db->prepare('SELECT id FROM user_trusts WHERE id = :id AND user_id = :user_id LIMIT 1');
       $stmt->execute([':id' => $trustId, ':user_id' => $userId]);
       $trust = $stmt->fetch();
       
       if (!$trust) {
           send_json(['success' => false, 'message' => 'Trust not found'], 404);
       }
       
       try {
           // Delete trust (CASCADE will handle related data if foreign keys are set up)
           $del = $db->prepare('DELETE FROM user_trusts WHERE id = :id AND user_id = :user_id');
           $del->execute([':id' => $trustId, ':user_id' => $userId]);
           
           send_json(['success' => true, 'message' => 'Trust deleted successfully']);
       } catch (Exception $e) {
           error_log('Delete user trust failed: ' . $e->getMessage());
           send_json(['success' => false, 'message' => 'Failed to delete trust'], 500);
       }
   }
   ```

**Expected Result**: DELETE endpoint allows users to delete their trusts

---

## Testing Checklist

- [ ] Dashboard shows correct trust count after creating a trust
- [ ] Dashboard shows correct beneficiary count
- [ ] "My Trusts" page loads and displays trusts correctly
- [ ] Trust names display properly (not "Untitled Trust" when service name exists)
- [ ] No redundant trust type display
- [ ] View button navigates to trust detail page
- [ ] Edit button navigates to trust detail page with edit mode
- [ ] Delete button shows confirmation and deletes trust
- [ ] Header/navigation matches dashboard exactly
- [ ] Mobile menu works consistently
- [ ] All styling matches dashboard

## Files to Modify

1. `api/user/trusts.php` - Add back-compat fields to LIST, add DELETE handler
2. `dashboard/user/dashboard.php` - Fix trust/beneficiary counting and rendering
3. `dashboard/user/manage-trust.php` - Fix loading, display, actions, and design consistency

## Estimated Impact

- **User Experience**: Significantly improved - users can see their trusts and manage them properly
- **Consistency**: Design consistency across all dashboard pages
- **Functionality**: Full CRUD operations for trusts (Create, Read, Update, Delete)
