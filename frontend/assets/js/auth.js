// Auth utility functions for Cookboxd

const API_BASE = '/AmarCajdric/cookboxd/backend';

// Store token after login
function setToken(token) {
    localStorage.setItem('token', token);
}

// Get stored token
function getToken() {
    return localStorage.getItem('token');
}

// Remove token on logout
function removeToken() {
    localStorage.removeItem('token');
    localStorage.removeItem('user');
}

// Store user data
function setUser(user) {
    localStorage.setItem('user', JSON.stringify(user));
}

// Get user data
function getUser() {
    const user = localStorage.getItem('user');
    return user ? JSON.parse(user) : null;
}

// Check if user is logged in
function isLoggedIn() {
    return getToken() !== null;
}

// Check if user is admin
function isAdmin() {
    const user = getUser();
    return user && user.role === 'admin';
}

// Make authenticated API request
async function apiRequest(endpoint, options = {}) {
    const token = getToken();
    
    const headers = {
        'Content-Type': 'application/json',
        ...(token && { 'Authentication': token })
    };
    
    const response = await fetch(API_BASE + endpoint, {
        ...options,
        headers: {
            ...headers,
            ...options.headers
        }
    });
    
    if (response.status === 401) {
        // Token expired or invalid
        removeToken();
        window.location.hash = '#login';
        throw new Error('Session expired. Please login again.');
    }
    
    return response;
}

// Login function
async function login(email, password) {
    const response = await fetch(API_BASE + '/auth/login', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email, password })
    });
    
    const result = await response.json();
    
    if (response.ok && result.data) {
        setToken(result.data.token);
        setUser(result.data);
        return { success: true, data: result.data };
    }
    
    return { success: false, error: result.message || 'Login failed' };
}

// Register function
async function register(username, email, password) {
    const response = await fetch(API_BASE + '/auth/register', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ username, email, password })
    });
    
    const result = await response.json();
    
    if (response.ok) {
        return { success: true, data: result.data };
    }
    
    return { success: false, error: result.message || 'Registration failed' };
}

// Logout function
function logout() {
    removeToken();
    window.location.hash = '#home';
    updateNavForAuth();
}

// Update navigation based on auth state
function updateNavForAuth() {
    const guestLinks = document.querySelectorAll('.auth-guest');
    const userLinks = document.querySelectorAll('.auth-user');
    
    if (isLoggedIn()) {
        guestLinks.forEach(link => link.style.display = 'none');
        userLinks.forEach(link => link.style.display = 'inline-block');
    } else {
        guestLinks.forEach(link => link.style.display = 'inline-block');
        userLinks.forEach(link => link.style.display = 'none');
    }
}

// Initialize auth on page load
document.addEventListener('DOMContentLoaded', updateNavForAuth);
