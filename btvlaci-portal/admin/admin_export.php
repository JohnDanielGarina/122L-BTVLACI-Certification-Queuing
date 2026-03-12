<?php 
require_once '../config.php'; 
auth_check('admin');

$pdo = new PDO('sqlite:' . DB_PATH);

$filename = 'btvlaci_applications_' . date('Y-m-d') . '.csv';
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');

// Headers
fputcsv($output, ['App ID', 'Name', 'Email', 'Qualification', 'Status', 'Batch Code', 'Created']);

// Data
$where = $_GET['filter'] ? 'WHERE status = :status' : '';
$stmt = $pdo->prepare("SELECT a.application_code, ap.name, ap.email, a.qualification, a.status, b.batch_code, a.created_at 
                       FROM applications a 
                       JOIN applicants ap ON a.applicant_id = ap.id 
                       LEFT JOIN batches b ON a.batch_id = b.id $where 
                       ORDER BY a.created_at DESC");
if ($_GET['filter']) $stmt->bindValue(':status', $_GET['filter']);
$stmt->execute();
while ($row = $stmt->fetch()) {
  fputcsv($output, $row);
}

exit;
?>

