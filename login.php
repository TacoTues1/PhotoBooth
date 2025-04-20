<?php
session_start();
if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login</title>

  <!-- Google Font -->
  <link href="https://fonts.googleapis.com/css2?family=Montserrat&display=swap" rel="stylesheet">

  <!-- Bootstrap (optional, used for layout only) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    button {
      font-family: 'Montserrat', sans-serif;
      font-size: small;
    }
    body{
        background: linear-gradient(to right, #6a11cb, #2575fc);
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    margin: 0;
    }
    button {
      padding: 10px;
      margin-top: 10px;
      width: 100%;
      color: white;
      background-color: #6496c8;
      border: none;
      border-radius: 4px;
      box-shadow: 1px 5px #27496d;
      transition: all 0.2s ease-in-out;
    }

    button:hover,
    button.hover {
      background-color: #417cb8;
    }

    button:active,
    button.active {
      background-color: #417cb8;
      box-shadow: 0 3px #27496d;
      transform: translateY(2px) translateX(1px);
    }

    button:disabled,
    button.disabled {
      background-color: darkgrey;
      box-shadow: 0 3px #27496d;
      transform: translateY(2px) translateX(1px);
    }

    .container {
      max-width: 400px;
      margin-top: 50px;
    }
.form-wrapper {
  background-color: white;
  padding: 30px;
  border-radius: 10px;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
}

  </style>
</head>
<body>
<div class="container">
  <div class="form-wrapper">
    <h2 class="text-center mb-4">Login</h2>
    <form action="login_process.php" method="POST">
      <div class="mb-3">
        <label for="email" class="form-label">Email:</label>
        <input type="email" name="email" class="form-control" required>
      </div>
      <div class="mb-3">
        <label for="password" class="form-label">Password:</label>
        <input type="password" name="password" class="form-control" required>
      </div>
      <button type="submit">Login</button>
    </form>
    <p class="text-center mt-3 text-muted">
      Don't have an account? <a href="register.php" class="text-primary">Register here</a>
    </p>
  </div>
</div>

  <!-- Optional: Bootstrap JS (not required for buttons) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
