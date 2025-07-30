<script setup lang="ts">
import { computed, ref } from 'vue';

const props = defineProps<{
  developers: Array<{
    id: number;
    name: string;
    bio: string;
    special_ability: string;
    is_locked: boolean;
    is_featured?: boolean;
    image_url: string;
    slug?: string;
  }>;
  onCharacterSelect: (character: any) => void;
  onBack?: () => void;
}>();

// Get unlocked characters from localStorage
const getUnlockedCharacters = (): string[] => {
  const unlocked = localStorage.getItem('larakart-unlocked-characters');
  return unlocked ? JSON.parse(unlocked) : ['aaron-francis']; // Aaron starts unlocked
};

// Save unlocked character to localStorage
const markCharacterAsUnlocked = (characterSlug: string): void => {
  const unlockedCharacters = getUnlockedCharacters();
  if (!unlockedCharacters.includes(characterSlug)) {
    unlockedCharacters.push(characterSlug);
    localStorage.setItem('larakart-unlocked-characters', JSON.stringify(unlockedCharacters));
  }
};

// Character unlock logic based on localStorage progression
const isCharacterUnlocked = (developer: any): boolean => {
  const unlockedCharacters = getUnlockedCharacters();
  
  // Check by slug first, then fall back to name matching
  if (developer.slug) {
    return unlockedCharacters.includes(developer.slug);
  }
  
  // Fallback name matching for characters without slug
  const name = developer.name?.toLowerCase();
  if (name?.includes('aaron')) return unlockedCharacters.includes('aaron-francis');
  if (name?.includes('tj') || name?.includes('miller')) return unlockedCharacters.includes('tj-miller');
  if (name?.includes('nuno')) return unlockedCharacters.includes('nuno-maduro');
  if (name?.includes('taylor')) return unlockedCharacters.includes('taylor-otwell');
  
  return false;
};

// Override developer lock status based on localStorage
const getDeveloperWithUnlockStatus = (developer: any) => {
  const unlocked = isCharacterUnlocked(developer);
  return { ...developer, is_locked: !unlocked };
};

// Manual unlock function for testing
const unlockTaylorManually = () => {
  markCharacterAsUnlocked('taylor-otwell');
  localStorage.setItem('larakart-lambo-unlocked', 'true');
  console.log('Taylor and Lambo manually unlocked!');
};

// Expose functions for parent components and testing
defineExpose({
  markCharacterAsUnlocked,
  getUnlockedCharacters
});
(window as any).unlockTaylorManually = unlockTaylorManually;

const featuredDeveloper = computed(() => {
  const featured = props.developers.find(dev => dev.is_featured);
  return featured ? getDeveloperWithUnlockStatus(featured) : featured;
});

const allDevelopers = computed(() => {
  return props.developers
    .filter(dev => dev !== props.developers.find(d => d.is_featured))
    .map(dev => getDeveloperWithUnlockStatus(dev));
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
      <h2 class="racing-font text-3xl font-bold">Select Your Developer</h2>
      <div class="w-20"></div> <!-- Spacer for alignment -->
    </div>

    <!-- Featured Developer -->
    <div v-if="featuredDeveloper" class="mb-12">
      <h3 class="text-xl font-semibold mb-4 text-yellow-400">Featured</h3>
      <div 
        @click="featuredDeveloper.is_locked ? null : onCharacterSelect(featuredDeveloper)"
        :class="[
          'rounded-xl p-6 transition-all duration-300 border-4',
          featuredDeveloper.is_locked 
            ? 'bg-gradient-to-r from-gray-700 to-gray-800 border-gray-500 cursor-not-allowed opacity-75'
            : 'bg-gradient-to-r from-yellow-700 to-yellow-800 border-yellow-400 cursor-pointer transform hover:scale-105'
        ]"
      >
        <div class="flex items-center">
          <div class="w-32 h-32 bg-gray-800 rounded-lg overflow-hidden mr-6">
            <img 
              :src="featuredDeveloper.image_url" 
              :alt="featuredDeveloper.name"
              class="w-full h-full object-cover"
            />
          </div>
          <div class="flex-1">
            <h3 class="racing-font text-2xl font-bold" :class="featuredDeveloper.is_locked ? 'text-gray-400' : 'text-yellow-400'">
              {{ featuredDeveloper.name }}
              <span class="ml-2 text-sm" :class="featuredDeveloper.is_locked ? 'text-gray-500' : 'text-yellow-200'">
                {{ featuredDeveloper.is_locked ? '🔒 LOCKED' : '★ Featured' }}
              </span>
            </h3>
            <p class="text-gray-300 my-2">{{ featuredDeveloper.bio }}</p>
            <div class="mt-3">
              <span class="inline-block bg-yellow-600 text-yellow-100 text-xs px-2 py-1 rounded">
                Special: {{ featuredDeveloper.special_ability }}
              </span>
            </div>
          </div>
          <div class="text-3xl" :class="featuredDeveloper.is_locked ? 'text-gray-500' : 'text-yellow-400'">
          </div>
        </div>
      </div>
    </div>

    <!-- All Developers -->
    <div class="mb-12">
      <h3 class="text-xl font-semibold mb-4">All Developers</h3>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div 
          v-for="developer in allDevelopers" 
          :key="developer.id"
          @click="developer.is_locked ? null : onCharacterSelect(developer)"
          :class="[
            'rounded-lg p-4 transition-all duration-300 border-2',
            developer.is_locked 
              ? 'bg-gray-700 border-gray-600 cursor-not-allowed opacity-75'
              : 'bg-gray-800 border-gray-700 cursor-pointer transform hover:scale-105'
          ]"
        >
          <div class="flex items-center">
            <div class="w-20 h-20 bg-gray-700 rounded-full overflow-hidden mr-4">
              <img 
                :src="developer.image_url" 
                :alt="developer.name"
                class="w-full h-full object-cover"
              />
            </div>
            <div class="flex-1">
              <h4 class="font-bold text-lg" :class="developer.is_locked ? 'text-gray-400' : 'text-white'">
                {{ developer.name }}
                <span v-if="developer.is_locked" class="ml-2 text-sm text-gray-500">🔒 LOCKED</span>
              </h4>
              <p class="text-sm text-gray-400">{{ developer.special_ability }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>


  </div>
</template>
