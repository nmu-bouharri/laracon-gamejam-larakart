<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';

const props = defineProps<{
  selectedCharacter: any;
  selectedCar: any;
  onBack?: () => void;
}>();

const emit = defineEmits(['start-race']);

const players = ref([
  {
    id: 1,
    name: 'You',
    character: props.selectedCharacter,
    car: props.selectedCar,
    isReady: true
  },
  // AI or other players will be added here
]);

const countdown = ref(5);
let countdownInterval: number | null = null;

const startCountdown = () => {
  countdownInterval = window.setInterval(() => {
    countdown.value--;
    if (countdown.value <= 0) {
      clearInterval(countdownInterval!);
      emit('start-race');
    }
  }, 1000);
};

const toggleReady = () => {
  // In a real implementation, this would emit to the server
  const player = players.value.find(p => p.id === 1);
  if (player) {
    player.isReady = !player.isReady;
  }
};

// Simulate other players joining (in a real app, this would come from WebSocket)
onMounted(() => {
  const aiPlayers = [
    {
      id: 2,
      name: 'AI Racer 1',
      character: { name: 'AI Dev', image_url: '/images/ai1.jpg' },
      car: { name: 'Standard Kart', image_url: '/images/kart1.png' },
      isReady: false
    },
    {
      id: 3,
      name: 'AI Racer 2',
      character: { name: 'AI Dev', image_url: '/images/ai2.jpg' },
      car: { name: 'Standard Kart', image_url: '/images/kart1.png' },
      isReady: false
    }
  ];

  // Add AI players after a short delay to simulate joining
  setTimeout(() => {
    players.value = [...players.value, ...aiPlayers];
    
    // Simulate AI players becoming ready
    setTimeout(() => {
      players.value.forEach(player => {
        if (player.id !== 1) {
          player.isReady = true;
        }
      });
    }, 1000);
  }, 1000);

  return () => {
    if (countdownInterval) {
      clearInterval(countdownInterval);
    }
  };
});

onUnmounted(() => {
  if (countdownInterval) {
    clearInterval(countdownInterval);
  }
});
</script>

<template>
  <div class="max-w-4xl mx-auto">
    <div class="flex justify-between items-center mb-8">
      <button 
        @click="onBack?.()"
        class="flex items-center text-yellow-400 hover:text-yellow-300 transition-colors cursor-pointer"
      >
        <span class="text-2xl mr-2">◀</span> Back
      </button>
      <h2 class="racing-font text-3xl font-bold">Race Lobby</h2>
      <div class="w-20"></div> <!-- Spacer for alignment -->
    </div>

    <div class="bg-gray-800 rounded-xl p-6 mb-8">
      <h3 class="text-xl font-semibold mb-4">Race Settings</h3>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <h4 class="text-gray-400 mb-2">Track</h4>
          <div class="bg-gray-900 p-4 rounded-lg">
            <div class="text-center">
              <div class="text-5xl mb-2">🏁</div>
              <div class="font-medium">Laracon Speedway</div>
              <div class="text-sm text-gray-400">3 Laps • Medium Difficulty</div>
            </div>
          </div>
        </div>
        <div>
          <h4 class="text-gray-400 mb-2">Race Code</h4>
          <div class="bg-gray-900 p-4 rounded-lg flex items-center justify-between">
            <div>
              <div class="font-mono text-2xl font-bold tracking-wider">LAR4C0N</div>
              <div class="text-sm text-gray-400">Share this code to invite friends</div>
            </div>
            <button class="bg-blue-600 hover:bg-blue-500 text-white px-4 py-2 rounded-lg">
              Copy
            </button>
          </div>
        </div>
      </div>
    </div>

    <div class="bg-gray-800 rounded-xl p-6 mb-8">
      <h3 class="text-xl font-semibold mb-4">Players ({{ players.length }}/4)</h3>
      
      <div class="space-y-4">
        <div 
          v-for="player in players" 
          :key="player.id"
          class="bg-gray-900 rounded-lg p-4 flex items-center justify-between"
          :class="{ 'border-2 border-yellow-500': player.id === 1 }"
        >
          <div class="flex items-center">
            <div class="w-12 h-12 bg-gray-800 rounded-full overflow-hidden mr-4">
              <img 
                :src="player.character.image_url" 
                :alt="player.character.name"
                class="w-full h-full object-cover"
              />
            </div>
            <div>
              <div class="font-medium">{{ player.name }}</div>
              <div class="text-sm text-gray-400">{{ player.car.name }}</div>
            </div>
          </div>
          
          <div>
            <span 
              v-if="player.isReady" 
              class="bg-green-600 text-green-100 text-xs px-2 py-1 rounded-full"
            >
              READY
            </span>
            <span 
              v-else 
              class="bg-yellow-600 text-yellow-100 text-xs px-2 py-1 rounded-full"
            >
              NOT READY
            </span>
          </div>
        </div>
      </div>
      
      <div class="mt-8 pt-6 border-t border-gray-700 flex justify-between items-center">
        <div>
          <div v-if="players.length < 4" class="text-gray-400 text-sm">
            Waiting for players... ({{ 4 - players.length }} more needed)
          </div>
          <div v-else class="text-green-400 font-medium">
            All players ready! Starting soon...
          </div>
        </div>
        
        <div class="space-x-4">
          <button 
            @click="onBack?.()"
            class="px-6 py-2 bg-gray-700 hover:bg-gray-600 rounded-lg transition-colors"
          >
            Cancel
          </button>
          <button 
            @click="toggleReady"
            class="px-6 py-2 bg-yellow-600 hover:bg-yellow-500 text-white rounded-lg transition-colors"
            :class="{ 'opacity-50': players.length < 2 }"
            :disabled="players.length < 2"
          >
            {{ players.find(p => p.id === 1)?.isReady ? 'Not Ready' : 'Ready Up' }}
          </button>
        </div>
      </div>
    </div>
    
    <!-- Countdown overlay -->
    <div 
      v-if="countdown < 5"
      class="fixed inset-0 bg-black bg-opacity-80 flex items-center justify-center z-50"
    >
      <div class="text-center">
        <div class="text-9xl font-bold text-yellow-400 mb-4">
          {{ countdown > 0 ? countdown : 'GO!' }}
        </div>
        <div class="text-xl text-gray-300">
          Race starting in {{ countdown }} second{{ countdown !== 1 ? 's' : '' }}...
        </div>
      </div>
    </div>
  </div>
</template>
