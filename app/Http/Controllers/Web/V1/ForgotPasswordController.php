<?php

namespace App\Http\Controllers\Web\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Carbon\Carbon;
use App\CentralLogics\Helpers;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ForgotPassword;
use App\Models\User;

class ForgotPasswordController extends Controller
{
    //sendResetLink
    public function sendResetLink(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email'
        ], [
            'email.required' => 'The email field is required.',
            'email.email' => 'The email field must have "@" symbol.',
            'email.exists' => 'This email does not exist.',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => Helpers::error_processor($validator)], 403);
        }

        $user = User::where('email', $request->email)->first();
        // $token = bcrypt($user->email . now());
        $token = Crypt::encryptString($user->email . '|' . now());
        
        $data = [
            'user' => $user,
            'token' => $token
        ];

        Notification::route('mail', [$request->email])->notify(new ForgotPassword($data));

        return response()->json([
            'success' => true,
            'token' => $token,
            'url' => url('reset-password/' . $token),
            'message' => 'Password reset link has been sent to your email',
            
        ]);

    }

    // Send email logic
        // Mail::send('emails.password-reset', ['token' => $token], function ($message) use ($user) {
        //     $message->to($user->email);
        //     $message->subject('Password Reset Request');
        // });

    //resetPassword
    // public function resetPassword(Request $request)
    // {
    //     // $request->validate([
    //     //     'token' => 'required',
    //     //     'email' => 'required|email|exists:users,email',
    //     //     'password' => 'required|confirmed|min:8',
    //     // ]);
    //     $validator = Validator::make($request->all(), [
    //         'token' => 'required',
    //         'email' => 'required|email|exists:users,email',
    //         'password' => 'required|confirmed|min:6', //password_confirmation
    //     ], [
    //         'email.required' => 'The email field is required.',
    //         'email.email' => 'The email field must have "@" symbol.',
    //         'email.exists' => 'This email does not exist.',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json(['success' => false, 'errors' => Helpers::error_processor($validator)], 403);
    //     }

    //     $user = User::where('email', $request->email)->first();

    //     // Validate the token (simply comparing hashes is not secure for production, adjust as needed)
    //     $tokenValid = Hash::check($user->email . now()->subMinutes(30), $request->token);

    //     // Validate the token (assuming simple token generation)
    //     if ($tokenValid) {
    //         $user->password = Hash::make($request->password);
    //         $user->save();

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Password has been reset successfully.', 
    //         ]);
    //     }

    //     return response()->json([
    //         'success' => false,
    //         'message' => 'Invalid token.', 
    //     ]);
    // }

    /**
     * Store a newly created resource in storage.
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'email' => 'required|email|exists:users,email',
            'password' => 'required|confirmed|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 403);
        }

        try {
            // Decrypt the token to get the email and timestamp
            $decrypted = Crypt::decryptString($request->token);
            list($email, $timestamp) = explode('|', $decrypted);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Invalid token.'], 403);
        }

        // Check if the email matches and the token is not expired (e.g., 60 minutes validity)
        if ($email !== $request->email || Carbon::parse($timestamp)->addMinutes(60)->isPast()) {
            return response()->json(['success' => false, 'message' => 'Invalid or expired token.'], 403);
        }

        // Find the user and reset the password
        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Password has been reset successfully.'
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
