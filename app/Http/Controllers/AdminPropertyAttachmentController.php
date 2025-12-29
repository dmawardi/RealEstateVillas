<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\PropertyAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AdminPropertyAttachmentController extends Controller
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
        // Check if files array is present and not empty
        if (!$request->hasFile('files')) {
            return back()->withErrors(['files' => 'No files were uploaded']);
        }
        
        // Get files and check if they're valid
        $files = $request->file('files');
        $duplicateFiles = [];
        
        // Check for empty or invalid files
        foreach ($files as $index => $file) {
            if (!$file->isValid()) {
                return back()->withErrors(['files' => "File '{$file->getClientOriginalName()}' is invalid or corrupted"]);
            }
            
            if ($file->getSize() == 0) {
                return back()->withErrors(['files' => "File '{$file->getClientOriginalName()}' is empty"]);
            }

            // Check if file with same original_filename already exists for this property
            $existingAttachment = $property->attachments()
                ->where('is_active', true)
                ->where('original_filename', $file->getClientOriginalName())
                ->first();

            if ($existingAttachment) {
                $duplicateFiles[] = $file->getClientOriginalName();
            }
        }

        // If there are duplicate files, return error with all duplicates listed
        if (!empty($duplicateFiles)) {
            return back()->withErrors([
                'files' => 'The following files already exist and would be overwritten: ' . implode(', ', $duplicateFiles)
            ]);
        }
        
        // Validation
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
            $this->handleAttachments($files, $property);

            $uploadedCount = count($files);
            $message = $uploadedCount . ' file(s) uploaded successfully';
            
            return back()->with('success', $message);

        } catch (\Exception $e) {
            Log::error('Failed to store property attachment', [
                'property_id' => $property->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->withErrors(['files' => 'Failed to store attachment: ' . $e->getMessage()]);
        }
    }

    /**
     * Update an attachment (only database fields, not the file itself)
     */
    public function update(Request $request, PropertyAttachment $attachment)
    {
        // Validate the request - only allow updating non-file fields
        $request->validate([
            'title' => 'nullable|string|max:255',
            'caption' => 'nullable|string|max:500',
            'type' => 'nullable|string|in:image,document,floor_plan',
            'is_visible_to_customer' => 'nullable|boolean',
            'order' => 'nullable|integer|min:0',
        ]);

        try {
            // Get only the fields that can be updated (exclude file-related fields)
            $updateData = $request->only([
                'title',
                'caption', 
                'type',
                'is_visible_to_customer',
                'order'
            ]);

            // Remove any null values to avoid overwriting existing data with nulls
            $updateData = array_filter($updateData, function($value) {
                return $value !== null;
            });

            // Update the attachment
            $attachment->update($updateData);

            Log::info('Attachment updated successfully', [
                'attachment_id' => $attachment->id,
                'property_id' => $attachment->property_id,
                'updated_fields' => array_keys($updateData),
                'original_filename' => $attachment->original_filename
            ]);

            return back()->with('success', 'Attachment updated successfully');

        } catch (\Exception $e) {
            Log::error('Failed to update attachment', [
                'attachment_id' => $attachment->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->withErrors(['attachment' => 'Failed to update attachment: ' . $e->getMessage()]);
        }
    }


    public function destroy(PropertyAttachment $attachment)
    {
        try {
            // Attempt to delete the file from S3
            if ($attachment->path) {
                try {
                    Storage::disk('s3')->delete($attachment->path);
                } catch (\Exception $e) {
                    Log::error('Failed to delete attachment file from S3', [
                        'attachment_id' => $attachment->id,
                        'path' => $attachment->path,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            // Delete the attachment record from the database
            $attachment->delete();
            
            return back()->with('success', 'Attachment deleted successfully.');
            
        } catch (\Exception $e) {
            Log::error('Failed to delete attachment', [
                'attachment_id' => $attachment->id,
                'error' => $e->getMessage()
            ]);
            
            return back()->withErrors(['attachment' => 'Failed to delete attachment: ' . $e->getMessage()]);
        }
    }

     // S3 Helper functions
    // This method handles the S3 upload of an image
    private function handleS3Upload($file, $propertyId)
    {
        // Handle S3 upload logic here
        $path = $file->store('properties/' . $propertyId . "/attachments" , [
            'disk' => 's3',
            'visibility' => 'public'
        ]);

        if (!$path) {
            throw new \Exception('S3 upload failed - store returned false.');
        }

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
