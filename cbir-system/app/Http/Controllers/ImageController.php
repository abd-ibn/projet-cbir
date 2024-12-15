<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Image;
use App\Models\Category;
use Illuminate\Support\Facades\Storage;

use Intervention\Image\ImageManager;


use Illuminate\Support\Facades\Http;


class ImageController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::all();

        $query = Image::with('category');

        if ($request->has('category_id') && $request->category_id) {
            $query->where('category_id', $request->category_id); 
        }

        $images = $query->get(); 

        return view('images.index', compact('images', 'categories'));
    }

    public function upload(Request $request)
    {
        $request->validate([
            'image.*' => 'required|image|mimes:jpeg,png,jpg|max:2048', // Validate each image in the array
            'category_id' => 'required|exists:categories,id',
        ]);

        $categoryId = $request->category_id;
        $images = $request->file('image');

        foreach ($images as $image) {
            // Generate a unique filename
            $fileName = time() . '_' . $image->getClientOriginalName();
            $filePath = $image->storeAs('uploads', $fileName, 'public');

            // Store each image as a new row in the database
            Image::create([
                'name' => $fileName,
                'filepath' => $filePath,
                'category_id' => $categoryId,
            ]);
        }

        return redirect()->back()->with('success', 'Images uploaded successfully!');
    }


    public function destroy($id)
    {
        $image = Image::findOrFail($id);

        // Delete the file from storage
        Storage::disk('public')->delete($image->filepath);

        // Delete the record from the database
        $image->delete();

        return redirect()->back()->with('success', 'Image deleted successfully!');
    }

    public function download($id)
    {
        $image = Image::findOrFail($id);

        // Ensure the file exists before attempting to download
        if (file_exists(storage_path('app/public/' . $image->filepath))) {
            return response()->download(storage_path('app/public/' . $image->filepath));
        } else {
            return redirect()->route('images.index')->with('error', 'File not found.');
        }
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->ids;
        if (!$ids || !is_array($ids)) {
            return response()->json(['success' => false, 'message' => 'Invalid request.']);
        }

        foreach ($ids as $id) {
            $image = Image::find($id);
            if ($image) {
                Storage::delete('public/' . $image->filepath);
                $image->delete();
            }
        }

        return response()->json(['success' => true, 'message' => 'Images deleted successfully.']);
    }

    public function bulkDownload(Request $request)
    {
        $selectedImages = $request->input('selected_images');

        if (empty($selectedImages)) {
            return response()->json([
                'success' => false,
                'message' => 'Nothing is selected yet',
            ]);
        }

        $downloadLinks = [];

        foreach ($selectedImages as $imageId) {
            $image = Image::find($imageId);

            if ($image) {
                $filePath = storage_path('app/public/' . $image->filepath);

                if (file_exists($filePath)) {
                    // Generate a download link for each image
                    $downloadLinks[] = asset('storage/' . $image->filepath);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'One or more files do not exist',
                    ]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'downloadLinks' => $downloadLinks,
        ]);
    }







    public function edit($id)
    {
        $image = Image::findOrFail($id);
        return view('images.transform', compact('image'));
    }

    public function transform($id, Request $request)
    {
        // Retrieve the image from the database
        $image = Image::findOrFail($id);

        // Get crop dimensions from the request
        $cropWidth = $request->input('crop_width');
        $cropHeight = $request->input('crop_height');

        // Construct the path to the original image
        $originalPath = public_path('storage/' . $image->filepath);

        // Extract file name and extension
        $fileInfo = pathinfo($image->filepath);
        $fileName = $fileInfo['filename']; // Name without extension
        $fileExtension = $fileInfo['extension']; // Original extension

        // Open the image file (assumes JPEG; expand logic for other formats if needed)
        if ($fileExtension === 'jpg' || $fileExtension === 'jpeg') {
            $originalImage = imagecreatefromjpeg($originalPath);
        } elseif ($fileExtension === 'png') {
            $originalImage = imagecreatefrompng($originalPath);
        } else {
            return redirect()->back()->with('error', 'Unsupported image format.');
        }

        // Get the original image dimensions
        $originalWidth = imagesx($originalImage);
        $originalHeight = imagesy($originalImage);

        // Ensure crop dimensions are valid
        $cropWidth = min($cropWidth, $originalWidth);
        $cropHeight = min($cropHeight, $originalHeight);

        // Create a new blank image for the cropped version
        $croppedImage = imagecreatetruecolor($cropWidth, $cropHeight);

        // Perform the crop
        imagecopy($croppedImage, $originalImage, 0, 0, 0, 0, $cropWidth, $cropHeight);

        // Save the cropped image in the same directory as the original
        $newFileName = $fileName . '_cropped.' . $fileExtension;
        $newFilePath = $fileInfo['dirname'] . '/' . $newFileName;
        $transformedPath = public_path('storage/' . $newFilePath);

        if ($fileExtension === 'jpg' || $fileExtension === 'jpeg') {
            imagejpeg($croppedImage, $transformedPath);
        } elseif ($fileExtension === 'png') {
            imagepng($croppedImage, $transformedPath);
        }

        // Clean up
        imagedestroy($originalImage);
        imagedestroy($croppedImage);

        // Save the transformed image as a new record in the database
        Image::create([
            'name' => $newFileName,
            'filepath' => $newFilePath,
            'category_id' => $image->category_id, // Retain the original category
        ]);

        return redirect()->route('images.index')->with('success', 'Image cropped successfully!');
    }















    public function testFlaskAPI()
    {
        $response = Http::get(env('FLASK_API_URL') . '/');
        
        // Debug the response content
        if ($response->failed()) {
            return response()->json(['error' => 'Request to Flask API failed', 'status' => $response->status()]);
        }

        return response()->json([
            'status' => $response->status(),
            'body' => $response->body(),
            'json' => $response->json()
        ]);
    }

    // public function showDescriptors($id)
    // {
    //     $image = Image::findOrFail($id);
    //     $response = Http::get(env('FLASK_API_URL') . '/descriptors/' . $id);

    //     // Assuming the Flask API returns a JSON response
    //     $descriptors = $response->json();

    //     return view('images.descriptors', compact('image', 'descriptors'));
    // }



    // public function showDescriptors($id)
    // {
    //     $image = Image::findOrFail($id);
    
    //     // Construct the path to the original image in the public storage directory
    //     $imagePath = public_path('storage' . DIRECTORY_SEPARATOR . $image->filepath);
    //     $imagePath = str_replace('/', '\\', public_path('storage/' . $image->filepath));

    //     if (!file_exists($imagePath)) {
    //         return response()->json(['error' => 'Image not found'], 404);
    //     }
    
    //     // Fetch descriptors from the Flask API
    //     $response = Http::get("http://127.0.0.1:5000/descriptors/{$id}");
    
    //     if ($response->failed()) {
    //         return response()->json(['error' => 'Failed to retrieve descriptors from Flask API'], 500);
    //     }
    
    //     $descriptors = $response->json();
    
    //     return view('images.descriptors', compact('image', 'descriptors'));
    // }
    

    public function showDescriptors($id)
    {
        $image = Image::findOrFail($id);
    
        // Construct the full path to the image
        $imagePath = str_replace('/', DIRECTORY_SEPARATOR, public_path('storage/' . $image->filepath));
    
        if (!file_exists($imagePath)) {
            return response()->json(['error' => 'Image not found'], 404);
        }
    
        // Send the image path to the Flask API
        $response = Http::post("http://127.0.0.1:5000/descriptors", [
            'image_path' => $imagePath
        ]);
    
        if ($response->failed()) {
            return response()->json(['error' => 'Failed to retrieve descriptors from Flask API'], 500);
        }
    
        $descriptors = $response->json();
    
        return view('images.descriptors', compact('image', 'descriptors'));
    }
    
    
    
    
    
    
    
















    public function search($id)
    {
        $image = Image::findOrFail($id);
        return view('images.search', compact('image'));
    }



    

    public function simpleSearch($id)
    {
        $image = Image::findOrFail($id);
    
        // Logic to find similar images for the simple search
        $similarImages = Image::where('id', '!=', $id) // Example logic to exclude the current image
                              ->take(5) // Limit to the first 5 results (adjust as needed)
                              ->get();
    
        return view('images.search', compact('image', 'similarImages'));
    }
    
    public function relevanceSearch($id)
    {
        $image = Image::findOrFail($id);
    
        // Logic to find similar images for the relevance feedback search
        $similarImages = Image::where('id', '!=', $id) // Example logic to exclude the current image
                              ->take(5) // Limit to the first 5 results (adjust as needed)
                              ->get();
    
        return view('images.search', compact('image', 'similarImages'));
    }
    

}
