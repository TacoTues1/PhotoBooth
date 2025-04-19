<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Register</title>

  <!-- Google Font -->
  <link href="https://fonts.googleapis.com/css2?family=Montserrat&display=swap" rel="stylesheet">

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Custom Styles -->
  <style>
    * {
      font-family: 'Montserrat', sans-serif;
    }

    body {
      background: linear-gradient(to right, #6a11cb, #2575fc);
      min-height: 100vh;
      margin: 0;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .form-container {
      background-color: white;
      padding: 30px;
      border-radius: 15px;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
      max-width: 400px;
      width: 100%;
    }

    button {
      font-size: small;
      padding: 10px;
      width: 100%;
      color: white;
      background-color: #6496c8;
      border: none;
      border-radius: 4px;
      box-shadow: 1px 5px #27496d;
      transition: all 0.2s ease-in-out;
    }

    button:hover {
      background-color: #417cb8;
    }

    button:active {
      background-color: #417cb8;
      box-shadow: 0 3px #27496d;
      transform: translateY(2px) translateX(1px);
    }

    button:disabled {
      background-color: darkgrey;
      box-shadow: 0 3px #27496d;
      transform: translateY(2px) translateX(1px);
    }
  </style>
</head>
<body>
  <div class="form-container">
    <h2 class="text-center mb-4">Register</h2>
    <form action="register_process.php" method="POST">
      <div class="mb-3">
        <label for="name" class="form-label">Name:</label>
        <input type="text" name="name" class="form-control" required>
      </div>
      <div class="mb-3">
        <label for="age" class="form-label">Age:</label>
        <input type="number" name="age" class="form-control" required>
      </div>
      <div class="mb-3">
        <label for="gender" class="form-label">Gender:</label>
        <select name="gender" class="form-select" required>
          <option value="Male">Male</option>
          <option value="Female">Female</option>
          <option value="Other">Other</option>
        </select>
      </div>
      <div class="mb-3">
        <label for="email" class="form-label">Email:</label>
        <input type="email" name="email" class="form-control" required>
      </div>
      <div class="mb-3">
        <label for="password" class="form-label">Password:</label>
        <input type="password" name="password" class="form-control" required>
      </div>
      <button type="submit">Register</button>
    </form>
    <p class="text-center mt-3 text-muted">
      Already have an account? <a href="login.php" class="text-primary">Login here</a>
    </p>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
