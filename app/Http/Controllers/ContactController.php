<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Contact;
use App\Models\Subscription;
use Illuminate\Http\Request;

class ContactController extends Controller
{

public function index()
{
    $userId = \Auth::id();

    if (\Auth::user()->can('manage contact')) {
        // Inbox = messages where I am the receiver
        $inbox = Contact::where('parent_id', $userId)
            ->orderBy('created_at', 'desc')
            ->take(8)
            ->get();

        // Sent = messages where I am the sender
        $sent = Contact::where('created_by', $userId)
            ->orderBy('created_at', 'desc')
            ->take(8)
            ->get();

        return view('contact.index', compact('inbox', 'sent'));
    }

    return redirect()->back()->with('error', __('Permission denied.'));
}




    public function create()
    {
        return view('contact.create');
    }

public function store(Request $request)
{
    if (\Auth::user()->can('create contact')) {
        $validator = \Validator::make(
            $request->all(),
            [
                'name'    => 'required',
                'email'   => 'required|email', // receiver email
                'subject' => 'required',
                'message' => 'required',
            ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }

        // Find the user who will receive the message based on the email
        $receiver = User::where('email', $request->email)->first();

        if (!$receiver) {
            return redirect()->back()->with('error', __('Receiver not found.'));
        }

        $contact = new Contact();
        $contact->name           = $request->name;
        $contact->email          = $request->email;
        $contact->contact_number = $request->contact_number;
        $contact->subject        = $request->subject;
        $contact->message        = $request->message;

        // Set sender and receiver
        $contact->created_by = \Auth::id();   // sender (logged-in user)
        $contact->parent_id  = $receiver->id; // receiver ID

        $contact->save();

        return redirect()->back()->with('success', __('Contact successfully created.'));
    } else {
        return redirect()->back()->with('error', __('Permission denied.'));
    }
}




    public function show(Contact $contact)
    {
        //
    }


    public function edit(Contact $contact)
    {
        return view('contact.edit', compact('contact'));
    }


    public function update(Request $request, Contact $contact)
    {
        if (\Auth::user()->can('edit contact') ) {
            $validator = \Validator::make(
                $request->all(), [
                    'name' => 'required',
                    'subject' => 'required',
                    'message' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }


            $contact->name = $request->name;
            $contact->email = $request->email;
            $contact->contact_number = $request->contact_number;
            $contact->subject = $request->subject;
            $contact->message = $request->message;
            $contact->save();

            return redirect()->back()->with('success', __('Contact successfully updated.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

    }


    public function destroy(Contact $contact)
    {
        if (\Auth::user()->can('edit contact') ) {
            $contact->delete();

            return redirect()->back()->with('success', 'Contact successfully deleted.');
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

    }
public function search(Request $request) {
    $q = $request->get('q');
    $authUser = \Auth::user();
    $hostandEmail = 'servizi.atman@gmail.com';

    // Build the base query
    $query = User::query();

    // If user type is 'owner', always include Hostand user
    if ($authUser && $authUser->type == 'owner') {
        // Get Hostand user
        $hostandUser = User::where('email', $hostandEmail)->first(['id','first_name','last_name','email','phone_number']);
        
        // Build search query for other users
        if ($q) {
            $query->where(function($subQuery) use ($q) {
                $subQuery->where('first_name', 'like', "%$q%")
                         ->orWhere('last_name', 'like', "%$q%");
            });
        } else {
            // If no search query, return empty for regular users (only Hostand will show)
            $query->whereRaw('1 = 0'); // This ensures no regular users are returned
        }
        
        $users = $query->get(['id','first_name','last_name','email','phone_number']);
        
        // Add Hostand user if it exists and matches the search (or if no search query)
        if ($hostandUser) {
            $shouldIncludeHostand = true;
            
            // If there's a search query, check if it matches Hostand
            if ($q) {
                $qLower = strtolower($q);
                $name = strtolower(trim($hostandUser->first_name . ' ' . $hostandUser->last_name));
                $email = strtolower($hostandUser->email);
                
                // Only include if query matches name or email
                if (strpos($name, $qLower) === false && strpos($email, $qLower) === false) {
                    $shouldIncludeHostand = false;
                }
            }
            
            // Add Hostand to results if it should be included
            if ($shouldIncludeHostand) {
                $users = $users->push($hostandUser)->unique('id');
            }
        }
    } else {
        // For non-owners, normal search behavior
        if ($q) {
            $users = $query->where(function($subQuery) use ($q) {
                    $subQuery->where('first_name', 'like', "%$q%")
                             ->orWhere('last_name', 'like', "%$q%");
                })
                ->get(['id','first_name','last_name','email','phone_number']);
        } else {
            // Return initial results when dropdown is opened (limit to prevent too many results)
            $users = $query->orderBy('first_name')
                           ->limit(50)
                           ->get(['id','first_name','last_name','email','phone_number']);
        }
    }

    // Wrap in results
    return response()->json([
        'results' => $users->map(function($user) {
            return [
                'id' => $user->id,
                'text' => $user->first_name . ' ' . $user->last_name,
                'email' => $user->email,
                'contact' => $user->phone_number
            ];
        })
    ]);
}


}
