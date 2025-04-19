<?php
session_start();
if (!isset($_SESSION['photo_data'])) {
    header("Location: dashboard.php"); // Redirect back to dashboard if no photo data
    exit();
}
$photo_data = $_SESSION['photo_data'];
unset($_SESSION['photo_data']); // Clear the session data after use
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Photo Preview</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts: Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;700&display=swap" rel="stylesheet">
</head>
<style>
    body {
      background: linear-gradient(135deg, #f0f4ff, #d0e8ff);
      font-family: 'Segoe UI', sans-serif;
      overflow-x: hidden;
    }
    .navbar-brand {
      font-weight: bold;
      font-size: 1.5rem;
      color: #0056b3 !important;
    }
</style>
<body>
    <!-- Modern Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">📸 PhotoBooth</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link active" href="dashboard.php">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="gallery.php">Gallery</a></li>
      </ul>
      <form action="logout.php" method="POST">
        <button type="submit" class="btn btn-outline-danger">Logout</button>
      </form>
    </div>
  </div>
</nav>

    <div class="container">
        <h2 class="text-center mb-4" style="font-family: 'Poppins', sans-serif;">Photo Preview</h2>
        <div class="text-center">
            <canvas id="previewCanvas" class="border"></canvas>
        </div>
        <div class="text-center mt-4">
            <button id="applyFilter" class="btn btn-primary">Apply Filter</button>
            <button id="addText" class="btn btn-secondary">Add Text</button>
            <form action="upload_photo.php" method="POST" enctype="multipart/form-data" class="mt-4 d-inline" id="saveForm">
                <input type="hidden" name="photo_data" id="photoData" value="<?php echo $photo_data; ?>">
                <button type="submit" class="btn btn-success">Save Photo</button>
            </form>
            <button id="deletePhoto" class="btn btn-danger d-inline">Retake Photo</button>
            <button id="takeAnotherPhoto" class="btn btn-warning d-inline">Take Another Photo</button>
        </div>
    </div>

    <!-- Loading Animation -->
    <div id="saveLoadingAnimation" class="hidden text-center mt-5">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Saving...</span>
        </div>
        <p class="mt-3">Saving your photo...</p>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const photoData = "<?php echo $photo_data; ?>";
        const previewCanvas = document.getElementById('previewCanvas');
        const previewContext = previewCanvas.getContext('2d');
        const img = new Image();
        img.src = photoData;
        img.onload = () => {
            previewCanvas.width = img.width;
            previewCanvas.height = img.height;
            previewContext.drawImage(img, 0, 0);
        };

        // Apply filter functionality
        document.getElementById('applyFilter').addEventListener('click', () => {
            const imageData = previewContext.getImageData(0, 0, previewCanvas.width, previewCanvas.height);
            const data = imageData.data;

            for (let i = 0; i < data.length; i += 4) {
                const avg = (data[i] + data[i + 1] + data[i + 2]) / 3;
                data[i] = avg;
                data[i + 1] = avg;
                data[i + 2] = avg;
            }

            previewContext.putImageData(imageData, 0, 0);
        });

        // Add text functionality
        document.getElementById('addText').addEventListener('click', () => {
            const text = prompt('Enter text to add:');
            if (text) {
                previewContext.font = '30px Arial';
                previewContext.fillStyle = 'white';
                previewContext.fillText(text, 20, 50);
            }
        });

        // Retake photo functionality
        document.getElementById('deletePhoto').addEventListener('click', () => {
            if (confirm('Are you sure you want to retake this photo?')) {
                window.location.href = 'dashboard.php?retake=true';
            }
        });

        // Take another photo functionality
        document.getElementById('takeAnotherPhoto').addEventListener('click', () => {
            window.location.href = 'dashboard.php';
        });

        // Save photo functionality
        document.getElementById('saveForm').addEventListener('submit', async (e) => {
            e.preventDefault();

            const saveLoadingAnimation = document.getElementById('saveLoadingAnimation');
            saveLoadingAnimation.classList.remove('hidden');

            const photoData = document.getElementById('photoData').value;

            try {
                const response = await fetch('upload_photo.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `photo_data=${encodeURIComponent(photoData)}`
                });

                const result = await response.text();
                saveLoadingAnimation.classList.add('hidden');

                if (response.ok) {
                    // alert('Photo successfully saved to the database!');
                    window.location.href = 'gallery.php';
                } else {
                    // alert('Failed to save the photo. Please try again.');
                    console.error('Error:', result);
                }
            } catch (error) {
                saveLoadingAnimation.classList.add('hidden');
                // alert('An error occurred while saving the photo. Please try again.');
                console.error('Error:', error);
            }
        });
    </script>
</body>
</html>