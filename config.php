<?php 
// Database configuration    
require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$clientID = $_ENV['GOOGLE_CLIENT_ID'];
$clientSecret = $_ENV['GOOGLE_CLIENT_SECRET'];
$googleOauthScope = $_ENV['GOOGLE_OAUTH_SCOPE'];
$redirectUri = $_ENV['REDIRECT_URI'];
 
// Start session 
if(!session_id()) session_start(); 

if (isset($_GET['debug'])) {
    echo "<h2>🔍 Debug Information</h2>";
    echo "<pre style='background:#f5f5f5; padding:15px; border:1px solid #ddd;'>";
    echo "Session ID: " . session_id() . "\n";
    echo "Session Contents:\n";
    print_r($_SESSION);
    echo "\nEnvironment Variables:\n";
    echo "CLIENT_ID: " . substr($clientID, 0, 20) . "...\n";
    echo "REDIRECT_URI: " . $redirectUri . "\n";
    echo "OAUTH_SCOPE: " . $googleOauthScope . "\n";
    echo "</pre>";
    
    if (isset($_SESSION['google_access_token'])) {
        echo "<h3>✅ Found Access Token!</h3>";
        echo "<textarea rows='5' cols='100' readonly style='width:100%;'>";
        echo $_SESSION['google_access_token'];
        echo "</textarea>";
    } else {
        echo "<h3>❌ No Access Token Found</h3>";
        echo "<p>You need to go through OAuth flow first.</p>";
    }
}
 
// Google OAuth URL 
$googleOauthURL = 'https://accounts.google.com/o/oauth2/auth?scope=' . urlencode($googleOauthScope) . '&redirect_uri=' . $redirectUri . '&response_type=code&client_id=' . $clientID . '&access_type=online'; 
 
?>