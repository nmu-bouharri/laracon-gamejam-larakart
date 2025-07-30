<script setup lang="ts"></script>

<template>
    <!-- Header -->
    <div class="text-center mb-12">
        <h1
            class="racing-font text-6xl font-black mb-4 text-transparent bg-clip-text bg-gradient-to-r from-yellow-400 to-orange-500"
        >
            🏁 Larakart 🏁
        </h1>
    </div>

    <!-- Main Menu -->
    <div class="max-w-4xl mx-auto">
        <div class="text-center mb-12">
            <h2 class="racing-font text-3xl font-bold mb-8">
                Choose Your Game Mode
            </h2>
            <!-- Game Modes - Mario Kart Style -->
            <div class="max-w-2xl mx-auto">
                <div class="space-y-6">
                    <!-- Single Player Mode -->
                    <div
                        class="mode-card bg-gradient-to-r from-red-600 to-red-700 rounded-xl p-6 cursor-pointer transform hover:scale-105 transition-all duration-300 border-4 border-yellow-400"
                        onclick="selectMode('singleplayer')"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-4">
                                <div class="text-5xl">🏁</div>
                                <div>
                                    <h2
                                        class="racing-font text-2xl font-bold text-white"
                                    >
                                        1 PLAYER
                                    </h2>
                                    <p class="text-yellow-200">
                                        Race against AI • Unlock Taylor!
                                    </p>
                                </div>
                            </div>
                            <div class="text-white text-2xl">▶️</div>
                        </div>
                    </div>

                    <!-- Multiplayer Mode -->
                    <div
                        class="mode-card bg-gradient-to-r from-blue-600 to-blue-700 rounded-xl p-6 cursor-pointer transform hover:scale-105 transition-all duration-300 border-4 border-yellow-400"
                        onclick="selectMode('multiplayer')"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-4">
                                <div class="text-5xl">👥</div>
                                <div>
                                    <h2
                                        class="racing-font text-2xl font-bold text-white"
                                    >
                                        2+ PLAYERS
                                    </h2>
                                    <p class="text-yellow-200">
                                        Race against friends online
                                    </p>
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
        <button
            onclick="startRacing()"
            class="racing-font bg-gradient-to-r from-yellow-400 to-orange-500 text-black px-12 py-4 rounded-xl text-xl font-bold hover:scale-105 transition-transform lambo-glow"
        >
            🏁 START CRUISIN' 🏁
        </button>
    </div>
</template>
