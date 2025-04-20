<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
  <div class="container-fluid">
    <a class="navbar-brand" href="dashboard.php">📸 PhotoBooth</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link active" href="dashboard.php">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="gallery.php">Gallery</a></li>
      </ul>
      <form action="logout.php" method="POST" class="text-center">
        <button type="submit" class="btn btn-outline-danger">Logout</button>
      </form>
    </div>
  </div>
</nav>