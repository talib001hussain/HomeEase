<?php
include 'database.php';

$today = date('Y-m-d');
$tomorrow = date('Y-m-d', strtotime('+1 day'));

// Automatically mark overdue tasks
$conn->query("UPDATE tasks SET status='overdue' WHERE due_date < '$today' AND status='pending'");

$result = $conn->query("SELECT * FROM tasks ORDER BY created_at DESC");
$output = '';

while ($row = $result->fetch_assoc()) {
    $statusBadge = '';
    $alarmBadge = '';
    $dueSoonAttr = '';

    // ✅ Always set dueSoonAttr and alarmBadge regardless of status
    if (in_array($row["status"], ['pending', 'in progress'])) {
        if ($row["due_date"] === $today) {
            $dueSoonAttr = 'data-due-soon="today"';
            $alarmBadge = '<span class="badge bg-danger ms-2">⏰ Due Today</span>';
        } elseif ($row["due_date"] === $tomorrow) {
            $dueSoonAttr = 'data-due-soon="tomorrow"';
            $alarmBadge = '<span class="badge bg-info ms-2">🔔 Due Tomorrow</span>';
        }
    }

    

    // Status Badge
    switch ($row["status"]) {
        case "pending":
            $statusBadge = '<span class="badge bg-warning">Pending</span>';
            break;
        case "in progress":
            $statusBadge = '<span class="badge bg-primary">In Progress</span>';
            break;
        case "completed":
            $statusBadge = '<span class="badge bg-success">Completed</span>';
            break;
        case "overdue":
            $statusBadge = '<span class="badge bg-danger">Overdue</span>';
            break;
    }

    // Buttons based on status
    $progressButton = ($row["status"] == "pending") 
        ? '<button class="btn btn-info btn-sm progress-task" data-id="'.$row["id"].'">⏳ In Progress</button>' 
        : '';
    $completeButton = ($row["status"] == "in progress") 
        ? '<button class="btn btn-success btn-sm complete-task" data-id="'.$row["id"].'">✅ Complete</button>' 
        : '';

    // ✅ Inject data-due-soon attribute to trigger audio alert in JS
    $output .= '
    <li class="list-group-item d-flex justify-content-between align-items-center" '.$dueSoonAttr.' data-due-date="'.$row["due_date"].'">
        <div>
            <span>'.$row["task"].' <small class="text-muted">(Due: '.$row["due_date"].')</small></span>
            '.$statusBadge.'
            '.$alarmBadge.'
            <span class="badge bg-secondary ms-2 timer-badge" style="min-width:90px;">--:--:--</span>
        </div>
        <div>
            '.$progressButton.'
            '.$completeButton.'
            <button class="btn btn-warning btn-sm edit-task" data-id="'.$row["id"].'" data-task="'.$row["task"].'" data-due="'.$row["due_date"].'">✏️ Edit</button>
            <button class="btn btn-danger btn-sm delete-task" data-id="'.$row["id"].'">🗑 Delete</button>
        </div>
    </li>';
}

echo $output;
