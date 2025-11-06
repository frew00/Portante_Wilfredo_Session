<?php
require_once 'misc/headernavfooter.php';
require_once 'misc/signup_function.php';
require_once 'misc/security/SessionManager.php';

$sessionManager = SessionManager::getInstance();
$csrfToken = $sessionManager->getCsrfToken();
?>

<div class="modal fade" id="authModal" tabindex="-1" aria-labelledby="modalTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content auth-modal">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTitle">Login</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-4">
        <!-- ================= LOGIN FORM ================= -->
        <div id="loginFormContainer">
          <form id="loginForm" novalidate>
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            <div class="mb-3">
              <label for="loginEmail" class="form-label">Email</label>
              <div class="input-inner">
                <input type="email" name="email" class="form-control styled-input" id="loginEmail">
              </div>
              <div id="loginEmailError" class="error-msg"></div>
            </div>

            <div class="mb-3">
              <label for="loginPassword" class="form-label">Password</label>
              <div class="input-inner">
                <input type="password" name="password" class="form-control styled-input" id="loginPassword">
                <button type="button" class="toggle-password" data-target="#loginPassword">
                  <i class="fa fa-eye"></i>
                </button>
              </div>
              <div id="loginPasswordError" class="error-msg"></div>
            </div>

            <div id="loginMessage" class="alert d-none"></div>
            <button type="submit" class="btn modal-btn w-100">Login</button>
            <div class="text-center mt-3">
              <a href="#" id="openSignup" class="modal-link">Don't have an Account? Sign up.</a>
            </div>
          </form>
        </div>

        <!-- ================= SIGN UP FORM ================= -->
        <div id="signupFormContainer" class="d-none">
          <form id="signupForm" method="POST" novalidate>
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            <div class="row g-2">
              <div class="col-md-6">
                <label class="form-label">First Name</label>
                <div class="input-inner">
                  <input type="text" class="form-control styled-input" name="firstName" id="firstName" placeholder="First Name">
                </div>
                <div id="firstNameError" class="error-msg"></div>
              </div>

              <div class="col-md-6">
                <label class="form-label">Last Name</label>
                <div class="input-inner">
                  <input type="text" class="form-control styled-input" name="lastName" id="lastName" placeholder="Last Name">
                </div>
                <div id="lastNameError" class="error-msg"></div>
              </div>
            </div>

            <div class="mb-3 mt-2">
              <label class="form-label">Address</label>
              <div class="input-inner">
                <input type="text" class="form-control styled-input" name="address" id="address" placeholder="Address">
              </div>
              <div id="addressError" class="error-msg"></div>
            </div>

            <div class="mb-3">
              <label class="form-label">Email</label>
              <div class="input-inner">
                <input type="email" class="form-control styled-input" name="email" id="signupEmail" placeholder="Email">
              </div>
              <div id="signupEmailError" class="error-msg"></div>
            </div>

            <div class="mb-3">
              <label class="form-label">Contact Number</label>
              <div class="input-inner">
                <input type="text" class="form-control styled-input" name="number" id="contact" placeholder="09XXXXXXXXX" maxlength="11">
              </div>
              <div id="contactError" class="error-msg"></div>
            </div>

            <div class="mb-3">
              <label class="form-label">Password</label>
              <div class="input-inner">
                <input type="password" class="form-control styled-input" name="password" id="signupPassword" placeholder="Create password">
                <button type="button" class="toggle-password" data-target="#signupPassword">
                  <i class="fa fa-eye"></i>
                </button>
              </div>
              <div id="signupPasswordError" class="error-msg"></div>
            </div>

            <div class="mb-3">
              <label class="form-label">Retype Password</label>
              <div class="input-inner">
                <input type="password" class="form-control styled-input" id="confirmPassword" placeholder="Re-enter password">
                <button type="button" class="toggle-password" data-target="#confirmPassword">
                  <i class="fa fa-eye"></i>
                </button>
              </div>
              <div id="confirmPasswordError" class="error-msg"></div>
            </div>

            <button type="submit" class="btn modal-btn w-100">Sign Up</button>

            <div class="text-center mt-3">
              <a href="#" id="openLogin" class="modal-link">Back to Login</a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>