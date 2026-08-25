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
    // DELETE CONFIRMATION
    // ============================================
    document.querySelectorAll('[data-confirm-delete]').forEach(function(form) {
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

    // ============================================
    // UPDATE CONFIRMATION
    // ============================================
    document.querySelectorAll('[data-confirm-update]').forEach(function(form) {
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

});

// ============================================
// START ALPINE.JS
// ============================================
Alpine.start();