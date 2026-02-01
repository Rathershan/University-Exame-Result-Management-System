<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>GWUIM Examination Management System</title>
  <link rel="icon" type="image/png" href="logo.png">

  <!-- Bootstrap CSS CDN -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet" />
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <!-- Animate.css CDN -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />

  <style>
    :root {
      --primary-color: #00695c;
      --secondary-color: #2e7d32;
      --accent-color: #ff8f00;
      --light-color: #f8f9fa;
      --dark-color: #212529;
    }

    body {
      padding-top: 70px;
      font-family: 'Poppins', sans-serif;
      background-color: #f4f4f4;
      color: #333;
    }

    /* Navbar Styles */
    .navbar {
      background: linear-gradient(135deg, var(--dark-color), #343a40) !important;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .navbar-brand {
      font-weight: 700;
      font-size: 1.5rem;
      display: flex;
      align-items: center;
    }

    .navbar-brand img {
      height: 40px;
      margin-right: 10px;
    }

    .nav-link {
      font-weight: 500;
      padding: 0.5rem 1rem;
      margin: 0 0.2rem;
      transition: all 0.3s ease;
    }

    .nav-link:hover {
      background-color: var(--primary-color);
      border-radius: 5px;
      color: white !important;
      transform: translateY(-2px);
    }

    .dropdown-menu {
      border: none;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .dropdown-item:hover {
      background-color: var(--primary-color);
      color: white;
    }

    /* Hero Section */
    .hero {
      background:url('ai.jpg') no-repeat center center/cover;
      height: 80vh;
      min-height: 500px;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      text-align: center;
      color: white;
      position: relative;
      overflow: hidden;
    }

    .hero::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.5);
    }

    .hero-content {
      position: relative;
      z-index: 1;
      max-width: 800px;
      padding: 0 20px;
    }

    .hero h1 {
      font-weight: 700;
      font-size: 2.8rem;
      margin-bottom: 1.5rem;
      line-height: 1.3;
      text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
    }

    .hero h2 {
      font-weight: 400;
      font-size: 1.8rem;
      margin-bottom: 2rem;
      text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.5);
    }

    .hero-btn {
      padding: 12px 30px;
      font-size: 1.1rem;
      font-weight: 600;
      border-radius: 50px;
      transition: all 0.3s ease;
      margin: 0 10px;
    }

    .btn-primary {
      background-color: var(--primary-color);
      border-color: var(--primary-color);
    }

    .btn-outline-light:hover {
      background-color: var(--primary-color);
      border-color: var(--primary-color);
    }

    /* Features Section */
    .section-title {
      font-weight: 700;
      color: var(--primary-color);
      margin-bottom: 3rem;
      position: relative;
      display: inline-block;
    }

    .section-title::after {
      content: '';
      position: absolute;
      bottom: -10px;
      left: 50%;
      transform: translateX(-50%);
      width: 80px;
      height: 3px;
      background-color: var(--accent-color);
    }

    .feature-card {
      background: white;
      border-radius: 15px;
      padding: 30px 20px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
      transition: all 0.3s ease;
      height: 100%;
      border: none;
    }

    .feature-card:hover {
      transform: translateY(-10px);
      box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
    }

    .feature-img {
      width: 120px;
      height: 120px;
      object-fit: cover;
      border-radius: 50%;
      margin-bottom: 1.5rem;
      border: 5px solid var(--light-color);
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .feature-title {
      font-weight: 600;
      color: var(--primary-color);
      margin-bottom: 1rem;
    }

    /* Departments Section */
    .department-card {
      position: relative;
      overflow: hidden;
      border-radius: 15px;
      height: 250px;
      margin-bottom: 30px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
      transition: all 0.5s ease;
    }

    .department-card:hover {
      transform: scale(1.03);
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
    }

    .department-img {
      height: 100%;
      width: 100%;
      object-fit: cover;
      transition: transform 0.5s ease;
    }

    .department-card:hover .department-img {
      transform: scale(1.1);
    }

    .department-overlay {
      position: absolute;
      bottom: 0;
      left: 0;
      right: 0;
      background: linear-gradient(to top, rgba(0, 0, 0, 0.8), transparent);
      color: white;
      padding: 20px;
    }

    .department-title {
      font-weight: 600;
      margin-bottom: 0.5rem;
    }

    /* Footer */
    footer {
      background: linear-gradient(135deg, var(--dark-color), #343a40);
      color: white;
      padding: 40px 0 20px;
      margin-top: 80px;
    }

    .footer-logo {
      font-size: 1.8rem;
      font-weight: 700;
      margin-bottom: 1.5rem;
    }

    .footer-links h5 {
      font-weight: 600;
      margin-bottom: 1.5rem;
      position: relative;
    }

    .footer-links h5::after {
      content: '';
      position: absolute;
      bottom: -8px;
      left: 0;
      width: 40px;
      height: 2px;
      background-color: var(--accent-color);
    }

    .footer-links ul {
      list-style: none;
      padding-left: 0;
    }

    .footer-links li {
      margin-bottom: 0.8rem;
    }

    .footer-links a {
      color: #ddd;
      text-decoration: none;
      transition: all 0.3s ease;
    }

    .footer-links a:hover {
      color: white;
      padding-left: 5px;
    }

    .social-icons a {
      display: inline-block;
      width: 40px;
      height: 40px;
      background-color: rgba(255, 255, 255, 0.1);
      border-radius: 50%;
      text-align: center;
      line-height: 40px;
      color: white;
      margin-right: 10px;
      transition: all 0.3s ease;
    }

    .social-icons a:hover {
      background-color: var(--accent-color);
      transform: translateY(-5px);
    }

    .copyright {
      border-top: 1px solid rgba(255, 255, 255, 0.1);
      padding-top: 20px;
      margin-top: 30px;
    }

    /* Responsive Adjustments */
    @media (max-width: 768px) {
      .hero h1 {
        font-size: 2rem;
      }
      
      .hero h2 {
        font-size: 1.3rem;
      }
      
      .hero-btn {
        display: block;
        width: 80%;
        margin: 10px auto;
      }
    }
  </style>
</head>

<body>
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
    <div class="container">
      <a class="navbar-brand" href="#">
        <img src="gwuim.png" alt="GWUIM Logo">
        GWUIM
      </a>

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
        aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link active" href="home.php">Home</a></li>
          <li class="nav-item"><a class="nav-link" href="">About</a></li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              Login
            </a>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="student_login.php"><i class="fas fa-user-graduate me-2"></i>Student Login</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="login.php"><i class="fas fa-user-shield me-2"></i>Admin Login</a></li>
            </ul>
          </li>
          <li class="nav-item"><a class="nav-link" href="">Contact Us</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Hero Section -->
  <section class="hero">
    <div class="hero-content">
      <h1 class="animate__animated animate__fadeInDown">
        Welcome to the GWUIM Examination Management System
      </h1>
      <!--<h2 class="animate__animated animate__fadeIn animate__delay-1s">
        Excellence in Indigenous Medical Education
      </h2> -->
      <div class="mt-4 animate__animated animate__fadeIn animate__delay-2s">
        <a href="#departments" class="btn btn-primary hero-btn me-2">
          <i class="fas fa-university me-2"></i>Our Departments
        </a>
        <a href="student_login.php" class="btn btn-outline-light hero-btn">
          <i class="fas fa-sign-in-alt me-2"></i>Student Portal
        </a>
      </div>
    </div>
  </section>

  <!-- Features Section 
  <section class="container my-5 py-5">
    <div class="text-center mb-5">
      <h2 class="section-title animate__animated animate__fadeIn">
        Examination Management System
      </h2>
      <p class="lead">Streamlining academic processes for better education outcomes</p>
    </div>
    
    <div class="row g-4">
      <div class="col-md-4 animate__animated animate__fadeInUp">
        <div class="feature-card text-center">
          <img src="online-exam.png" alt="Online Exams" class="feature-img">
          <h3 class="feature-title">Online Examination</h3>
          <p>Secure and efficient digital examination platform with real-time monitoring</p>
        </div>
      </div>
      
      <div class="col-md-4 animate__animated animate__fadeInUp animate__delay-1s">
        <div class="feature-card text-center">
          <img src="results.png" alt="Result Management" class="feature-img">
          <h3 class="feature-title">Result Management</h3>
          <p>Comprehensive result processing with analytics and reporting tools</p>
        </div>
      </div>
      
      <div class="col-md-4 animate__animated animate__fadeInUp animate__delay-2s">
        <div class="feature-card text-center">
          <img src="resources.png" alt="Learning Resources" class="feature-img">
          <h3 class="feature-title">Learning Resources</h3>
          <p>Access to study materials, past papers, and academic resources</p>
        </div>
      </div>
    </div>
  </section>
  

    <--! Departments Section -->
  <section id="departments" class="container-fluid py-5 bg-light">
    <div class="container">
      <div class="text-center mb-5">
        <h2 class="section-title animate__animated animate__fadeIn">
          Our Departments
        </h2>
        
      </div>
      
      <div class="row g-4">
        <div class="col-md-4 animate__animated animate__fadeInLeft">
          <div class="department-card">
            <img src="dd.jpg" alt="Indigenous Health Sciences" class="department-img">
            <div class="department-overlay">
              <h3 class="department-title">Department of Indigenous Health Sciences</h3>
              <a href="" class="btn btn-sm btn-outline-light">Explore</a>
            </div>
          </div>
        </div>
        
        <div class="col-md-4 animate__animated animate__fadeInUp">
          <div class="department-card">
            <img src="Tech.webp" alt="Technology" class="department-img">
            <div class="department-overlay">
              <h3 class="department-title">Department of Technology</h3>
              <a href="" class="btn btn-sm btn-outline-light">Explore</a>
            </div>
          </div>
        </div>
        
        <div class="col-md-4 animate__animated animate__fadeInRight">
          <div class="department-card">
            <img src="mr.jpeg" alt="Medical Resources" class="department-img">
            <div class="department-overlay">
              <h3 class="department-title">Department of Indigenous Medical Resources</h3>
              <a href="" class="btn btn-sm btn-outline-light">Explore</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Call to Action -->
  <section class="container my-5 py-5 text-center animate__animated animate__fadeIn">
    <h2 class="mb-4">Ready to Access Your Examination Portal?</h2>
    <div class="d-flex justify-content-center gap-3">
      <a href="student_login.php" class="btn btn-primary btn-lg px-4">
        <i class="fas fa-user-graduate me-2"></i>Student Login
      </a><!--
      <a href="login.php" class="btn btn-outline-primary btn-lg px-4">
        <i class="fas fa-user-shield me-2"></i>Admin Login
      </a>-->
    </div>
  </section>

  <!-- Footer -->
  <footer>
    <div class="container">
      <div class="row">
        <div class="col-md-4 mb-4">
          <div class="footer-logo">GWUIM</div>
          <p>Gampaha Wickramarachchi University of Indigenous Medicine is committed to excellence in indigenous medical education and research.</p>
          <div class="social-icons mt-3">
            <a href="#"><i class="fab fa-facebook-f"></i></a>
            <a href="#"><i class="fab fa-twitter"></i></a>
            <a href="#"><i class="fab fa-linkedin-in"></i></a>
            <a href="#"><i class="fab fa-youtube"></i></a>
          </div>
        </div>
        
        <div class="col-md-2 mb-4">
          <div class="footer-links">
            <h5>Quick Links</h5>
            <ul>
              <li><a href="home.php">Home</a></li>
              <li><a href="">About Us</a></li>
              <li><a href="#departments">Departments</a></li>
              <li><a href="">Contact</a></li>
            </ul>
          </div>
        </div>
        
        <div class="col-md-3 mb-4">
          <div class="footer-links">
            <h5>Academic</h5>
            <ul>
              <li><a href="student_login.php">Student Portal</a></li>
              <li><a href="">Exam Schedule</a></li>
              <li><a href="">Results</a></li>
              <li><a href="">Resources</a></li>
            </ul>
          </div>
        </div>
        
        <div class="col-md-3 mb-4">
          <div class="footer-links">
            <h5>Contact Info</h5>
            <ul class="contact-info">
              <li><i class="fas fa-map-marker-alt me-2"></i> Kandy Road, Yakkala, Sri Lanka</li>
              <li><i class="fas fa-phone me-2"></i> +94 33 222 2681</li>
              <li><i class="fas fa-envelope me-2"></i> info@gwuim.ac.lk</li>
            </ul>
          </div>
        </div>
      </div>
      
      <div class="copyright text-center">
        <p class="mb-0">&copy; 2025 Gampaha Wickramarachchi University of Indigenous Medicine. All Rights Reserved.</p>
      </div>
    </div>
  </footer>

  <!-- Bootstrap JS CDN -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  
  <script>
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
      anchor.addEventListener('click', function (e) {
        e.preventDefault();
        
        document.querySelector(this.getAttribute('href')).scrollIntoView({
          behavior: 'smooth'
        });
      });
    });
    
    // Add shadow to navbar on scroll
    window.addEventListener('scroll', function() {
      const navbar = document.querySelector('.navbar');
      if (window.scrollY > 50) {
        navbar.style.boxShadow = '0 2px 10px rgba(0, 0, 0, 0.1)';
      } else {
        navbar.style.boxShadow = 'none';
      }
    });
  </script>
</body>
</html>