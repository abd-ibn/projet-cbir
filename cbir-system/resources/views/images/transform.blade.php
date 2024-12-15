@extends('layouts.app')

@section('content')
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Image</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <style>

        .fixed-buttons {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 80px;
            z-index: 1000;
            background-color: white;
            box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
        }

        .fixed-left-buttons, .fixed-right-buttons {
            position: fixed;
            top: 0;
            width: auto;
            z-index: 1000;
        }

        .fixed-left-buttons {
            left: 0;
        }

        .fixed-right-buttons {
            right: 0;
        }

        .fixed-right-buttons .container {
            display: flex;
            gap: 8px;
            align-items: center;
            padding: 15px 20px;
        }

        .fixed-left-buttons .container {
            display: flex;
            gap: 16px;
            align-items: center;
            padding: 15px 20px;
        }

        .fixed-left-buttons .btn,
        .fixed-right-buttons .btn {
            background-color: #f8f9fa;
            border: 0px solid #ccc;
            box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
            color: #000;
            padding: 13px 22px;
            transition: background-color 0.3s ease;
        }

        .fixed-left-buttons .btn:hover,
        .fixed-right-buttons .btn:hover {
            background-color: #e2e6ea;
        }

        .container {
            width: 100%;
            padding-left: 20px;
            padding-right: 20px;
            margin: auto;

            gap: 15px;
            padding: 15px 20px;
        }

        body {
            padding-top: 20px;
        }

    </style>
</head>
<body>
    <!-- navbar -->
    <div class="fixed-buttons">
        <div class="fixed-left-buttons">
            <div class="container">
                <a href="{{ route('images.index') }}" class="btn">Home</a>
            </div>
        </div>
    </div>

    <div class="container">
        <h3>Transform Image: {{ $image->name }}</h3>
        <img src="{{ asset('storage/' . $image->filepath) }}" class="img-fluid mb-3" alt="{{ $image->name }}">

        <form action="{{ route('images.transform', $image->id) }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="crop_width">Crop Width</label>
                <input type="number" name="crop_width" id="crop_width" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="crop_height">Crop Height</label>
                <input type="number" name="crop_height" id="crop_height" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Apply Transformation</button>
        </form>

    </div>

</body>
</html>

@endsection
