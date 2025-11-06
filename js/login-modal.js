$(function () {
  // ======= OPEN LOGIN MODAL =======
  $('#loginButton').on('click', function (e) {
    e.preventDefault();
    openAuthModal('login');
  });

  // ======= OPEN REGISTER MODAL =======
  $('#registerButton').on('click', function (e) {
    e.preventDefault();
    openAuthModal('signup');
  });

  // LOGOUT
  $('#logoutButton').on('click', function (e) {
    e.preventDefault();
    $.post('misc/logout.php', function () {
      alert('Logged out successfully!');
      window.location.href = "index.php"  // reload page to reflect logged-out state
    });
  });

  $('#loginCartNotif').on('click', function (e) {

    alert("Please Login to Continue!");
    openAuthModal('login');

  });


  // ======= FUNCTION TO LOAD AND SHOW MODAL =======
  function openAuthModal(mode) {
    $('body').addClass('modal-blur-active');

    if (!$('#authModal').length) {
      $('body').append('<div id="modalContainer"></div>');
      $('#modalContainer').load('login-modal.php #authModal', function () {
        $('#authModal').modal('show');

        // Switch to correct form after loading
        if (mode === 'signup') {
          $('#loginFormContainer').addClass('d-none');
          $('#signupFormContainer').removeClass('d-none');
          $('#modalTitle').text('Sign Up');
        } else {
          $('#signupFormContainer').addClass('d-none');
          $('#loginFormContainer').removeClass('d-none');
          $('#modalTitle').text('Login');
        }
      });
    } else {
      $('#authModal').modal('show');
      if (mode === 'signup') {
        $('#loginFormContainer').addClass('d-none');
        $('#signupFormContainer').removeClass('d-none');
        $('#modalTitle').text('Sign Up');
      } else {
        $('#signupFormContainer').addClass('d-none');
        $('#loginFormContainer').removeClass('d-none');
        $('#modalTitle').text('Login');
      }
    }
  }

  // ======= HANDLE MODAL EVENTS AFTER LOAD =======
  $(document).on('shown.bs.modal', '#authModal', function () {
    console.log("Modal fully loaded");
    $('body').addClass('modal-blur-active');

    // ============== Prevent invalid characters in email ==============
    $(document).off('keypress', '#loginEmail, #signupEmail').on('keypress', '#loginEmail, #signupEmail', function (e) {
      let regex = /^[a-zA-Z0-9_@.\-]+$/;
      let char = String.fromCharCode(e.which);
      if (!regex.test(char)) e.preventDefault();
    });

    // ============== PASSWORD TOGGLE ==============
    $(document).off('click', '.toggle-password').on('click', '.toggle-password', function () {
      const target = $($(this).data('target'));
      const type = target.attr('type') === 'password' ? 'text' : 'password';
      target.attr('type', type);
      $(this).find('i').toggleClass('fa-eye fa-eye-slash');
    });

    // ============== SWITCH BETWEEN LOGIN / SIGNUP ==============
    $(document).off('click', '#openSignup').on('click', '#openSignup', function (e) {
      e.preventDefault();
      $('#loginFormContainer').addClass('d-none');
      $('#signupFormContainer').removeClass('d-none');
      $('#modalTitle').text('Sign Up');
    });

    $(document).off('click', '#openLogin').on('click', '#openLogin', function (e) {
      e.preventDefault();
      $('#signupFormContainer').addClass('d-none');
      $('#loginFormContainer').removeClass('d-none');
      $('#modalTitle').text('Login');
    });

    // ============== LOGIN VALIDATION ==============
    $(document).off('submit', '#loginForm').on('submit', '#loginForm', function (e) {
      e.preventDefault();

      let email = $('#loginEmail').val().trim();
      let password = $('#loginPassword').val().trim();
      let valid = true;

      $('#loginEmailError, #loginPasswordError').text('');

      if (!email || !/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(email)) {
        $('#loginEmailError').text('Please enter a valid email.');
        valid = false;
      }
      if (!password) {
        $('#loginPasswordError').text('Password is required.');
        valid = false;
      }

      if (!valid) return;

      // ======= AJAX LOGIN =======
      // Include CSRF token from hidden field so server-side validation succeeds
      const csrfToken = $('#loginForm').find('input[name="csrf_token"]').val() || $('input[name="csrf_token"]').val() || '';
      $.ajax({
        url: 'misc/login_function.php',
        type: 'POST',
        data: { email: email, password: password, csrf_token: csrfToken },
        success: function (response) {
          // Try to parse JSON; fallback to text
          let res;
          try {
            res = (typeof response === 'object') ? response : JSON.parse(response);
          } catch (e) {
            res = { success: false, message: $('<div>').html(response).text() };
          }

          // Clear previous field errors
          $('#loginEmailError').text('');
          $('#loginPasswordError').text('');

          if (res.success) {
            // Successful login — switch UI and reload
            $('#authModal').modal('hide');
            $('#loginForm')[0].reset();
            location.reload();
          } else {
            const msg = res.message || 'Invalid credentials';
            // Map common messages to fields; prefer non-blocking inline errors
            if (/email/i.test(msg)) {
              $('#loginEmailError').text(msg);
            }
            if (/password|credential|invalid/i.test(msg)) {
              $('#loginPasswordError').text(msg);
            }
            // If message doesn't match, show it under both fields
            if (!(/email/i.test(msg) || /password|credential|invalid/i.test(msg))) {
              $('#loginEmailError').text(msg);
              $('#loginPasswordError').text(msg);
            }
          }
        },
        error: function () {
          $('#loginPasswordError').text('An error occurred. Please try again.');
        }
      });
    });


    // ============== SIGNUP VALIDATION ==============
    $(document).off('submit', '#signupForm').on('submit', '#signupForm', function (e) {
      e.preventDefault(); // prevent default for now
      let valid = true;
      const emailRegex = /^[^@\s]+@[^@\s]+\.[^@\s]+$/;
      const nameRegex = /^[A-Za-z]+(?:\s[A-Za-z]+)*$/;

      const firstName = $('#firstName').val().trim();
      const lastName = $('#lastName').val().trim();
      const address = $('#address').val().trim();
      const email = $('#signupEmail').val().trim();
      const contact = $('#contact').val().trim();
      const password = $('#signupPassword').val();
      const confirm = $('#confirmPassword').val();

      $('.error-msg').text('');

      if (!firstName || !nameRegex.test(firstName)) {
        $('#firstNameError').text('Invalid first name.');
        valid = false;
      }
      if (!lastName || !nameRegex.test(lastName)) {
        $('#lastNameError').text('Invalid last name.');
        valid = false;
      }
      if (!address) {
        $('#addressError').text('Address is required.');
        valid = false;
      }
      if (!email || !emailRegex.test(email)) {
        $('#signupEmailError').text('Invalid email format.');
        valid = false;
      }
      if (!/^[0-9]{11}$/.test(contact)) {
        $('#contactError').text('Contact must be 11 digits.');
        valid = false;
      }
      if (password.includes(' ') || password.length < 7) {
        $('#signupPasswordError').text('Password must be at least 7 characters long and contain no spaces.');
        valid = false;
      }
      if (password !== confirm) {
        $('#confirmPasswordError').text('Passwords do not match.');
        valid = false;
      }

      // check if there are existing live validation errors
      if ($('#signupEmailError').text().includes('already') || $('#contactError').text().includes('already')) {
        valid = false;
      }

      if (valid) {
        // AJAX signup
        // Include CSRF token from hidden field for server validation
        const signupCsrf = $('#signupForm').find('input[name="csrf_token"]').val() || $('input[name="csrf_token"]').val() || '';
        $.ajax({
          url: 'misc/signup_function.php',
          type: 'POST',
          data: {
            firstName: firstName,
            lastName: lastName,
            address: address,
            email: email,
            number: contact,
            password: password,
            csrf_token: signupCsrf
          },
          success: function (response) {
            // Try to parse JSON; fallback to text
            let res;
            try {
              res = (typeof response === 'object') ? response : JSON.parse(response);
            } catch (e) {
              res = { success: false, message: $('<div>').html(response).text() };
            }

            if (res.success) {
              // show inline success (or switch to login)
              $('#signupForm')[0].reset();
              // Optionally show a brief inline success message
              $('#signupEmailError').text('');
              $('#contactError').text('');
              // switch to login form and show a small success notice
              $('#openLogin').click();
              // show a non-blocking message in login modal
              const loginMsg = $('#loginMessage');
              if (loginMsg.length) {
                loginMsg.removeClass('d-none').addClass('alert alert-success').text(res.message || 'Account created successfully');
              } else {
                alert(res.message || 'Account created successfully');
              }
            } else {
              // Map specific messages to inline fields
              const msg = res.message || 'Error creating account';
              if (/email/i.test(msg)) {
                $('#signupEmailError').text(msg);
              } else if (/contact/i.test(msg) || /number/i.test(msg)) {
                $('#contactError').text(msg);
              } else {
                // fallback: show in a general place (signupEmailError)
                $('#signupEmailError').text(msg);
              }
            }
          },
          error: function () {
            $('#signupEmailError').text('An error occurred. Please try again.');
          }
        });
      }

    });

    // ============== LIVE EMAIL VALIDATION ==============
    $(document).off('input', '#signupEmail').on('input', '#signupEmail', function () {
      let email = $(this).val().trim();
      if (email.length === 0) {
        $('#signupEmailError').text('');
        return;
      }

      // Simple format check before AJAX
      const emailRegex = /^[^@\s]+@[^@\s]+\.[^@\s]+$/;
      if (!emailRegex.test(email)) {
        $('#signupEmailError').text('Invalid email format.');
        return;
      }

      // AJAX to check if email exists
      $.ajax({
        url: 'validate_user.php',
        type: 'POST',
        data: { email: email },
        success: function (response) {
          if (response === 'email_exists') {
            $('#signupEmailError').text('Email is already in use.');
          } else {
            $('#signupEmailError').text('');
          }
        }
      });
    });

    // ============== LIVE CONTACT VALIDATION ==============
    $(document).off('input', '#contact').on('input', '#contact', function () {
      let contact = $(this).val().replace(/[^0-9]/g, '').slice(0, 11);
      $(this).val(contact);

      if (contact.length === 0) {
        $('#contactError').text('');
        return;
      }

      if (!/^[0-9]{11}$/.test(contact)) {
        $('#contactError').text('Contact must be 11 digits.');
        return;
      }

      // AJAX to check if contact exists
      $.ajax({
        url: 'validate_user.php',
        type: 'POST',
        data: { contact: contact },
        success: function (response) {
          if (response === 'contact_exists') {
            $('#contactError').text('Contact number is already registered.');
          } else {
            $('#contactError').text('');
          }
        }
      });
    });


    // ============== CONTACT VALIDATION ==============
    $(document).off('input', '#contact').on('input', '#contact', function () {
      this.value = this.value.replace(/[^0-9]/g, '').slice(0, 11);
    });
  });

  // ======= REMOVE BLUR ON CLOSE =======
  $(document).on('hidden.bs.modal', '#authModal', function () {
    $('body').removeClass('modal-blur-active');
  });
});
