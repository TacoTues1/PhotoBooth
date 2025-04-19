<?php
session_start();
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
  <style>
    body {
      background: linear-gradient(135deg, #f0f4ff, #d0e8ff);
      font-family: 'Segoe UI', sans-serif;
      overflow-x: hidden;
    }

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

    .navbar-brand {
      font-weight: bold;
      font-size: 1.5rem;
      color: #0056b3 !important;
    }

    .spinner-border {
      width: 3rem;
      height: 3rem;
    }
  </style>
</head>
<body>

<!-- Navigation -->
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

<div class="container mt-5">

  <!-- Layout Selection -->
  <div id="layoutSelection">
    <h2 class="text-center mb-4">✨ Choose Your Layout</h2>
    <div class="text-center mb-4">
      <label for="layoutSelect" class="form-label">Select Layout:</label>
      <select id="layoutSelect" class="form-select w-50 mx-auto shadow">
        <option value="1">1 Photo</option>
        <option value="2">2 Photos</option>
        <option value="3">3 Photos</option>
        <option value="4" selected>4 Photos</option>
      </select>
    </div>
    <div class="text-center">
      <button id="continueButton" class="btn btn-primary btn-lg shadow">Continue</button>
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

      if (photoLayout.children.length === maxPhotos) {
        const combinedCanvas = document.createElement('canvas');
        combinedCanvas.width = webcam.videoWidth;
        combinedCanvas.height = webcam.videoHeight * maxPhotos;
        const combinedContext = combinedCanvas.getContext('2d');

        const images = Array.from(photoLayout.children);
        let loadedCount = 0;

        images.forEach((child, index) => {
          const photo = new Image();
          photo.src = child.src;
          photo.onload = () => {
            combinedContext.drawImage(photo, 0, index * webcam.videoHeight, webcam.videoWidth, webcam.videoHeight);
            loadedCount++;
            if (loadedCount === maxPhotos) {
              cameraSection.classList.add('hidden');
              loadingAnimation.classList.remove('hidden');

              const photoDataURL = combinedCanvas.toDataURL('image/png');
              photoDataInput.value = photoDataURL;

              setTimeout(() => {
                fetch('save_photo_data.php', {
                  method: 'POST',
                  headers: { 'Content-Type': 'application/json' },
                  body: JSON.stringify({ photo_data: photoDataURL })
                }).then(() => {
                  window.location.href = 'photo_preview.php';
                });
              }, 2000);
            }
          };
        });
      }
    }
  }
</script>
</body>
</html>