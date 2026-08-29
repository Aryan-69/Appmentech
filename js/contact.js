// js/contact.js — client-side validation + mailto submission for contact.html
(function () {
  var CONTACT_EMAIL = 'administrator@appmentech.in';

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

    function val(id) {
      var el = document.getElementById(id);
      return el ? el.value.trim() : '';
    }

    function buildBody() {
      var lines = [
        'Name: ' + val('field-name'),
        'Company: ' + val('field-company'),
        'Email: ' + val('field-email'),
        'Phone: ' + val('field-phone'),
        'Industry: ' + val('field-industry'),
        'Solution Required: ' + val('field-solution'),
        'Estimated Budget: ' + val('field-budget'),
        'Project Timeline: ' + val('field-timeline'),
        '',
        'Project Description:',
        val('field-description')
      ];
      return lines.join('\n');
    }

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      messageEl.style.display = 'none';

      if (!validate()) {
        showMessage('Please fill in Name, a valid Email, Project Description, and a valid Phone (if provided).', true);
        return;
      }

      var subject = 'Project Requirement from ' + val('field-name');
      var mailto = 'mailto:' + CONTACT_EMAIL +
        '?subject=' + encodeURIComponent(subject) +
        '&body=' + encodeURIComponent(buildBody());

      window.location.href = mailto;

      showMessage('Opening your email app to send to ' + CONTACT_EMAIL +
        '. If nothing opens, please email us there directly.', false);
    });
  });
})();
