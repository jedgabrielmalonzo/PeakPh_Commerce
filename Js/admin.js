  // ================== INVENTORY ============================== //
// Show Add Product Modal
function showAddProductModal() {
  document.getElementById('addProductModal').style.display = 'flex';
  document.body.style.overflow = 'hidden'; // Prevent background scroll
}

// Hide Add Product Modal
function hideAddProductModal() {
  document.getElementById('addProductModal').style.display = 'none';
  document.body.style.overflow = 'auto'; // Re-enable scroll
  // Reset the form and drag-drop area if the functions exist
  if (typeof resetAddProductModal === 'function') {
    resetAddProductModal();
  }
}

// Toggle Add Product Form (legacy function for backward compatibility)
function toggleAddForm() {
  const form = document.getElementById('addProductForm');
  if (form) {
    form.classList.toggle('hidden');
  } else {
    // If old form doesn't exist, use modal instead
    showAddProductModal();
  }
}

// Show Edit Product Modal
function showEditForm(product) {
  document.getElementById('editProductModal').style.display = 'flex';
  document.getElementById('edit_id').value = product.id;
  document.getElementById('edit_name').value = product.product_name;
  document.getElementById('edit_price').value = product.price;
  document.getElementById('edit_stock').value = product.stock;
  document.getElementById('edit_tag').value = product.tag;
  
  // Populate new fields
  document.getElementById('edit_description').value = product.description || '';
  document.getElementById('edit_specifications').value = product.specifications || '';
  document.getElementById('edit_dimensions').value = product.dimensions || '';
  document.getElementById('edit_weight').value = product.weight || '';
  document.getElementById('edit_video_url').value = product.video_url || '';
  
  // Show current image preview if exists
  const imagePreview = document.getElementById('current_image_preview');
  if (product.image && product.image !== '') {
    imagePreview.src = '../' + product.image;
    imagePreview.style.display = 'block';
  } else {
    imagePreview.style.display = 'none';
  }
  
  // Show additional images if they exist
  const additionalImagesDiv = document.getElementById('editAdditionalImages');
  additionalImagesDiv.innerHTML = '';
  
  if (product.additional_images) {
    let images = [];
    try {
      images = JSON.parse(product.additional_images);
    } catch (e) {
      console.error('Error parsing additional_images:', e);
    }
    
    if (Array.isArray(images) && images.length > 0) {
      images.forEach((imagePath, index) => {
        const imgDiv = document.createElement('div');
        imgDiv.className = 'additional-image-preview';
        imgDiv.innerHTML = `
          <img src="../${imagePath}" alt="Additional image ${index + 1}">
          <button type="button" class="remove-additional-image" onclick="removeAdditionalImage(${product.id}, '${imagePath}', this)">×</button>
        `;
        additionalImagesDiv.appendChild(imgDiv);
      });
    }
  }
}

// Hide Edit Product Modal
function hideEditProductModal() {
  document.getElementById('editProductModal').style.display = 'none';
  document.body.style.overflow = 'auto'; // Re-enable scroll
  // Reset the form and drag-drop area if the functions exist
  if (typeof resetEditProductModal === 'function') {
    resetEditProductModal();
  }
}

// Legacy function for backward compatibility
function toggleEditForm() {
  hideEditProductModal();
}

// Delete Product
function deleteProduct(id) {
  if (confirm("Are you sure you want to delete this product?")) {
    window.location.href = `inventory_delete.php?id=${id}`;
  }
}

// Show Label Menu
function showLabelMenu(id, button, currentLabel) {
  const menu = document.getElementById('labelMenu');
  menu.style.display = 'block';
  menu.style.position = 'absolute';
  menu.style.top = (button.getBoundingClientRect().bottom + window.scrollY) + 'px';
  menu.style.left = (button.getBoundingClientRect().left + window.scrollX) + 'px';
  document.getElementById('label_id').value = id;
}

// Hide Label Menu if clicked outside
document.addEventListener('click', function(e) {
  const menu = document.getElementById('labelMenu');
  if (!menu.contains(e.target) && !e.target.classList.contains('tag-btn')) {
    menu.style.display = 'none';
  }
});

// Close modals when clicking outside
document.addEventListener('click', function(e) {
  const addModal = document.getElementById('addProductModal');
  const editModal = document.getElementById('editProductModal');
  
  if (addModal && e.target === addModal) {
    hideAddProductModal();
  }
  
  if (editModal && e.target === editModal) {
    hideEditProductModal();
  }
});

// Close modals with Escape key
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    const addModal = document.getElementById('addProductModal');
    const editModal = document.getElementById('editProductModal');
    
    if (addModal && addModal.style.display === 'flex') {
      hideAddProductModal();
    }
    
    if (editModal && editModal.style.display === 'flex') {
      hideEditProductModal();
    }
  }
});


//   ===================  USERS MANAGEMENT  =========================//
function deleteUser(userId) {
  let table = document.getElementById("usersTable");
  for (let i = 0; i < table.rows.length; i++) {
    if (table.rows[i].cells[0].innerText == userId) {
      table.deleteRow(i);
      alert("User ID " + userId + " deleted!");
      break;
    }
  }
}

function bypassUser(userId) {
  let table = document.getElementById("usersTable");
  for (let i = 0; i < table.rows.length; i++) {
    if (table.rows[i].cells[0].innerText == userId) {
      table.rows[i].cells[4].innerText = "Bypassed";
      alert("User ID " + userId + " has been bypassed!");
      break;
    }
  }
}


// ======================== DASHBOARD ================================//
// ===== LOGOUT FUNCTION =====
function logout() {
  if (confirm("Are you sure you want to logout?")) {
    // Detect current path and use appropriate logout path
    let currentPath = window.location.pathname;
    let logoutPath = "logout.php";
    
    // If we're in a subdirectory (like inventory/ or content/), use ../logout.php
    if (currentPath.includes("/inventory/") || currentPath.includes("/content/") || currentPath.includes("/users/")) {
      logoutPath = "../logout.php";
    }
    
    window.location.href = logoutPath;
  }
}

// ===== DASHBOARD CHART =====
const ctx = document.getElementById('salesChart');
if (ctx) {
  // Default dataset (Week)
  let currentData = [12000, 15000, 10000, 18000, 22000, 20000, 25000];
  let currentLabels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

  const salesChart = new Chart(ctx, {
    type: 'line',
    data: {
      labels: currentLabels,
      datasets: [{
        label: 'Sales (₱)',
        data: currentData,
        borderColor: '#27ae60',
        backgroundColor: 'rgba(39, 174, 96, 0.2)',
        fill: true,
        tension: 0.4,
        pointBackgroundColor: '#27ae60'
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: {
          labels: { color: '#14532d', font: { family: 'Poppins' } }
        }
      },
      scales: {
        x: { ticks: { color: '#14532d' }, grid: { color: '#e0e0e0' } },
        y: { ticks: { color: '#14532d' }, grid: { color: '#e0e0e0' } }
      }
    }
  });

  // ===== TAB SWITCHING =====
  const weekBtn = document.querySelector('.chart-tabs button:nth-child(1)');
  const monthBtn = document.querySelector('.chart-tabs button:nth-child(2)');
  const yearBtn = document.querySelector('.chart-tabs button:nth-child(3)');

  if (weekBtn && monthBtn && yearBtn) {
    weekBtn.addEventListener('click', () => updateChart(
      ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
      [12000, 15000, 10000, 18000, 22000, 20000, 25000],
      weekBtn
    ));

    monthBtn.addEventListener('click', () => updateChart(
      ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
      [60000, 72000, 54000, 85000],
      monthBtn
    ));

    yearBtn.addEventListener('click', () => updateChart(
      ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
      [200000, 180000, 240000, 220000, 260000, 280000, 300000, 270000, 250000, 310000, 330000, 350000],
      yearBtn
    ));
  }

  function updateChart(newLabels, newData, activeBtn) {
    salesChart.data.labels = newLabels;
    salesChart.data.datasets[0].data = newData;
    salesChart.update();

    // Update active button styling
    document.querySelectorAll('.chart-tabs button').forEach(btn => btn.classList.remove('active'));
    activeBtn.classList.add('active');
  }
}

// ===== CLOCK (Right Sidebar) =====
function updateClockSidebar() {
  const timeEl = document.getElementById('time');
  const dateEl = document.getElementById('date');
  const dayEl = document.getElementById('day');
  if (!timeEl || !dateEl || !dayEl) return;

  const now = new Date();
  const optionsDate = { year: 'numeric', month: 'long', day: 'numeric' };
  const optionsDay = { weekday: 'long' };

  timeEl.textContent = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
  dateEl.textContent = now.toLocaleDateString('en-US', optionsDate);
  dayEl.textContent = now.toLocaleDateString('en-US', optionsDay);
}
setInterval(updateClockSidebar, 1000);
updateClockSidebar();

  
  // added 
  
  function showSection(sectionId, event) {
  if (event) event.preventDefault();

  // Hide all sections
  document.querySelectorAll(".section").forEach(sec => sec.classList.remove("active"));

  // Show the selected section
  document.getElementById(sectionId).classList.add("active");

  // Remove active class from all menu links
  document.querySelectorAll(".sidebar .menu-link").forEach(link => link.classList.remove("active"));

  // Add active class to clicked link
  event.currentTarget.classList.add("active");
}

function toggleContentManager() {
      var links = document.getElementById('contentManagerLinks');
      var arrow = document.getElementById('arrow');
      if (links.style.display === "none") {
        links.style.display = "block";
        arrow.innerHTML = "&#9650;";
      } else {
        links.style.display = "none";
        arrow.innerHTML = "&#9660;";
      }
    }
// ========================= MINI VIEW FUNCTIONS ========================//
function updateBanner() {
  const bannerTitle = document.getElementById('bannerTitle').value;
  if (!bannerTitle.trim()) {
    alert('Please enter a banner title');
    return;
  }
  
  // For now, just show an alert. This can be connected to a backend endpoint later
  alert('Banner update functionality will be implemented soon.\nTitle: ' + bannerTitle);
  
  // Clear the input
  document.getElementById('bannerTitle').value = '';
  
  // Refresh the preview (optional)
  const iframe = document.getElementById('homepagePreview');
  if (iframe) {
    iframe.src = iframe.src; // Force reload
  }
}

// ========================= MODAL ENHANCEMENTS ========================//
// Close modals when clicking outside
document.addEventListener('DOMContentLoaded', function() {
  const addModal = document.getElementById('addProductModal');
  const editModal = document.getElementById('editProductModal');
  
  if (addModal) {
    addModal.addEventListener('click', function(e) {
      if (e.target === this) {
        hideAddProductModal();
      }
    });
  }
  
  if (editModal) {
    editModal.addEventListener('click', function(e) {
      if (e.target === this) {
        hideEditProductModal();
      }
    });
  }
  
  // Close modals with ESC key
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
      if (addModal && addModal.style.display === 'flex') {
        hideAddProductModal();
      }
      if (editModal && editModal.style.display === 'flex') {
        hideEditProductModal();
      }
    }
  });
});

// ============================= ADMIN JS =================================//

