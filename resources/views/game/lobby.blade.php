<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Racing Lobby - PHP Racer</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&display=swap');
        
        .racing-font {
            font-family: 'Orbitron', monospace;
        }
        
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .lobby-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .race-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }
        
        .race-card:hover {
            border-color: rgba(255, 215, 0, 0.8);
            box-shadow: 0 0 30px rgba(255, 215, 0, 0.4);
        }
        
        .player-slot {
            background: rgba(255, 255, 255, 0.1);
            border: 1px dashed rgba(255, 255, 255, 0.3);
        }
        
        .player-slot.filled {
            background: rgba(34, 197, 94, 0.2);
            border: 1px solid rgba(34, 197, 94, 0.5);
        }
        
        .waiting-pulse {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
    </style>
</head>
<body class="gradient-bg min-h-screen text-white">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="racing-font text-4xl font-black mb-4 text-transparent bg-clip-text bg-gradient-to-r from-yellow-400 to-orange-500">
                🏁 Racing Lobby 🏁
            </h1>
            <p class="text-lg opacity-80">Join a race or create your own!</p>
        </div>

        <!-- Player Selection Summary -->
        <div class="lobby-card rounded-xl p-6 mb-8">
            <h2 class="racing-font text-2xl font-bold mb-4">Your Selection</h2>
            <div class="grid md:grid-cols-2 gap-6">
                <div class="text-center">
                    <div class="text-4xl mb-2">👨‍💻</div>
                    <h3 class="font-bold mb-1">Developer</h3>
                    <p id="selected-developer" class="opacity-80">Select a character first</p>
                </div>
                <div class="text-center">
                    <div class="text-4xl mb-2">🏎️</div>
                    <h3 class="font-bold mb-1">Car</h3>
                    <p id="selected-car" class="opacity-80">Select a car first</p>
                </div>
            </div>
        </div>

        <!-- Create New Race -->
        <div class="lobby-card rounded-xl p-6 mb-8">
            <h2 class="racing-font text-2xl font-bold mb-4">Create New Race</h2>
            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold mb-2">Race Name</label>
                    <input type="text" id="race-name" placeholder="Epic PHP Race" 
                           class="w-full bg-black bg-opacity-30 border border-gray-600 rounded-lg px-4 py-2 text-white placeholder-gray-400">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-2">Track</label>
                    <select id="track-select" class="w-full bg-black bg-opacity-30 border border-gray-600 rounded-lg px-4 py-2 text-white">
                        <option value="Laravel Circuit">Laravel Circuit</option>
                        <option value="PHP Speedway">PHP Speedway</option>
                        <option value="Symfony Grand Prix">Symfony Grand Prix</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-2">Max Players</label>
                    <select id="max-players" class="w-full bg-black bg-opacity-30 border border-gray-600 rounded-lg px-4 py-2 text-white">
                        <option value="2">2 Players</option>
                        <option value="4" selected>4 Players</option>
                        <option value="6">6 Players</option>
                        <option value="8">8 Players</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-2">Laps</label>
                    <select id="laps" class="w-full bg-black bg-opacity-30 border border-gray-600 rounded-lg px-4 py-2 text-white">
                        <option value="1">1 Lap</option>
                        <option value="3" selected>3 Laps</option>
                        <option value="5">5 Laps</option>
                        <option value="10">10 Laps</option>
                    </select>
                </div>
            </div>
            <div class="mt-6 text-center">
                <button onclick="createRace()" class="bg-gradient-to-r from-green-400 to-green-600 text-black px-8 py-3 rounded-lg font-bold hover:scale-105 transition-transform">
                    Create Race 🏁
                </button>
            </div>
        </div>

        <!-- Available Races -->
        <div class="mb-8">
            <h2 class="racing-font text-2xl font-bold mb-6">Available Races</h2>
            
            @if($waitingRaces->count() > 0)
                <div class="grid gap-6">
                    @foreach($waitingRaces as $race)
                    <div class="race-card rounded-xl p-6">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="racing-font text-xl font-bold mb-1">{{ $race->name }}</h3>
                                <p class="text-sm opacity-70">Track: {{ $race->track_name }}</p>
                                <p class="text-sm opacity-70">{{ $race->laps }} Laps</p>
                            </div>
                            <div class="text-right">
                                <div class="text-sm opacity-70">Players</div>
                                <div class="text-xl font-bold">{{ $race->current_players }}/{{ $race->max_players }}</div>
                            </div>
                        </div>

                        <!-- Player Slots -->
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
                            @for($i = 0; $i < $race->max_players; $i++)
                                @if($i < $race->participants->count())
                                    @php $participant = $race->participants[$i] @endphp
                                    <div class="player-slot filled rounded-lg p-3 text-center">
                                        <div class="text-2xl mb-1">👨‍💻</div>
                                        <div class="text-xs font-semibold">{{ $participant->user->name }}</div>
                                        <div class="text-xs opacity-70">{{ $participant->phpDeveloper->name }}</div>
                                        <div class="text-xs opacity-70">{{ $participant->car->name }}</div>
                                    </div>
                                @else
                                    <div class="player-slot rounded-lg p-3 text-center waiting-pulse">
                                        <div class="text-2xl mb-1">❓</div>
                                        <div class="text-xs opacity-50">Waiting...</div>
                                    </div>
                                @endif
                            @endfor
                        </div>

                        <div class="flex justify-between items-center">
                            <div class="text-sm opacity-70">
                                Status: <span class="text-yellow-400 font-semibold">{{ ucfirst($race->status) }}</span>
                            </div>
                            <button onclick="joinRace({{ $race->id }})" 
                                    class="bg-gradient-to-r from-blue-400 to-blue-600 text-white px-6 py-2 rounded-lg font-semibold hover:scale-105 transition-transform">
                                Join Race
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="race-card rounded-xl p-12 text-center">
                    <div class="text-6xl mb-4">🏁</div>
                    <h3 class="text-xl font-bold mb-2">No Active Races</h3>
                    <p class="opacity-70 mb-6">Be the first to create a race and challenge other PHP developers!</p>
                    <button onclick="scrollToCreateRace()" class="bg-gradient-to-r from-yellow-400 to-orange-500 text-black px-6 py-3 rounded-lg font-bold hover:scale-105 transition-transform">
                        Create First Race
                    </button>
                </div>
            @endif
        </div>

        <!-- Quick Race Option -->
        <div class="lobby-card rounded-xl p-6 mb-8 text-center">
            <h2 class="racing-font text-2xl font-bold mb-4">Quick Race</h2>
            <p class="opacity-80 mb-6">Can't wait? Jump into a quick race with AI opponents!</p>
            <button onclick="startQuickRace()" class="bg-gradient-to-r from-purple-400 to-purple-600 text-white px-8 py-3 rounded-lg font-bold hover:scale-105 transition-transform">
                Start Quick Race 🚀
            </button>
        </div>

        <!-- Navigation -->
        <div class="text-center">
            <button onclick="goBack()" class="bg-gray-600 hover:bg-gray-700 px-6 py-3 rounded-lg font-semibold transition-colors">
                ← Back to Car Selection
            </button>
        </div>
    </div>

    <script>
        // Load selected developer and car from session storage
        document.addEventListener('DOMContentLoaded', function() {
            const developerName = sessionStorage.getItem('selectedDeveloperName');
            const carName = sessionStorage.getItem('selectedCarName');
            
            if (developerName) {
                document.getElementById('selected-developer').textContent = developerName;
            }
            
            if (carName) {
                document.getElementById('selected-car').textContent = carName;
            }

            // Auto-refresh lobby every 5 seconds
            setInterval(refreshLobby, 5000);
        });

        function createRace() {
            const raceName = document.getElementById('race-name').value || 'Epic PHP Race';
            const trackName = document.getElementById('track-select').value;
            const maxPlayers = document.getElementById('max-players').value;
            const laps = document.getElementById('laps').value;

            // For demo purposes, create a mock race
            alert(`Creating race: ${raceName} on ${trackName} with ${maxPlayers} players and ${laps} laps!`);
            
            // In a real implementation, this would make an API call
            // fetch('/races', { method: 'POST', ... })
        }

        function joinRace(raceId) {
            const developerId = sessionStorage.getItem('selectedDeveloperId');
            const carId = sessionStorage.getItem('selectedCarId');
            
            if (!developerId || !carId) {
                alert('Please select a developer and car first!');
                return;
            }

            // For demo purposes
            alert(`Joining race ${raceId} with developer ${developerId} and car ${carId}!`);
            
            // In a real implementation, this would make an API call
            // fetch(`/races/${raceId}/join`, { method: 'POST', ... })
        }

        function startQuickRace() {
            const developerId = sessionStorage.getItem('selectedDeveloperId');
            const carId = sessionStorage.getItem('selectedCarId');
            
            if (!developerId || !carId) {
                alert('Please select a developer and car first!');
                window.location.href = '/select-character';
                return;
            }

            // Start a quick race simulation
            alert('Starting quick race! 🏁');
            
            // Create a simple race simulation
            setTimeout(() => {
                showRaceResults();
            }, 3000);
        }

        function showRaceResults() {
            const developerName = sessionStorage.getItem('selectedDeveloperName');
            const carName = sessionStorage.getItem('selectedCarName');
            
            const positions = ['1st 🥇', '2nd 🥈', '3rd 🥉', '4th'];
            const randomPosition = positions[Math.floor(Math.random() * positions.length)];
            
            alert(`Race finished! ${developerName} in ${carName} finished in ${randomPosition} place!`);
        }

        function refreshLobby() {
            // In a real implementation, this would refresh the race list
            console.log('Refreshing lobby...');
        }

        function scrollToCreateRace() {
            document.querySelector('.lobby-card').scrollIntoView({ behavior: 'smooth' });
        }

        function goBack() {
            window.location.href = '/select-car';
        }

        // Add some interactive effects
        document.querySelectorAll('.race-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-3px)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });
    </script>
</body>
</html>
