<?php

namespace App\Http\Controllers\API;

use App\Document;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Spatie\Browsershot\Browsershot;
use App\Http\Controllers\Controller;
use App\Http\Requests\DocumentRequest;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\DocumentUpdateRequest;

class DocumentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return DocumentResource::collection(
            Document::all()
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(DocumentRequest $request)
    {
        $pdfFile = ($request->has('url') ? Browsershot::url($request->url) : Browsershot::html($request->html))
            ->setNodeBinary(env('NODE_PATH'))
            ->setNpmBinary(env('NPM_PATH'))
            ->noSandbox()
            ->disableJavascript()
            ->setOption(($request->has('orientation') ? $request->orientation : 'portrait'), true)
            ->format('a4')
            ->showBackground()
            ->emulateMedia('print')
            ->margins(10, 0, 0, 10)
            ->pdf();
        
        $filePath = '/'.env('APP_ENV').'/documents/'.Str::random(32).'.pdf';

        $storagePath = Storage::disk('s3')->put($filePath, $pdfFile, $request->visibility);
        // $storagePath = Storage::put($filePath, $pdf);

        if(! $storagePath) {
            return response()->json([
                'message' => 'Problem uploading document.'
            ], 400);
        }
            
        $request->merge([
            'file_path' => $filePath,
        ]);
    
        $document = Document::create($request->only([
            'name',
            'description',
            'orientation',
            'visibility',
            'json'
        ]));

        if($request->has('stream_file') && $request->stream_file) {
            return response($pdfFile)->header('Content-Type', 'application/pdf');
        }

        $document = determineFileUrl($request, $document);
        
        return new DocumentResource($document); 
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Document  $document
     * @return \Illuminate\Http\Response
     */
    public function show(Document $document)
    {
        $document = fileUrl($request, $document);
        return new DocumentResource($document); 
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Document  $document
     * @return \Illuminate\Http\Response
     */
    public function update(DocumentUpdateRequest $request, Document $document)
    {
        $document->update($request->only([
            'name',
            'description'
        ]));

        return new DocumentResource($document); 
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Document  $document
     * @return \Illuminate\Http\Response
     */
    public function destroy(Document $document)
    {
        Storage::disk('s3')->delete($document->file_path);
        $document->delete();

        return response()->json(null, 204);
    }

    /**
     * Determine how to return the URL of a document based on it's visibility
     *
     * @param  $request
     * @param  \App\Document  $document
     * @return Object
     */
    protected function determineFileUrl(Object $request, \App\Document $document) : Object
    {
        if($document->visibility == 'public') {
            $document['public_url'] = Storage::disk('s3')->url($document->file_path);
        }

        if($document->visibility == 'private') {
            $document['temporary_url'] = Storage::disk('s3')->temporaryUrl(
                ltrim($document->file_path, '/'), now()->addMinutes(
                    $request->expiry ?: 5
                )
            );
        }

        return $document;    
    }
}
