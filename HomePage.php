<?php
include 'auth.php';
$user_id = $_SESSION['user_id'];

$conn = new mysqli('localhost', 'root', '', 'photobooth');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>PhotoBooth Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="styles.css">
  <style>
    h2 {
      animation: fadeInDown 1s ease-in-out;
    }

    .hidden {
      display: none !important;
    }

    .photo-layout img {
      width: 100%;
      border-radius: 12px;
      transition: transform 0.3s ease;
    }

    .photo-layout img:hover {
      transform: scale(1.05);
    }

    .btn {
      transition: transform 0.2s ease-in-out;
    }

    .btn:hover {
      transform: scale(1.05);
    }

    #cameraSection,
    #photoPreview {
      animation: fadeInUp 1s ease-in-out;
    }

    @keyframes fadeInDown {
      from { opacity: 0; transform: translateY(-30px); }
      to { opacity: 1; transform: translateY(0); }
    }

    @keyframes fadeInUp {
      from { opacity: 0; transform: translateY(30px); }
      to { opacity: 1; transform: translateY(0); }
    }

    #timerOverlay {
      font-size: 4rem;
      background: rgba(0, 0, 0, 0.6);
      padding: 10px 20px;
      border-radius: 10px;
      animation: pulse 1s infinite ease-in-out;
    }

    @keyframes pulse {
      0%, 100% { transform: scale(1); }
      50% { transform: scale(1.1); }
    }
    .spinner-border {
      width: 3rem;
      height: 3rem;
    }

    .layout-option {
      border: 2px solid transparent;
      border-radius: 10px;
      transition: border-color 0.3s ease;
    }

    .layout-option.selected {
      border-color: #007bff; /* Highlight selected layout with blue border */
    }

    .layout-option img {
      transition: transform 0.3s ease;
    }

    .layout-option img:hover {
      transform: scale(1.1); /* Slight zoom effect on hover */
    }
  </style>
</head>
<body>

<!-- Navigation -->
<?php include 'Header.php'; ?>

<div class="container mt-5">

  <!-- Layout Selection -->
  <div id="layoutSelection">
    <h2 class="text-center mb-4">✨ Choose Your Layout</h2>
    <div class="d-flex justify-content-center flex-wrap gap-3">
      <?php
        // Load the JSON file
        $jsonFile = __DIR__ . '/layouts.json';
        $layouts = json_decode(file_get_contents($jsonFile), true);

        // Loop through the layouts and display them
        foreach ($layouts as $layout) {
            echo '<div class="layout-option" data-layout="' . $layout['id'] . '" data-type="' . $layout['type'] . '">';
            echo '<img src="' . $layout['image'] . '" alt="' . $layout['name'] . '" class="img-fluid rounded shadow" style="width: 150px; cursor: pointer;">';
            echo '</div>';
        }
      ?>
    </div>
    <div class="text-center mt-4">
      <button id="continueButton" class="btn btn-primary btn-lg shadow" disabled>Continue</button>
    </div>
  </div>

  <!-- Camera Section -->
  <div id="cameraSection" class="hidden mt-5">
    <h2 class="text-center mb-4">📷 Smile for the Camera!</h2>
    <div class="text-center mb-4">
      <label for="timerSelect" class="form-label">Set Timer:</label>
      <select id="timerSelect" class="form-select w-50 mx-auto shadow">
        <option value="0" selected>No Timer</option>
        <option value="3">3 Seconds</option>
        <option value="5">5 Seconds</option>
        <option value="10">10 Seconds</option>
      </select>
    </div>
    <div class="d-flex justify-content-center align-items-start gap-4 flex-wrap">
      <div class="position-relative shadow rounded" style="width: 320px; height: 240px; overflow: hidden;">
        <video id="webcam" autoplay playsinline class="w-100 h-100 rounded"></video>
        <div id="timerOverlay" class="position-absolute top-50 start-50 translate-middle text-white fw-bold" style="display: none;"></div>
      </div>
      <div class="photo-layout border p-3 rounded shadow bg-white" id="photoLayout" style="width: 320px; min-height: 240px;"></div>
    </div>
    <div class="text-center mt-4">
      <button id="capture" class="btn btn-success btn-lg shadow">Capture Photo</button>
    </div>
  </div>

  <!-- Photo Preview Section -->
  <div id="photoPreview" class="hidden mt-5">
    <h2 class="text-center mb-4">🖼️ Photo Preview</h2>
    <div class="text-center">
      <canvas id="previewCanvas" class="border shadow rounded"></canvas>
    </div>
    <div class="text-center mt-4">
      <button id="applyFilter" class="btn btn-primary">Apply Filter</button>
      <button id="addText" class="btn btn-secondary">Add Text</button>
      <form action="upload_photo.php" method="POST" enctype="multipart/form-data" class="mt-4">
        <input type="hidden" name="photo_data" id="photoData">
        <button type="submit" class="btn btn-success">Save Photo</button>
      </form>
    </div>
  </div>

  <!-- Loading Animation -->
  <div id="loadingAnimation" class="hidden text-center mt-5">
    <div class="spinner-border text-primary" role="status">
      <span class="visually-hidden">Loading...</span>
    </div>
    <p class="mt-3 fw-bold">Preparing your photo preview...</p>
  </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
  const layoutSelection = document.getElementById('layoutSelection');
  const cameraSection = document.getElementById('cameraSection');
  const photoPreview = document.getElementById('photoPreview');
  const loadingAnimation = document.getElementById('loadingAnimation');
  const continueButton = document.getElementById('continueButton');
  const layoutSelect = document.getElementById('layoutSelect');
  const timerSelect = document.getElementById('timerSelect');
  const webcam = document.getElementById('webcam');
  const captureButton = document.getElementById('capture');
  const photoLayout = document.getElementById('photoLayout');
  const photoDataInput = document.getElementById('photoData');
  const timerOverlay = document.getElementById('timerOverlay');

  let maxPhotos = 4;

  const urlParams = new URLSearchParams(window.location.search);
  if (urlParams.get('retake') === 'true') {
    layoutSelection.classList.add('hidden');
    cameraSection.classList.remove('hidden');
  }

  continueButton.addEventListener('click', () => {
    maxPhotos = parseInt(layoutSelect.value);
    layoutSelection.classList.add('hidden');
    cameraSection.classList.remove('hidden');
  });

  navigator.mediaDevices.getUserMedia({ video: true })
    .then(stream => {
      webcam.srcObject = stream;
    })
    .catch(err => {
      console.error('Error accessing webcam:', err);
    });

  captureButton.addEventListener('click', () => {
    const timerValue = parseInt(timerSelect.value);
    let photosCaptured = 0;
    captureButton.disabled = true;

    function startCapture() {
      if (photosCaptured < maxPhotos) {
        if (timerValue > 0) {
          let countdown = timerValue;
          timerOverlay.style.display = 'block';
          const countdownInterval = setInterval(() => {
            timerOverlay.textContent = countdown;
            countdown--;
            if (countdown < 0) {
              clearInterval(countdownInterval);
              timerOverlay.style.display = 'none';
              capturePhoto();
              photosCaptured++;
              startCapture();
            }
          }, 1000);
        } else {
          capturePhoto();
          photosCaptured++;
          startCapture();
        }
      } else {
        captureButton.disabled = false;
        generateTemplates(); // Generate templates based on the selected layout type
      }
    }

    startCapture();
  });

  function capturePhoto() {
  if (photoLayout.children.length < maxPhotos) {
    const canvas = document.createElement('canvas');
    canvas.width = webcam.videoWidth;
    canvas.height = webcam.videoHeight;
    const context = canvas.getContext('2d');
    context.drawImage(webcam, 0, 0, canvas.width, canvas.height);

    const img = document.createElement('img');
    img.src = canvas.toDataURL('image/png');
    photoLayout.appendChild(img);

    // Save each photo to the server
    const photoDataURL = canvas.toDataURL('image/png');
    fetch('save_photo.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ photo_data: photoDataURL })
    })
      .then(response => response.json())
      .then(data => {
        console.log('Photo saved:', data);

        // Check if all photos are captured
        if (photoLayout.children.length === maxPhotos) {
          console.log('All photos captured. Redirecting to photo_preview.php...');
          cameraSection.classList.add('hidden');
          loadingAnimation.classList.remove('hidden');

          setTimeout(() => {
            window.location.href = 'photo_preview.php';
          }, 2000);
        }
      })
      .catch(error => {
        console.error('Error saving photo:', error);
      });
  }
}

  document.addEventListener('DOMContentLoaded', () => {
  const layoutOptions = document.querySelectorAll('.layout-option');
  const continueButton = document.getElementById('continueButton');
  const layoutSelection = document.getElementById('layoutSelection');
  const cameraSection = document.getElementById('cameraSection');
  let selectedLayout = null;
  let selectedLayoutType = null;

  layoutOptions.forEach(option => {
    option.addEventListener('click', () => {
      // Remove 'selected' class from all options
      layoutOptions.forEach(opt => opt.classList.remove('selected'));

      // Add 'selected' class to the clicked option
      option.classList.add('selected');

      // Enable the continue button
      continueButton.disabled = false;

      // Store the selected layout and its type
      selectedLayout = option.getAttribute('data-layout');
      selectedLayoutType = option.getAttribute('data-type');
    });
  });

  continueButton.addEventListener('click', () => {
    if (selectedLayout) {
      console.log('Selected Layout:', selectedLayout);
      console.log('Selected Layout Type:', selectedLayoutType);

      // Set the number of takes to 8
      maxPhotos = 8;

      // Hide the layout selection and show the camera section
      layoutSelection.classList.add('hidden');
      cameraSection.classList.remove('hidden');
    }
  });
});
</script>

</body>
</html>