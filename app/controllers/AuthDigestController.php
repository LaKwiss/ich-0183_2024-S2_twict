<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\User;

class AuthDigestController extends AppController
{
    private string $realm = 'Twict Authentication';
    private string $opaque = 'twict_digest_auth_opaque';

    public function login(): void
    {
        // Vérifier si les informations d'authentification sont présentes
        if (!isset($_SERVER['PHP_AUTH_DIGEST'])) {
            // Aucun en-tête d'authentification - envoyer le challenge
            $this->sendAuthenticationChallenge();
            exit('Authentication required');
        }

        // Analyser les infos d'authentification Digest
        $digestData = $this->parseDigestHeader($_SERVER['PHP_AUTH_DIGEST']);

        // Si les données d'authentification sont incorrectes ou incomplètes
        if (!$digestData) {
            $this->sendAuthenticationChallenge();
            exit('Invalid digest authentication data');
        }

        // Récupérer l'utilisateur par son nom d'utilisateur (email)
        $user = User::findByEmail($digestData['username']);

        if (!$user) {
            $this->sendAuthenticationChallenge();
            exit('User not found');
        }

        // Calculer la réponse valide que nous attendons
        $validResponse = $this->generateValidResponse(
            $digestData,
            $user['password']
        );

        // Vérifier si la réponse client correspond à la réponse attendue
        if ($digestData['response'] === $validResponse) {
            // Authentification réussie
            $_SESSION['user'] = $user;
            $this->flash->success('Successfully logged in');
            $this->redirect('/');
        } else {
            // Authentification échouée
            $this->sendAuthenticationChallenge();
            $this->flash->danger('Authentication failed');
            exit('Authentication failed');
        }
    }

    public function logout(): void
    {
        // Supprimer l'utilisateur de la session
        if (isset($_SESSION['user'])) {
            unset($_SESSION['user']);
        }

        $this->flash->info('You have been logged out');
        $this->redirect('/');
    }

    private function sendAuthenticationChallenge(): void
    {
        // Générer un nonce unique
        $nonce = md5(uniqid((string)mt_rand(), true));

        // Stocker le nonce en session pour vérification ultérieure
        $_SESSION['digest_nonce'] = $nonce;

        // Envoyer l'en-tête de challenge d'authentification Digest
        header('HTTP/1.1 401 Unauthorized');
        header('WWW-Authenticate: Digest ' .
            'realm="' . $this->realm . '", ' .
            'qop="auth", ' .
            'nonce="' . $nonce . '", ' .
            'opaque="' . $this->opaque . '"');
    }

    private function parseDigestHeader(string $digestHeader): ?array
    {
        // Extraire les paramètres de l'en-tête Digest
        preg_match_all('/(\w+)=(?:"([^"]+)"|([^,]+))/', $digestHeader, $matches, PREG_SET_ORDER);

        $data = [];
        foreach ($matches as $match) {
            $data[$match[1]] = isset($match[3]) ? $match[3] : $match[2];
        }

        // Vérifier que tous les paramètres requis sont présents
        $requiredParams = ['username', 'realm', 'nonce', 'uri', 'response'];
        foreach ($requiredParams as $param) {
            if (!isset($data[$param])) {
                return null;
            }
        }

        // Vérifier que le nonce correspond à celui que nous avons envoyé
        if (!isset($_SESSION['digest_nonce']) || $data['nonce'] !== $_SESSION['digest_nonce']) {
            return null;
        }

        return $data;
    }

    private function generateValidResponse(array $digestData, string $password): string
    {
        // Calcule HA1 = MD5(username:realm:password)
        $ha1 = md5($digestData['username'] . ':' . $this->realm . ':' . $password);

        // Calcule HA2 = MD5(method:uri)
        $ha2 = md5($_SERVER['REQUEST_METHOD'] . ':' . $digestData['uri']);

        // Si qop est spécifié
        if (isset($digestData['qop'])) {
            // Calcule response = MD5(HA1:nonce:nc:cnonce:qop:HA2)
            return md5($ha1 . ':' . $digestData['nonce'] . ':' .
                $digestData['nc'] . ':' . $digestData['cnonce'] . ':' .
                $digestData['qop'] . ':' . $ha2);
        } else {
            // Calcule response = MD5(HA1:nonce:HA2)
            return md5($ha1 . ':' . $digestData['nonce'] . ':' . $ha2);
        }
    }
}
