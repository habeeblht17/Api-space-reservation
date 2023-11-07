<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisteredUserRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class RegisteredUserController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(RegisteredUserRequest $request): JsonResponse
    {

        $request->validated($request->all());

        $result = $this->handleImageUpload($request);

        $user = User::create([
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 4,
            'qrcode' => Str::random(20),
            'image' => $result['imagePath'],
        ]);

        // Generate and save the QR code image
        $pathToQrcode = $this->generateQrCode($user);

        event(new Registered($user));

        //Auth::login($user);

        // Get the login URL
        $loginUrl = URL::route('login');
        $temporaryImageUrl = $result['temporaryImageUrl'];

        return response()->json([
            'message' => 'User registered successfully.',
            'user' => $user,
            'redirectUrl' => $loginUrl,
            'temporaryImageUrl' => $temporaryImageUrl ?? null,
            'qrcodeUrl' => $pathToQrcode,
        ], 201);
    }

    /**
     * Image Upload logic
     */
    private function handleImageUpload($request)
    {
        $image = $request->file('image');
        $imageName = $image->hashName();
        $uniqueImageName = time() . '_' . $imageName;
        $imagePath = $image->storeAs('users/images', $uniqueImageName, 'public');

        // Generate temporary URL for the uploaded image
        $temporaryImageUrl = Storage::url($imagePath);

        return [
            'imagePath' => $imagePath,
            'temporaryImageUrl' => $temporaryImageUrl,
        ];
    }



    /**
     * Qrcode Image Delete logic
     */
    private function generateQrCode($user)
    {
        $directory = 'users/qrcodes/';

        if (!Storage::disk('public')->exists($directory)) {
            Storage::disk('public')->makeDirectory($directory);
        }

        $qrCodeImagePath = $directory . $user->id . '.svg';

        QrCode::size(300)->generate($user->qrcode, storage_path('app/public/' . $qrCodeImagePath));

        return $qrCodeImagePath;
    }

}
