/**
 * Renderer process for Arma Battles Chat Desktop
 * Handles custom title bar controls
 */
import "./index.css";

console.log('⚔️ Arma Battles Chat Desktop - Renderer loaded');

// Wait for DOM to be ready
document.addEventListener('DOMContentLoaded', () => {
  // Get window control buttons
  const minimizeBtn = document.getElementById('btn-minimize');
  const maximizeBtn = document.getElementById('btn-maximize');
  const closeBtn = document.getElementById('btn-close');

  // Add event listeners to window controls
  if (minimizeBtn) {
    minimizeBtn.addEventListener('click', () => {
      (window as any).native?.minimise();
    });
  }

  if (maximizeBtn) {
    maximizeBtn.addEventListener('click', () => {
      (window as any).native?.maximise();
    });
  }

  if (closeBtn) {
    closeBtn.addEventListener('click', () => {
      (window as any).native?.close();
    });
  }

  console.log('✅ Title bar controls initialized');
});
