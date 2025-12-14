// frontend/js/router.js

const app = document.getElementById('app');
if (!app) {
  console.error('Router: #app container not found.');
}

// Default route
const DEFAULT_ROUTE = 'home';

// Define base path for views
const VIEWS_BASE = '/AmarCajdric/cookboxd/frontend/views/';

// Map of routes to view files
const routes = {
  home: 'home.html',
  recipes: 'recipes.html',
  'recipe-detail': 'recipe-detail.html',
  'create-recipe': 'create-recipe.html',
  login: 'login.html',
  register: 'register.html',
  about: 'about.html',
  contact: 'contact.html',
  '404': '404.html'
};

function showLoader() {
  if (!app) return;
  app.innerHTML = `
    <div class="d-flex justify-content-center align-items-center" style="height:60vh">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
    </div>
  `;
}

function getHash() {
  const hash = window.location.hash.replace(/^#\/?/, '').trim();
  // Handle dynamic routes like recipe-detail/carbonara
  const parts = hash.split('/');
  return parts[0] || '';
}

function updateActiveLink(page) {
  const links = document.querySelectorAll('nav a[href^="#"]');
  links.forEach(link => {
    const target = link.getAttribute('href').replace(/^#\/?/, '');
    link.classList.toggle('active', page && target === page);
  });
}

async function loadPage(page) {
  if (!app) return;
  
  showLoader();
  
  const key = routes[page] ? page : '404';
  const viewPath = VIEWS_BASE + routes[key];
  
  try {
    const res = await fetch(viewPath, { cache: 'no-store' });
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    
    const html = await res.text();
    app.innerHTML = html;
    
    window.scrollTo({ top: 0, behavior: 'smooth' });
    
    if (window.AOS && typeof window.AOS.refresh === 'function') {
      window.AOS.refresh();
    }
    
    updateActiveLink(key === '404' ? null : page);
  } catch (err) {
    console.error('Router load error:', err);
    app.innerHTML = `
      <div class="alert alert-danger text-center mt-5" role="alert">
        Sorry, something went wrong and the page could not be loaded.
      </div>
    `;
    updateActiveLink(null);
  }
}

function router() {
  const page = getHash() || DEFAULT_ROUTE;
  loadPage(page);
}

window.addEventListener('hashchange', router);
document.addEventListener('DOMContentLoaded', router);
