<?php
// 16. ContactController.php
namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ContactController extends Controller
{

    public function index(Request $request)
    {
        try {
            $query = Contact::with('user');

            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('message', 'like', "%{$search}%");
                });
            }

            $contacts = $query->orderBy('created_at', 'desc')->get();

            return response()->json([
                'success' => true,
                'data' => $contacts
            ]);
        } catch (\Exception $e) {
            Log::error("Contact index error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch contacts'
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:100',
                'email' => 'required|email|max:100',
                'message' => 'required|string'
            ]);

            $contactData = [
                'name' => $request->name,
                'email' => $request->email,
                'message' => $request->message
            ];

            $contact = Contact::create($contactData);

            return response()->json([
                'success' => true,
                'message' => 'Contact created successfully',
                'data' => $contact
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error("Contact store error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to create contact'
            ], 500);
        }
    }

    /**
     * Store phiên bản yêu cầu login
     */
    // public function store(Request $request)
    // {
    //     try {
    //         if (!Auth::check()) {
    //             return response()->json(['error' => 'Bạn phải đăng nhập để gửi phản hồi'], 401);
    //         }
    //
    //         $user = Auth::user();
    //
    //         $request->validate([
    //             'message' => 'required|string'
    //         ]);
    //
    //         $contactData = [
    //             'user_id' => $user->id,
    //             'email' => $user->email,
    //             'message' => $request->message
    //         ];
    //
    //         $contact = Contact::create($contactData);
    //
    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Contact created successfully',
    //             'data' => $contact->load('user')
    //         ], 201);
    //     } catch (ValidationException $e) {
    //         return response()->json([
    //             'success' => false,
    //             'errors' => $e->errors()
    //         ], 422);
    //     } catch (\Exception $e) {
    //         Log::error("Contact store (auth) error: " . $e->getMessage());
    //         return response()->json([
    //             'success' => false,
    //             'error' => 'Failed to create contact'
    //         ], 500);
    //     }
    // }
    public function show($id)
    {
        try {
            $contact = Contact::with('user')->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $contact
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Contact not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error("Contact show error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch contact'
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $contact = Contact::findOrFail($id);
            $contact->delete();

            return response()->json([
                'success' => true,
                'message' => 'Contact deleted successfully'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Contact not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error("Contact destroy error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to delete contact'
            ], 500);
        }
    }
}
