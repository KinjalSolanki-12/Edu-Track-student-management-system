/**
 * validation.js - Reusable client-side form validation
 * Add `data-validate` to a <form> and this script validates inputs
 * carrying `data-rule` attributes before submit.
 * Supported rules: required | email | min:N | max:N | match:#fieldId | phone | number
 */

function showFieldError(input, message) {
    input.classList.add('invalid');
    let errorEl = input.parentElement.querySelector('.form-error');
    if (!errorEl) {
        errorEl = document.createElement('div');
        errorEl.className = 'form-error';
        input.parentElement.appendChild(errorEl);
    }
    errorEl.textContent = message;
    errorEl.style.display = 'block';
}

function clearFieldError(input) {
    input.classList.remove('invalid');
    const errorEl = input.parentElement.querySelector('.form-error');
    if (errorEl) errorEl.style.display = 'none';
}

function validateField(input) {
    const rules = (input.getAttribute('data-rule') || '').split('|').filter(Boolean);
    const value = input.value.trim();
    clearFieldError(input);

    for (const rule of rules) {
        const [name, param] = rule.split(':');

        if (name === 'required' && value === '') {
            showFieldError(input, 'This field is required.');
            return false;
        }
        if (name === 'email' && value !== '') {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!re.test(value)) {
                showFieldError(input, 'Enter a valid email address.');
                return false;
            }
        }
        if (name === 'phone' && value !== '') {
            const re = /^[0-9]{10}$/;
            if (!re.test(value)) {
                showFieldError(input, 'Enter a valid 10-digit phone number.');
                return false;
            }
        }
        if (name === 'number' && value !== '' && isNaN(value)) {
            showFieldError(input, 'This field must be a number.');
            return false;
        }
        if (name === 'min' && value.length < parseInt(param, 10)) {
            showFieldError(input, `Minimum ${param} characters required.`);
            return false;
        }
        if (name === 'max' && value.length > parseInt(param, 10)) {
            showFieldError(input, `Maximum ${param} characters allowed.`);
            return false;
        }
        if (name === 'match') {
            const other = document.querySelector(param);
            if (other && other.value !== value) {
                showFieldError(input, 'Values do not match.');
                return false;
            }
        }
    }
    return true;
}

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('form[data-validate]').forEach(function (form) {
        const fields = form.querySelectorAll('[data-rule]');

        fields.forEach(function (field) {
            field.addEventListener('blur', () => validateField(field));
            field.addEventListener('input', () => {
                if (field.classList.contains('invalid')) validateField(field);
            });
        });

        form.addEventListener('submit', function (e) {
            let valid = true;
            fields.forEach(function (field) {
                if (!validateField(field)) valid = false;
            });
            if (!valid) {
                e.preventDefault();
                const firstInvalid = form.querySelector('.invalid');
                if (firstInvalid) firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
    });
});
