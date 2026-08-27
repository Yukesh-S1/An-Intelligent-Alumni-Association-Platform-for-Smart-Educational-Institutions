<?php
echo "<h2>🔧 Admin Portal Status</h2>";

echo "<h3>✅ Available Pages:</h3>";
$pages = [
    'manage_accounts.php' => 'Main entry point (redirects to working version)',
    'manage_accounts_minimal.php' => 'Minimal working version - RECOMMENDED',
    'manage_accounts_simple.php' => 'Simple version (may have autofix issues)',
    'test_admin_page.php' => 'Diagnostic test page',
    'main_menu_admin.php' => 'Admin dashboard',
    'login.php' => 'Login page'
];

echo "<ul>";
foreach ($pages as $file => $description) {
    $status = file_exists($file) ? '✅' : '❌';
    echo "<li>$status <a href='$file'><strong>$file</strong></a> - $description</li>";
}
echo "</ul>";

echo "<h3>🎯 Recommended Flow:</h3>";
echo "<ol>";
echo "<li><strong>Login</strong>: <a href='login.php'>login.php</a> (use admin credentials)</li>";
echo "<li><strong>Admin Dashboard</strong>: <a href='main_menu_admin.php'>main_menu_admin.php</a></li>";
echo "<li><strong>Manage Users</strong>: <a href='manage_accounts_minimal.php'>manage_accounts_minimal.php</a></li>";
echo "</ol>";

echo "<h3>🔧 Troubleshooting:</h3>";
echo "<ul>";
echo "<li>If pages don't load: Check <a href='test_admin_page.php'>test_admin_page.php</a></li>";
echo "<li>If styling is broken: CSS cleanup was successful, use minimal version</li>";
echo "<li>If database errors: Check database connection and table structure</li>";
echo "</ul>";

echo "<h3>📊 File Status:</h3>";
$files = ['css/admin-clean.css', 'css/styles.css'];
echo "<table style='border-collapse: collapse; width: 100%;'>";
echo "<tr style='background: #f0f0f0;'><th style='border: 1px solid #ddd; padding: 0.5rem;'>File</th><th style='border: 1px solid #ddd; padding: 0.5rem;'>Status</th><th style='border: 1px solid #ddd; padding: 0.5rem;'>Size</th></tr>";

foreach ($files as $file) {
    echo "<tr>";
    echo "<td style='border: 1px solid #ddd; padding: 0.5rem;'><code>$file</code></td>";
    if (file_exists($file)) {
        $size = round(filesize($file) / 1024, 2);
        echo "<td style='border: 1px solid #ddd; padding: 0.5rem; color: green;'>✅ Exists</td>";
        echo "<td style='border: 1px solid #ddd; padding: 0.5rem;'>{$size} KB</td>";
    } else {
        echo "<td style='border: 1px solid #ddd; padding: 0.5rem; color: red;'>❌ Missing</td>";
        echo "<td style='border: 1px solid #ddd; padding: 0.5rem;'>-</td>";
    }
    echo "</tr>";
}
echo "</table>";

echo "<style>
body { font-family: 'Segoe UI', sans-serif; margin: 2rem; line-height: 1.6; }
h2, h3 { color: #2563eb; }
a { color: #2563eb; text-decoration: none; }
a:hover { text-decoration: underline; }
ul, ol { margin: 1rem 0; }
li { margin: 0.5rem 0; }
code { background: #f1f5f9; padding: 0.2rem 0.4rem; border-radius: 4px; }
</style>";
?>