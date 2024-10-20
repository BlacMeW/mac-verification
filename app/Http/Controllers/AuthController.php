<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use PragmaRX\Google2FA\Google2FA;

class AuthController extends Controller
{
    protected $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    // User Registration and generate TOTP secret
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        // Create user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'totp_secret' => $this->google2fa->generateSecretKey(),
        ]);

        return response()->json([
            'message' => 'User registered successfully!',
            'totp_secret' => $user->totp_secret, // TOTP secret for the user
        ]);
    }

    // User Login and return TOTP verification request
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();

            return response()->json([
                'message' => 'Login successful. Please verify with TOTP.',
                'totp_secret' => $user->totp_secret, // Return secret for TOTP app to generate the code
            ]);
        } else {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }
    }

    // Verify TOTP Code
    public function verifyTOTP(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'totp_code' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $totpCode = $request->totp_code;
        $secret = $user->totp_secret;

        // Log the secret and code for debugging
        \Log::info('TOTP Secret: ' . $secret);
        \Log::info('User Input TOTP Code: ' . $totpCode);

        // Check with +/- 1 interval (window parameter set to 2)
        if ($this->google2fa->verifyKey($secret, $totpCode, 2)) {
            return response()->json(['message' => 'TOTP verification successful!']);
        } else {
            return response()->json(['message' => 'Invalid TOTP code'], 403);
        }
    }

}
