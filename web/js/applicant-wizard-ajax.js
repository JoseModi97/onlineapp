$(document).ready(function() {
    var wizardContainer = $('.app-applicant-user-update-wizard'); // Main container
    var contentArea = wizardContainer.find('#wizard-step-content'); // Area to load step HTML
    var navTabsContainer = wizardContainer.find('.nav-tabs'); // Container for nav tabs

    var currentWizardUrl = window.location.href; // Initial URL
    var currentApplicantUserId = wizardContainer.data('applicant-user-id') || null;

    // Function to update UI elements (tabs, buttons)
    function updateWizardUI(activeStepKey, stepsConfig, applicantUserId) {
        currentApplicantUserId = applicantUserId || currentApplicantUserId;

        navTabsContainer.find('.nav-item').each(function() {
            var $tabLink = $(this).find('a.nav-link');
            var stepKey = $tabLink.data('step');
            var stepIndex = stepsConfig.indexOf(stepKey);
            var activeStepIndex = stepsConfig.indexOf(activeStepKey);

            $tabLink.removeClass('active').attr('aria-current', 'false');
            $(this).removeClass('active'); // For Bootstrap 5, active class might be on nav-item or nav-link

            var isDisabled = false;
            if (!currentApplicantUserId && stepIndex > 0) { // First step not saved, disable subsequent
                isDisabled = true;
            } else if (stepIndex > activeStepIndex) { // Future step
                isDisabled = true;
            }

            if (stepKey === activeStepKey) {
                $tabLink.addClass('active').attr('aria-current', 'page');
                $(this).addClass('active');
                isDisabled = false; // Current step is never disabled
            }

            if (isDisabled) {
                $tabLink.addClass('disabled-link disabled').attr('tabindex', '-1').attr('aria-disabled', 'true');
            } else {
                $tabLink.removeClass('disabled-link disabled').removeAttr('tabindex').removeAttr('aria-disabled');
            }
        });

        // Update Next/Save/Previous button visibility based on activeStepKey
        var isLastStep = stepsConfig.indexOf(activeStepKey) >= stepsConfig.length - 1;
        var isFirstStep = stepsConfig.indexOf(activeStepKey) === 0;

        wizardContainer.find('#wizard-next-btn').toggle(!isLastStep);
        wizardContainer.find('#wizard-save-btn').toggle(isLastStep); // Typically shown only on the last step
        wizardContainer.find('#wizard-previous-btn').toggle(!isFirstStep);

        // Update data attribute for applicant_user_id on the container if it changed
        if (applicantUserId) {
            wizardContainer.attr('data-applicant-user-id', applicantUserId);
        }
    }

    // Function to handle AJAX response
    function handleAjaxResponse(response, targetStepKeyForUrl) {
        if (response.success) {
            if (response.completed && response.redirectUrl) {
                window.location.href = response.redirectUrl;
                return;
            }
            if (response.html) {
                contentArea.html(response.html);
                // Re-attach delegated event handlers if forms are replaced, or initialize specific JS for new content
                // For simple forms, direct re-attachment might not be needed if using delegated events on wizardContainer
            }
            var newActiveStep = response.nextStep || response.currentStep || targetStepKeyForUrl;
            if (newActiveStep) {
                var stepsFromServer = wizardContainer.data('steps-array'); // Expect this to be set on wizard container
                updateWizardUI(newActiveStep, stepsFromServer, response.applicant_user_id);

                // Update URL
                var newUrl = new URL(currentWizardUrl);
                newUrl.searchParams.set('currentStep', newActiveStep);
                if (response.applicant_user_id) {
                    newUrl.searchParams.set('applicant_user_id', response.applicant_user_id);
                } else if (currentApplicantUserId) {
                     newUrl.searchParams.set('applicant_user_id', currentApplicantUserId);
                }
                history.pushState({ step: newActiveStep, applicant_user_id: response.applicant_user_id || currentApplicantUserId }, '', newUrl.toString());
                currentWizardUrl = newUrl.toString();

                // Re-initialize any specific JS needed for the new step's content here
                // e.g., date pickers, select2, etc.
                // contentArea.find('.datepicker').datepicker();
            }
             // Clear previous errors
            $('.form-control').removeClass('is-invalid');
            $('.invalid-feedback').remove();
            $('#wizard-general-error').hide().text('');

        } else { // AJAX call success=false (validation errors or other server error)
            if (response.errors) {
                // Clear previous errors more carefully
                contentArea.find('.form-control.is-invalid').removeClass('is-invalid');
                contentArea.find('.invalid-feedback').each(function() { // Clear text and hide, but don't remove structure for specific divs like #profile-image-error
                    if ($(this).attr('id') === 'profile-image-error') {
                        $(this).html('').hide();
                    } else {
                        // For other generic feedback blocks that might be added/removed by Yii/Bootstrap
                        $(this).remove();
                    }
                });
                 // Ensure our specific profile image error div is hidden if it wasn't caught above (e.g. if it had no text)
                contentArea.find('#profile-image-error').html('').hide();


                $('#wizard-general-error').hide().text('');

                $.each(response.errors, function(field, messages) {
                    var errorMsg = messages.join('<br>');
                    if (field === 'profile_image_file') {
                        var profileImageInput = contentArea.find('#profile-image-input');
                        var profileImageErrorDiv = contentArea.find('#profile-image-error');

                        profileImageInput.addClass('is-invalid');
                        profileImageErrorDiv.html(errorMsg).show();
                    } else {
                        var input = contentArea.find('[name*="[' + field + ']"], [name="' + field + '"]');
                        input.addClass('is-invalid');

                        // Remove old generic feedback for this field before adding new one
                        input.closest('.mb-3, .form-group').find('.invalid-feedback:not(#profile-image-error)').remove();
                        input.closest('.mb-3, .form-group').append('<div class="invalid-feedback">' + errorMsg + '</div>');
                        // Make sure it's visible, Yii might add it hidden
                        input.closest('.mb-3, .form-group').find('.invalid-feedback').show();
                    }
                });

                if (response.message) {
                    $('#wizard-general-error').text("Input Error: " + response.message).show();
                } else {
                    $('#wizard-general-error').text("Input Error: Please correct the highlighted fields below.").show();
                }
            } else if (response.message) { // Server error with a specific message
                $('#wizard-general-error').text("Update Failed: " + response.message).show();
            } else { // Fallback for other success=false cases without specific errors or message
                $('#wizard-general-error').text('Request Failed: An unknown error occurred. Please try again.').show();
            }
             if (response.redirectToStep) {
                // Force navigation to a specific step, e.g., if user tries to access invalid step
                makeAjaxRequest(response.redirectToStep, 'GET', null);
            }
        }
    }

    // Function to make AJAX request
    function makeAjaxRequest(targetStepKey, method, formData) {
        var ajaxUrl = new URL(currentWizardUrl); // Use current base URL
        ajaxUrl.searchParams.set('currentStep', targetStepKey); // For GET, this is the target. For POST, it's more for context.
        if (currentApplicantUserId) {
             ajaxUrl.searchParams.set('applicant_user_id', currentApplicantUserId);
        }

        var ajaxData = formData;
        if (method === 'POST' && ajaxData instanceof FormData) {
            // Add current_step_validated to ensure server knows which step's data is being sent
            ajaxData.append('current_step_validated', targetStepKey);
        } else if (method === 'POST' && typeof ajaxData === 'object' && ajaxData !== null) {
            ajaxData.current_step_validated = targetStepKey;
        }


        $.ajax({
            url: ajaxUrl.toString(),
            type: method,
            data: ajaxData,
            processData: (formData instanceof FormData) ? false : true, // Important for FormData
            contentType: (formData instanceof FormData) ? false : 'application/x-www-form-urlencoded; charset=UTF-8', // Important for FormData
            beforeSend: function() {
                // TODO: Show loader
                wizardContainer.find('#wizard-next-btn, #wizard-save-btn, #wizard-previous-btn').prop('disabled', true);
            },
            success: function(response) {
                handleAjaxResponse(response, targetStepKey);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                // More direct message for critical AJAX failures
                $('#wizard-general-error').text('System Error: Could not complete your request due to a technical issue. Please try again. If the problem continues, contact support.').show();
                console.error("AJAX Error:", textStatus, errorThrown, jqXHR.responseText); // Keep detailed log for developers
            },
            complete: function() {
                // TODO: Hide loader
                wizardContainer.find('#wizard-next-btn, #wizard-save-btn, #wizard-previous-btn').prop('disabled', false);
            }
        });
    }

    // Attach event listeners
    // For 'Next' and 'Save' buttons
    wizardContainer.on('click', '#wizard-next-btn, #wizard-save-btn', function(e) {
        e.preventDefault();
        var currentActiveStep = navTabsContainer.find('a.nav-link.active').data('step');
        var form = contentArea.find('form'); // Assumes one form per step, or a single form wrapping all

        var formData;
        if (form.length > 0 && window.FormData) {
            formData = new FormData(form[0]);
        } else if (form.length > 0) { // Fallback for older browsers if FormData not supported for some reason
            formData = form.serialize();
        } else {
            formData = {}; // No form, maybe step has no inputs
        }

        // Add button name to distinguish between next and save on server if needed
        if (this.id === 'wizard-save-btn') {
             if (formData instanceof FormData) formData.append('wizard_save', '1'); else formData.wizard_save = '1';
        } else {
             if (formData instanceof FormData) formData.append('wizard_next', '1'); else formData.wizard_next = '1';
        }
        makeAjaxRequest(currentActiveStep, 'POST', formData);
    });

    // For 'Previous' button (can be simple GET or POST if state needs to be saved before going back)
    // For now, let's make it a GET request to the previous step.
    wizardContainer.on('click', '#wizard-previous-btn', function(e) {
        e.preventDefault();
        var stepsFromServer = wizardContainer.data('steps-array');
        var currentActiveStepKey = navTabsContainer.find('a.nav-link.active').data('step');
        var currentIdx = stepsFromServer.indexOf(currentActiveStepKey);
        if (currentIdx > 0) {
            var prevStepKey = stepsFromServer[currentIdx - 1];
            makeAjaxRequest(prevStepKey, 'GET', null);
        }
    });

    // For Tab clicks
    navTabsContainer.on('click', 'a.nav-link:not(.disabled):not(.active)', function(e) {
        e.preventDefault();
        var targetStepKey = $(this).data('step');
        makeAjaxRequest(targetStepKey, 'GET', null);
    });

    // Handle browser back/forward buttons
    $(window).on('popstate', function(event) {
        var state = event.originalEvent.state;
        if (state && state.step) {
            // Check if applicant_user_id in state matches current context, might need more robust handling
            currentApplicantUserId = state.applicant_user_id || currentApplicantUserId;
            makeAjaxRequest(state.step, 'GET', null);
        } else {
            // If no state, could reload initial state or a default step
            // For simplicity, might just reload the page if state is unexpectedly empty
            // window.location.reload();
            // Or, try to go to the initial step based on current URL params if any
            const urlParams = new URLSearchParams(window.location.search);
            const stepFromUrl = urlParams.get('currentStep') || wizardContainer.data('steps-array')[0];
            makeAjaxRequest(stepFromUrl, 'GET', null);
        }
    });

    // Initial UI setup (e.g. disable tabs based on current step from server)
    var initialStep = navTabsContainer.find('a.nav-link.active').data('step');
    var allSteps = wizardContainer.data('steps-array');
    if (initialStep && allSteps && allSteps.length > 0) {
        updateWizardUI(initialStep, allSteps, currentApplicantUserId);
    }

    // Add a div for general AJAX errors if not present
    if ($('#wizard-general-error').length === 0) {
        contentArea.before('<div id="wizard-general-error" class="alert alert-danger" style="display:none;"></div>');
    }
});
