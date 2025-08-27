<?php
// 16. ContactController.php
namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContactController extends Controller
{
    public function index(Request $request)
    {
        $query = Contact::with('user');

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('message', 'like', "%{$search}%");
            });
        }

        $contacts = $query->orderBy('created_at', 'desc')->paginate(15);
        return response()->json($contacts);
    }

    public function store(Request $request)
    {
        $validationRules = [
            'message' => 'required|string'
        ];

        if (Auth::check()) {
            $validationRules['email'] = 'required|email';
        } else {
            $validationRules['name'] = 'required|string|max:100';
            $validationRules['email'] = 'required|email|max:100';
        }

        $request->validate($validationRules);

        $contactData = [
            'message' => $request->message,
            'email' => $request->email
        ];

        if (Auth::check()) {
            $contactData['user_id'] = Auth::id();
        } else {
            $contactData['name'] = $request->name;
        }

        $contact = Contact::create($contactData);
        return response()->json($contact->load('user'), 201);
    }

    public function show($id)
    {
        $contact = Contact::with('user')->findOrFail($id);
        return response()->json($contact);
    }

    public function destroy($id)
    {
        $contact = Contact::findOrFail($id);
        $contact->delete();
        return response()->json(['message' => 'Contact deleted successfully']);
    }
}