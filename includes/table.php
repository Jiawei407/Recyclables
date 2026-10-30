<?php
// The calling page supplies $kind and its original $columns.
$table_records = page_records($kind);
if (!$table_records) {
    echo '<tr><td class="empty-state" colspan="' . count($columns) . '">No records to display.</td></tr>';
}
foreach ($table_records as $index => $row) {
    echo '<tr data-record data-date="' . e($row['created_at'] ?? $row['publish_date'] ?? $row['period'] ?? '') . '" data-status="' . e($row['status'] ?? '') . '">';
    foreach ($columns as $column) {
        $text='—'; $link='';
        switch (strtolower($column)) {
            case 'rank': $text=$index+1; break;
            case 'participant': case 'name': $text=$row['full_name'] ?? $row['name'] ?? '—'; break;
            case 'faculty': $text=$row['faculty'] ?? '—'; break;
            case 'role': $text=$row['role'] ?? '—'; break;
            case 'green points': case 'points': $text=$row['points'] ?? $row['points_awarded'] ?? '0'; break;
            case 'status': case 'state': $text=$row['status'] ?? (isset($row['publish_date']) ? ($row['publish_date'] > date('Y-m-d') ? 'Scheduled' : 'Published') : 'Saved'); break;
            case 'challenge': case 'title': case 'queue': $text=$row['title'] ?? ('Submission #' . ($row['id'] ?? '')); break;
            case 'activity': $text=$row['activity'] ?? $row['name'] ?? $row['title'] ?? $row['reason'] ?? '—'; break;
            case 'campaign': $text=$row['name']; break;
            case 'report': $text=$row['activity']; break;
            case 'submission': $text='#' . $row['id'] . (($row['duplicate_count'] ?? 0)>1 ? ' · Duplicate photo: check manually' : ''); break;
            case 'submitted': case 'time': case 'completed': $text=$row['created_at'] ?? $row['completed'] ?? '0'; break;
            case 'published': case 'publish date': $text=$row['publish_date']; break;
            case 'end date': $text=$row['end_date']; break;
            case 'date and time': $text=$row['activity_date']; break;
            case 'period': $text=$row['period'] ?? ($row['start_date'] . ' to ' . $row['end_date']); break;
            case 'participants': $text=$row['participants'] ?? $row['joined'] ?? '0'; break;
            case 'joined': case 'joined / eligible': $text=$row['joined'] ?? '0'; break;
            case 'active': $text=max(0,(int)$row['joined']-(int)$row['completed']); break;
            case 'completed / joined': $text=($row['completed'] ?? 0) . ' / ' . ($row['joined'] ?? 0); break;
            case 'completion': case 'rate': $text=($row['joined'] ? round(100*$row['completed']/$row['joined']) : 0) . '%'; break;
            case 'reason': $text=$row['reason'] ?? 'Manual point review'; break;
            case 'outcome': case 'recorded outcome': $text=$row['outcome'] ?? '—'; break;
            case 'actions': $text=$row['actions'] ?? '0'; break;
            case 'record': $text='#' . $row['id']; break;
            case 'achievement': $text=$row['name']; break;
            case 'count': $text=$row['required_count']; break;
            case 'item or activity': $text=$row['item']; break;
            case 'condition': $text=$row['condition_name']; break;
            case 'action': case 'details':
                $text='View';
                if ($kind === 'users') $link='admin/users-edit-user.php';
                if ($kind === 'rankings') $link=$role === 'admin' ? 'admin/leaderboard-review-user-points.php' : '';
                if ($kind === 'submissions') $link=$role === 'user' ? 'user/submit-evidence-submission-details.php' : 'coordinator/submissions-review-submission.php';
                if ($kind === 'challenges') $link=($role === 'admin' ? 'admin/challenge-balancing.php' : ($role === 'faculty' ? 'faculty/participation-campus-cleanup.php' : ($role === 'coordinator' ? 'coordinator/challenges-edit.php' : 'user/challenges-view-challenge.php')));
                if ($kind === 'campaigns') $link='coordinator/campaigns-edit.php';
                if ($kind === 'announcements') $link=$role . '/announcements-edit.php';
                if ($kind === 'activities') $link='faculty/faculty-activities-faculty-cleanup.php';
                if ($kind === 'reports') $link=$role === 'admin' ? 'admin/reports-faculty-report.php' : 'faculty/submit-report-activity-report.php';
                if ($kind === 'audit') $link='admin/audit-log-audit-details.php';
                break;
        }
        echo '<td>';
        if ($link) echo '<a class="text-link" href="' . e($base . '/' . $link . '?id=' . $row['id']) . '">' . e($text) . '</a>';
        else echo e($text);
        echo '</td>';
    }
    echo '</tr>';
}
