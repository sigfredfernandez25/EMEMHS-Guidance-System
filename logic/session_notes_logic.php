<?php
// Session Notes Logic - Backend for managing counseling session notes

require_once 'db_connection.php';
require_once 'sql_querries.php';

/**
 * Add a new session note
 */
function addSessionNote($data) {
    global $pdo;
    
    try {
        // Get next session number for this complaint
        $stmt = $pdo->prepare("SELECT COALESCE(MAX(session_number), 0) + 1 as next_session FROM session_notes WHERE complaint_id = ?");
        $stmt->execute([$data['complaint_id']]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $session_number = $result['next_session'];
        
        // Insert session note
        $sql = "INSERT INTO session_notes (
            complaint_id, session_number, session_date, session_time,
            presenting_problem_1, presenting_problem_2, presenting_problem_3,
            general_observations, session_summary, action_taken,
            follow_up_recommendations, next_appointment_date, next_appointment_time,
            counselor_name, counselor_id
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $data['complaint_id'],
            $session_number,
            $data['session_date'],
            $data['session_time'],
            $data['presenting_problem_1'] ?? null,
            $data['presenting_problem_2'] ?? null,
            $data['presenting_problem_3'] ?? null,
            $data['general_observations'] ?? null,
            $data['session_summary'],
            $data['action_taken'] ?? null,
            $data['follow_up_recommendations'] ?? null,
            $data['next_appointment_date'] ?? null,
            $data['next_appointment_time'] ?? null,
            $data['counselor_name'] ?? null,
            $data['counselor_id'] ?? null
        ]);
        
        $session_id = $pdo->lastInsertId();
        
        // Update session count in complaints table
        $stmt = $pdo->prepare("UPDATE complaints_concerns SET session_count = session_count + 1 WHERE id = ?");
        $stmt->execute([$data['complaint_id']]);
        
        return [
            'success' => true,
            'session_id' => $session_id,
            'session_number' => $session_number,
            'message' => 'Session note added successfully'
        ];
        
    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => 'Error adding session note: ' . $e->getMessage()
        ];
    }
}

/**
 * Update an existing session note
 */
function updateSessionNote($session_id, $data) {
    global $pdo;
    
    try {
        $sql = "UPDATE session_notes SET
            session_date = ?,
            session_time = ?,
            presenting_problem_1 = ?,
            presenting_problem_2 = ?,
            presenting_problem_3 = ?,
            general_observations = ?,
            session_summary = ?,
            action_taken = ?,
            follow_up_recommendations = ?,
            next_appointment_date = ?,
            next_appointment_time = ?,
            counselor_name = ?
        WHERE id = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $data['session_date'],
            $data['session_time'],
            $data['presenting_problem_1'] ?? null,
            $data['presenting_problem_2'] ?? null,
            $data['presenting_problem_3'] ?? null,
            $data['general_observations'] ?? null,
            $data['session_summary'],
            $data['action_taken'] ?? null,
            $data['follow_up_recommendations'] ?? null,
            $data['next_appointment_date'] ?? null,
            $data['next_appointment_time'] ?? null,
            $data['counselor_name'] ?? null,
            $session_id
        ]);
        
        return [
            'success' => true,
            'message' => 'Session note updated successfully'
        ];
        
    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => 'Error updating session note: ' . $e->getMessage()
        ];
    }
}

/**
 * Get all session notes for a complaint
 */
function getSessionNotesByComplaint($complaint_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT 
                sn.id as session_id,
                sn.complaint_id,
                sn.session_number,
                sn.session_date,
                sn.session_time,
                sn.presenting_problem_1,
                sn.presenting_problem_2,
                sn.presenting_problem_3,
                sn.general_observations,
                sn.session_summary,
                sn.action_taken,
                sn.follow_up_recommendations,
                sn.next_appointment_date,
                sn.next_appointment_time,
                sn.counselor_name,
                sn.created_at,
                sn.updated_at,
                cc.type as complaint_type,
                cc.severity,
                cc.status as complaint_status,
                s.id as student_id,
                s.first_name,
                s.last_name,
                s.grade_level,
                s.section,
                CONCAT(s.first_name, ' ', s.last_name) as student_name
            FROM session_notes sn
            JOIN complaints_concerns cc ON sn.complaint_id = cc.id
            JOIN students s ON cc.student_id = s.id
            WHERE sn.complaint_id = ?
            ORDER BY sn.session_number
        ");
        $stmt->execute([$complaint_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Get a specific session note
 */
function getSessionNote($session_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT 
                sn.id as session_id,
                sn.complaint_id,
                sn.session_number,
                sn.session_date,
                sn.session_time,
                sn.presenting_problem_1,
                sn.presenting_problem_2,
                sn.presenting_problem_3,
                sn.general_observations,
                sn.session_summary,
                sn.action_taken,
                sn.follow_up_recommendations,
                sn.next_appointment_date,
                sn.next_appointment_time,
                sn.counselor_name,
                sn.created_at,
                sn.updated_at,
                cc.type as complaint_type,
                cc.severity,
                cc.status as complaint_status,
                s.id as student_id,
                s.first_name,
                s.last_name,
                s.grade_level,
                s.section,
                CONCAT(s.first_name, ' ', s.last_name) as student_name
            FROM session_notes sn
            JOIN complaints_concerns cc ON sn.complaint_id = cc.id
            JOIN students s ON cc.student_id = s.id
            WHERE sn.id = ?
        ");
        $stmt->execute([$session_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        return null;
    }
}

/**
 * Delete a session note
 */
function deleteSessionNote($session_id) {
    global $pdo;
    
    try {
        // Get complaint_id before deleting
        $stmt = $pdo->prepare("SELECT complaint_id FROM session_notes WHERE id = ?");
        $stmt->execute([$session_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$result) {
            return ['success' => false, 'message' => 'Session note not found'];
        }
        
        $complaint_id = $result['complaint_id'];
        
        // Delete the session note
        $stmt = $pdo->prepare("DELETE FROM session_notes WHERE id = ?");
        $stmt->execute([$session_id]);
        
        // Update session count
        $stmt = $pdo->prepare("UPDATE complaints_concerns SET session_count = (SELECT COUNT(*) FROM session_notes WHERE complaint_id = ?) WHERE id = ?");
        $stmt->execute([$complaint_id, $complaint_id]);
        
        return [
            'success' => true,
            'message' => 'Session note deleted successfully'
        ];
        
    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => 'Error deleting session note: ' . $e->getMessage()
        ];
    }
}

/**
 * Get session count for a complaint
 */
function getSessionCount($complaint_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM session_notes WHERE complaint_id = ?");
        $stmt->execute([$complaint_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
        
    } catch (PDOException $e) {
        return 0;
    }
}

/**
 * Get latest session for a complaint
 */
function getLatestSession($complaint_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT 
                sn.id as session_id,
                sn.complaint_id,
                sn.session_number,
                sn.session_date,
                sn.session_time,
                sn.presenting_problem_1,
                sn.presenting_problem_2,
                sn.presenting_problem_3,
                sn.general_observations,
                sn.session_summary,
                sn.action_taken,
                sn.follow_up_recommendations,
                sn.next_appointment_date,
                sn.next_appointment_time,
                sn.counselor_name,
                sn.created_at,
                sn.updated_at,
                cc.type as complaint_type,
                cc.severity,
                cc.status as complaint_status,
                s.id as student_id,
                s.first_name,
                s.last_name,
                s.grade_level,
                s.section,
                CONCAT(s.first_name, ' ', s.last_name) as student_name
            FROM session_notes sn
            JOIN complaints_concerns cc ON sn.complaint_id = cc.id
            JOIN students s ON cc.student_id = s.id
            WHERE sn.complaint_id = ?
            ORDER BY sn.session_number DESC
            LIMIT 1
        ");
        $stmt->execute([$complaint_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        return null;
    }
}
