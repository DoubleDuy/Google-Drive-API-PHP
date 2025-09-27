<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Include Google drive api handler class and config
include_once 'GoogleDriveApi.class.php';
include_once 'config.php';

// Response helper function
function sendResponse($success = true, $data = null, $message = '', $code = 200) {
    http_response_code($code);
    echo json_encode([
        'success' => $success,
        'data' => $data,
        'message' => $message,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    exit();
}

// Get request method and path
$method = $_SERVER['REQUEST_METHOD'];
$path = $_GET['path'] ?? '';
$access_token = $_SESSION['google_access_token'] ?? $_GET['access_token'] ?? null;

// Check if access token exists
if (!$access_token) {
    sendResponse(false, null, 'Access token required', 401);
}

// Initialize Google Drive API
$GoogleDriveApi = new GoogleDriveApi();

try {
    switch ($method) {
        case 'GET':
            handleGetRequest($path, $GoogleDriveApi, $access_token);
            break;
        case 'POST':
            handlePostRequest($path, $GoogleDriveApi, $access_token);
            break;
        case 'PUT':
            handlePutRequest($path, $GoogleDriveApi, $access_token);
            break;
        case 'DELETE':
            handleDeleteRequest($path, $GoogleDriveApi, $access_token);
            break;
        default:
            sendResponse(false, null, 'Method not allowed', 405);
    }
} catch (Exception $e) {
    sendResponse(false, null, $e->getMessage(), 500);
}

function handleGetRequest($path, $api, $access_token) {
    switch ($path) {
        case 'files':
            // GET /api.php?path=files - List files
            $params = [];
            
            // Optional query parameters
            if (isset($_GET['q'])) $params['q'] = $_GET['q'];
            if (isset($_GET['pageSize'])) $params['pageSize'] = $_GET['pageSize'];
            if (isset($_GET['fields'])) $params['fields'] = $_GET['fields'];
            if (isset($_GET['orderBy'])) $params['orderBy'] = $_GET['orderBy'];
            
            $files = $api->ListFiles($access_token, $params);
            sendResponse(true, $files, 'Files retrieved successfully');
            break;
            
        case 'file':
            // GET /api.php?path=file&id=FILE_ID - Get file info
            $file_id = $_GET['id'] ?? null;
            if (!$file_id) {
                sendResponse(false, null, 'File ID required', 400);
            }
            
            $fields = $_GET['fields'] ?? '*';
            $file = $api->GetFile($access_token, $file_id, $fields);
            sendResponse(true, $file, 'File information retrieved successfully');
            break;
            
        case 'download':
            // GET /api.php?path=download&id=FILE_ID - Download file
            $file_id = $_GET['id'] ?? null;
            if (!$file_id) {
                sendResponse(false, null, 'File ID required', 400);
            }
            
            // Get file info first to get the filename
            $file_info = $api->GetFile($access_token, $file_id, 'name,mimeType');
            $file_content = $api->DownloadFile($access_token, $file_id);
            
            // Set appropriate headers for file download
            header('Content-Type: ' . $file_info['mimeType']);
            header('Content-Disposition: attachment; filename="' . $file_info['name'] . '"');
            echo $file_content;
            exit();
            break;
            
        default:
            sendResponse(false, null, 'Invalid endpoint', 404);
    }
}

function handlePostRequest($path, $api, $access_token) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    switch ($path) {
        case 'file':
            // POST /api.php?path=file - Create new file
            $file_metadata = $input['metadata'] ?? [];
            $file_content = $input['content'] ?? null;
            $mime_type = $input['mimeType'] ?? null;
            
            if (empty($file_metadata['name'])) {
                sendResponse(false, null, 'File name required in metadata', 400);
            }
            
            $result = $api->CreateFile($access_token, $file_metadata, $file_content, $mime_type);
            sendResponse(true, $result, 'File created successfully', 201);
            break;
            
        case 'folder':
            // POST /api.php?path=folder - Create new folder
            $folder_name = $input['name'] ?? null;
            $parent_id = $input['parentId'] ?? null;
            
            if (!$folder_name) {
                sendResponse(false, null, 'Folder name required', 400);
            }
            
            $result = $api->CreateFolder($access_token, $folder_name, $parent_id);
            sendResponse(true, $result, 'Folder created successfully', 201);
            break;
            
        case 'copy':
            // POST /api.php?path=copy - Copy file
            $file_id = $input['fileId'] ?? null;
            $metadata = $input['metadata'] ?? [];
            
            if (!$file_id) {
                sendResponse(false, null, 'File ID required', 400);
            }
            
            $result = $api->CopyFile($access_token, $file_id, $metadata);
            sendResponse(true, $result, 'File copied successfully', 201);
            break;
            
        case 'upload':
            // POST /api.php?path=upload - Upload file from form data
            if (!isset($_FILES['file'])) {
                sendResponse(false, null, 'No file uploaded', 400);
            }
            
            $file = $_FILES['file'];
            $file_name = $file['name'];
            $file_content = file_get_contents($file['tmp_name']);
            $mime_type = $file['type'];
            
            // Optional metadata from POST data
            $metadata = [
                'name' => $_POST['name'] ?? $file_name
            ];
            
            if (isset($_POST['parentId'])) {
                $metadata['parents'] = [$_POST['parentId']];
            }
            
            $result = $api->CreateFile($access_token, $metadata, $file_content, $mime_type);
            sendResponse(true, $result, 'File uploaded successfully', 201);
            break;
            
        default:
            sendResponse(false, null, 'Invalid endpoint', 404);
    }
}

function handlePutRequest($path, $api, $access_token) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    switch ($path) {
        case 'file':
            // PUT /api.php?path=file&id=FILE_ID - Update file
            $file_id = $_GET['id'] ?? null;
            if (!$file_id) {
                sendResponse(false, null, 'File ID required', 400);
            }
            
            $file_metadata = $input['metadata'] ?? null;
            $file_content = $input['content'] ?? null;
            $mime_type = $input['mimeType'] ?? null;
            
            if (!$file_metadata && !$file_content) {
                sendResponse(false, null, 'Either metadata or content required', 400);
            }
            
            $result = $api->UpdateFile($access_token, $file_id, $file_metadata, $file_content, $mime_type);
            sendResponse(true, $result, 'File updated successfully');
            break;
            
        default:
            sendResponse(false, null, 'Invalid endpoint', 404);
    }
}

function handleDeleteRequest($path, $api, $access_token) {
    switch ($path) {
        case 'file':
            // DELETE /api.php?path=file&id=FILE_ID - Delete file
            $file_id = $_GET['id'] ?? null;
            if (!$file_id) {
                sendResponse(false, null, 'File ID required', 400);
            }
            
            $result = $api->DeleteFile($access_token, $file_id);
            sendResponse(true, null, 'File deleted successfully');
            break;
            
        default:
            sendResponse(false, null, 'Invalid endpoint', 404);
    }
}

?>