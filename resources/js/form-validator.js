/**
 * Form Validator - Alpine.js Component
 *
 * Reusable client-side validation for Livewire forms.
 * Supports Laravel-style validation rules.
 *
 * Usage:
 * x-data="{
 *   ...formValidator({
 *     form: @json($form),
 *     rules: @json($form->rules()),
 *     messages: @json($form->messages())
 *   }),
 *   // Custom methods...
 * }"
 */
export function formValidator(config = {}) {
  return {
    // Core state
    form: config.form || {},
    rules: config.rules || {},
    messages: config.messages || {},
    errors: {},
    touched: {},

    /**
     * Validate a single field
     * @param {HTMLElement|string} el - Field element or field name
     * @param {boolean} includeTouched - Validate even if not touched
     * @returns {boolean} - True if valid
     */
    validateField(el, includeTouched = false) {
      const fieldName = typeof el === 'string' ? el : el.name;
      const element = typeof el === 'string'
        ? document.getElementById(el) || document.querySelector(`[name="${el}"]`)
        : el;

      if (!this.touched[fieldName] && !includeTouched) {
        return true;
      }

      const rulesString = this.rules[fieldName];

      let value;
      if (element && (element.type === 'radio' || element.type === 'checkbox')) {
        const checked = document.querySelector(`[name="${fieldName}"]:checked`);
        value = checked ? checked.value : '';
      } else {
        value = element ? element.value.trim() : this.form[fieldName];
      }
      let hasError = false;

      this.errors[fieldName] = '';

      if (!rulesString) return true;

      const rules = rulesString.split('|');

      for (let rule of rules) {
        let [ruleName, param] = rule.split(':');

        // Handle required_if specially
        if (ruleName === 'required_if') {
          const [targetField, targetValue] = param.split(',');
          const left = String(this.form[targetField]).toLowerCase();
          const right = String(targetValue).toLowerCase();

          if (left === right) {
            if (value === null || value === undefined || value === '') {
              this.addError(fieldName, ruleName);
              hasError = true;
              break;
            }
          }
          continue;
        }

        if (this.runRule(ruleName, value, param) === false) {
          this.addError(fieldName, ruleName);
          hasError = true;
          break;
        }
      }

      return !hasError;
    },

    /**
     * Run a single validation rule
     * @param {string} ruleName - Name of the rule
     * @param {*} value - Value to validate
     * @param {string} param - Rule parameter (e.g., "2" for min:2)
     * @returns {boolean} - True if valid
     */
    runRule(ruleName, value, param) {
      // Empty values pass all rules except required/accepted
      if (value === '' || value === null || value === false) {
        return ruleName !== 'required' && ruleName !== 'accepted';
      }

      switch (ruleName) {
        case 'required':
          return value !== undefined && value !== '';

        case 'min':
          if (typeof value === 'string') return value.length >= parseInt(param);
          if (Array.isArray(value)) return value.length >= parseInt(param);
          if (!isNaN(value)) return parseFloat(value) >= parseFloat(param);
          return false;

        case 'max':
          if (typeof value === 'string') return value.length <= parseInt(param);
          if (Array.isArray(value)) return value.length <= parseInt(param);
          if (!isNaN(value)) return parseFloat(value) <= parseFloat(param);
          return false;

        case 'alpha':
          return /^[a-zA-Z]+$/.test(value);

        case 'boolean':
          return /^(true|false|1|0)$/.test(String(value));

        case 'numeric':
          return /^\d+$/.test(value);

        case 'integer':
          return /^-?\d+$/.test(String(value));

        case 'string':
          return typeof value === 'string';

        case 'email':
          return /^\w+([.-]?\w+)*@\w+([.-]?\w+)*(\.\w{2,3})+$/.test(value);

        case 'accepted':
          return value === true || value === 'true' || value === 1 || value === '1' || value === 'yes' || value === 'on';

        case 'regex':
          return this.validateRegex(value, param);

        case 'nullable':
        case 'sometimes':
          return true;

        default:
          return true;
      }
    },

    /**
     * Validate regex pattern (handles PHP regex format)
     * @param {string} value - Value to test
     * @param {string} param - Regex pattern (PHP format)
     * @returns {boolean} - True if matches
     */
    validateRegex(value, param) {
      const invalidModifiers = ['x', 's', 'u', 'X', 'U', 'A'];

      let pattern = param;
      let modifiers = '';

      if (param.startsWith('/') && param.lastIndexOf('/') > 0) {
        const lastSlash = param.lastIndexOf('/');
        pattern = param.slice(1, lastSlash);
        modifiers = param.slice(lastSlash + 1);
        modifiers = [...modifiers].filter(m => !invalidModifiers.includes(m)).join('');
      }

      const jsRegex = new RegExp(pattern, modifiers);
      return jsRegex.test(value);
    },

    /**
     * Validate all fields in the form
     * @param {string} formId - Optional form ID to scope validation
     * @returns {boolean} - True if all fields are valid
     */
    validateAll(formId = null) {
      let isValid = true;
      this.errors = {};

      for (const fieldName in this.rules) {
        let field = document.getElementById(fieldName);

        if (!field || !field.name) {
          field = document.querySelector(`[name="${fieldName}"]`);
        }

        // Skip validation when hidden or not in DOM
        if (field && field.offsetParent !== null) {
          if (!this.validateField(field, true)) {
            isValid = false;
          }
        }
      }

      // Focus first error field
      if (!isValid) {
        this.focusFirstError(formId);
      }

      return isValid;
    },

    /**
     * Focus the first field with an error
     * @param {string} formId - Optional form ID to scope
     */
    focusFirstError(formId = null) {
      const errorFields = Object.keys(this.errors).filter(
        key => this.errors[key] && this.errors[key].length > 0
      );

      const form = formId
        ? document.getElementById(formId)
        : document.querySelector('form');

      if (!form) return;

      const formElements = [...form.elements];
      const firstErrorEl = formElements.find(
        el => el.name && errorFields.includes(el.name) && el.offsetParent !== null
      );

      if (firstErrorEl) {
        firstErrorEl.focus();
      }
    },

    /**
     * Add an error message for a field
     * @param {string} fieldName - Field name
     * @param {string} ruleName - Rule that failed
     */
    addError(fieldName, ruleName) {
      const messageKey = `${fieldName}.${ruleName}`;
      this.errors[fieldName] = this.messages[messageKey] || 'Dit veld is ongeldig';
    },

    /**
     * Mark a field as touched
     * @param {string} fieldName - Field name
     */
    markTouched(fieldName) {
      this.touched[fieldName] = true;
    },

    /**
     * Clear error for a specific field
     * @param {string} fieldName - Field name
     */
    clearError(fieldName) {
      this.errors[fieldName] = '';
    },

    /**
     * Clear all errors
     */
    clearAllErrors() {
      this.errors = {};
    },

    /**
     * Check if a field has an error
     * @param {string} fieldName - Field name
     * @returns {boolean}
     */
    hasError(fieldName) {
      return !!this.errors[fieldName];
    },

    /**
     * Get error message for a field
     * @param {string} fieldName - Field name
     * @returns {string}
     */
    getError(fieldName) {
      return this.errors[fieldName] || '';
    }
  };
}

export default formValidator;
