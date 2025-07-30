<template>
  <div class="max-w-4xl mx-auto">
    <div class="flex justify-between items-center mb-8">
      <button 
        @click="onBack?.()"
        class="flex items-center text-yellow-400 hover:text-yellow-300 transition-colors cursor-pointer"
      >
        <span class="text-2xl mr-2">◀</span> Back
      </button>
      <h2 class="racing-font text-3xl font-bold">Select Your Challenge</h2>
      <div class="w-20"></div> <!-- Spacer for alignment -->
    </div>

    <!-- Selected Character & Car Info -->
    <div class="bg-gray-800 rounded-lg p-4 mb-8 flex items-center justify-between">
      <div class="flex items-center">
        <div class="w-16 h-16 bg-gray-700 rounded-full overflow-hidden mr-4">
          <img 
            :src="selectedCharacter?.image_url" 
            :alt="selectedCharacter?.name"
            class="w-full h-full object-cover"
          />
        </div>
        <div>
          <h3 class="font-bold">{{ selectedCharacter?.name }}</h3>
          <p class="text-sm text-gray-400">{{ selectedCharacter?.special_ability }}</p>
        </div>
      </div>
      <div class="flex items-center">
        <div class="w-16 h-16 bg-gray-900 rounded-lg overflow-hidden mr-4">
            <img 
              :src="selectedCar?.image_url" 
              :alt="selectedCar?.name"
              class="w-full h-full object-cover"
            />
        </div>
        <div>
          <h3 class="font-bold">{{ selectedCar?.name }}</h3>
          <p class="text-sm text-gray-400">Ready to race!</p>
        </div>
      </div>
    </div>

    <!-- Race Stages -->
    <div class="space-y-6">
      <h3 class="text-2xl font-bold mb-6">Championship Stages</h3>
      
      <!-- Stage 1: TJ Miller -->
      <div 
        @click="selectStage('tj-miller')"
        :class="[
          'bg-gray-800 rounded-xl p-6 border-2 transition-all duration-300',
          'cursor-pointer hover:border-yellow-500 transform hover:scale-105'
        ]"
      >
        <div class="flex items-center justify-between">
          <div class="flex items-center">
            <div class="w-20 h-20 bg-gray-700 rounded-full overflow-hidden mr-6">
              <img 
                :src="developers.find(d => d.slug === 'tj-miller')?.image_url || '/storage/tj.png'" 
                alt="TJ Miller"
                class="w-full h-full object-cover"
              />
            </div>
            <div>
              <h4 class="text-xl font-bold text-green-400">Stage 1: TJ Miller</h4>
              <p class="text-gray-400">AI Programming Challenge</p>
              <p class="text-sm text-gray-500">Difficulty: ⭐⭐☆☆☆</p>
            </div>
          </div>
          <div class="text-green-400 text-2xl">✓ UNLOCKED</div>
        </div>
      </div>

      <!-- Stage 2: Nuno Maduro -->
      <div 
        @click="isStageUnlocked('nuno-maduro') ? selectStage('nuno-maduro') : null"
        :class="[
          'rounded-xl p-6 border-2 transition-all duration-300',
          isStageUnlocked('nuno-maduro')
            ? 'bg-gray-800 border-gray-700 cursor-pointer hover:border-yellow-500 transform hover:scale-105'
            : 'bg-gray-700 border-gray-600 cursor-not-allowed opacity-75'
        ]"
      >
        <div class="flex items-center justify-between">
          <div class="flex items-center">
            <div class="w-20 h-20 bg-gray-700 rounded-full overflow-hidden mr-6">
              <img 
                :src="developers.find(d => d.slug === 'nuno-maduro')?.image_url || '/storage/nuno.png'" 
                alt="Nuno Maduro"
                class="w-full h-full object-cover"
                :class="developers.find(d => d.slug === 'nuno-maduro' && d.is_locked) ? 'grayscale' : ''"
              />
            </div>
            <div>
              <h4 class="text-xl font-bold" :class="isStageUnlocked('nuno-maduro') ? 'text-blue-400' : 'text-gray-400'">
                Stage 2: Nuno Maduro
              </h4>
              <p class="text-gray-400">Testing Framework Master</p>
              <p class="text-sm text-gray-500">Difficulty: ⭐⭐⭐☆☆</p>
            </div>
          </div>
          <div :class="isStageUnlocked('nuno-maduro') ? 'text-blue-400' : 'text-gray-500'" class="text-2xl">
            {{ isStageUnlocked('nuno-maduro') ? '✓ UNLOCKED' : '🔒 LOCKED' }}
          </div>
        </div>
      </div>

      <!-- Stage 3: Aaron Francis -->
      <div 
        @click="isStageUnlocked('aaron-francis') ? selectStage('aaron-francis') : null"
        :class="[
          'rounded-xl p-6 border-2 transition-all duration-300',
          isStageUnlocked('aaron-francis')
            ? 'bg-gray-800 border-gray-700 cursor-pointer hover:border-yellow-500 transform hover:scale-105'
            : 'bg-gray-700 border-gray-600 cursor-not-allowed opacity-75'
        ]"
      >
        <div class="flex items-center justify-between">
          <div class="flex items-center">
            <div class="w-20 h-20 bg-gray-700 rounded-full overflow-hidden mr-6">
              <img 
                :src="developers.find(d => d.slug === 'aaron-francis')?.image_url || '/storage/aaron.png'" 
                alt="Aaron Francis"
                class="w-full h-full object-cover"
                :class="developers.find(d => d.slug === 'aaron-francis' && d.is_locked) ? 'grayscale' : ''"
              />
            </div>
            <div>
              <h4 class="text-xl font-bold" :class="isStageUnlocked('aaron-francis') ? 'text-purple-400' : 'text-gray-400'">
                Stage 3: Aaron Francis
              </h4>
              <p class="text-gray-400">Database Optimization Expert</p>
              <p class="text-sm text-gray-500">Difficulty: ⭐⭐⭐⭐☆</p>
            </div>
          </div>
          <div :class="isStageUnlocked('aaron-francis') ? 'text-purple-400' : 'text-gray-500'" class="text-2xl">
            {{ isStageUnlocked('aaron-francis') ? '✓ UNLOCKED' : '🔒 LOCKED' }}
          </div>
        </div>
      </div>

      <!-- Final Stage: Taylor Otwell -->
      <div 
        @click="isStageUnlocked('taylor-otwell') ? selectStage('taylor-otwell') : null"
        :class="[
          'rounded-xl p-6 border-2 transition-all duration-300',
          isStageUnlocked('taylor-otwell')
            ? 'bg-gradient-to-r from-yellow-900 to-yellow-800 border-yellow-500 cursor-pointer hover:border-yellow-400 transform hover:scale-105'
            : 'bg-gray-700 border-gray-600 cursor-not-allowed opacity-75'
        ]"
      >
        <div class="flex items-center justify-between">
          <div class="flex items-center">
            <div class="w-20 h-20 bg-gray-700 rounded-full overflow-hidden mr-6">
              <img 
                :src="developers.find(d => d.slug === 'taylor-otwell')?.image_url || '/storage/taylor.png'" 
                alt="Taylor Otwell"
                class="w-full h-full object-cover"
                :class="developers.find(d => d.slug === 'taylor-otwell' && d.is_locked) ? 'grayscale' : ''"
              />
            </div>
            <div>
              <h4 class="text-xl font-bold" :class="isStageUnlocked('taylor-otwell') ? 'text-yellow-400' : 'text-gray-400'">
                Final Boss: Taylor Otwell
              </h4>
              <p class="text-gray-400">Laravel Creator & Legend</p>
              <p class="text-sm text-gray-500">Difficulty: ⭐⭐⭐⭐⭐</p>
            </div>
          </div>
          <div :class="isStageUnlocked('taylor-otwell') ? 'text-yellow-400' : 'text-gray-500'" class="text-2xl">
            {{ isStageUnlocked('taylor-otwell') ? '👑 READY' : '🔒 LOCKED' }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
interface Developer {
  id: number;
  name: string;
  slug: string;
  bio: string;
  special_ability: string;
  is_locked: boolean;
  image_url: string;
}

interface Car {
  id: number;
  name: string;
  image_url: string;
}

interface Props {
  selectedCharacter: Developer | null;
  selectedCar: Car | null;
  developers: Developer[];
  onBack?: () => void;
  onStageSelect: (stage: string) => void;
}

const props = defineProps<Props>();

const selectStage = (stage: string) => {
  props.onStageSelect(stage);
};

// Get beaten stages from localStorage
const getBeatenStages = (): string[] => {
  const beaten = localStorage.getItem('larakart-beaten-stages');
  return beaten ? JSON.parse(beaten) : [];
};

// Save beaten stage to localStorage
const markStageAsBeaten = (stage: string): void => {
  const beatenStages = getBeatenStages();
  if (!beatenStages.includes(stage)) {
    beatenStages.push(stage);
    localStorage.setItem('larakart-beaten-stages', JSON.stringify(beatenStages));
  }
};

// Stage unlock logic based on localStorage progression
const isStageUnlocked = (stage: string): boolean => {
  const beatenStages = getBeatenStages();
  
  switch (stage) {
    case 'tj-miller':
      return true; // Always unlocked - first stage
    case 'nuno-maduro':
      // Unlocked when TJ Miller has been beaten
      return beatenStages.includes('tj-miller');
    case 'aaron-francis':
      // Unlocked when Nuno Maduro has been beaten
      return beatenStages.includes('nuno-maduro');
    case 'taylor-otwell':
      // Unlocked when Aaron Francis has been beaten
      return beatenStages.includes('aaron-francis');
    default:
      return false;
  }
};

// Expose functions for parent components
defineExpose({
  markStageAsBeaten,
  getBeatenStages
});
</script>
