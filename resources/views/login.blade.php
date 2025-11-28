<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Login</title>

    <!-- Fonts & icons -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>
<body>
<!-- Background tetap -->
    <div id="background-fixed"></div>

    <!-- Slideshow foto orang -->
    <div class="photo-wrapper">
        <img src="{{ asset('download/foto1.png') }}" class="active" alt="foto1">
        <img src="{{ asset('download/foto2.png') }}" alt="foto2">
        <img src="{{ asset('download/foto3.png') }}" alt="foto3">
    </div>

  <div class="container">
      <!-- LEFT: Judul + ilustrasi -->
      <div class="left">
          <h1 class="hero-title">WELCOME TO THE WEBSITE</h1>
      </div>

      <!-- RIGHT: Login card -->
      <div class="right">
          <div class="login-card" role="form" aria-labelledby="signin-heading">
              <h2 id="signin-heading" style="display:none">Sign in</h2>

              @if ($errors->any())
                  <div class="error-message">
                      {{ $errors->first() }}
                  </div>
              @endif

              <form action="{{ route('login') }}" method="POST">
                  @csrf
                  <div class="input-group">
                      <label class="label" for="username">Username</label>
                      <div style="height:6px;"></div>
                      <div style="position:relative;">
                          <span class="input-icon"><i class="fas fa-user"></i></span>
                          <input class="input-field" type="text" name="username" id="username" placeholder="Username" value="{{ old('username') }}" required>
                      </div>
                  </div>

                  <div class="input-group">
                      <label class="label" for="password">Password</label>
                      <div style="height:6px;"></div>
                      <div style="position:relative;">
                          <span class="input-icon"><i class="fas fa-key"></i></span>
                          <input class="input-field" type="password" name="password" id="password" placeholder="Password" required>
                          <button type="button" class="toggle-password" onclick="togglePassword()" aria-label="toggle password">
                              <i class="fas fa-eye" id="eyeIcon"></i>
                          </button>
                      </div>
                  </div>

                  <button class="btn" type="submit">Sign in</button>
              </form>
          </div>
      </div>
  </div>
  
<script>
 // Ganti foto orang tiap 4 detik
        const photos = document.querySelectorAll(".photo-wrapper img");
        let index = 0;

        setInterval(() => {
            photos[index].classList.remove("active");
            index = (index + 1) % photos.length;
            photos[index].classList.add("active");
        }, 5000);
        function togglePassword() {
    const passwordInput = document.getElementById('password');
    const eyeIcon = document.getElementById('eyeIcon');

    if (passwordInput.type === 'password') {
        passwordInput.type = 'text'; // tampilkan password
        eyeIcon.classList.remove('fa-eye');
        eyeIcon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password'; // sembunyikan password
        eyeIcon.classList.remove('fa-eye-slash');
        eyeIcon.classList.add('fa-eye');
    }
}
</script>

</body>
</html>
