<?php

namespace App\Http\Traits;

use Illuminate\Support\Facades\Storage;


trait DocumentTrait
{
    /**
     * Determine how to return the URL of a document based on it's visibility
     *
     * @param  $request
     * @param  \App\Document  $document
     * @return Object
     */
    protected function determineFileUrl(Object $request, \App\Document $document): Object
    {
        if ($document->visibility == 'public') {
            $document['public_url'] = Storage::disk('s3')->url($document->file_path);
        }

        if ($document->visibility == 'private') {
            $document['temporary_url'] = Storage::disk('s3')->temporaryUrl(
                ltrim($document->file_path, '/'),
                now()->addMinutes(
                    $request->expiry ?: 5
                )
            );
        }

        return $document;
    }
}
