<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Operation Restore Hope</title>
  
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&family=Lato:wght@400;700;900&display=swap" rel="stylesheet">
  
  <style>
    /* ========== CSS RESET & BASE STYLES ========== */
*, *::before, *::after {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
}

:root {
  /* Modern Color Palette */
  --primary-blue: #2563eb;
  --primary-red: #dc2626;
  --primary-green: #16a34a;
  --accent-yellow: #ca8a04;
  --accent-light-blue: #0ea5e9;
  --text-dark: #0f172a;
  --text-medium: #334155;
  --text-light: #64748b;
  --text-lighter: #94a3b8;
  --bg-white: #ffffff;
  --bg-light: #f8fafc;
  --bg-gray: #f1f5f9;
  --surface: #ffffff;
  --surface-elevated: #f8fafc;
  --border-light: #e2e8f0;
  --shadow-xs: 0 1px 2px 0 rgb(0 0 0 / 0.05);
  --shadow-sm: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
  --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
  --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
  --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
  --shadow-2xl: 0 25px 50px -12px rgb(0 0 0 / 0.25);
  
  /* Typography Scale */
  --font-primary: "Inter", "Poppins", -apple-system, BlinkMacSystemFont, sans-serif;
  --font-secondary: "Cal Sans", "Lato", Georgia, serif;
  --font-mono: "JetBrains Mono", "Fira Code", monospace;
  --font-size-xs: 0.75rem;
  --font-size-sm: 0.875rem;
  --font-size-base: 1rem;
  --font-size-lg: 1.125rem;
  --font-size-xl: 1.25rem;
  --font-size-2xl: 1.5rem;
  --font-size-3xl: 1.875rem;
  --font-size-4xl: 2.25rem;
  --font-size-5xl: 3rem;
  
  /* Perfect Spacing Scale */
  --space-px: 1px;
  --space-0: 0;
  --space-1: 0.25rem;
  --space-2: 0.5rem;
  --space-3: 0.75rem;
  --space-4: 1rem;
  --space-5: 1.25rem;
  --space-6: 1.5rem;
  --space-8: 2rem;
  --space-10: 2.5rem;
  --space-12: 3rem;
  --space-16: 4rem;
  --space-20: 5rem;
  --space-24: 6rem;
  
  /* Layout & Components */
  --max-width: 1280px;
  --border-radius-none: 0;
  --border-radius-sm: 0.125rem;
  --border-radius-md: 0.375rem;
  --border-radius-lg: 0.5rem;
  --border-radius-xl: 0.75rem;
  --border-radius-2xl: 1rem;
  --border-radius-3xl: 1.5rem;
  --border-radius-full: 9999px;
  
  /* Modern Transitions */
  --duration-75: 75ms;
  --duration-100: 100ms;
  --duration-150: 150ms;
  --duration-200: 200ms;
  --duration-300: 300ms;
  --duration-500: 500ms;
  --duration-700: 700ms;
  --ease-linear: linear;
  --ease-out: cubic-bezier(0, 0, 0.2, 1);
  --ease-in: cubic-bezier(0.4, 0, 1, 1);
  --ease-in-out: cubic-bezier(0.4, 0, 0.2, 1);
  --transition: all var(--duration-200) var(--ease-out);
  --transition-fast: all var(--duration-150) var(--ease-out);
}

/* Enhanced Google Fonts Import */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Cal+Sans:wght@400;600&display=swap');

html {
  scroll-behavior: smooth;
  -webkit-text-size-adjust: 100%;
  text-rendering: optimizeLegibility;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}

body {
  font-family: var(--font-primary);
  font-size: var(--font-size-base);
  font-weight: 400;
  line-height: 1.7;
  color: var(--text-dark);
  background: var(--bg-white);
  overflow-x: hidden;
  overflow-y: hidden;
  letter-spacing: -0.011em;
}

img {
  max-width: 100%;
  height: auto;
  display: block;
}

/* ========== MODERN LAYOUT COMPONENTS ========== */
.section {
  padding: var(--space-20) 0;
  position: relative;
  
}

.section--compact {
  padding: var(--space-16) 0;
}

.section::before {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(180deg, transparent 0%, rgba(248, 250, 252, 0.4) 50%, transparent 100%);
  pointer-events: none;
  z-index: -1;
}

.container {
  width: 100%;
  max-width: var(--max-width);
  
  padding: 0rem 0rem 0rem 8rem; /* Increased from 2rem (32px) to 4rem (64px) on each side */

}

/* Responsive margin adjustments */
@media (max-width: 768px) {
  .container {
    padding: 0 3rem; /* 48px on mobile/tablet for better balance */
  }
}

@media (max-width: 480px) {
  .container {
    padding: 0 2rem; /* Back to 32px on very small screens to maintain readability */
  }
}

.section__header {
  text-align: center;
  margin-bottom: var(--space-16);
  position: relative;
}

.section__title {
  font-family: var(--font-secondary);
  font-size: clamp(var(--font-size-3xl), 4vw, var(--font-size-5xl));
  font-weight: 600;
  color: var(--primary-blue);
  margin-bottom: var(--space-4);
  letter-spacing: -0.02em;
  line-height: 1.1;
  background: linear-gradient(135deg, var(--primary-blue) 0%, var(--accent-light-blue) 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  opacity: 0;
  transform: translateY(30px);
  transition: all var(--duration-700) var(--ease-out);
}

.section__title.visible {
  opacity: 1;
  transform: translateY(0);
}

.section__subtitle {
  font-size: var(--font-size-lg);
  color: var(--text-medium);
  max-width: 42rem;
  margin: 0 auto;
  font-weight: 400;
  line-height: 1.6;
  opacity: 0;
  transform: translateY(20px);
  transition: all var(--duration-700) var(--ease-out) 0.2s;
}

.section__subtitle.visible {
  opacity: 1;
  transform: translateY(0);
}
/* ========== ENHANCED ANIMATIONS ========== */
@keyframes fadeIn {
  from { 
    opacity: 0; 
    transform: translateY(var(--space-6)); 
  }
  to { 
    opacity: 1; 
    transform: translateY(0); 
  }
}

@keyframes fadeInUp {
  from { 
    opacity: 0; 
    transform: translateY(var(--space-10)); 
  }
  to { 
    opacity: 1; 
    transform: translateY(0); 
  }
}

@keyframes fadeInDown {
  from { 
    opacity: 0; 
    transform: translateY(calc(var(--space-6) * -1)); 
  }
  to { 
    opacity: 1; 
    transform: translateY(0); 
  }
}

@keyframes slideInLeft {
  from {
    opacity: 0;
    transform: translateX(-var(--space-12));
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

@keyframes slideInRight {
  from {
    opacity: 0;
    transform: translateX(var(--space-12));
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

.animate-on-scroll {
  opacity: 0;
  transform: translateY(30px);
  transition: all var(--duration-700) var(--ease-out);
}

.animate-on-scroll.animate-in {
  opacity: 1;
  transform: translateY(0);
}

.animate-delay-1 { transition-delay: 100ms; }
.animate-delay-2 { transition-delay: 200ms; }
.animate-delay-3 { transition-delay: 300ms; }
.animate-delay-4 { transition-delay: 400ms; }
.animate-delay-5 { transition-delay: 500ms; }

/* Scroll-triggered animations */
.fade-in-on-scroll {
  opacity: 0;
  transform: translateY(30px);
  transition: all var(--duration-700) var(--ease-out);
}

.fade-in-on-scroll.visible {
  opacity: 1;
  transform: translateY(0);
}

/* ========== MODERN BUTTON SYSTEM ========== */
.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: var(--space-2);
  padding: var(--space-3) var(--space-6);
  font-size: var(--font-size-sm);
  font-weight: 600;
  text-decoration: none;
  border: none;
  border-radius: var(--border-radius-2xl);
  cursor: pointer;
  transition: var(--transition);
  white-space: nowrap;
  position: relative;
  overflow: hidden;
  font-family: var(--font-primary);
  letter-spacing: -0.01em;
}

.btn::before {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(45deg, rgba(255,255,255,0.1) 0%, transparent 100%);
  opacity: 0;
  transition: var(--transition);
}

.btn:hover::before {
  opacity: 1;
}

.btn--primary {
  background: linear-gradient(135deg, var(--primary-green) 0%, #15803d 100%);
  color: white;
  box-shadow: var(--shadow-md);
}

.btn--primary:hover {
  background: linear-gradient(135deg, #15803d 0%, #166534 100%);
  transform: translateY(-1px);
  box-shadow: var(--shadow-lg);
}

.btn--secondary {
  background: linear-gradient(135deg, var(--primary-blue) 0%, #1d4ed8 100%);
  color: white;
  box-shadow: var(--shadow-md);
}

.btn--secondary:hover {
  background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
  transform: translateY(-1px);
  box-shadow: var(--shadow-lg);
}

.btn--accent {
  background: linear-gradient(135deg, var(--primary-red) 0%, #b91c1c 100%);
  color: white;
  box-shadow: var(--shadow-md);
}

.btn--accent:hover {
  background: linear-gradient(135deg, #b91c1c 0%, #991b1b 100%);
  transform: translateY(-1px);
  box-shadow: var(--shadow-lg);
}

.btn--ghost {
  background: rgba(255, 255, 255, 0.1);
  color: white;
  backdrop-filter: blur(12px);
  border: 1px solid rgba(255, 255, 255, 0.2);
}

.btn--ghost:hover {
  background: rgba(255, 255, 255, 0.2);
  transform: scale(1.02);
  border-color: rgba(255, 255, 255, 0.3);
}

/* ========== ADVANCED CARD SYSTEM ========== */
.card {
  background: var(--surface);
  border-radius: var(--border-radius-2xl);
  overflow: hidden;
  box-shadow: var(--shadow-md);
  transition: var(--transition);
  height: 100%;
  display: flex;
  flex-direction: column;
  position: relative;
  border: 1px solid var(--border-light);
}

.card::before {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, transparent 50%);
  opacity: 0;
  transition: var(--transition);
  pointer-events: none;
  z-index: 1;
}

.card:hover::before {
  opacity: 1;
}

.card:hover {
  transform: translateY(-var(--space-2));
  box-shadow: var(--shadow-xl);
  border-color: rgba(255, 255, 255, 0.2);
}

.card__image {
  width: 100%;
  height: 200px;
  object-fit: cover;
  transition: var(--transition);
}

.card__image:hover {
  transform: scale(1.02);
}

.card__content {
  padding: var(--space-8);
  flex-grow: 1;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  position: relative;
  z-index: 2;
  
}

.card__title {
  font-family: var(--font-secondary);
  font-size: var(--font-size-xl);
  font-weight: 600;
  margin-bottom: var(--space-3);
  color: white;
  letter-spacing: -0.01em;
  line-height: 1.3;
}

.card__text {
  font-size: var(--font-size-sm);
  line-height: 1.6;
  margin-bottom: var(--space-6);
  color: rgba(255, 255, 255, 0.9);
  font-weight: 400;
}

.card--red { 
  background: linear-gradient(135deg, var(--primary-red) 0%, #dc2626 50%, #b91c1c 100%);
}

.card--green { 
  background: linear-gradient(135deg, #fbe016ff 20%, #f1c410ff 60%, #dea806ff 100%);
}


.card--blue { 
  background: linear-gradient(135deg, var(--accent-light-blue) 0%, var(--primary-blue) 50%, #1d4ed8 100%);
}

/* ========== MODERN GRID SYSTEM ========== */
.grid {
  display: grid;
  gap: var(--space-8);
}

.grid--2 {
  grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
}

.grid--3 {
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
}

.grid--auto {
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
}

.flex {
  display: flex;
  gap: var(--space-6);
}

.flex--center {
  align-items: center;
  justify-content: center;
}

.flex--between {
  align-items: center;
  justify-content: space-between;
}

.flex--wrap {
  flex-wrap: wrap;
}

/* ========== WELCOME SECTION REDESIGN ========== */
.welcome {
  background: linear-gradient(135deg, var(--bg-light) 0%, var(--bg-gray) 100%);
  position: relative;
  overflow: hidden;
}

.welcome::before {
  content: '';
  position: absolute;
  inset: 0;
  background: 
    radial-gradient(circle at 20% 20%, rgba(37, 99, 235, 0.05) 0%, transparent 50%),
    radial-gradient(circle at 80% 80%, rgba(220, 38, 38, 0.03) 0%, transparent 50%);
  pointer-events: none;
}

.welcome__content {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: var(--space-16);
  align-items: start;
  position: relative;
  z-index: 1;
}

.welcome__text {
  font-size: var(--font-size-base);
  line-height: 1.7;
  color: var(--text-medium);
  font-weight: 400;
}

.welcome__text p {
  margin-bottom: var(--space-6);
  opacity: 0;
  transform: translateX(-30px);
  transition: all var(--duration-700) var(--ease-out);
}

.welcome__text p.visible {
  opacity: 1;
  transform: translateX(0);
}

.welcome__text p.visible:nth-child(1) { transition-delay: 200ms; }
.welcome__text p.visible:nth-child(2) { transition-delay: 300ms; }
.welcome__text p.visible:nth-child(3) { transition-delay: 400ms; }
.welcome__text p.visible:nth-child(4) { transition-delay: 500ms; }

.welcome__video {
  position: relative;
  border-radius: var(--border-radius-3xl);
  overflow: hidden;
  box-shadow: var(--shadow-2xl);
  background: linear-gradient(45deg, var(--primary-blue), var(--accent-light-blue));
  padding: var(--space-1);
  opacity: 0;
  transform: translateX(30px);
  transition: all var(--duration-700) var(--ease-out) 400ms;
}

.welcome__video.visible {
  opacity: 1;
  transform: translateX(0);
}

.welcome__video:hover {
  transform: translateY(-var(--space-1)) scale(1.01);
  box-shadow: var(--shadow-2xl);
}

.welcome__video::before {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(45deg, transparent 0%, rgba(255,255,255,0.1) 50%, transparent 100%);
  border-radius: var(--border-radius-3xl);
  pointer-events: none;
}

.welcome__video:hover {
  transform: translateY(-var(--space-1)) scale(1.01);
  box-shadow: var(--shadow-2xl);
}

.welcome__video iframe {
  width: 100%;
  aspect-ratio: 16/9;
  border: none;
  border-radius: calc(var(--border-radius-3xl) - var(--space-1));
}

/* ========== MISSION CARDS ENHANCED ========== */
.mission-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: var(--space-8);
}

/* ========== TRANSFORMATIONS SECTION ========== */
.transformations {
  background: linear-gradient(180deg, var(--bg-light) 0%, var(--bg-white) 100%);
  position: relative;
}

.transformation-card {
  background: var(--surface);
  border-radius: var(--border-radius-2xl);
  padding: var(--space-8);
  text-align: center;
  box-shadow: var(--shadow-lg);
  transition: var(--transition);
  border: 1px solid var(--border-light);
  position: relative;
  overflow: hidden;
}

.transformation-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 2px;
  background: linear-gradient(90deg, var(--primary-blue), var(--accent-light-blue), var(--primary-green));
  transition: var(--duration-500) var(--ease-out);
}

.transformation-card:hover::before {
  left: 0;
}

.transformation-card:hover {
  transform: translateY(-var(--space-3));
  box-shadow: var(--shadow-2xl);
  border-color: rgba(37, 99, 235, 0.2);
}

.transformation__images {
  display: flex;
  gap: var(--space-4);
  margin-bottom: var(--space-4);
}

.transformation__image {
  flex: 1;
  border-radius: var(--border-radius-xl);
  overflow: hidden;
  box-shadow: var(--shadow-md);
  transition: var(--transition);
  position: relative;
}

.transformation__image::before {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(45deg, rgba(37, 99, 235, 0.1) 0%, transparent 100%);
  opacity: 0;
  transition: var(--transition);
  z-index: 1;
}

.transformation__image:hover::before {
  opacity: 1;
}

.transformation__image img {
  width: 100%;
  height: 160px;
  object-fit: cover;
  transition: var(--transition);
}

/* Better responsive behavior for transformation images */
@media (max-width: 1024px) {
  .container {
    padding: 0 1.5rem; /* 24px for tablets */
  }
  
  .transformation__image img {
    height: 140px;
  }
}

@media (max-width: 768px) {
  .container {
    padding: 0 1.25rem; /* 20px for mobile */
  }
  
  .transformation__image img {
    height: auto;
    min-height: 120px;
    max-height: 160px;
    aspect-ratio: 4/3;
    object-fit: contain;
  }
}

.transformation__image:hover img {
  transform: scale(1.05);
}

.transformation__label {
  font-weight: 600;
  font-size: var(--font-size-sm);
  color: var(--text-medium);
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

/* ========== HELP SECTION ENHANCED ========== */
.help-item {
  display: flex;
  align-items: center;
  gap: var(--space-16);
  margin-bottom: var(--space-20);
  position: relative;
}

.help-item:nth-child(even) .help__image {
  order: 2;
}

.help-item:nth-child(even) .help__content {
  order: 1;
}

.help__content {
  flex: 1;
  position: relative;
}

.help__image {
  flex: 1;
  position: relative;
}

.help__image::before {
  content: '';
  position: absolute;
  inset: -var(--space-2);
  background: linear-gradient(135deg, var(--primary-blue), var(--accent-light-blue));
  border-radius: var(--border-radius-3xl);
  z-index: -1;
  opacity: 0.1;
  transition: var(--transition);
}

.help__image:hover::before {
  opacity: 0.15;
  transform: scale(1.02);
}

.help__image img {
  border-radius: var(--border-radius-2xl);
  box-shadow: var(--shadow-xl);
  transition: var(--transition);
  width: 100%;
}

.help__image img:hover {
  transform: scale(1.01);
  box-shadow: var(--shadow-2xl);
}

.help__title {
  font-size: var(--font-size-3xl);
  font-weight: 700;
  margin-bottom: var(--space-4);
  color: var(--text-dark);
  font-family: var(--font-secondary);
  letter-spacing: -0.02em;
}

.help__description {
  font-size: var(--font-size-lg);
  color: var(--text-medium);
  margin-bottom: var(--space-8);
  line-height: 1.6;
  font-weight: 400;
}

/* ========== SUPPORTERS CAROUSEL REDESIGN ========== */
.supporters-carousel {
  position: relative;
  overflow: hidden;
  border-radius: var(--border-radius-2xl);
  background: var(--surface);
  box-shadow: var(--shadow-md);
  padding: var(--space-8);
}

.supporters-track {
  display: flex;
  transition: transform var(--duration-500) var(--ease-out);
  gap: var(--space-8);
  align-items: center;
}

.supporter {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: var(--space-6);
  transition: var(--transition);
  border-radius: var(--border-radius-xl);
  background: var(--surface);
  box-shadow: var(--shadow-sm);
  border: 1px solid var(--border-light);
  position: relative;
  overflow: hidden;
  flex-shrink: 0;
  min-width: 200px;
  height: 120px;
}

.supporter::before {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, var(--primary-blue), var(--accent-light-blue));
  opacity: 0;
  transition: var(--transition);
}

.supporter:hover::before {
  opacity: 0.03;
}

.supporter img {
  max-height: 80px;
  max-width: 100%;
  width: auto;
  filter: grayscale(80%) opacity(0.8);
  transition: var(--transition);
  position: relative;
  z-index: 1;
  object-fit: contain;
}

.supporter:hover {
  transform: translateY(-var(--space-1));
  box-shadow: var(--shadow-lg);
  border-color: rgba(37, 99, 235, 0.2);
}

.supporter:hover img {
  filter: grayscale(0%) opacity(1);
  transform: scale(1.1);
}

/* Carousel Navigation */
.carousel-nav {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  background: rgba(255, 255, 255, 0.9);
  border: 1px solid var(--border-light);
  border-radius: var(--border-radius-full);
  width: 48px;
  height: 48px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: var(--transition);
  z-index: 10;
  backdrop-filter: blur(8px);
}

.carousel-nav:hover {
  background: white;
  box-shadow: var(--shadow-lg);
  transform: translateY(-50%) scale(1.1);
}

.carousel-nav--prev {
  left: var(--space-4);
}

.carousel-nav--next {
  right: var(--space-4);
}

.carousel-nav svg {
  width: 20px;
  height: 20px;
  color: var(--primary-blue);
}

/* Carousel Indicators */
.carousel-indicators {
  display: flex;
  justify-content: center;
  gap: var(--space-2);
  margin-top: var(--space-6);
}

.carousel-indicator {
  width: 12px;
  height: 12px;
  border-radius: var(--border-radius-full);
  background: var(--border-light);
  cursor: pointer;
  transition: var(--transition);
}

.carousel-indicator.active {
  background: var(--primary-blue);
  transform: scale(1.2);
}

.carousel-indicator:hover {
  background: var(--accent-light-blue);
}

/* ========== RESPONSIVE DESIGN ENHANCED ========== */
@media (max-width: 1024px) {
  :root {
    --space-20: var(--space-16);
    --space-16: var(--space-12);
  }
  
  .welcome__content,
  .help-item {
    gap: var(--space-12);
  }
}

@media (max-width: 768px) {
  :root {
    --space-20: var(--space-12);
    --space-16: var(--space-10);
  }
  
  .section__title {
    font-size: clamp(1.5rem, 5vw, 2rem); /* 24px to 32px */
  }
  
  .section__subtitle {
    font-size: 0.875rem; /* 14px */
    line-height: 1.5;
  }
  
  .welcome__text {
    font-size: 0.875rem; /* 14px */
    line-height: 1.6;
  }
  
  .help__title {
    font-size: 1.5rem; /* 24px */
  }
  
  .help__description {
    font-size: 0.875rem; /* 14px */
  }
  
  .card__title {
    font-size: 1.25rem; /* 20px */
  }
  
  .card__text {
    font-size: 0.75rem; /* 12px */
  }
  
  .welcome__content {
    grid-template-columns: 1fr;
    gap: var(--space-10);
  }
  
  .help-item {
    flex-direction: column;
    text-align: center;
    gap: var(--space-8);
  }
  
  .help-item:nth-child(even) .help__image,
  .help-item:nth-child(even) .help__content {
    order: initial;
  }
  
  .mission-cards {
    grid-template-columns: 1fr;
    gap: var(--space-6);
  }
  
  .grid--3 {
    grid-template-columns: 1fr;
    gap: var(--space-6);
  }
  
  .supporters-carousel {
    padding: var(--space-6);
  }
  
  .supporter {
    min-width: 180px;
    height: 100px;
  }
  
  .supporter img {
    max-height: 60px;
  }
  
  .carousel-nav {
    width: 40px;
    height: 40px;
  }
  
  .carousel-nav svg {
    width: 16px;
    height: 16px;
  }
  
  .transformation__images {
    gap: var(--space-3);
  }
}

@media (max-width: 480px) {
  :root {
    --space-20: var(--space-8);
    --space-16: var(--space-6);
    --space-12: var(--space-8);
    --space-10: var(--space-6);
    --space-8: var(--space-5);
  }
  
  .container {
    padding: 0 1rem; /* 16px for small mobile - minimum but reasonable */
  }
  
  .section__title {
    font-size: 1.5rem !important; /* 24px */
    margin-bottom: var(--space-3);
  }
  
  .section__subtitle {
    font-size: 0.75rem; /* 12px */
    line-height: 1.4;
    margin-bottom: var(--space-4);
  }
  
  .welcome__text {
    font-size: 0.75rem; /* 12px */
    line-height: 1.5;
  }
  
  .welcome__text p {
    margin-bottom: var(--space-3);
  }
  
  .btn {
    padding: var(--space-2) var(--space-4);
    font-size: 0.75rem; /* 12px */
  }
  
  .card__content {
    padding: var(--space-4);
  }
  
  .card__title {
    font-size: 1rem; /* 16px */
    margin-bottom: var(--space-2);
  }
  
  .card__text {
    font-size: 0.6875rem; /* 11px */
    margin-bottom: var(--space-4);
    line-height: 1.4;
  }
  
  .transformation-card {
    padding: var(--space-4);
  }
  
  .transformation__images {
    flex-direction: column;
    gap: var(--space-2);
    margin-bottom: var(--space-3);
  }
  
  .transformation__image img {
    height: auto;
    min-height: 120px;
    max-height: 160px;
    aspect-ratio: 3/4;
    object-fit: contain;
    background: var(--bg-light);
    border-radius: var(--border-radius-lg);
  }
  
  .transformation__label {
    font-size: 0.625rem; /* 10px */
  }
  
  .help__title {
    font-size: 1.5rem; /* 24px */
    margin-bottom: var(--space-3);
  }
  
  .help__description {
    font-size: 0.75rem; /* 12px */
    margin-bottom: var(--space-4);
    line-height: 1.4;
  }
  
  .supporters-carousel {
    padding: var(--space-4);
  }
  
  .supporter {
    min-width: 150px;
    height: 80px;
    padding: var(--space-3);
  }
  
  .supporter img {
    max-height: 40px;
  }
  
  .carousel-nav {
    width: 36px;
    height: 36px;
  }
  
  .carousel-nav--prev {
    left: var(--space-2);
  }
  
  .carousel-nav--next {
    right: var(--space-2);
  }
  
  .section {
    padding: var(--space-8) 0;
  }
  
  .section--compact {
    padding: var(--space-6) 0;
  }
  
  .section__header {
    margin-bottom: var(--space-8);
  }
}

/* ========== UTILITY CLASSES ========== */
.text-center { text-align: center; }
.text-left { text-align: left; }
.text-right { text-align: right; }

.mb-4 { margin-bottom: var(--space-4); }
.mb-6 { margin-bottom: var(--space-6); }
.mb-8 { margin-bottom: var(--space-8); }
.mb-12 { margin-bottom: var(--space-12); }

.mt-4 { margin-top: var(--space-4); }
.mt-6 { margin-top: var(--space-6); }
.mt-8 { margin-top: var(--space-8); }
.mt-12 { margin-top: var(--space-12); }

/* ========== ACCESSIBILITY & PERFORMANCE ========== */
@media (prefers-reduced-motion: reduce) {
  *, *::before, *::after {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
  }
}

@media (prefers-contrast: high) {
  :root {
    --shadow-sm: 0 2px 4px 0 rgb(0 0 0 / 0.3);
    --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.3);
    --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.3);
  }
}

.sr-only {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  border: 0;
}

/* Focus styles for accessibility */
.btn:focus-visible,
.card:focus-visible {
  outline: 2px solid var(--primary-blue);
  outline-offset: 2px;
}

/* ========== IMAGE PREVIEW MODAL ========== */
.image-preview-modal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.9);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
  opacity: 0;
  visibility: hidden;
  transition: all var(--duration-300) var(--ease-out);
  backdrop-filter: blur(8px);
}

.image-preview-modal.active {
  opacity: 1;
  visibility: visible;
}

.image-preview-content {
  position: relative;
  max-width: 95vw;
  max-height: 90vh;
  background: var(--surface);
  border-radius: var(--border-radius-2xl);
  overflow: hidden;
  box-shadow: var(--shadow-2xl);
  transform: scale(0.8);
  transition: transform var(--duration-300) var(--ease-out);
}

.image-preview-modal.active .image-preview-content {
  transform: scale(1);
}

.image-preview-images {
  display: flex;
  gap: var(--space-4);
  padding: var(--space-6);
  align-items: center;
  justify-content: center;
}

.image-preview-single {
  flex: 1;
  text-align: center;
}

.image-preview-single img {
  width: 100%;
  height: auto;
  max-height: 70vh;
  object-fit: contain;
  border-radius: var(--border-radius-lg);
  box-shadow: var(--shadow-md);
}

.image-preview-label {
  margin-top: var(--space-3);
  font-size: var(--font-size-sm);
  font-weight: 600;
  color: var(--text-medium);
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.image-preview-separator {
  width: 2px;
  height: 60vh;
  background: linear-gradient(to bottom, transparent, var(--border-light), transparent);
  margin: 0 var(--space-4);
  flex-shrink: 0;
}

.image-preview-close {
  position: absolute;
  top: var(--space-4);
  right: var(--space-4);
  width: 40px;
  height: 40px;
  background: rgba(0, 0, 0, 0.7);
  border: none;
  border-radius: var(--border-radius-full);
  color: white;
  font-size: var(--font-size-xl);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: var(--transition);
  z-index: 10;
}

.image-preview-close:hover {
  background: rgba(0, 0, 0, 0.9);
  transform: scale(1.1);
}

.image-preview-info {
  background: var(--bg-light);
  color: var(--text-dark);
  padding: var(--space-6);
  text-align: center;
  border-top: 1px solid var(--border-light);
}

.image-preview-title {
  font-size: var(--font-size-xl);
  font-weight: 700;
  margin-bottom: var(--space-3);
  color: var(--primary-blue);
  font-family: var(--font-secondary);
}

.image-preview-description {
  font-size: var(--font-size-base);
  color: var(--text-medium);
  line-height: 1.6;
}

/* Enhanced transformation image hover effect */
.transformation__image {
  cursor: pointer;
  position: relative;
}

.transformation__image::after {
  content: '🔍';
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  font-size: var(--font-size-2xl);
  color: white;
  background: rgba(0, 0, 0, 0.7);
  width: 50px;
  height: 50px;
  border-radius: var(--border-radius-full);
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transition: var(--transition);
  pointer-events: none;
}

.transformation__image:hover::after {
  opacity: 1;
}

/* Mobile responsive adjustments for modal */
@media (max-width: 768px) {
  .image-preview-content {
    max-width: 98vw;
    max-height: 85vh;
    margin: var(--space-2);
  }

  .image-preview-images {
    flex-direction: row;
    gap: var(--space-3);
    padding: var(--space-4);
  }

  .image-preview-separator {
    width: 2px;
    height: 40vh;
    background: linear-gradient(to bottom, transparent, var(--border-light), transparent);
    margin: 0 var(--space-2);
  }

  .image-preview-single img {
    max-height: 25vh;
    width: 100%;
    object-fit: contain;
  }

  .image-preview-close {
    top: var(--space-2);
    right: var(--space-2);
    width: 35px;
    height: 35px;
    font-size: var(--font-size-lg);
  }

  .image-preview-info {
    padding: var(--space-4);
  }
}

@media (max-width: 480px) {
  .image-preview-content {
    max-width: 95vw;
    max-height: 80vh;
    margin: var(--space-1);
  }

  .image-preview-images {
    flex-direction: column;
    gap: var(--space-2);
    padding: var(--space-3);
  }

  .image-preview-separator {
    width: 80%;
    height: 2px;
    background: linear-gradient(to right, transparent, var(--border-light), transparent);
    margin: var(--space-2) auto;
  }

  .image-preview-single img {
    max-height: 25vh;
    width: 100%;
    object-fit: contain;
  }

  .image-preview-label {
    font-size: 0.625rem; /* 10px */
    margin-top: var(--space-2);
  }

  .image-preview-info {
    padding: var(--space-3);
  }

  .image-preview-title {
    font-size: 1rem; /* 16px */
    margin-bottom: var(--space-2);
  }

  .image-preview-description {
    font-size: 0.75rem; /* 12px */
    line-height: 1.4;
  }

  .image-preview-close {
    width: 30px;
    height: 30px;
    font-size: 1rem;
  }
}

.supporter-text span {
  font-family: 'Lato', sans-serif; /* or Poppins, adjust as needed */
  font-weight: 700; /* bold like logos */
  font-size: 1.2rem; /* adjust size */
  color: #111827; /* dark gray/black */
  letter-spacing: 1px; /* spacing for logo-like feel */
  text-align: center;
  display: block;
}
  </style>
</head>
<body>
  <!-- Welcome Section -->
  <section class="section welcome">
    <div class="container">
      <div class="section__header">
        <h1 class="section__title">Welcome</h1>
      </div>
      
      <div class="welcome__content">
        <div class="welcome__text">
          <p>Operation Restore Hope Australia (ORHA) has been caring for children in the Philippines with cleft lip and palate as well as craniofacial facial birth defects and deformities for more than 30 years. These children would go untreated without charitable intervention.</p>
          
          <p>Cleft lip and palate compromises not just the appearance and therefore social interactions of the child but also their food and oxygen uptake and ability to communicate. A single operation or operations plural if required can change this and give these children the chance to live normal, healthy lives, be accepted into society and have a chance to fulfil their potential.</p>
          
          <p>Please take some time to explore our site as well as watch our videos and feel free to contact us if you are interested in learning more about ORHA or finding out how you can help.</p>
          
          <p>Thank you for taking time out of your busy life to consider others less fortunate than ourselves.</p>
        </div>
        
        <div class="welcome__video">
          <iframe 
            src="https://www.youtube.com/embed/_9WvDfkagTg" 
            title="Welcome to Operation Restore Hope" 
            allowfullscreen>
          </iframe>
        </div>
      </div>
    </div>
  </section>

  <!-- Mission Section -->
  <section class="section">
    <div class="container">
      <div class="section__header">
        <h2 class="section__title">Understanding our Mission</h2>
      </div>
      
      <div class="mission-cards">
        <article class="card card--red animate-on-scroll animate-delay-1">
          <img 
            src="https://static.wixstatic.com/media/52448e_75a21b2ef004417d981af095ceb91213~mv2.jpg" 
            alt="About Operation Restore Hope" 
            class="card__image"
          >
          <div class="card__content">
            <h3 class="card__title">About Operation Restore Hope</h3>
            <p class="card__text">Operation Restore Hope (ORH) is an Australian based surgical charity for less fortunate children in the Philippines with birth defects and deformities, especially cleft lip and palate.</p>
            <a href="#" class="btn btn--ghost">Read More →</a>
          </div>
        </article>

        <article class="card card--green animate-on-scroll animate-delay-2">
          <img 
            src="https://static.wixstatic.com/media/52448e_8b4b48e7e85c42b193b4cc88cd91ace9~mv2.jpg" 
            alt="What is a Cleft lip & palate" 
            class="card__image"
          >
          <div class="card__content">
            <h3 class="card__title">What is a Cleft lip &amp; palate</h3>
            <p class="card__text">A cleft is a congenital birth defect which may be of one or two sides of the lip, the palate, or both. It develops when parts of the lip or palate fail to join together during development.</p>
            <a href="#" class="btn btn--ghost">Read More →</a>
          </div>
        </article>

        <article class="card card--blue animate-on-scroll animate-delay-3">
          <img 
            src="https://static.wixstatic.com/media/52448e_1fabeb8274574bee8e901e3e4424a808~mv2.jpg" 
            alt="Transforming Lives" 
            class="card__image"
          >
          <div class="card__content">
            <h3 class="card__title">Transforming Lives</h3>
            <p class="card__text">Operation Restore Hope (ORH) is an Australian based surgical charity for less fortunate children in the Philippines with birth defects and deformities, especially cleft lip and palate.</p>
            <a href="#" class="btn btn--ghost">Read More →</a>
          </div>
        </article>
      </div>
    </div>
  </section>

  <!-- Transformations Section -->
  <section class="section transformations">
    <div class="container">
      <div class="section__header">
        <h2 class="section__title">Smiles Transformed</h2>
        <p class="section__subtitle">A single surgery can change a child's life forever. Our patients not only gain new smiles, but also the ability to eat, speak, and thrive. See the powerful transformations that give children hope and a brighter future.</p>
      </div>
      
      <div class="grid grid--3">
        <article class="transformation-card animate-on-scroll animate-delay-1">
          <div class="transformation__images">
            <div class="transformation__image">
              <img src="https://static.wixstatic.com/media/52448e_57e2b30e2ce04775aa634505ebb050d5~mv2.jpg" alt="Before transformation">
            </div>
            <div class="transformation__image">
              <img src="https://static.wixstatic.com/media/52448e_a48d207cc790455c8fe6fbd0e07e8877~mv2.jpg" alt="After transformation">
            </div>
          </div>
          <p class="transformation__label">before | after</p>
        </article>

        <article class="transformation-card animate-on-scroll animate-delay-2">
          <div class="transformation__images">
            <div class="transformation__image">
              <img src="https://static.wixstatic.com/media/52448e_57e2b30e2ce04775aa634505ebb050d5~mv2.jpg" alt="Before transformation">
            </div>
            <div class="transformation__image">
              <img src="https://static.wixstatic.com/media/52448e_a48d207cc790455c8fe6fbd0e07e8877~mv2.jpg" alt="After transformation">
            </div>
          </div>
          <p class="transformation__label">before | after</p>
        </article>

        <article class="transformation-card animate-on-scroll animate-delay-3">
          <div class="transformation__images">
            <div class="transformation__image">
              <img src="https://static.wixstatic.com/media/52448e_57e2b30e2ce04775aa634505ebb050d5~mv2.jpg" alt="Before transformation">
            </div>
            <div class="transformation__image">
              <img src="https://static.wixstatic.com/media/52448e_a48d207cc790455c8fe6fbd0e07e8877~mv2.jpg" alt="After transformation">
            </div>
          </div>
          <p class="transformation__label">before | after</p>
        </article>
      </div>
      
      <div class="text-center mt-xl animate-on-scroll animate-delay-4">
        <a href="#" class="btn btn--primary">See more transformations →</a>
      </div>
    </div>
  </section>

  <!-- Help Section -->
  <section class="section">
    <div class="container">
      <div class="section__header">
        <h2 class="section__title">How You Can Help</h2>
      </div>
      
      <div class="help-item animate-on-scroll animate-delay-1">
        <div class="help__image">
          <img 
            src="https://static.wixstatic.com/media/52448e_e7da5290ab7647e98427a196be292f92~mv2.png/v1/fill/w_368,h_289,fp_0.62_0.40,q_85,usm_0.66_1.00_0.01,enc_auto/cropdonateimasge.png" 
            alt="Make a donation to support our mission"
          >
        </div>
        <div class="help__content">
          <h3 class="help__title">DONATE</h3>
          <p class="help__description">Show your support today and help us bring hope and healing to children in need. Every donation makes a real difference in a child's life.</p>
          <a href="#" class="btn btn--accent">Make a donation →</a>
        </div>
      </div>

      <div class="help-item animate-on-scroll animate-delay-2">
        <div class="help__image">
          <img 
            src="https://static.wixstatic.com/media/52448e_fa00dddd4e5642b2b89213cc44273516~mv2.jpg/v1/fill/w_361,h_271,fp_0.38_0.26,q_80,usm_0.66_1.00_0.01,enc_auto/involved.jpg" 
            alt="Volunteer with Operation Restore Hope"
          >
        </div>
        <div class="help__content">
          <h3 class="help__title">VOLUNTEER</h3>
          <p class="help__description">Be part of our mission to bring hope and healing to children with cleft lip and palate. Join our dedicated team of volunteers.</p>
          <a href="#" class="btn btn--secondary">Get Involved →</a>
        </div>
      </div>
    </div>
  </section>

  <!-- Supporters Section -->
  <section class="section section--compact">
    <div class="container">
      <div class="section__header">
        <h2 class="section__title">Our Supporters</h2>
        <p class="section__subtitle">We are proudly supported by generous organizations and individuals who share our mission to transform lives.</p>
      </div>
      
      <div class="supporters-carousel animate-on-scroll">
        <div class="supporters-track" id="supportersTrack">
          <div class="supporter supporter-text">
            <span>CYNTHIA EVA HUJAR ORR</span>
          </div>
          <div class="supporter supporter-text">
            <span>THE MILL HOUSE FOUNDATION</span>
          </div>
          <div class="supporter supporter-text">
            <span>THE BLUESAND FOUNDATION</span>
          </div>
          <div class="supporter">
            <img src="https://static.wixstatic.com/media/52448e_24dcf92caac9475cbde5fc20bc6b33d0~mv2.jpg/v1/fill/w_89,h_43,al_c,q_80,usm_0.66_1.00_0.01,enc_auto/spon4.jpg" alt="Supporter organization">
          </div>
          <div class="supporter">
            <img src="https://static.wixstatic.com/media/52448e_8f1fc724293f4192b426a497c5869d96~mv2.jpg/v1/fill/w_90,h_78,al_c,q_80,usm_0.66_1.00_0.01,enc_auto/spon5.jpg" alt="Supporter organization">
          </div>
          <div class="supporter supporter-text">
            <span>ROTARY CLUB INTERNATIONAL</span>
          </div>
          <div class="supporter supporter-text">
            <span>LIONS CLUB FOUNDATION</span>
          </div>
          <div class="supporter supporter-text">
            <span>COMMUNITY HEALTH PARTNERS</span>
          </div>
        </div>
        
        <!-- Navigation Arrows -->
        <button class="carousel-nav carousel-nav--prev" id="prevBtn" aria-label="Previous supporters">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="15,18 9,12 15,6"></polyline>
          </svg>
        </button>
        <button class="carousel-nav carousel-nav--next" id="nextBtn" aria-label="Next supporters">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="9,18 15,12 9,6"></polyline>
          </svg>
        </button>
        
        <!-- Indicators -->
        <div class="carousel-indicators" id="carouselIndicators"></div>
      </div>
    </div>
  </section>

  <!-- Image Preview Modal -->
  <div id="imagePreviewModal" class="image-preview-modal">
    <div class="image-preview-content">
      <button class="image-preview-close" onclick="closeImagePreview()">&times;</button>
      <div class="image-preview-images">
        <div class="image-preview-single">
          <img id="previewImageBefore" src="" alt="Before transformation">
          <div class="image-preview-label">Before</div>
        </div>
        <div class="image-preview-separator"></div>
        <div class="image-preview-single">
          <img id="previewImageAfter" src="" alt="After transformation">
          <div class="image-preview-label">After</div>
        </div>
      </div>
      <div class="image-preview-info">
        <div class="image-preview-title" id="previewTitle">Transformation Journey</div>
        <div class="image-preview-description" id="previewDescription">See the incredible transformation that gives children hope and a brighter future through life-changing surgery.</div>
      </div>
    </div>
  </div>

  <script>
    // Scroll-triggered Animation System
    function initScrollAnimations() {
      // Create Intersection Observer
      const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
      };

      const observer = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
          if (entry.isIntersecting) {
            entry.target.classList.add('visible');
            entry.target.classList.add('animate-in');
          }
        });
      }, observerOptions);

      // Observe all elements with animation classes
      const animatedElements = document.querySelectorAll('.animate-on-scroll, .fade-in-on-scroll, .section__title, .section__subtitle, .welcome__text p, .welcome__video');
      
      animatedElements.forEach(function(element) {
        observer.observe(element);
      });
    }

    // Image Preview Functionality
    function openImagePreview(beforeImageSrc, afterImageSrc, title, description) {
      const modal = document.getElementById('imagePreviewModal');
      const previewImageBefore = document.getElementById('previewImageBefore');
      const previewImageAfter = document.getElementById('previewImageAfter');
      const previewTitle = document.getElementById('previewTitle');
      const previewDescription = document.getElementById('previewDescription');
      
      previewImageBefore.src = beforeImageSrc;
      previewImageAfter.src = afterImageSrc;
      previewTitle.textContent = title;
      previewDescription.textContent = description;
      
      modal.classList.add('active');
      document.body.style.overflow = 'hidden'; // Prevent background scrolling
    }

    function closeImagePreview() {
      const modal = document.getElementById('imagePreviewModal');
      modal.classList.remove('active');
      document.body.style.overflow = ''; // Restore scrolling
    }

    // Close modal when clicking outside the image
    document.getElementById('imagePreviewModal').addEventListener('click', function(e) {
      if (e.target === this) {
        closeImagePreview();
      }
    });

    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') {
        closeImagePreview();
      }
    });

    // Supporters Carousel Functionality
    function initSupportersCarousel() {
      const track = document.getElementById('supportersTrack');
      const prevBtn = document.getElementById('prevBtn');
      const nextBtn = document.getElementById('nextBtn');
      const indicatorsContainer = document.getElementById('carouselIndicators');
      
      if (!track || !prevBtn || !nextBtn || !indicatorsContainer) return;
      
      const supporters = track.querySelectorAll('.supporter');
      const supporterWidth = 208; // 200px min-width + 8px gap
      const visibleSupporters = Math.floor(track.parentElement.offsetWidth / supporterWidth) || 3;
      const totalSlides = Math.ceil(supporters.length / visibleSupporters);
      
      let currentSlide = 0;
      let autoPlayInterval;
      
      // Create indicators
      function createIndicators() {
        indicatorsContainer.innerHTML = '';
        for (let i = 0; i < totalSlides; i++) {
          const indicator = document.createElement('div');
          indicator.className = 'carousel-indicator';
          if (i === 0) indicator.classList.add('active');
          indicator.addEventListener('click', () => goToSlide(i));
          indicatorsContainer.appendChild(indicator);
        }
      }
      
      // Update indicators
      function updateIndicators() {
        const indicators = indicatorsContainer.querySelectorAll('.carousel-indicator');
        indicators.forEach((indicator, index) => {
          indicator.classList.toggle('active', index === currentSlide);
        });
      }
      
      // Go to specific slide
      function goToSlide(slideIndex) {
        currentSlide = slideIndex;
        const translateX = -(currentSlide * visibleSupporters * supporterWidth);
        track.style.transform = `translateX(${translateX}px)`;
        updateIndicators();
      }
      
      // Next slide
      function nextSlide() {
        currentSlide = (currentSlide + 1) % totalSlides;
        goToSlide(currentSlide);
      }
      
      // Previous slide
      function prevSlide() {
        currentSlide = (currentSlide - 1 + totalSlides) % totalSlides;
        goToSlide(currentSlide);
      }
      
      // Auto play
      function startAutoPlay() {
        autoPlayInterval = setInterval(nextSlide, 4000); // Change slide every 4 seconds
      }
      
      function stopAutoPlay() {
        clearInterval(autoPlayInterval);
      }
      
      // Event listeners
      nextBtn.addEventListener('click', () => {
        nextSlide();
        stopAutoPlay();
        setTimeout(startAutoPlay, 8000); // Restart autoplay after 8 seconds
      });
      
      prevBtn.addEventListener('click', () => {
        prevSlide();
        stopAutoPlay();
        setTimeout(startAutoPlay, 8000); // Restart autoplay after 8 seconds
      });
      
      // Pause autoplay on hover
      track.parentElement.addEventListener('mouseenter', stopAutoPlay);
      track.parentElement.addEventListener('mouseleave', startAutoPlay);
      
      // Touch/swipe support for mobile
      let startX = 0;
      let currentX = 0;
      let isDragging = false;
      
      track.addEventListener('touchstart', (e) => {
        startX = e.touches[0].clientX;
        isDragging = true;
        stopAutoPlay();
      });
      
      track.addEventListener('touchmove', (e) => {
        if (!isDragging) return;
        currentX = e.touches[0].clientX;
        const diffX = startX - currentX;
        
        // Add some resistance
        if (Math.abs(diffX) > 50) {
          if (diffX > 0) {
            nextSlide();
          } else {
            prevSlide();
          }
          isDragging = false;
        }
      });
      
      track.addEventListener('touchend', () => {
        isDragging = false;
        setTimeout(startAutoPlay, 8000);
      });
      
      // Keyboard navigation
      document.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowLeft') {
          prevSlide();
          stopAutoPlay();
          setTimeout(startAutoPlay, 8000);
        } else if (e.key === 'ArrowRight') {
          nextSlide();
          stopAutoPlay();
          setTimeout(startAutoPlay, 8000);
        }
      });
      
      // Responsive handling
      function handleResize() {
        const newVisibleSupporters = Math.floor(track.parentElement.offsetWidth / supporterWidth) || 1;
        if (newVisibleSupporters !== visibleSupporters) {
          location.reload(); // Simple solution for responsive changes
        }
      }
      
      window.addEventListener('resize', handleResize);
      
      // Initialize
      createIndicators();
      startAutoPlay();
    }

    // Initialize all functionality when DOM is loaded
    document.addEventListener('DOMContentLoaded', function() {
      // Initialize scroll animations
      initScrollAnimations();
      
      // Initialize supporters carousel
      initSupportersCarousel();

      // Add click event listeners to all transformation cards
      const transformationCards = document.querySelectorAll('.transformation-card');
      
      transformationCards.forEach(function(card, index) {
        card.addEventListener('click', function() {
          // Get both before and after images from this card
          const images = card.querySelectorAll('.transformation__image img');
          if (images.length >= 2) {
            const beforeImage = images[0]; // First image is before
            const afterImage = images[1];  // Second image is after
            
            const title = 'Transformation Journey';
            const description = 'See the incredible transformation that gives children hope and a brighter future through life-changing surgery.';
            
            openImagePreview(beforeImage.src, afterImage.src, title, description);
          }
        });
      });

      // Also add click listeners to individual images for backward compatibility
      const transformationImages = document.querySelectorAll('.transformation__image');
      
      transformationImages.forEach(function(imageContainer) {
        imageContainer.addEventListener('click', function(e) {
          e.stopPropagation(); // Prevent card click event
          
          // Find the parent card
          const card = imageContainer.closest('.transformation-card');
          if (card) {
            const images = card.querySelectorAll('.transformation__image img');
            if (images.length >= 2) {
              const beforeImage = images[0];
              const afterImage = images[1];
              
              const title = 'Transformation Journey';
              const description = 'See the incredible transformation that gives children hope and a brighter future through life-changing surgery.';
              
              openImagePreview(beforeImage.src, afterImage.src, title, description);
            }
          }
        });
      });
    });
  </script>

</body>
</html>