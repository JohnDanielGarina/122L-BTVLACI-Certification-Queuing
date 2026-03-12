// Modern UI Interactions & Validation
document.addEventListener('DOMContentLoaded', function() {
  // Enhanced form validation
  const forms = document.querySelectorAll('form[data-validate]');
  forms.forEach(form => {
    form.addEventListener('submit', function(e) {
      if (!this.checkValidity()) {
        e.preventDefault();
        showMessage('Please complete all required fields correctly.', 'error');
        return false;
      }
      // Add loading state
      form.classList.add('loading');
    });
  });

  // Dynamic NCIII field
  const qualSelects = document.querySelectorAll('#qualification');
  qualSelects.forEach(select => {
    const nc2Group = document.querySelector('#nc2_group') || select.parentElement.querySelector('.nc2-group');
    if (nc2Group) {
      select.addEventListener('change', function() {
        nc2Group.style.display = this.value === 'NCIII' ? 'block' : 'none';
      });
    }
  });

  // File upload validation & preview
  const fileInputs = document.querySelectorAll('input[type=\"file\"]');
  fileInputs.forEach(input => {
    input.addEventListener('change', function(e) {
      const file = e.target.files[0];
      const maxSize = 5 * 1024 * 1024; // 5MB
      const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'];
      
      if (file) {
        if (file.size > maxSize) {
          showMessage('File size must be under 5MB.', 'error');
          this.value = '';
          return;
        }
        if (!allowedTypes.includes(file.type)) {
          showMessage('Only PDF, JPG, PNG allowed.', 'error');
          this.value = '';
          return;
        }
        showMessage('File selected: ' + file.name, 'success');
        // Preview for images
        if (file.type.startsWith('image/')) {
          const reader = new FileReader();
          reader.onload = function(ev) {
            let preview = document.querySelector('#' + input.id + '_preview');
            if (!preview) {
              preview = document.createElement('img');
              preview.id = input.id + '_preview';
              preview.style.cssText = 'max-width:200px;max-height:200px;margin-top:10px;border-radius:10px;';
              input.parentNode.appendChild(preview);
            }
            preview.src = ev.target.result;
          };
          reader.readAsDataURL(file);
        }
      }
    });
  });

  // Status refresh (for lazy checks)
  const refreshBtns = document.querySelectorAll('.refresh-status');
  refreshBtns.forEach(btn => {
    btn.addEventListener('click', function() {
      location.reload();
    });
  });

  // Smooth scroll
  document.querySelectorAll('a[href^=\"#\"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
      e.preventDefault();
      document.querySelector(this.getAttribute('href')).scrollIntoView({ behavior: 'smooth' });
    });
  });
});

function showMessage(msg, type) {
  const existing = document.querySelector('.temp-message');
  if (existing) existing.remove();
  
  const div = document.createElement('div');
  div.className = `message ${type} temp-message`;
  div.textContent = msg;
  document.querySelector('.container')?.prepend(div);
  
  setTimeout(() => div.remove(), 5000);
}

