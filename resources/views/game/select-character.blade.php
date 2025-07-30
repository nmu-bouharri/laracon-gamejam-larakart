<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Your Developer - Larakart</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&display=swap');
        
        .racing-font {
            font-family: 'Orbitron', monospace;
        }
        
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .character-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }
        
        .character-card:hover {
            border-color: rgba(255, 215, 0, 0.8);
            box-shadow: 0 0 30px rgba(255, 215, 0, 0.4);
            transform: translateY(-5px);
        }
        
        .character-card.selected {
            border-color: #ffd700;
            box-shadow: 0 0 40px rgba(255, 215, 0, 0.6);
            background: rgba(255, 215, 0, 0.1);
        }
        
        .featured-badge {
            background: linear-gradient(45deg, #ffd700, #ffed4e);
            color: #000;
        }
        
        .stat-bar {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            overflow: hidden;
        }
        
        .stat-fill {
            height: 8px;
            border-radius: 10px;
            transition: width 0.5s ease;
        }
        
        .locked-card {
            position: relative;
            pointer-events: auto;
        }
        
        .locked-card:hover {
            border-color: rgba(239, 68, 68, 0.8) !important;
            box-shadow: 0 0 30px rgba(239, 68, 68, 0.4) !important;
        }
    </style>
</head>
<body class="gradient-bg min-h-screen text-white">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="racing-font text-4xl font-black mb-4 text-white">
                SELECT RACER
            </h1>
            <p class="text-xl text-yellow-400">Choose your PHP developer!</p>
        </div>

        <!-- Developer Grid - Mario Kart Style -->
        <div class="max-w-4xl mx-auto">
            <div class="grid grid-cols-2 gap-8 mb-8">
                @foreach($developers as $developer)
                @php
                    $isLocked = $developer->name === 'Taylor Otwell' && !session('taylor_unlocked', false);
                    $cardClass = $isLocked ? 'character-card locked-card' : 'character-card';
                    $clickHandler = $isLocked ? 'showLockedMessage()' : "selectDeveloper({$developer->id}, '{$developer->name}')";
                @endphp
                <div class="{{ $cardClass }} bg-gradient-to-b from-gray-700 to-gray-800 rounded-xl p-8 cursor-pointer transform hover:scale-105 transition-all duration-300 border-4 border-yellow-400 {{ $isLocked ? 'opacity-60 grayscale' : '' }}" 
                     data-developer-id="{{ $developer->id }}"
                     onclick="{{ $clickHandler }}">
                    
                    @if($isLocked)
                    <div class="bg-red-600 text-white text-sm font-bold px-3 py-1 rounded-full mb-4 inline-block">
                        🔒 LOCKED
                    </div>
                    @endif
                
                <!-- Developer Avatar -->
                <div class="text-center">
                    <div class="text-8xl mb-4">👨‍💻</div>
                    <h3 class="racing-font text-2xl font-bold text-white mb-2">{{ $developer->name }}</h3>
                    <p class="text-yellow-300 text-sm font-medium">{{ $developer->special_ability }}</p>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Selected Developer Info -->
        <div id="selected-info" class="hidden bg-black bg-opacity-30 rounded-xl p-6 mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="racing-font text-2xl font-bold mb-2">Selected: <span id="selected-name"></span></h3>
                    <p id="selected-bio" class="opacity-80"></p>
                </div>
                <div class="text-6xl">🏆</div>
            </div>
        </div>

        <!-- Navigation -->
        <div class="flex justify-between items-center">
            <button onclick="goBack()" class="bg-gray-600 hover:bg-gray-700 px-6 py-3 rounded-lg font-semibold transition-colors">
                ← Back to Menu
            </button>
            
            <button id="continue-btn" onclick="continueToCarSelection()" 
                    class="bg-gradient-to-r from-yellow-400 to-orange-500 text-black px-8 py-3 rounded-lg font-bold hover:scale-105 transition-transform disabled:opacity-50 disabled:cursor-not-allowed" 
                    disabled>
                Continue to Car Selection →
            </button>
        </div>
    </div>

    <script>
        let selectedDeveloperId = null;
        let selectedDeveloperName = null;

        function selectDeveloper(developerId, developerName) {
            // Remove previous selection
            document.querySelectorAll('.character-card').forEach(card => {
                card.classList.remove('selected');
            });

            // Add selection to clicked card
            const selectedCard = document.querySelector(`[data-developer-id="${developerId}"]`);
            selectedCard.classList.add('selected');

            // Store selection
            selectedDeveloperId = developerId;
            selectedDeveloperName = developerName;

            // Update selected info
            document.getElementById('selected-name').textContent = developerName;
            document.getElementById('selected-info').classList.remove('hidden');

            // Enable continue button
            document.getElementById('continue-btn').disabled = false;

            // Store in session storage
            sessionStorage.setItem('selectedDeveloperId', developerId);
            sessionStorage.setItem('selectedDeveloperName', developerName);
        }

        function continueToCarSelection() {
            if (selectedDeveloperId) {
                const urlParams = new URLSearchParams(window.location.search);
                const mode = urlParams.get('mode') || 'singleplayer';
                window.location.href = `/select-car?mode=${mode}&developer=${selectedDeveloperId}`;
            }
        }
        
        function showLockedMessage() {
            alert('🔒 Taylor Otwell is locked! Beat him in single player mode to unlock him and his legendary Lambo!');
        }

        function goBack() {
            window.location.href = '/';
        }

        // Auto-select if coming back from car selection
        document.addEventListener('DOMContentLoaded', function() {
            const storedDeveloperId = sessionStorage.getItem('selectedDeveloperId');
            const storedDeveloperName = sessionStorage.getItem('selectedDeveloperName');
            
            if (storedDeveloperId && storedDeveloperName) {
                selectDeveloper(parseInt(storedDeveloperId), storedDeveloperName);
            }

            // Add keyboard navigation
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && selectedDeveloperId) {
                    continueToCarSelection();
                } else if (e.key === 'Escape') {
                    goBack();
                }
            });
        });

        // Add some interactive effects
        document.querySelectorAll('.character-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                if (!this.classList.contains('selected')) {
                    this.style.transform = 'translateY(-3px)';
                }
            });
            
            card.addEventListener('mouseleave', function() {
                if (!this.classList.contains('selected')) {
                    this.style.transform = 'translateY(0)';
                }
            });
        });
    </script>
</body>
</html>
