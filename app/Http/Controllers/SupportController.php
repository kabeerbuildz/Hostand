<?php

namespace App\Http\Controllers;

use App\Models\Support;
use App\Models\SupportReply;
use App\Models\User;
use App\Models\MaintenanceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;


class SupportController extends Controller
{
    /**
     * Check if user has access to a specific support ticket
     */
    private function hasAccessToSupport($support, $user = null)
    {
        // dd($support);
        if (!$user) {
            $user = Auth::user();
        }
        
        // Super admin and admin have access to all tickets
        if ($user->type == 'super admin' || $user->type == 'admin') {
            return true;
        }
        
        // Ticket creator has access
        if ($support->created_id == $user->id) {
            return true;
        }
        
        // Assigned user has access (read-only for maintainers)
        if ($support->assign_user == $user->id) {
            return true;
        }
        // Assigned user has access (read-only for maintainers)
        // $requests = MaintenanceRequest::where('maintainer_id',$user->id)->get();

        // if ($support->request_id == $user->id) {
        //     return true;
        // }
      

        
        return false;
    }

    /**
     * Check if user can edit a support ticket
     */
    private function canEditSupport($support, $user = null)
    {
        if (!$user) {
            $user = Auth::user();
        }
        
        // Super admin and admin can edit any ticket
        if ($user->type == 'super admin' || $user->type == 'admin') {
            return true;
        }
        
        // Ticket creator can edit their own ticket
        if ($support->created_id == $user->id) {
            return true;
        }
        
        return false;
    }

    /**
     * Check if user can reply to a support ticket
     */
    private function canReplyToSupport($support, $user = null)
    {
        if (!$user) {
            $user = Auth::user();
        }
        
        // Super admin and admin can reply to any ticket
        if ($user->type == 'super admin' || $user->type == 'admin') {
            return true;
        }
        
        // Ticket creator can reply to their own ticket
        if ($support->created_id == $user->id) {
            return true;
        }
        
        // Assigned user can reply to their assigned ticket
        if ($support->assign_user == $user->id) {
            return true;
        }
        
        return false;
    }

    public function index()
    {
        $user = Auth::user();
        
        if ($user->type == 'super admin' || $user->type == 'admin') {
            // Admin can see all tickets
            $supports = Support::with(['createdUser', 'assignUser'])->orderBy('created_at', 'desc')->get();
        } elseif ($user->type == 'owner' || $user->type == 'tenant') {
            // Owners and tenants can only see their own tickets
            $supports = Support::where('created_id', $user->id)
                ->with(['createdUser', 'assignUser'])
                ->orderBy('created_at', 'desc')
                ->get();
        } elseif ($user->type == 'maintainer') {
            $requestIds = MaintenanceRequest::where('maintainer_id', $user->id)->pluck('id');
        
            $supports = Support::whereIn('request_id', $requestIds)
                ->with(['createdUser', 'assignUser'])
                ->orderBy('created_at', 'desc')
                ->get();
        }else {
            // Maintainers can see tickets assigned to them
            $supports = Support::where('assign_user', $user->id)
                ->with(['createdUser', 'assignUser'])
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return view('support.index', compact('supports'));
    }

    public function create()
    {
        $user = Auth::user();
        
        // Only owners, tenants, and admins can create tickets
        if (!in_array($user->type, ['owner', 'tenant', 'super admin', 'admin'])) {
            return redirect()->route('support.index')->with('error', 'You do not have permission to create tickets.');
        }
    
        $priority = Support::$priority;
        $status = Support::$status;
        
        // Get admin users for assignment
        $admins = User::whereIn('type', ['super admin', 'admin'])->get();
    
        // Requests only for today's date (using request ID)
        $requests = MaintenanceRequest::with('properties','types')
            // ->where('parent_id', parentId())
            ->whereDate('request_date', now()->toDateString())
            ->select('id', 'request_date', 'service_type', 'property_id') 
            ->get();

        Log::info('🔍 Support Create Data Dump', [
    'priority' => $priority,
    'status'   => $status,
    'admins'   => $admins,
    'requests' => $requests,
]);

    
        return view('support.create', compact('priority', 'status', 'admins', 'requests'));
    }
    


    public function store(Request $request)
    {
        // dd($request->all());
        $user = Auth::user();
        
        // Only owners, tenants, and admins can create tickets
        if (!in_array($user->type, ['owner', 'tenant', 'super admin', 'admin'])) {
            return redirect()->route('support.index')->with('error', 'You do not have permission to create tickets.');
        }
    
        $validator = Validator::make($request->all(), [
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'required|in:low,medium,high,critical',
            'assign_user' => 'nullable|exists:users,id',
            'request_id' => 'nullable|exists:maintenance_requests,id', // validate request_id
            'attachment.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
    
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
    
        $support = new Support();
        $support->subject = $request->subject;
        $support->description = $request->description;
        $support->priority = $request->priority;
        $support->status = 'pending';
        $support->created_id = $user->id;
    
        // store the selected service request ID
        $support->request_id = $request->request_id;
  
        // Handle assignment based on user type
        if (in_array($user->type, ['super admin', 'admin'])) {
            $support->assign_user = $request->assign_user ?? null;
        } else {
            $admin = User::whereIn('type', ['super admin', 'admin'])->first();
            $support->assign_user = $admin ? $admin->id : null;
        }
    
        // Handle photo attachments
        if ($request->hasFile('attachment')) {
            $attachments = [];
            $uploadPath = 'upload/support';
    
            if (!Storage::exists('public/' . $uploadPath)) {
                Storage::makeDirectory('public/' . $uploadPath);
            }
    
            foreach ($request->file('attachment') as $file) {
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->storeAs('public/' . $uploadPath, $filename);
                $attachments[] = $filename;
            }
            $support->attachment = json_encode($attachments);
        }
    
        $support->save();
    
        return redirect()->route('support.index')->with('success', 'Ticket created successfully.');
    }
    

    public function show($id)
    {
        $support = Support::with(['createdUser', 'assignUser', 'reply.user'])->findOrFail($id);
        
        // Check if user has access to this ticket
        if(Auth::user()->type !== 'maintainer'){
            if (!$this->hasAccessToSupport($support)) {
                return redirect()->route('support.index')->with('error', 'You do not have permission to view this ticket.');
            }
        }
       

        return view('support.show', compact('support'));
    }

    public function edit($id)
    {
        $support = Support::findOrFail($id);
        
        // Check if user can edit this ticket
        if (!$this->canEditSupport($support)) {
            return redirect()->route('support.index')->with('error', 'You do not have permission to edit this ticket.');
        }

        $priority = Support::$priority;
        $status = Support::$status;
        $admins = User::whereIn('type', ['super admin', 'admin'])->get();
        
        return view('support.edit', compact('support', 'priority', 'status', 'admins'));
    }

    public function update(Request $request, $id)
    {
        $support = Support::findOrFail($id);
       
        // Debug: Log the request data
        \Illuminate\Support\Facades\Log::info('Update request data', [
            'ticket_id' => $id,
            'request_data' => $request->all(),
            'user_type' => Auth::user()->type,
            'method' => $request->method(),
            'has_subject' => $request->has('subject'),
            'has_description' => $request->has('description'),
            'has_priority' => $request->has('priority'),
            'has_status' => $request->has('status'),
            'has_assign_user' => $request->has('assign_user'),
        ]);
        
        // Check if user can edit this ticket
        if (!$this->canEditSupport($support)) {
            return redirect()->route('support.index')->with('error', 'You do not have permission to update this ticket.');
        }

        $user = Auth::user();
        $rules = [
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'required|in:low,medium,high,critical',
            'attachment.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
        // Only validate status if user is admin
        if (in_array($user->type, ['super admin', 'admin'])) {
            $rules['status'] = 'required|in:pending,open,close,on_hold';
        }
        
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            \Illuminate\Support\Facades\Log::error('Validation failed', [
                'errors' => $validator->errors()->toArray()
            ]);
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Store original values for comparison
        $originalData = [
            'subject' => $support->subject,
            'description' => $support->description,
            'priority' => $support->priority,
            'status' => $support->status,
            'assign_user' => $support->assign_user,
        ];

        $support->subject = $request->subject;
        $support->description = $request->description;
        $support->priority = $request->priority;
        
        // Only update status if user is admin
        if (in_array($user->type, ['super admin', 'admin'])) {
            $support->status = $request->status;
        }

        // Handle new photo attachments
        if ($request->hasFile('attachment')) {
            $uploadPath = 'upload/support';
            
            // Ensure directory exists
            if (!Storage::exists('public/' . $uploadPath)) {
                Storage::makeDirectory('public/' . $uploadPath);
            }
            
            // Get existing attachments
            $existingAttachments = [];
            if ($support->attachment) {
                $existingAttachments = json_decode($support->attachment, true) ?? [];
            }
            
            // Add new attachments
            foreach ($request->file('attachment') as $file) {
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->storeAs('public/' . $uploadPath, $filename);
                $existingAttachments[] = $filename;
            }
            
            $support->attachment = json_encode($existingAttachments);
        }

        // Debug: Log before save
        \Illuminate\Support\Facades\Log::info('Before save', [
            'ticket_id' => $support->id,
            'subject' => $support->subject,
            'description' => $support->description,
            'priority' => $support->priority,
            'status' => $support->status,
            'assign_user' => $support->assign_user,
        ]);

        $support->save();

        // Debug: Log after save
        \Illuminate\Support\Facades\Log::info('After save', [
            'ticket_id' => $support->id,
            'subject' => $support->subject,
            'description' => $support->description,
            'priority' => $support->priority,
            'status' => $support->status,
            'assign_user' => $support->assign_user,
        ]);

        // Debug: Log the update comparison
        \Illuminate\Support\Facades\Log::info('Ticket updated comparison', [
            'ticket_id' => $support->id,
            'original' => $originalData,
            'updated' => [
                'subject' => $support->subject,
                'description' => $support->description,
                'priority' => $support->priority,
                'status' => $support->status,
                'assign_user' => $support->assign_user,
            ],
            'user_type' => $user->type
        ]);

        return redirect()->route('support.index')->with('success', 'Ticket updated successfully.');
    }

    public function destroy($id)
    {
        $support = Support::findOrFail($id);
        
        // Check if user can edit this ticket (same permissions as edit)
        if (!$this->canEditSupport($support)) {
            return redirect()->route('support.index')->with('error', 'You do not have permission to delete this ticket.');
        }

        // Delete attachments
        if ($support->attachment) {
            $attachments = json_decode($support->attachment, true);
            if (is_array($attachments)) {
                foreach ($attachments as $attachment) {
                    Storage::delete('public/upload/support/' . $attachment);
                }
            }
        }

        $support->delete();

        return redirect()->route('support.index')->with('success', 'Ticket deleted successfully.');
    }

    public function reply(Request $request, $id)
    {
        $support = Support::findOrFail($id);
        $user = Auth::user();
        
        // Check if user can reply to this ticket
        if (!$this->canReplyToSupport($support)) {
            return redirect()->route('support.show', $id)->with('error', 'You do not have permission to reply to this ticket.');
        }

        $validator = Validator::make($request->all(), [
            'description' => 'required|string',
            'attachment.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $reply = new SupportReply();
        $reply->support_id = $id;
        $reply->user_id = $user->id;
        $reply->description = $request->description;

        // Handle photo attachments for reply
        if ($request->hasFile('attachment')) {
            $attachments = [];
            $uploadPath = 'upload/support';
            
            // Ensure directory exists
            if (!Storage::exists('public/' . $uploadPath)) {
                Storage::makeDirectory('public/' . $uploadPath);
            }
            
            foreach ($request->file('attachment') as $file) {
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->storeAs('public/' . $uploadPath, $filename);
                $attachments[] = $filename;
            }
            $reply->attachment = json_encode($attachments);
        }

        $reply->save();

        // Update ticket status to open if it was pending
        if ($support->status == 'pending') {
            $support->status = 'open';
            $support->save();
        }

        return redirect()->route('support.show', $id)->with('success', 'Reply added successfully.');
    }

    public function close($id)
    {
        $support = Support::findOrFail($id);
        
        // Check if user can edit this ticket (same permissions as edit)
        if (!$this->canEditSupport($support)) {
            return redirect()->route('support.show', $id)->with('error', 'You do not have permission to close this ticket.');
        }

        $support->status = 'close';
        $support->save();

        return redirect()->route('support.show', $id)->with('success', 'Ticket closed successfully.');
    }

    /**
     * View photo with one-time access and automatic deletion (only for admins)
     */
    public function viewPhoto($ticketId, $photoName)
    {
        $support = Support::findOrFail($ticketId);
        $user = Auth::user();
        
        // Check if user has access to this ticket
        if (!$this->hasAccessToSupport($support)) {
            return response()->json(['error' => 'You do not have permission to view this photo.'], 403);
        }

        $filePath = storage_path('public/upload/support/' . $photoName);
        
        // Check if file exists
        if (!file_exists($filePath)) {
            return response()->json(['error' => 'Photo not found.'], 404);
        }

        // Get file info
        $fileInfo = pathinfo($filePath);
        $extension = strtolower($fileInfo['extension']);
        
        // Check if it's an image
        if (!in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
            return response()->json(['error' => 'Invalid file type.'], 400);
        }

        // Read file content
        $fileContent = file_get_contents($filePath);
        
        // Only delete the file if the user is an admin
        if ($user->type == 'super admin' || $user->type == 'admin') {
            // Delete the file immediately after reading
            unlink($filePath);
            
            // Update the support record to remove the photo from attachments
            $this->removePhotoFromAttachments($support, $photoName);
        }

        // Return the image with appropriate headers
        return response($fileContent)
            ->header('Content-Type', $this->getMimeType($extension))
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    /**
     * Remove photo from attachments JSON
     */
    private function removePhotoFromAttachments($support, $photoName)
    {
        if ($support->attachment) {
            $attachments = json_decode($support->attachment, true);
            if (is_array($attachments)) {
                $attachments = array_filter($attachments, function($attachment) use ($photoName) {
                    return $attachment !== $photoName;
                });
                $support->attachment = !empty($attachments) ? json_encode(array_values($attachments)) : null;
                $support->save();
            }
        }
    }

    /**
     * Get MIME type for file extension
     */
    private function getMimeType($extension)
    {
        $mimeTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif'
        ];
        
        return $mimeTypes[$extension] ?? 'application/octet-stream';
    }

    /**
     * Show ticket details in modal for admins and operators
     */
    public function showModal($id)
    {
        $support = Support::with(['createdUser', 'assignUser', 'reply.user'])->findOrFail($id);
        $user = Auth::user();
        
        // Check if user has access to this ticket
        if (!$this->hasAccessToSupport($support)) {
            return response()->json(['error' => 'You do not have permission to view this ticket.'], 403);
        }

        // Get priority and status options for the modal
        $priority = Support::$priority;
        $status = Support::$status;
        $admins = User::whereIn('type', ['super admin', 'admin'])->get();
        
        return response()->json([
            'support' => $support->load(['createdUser', 'assignUser']),
            'priority' => $priority,
            'status' => $status,
            'admins' => $admins,
            'canEdit' => $this->canEditSupport($support),
            'canReply' => $this->canReplyToSupport($support),
            'user' => $user
        ]);
    }

    /**
     * Reply to ticket via modal
     */
    public function replyModal(Request $request, $id)
    {
        $support = Support::findOrFail($id);
        $user = Auth::user();
        
        // Check if user can reply to this ticket
        if (!$this->canReplyToSupport($support)) {
            return response()->json(['error' => 'You do not have permission to reply to this ticket.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'description' => 'required|string',
            'attachment.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $reply = new SupportReply();
        $reply->support_id = $id;
        $reply->user_id = $user->id;
        $reply->description = $request->description;

        // Handle photo attachments for reply
        if ($request->hasFile('attachment')) {
            $attachments = [];
            $uploadPath = 'upload/support';
            
            // Ensure directory exists
            if (!Storage::exists('public/' . $uploadPath)) {
                Storage::makeDirectory('public/' . $uploadPath);
            }
            
            foreach ($request->file('attachment') as $file) {
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->storeAs('public/' . $uploadPath, $filename);
                $attachments[] = $filename;
            }
            $reply->attachment = json_encode($attachments);
        }

        $reply->save();

        // Update ticket status to open if it was pending
        if ($support->status == 'pending') {
            $support->status = 'open';
            $support->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Reply added successfully.',
            'reply' => $reply->load('user')
        ]);
    }

    /**
     * View reply photo with one-time access and automatic deletion (only for admins)
     */
    public function viewReplyPhoto($replyId, $photoName)
    {
        $reply = SupportReply::findOrFail($replyId);
        $support = Support::findOrFail($reply->support_id);
        $user = Auth::user();
        
        // Check if user has access to this ticket
        if (!$this->hasAccessToSupport($support)) {
            return redirect()->route('support.index')->with('error', 'You do not have permission to view this photo.');
        }

        $filePath = storage_path('public/upload/support/' . $photoName);
        
        // Check if file exists
        if (!file_exists($filePath)) {
            return redirect()->route('support.show', $support->id)->with('error', 'Photo not found.');
        }

        // Get file info
        $fileInfo = pathinfo($filePath);
        $extension = strtolower($fileInfo['extension']);
        
        // Check if it's an image
        if (!in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
            return redirect()->route('support.show', $support->id)->with('error', 'Invalid file type.');
        }

        // Read file content
        $fileContent = file_get_contents($filePath);
        
        // Only delete the file if the user is an admin
        if ($user->type == 'super admin' || $user->type == 'admin') {
            // Delete the file immediately after reading
            unlink($filePath);
            
            // Update the reply record to remove the photo from attachments
            $this->removePhotoFromReplyAttachments($reply, $photoName);
        }

        // Return the image with appropriate headers
        return response($fileContent)
            ->header('Content-Type', $this->getMimeType($extension))
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    /**
     * Remove photo from reply attachments JSON
     */
    private function removePhotoFromReplyAttachments($reply, $photoName)
    {
        if ($reply->attachment) {
            $attachments = json_decode($reply->attachment, true);
            if (is_array($attachments)) {
                $attachments = array_filter($attachments, function($attachment) use ($photoName) {
                    return $attachment !== $photoName;
                });
                $reply->attachment = !empty($attachments) ? json_encode(array_values($attachments)) : null;
                $reply->save();
            }
        }
    }
} 