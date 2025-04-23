<?php
// API Keys (Replace with your valid keys)
$gemini_key = 'AIzaSyBl1W4CVWof67yJ4KyEglgEqEUUZjIMng0'; // Gemini API Key
$weather_key = '53cfbd94d22893fe626ef5ec1a50c5d1'; // Replace with your valid OpenWeatherMap API Key

// Initialize variables
$crops = [];
$error = null;
$recommendations = null;
$temp = null;
$humidity = null;
$condition = null;
$season = null;
$city = null;
$forecast = []; // For 5-day weather forecast
$crop_type = null; // New variable for crop type

// Function to determine the season
function get_season() {
    $month = date('n');
    if ($month >= 10 || $month < 3) return 'Winter';
    if ($month >= 3 && $month < 7) return 'Summer';
    return 'Monsoon';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $city = filter_input(INPUT_POST, 'city', FILTER_SANITIZE_STRING);
    $crop_type = filter_input(INPUT_POST, 'crop_type', FILTER_SANITIZE_STRING);

    try {
        // Validate city input
        if (empty($city)) {
            throw new Exception("Please enter a city name.");
        }

        // Fetch current weather data
        $weather_url = "https://api.openweathermap.org/data/2.5/weather?q=" . urlencode($city) . "&units=metric&appid=$weather_key";

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $weather_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FAILONERROR => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => true
        ]);

        $weather_response = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new Exception("Weather API Connection Error: " . curl_error($ch));
        }

        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpcode != 200) {
            $error_data = json_decode($weather_response, true);
            $message = $error_data['message'] ?? 'Unknown error';
            throw new Exception("Weather API Error ($httpcode): " . ucfirst($message));
        }

        $weather_data = json_decode($weather_response, true);

        // Extract current weather data
        $temp = $weather_data['main']['temp'] ?? null;
        $humidity = $weather_data['main']['humidity'] ?? null;
        $condition = $weather_data['weather'][0]['main'] ?? null;
        $season = get_season();

        // Fetch 5-day/3-hour weather forecast (free tier)
        $forecast_url = "https://api.openweathermap.org/data/2.5/forecast?q=" . urlencode($city) . "&units=metric&appid=$weather_key";

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $forecast_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FAILONERROR => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => true
        ]);

        $forecast_response = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new Exception("Forecast API Connection Error: " . curl_error($ch));
        }

        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpcode != 200) {
            $error_data = json_decode($forecast_response, true);
            $message = $error_data['message'] ?? 'Unknown error';
            throw new Exception("Forecast API Error ($httpcode): " . ucfirst($message));
        }

        $forecast_data = json_decode($forecast_response, true);

        // Parse forecast data
        if (!empty($forecast_data['list'])) {
            $grouped_forecast = [];
            foreach ($forecast_data['list'] as $item) {
                if (isset($item['dt'], $item['main']['temp'], $item['main']['humidity'], $item['weather'][0]['main'])) {
                    $date = date('Y-m-d', $item['dt']);
                    if (!isset($grouped_forecast[$date])) {
                        $grouped_forecast[$date] = [
                            'date' => date('D, M j', $item['dt']),
                            'temp' => round($item['main']['temp'], 1),
                            'condition' => $item['weather'][0]['main'],
                            'humidity' => $item['main']['humidity']
                        ];
                    }
                }
            }
            $forecast = array_values($grouped_forecast);
        } else {
            throw new Exception("No forecast data found in API response.");
        }

        // Prepare Gemini prompt with crop type filter and requirements
        $prompt = "Act as an agricultural expert. Generate crop recommendations for $city with:
        - Current temperature: {$temp}°C
        - Humidity: {$humidity}%
        - Weather condition: $condition
        - Season: $season";

        if ($crop_type && $crop_type !== 'all') {
            $prompt .= "
        - Only recommend crops of type: $crop_type";
        }

        $prompt .= "

        Provide recommendations in this EXACT format:

        * **Crop Name**
        * **Min Temperature Required:** [minimum temperature in °C for successful growth]
        * **Humidity Level Required:** [minimum humidity percentage for successful growth]
        * **Explanation:** [detailed explanation]
        * **Variety Suggestions:** [comma-separated varieties]
        * **Fertilizer Recommendations:** [comma-separated fertilizers available in India]
        * **Crop Type:** [e.g., cereals, legumes, vegetables, fruits, oilseeds]

        Consider Rajasthan's traditional farming practices and local soil types.
        Include 5-8 relevant crops for the current conditions (or fewer if filtered by type) and recommend fertilizers commonly available in India.";

        // Call Gemini API
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent';
        $data = [
            'contents' => [
                'parts' => [
                    ['text' => $prompt]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.7,
                'maxOutputTokens' => 1500
            ],
            'safetySettings' => [
                [
                    'category' => 'HARM_CATEGORY_DANGEROUS_CONTENT',
                    'threshold' => 'BLOCK_ONLY_HIGH'
                ]
            ]
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => "$url?key=$gemini_key",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true
        ]);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new Exception("Gemini API Error: " . curl_error($ch));
        }

        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpcode != 200) {
            throw new Exception("Gemini API Error: " . ($response ?? 'Unknown error'));
        }

        $gemini_response = json_decode($response, true);
        $recommendations = $gemini_response['candidates'][0]['content']['parts'][0]['text'] ?? '';

        // Parse recommendations and filter by crop type if specified
        if (!empty($recommendations)) {
            $cropSections = preg_split('/\n\s*\n/', $recommendations);

            foreach ($cropSections as $section) {
                if (preg_match('/\* \*\*(.+?)\*\*/', $section, $nameMatch)) {
                    $crop = [
                        'name' => htmlspecialchars($nameMatch[1]),
                        'min_temp' => '', // New field for minimum temperature
                        'humidity_level' => '', // New field for humidity level
                        'explanation' => '',
                        'varieties' => [],
                        'fertilizers' => [],
                        'type' => '' // Crop type
                    ];

                    // Parse minimum temperature
                    if (preg_match('/\* \*\*Min Temperature Required:\*\* (.+?)(\n|$)/s', $section, $tempMatch)) {
                        $crop['min_temp'] = htmlspecialchars(trim($tempMatch[1]));
                    }

                    // Parse humidity level
                    if (preg_match('/\* \*\*Humidity Level Required:\*\* (.+?)(\n|$)/s', $section, $humidMatch)) {
                        $crop['humidity_level'] = htmlspecialchars(trim($humidMatch[1]));
                    }

                    // Parse explanation
                    if (preg_match('/\* \*\*Explanation:\*\* (.+?)(\n|$)/s', $section, $expMatch)) {
                        $crop['explanation'] = htmlspecialchars(trim($expMatch[1]));
                    }

                    // Parse varieties
                    if (preg_match('/\* \*\*Variety Suggestions:\*\* (.+?)(\n|$)/s', $section, $varMatch)) {
                        $varieties = array_map('trim', explode(',', $varMatch[1]));
                        $crop['varieties'] = array_map('htmlspecialchars', $varieties);
                    }

                    // Parse fertilizers
                    if (preg_match('/\* \*\*Fertilizer Recommendations:\*\* (.+?)(\n|$)/s', $section, $fertMatch)) {
                        $fertilizers = array_map('trim', explode(',', $fertMatch[1]));
                        $crop['fertilizers'] = array_map('htmlspecialchars', $fertilizers);
                    }

                    // Parse crop type
                    if (preg_match('/\* \*\*Crop Type:\*\* (.+?)(\n|$)/s', $section, $typeMatch)) {
                        $crop['type'] = htmlspecialchars(trim($typeMatch[1]));
                    }

                    // Filter crops by type if specified
                    if (!$crop_type || $crop_type === 'all' || strtolower($crop['type']) === strtolower($crop_type)) {
                        $crops[] = $crop;
                    }
                }
            }
        }

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agricultural Advisor</title>
    <link rel="stylesheet" href="video-background.css">
    <style>
        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            line-height: 1.6;
            color: #2d3748;
            background-color: #f8fafc;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        h1 {
            color: #2f855a;
            font-size: 2rem;
            margin-bottom: 1.5rem;
            border-bottom: 2px solid #cbd5e0;
            padding-bottom: 0.5rem;
        }

        .weather-header {
            background: #f7fafc;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            border-left: 4px solid #48bb78;
        }

        .crop-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            transition: transform 0.2s ease;
        }

        .crop-card:hover {
            transform: translateY(-2px);
        }

        .crop-name {
            color: #2f855a;
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .crop-name::before {
            content: "🌱";
            font-size: 1.2em;
        }

        .crop-requirements {
            color: #4a5568;
            font-size: 0.9rem;
            margin-right: 1rem;
        }

        .explanation {
            color: #4a5568;
            margin-left: 1.5rem;
            padding-left: 1rem;
            border-left: 2px solid #cbd5e0;
            margin-bottom: 1rem;
        }

        .varieties, .fertilizers, .forecast-container {
            background: #f7fafc;
            padding: 1rem;
            border-radius: 6px;
            margin-top: 1rem;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
        }

        .dropdown-icon {
            font-size: 1.2rem;
            transition: transform 0.3s ease;
        }

        .dropdown-icon.expanded {
            transform: rotate(180deg);
        }

        .variety-list, .fertilizer-list, .forecast-grid {
            columns: 2;
            list-style-type: none;
            padding: 0;
            margin: 0;
            display: none; /* Initially hidden */
        }

        .variety-list li, .fertilizer-list li {
            padding: 0.25rem 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .variety-list li::before {
            content: "✔️";
            color: #48bb78;
        }

        .fertilizer-list li::before {
            content: "🌿";
            color: #2f855a;
        }

        .forecast-grid {
            display: none; /* Initially hidden */
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }

        .forecast-day {
            background: white;
            padding: 1rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .forecast-day h4 {
            margin: 0 0 0.5rem 0;
            color: #2f855a;
        }

        .forecast-day p {
            margin: 0.25rem 0;
            color: #4a5568;
        }

        .note {
            background: #fff9db;
            color: #5f3dc4;
            padding: 1rem;
            border-radius: 6px;
            margin-top: 2rem;
            border: 1px solid #ffe066;
        }

        .form-container {
            max-width: 500px;
            margin: 20px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        input[type="text"], select {
            width: 70%;
            padding: 12px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-size: 16px;
            flex: 1;
        }

        button {
            padding: 12px 24px;
            background: #48bb78;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: background 0.3s ease;
            flex: 0 0 auto;
        }

        button:hover {
            background: #38a169;
        }

        .error {
            color: #e53e3e;
            padding: 1rem;
            background: #fff5f5;
            border-radius: 6px;
            margin: 1rem auto;
            max-width: 800px;
        }

        /* Loading Screen Styles */
        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .loader {
            border: 8px solid #f3f3f3;
            border-top: 8px solid #48bb78;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }
            
            .form-container {
                padding: 15px;
                flex-direction: column;
                gap: 15px;
            }
            
            input[type="text"], select {
                width: 100%;
            }
            
            button {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="video-background">
        <video autoplay muted loop playsinline>
            <source src="farming-video.mp4" type="video/mp4">
            Your browser does not support the video tag.
        </video>
    </div>

    <div class="loading-overlay" id="loadingOverlay">
        <div class="loader"></div>
    </div>

    <div class="form-container">
        <form method="POST" id="recommendationForm">
            <input type="text" name="city" placeholder="Enter city (e.g., Jaipur)" required>
            <select name="crop_type" required>
                <option value="all">All Crops</option>
                <option value="cereals">Cereals</option>
                <option value="legumes">Legumes</option>
                <option value="vegetables">Vegetables</option>
                <option value="fruits">Fruits</option>
                <option value="oilseeds">Oilseeds</option>
            </select>
            <button type="submit">Get Recommendations</button>
        </form>
    </div>

    <?php if (isset($error)): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if (isset($recommendations)): ?>
    <div class="container">
        <h1>Agricultural Recommendations for <?= htmlspecialchars($city) ?></h1>
        
        <div class="weather-header">
            <h2>Current Conditions</h2>
            <p>🌡 Temperature: <?= round($temp, 1) ?>°C</p>
            <p>💧 Humidity: <?= $humidity ?>%</p>
            <p>☀️ Weather: <?= htmlspecialchars($condition) ?></p>
            <p>📅 Season: <?= htmlspecialchars($season) ?></p>
        </div>

        <?php if (!empty($forecast)): ?>
        <div class="forecast-container">
            <div class="section-header" onclick="toggleSection('forecast-grid', this)">
                <h2>5-Day Weather Forecast</h2>
                <span class="dropdown-icon">▼</span>
            </div>
            <div class="forecast-grid" id="forecast-grid">
                <?php foreach ($forecast as $day): ?>
                <div class="forecast-day">
                    <h4><?= $day['date'] ?></h4>
                    <p>🌡 <?= $day['temp'] ?>°C</p>
                    <p>☀️ <?= $day['condition'] ?></p>
                    <p>💧 <?= $day['humidity'] ?>%</p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($crops)): ?>
            <div class="recommendations">
                <h2>Crop Recommendations</h2>
                <?php $index = 0; ?>
                <?php foreach ($crops as $crop): ?>
                <div class="crop-card">
                    <div class="crop-name">
                        <?= $crop['name'] ?>
                        <span class="crop-requirements">
                            Min Temp: <?= $crop['min_temp'] ?>, Humidity: <?= $crop['humidity_level'] ?>  
                        </span>
                    </div>
                    <?php if (!empty($crop['explanation'])): ?>
                    <div class="explanation">
                        <?= $crop['explanation'] ?>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($crop['varieties'])): ?>
                    <div class="varieties">
                        <div class="section-header" onclick="toggleSection('variety-list-<?= $index ?>', this)">
                            <h4>Recommended Varieties:</h4>
                            <span class="dropdown-icon">▼</span>
                        </div>
                        <ul class="variety-list" id="variety-list-<?= $index ?>">
                            <?php foreach ($crop['varieties'] as $variety): ?>
                            <li><?= $variety ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($crop['fertilizers'])): ?>
                    <div class="fertilizers">
                        <div class="section-header" onclick="toggleSection('fertilizer-list-<?= $index ?>', this)">
                            <h4>Recommended Fertilizers (Available in India):</h4>
                            <span class="dropdown-icon">▼</span>
                        </div>
                        <ul class="fertilizer-list" id="fertilizer-list-<?= $index ?>">
                            <?php foreach ($crop['fertilizers'] as $fertilizer): ?>
                            <li><?= $fertilizer ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($crop['type'])): ?>
                    <div class="crop-type">
                        <p><strong>Crop Type:</strong> <?= htmlspecialchars($crop['type']) ?></p>
                    </div>
                    <?php endif; ?>
                </div>
                <?php $index++; ?>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="error">No crop recommendations found for current conditions and selected crop type.</div>
        <?php endif; ?>

        <div class="note">
            <p>Note: Recommendations powered by Google Gemini. Verify with local experts.</p>
        </div>
    </div>
    <?php endif; ?>

    <script>
        // Toggle section visibility
        function toggleSection(id, header) {
            const element = document.getElementById(id);
            const icon = header.querySelector('.dropdown-icon');
            if (element.style.display === "none" || element.style.display === "") {
                element.style.display = id === 'forecast-grid' ? 'grid' : 'block';
                icon.classList.add('expanded');
            } else {
                element.style.display = "none";
                icon.classList.remove('expanded');
            }
        }

        // Show loading screen on form submit
        document.getElementById('recommendationForm').addEventListener('submit', function() {
            document.getElementById('loadingOverlay').style.display = 'flex';
        });

        // Hide loading screen when page loads (after PHP processing)
        window.addEventListener('load', function() {
            document.getElementById('loadingOverlay').style.display = 'none';
        });
    </script>
</body>
</html>