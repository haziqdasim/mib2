<?php
// Set timezone
date_default_timezone_set('Asia/Kuala_Lumpur');

// Read configuration parameters
$config_file = 'active_slide.txt';
$interval_file = 'carousel_interval.txt';
$upload_dir = 'assets/slide/';

$live_image = file_exists($config_file) ? trim(file_get_contents($config_file)) : '10.png';
$carousel_seconds = file_exists($interval_file) ? intval(trim(file_get_contents($interval_file))) : 5;

// Collect all slides in directory to establish the javascript carousel collection map
$all_slides = [];
if (is_dir($upload_dir)) {
    $scanned_slides = array_diff(scandir($upload_dir), array('.', '..'));
    $all_slides = array_values($scanned_slides); // Clean array indices
}
// Fallback if directory is empty
if (empty($all_slides)) {
    $all_slides[] = $live_image;
}

// Load World Cup Matches
$json_file = 'fifa_world_cup_2026_malaysia_time.json';
$matches = [];
if (file_exists($json_file)) {
    $json_data = file_get_contents($json_file);
    $match_data = json_decode($json_data, true);
    if (isset($match_data['matches']) && is_array($match_data['matches'])) {
        $now = new DateTime('now', new DateTimeZone('Asia/Kuala_Lumpur'));
        $upcoming_matches = [];
        foreach ($match_data['matches'] as $match) {
            $time_str = $match['malaysia_time'];
            $match_time = DateTime::createFromFormat('Y-m-d H:i', $time_str, new DateTimeZone('Asia/Kuala_Lumpur'));
            if ($match_time) {
                // A match is finished 2 hours after starting
                $finish_time = clone $match_time;
                $finish_time->modify('+2 hours');
                if ($now >= $finish_time) {
                    continue; // Skip finished match
                }
            }
            $upcoming_matches[] = $match;
        }
        $matches = array_slice($upcoming_matches, 0, 4);
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TV Multipurpose Information Board</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        @font-face {
            font-family: 'FWC2026-NormalRegular';
            src: url('/fonts/FWC2026-NormalRegular.ttf') format('truetype');
            font-weight: normal;
            font-style: normal;
        }

        @font-face {
            font-family: 'Inter-Custom';
            src: url('/assets/fonts/Inter_18pt-Regular.ttf') format('truetype');
            font-weight: normal;
            font-style: normal;
        }

        *, *::before, *::after {
            box-sizing: border-box;
        }
        
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            background-color: #000;
            color: #fff;
            overflow: hidden;
            font-family: 'FWC2026-NormalRegular', -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        h1, h2, h3, h4, h5, h6, span, div, p {
            font-family: 'FWC2026-NormalRegular', sans-serif;
        }

        .inter { 
            font-family: 'Inter-Custom', sans-serif !important; 
        }

        .tv-container {
            height: 100vh;
            display: table;
            width: 100%;
            table-layout: fixed;
        }

        .main-content-row {
            display: table-row;
            height: 88vh;
        }

        .sidebar-cell {
            display: table-cell;
            width: 16%;
            vertical-align: top;
            padding: 20px 15px;
            background-image: url(assets/bg-sidebar.png);
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        .sidebar-header-wrapper {
            position: relative;
            text-align: center;
            margin-bottom: 25px;
        }

        .carousel-cell {
            display: table-cell;
            width: 84%;
            color: #1a1a1a;
            vertical-align: middle;
            text-align: center;
            position: relative;
            background-position: center;
            background-size: cover;
            background-repeat: no-repeat;
            box-shadow: 
            inset 4px 4px 30px rgba(0, 0, 0, 0.1), 
            inset -4px -4px 30px rgba(0, 0, 0, 0.1);
            transition: background-image 0.8s ease-in-out; /* Smooth slide change transitions */
        }

        .ticker-row {
            display: table-row;
            height: 12vh;
            background-image: url(assets/bg-sidebar2.png);
            background-repeat: no-repeat;
            background-size: contain;
            background-position: left;
        }

        .ticker-container-cell {
            display: table-cell;
            vertical-align: middle;
            padding: 0 15px;
        }

        .ticker-flex-layout {
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 100%;
        }

        .ticker-label {
            font-weight: 700;
            font-size: 1.15rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            white-space: nowrap;
            padding-right: 20px;
            color: #ffffff;
        }

        .livescore-card{
            background-color: white;
            border-radius: 5px 20px 5px;
        }

        .bottom-right-logo-cell {
            display: table-cell;
            width: auto;
            vertical-align: middle;
            text-align: center;
            background-image: url(assets/bg-lowerbar.png);
            background-repeat: no-repeat;
            background-size: cover;
            background-position: center;
        }

        .red{ background-color: #D40101; border-radius: 5px 20px 5px; }
        .dark-red{ background-color: #731311; border-radius: 5px 20px 5px; }
        .green{ background-color: #00C953; border-radius: 5px 20px 5px; }
        .dark-green{ background-color: #004E3C; border-radius: 5px 20px 5px; }
        .card{ border: none; }
        .card-header:first-child{ border-radius: 5px 19px 0 0; }
    </style>
</head>

<body>

    <div class="tv-container">
        <div class="main-content-row">
            <div class="sidebar-cell">
                <div class="sidebar-header-wrapper" style="padding-bottom: 20%">
                    <a href="dashboard.php">
                        <img src="/assets/logo-white.png" class="img-fluid" style="width:30%" alt="logo">
                    </a>
                </div>

                <h5 class="text-white mt-5">World Cup Matches</h5>
                
                <div id="matches-container">
                    <?php
                    if (!isset($matches) || !is_array($matches)) {
                        $matches = [];
                    }
                    $colors = ['dark-red', 'red', 'green', 'dark-green'];
                    foreach ($matches as $index => $match):
                        $color = $colors[$index % count($colors)];
                        $time_str = $match['malaysia_time'];
                        $datetime = DateTime::createFromFormat('Y-m-d H:i', $time_str);
                        if ($datetime) {
                            $formatted_time = $datetime->format('D, j M') . '<br>' . $datetime->format('g:ia');
                        } else {
                            $formatted_time = htmlspecialchars($time_str);
                        }
                    ?>
                    <div class="card mb-3" style="border-radius: 5px 20px 5px;">
                        <div class="card-header text-white inter fw-bold <?php echo $color; ?>" id="stage"><?php echo htmlspecialchars($match['stage']); ?></div>
                        <div class="card-body text-dark">
                            <div class="row">
                                <div class="col-md-7">
                                    <span class="inter" id="home_team"><?php echo htmlspecialchars($match['home_team']); ?></span><br>
                                    <span class="inter" id="away_team"><?php echo htmlspecialchars($match['away_team']); ?></span>
                                </div>
                                <div class="col-md-5">
                                    <span class="inter" id="malaysia_time"><?php echo $formatted_time; ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="carousel-cell" id="main-carousel-board" style="background-image: url('assets/slide/<?php echo htmlspecialchars($live_image); ?>');">
            </div>
        </div>

        <div class="ticker-row">
            <div class="ticker-container-cell">
                <div class="ticker-flex-layout">
                    <div></div>
                    <div class="d-flex justify-content-end ticker-label">Live Score :</div>
                </div>
            </div>

            <div class="bottom-right-logo-cell">
                <div class="d-flex bd-highlight mb-3" id="live-scores-container">
                    </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
        crossorigin="anonymous"></script>
        
    <script>
        // Inject server-side structural variables safely into the client tracking environment
        let slideCollection = <?php echo json_encode($all_slides); ?>;
        let currentIntervalDelay = <?php echo $carousel_seconds * 1000; ?>;
        let activeImagePointer = "<?php echo htmlspecialchars($live_image); ?>";
        
        let currentSlideIndex = slideCollection.indexOf(activeImagePointer);
        if (currentSlideIndex === -1) currentSlideIndex = 0;

        let carouselIntervalTimer = null;

        // Auto-run carousel rotation function
        function startCarouselLoop() {
            if (carouselIntervalTimer) clearInterval(carouselIntervalTimer);
            if (slideCollection.length <= 1) return; // Do not loop if only 1 image exists

            carouselIntervalTimer = setInterval(() => {
                currentSlideIndex = (currentSlideIndex + 1) % slideCollection.length;
                const nextImage = slideCollection[currentSlideIndex];
                document.getElementById('main-carousel-board').style.backgroundImage = `url('assets/slide/${nextImage}')`;
            }, currentIntervalDelay);
        }

        // Initialize Carousel execution tracking parameters
        startCarouselLoop();

        function scheduleMatchExpiryReload() {
            fetch('fifa_world_cup_2026_malaysia_time.json')
                .then(response => response.json())
                .then(data => {
                    if (!data.matches) return;
                    const now = new Date();
                    let closestTimeout = null;

                    data.matches.forEach(match => {
                        if (match.malaysia_time.includes('to') || match.malaysia_time.includes('Various')) return;
                        const parts = match.malaysia_time.split(/[- :]/);
                        if (parts.length < 5) return;
                        
                        const matchTime = new Date(parts[0], parts[1] - 1, parts[2], parts[3], parts[4], 0, 0);
                        const expiryTime = new Date(matchTime.getTime() + (2 * 60 * 60 * 1000));
                        
                        if (expiryTime > now) {
                            const delay = expiryTime.getTime() - now.getTime();
                            if (closestTimeout === null || delay < closestTimeout) {
                                closestTimeout = delay;
                            }
                        }
                    });

                    if (closestTimeout !== null) {
                        setTimeout(() => { window.location.reload(); }, closestTimeout);
                    } else {
                        setTimeout(scheduleMatchExpiryReload, 900000);
                    }
                })
                .catch(err => console.error('Failed to parse match schedule metadata:', err));
        }
        scheduleMatchExpiryReload();

        // 3-second system state updates handler logic block
        setInterval(() => {
            fetch(window.location.href)
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                
                // Read fresh inline JS arrays directly from data attributes or script extractions dynamically
                // We parse the DOM variables to check if assets or interval parameters have changed in the backend
                const scriptText = html;
                const matchSlides = scriptText.match(/let slideCollection = (\[.*?\]);/);
                const matchDelay = scriptText.match(/let currentIntervalDelay = ([0-9]+);/);

                if (matchSlides && matchDelay) {
                    const newSlides = JSON.parse(matchSlides[1]);
                    const newDelay = parseInt(matchDelay[1]);

                    // If dashboard configs have been updated, rebuild the carousel engine dynamically
                    if (JSON.stringify(newSlides) !== JSON.stringify(slideCollection) || newDelay !== currentIntervalDelay) {
                        slideCollection = newSlides;
                        currentIntervalDelay = newDelay;
                        currentSlideIndex = 0;
                        startCarouselLoop();
                    }
                }
                
                const newMatches = doc.querySelector('#matches-container');
                const currentMatches = document.querySelector('#matches-container');
                if (newMatches && currentMatches) {
                    currentMatches.innerHTML = newMatches.innerHTML;
                }
            });
        }, 3000);

        function getStageLabel(game) {
            const typeMap = {
                'group': 'Group ' + game.group,
                'r32':   'Round of 32',
                'r16':   'Round of 16',
                'qf':    'Quarter-Final',
                'sf':    'Semi-Final',
                'third': '3rd Place',
                'final': 'Final'
            };
            return typeMap[game.type] || game.group || 'Match';
        }

        function buildScoreCard(game, index) {
            const homeTeam = game.home_team_name_en || game.home_team_label || '?';
            const awayTeam = game.away_team_name_en || game.away_team_label || '?';
            const homeScore = game.home_score !== undefined ? game.home_score : '-';
            const awayScore = game.away_score !== undefined ? game.away_score : '-';
            const stage = getStageLabel(game);

            return `
                <div class="p-2 bd-highlight">
                    <div class="livescore-card">
                        <div class="d-flex bd-highlight">
                            <div class="py-1 px-3 flex-fill bd-highlight">
                                <span class="text-dark inter" id="home_team_name_en_${index}">${homeTeam}</span>
                            </div>
                            <div class="py-1 px-3 flex-fill bd-highlight">
                                <span class="text-dark inter fw-bold" id="home_score_${index}">${homeScore}</span>
                                <span class="text-dark">V</span>
                                <span class="text-dark inter fw-bold" id="away_score_${index}">${awayScore}</span>
                            </div>
                            <div class="py-1 px-3 flex-fill bd-highlight">
                                <span class="text-dark inter" id="away_team_name_en_${index}">${awayTeam}</span>
                            </div>
                        </div>
                        <div class="d-flex bd-highlight">
                            <div class="py-1 px-3 flex-fill bd-highlight">
                                <span class="text-dark inter" id="stage_${index}"><b>${stage}</b></span>
                            </div>
                        </div>
                    </div>
                </div>`;
        }

        function fetchLiveScores() {
            fetch('proxy.php')
                .then(response => response.json())
                .then(data => {
                    const games = data.games || [];
                    const finishedGames = games.filter(g => 
                        g.time_elapsed === 'finished' || 
                        g.finished === 'TRUE' || 
                        g.finished === true
                    );

                    finishedGames.sort((a, b) => parseInt(b.id) - parseInt(a.id));
                    const selected = finishedGames.slice(0, 4);

                    const container = document.getElementById('live-scores-container');
                    if (container) {
                        container.innerHTML = selected.map((game, i) => buildScoreCard(game, i)).join('');
                    }
                })
                .catch(err => console.error('Live score fetch failed:', err));
        }

        fetchLiveScores();
        setInterval(fetchLiveScores, 30000);
    </script>
</body>

</html>