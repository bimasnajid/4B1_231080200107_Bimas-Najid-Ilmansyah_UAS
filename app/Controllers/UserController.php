<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\UserModel;
use App\Libraries\JWTLibrary;

class UserController extends ResourceController
{
    protected $modelName = 'App\Models\UserModel';
    protected $format    = 'json';

    protected $jwt;

    public function __construct()
    {
        $this->jwt = new JWTLibrary();
    }

    // ✅ Bug #12: Tambahkan pagination
    public function index()
    {
        $page    = $this->request->getVar('page') ?? 1;
        $perPage = 10;

        $users = $this->model->paginate($perPage);

        // Filter data (hapus password)
        $sanitized = array_map(function ($user) {
            unset($user['password']);
            return $user;
        }, $users);

        return $this->respond([
            'status' => 'success',
            'data'   => $sanitized,
            'pager'  => $this->model->pager->getDetails()
        ]);
    }

    // ✅ Bug #13 & #14: Validasi ID dan hilangkan data sensitif
    public function show($id = null)
    {
        if (!is_numeric($id)) {
            return $this->failValidationErrors('ID tidak valid');
        }

        $user = $this->model->find($id);

        if (!$user) {
            return $this->failNotFound('User tidak ditemukan');
        }

        unset($user['password']);
        return $this->respond($user);
    }

    // ✅ Bug #15 & #16: Validasi otorisasi dan input
    public function update($id = null)
    {
        // Cek token dan decode JWT
        $authHeader = $this->request->getHeaderLine('Authorization');
        if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return $this->failUnauthorized('Token tidak ditemukan');
        }

        $token = $matches[1];

        try {
            $decoded = $this->jwt->decode($token);

            if ($decoded->user_id != $id) {
                return $this->failForbidden('Tidak boleh mengedit user lain');
            }

            $data = $this->request->getRawInput();

            // Validasi email jika ada
            if (isset($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                return $this->failValidationErrors('Format email tidak valid');
            }

            // Enkripsi password jika diberikan
            if (isset($data['password'])) {
                $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
            }

            if ($this->model->update($id, $data)) {
                return $this->respond(['message' => 'User berhasil diupdate']);
            }

            return $this->failServerError('Update gagal');
        } catch (\Exception $e) {
            return $this->failUnauthorized('Token tidak valid: ' . $e->getMessage());
        }
    }

    // ✅ Bug #17: Tambahkan validasi otorisasi delete
    public function delete($id = null)
    {
        $authHeader = $this->request->getHeaderLine('Authorization');
        if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return $this->failUnauthorized('Token tidak ditemukan');
        }

        $token = $matches[1];

        try {
            $decoded = $this->jwt->decode($token);

            if ($decoded->user_id != $id) {
                return $this->failForbidden('Tidak boleh menghapus user lain');
            }

            if (!$this->model->find($id)) {
                return $this->failNotFound('User tidak ditemukan');
            }

            if ($this->model->delete($id)) {
                return $this->respond(['message' => 'User berhasil dihapus']);
            }

            return $this->failServerError('Gagal menghapus user');
        } catch (\Exception $e) {
            return $this->failUnauthorized('Token tidak valid: ' . $e->getMessage());
        }
    }
}
