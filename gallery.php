<?php
include 'auth.php'; 

$user_id = $_SESSION['user_id'];

$conn = new mysqli('localhost', 'root', '', 'photobooth');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT id, photo_path, captured_at FROM photos WHERE user_id = $user_id ORDER BY captured_at DESC";
$result = $conn->query($sql);

$photos = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $photos[] = $row;
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Gallery</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="styles.css">
  <style>
    h2 {
      font-weight: 700;
    }

    .gallery-card {
      border: none;
      border-radius: 16px;
      overflow: hidden;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    .gallery-card:hover {
      transform: translateY(-8px);
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
    }

    .album-thumbnail {
      height: 160px;
      object-fit: cover;
      transition: opacity 0.3s ease;
    }

    .select-label-hidden {
      display: none;
    }

    .checkbox-hidden {
      display: none;
    }

    .btn-animated {
      transition: background-color 0.3s ease, transform 0.2s ease;
    }

    .btn-animated:hover {
      transform: scale(1.05);
    }

    .modal-content {
      border-radius: 12px;
    }
    .btn {
      transition: transform 0.2s ease-in-out;
    }

    .btn:hover {
      transform: scale(1.05);
    }
    .modal-photo {
      border-radius: 12px;
      max-height: 500px;
      object-fit: contain;
    }
    .spinner-border {
      width: 3rem;
      height: 3rem;
    }
  </style>
</head>
<body>
<?php include 'Header.php'; ?>
<div class="container mt-5">
  <h2 class="text-center mb-4">Your Captured Moments</h2>

  <form id="deleteForm" method="POST" action="delete_photos.php">
    <div class="text-center mb-4">
      <button type="button" id="toggleDelete" class="btn btn-danger me-2 btn-animated">🗑️ Delete Photos</button>
      <button type="submit" id="confirmDelete" class="btn btn-warning btn-animated checkbox-hidden">Confirm Deletion</button>
    </div>

    <div class="row g-4">
      <?php foreach ($photos as $photo): ?>
        <div class="col-sm-6 col-md-4 col-lg-3">
          <div class="card gallery-card" data-bs-toggle="modal" data-bs-target="#photoModal" onclick="showPhoto('<?php echo $photo['photo_path']; ?>', '<?php echo $photo['captured_at']; ?>')">
            <img src="<?php echo $photo['photo_path']; ?>" class="album-thumbnail w-100" alt="Captured Photo">
            <div class="card-body text-center p-2">
              <p class="text-muted small mb-0">Captured at:</p>
              <p class="text-secondary small"><?php echo $photo['captured_at']; ?></p>
              <label class="select-label-hidden">
                <input type="checkbox" name="photo_ids[]" value="<?php echo $photo['id']; ?>" class="checkbox-hidden"> Select
              </label>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </form>
</div>

<!-- Modal -->
<div class="modal fade" id="photoModal" tabindex="-1" aria-labelledby="photoModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content p-3">
      <div class="modal-header border-0">
        <h5 class="modal-title fw-semibold" id="photoModalLabel">Captured Photo</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center">
        <img id="modalPhoto" src="" class="img-fluid modal-photo mb-3" alt="Full Photo">
        <p class="text-muted small mb-0">Captured at:</p>
        <p class="text-secondary small" id="modalCapturedAt"></p>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
  const toggleDeleteButton = document.getElementById('toggleDelete');
  const confirmDeleteButton = document.getElementById('confirmDelete');
  const checkboxes = document.querySelectorAll('input[type="checkbox"]');
  const labels = document.querySelectorAll('.select-label-hidden');

  toggleDeleteButton.addEventListener('click', () => {
    checkboxes.forEach(c => c.classList.toggle('checkbox-hidden'));
    labels.forEach(l => l.classList.toggle('select-label-hidden'));
    confirmDeleteButton.classList.toggle('checkbox-hidden');
    toggleDeleteButton.textContent =
      toggleDeleteButton.textContent.includes('Delete') ? '❌ Cancel' : '🗑️ Delete Photos';
  });

  function showPhoto(photoPath, capturedAt) {
    document.getElementById('modalPhoto').src = photoPath;
    document.getElementById('modalCapturedAt').textContent = capturedAt;
  }
</script>
</body>
</html>