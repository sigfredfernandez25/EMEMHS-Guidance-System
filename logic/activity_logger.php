<?php
/**
 * Activity Logger
 * Logs all activities for timeline feature
 */

require_once 'db_connection.php';

/**
 * Log an activity
 * 
 * @param string $referenceType 'complaint' or 'lost_item'
 * @param int $referenceId ID of the complaint or lost item
 * @param string $action Action performed (e.g., 'submitted', 'scheduled', 'resolved')
 * @param string $description Detailed description of the action
 * @param int|null $performedBy User ID who performed the action
 * @return bool Success status
 */
function logActivity($referenceType, $referenceId, $action, $description, $performedBy = null) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO activity_log (reference_type, reference_id, action, description, performed_by)
            VALUES (?, ?, ?, ?, ?)
        ");
        
        return $stmt->execute([
            $referenceType,
            $referenceId,
            $action,
            $description,
            $performedBy
        ]);
    } catch (PDOException $e) {
        error_log("Error logging activity: " . $e->getMessage());
        return false;
    }
}

/**
 * Get timeline for a specific item
 * 
 * @param string $referenceType 'complaint' or 'lost_item'
 * @param int $referenceId ID of the complaint or lost item
 * @return array Timeline events
 */
function getTimeline($referenceType, $referenceId) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT 
                al.*,
                CASE 
                    WHEN al.performed_by IS NOT NULL THEN CONCAT(s.first_name, ' ', s.last_name)
                    ELSE 'System'
                END as performed_by_name
            FROM activity_log al
            LEFT JOIN students s ON al.performed_by = s.user_id
            WHERE al.reference_type = ? AND al.reference_id = ?
            ORDER BY al.created_at ASC
        ");
        
        $stmt->execute([$referenceType, $referenceId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error getting timeline: " . $e->getMessage());
        return [];
    }
}

/**
 * Get action status (completed, active, pending)
 * 
 * @param string $action Action name
 * @param array $timeline All timeline events
 * @param int $currentIndex Current event index
 * @return string Status: 'completed', 'active', or 'pending'
 */
function getActionStatus($action, $timeline, $currentIndex) {
    $totalEvents = count($timeline);
    
    if ($currentIndex < $totalEvents - 1) {
        return 'completed';
    } elseif ($currentIndex === $totalEvents - 1) {
        return 'active';
    } else {
        return 'pending';
    }
}

/**
 * Predefined timeline templates for different reference types
 */
function getTimelineTemplate($referenceType, $currentStatus = null) {
    $templates = [
        'complaint' => [
            'submitted' => 'Complaint Submitted',
            'reviewed' => 'Reviewed by Admin',
            'scheduled' => 'Session Scheduled',
            'in_progress' => 'Session In Progress',
            'resolved' => 'Resolved'
        ],
        'lost_item' => [
            'reported' => 'Item Reported',
            'under_review' => 'Under Review',
            'found' => 'Item Found',
            'notified' => 'Student Notified',
            'claimed' => 'Item Claimed'
        ]
    ];
    
    return $templates[$referenceType] ?? [];
}
?>
