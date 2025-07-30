<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Your Car - Larakart</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&display=swap');
        
        .racing-font {
            font-family: 'Orbitron', monospace;
        }
        
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .car-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }
        
        .car-card:hover {
            border-color: rgba(255, 215, 0, 0.8);
            box-shadow: 0 0 30px rgba(255, 215, 0, 0.4);
            transform: translateY(-5px);
        }
        
        .car-card.selected {
            border-color: #ffd700;
            box-shadow: 0 0 40px rgba(255, 215, 0, 0.6);
            background: rgba(255, 215, 0, 0.1);
        }
        
        .lambo-badge {
            background: linear-gradient(45deg, #ffd700, #ffed4e);
            color: #000;
        }
        
        .premium-badge {
            background: linear-gradient(45deg, #8b5cf6, #a855f7);
            color: #fff;
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
        
        .lambo-glow {
            animation: lambo-pulse 2s infinite;
        }
        
        @keyframes lambo-pulse {
            0%, 100% { box-shadow: 0 0 20px rgba(255, 215, 0, 0.4); }
            50% { box-shadow: 0 0 40px rgba(255, 215, 0, 0.8); }
        }
    </style>
</head>
<body class="gradient-bg min-h-screen text-white">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="racing-font text-4xl font-black mb-4 text-white">
                SELECT CAR
            </h1>
            <p class="text-xl text-yellow-400">Choose your ride!</p>
        </div>

        <!-- Cars Grid - Mario Kart Style -->
        <div class="max-w-4xl mx-auto">
            <div class="grid grid-cols-2 gap-8 mb-8">
                @foreach(array_merge($lambos->toArray(), $starterCars->toArray()) as $car)
                @php
                    $carObj = (object) $car;
                    $isLocked = $carObj->unlock_level === 999 && !session('taylor_unlocked', false);
                    $cardClass = $isLocked ? 'car-card locked-card' : 'car-card';
                    $clickHandler = $isLocked ? 'showLockedCarMessage()' : "selectCar({$carObj->id}, '{$carObj->name}', '{$carObj->brand}')";
                @endphp
                <div class="{{ $cardClass }} bg-gradient-to-b from-gray-700 to-gray-800 rounded-xl p-8 cursor-pointer transform hover:scale-105 transition-all duration-300 border-4 border-yellow-400 {{ $isLocked ? 'opacity-60 grayscale' : '' }}" 
                     data-car-id="{{ $carObj->id }}"
                     onclick="{{ $clickHandler }}">
                    
                    @if($isLocked)
                    <div class="bg-red-600 text-white text-sm font-bold px-3 py-1 rounded-full mb-4 inline-block">
                        🔒 LOCKED
                    </div>
                    @endif
                    
                    <!-- Car Display -->
                    <div class="text-center">
                        <div class="text-8xl mb-4">{{ $carObj->is_lambo ? '🏎️' : '🚗' }}</div>
                        <h3 class="racing-font text-2xl font-bold text-white mb-2">{{ $carObj->name }}</h3>
                        <p class="text-yellow-300 text-sm font-medium">{{ $carObj->brand }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Selected Car Info -->
        <div id="selected-info" class="hidden bg-black bg-opacity-30 rounded-xl p-6 mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="racing-font text-2xl font-bold mb-2">Selected: <span id="selected-name"></span></h3>
                    <p id="selected-brand" class="opacity-80"></p>
                </div>
                <div class="text-6xl">🏎️</div>
            </div>
        </div>

        <!-- Navigation -->
        <div class="flex justify-between items-center">
            <button onclick="goBack()" class="bg-gray-600 hover:bg-gray-700 px-6 py-3 rounded-lg font-semibold transition-colors">
                ← Back to Characters
            </button>
            
            <button id="continue-btn" onclick="startRace()" 
                    class="bg-gradient-to-r from-yellow-400 to-orange-500 text-black px-8 py-3 rounded-lg font-bold hover:scale-105 transition-transform disabled:opacity-50 disabled:cursor-not-allowed" 
                    disabled>
                Start Racing! 🏁
            </button>
        </div>
    </div>

    <script>
        let selectedCarId = null;
        let selectedCarName = null;
        let selectedCarBrand = null;

        function selectCar(carId, carName, carBrand) {
            // Remove previous selection
            document.querySelectorAll('.car-card').forEach(card => {
                card.classList.remove('selected');
            });

            // Add selection to clicked card
            const selectedCard = document.querySelector(`[data-car-id="${carId}"]`);
            selectedCard.classList.add('selected');

            // Store selection
            selectedCarId = carId;
            selectedCarName = carName;
            selectedCarBrand = carBrand;

            // Update selected info
            document.getElementById('selected-name').textContent = carName;
            document.getElementById('selected-brand').textContent = carBrand;
            document.getElementById('selected-info').classList.remove('hidden');

            // Enable continue button
            document.getElementById('continue-btn').disabled = false;

            // Store in session storage
            sessionStorage.setItem('selectedCarId', carId);
            sessionStorage.setItem('selectedCarName', carName);
            sessionStorage.setItem('selectedCarBrand', carBrand);
        }

        function startRace() {
            if (selectedCarId) {
                const urlParams = new URLSearchParams(window.location.search);
                const mode = urlParams.get('mode') || 'custom';
                const developerId = urlParams.get('developer') || sessionStorage.getItem('selectedDeveloperId');
                
                if (mode === 'quick') {
                    // For quick race, go straight to a race
                    window.location.href = `/lobby?mode=quick&developer=${developerId}&car=${selectedCarId}`;
                } else {
                    // For custom race, go to lobby
                    window.location.href = `/lobby?developer=${developerId}&car=${selectedCarId}`;
                }
            }
        }

        function goBack() {
            const urlParams = new URLSearchParams(window.location.search);
            const mode = urlParams.get('mode') || 'custom';
            window.location.href = `/select-character?mode=${mode}`;
        }

        // Auto-select if coming back
        document.addEventListener('DOMContentLoaded', function() {
            const storedCarId = sessionStorage.getItem('selectedCarId');
            const storedCarName = sessionStorage.getItem('selectedCarName');
            const storedCarBrand = sessionStorage.getItem('selectedCarBrand');
            
            if (storedCarId && storedCarName && storedCarBrand) {
                selectCar(parseInt(storedCarId), storedCarName, storedCarBrand);
            }

            // Add keyboard navigation
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && selectedCarId) {
                    startRace();
                } else if (e.key === 'Escape') {
                    goBack();
                }
            });
        });

        // Add some interactive effects
        document.querySelectorAll('.car-card').forEach(card => {
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
