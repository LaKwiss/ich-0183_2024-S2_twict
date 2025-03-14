<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\User;

class AuthFormController extends AppController
{
    public function login(): void
    {
        // Si l'utilisateur est déjà connecté, redirigez-le vers la page d'accueil
        if (isset($_SESSION['user'])) {
            $this->redirect('/');
            return;
        }
        
        // Si le formulaire a été soumis (POST)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Récupérer les identifiants soumis
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            
            // Vérifier les identifiants
            $user = User::findByEmail($email);
            
            if ($user && $user['password'] === $password) {
                // Authentification réussie
                $_SESSION['user'] = $user;
                $_SESSION['auth_method'] = 'Form';
                
                $this->flash->success('Successfully logged in');
                $this->redirect('/');
            } else {
                // Authentification échouée
                $this->flash->danger('Invalid email or password');
                
                // Conserver l'email pour le réafficher dans le formulaire
                $this->view['email'] = $email;
            }
        }
        
        // Afficher le formulaire de connexion
    }
    
    public function logout(): void
    {
        // Supprimer l'utilisateur de la session
        if (isset($_SESSION['user'])) {
            unset($_SESSION['user']);
        }
        
        // Supprimer la méthode d'authentification
        if (isset($_SESSION['auth_method'])) {
            unset($_SESSION['auth_method']);
        }
        
        $this->flash->info('You have been logged out');
        $this->redirect('/');
    }
}