<?php

namespace App\Http\Controllers\API;


use App\Http\Controllers\Controller;
use App\Http\Requests\FileUploadRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\Attachment;
use App\Models\User;
use App\Services\FileUploadService;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    /**
     * Register a new user
     *
     * @param RegisterRequest $request
     * @return JsonResponse
     */
    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('api')->plainTextToken;
        return response()->json([
            'message' => 'User successfully created',
            'data' => ['token' => $token],
        ]);
    }

    /**
     * Display a listing of resource
     *
     * @param LoginRequest $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function login(LoginRequest $request)
    {
        $request->authenticate();
        $user = auth()->user();
        $user->tokens()->delete();
        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'message' => 'You successfully logged in',
            'data' => ['token' => $token],
        ]);
    }

    /**
     * Display a listing of resource
     *
     * @return JsonResponse
     */
    public function logout()
    {
        $user = UserService::getAuthenticatedUser();
        $user->tokens()->delete();
        return response()->json(['message' => 'You successfully logged in']);
    }

    /**
     * Display a user info
     *
     * @return JsonResponse
     */
    public function index()
    {
        $user = auth('sanctum')->user();
        return response()->json(['data' => $user->toArray()]);
    }

    /**
     * Display a list of user files or return single file
     */
    public function fileInfo($fileID = null)
    {
        $user = auth('sanctum')->user();
        if($fileID){
            $fileQuery = $user->files()->where('id', $fileID);
            if($fileQuery->exists()) {
                $file = $fileQuery->first();
                return Storage::disk('storage')->download($file->path);
            }

            return response()->json(['message' => 'File not found'], 422);
        }

        return response()->json(['data' => $user->files->toArray()]);
    }

    /**
     * Upload user file
     *
     * @return JsonResponse
     */
    public function uploadFile(FileUploadRequest $request, FileUploadService $fileService)
    {
        $user = auth('sanctum')->user();
        $file = $request->file('file');

        $filePath = $fileService->uploadFile($file, $user->id);

        Attachment::create([
            'user_id' => $user->id,
            'path' => $filePath,
        ]);

        return response()->json(['message' => 'File uploaded successfully']);
    }

    /**
     * Delete user file
     *
     * @return JsonResponse
     */
    public function deleteFile($fileID)
    {
        $user = auth('sanctum')->user();
        $fileQuery = $user->files()->where('id', $fileID);
        if($fileQuery->exists()){
            $file = $fileQuery->first();
            $isDeleted = Storage::disk('storage')->delete($file->path);
            if($isDeleted){
                $file->delete();
                return response()->json(['message' => 'File deleted successfully']);
            }

            return response()->json(['message' => 'Some error occurred'], 400);
        }

        return response()->json(['message' => 'File not found'], 422);
    }
}
