// --- Configuraciones de Firebase (Requeridas por la plataforma, aunque no se usen en esta lógica) ---
const appId = typeof __app_id !== 'undefined' ? __app_id : 'default-app-id';
const firebaseConfig = typeof __firebase_config !== 'undefined' ? JSON.parse(__firebase_config) : {};
const initialAuthToken = typeof __initial_auth_token !== 'undefined' ? __initial_auth_token : null;

// Importar funciones de Firebase (se asume que están disponibles)
import { initializeApp } from "https://www.gstatic.com/firebasejs/11.6.1/firebase-app.js";
import { getAuth, signInAnonymously, signInWithCustomToken } from "https://www.gstatic.com/firebasejs/11.6.1/firebase-auth.js";
import { getFirestore, setLogLevel } from "https://www.gstatic.com/firebasejs/11.6.1/firebase-firestore.js";

let app, db, auth;

// Nota: La inicialización de Firebase está aquí por requerimiento de la plataforma, 
// pero se comenta si la lógica principal no lo necesita.
/*
async function initializeFirebase() {
    try {
        app = initializeApp(firebaseConfig);
        auth = getAuth(app);
        db = getFirestore(app);
        setLogLevel('Debug');

        if (initialAuthToken) {
            await signInWithCustomToken(auth, initialAuthToken);
        } else {
            await signInAnonymously(auth);
        }
    } catch (error) {
        console.error("Error initializing or authenticating Firebase:", error);
    }
}
*/

// ------------------------------------------------------------------
// --- Lógica del Selector de Tema con localStorage ---
// ------------------------------------------------------------------

const THEME_STORAGE_KEY = 'theme'; // Clave para localStorage

/**
 * Actualiza el ícono del botón (sol o luna) según el modo actual.
 * @param {boolean} isLightMode - Verdadero si el modo claro está activo.
 */
function updateToggleButtonIcon(isLightMode) {
    const toggleButton = document.getElementById('theme-toggle');
    if (toggleButton) {
        // Usa el sol para modo claro (light-mode) y la luna para modo oscuro (dark-mode)
        toggleButton.innerHTML = isLightMode ? '☀️' : '🌙'; 
        toggleButton.title = isLightMode ? 'Activar Modo Oscuro' : 'Activar Modo Claro';
    }
}

/**
 * Aplica el tema guardado o el predeterminado al cuerpo del documento.
 */
function applyInitialTheme() {
    const body = document.body;
    // 1. Cargar preferencia guardada
    const savedTheme = localStorage.getItem(THEME_STORAGE_KEY);
    
    // 2. Determinar el tema a aplicar
    let isLightMode = false;

    if (savedTheme === 'light') {
        // Si está guardado como 'light', lo aplicamos
        isLightMode = true;
    } else if (savedTheme === null) {
        // Si no hay preferencia guardada, usamos la preferencia del sistema (si existe y es dark),
        // sino, por defecto asumimos el modo oscuro (no se añade 'light-mode').
        // La consulta de preferencia del sistema es opcional y no está en la versión actual para simplicidad.
        // Aquí asumimos 'dark' por defecto si no hay nada guardado.
        isLightMode = false;
        // Podrías añadir: isLightMode = window.matchMedia('(prefers-color-scheme: light)').matches; 
    }
    // Si savedTheme es 'dark', isLightMode sigue siendo false.

    // 3. Aplicar la clase
    if (isLightMode) {
        body.classList.add('light-mode');
    } else {
        body.classList.remove('light-mode'); // Asegura que no tenga la clase si debe ser oscuro
    }

    // 4. Inicializar el ícono del botón con el estado actual
    updateToggleButtonIcon(isLightMode);
}

/**
 * Configura la lógica de alternancia de temas.
 */
function setupThemeSwitcher() {
    const toggleButton = document.getElementById('theme-toggle');
    // Usamos el elemento HTML (document.documentElement) para aplicar la clase,
    // ya que es donde el script anti-flicker lo hace.
    const rootElement = document.documentElement; 
    
    // Inicializar el ícono del botón con el estado actual
    // Leemos el estado que ya aplicó el script del <head>
    updateToggleButtonIcon(rootElement.classList.contains('light-mode')); 

    // 2. Manejar el evento de click
    if (toggleButton) {
        toggleButton.addEventListener('click', () => {
            // Alternar la clase en el <html>
            let isLightModeActive = rootElement.classList.toggle('light-mode'); 
            
            // 3. Guardar la nueva preferencia
            const newTheme = isLightModeActive ? 'light' : 'dark';
            localStorage.setItem('theme', newTheme);

            // 4. ACTUALIZACIÓN INMEDIATA DEL ÍCONO Y ESTADO (YA ESTÁ AQUÍ, SOLO CONFIRMAMOS)
            updateToggleButtonIcon(isLightModeActive); 
            
            // Opcional: Esto ayuda si tienes transiciones que puedan tardar
            // Forzamos el reflow del navegador si fuera necesario (aunque no suele ser el caso aquí)
            // rootElement.offsetHeight; 
        });
    }
}

// Inicializar al cargar la página
document.addEventListener('DOMContentLoaded', () => {
    // initializeFirebase(); // Descomentar si se requiere la inicialización de Firebase
    setupThemeSwitcher();
});