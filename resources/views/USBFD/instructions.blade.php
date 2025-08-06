<!DOCTYPE html>
<html>
<head>
    <title>Print Instructions</title>
    <style>
        body {
            font-family: sans-serif;
            background: #f8fafc;
            text-align: center;
            padding: 50px;
        }

        h1 {
            font-size: 2.5rem;
            color: #333;
            margin-bottom: 30px;
        }

        .step {
            margin-bottom: 40px;
        }

        .step img {
            width: 300px;
            height: auto;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .step-title {
            font-size: 1.5rem;
            color: #007bff;
            margin-top: 15px;
        }

        .done-btn {
            padding: 12px 25px;
            background-color: #28a745;
            color: white;
            text-decoration: none;
            font-size: 16px;
            border-radius: 8px;
            transition: background 0.3s;
        }

        .done-btn:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>

    <h1>Follow These Steps Before Printing</h1>

    <div class="step">
        <img src="{{ asset('images/step1.gif') }}" alt="Get Your Paper">
        <div class="step-title">Step 1: Get Your Paper</div>
    </div>

    <div class="step">
        <img src="{{ asset('images/step2.gif') }}" alt="Load Your Paper">
        <div class="step-title">Step 2: Load Your Paper</div>
    </div>

    <<a href="{{ route('finalize') }}" class="done-btn">Done</a>

</body>
</html>
