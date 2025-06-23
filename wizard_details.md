# Wizard Functionality Overview

This document outlines the files and functionalities involved in the applicant update wizard.

## Core Functionality

The wizard allows users to update applicant information through a multi-step process. It handles data input, validation at each step, session management for temporary data storage, and final data persistence to the database. The client-side interactions are managed via AJAX to provide a smooth user experience without full page reloads.

## Participating Files and Their Roles

Here's a list of the primary files involved in the wizard functionalities:

**1. Controller:**

*   **File:** `controllers/ApplicantUserController.php`
*   **Role:** This is the central component orchestrating the wizard.
    *   Manages the sequence of steps.
    *   Handles data submission and validation for each step using models.
    *   Stores valid data temporarily in the session.
    *   Loads data for each step (from session or database).
    *   Performs the final transactional save of all data to the database.
    *   Responds to both initial page loads and AJAX requests from the client-side JavaScript, typically returning JSON (with HTML for steps, messages, or status).

**2. Main Wizard View:**

*   **File:** `views/applicant-user/update-wizard.php`
*   **Role:** The main HTML template and container for the wizard interface.
    *   Renders the overall structure (titles, navigation tabs for steps).
    *   Contains a content area (`#wizard-step-content`) where individual step views are dynamically loaded.
    *   Includes navigation buttons (Previous, Next, Save).
    *   Embeds the primary JavaScript file for client-side logic.
    *   Displays global messages (flash messages, general errors).

**3. Step Partial Views:**

*   **Files:**
    *   `views/applicant-user/personal-details.php`
    *   `views/applicant-user/applicant-specifics.php`
    *   `views/applicant-user/account-settings.php`
*   **Role:** Each file defines the HTML form and input fields for a specific step in the wizard.
    *   Rendered within the `#wizard-step-content` area of `update-wizard.php`.
    *   Typically use `yii\widgets\ActiveForm` linked to the relevant models.
    *   Display validation errors specific to their fields.

**4. JavaScript:**

*   **File:** `web/js/applicant-wizard-ajax.js`
*   **Role:** Manages all client-side interactions, enabling a dynamic experience.
    *   Attaches event listeners to wizard navigation elements (buttons, tabs).
    *   Handles form submissions via AJAX (using `FormData` for file support).
    *   Processes JSON responses from the controller to:
        *   Update the HTML content of the current step.
        *   Update the visual state of navigation tabs and buttons.
        *   Update the browser URL using `history.pushState()`.
        *   Display success messages or validation errors.
    *   Manages browser back/forward navigation for the wizard.

**5. Models:**

*   **Files:**
    *   `models/AppApplicantUser.php`
    *   `models/AppApplicant.php`
*   **Role:** Define the data structure, validation rules, and business logic for applicant data.
    *   Attributes map to database columns.
    *   `rules()` method defines validation logic used by the controller.
    *   May use scenarios for step-specific validation.
    *   Handle database interaction (saving, retrieving records).

## Interaction Flow Summary

1.  User accesses the wizard URL, handled by `ApplicantUserController`.
2.  The controller renders `update-wizard.php`, which in turn renders the specific view for the initial step.
3.  User interacts with the form; actions are intercepted by `applicant-wizard-ajax.js`.
4.  JavaScript sends AJAX requests to the controller.
5.  The controller processes data, validates, saves to session (or DB on final step), and returns JSON.
6.  JavaScript updates the page content, UI elements, and URL based on the controller's response.
7.  This cycle continues for each step until completion or cancellation.
