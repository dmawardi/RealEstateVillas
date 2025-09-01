<?php

namespace App\Http\Controllers;

use App\Models\PropertyAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PropertyAttachmentController extends Controller
{
    public function destroy(Request $request, $id)
    {
        $attachment = PropertyAttachment::findOrFail($id);
        $attachment->delete();

        // Check if this is an API call or web call
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'message' => 'Attachment deleted successfully.'
            ]);
        }

        // Web request - return redirect
        return redirect()->back()->with('success', 'Attachment deleted successfully.');
    
    }
}
