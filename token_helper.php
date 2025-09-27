<?php
include_once 'config.php';
include_once 'GoogleDriveApi.class.php';
?>
<!DOCTYPE html>
<html>

<head>
    <title>🔑 Get Google Drive Token</title>
    <style>
    body {
        font-family: Arial, sans-serif;
        max-width: 800px;
        margin: 50px auto;
        padding: 20px;
        background: #f5f5f5;
    }

    .container {
        background: white;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .success {
        background: #d4edda;
        color: #155724;
        padding: 15px;
        border-radius: 5px;
        margin: 20px 0;
    }

    .error {
        background: #f8d7da;
        color: #721c24;
        padding: 15px;
        border-radius: 5px;
        margin: 20px 0;
    }

    .info {
        background: #d1ecf1;
        color: #0c5460;
        padding: 15px;
        border-radius: 5px;
        margin: 20px 0;
    }

    .btn {
        background: #007bff;
        color: white;
        padding: 12px 24px;
        text-decoration: none;
        border-radius: 5px;
        display: inline-block;
        margin: 10px 5px;
    }

    .btn-success {
        background: #28a745;
    }

    .btn-danger {
        background: #dc3545;
    }

    textarea {
        width: 100%;
        height: 100px;
        font-family: monospace;
        font-size: 12px;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 5px;
    }

    .debug {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        padding: 15px;
        border-radius: 5px;
        margin: 20px 0;
        font-family: monospace;
        font-size: 12px;
    }
    </style>
</head>

<body>
    <div class="container">
        <h1>🔑 Google Drive Access Token Helper</h1>

        <?php
        // Check if we're coming back from OAuth
        if (isset($_GET['code'])) {
            echo "<div class='info'><h3>🔄 Processing OAuth Code...</h3></div>";
            
            try {
                $GoogleDriveApi = new GoogleDriveApi();
                $tokenData = $GoogleDriveApi->GetAccessToken($clientID, $_GET['redirect_uri'] ?? $redirectUri, $clientSecret, $_GET['code']);
                
                // Store token in session
                $_SESSION['google_access_token'] = $tokenData['access_token'];
                
                // Also save to file for backup
                file_put_contents('access_token_backup.txt', $tokenData['access_token']);
                
                echo "<div class='success'>";
                echo "<h2>✅ SUCCESS! Got Access Token</h2>";
                echo "<h3>📋 Your Access Token:</h3>";
                echo "<textarea onclick='this.select()' readonly>" . $tokenData['access_token'] . "</textarea>";
                echo "<p><strong>Token Length:</strong> " . strlen($tokenData['access_token']) . " characters</p>";
                echo "<p><strong>Expires In:</strong> " . ($tokenData['expires_in'] ?? 3600) . " seconds (" . round(($tokenData['expires_in'] ?? 3600)/60) . " minutes)</p>";
                
                echo "<h3>🎯 Next Steps:</h3>";
                echo "<ol>";
                echo "<li>Copy the token above (click to select all)</li>";
                echo "<li>Open <strong>api_tester.html</strong></li>";
                echo "<li>Paste the token in the 'Access Token' field</li>";
                echo "<li>Start testing the APIs!</li>";
                echo "</ol>";
                
                echo "<div style='margin: 20px 0;'>";
                echo "<a href='api_tester.html' class='btn btn-success'>🧪 Open API Tester</a>";
                echo "<a href='api_examples.php' class='btn'>📝 View Examples</a>";
                echo "</div>";
                
                echo "</div>";
                
            } catch (Exception $e) {
                echo "<div class='error'>";
                echo "<h2>❌ Error Getting Token</h2>";
                echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
                echo "<p><strong>Code:</strong> " . $_GET['code'] . "</p>";
                echo "<a href='?' class='btn btn-danger'>🔄 Try Again</a>";
                echo "</div>";
            }
            
        } else {
            // Show authorization button
            echo "<div class='info'>";
            echo "<h2>📋 Step 1: Get Authorization</h2>";
            echo "<p>Click the button below to authorize access to your Google Drive:</p>";
            echo "<a href='{$googleOauthURL}' class='btn btn-success' style='font-size: 18px; padding: 15px 30px;'>🔐 Authorize Google Drive Access</a>";
            echo "</div>";
            
            echo "<div class='info'>";
            echo "<h3>🔍 What will happen:</h3>";
            echo "<ol>";
            echo "<li>You'll be redirected to Google's login page</li>";
            echo "<li>Sign in with your Google account</li>";
            echo "<li>Grant permission to access Google Drive</li>";
            echo "<li>You'll be redirected back here with an access token</li>";
            echo "</ol>";
            echo "</div>";
        }
        
        // Show current status
        echo "<hr style='margin: 30px 0;'>";
        echo "<h3>🔍 Current Status</h3>";
        
        if (isset($_SESSION['google_access_token'])) {
            echo "<div class='success'>";
            echo "<p>✅ <strong>Access token found in session!</strong></p>";
            echo "<p><strong>Token Preview:</strong> " . substr($_SESSION['google_access_token'], 0, 30) . "...</p>";
            echo "<p><strong>Full Token:</strong></p>";
            echo "<textarea onclick='this.select()' readonly>" . $_SESSION['google_access_token'] . "</textarea>";
            echo "</div>";
        } else {
            echo "<div class='error'>";
            echo "<p>❌ <strong>No access token found in session</strong></p>";
            echo "<p>You need to authorize first using the button above.</p>";
            echo "</div>";
        }
        
        // Check if backup file exists
        if (file_exists('access_token_backup.txt')) {
            $backup_token = file_get_contents('access_token_backup.txt');
            echo "<div class='info'>";
            echo "<p>💾 <strong>Backup token file found!</strong></p>";
            echo "<p><strong>Backup Token:</strong></p>";
            echo "<textarea onclick='this.select()' readonly>" . $backup_token . "</textarea>";
            echo "</div>";
        }
        
        // Debug information
        if (isset($_GET['debug'])) {
            echo "<div class='debug'>";
            echo "<h3>🐛 Debug Information</h3>";
            echo "<strong>Session Data:</strong><br>";
            echo "<pre>" . print_r($_SESSION, true) . "</pre>";
            echo "<strong>GET Parameters:</strong><br>";
            echo "<pre>" . print_r($_GET, true) . "</pre>";
            echo "<strong>OAuth URL:</strong><br>";
            echo "<pre>" . $googleOauthURL . "</pre>";
            echo "</div>";
        }
        
        echo "<hr style='margin: 30px 0;'>";
        echo "<p style='text-align: center;'>";
        echo "<a href='?debug=1' class='btn'>🐛 Show Debug Info</a> ";
        echo "<a href='?' class='btn'>🔄 Refresh Page</a>";
        echo "</p>";
        ?>
    </div>
</body>

</html>