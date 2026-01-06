/**
 * The Giving Grid - Main JavaScript
 * 
 * Vanilla ES6+ for progressive enhancement.
 * Full interactivity will be implemented in Batch 7.
 */

'use strict';

// ═══════════════════════════════════════════════════════════════════════════
// DOM Ready
// ═══════════════════════════════════════════════════════════════════════════

document.addEventListener('DOMContentLoaded', () => {
    initMobileNav();
    initFlashDismiss();
});

// ═══════════════════════════════════════════════════════════════════════════
// Mobile Navigation Toggle
// ═══════════════════════════════════════════════════════════════════════════

function initMobileNav() {
    const toggle = document.querySelector('.nav-toggle');
    const menu = document.querySelector('.nav-menu');
    
    if (!toggle || !menu) return;
    
    toggle.addEventListener('click', () => {
        const expanded = toggle.getAttribute('aria-expanded') === 'true';
        toggle.setAttribute('aria-expanded', !expanded);
        menu.classList.toggle('nav-menu-open', !expanded);
    });
}

// ═══════════════════════════════════════════════════════════════════════════
// Flash Message Auto-Dismiss
// ═══════════════════════════════════════════════════════════════════════════

function initFlashDismiss() {
    const flashes = document.querySelectorAll('.flash');
    
    flashes.forEach(flash => {
        // Auto-dismiss after 5 seconds
        setTimeout(() => {
            flash.style.opacity = '0';
            flash.style.transform = 'translateY(-10px)';
            setTimeout(() => flash.remove(), 300);
        }, 5000);
    });
}

// ═══════════════════════════════════════════════════════════════════════════
// Utility Functions
// ═══════════════════════════════════════════════════════════════════════════

/**
 * Escape HTML entities
 * @param {string} str - String to escape
 * @returns {string} Escaped string
 */
function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

/**
 * Debounce function calls
 * @param {Function} func - Function to debounce
 * @param {number} wait - Milliseconds to wait
 * @returns {Function} Debounced function
 */
function debounce(func, wait = 300) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}
