/**
 * DELIBERATELY VULNERABLE CLIENT-SIDE CODE - FOR EDUCATIONAL PURPOSES
 * Demonstrates DOM-based XSS vulnerability
 * 
 * VULNERABILITIES:
 * 1. Using innerHTML with unsanitized data (DOM XSS)
 * 2. Using eval() with user input
 * 3. No input validation or sanitization
 */

// VULNERABILITY 1: DOM-based XSS using innerHTML
function updateUserProfile() {
    // This simulates getting user data from URL/API
    const urlParams = new URLSearchParams(window.location.search);
    const username = urlParams.get('username') || 'Guest';
    const bio = urlParams.get('bio') || 'No bio set';
    
    // CRITICAL VULNERABILITY: Using innerHTML with unsanitized user input
    document.getElementById('profileContainer').innerHTML = `
        <div class="profile">
            <h2>Welcome, ${username}!</h2>
            <p>${bio}</p>
        </div>
    `;
}

// VULNERABILITY 2: Using eval() with user input (EXTREMELY DANGEROUS)
function evaluateExpression(expression) {
    // NEVER DO THIS IN REAL CODE!
    try {
        // CRITICAL VULNERABILITY: eval() with unsanitized user input
        return eval(expression);
    } catch (e) {
        console.log(e);
    }
}

// VULNERABILITY 3: Direct DOM manipulation without sanitization
function displayUserComment(comment) {
    const commentDiv = document.createElement('div');
    
    // VULNERABILITY: Setting textContent to HTML (should use textContent instead of innerHTML)
    commentDiv.innerHTML = '<strong>User Comment:</strong> ' + comment;
    
    document.getElementById('commentsSection').appendChild(commentDiv);
}

// VULNERABILITY 4: jQuery with HTML insertion
function loadUserData(userId) {
    // Simulating AJAX response without sanitization
    const userData = {
        name: getQueryParam('name'),
        email: getQueryParam('email'),
        website: getQueryParam('website')
    };
    
    // VULNERABLE: Using jQuery html() with unsanitized data
    $('#userCard').html(`
        <h3>${userData.name}</h3>
        <p>Email: ${userData.email}</p>
        <a href="${userData.website}">Visit Website</a>
    `);
}

// Helper function - also vulnerable
function getQueryParam(param) {
    // No sanitization, returns raw user input
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get(param) || '';
}

// VULNERABILITY 5: DOM Clobbering vulnerability
function getElementById(id) {
    // If user passes HTML string, it could clobber the DOM
    return document.getElementById(id);
}

// VULNERABILITY 6: Dangerous template literal usage
function renderCart(items) {
    let html = '';
    
    items.forEach(item => {
        // VULNERABLE: No escaping in template
        html += `<div onclick="alert('${item.name}');">${item.name}</div>`;
    });
    
    return html;
}

// Test function - demonstrates vulnerability
function demonstrateVulnerability() {
    console.log("===== DOM XSS Vulnerability Demo =====");
    console.log("Try these payloads:");
    console.log("1. updateUserProfile() with: ?username=<img src=x onerror=alert('XSS')>");
    console.log("2. evaluateExpression() can execute: alert('XSS')");
    console.log("3. displayUserComment() with: <script>alert('XSS')</script>");
}

// Auto-run on page load (if element exists)
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('profileContainer')) {
        updateUserProfile();
    }
});
