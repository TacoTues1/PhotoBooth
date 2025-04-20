<?php
include 'auth.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login if the user is not logged in
    exit();
}

// Database connection
$conn = new mysqli('localhost', 'root', '', 'photobooth');
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Fetch all photos for the logged-in user
$userId = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT photo_path FROM photos WHERE user_id = ? ORDER BY captured_at DESC");
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();

$photos = [];
while ($row = $result->fetch_assoc()) {
    $photos[] = $row['photo_path'];
}

$stmt->close();
$conn->close();

if (empty($photos)) {
    // Redirect to the dashboard if no photos are found
    header("Location: dashboard.php");
    exit();
}

$photoPath = $photos[0]; // Use the latest photo
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Photo Preview</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <style>
        .hidden {
            display: none !important;
        }
        .template {
        width: 150px; /* Smaller width for templates */
        height: auto; /* Maintain aspect ratio */
        cursor: pointer;
        margin: 0 auto; /* Center the template */
        display: block; /* Ensure it appears as a block element */
        }   
        .template.selected {
            border: 3px solid #007bff; /* Blue border for selection */
            border-radius: 5px;
        }
        .preview-container {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
        }
        .templates-container,
        .photo-preview-container {
            flex: 1;
            max-width: 50%; /* Ensure both columns take up equal space */
        }
        canvas {
            border: 1px solid #ccc;
            border-radius: 5px;
            width: 100%; /* Ensure the canvas fits within its container */
            height: auto; /* Maintain aspect ratio */
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <?php include 'Header.php'; ?>

    <div class="container mt-4">
        <h2 class="text-center mb-4">Photo Preview</h2>
        <div class="preview-container">
            <!-- Photo Preview Section -->
            <div class="photo-preview-container">
                <canvas id="previewCanvas"></canvas>
                <div class="text-center mt-4">
                    <button id="applyFilter" class="btn btn-primary">Apply Filter</button>
                    <button id="addText" class="btn btn-secondary">Add Text</button>
                    <form action="upload_photo.php" method="POST" enctype="multipart/form-data" class="mt-4 d-inline" id="saveForm">
                        <input type="hidden" name="photo_path" id="photoPath" value="<?php echo htmlspecialchars($photoPath); ?>">
                        <button type="submit" class="btn btn-success">Save Photo</button>
                    </form>
                    <button id="deletePhoto" class="btn btn-danger d-inline">Retake Photo</button>
                    <button id="takeAnotherPhoto" class="btn btn-warning d-inline">Take Another Photo</button>
                </div>
            </div>

            <!-- Templates Section -->
            <div class="templates-container">
                <h4 class="text-center">Select a Template</h4>
                <div id="templateList" class="text-center"></div>
                <button id="applyTemplate" class="btn btn-success w-100 mt-3">Apply Selected Template</button>
            </div>
        </div>
    </div>

    <!-- Loading Animation -->
    <div id="saveLoadingAnimation" class="hidden text-center mt-5">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Saving...</span>
        </div>
        <p class="mt-3">Saving your photo...</p>
    </div>
    <?php include 'footer.php'; ?>
    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const sessionLayoutType = "<?php echo isset($_SESSION['layout_type']) ? $_SESSION['layout_type'] : 'all'; ?>";
        let selectedTemplate = '';

        const userPhotos = <?php echo json_encode($photos); ?>;

        // Fetch templates from the JSON file
        fetch('templates.json')
            .then(response => response.json())
            .then(templates => {
                const templateContainer = document.getElementById('templateList');
                const prevButton = document.createElement('button');
                const nextButton = document.createElement('button');
                let currentIndex = 0;

                // Create navigation buttons
                prevButton.textContent = 'Previous';
                nextButton.textContent = 'Next';
                prevButton.classList.add('btn', 'btn-secondary', 'me-2');
                nextButton.classList.add('btn', 'btn-secondary');

                // Function to render the current template
                const renderTemplate = () => {
                    templateContainer.innerHTML = ''; // Clear existing template
                    const template = templates[currentIndex];

                    // Check if the template matches the session layout type
                    if (sessionLayoutType === 'all' || template.type === sessionLayoutType) {
                        const templateDiv = document.createElement('div');
                        templateDiv.classList.add('template-container');
                        templateDiv.innerHTML = `
                            <img src="${template.path}" alt="Template" class="template" data-template="${template.path}" data-type="${template.type}">
                        `;
                        templateContainer.appendChild(templateDiv);

                        // Attach click event to select the template
                        const templateImg = templateDiv.querySelector('.template');
                        templateImg.addEventListener('click', () => {
                            document.querySelectorAll('.template').forEach(t => t.classList.remove('selected'));
                            templateImg.classList.add('selected');
                            selectedTemplate = templateImg.getAttribute('data-template');
                        });
                    }
                };

                // Handle navigation
                prevButton.addEventListener('click', () => {
                    currentIndex = (currentIndex - 1 + templates.length) % templates.length;
                    renderTemplate();
                });

                nextButton.addEventListener('click', () => {
                    currentIndex = (currentIndex + 1) % templates.length;
                    renderTemplate();
                });

                // Add navigation buttons to the DOM
                const navigationContainer = document.createElement('div');
                navigationContainer.classList.add('text-center', 'mt-3');
                navigationContainer.appendChild(prevButton);
                navigationContainer.appendChild(nextButton);
                templateContainer.parentElement.appendChild(navigationContainer);

                // Initial render
                renderTemplate();
            })
            .catch(error => console.error('Error loading templates:', error));

        // Apply the selected template
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
                    img.src = "<?php echo $photoPath; ?>";
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

        // Load the photo into the canvas
        const photoPath = "<?php echo $photoPath; ?>";
        const previewCanvas = document.getElementById('previewCanvas');
        const previewContext = previewCanvas.getContext('2d');
        const img = new Image();
        img.src = photoPath;
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
                const photoPath = document.getElementById('photoPath').value;

                try {
                    const response = await fetch('upload_photo.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `photo_path=${encodeURIComponent(photoPath)}`
                    });

                    saveLoadingAnimation.classList.add('hidden');

                    if (response.ok) {
                        window.location.href = 'gallery.php';
                    } else {
                        console.error('Error:', await response.text());
                    }
                } catch (error) {
                    saveLoadingAnimation.classList.add('hidden');
                    console.error('Error:', error);
                }
            }, 2000);
        });
    </script>
</body>
</html>