<?php

namespace App\Http\Controllers;

use App\Models\Maintainer;
use App\Models\MaintenanceRequest;
use App\Models\Notification;
use App\Models\Property;
use App\Models\Tenant;
use App\Models\Type;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;


class MaintenanceRequestController extends Controller
{

    public function index()
    {

        
        if (\Auth::user()->can('manage maintenance request') || \Auth::user()->type == 'admin') {
            if (\Auth::user()->type == 'maintainer') {
                $maintenanceRequests = MaintenanceRequest::where('maintainer_id', \Auth::user()->id)->get();
            } elseif (\Auth::user()->type == 'tenant') {
                $user = \Auth::user();
                $tenant = $user->tenants;
                $maintenanceRequests = MaintenanceRequest::where('property_id', !empty($tenant) ? $tenant->property : 0)
                    ->where('unit_id', !empty($tenant) ? $tenant->unit : 0)
                    ->get();
            } else {
                $maintenanceRequests = MaintenanceRequest::where('parent_id', parentId())->get();
            }

            // Load all relationships with property details
            $maintenanceRequests->load([
                'properties' => function($query) {
                    $query->with(['propertyImages', 'totalUnits']);
                },
                'units',
                'types',
                'maintainers'
            ]); // eager load relationships

            $events = $maintenanceRequests->map(function ($request) {
                return [
                    'id' => $request->id,
                    'title' => $request->types->title ?? 'No Issue',
                    'start' => $request->arrival_time,
                    'extendedProps' => [
                        'property' => $request->properties->name ?? '-',
                        'unit' => $request->units->name ?? '-',
                        'maintainer' => $request->maintainers->name ?? '-',
                        'status' => $request->status,
                        'attachment' => $request->issue_attachment,
                        'people_count' => $request->people_count,
                        'arrival_time' => $request->arrival_time,
                        'show_url' => route('maintenance-request.show', $request->id),
                        'edit_url' => route('maintenance-request.edit', $request->id),
                        'delete_url' => route('maintenance-request.destroy', $request->id),
                        'status_url' => route('maintenance-request.action', $request->id),
                    ]
                ];
            });

           return view('maintenance_request.index', compact('events'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }

                                                        //   method of the maintaince modal
public function create()
{
    if (\Auth::user()->can('create maintenance request')) {
        // Property dropdown
       if(Auth::user()->type == "super admin"){
    $property = Property::pluck('name', 'id');
}else{
    $property = Property::where('parent_id', parentId())->pluck('name', 'id');
}

 
        $property->prepend(__('Select Property'), 0);

        // Service types from "types" table
        $services = Type::where('type', 'issue')
                    ->pluck('title', 'id'); // title as label, id as value

        // Number of people options
        $peopleCount = [
            1 => '1',
            2 => '2',
            3 => '3',
            4 => '4',
            5 => '5',
            6 => '6',
            7 => '7+',
        ];

        return view('maintenance_request.create', compact(
            'property',
            'services',
            'peopleCount'
        ));
    } else {
        return redirect()->back()->with('error', __('Permission Denied!'));
    }
}


public function store(Request $request)
{
    if (\Auth::user()->can('create maintenance request')) {

        // ✅ Validation
        $validator = \Validator::make(
            $request->all(),
            [
                'property_id'   => 'required',
                'unit_id'       => 'required',
                'service'       => 'required',
                'arrival_time'  => 'required|date',
                'people_count'  => 'required',
            ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }

        // ✅ Find maintainer for this parent
        $maintainer = \App\Models\Maintainer::where('parent_id', parentId())->first();

        // ✅ Create maintenance request
        $MaintenanceRequest = new MaintenanceRequest();
        $MaintenanceRequest->property_id   = $request->property_id;
        $MaintenanceRequest->unit_id       = $request->unit_id;
        $MaintenanceRequest->service_type  = $request->service;
        $MaintenanceRequest->arrival_time  = $request->arrival_time; // user input
        $MaintenanceRequest->request_date  = now()->toDateString();   // today's date
        $MaintenanceRequest->people_count  = $request->people_count;
        $MaintenanceRequest->notes         = $request->notes;
        $MaintenanceRequest->parent_id     = parentId();
        $MaintenanceRequest->status        = 'pending';

        // ✅ Save maintainer_id if found
        if ($maintainer) {
            $MaintenanceRequest->maintainer_id = $maintainer->user_id;
        }

        $MaintenanceRequest->save();

        // ✅ Notifications
        $module = 'maintenance_request_create';
        if(Auth::user()->type == "super admin"){
            $notification = Notification::where('module', $module)->first();
        }else{
            $notification = Notification::where('parent_id', parentId())->where('module', $module)->first();
        }
      
        $notification['user_id'] = \Auth::user()->id;
        $setting = settings();
        $errorMessage = '';

        if (!empty($notification) && $notification->enabled_email == 1 && $maintainer) {
            $user = User::where('id', $maintainer->user_id)->first();
            if ($user) {
                $notification_responce = MessageReplace($notification, $MaintenanceRequest->id);
                $datas['subject'] = $notification_responce['subject'];
                $datas['message'] = $notification_responce['message'];
                $datas['module']  = $module;
                $datas['logo']    = $setting['company_logo'];
                $to = $user->email;
                $response = commonEmailSend($to, $datas);
                if ($response['status'] == 'error') {
                    $errorMessage = $response['message'];
                }
            }
        }

        return redirect()->back()->with('success', __('Maintenance request successfully created.') . '</br>' . $errorMessage);

    } else {
        return redirect()->back()->with('error', __('Permission Denied!'));
    }
}




    public function show(MaintenanceRequest $maintenanceRequest)
    {
        // Load all relationships with property details
        $maintenanceRequest->load([
            'properties' => function($query) {
                $query->with(['propertyImages', 'totalUnits']);
            },
            'units',
            'types',
            'maintainers'
        ]);
        
        return view('maintenance_request.show', compact('maintenanceRequest'));
    }


   public function edit(MaintenanceRequest $maintenanceRequest)
{
    
    if (\Auth::user()->can('edit maintenance request')) {

        if(Auth::user()->type == "super admin")
        {
                        $property = Property::pluck('name', 'id');
        }else{
        $property = Property::where('parent_id', parentId())->get()->pluck('name', 'id');

        }
        
        $property->prepend(__('Select Property'), 0);

        
        if(Auth::user()->type == "super admin"){
        $maintainers = User::where('type', 'maintainer')->get()->pluck('name', 'id');

        }else{
        $maintainers = User::where('parent_id', parentId())->where('type', 'maintainer')->get()->pluck('name', 'id');

        }
        $maintainers->prepend(__('Select Maintainer'), 0);

        $types = Type::where('type', 'issue')->pluck('title', 'id');
        $types->prepend(__('Select Type'), '');

        // <-- add peopleCount here
        $peopleCount = [
            1 => '1',
            2 => '2',
            3 => '3',
            4 => '4',
            5 => '5',
            6 => '6',
            7 => '7+',
        ];

        $status = MaintenanceRequest::$status;

     


        return view('maintenance_request.edit', compact(
            'property',
            'types',
            'maintainers',
            'maintenanceRequest',
            'status',
            'peopleCount' // <-- pass it to the view
        ));
    } else {
        return redirect()->back()->with('error', __('Permission Denied!'));
    }
}


public function update(Request $request, MaintenanceRequest $maintenanceRequest)
{
    if (!\Auth::user()->can('edit maintenance request')) {
        return redirect()->back()->with('error', __('Permission Denied!'));
    }

    // Validation (mirror store validation + update-specific rules)
    $validator = \Validator::make($request->all(), [
        'property_id'   => 'required',
        'unit_id'       => 'required',
        'issue_type'    => 'required',
        'maintainer_id' => Auth::user()->type != 'owner' ?'required':'nullable',
        // 'request_date'  => 'required|date',
        'people_count'  => 'nullable|in:1,2,3,4,5,6,7',
        'arrival_time'  => 'nullable|date',
        'issue_attachment' => 'nullable|file', // optional - adjust rules if needed
    ]);

    if ($validator->fails()) {
        $messages = $validator->getMessageBag();
        return redirect()->back()->with('error', $messages->first());
    }

    // Update fields (preserve existing if not present)
    $maintenanceRequest->property_id  = $request->property_id;
    $maintenanceRequest->unit_id      = $request->unit_id;
    $maintenanceRequest->service_type   = $request->issue_type;
    
    // If you also use service/service_type field in some places, handle it:
    if ($request->filled('service')) {
        $maintenanceRequest->service_type = $request->service;
    }
    if (\Auth::user()->type == 'owner') {
    // Keep existing maintainer if owner does not send one
    $maintenanceRequest->maintainer_id = $maintenanceRequest->maintainer_id;
    } else {
        $maintenanceRequest->maintainer_id = $request->maintainer_id;
    }

    $maintenanceRequest->people_count  = $request->people_count ?? $maintenanceRequest->people_count;
    $maintenanceRequest->arrival_time  = $request->arrival_time ?? $maintenanceRequest->arrival_time;
    // $maintenanceRequest->request_date  = $request->request_date;
    $maintenanceRequest->status        = $request->status ?? $maintenanceRequest->status;
    $maintenanceRequest->notes         = $request->notes;

    $maintenanceRequest->save();

    // Handle issue_attachment upload (same pattern used in store)
    if ($request->hasFile('issue_attachment')) {
        $requestFilenameWithExt = $request->file('issue_attachment')->getClientOriginalName();
        $requestFilename = pathinfo($requestFilenameWithExt, PATHINFO_FILENAME);
        $requestExtension = $request->file('issue_attachment')->getClientOriginalExtension();
        $requestFileName = $requestFilename . '_' . time() . '.' . $requestExtension;

        // ensure directory exists (optional: you already store into storage/app/upload/issue_attachment)
        $dir = storage_path('app/upload/issue_attachment');
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }

        // storeAs stores into storage/app/upload/issue_attachment/
        $request->file('issue_attachment')->storeAs('upload/issue_attachment/', $requestFileName);

        // save filename to model
        $maintenanceRequest->issue_attachment = $requestFileName;
        $maintenanceRequest->save();
    }

    // Notifications (robust to $notification being array or object)
    $module = 'maintenance_request_update';
    $notification = Notification::where('parent_id', parentId())
        ->where('module', $module)
        ->first();

    $setting = settings();
    $errorMessage = '';

    // Use data_get to read enabled_email whether $notification is array or object
    if (!empty($notification) && data_get($notification, 'enabled_email') == 1 && $maintenanceRequest->maintainer_id) {
        $user = User::find($maintenanceRequest->maintainer_id);
        if ($user) {
            // MessageReplace should accept model or array — keep passing $notification
            $notification_response = MessageReplace($notification, $maintenanceRequest->id);

            $datas['subject'] = data_get($notification_response, 'subject');
            $datas['message'] = data_get($notification_response, 'message');
            $datas['module']  = $module;
            $datas['logo']    = $setting['company_logo'] ?? null;
            $to = $user->email;

            $response = commonEmailSend($to, $datas);
            if (isset($response['status']) && $response['status'] == 'error') {
                $errorMessage = $response['message'];
            }
        }
    }


    return redirect()->back()->with('success', __('Maintenance request successfully updated.') . '</br>' . $errorMessage);
}


    public function destroy(MaintenanceRequest $maintenanceRequest)
    {
        if (\Auth::user()->can('delete maintenance request')) {
            $maintenanceRequest->delete();
            return redirect()->back()->with('success', __('Maintenance request successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }

    public function action($id)
    {
        $maintenanceRequest = MaintenanceRequest::find($id);
        
        // Load all relationships with property details
        $maintenanceRequest->load([
            'properties' => function($query) {
                $query->with(['propertyImages', 'totalUnits']);
            },
            'units',
            'types',
            'maintainers'
        ]);
        
        $status = MaintenanceRequest::$status;
        return view('maintenance_request.action', compact('maintenanceRequest', 'status'));
    }

    public function actionData(Request $request, $id)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'fixed_date' => 'required',
                'status' => 'required',
              
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }
        $setting = settings();

        $maintenanceRequest = MaintenanceRequest::find($id);
        $maintenanceRequest->fixed_date = $request->fixed_date;
        $maintenanceRequest->status = $request->status;

        $maintenanceRequest->save();

        if (!empty($request->invoice)) {
            $requestFilenameWithExt = $request->file('invoice')->getClientOriginalName();
            $requestFilename = pathinfo($requestFilenameWithExt, PATHINFO_FILENAME);
            $requestExtension = $request->file('invoice')->getClientOriginalExtension();
            $requestFileName = $requestFilename . '_' . time() . '.' . $requestExtension;
            $dir = storage_path('upload/invoice');
            if (!file_exists($dir)) {
                mkdir($dir, 0777, true);
            }
            $request->file('invoice')->storeAs('upload/invoice/', $requestFileName);
            $maintenanceRequest->invoice = $requestFileName;
            $maintenanceRequest->save();
        }


        $tenants = Tenant::where('property', $maintenanceRequest->property_id)
            ->where('unit', $maintenanceRequest->unit_id)
            ->get();

        if ($tenants->isNotEmpty()) {
            // Collect all tenant user_ids and parent_ids
            $userIds = [];
            foreach ($tenants as $tenant) {
                $userIds[] = $tenant->user_id;
                $userIds[] = $tenant->parent_id;
            }

            // Remove duplicates and fetch all emails
            $email = User::whereIn('id', array_unique($userIds))
                ->pluck('email')
                ->toArray();
        } else {
            // Fallback: Use the parent_id from the maintenance request
            $email = User::where('id', $maintenanceRequest->parent_id)
                ->pluck('email')
                ->toArray();
        }

        $module = 'maintenance_request_complete';
        $notification = Notification::where('parent_id', parentId())->where('module', $module)->first();
        $errorMessage = '';
        if (!empty($notification) && $notification->enabled_email == 1) {
            $user_email = $maintenanceRequest->maintainers->email;
            $notification_responce = MessageReplace($notification, $id);
            $datas['subject'] = $notification_responce['subject'];
            $datas['message'] = $notification_responce['message'];
            $datas['module'] = $module;
            $datas['logo'] =  $setting['company_logo'];
            $to = $email;
            $response = commonEmailSend($to, $datas);
            if ($response['status'] == 'error') {
                $errorMessage = $response['message'];
            }
        }

        return redirect()->back()->with('success', __('Maintenance request successfully update.') . '</br>' . $errorMessage);
    }

    public function pendingRequest()
    {
        if (\Auth::user()->can('manage maintenance request')) {
            if (\Auth::user()->type == 'maintainer') {
                $maintenanceRequests = MaintenanceRequest::where('maintainer_id', \Auth::user()->id)->where('status', 'pending')->get();
            } elseif (\Auth::user()->type == 'tenant') {
                $user = \Auth::user();
                $tenant = $user->tenants;
                $maintenanceRequests = MaintenanceRequest::where('property_id', !empty($tenant) ? $tenant->property : 0)->where('unit_id', !empty($tenant) ? $tenant->unit : 0)->where('status', 'pending')->get();
            } else {
                $maintenanceRequests = MaintenanceRequest::where('parent_id', parentId())->where('status', 'pending')->get();
            }
            
            // Load all relationships with property details
            $maintenanceRequests->load([
                'properties' => function($query) {
                    $query->with(['propertyImages', 'totalUnits']);
                },
                'units',
                'types',
                'maintainers'
            ]);
            
            return view('maintenance_request.type', compact('maintenanceRequests'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }

    public function inProgressRequest()
    {
        if (\Auth::user()->can('manage maintenance request')) {
            if (\Auth::user()->type == 'maintainer') {
                $maintenanceRequests = MaintenanceRequest::where('maintainer_id', \Auth::user()->id)->where('status', 'in_progress')->get();
            } elseif (\Auth::user()->type == 'tenant') {
                $user = \Auth::user();
                $tenant = $user->tenants;
                $maintenanceRequests = MaintenanceRequest::where('property_id', !empty($tenant) ? $tenant->property : 0)->where('unit_id', !empty($tenant) ? $tenant->unit : 0)->where('status', 'in_progress')->get();
            } else {
                $maintenanceRequests = MaintenanceRequest::where('parent_id', parentId())->where('status', 'in_progress')->get();
            }
            
            // Load all relationships with property details
            $maintenanceRequests->load([
                'properties' => function($query) {
                    $query->with(['propertyImages', 'totalUnits']);
                },
                'units',
                'types',
                'maintainers'
            ]);
            
            return view('maintenance_request.type', compact('maintenanceRequests'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }
    
}
