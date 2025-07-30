<script setup lang="ts">
import { ref, onMounted, onUnmounted, watch } from 'vue';
import * as THREE from 'three';
import axios from 'axios';

const props = defineProps<{
  selectedCharacter: any;
  selectedCar: any;
  selectedStage?: string;
  isMultiplayer?: boolean;
  onBack?: () => void;
  onRaceComplete?: (results: any) => void;
}>();

const emit = defineEmits(['race-complete']);

// Game state
const raceStarted = ref(false);
const raceTime = ref(0);
const currentLap = ref(1);
const totalLaps = 3;
const position = ref(1);
const totalRacers = 4;
const raceFinished = ref(false);
const speed = ref(0);
const maxSpeed = 0.56; // Reduced by 30% (0.8 * 0.7)
const acceleration = 0.014; // Reduced by 30% (0.02 * 0.7)
const friction = 0.95;
const turnSpeed = 0.015; // Reduced for wider turning radius

// Track player progress
const checkpoints = ref<boolean[]>([]);
const lastCheckpoint = ref(-1);
const lapTime = ref(0);
const bestLapTime = ref(Infinity);

// Three.js setup
let scene: THREE.Scene;
let camera: THREE.PerspectiveCamera;
let renderer: THREE.WebGLRenderer;
let controls;
let playerKart: THREE.Group;
let aiKarts: THREE.Group[] = [];
let track: THREE.Group;
let animationFrameId: number;
let trackCurve: THREE.CatmullRomCurve3;
let checkpointMeshes: THREE.Mesh[] = [];

// Track dimensions
let trackRadius = 60;
let trackWidth = 20;

// Random winding race track SVG path
const defaultTrackSVG = `
  <svg viewBox="-120 -80 240 160" xmlns="http://www.w3.org/2000/svg">
    <path d="M -80,0 
             C -80,-40 -40,-60 0,-50
             C 40,-40 80,-20 90,0
             C 100,20 80,40 60,50
             C 40,60 20,45 10,30
             C 0,15 -10,25 -20,35
             C -30,45 -50,50 -70,40
             C -90,30 -95,10 -80,0 Z" 
          fill="none" stroke="black" stroke-width="2"/>
  </svg>
`;

// Function to parse SVG path and generate track points
const generateTrackFromSVG = (svgString: string) => {
  const parser = new DOMParser();
  const svgDoc = parser.parseFromString(svgString, 'image/svg+xml');
  const trackPoints: THREE.Vector3[] = [];
  
  // Find the first path or ellipse element
  const pathElement = svgDoc.querySelector('path');
  const ellipseElement = svgDoc.querySelector('ellipse');
  
  if (ellipseElement) {
    // Handle ellipse (our default case)
    const cx = parseFloat(ellipseElement.getAttribute('cx') || '0');
    const cy = parseFloat(ellipseElement.getAttribute('cy') || '0');
    const rx = parseFloat(ellipseElement.getAttribute('rx') || '80');
    const ry = parseFloat(ellipseElement.getAttribute('ry') || '40');
    
    // Generate points around the ellipse
    for (let i = 0; i <= 64; i++) {
      const angle = (i / 64) * Math.PI * 2;
      const x = cx + Math.cos(angle) * rx;
      const z = cy + Math.sin(angle) * ry;
      trackPoints.push(new THREE.Vector3(x, 0, z));
    }
  } else if (pathElement) {
    // Handle SVG path (for custom shapes)
    const pathData = pathElement.getAttribute('d') || '';
    
    // Simple path parsing for our custom track
    if (pathData.includes('M') && pathData.includes('C')) {
      // Parse the cubic bezier path
      return parseCustomTrackPath(pathData);
    } else {
      console.warn('Complex SVG path parsing not implemented, using default oval');
      return generateDefaultTrackPoints();
    }
  } else {
    // Fallback to default
    return generateDefaultTrackPoints();
  }
  
  return trackPoints;
};

// Parse custom track path (simplified cubic bezier)
const parseCustomTrackPath = (pathData: string) => {
  const trackPoints: THREE.Vector3[] = [];
  
  // Define key points for our custom winding track
  const keyPoints = [
    { x: -80, z: 0 },    // Start
    { x: -40, z: -50 },  // Top curve
    { x: 0, z: -50 },    // Top straight
    { x: 90, z: 0 },     // Right curve
    { x: 60, z: 50 },    // Bottom right
    { x: 10, z: 30 },    // Chicane 1
    { x: -20, z: 35 },   // Chicane 2
    { x: -70, z: 40 },   // Bottom left
    { x: -80, z: 0 }     // Back to start
  ];
  
  // Generate smooth curve through key points
  for (let i = 0; i <= 64; i++) {
    const t = i / 64;
    const segmentIndex = Math.floor(t * (keyPoints.length - 1));
    const localT = (t * (keyPoints.length - 1)) - segmentIndex;
    
    const p1 = keyPoints[segmentIndex];
    const p2 = keyPoints[Math.min(segmentIndex + 1, keyPoints.length - 1)];
    
    // Simple linear interpolation between points
    const x = p1.x + (p2.x - p1.x) * localT;
    const z = p1.z + (p2.z - p1.z) * localT;
    
    trackPoints.push(new THREE.Vector3(x, 0, z));
  }
  
  return trackPoints;
};

// Generate default oval track points
const generateDefaultTrackPoints = () => {
  const trackPoints: THREE.Vector3[] = [];
  for (let i = 0; i <= 64; i++) {
    const angle = (i / 64) * Math.PI * 2;
    const x = Math.cos(angle) * 80;
    const z = Math.sin(angle) * 40;
    trackPoints.push(new THREE.Vector3(x, 0, z));
  }
  return trackPoints;
};

// Physics
let velocity = new THREE.Vector3();
let kartRotation = 0;

// Shared physics function for all karts
const applyKartPhysics = (kart: THREE.Group, kartSpeed: { value: number }, kartRotation: number, isPlayer: boolean = false) => {
  // Apply movement based on current rotation
  const direction = new THREE.Vector3(0, 0, kartSpeed.value);
  direction.applyAxisAngle(new THREE.Vector3(0, 1, 0), kartRotation);
  
  kart.position.add(direction);
  kart.rotation.y = kartRotation;
  
  // Keep Y position fixed on 2D plane
  kart.position.y = 1;
  
  // Find closest point on track curve for collision detection
  const kartPosition = kart.position.clone();
  let closestDistance = Infinity;
  let closestPoint = null;
  
  // Sample points along the track curve to find closest
  for (let i = 0; i <= 100; i++) {
    const t = i / 100;
    const point = trackCurve.getPoint(t);
    const distance = kartPosition.distanceTo(point);
    
    if (distance < closestDistance) {
      closestDistance = distance;
      closestPoint = point;
    }
  }
  
  let hitWall = false;
  
  if (closestPoint) {
    // Check if kart is outside track boundaries based on distance from track curve
    const innerBoundary = trackWidth / 2 - 2;
    const outerBoundary = trackWidth / 2 + 2;
    
    if (closestDistance > outerBoundary) {
      hitWall = true;
      
      // Push kart back toward track curve
      const directionToTrack = closestPoint.clone().sub(kartPosition).normalize();
      const pushDirection = directionToTrack.clone().multiplyScalar(0.5);
      kart.position.add(pushDirection);
      
      // Keep Y position fixed after collision
      kart.position.y = 1;
      
      // Gradually reduce speed
      kartSpeed.value *= 0.8;
    }
  }
  
  return hitWall;
};

// Initialize the race track
const initRace = () => {
  // Setup Three.js scene
  scene = new THREE.Scene();
  scene.fog = new THREE.Fog(0x87CEEB, 50, 200);
  
  // Create gradient sky
  const skyGeometry = new THREE.SphereGeometry(500, 32, 15);
  const skyMaterial = new THREE.ShaderMaterial({
    uniforms: {
      topColor: { value: new THREE.Color(0x0077ff) },
      bottomColor: { value: new THREE.Color(0xffffff) },
      offset: { value: 33 },
      exponent: { value: 0.6 }
    },
    vertexShader: `
      varying vec3 vWorldPosition;
      void main() {
        vec4 worldPosition = modelMatrix * vec4(position, 1.0);
        vWorldPosition = worldPosition.xyz;
        gl_Position = projectionMatrix * modelViewMatrix * vec4(position, 1.0);
      }
    `,
    fragmentShader: `
      uniform vec3 topColor;
      uniform vec3 bottomColor;
      uniform float offset;
      uniform float exponent;
      varying vec3 vWorldPosition;
      void main() {
        float h = normalize(vWorldPosition + offset).y;
        gl_FragColor = vec4(mix(bottomColor, topColor, max(pow(max(h, 0.0), exponent), 0.0)), 1.0);
      }
    `,
    side: THREE.BackSide
  });
  const sky = new THREE.Mesh(skyGeometry, skyMaterial);
  scene.add(sky);
  
  // Enhanced lighting
  const ambientLight = new THREE.AmbientLight(0xffffff, 0.4);
  scene.add(ambientLight);
  
  const directionalLight = new THREE.DirectionalLight(0xffffff, 1.0);
  directionalLight.position.set(100, 100, 50);
  directionalLight.castShadow = true;
  directionalLight.shadow.mapSize.width = 2048;
  directionalLight.shadow.mapSize.height = 2048;
  scene.add(directionalLight);
  
  // Create Mario Kart-style oval track
  createRaceTrack();
  
  // Create player kart
  createPlayerKart();
  
  // Create AI karts
  createAIKarts();
  
  // Add environment decorations
  addEnvironmentDecorations();
  
  // Setup camera for third-person racing view
  camera = new THREE.PerspectiveCamera(
    75, 
    window.innerWidth / window.innerHeight, 
    0.1, 
    1000
  );
  
  // Setup renderer with enhanced settings
  renderer = new THREE.WebGLRenderer({ antialias: true });
  renderer.setSize(window.innerWidth, window.innerHeight - 100);
  renderer.shadowMap.enabled = true;
  renderer.shadowMap.type = THREE.PCFSoftShadowMap;
  renderer.toneMapping = THREE.ACESFilmicToneMapping;
  renderer.toneMappingExposure = 1.2;
  
  const container = document.getElementById('game-container');
  if (container) {
    container.innerHTML = '';
    container.appendChild(renderer.domElement);
  }
  
  // Handle window resize
  window.addEventListener('resize', onWindowResize);
  
  // Start animation loop
  animate();
};

// Create Mario Kart-style oval race track
const createRaceTrack = () => {
  track = new THREE.Group();
  
  // Generate track points from SVG (default or custom)
  const trackPoints = generateTrackFromSVG(defaultTrackSVG);
  
  trackCurve = new THREE.CatmullRomCurve3(trackPoints);
  trackCurve.closed = true;
  
  // Create flat track surface as single unified geometry
  const trackMaterial = new THREE.MeshLambertMaterial({ color: 0x444444 });
  
  // Generate unified track geometry
  const numSegments = 100;
  const vertices = [];
  const indices = [];
  
  // Generate all vertices for the track strip
  for (let i = 0; i <= numSegments; i++) {
    const t = i / numSegments;
    const point = trackCurve.getPoint(t);
    
    // Calculate perpendicular direction for width
    const nextT = ((i + 1) % (numSegments + 1)) / numSegments;
    const nextPoint = trackCurve.getPoint(nextT);
    const direction = nextPoint.clone().sub(point).normalize();
    const perpendicular = new THREE.Vector3(-direction.z, 0, direction.x);
    
    // Left and right edge points
    const leftPoint = point.clone().add(perpendicular.clone().multiplyScalar(-trackWidth / 2));
    const rightPoint = point.clone().add(perpendicular.clone().multiplyScalar(trackWidth / 2));
    
    // Add vertices (left then right for each point along curve)
    vertices.push(
      leftPoint.x, 0, leftPoint.z,   // Left vertex
      rightPoint.x, 0, rightPoint.z  // Right vertex
    );
  }
  
  // Generate indices to connect the vertices into triangles
  for (let i = 0; i < numSegments; i++) {
    const base = i * 2;
    
    // First triangle of quad
    indices.push(base, base + 1, base + 2);
    // Second triangle of quad
    indices.push(base + 1, base + 3, base + 2);
  }
  
  // Create single unified track geometry
  const trackGeometry = new THREE.BufferGeometry();
  trackGeometry.setAttribute('position', new THREE.Float32BufferAttribute(vertices, 3));
  trackGeometry.setIndex(indices);
  trackGeometry.computeVertexNormals();
  
  const trackMesh = new THREE.Mesh(trackGeometry, trackMaterial);
  trackMesh.position.y = 0; // Ground level
  track.add(trackMesh);
  
  // Create track guidance line (invisible for clean racing)
  const guidanceGeometry = new THREE.BufferGeometry().setFromPoints(trackCurve.getPoints(100));
  const guidanceMaterial = new THREE.LineBasicMaterial({ color: 0xffff00, linewidth: 3 });
  const guidanceLine = new THREE.Line(guidanceGeometry, guidanceMaterial);
  guidanceLine.position.y = 2; // Above track
  guidanceLine.visible = false; // Hidden for clean racing experience
  scene.add(guidanceLine);
  
  // Border posts removed - they don't track the custom course well
  
  // Add collision walls generated from track curve (guidance line)
  const wallMaterial = new THREE.MeshBasicMaterial({ 
    color: 0x00ffff,
    wireframe: false,
    transparent: true,
    opacity: 0.0 // Make invisible
  });
  
  // Inner collision wall - tube following track curve
  const innerWallGeometry = new THREE.TubeGeometry(
    trackCurve, 
    100, // segments
    trackWidth / 2 - 2, // radius (inner boundary)
    8, // radial segments
    true // closed
  );
  const innerWall = new THREE.Mesh(innerWallGeometry, wallMaterial);
  innerWall.position.y = 2;
  innerWall.userData.isWall = true;
  innerWall.userData.isInnerWall = true;
  scene.add(innerWall);
  
  // Outer collision wall - tube following track curve
  const outerWallGeometry = new THREE.TubeGeometry(
    trackCurve, 
    100, // segments
    trackWidth / 2 + 2, // radius (outer boundary)
    8, // radial segments
    true // closed
  );
  const outerWall = new THREE.Mesh(outerWallGeometry, wallMaterial);
  outerWall.position.y = 2;
  outerWall.userData.isWall = true;
  outerWall.userData.isOuterWall = true;
  scene.add(outerWall);
  
  // Add checkpoints (invisible but functional)
  for (let i = 0; i < 4; i++) {
    const t = i / 4;
    const point = trackCurve.getPoint(t);
    
    const checkpointGeometry = new THREE.PlaneGeometry(trackWidth * 2, 10);
    const checkpointMaterial = new THREE.MeshBasicMaterial({ 
      color: 0x00ff00, 
      transparent: true, 
      opacity: 0.0, // Make invisible
      side: THREE.DoubleSide
    });
    const checkpoint = new THREE.Mesh(checkpointGeometry, checkpointMaterial);
    checkpoint.position.copy(point);
    checkpoint.position.y = 5;
    checkpoint.rotation.x = Math.PI / 2;
    checkpoint.userData.checkpointIndex = i;
    checkpointMeshes.push(checkpoint);
    track.add(checkpoint);
  }
  
  // Add start/finish line
  const startLineGeometry = new THREE.PlaneGeometry(trackWidth * 2, 2);
  const startLineMaterial = new THREE.MeshBasicMaterial({ 
    color: 0xffffff,
    transparent: true,
    opacity: 0.8,
    side: THREE.DoubleSide
  });
  const startLine = new THREE.Mesh(startLineGeometry, startLineMaterial);
  const startPoint = trackCurve.getPoint(0);
  startLine.position.copy(startPoint);
  startLine.position.y = 0.1;
  startLine.rotation.x = -Math.PI / 2;
  track.add(startLine);
  
  scene.add(track);
};

// Create player kart with Mario Kart-style design
// Get character and car colors based on selection
const getCharacterColors = (character: any, car: any) => {
  const characterName = character?.name?.toLowerCase() || '';
  const carName = car?.name?.toLowerCase() || '';
  
  let driverColor = 0xffdbac; // Default skin color
  let kartColor = 0x156289;   // Default blue
  
  // Character-specific driver colors (unchanged)
  if (characterName.includes('aaron')) {
    driverColor = 0x808080; // Gray
  } else if (characterName.includes('tj') || characterName.includes('miller')) {
    driverColor = 0x8B4CB8; // Purple
  } else if (characterName.includes('nuno')) {
    driverColor = 0x8B4CB8; // Purple
  } else if (characterName.includes('taylor')) {
    driverColor = 0xFF4444; // Red
  }
  
  // Car-specific colors based on SELECTED CAR, not character
  if (carName.includes('green') || carName.includes('nuno')) {
    kartColor = 0x44FF44; // Green car (Nuno's car)
  } else if (carName.includes('blue') || carName.includes('aaron') || carName.includes('tj') || carName.includes('miller')) {
    kartColor = 0x4444FF; // Blue car (Aaron's and TJ's cars)
  } else if (carName.includes('red') || carName.includes('taylor') || carName.includes('lambo')) {
    kartColor = 0xFF4444; // Red car (Taylor's Lambo)
  } else if (carName.includes('purple')) {
    kartColor = 0x8B4CB8; // Purple car
  } else {
    // Fallback: use character-based colors if car name doesn't match
    if (characterName.includes('aaron')) {
      kartColor = 0x4444FF; // Blue car
    } else if (characterName.includes('tj') || characterName.includes('miller')) {
      kartColor = 0x4444FF; // Blue car (TJ's car is blue)
    } else if (characterName.includes('nuno')) {
      kartColor = 0x44FF44; // Green car (Nuno's car is green)
    } else if (characterName.includes('taylor')) {
      kartColor = 0xFF4444; // Red car
    }
  }
  
  return { driverColor, kartColor };
};

const createPlayerKart = () => {
  playerKart = new THREE.Group();
  
  // Get colors based on selected character and car
  const { driverColor, kartColor } = getCharacterColors(props.selectedCharacter, props.selectedCar);
  
  // Kart body
  const bodyGeometry = new THREE.BoxGeometry(3, 1.5, 5);
  const bodyMaterial = new THREE.MeshPhongMaterial({ 
    color: kartColor,
    shininess: 100
  });
  const body = new THREE.Mesh(bodyGeometry, bodyMaterial);
  body.position.y = 1;
  body.castShadow = true;
  playerKart.add(body);
  
  // Driver (simple representation)
  const driverGeometry = new THREE.SphereGeometry(0.8, 8, 8);
  const driverMaterial = new THREE.MeshPhongMaterial({ color: driverColor });
  const driver = new THREE.Mesh(driverGeometry, driverMaterial);
  driver.position.set(0, 2.5, -1);
  driver.castShadow = true;
  playerKart.add(driver);
  
  // Wheels
  const wheelGeometry = new THREE.CylinderGeometry(0.8, 0.8, 0.5, 8);
  const wheelMaterial = new THREE.MeshPhongMaterial({ color: 0x333333 });
  
  const wheels = [
    { x: -1.5, z: 1.5 },   // Front left
    { x: 1.5, z: 1.5 },    // Front right
    { x: -1.5, z: -1.5 },  // Rear left
    { x: 1.5, z: -1.5 }    // Rear right
  ];
  
  wheels.forEach(wheelPos => {
    const wheel = new THREE.Mesh(wheelGeometry, wheelMaterial);
    wheel.position.set(wheelPos.x, 0.5, wheelPos.z);
    wheel.rotation.z = Math.PI / 2;
    wheel.castShadow = true;
    playerKart.add(wheel);
  });
  
  // Position kart at start
  const startPoint = trackCurve.getPoint(0);
  playerKart.position.copy(startPoint);
  playerKart.position.y = 1;
  
  scene.add(playerKart);
};

// Create AI karts based on selected stage
const createAIKarts = () => {
  const colors = [0xff4444, 0x44ff44, 0x4444ff];
  
  // Determine AI opponent based on stage
  let aiName = 'TJ Miller';
  let aiDifficulty = 0.7; // Base difficulty
  
  switch (props.selectedStage) {
    case 'tj-miller':
      aiName = 'TJ Miller';
      aiDifficulty = 0.45; // Stage 1 - Very easy start
      break;
    case 'nuno-maduro':
      aiName = 'Nuno Maduro';
      aiDifficulty = 0.52; // Stage 2 - Gentle step up
      break;
    case 'aaron-francis':
      aiName = 'Aaron Francis';
      aiDifficulty = 0.58; // Stage 3 - Moderate challenge
      break;
    case 'taylor-otwell':
      aiName = 'Taylor Otwell';
      aiDifficulty = 0.65; // Final boss - What TJ used to be
      break;
    default:
      aiName = 'TJ Miller';
      aiDifficulty = 0.45;
  }
  
  const aiNames = [aiName]; // Single opponent for stage racing
  
  // Create single AI opponent for stage racing
  const aiKart = new THREE.Group();
  
  // Get AI character colors based on the opponent
  const aiCharacter = { name: aiName };
  const aiCar = { name: 'AI Car' }; // Placeholder
  const { driverColor: aiDriverColor, kartColor: aiKartColor } = getCharacterColors(aiCharacter, aiCar);
  
  // AI Kart body
  const bodyGeometry = new THREE.BoxGeometry(3, 1.5, 5);
  const bodyMaterial = new THREE.MeshPhongMaterial({ 
    color: aiKartColor,
    shininess: 100
  });
  const body = new THREE.Mesh(bodyGeometry, bodyMaterial);
  body.position.y = 1;
  body.castShadow = true;
  aiKart.add(body);
  
  // AI Driver
  const driverGeometry = new THREE.SphereGeometry(0.8, 8, 8);
  const driverMaterial = new THREE.MeshPhongMaterial({ color: aiDriverColor });
  const driver = new THREE.Mesh(driverGeometry, driverMaterial);
  driver.position.set(0, 2.5, -1);
  driver.castShadow = true;
  aiKart.add(driver);
  
  // AI Wheels
  const wheelGeometry = new THREE.CylinderGeometry(0.8, 0.8, 0.5, 8);
  const wheelMaterial = new THREE.MeshPhongMaterial({ color: 0x333333 });
  
  const wheels = [
    { x: -1.5, z: 1.5 },
    { x: 1.5, z: 1.5 },
    { x: -1.5, z: -1.5 },
    { x: 1.5, z: -1.5 }
  ];
  
  wheels.forEach(wheelPos => {
    const wheel = new THREE.Mesh(wheelGeometry, wheelMaterial);
    wheel.position.set(wheelPos.x, 0.5, wheelPos.z);
    wheel.rotation.z = Math.PI / 2;
    wheel.castShadow = true;
    aiKart.add(wheel);
  });
  
  // Position AI kart behind player
  if (trackCurve) {
    const startPoint = trackCurve.getPoint(0.02);
    aiKart.position.copy(startPoint);
    aiKart.position.y = 1;
  } else {
    // Fallback position if trackCurve not available
    aiKart.position.set(0, 1, -10);
  }
  aiKart.name = aiName; // Assign name to AI kart
  
  // Store AI difficulty for movement calculations
  aiKart.userData = { 
    difficulty: aiDifficulty,
    trackProgress: 0.02,
    speed: maxSpeed * (0.8 + Math.random() * 0.2) * aiDifficulty, // Scale speed by difficulty
    currentSpeed: 0,
    wanderDirection: (Math.random() - 0.5) * 0.5,
    lap: 1
  };
  
  // Add AI kart to scene and array
  aiKarts.push(aiKart);
  scene.add(aiKart);
};

// Add environment decorations
const addEnvironmentDecorations = () => {
  // Add trees around the track
  for (let i = 0; i < 20; i++) {
    const tree = new THREE.Group();
    
    // Tree trunk
    const trunkGeometry = new THREE.CylinderGeometry(2, 3, 15, 8);
    const trunkMaterial = new THREE.MeshLambertMaterial({ color: 0x8B4513 });
    const trunk = new THREE.Mesh(trunkGeometry, trunkMaterial);
    trunk.position.y = 7.5;
    trunk.castShadow = true;
    tree.add(trunk);
    
    // Tree foliage
    const foliageGeometry = new THREE.SphereGeometry(8, 8, 8);
    const foliageMaterial = new THREE.MeshLambertMaterial({ color: 0x228B22 });
    const foliage = new THREE.Mesh(foliageGeometry, foliageMaterial);
    foliage.position.y = 20;
    foliage.castShadow = true;
    tree.add(foliage);
    
    // Random position around track
    const angle = (i / 20) * Math.PI * 2;
    const distance = 100 + Math.random() * 50;
    tree.position.set(
      Math.cos(angle) * distance,
      0,
      Math.sin(angle) * distance
    );
    
    scene.add(tree);
  }
  
  // Add ground plane
  const groundGeometry = new THREE.PlaneGeometry(1000, 1000);
  const groundMaterial = new THREE.MeshLambertMaterial({ color: 0x90EE90 });
  const ground = new THREE.Mesh(groundGeometry, groundMaterial);
  ground.rotation.x = -Math.PI / 2;
  ground.position.y = -1;
  ground.receiveShadow = true;
  scene.add(ground);
};

// Update camera to follow player kart
const updateCamera = () => {
  if (!playerKart) return;
  
  const kartPosition = playerKart.position.clone();
  
  // Get the kart's forward direction based on its rotation
  const kartDirection = new THREE.Vector3(0, 0, -1); // Forward direction
  kartDirection.applyAxisAngle(new THREE.Vector3(0, 1, 0), kartRotation);
  
  // Camera position behind and above the kart
  const cameraOffset = kartDirection.clone().multiplyScalar(15); // Behind the kart
  cameraOffset.y = 8;
  
  const targetPosition = kartPosition.clone().add(cameraOffset);
  camera.position.lerp(targetPosition, 0.1);
  
  // Look at point slightly ahead of the kart
  const lookAtPoint = kartPosition.clone().add(kartDirection.multiplyScalar(-10));
  lookAtPoint.y += 2;
  camera.lookAt(lookAtPoint);
};

// Animation loop
const animate = () => {
  animationFrameId = requestAnimationFrame(animate);
  
  if (raceStarted.value && !raceFinished.value) {
    // Update race time
    raceTime.value += 0.016; // Roughly 60fps
    lapTime.value += 0.016;
    
    // Handle player input and physics
    updatePlayerMovement();
    
    // Update AI karts
    updateAIKarts();
    
    // Check for checkpoint/lap completion
    checkLapProgress();
    
    // Update camera to follow player
    updateCamera();
  }
  
  renderer.render(scene, camera);
};

// Update player kart physics and movement
const updatePlayerMovement = () => {
  if (!playerKart) return;
  
  // Handle input
  if (keys.w || keys.ArrowUp) {
    speed.value = Math.min(speed.value + acceleration, maxSpeed);
  } else if (keys.s || keys.ArrowDown) {
    speed.value = Math.max(speed.value - acceleration, -maxSpeed * 0.5);
  } else {
    speed.value *= friction;
  }
  
  // Handle turning
  if (keys.a || keys.ArrowLeft) {
    kartRotation += turnSpeed * (speed.value > 0 ? 1 : -1); // Turn left
  }
  if (keys.d || keys.ArrowRight) {
    kartRotation -= turnSpeed * (speed.value > 0 ? 1 : -1); // Turn right
  }
  
  // Use shared physics function
  applyKartPhysics(playerKart, speed, kartRotation, true);
};

// Update AI karts
const updateAIKarts = () => {
  aiKarts.forEach((aiKart, index) => {
    const userData = aiKart.userData;
    
    // Progressive speed based on difficulty (stage progression)
    const baseSpeed = maxSpeed * 0.9; // Slightly slower than max player speed
    const difficultyMultiplier = userData.difficulty || 0.7;
    const targetSpeed = baseSpeed * difficultyMultiplier;
    
    const currentSpeed = userData.currentSpeed || 0;
    
    // Gradually accelerate to target speed
    if (currentSpeed < targetSpeed) {
      userData.currentSpeed = Math.min(currentSpeed + acceleration * 1.5, targetSpeed);
    } else {
      userData.currentSpeed = currentSpeed * friction;
    }
    
    // Follow track curve exactly - no wandering for precise racing
    userData.trackProgress += userData.currentSpeed / 300;
    if (userData.trackProgress >= 1) {
      userData.trackProgress = 0;
      userData.lap++;
    }
    
    // Follow track exactly - position AI on the track curve
    const currentPos = trackCurve.getPoint(userData.trackProgress);
    const nextPos = trackCurve.getPoint((userData.trackProgress + 0.01) % 1);
    
    // Position AI exactly on the track
    aiKart.position.x = currentPos.x;
    aiKart.position.z = currentPos.z;
    aiKart.position.y = 1; // Keep consistent height
    
    // Calculate exact track direction for rotation
    const trackDirection = nextPos.clone().sub(currentPos).normalize();
    const aiRotation = Math.atan2(trackDirection.x, trackDirection.z);
    
    // Create speed object for shared physics
    const aiSpeedRef = { value: userData.currentSpeed };
    userData.previousSpeed = userData.currentSpeed;
    
    // Use same physics as player
    const wallHit = applyKartPhysics(aiKart, aiSpeedRef, aiRotation, false);
    
    // Update stored speed after physics
    userData.currentSpeed = aiSpeedRef.value;
  });
};

// Vehicle collision detection (disabled)
// const checkVehicleCollision = (aiKart: THREE.Group) => {
//   // Collision disabled per user request
// };

// Check lap progress using track curve
const checkLapProgress = () => {
  if (!playerKart || !trackCurve) return;
  
  // Find closest point on track curve
  const kartPosition = playerKart.position.clone();
  kartPosition.y = 0; // Project to track level
  
  let closestT = 0;
  let closestDistance = Infinity;
  
  // Sample points along the curve to find closest
  for (let i = 0; i <= 100; i++) {
    const t = i / 100;
    const point = trackCurve.getPoint(t);
    const distance = kartPosition.distanceTo(point);
    
    if (distance < closestDistance) {
      closestDistance = distance;
      closestT = t;
    }
  }
  
  // Check if we've crossed checkpoints
  const currentCheckpoint = Math.floor(closestT * 4);
  
  if (currentCheckpoint !== lastCheckpoint.value && closestDistance < 15) {
    // Only count if we're close enough to the track and progressing forward
    const expectedNext = (lastCheckpoint.value + 1) % 4;
    
    if (currentCheckpoint === expectedNext) {
      checkpoints.value[currentCheckpoint] = true;
      lastCheckpoint.value = currentCheckpoint;
      
      console.log(`Checkpoint ${currentCheckpoint} passed!`);
      
      // Check if lap completed (passed checkpoint 0 after having all others)
      if (currentCheckpoint === 0 && checkpoints.value.length === 4 && checkpoints.value.every(Boolean)) {
        completeLap();
      }
    }
  }
};

// Handle window resize
const onWindowResize = () => {
  camera.aspect = window.innerWidth / window.innerHeight;
  camera.updateProjectionMatrix();
  renderer.setSize(window.innerWidth, window.innerHeight - 100);
};

// Handle keyboard input
const keys: { [key: string]: boolean } = {};

const onKeyDown = (event: KeyboardEvent) => {
  keys[event.key] = true;
  keys[event.code] = true; // Also store by code for WASD
  
  // Start race on first key press
  if (!raceStarted.value && !raceFinished.value) {
    raceStarted.value = true;
  }
};

const onKeyUp = (event: KeyboardEvent) => {
  keys[event.key] = false;
  keys[event.code] = false;
};

// Handle lap completion
const completeLap = () => {
  const currentLapTime = lapTime.value;
  
  if (currentLapTime < bestLapTime.value) {
    bestLapTime.value = currentLapTime;
  }
  
  console.log(`Lap ${currentLap.value} completed in ${currentLapTime.toFixed(2)}s`);
  
  currentLap.value++;
  lapTime.value = 0;
  checkpoints.value = [false, false, false, false]; // Reset to false values
  lastCheckpoint.value = -1;
  
  if (currentLap.value > totalLaps) {
    finishRace();
  }
};

// Get the beaten opponent based on current race
const getBeatenOpponent = () => {
  // Return the selected stage opponent
  return props.selectedStage || 'tj-miller';
};

// Handle race completion
const finishRace = async () => {
  raceFinished.value = true;
  raceStarted.value = false;
  
  const isWinner = position.value === 1;
  
  // Check if player won and unlock next developer
  if (isWinner) {
    try {
      // Find which AI opponent the player beat
      const beatenOpponent = getBeatenOpponent();
      if (beatenOpponent) {
        const response = await axios.post('/api/unlock-next-developer', {
          beaten_developer: beatenOpponent
        });
        if (response.data.success && response.data.unlocked_developer) {
          console.log(`${response.data.unlocked_developer} unlocked!`);
          // You could show a notification here
        }
      }
    } catch (error) {
      console.error('Failed to unlock next developer:', error);
    }
  }
  
  console.log('Race finished!');
  
  // Call parent handler with race results
  const results = {
    position: position.value,
    time: raceTime.value,
    laps: totalLaps,
    isWinner
  };
  
  if (props.onRaceComplete) {
    props.onRaceComplete(results);
  } else {
    // Fallback emit for compatibility
    emit('race-complete', results);
  }
};

// Check if Taylor is in the current race
const hasTaylorInRace = () => {
  return aiKarts.some(kart => kart.name?.toLowerCase().includes('taylor'));
};

// Start a new race
const startRace = () => {
  raceStarted.value = true;
  raceTime.value = 0;
  lapTime.value = 0;
  currentLap.value = 1;
  position.value = 1;
  raceFinished.value = false;
  checkpoints.value = [false, false, false, false]; // Initialize with false values
  lastCheckpoint.value = -1;
  speed.value = 0;
  kartRotation = 0;
  
  // Reset player kart position
  if (playerKart && trackCurve) {
    const startPoint = trackCurve.getPoint(0);
    const nextPoint = trackCurve.getPoint(0.01);
    
    playerKart.position.copy(startPoint);
    playerKart.position.y = 1;
    
    // Face the kart in the direction of the track
    const direction = nextPoint.clone().sub(startPoint).normalize();
    kartRotation = Math.atan2(direction.x, direction.z);
    playerKart.rotation.y = kartRotation;
  }
  
  // Reset AI karts
  aiKarts.forEach((aiKart, index) => {
    aiKart.userData.trackProgress = (index + 1) * 0.02;
    aiKart.userData.currentSpeed = 0;
    aiKart.userData.wanderDirection = (Math.random() - 0.5) * 0.5;
    aiKart.userData.lap = 1;
    const startPoint = trackCurve.getPoint(aiKart.userData.trackProgress);
    aiKart.position.copy(startPoint);
    aiKart.position.y = 1;
  });
};

// Clean up on unmount
onUnmounted(() => {
  window.removeEventListener('resize', onWindowResize);
  window.removeEventListener('keydown', onKeyDown);
  window.removeEventListener('keyup', onKeyUp);
  
  if (animationFrameId) {
    cancelAnimationFrame(animationFrameId);
  }
  
  if (renderer) {
    renderer.dispose();
  }
});

// Initialize on mount
onMounted(() => {
  initRace();
  
  // Add keyboard event listeners
  window.addEventListener('keydown', onKeyDown);
  window.addEventListener('keyup', onKeyUp);
  
  // Start the race after a short delay
  setTimeout(() => {
    startRace();
  }, 1000);
});
</script>

<template>
  <div class="relative h-full">
    <!-- Game container -->
    <div id="game-container" class="w-full"></div>
    
    <!-- HUD -->
    <div class="absolute top-4 left-4 bg-black bg-opacity-50 text-white p-4 rounded-lg">
      <div class="text-xl font-bold mb-2">Lap: {{ currentLap }}/{{ totalLaps }}</div>
      <div class="text-lg">Time: {{ raceTime.toFixed(2) }}s</div>
    </div>
    
    <!-- Race start countdown -->
    <div 
      v-if="!raceStarted && !raceFinished"
      class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-70"
    >
      <div class="text-center">
        <div class="text-8xl font-bold text-yellow-400 mb-4">
          {{ raceTime < 1 ? 'GO!' : 'READY' }}
        </div>
        <div class="text-2xl text-gray-300">
          Use WASD to drive
        </div>
      </div>
    </div>
    
    <!-- Race finished overlay -->
    <div 
      v-if="raceFinished"
      class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-80"
    >
      <div class="text-center p-8 bg-gray-900 rounded-xl max-w-md w-full">
        <h2 class="text-4xl font-bold text-yellow-400 mb-4">
          {{ position === 1 ? 'VICTORY!' : 'Race Finished' }}
        </h2>
        
        <div class="mb-6">
          <div class="text-2xl mb-2">Final Time: {{ raceTime.toFixed(2) }}s</div>
          
          <div v-if="position === 1" class="mt-4 text-yellow-300">
            🏆 You won the race! 🏆
          </div>
        </div>
        
        <div class="space-y-4">
          <button 
            @click="startRace"
            class="w-full bg-yellow-600 hover:bg-yellow-500 text-white py-3 px-6 rounded-lg font-bold text-lg"
          >
            Race Again
          </button>
          
          <button 
            @click="onBack?.()"
            class="w-full bg-gray-700 hover:bg-gray-600 text-white py-3 px-6 rounded-lg font-bold text-lg"
          >
            Back to Menu
          </button>
        </div>
      </div>
    </div>
    
    <!-- Mobile controls (for touch devices) -->
    <div class="md:hidden fixed bottom-0 left-0 right-0 p-4 bg-gray-900 bg-opacity-80">
      <div class="grid grid-cols-3 gap-2 max-w-md mx-auto">
        <div></div>
        <button 
          @touchstart="keys.ArrowUp = true" 
          @touchend="keys.ArrowUp = false"
          class="bg-gray-700 text-white p-4 rounded-lg text-xl"
        >
          ↑
        </button>
        <div></div>
        
        <button 
          @touchstart="keys.ArrowLeft = true" 
          @touchend="keys.ArrowLeft = false"
          class="bg-gray-700 text-white p-4 rounded-lg text-xl"
        >
          ←
        </button>
        <div class="bg-gray-800 rounded-lg flex items-center justify-center">
          <div class="text-xs text-center p-2">
            Lap {{ currentLap }}/{{ totalLaps }}
          </div>
        </div>
        <button 
          @touchstart="keys.ArrowRight = true" 
          @touchend="keys.ArrowRight = false"
          class="bg-gray-700 text-white p-4 rounded-lg text-xl"
        >
          →
        </button>
        
        <div></div>
        <button 
          @touchstart="keys.ArrowDown = true" 
          @touchend="keys.ArrowDown = false"
          class="bg-gray-700 text-white p-4 rounded-lg text-xl"
        >
          ↓
        </button>
        <div></div>
      </div>
    </div>
  </div>
</template>
