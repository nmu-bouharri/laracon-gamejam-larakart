<template>
  <div class="min-h-screen bg-gradient-to-b from-blue-900 to-purple-900 text-white p-8">
    <div class="max-w-4xl mx-auto">
      <h1 class="text-4xl font-bold text-center mb-8">Multiplayer Lobby</h1>
      
      <div class="bg-black/30 rounded-lg p-6 mb-6">
        <h2 class="text-2xl font-bold mb-4">Lobby: {{ lobbyKey }}</h2>
        <p class="text-gray-300 mb-4">Players: {{ lobby.players.length }}/4</p>
        
        <div class="grid grid-cols-2 gap-4 mb-6">
          <div v-for="(player, index) in lobby.players" :key="player.id" 
               class="bg-gray-800 rounded-lg p-4 flex items-center justify-between">
            <div>
              <h3 class="font-bold">{{ player.name }}</h3>
              <p class="text-sm text-gray-400">Position {{ player.position }}</p>
              <span v-if="player.is_ai" class="text-xs bg-blue-600 px-2 py-1 rounded">AI</span>
            </div>
            <div v-if="player.ready" class="text-green-400">✓ Ready</div>
            <div v-else class="text-yellow-400">⏳ Waiting</div>
          </div>
          
          <!-- Empty slots -->
          <div v-for="slot in (4 - lobby.players.length)" :key="'empty-' + slot" 
               class="bg-gray-700/50 rounded-lg p-4 flex items-center justify-center border-2 border-dashed border-gray-600">
            <span class="text-gray-500">Waiting for player...</span>
          </div>
        </div>
        
        <div class="flex gap-4 justify-center">
          <button v-if="lobby.players.length < 4" 
                  @click="addAI" 
                  class="bg-blue-600 hover:bg-blue-700 px-6 py-3 rounded-lg font-bold transition-colors">
            Add AI Driver
          </button>
          
          <button v-if="canStartRace" 
                  @click="startRace" 
                  class="bg-green-600 hover:bg-green-700 px-8 py-3 rounded-lg font-bold transition-colors">
            Start Race
          </button>
          
          <button @click="leaveLobby" 
                  class="bg-red-600 hover:bg-red-700 px-6 py-3 rounded-lg font-bold transition-colors">
            Leave Lobby
          </button>
        </div>
      </div>
      
      <div v-if="lobby.status === 'countdown' || lobby.status === 'racing'" class="text-center">
        <h2 class="text-2xl font-bold text-green-400 mb-4">Race Starting!</h2>
        <p class="text-gray-300">Get ready to race...</p>
      </div>
    </div>
    
    <!-- Countdown Overlay -->
    <div v-if="showCountdown" class="fixed inset-0 bg-black/80 flex items-center justify-center z-50">
      <div class="text-center">
        <div class="text-9xl font-bold text-white mb-4 animate-pulse">
          {{ countdownNumber }}
        </div>
        <p class="text-2xl text-gray-300">Get Ready!</p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import axios from 'axios'
// @ts-ignore
import Echo from 'laravel-echo'
// @ts-ignore

const emit = defineEmits(['startRace', 'leaveLobby'])

const lobbyKey = ref('')
const playerId = ref('')
const countdownNumber = ref<string | number | null>(null)
const showCountdown = ref(false)
let echo: Echo | null = null
interface Player {
  id: string;
  name: string;
  position: number;
  ready: boolean;
  is_ai: boolean;
}

interface Lobby {
  key: string;
  players: Player[];
  status: string;
  created_at: string | null;
}

const lobby = ref<Lobby>({
  key: '',
  players: [],
  status: 'waiting',
  created_at: null
})

const canStartRace = computed(() => {
  return lobby.value.players.length >= 2 && lobby.value.status === 'waiting'
})

let pollInterval: number | null = null

const initializeWebSocket = () => {
  if (typeof window !== 'undefined') {
    echo = new Echo({
      broadcaster: 'reverb',
      key: import.meta.env.VITE_REVERB_APP_KEY,
      wsHost: import.meta.env.VITE_REVERB_HOST,
      wsPort: import.meta.env.VITE_REVERB_PORT,
      wssPort: import.meta.env.VITE_REVERB_PORT,
      forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
      enabledTransports: ['ws', 'wss'],
    })
  }
}

const subscribeToLobbyEvents = () => {
  if (!echo || !lobbyKey.value) return
  
  echo.channel(`lobby.${lobbyKey.value}`)
    .listen('.race.countdown', (e: any) => {
      showCountdown.value = true
      countdownNumber.value = e.count
      
      if (e.count === 'GO!') {
        setTimeout(() => {
          showCountdown.value = false
        }, 1000)
      }
    })
    .listen('.race.started', (e: any) => {
      // Emit event to parent to start the race
      emit('startRace', {
        lobbyKey: lobbyKey.value,
        players: e.players
      })
    })
}

const joinLobby = async () => {
  try {
    const response = await axios.post('/api/lobby/join', {
      player_id: playerId.value,
      player_name: 'Player ' + Math.floor(Math.random() * 1000)
    })
    
    lobbyKey.value = response.data.lobby_key
    playerId.value = response.data.player_id
    lobby.value = response.data.lobby
    
    // Subscribe to websocket events
    subscribeToLobbyEvents()
    
    // Start polling for lobby updates
    startPolling()
  } catch (error) {
    console.error('Failed to join lobby:', error)
  }
}

const addAI = async () => {
  try {
    const response = await axios.post('/api/lobby/add-ai', {
      lobby_key: lobbyKey.value
    })
    
    lobby.value = response.data.lobby
  } catch (error) {
    console.error('Failed to add AI:', error)
  }
}

const startRace = async () => {
  try {
    const response = await axios.post('/api/lobby/start-race', {
      lobby_key: lobbyKey.value
    })
    
    lobby.value = response.data.lobby
    // Race start will be handled by websocket events
  } catch (error) {
    console.error('Failed to start race:', error)
  }
}

const leaveLobby = () => {
  stopPolling()
  if (echo && lobbyKey.value) {
    echo.leaveChannel(`lobby.${lobbyKey.value}`)
  }
  emit('leaveLobby')
}

const pollLobby = async () => {
  if (!lobbyKey.value) return
  
  try {
    const response = await axios.get(`/api/lobby/${lobbyKey.value}`)
    lobby.value = response.data.lobby
    
    // If race started, emit event
    if (lobby.value.status === 'racing') {
      setTimeout(() => {
        emit('startRace', {
          lobbyKey: lobbyKey.value,
          players: lobby.value.players
        })
      }, 2000) // 2 second delay for "Get ready" message
    }
  } catch (error) {
    console.error('Failed to poll lobby:', error)
  }
}

const startPolling = () => {
  pollInterval = setInterval(pollLobby, 1000) // Poll every second
}

const stopPolling = () => {
  if (pollInterval) {
    clearInterval(pollInterval)
    pollInterval = null
  }
}

onMounted(() => {
  initializeWebSocket()
  joinLobby()
})

onUnmounted(() => {
  stopPolling()
  if (echo && lobbyKey.value) {
    echo.leaveChannel(`lobby.${lobbyKey.value}`)
  }
})
</script>
