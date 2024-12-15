@extends('layouts.app')

@section('content')
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Image Management</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <style>
        .image-card img {
            max-width: 100%;
            max-height: 175px;
            object-fit: cover;
            display: block;
        }

        .image-card {
            height: 90%;
        }

        .col-12, .col-sm-6, .col-md-4, .col-lg-3 {
            display: flex;
            justify-content: center;
        }

        .fixed-image-size {
            max-width: 100%;
            height: auto;
            object-fit: cover;
        }
        
        .action-buttons {
            position: absolute;
            top: 8px;
            right: 8px;
            display: grid;
            grid-template-areas:
                "delete-btn transform-btn"
                "download-btn show-descriptors-btn"
                ". search-btn"; /* Center the fifth button below the second row */
            gap: 6px; /* Space between buttons */
            opacity: 0;
            transition: opacity 0.3s;
        }

        .delete-btn {
            grid-area: delete-btn;
        }

        .transform-btn {
            grid-area: transform-btn;
        }

        .download-btn {
            grid-area: download-btn;
        }

        .show-descriptors-btn {
            grid-area: show-descriptors-btn;
        }

        .search-btn {
            grid-area: search-btn;
        }

        .image-card:hover .action-buttons {
            opacity: 1;
        }

        .delete-btn, .transform-btn, .download-btn, .show-descriptors-btn, .search-btn {
            border: none;
            background-color: rgba(0, 0, 0, 0.3);
            font-size: 1rem;
            cursor: pointer;
            color: white;
            transition: color 0.2s ease;
            padding: 5px 9px;

            display: flex;
            align-items: center;
            justify-content: center;
        }

        .delete-btn:hover, .transform-btn:hover, .download-btn:hover, .show-descriptors-btn:hover, .search-btn:hover {
            color: black;
            background-color: white;
        }

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

        .sidebar {
            position: fixed;
            top: 60px;
            left: 0;
            height: calc(100vh - 60px);
            width: 300px;
            padding: 60px 35px 20px 35px;
            border-right: 1px solid #ddd;
            background-color: #f9f9f9;
            overflow-y: auto;
            z-index: 999;
        }

        .sidebar h4 {
            margin-bottom: 20px;
        }

        .sidebar .btn {
            width: 100%;
            margin-bottom: 10px;
            text-align: left;
            outline: none;
            border: none;
            transition: background-color 0.2s ease;
        }

        .sidebar .btn:focus,
        .sidebar .btn:active {
            outline: none;
            border: none;
            box-shadow: none;
        }

        .sidebar .btn.active {
            background-color: #e2e6ea;
            color: #000000;
            border: none;
        }

        .fixed-image-size {
            width: 100%;
            height: auto;
            max-height: 250px;
            object-fit: cover;
        }

        .card-title, .card-text {
            font-size: 0.9rem;
        }

        .container {
            width: 100%;
            padding-left: 20px;
            padding-right: 20px;
            margin: 0;

            display: flex;
            gap: 15px;
            align-items: center;
            padding: 15px 20px;
        }

        body {
            padding-top: 20px;
            padding-left: 300px;
        }
    </style>
</head>

<body>
    <!-- navbar -->
    <div class="fixed-buttons">
        <div class="fixed-left-buttons">
            <div class="container">
                <a href="{{ route('images.index') }}" class="btn">Home</a>
                <button class="btn" data-bs-toggle="modal" data-bs-target="#uploadModal">Upload</button>
            </div>
        </div>
        <div class="fixed-right-buttons">
            <div class="container">
                <button id="bulkDelete" class="btn btn-danger">
                    <i class="bi bi-trash"></i>
                </button>
                <button id="bulkDownload" class="btn btn-success ms-2">
                    <i class="bi bi-download"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- sidebar categories filter -->
    <div class="sidebar">
        <h4>Filter by Category</h4>
        <form method="GET" action="{{ route('images.index') }}">
            <button 
                type="submit" 
                name="category_id" 
                value="" 
                class="btn {{ request('category_id') == '' ? 'active' : '' }}">
                All Categories
            </button>
            @foreach($categories as $category)
                <button 
                    type="submit" 
                    name="category_id" 
                    value="{{ $category->id }}" 
                    class="btn {{ request('category_id') == $category->id ? 'active' : '' }}">
                    {{ $category->name }}
                </button>
            @endforeach
        </form>
    </div>

    <!-- main -->
    <div class="container mt-3 pt-4">
        <div class="row">
            @forelse($images as $image)
                <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                    <div class="card mb-2 image-card">
                        <div class="position-relative">
                            <input type="checkbox" class="form-check-input position-absolute top-0 start-0 m-2 image-checkbox" value="{{ $image->id }}">
                            <img src="{{ asset('storage/' . $image->filepath) }}" class="card-img-top img-fluid fixed-image-size" alt="{{ $image->name }}">
                        </div>
                        <div class="card-body">
                            <h5 class="card-title">{{ $image->name }}</h5>
                            <p class="card-text">Category: {{ $image->category->name }}</p>
                            <div class="action-buttons">
                                <form action="{{ route('images.destroy', $image->id) }}" method="POST" class="delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger delete-btn">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                <a href="{{ route('images.edit', $image->id) }}" class="btn btn-primary transform-btn">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="{{ route('images.download', $image->id) }}" class="btn btn-success download-btn">
                                    <i class="bi bi-download"></i>
                                </a>
                                <a href="{{ route('images.showDescriptors', $image->id) }}" class="btn btn-info show-descriptors-btn">
                                    <i class="bi bi-info-circle"></i> <!-- New icon for the "Show Descriptors" button -->
                                </a>
                                <a href="{{ route('images.search', $image->id) }}" class="btn btn-secondary search-btn">
                                    <i class="bi bi-search"></i> <!-- New icon for the "Search" button -->
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <p>No images found for this category.</p>
            @endforelse
        </div>
    </div>


    <!-- upload form -->
    <div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="uploadModalLabel">Upload Image</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('images.upload') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="image" class="form-label">Select Images</label>
                            <input type="file" name="image[]" class="form-control" multiple required>
                        </div>
                        <div class="mb-3">
                            <label for="category_id" class="form-label">Select Category</label>
                            <select name="category_id" class="form-select" required>
                                <option value="" disabled selected>Select a category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Upload</button>
                    </form>

                </div>
            </div>
        </div>
    </div>


    <!-- to move to app.js later -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const deleteButton = document.getElementById('bulkDelete');
            const downloadButton = document.getElementById('bulkDownload');

            const getSelectedImages = () => {
                const checkboxes = document.querySelectorAll('.image-checkbox:checked');
                return Array.from(checkboxes).map(cb => cb.value);
            };

            deleteButton.addEventListener('click', () => {
                const selected = getSelectedImages();
                if (selected.length === 0) {
                    alert('Nothing is selected yet.');
                    return;
                }

                if (confirm('Are you sure you want to delete the selected images?')) {
                    fetch('{{ route('images.bulkDelete') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        },
                        body: JSON.stringify({ ids: selected }),
                    }).then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Selected images deleted successfully.');
                            location.reload();
                        }
                    });
                }
            });

            downloadButton.addEventListener('click', () => {
                const selected = getSelectedImages();
                if (selected.length === 0) {
                    alert('Nothing is selected yet.');
                    return;
                }

                fetch('{{ route('images.bulkDownload') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: JSON.stringify({ selected_images: selected }),
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        data.downloadLinks.forEach(link => {
                            const a = document.createElement('a');
                            a.href = link;
                            a.download = link.split('/').pop(); // Extract filename from the URL
                            document.body.appendChild(a);
                            a.click();
                            document.body.removeChild(a);
                        });
                    } else {
                        alert(data.message || 'Error downloading images.');
                    }
                })
                .catch(error => {
                    console.error('Fetch Error:', error);
                    alert('An error occurred while processing your request.');
                });
            });
        });
    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
@endsection
