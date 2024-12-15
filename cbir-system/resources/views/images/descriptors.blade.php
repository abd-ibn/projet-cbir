@extends('layouts.app')

@section('content')
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Image Descriptors</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    

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
        <h1>Descriptors for Image: {{ $image->name }}</h1>

        <div class="image-container">
            <img src="{{ asset('storage/' . $image->filepath) }}" alt="Image" class="img-fluid">
        </div>

        @if ($descriptors)
        <div class="descriptors">
            <h3>Color Histogram :</h3>
            @if (isset($descriptors['color_histograms']))
                <canvas id="combined-histogram" width="400" height="150"></canvas>
                <script>
                    var ctx = document.getElementById('combined-histogram').getContext('2d');
                    
                    // Extract the individual histograms for BGR channels
                    var bHistogram = {!! json_encode($descriptors['color_histograms']['b']) !!};
                    var gHistogram = {!! json_encode($descriptors['color_histograms']['g']) !!};
                    var rHistogram = {!! json_encode($descriptors['color_histograms']['r']) !!};

                    // Create a combined dataset for the histogram
                    new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: Array.from({ length: 256 }, (_, i) => i), // X-axis: 0-255 (pixel intensities)
                            datasets: [
                                {
                                    label: 'Blue Channel',
                                    data: bHistogram,
                                    backgroundColor: 'rgba(0, 0, 255, 0.5)', // Blue color
                                    borderColor: 'rgba(0, 0, 255, 1)',
                                    borderWidth: 1
                                },
                                {
                                    label: 'Green Channel',
                                    data: gHistogram,
                                    backgroundColor: 'rgba(0, 255, 0, 0.5)', // Green color
                                    borderColor: 'rgba(0, 255, 0, 1)',
                                    borderWidth: 1
                                },
                                {
                                    label: 'Red Channel',
                                    data: rHistogram,
                                    backgroundColor: 'rgba(255, 0, 0, 0.5)', // Red color
                                    borderColor: 'rgba(255, 0, 0, 1)',
                                    borderWidth: 1
                                }
                            ]
                        },
                        options: {
                            scales: {
                                x: {
                                    title: {
                                        display: true,
                                        text: 'Pixel Intensity (0-255)'
                                    }
                                },
                                y: {
                                    title: {
                                        display: true,
                                        text: 'Frequency'
                                    },
                                    beginAtZero: true
                                }
                            },
                            responsive: true,
                            plugins: {
                                legend: {
                                    position: 'top',
                                }
                            }
                        }
                    });
                </script>
            @else
                <p>Error: Histogram data is not valid.</p>
            @endif

            <h3>Dominant Colors :</h3>
            <div style="display: flex;">
                @foreach ($descriptors['dominant_colors'] as $color)
                    <div style="background-color: {{ $color }}; width: 50px; height: 50px; margin-right: 5px;"></div>
                @endforeach
            </div>





            <h3>Gabor Filters :</h3>
            @if (isset($descriptors['texture_images']))
                <div style="display: flex; flex-wrap: wrap;">
                    @foreach ($descriptors['texture_images'] as $index => $image)
                        <!-- Custom pattern to display images -->
                        @if (($index == 0 || $index == 1) || ($index == 4 || $index == 5))
                            <div style="margin: 10px; text-align: center;">
                                <img src="{{ $image }}" alt="Gabor Filter" style="max-width: 200px; max-height: 200px;">
                                <p>Filter {{ $index + 1 }}</p>
                            </div>
                        @endif
                    @endforeach
                </div>
            @else
                <p>Error: Texture images are not available.</p>
            @endif





            <h3>Hu Moments :</h3>

            <!-- Chart Container -->
            <div style="width: 70%; margin: 0 auto;">
                <canvas id="huMomentsChart"></canvas>
            </div>

            <!-- Display the values below the chart -->
            <div style="width: 70%; text-align;">
                <h4>Raw Hu Moments Values :</h4>
                <ul id="huMomentsText"></ul>
            </div>

            <script>
                // Check if hu_moments are available
                @if (isset($descriptors['hu_moments']))
                    var huMoments = @json($descriptors['hu_moments']);
                @else
                    var huMoments = [];
                @endif

                // Normalize Hu Moments to a range of 0 to 100
                var max = Math.max(...huMoments.map(Math.abs)); // Find the largest absolute value
                var normalizedHuMoments = huMoments.map(function(value) {
                    // Normalize to range [0, 100] (handling negative values)
                    return Math.abs(value) / max * 100; // Scale the absolute value to fit in 0-100
                });

                // Create a chart to visualize the Hu Moments
                var ctx = document.getElementById('huMomentsChart').getContext('2d');
                var huMomentsChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: ['Hu Moment 1', 'Hu Moment 2', 'Hu Moment 3', 'Hu Moment 4', 'Hu Moment 5', 'Hu Moment 6', 'Hu Moment 7'],
                        datasets: [{
                            label: 'Hu Moments (Normalized)',
                            data: normalizedHuMoments,
                            backgroundColor: 'rgba(75, 192, 192, 0.2)',
                            borderColor: 'rgba(75, 192, 192, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 100, // Set a maximum scale of 100
                                ticks: {
                                    callback: function(value) {
                                        return value.toFixed(2);  // Display the values with two decimal points
                                    }
                                }
                            }
                        }
                    }
                });

                // Display the raw values as text below the chart
                var huMomentsTextContainer = document.getElementById("huMomentsText");
                huMoments.forEach(function(moment, index) {
                    var listItem = document.createElement("li");
                    listItem.textContent = `Moment ${index + 1}: ${moment.toFixed(6)}`;
                    huMomentsTextContainer.appendChild(listItem);
                });
            </script>





        </div>



        @else
            <p>No descriptors available.</p>
        @endif
    </div>
</body>
</html>
    
@endsection
