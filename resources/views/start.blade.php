<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Kiosk Instaprint</title>
  <meta name="viewport" content="width=device-width, height=600, initial-scale=1.0" />

  <style>
    html, body {
      width: 100%;
      height: 100%;
      margin: 0;
      padding: 0;
      overflow: hidden;
      background-color: #1f2937; /* gray-800 */
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .main-container {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      width: 100vw;
      height: 100vh;
      background-color: #374151; /* gray-700 */
      text-align: center;
    }

    /* ✅ START BUTTON — Large, Touch-Friendly */
    .btn-start {
      background: linear-gradient(145deg, #06b6d4, #0891b2);
      color: white;
      font-weight: 900;
      border: none;
      border-radius: 5vh;
      box-shadow: 0 0 3vw rgba(6,182,212,0.6);
      padding: 14vh 22vw;
      font-size: 14vh;
      transition: all 0.3s ease-in-out;
      cursor: pointer;
      user-select: none;
    }

    .btn-start:hover,
    .btn-start:focus,
    .btn-start:active {
      background: linear-gradient(145deg, #dc2626, #b91c1c);
      box-shadow: 0 0 5vw rgba(220,38,38,0.9);
      transform: scale(1.08);
      outline: none;
    }

    /* ✅ Smaller Logo + Tight spacing */
    .logo-section {
      display: flex;
      flex-direction: column;
      align-items: center;
      margin-top: -5vh;
      gap: 1vh; /* reduced space between img & text */
    }

    .instaprint-logo {
      width: clamp(15vw, 20vw, 25vw);
      height: auto;
      filter: drop-shadow(0 0 1.5vw rgba(6,182,212,0.6));
      transition: transform 0.3s ease-in-out;
      user-select: none;
      -webkit-user-drag: none;
    }

    .instaprint-logo:hover {
      transform: scale(1.05);
    }

    .title {
      color: #22d3ee;
      font-weight: 900;
      text-shadow: 0.3vw 0.3vw 0.5vw rgba(0, 0, 0, 0.5);
      font-size: 8vh;
      margin: 0; /* removed default h1 spacing */
      line-height: 1;
    }

    .subtitle {
      color: #9ca3af;
      font-size: 4vh;
      margin: 0;
      line-height: 1.2;
    }

    @media (max-width: 1024px) and (max-height: 600px) {
      .btn-start {
        font-size: 12vh;
        padding: 12vh 20vw;
      }
      .title {
        font-size: 7vh;
      }
      .subtitle {
        font-size: 3.5vh;
      }
    }
  </style>
</head>

<body>
  <div class="main-container">
    <!-- Big START button -->
    <a href="{{ route('options') }}">
      <button class="btn-start" type="button">
        START
      </button>
    </a>

    <!-- Compact Logo Section -->
    <div class="logo-section">
      <img src="/icons/instaprint1.png" alt="Instaprint Logo" class="instaprint-logo" />
      <h1 class="title">INSTAPRINT</h1>
      <p class="subtitle">PRINTING VENDO MACHINE</p>
    </div>
  </div>

  @include('partials.emergency-check')
  @include('partials.hide-url')
</body>
</html>