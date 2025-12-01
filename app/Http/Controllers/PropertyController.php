<?php

namespace App\Http\Controllers;

use App\Models\Type;
use App\Models\Property;
use App\Models\PropertyUnit;
use App\Models\Subscription;
use Illuminate\Http\Request;
use App\Models\PropertyImage;
use App\Models\UnitArrangementPhoto;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PropertyController extends Controller
{

public function index()
{
    if (\Auth::user()->can('manage property')) {

        if (\Auth::user()->type === 'super admin') {
            // Admin sees everything, eager load thumbnail and locationType
            $properties = Property::with(['thumbnail', 'locationType'])->where('is_active', 1)->get();
        } else {
            // Normal users see only their own properties, eager load thumbnail and locationType
            $properties = Property::with(['thumbnail', 'locationType'])
                                  ->where('is_active', 1)
                                  ->where('parent_id', \Auth::id())
                                  ->get();
        }

        return view('property.index', compact('properties'));

    } else {
        return redirect()->back()->with('error', __('Permission Denied!'));
    }
}


    public function create()
    {

        if (\Auth::user()->can('create property')) {
            $types = collect(Property::$Type)->keys()->mapWithKeys(fn($k) => [$k => __("$k")])->toArray();

            $propertyTypes = Type::select('id','title')->where('type','location_type')->pluck('title', 'id');  // code by hamza 
            $rentTypes = PropertyUnit::$rentTypes;

           return view('property.create', compact('types','propertyTypes','rentTypes'));
            
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }

public function store(Request $request)
{
  if (!\Auth::user()->can('create property')) {
        return redirect()->back()->with('error', __('Permission Denied!'));
    }

    $customMessages = [
        'access_description.required' => 'The access description field is required.',
        'sofa_beds.required_without'  => 'Provide sofa beds count or select yes/no for sofa bed.',
        'sofa_bed.required_without'   => 'Select yes/no for sofa bed or provide sofa beds count.',
    ];

    $validator = Validator::make(
        $request->all(),
        [
            'name'          => 'required',
            // 'description'   => 'required',
            // 'type'          => 'required',
            'property_type' => 'required',
            'country'       => 'required',
            'state'         => 'required',
            'city'          => 'required',
            'zip_code'      => 'required',
            'address'       => 'required',
            'thumbnail'     => 'required',
        ],
        $customMessages
    );

    if ($validator->fails()) {
        return response()->json([
            'status' => 'error',
            'msg'    => $validator->getMessageBag()->first(),
        ]);
    }

    $ids = parentId();
    $authUser = \App\Models\User::find($ids);
    $totalProperty = $authUser->totalProperty();
    $subscription = Subscription::find($authUser->subscription);

    if ($totalProperty >= $subscription->property_limit && $subscription->property_limit != 0) {
        return response()->json([
            'status' => 'error',
            'msg'    => __('Your property limit is over, please upgrade your subscription.'),
        ]);
    }

    // =============================
    // 🏠 Save the main property
    // =============================
    $property = new Property();
    $property->name                    = $request->name;
    $property->description             = $request->description ?? null;
    $property->type                    = $request->type;
    $property->country                 = $request->country;
    $property->state                   = $request->state;
    $property->city                    = $request->city;
    $property->zip_code                = $request->zip_code;
    $property->address                 = $request->address;
    $property->location_type           = $request->property_type;
    $property->piano                   = $request->piano ?? null;
    $property->staircase               = $request->staircase ?? null;
    $property->access_other            = $request->access_other ?? null;
    $property->sign_detail             = $request->sign_detail ?? null;
    $property->opening_type            = $request->opening_type ?? null;
    $property->street_code             = $request->street_code ?? null;
    $property->door_code              = $request->door_code ?? null;
    $property->key_description        = $request->key_description ?? null;
    $property->bnb_unit_type           = $request->bnb_unit_type ?? null;
    $property->bnb_unit_count          = $request->bnb_unit_count ?? 0;
    $property->sofa_bed                = $request->input('sofa_bed', 'no');
    $property->parent_id               = parentId();

    $property->save();

    // =============================
    // 📸 Handle thumbnail & images
    // =============================
     if ($request->hasFile('thumbnail') && $request->file('thumbnail')->isValid()) {
            $thumbnail = $request->file('thumbnail');
            $thumbnailFileName = 'THUMBNAIL_' . microtime(true) . '_' . uniqid() . '.' . $thumbnail->getClientOriginalExtension();

            // Ensure directory exists
            $thumbnailDir = public_path('uploads/thumbnail');
            if (!file_exists($thumbnailDir)) {
                mkdir($thumbnailDir, 0777, true);
            }

            if ($thumbnail->move($thumbnailDir, $thumbnailFileName)) {
                $thumbnailRecord = new PropertyImage();
                $thumbnailRecord->property_id = $property->id;
                $thumbnailRecord->image = $thumbnailFileName;
                $thumbnailRecord->type = 'thumbnail';
                $thumbnailRecord->save();
            } else {
                return response()->json([
                    'status' => 'error',
                    'msg'    => __('Failed to upload thumbnail.'),
                ]);
            }
        }

        // Handle additional property images (property_images[] input)
        if ($request->hasFile('property_images')) {
            foreach ($request->file('property_images') as $file) {
                if ($file && $file->isValid()) {
                    $propertyFileName = 'PROPERTY_IMG_' . microtime(true) . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

                    $propertyDir = public_path('uploads/property');
                    if (!file_exists($propertyDir)) {
                        mkdir($propertyDir, 0777, true);
                    }

                    if ($file->move($propertyDir, $propertyFileName)) {
                        $propertyImage = new PropertyImage();
                        $propertyImage->property_id = $property->id;
                        $propertyImage->image = $propertyFileName;
                        $propertyImage->type = 'extra';
                        $propertyImage->save();
                    } else {
                        return response()->json([
                            'status' => 'error',
                            'msg'    => __('Failed to upload one or more property images.'),
                        ]);
                    }
                }
            }
        }

    // =============================
    // 🧱 Save all units
    // =============================
    if (!empty($request->units) && is_array($request->units)) {
                foreach ($request->units as $unitIndex => $unitData) {
                    Log::info("Saving unit #{$unitIndex}", $unitData);

                    $unit = new PropertyUnit();
                    $unit->name                    = $unitData['unitname'] ?? 'Unnamed Unit';
                    $unit->bedroom                 = $unitData['bedroom'] ?? 0;
                    $unit->bedroom_type            = $unitData['bedroom_type'] ?? null; // NEW: For BNB/HOTEL
                    $unit->double_beds             = $unitData['double_beds'] ?? 0;
                    $unit->single_beds             = $unitData['single_beds'] ?? 0;
                    $unit->sofa_beds               = $unitData['sofa_beds'] ?? 0;
                    $unit->kitchen                 = $unitData['kitchen'] ?? 'no';
                    $unit->baths                   = $unitData['baths'] ?? 0;
                    $unit->rent                    = $unitData['rent'] ?? 0;
                    $unit->rent_type               = $unitData['rent_type'] ?? 0;
                    $unit->access_description      = $unitData['access_description'] ?? null;
                    $unit->deposit_type            = $unitData['deposit_type'] ?? null;
                    $unit->deposit_amount          = $unitData['deposit_amount'] ?? 0;
                    $unit->notes                   = $unitData['notes'] ?? null;
                    $unit->description             = $unitData['description'] ?? null; // NEW: Unit description
                    $unit->property_id             = $property->id;
                    $unit->parent_id               = parentId();

                    // Opening type and access code fields
                    $unit->opening_type            = $unitData['opening_type'] ?? null;
                    $unit->street_code             = $unitData['street_opening_code'] ?? null;
                    $unit->door_code               = $unitData['door_opening_code'] ?? null;
                    $unit->key_description         = $unitData['key_description'] ?? null;
                    $unit->access_other            = $unitData['other_access_information'] ?? null;
                    
                    // NEW: Floor, Staircase, and Sign Detail for units
                    $unit->piano                   = $unitData['piano'] ?? null;
                    $unit->staircase               = $unitData['staircase'] ?? null;
                    $unit->sign_detail             = $unitData['sign_detail'] ?? null;

                    Log::info("Unit before save", $unit->toArray()); // 🔹 log unit data before save

                    $unit->save();

                    Log::info("Unit saved with ID {$unit->id}");
                }

                }
        return response()->json([
                    'status' => 'success',
                    'id'     => $property->id,
                    'msg'    => __('Property successfully created.') 
                                . (!empty($errorMessage) ? '</br>' . $errorMessage : ''),
        ]);


}



    public function show(Property $property)
    {
        if (\Auth::user()->can('show property')) {
            // Eager load locationType relationship
            $property->load('locationType');
            
            $units = PropertyUnit::where('property_id', $property->id)->orderBy('id', 'desc')->get();
            
            // Get maintenance requests/services for this property
            $maintenanceRequests = \App\Models\MaintenanceRequest::where('property_id', $property->id)
                ->with(['units', 'types', 'maintainers'])
                ->orderBy('request_date', 'desc')
                ->get();
            
            return view('property.show', compact('property', 'units', 'maintenanceRequests'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }


  public function edit(Property $property)
{
    if (\Auth::user()->can('edit property')) {
        // keep types as you had (or use the collect(...) variant if you prefer)
        $types = Property::$Type;

        // copied from create() to provide $propertyTypes to the view
        $propertyTypes = Type::select('id','title')
                             ->where('type','location_type')
                             ->pluck('title', 'id');

        return view('property.edit', compact('types', 'property', 'propertyTypes'));
    } else {
        return redirect()->back()->with('error', __('Permission Denied!'));
    }
}



public function update(Request $request, Property $property)
{
    // Permission
    if (! \Auth::user()->can('edit property')) {
        return redirect()->back()->with('error', __('Permission Denied!'));
    }

    // Validation - match the edit form fields
    $validator = Validator::make($request->all(), [
        'name'          => 'required',
        'property_type' => 'required',
        'country'       => 'required',
        'state'         => 'required',
        'city'          => 'required',
        'zip_code'      => 'required',
        'address'       => 'required',
        // description, type, thumbnail and property_images optional on update
    ]);

    if ($validator->fails()) {
        $messages = $validator->getMessageBag();
        return response()->json([
            'status' => 'error',
            'msg'    => $messages->first(),
        ]);
    }

    try {
        // Update property fields (note: location_type column used in store())
        $property->name            = $request->name;
        $property->description     = $request->description ?? $property->description;
        $property->type            = $request->type ?? $property->type; // Optional since removed from form
        $property->location_type   = $request->property_type; // IMPORTANT: matches store()
        $property->country         = $request->country;
        $property->state           = $request->state;
        $property->city            = $request->city;
        $property->zip_code        = $request->zip_code;
        $property->address         = $request->address;
        $property->piano           = $request->piano ?? $property->piano;
        $property->staircase       = $request->staircase ?? $property->staircase;
        $property->access_other    = $request->access_other ?? $property->access_other;
        $property->sign_detail     = $request->sign_detail ?? $property->sign_detail;
        $property->opening_type    = $request->opening_type ?? $property->opening_type;
        $property->street_code    = $request->street_code ?? $property->street_code;
        $property->door_code       = $request->door_code ?? $property->door_code;
        $property->key_description = $request->key_description ?? $property->key_description;
        $property->bnb_unit_type   = $request->bnb_unit_type ?? $property->bnb_unit_type;
        $property->bnb_unit_count = $request->bnb_unit_count ?? $property->bnb_unit_count;
        // sofa_bed saved as yes/no in properties table in store()
        $property->sofa_bed        = $request->input('sofa_bed', $property->sofa_bed ?? 'no');

        $property->save();

        // ---- Thumbnail upload (public/uploads/thumbnail) ----
        if ($request->hasFile('thumbnail') && $request->file('thumbnail')->isValid()) {
            $thumbnail = $request->file('thumbnail');
            $thumbnailFileName = 'THUMBNAIL_' . microtime(true) . '_' . uniqid() . '.' . $thumbnail->getClientOriginalExtension();

            $thumbnailDir = public_path('uploads/thumbnail');
            if (!file_exists($thumbnailDir)) {
                mkdir($thumbnailDir, 0777, true);
            }

            // delete existing thumbnail file & record (if exists)
            $existingThumb = PropertyImage::where('property_id', $property->id)->where('type', 'thumbnail')->first();
            if ($existingThumb && !empty($existingThumb->image)) {
                $existingPath = public_path('uploads/thumbnail/' . $existingThumb->image);
                if (file_exists($existingPath)) {
                    @unlink($existingPath);
                }
            }

            if ($thumbnail->move($thumbnailDir, $thumbnailFileName)) {
                if ($existingThumb) {
                    $existingThumb->image = $thumbnailFileName;
                    $existingThumb->save();
                } else {
                    $thumbnailRecord = new PropertyImage();
                    $thumbnailRecord->property_id = $property->id;
                    $thumbnailRecord->image = $thumbnailFileName;
                    $thumbnailRecord->type = 'thumbnail';
                    $thumbnailRecord->save();
                }
            } else {
                return response()->json([
                    'status' => 'error',
                    'msg'    => __('Failed to upload thumbnail.'),
                ]);
            }
        }

        // ---- Additional property images (public/uploads/property) ----
        if ($request->hasFile('property_images')) {
            $propertyDir = public_path('uploads/property');
            if (!file_exists($propertyDir)) {
                mkdir($propertyDir, 0777, true);
            }

            foreach ($request->file('property_images') as $file) {
                if ($file && $file->isValid()) {
                    $propertyFileName = 'PROPERTY_IMG_' . microtime(true) . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

                    if ($file->move($propertyDir, $propertyFileName)) {
                        $propertyImage = new PropertyImage();
                        $propertyImage->property_id = $property->id;
                        $propertyImage->image = $propertyFileName;
                        $propertyImage->type = 'extra';
                        $propertyImage->save();
                    } else {
                        return response()->json([
                            'status' => 'error',
                            'msg'    => __('Failed to upload one or more property images.'),
                        ]);
                    }
                }
            }
        }

        // Optionally update first unit's access_description if you want to mirror store() behavior:
        // If you have property units and want to update them here, implement it similarly.

        return response()->json([
            'status' => 'success',
            'msg'    => __('Property successfully updated.'),
            'id'     => $property->id,
        ]);
    } catch (\Exception $e) {
        // Log if needed: \Log::error($e);
        return response()->json([
            'status' => 'error',
            'msg'    => $e->getMessage(),
        ]);
    }
}



  public function destroy(Property $property)
{

    if (\Auth::user()->can('delete property')) {
        // Delete related units first
        $units = $property->totalUnits;

        foreach ($units as $unit) {
            $unit->delete();
        }

        // Then delete the property itself
        $property->delete();

        return redirect()->back()->with('success', 'Property successfully deleted.');
    } else {
        return redirect()->back()->with('error', __('Permission Denied!'));
    }
}



   public function units()
{
    if (\Auth::user()->can('manage unit')) {
        $units = PropertyUnit::with('properties')
            ->where('parent_id', parentId())
            ->get();

        return view('unit.index', compact('units'));
    } else {
        return redirect()->back()->with('error', __('Permission Denied!'));
    }
}


    public function unitCreate($property_id)
    {

        $types = PropertyUnit::$Types;
        $rentTypes = PropertyUnit::$rentTypes;
        return view('unit.create', compact('types', 'property_id', 'rentTypes'));
    }



    public function unitStore(Request $request, $property_id)
    {

        if (\Auth::user()->can('create unit')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'name' => 'required',
                    'bedroom' => 'required',
                    'kitchen' => 'required',
                    'baths' => 'required',
                    'rent' => 'required',
                    'rent_type' => 'required',
                    'deposit_type' => 'required',
                    'deposit_amount' => 'required',
                    'late_fee_type' => 'required',
                    'late_fee_amount' => 'required',
                    'incident_receipt_amount' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $unit = new PropertyUnit();
            $unit->name = $request->name;
            $unit->bedroom = $request->bedroom;
            $unit->kitchen = $request->kitchen;
            $unit->baths = $request->baths;
            $unit->rent = $request->rent;
            $unit->rent_type = $request->rent_type;
            if ($request->rent_type == 'custom') {
                $unit->start_date = $request->start_date;
                $unit->end_date = $request->end_date;
                $unit->payment_due_date = $request->payment_due_date;
            } else {
                $unit->rent_duration = $request->rent_duration;
            }

            $unit->deposit_type = $request->deposit_type;
            $unit->deposit_amount = $request->deposit_amount;
            $unit->late_fee_type = $request->late_fee_type;
            $unit->late_fee_amount = $request->late_fee_amount;
            $unit->incident_receipt_amount = $request->incident_receipt_amount;
            $unit->notes = $request->notes;
            $unit->property_id = $property_id;
            $unit->parent_id = parentId();
            $unit->save();
            return redirect()->back()->with('success', __('Unit successfully created.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }

    public function unitdirectCreate()
    {
        $name = Property::select('name', 'id')
            ->where('parent_id', parentId())
            ->pluck('name', 'id');

        $types = PropertyUnit::$Types;
        $rentTypes = PropertyUnit::$rentTypes;

        return view('unit.directcreate', compact('types', 'rentTypes', 'name'));
    }

    // API endpoint to get property location type
    public function getPropertyLocationType($propertyId)
    {
        try {
            $property = Property::find($propertyId);
            if (!$property) {
                return response()->json([
                    'status' => 'error',
                    'msg' => 'Property not found'
                ], 404);
            }

            $locationTypeId = $property->location_type;
            $locationType = null;
            $locationTypeTitle = null;

            if ($locationTypeId) {
                $locationType = Type::find($locationTypeId);
                if ($locationType) {
                    $locationTypeTitle = $locationType->title;
                }
            }

            return response()->json([
                'status' => 'success',
                'location_type_id' => $locationTypeId,
                'location_type_title' => $locationTypeTitle
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'msg' => $e->getMessage()
            ], 500);
        }
    }


    public function unitdirectStore(Request $request)
    {
        $property_id = $request->property_id;
        if (!\Auth::user()->can('create unit')) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }

        // ✅ Validation - make bedroom and kitchen optional since they're hidden for some property types
        $validator = \Validator::make(
            $request->all(),
            [
                'name' => 'required',
                'bedroom' => 'nullable|integer|min:0',
                'bedroom_type' => 'nullable|in:double,triple,quadruple,quintuple',
                'double_beds' => 'nullable|integer|min:0',
                'single_beds' => 'nullable|integer|min:0',
                'sofa_beds' => 'nullable|integer|min:0',
                'kitchen' => 'nullable|in:yes,no',
                'baths' => 'required|integer|min:0',
                'piano' => 'nullable|string|max:191',
                'staircase' => 'nullable|string|max:191',
                'opening_type' => 'nullable|in:key,code',
                'access_description' => 'nullable|string|max:2000',
                'sign_detail' => 'nullable|string|max:255',
                'description' => 'nullable|string|max:2000',
                'notes' => 'nullable|string|max:1000',
                'street_code' => 'nullable|string|max:191',
                'door_code' => 'nullable|string|max:191',
                'key_description' => 'nullable|string|max:255',
                'access_other' => 'nullable|string|max:255',
                // 'arrangement_photos.*' => 'nullable|mimes:jpeg,png|max:2048',
                'arrangement_photos.*' => 'nullable|mimes:jpeg,png|max:102400',

            ]
        );

            if ($validator->fails()) {
                return redirect()->back()->with('error', $validator->getMessageBag()->first());
            }

            $property = Property::find($property_id);

            if (!$property) {
                return redirect()->back()->with('error', __('Please select a valid property.'));
            }




        /**
         * -----------------------------------------
         * 1. Save Unit-specific data
         * -----------------------------------------
         */
        $unit = new PropertyUnit();
        $unit->name = $request->name;
        $unit->bedroom = $request->bedroom ?? 0;
        $unit->bedroom_type = $request->bedroom_type ?? null;
        $unit->double_beds = $request->double_beds ?? 0;
        $unit->single_beds = $request->single_beds ?? 0;
        $unit->sofa_beds = $request->sofa_beds ?? 0;
        $unit->kitchen = $request->kitchen ?? 'no';
        $unit->baths = $request->baths;
        $unit->notes = $request->notes ?? null;
        $unit->description = $request->description ?? null;
        $unit->access_description = $request->access_description ?? null;
        $unit->piano = $request->piano ?? null;
        $unit->staircase = $request->staircase ?? null;
        $unit->sign_detail = $request->sign_detail ?? null;
        $unit->opening_type = $request->opening_type ?? null;
        $unit->street_code = $request->street_code ?? null;
        $unit->door_code = $request->door_code ?? null;
        $unit->key_description = $request->key_description ?? null;
        $unit->access_other = $request->access_other ?? null;
        $unit->property_id = $property_id;
        $unit->parent_id = parentId();
        $unit->save();

        /**
         * -----------------------------------------
         * 2. Update Property-related fields (if provided)
         * -----------------------------------------
         */
        if ($request->has('piano')) {
            $property->piano = $request->piano;
        }
        if ($request->has('staircase')) {
            $property->staircase = $request->staircase;
        }
        if ($request->has('sign_detail')) {
            $property->sign_detail = $request->sign_detail;
        }
        if ($request->has('description')) {
            $property->description = $request->description;
        }
        if ($request->has('opening_type')) {
            $property->opening_type = $request->opening_type;
        }
        if ($request->has('street_code')) {
            $property->street_code = $request->street_code;
        }
        if ($request->has('door_code')) {
            $property->door_code = $request->door_code;
        }
        if ($request->has('key_description')) {
            $property->key_description = $request->key_description;
        }
        if ($request->has('access_other')) {
            $property->access_other = $request->access_other;
        }
        $property->save();

        /**
         * -----------------------------------------
         * 3. Handle arrangement photos upload (Bed/Towel Arrangement)
         * -----------------------------------------
         */
        if ($request->hasFile('arrangement_photos')) {
            $arrangementDir = public_path('uploads/arrangement');
            if (!file_exists($arrangementDir)) {
                mkdir($arrangementDir, 0777, true);
            }

            foreach ($request->file('arrangement_photos') as $file) {
                if ($file && $file->isValid()) {
                    $arrangementFileName = 'ARRANGEMENT_' . microtime(true) . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

                    if ($file->move($arrangementDir, $arrangementFileName)) {
                        $arrangementPhoto = new UnitArrangementPhoto();
                        $arrangementPhoto->property_unit_id = $unit->id;
                        $arrangementPhoto->image = $arrangementFileName;
                        $arrangementPhoto->description = null; // Can be added later if needed
                        $arrangementPhoto->save();
                    } else {
                        return redirect()->back()->with('error', __('Failed to upload one or more arrangement photos.'));
                    }
                }
            }
        }

        return redirect()->back()->with('success', __('Unit successfully created.'));
    }



    public function unitEdit($property_id, $unit_id)
    {
        $unit = PropertyUnit::find($unit_id);
        $property = Property::find($property_id);
        $types = PropertyUnit::$Types;
        $rentTypes = PropertyUnit::$rentTypes;
        return view('unit.edit', compact('types', 'property_id', 'rentTypes', 'unit', 'property'));
    }
    public function unitUpdate(Request $request, $property_id, $unit_id)
    {
        // 🔐 Permission Check
        if (!\Auth::user()->can('edit unit')) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }

        // ✅ Validation
        $validator = \Validator::make($request->all(), [
            'name'                  => 'required|string|max:255',
            'bedroom'               => 'required|integer|min:0',
            'double_beds'           => 'nullable|integer|min:0',
            'single_beds'           => 'nullable|integer|min:0',
            'sofa_beds'             => 'nullable|integer|min:0',
            'kitchen'               => 'required|in:yes,no,1,0',
            'baths'                 => 'required|integer|min:0',
            'access_description'    => 'nullable|string|max:2000',
            'notes'                 => 'nullable|string|max:1000',
            'arrangement_photos.*'  => 'nullable|mimes:jpeg,png|max:2048',

            // property fields
            'piano'                 => 'nullable|string|max:191',
            'staircase'             => 'nullable|string|max:191',
            'opening_type'          => 'nullable|in:key,code',
            'sign_detail'           => 'nullable|string|max:255',
            'description'           => 'nullable|string|max:2000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first());
        }

        // 🔎 Find Unit
        $unit = \App\Models\PropertyUnit::findOrFail($unit_id);

        /**
         * -----------------------------------------
         * 1. Update Unit-specific data
         * -----------------------------------------
         */
        $unit->name            = $request->name;
        $unit->bedroom         = (int) $request->bedroom;
        $unit->double_beds     = (int) ($request->double_beds ?? 0);
        $unit->single_beds     = (int) ($request->single_beds ?? 0);
        $unit->sofa_beds       = (int) ($request->sofa_beds ?? 0);
        $unit->baths           = (int) $request->baths;

        // normalize kitchen (1/0 → yes/no)
        $kitchen = $request->kitchen;
        $unit->kitchen = ($kitchen === '1' || $kitchen === 1) ? 'yes' : (($kitchen === '0' || $kitchen === 0) ? 'no' : $kitchen);

        $unit->notes              = $request->notes;
        $unit->access_description = $request->access_description;
        $unit->save();

        /**
         * -----------------------------------------
         * 2. Update Property-related fields
         * -----------------------------------------
         */
        $property = \App\Models\Property::findOrFail($property_id);
        $property->piano        = $request->piano;
        $property->staircase    = $request->staircase;
        $property->opening_type = $request->opening_type;
        $property->sign_detail  = $request->sign_detail;
        $property->description  = $request->description;
        $property->save();

        /**
         * -----------------------------------------
         * 3. Handle property images upload
         * -----------------------------------------
         */
        if ($request->hasFile('arrangement_photos')) {
            foreach ($request->file('arrangement_photos') as $file) {
                if ($file && $file->isValid()) {
                    $propertyFileName = 'PROPERTY_IMG_' . microtime(true) . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

                    $propertyDir = public_path('uploads/property');
                    if (!file_exists($propertyDir)) {
                        mkdir($propertyDir, 0777, true);
                    }

                    if ($file->move($propertyDir, $propertyFileName)) {
                        $propertyImage = new \App\Models\PropertyImage();
                        $propertyImage->property_id = $property->id;
                        $propertyImage->image       = $propertyFileName;
                        $propertyImage->type        = 'extra';
                        $propertyImage->save();
                    } else {
                        return redirect()->back()->with('error', __('Failed to upload one or more property images.'));
                    }
                }
            }
        }

        return redirect()->back()->with('success', __('Unit successfully updated.'));
    }



    public function unitDestroy($property_id, $unit_id)
    {
        if (\Auth::user()->can('delete unit')) {
            $unit = PropertyUnit::find($unit_id);
            $unit->delete();
            return redirect()->back()->with('success', 'Unit successfully deleted.');
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }
    public function getPropertyUnit($property_id)
    {
        $units = PropertyUnit::where('property_id', $property_id)->get()->pluck('name', 'id');
        return response()->json($units);
    }

    public function deleteThumbnail(Request $request, $id) {
        // permission checks...
        $thumb = PropertyImage::find($id);
        if (!$thumb) return response()->json(['success'=>false,'message'=>'Not found'],404);
        @unlink(public_path('uploads/thumbnail/'.$thumb->image));
        $thumb->delete();
        return response()->json(['success'=>true,'message'=>__('Thumbnail deleted successfully')]);
    }

    public function deletePropertyImage(Request $request, $id) {
        // permission checks...
        $img = PropertyImage::find($id);
        if (!$img) return response()->json(['success'=>false,'message'=>'Not found'],404);
        @unlink(public_path('uploads/property/'.$img->image));
        $img->delete();
        return response()->json(['success'=>true,'message'=>__('Image deleted successfully')]);
    }


}
