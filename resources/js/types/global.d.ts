// Global TypeScript declarations

// Import meta glob for Vite
interface ImportMeta {
  glob: (pattern: string) => Record<string, any>;
}

// Vue component with layout
interface ComponentWithLayout {
  layout?: any;
  default: any;
}

// Game state types
type GameMode = 'single' | 'multiplayer';

type Developer = {
  id: number;
  name: string;
  bio: string;
  special_ability: string;
  popularity_rating: number;
  is_featured: boolean;
  is_locked: boolean;
  image_url: string;
};

type Car = {
  id: number;
  name: string;
  speed: number;
  acceleration: number;
  handling: number;
  drift: number;
  is_locked: boolean;
  image_url: string;
};

type RaceParticipant = {
  id: number;
  name: string;
  character: Pick<Developer, 'name' | 'image_url'>;
  car: Pick<Car, 'name' | 'image_url'>;
  isReady: boolean;
};

type RaceResult = {
  position: number;
  time: number;
  laps: number;
  isWinner: boolean;
};

// Extend Window interface for global variables
declare global {
  interface Window {
    // Add any global variables here if needed
  }
}

export {}; // This file needs to be a module
