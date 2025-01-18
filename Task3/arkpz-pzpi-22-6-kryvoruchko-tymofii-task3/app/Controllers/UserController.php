<?php
namespace App\Controllers;

use Firebase\JWT\JWT;
use Exception;
use App\Models\User;
use App\Models\Lock;

require_once __DIR__ . '/helpers.php';

class UserController {
    
    public function index() {
        try {
            $users = User::all();
            error_log("Fetched users: " . json_encode($users));
            
            if ($users->isEmpty()) {
                error_log("No users found");
            }
    
            jsonResponse($users);
        } catch (\Exception $e) {
            error_log("Error in UserController index: " . $e->getMessage());
            jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    public function store() {
        try {
            $data = getRequestData();

            if (User::where('Email', $data['Email'])->exists()) {
                jsonResponse(['error' => 'Email already exists'], 400);
            }

            if (!isset($data['Username']) || !isset($data['Email']) || !isset($data['PasswordHash']) || !isset($data['Role'])) {
                jsonResponse(['message' => 'All fields are required: Username, Email, PasswordHash, Role'], 400);
            }
    
            $user = new User();
            $user->Username = $data['Username'];
            $user->Email = $data['Email'];
            $user->PasswordHash = password_hash($data['PasswordHash'], PASSWORD_BCRYPT);
            $user->Role = $data['Role'];
            $user->save(); 
    
            jsonResponse(['message' => 'User created successfully', 'user' => $user], 201);
        } catch (\Exception $e) {
            jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    public function show($id) {
        try {
            $user = User::find($id);
            if ($user) {
                jsonResponse($user);
            } else {
                jsonResponse(['message' => 'User not found'], 404);
            }
        } catch (\Exception $e) {
            jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    public function update($id) {
        try {
            $data = getRequestData();
            $user = User::find($id);
    
            if (!$user) {
                jsonResponse(['message' => 'User not found'], 404);
            }
    
            if (isset($data['Email']) && User::where('Email', $data['Email'])->where('UserID', '!=', $id)->exists()) {
                jsonResponse(['error' => 'Email already exists'], 400);
            }
    
            $user->update([
                'Username' => $data['Username'] ?? $user->Username,
                'Email' => $data['Email'] ?? $user->Email,
                'PasswordHash' => isset($data['Password']) ? password_hash($data['Password'], PASSWORD_BCRYPT) : $user->PasswordHash,
                'Role' => $data['Role'] ?? $user->Role,
            ]);
    
            jsonResponse($user);
        } catch (\Exception $e) {
            jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id) {
        try {
            $user = User::find($id);
            if ($user) {
                $user->delete();
                jsonResponse(['message' => 'User deleted successfully']);
            } else {
                jsonResponse(['message' => 'User not found'], 404);
            }
        } catch (\Exception $e) {
            jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    public function getByRole($role) {
        try {
            error_log("Fetching users with role: {$role}");
            $users = User::where('Role', $role)->get();
            if ($users->isEmpty()) {
                jsonResponse(['message' => 'No users found with the given role'], 404);
            } else {
                jsonResponse($users);
            }
        } catch (\Exception $e) {
            jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    public function getLocksByUser($userId) {
        try {
            $locks = Lock::where('OwnerID', $userId)->get();
    
            if ($locks->isEmpty()) {
                jsonResponse(['message' => 'No locks found for this user'], 404);
            }
    
            jsonResponse($locks);
        } catch (\Exception $e) {
            jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    private $secretKey = "your_secret_key"; 

    public function register() {
        try {
            $data = getRequestData();

            if (empty($data['Username']) || empty($data['Email']) || empty($data['Password']) || empty($data['Role'])) {
                jsonResponse(['message' => 'All fields are required: Username, Email, Password, Role'], 400);
                return;
            }

            if (User::where('Email', $data['Email'])->exists()) {
                jsonResponse(['error' => 'Email already exists'], 400);
                return;
            }

            $user = new User();
            $user->Username = $data['Username'];
            $user->Email = $data['Email'];
            $user->PasswordHash = password_hash($data['Password'], PASSWORD_BCRYPT);
            $user->Role = $data['Role'];
            $user->save();

            jsonResponse(['message' => 'User registered successfully', 'user' => $user], 201);
        } catch (Exception $e) {
            jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    
    public function login() {
        try {
            $data = getRequestData();
    
            $user = User::where('Email', $data['Email'])->first();
            if (!$user) {
                jsonResponse(['error' => 'User not found'], 404);
                return;
            }
    
            if (!password_verify($data['Password'], $user->PasswordHash)) {
                jsonResponse(['error' => 'Invalid password'], 400);
                return;
            }
    
            $issuedAt = time();
            $expirationTime = $issuedAt + 3600;
            $payload = [
                'UserID' => $user->UserID,
                'Username' => $user->Username,
                'Role' => $user->Role,
                'iat' => $issuedAt,
                'exp' => $expirationTime
            ];
    
            $jwt = JWT::encode($payload, $this->secretKey, 'HS256');
    
            jsonResponse(['message' => 'Login successful', 'token' => $jwt]);
        } catch (Exception $e) {
            jsonResponse(['error' => $e->getMessage()], 500);
        }
    }
    
    public function updateProfile() {
        try {
            $data = getRequestData();
    
            if (!isset($_SESSION['user'])) {
                jsonResponse(['error' => 'User not authenticated'], 401);
                return;
            }
    
            $user = $_SESSION['user'];
    
            $userModel = User::find($user['UserID']); 
            if (!$userModel) {
                jsonResponse(['error' => 'User not found'], 404);
                return;
            }
    
            if (isset($data['Username'])) {
                $userModel->Username = $data['Username'];
            }
            if (isset($data['Email'])) {
                if (User::where('Email', $data['Email'])->where('UserID', '!=', $user['UserID'])->exists()) {
                    jsonResponse(['error' => 'Email already in use'], 400);
                    return;
                }
                $userModel->Email = $data['Email'];
            }
            if (isset($data['Password'])) {
                $userModel->PasswordHash = password_hash($data['Password'], PASSWORD_BCRYPT);
            }
    
            $userModel->save();
    
            jsonResponse([
                'message' => 'Profile updated successfully',
                'user' => $userModel
            ], 200);
        } catch (Exception $e) {
            jsonResponse(['error' => $e->getMessage()], 500);
        }
    }
    
    public function getSummaryReport() {
        try {

            $totalUsers = User::count();
    
            $totalAdmins = User::where('Role', 'admin')->count();
    
            $totalLocks = Lock::count();

            $totalRegularUsers = $totalUsers - $totalAdmins;
    
            $report = [
                'totalUsers' => $totalUsers,
                'totalAdmins' => $totalAdmins,
                'totalRegularUsers' => $totalRegularUsers,
                'totalLocks' => $totalLocks,
            ];
    
            jsonResponse(['message' => 'Summary report generated successfully', 'report' => $report], 200);
        } catch (Exception $e) {
            jsonResponse(['error' => $e->getMessage()], 500);
        }
    }
    
    
}

?>
