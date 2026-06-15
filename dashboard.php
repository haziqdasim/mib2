<?php
// Define directories and file path tracking indicators
$upload_dir = 'assets/slide/';
$config_file = 'active_slide.txt';
$interval_file = 'carousel_interval.txt';

// Ensure directory exists safely on target servers
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Default system baseline image initialization
if (!file_exists($config_file)) {
    file_put_contents($config_file, '10.png');
}

// Default interval timing configuration setting (Default: 5 seconds)
if (!file_exists($interval_file)) {
    file_put_contents($interval_file, '5');
}

$message = "";
$message_type = "";

// Handle Carousel Interval Updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['carousel_interval'])) {
    $interval_val = intval($_POST['carousel_interval']);
    if ($interval_val >= 1) {
        file_put_contents($interval_file, $interval_val);
        $message = "Carousel timing loop interval updated to " . $interval_val . " seconds.";
        $message_type = "success";
    } else {
        $message = "Error: Timing value must be at least 1 second.";
        $message_type = "danger";
    }
}

// Handle file processing on form submission execution pipelines
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['imageUpload'])) {
    $file = $_FILES['imageUpload'];

    // Safely parse individual file extension details
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed_exts = ['jpg', 'jpeg', 'png', 'webp'];

    if (!in_array($file_ext, $allowed_exts)) {
        $message = "Error: Invalid file format type. Please upload a standard JPG, PNG, or WEBP image layout.";
        $message_type = "danger";
    } elseif ($file['error'] !== 0) {
        $message = "An error occurred while uploading. System Error Code: " . $file['error'];
        $message_type = "danger";
    } else {
        $sanitized_name = time() . '_' . preg_replace("/[^a-zA-Z0-9.]/", "_", $file['name']);
        $target_path = $upload_dir . $sanitized_name;

        if (move_uploaded_file($file['tmp_name'], $target_path)) {
            $message = "Success! New image asset has been uploaded and stored.";
            $message_type = "success";
        } else {
            $message = "Critical Error: Failed to write file payloads to targeted folder pathways.";
            $message_type = "danger";
        }
    }
}

// Handle single-action immediate file removal requests
if (isset($_GET['delete'])) {
    $file_to_delete = basename($_GET['delete']);
    $full_delete_path = $upload_dir . $file_to_delete;

    if (file_exists($full_delete_path)) {
        unlink($full_delete_path);

        // If the fallback reference configuration mirrors the deleted file, sync a refresh fallback flag
        if (file_exists($config_file) && trim(file_get_contents($config_file)) === $file_to_delete) {
            file_put_contents($config_file, '10.png');
        }

        $message = "Asset removed completely from active display view maps.";
        $message_type = "success";
    }
}

// Fetch active interval tracking configurations
$current_interval = file_exists($interval_file) ? file_get_contents($interval_file) : '5';

// Scan the current directory configuration folder values
$active_slides = array_diff(scandir($upload_dir), array('.', '..'));
$active_slides = array_values($active_slides); // Reset array keys cleanly
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Media Management Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        @font-face {
            font-family: 'FWC2026-NormalRegular';
            src: url('/fonts/FWC2026-NormalRegular.ttf') format('truetype');
            font-weight: normal;
            font-style: normal;
        }

        body {
            background-color: #121212;
            color: #fff;
            font-family: 'FWC2026-NormalRegular', -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        .admin-card {
            background-color: #1a1a1a;
            border: 1px solid #2d2d2d;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        }

        .inter {
            font-family: 'Inter', sans-serif;
        }

        .btn-dark-red {
            background-color: #731311;
            color: white;
            border: none;
        }

        .btn-dark-red:hover {
            background-color: #D40101;
            color: white;
        }

        .btn-dark-green {
            background-color: #004E3C;
            color: white;
            border: none;
        }

        .btn-dark-green:hover {
            background-color: #00C953;
            color: white;
        }

        .preview-box {
            border: 2px dashed #444;
            border-radius: 8px;
            height: 280px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #0b0b0b;
            overflow: hidden;
        }

        .preview-box img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .table-dark-custom {
            background-color: #1a1a1a;
            color: white;
        }

        .table-dark-custom th {
            background-color: #0b0b0b;
            border-color: #2d2d2d;
        }

        .table-dark-custom td {
            border-color: #2d2d2d;
            vertical-align: middle;
        }
    </style>
</head>

<body>

    <nav class="navbar navbar-dark bg-black border-bottom border-secondary py-3">
        <div class="container-fluid px-4">
            <a class="navbar-brand d-flex align-items-center" href="#">
                <span class="fs-5 tracking-wider text-uppercase">FWC Multipurpose Information Board</span>
            </a>
            <div class="d-flex align-items-center">
                <span class="badge bg-danger p-2 inter me-2"><i class="bi bi-broadcast me-1"></i> Carousel Loop
                    Active</span>
                <a href="index.php" class="btn btn-outline-light btn-sm inter text-uppercase tracking-wider px-3"
                    style="font-size: 0.82rem; font-weight:600;">
                    <i class="bi bi-tv me-1"></i> Display
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid p-4">

        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show inter" role="alert">
                <?php echo $message; ?>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row g-4">

            <div class="col-lg-5">
                <div class="card admin-card p-4">
                    <h4 class="mb-4 text-white"><i class="bi bi-cloud-arrow-up me-2 text-danger"></i>Upload New Slide
                        Media</h4>

                    <form action="dashboard.php" method="POST" enctype="multipart/form-data">
                        <div class="mb-4">
                            <label for="imageUpload"
                                class="form-label inter text-gray-400 small fw-bold text-uppercase">Select Image
                                File</label>
                            <input class="form-control bg-dark text-white border-secondary inter" type="file"
                                name="imageUpload" id="imageUpload" accept="image/*" required>
                            <div class="form-text text-muted small inter">Recommended: 16:9 Aspect Ratio (e.g.,
                                1920x1080) to accurately match the screen frame scaling properties perfectly.</div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label inter text-gray-400 small fw-bold text-uppercase">Image
                                Preview</label>
                            <div class="preview-box" id="previewContainer">
                                <div class="text-center text-muted inter" id="previewPlaceholder">
                                    <i class="bi bi-image fs-1 d-block mb-2"></i>
                                    <span>No file selected for staging</span>
                                </div>
                                <img src="" id="imagePreview" class="d-none" alt="Staged Upload File Target">
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-dark-green py-2 fw-bold text-uppercase tracking-wider">
                                <i class="bi bi-plus-circle me-2"></i>Update Folder Assets
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="card admin-card p-4 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                        <h4 class="mb-0 text-white"><i class="bi bi-images me-2 text-success"></i>Current Board Assets
                        </h4>

                        <div class="d-flex align-items-center gap-2">
                            <form action="dashboard.php" method="POST"
                                class="d-flex align-items-center bg-dark rounded border border-secondary p-1">
                                <div class="input-group input-group-sm" style="max-width: 300px;">
                                    <span class="input-group-text bg-transparent border-0 text-muted small inter"><i
                                            class="bi bi-stopwatch-fill me-1"></i>Loop:</span>
                                    <div data-bs-theme="dark">
                                        <input type="number" class="form-control text-center inter px-1"
                                            name="carousel_interval"
                                            value="<?php echo htmlspecialchars($current_interval); ?>" min="1" required
                                            style="width: 50px;">
                                    </div>
                                    <span
                                        class="input-group-text bg-transparent border-0 text-white small inter">second/s</span>
                                    <button class="btn btn-dark-green btn-sm rounded px-2" type="submit">Apply</button>
                                </div>
                            </form>
                            <span class="badge bg-dark border border-secondary text-light px-3 py-2 inter"
                                style="height: 38px; display: inline-flex; align-items: center;">Total Available:
                                <?php echo count($active_slides); ?></span>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-dark-custom align-middle inter">
                            <thead>
                                <tr>
                                    <th scope="col" width="20%">Thumbnail Preview</th>
                                    <th scope="col" width="55%">File Path Identifier Location</th>
                                    <th scope="col" width="25%" class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($active_slides) > 0): ?>
                                    <?php foreach ($active_slides as $slide): ?>
                                        <tr>
                                            <td>
                                                <img src="<?php echo $upload_dir . $slide; ?>"
                                                    class="rounded border border-secondary"
                                                    style="width: 80px; height: 45px; object-fit: cover;">
                                            </td>
                                            <td>
                                                <span class="fw-bold text-truncate d-inline-block"
                                                    style="max-width: 320px;"><?php echo $slide; ?></span><br>
                                                <small class="text-muted"><?php echo $upload_dir . $slide; ?></small>
                                            </td>
                                            <td class="text-end">
                                                <a href="dashboard.php?delete=<?php echo urlencode($slide); ?>"
                                                    class="btn btn-sm btn-dark-red p-2 px-3 fw-bold rounded-3"
                                                    onclick="return confirm('Are you sure you want to delete this media element permanently?')"
                                                    title="Delete File">
                                                    <i class="bi bi-trash-fill me-1"></i> Delete
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="text-center py-5 text-muted">No images are in the folder
                                            queue directory locations. Upload file content to view here.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
        crossorigin="anonymous"></script>

    <script>
        document.getElementById('imageUpload').addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (event) {
                    document.getElementById('imagePreview').src = event.target.result;
                    document.getElementById('imagePreview').classList.remove('d-none');
                    document.getElementById('previewPlaceholder').classList.add('d-none');
                }
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>

</html>