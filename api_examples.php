<?php
// Include Google drive api handler class 
include_once 'GoogleDriveApi.class.php';
include_once 'config.php';

// สมมติว่าเรามี access token แล้ว
$access_token = $_SESSION['google_access_token'] ?? null;

if (!$access_token) {
    echo "Please get access token first!";
    exit();
}

// Initialize Google Drive API class
$GoogleDriveApi = new GoogleDriveApi();

try {
    echo "<h2>Google Drive API Examples</h2>";

    // 1. List Files - ดูรายการไฟล์
    echo "<h3>1. List Files</h3>";
    $files = $GoogleDriveApi->ListFiles($access_token, array(
        'pageSize' => 10,
        'q' => "trashed=false" // เฉพาะไฟล์ที่ไม่ได้ถูกลบ
    ));
    
    echo "<ul>";
    foreach ($files['files'] as $file) {
        echo "<li>{$file['name']} (ID: {$file['id']}, Type: {$file['mimeType']})</li>";
    }
    echo "</ul>";

    // ถ้ามีไฟล์อยู่แล้ว ให้ใช้ไฟล์แรกเป็นตัวอย่าง
    $sample_file_id = !empty($files['files']) ? $files['files'][0]['id'] : null;

    if ($sample_file_id) {
        // 2. Get File - ดึงข้อมูลไฟล์
        echo "<h3>2. Get File Information</h3>";
        $file_info = $GoogleDriveApi->GetFile($access_token, $sample_file_id);
        echo "<pre>" . json_encode($file_info, JSON_PRETTY_PRINT) . "</pre>";

        // 3. Copy File - คัดลอกไฟล์
        echo "<h3>3. Copy File</h3>";
        $copy_metadata = array(
            'name' => 'Copy of ' . $file_info['name']
        );
        $copied_file = $GoogleDriveApi->CopyFile($access_token, $sample_file_id, $copy_metadata);
        echo "Copied file: {$copied_file['name']} (ID: {$copied_file['id']})";

        // 4. Update File Metadata - แก้ไขข้อมูลไฟล์
        echo "<h3>4. Update File Metadata</h3>";
        $update_metadata = array(
            'name' => $copied_file['name'] . ' - Updated',
            'description' => 'This is an updated file'
        );
        $updated_file = $GoogleDriveApi->UpdateFile($access_token, $copied_file['id'], $update_metadata);
        echo "Updated file: {$updated_file['name']}";

        // 5. Delete File - ลบไฟล์ (ลบไฟล์ที่คัดลอกมา)
        echo "<h3>5. Delete File</h3>";
        $delete_result = $GoogleDriveApi->DeleteFile($access_token, $copied_file['id']);
        if ($delete_result) {
            echo "File deleted successfully!";
        }
    }

    // 6. Create Folder - สร้างโฟลเดอร์
    echo "<h3>6. Create Folder</h3>";
    $new_folder = $GoogleDriveApi->CreateFolder($access_token, 'Test Folder ' . date('Y-m-d H:i:s'));
    echo "Created folder: {$new_folder['name']} (ID: {$new_folder['id']})";

    // 7. Create Text File - สร้างไฟล์ text ใหม่
    echo "<h3>7. Create Text File</h3>";
    $file_metadata = array(
        'name' => 'test-file.txt',
        'parents' => array($new_folder['id']) // วางไฟล์ในโฟลเดอร์ที่สร้างใหม่
    );
    $file_content = "This is a test file created via API\nCreated at: " . date('Y-m-d H:i:s');
    $new_file = $GoogleDriveApi->CreateFile($access_token, $file_metadata, $file_content, 'text/plain');
    echo "Created file: {$new_file['name']} (ID: {$new_file['id']})";

    // 8. Download File - ดาวน์โหลดไฟล์
    echo "<h3>8. Download File Content</h3>";
    $file_content = $GoogleDriveApi->DownloadFile($access_token, $new_file['id']);
    echo "<pre>" . htmlspecialchars($file_content) . "</pre>";

    // 9. Update File Content - แก้ไขเนื้อหาไฟล์
    echo "<h3>9. Update File Content</h3>";
    $new_content = $file_content . "\nUpdated at: " . date('Y-m-d H:i:s');
    $updated_content_file = $GoogleDriveApi->UpdateFile($access_token, $new_file['id'], null, $new_content, 'text/plain');
    echo "File content updated: {$updated_content_file['name']}";

    // 10. Search Files - ค้นหาไฟล์
    echo "<h3>10. Search Files</h3>";
    $search_results = $GoogleDriveApi->ListFiles($access_token, array(
        'q' => "name contains 'test'",
        'pageSize' => 5
    ));
    
    echo "<ul>";
    foreach ($search_results['files'] as $file) {
        echo "<li>{$file['name']} (ID: {$file['id']})</li>";
    }
    echo "</ul>";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

?>

<style>
body {
    font-family: Arial, sans-serif;
    margin: 20px;
    line-height: 1.6;
}

h2,
h3 {
    color: #333;
}

pre {
    background-color: #f4f4f4;
    padding: 10px;
    border-radius: 5px;
    overflow-x: auto;
}

ul {
    margin: 10px 0;
}

li {
    margin: 5px 0;
}
</style>