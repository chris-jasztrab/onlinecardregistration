<?php
ob_start();
require __DIR__ . '/form.php';
$pageContent = ob_get_clean();

$validationScript = <<<'HTML'
<script>
(function() {
    function onReady(callback) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', callback);
        } else {
            callback();
        }
    }

    onReady(function() {
        var emailInput = document.querySelector('input[name="email"]');
        if (!emailInput) {
            return;
        }

        var feedbackId = 'email-validation-feedback';
        var feedback = document.getElementById(feedbackId);
        if (!feedback) {
            feedback = document.createElement('div');
            feedback.id = feedbackId;
            feedback.className = 'email-validation-feedback';
            feedback.style.fontSize = '0.9em';
            feedback.style.marginTop = '5px';
            emailInput.insertAdjacentElement('afterend', feedback);
        }

        emailInput.setAttribute('aria-describedby', feedbackId);
        var originalBorderColor = emailInput.style.borderColor;

        function setFeedback(message, isValid) {
            feedback.textContent = message;
            feedback.style.color = isValid ? '#2d7a1f' : '#b30000';
            if (isValid) {
                feedback.setAttribute('data-state', 'valid');
                emailInput.style.borderColor = '#2d7a1f';
            } else {
                feedback.setAttribute('data-state', 'invalid');
                emailInput.style.borderColor = '#b30000';
            }
        }

        function clearFeedback() {
            feedback.textContent = '';
            feedback.removeAttribute('data-state');
            emailInput.style.borderColor = originalBorderColor;
        }

        function validateEmail() {
            var value = emailInput.value.replace(/\s+/g, ' ').trim();
            if (value === '') {
                setFeedback('Please enter your email address.', false);
                return false;
            }

            var pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!pattern.test(value)) {
                setFeedback('Please enter a valid email address.', false);
                return false;
            }

            setFeedback('Email address looks good.', true);
            return true;
        }

        emailInput.addEventListener('blur', validateEmail);

        emailInput.addEventListener('input', function() {
            var state = feedback.getAttribute('data-state');
            if (state === 'invalid') {
                validateEmail();
            } else if (emailInput.value.trim() === '') {
                clearFeedback();
            }
        });

        var form = emailInput.form;
        if (form) {
            form.addEventListener('submit', function(event) {
                if (!validateEmail()) {
                    event.preventDefault();
                    emailInput.focus();
                }
            });
        }
    });
})();
</script>
HTML;

if (stripos($pageContent, '</body>') !== false) {
    $pageContent = preg_replace('/<\/body>/i', $validationScript . '</body>', $pageContent, 1);
} else {
    $pageContent .= $validationScript;
}

echo $pageContent;
