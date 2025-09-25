<?php 
// Include configuration file 
include_once 'config.php'; 
 
$status = $statusMsg = ''; 
if(!empty($_SESSION['status_response'])){ 
    $status_response = $_SESSION['status_response']; 
    $status = $status_response['status']; 
    $statusMsg = $status_response['status_msg']; 
     
    unset($_SESSION['status_response']); 
} 
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <div class="wrapper">
        <h2>Upload File</h2>

        <!-- Status message -->
        <?php if(!empty($statusMsg)){ ?>
        <div class="alert alert-<?php echo $status; ?>"><?php echo $statusMsg; ?></div>
        <?php } ?>

        <div class="col-md-12">
            <form method="post" action="upload.php" class="form" enctype="multipart/form-data">
                <div class="form-group">
                    <label>File</label>
                    <input type="file" name="file" class="form-control">
                </div>
                <div class="form-group">
                    <input type="submit" class="form-control btn-primary" name="submit" value="Upload" />
                </div>
            </form>
        </div>
    </div>

</body>

</html>