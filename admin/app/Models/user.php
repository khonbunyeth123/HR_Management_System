<?php
// models/User.php
class User {
    private $conn;
    private $table_name = "users";

    public $id;
    public $employee_id;
    public $username;
    public $email;
    public $password;
    public $role;
    public $status;
    public $login_attempts;
    public $lock_until;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create user
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                SET employee_id=:employee_id, username=:username, email=:email, 
                    password=:password, role=:role, status=:status, 
                    login_attempts=:login_attempts, lock_until=:lock_until, 
                    created_at=NOW()";

        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $this->employee_id = htmlspecialchars(strip_tags($this->employee_id));
        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->password = password_hash($this->password, PASSWORD_DEFAULT);
        $this->role = htmlspecialchars(strip_tags($this->role));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->login_attempts = htmlspecialchars(strip_tags($this->login_attempts));
        $this->lock_until = $this->lock_until;

        // Bind values
        $stmt->bindParam(":employee_id", $this->employee_id);
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password", $this->password);
        $stmt->bindParam(":role", $this->role);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":login_attempts", $this->login_attempts);
        $stmt->bindParam(":lock_until", $this->lock_until);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Read all users
    public function read() {
        $query = "SELECT id, employee_id, username, email, role, status, 
                         login_attempts, lock_until, created_at, updated_at 
                  FROM " . $this->table_name . " 
                  ORDER BY created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Read single user
    public function readOne() {
        $query = "SELECT id, employee_id, username, email, role, status, 
                         login_attempts, lock_until, created_at, updated_at 
                  FROM " . $this->table_name . " 
                  WHERE id = :id LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if($row) {
            $this->employee_id = $row['employee_id'];
            $this->username = $row['username'];
            $this->email = $row['email'];
            $this->role = $row['role'];
            $this->status = $row['status'];
            $this->login_attempts = $row['login_attempts'];
            $this->lock_until = $row['lock_until'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            return true;
        }
        return false;
    }

    // Update user
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                SET employee_id=:employee_id, username=:username, email=:email, 
                    role=:role, status=:status, login_attempts=:login_attempts, 
                    lock_until=:lock_until, updated_at=NOW() 
                WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $this->employee_id = htmlspecialchars(strip_tags($this->employee_id));
        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->role = htmlspecialchars(strip_tags($this->role));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->login_attempts = htmlspecialchars(strip_tags($this->login_attempts));
        $this->id = htmlspecialchars(strip_tags($this->id));

        // Bind values
        $stmt->bindParam(":employee_id", $this->employee_id);
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":role", $this->role);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":login_attempts", $this->login_attempts);
        $stmt->bindParam(":lock_until", $this->lock_until);
        $stmt->bindParam(":id", $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Delete user
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(":id", $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Check if username exists
    public function usernameExists() {
        $query = "SELECT id, username FROM " . $this->table_name . " WHERE username = :username";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":username", $this->username);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            return true;
        }
        return false;
    }

    // Check if email exists
    public function emailExists() {
        $query = "SELECT id, email FROM " . $this->table_name . " WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $this->email);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            return true;
        }
        return false;
    }

    // Authenticate user
    public function authenticate($username, $password) {
        $query = "SELECT id, employee_id, username, email, password, role, status, 
                         login_attempts, lock_until 
                  FROM " . $this->table_name . " 
                  WHERE username = :username LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Check if account is locked
            if($row['lock_until'] && strtotime($row['lock_until']) > time()) {
                return ['success' => false, 'message' => 'Account is locked. Please try again later.'];
            }

            // Check if account is active
            if($row['status'] != 1) {
                return ['success' => false, 'message' => 'Account is inactive. Please contact administrator.'];
            }

            // Verify password
            if(password_verify($password, $row['password'])) {
                // Reset login attempts on successful login
                $this->resetLoginAttempts($row['id']);
                
                return [
                    'success' => true,
                    'user' => [
                        'id' => $row['id'],
                        'employee_id' => $row['employee_id'],
                        'username' => $row['username'],
                        'email' => $row['email'],
                        'role' => $row['role'],
                        'status' => $row['status']
                    ]
                ];
            } else {
                // Increment login attempts
                $this->incrementLoginAttempts($row['id']);
                return ['success' => false, 'message' => 'Invalid username or password.'];
            }
        }

        return ['success' => false, 'message' => 'Invalid username or password.'];
    }

    // Increment login attempts
    private function incrementLoginAttempts($userId) {
        $query = "UPDATE " . $this->table_name . " 
                SET login_attempts = login_attempts + 1,
                    lock_until = CASE 
                        WHEN login_attempts >= 4 THEN DATE_ADD(NOW(), INTERVAL 30 MINUTE)
                        ELSE lock_until 
                    END
                WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $userId);
        $stmt->execute();
    }

    // Reset login attempts
    private function resetLoginAttempts($userId) {
        $query = "UPDATE " . $this->table_name . " 
                SET login_attempts = 0, lock_until = NULL 
                WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $userId);
        $stmt->execute();
    }

    // Search users
    public function search($searchField, $searchValue) {
        $query = "SELECT id, employee_id, username, email, role, status, 
                         login_attempts, lock_until, created_at, updated_at 
                  FROM " . $this->table_name . " 
                  WHERE " . $searchField . " LIKE :searchValue 
                  ORDER BY created_at DESC";

        $stmt = $this->conn->prepare($query);
        $searchValue = "%{$searchValue}%";
        $stmt->bindParam(":searchValue", $searchValue);
        $stmt->execute();
        return $stmt;
    }
}
?>