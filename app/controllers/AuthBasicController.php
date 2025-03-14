<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\User;

class AuthBasicController extends AppController
{
    public function login(): void
    {
        // Check if we have auth credentials
        if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW'])) {
            // No credentials provided, prompt for login
            header('WWW-Authenticate: Basic realm="Twict Authentication"');
            header('HTTP/1.0 401 Unauthorized');
            exit('Authentication required');
        }

        // Verify credentials against database
        $email = $_SERVER['PHP_AUTH_USER'];
        $password = $_SERVER['PHP_AUTH_PW'];

        // Query the user by email
        $user = User::findByEmail($email);

        if ($user && $user['password'] === $password) {
            // Valid login
            $_SESSION['user'] = $user;
            $this->flash->success('Successfully logged in');
            $this->redirect('/');
        } else {
            // Invalid login
            $this->flash->danger('Invalid credentials');
            header('WWW-Authenticate: Basic realm="Twict Authentication"');
            header('HTTP/1.0 401 Unauthorized');
            exit('Authentication failed');
        }
    }

    public function logout(): void
    {
        // Remove user from session
        if (isset($_SESSION['user'])) {
            unset($_SESSION['user']);
        }

        $this->flash->info('You have been logged out');
        $this->redirect('/');
    }
}
