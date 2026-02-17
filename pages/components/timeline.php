<?php
/**
 * Timeline Component
 * Displays activity timeline for complaints and lost items
 * 
 * Usage:
 * include 'components/timeline.php';
 * renderTimeline('complaint', $complaint_id);
 */

require_once __DIR__ . '/../../logic/activity_logger.php';

function renderTimeline($referenceType, $referenceId) {
    $timeline = getTimeline($referenceType, $referenceId);
    $template = getTimelineTemplate($referenceType);
    
    ?>
    <style>
        .timeline-container {
            background: white;
            padding: 1rem 1.25rem;
            border-radius: 0.5rem;
            border: 1px solid #e5e7eb;
        }
        
        .timeline {
            position: relative;
            padding-left: 32px;
        }
        
        .timeline::before {
            content: '';
            position: absolute;
            left: 11px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #e5e7eb;
        }
        
        .timeline-item {
            position: relative;
            padding-bottom: 1.25rem;
        }
        
        .timeline-item:last-child {
            padding-bottom: 0;
        }
        
        .timeline-marker {
            position: absolute;
            left: -25px;
            top: 2px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            border: 2px solid #e5e7eb;
            background: white;
            transition: all 0.3s ease;
        }
        
        .timeline-item.completed .timeline-marker {
            background: #22c55e;
            border-color: #22c55e;
        }
        
        .timeline-item.active .timeline-marker {
            background: #3b82f6;
            border-color: #3b82f6;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% {
                box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.4);
            }
            50% {
                box-shadow: 0 0 0 6px rgba(59, 130, 246, 0);
            }
        }
        
        .timeline-content {
            background: #f8fafc;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            border-left: 2px solid #e5e7eb;
        }
        
        .timeline-item.completed .timeline-content {
            border-left-color: #22c55e;
        }
        
        .timeline-item.active .timeline-content {
            border-left-color: #3b82f6;
        }
        
        .timeline-title {
            font-weight: 600;
            color: #0f172a;
            font-size: 0.875rem;
            margin-bottom: 0.25rem;
        }
        
        .timeline-date {
            font-size: 0.75rem;
            color: #64748b;
            margin-bottom: 0.5rem;
        }
        
        .timeline-description {
            font-size: 0.8125rem;
            color: #475569;
            line-height: 1.4;
        }
        
        .timeline-badge {
            display: inline-block;
            padding: 0.25rem 0.625rem;
            border-radius: 0.375rem;
            font-size: 0.625rem;
            font-weight: 600;
            text-transform: uppercase;
            margin-top: 0.5rem;
            letter-spacing: 0.025em;
        }
        
        .badge-completed {
            background: #dcfce7;
            color: #166534;
        }
        
        .badge-active {
            background: #dbeafe;
            color: #1e40af;
        }
        
        .badge-pending {
            background: #fef3c7;
            color: #92400e;
        }
    </style>
    
    <div class="timeline-container">
        <h3 style="margin-bottom: 1rem; color: #0f172a; font-size: 0.9375rem; font-weight: 600;">
            Activity Timeline
        </h3>
        
        <div class="timeline">
            <?php if (empty($timeline)): ?>
                <p style="color: #94a3b8; text-align: center; padding: 1.5rem 0; font-size: 0.875rem;">
                    No activity recorded yet.
                </p>
            <?php else: ?>
                <?php foreach ($timeline as $index => $event): ?>
                    <?php
                        $isLast = ($index === count($timeline) - 1);
                        $status = $isLast ? 'active' : 'completed';
                    ?>
                    <div class="timeline-item <?php echo $status; ?>">
                        <div class="timeline-marker"></div>
                        <div class="timeline-content">
                            <div class="timeline-title">
                                <?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $event['action']))); ?>
                            </div>
                            <div class="timeline-date">
                                <?php echo date('M j, Y \a\t g:i A', strtotime($event['created_at'])); ?>
                            </div>
                            <?php if (!empty($event['description'])): ?>
                                <div class="timeline-description">
                                    <?php echo htmlspecialchars($event['description']); ?>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($event['performed_by_name'])): ?>
                                <div class="timeline-description" style="margin-top: 0.5rem; font-size: 0.75rem;">
                                    By: <?php echo htmlspecialchars($event['performed_by_name']); ?>
                                </div>
                            <?php endif; ?>
                            <span class="timeline-badge badge-<?php echo $status; ?>">
                                <?php echo ucfirst($status); ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    <?php
}
?>
