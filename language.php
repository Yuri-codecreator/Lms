<?php
require_once __DIR__ . '/../config.php';

// language_form.php - standalone version

session_start();

// Simulate a logged-in user
if (!isset($_SESSION['user'])) {
    $_SESSION['user'] = [
        'id' => 1,
        'name' => 'John Doe',
        'lang' => 'en' // default language
    ];
}

$user = &$_SESSION['user'];

// List of available languages
$languages = [
    'en' => 'English',
    'es' => 'Spanish',
    'fr' => 'French',
    'de' => 'German',
    'jp' => 'Japanese'
];

$successMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selectedLang = $_POST['lang'] ?? $user['lang'];
    
    if (array_key_exists($selectedLang, $languages)) {
        $user['lang'] = $selectedLang;
        $successMessage = "Language changed successfully to " . $languages[$selectedLang] . "!";
    } else {
        $successMessage = "Invalid language selection.";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Change Preferred Language</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 50px; }
        .success { color: green; margin-bottom: 20px; }
        label, select { font-size: 16px; margin-right: 10px; }
        button { padding: 5px 10px; font-size: 16px; }
    </style>
</head>
<body>

<h2>Change Preferred Language for <?php echo htmlspecialchars($user['name']); ?></h2>

<?php if ($successMessage): ?>
    <div class="success"><?php echo htmlspecialchars($successMessage); ?></div>
<?php endif; ?>

<form method="post">
    <label for="lang">Select Language:</label>
    <select name="lang" id="lang">
        <?php foreach ($languages as $code => $name): ?>
            <option value="<?php echo $code; ?>" <?php if ($user['lang'] === $code) echo 'selected'; ?>>
                <?php echo htmlspecialchars($name); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <button type="submit">Save</button>
</form>

<p>Current language: <strong><?php echo htmlspecialchars($languages[$user['lang']]); ?></strong></p>

</body>
</html>
