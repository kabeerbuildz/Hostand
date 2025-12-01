# New Database Fields Required

## Summary
This document lists all new fields that need to be added to the database based on the property creation form updates.

---

## Property Table (`properties`)
**Status:** ✅ All fields already exist in the migration `2024_01_15_000001_add_new_fields_to_properties_table.php`

The following fields are already in the database:
- `piano` (Floor) - string, nullable
- `staircase` - string, nullable
- `access_other` (Internal Access Description) - string, nullable
- `sign_detail` (Name on Doorbell) - string, nullable
- `opening_type` - enum('key', 'code'), nullable
- `street_code` (Street Opening Code) - string, nullable
- `door_code` (Door Opening Code) - string, nullable
- `key_description` (Key Description) - string, nullable

**Action Required:** ✅ None - All property fields already exist

---

## Property Units Table (`property_units`)
**Status:** ❌ NEW FIELDS NEED TO BE ADDED

The following new fields need to be added to the `property_units` table:

### 1. `bedroom_type`
- **Type:** `enum('double', 'triple', 'quadruple', 'quintuple')`
- **Nullable:** Yes
- **Description:** Bedroom type for BNB/HOTEL properties (Double, Triple, Quadruple, Quintuple)
- **Used for:** BNB/GUESTHOUSE (AFFITTACAMERE) or HOTEL property types

### 2. `piano`
- **Type:** `string(191)`
- **Nullable:** Yes
- **Description:** Floor number/name for the unit
- **Used for:** All property types except Holiday Home

### 3. `staircase`
- **Type:** `string(191)`
- **Nullable:** Yes
- **Description:** Staircase information for the unit
- **Used for:** Regular properties (hidden for Holiday Home and BNB/HOTEL)

### 4. `sign_detail`
- **Type:** `string(255)`
- **Nullable:** Yes
- **Description:** Plate/Identifying Detail on door or other identifying information
- **Used for:** Regular properties (hidden for Holiday Home and BNB/HOTEL)

### 5. `description`
- **Type:** `text`
- **Nullable:** Yes
- **Description:** Unit description
- **Used for:** Regular properties (hidden for BNB/HOTEL)

### 6. `opening_type`
- **Type:** `enum('key', 'code')`
- **Nullable:** Yes
- **Description:** Opening type for the unit (Key or Code)
- **Used for:** Regular properties and BNB/HOTEL (hidden for Holiday Home)
- **Note:** Already being saved in controller, but may need to be added to table if not exists

### 7. `street_code`
- **Type:** `string(191)`
- **Nullable:** Yes
- **Description:** Street opening code (when opening_type is 'code')
- **Used for:** Regular properties and BNB/HOTEL
- **Note:** Already being saved in controller, but may need to be added to table if not exists

### 8. `door_code`
- **Type:** `string(191)`
- **Nullable:** Yes
- **Description:** Door opening code (when opening_type is 'code')
- **Used for:** Regular properties and BNB/HOTEL
- **Note:** Already being saved in controller, but may need to be added to table if not exists

### 9. `key_description`
- **Type:** `string(255)`
- **Nullable:** Yes
- **Description:** Key description (when opening_type is 'key' or for code access)
- **Used for:** Regular properties and BNB/HOTEL
- **Note:** Already being saved in controller, but may need to be added to table if not exists

### 10. `access_other`
- **Type:** `string(255)`
- **Nullable:** Yes
- **Description:** Other access information
- **Used for:** Regular properties and BNB/HOTEL
- **Note:** Already being saved in controller, but may need to be added to table if not exists

---

## Migration SQL Example

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('property_units', function (Blueprint $table) {
            // NEW: Bedroom type for BNB/HOTEL
            $table->enum('bedroom_type', ['double', 'triple', 'quadruple', 'quintuple'])
                  ->nullable()
                  ->after('bedroom');
            
            // NEW: Floor for units
            $table->string('piano', 191)->nullable()->after('access_description');
            
            // NEW: Staircase for units
            $table->string('staircase', 191)->nullable()->after('piano');
            
            // NEW: Sign detail for units
            $table->string('sign_detail', 255)->nullable()->after('staircase');
            
            // NEW: Description for units
            $table->text('description')->nullable()->after('sign_detail');
            
            // Check if these exist before adding (they may already exist)
            if (!Schema::hasColumn('property_units', 'opening_type')) {
                $table->enum('opening_type', ['key', 'code'])->nullable()->after('description');
            }
            
            if (!Schema::hasColumn('property_units', 'street_code')) {
                $table->string('street_code', 191)->nullable()->after('opening_type');
            }
            
            if (!Schema::hasColumn('property_units', 'door_code')) {
                $table->string('door_code', 191)->nullable()->after('street_code');
            }
            
            if (!Schema::hasColumn('property_units', 'key_description')) {
                $table->string('key_description', 255)->nullable()->after('door_code');
            }
            
            if (!Schema::hasColumn('property_units', 'access_other')) {
                $table->string('access_other', 255)->nullable()->after('key_description');
            }
        });
    }

    public function down()
    {
        Schema::table('property_units', function (Blueprint $table) {
            $table->dropColumn([
                'bedroom_type',
                'piano',
                'staircase',
                'sign_detail',
                'description',
                'opening_type',
                'street_code',
                'door_code',
                'key_description',
                'access_other',
            ]);
        });
    }
};
```

---

## Controller Updates Summary

### PropertyController::store() - Updated Fields

**Property Level (already existed, now being saved):**
- ✅ `access_other` - Internal Access Description
- ✅ `street_code` - Street Opening Code
- ✅ `door_code` - Door Opening Code
- ✅ `key_description` - Key Description

**Unit Level (NEW fields being saved):**
- ✅ `bedroom_type` - Bedroom Type (Double, Triple, Quadruple, Quintuple)
- ✅ `piano` - Floor
- ✅ `staircase` - Staircase
- ✅ `sign_detail` - Plate/Identifying Detail
- ✅ `description` - Unit Description
- ✅ `opening_type` - Opening Type (already existed)
- ✅ `street_code` - Street Opening Code (already existed)
- ✅ `door_code` - Door Opening Code (already existed)
- ✅ `key_description` - Key Description (already existed)
- ✅ `access_other` - Other Access Information (already existed)

---

## Model Updates

### PropertyUnit Model
**Updated fillable array to include:**
- `bedroom_type`
- `description`
- `piano`
- `staircase`
- `sign_detail`
- `opening_type`
- `street_code`
- `door_code`
- `key_description`
- `access_other`

---

## Next Steps

1. ✅ **Controller Updated** - PropertyController::store() method updated
2. ✅ **Model Updated** - PropertyUnit fillable array updated
3. ❌ **Database Migration Needed** - Create and run migration for new property_units fields
4. ✅ **Form Fields** - All form fields already implemented in create.blade.php

