<?php 
/** 
 * 
 * This Google Drive API handler class is a custom PHP library to handle the Google Drive API calls. 
 * 
 * @class        GoogleDriveApi 
 * @author        CodexWorld 
 * @link        http://www.codexworld.com 
 * @version        1.0 
 */ 
class GoogleDriveApi { 
    const OAUTH2_TOKEN_URI = 'https://oauth2.googleapis.com/token'; 
    const DRIVE_FILE_UPLOAD_URI = 'https://www.googleapis.com/upload/drive/v3/files'; 
    const DRIVE_FILES_LIST_URI = 'https://www.googleapis.com/drive/v3/files'; 
    const DRIVE_FILE_META_URI = 'https://www.googleapis.com/drive/v3/files/'; 
     
    function __construct($params = array()) { 
        if (count($params) > 0){ 
            $this->initialize($params);         
        } 
    } 
     
    function initialize($params = array()) { 
        if (count($params) > 0){ 
            foreach ($params as $key => $val){ 
                if (isset($this->$key)){ 
                    $this->$key = $val; 
                } 
            }         
        } 
    } 
     
    public function GetAccessToken($client_id, $redirect_uri, $client_secret, $code) { 
        $curlPost = 'client_id=' . $client_id . '&redirect_uri=' . $redirect_uri . '&client_secret=' . $client_secret . '&code='. $code . '&grant_type=authorization_code'; 
        $ch = curl_init();         
        curl_setopt($ch, CURLOPT_URL, self::OAUTH2_TOKEN_URI);         
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);         
        curl_setopt($ch, CURLOPT_POST, 1);         
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);     
        $data = json_decode(curl_exec($ch), true); 
        $http_code = curl_getinfo($ch,CURLINFO_HTTP_CODE); 
         
        if ($http_code != 200) { 
            $error_msg = 'Failed to receieve access token'; 
            if (curl_errno($ch)) { 
                $error_msg = curl_error($ch); 
            } 
            throw new Exception('Error '.$http_code.': '.$error_msg); 
        } 
             
        return $data; 
    } 
     
    public function UploadFileToDrive($access_token, $file_content, $mime_type) { 
        $apiURL = self::DRIVE_FILE_UPLOAD_URI . '?uploadType=media'; 
         
        $ch = curl_init();         
        curl_setopt($ch, CURLOPT_URL, $apiURL);         
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);         
        curl_setopt($ch, CURLOPT_POST, 1);         
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: '.$mime_type, 'Authorization: Bearer '. $access_token)); 
        curl_setopt($ch, CURLOPT_POSTFIELDS, $file_content); 
        $data = json_decode(curl_exec($ch), true); 
        $http_code = curl_getinfo($ch,CURLINFO_HTTP_CODE);         
         
        if ($http_code != 200) { 
            $error_msg = 'Failed to upload file to Google Drive'; 
            if (curl_errno($ch)) { 
                $error_msg = curl_error($ch); 
            } 
            throw new Exception('Error '.$http_code.': '.$error_msg); 
        } 
 
        return $data['id']; 
    } 
     
    public function UpdateFileMeta($access_token, $file_id, $file_meatadata) { 
        $apiURL = self::DRIVE_FILE_META_URI . $file_id; 
         
        $ch = curl_init();         
        curl_setopt($ch, CURLOPT_URL, $apiURL);         
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);         
        curl_setopt($ch, CURLOPT_POST, 1);         
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: Bearer '. $access_token)); 
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH'); 
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($file_meatadata)); 
        $data = json_decode(curl_exec($ch), true); 
        $http_code = curl_getinfo($ch,CURLINFO_HTTP_CODE);         
         
        if ($http_code != 200) { 
            $error_msg = 'Failed to update file metadata'; 
            if (curl_errno($ch)) { 
                $error_msg = curl_error($ch); 
            } 
            throw new Exception('Error '.$http_code.': '.$error_msg); 
        } 
 
        return $data; 
    } 

    /**
     * List files from Google Drive
     * @param string $access_token - Google access token
     * @param array $params - Query parameters (q, pageSize, fields, etc.)
     * @return array - List of files
     */
    public function ListFiles($access_token, $params = array()) {
        $queryParams = array();
        
        // Default parameters
        $defaultParams = array(
            'fields' => 'files(id,name,mimeType,size,createdTime,modifiedTime,parents)',
            'pageSize' => 100
        );
        
        $params = array_merge($defaultParams, $params);
        
        foreach ($params as $key => $value) {
            $queryParams[] = $key . '=' . urlencode($value);
        }
        
        $apiURL = self::DRIVE_FILES_LIST_URI . '?' . implode('&', $queryParams);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiURL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $access_token));
        
        $data = json_decode(curl_exec($ch), true);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if ($http_code != 200) {
            $error_msg = 'Failed to list files from Google Drive';
            if (curl_errno($ch)) {
                $error_msg = curl_error($ch);
            }
            throw new Exception('Error ' . $http_code . ': ' . $error_msg);
        }
        
        return $data;
    }

    /**
     * Get file information from Google Drive
     * @param string $access_token - Google access token
     * @param string $file_id - File ID
     * @param string $fields - Fields to retrieve
     * @return array - File information
     */
    public function GetFile($access_token, $file_id, $fields = '*') {
        $apiURL = self::DRIVE_FILE_META_URI . $file_id . '?fields=' . urlencode($fields);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiURL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $access_token));
        
        $data = json_decode(curl_exec($ch), true);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if ($http_code != 200) {
            $error_msg = 'Failed to get file information';
            if (curl_errno($ch)) {
                $error_msg = curl_error($ch);
            }
            throw new Exception('Error ' . $http_code . ': ' . $error_msg);
        }
        
        return $data;
    }

    /**
     * Create a new file or folder in Google Drive
     * @param string $access_token - Google access token
     * @param array $file_metadata - File metadata (name, parents, mimeType, etc.)
     * @param string $file_content - File content (optional, for files with content)
     * @param string $mime_type - MIME type (optional, for files with content)
     * @return array - Created file information
     */
    public function CreateFile($access_token, $file_metadata, $file_content = null, $mime_type = null) {
        if ($file_content !== null && $mime_type !== null) {
            // Multipart upload for files with content
            $boundary = '-------314159265358979323846';
            $delimiter = "\r\n--" . $boundary . "\r\n";
            $close_delim = "\r\n--" . $boundary . "--";
            
            $metadata = json_encode($file_metadata);
            
            $multipart_body = $delimiter .
                'Content-Type: application/json' . "\r\n\r\n" .
                $metadata . $delimiter .
                'Content-Type: ' . $mime_type . "\r\n\r\n" .
                $file_content . $close_delim;
            
            $apiURL = self::DRIVE_FILE_UPLOAD_URI . '?uploadType=multipart';
            $content_type = 'multipart/related; boundary="' . $boundary . '"';
        } else {
            // Metadata only (for folders or empty files)
            $multipart_body = json_encode($file_metadata);
            $apiURL = self::DRIVE_FILES_LIST_URI;
            $content_type = 'application/json';
        }
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiURL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: ' . $content_type,
            'Authorization: Bearer ' . $access_token
        ));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $multipart_body);
        
        $data = json_decode(curl_exec($ch), true);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if ($http_code != 200) {
            $error_msg = 'Failed to create file';
            if (curl_errno($ch)) {
                $error_msg = curl_error($ch);
            }
            throw new Exception('Error ' . $http_code . ': ' . $error_msg);
        }
        
        return $data;
    }

    /**
     * Update an existing file in Google Drive
     * @param string $access_token - Google access token
     * @param string $file_id - File ID to update
     * @param array $file_metadata - New file metadata (optional)
     * @param string $file_content - New file content (optional)
     * @param string $mime_type - MIME type (required if file_content is provided)
     * @return array - Updated file information
     */
    public function UpdateFile($access_token, $file_id, $file_metadata = null, $file_content = null, $mime_type = null) {
        if ($file_content !== null && $mime_type !== null) {
            if ($file_metadata !== null) {
                // Multipart update (metadata + content)
                $boundary = '-------314159265358979323846';
                $delimiter = "\r\n--" . $boundary . "\r\n";
                $close_delim = "\r\n--" . $boundary . "--";
                
                $metadata = json_encode($file_metadata);
                
                $multipart_body = $delimiter .
                    'Content-Type: application/json' . "\r\n\r\n" .
                    $metadata . $delimiter .
                    'Content-Type: ' . $mime_type . "\r\n\r\n" .
                    $file_content . $close_delim;
                
                $apiURL = self::DRIVE_FILE_UPLOAD_URI . '/' . $file_id . '?uploadType=multipart';
                $content_type = 'multipart/related; boundary="' . $boundary . '"';
                $method = 'PATCH';
            } else {
                // Media only update
                $multipart_body = $file_content;
                $apiURL = self::DRIVE_FILE_UPLOAD_URI . '/' . $file_id . '?uploadType=media';
                $content_type = $mime_type;
                $method = 'PATCH';
            }
        } else if ($file_metadata !== null) {
            // Metadata only update
            $multipart_body = json_encode($file_metadata);
            $apiURL = self::DRIVE_FILE_META_URI . $file_id;
            $content_type = 'application/json';
            $method = 'PATCH';
        } else {
            throw new Exception('Either file_metadata or file_content must be provided');
        }
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiURL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: ' . $content_type,
            'Authorization: Bearer ' . $access_token
        ));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $multipart_body);
        
        $data = json_decode(curl_exec($ch), true);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if ($http_code != 200) {
            $error_msg = 'Failed to update file';
            if (curl_errno($ch)) {
                $error_msg = curl_error($ch);
            }
            throw new Exception('Error ' . $http_code . ': ' . $error_msg);
        }
        
        return $data;
    }

    /**
     * Delete a file from Google Drive
     * @param string $access_token - Google access token
     * @param string $file_id - File ID to delete
     * @return boolean - True if successful
     */
    public function DeleteFile($access_token, $file_id) {
        $apiURL = self::DRIVE_FILE_META_URI . $file_id;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiURL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $access_token));
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if ($http_code != 200 && $http_code != 204) {
            $error_msg = 'Failed to delete file';
            if (curl_errno($ch)) {
                $error_msg = curl_error($ch);
            }
            throw new Exception('Error ' . $http_code . ': ' . $error_msg);
        }
        
        return true;
    }

    /**
     * Copy a file in Google Drive
     * @param string $access_token - Google access token
     * @param string $file_id - Source file ID
     * @param array $file_metadata - Metadata for the copied file (name, parents, etc.)
     * @return array - Copied file information
     */
    public function CopyFile($access_token, $file_id, $file_metadata = array()) {
        $apiURL = self::DRIVE_FILE_META_URI . $file_id . '/copy';
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiURL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $access_token
        ));
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($file_metadata));
        
        $data = json_decode(curl_exec($ch), true);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if ($http_code != 200) {
            $error_msg = 'Failed to copy file';
            if (curl_errno($ch)) {
                $error_msg = curl_error($ch);
            }
            throw new Exception('Error ' . $http_code . ': ' . $error_msg);
        }
        
        return $data;
    }

    /**
     * Download file content from Google Drive
     * @param string $access_token - Google access token
     * @param string $file_id - File ID to download
     * @return string - File content
     */
    public function DownloadFile($access_token, $file_id) {
        $apiURL = self::DRIVE_FILE_META_URI . $file_id . '?alt=media';
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiURL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $access_token));
        
        $data = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if ($http_code != 200) {
            $error_msg = 'Failed to download file';
            if (curl_errno($ch)) {
                $error_msg = curl_error($ch);
            }
            throw new Exception('Error ' . $http_code . ': ' . $error_msg);
        }
        
        return $data;
    }

    /**
     * Create a folder in Google Drive
     * @param string $access_token - Google access token
     * @param string $folder_name - Folder name
     * @param string $parent_id - Parent folder ID (optional)
     * @return array - Created folder information
     */
    public function CreateFolder($access_token, $folder_name, $parent_id = null) {
        $metadata = array(
            'name' => $folder_name,
            'mimeType' => 'application/vnd.google-apps.folder'
        );
        
        if ($parent_id) {
            $metadata['parents'] = array($parent_id);
        }
        
        return $this->CreateFile($access_token, $metadata);
    }
} 
?>