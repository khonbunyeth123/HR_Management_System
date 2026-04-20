<?php
namespace App\Services;

use App\Models\User;
use App\Helpers\FormPermissionHelper;

class UserService
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    // Check permission before action
    private function checkPermission(string $module, string $action)
    {
        if (!FormPermissionHelper::can($module, $action)) {
            throw new \Exception("You do not have permission to $action $module");
        }
    }

    public function getAllUsers(int $page = 1, int $per_page = 18, array $filters = [], array $sorts = [])
    {
        $this->checkPermission('user', 'view');

        $offset = ($page - 1) * $per_page;
        $result = $this->userModel->getAll($offset, $per_page, $filters, $sorts);

        return [
            'data' => ['users' => $result['data']],
            'pagination' => [
                'page' => $page,
                'per_page' => $per_page,
                'total' => $result['total'],
                'total_pages' => ceil($result['total'] / $per_page)
                
            ]
        ];
    }

    public function createUser(array $data)
    {
        $this->checkPermission('user', 'create');
        return $this->userModel->create($data);
    }

    public function updateUser(int $id, array $data)
    {
        $this->checkPermission('user', 'update');
        return $this->userModel->update($id, $data);
    }

    public function deleteUser(int $id, $deleted_by = null)
    {
        $this->checkPermission('user', 'delete');
        return $this->userModel->delete($id, $deleted_by);
    }

    public function getUserById(int $id)
    {
        $this->checkPermission('user', 'view');
        return $this->userModel->getById($id);
    }
}
