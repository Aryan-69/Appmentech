// js/contact.js — contact form: searchable dropdowns, validation, attachment
// checks and the SMTP/OneDrive submit for contact.html.
(function () {
  var ENDPOINT = 'submit.php';
  var CONTACT_EMAIL = 'contact@appmentech.in';
  var MAX_BYTES = 10 * 1024 * 1024;
  var ALLOWED_EXT = ['pdf', 'png', 'jpg', 'jpeg', 'docx', 'xlsx', 'pptx', 'txt'];
  var FILE_RULES = 'PDF, PNG, JPG, JPEG, DOCX, XLSX, PPTX, TXT &nbsp;&middot;&nbsp; Maximum file size: 10 MB';

  // Industries — the site's "What Services We Provide" menu.
  var INDUSTRIES = [
    'E-Commerce & Retails Websites', 'Healthcare', 'Education',
    'Media & Entertainment', 'Transportation & Logistics', 'Enterprise',
    'Travel & Hospitality', 'Real Estate', 'Food & Hospitality', 'Government'
  ];

  // Solutions — the site's "Our Industry Solutions" menu.
  var SOLUTIONS = [
    'Web Development', 'Mobile Applications', 'SaaS Products', 'AI & GenAI',
    'Cloud Solutions', 'Automation', 'API & Integration',
    'Testing & Quality Engineering'
  ];

  var TIMELINES = ['Immediate', '1-3 months', '3-6 months', '6-12 months', '12+ months'];

  // Dial codes. flag is the regional-indicator pair for the ISO code.
  var COUNTRIES = [
    ['IN', 'India', '+91'], ['US', 'United States', '+1'], ['CA', 'Canada', '+1'],
    ['GB', 'United Kingdom', '+44'], ['IE', 'Ireland', '+353'], ['AU', 'Australia', '+61'],
    ['NZ', 'New Zealand', '+64'], ['SG', 'Singapore', '+65'], ['MY', 'Malaysia', '+60'],
    ['AE', 'United Arab Emirates', '+971'], ['SA', 'Saudi Arabia', '+966'],
    ['QA', 'Qatar', '+974'], ['KW', 'Kuwait', '+965'], ['BH', 'Bahrain', '+973'],
    ['OM', 'Oman', '+968'], ['DE', 'Germany', '+49'], ['FR', 'France', '+33'],
    ['NL', 'Netherlands', '+31'], ['BE', 'Belgium', '+32'], ['ES', 'Spain', '+34'],
    ['IT', 'Italy', '+39'], ['PT', 'Portugal', '+351'], ['CH', 'Switzerland', '+41'],
    ['AT', 'Austria', '+43'], ['SE', 'Sweden', '+46'], ['NO', 'Norway', '+47'],
    ['DK', 'Denmark', '+45'], ['FI', 'Finland', '+358'], ['PL', 'Poland', '+48'],
    ['CZ', 'Czechia', '+420'], ['RO', 'Romania', '+40'], ['GR', 'Greece', '+30'],
    ['TR', 'Turkey', '+90'], ['IL', 'Israel', '+972'], ['ZA', 'South Africa', '+27'],
    ['NG', 'Nigeria', '+234'], ['KE', 'Kenya', '+254'], ['EG', 'Egypt', '+20'],
    ['BR', 'Brazil', '+55'], ['MX', 'Mexico', '+52'], ['AR', 'Argentina', '+54'],
    ['CL', 'Chile', '+56'], ['CO', 'Colombia', '+57'], ['JP', 'Japan', '+81'],
    ['KR', 'South Korea', '+82'], ['CN', 'China', '+86'], ['HK', 'Hong Kong', '+852'],
    ['TW', 'Taiwan', '+886'], ['TH', 'Thailand', '+66'], ['VN', 'Vietnam', '+84'],
    ['ID', 'Indonesia', '+62'], ['PH', 'Philippines', '+63'], ['BD', 'Bangladesh', '+880'],
    ['PK', 'Pakistan', '+92'], ['LK', 'Sri Lanka', '+94'], ['NP', 'Nepal', '+977']
  ];

  function countryLabel(c) {
    return c[1] + ' (' + c[2] + ')';
  }

  /**
   * Attach a searchable dropdown to a combobox input. Typing filters the list;
   * anything the visitor types that is not in the list is still accepted.
   * onPick is called with the chosen option object when one is selected.
   */
  function combobox(root, options, onPick) {
    var input = root.querySelector('.combo-input');
    var list = root.querySelector('.combo-list');
    var active = -1;

    function labelOf(o) { return typeof o === 'string' ? o : o.label; }
    // What the input shows once an option is chosen. The dial-code combobox
    // shows just the ISO code while still searching on the full label.
    function displayOf(o) {
      return (o && o.display) ? o.display : labelOf(o);
    }

    function render(filter) {
      var q = (filter || '').toLowerCase();
      var matches = options.filter(function (o) {
        return labelOf(o).toLowerCase().indexOf(q) !== -1;
      });
      list.innerHTML = '';
      if (!matches.length) {
        var empty = document.createElement('li');
        empty.className = 'combo-empty';
        empty.textContent = 'No match — your entry is kept as typed';
        list.appendChild(empty);
        active = -1;
        return matches;
      }
      matches.forEach(function (o) {
        var li = document.createElement('li');
        li.setAttribute('role', 'option');
        li.setAttribute('aria-selected', 'false');
        if (o && o.badge) {
          var badge = document.createElement('span');
          badge.className = 'combo-badge';
          badge.textContent = o.badge;
          li.appendChild(badge);
        }
        li.appendChild(document.createTextNode(labelOf(o)));
        li.option = o;
        li.addEventListener('mousedown', function (e) {
          e.preventDefault(); // keep focus so blur does not close first
          pick(o);
        });
        list.appendChild(li);
      });
      active = -1;
      return matches;
    }

    function open(filter) {
      var matches = render(filter);
      list.hidden = false;
      input.setAttribute('aria-expanded', 'true');
      return matches;
    }

    function close() {
      list.hidden = true;
      input.setAttribute('aria-expanded', 'false');
      active = -1;
    }

    function highlight(i) {
      var items = list.querySelectorAll('li[role="option"]');
      if (!items.length) return;
      if (i < 0) i = items.length - 1;
      if (i >= items.length) i = 0;
      for (var n = 0; n < items.length; n++) {
        items[n].setAttribute('aria-selected', n === i ? 'true' : 'false');
      }
      items[i].scrollIntoView({ block: 'nearest' });
      active = i;
    }

    var chosen = null;

    function pick(o) {
      chosen = o;
      input.value = displayOf(o);
      close();
      if (onPick) onPick(o);
    }

    input.addEventListener('focus', function () {
      // Typing replaces the compact display, so start from an open list.
      if (chosen && displayOf(chosen) !== labelOf(chosen)) input.value = '';
      open('');
    });
    input.addEventListener('input', function () { open(input.value); });
    input.addEventListener('blur', function () {
      setTimeout(function () {
        close();
        // Nothing new picked: put the previous display back.
        if (chosen && input.value !== displayOf(chosen)) input.value = displayOf(chosen);
      }, 120);
    });
    input.addEventListener('keydown', function (e) {
      if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
        e.preventDefault();
        if (list.hidden) open(input.value);
        highlight(active + (e.key === 'ArrowDown' ? 1 : -1));
        return;
      }
      if (e.key === 'Enter' && !list.hidden && active > -1) {
        e.preventDefault();
        var items = list.querySelectorAll('li[role="option"]');
        if (items[active] && items[active].option) pick(items[active].option);
        return;
      }
      if (e.key === 'Escape') close();
    });
  }

  // Email: shape plus a plausible TLD (letters only, 2+ chars) on the domain.
  function isValidEmail(value) {
    if (!/^[^\s@]+@[^\s@]+$/.test(value)) return false;
    var domain = value.split('@').pop();
    return /^[a-z0-9]([a-z0-9-]*[a-z0-9])?(\.[a-z0-9]([a-z0-9-]*[a-z0-9])?)*\.[a-z]{2,}$/i.test(domain);
  }

  function extensionOf(name) {
    var parts = name.split('.');
    return parts.length > 1 ? parts.pop().toLowerCase() : '';
  }

  function formatSize(bytes) {
    return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
  }

  document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('contact-form');
    if (!form) return;

    var messageEl = document.getElementById('form-message');
    var submitBtn = document.getElementById('form-submit');

    // --- searchable dropdowns ---------------------------------------------
    combobox(document.querySelector('[data-combo="industry"]'), INDUSTRIES);
    combobox(document.querySelector('[data-combo="solution"]'), SOLUTIONS);
    combobox(document.querySelector('[data-combo="timeline"]'), TIMELINES);

    var dialCountry = document.getElementById('field-phone-country');
    var dialCode = document.getElementById('field-phone-code');
    combobox(
      document.querySelector('[data-combo="dial"]'),
      COUNTRIES.map(function (c) {
        return { label: countryLabel(c), display: c[0], badge: c[0], iso: c[0], dial: c[2] };
      }),
      function (o) {
        dialCountry.value = o.iso;
        dialCode.value = o.dial;
      }
    );

    // --- budget: guidance disables the amount ------------------------------
    var guidance = document.getElementById('field-budget-guidance');
    var budget = document.getElementById('field-budget');
    guidance.addEventListener('change', function () {
      budget.disabled = guidance.checked;
      if (guidance.checked) budget.value = '';
    });

    // --- best time to contact: shown local, submitted as UTC ---------------
    var bestTime = document.getElementById('field-best-time');
    var bestTz = document.getElementById('field-best-time-tz');
    var bestUtc = document.getElementById('field-best-time-utc');

    var tz = 'UTC';
    try {
      tz = Intl.DateTimeFormat().resolvedOptions().timeZone || 'UTC';
    } catch (e) { /* older browsers: fall back to the raw offset below */ }
    bestTz.value = tz;

    function toUtcWindow(range) {
      if (!range) return '';
      var parts = range.split('-');
      // Offset for today, in minutes west of UTC (what getTimezoneOffset returns).
      var offset = new Date().getTimezoneOffset();
      return parts.map(function (hhmm) {
        var bits = hhmm.split(':');
        var minutes = (parseInt(bits[0], 10) * 60 + parseInt(bits[1], 10) + offset + 1440) % 1440;
        var h = String(Math.floor(minutes / 60));
        var m = String(minutes % 60);
        return (h.length < 2 ? '0' + h : h) + ':' + (m.length < 2 ? '0' + m : m);
      }).join('-') + ' UTC';
    }

    function syncBestTime() {
      // Shown to the visitor in their own timezone, submitted as UTC.
      bestUtc.value = toUtcWindow(bestTime.value);
    }
    bestTime.addEventListener('change', syncBestTime);
    syncBestTime();

    // --- attachment ---------------------------------------------------------
    var fileInput = document.getElementById('field-attachment');
    var fileNote = document.getElementById('attachment-note');

    function checkFile() {
      var f = fileInput.files && fileInput.files[0];
      fileNote.classList.remove('error');
      if (!f) {
        fileNote.innerHTML = FILE_RULES;
        return true;
      }
      if (ALLOWED_EXT.indexOf(extensionOf(f.name)) === -1) {
        fileNote.textContent = 'That file type is not allowed. Use PDF, PNG, JPG, JPEG, DOCX, XLSX, PPTX or TXT.';
        fileNote.classList.add('error');
        return false;
      }
      if (f.size > MAX_BYTES) {
        fileNote.textContent = 'That file is ' + formatSize(f.size) + '. The maximum is 10 MB.';
        fileNote.classList.add('error');
        return false;
      }
      fileNote.textContent = f.name + ' — ' + formatSize(f.size);
      return true;
    }
    fileInput.addEventListener('change', checkFile);

    // --- validation ---------------------------------------------------------
    function showMessage(text, isError) {
      messageEl.textContent = text;
      messageEl.style.display = 'block';
      messageEl.style.color = isError ? '#DC2626' : '#16A34A';
    }

    function clearFieldErrors() {
      var invalid = form.querySelectorAll('.form-field.invalid');
      for (var i = 0; i < invalid.length; i++) invalid[i].classList.remove('invalid');
    }

    function markInvalid(input) {
      var field = input.closest('.form-field');
      if (field) field.classList.add('invalid');
    }

    function validate() {
      clearFieldErrors();
      var valid = true;
      var problems = [];

      var name = document.getElementById('field-name');
      if (!name.value.trim()) { markInvalid(name); problems.push('Name'); valid = false; }

      var email = document.getElementById('field-email');
      if (!isValidEmail(email.value.trim())) {
        markInvalid(email);
        problems.push('a valid Email (including a real domain ending, e.g. .com or .in)');
        valid = false;
      }

      var phone = document.getElementById('field-phone');
      var digits = phone.value.replace(/\D/g, '');
      if (digits.length < 6 || digits.length > 15) {
        markInvalid(phone);
        problems.push('a Phone number of 6-15 digits');
        valid = false;
      }

      var description = document.getElementById('field-description');
      if (!description.value.trim()) { markInvalid(description); problems.push('Project Description'); valid = false; }

      if (!checkFile()) { markInvalid(fileInput); valid = false; }

      return { valid: valid, problems: problems };
    }

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      messageEl.style.display = 'none';

      var result = validate();
      if (!result.valid) {
        showMessage(
          result.problems.length
            ? 'Please provide ' + result.problems.join(', ') + '.'
            : 'Please correct the highlighted fields.',
          true
        );
        return;
      }

      // Submit the full international number alongside its parts.
      var data = new FormData(form);
      data.set('phone', dialCode.value + ' ' + document.getElementById('field-phone').value.trim());
      if (guidance.checked) data.set('budget_amount', '');

      submitBtn.disabled = true;
      submitBtn.textContent = 'Sending...';

      fetch(ENDPOINT, {
        method: 'POST',
        headers: { 'Accept': 'application/json' },
        body: data
      }).then(function (response) {
        return response.json().then(function (payload) {
          return { ok: response.ok, data: payload };
        }).catch(function () {
          // Not JSON: the endpoint is missing or the server returned an error
          // page. Surface the status so the cause is not guesswork.
          return {
            ok: false,
            data: { error: 'The form endpoint returned HTTP ' + response.status + '.' }
          };
        });
      }).then(function (res) {
        if (res.ok && res.data && res.data.ok) {
          form.reset();
          form.style.display = 'none';
          var note = res.data.attachment_warning
            ? ' ' + res.data.attachment_warning
            : '';
          showMessage(
            'Thank you — your project requirement has been sent. We will get back to you shortly.' + note,
            !!res.data.attachment_warning
          );
          return;
        }
        submitBtn.disabled = false;
        submitBtn.textContent = 'Send Project Requirement';
        var err = (res.data && res.data.error) ? res.data.error : 'Something went wrong sending your request.';
        showMessage(err + ' If it keeps happening, email us at ' + CONTACT_EMAIL + '.', true);
      }).catch(function () {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Send Project Requirement';
        showMessage('Something went wrong sending your request. Please email ' + CONTACT_EMAIL + ' directly.', true);
      });
    });
  });
})();
