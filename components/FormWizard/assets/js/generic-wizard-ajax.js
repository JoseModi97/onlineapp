(function(window, $) {
    'use strict';

    var GenericWizard = {
        wizards: {}, // Store multiple wizard instances if needed

        init: function(options) {
            var wizardId = options.wizardId || 'defaultWizard';
            var containerSelector = options.containerSelector || '#' + wizardId + '-container';
            var $wizardContainer = $(containerSelector);

            if (!$wizardContainer.length) {
                console.error('Wizard container not found:', containerSelector);
                return;
            }

            this.wizards[wizardId] = {
                id: wizardId,
                container: $wizardContainer,
                form: $wizardContainer.find('form'),
                stepContentContainer: $wizardContainer.find('.wizard-step-content'),
                navContainer: $wizardContainer.find('.wizard-navigation-container'),
                buttonsContainer: $wizardContainer.find('.wizard-buttons-container'),
                messageArea: $wizardContainer.find('#wizard-general-message-area'),
                currentStep: options.initialStep || $wizardContainer.find('input[name="current_step_key"]').val(),
                ajaxUrl: options.ajaxUrl || window.location.href, // URL to send AJAX requests
                isSubmitting: false,
                config: options // Store full config
            };

            this.attachEventHandlers(wizardId);
            this.updateUI(wizardId, { currentStep: this.wizards[wizardId].currentStep }); // Initial UI update
            console.log('GenericWizard initialized for ID:', wizardId, this.wizards[wizardId]);
        },

        attachEventHandlers: function(wizardId) {
            var self = this;
            var wizard = this.wizards[wizardId];

            // Handle form submission for Next, Previous, Save buttons
            wizard.form.on('submit', function(e) {
                e.preventDefault();
                // Determine which button was clicked if multiple submit buttons
                var action = $(document.activeElement).val() || 'next';
                if ($(document.activeElement).attr('name') === 'wizard_action') {
                     self.submitStep(wizardId, action);
                }
            });

            // Delegated click for buttons if they are re-rendered
            wizard.container.on('click', 'button[name="wizard_action"]', function(e) {
                e.preventDefault();
                var action = $(this).val();
                self.submitStep(wizardId, action);
            });


            // Handle navigation clicks (e.g., tabs)
            wizard.navContainer.on('click', 'a[data-step]:not(.disabled):not(.active)', function(e) {
                e.preventDefault();
                var targetStepKey = $(this).data('step');
                self.navigateToStep(wizardId, targetStepKey);
            });

            // Handle browser back/forward (popstate)
            $(window).on('popstate.' + wizardId, function(event) {
                var state = event.originalEvent.state;
                if (state && state.wizardId === wizardId && state.step) {
                    self.loadStep(wizardId, state.step, null, false); // Don't push state again
                }
            });
        },

        submitStep: function(wizardId, action) {
            var wizard = this.wizards[wizardId];
            if (wizard.isSubmitting) return;

            var formData = new FormData(wizard.form[0]);
            formData.append('wizard_action', action); // Ensure action is part of form data

            this.loadStep(wizardId, wizard.currentStep, formData, true, action);
        },

        navigateToStep: function(wizardId, targetStepKey) {
            var wizard = this.wizards[wizardId];
            if (wizard.isSubmitting || wizard.currentStep === targetStepKey) return;

            // For GET navigation (e.g. clicking a tab for a previous step)
            this.loadStep(wizardId, targetStepKey, null, true);
        },

        loadStep: function(wizardId, stepKey, data, pushHistory, actionContext = null) {
            var wizard = this.wizards[wizardId];
            var self = this;

            if (wizard.isSubmitting) return;
            wizard.isSubmitting = true;
            this.toggleLoading(wizardId, true);
            this.clearMessages(wizardId);

            var method = (data instanceof FormData) ? 'POST' : 'GET';
            var ajaxData = (data instanceof FormData) ? data : (data || {}); // data can be null for GET

            if (method === 'POST' && !(ajaxData instanceof FormData)) {
                 // If it's an object, ensure stepKey is there for context.
                 // FormData will get it from submitStep
                ajaxData.current_step_key = wizard.currentStep;
            }


            $.ajax({
                url: self.prepareUrl(wizard.ajaxUrl, stepKey, wizard.id, (method === 'GET' ? ajaxData : null) ),
                type: method,
                data: ajaxData,
                processData: (ajaxData instanceof FormData) ? false : true,
                contentType: (ajaxData instanceof FormData) ? false : 'application/x-www-form-urlencoded; charset=UTF-8',
                dataType: 'json', // Expect JSON response from WizardController
                success: function(response) {
                    self.handleResponse(wizardId, response, pushHistory, actionContext);
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error("AJAX Error:", textStatus, errorThrown, jqXHR.responseText);
                    self.displayMessage(wizardId, 'An unexpected error occurred. Please try again. Details: ' + errorThrown, 'danger');
                    // Restore current step key in hidden input if POST failed badly
                    if (method === 'POST') {
                        wizard.form.find('input[name="current_step_key"]').val(wizard.currentStep);
                    }
                },
                complete: function() {
                    wizard.isSubmitting = false;
                    self.toggleLoading(wizardId, false);
                }
            });
        },

        prepareUrl: function(baseUrl, targetStepKey, wizardInstanceId, getData) {
            var url = new URL(baseUrl, window.location.origin); // Ensure absolute URL
            url.searchParams.set('wizard_id', wizardInstanceId);
            url.searchParams.set('requested_step_key', targetStepKey);

            if (getData && typeof getData === 'object') {
                for (const key in getData) {
                    if (getData.hasOwnProperty(key)) {
                         url.searchParams.set(key, getData[key]);
                    }
                }
            }
            return url.toString();
        },

        handleResponse: function(wizardId, response, pushHistory, actionContext) {
            var wizard = this.wizards[wizardId];

            if (response.success) {
                if (response.completed) { // Wizard finished (e.g. final save)
                    this.displayMessage(wizardId, response.message || 'Operation completed successfully!', 'success');
                    if (response.redirectUrl) {
                        window.location.href = response.redirectUrl;
                    } else {
                        // Wizard complete, may disable form or update UI
                        wizard.form.find('button[name="wizard_action"]').prop('disabled', true);
                         // If new HTML is provided for completion screen
                        if (response.html) {
                             wizard.stepContentContainer.html(response.html);
                             wizard.navContainer.html(response.navigationHtml || ''); // Update nav if provided
                             wizard.buttonsContainer.html(response.buttonsHtml || ''); // Update buttons if provided
                        }
                    }
                    return;
                }

                if (response.html) {
                    wizard.stepContentContainer.html(response.html);
                }
                if (response.navigationHtml) { // Server can send updated nav
                    wizard.navContainer.html(response.navigationHtml);
                }
                 if (response.buttonsHtml) { // Server can send updated buttons
                    wizard.buttonsContainer.html(response.buttonsHtml);
                }

                var newStepKey = response.currentStep || wizard.currentStep;
                wizard.currentStep = newStepKey;
                wizard.form.find('input[name="current_step_key"]').val(newStepKey);

                this.updateUI(wizardId, response); // response might contain isFirstStep, isLastStep

                if (pushHistory) {
                    var historyUrl = this.prepareUrl(wizard.ajaxUrl, newStepKey, wizard.id);
                    history.pushState({ wizardId: wizardId, step: newStepKey }, '', historyUrl);
                }
                 if (response.message) { // Informational message for successful step transition
                    this.displayMessage(wizardId, response.message, 'info');
                }

            } else { // success: false (validation errors or other server-side issue)
                this.displayMessage(wizardId, response.message || 'Please correct the errors below.', 'danger');
                if (response.errors) {
                    this.displayValidationErrors(wizardId, response.errors);
                }
                // If server provides HTML for the error state (e.g. form with errors pre-filled)
                if (response.html) {
                    wizard.stepContentContainer.html(response.html);
                }
                 // Restore current step key in hidden input if POST failed
                if (actionContext) { // actionContext is not null for POST requests
                    wizard.form.find('input[name="current_step_key"]').val(wizard.currentStep);
                }
            }
        },

        updateUI: function(wizardId, data) {
            var wizard = this.wizards[wizardId];
            // Update navigation active states (server might send fully rendered nav, or client can do it)
            wizard.navContainer.find('a.nav-link.active').removeClass('active');
            wizard.navContainer.find('a[data-step="' + wizard.currentStep + '"]').addClass('active');

            // Update button visibility (server might send fully rendered buttons, or client can do it)
            // Assuming server sends flags like isFirstStep, isLastStep if client is to manage this
            if (typeof data.isFirstStep !== 'undefined' || typeof data.isLastStep !== 'undefined') {
                wizard.buttonsContainer.find('button[value="previous"]').toggle(!data.isFirstStep);
                wizard.buttonsContainer.find('button[value="next"]').toggle(!data.isLastStep);
                wizard.buttonsContainer.find('button[value="save"]').toggle(data.isLastStep);
            }

            // Re-initialize plugins or specific JS for the new step content if necessary
            // $(document).trigger('wizard:stepLoaded', { wizardId: wizardId, stepKey: wizard.currentStep, container: wizard.stepContentContainer });
        },

        displayValidationErrors: function(wizardId, errors) {
            var wizard = this.wizards[wizardId];
            wizard.form.find('.is-invalid').removeClass('is-invalid');
            wizard.form.find('.invalid-feedback').remove();

            $.each(errors, function(field, messages) {
                // Try to find input by name (model[attribute] or just attribute)
                var input = wizard.form.find('[name*="[' + field + ']"], [name="' + field + '"]').first();
                if (input.length) {
                    input.addClass('is-invalid');
                    // Try to find a suitable place for the error message
                    var feedbackContainer = input.parent().find('.invalid-feedback');
                    if(!feedbackContainer.length){
                         input.after('<div class="invalid-feedback">' + messages.join('<br>') + '</div>');
                    } else {
                        feedbackContainer.html(messages.join('<br>')).show();
                    }
                } else {
                    // If field not found, add to general message area as a fallback
                    console.warn('Field not found for error:', field);
                    var generalErrorMsg = field + ': ' + messages.join(', ');
                    wizard.messageArea.append('<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                        generalErrorMsg +
                        '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');

                }
            });
        },

        displayMessage: function(wizardId, message, type) { // type: 'success', 'info', 'warning', 'danger'
            var wizard = this.wizards[wizardId];
            if (wizard.messageArea.length) {
                 wizard.messageArea.html('<div class="alert alert-' + type + ' alert-dismissible fade show" role="alert">' +
                    message +
                    '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                    '</div>');
            } else {
                alert(type.toUpperCase() + ": " + message); // Fallback
            }
        },

        clearMessages: function(wizardId) {
            var wizard = this.wizards[wizardId];
            if (wizard.messageArea.length) {
                wizard.messageArea.html('');
            }
            wizard.form.find('.is-invalid').removeClass('is-invalid');
            wizard.form.find('.invalid-feedback').remove();
        },

        toggleLoading: function(wizardId, show) {
            var wizard = this.wizards[wizardId];
            wizard.form.find('button[name="wizard_action"]').prop('disabled', show);
            // Optionally, show/hide a global spinner for the wizard
            if (show) {
                wizard.container.addClass('wizard-loading');
            } else {
                wizard.container.removeClass('wizard-loading');
            }
        }
    };

    // Expose GenericWizard to global scope
    window.GenericWizard = GenericWizard;

})(window, jQuery);
