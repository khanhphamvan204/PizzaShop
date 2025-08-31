<?php
// 15. FaqController.php
namespace App\Http\Controllers;

use App\Models\Faq;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Exception;

class FaqController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Faq::query();

            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('question', 'like', "%{$search}%")
                        ->orWhere('answer', 'like', "%{$search}%");
                });
            }

            $faqs = $query->orderBy('created_at', 'desc')->get();
            return response()->json($faqs);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch FAQs',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'question' => 'required|string|max:255',
                'answer' => 'required|string'
            ]);

            $faq = Faq::create($request->only(['question', 'answer']));
            return response()->json($faq, 201);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to create FAQ',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $faq = Faq::findOrFail($id);
            return response()->json($faq);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'FAQ not found'
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch FAQ',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'question' => 'required|string|max:255',
                'answer' => 'required|string'
            ]);

            $faq = Faq::findOrFail($id);
            $faq->update($request->only(['question', 'answer']));
            return response()->json($faq);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $e->errors()
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'FAQ not found'
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to update FAQ',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $faq = Faq::findOrFail($id);
            $faq->delete();
            return response()->json(['message' => 'FAQ deleted successfully']);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'FAQ not found'
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to delete FAQ',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
