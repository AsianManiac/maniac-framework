<?php

namespace App\Controllers;

use Exception;
use App\Models\User;
use Core\Logging\Log;
use Core\Http\Request;
use Core\Mvc\Controller;
use Core\Http\Response\Response;

class UserController extends Controller
{
    public function show(int $id)
    {
        Log::info("Attempting to fetch user with ID: {$id}");

        try {
            $user = User::findOrFail($id);
            Log::debug("User found", ['user_id' => $user->id, 'user_email' => $user->email]);
            return view('users.show', ['user' => $user]);
        } catch (Exception $e) {
            // Log the exception with stack trace via the exception handler context
            Log::error("User not found exception", ['exception' => $e, 'requested_id' => $id]);

            // Optionally log a simpler message too
            // Log::warning("Could not find user with ID: {$id}");

            http_response_code(404);
            return view('errors.404');
        }
    }


    public function index()
    {
        $users = User::all();
        return $this->view('users.index', ['users' => $users]);
    }

    public function apiIndex()
    {
        $users = User::all();
        return $this->json($users);
    }

    public function create()
    {
        return response()->view('users.create');
    }

    public function store(Request $request)
    {
        // ... validation ...
        Log::channel('daily')->info('Creating new user', $request->all());
        // ... user creation ...
        $user = new User();
        $user->fill($request->only(['name', 'email', 'password'])); // Use fillable
        if ($user->save()) {
            // Use the redirect helper
            return redirect('/users/' . $user->id); // Redirect to the new user's page
            // Or redirect back with success message (requires session flash)
            // return redirect()->back()->with('success', 'User created!');
        } else {
            // Use the redirect helper to go back (requires session flash for errors/old input)
            // return redirect()->back()->withInput()->withErrors(['save' => 'Could not save user.']);
            return response("Error saving user", 500); // Simple error for now
        }
    }

    public function downloadAvatar(int $id)
    {
        $user = User::find($id);
        if (!$user || !$user->avatar_path || !file_exists(BASE_PATH . '/storage/avatars/' . $user->avatar_path)) {
            return response('Avatar not found.', 404);
        }
        $filePath = BASE_PATH . '/storage/avatars/' . $user->avatar_path;
        // Use the facade (example)
        return Response::download($filePath, $user->username . '_avatar.png');
    }
}
