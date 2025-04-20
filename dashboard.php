<?php
include 'auth.php'; // Include authentication check
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
  <title>PhotoBooth Templates</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&family=Dancing+Script:wght@700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="styles.css">
  <style>
    /* Sliding Templates Container */
    .template-train-container {
    position: relative;
    width: 100%;
    overflow: hidden; /* Hide templates outside the container */
    height: 300px; /* Set a fixed height for the train */
    margin-bottom: 3rem; 
    }

    .template-train {
      display: flex;
      animation: slideTemplates 20s linear infinite; /* Slower slide animation */
      width: calc(200%); /* Double the width to fit the duplicated templates */
    }

    .template-train img {
      max-width: 300px;
      max-height: 300px;
      margin: 0 40px; /* Add spacing between templates */
      border-radius: 10px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }

    /* Sliding Animation */
    @keyframes slideTemplates {
      0% {
        transform: translateX(0); /* Start at the initial position */
      }
      100% {
        transform: translateX(-50%); /* Move left by half the width (one set of templates) */
      }
    }

    /* START Button */
    .btn {
      transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }

    .btn:hover {
      transform: translateY(-3px);
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }
    /* Welcome Text Styling */
    .welcome-text {
      margin-top: 4%;
      animation: bounce 2s infinite;
    }

    .welcome-text h1 {
      font-family: 'Dancing Script', cursive; /* Use Dancing Script for the Welcome text */
      font-size: 4rem; /* Adjust font size for a bold and fancy look */
      color: #0056b3; /* Blue color for the text */
    }

    @keyframes bounce {
      0%, 20%, 50%, 80%, 100% {
        transform: translateY(0);
      }
      40% {
        transform: translateY(-30px);
      }
      60% {
        transform: translateY(-15px);
      }
    }
  </style>
</head>
<body>

<!-- Navigation -->
<?php include 'Header.php'; ?>

<!-- Welcome Text -->
<div class="welcome-text text-center">
  <h1 class="display-1">Welcome</h1>
</div>

<!-- Sliding Templates -->
<div id="templateTrain" class="template-train-container text-center mt-5">
  <div class="template-train">
    <!-- Templates will be dynamically added here -->
  </div>
</div>

<!-- START Button -->
<div class="text-center mt-4">
  <a href="HomePage.php" class="btn btn-primary btn-lg">START</a>
</div>
<?php include 'footer.php'; ?>
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const templateTrain = document.querySelector('.template-train');

    // Dynamically load templates from the templates folder
    const templates = <?php
      $templateFiles = array();
      $templateDir = __DIR__ . '/templates';
      if (is_dir($templateDir)) {
          foreach (scandir($templateDir) as $file) {
              if (in_array(pathinfo($file, PATHINFO_EXTENSION), ['png', 'jpg', 'jpeg', 'gif'])) {
                  $templateFiles[] = 'templates/' . $file;
              }
          }
      }
      echo json_encode($templateFiles);
    ?>;

    // Add templates to the train
    templates.forEach((template) => {
      const img = document.createElement('img');
      img.src = template;
      img.alt = 'Template';
      img.classList.add('img-fluid');
      templateTrain.appendChild(img);
    });

    // Duplicate templates for seamless sliding
    templates.forEach((template) => {
      const img = document.createElement('img');
      img.src = template;
      img.alt = 'Template';
      img.classList.add('img-fluid');
      templateTrain.appendChild(img);
    });
  });
</script>
</body>
</html>