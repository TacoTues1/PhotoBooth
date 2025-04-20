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
    .hidden {
    display: none !important;
    }
    .template {
        width: 100px; /* Set the desired width */
        height: auto; /* Maintain aspect ratio */
    }
    .template.selected {
        border: 3px solid #007bff; /* Blue border for selection */
        border-radius: 5px;
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
        <div class="container mt-4">
            <h3 class="text-center">Select a Template</h3>
            <div class="row">
                <div class="col-md-4">
                    <img src="templates/template1.png" alt="Template 1" class="img-fluid template" data-template="template1.png">
                </div>
                <div class="col-md-4">
                    <img src="templates/template2.png" alt="Template 2" class="img-fluid template" data-template="template2.png">
                </div>
                <div class="col-md-4">
                    <img src="templates/template3.png" alt="Template 3" class="img-fluid template" data-template="template3.png">
                </div>
            </div>
            <button id="applyTemplate" class="btn btn-success mt-3">Apply Selected Template</button>
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
    let selectedTemplate = '';

    document.querySelectorAll('.template').forEach(item => {
        item.addEventListener('click', (e) => {
            // Remove the 'selected' class from all templates
            document.querySelectorAll('.template').forEach(template => {
                template.classList.remove('selected');
            });

            // Add the 'selected' class to the clicked template
            e.target.classList.add('selected');

            // Set the selected template
            selectedTemplate = e.target.getAttribute('data-template');
        });
    });

    document.getElementById('applyTemplate').addEventListener('click', () => {
    if (selectedTemplate) {
        const previewCanvas = document.getElementById('previewCanvas');
        const previewContext = previewCanvas.getContext('2d');
        const templateImage = new Image();
        templateImage.src = selectedTemplate;

        // Load the selected template image
        templateImage.onload = () => {
            // Set canvas size to match the template
            previewCanvas.width = templateImage.width;
            previewCanvas.height = templateImage.height;

            // Draw the template on the canvas
            previewContext.drawImage(templateImage, 0, 0);

            // Draw the user's photo on top of the template
            const img = new Image();
            img.src = photoData; // Assuming photoData contains the photo's data URL
            img.onload = () => {
                // Adjust the user's photo size and position
                const photoWidth = previewCanvas.width * 0.8; // Scale photo to 80% of canvas width
                const photoHeight = (img.height / img.width) * photoWidth; // Maintain aspect ratio
                const photoX = (previewCanvas.width - photoWidth) / 2; // Center the photo horizontally
                const photoY = (previewCanvas.height - photoHeight) / 2; // Center the photo vertically

                previewContext.drawImage(img, photoX, photoY, photoWidth, photoHeight);
            };
        };
    } else {
        alert('Please select a template first!');
    }
});

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

    // Simulate loading for 2 seconds before proceeding
    setTimeout(async () => {
        const photoData = document.getElementById('photoData').value;

        try {
            const response = await fetch('upload_photo.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `photo_data=${encodeURIComponent(photoData)}`
            });

            saveLoadingAnimation.classList.add('hidden');

            if (response.ok) {
                // alert('Photo successfully saved to the database!');
                window.location.href = 'gallery.php';
            } else {
                // alert('Failed to save the photo. Please try again.');
                console.error('Error:', await response.text());
            }
        } catch (error) {
            saveLoadingAnimation.classList.add('hidden');
            // alert('An error occurred while saving the photo. Please try again.');
            console.error('Error:', error);
        }
    }, 2000);
});
    </script>
</body>
</html>