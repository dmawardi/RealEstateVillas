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
        // Updated validation to handle both single and multiple files
        $validated = $request->validate([
            // Primary validation for multiple files
            'files' => 'required|array|min:1|max:10',
            'files.*' => 'required|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048',
            
            // Optional single file (for backward compatibility)
            'file' => 'sometimes|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048',
            
            // Optional fields
            'title' => 'nullable|string|max:255',
            'titles' => 'nullable|array',
            'titles.*' => 'nullable|string|max:255',
            'type' => 'nullable|string|in:image,document,floor_plan',
            'caption' => 'nullable|string|max:500',
            'captions' => 'nullable|array',
            'captions.*' => 'nullable|string|max:500',
            'is_visible_to_customer' => 'nullable|boolean',
            'order' => 'nullable|integer|min:0',
        ]);

        try {
            $uploadedAttachments = [];
            $errors = [];
            
            // Handle both single file and multiple files
            $files = [];
            if ($request->hasFile('file')) {
                // Single file upload (backward compatibility)
                $files = [$request->file('file')];
            } elseif ($request->hasFile('files')) {
                // Multiple file upload
                $files = $request->file('files');
            }

            foreach ($files as $index => $file) {
                try {
                    // Handle S3 upload using your existing method
                    $path = $this->handleS3Upload($file, $property->id);

                    // Get next order number for this attachment
                    $order = $this->getNextOrderNumber($property);

                    // Determine file type
                    $fileType = $validated['type'] ?? $this->determineFileType($file->getClientMimeType());

                    // Get title and caption for this specific file
                    $title = $this->getFileTitle($validated, $index, $file);
                    $caption = $this->getFileCaption($validated, $index);

                    // Create a new PropertyAttachment instance (using your existing pattern)
                    $attachment = new PropertyAttachment();
                    $attachment->property_id = $property->id;
                    $attachment->title = $title;
                    $attachment->path = $path;
                    $attachment->original_filename = $file->getClientOriginalName();
                    $attachment->file_type = $file->getClientMimeType();
                    $attachment->file_size = $file->getSize();
                    $attachment->type = $fileType;
                    $attachment->caption = $caption;
                    $attachment->is_visible_to_customer = $validated['is_visible_to_customer'] ?? true;
                    $attachment->is_active = true;
                    $attachment->order = $validated['order'] ?? $order;
                    $attachment->save();

                    $uploadedAttachments[] = [
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

                    Log::info('Attachment uploaded successfully', [
                        'attachment' => $attachment,
                        'property_id' => $property->id,
                        'filename' => $file->getClientOriginalName()
                    ]);

                } catch (\Exception $e) {
                    $errors[] = [
                        'file' => $file->getClientOriginalName(),
                        'error' => $e->getMessage()
                    ];
                    
                    Log::error('Failed to upload individual attachment', [
                        'property_id' => $property->id,
                        'file' => $file->getClientOriginalName(),
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Return results based on upload type
            if (count($files) === 1 && !empty($uploadedAttachments)) {
                // Single file upload - return single attachment (backward compatibility)
                return response()->json([
                    'success' => true,
                    'data' => $uploadedAttachments[0]
                ]);
            }

            // Multiple file upload - return batch results
            if (empty($uploadedAttachments) && !empty($errors)) {
                return response()->json([
                    'success' => false,
                    'message' => 'All uploads failed',
                    'errors' => $errors
                ], 422);
            }

            $message = count($uploadedAttachments) . ' file(s) uploaded successfully';
            if (count($errors) > 0) {
                $message .= ', ' . count($errors) . ' file(s) failed';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'attachments' => $uploadedAttachments,
                    'uploaded_count' => count($uploadedAttachments),
                    'failed_count' => count($errors),
                    'errors' => $errors
                ]
            ], 201);

        } catch (\Exception $e) {
            Log::error('Failed to store property attachment', [
                'property_id' => $property->id,
                'error' => $e->getMessage()
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
     * Helper method to get title for a specific file
     */
    private function getFileTitle(array $validated, int $index, $file): string
    {
        // For single file upload, use the 'title' field
        if (isset($validated['title']) && count($validated) === 1) {
            return $validated['title'];
        }
        
        // For multiple files, use titles array or default to filename
        if (isset($validated['titles'][$index])) {
            return $validated['titles'][$index];
        }
        
        // Default to original filename without extension
        return pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
    }

    /**
     * Helper method to get caption for a specific file
     */
    private function getFileCaption(array $validated, int $index): ?string
    {
        // For single file upload, use the 'caption' field
        if (isset($validated['caption'])) {
            return $validated['caption'];
        }
        
        // For multiple files, use captions array
        if (isset($validated['captions'][$index])) {
            return $validated['captions'][$index];
        }
        
        return null;
    }
}
