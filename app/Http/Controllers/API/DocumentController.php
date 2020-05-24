<?php

namespace App\Http\Controllers\API;

use App\Document;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Spatie\Browsershot\Browsershot;
use App\Http\Controllers\Controller;
use App\Http\Requests\DocumentRequest;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(DocumentRequest $request)
    {

        $filePath = '/'.env('APP_ENV').'/documents/'.Str::random(32).'.pdf';

        $pdf = ($request->has('url') ? Browsershot::url($request->url) : Browsershot::html($request->html))
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

        $storagePath = Storage::put($filePath, $pdf);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Document  $document
     * @return \Illuminate\Http\Response
     */
    public function show(Document $document)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Document  $document
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Document $document)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Document  $document
     * @return \Illuminate\Http\Response
     */
    public function destroy(Document $document)
    {
        //
    }
}
