<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Larakart - Beat Taylor Otwell to Unlock His Lambo!</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&display=swap');
        
        .racing-font {
            font-family: 'Orbitron', monospace;
        }
        
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .lambo-glow {
            box-shadow: 0 0 30px rgba(255, 215, 0, 0.6);
        }
        
        .racing-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        @keyframes pulse-glow {
            0%, 100% { box-shadow: 0 0 20px rgba(255, 215, 0, 0.4); }
            50% { box-shadow: 0 0 40px rgba(255, 215, 0, 0.8); }
        }
        
        .pulse-glow {
            animation: pulse-glow 2s infinite;
        }
    </style>
</head>
<body class="gradient-bg min-h-screen text-white">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="text-center mb-12">
            <h1 class="racing-font text-6xl font-black mb-4 text-transparent bg-clip-text bg-gradient-to-r from-yellow-400 to-orange-500">
                🏁 Larakart 🏁
            </h1>
        </div>

        <!-- Main Menu -->
        <div class="max-w-4xl mx-auto">
            <div class="text-center mb-12">
                <h2 class="racing-font text-3xl font-bold mb-8">Choose Your Game Mode</h2>
                <!-- Game Modes - Mario Kart Style -->
        <div class="max-w-2xl mx-auto">
            <div class="space-y-6">
                <!-- Single Player Mode -->
                <div class="mode-card bg-gradient-to-r from-red-600 to-red-700 rounded-xl p-6 cursor-pointer transform hover:scale-105 transition-all duration-300 border-4 border-yellow-400" 
                     onclick="selectMode('singleplayer')">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <div class="text-5xl">🏁</div>
                            <div>
                                <h2 class="racing-font text-2xl font-bold text-white">1 PLAYER</h2>
                                <p class="text-yellow-200">Race against AI • Unlock Taylor!</p>
                            </div>
                        </div>
                        <div class="text-white text-2xl">▶️</div>
                    </div>
                </div>

                <!-- Multiplayer Mode -->
                <div class="mode-card bg-gradient-to-r from-blue-600 to-blue-700 rounded-xl p-6 cursor-pointer transform hover:scale-105 transition-all duration-300 border-4 border-yellow-400" 
                     onclick="selectMode('multiplayer')">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <div class="text-5xl">👥</div>
                            <div>
                                <h2 class="racing-font text-2xl font-bold text-white">2+ PLAYERS</h2>
                                <p class="text-yellow-200">Race against friends online</p>
                            </div>
                        </div>
                        <div class="text-white text-2xl">▶️</div>
                    </div>
                </div>
            </div>
        </div>
                </div>
            </div>

            <!-- Start Racing Button -->
            <div class="text-center">
                <button onclick="startRacing()" class="racing-font bg-gradient-to-r from-yellow-400 to-orange-500 text-black px-12 py-4 rounded-xl text-xl font-bold hover:scale-105 transition-transform lambo-glow">
                    🏁 START CRUISIN' 🏁
                </button>
            </div>
        </div>
    </div>

    <script>
        function selectMode(mode) {
            // Simple Mario Kart-style flow: Mode → Racer → Car → Race
            window.location.href = `/select-character?mode=${mode}`;
        }

        // Add some racing sound effects (optional)
        document.addEventListener('DOMContentLoaded', function() {
            // Add hover sound effects to cards
            const cards = document.querySelectorAll('.racing-card');
            cards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    // Could add engine sound effect here
                    this.style.transform = 'scale(1.02)';
                });
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'scale(1)';
                });
            });
        });
    </script>
</body>
</html>
