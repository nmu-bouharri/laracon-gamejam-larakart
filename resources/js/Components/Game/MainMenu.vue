<script setup lang="ts">
const emit = defineEmits(['start-game', 'quick-race']);

const startSinglePlayer = () => {
  emit('start-game', 'single');
};

// Multiplayer removed - focusing on AI racing

const startQuickRace = () => {
  emit('quick-race');
};
</script>

<template>
  <div class="max-w-4xl mx-auto">
    <div class="max-w-2xl mx-auto space-y-6">
      <img class="w-full" alt="screenshot of game" :src="'/storage/screenshot.png'" />

      <!-- Game Description -->
      <div class="text-center bg-gray-800 rounded-lg p-4 border border-gray-600">
        <p class="text-gray-300 text-lg">
          🏁 <strong>Larakart:</strong> Mario Kart-style racing with Laravel legends! Race as Aaron, TJ, Nuno & Taylor. Built with Vue3/Laravel + shameless AI assistance. 🤖
        </p>
        <p class="text-gray-500 text-sm">
          P.S. This was written by AI.
        </p>
      </div>

      <!-- Single Player Mode -->
      <button
        @click="startSinglePlayer"
        class="w-full text-left bg-gradient-to-r from-red-400 to-red-500 rounded-xl p-6 cursor-pointer transform hover:scale-105 transition-all duration-300 border-4 border-yellow-400 focus:outline-none focus:ring-4 focus:ring-yellow-500 focus:ring-opacity-50"
      >
        <div class="flex items-center justify-between">
          <div class="flex items-center space-x-4">
            <div class="text-5xl -translate-y-2.5">🏎️</div>
            <div>
              <h2 class="racing-font text-2xl font-bold text-white">
                SINGLE PLAYER
              </h2>
            </div>
          </div>
        </div>
      </button>

      

      <!-- Multiplayer removed - focusing on AI racing -->
        
      <!-- Quick Race for Testing -->
    </div>
  </div>
</template>
