<?php
/**
 * Samburu EWS — Contact
 *
 * Contact form with CSRF, honeypot, and AJAX submission
 * to /api/contact-submit.php.
 */
require __DIR__ . '/config.php';
require __DIR__ . '/includes/Csrf.php';

$pageTitle = 'Contact Us';
require __DIR__ . '/includes/header.php';
?>

<!-- Hero -->
<section class="hero" style="padding:var(--sp-2xl) 0;">
    <div class="container">
        <h1>Contact Us</h1>
        <p>Questions, feedback, or partnership inquiries — we'd love to hear from you.</p>
    </div>
</section>

<section class="page-section">
    <div class="container">
        <div class="grid grid-2 grid-auto" style="align-items:start;">

            <!-- Contact Form -->
            <div class="card">
                <h2 class="card-title mb-lg">Send a Message</h2>

                <!-- Success / error banner -->
                <div id="formAlert" class="alert-banner" style="display:none;" role="alert"></div>

                <form id="contactForm" method="POST" action="api/contact-submit.php" novalidate>
                    <!-- CSRF token -->
                    <?= Csrf::field() ?>

                    <!-- Honeypot (hidden from humans) -->
                    <div style="position:absolute;left:-9999px;" aria-hidden="true">
                        <label for="website">Leave blank</label>
                        <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="name">Full Name <span style="color:var(--clr-danger);">*</span></label>
                        <input type="text" class="form-input" id="name" name="name" required
                               minlength="2" maxlength="100" placeholder="e.g. James Lekurtit">
                        <span class="field-error" id="nameError"></span>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="email">Email Address <span style="color:var(--clr-danger);">*</span></label>
                        <input type="email" class="form-input" id="email" name="email" required
                               maxlength="255" placeholder="e.g. james@example.com">
                        <span class="field-error" id="emailError"></span>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="stakeholder_group">Stakeholder Group</label>
                        <select class="form-select" id="stakeholder_group" name="stakeholder_group">
                            <option value="">— Select (optional) —</option>
                            <option value="government">Government Agency</option>
                            <option value="ngo">NGO / Development Partner</option>
                            <option value="radio">Community Radio Station</option>
                            <option value="pastoralist">Pastoralist Community</option>
                            <option value="intermediary">Intermediary / Chief</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="subject">Subject <span style="color:var(--clr-danger);">*</span></label>
                        <input type="text" class="form-input" id="subject" name="subject" required
                               maxlength="200" placeholder="e.g. Partnership inquiry">
                        <span class="field-error" id="subjectError"></span>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="message">Message <span style="color:var(--clr-danger);">*</span></label>
                        <textarea class="form-textarea" id="message" name="message" required
                                  minlength="10" maxlength="5000" rows="6"
                                  placeholder="Type your message here…"></textarea>
                        <span class="field-error" id="messageError"></span>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg" id="submitBtn" style="width:100%;">
                        Send Message
                    </button>
                </form>
            </div>

            <!-- Contact info sidebar -->
            <div>
                <div class="card mb-lg">
                    <div class="card-header">
                        <div class="card-icon"></div>
                        <h3 class="card-title">Office Location</h3>
                    </div>
                    <div class="card-body">
                        <p class="text-muted" style="line-height:1.8;">
                            Samburu County Government<br>
                            Department of Agriculture &amp; Pastoral Economy<br>
                            P.O. Box 3 – Maralal, 20600<br>
                            Samburu County, Kenya
                        </p>
                    </div>
                </div>

                <div class="card mb-lg">
                    <div class="card-header">
                        <div class="card-icon"></div>
                        <h3 class="card-title">Phone &amp; Email</h3>
                    </div>
                    <div class="card-body">
                        <p class="text-muted" style="line-height:1.8;">
                            <a href="tel:+254800723253">0800-723-253</a> (NDMA Hotline)<br>
                            <a href="mailto:info@samburuews.example">info@samburuews.example</a>
                        </p>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <div class="card-icon">⏰</div>
                        <h3 class="card-title">Response Time</h3>
                    </div>
                    <div class="card-body">
                        <p class="text-muted" style="line-height:1.8;">
                            We aim to respond to all inquiries within <strong>48 hours</strong>.
                            For urgent drought-related matters, please call the NDMA hotline directly.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.field-error {
    display: block;
    font-size: var(--fs-xs);
    color: var(--clr-danger);
    margin-top: var(--sp-xs);
    min-height: 1.2em;
}
.form-input.invalid, .form-textarea.invalid, .form-select.invalid {
    border-color: var(--clr-danger);
}
.form-input.valid, .form-textarea.valid {
    border-color: var(--clr-primary-light);
}
</style>

<script>
(function () {
    'use strict';

    const form      = document.getElementById('contactForm');
    const alertBox  = document.getElementById('formAlert');
    const submitBtn = document.getElementById('submitBtn');
    if (!form) return;

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        // Client-side validation
        clearErrors();
        let valid = true;

        const name = form.name.value.trim();
        const email = form.email.value.trim();
        const subject = form.subject.value.trim();
        const message = form.message.value.trim();

        if (!name || name.length < 2)      { showFieldError('name', 'Name is required (min 2 characters).'); valid = false; }
        if (!email || !isEmail(email))      { showFieldError('email', 'A valid email is required.'); valid = false; }
        if (!subject)                        { showFieldError('subject', 'Subject is required.'); valid = false; }
        if (!message || message.length < 10) { showFieldError('message', 'Message must be at least 10 characters.'); valid = false; }

        if (!valid) return;

        // Submit via fetch
        submitBtn.disabled = true;
        submitBtn.textContent = 'Sending…';

        const body = new FormData(form);

        fetch(form.action, { method: 'POST', body: body })
            .then(res => res.json())
            .then(data => {
                if (data.ok) {
                    showAlert('success', data.message || 'Message sent successfully!');
                    form.reset();
                    if (window.EWS?.toast) EWS.toast('Message sent!', 'success');
                } else {
                    const msgs = (data.errors || ['An error occurred.']).join('<br>');
                    showAlert('error', msgs);
                    if (window.EWS?.toast) EWS.toast('Please fix the errors.', 'error');
                }
            })
            .catch(() => {
                showAlert('error', 'Network error. Please try again.');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Send Message';
            });
    });

    function showAlert(type, html) {
        alertBox.style.display = 'flex';
        alertBox.className = 'alert-banner ' + (type === 'success' ? 'alert-green' : 'alert-red');
        alertBox.innerHTML = '<div>' + html + '</div>';
        alertBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    function showFieldError(field, msg) {
        const el = document.getElementById(field + 'Error');
        if (el) el.textContent = msg;
        const input = form[field];
        if (input) input.classList.add('invalid');
    }

    function clearErrors() {
        form.querySelectorAll('.field-error').forEach(el => el.textContent = '');
        form.querySelectorAll('.invalid').forEach(el => el.classList.remove('invalid'));
        alertBox.style.display = 'none';
    }

    function isEmail(str) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(str);
    }

})();
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
