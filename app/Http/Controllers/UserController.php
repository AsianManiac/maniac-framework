<?php

namespace App\Http\Controllers;

use Core\Mvc\Controller;

class UserController extends Controller
{
    public function index()
    {
        return $this->view('UserController.index');
    }
}