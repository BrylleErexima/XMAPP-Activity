document.addEventListener('DOMContentLoaded', function() {
  const forms = document.querySelectorAll('form');
  
  forms.forEach(form => {
    form.addEventListener('submit', function(e) {
      const password = form.querySelector('[name="password"]');
      
      if (password && password.value.length < 8) {
        alert('Password must be at least 8 characters!');
        e.preventDefault();
      }
    });
  });
});