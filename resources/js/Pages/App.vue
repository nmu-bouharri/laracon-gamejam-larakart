<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import axios from 'axios';
import MainMenu from '@/Components/Game/MainMenu.vue';
import CharacterSelect from '@/Components/Game/CharacterSelect.vue';
import CarSelect from '@/Components/Game/CarSelect.vue';
import StageSelect from '@/Components/Game/StageSelect.vue';
import RaceLobby from '@/Components/Game/RaceLobby.vue';
// MultiplayerLobby removed - focusing on AI racing
import RaceTrack from '@/Components/Game/RaceTrack.vue';

// Game state
const gameState = ref('main-menu'); // main-menu, character-select, car-select, stage-select, race (lobby skipped)
const selectedStage = ref('');
const selectedMode = ref(''); // 'single' only - multiplayer removed
const selectedCharacter = ref(null);
const selectedCar = ref(null);
const raceData = ref(null);

// Game data
const developers = ref([]);
const cars = ref([]);

// Fetch initial game data
const fetchGameData = async () => {
  try {
    const [devsRes, carsRes] = await Promise.all([
      axios.get('/api/developers'),
      axios.get('/api/cars')
    ]);
    
    developers.value = devsRes.data;
    cars.value = carsRes.data;
  } catch (error) {
    console.error('Error fetching game data:', error);
  }
};

// Navigation handlers
const startGame = (mode: string) => {
  selectedMode.value = mode;
  gameState.value = 'character-select';
};

const onCharacterSelect = (character: any) => {
  selectedCharacter.value = character;
  gameState.value = 'car-select';
};

const onCarSelect = (car: any) => {
  selectedCar.value = car;
  gameState.value = 'stage-select';
};

const onStageSelect = (stage: string) => {
  selectedStage.value = stage;
  gameState.value = 'race'; // Skip lobby, go directly to race
};

// Back navigation handler
const goBack = () => {
  switch (gameState.value) {
    case 'character-select':
      gameState.value = 'main-menu';
      break;
    case 'car-select':
      gameState.value = 'character-select';
      break;
    case 'stage-select':
      gameState.value = 'car-select';
      break;
    case 'lobby':
      gameState.value = 'car-select';
      break;
    case 'race':
      gameState.value = 'lobby';
      break;
    default:
      gameState.value = 'main-menu';
  }
};

const startSinglePlayerRace = () => {
  // TODO: Implement single player race setup
  console.log('Starting single player race with:', {
    character: selectedCharacter.value,
    car: selectedCar.value
  });
};

const startQuickRace = () => {
  // Use default character and car for quick testing
  selectedMode.value = 'single';
  selectedCharacter.value = developers.value[0] || { 
    id: 1, 
    name: 'Test Driver', 
    special_ability: 'Quick Testing',
    image_url: '/images/default-driver.jpg'
  };
  selectedCar.value = cars.value[0] || {
    id: 1,
    name: 'Test Car',
    speed: 85,
    acceleration: 80,
    handling: 75,
    image_url: '/images/default-car.jpg'
  };
  gameState.value = 'race';
};

// Reset game progress (for testing)
const resetProgress = () => {
  localStorage.removeItem('larakart-beaten-stages');
  localStorage.removeItem('larakart-unlocked-characters');
  localStorage.removeItem('larakart-lambo-unlocked');
  console.log('Game progress reset! All stages, characters, and Lambo locked again.');
};

// Handle race completion
const onRaceComplete = async (results: any) => {
  console.log('Race completed with results:', results);
  
  // If player won, save the beaten stage and unlock character
  if (results.isWinner && selectedStage.value) {
    try {
      // Save beaten stage to localStorage
      const beatenStages = JSON.parse(localStorage.getItem('larakart-beaten-stages') || '[]');
      if (!beatenStages.includes(selectedStage.value)) {
        beatenStages.push(selectedStage.value);
        localStorage.setItem('larakart-beaten-stages', JSON.stringify(beatenStages));
        console.log(`Stage ${selectedStage.value} marked as beaten!`);
      }
      
      // Unlock the character you just beat
      const unlockedCharacters = JSON.parse(localStorage.getItem('larakart-unlocked-characters') || '["aaron-francis"]');
      if (!unlockedCharacters.includes(selectedStage.value)) {
        unlockedCharacters.push(selectedStage.value);
        localStorage.setItem('larakart-unlocked-characters', JSON.stringify(unlockedCharacters));
        console.log(`Character ${selectedStage.value} unlocked!`);
      }
      
      // Special handling for beating Taylor - unlock his Lambo too!
      if (selectedStage.value === 'taylor-otwell') {
        // Unlock Taylor's Lambo
        localStorage.setItem('larakart-lambo-unlocked', 'true');
        
        console.log('🎉 CHAMPIONSHIP COMPLETE! Taylor Otwell and his Lambo unlocked!');
        
        // Send to character select to show the unlocked rewards
        gameState.value = 'character-select';
        return;
      }
    } catch (error) {
      console.error('Failed to save beaten stage:', error);
    }
  }
  
  // Return to stage select to show newly unlocked stages
  gameState.value = 'stage-select';
};

// Initialize game data
onMounted(() => {
  fetchGameData();
});
</script>

<template>
  <Head title="Larakart" />
  
  <div class="min-h-screen bg-gray-900 text-white">
    <!-- Game Container -->
    <div class="container mx-auto px-4 py-8">
      <!-- Game Title -->
      <header class="text-center mb-8">
        <h1 class="racing-font text-6xl font-black mb-2 text-transparent bg-clip-text bg-gradient-to-r from-yellow-400 to-orange-500">
          🏁 Larakart 🏁
        </h1>
      </header>

      <!-- Game Content -->
      <main class="game-content">
        <Transition name="fade" mode="out-in">
          <component 
            :is="{
              'main-menu': MainMenu,
              'character-select': CharacterSelect,
              'car-select': CarSelect,
              'stage-select': StageSelect,
              'lobby': RaceLobby,
              'race': RaceTrack
            }[gameState] || 'div'"
            
            v-bind="{
              developers,
              cars,
              selectedMode,
              selectedCharacter,
              selectedCar,
              selectedStage,
              onStartGame: startGame,
              onCharacterSelect,
              onCarSelect,
              onStageSelect,
              onQuickRace: startQuickRace,
              onBack: goBack,
              onRaceComplete
            }"
          />
        </Transition>
      </main>
    </div>
  </div>
</template>

<style scoped>
.game-content {
  min-height: 60vh;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* Transitions */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

/* Racing font */
@font-face {
  font-family: 'Racing Sans One';
  src: url('/fonts/RacingSansOne-Regular.ttf') format('truetype');
  font-weight: normal;
  font-style: normal;
  font-display: swap;
}

.racing-font {
  font-family: 'Racing Sans One', sans-serif;
  letter-spacing: 1px;
  text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
}
</style>
