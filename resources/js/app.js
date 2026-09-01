// ============================================
// IMPORT ALPINE.JS
// ============================================
import Alpine from 'alpinejs';
import { distance as levenshteinDistance } from 'fastest-levenshtein';
import intlTelInput from 'intl-tel-input';
import 'intl-tel-input/styles';

window.Alpine = Alpine;
window.cpbnLevenshteinDistance = levenshteinDistance;

// ============================================
// CONFIRMATION MODAL
// ============================================

function showConfirmModal(options) {
    const {
        title = 'Confirm Action',
        message = 'Are you sure you want to proceed?',
        confirmText = 'Yes, Proceed',
        cancelText = 'Cancel',
        type = 'warning',
        onConfirm = null,
        onCancel = null
    } = options;

    closeModal();

    const overlay = document.createElement('div');
    overlay.className = 'cpbn-modal-overlay';
    overlay.id = 'cpbn-modal-overlay';

    const modal = document.createElement('div');
    modal.className = 'cpbn-modal';

    const iconMap = {
        danger: '⚠️',
        warning: '⚡',
        info: 'ℹ️',
        success: '✅'
    };

    const btnClass =
        type === 'danger'
            ? 'danger'
            : '';

    modal.innerHTML = `
        <div class="cpbn-modal-header">
            <h3>${iconMap[type] || 'ℹ️'} ${title}</h3>
            <button
                type="button"
                class="cpbn-modal-close"
                id="cpbn-modal-close-btn"
            >
                ×
            </button>
        </div>

        <div class="cpbn-modal-body">
            <p>${message}</p>
        </div>

        <div class="cpbn-modal-footer">
            <button
                type="button"
                class="cpbn-modal-btn cpbn-modal-btn-cancel"
                id="cpbn-modal-cancel-btn"
            >
                ${cancelText}
            </button>

            <button
                type="button"
                class="cpbn-modal-btn cpbn-modal-btn-confirm ${btnClass}"
                id="cpbn-modal-confirm-btn"
            >
                ${confirmText}
            </button>
        </div>
    `;

    overlay.appendChild(modal);
    document.body.appendChild(overlay);

    const closeModalOnly = function() {
        const overlayElement =
            document.getElementById(
                'cpbn-modal-overlay'
            );

        if (overlayElement) {
            overlayElement.remove();
        }
    };

    const cancelButton =
        document.getElementById(
            'cpbn-modal-cancel-btn'
        );

    if (cancelButton) {
        cancelButton.addEventListener(
            'click',
            function() {
                closeModalOnly();

                if (
                    typeof onCancel
                    === 'function'
                ) {
                    onCancel();
                }
            }
        );
    }

    const closeButton =
        document.getElementById(
            'cpbn-modal-close-btn'
        );

    if (closeButton) {
        closeButton.addEventListener(
            'click',
            function() {
                closeModalOnly();

                if (
                    typeof onCancel
                    === 'function'
                ) {
                    onCancel();
                }
            }
        );
    }

    const confirmButton =
        document.getElementById(
            'cpbn-modal-confirm-btn'
        );

    if (confirmButton) {
        confirmButton.addEventListener(
            'click',
            function() {
                closeModalOnly();

                if (
                    typeof onConfirm
                    === 'function'
                ) {
                    onConfirm();
                }
            }
        );
    }

    overlay.addEventListener(
        'click',
        function(event) {
            if (event.target === overlay) {
                closeModalOnly();

                if (
                    typeof onCancel
                    === 'function'
                ) {
                    onCancel();
                }
            }
        }
    );

    document.addEventListener(
        'keydown',
        function(event) {
            if (event.key !== 'Escape') {
                return;
            }

            const overlayElement =
                document.getElementById(
                    'cpbn-modal-overlay'
                );

            if (! overlayElement) {
                return;
            }

            closeModalOnly();

            if (
                typeof onCancel
                === 'function'
            ) {
                onCancel();
            }
        },
        {
            once: true
        }
    );
}

function closeModal() {
    const overlay =
        document.getElementById(
            'cpbn-modal-overlay'
        );

    if (overlay) {
        overlay.remove();
    }
}

window.showConfirmModal =
    showConfirmModal;

// ============================================
// CERTIFICATE FILE HELPERS
// ============================================

const maximumCertificateSize =
    5 * 1024 * 1024;

const allowedCertificateExtensions = [
    'pdf',
    'jpg',
    'jpeg',
    'png'
];

function certificateFileError(file) {
    const extension = file.name
        .split('.')
        .pop()
        .toLowerCase();

    if (
        ! allowedCertificateExtensions
            .includes(extension)
    ) {
        return 'Certificate evidence must be a PDF, JPG, JPEG or PNG file.';
    }

    if (
        file.size
        > maximumCertificateSize
    ) {
        return 'Certificate evidence must not exceed 5 MB.';
    }

    return null;
}

function restoreCertificateFile(
    input,
    file
) {
    if (! file) {
        return false;
    }

    try {
        const transfer =
            new DataTransfer();

        transfer.items.add(file);

        input.files =
            transfer.files;

        return true;
    } catch (error) {
        return false;
    }
}

function updateCertificateFileStatus(input) {
    const field =
        input.closest(
            '.cpbn-field'
        );

    if (! field) {
        return;
    }

    const status =
        field.querySelector(
            '.cpbn-file-selected'
        );

    const clearButton =
        field.querySelector(
            '.cpbn-clear-file'
        );

    if (
        ! status
        || ! clearButton
    ) {
        return;
    }

    const file =
        input.files[0]
        ?? null;

    if (! file) {
        status.hidden = true;
        status.textContent = '';

        clearButton.hidden = true;

        return;
    }

    const card =
        input.closest(
            '.cpbn-certification-card'
        );

    const hasExistingEvidence =
        card?.dataset
            .hasExistingEvidence
        === '1';

    status.textContent =
        hasExistingEvidence
            ? `Selected replacement: ${file.name}. Existing evidence remains safe until this profile is successfully saved.`
            : `Selected file: ${file.name}`;

    status.hidden = false;
    clearButton.hidden = false;
}

function initialiseCertificateFileInput(
    input
) {
    if (
        ! input
        || input.dataset
            .certificateInitialised
            === '1'
    ) {
        return;
    }

    input.dataset
        .certificateInitialised =
        '1';

    input._cpbnPreviousFile =
        input.files[0]
        ?? null;

    input.addEventListener(
        'click',
        function() {
            input._cpbnPreviousFile =
                input.files[0]
                ?? input._cpbnPreviousFile
                ?? null;

            window.addEventListener(
                'focus',
                function() {
                    setTimeout(
                        function() {
                            if (
                                ! input.files.length
                                && input
                                    ._cpbnPreviousFile
                            ) {
                                restoreCertificateFile(
                                    input,
                                    input
                                        ._cpbnPreviousFile
                                );
                            }

                            updateCertificateFileStatus(
                                input
                            );
                        },
                        0
                    );
                },
                {
                    once: true
                }
            );
        }
    );

    input.addEventListener(
        'change',
        function() {
            const selectedFile =
                input.files[0]
                ?? null;

            if (! selectedFile) {
                if (
                    input
                        ._cpbnPreviousFile
                ) {
                    restoreCertificateFile(
                        input,
                        input
                            ._cpbnPreviousFile
                    );
                }

                updateCertificateFileStatus(
                    input
                );

                return;
            }

            const error =
                certificateFileError(
                    selectedFile
                );

            if (error) {
                window.alert(error);

                if (
                    ! restoreCertificateFile(
                        input,
                        input
                            ._cpbnPreviousFile
                    )
                ) {
                    input.value = '';
                }

                updateCertificateFileStatus(
                    input
                );

                return;
            }

            input._cpbnPreviousFile =
                selectedFile;

            updateCertificateFileStatus(
                input
            );

            if (
                typeof window
                    .setFormChanged
                === 'function'
            ) {
                window.setFormChanged(
                    true
                );
            }
        }
    );

    updateCertificateFileStatus(
        input
    );
}

window.clearSelectedCertificateFile =
    function(button) {
        const field =
            button.closest(
                '.cpbn-field'
            );

        if (! field) {
            return;
        }

        const input =
            field.querySelector(
                '.cpbn-certificate-file'
            );

        if (! input) {
            return;
        }

        input._cpbnPreviousFile =
            null;

        input.value = '';

        updateCertificateFileStatus(
            input
        );

        if (
            typeof window
                .setFormChanged
            === 'function'
        ) {
            window.setFormChanged(
                true
            );
        }
    };

// ============================================
// AUTO-ATTACH CONFIRMATIONS
// ============================================

document.addEventListener(
    'DOMContentLoaded',
    function() {

        // ============================================
        // DELETE CONFIRMATION - FORMS
        // ============================================

        document
            .querySelectorAll(
                'form[data-confirm-delete]'
            )
            .forEach(
                function(form) {
                    form.addEventListener(
                        'submit',
                        function(event) {
                            if (
                                this.dataset
                                    .deleteApproved
                                === '1'
                            ) {
                                delete this.dataset
                                    .deleteApproved;

                                return;
                            }

                            event.preventDefault();
                            event.stopImmediatePropagation();

                            const itemName =
                                this.dataset
                                    .itemName
                                || 'this item';

                            const formReference =
                                this;

                            showConfirmModal({
                                title:
                                    'Confirm Delete',

                                message:
                                    `Are you sure you want to delete "${itemName}"? This action cannot be undone.`,

                                confirmText:
                                    'Yes, Delete',

                                cancelText:
                                    'Cancel',

                                type:
                                    'danger',

                                onConfirm:
                                    function() {
                                        formReference
                                            .dataset
                                            .deleteApproved =
                                            '1';

                                        formReference
                                            .requestSubmit();
                                    }
                            });
                        }
                    );
                }
            );

        // ============================================
        // DELETE CONFIRMATION - NON-FORM ELEMENTS
        // ============================================

        document
            .querySelectorAll(
                '[data-confirm-delete]'
            )
            .forEach(
                function(element) {
                    if (
                        element.tagName
                        === 'FORM'
                    ) {
                        return;
                    }

                    element.addEventListener(
                        'click',
                        function(event) {
                            const itemName =
                                this.dataset
                                    .itemName
                                || 'this item';

                            event.preventDefault();
                            event.stopPropagation();

                            const elementReference =
                                this;

                            showConfirmModal({
                                title:
                                    'Confirm Delete',

                                message:
                                    `Are you sure you want to delete "${itemName}"? This action cannot be undone.`,

                                confirmText:
                                    'Yes, Delete',

                                cancelText:
                                    'Cancel',

                                type:
                                    'danger',

                                onConfirm:
                                    function() {
                                        if (
                                            elementReference
                                                .tagName
                                            === 'A'
                                        ) {
                                            window.location.href =
                                                elementReference
                                                    .href;

                                            return;
                                        }

                                        const form =
                                            elementReference
                                                .closest(
                                                    'form'
                                                );

                                        if (form) {
                                            form.requestSubmit();
                                        }
                                    }
                            });
                        }
                    );
                }
            );

        // ============================================
        // UPDATE CONFIRMATION - FORMS
        // ============================================

        document
            .querySelectorAll(
                'form[data-confirm-update]'
            )
            .forEach(
                function(form) {
                    form.addEventListener(
                        'submit',
                        function(event) {
                            if (
                                this.dataset
                                    .updateApproved
                                === '1'
                            ) {
                                delete this.dataset
                                    .updateApproved;

                                return;
                            }

                            event.preventDefault();
                            event.stopImmediatePropagation();

                            const itemName =
                                this.dataset
                                    .itemName
                                || 'this item';

                            const formReference =
                                this;

                            showConfirmModal({
                                title:
                                    'Confirm Update',

                                message:
                                    `Are you sure you want to update "${itemName}"?`,

                                confirmText:
                                    'Yes, Update',

                                cancelText:
                                    'Cancel',

                                type:
                                    'warning',

                                onConfirm:
                                    function() {
                                        formReference
                                            .dataset
                                            .updateApproved =
                                            '1';

                                        formReference
                                            .requestSubmit();
                                    }
                            });
                        }
                    );
                }
            );

        // ============================================
        // UPDATE CONFIRMATION - BUTTONS
        // ============================================

        document
            .querySelectorAll(
                '[data-confirm-update]'
            )
            .forEach(
                function(element) {
                    if (
                        element.tagName
                        === 'FORM'
                    ) {
                        return;
                    }

                    element.addEventListener(
                        'click',
                        function(event) {
                            const form =
                                this.closest(
                                    'form'
                                );

                            if (
                                form
                                && ! form.reportValidity()
                            ) {
                                return;
                            }

                            event.preventDefault();
                            event.stopPropagation();

                            const itemName =
                                this.dataset
                                    .itemName
                                || 'this item';

                            showConfirmModal({
                                title:
                                    'Confirm Update',

                                message:
                                    `Are you sure you want to update "${itemName}"?`,

                                confirmText:
                                    'Yes, Update',

                                cancelText:
                                    'Cancel',

                                type:
                                    'warning',

                                onConfirm:
                                    function() {
                                        if (form) {
                                            form.requestSubmit();
                                        }
                                    }
                            });
                        }
                    );
                }
            );

        // ============================================
        // SAVE CONFIRMATION
        // ============================================

        document
            .querySelectorAll(
                '[data-confirm-save]'
            )
            .forEach(
                function(button) {
                    button.addEventListener(
                        'click',
                        function(event) {
                            const form =
                                this.closest(
                                    'form'
                                );

                            if (! form) {
                                return;
                            }

                            if (
                                ! form.reportValidity()
                            ) {
                                return;
                            }

                            event.preventDefault();
                            event.stopPropagation();

                            showConfirmModal({
                                title:
                                    'Save Changes',

                                message:
                                    'Are you sure you want to save your changes?',

                                confirmText:
                                    'Yes, Save',

                                cancelText:
                                    'Cancel',

                                type:
                                    'info',

                                onConfirm:
                                    function() {
                                        form.requestSubmit();
                                    }
                            });
                        }
                    );
                }
            );

                // ============================================
        // INTERNATIONAL PHONE NUMBER VALIDATION
        // ============================================

        const phoneInput =
            document.querySelector(
                'input[name="phone"]'
            );

        const phoneCountryInput =
            document.getElementById(
                'phone_country'
            );

        if (
            phoneInput
            && phoneCountryInput
        ) {
            const phoneForm =
                phoneInput.closest('form');

            const phoneField =
                phoneInput.closest(
                    '.cpbn-field'
                );

            const iti =
                intlTelInput(
                    phoneInput,
                    {
                        initialCountry:
                            (
                                phoneCountryInput.value
                                || 'BN'
                            ).toLowerCase(),

                        countryOrder: [
                            'bn',
                            'my',
                            'sg',
                            'id',
                            'ph',
                            'th',
                            'vn',
                        ],

                        separateDialCode: true,

                        loadUtils:
                            () =>
                                import(
                                    'intl-tel-input/utils'
                                ),
                    }
                );

            let phoneInitialising =
                true;

            let phoneUtilsReady =
                false;

            function updatePhoneCountry() {
                const selectedCountry =
                    iti.getSelectedCountry();

                phoneCountryInput.value =
                    selectedCountry?.iso2
                        ? selectedCountry.iso2
                            .toUpperCase()
                        : '';
            }

            function getPhoneErrorElement() {
                if (! phoneField) {
                    return null;
                }

                let errorElement =
                    phoneField.querySelector(
                        '.phone-error'
                    );

                if (! errorElement) {
                    errorElement =
                        document.createElement(
                            'small'
                        );

                    errorElement.className =
                        'phone-error';

                    errorElement.style.cssText =
                        [
                            'color:#c65b4e',
                            'font-size:11px',
                            'display:block',
                            'margin-top:4px',
                        ].join(';');

                    phoneField.appendChild(
                        errorElement
                    );
                }

                return errorElement;
            }

            function clearPhoneError() {
                phoneInput.style.borderColor =
                    '';

                phoneInput.style.boxShadow =
                    '';

                phoneInput.setCustomValidity(
                    ''
                );

                const errorElement =
                    phoneField?.querySelector(
                        '.phone-error'
                    );

                if (errorElement) {
                    errorElement.remove();
                }
            }

            function showPhoneError(
                message
            ) {
                phoneInput.style.borderColor =
                    '#c65b4e';

                phoneInput.style.boxShadow =
                    '0 0 0 3px rgba(198,91,78,0.15)';

                phoneInput.setCustomValidity(
                    message
                );

                const errorElement =
                    getPhoneErrorElement();

                if (errorElement) {
                    errorElement.textContent =
                        message;
                }
            }

            function getPhoneValidationMessage() {
                const error =
                    iti.getValidationError();

                switch (error) {
                    case 'TOO_SHORT':
                        return 'This phone number is too short for the selected country.';

                    case 'TOO_LONG':
                        return 'This phone number is too long for the selected country.';

                    case 'INVALID_COUNTRY_CODE':
                        return 'Please select a valid country code.';

                    case 'INVALID_LENGTH':
                        return 'This phone number has an invalid length for the selected country.';

                    default:
                        return 'Enter a valid phone number for the selected country.';
                }
            }

            function validateInternationalPhone() {
                updatePhoneCountry();

                const value =
                    phoneInput.value.trim();

                if (value === '') {
                    clearPhoneError();

                    return true;
                }

                if (! iti.isValidNumber()) {
                    showPhoneError(
                        getPhoneValidationMessage()
                    );

                    return false;
                }

                clearPhoneError();

                return true;
            }

            phoneInput.addEventListener(
                'input',
                function() {
                    clearPhoneError();
                }
            );

            phoneInput.addEventListener(
                'blur',
                function() {
                    if (! phoneUtilsReady) {
                        return;
                    }

                    validateInternationalPhone();
                }
            );

            phoneInput.addEventListener(
                'countrychange',
                function() {
                    updatePhoneCountry();
                    clearPhoneError();

                    if (
                        ! phoneInitialising
                        && typeof window
                            .setFormChanged
                            === 'function'
                    ) {
                        window.setFormChanged(
                            true
                        );
                    }
                }
            );

            iti.promise.then(
                function() {
                    phoneUtilsReady =
                        true;

                    /*
                     * Stored numbers are saved in
                     * international E.164 format.
                     *
                     * setNumber() automatically selects
                     * the matching country.
                     */
                    if (
                        phoneInput.value.trim()
                        !== ''
                    ) {
                        iti.setNumber(
                            phoneInput.value.trim()
                        );
                    }

                    updatePhoneCountry();

                    phoneInitialising =
                        false;
                }
            );

            if (phoneForm) {
                /*
                 * Run phone validation before the
                 * existing confirmation handler.
                 *
                 * We deliberately DO NOT requestSubmit()
                 * again after successful validation.
                 * The normal profile confirmation flow
                 * remains responsible for submission.
                 */
                phoneForm.addEventListener(
                    'submit',
                    async function(event) {
                        updatePhoneCountry();

                        if (
                            phoneInput.value.trim()
                            === ''
                        ) {
                            clearPhoneError();

                            return;
                        }

                        if (! phoneUtilsReady) {
                            event.preventDefault();
                            event.stopImmediatePropagation();

                            const submitter =
                                event.submitter;

                            await iti.promise;

                            phoneUtilsReady =
                                true;

                            if (submitter) {
                                phoneForm.requestSubmit(
                                    submitter
                                );
                            } else {
                                phoneForm.requestSubmit();
                            }

                            return;
                        }

                        if (
                            ! validateInternationalPhone()
                        ) {
                            event.preventDefault();
                            event.stopImmediatePropagation();

                            phoneInput.focus();
                        }
                    },
                    true
                );
            }
        }

        // ============================================
        // MILESTONE FORM VALIDATION
        // ============================================

        const milestoneForm =
            document.getElementById(
                'milestone-form'
            );

        const titleInput =
            document.getElementById(
                'milestone-title'
            );

        const titleError =
            document.getElementById(
                'title-error'
            );

        if (
            milestoneForm
            && titleInput
        ) {
            milestoneForm.addEventListener(
                'submit',
                function(event) {
                    const title =
                        titleInput
                            .value
                            .trim();

                    if (title === '') {
                        event.preventDefault();

                        titleInput.classList
                            .add(
                                'error-input'
                            );

                        if (titleError) {
                            titleError.style.display =
                                'block';
                        }

                        titleInput.focus();

                        return false;
                    }

                    titleInput.classList
                        .remove(
                            'error-input'
                        );

                    if (titleError) {
                        titleError.style.display =
                            'none';
                    }

                    return true;
                }
            );
        }

        // ============================================
        // DYNAMIC PROJECTS
        // ============================================

        const projectList =
            document.getElementById(
                'project-list'
            );

        if (projectList) {
            let projectIndex =
                Number(
                    projectList.dataset
                        .nextIndex
                ) || 0;

            const addProjectButton =
                document.getElementById(
                    'add-project'
                );

            if (addProjectButton) {
                addProjectButton
                    .addEventListener(
                        'click',
                        function() {
                            const card =
                                document.createElement(
                                    'div'
                                );

                            card.className =
                                'cpbn-project-card';

                            const iteration =
                                projectList
                                    .children
                                    .length
                                + 1;

                            card.innerHTML = `
                                <span class="cpbn-project-number">
                                    Project #${iteration}
                                </span>

                                <div class="cpbn-fgrid">
                                    <div class="cpbn-field">
                                        <label>
                                            Project Title
                                            <span class="req">*</span>
                                        </label>

                                        <input
                                            type="text"
                                            name="projects[${projectIndex}][title]"
                                            placeholder="e.g. Hobbee Apps"
                                            required
                                        >
                                    </div>

                                    <div class="cpbn-field">
                                        <label>Your Role</label>

                                        <input
                                            type="text"
                                            name="projects[${projectIndex}][role]"
                                            placeholder="e.g. Lead Developer"
                                        >
                                    </div>

                                    <div class="cpbn-field">
                                        <label>Project URL</label>

                                        <input
                                            type="url"
                                            name="projects[${projectIndex}][project_url]"
                                            placeholder="https://github.com/your-project"
                                        >
                                    </div>

                                    <div class="cpbn-field full">
                                        <label>Description</label>

                                        <textarea
                                            name="projects[${projectIndex}][description]"
                                            rows="2"
                                            placeholder="Brief description"
                                        ></textarea>
                                    </div>

                                    <div class="cpbn-field full">
                                        <label>Technologies Used</label>

                                        <input
                                            type="text"
                                            name="projects[${projectIndex}][technologies_used]"
                                            placeholder="e.g. Python, React, MySQL"
                                        >

                                        <small class="cpbn-file-note">
                                            Separate technologies with commas
                                        </small>
                                    </div>

                                    <div class="cpbn-field">
                                        <label>Start Date</label>

                                        <input
                                            type="date"
                                            name="projects[${projectIndex}][start_date]"
                                        >
                                    </div>

                                    <div class="cpbn-field">
                                        <label>End Date</label>

                                        <input
                                            type="date"
                                            name="projects[${projectIndex}][end_date]"
                                        >
                                    </div>

                                    <div class="cpbn-field full">
                                        <label>Achievements</label>

                                        <textarea
                                            name="projects[${projectIndex}][achievements]"
                                            rows="2"
                                            placeholder="What did you accomplish?"
                                        ></textarea>
                                    </div>
                                </div>

                                <button
                                    type="button"
                                    class="cpbn-remove-project"
                                    onclick="removeProject(this)"
                                >
                                    <i class="fas fa-trash-alt"></i>
                                    Remove
                                </button>
                            `;

                            projectList
                                .appendChild(
                                    card
                                );

                            projectIndex++;

                            if (
                                typeof window
                                    .setFormChanged
                                === 'function'
                            ) {
                                window.setFormChanged(
                                    true
                                );
                            }
                        }
                    );
            }
        }

        // ============================================
        // DYNAMIC CERTIFICATIONS
        // ============================================

        const certificationList =
            document.getElementById(
                'certification-list'
            );

        if (certificationList) {
            let certificationIndex =
                Number(
                    certificationList
                        .dataset
                        .nextIndex
                ) || 0;

            const addCertificationButton =
                document.getElementById(
                    'add-certification'
                );

            if (addCertificationButton) {
                addCertificationButton
                    .addEventListener(
                        'click',
                        function() {
                            const card =
                                document.createElement(
                                    'div'
                                );

                            card.className =
                                'cpbn-certification-card';

                            card.dataset
                                .hasExistingEvidence =
                                '0';

                            card.innerHTML = `
                                <div class="cpbn-fgrid">
                                    <div class="cpbn-field">
                                        <label>
                                            Certification Name
                                            <span class="req">*</span>
                                        </label>

                                        <input
                                            type="text"
                                            name="certifications[${certificationIndex}][certification_name]"
                                            placeholder="e.g. AWS Cloud Practitioner"
                                            required
                                        >
                                    </div>

                                    <div class="cpbn-field">
                                        <label>Issuing Organisation</label>

                                        <input
                                            type="text"
                                            name="certifications[${certificationIndex}][issuing_organization]"
                                            placeholder="e.g. AWS, Cisco, Politeknik Brunei"
                                        >
                                    </div>

                                    <div class="cpbn-field">
                                        <label>Issue Date</label>

                                        <input
                                            type="date"
                                            name="certifications[${certificationIndex}][issue_date]"
                                            max="${new Date().toISOString().split('T')[0]}"
                                        >
                                    </div>

                                    <div class="cpbn-field">
                                        <label>Certificate File</label>

                                        <input
                                            type="file"
                                            class="cpbn-certificate-file"
                                            name="certifications[${certificationIndex}][certificate_file]"
                                            accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png"
                                        >

                                        <small class="cpbn-file-note">
                                            PDF, JPG, JPEG or PNG · Maximum 5 MB
                                        </small>

                                        <div
                                            class="cpbn-file-selected"
                                            hidden
                                        ></div>

                                        <button
                                            type="button"
                                            class="cpbn-clear-file"
                                            onclick="clearSelectedCertificateFile(this)"
                                            hidden
                                        >
                                            Clear selected file
                                        </button>
                                    </div>
                                </div>

                                <button
                                    type="button"
                                    class="cpbn-remove-certification"
                                    onclick="removeCertification(this)"
                                >
                                    <i class="fas fa-trash-alt"></i>
                                    Remove
                                </button>
                            `;

                            certificationList
                                .appendChild(
                                    card
                                );

                            initialiseCertificateFileInput(
                                card.querySelector(
                                    '.cpbn-certificate-file'
                                )
                            );

                            certificationIndex++;

                            if (
                                typeof window
                                    .setFormChanged
                                === 'function'
                            ) {
                                window.setFormChanged(
                                    true
                                );
                            }
                        }
                    );
            }

            certificationList
                .querySelectorAll(
                    '.cpbn-certificate-file'
                )
                .forEach(
                    initialiseCertificateFileInput
                );
        }

        // ============================================
        // INTERESTS - OTHERS
        // ============================================

        const otherCheckbox =
            document.getElementById(
                'interest-others'
            );

        if (otherCheckbox) {
            otherCheckbox.addEventListener(
                'change',
                function() {
                    const field =
                        document.getElementById(
                            'interest-others-field'
                        );

                    const textInput =
                        document.getElementById(
                            'interest-others-text'
                        );

                    if (
                        ! field
                        || ! textInput
                    ) {
                        return;
                    }

                    if (this.checked) {
                        field.style.display =
                            'block';

                        textInput.setAttribute(
                            'required',
                            'required'
                        );

                        textInput.focus();
                    } else {
                        field.style.display =
                            'none';

                        textInput.removeAttribute(
                            'required'
                        );

                        textInput.value =
                            '';
                    }

                    if (
                        typeof window
                            .setFormChanged
                        === 'function'
                    ) {
                        window.setFormChanged(
                            true
                        );
                    }
                }
            );
        }

        // ============================================
        // PROFILE FORM
        // ============================================

        const profileForm =
            document.getElementById(
                'profile-form'
            );

        if (profileForm) {
            /*
             * Convert the comma-separated "Other Interests"
             * field into normal interests[] values before
             * Laravel receives the form.
             */
            profileForm.addEventListener(
                'submit',
                function() {
                    profileForm
                        .querySelectorAll(
                            '[data-generated-other-interest]'
                        )
                        .forEach(
                            element =>
                                element.remove()
                        );

                    const otherCheckbox =
                        document.getElementById(
                            'interest-others'
                        );

                    const otherText =
                        document.getElementById(
                            'interest-others-text'
                        );

                    if (
                        ! otherCheckbox?.checked
                        || ! otherText
                    ) {
                        return;
                    }

                    otherText.value
                        .split(',')
                        .map(
                            value =>
                                value.trim()
                        )
                        .filter(Boolean)
                        .forEach(
                            value => {
                                const hidden =
                                    document.createElement(
                                        'input'
                                    );

                                hidden.type =
                                    'hidden';

                                hidden.name =
                                    'interests[]';

                                hidden.value =
                                    value;

                                hidden.dataset
                                    .generatedOtherInterest =
                                    '1';

                                profileForm
                                    .appendChild(
                                        hidden
                                    );
                            }
                        );
                }
            );
        }

        // ============================================
        // UNSAVED CHANGES WARNING
        // ============================================

        const form =
            document.getElementById(
                'profile-form'
            );

        if (form) {
            let formChanged =
                false;

            function markChanged(event) {
                /*
                * Ignore changes fired programmatically by
                * components such as intl-tel-input while
                * the form is initialising.
                */
                if (
                    event
                    && event.isTrusted === false
                ) {
                    return;
                }

                formChanged =
                    true;

                window.formChanged =
                    true;
            }

            form
                .querySelectorAll(
                    'input, select, textarea'
                )
                .forEach(
                    function(input) {
                        input.addEventListener(
                            'change',
                            markChanged
                        );

                        input.addEventListener(
                            'input',
                            markChanged
                        );
                    }
                );

            const observer =
                new MutationObserver(
                    function(mutations) {
                        mutations.forEach(
                            function(mutation) {
                                mutation.addedNodes
                                    .forEach(
                                        function(node) {
                                            if (
                                                node.nodeType
                                                !== 1
                                                || ! node
                                                    .querySelectorAll
                                            ) {
                                                return;
                                            }

                                            node
                                                .querySelectorAll(
                                                    'input, select, textarea'
                                                )
                                                .forEach(
                                                    function(input) {
                                                        input.addEventListener(
                                                            'change',
                                                            markChanged
                                                        );

                                                        input.addEventListener(
                                                            'input',
                                                            markChanged
                                                        );
                                                    }
                                                );
                                        }
                                    );
                            }
                        );
                    }
                );

            observer.observe(
                form,
                {
                    childList: true,
                    subtree: true
                }
            );

            /*
             * Reset the warning only when the browser is
             * actually submitting the form.
             *
             * Do not reset it merely because Save Profile
             * was clicked; the user may still cancel the
             * confirmation modal.
             */
            form.addEventListener(
                'submit',
                function() {
                    formChanged =
                        false;

                    window.formChanged =
                        false;
                }
            );

            window.addEventListener(
                'beforeunload',
                function(event) {
                    if (! formChanged) {
                        return;
                    }

                    event.preventDefault();
                    event.returnValue =
                        '';
                }
            );

            document
                .querySelectorAll(
                    '.back, a[href*="profile"], a[href*="dashboard"], .nav-link, .nav-brand'
                )
                .forEach(
                    function(link) {
                        link.addEventListener(
                            'click',
                            function(event) {
                                if (
                                    ! formChanged
                                ) {
                                    return;
                                }

                                const confirmLeave =
                                    window.confirm(
                                        '⚠️ You have unsaved changes. Are you sure you want to leave?'
                                    );

                                if (
                                    ! confirmLeave
                                ) {
                                    event.preventDefault();
                                    event.stopPropagation();
                                }
                            }
                        );
                    }
                );

            window.formChanged =
                false;

            window.setFormChanged =
                function(value) {
                    formChanged =
                        Boolean(value);

                    window.formChanged =
                        formChanged;
                };
        }
    }
);

// ============================================
// GLOBAL PROJECT REMOVAL
// ============================================

window.removeProject =
    function(button) {
        const card =
            button.closest(
                '.cpbn-project-card'
            );

        if (! card) {
            return;
        }

        showConfirmModal({
            title:
                'Remove Project',

            message:
                'Remove this project from your profile? The change will only be saved after you confirm Save Profile.',

            confirmText:
                'Remove',

            cancelText:
                'Cancel',

            type:
                'warning',

            onConfirm:
                function() {
                    card.remove();

                    const projectList =
                        document.getElementById(
                            'project-list'
                        );

                    projectList
                        ?.querySelectorAll(
                            '.cpbn-project-card'
                        )
                        .forEach(
                            function(projectCard, index) {
                                const number =
                                    projectCard
                                        .querySelector(
                                            '.cpbn-project-number'
                                        );

                                if (number) {
                                    number.textContent =
                                        `Project #${index + 1}`;
                                }
                            }
                        );

                    if (
                        typeof window
                            .setFormChanged
                        === 'function'
                    ) {
                        window.setFormChanged(
                            true
                        );
                    }
                }
        });
    };

// ============================================
// GLOBAL CERTIFICATION REMOVAL
// ============================================

window.removeCertification =
    function(button) {
        const card =
            button.closest(
                '.cpbn-certification-card'
            );

        if (! card) {
            return;
        }

        const hasExistingEvidence =
            card.dataset
                .hasExistingEvidence
            === '1';

        const message =
            hasExistingEvidence
                ? 'Remove this certification? Its saved evidence will also be deleted only after you save the profile.'
                : 'Remove this certification? The change will only take effect after you save the profile.';

        showConfirmModal({
            title:
                'Remove Certification',

            message:
                message,

            confirmText:
                'Remove',

            cancelText:
                'Cancel',

            type:
                'warning',

            onConfirm:
                function() {
                    const idInput =
                        card.querySelector(
                            'input[name$="[id]"]'
                        );

                    if (
                        idInput
                        && idInput.value
                    ) {
                        const removedFields =
                            document.getElementById(
                                'removed-certification-fields'
                            );

                        if (removedFields) {
                            const alreadyMarked =
                                Array.from(
                                    removedFields
                                        .querySelectorAll(
                                            'input[name="removed_certification_ids[]"]'
                                        )
                                )
                                    .some(
                                        input =>
                                            input.value
                                            === idInput.value
                                    );

                            if (! alreadyMarked) {
                                const hidden =
                                    document.createElement(
                                        'input'
                                    );

                                hidden.type =
                                    'hidden';

                                hidden.name =
                                    'removed_certification_ids[]';

                                hidden.value =
                                    idInput.value;

                                removedFields
                                    .appendChild(
                                        hidden
                                    );
                            }
                        }
                    }

                    card.remove();

                    if (
                        typeof window
                            .setFormChanged
                        === 'function'
                    ) {
                        window.setFormChanged(
                            true
                        );
                    }
                }
        });
    };

// ============================================
// START ALPINE.JS
// ============================================

Alpine.start();