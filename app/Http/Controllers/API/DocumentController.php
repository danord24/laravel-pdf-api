<?php

namespace App\Http\Controllers\API;

use App\Document;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Spatie\Browsershot\Browsershot;
use App\Http\Controllers\Controller;
use App\Http\Requests\DocumentRequest;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\DocumentResource;
use App\Http\Requests\DocumentUpdateRequest;
use App\Http\Traits\DocumentTrait;

class DocumentController extends Controller
{
    use DocumentTrait;

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
            // ->margins(10, 0, 0, 10)
            ->pdf();

        $filePath = '/' . env('APP_ENV') . '/documents/' . Str::random(32) . '.pdf';

        $storagePath = Storage::disk('s3')->put($filePath, $pdfFile, $request->visibility);

        if (!$storagePath) {
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
            'file_path',
            'orientation',
            'visibility',
            'json'
        ]));

        if ($request->has('stream_file') && $request->stream_file) {
            return response($pdfFile)->header('Content-Type', 'application/pdf');
        }

        $document = $this->determineFileUrl($request, $document);

        return new DocumentResource($document);
    }

    /**
     * Display the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Document  $document
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, Document $document)
    {
        $document = $this->determineFileUrl($request, $document);
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

        $document = $this->determineFileUrl($request, $document);

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
}
