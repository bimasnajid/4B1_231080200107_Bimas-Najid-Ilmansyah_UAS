<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\UserModel;
use App\Libraries\JWTLibrary;
use CodeIgniter\API\ResponseTrait;

class AuthController extends ResourceController
{
    use ResponseTrait;

    protected $modelName = 'App\Models\UserModel';
    protected $format    = 'json';
    protected $jwt;

    public function __construct()
    {
        $this->jwt = new JWTLibrary();
    }

    public function register()
    {
        $data = $this->request->getPost();

        // ✅ Bug #6: Validasi input
        if (!isset($data['name'], $data['email'], $data['password'])) {
            return $this->failValidationErrors('Semua field wajib diisi: name, email, password');
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return $this->failValidationErrors('Format email tidak valid');
        }

        $userModel = new UserModel();

        // Cek apakah email sudah digunakan
        if ($userModel->where('email', $data['email'])->first()) {
            return $this->failValidationErrors('Email sudah terdaftar');
        }

        // ✅ Bug #7: Hash password sebelum simpan
        $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);

        $userData = [
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => $hashedPassword
        ];

        $userId = $userModel->insert($userData);

        if ($userId) {
            return $this->respondCreated([
                'status'  => 'success',
                'message' => 'User registered successfully',
                'data'    => [
                    'id'    => $userId,
                    'name'  => $data['name'],
                    'email' => $data['email']
                    // ✅ Bug #8: password tidak dikembalikan
                ]
            ]);
        }

        return $this->failServerError('Registration failed');
    }

    public function login()
    {
        $email    = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        // ✅ Bug #9: Validasi input login
        if (!$email || !$password) {
            return $this->failValidationErrors('Email dan password wajib diisi');
        }

        $userModel = new UserModel();
        $user      = $userModel->where('email', $email)->first();

        // ✅ Bug #10: Gunakan password_verify
        if ($user && password_verify($password, $user['password'])) {
            $payload = [
                'user_id' => $user['id'],
                'email'   => $user['email'],
                'exp'     => time() + 3600 // 1 jam
            ];

            $token = $this->jwt->encode($payload);

            return $this->respond([
                'status' => 'success',
                'token'  => $token,
                'user'   => [
                    'id'    => $user['id'],
                    'name'  => $user['name'],
                    'email' => $user['email']
                ]
            ]);
        }

        return $this->failUnauthorized('Email atau password salah');
    }

    public function refresh()
    {
        // ✅ Bug #11: Implementasi refresh token
        $authHeader = $this->request->getHeaderLine('Authorization');
        if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return $this->failUnauthorized('Token tidak ditemukan');
        }

        $oldToken = $matches[1];

        try {
            $decoded = $this->jwt->decode($oldToken);
            $newPayload = [
                'user_id' => $decoded->user_id,
                'email'   => $decoded->email,
                'exp'     => time() + 3600
            ];

            $newToken = $this->jwt->encode($newPayload);

            return $this->respond([
                'status' => 'success',
                'token'  => $newToken
            ]);

        } catch (\Exception $e) {
            return $this->failUnauthorized('Token tidak valid: ' . $e->getMessage());
        }
    }
}
