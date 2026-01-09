<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CoverImageService
{
    /**
     * Download and store a cover image from a URL
     *
     * @param string $url The URL of the cover image
     * @param string|null $bookIdentifier Optional identifier for the filename (ISBN, ID, etc.)
     * @return string|null The local path to the stored image, or null on failure
     */
    public function downloadAndStore(string $url, ?string $bookIdentifier = null): ?string
    {
        try {
            // Validate URL
            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                Log::warning('Invalid cover URL provided', ['url' => $url]);
                return null;
            }

            // Download the image
            $response = Http::timeout(30)->get($url);

            if (!$response->successful()) {
                Log::warning('Failed to download cover image', [
                    'url' => $url,
                    'status' => $response->status(),
                ]);
                return null;
            }

            // Get the image content
            $imageContent = $response->body();

            // Validate that it's actually an image
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->buffer($imageContent);

            if (!str_starts_with($mimeType, 'image/')) {
                Log::warning('Downloaded file is not an image', [
                    'url' => $url,
                    'mime_type' => $mimeType,
                ]);
                return null;
            }

            // Determine file extension
            $extension = match ($mimeType) {
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/gif' => 'gif',
                'image/webp' => 'webp',
                default => 'jpg',
            };

            // Generate filename
            $filename = $this->generateFilename($bookIdentifier, $extension);

            // Ensure the covers directory exists
            Storage::disk('public')->makeDirectory('covers');

            // Store the image
            $path = 'covers/' . $filename;
            Storage::disk('public')->put($path, $imageContent);

            Log::info('Cover image stored successfully', [
                'url' => $url,
                'path' => $path,
            ]);

            return $path;

        } catch (\Exception $e) {
            Log::error('Error downloading and storing cover image', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Generate a unique filename for the cover image
     *
     * @param string|null $identifier Optional identifier (ISBN, book ID, etc.)
     * @param string $extension File extension
     * @return string The generated filename
     */
    protected function generateFilename(?string $identifier, string $extension): string
    {
        if ($identifier) {
            // Sanitize the identifier for use in filename
            $sanitized = preg_replace('/[^a-zA-Z0-9_-]/', '_', $identifier);
            return $sanitized . '_' . time() . '.' . $extension;
        }

        // Generate a random filename if no identifier provided
        return uniqid('cover_', true) . '.' . $extension;
    }

    /**
     * Delete a cover image from storage
     *
     * @param string $path The path to the image file
     * @return bool True if deleted successfully, false otherwise
     */
    public function delete(string $path): bool
    {
        try {
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
                Log::info('Cover image deleted', ['path' => $path]);
                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Error deleting cover image', [
                'path' => $path,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get the public URL for a stored cover image
     *
     * @param string $path The storage path
     * @return string The public URL
     */
    public function getPublicUrl(string $path): string
    {
        return Storage::disk('public')->url($path);
    }
}
