@extends('layouts.app')

@section('content')
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Similarity Search</title>
    
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

        .results-section {
            margin-top: 20px;
        }

        .results-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .image-card {
            width: 150px;
            text-align: center;
        }

        .image-card img {
            max-width: 100%;
            height: auto;
        }

        .search-buttons {
            display: flex;
            padding: 20px 0;
            gap: 10px;
            width: 100%;

        }

        .recherche {
            padding-left: 20px;
            padding-right: 20px;
            background-color: #f8f9fa;
            transition: background-color 0.3s ease;
            color: black;
            border: 0;
            outline: none;
            border: none;
            box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.04);

        }

        .recherche:hover {
            background-color: #e2e6ea;
            color: black;
            outline: none;
            border: none;
        }

        .recherche:focus,
        .recherche:active, .recherche:hover:active {
            outline: none;
            border: none;
            background-color: #e2e6ea;
            color: black;
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
        <h1>Search for Similar Images: {{ $image->name }}</h1>

        <div class="image-container">
            <img src="{{ asset('storage/' . $image->filepath) }}" alt="Chosen Image" class="img-fluid">
        </div>

        <div class="search-buttons">
            <form method="GET" action="{{ route('images.simpleSearch', $image->id) }}">
                <button type="submit" class="btn btn-primary recherche">Recherche simple</button>
            </form>
            <form method="GET" action="{{ route('images.relevanceSearch', $image->id) }}">
                <button type="submit" class="btn btn-success recherche">Recherche avec retour de pertinence</button>
            </form>
        </div>

        @if (isset($similarImages) && count($similarImages) > 0)
            <div class="results-section">
                <h2>Similar Images</h2>
                <div class="results-container">
                    @foreach ($similarImages as $similarImage)
                        <div class="image-card">
                            <img src="{{ asset('storage/' . $similarImage->filepath) }}" alt="Similar Image" class="img-thumbnail">
                            <p>{{ $similarImage->name }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <p>No similar images found.</p>
        @endif
    </div>

</body>
</html>

@endsection
