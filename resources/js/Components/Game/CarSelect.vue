s<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
  cars: Array<{
    id: number;
    name: string;
    speed: number;
    acceleration: number;
    handling: number;
    drift: number;
    is_locked: boolean;
    image_url: string;
    slug?: string;
  }>;
  onCarSelect: (car: any) => void;
  selectedCharacter: any;
  onBack?: () => void;
}>();

// Check if Taylor's Lambo is unlocked from localStorage
const isLamboUnlocked = computed(() => {
  return localStorage.getItem('larakart-lambo-unlocked') === 'true';
});

// Override car lock status based on localStorage
const getCarWithUnlockStatus = (car: any) => {
  // If this is Taylor's Lambo and it's been unlocked, override the lock status
  if (car.name?.toLowerCase().includes('lambo') && isLamboUnlocked.value) {
    return { ...car, is_locked: false };
  }
  return car;
};

const allCars = computed(() => {
  return props.cars.map(car => getCarWithUnlockStatus(car));
});

const getStatClass = (value: number) => {
  if (value >= 90) return 'text-green-400';
  if (value >= 80) return 'text-yellow-400';
  return 'text-red-400';
};

const getStatBars = (value: number) => {
  const bars = Math.ceil(value / 20); // Convert 0-100 to 1-5 bars
  return '⚡'.repeat(bars);
};
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
      <h2 class="racing-font text-3xl font-bold">Select Your Vehicle</h2>
      <div class="w-20"></div> <!-- Spacer for alignment -->
    </div>

    <!-- Selected Character Info -->
    <div class="bg-gray-800 rounded-lg p-4 mb-8 flex items-center">
      <div class="w-16 h-16 bg-gray-700 rounded-full overflow-hidden mr-4">
        <img 
          :src="selectedCharacter.image_url" 
          :alt="selectedCharacter.name"
          class="w-full h-full object-cover"
        />
      </div>
      <div>
        <h3 class="font-bold text-lg">{{ selectedCharacter.name }}</h3>
        <p class="text-sm text-gray-400">{{ selectedCharacter.special_ability }}</p>
      </div>
    </div>

    <!-- All Cars -->
    <div class="mb-12">
      <h3 class="text-xl font-semibold mb-4">Select Your Vehicle</h3>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div 
          v-for="car in allCars" 
          :key="car.id"
          @click="car.is_locked ? null : onCarSelect(car)"
          :class="[
            'rounded-xl p-6 transition-all duration-300 border-2',
            car.is_locked 
              ? 'bg-gray-700 border-gray-600 cursor-not-allowed opacity-75'
              : 'bg-gray-800 border-gray-700 hover:border-yellow-500 cursor-pointer transform hover:scale-105'
          ]"
        >
          <div class="mb-4 bg-gray-900 rounded-lg overflow-hidden aspect-square">
            <img 
              :src="car.image_url" 
              :alt="car.name"
              class="w-full h-full object-cover"
            />
          </div>
          
          <h4 class="text-xl font-bold mb-2" :class="car.is_locked ? 'text-gray-400' : 'text-white'">
            {{ car.name }}
            <span v-if="car.is_locked" class="ml-2 text-sm text-gray-500">🔒 LOCKED</span>
          </h4>
          
          <!-- Stats -->
          <div class="space-y-2 text-sm">
            <div class="flex justify-between">
              <span>Speed:</span>
              <span :class="getStatClass(car.speed)">
                {{ getStatBars(car.speed) }}
              </span>
            </div>
            <div class="flex justify-between">
              <span>Acceleration:</span>
              <span :class="getStatClass(car.acceleration)">
                {{ getStatBars(car.acceleration) }}
              </span>
            </div>
            <div class="flex justify-between">
              <span>Handling:</span>
              <span :class="getStatClass(car.handling)">
                {{ getStatBars(car.handling) }}
              </span>
            </div>
            <div class="flex justify-between">
              <span>Drift:</span>
              <span :class="getStatClass(car.drift)">
                {{ getStatBars(car.drift) }}
              </span>
            </div>
          </div>
          
          <div class="mt-4 pt-4 border-t border-gray-700">
            <button 
              class="w-full bg-yellow-600 hover:bg-yellow-500 text-white py-2 px-4 rounded-lg font-medium transition-colors"
            >
              Select Vehicle
            </button>
          </div>
        </div>
      </div>
    </div>


  </div>
</template>
