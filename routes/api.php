<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/upload', function (Request $request) {
    try {
        if (!$request->hasFile('image')) {
            return response()->json([
                'success' => false,
                'message' => 'No file uploaded'
            ], 400);
        }

        $file = $request->file('image');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $file->storeAs('uploads', $fileName, 'public');

        return response()->json([
            'success' => true,
            'message' => 'File uploaded successfully',
            'data' => [
                'image' => $fileName,
                'path' => asset('uploads/' . $fileName)
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Upload failed: ' . $e->getMessage()
        ], 500);
    }
});

<<<<<<< HEAD
=======

>>>>>>> upstream/main
Route::post('/callback/midtrans', function (Request $request) {
    $controller = new \App\api\callback\MidtransController();
    return $controller->notification($request);
});
