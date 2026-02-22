<?php
require_once(__DIR__ . '/../config.php');
require_login();

$PAGE->set_url('/user/portfoliologs.php');
$PAGE->set_pagelayout('mypublic');
$PAGE->set_title('Public Profile');
$PAGE->set_heading('Public Profile Page');

echo $OUTPUT->header();
echo "<h2>Welcome to the Public Profile Page</h2>";
echo "<p>This is a simplified version for your LMS (Mapandan Catholic School).</p>";
echo $OUTPUT->footer();
