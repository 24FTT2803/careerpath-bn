// ============================================
// IMPORT ALPINE.JS
// ============================================
import Alpine from 'alpinejs';

window.Alpine = Alpine;

// ============================================
// CONFIRMATION MODAL - FIXED
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

    // Remove existing modal
    closeModal();

    // Create overlay
    const overlay = document.createElement('div');
    overlay.className = 'cpbn-modal-overlay';
    overlay.id = 'cpbn-modal-overlay';
    
    // Create modal
    const modal = document.createElement('div');
    modal.className = 'cpbn-modal';
    
    const iconMap = {
        danger: '⚠️',
        warning: '⚡',
        info: 'ℹ️',
        success: '✅'
    };
    
    const btnClass = type === 'danger' ? 'danger' : '';
    
    modal.innerHTML = `
        <div class="cpbn-modal-header">
            <h3>${iconMap[type] || 'ℹ️'} ${title}</h3>
            <button type="button" class="cpbn-modal-close" id="cpbn-modal-close-btn">×</button>
        </div>
        <div class="cpbn-modal-body">
            <p>${message}</p>
        </div>
        <div class="cpbn-modal-footer">
            <button type="button" class="cpbn-modal-btn cpbn-modal-btn-cancel" id="cpbn-modal-cancel-btn">
                ${cancelText}
            </button>
            <button type="button" class="cpbn-modal-btn cpbn-modal-btn-confirm ${btnClass}" id="cpbn-modal-confirm-btn">
                ${confirmText}
            </button>
        </div>
    `;
    
    overlay.appendChild(modal);
    document.body.appendChild(overlay);
    
    // ============================================
    // CLOSE MODAL ONLY (no callbacks)
    // ============================================
    const closeModalOnly = function() {
        const overlayEl = document.getElementById('cpbn-modal-overlay');
        if (overlayEl) {
            overlayEl.remove();
        }
    };
    
    // ============================================
    // CANCEL BUTTON
    // ============================================
    const cancelBtn = document.getElementById('cpbn-modal-cancel-btn');
    if (cancelBtn) {
        cancelBtn.addEventListener('click', function() {
            closeModalOnly();
            if (typeof onCancel === 'function') {
                onCancel();
            }
        });
    }
    
    // ============================================
    // CLOSE (X) BUTTON
    // ============================================
    const closeBtn = document.getElementById('cpbn-modal-close-btn');
    if (closeBtn) {
        closeBtn.addEventListener('click', function() {
            closeModalOnly();
            if (typeof onCancel === 'function') {
                onCancel();
            }
        });
    }
    
    // ============================================
    // CONFIRM BUTTON
    // ============================================
    const confirmBtn = document.getElementById('cpbn-modal-confirm-btn');
    if (confirmBtn) {
        confirmBtn.addEventListener('click', function() {
            closeModalOnly();
            if (typeof onConfirm === 'function') {
                onConfirm();
            }
        });
    }
    
    // ============================================
    // CLICK OUTSIDE
    // ============================================
    overlay.addEventListener('click', function(e) {
        if (e.target === overlay) {
            closeModalOnly();
            if (typeof onCancel === 'function') {
                onCancel();
            }
        }
    });
    
    // ============================================
    // ESCAPE KEY
    // ============================================
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const overlayEl = document.getElementById('cpbn-modal-overlay');
            if (overlayEl) {
                closeModalOnly();
                if (typeof onCancel === 'function') {
                    onCancel();
                }
            }
        }
    });
}

function closeModal() {
    const overlay = document.getElementById('cpbn-modal-overlay');
    if (overlay) {
        overlay.remove();
    }
}

// ============================================
// AUTO-ATTACH CONFIRMATIONS
// ============================================

document.addEventListener('DOMContentLoaded', function() {
    
    // ============================================
    // DELETE CONFIRMATION - FORMS & BUTTONS
    // ============================================
    
    // Handle forms with data-confirm-delete
    document.querySelectorAll('form[data-confirm-delete]').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            const itemName = this.dataset.itemName || 'this item';
            e.preventDefault();
            e.stopPropagation();
            
            const formRef = this;
            
            showConfirmModal({
                title: 'Confirm Delete',
                message: `Are you sure you want to delete "${itemName}"? This action cannot be undone.`,
                confirmText: 'Yes, Delete',
                cancelText: 'Cancel',
                type: 'danger',
                onConfirm: function() {
                    formRef.submit();
                },
                onCancel: function() {
                    // Just close modal, do nothing
                }
            });
        });
    });
    
    // Handle buttons with data-confirm-delete (including dynamically created ones)
    document.querySelectorAll('[data-confirm-delete]').forEach(function(element) {
        // Skip if it's a form (already handled above)
        if (element.tagName === 'FORM') return;
        
        // For buttons and links
        element.addEventListener('click', function(e) {
            const itemName = this.dataset.itemName || 'this item';
            e.preventDefault();
            e.stopPropagation();
            
            const elementRef = this;
            
            showConfirmModal({
                title: 'Confirm Delete',
                message: `Are you sure you want to delete "${itemName}"? This action cannot be undone.`,
                confirmText: 'Yes, Delete',
                cancelText: 'Cancel',
                type: 'danger',
                onConfirm: function() {
                    // If it's a link, navigate
                    if (elementRef.tagName === 'A') {
                        window.location.href = elementRef.href;
                    }
                    // If it's a button, find the form and submit
                    else if (elementRef.tagName === 'BUTTON') {
                        const form = elementRef.closest('form');
                        if (form) {
                            form.submit();
                        } else {
                            // If no form, just trigger the click
                            elementRef.click();
                        }
                    }
                },
                onCancel: function() {
                    // Just close modal, do nothing
                }
            });
        });
    });

    // ============================================
    // UPDATE CONFIRMATION - FORMS & BUTTONS
    // ============================================
    
    // Handle forms with data-confirm-update
    document.querySelectorAll('form[data-confirm-update]').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            const itemName = this.dataset.itemName || 'this item';
            e.preventDefault();
            e.stopPropagation();
            
            const formRef = this;
            
            showConfirmModal({
                title: 'Confirm Update',
                message: `Are you sure you want to update "${itemName}"?`,
                confirmText: 'Yes, Update',
                cancelText: 'Cancel',
                type: 'warning',
                onConfirm: function() {
                    formRef.submit();
                },
                onCancel: function() {
                    // Just close modal, do nothing
                }
            });
        });
    });
    
    // Handle buttons with data-confirm-update
    document.querySelectorAll('[data-confirm-update]').forEach(function(element) {
        if (element.tagName === 'FORM') return;
        
        element.addEventListener('click', function(e) {
            const itemName = this.dataset.itemName || 'this item';
            e.preventDefault();
            e.stopPropagation();
            
            const elementRef = this;
            
            showConfirmModal({
                title: 'Confirm Update',
                message: `Are you sure you want to update "${itemName}"?`,
                confirmText: 'Yes, Update',
                cancelText: 'Cancel',
                type: 'warning',
                onConfirm: function() {
                    const form = elementRef.closest('form');
                    if (form) {
                        form.submit();
                    }
                },
                onCancel: function() {
                    // Just close modal, do nothing
                }
            });
        });
    });

    // ============================================
    // SAVE CONFIRMATION
    // ============================================
    document.querySelectorAll('[data-confirm-save]').forEach(function(button) {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const form = this.closest('form');
            if (!form) return;
            
            const formRef = form;
            
            showConfirmModal({
                title: 'Save Changes',
                message: 'Are you sure you want to save your changes?',
                confirmText: 'Yes, Save',
                cancelText: 'Cancel',
                type: 'info',
                onConfirm: function() {
                    formRef.submit();
                },
                onCancel: function() {
                    // Just close modal, do nothing
                }
            });
        });
    });

    // ============================================
    // PHONE NUMBER VALIDATION
    // ============================================
    
    function validatePhone(input) {
        // Store original value
        const originalValue = input.value;
        
        // Remove any character that is not allowed
        const cleaned = originalValue.replace(/[^\d\+\s\-\(\)]/g, '');
        
        // Update the input if it changed
        if (cleaned !== originalValue) {
            input.value = cleaned;
        }
        
        // Check if the cleaned value meets the requirements
        const isValid = /^[\+\d\s\-\(\)]{7,20}$/.test(cleaned);
        
        // Update input styling
        if (cleaned.length > 0) {
            if (!isValid) {
                input.style.borderColor = '#c65b4e';
                input.style.boxShadow = '0 0 0 3px rgba(198,91,78,0.15)';
                
                // Show inline error message if not already shown
                let errorMsg = input.parentElement.querySelector('.phone-error');
                if (!errorMsg) {
                    errorMsg = document.createElement('small');
                    errorMsg.className = 'phone-error';
                    errorMsg.style.cssText = 'color:#c65b4e;font-size:11px;display:block;margin-top:4px;';
                    errorMsg.textContent = 'Please enter only digits, +, -, spaces, or parentheses (7-20 characters)';
                    input.parentElement.appendChild(errorMsg);
                }
            } else {
                input.style.borderColor = '#4c8a68';
                input.style.boxShadow = '0 0 0 3px rgba(76,138,104,0.15)';
                
                // Remove error message if valid
                const errorMsg = input.parentElement.querySelector('.phone-error');
                if (errorMsg) {
                    errorMsg.remove();
                }
            }
        } else {
            input.style.borderColor = '';
            input.style.boxShadow = '';
            
            // Remove error message if empty
            const errorMsg = input.parentElement.querySelector('.phone-error');
            if (errorMsg) {
                errorMsg.remove();
            }
        }
    }

    // Auto-attach phone validation
    const phoneInputs = document.querySelectorAll('input[name="phone"], input[type="tel"]');
    phoneInputs.forEach(input => {
        // Validate on input (typing)
        input.addEventListener('input', function() {
            validatePhone(this);
        });
        
        // Validate on blur (leaving the field)
        input.addEventListener('blur', function() {
            validatePhone(this);
        });
    });

    // ============================================
    // MILESTONE FORM VALIDATION
    // ============================================
    
    const milestoneForm = document.getElementById('milestone-form');
    const titleInput = document.getElementById('milestone-title');
    const titleError = document.getElementById('title-error');
    
    if (milestoneForm && titleInput) {
        // Validate on submit
        milestoneForm.addEventListener('submit', function(e) {
            const title = titleInput.value.trim();
            
            if (title === '') {
                e.preventDefault();
                titleInput.classList.add('error-input');
                if (titleError) {
                    titleError.style.display = 'block';
                }
                titleInput.focus();
                return false;
            }
            
            titleInput.classList.remove('error-input');
            if (titleError) {
                titleError.style.display = 'none';
            }
            return true;
        });

        // Clear error on input
        titleInput.addEventListener('input', function() {
            if (this.value.trim() !== '') {
                this.classList.remove('error-input');
                if (titleError) {
                    titleError.style.display = 'none';
                }
            }
        });

        // Clear error on focus
        titleInput.addEventListener('focus', function() {
            if (this.value.trim() !== '') {
                this.classList.remove('error-input');
                if (titleError) {
                    titleError.style.display = 'none';
                }
            }
        });
    }

    // ============================================
    // DYNAMIC PROJECTS - Add/Remove
    // ============================================

    const projectList = document.getElementById('project-list');
    
    if (projectList) {
        let projectIndex = Number(projectList.dataset.nextIndex) || 0;

        // If there are existing projects, find the highest index
        const existingProjectCards = projectList.querySelectorAll('.cpbn-project-card');
        if (existingProjectCards.length > 0) {
            let highestIndex = 0;
            existingProjectCards.forEach(card => {
                const inputs = card.querySelectorAll('input[name^="projects["]');
                inputs.forEach(input => {
                    const match = input.name.match(/projects\[(\d+)\]/);
                    if (match && parseInt(match[1]) > highestIndex) {
                        highestIndex = parseInt(match[1]);
                    }
                });
            });
            projectIndex = highestIndex + 1;
        }

        // Add Project
        const addProjectBtn = document.getElementById('add-project');
        if (addProjectBtn) {
            addProjectBtn.addEventListener('click', function() {
                const card = document.createElement('div');
                card.className = 'cpbn-project-card';

                const iteration = projectList.children.length + 1;

                card.innerHTML = `
                    <span class="cpbn-project-number">Project #${iteration}</span>

                    <div class="cpbn-fgrid">
                        <div class="cpbn-field">
                            <label>Project Title <span class="req">*</span></label>
                            <input type="text" name="projects[${projectIndex}][title]"
                                   placeholder="e.g. Hobbee Apps" required>
                        </div>
                        <div class="cpbn-field">
                            <label>Your Role</label>
                            <input type="text" name="projects[${projectIndex}][role]"
                                   placeholder="e.g. Lead Developer">
                        </div>
                        <div class="cpbn-field">
                            <label>Project URL (Optional)</label>
                            <input type="url" name="projects[${projectIndex}][project_url]"
                                   placeholder="https://github.com/your-project-name">
                        </div>
                        <div class="cpbn-field full">
                            <label>Description</label>
                            <textarea name="projects[${projectIndex}][description]" rows="2"
                                      placeholder="Brief description of the project"></textarea>
                        </div>
                        <div class="cpbn-field full">
                            <label>Technologies Used</label>
                            <input type="text" name="projects[${projectIndex}][technologies_used]"
                                   placeholder="e.g. Python, React, MySQL">
                            <small class="cpbn-file-note">Separate technologies with commas</small>
                        </div>
                        <div class="cpbn-field">
                            <label>Start Date</label>
                            <input type="date" name="projects[${projectIndex}][start_date]">
                        </div>
                        <div class="cpbn-field">
                            <label>End Date</label>
                            <input type="date" name="projects[${projectIndex}][end_date]">
                        </div>
                        <div class="cpbn-field full">
                            <label>Achievements</label>
                            <textarea name="projects[${projectIndex}][achievements]" rows="2"
                                      placeholder="What did you accomplish?"></textarea>
                        </div>
                    </div>

                    <button type="button" class="cpbn-remove-project" onclick="removeProject(this)" data-confirm-delete data-item-name="this new project">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;">
                            <path d="M18 6L6 18M6 6l12 12"/>
                        </svg>
                        Remove Project
                    </button>
                `;

                projectList.appendChild(card);
                projectIndex++;
            });
        }

        // Remove Project - Make it global so onclick works
        window.removeProject = function(button) {
            const card = button.closest('.cpbn-project-card');
            // Don't remove if it's the last one
            if (projectList.children.length <= 1) {
                // Just clear the inputs instead of removing
                const inputs = card.querySelectorAll('input, textarea');
                inputs.forEach(input => {
                    input.value = '';
                });
                return;
            }
            card.remove();

            // Renumber remaining projects
            const cards = projectList.querySelectorAll('.cpbn-project-card');
            cards.forEach((card, index) => {
                const numberSpan = card.querySelector('.cpbn-project-number');
                if (numberSpan) {
                    numberSpan.textContent = `Project #${index + 1}`;
                }
            });
        };
    }

    // ============================================
    // DYNAMIC CERTIFICATIONS - Add/Remove
    // ============================================

    const certificationList = document.getElementById('certification-list');
    
    if (certificationList) {
        let certificationIndex = Number(certificationList.dataset.nextIndex) || 0;

        // Add Certification
        const addCertBtn = document.getElementById('add-certification');
        if (addCertBtn) {
            addCertBtn.addEventListener('click', function() {
                const card = document.createElement('div');
                card.className = 'cpbn-certification-card';

                card.innerHTML = `
                    <div class="cpbn-fgrid">
                        <div class="cpbn-field">
                            <label>Certification Name <span class="req">*</span></label>
                            <input type="text" name="certifications[${certificationIndex}][certification_name]"
                                   placeholder="e.g. AWS Cloud Practitioner" required>
                        </div>
                        <div class="cpbn-field">
                            <label>Issuing Organisation</label>
                            <input type="text" name="certifications[${certificationIndex}][issuing_organization]"
                                   placeholder="e.g. AWS, Cisco, Politeknik Brunei">
                        </div>
                        <div class="cpbn-field">
                            <label>Issue Date</label>
                            <input type="date" name="certifications[${certificationIndex}][issue_date]"
                                   max="${new Date().toISOString().split('T')[0]}">
                        </div>
                        <div class="cpbn-field">
                            <label>Certificate File</label>
                            <input type="file" name="certifications[${certificationIndex}][certificate_file]"
                                   accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png">
                            <small class="cpbn-file-note">PDF, JPG, JPEG or PNG · Maximum 5 MB</small>
                        </div>
                    </div>

                    <button type="button" class="cpbn-remove-certification" onclick="removeCertification(this)" data-confirm-delete data-item-name="this new certification">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;">
                            <path d="M18 6L6 18M6 6l12 12"/>
                        </svg>
                        Remove Certification
                    </button>
                `;

                certificationList.appendChild(card);
                certificationIndex++;
            });
        }

        // Remove Certification - Make it global
        window.removeCertification = function(button) {
            const card = button.closest('.cpbn-certification-card');
            if (certificationList.children.length <= 1) {
                // Just clear the inputs instead of removing
                const inputs = card.querySelectorAll('input, textarea');
                inputs.forEach(input => {
                    input.value = '';
                });
                return;
            }
            card.remove();
        };
    }

});

// ============================================
// START ALPINE.JS
// ============================================
Alpine.start();