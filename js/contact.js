// js/contact.js — client-side validation + server-side (SMTP) submit for contact.html
(function () {
  var ENDPOINT = 'submit.php';
  var CONTACT_EMAIL = 'contact@appmentech.in';

  document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('contact-form');
    if (!form) return;

    var messageEl = document.getElementById('form-message');
    var submitBtn = document.getElementById('form-submit');

    function showMessage(text, isError) {
      messageEl.textContent = text;
      messageEl.style.display = 'block';
      messageEl.style.color = isError ? '#DC2626' : '#16A34A';
    }

    function isValidEmail(value) {
      return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
    }

    function clearFieldErrors() {
      var invalidFields = form.querySelectorAll('.form-field.invalid');
      for (var i = 0; i < invalidFields.length; i++) {
        invalidFields[i].classList.remove('invalid');
      }
    }

    function markInvalid(input) {
      var field = input.closest('.form-field');
      if (field) field.classList.add('invalid');
    }

    function validate() {
      clearFieldErrors();
      var valid = true;

      var name = document.getElementById('field-name');
      if (!name.value.trim()) { markInvalid(name); valid = false; }

      var email = document.getElementById('field-email');
      if (!email.value.trim() || !isValidEmail(email.value.trim())) { markInvalid(email); valid = false; }

      var phone = document.getElementById('field-phone');
      if (phone.value.trim() && !/^[0-9+\s()-]+$/.test(phone.value.trim())) { markInvalid(phone); valid = false; }

      var description = document.getElementById('field-description');
      if (!description.value.trim()) { markInvalid(description); valid = false; }

      return valid;
    }

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      messageEl.style.display = 'none';

      if (!validate()) {
        showMessage('Please fill in Name, a valid Email, Project Description, and a valid Phone (if provided).', true);
        return;
      }

      submitBtn.disabled = true;
      submitBtn.textContent = 'Sending...';

      fetch(ENDPOINT, {
        method: 'POST',
        headers: { 'Accept': 'application/json' },
        body: new FormData(form)
      }).then(function (response) {
        return response.json().then(function (data) {
          return { ok: response.ok, data: data };
        }).catch(function () {
          return { ok: response.ok, data: {} };
        });
      }).then(function (result) {
        if (result.ok && result.data && result.data.ok) {
          form.reset();
          form.style.display = 'none';
          showMessage('Thank you — your project requirement has been sent. We will get back to you shortly.', false);
        } else {
          submitBtn.disabled = false;
          submitBtn.textContent = 'Send Project Requirement';
          var err = (result.data && result.data.error) ? result.data.error : 'Something went wrong sending your request.';
          showMessage(err + ' If it keeps happening, email us at ' + CONTACT_EMAIL + '.', true);
        }
      }).catch(function () {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Send Project Requirement';
        showMessage('Something went wrong sending your request. Please email ' + CONTACT_EMAIL + ' directly.', true);
      });
    });
  });
})();
