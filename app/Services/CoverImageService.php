<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class CoverImageService
{
    /**
     * Download and store a cover image locally
     *
     * @param string|null $coverUrl The URL of the cover image
     * @param string $bookIdentifier Unique identifier for the book (e.g., ISBN or timestamp)
     * @return string|null The local path to the stored image
     */
    public function storeCoverFromUrl(?string $coverUrl, string $bookIdentifier): ?string
    {
        if (!$coverUrl) {
            return null;
        }

        try {
            // Download the image
            $response = Http::timeout(10)->get($coverUrl);

            if (!$response->successful()) {
                return null;
            }

            // Get the image content
            $imageContent = $response->body();

            // Determine file extension from content type
            $contentType = $response->header('Content-Type');
            $extension = $this->getExtensionFromContentType($contentType);

            // Generate filename
            $filename = 'covers/' . Str::slug($bookIdentifier) . '-' . time() . '.' . $extension;

            // Store the image
            Storage::disk('public')->put($filename, $imageContent);

            return $filename;
        } catch (\Exception $e) {
            \Log::error('Failed to download cover image: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Store an uploaded cover image
     *
     * @param \Illuminate\Http\UploadedFile $file The uploaded file
     * @param string $bookIdentifier Unique identifier for the book
     * @return string|null The local path to the stored image
     */
    public function storeUploadedCover($file, string $bookIdentifier): ?string
    {
        try {
            // Validate file is an image
            if (!in_array($file->getMimeType(), ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'])) {
                return null;
            }

            // Get file extension
            $extension = $file->getClientOriginalExtension() ?: 'jpg';

            // Generate filename
            $filename = 'covers/' . Str::slug($bookIdentifier) . '-' . time() . '.' . $extension;

            // Store the file
            $path = $file->storeAs('', $filename, 'public');

            return $path;
        } catch (\Exception $e) {
            \Log::error('Failed to upload cover image: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Delete a cover image from storage
     *
     * @param string|null $coverPath The path to the cover image
     * @return bool
     */
    public function deleteCover(?string $coverPath): bool
    {
        if (!$coverPath) {
            return false;
        }

        try {
            if (Storage::disk('public')->exists($coverPath)) {
                return Storage::disk('public')->delete($coverPath);
            }
            return false;
        } catch (\Exception $e) {
            \Log::error('Failed to delete cover image: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get the public URL for a stored cover
     *
     * @param string|null $coverPath The path to the cover image
     * @return string|null
     */
    public function getCoverUrl(?string $coverPath): ?string
    {
        if (!$coverPath) {
            return null;
        }

        return Storage::disk('public')->url($coverPath);
    }

    /**
     * Try to get cover from OpenLibrary as fallback
     *
     * @param string|null $isbn ISBN-10 or ISBN-13
     * @return string|null
     */
    public function getOpenLibraryCover(?string $isbn): ?string
    {
        if (!$isbn) {
            return null;
        }

        // Clean ISBN (remove hyphens and spaces)
        $isbn = str_replace(['-', ' '], '', $isbn);

        // OpenLibrary cover API
        $sizes = ['L', 'M']; // Large, Medium

        foreach ($sizes as $size) {
            $url = "https://covers.openlibrary.org/b/isbn/{$isbn}-{$size}.jpg";

            try {
                // Check if the cover exists (OpenLibrary returns a placeholder for missing covers)
                $response = Http::timeout(5)->get($url);

                if ($response->successful() && $response->header('Content-Length') > 1000) {
                    return $url;
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        return null;
    }

    /**
     * Get file extension from content type
     *
     * @param string|null $contentType
     * @return string
     */
    private function getExtensionFromContentType(?string $contentType): string
    {
        $map = [
            'image/jpeg' => 'jpg',
            'image/jpg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
        ];

        return $map[$contentType] ?? 'jpg';
    }
}
