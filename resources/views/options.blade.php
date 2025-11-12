<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=1024, height=600, initial-scale=1.0">
  <title>Choose Input Method</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    html, body {
      width: 100%;
      height: 100%;
      margin: 0;
      padding: 0;
      overflow: hidden;
      background-color: #111827;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .main-container {
      display: flex;
      width: 100vw;
      height: 100vh;
      background: linear-gradient(to bottom right, #1f2937, #111827);
    }

    /* LEFT PANEL */
    .left-panel {
      flex: 1;
      display: flex;
      flex-direction: column;
      justify-content: flex-start;
      align-items: center;
      padding: 4vh 3vw;
      text-align: center;
    }

    .footer-logo img {
      width: clamp(8vh, 12vw, 16vh);
      height: auto;
      filter: drop-shadow(0 0 1.5vw rgba(6,182,212,0.7));
      animation: fadeIn 1.2s ease forwards;
      user-select: none;
      -webkit-user-drag: none;
      margin-bottom: 2vh;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(40px); }
      to { opacity: 1; transform: translateY(0); }
    }

    /* BACK BUTTON */
    .back-btn {
      margin-top: 2vh;
      padding: clamp(1.5vh, 2vh, 2.5vh) clamp(5vw, 6vw, 7vw);
      background: linear-gradient(145deg, #0ea5e9, #0284c7);
      color: white;
      font-weight: 900;
      font-size: clamp(2.5vh, 2.8vw, 3vh);
      text-transform: uppercase;
      border-radius: 2.5vh;
      box-shadow: 0 0 1.5vw rgba(14,165,233,0.9);
      transition: all 0.3s ease-in-out;
      cursor: pointer;
      text-decoration: none;
    }

    .back-btn:hover {
      background: linear-gradient(145deg, #dc2626, #b91c1c);
      transform: scale(1.08);
      box-shadow: 0 0 2vw rgba(220,38,38,0.9);
    }

    /* RIGHT PANEL */
    .right-panel {
      flex: 1.5;
      display: flex;
      justify-content: center;
      align-items: center;
      overflow: hidden;
      padding: 2vh 4vw;
    }

    .button-group {
      display: flex;
      flex-direction: column;
      gap: 6vh;
      width: 100%;
      align-items: flex-end;
      padding-right: 5vw;
    }

    .option-btn {
      display: flex;
      align-items: center;
      justify-content: flex-start;
      gap: clamp(2vw, 3vw, 4vw);
      background: linear-gradient(145deg, #0ea5e9, #0284c7);
      color: white;
      font-weight: 900;
      font-size: clamp(3vh, 3.2vw, 4.2vh);
      text-transform: uppercase;
      padding: clamp(2.5vh, 3vh, 3.5vh) clamp(5vw, 7vw, 9vw);
      border-radius: 4vh;
      width: 85%;
      box-shadow: 0 0 2vw rgba(14,165,233,0.9), 10px 10px 0 rgba(0,0,0,0.5);
      text-decoration: none;
      opacity: 0;
      transform: translateX(200px);
      animation: slideIn 1s ease forwards;
      transition: all 0.3s ease-in-out;
      cursor: pointer;
    }

    .option-btn:nth-child(1) { animation-delay: 0.3s; }
    .option-btn:nth-child(2) { animation-delay: 0.6s; }
    .option-btn:nth-child(3) { animation-delay: 0.9s; }

    @keyframes slideIn {
      0% { opacity: 0; transform: translateX(200px); }
      100% { opacity: 1; transform: translateX(0); }
    }

    .option-btn:hover {
      transform: scale(1.08);
      background: linear-gradient(145deg, #dc2626, #b91c1c);
      box-shadow: 0 0 2vw rgba(220,38,38,0.9);
    }

    .option-btn img {
      width: clamp(6vh, 7vw, 10vh);
      height: auto;
      filter: drop-shadow(0 0 0.8vw rgba(255,255,255,0.6));
      transition: transform 0.3s ease;
    }

    .option-btn:hover img {
      transform: rotate(5deg) scale(1.1);
    }

    /* Staircase alignment */
    .bluetooth { margin-right: 0rem; }
    .usb { margin-right: -4vw; }
    .qr { margin-right: -8vw; }

    /* Small screen optimization */
    @media (max-width: 1024px) and (max-height: 600px) {
      .option-btn {
        font-size: clamp(2.8vh, 3vw, 3.5vh);
        padding: 2.5vh 5vw;
      }
    }
  </style>
</head>

<body>
  <div class="main-container">
    <!-- LEFT SIDE -->
    <div class="left-panel">
      <div class="footer-logo">
        <img src="/icons/instaprint1.png" alt="Instaprint Logo">
      </div>

      <!-- Back Button -->
      <a href="{{ route('start') }}" class="back-btn">←</a>
    </div>

    <!-- RIGHT SIDE -->
    <div class="right-panel">
      <div class="button-group">
        <a href="{{ route('bluetooth.index') }}" class="option-btn bluetooth">
          <img src="/icons/bluetooth.png" alt="Bluetooth Icon"> BLUETOOTH
        </a>
        <a href="{{ route('usbfd.index') }}" class="option-btn usb">
          <img src="/icons/usb.png" alt="USB Icon"> USB FLASH DRIVE
        </a>
        <a href="{{ route('qr.code') }}" class="option-btn qr">
          <img src="/icons/qr.png" alt="QR Icon"> QR CODE
        </a>
      </div>
    </div>
  </div>
  @include('partials.emergency-check')
</body>
</html>