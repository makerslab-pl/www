/**
 * MakersLab - Admin Panel JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    initDeleteConfirmation();
    initFormAutoSave();
    initEmojiPicker();
    initAllegroUrlHelper();
    initTableSort();
    initSearchFilter();
    initCharacterCount();
});

/**
 * Delete Confirmation
 */
function initDeleteConfirmation() {
    const deleteForms = document.querySelectorAll('form[onsubmit*="confirm"]');
    
    deleteForms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            if (!confirm('Czy na pewno chcesz usunąć ten element?')) {
                e.preventDefault();
            }
        });
    });
}

/**
 * Form Auto-Save to LocalStorage
 */
function initFormAutoSave() {
    const forms = document.querySelectorAll('.card form');
    
    forms.forEach(function(form) {
        const formId = form.querySelector('input[name="module_id"], input[name="kit_id"]')?.value || 'new';
        const storageKey = 'makerslab_form_' + formId;
        
        // Restore saved data
        const savedData = localStorage.getItem(storageKey);
        if (savedData && formId === 'new') {
            const data = JSON.parse(savedData);
            Object.keys(data).forEach(function(key) {
                const input = form.querySelector('[name="' + key + '"]');
                if (input && input.type !== 'hidden' && input.type !== 'checkbox') {
                    input.value = data[key];
                }
            });
        }
        
        // Save on input
        form.addEventListener('input', debounce(function() {
            const formData = new FormData(form);
            const data = {};
            formData.forEach(function(value, key) {
                if (key !== 'csrf_token') {
                    data[key] = value;
                }
            });
            localStorage.setItem(storageKey, JSON.stringify(data));
        }, 500));
        
        // Clear on submit
        form.addEventListener('submit', function() {
            localStorage.removeItem(storageKey);
        });
    });
}

/**
 * Emoji Picker for Module Icons
 */
function initEmojiPicker() {
    const emojiButtons = document.querySelectorAll('.emoji-btn');
    const iconInput = document.getElementById('iconInput');
    
    if (!iconInput) return;
    
    emojiButtons.forEach(function(btn) {
        btn.addEventListener('click', function() {
            iconInput.value = this.textContent;
            
            // Update selected state
            emojiButtons.forEach(function(b) {
                b.classList.remove('selected');
            });
            this.classList.add('selected');
        });
    });
    
    // Mark current emoji as selected
    const currentEmoji = iconInput.value;
    emojiButtons.forEach(function(btn) {
        if (btn.textContent === currentEmoji) {
            btn.classList.add('selected');
        }
    });
}

/**
 * Allegro URL Helper
 * Generates search URL from keywords
 */
function initAllegroUrlHelper() {
    const allegroTitleInput = document.querySelector('input[name="allegro_title"]');
    const allegroUrlInput = document.querySelector('input[name="allegro_url"]');
    
    if (!allegroTitleInput || !allegroUrlInput) return;
    
    // Add helper button
    const helperBtn = document.createElement('button');
    helperBtn.type = 'button';
    helperBtn.className = 'btn btn-secondary btn-sm';
    helperBtn.style.marginTop = '10px';
    helperBtn.textContent = '🔗 Generuj link z nazwy';
    
    helperBtn.addEventListener('click', function() {
        const title = allegroTitleInput.value.trim();
        if (title) {
            const searchQuery = encodeURIComponent(title);
            allegroUrlInput.value = 'https://allegro.pl/listing?string=' + searchQuery;
        }
    });
    
    allegroTitleInput.parentElement.appendChild(helperBtn);
}

/**
 * Table Sorting
 */
function initTableSort() {
    const tables = document.querySelectorAll('table');
    
    tables.forEach(function(table) {
        const headers = table.querySelectorAll('th');
        
        headers.forEach(function(header, index) {
            if (header.textContent.trim() === 'Akcje') return;
            
            header.style.cursor = 'pointer';
            header.title = 'Kliknij aby sortować';
            
            header.addEventListener('click', function() {
                sortTable(table, index);
            });
        });
    });
}

function sortTable(table, columnIndex) {
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    
    const isAsc = table.dataset.sortOrder !== 'asc';
    table.dataset.sortOrder = isAsc ? 'asc' : 'desc';
    
    rows.sort(function(a, b) {
        const aText = a.cells[columnIndex]?.textContent.trim() || '';
        const bText = b.cells[columnIndex]?.textContent.trim() || '';
        
        // Try numeric sort first
        const aNum = parseFloat(aText);
        const bNum = parseFloat(bText);
        
        if (!isNaN(aNum) && !isNaN(bNum)) {
            return isAsc ? aNum - bNum : bNum - aNum;
        }
        
        // Fall back to string sort
        return isAsc ? aText.localeCompare(bText) : bText.localeCompare(aText);
    });
    
    rows.forEach(function(row) {
        tbody.appendChild(row);
    });
}

/**
 * Search/Filter for Tables
 */
function initSearchFilter() {
    const tables = document.querySelectorAll('.table-container');
    
    tables.forEach(function(container) {
        const table = container.querySelector('table');
        if (!table) return;
        
        // Create search input
        const searchInput = document.createElement('input');
        searchInput.type = 'text';
        searchInput.className = 'form-input';
        searchInput.placeholder = '🔍 Szukaj...';
        searchInput.style.marginBottom = '15px';
        searchInput.style.maxWidth = '300px';
        
        container.insertBefore(searchInput, table);
        
        searchInput.addEventListener('input', debounce(function() {
            const query = this.value.toLowerCase();
            const rows = table.querySelectorAll('tbody tr');
            
            rows.forEach(function(row) {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(query) ? '' : 'none';
            });
        }, 200));
    });
}

/**
 * Character Count for Textareas
 */
function initCharacterCount() {
    const textareas = document.querySelectorAll('textarea');
    
    textareas.forEach(function(textarea) {
        const maxLength = textarea.maxLength || 500;
        
        const counter = document.createElement('div');
        counter.className = 'form-hint';
        counter.style.textAlign = 'right';
        
        function updateCount() {
            const remaining = maxLength - textarea.value.length;
            counter.textContent = remaining + ' znaków pozostało';
            counter.style.color = remaining < 50 ? '#ff6b35' : '';
        }
        
        textarea.parentElement.appendChild(counter);
        textarea.addEventListener('input', updateCount);
        updateCount();
    });
}

/**
 * Utility: Debounce Function
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = function() {
            clearTimeout(timeout);
            func.apply(this, args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Preview Module Card
 */
function previewModule() {
    const form = document.querySelector('form');
    if (!form) return;
    
    const title = form.querySelector('[name="title"]')?.value || 'Tytuł';
    const subtitle = form.querySelector('[name="subtitle"]')?.value || 'Podtytuł';
    const icon = form.querySelector('[name="icon"]')?.value || '🔧';
    const description = form.querySelector('[name="description"]')?.value || 'Opis modułu...';
    
    const preview = document.createElement('div');
    preview.className = 'module-card';
    preview.innerHTML = `
        <div class="module-header">
            <span class="module-week">Podgląd</span>
            <span class="module-icon">${icon}</span>
        </div>
        <h3 class="module-title">${title}</h3>
        <p class="module-subtitle">${subtitle}</p>
        <p class="module-description">${description}</p>
    `;
    
    // Show in modal or sidebar
    console.log('Preview:', preview.innerHTML);
}

/**
 * Copy to Clipboard
 */
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        showNotification('Skopiowano do schowka!', 'success');
    }).catch(function() {
        showNotification('Nie udało się skopiować', 'error');
    });
}

/**
 * Show Notification Toast
 */
function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = 'alert alert-' + type;
    notification.style.position = 'fixed';
    notification.style.bottom = '20px';
    notification.style.right = '20px';
    notification.style.zIndex = '9999';
    notification.style.animation = 'fadeInUp 0.3s ease';
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(function() {
        notification.style.animation = 'fadeInUp 0.3s ease reverse';
        setTimeout(function() {
            notification.remove();
        }, 300);
    }, 3000);
}

/**
 * Export Contacts to CSV
 */
function exportContactsCSV() {
    const table = document.querySelector('table');
    if (!table) return;
    
    const rows = table.querySelectorAll('tr');
    let csv = [];
    
    rows.forEach(function(row) {
        const cells = row.querySelectorAll('th, td');
        const rowData = Array.from(cells).map(function(cell) {
            return '"' + cell.textContent.replace(/"/g, '""').trim() + '"';
        });
        csv.push(rowData.join(','));
    });
    
    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = 'kontakty_makerslab_' + new Date().toISOString().slice(0, 10) + '.csv';
    link.click();
}

/**
 * Keyboard Shortcuts
 */
document.addEventListener('keydown', function(e) {
    // Ctrl+S to save form
    if (e.ctrlKey && e.key === 's') {
        e.preventDefault();
        const submitBtn = document.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.click();
        }
    }
    
    // Escape to cancel/close
    if (e.key === 'Escape') {
        const cancelBtn = document.querySelector('.btn-secondary[href*="tab="]');
        if (cancelBtn) {
            cancelBtn.click();
        }
    }
});
