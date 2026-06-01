<?php
declare(strict_types=1);

namespace App\Models\Audit;

use App\Core\Database;
use PDO;

/**
 * Model for leave audit logging.
 */
class LeaveAudit
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Record an action in the audit log.
     * 
     * @param int $leaveId
     * @param string $action
     * @param int $userId
     * @param string|null $ipAddress
     * @return bool
     */
    public function record(int $leaveId, string $action, int $userId, ?string $ipAddress = null): bool
    {
        $sql = "INSERT INTO tbl_leave_audit (leave_id, action, performed_by_user_id, ip_address, performed_at)
                VALUES (:leave_id, :action, :user_id, :ip_address, NOW())";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':leave_id' => $leaveId,
            ':action' => $action,
            ':user_id' => $userId,
            ':ip_address' => $ipAddress
        ]);
    }
}
