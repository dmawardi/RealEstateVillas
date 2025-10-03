<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\PropertyAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PropertyAttachmentController extends Controller
{
    /**
     * Get all attachments for a property (for Vue frontend)
     */
    public function index(Property $property)
    {
        
        try {
            $attachments = $property->attachments()
                ->where('is_active', true)
                ->orderBy('order')
                ->orderBy('file_type')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'attachments' => $attachments,
                    'property_id' => $property->id,
                    'total_count' => $attachments->count(),
                    'types_count' => [
                        'images' => $attachments->where('type', 'image')->count(),
                        'documents' => $attachments->where('type', 'document')->count(),
                        'floor_plans' => $attachments->where('type', 'floor_plan')->count(),
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch property attachments', [
                'property_id' => $property->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch attachments',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request, Property $property)
    {
        Log::info('Storing attachment for property', ['property_id' => $property->id]);
        Log::info('Request data', $request->all());
        Log::info('Request files', $request->allFiles());
        Log::info('Has files array:', ['has_files' => $request->hasFile('files')]);

        // Check if files array is present and not empty
        // Additional debugging for the empty file issue
        if ($request->hasFile('files')) {
            $files = $request->file('files');
            Log::info('Files debug info:', [
                'files_count' => count($files),
                'files_details' => collect($files)->map(function($file, $index) {
                    return [
                        'index' => $index,
                        'original_name' => $file->getClientOriginalName(),
                        'size' => $file->getSize(),
                        'mime_type' => $file->getClientMimeType(),
                        'is_valid' => $file->isValid(),
                        'error_code' => $file->getError(),
                        'temp_path' => $file->getRealPath(),
                        'path_name' => $file->getPathname()
                    ];
                })->toArray()
            ]);
        }
        
        // Check if files array is present and not empty
        if (!$request->hasFile('files')) {
            Log::error('No files found in request');
            return response()->json([
                'success' => false,
                'message' => 'No files were uploaded'
            ], 422);
        }
        
        // Get files and check if they're valid
        $files = $request->file('files');
        
        // Check for empty or invalid files
        foreach ($files as $index => $file) {
            if (!$file->isValid()) {
                Log::error("File {$index} is invalid:", [
                    'error_code' => $file->getError(),
                    'size' => $file->getSize(),
                    'name' => $file->getClientOriginalName()
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => "File '{$file->getClientOriginalName()}' is invalid or corrupted"
                ], 422);
            }
            
            if ($file->getSize() == 0) {
                Log::error("File {$index} is empty:", [
                    'name' => $file->getClientOriginalName(),
                    'size' => $file->getSize()
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => "File '{$file->getClientOriginalName()}' is empty"
                ], 422);
            }
        }

        Log::info('is_visible_to_customer:', ['value' => $request->input('is_visible_to_customer')]);
        
        // Simplified validation - focus on files array only
        $validated = $request->validate([
            // Primary validation for multiple files
            'files' => 'required|array|min:1|max:10',
            'files.*' => 'required|file|mimes:jpg,jpeg,png,pdf,doc,docx,webp|max:10024', // Max 10MB per file
            
            // Optional fields
            'title' => 'nullable|string|max:255',
            'titles' => 'nullable|array',
            'titles.*' => 'nullable|string|max:255',
            'type' => 'nullable|string|in:image,document,floor_plan',
            'caption' => 'nullable|string|max:500',
            'captions' => 'nullable|array',
            'captions.*' => 'nullable|string|max:500',
            'is_visible_to_customer' => 'sometimes|nullable|boolean',
            'order' => 'nullable|integer|min:0',
        ]);

        try {
            Log::info('Processing ' . count($files) . ' files');
            
            // Debug each file before processing
            foreach ($files as $index => $file) {
                Log::info("File {$index}:", [
                    'name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'mime' => $file->getClientMimeType(),
                    'is_valid' => $file->isValid(),
                    'error' => $file->getError(),
                    'temp_path' => $file->getRealPath()
                ]);
            }
            
            $this->handleAttachments($files, $property);

            // Get the newly created attachments for the response
            $uploadedAttachments = $property->attachments()
                ->where('is_active', true)
                ->orderBy('created_at', 'desc')
                ->take(count($files))
                ->get()
                ->map(function ($attachment) {
                    return [
                        'id' => $attachment->id,
                        'title' => $attachment->title,
                        'path' => $attachment->path,
                        'original_filename' => $attachment->original_filename,
                        'file_type' => $attachment->file_type,
                        'file_size' => $attachment->file_size,
                        'type' => $attachment->type,
                        'caption' => $attachment->caption,
                        'is_visible_to_customer' => $attachment->is_visible_to_customer,
                        'order' => $attachment->order,
                        'created_at' => $attachment->created_at,
                    ];
                });

            Log::info('Attachments uploaded successfully', [
                'property_id' => $property->id,
                'files_count' => count($files),
                'uploaded_count' => $uploadedAttachments->count()
            ]);

            return response()->json([
                'success' => true,
                'message' => $uploadedAttachments->count() . ' file(s) uploaded successfully',
                'data' => [
                    'attachments' => $uploadedAttachments,
                    'uploaded_count' => $uploadedAttachments->count(),
                    'failed_count' => 0,
                    'errors' => []
                ]
            ], 201);

        } catch (\Exception $e) {
            Log::error('Failed to store property attachment', [
                'property_id' => $property->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to store attachment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

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

     // S3 Helper functions
    // This method handles the S3 upload of an image
    private function handleS3Upload($file, $propertyId)
    {
        // Handle S3 upload logic here
        $path = $file->store('properties/' . $propertyId . "/attachments" , 's3');
        return $path;
    }

    /**
     * Helper method to get next order number
     */
    private function getNextOrderNumber(Property $property): int
    {
        $maxOrder = $property->attachments()
            ->where('is_active', true)
            ->max('order');
        
        return $maxOrder ? $maxOrder + 1 : 0;
    }

    /**
     * Helper method to determine file type from MIME type
     */
    private function determineFileType(string $mimeType): string
    {
        if (str_starts_with($mimeType, 'image/')) {
            return 'image';
        }
        
        if (in_array($mimeType, [
            'application/pdf', 
            'application/msword', 
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ])) {
            return 'document';
        }
        
        return 'document'; // Default to document
    }
    /**
     * Handle multiple attachments upload
     */
    private function handleAttachments($attachments, $property)
    {
        foreach ($attachments as $attachment) {
            try {
                // Handle S3 upload using your existing method
                $path = $this->handleS3Upload($attachment, $property->id);

                // Get next order number for this attachment
                $order = $this->getNextOrderNumber($property);

                // Determine file type
                $fileType = $this->determineFileType($attachment->getClientMimeType());

                // Create a new PropertyAttachment instance
                $propertyAttachment = new PropertyAttachment();
                $propertyAttachment->property_id = $property->id;
                $propertyAttachment->title = pathinfo($attachment->getClientOriginalName(), PATHINFO_FILENAME);
                $propertyAttachment->path = $path;
                $propertyAttachment->original_filename = $attachment->getClientOriginalName();
                $propertyAttachment->file_type = $attachment->getClientMimeType();
                $propertyAttachment->file_size = $attachment->getSize();
                $propertyAttachment->type = $fileType;
                $propertyAttachment->caption = null;
                $propertyAttachment->is_visible_to_customer = true;
                $propertyAttachment->is_active = true;
                // Set order to 0 for PDFs, otherwise use the next order number
                $propertyAttachment->order = $fileType == 'pdf' ? 0 : $order;
                $propertyAttachment->save();

                Log::info('Individual attachment saved', [
                    'id' => $propertyAttachment->id,
                    'filename' => $attachment->getClientOriginalName(),
                    'size' => $attachment->getSize()
                ]);

            } catch (\Exception $e) {
                Log::error('Failed to process individual attachment', [
                    'filename' => $attachment->getClientOriginalName(),
                    'error' => $e->getMessage()
                ]);
                throw $e; // Re-throw to be caught by parent try-catch
            }
        }
    }
}
