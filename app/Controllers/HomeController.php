<?php

namespace App\Controllers;

use Core\Mvc\Controller;
use App\Models\User;

class HomeController extends Controller
{
    public function show(): string
    {

        // $users = User::all();
        $users = [ // Dummy data for now
            ['name' => 'Alice'],
            ['name' => 'Bob']
        ];

        // Use the view helper inherited or directly
        return $this->view('home.index', ['users' => $users]);
        // Or return view('home.index', ['users' => $users]);
    }

    /**
     * Display the home page.
     *
     * @return string The rendered view.
     */
    public function index(): string
    {
        $users = [
            (object) ['name' => 'Alice', 'email' => 'alice@example.com'],
            (object) ['name' => 'Bob'], // No email for Bob
            (object) ['name' => 'Charlie', 'email' => 'charlie@example.com']
        ];

        return view('home', ['users' => $users]);
    }
}
