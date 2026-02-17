<?php
/**
 * Smart Matching Algorithm
 * Matches lost items with found items based on multiple criteria
 */

require_once 'db_connection.php';

/**
 * Calculate match score between a lost item and a found item
 * 
 * @param array $lostItem Lost item data
 * @param array $foundItem Found item data
 * @return float Match score (0-100)
 */
function calculateMatchScore($lostItem, $foundItem) {
    $score = 0;
    
    // 1. Name similarity (70% weight) - Using Levenshtein distance
    $lostName = strtolower(trim($lostItem['item_name']));
    $foundName = strtolower(trim($foundItem['item_name']));
    
    $maxLength = max(strlen($lostName), strlen($foundName));
    if ($maxLength > 0) {
        $distance = levenshtein($lostName, $foundName);
        $nameSimilarity = 1 - ($distance / $maxLength);
        $score += $nameSimilarity * 70;
    }
    
    // 2. Category match (20% weight)
    if (strtolower(trim($lostItem['category'])) === strtolower(trim($foundItem['category']))) {
        $score += 20;
    }
    
    // 3. Date proximity (5% weight) - Within 7 days
    $lostDate = strtotime($lostItem['date_lost']);
    $foundDate = strtotime($foundItem['date']);
    $daysDiff = abs($lostDate - $foundDate) / 86400; // Convert to days
    
    if ($daysDiff <= 7) {
        $dateScore = (7 - $daysDiff) / 7 * 5;
        $score += $dateScore;
    }
    
    // 4. Location similarity (5% weight)
    $lostLocation = strtolower(trim($lostItem['location']));
    $foundLocation = strtolower(trim($foundItem['location']));
    
    if (stripos($lostLocation, $foundLocation) !== false || stripos($foundLocation, $lostLocation) !== false) {
        $score += 5;
    }
    
    return round($score, 2);
}

/**
 * Get match reasons for display
 * 
 * @param array $lostItem Lost item data
 * @param array $foundItem Found item data
 * @param float $score Match score
 * @return array Reasons why items match
 */
function getMatchReasons($lostItem, $foundItem, $score) {
    $reasons = [];
    
    // Name similarity
    $lostName = strtolower(trim($lostItem['item_name']));
    $foundName = strtolower(trim($foundItem['item_name']));
    $maxLength = max(strlen($lostName), strlen($foundName));
    $distance = levenshtein($lostName, $foundName);
    $nameSimilarity = (1 - ($distance / $maxLength)) * 100;
    
    if ($nameSimilarity >= 90) {
        $reasons[] = "Same item name (100%)";
    } elseif ($nameSimilarity >= 70) {
        $reasons[] = "Very similar item name (" . round($nameSimilarity) . "%)";
    } elseif ($nameSimilarity >= 50) {
        $reasons[] = "Similar item name (" . round($nameSimilarity) . "%)";
    } else {
        $reasons[] = "Different item name";
    }
    
    // Category
    if (strtolower(trim($lostItem['category'])) === strtolower(trim($foundItem['category']))) {
        $reasons[] = "Same category";
    } else {
        $reasons[] = "Different category";
    }
    
    // Date
    $lostDate = strtotime($lostItem['date_lost']);
    $foundDate = strtotime($foundItem['date']);
    $daysDiff = abs($lostDate - $foundDate) / 86400;
    
    if ($daysDiff == 0) {
        $reasons[] = "Found on the same day";
    } elseif ($daysDiff == 1) {
        $reasons[] = "Found 1 day after you reported it";
    } elseif ($daysDiff <= 7) {
        $reasons[] = "Found " . round($daysDiff) . " days after report";
    } else {
        $reasons[] = "Found " . round($daysDiff) . " days after report";
    }
    
    // Location
    $lostLocation = strtolower(trim($lostItem['location']));
    $foundLocation = strtolower(trim($foundItem['location']));
    
    if ($lostLocation === $foundLocation) {
        $reasons[] = "Same location";
    } elseif (stripos($lostLocation, $foundLocation) !== false || stripos($foundLocation, $lostLocation) !== false) {
        $reasons[] = "Similar location";
    } else {
        $reasons[] = "Different location";
    }
    
    return $reasons;
}

/**
 * Find matches for a student's lost items
 * 
 * @param int $studentId Student ID
 * @param int $minScore Minimum match score (default: 40)
 * @param int $limit Maximum number of matches to return (default: 5)
 * @return array Array of matches with scores
 */
function findMatches($studentId, $minScore = 40, $limit = 5) {
    global $pdo;
    
    try {
        // Get student's pending lost items
        $stmt = $pdo->prepare("
            SELECT * FROM lost_items 
            WHERE student_id = ? AND status = 'pending'
            ORDER BY date DESC
        ");
        $stmt->execute([$studentId]);
        $lostItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($lostItems)) {
            return [];
        }
        
        // Get all found items (not claimed)
        $stmt = $pdo->prepare("
            SELECT li.*, s.first_name, s.last_name
            FROM lost_items li
            LEFT JOIN students s ON li.student_id = s.id
            WHERE li.status = 'found' AND li.claimed_by_student_id IS NULL
            ORDER BY li.date DESC
        ");
        $stmt->execute();
        $foundItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($foundItems)) {
            return [];
        }
        
        // Calculate matches
        $matches = [];
        
        foreach ($lostItems as $lostItem) {
            foreach ($foundItems as $foundItem) {
                $score = calculateMatchScore($lostItem, $foundItem);
                
                if ($score >= $minScore) {
                    $reasons = getMatchReasons($lostItem, $foundItem, $score);
                    
                    $matches[] = [
                        'lost_item_id' => $lostItem['id'],
                        'lost_item_name' => $lostItem['item_name'],
                        'found_item_id' => $foundItem['id'],
                        'found_item' => $foundItem,
                        'match_score' => $score,
                        'reasons' => $reasons
                    ];
                }
            }
        }
        
        // Sort by score (highest first)
        usort($matches, function($a, $b) {
            return $b['match_score'] <=> $a['match_score'];
        });
        
        // Return top matches
        return array_slice($matches, 0, $limit);
        
    } catch (PDOException $e) {
        error_log("Error finding matches: " . $e->getMessage());
        return [];
    }
}

/**
 * Get matches for display in dashboard
 * 
 * @param int $studentId Student ID
 * @return array Formatted matches for display
 */
function getMatchesForDashboard($studentId) {
    $matches = findMatches($studentId, 40, 5);
    
    $formattedMatches = [];
    foreach ($matches as $match) {
        $item = $match['found_item'];
        
        $formattedMatches[] = [
            'id' => $item['id'],
            'item_name' => $item['item_name'],
            'category' => $item['category'],
            'description' => $item['description'],
            'location' => $item['location'],
            'date' => $item['date'],
            'photo' => $item['photo'],
            'mime_type' => $item['mime_type'],
            'match_score' => $match['match_score'],
            'reasons' => $match['reasons'],
            'is_high_match' => $match['match_score'] >= 70
        ];
    }
    
    return $formattedMatches;
}
?>
