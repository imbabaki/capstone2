<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Kiosk Instaprint</title>
    <meta name="viewport" content="width=800, height=480">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 w-[800px] h-[480px] mx-auto flex flex-col justify-start items-center">
    
    <!-- Header -->
    <div class="w-full text-center py-4">
        <h1 class="text-3xl font-bold text-gray-800">Welcome to InstaPrint</h1>
    </div>

    <!-- Start Button -->
    <div class="mt-8">
      <a href="{{ route('options') }}"> Start</a>


    </div>

</body>
</html>
