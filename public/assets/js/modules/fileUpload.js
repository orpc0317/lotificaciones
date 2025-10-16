/**
 * File Upload Module
 * 
 * Handles file upload validation, preview generation,
 * and photo change functionality for employee photos.
 * 
 * @module fileUpload
 */

var FileUploadModule = (function() {
    'use strict';
    
    /**
     * Validate file type by MIME type
     * @param {File} file - File object to validate
     * @returns {boolean} True if valid
     */
    function validateFileType(file) {
        if (!file.type || ALLOWED_IMAGE_TYPES.indexOf(file.type) === -1) {
            ErrorHandler.showToast('Solo se permiten imágenes (JPG, PNG, GIF, WebP)');
            return false;
        }
        return true;
    }
    
    /**
     * Validate file size
     * @param {File} file - File object to validate
     * @returns {boolean} True if valid
     */
    function validateFileSize(file) {
        if (file.size > MAX_FILE_SIZE_BYTES) {
            ErrorHandler.showToast('La imagen no debe superar ' + MAX_FILE_SIZE_MB + 'MB');
            return false;
        }
        return true;
    }
    
    /**
     * Validate file signature (magic numbers)
     * @param {ArrayBuffer} buffer - File buffer
     * @returns {boolean} True if valid image
     */
    function validateFileSignature(buffer) {
        var arr = new Uint8Array(buffer).subarray(0, 4);
        var header = '';
        for (var i = 0; i < arr.length; i++) {
            header += arr[i].toString(16);
        }
        
        // Check magic numbers for common image formats
        return header.indexOf(MAGIC_NUMBERS.JPEG) === 0 ||
               header.indexOf(MAGIC_NUMBERS.PNG) === 0 ||
               header.indexOf(MAGIC_NUMBERS.GIF) === 0 ||
               header.indexOf(MAGIC_NUMBERS.WEBP) === 0;
    }
    
    /**
     * Update image preview
     * @param {HTMLInputElement} input - File input element
     * @param {string} dataUrl - Data URL of the image
     */
    function updatePreview(input, dataUrl) {
        var target = null;
        if (input.id === 'edit_foto') {
            target = document.getElementById('edit_foto_preview');
        } else if (input.id === 'foto') {
            target = document.getElementById('foto_preview');
        } else if (input.id === 'nuevo_foto') {
            target = document.getElementById('nuevo_foto_preview');
        }
        
        if (target) {
            target.src = dataUrl;
        }
    }
    
    /**
     * Handle file selection and validation
     * @param {HTMLInputElement} input - File input element
     */
    function handleFileChange(input) {
        var file = input.files && input.files[0];
        if (!file) return;
        
        // Validate file type
        if (!validateFileType(file)) {
            input.value = '';
            return;
        }
        
        // Validate file size
        if (!validateFileSize(file)) {
            input.value = '';
            return;
        }
        
        // Validate file signature (magic numbers)
        var reader = new FileReader();
        reader.onloadend = function(e) {
            try {
                if (!validateFileSignature(e.target.result)) {
                    ErrorHandler.showToast('El archivo no es una imagen válida');
                    input.value = '';
                    return;
                }
                
                // If valid, show preview
                var readerPreview = new FileReader();
                readerPreview.onload = function(ev) {
                    try {
                        var dataUrl = ev.target.result;
                        var img = new Image();
                        img.onload = function() {
                            try {
                                updatePreview(input, dataUrl);
                            } catch(e) {
                                ErrorHandler.log('FileUpload.UpdatePreview', e);
                            }
                        };
                        img.onerror = function() {
                            ErrorHandler.showToast('Error al cargar la imagen');
                            input.value = '';
                        };
                        img.src = dataUrl;
                    } catch(e) {
                        ErrorHandler.log('FileUpload.Preview', e);
                    }
                };
                readerPreview.readAsDataURL(file);
            } catch(err) {
                ErrorHandler.log('FileUpload.SignatureValidation', err);
                input.value = '';
            }
        };
        reader.readAsArrayBuffer(file.slice(0, 4));
    }
    
    /**
     * Initialize file upload event handlers
     */
    function init() {
        // Change photo button click handler
        $(document).on('click', '.change-photo-btn', function() {
            try {
                var btn = $(this);
                var card = btn.closest('.edit-photo-card');
                var fileInput = card.find('input[type="file"]');
                if (fileInput.length > 0) {
                    fileInput.click();
                }
            } catch(e) {
                ErrorHandler.log('FileUpload.ChangePhotoBtn', e);
            }
        });
        
        // File input change handler with validation
        $(document).on('change', 'input[type="file"][name="foto"]', function() {
            try {
                handleFileChange(this);
            } catch(e) {
                ErrorHandler.log('FileUpload.FileChange', e);
            }
        });
    }
    
    // Public API
    return {
        init: init,
        validateFileType: validateFileType,
        validateFileSize: validateFileSize,
        validateFileSignature: validateFileSignature
    };
})();
