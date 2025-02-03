// Validation rules
const RULES = {
    required: {
        validate: value => value !== undefined && value !== null && value.toString().trim() !== '',
        message: 'This field is required'
    },
    email: {
        validate: value => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value),
        message: 'Please enter a valid email address'
    },
    minLength: {
        validate: (value, min) => value.length >= min,
        message: min => `Must be at least ${min} characters long`
    },
    maxLength: {
        validate: (value, max) => value.length <= max,
        message: max => `Must not exceed ${max} characters`
    },
    pattern: {
        validate: (value, pattern) => new RegExp(pattern).test(value),
        message: 'Please match the requested format'
    },
    numeric: {
        validate: value => /^\d+$/.test(value),
        message: 'Please enter numbers only'
    },
    decimal: {
        validate: value => /^\d*\.?\d+$/.test(value),
        message: 'Please enter a valid number'
    },
    phone: {
        validate: value => /^\+?[\d\s-]{10,}$/.test(value),
        message: 'Please enter a valid phone number'
    },
    url: {
        validate: value => {
            try {
                new URL(value);
                return true;
            } catch {
                return false;
            }
        },
        message: 'Please enter a valid URL'
    },
    date: {
        validate: value => !isNaN(Date.parse(value)),
        message: 'Please enter a valid date'
    },
    match: {
        validate: (value, matchId) => {
            const matchElement = document.getElementById(matchId);
            return matchElement && value === matchElement.value;
        },
        message: 'Fields do not match'
    }
};

// Form state tracking
const forms = new Map();

// Event handlers
const handleInput = (event) => {
    const input = event.target;
    validateField(input);
};

const handleSubmit = (event) => {
    const form = event.target;
    const isValid = validateForm(form);
    
    if (!isValid) {
        event.preventDefault();
        showFirstError(form);
    }
};

// Validation functions
const validateField = (input) => {
    const rules = getFieldRules(input);
    const errors = [];

    rules.forEach(rule => {
        const { name, params } = parseRule(rule);
        const validator = RULES[name];

        if (validator && !validator.validate(input.value, ...params)) {
            errors.push(typeof validator.message === 'function' 
                ? validator.message(...params)
                : validator.message);
        }
    });

    updateFieldValidation(input, errors);
    return errors.length === 0;
};

const validateForm = (form) => {
    const inputs = getValidatableInputs(form);
    let isValid = true;

    inputs.forEach(input => {
        if (!validateField(input)) {
            isValid = false;
        }
    });

    return isValid;
};

// Helper functions
const getFieldRules = (input) => {
    const rules = input.dataset.validate;
    return rules ? rules.split('|') : [];
};

const parseRule = (rule) => {
    const [name, paramsString] = rule.split(':');
    const params = paramsString ? paramsString.split(',') : [];
    return { name, params };
};

const getValidatableInputs = (form) => {
    return Array.from(form.querySelectorAll('[data-validate]'));
};

const updateFieldValidation = (input, errors) => {
    const container = input.closest('.form-group') || input.parentElement;
    const feedback = container.querySelector('.invalid-feedback') 
        || createFeedbackElement(container);

    input.classList.toggle('is-invalid', errors.length > 0);
    input.classList.toggle('is-valid', errors.length === 0 && input.value !== '');
    
    if (errors.length > 0) {
        feedback.textContent = errors[0];
        feedback.style.display = 'block';
    } else {
        feedback.style.display = 'none';
    }
};

const createFeedbackElement = (container) => {
    const feedback = document.createElement('div');
    feedback.className = 'invalid-feedback';
    container.appendChild(feedback);
    return feedback;
};

const showFirstError = (form) => {
    const firstInvalid = form.querySelector('.is-invalid');
    if (firstInvalid) {
        firstInvalid.focus();
        firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
};

// Custom validation rules
const addValidationRule = (name, options) => {
    if (RULES[name]) {
        console.warn(`Validation rule '${name}' already exists and will be overwritten`);
    }
    RULES[name] = options;
};

// Initialize validation module
export const initializeValidation = () => {
    // Add validation to all forms with data-validate attribute
    document.querySelectorAll('form[data-validate]').forEach(form => {
        if (!forms.has(form)) {
            forms.set(form, true);
            
            // Add event listeners
            form.addEventListener('submit', handleSubmit);
            form.addEventListener('input', handleInput);
            
            // Initial validation
            validateForm(form);
        }
    });
};

// Export validation module
export const validation = {
    validateField,
    validateForm,
    addRule: addValidationRule,
    rules: RULES
}; 