// ============================================
// SEARCHABLE DROPDOWN COMPONENT
// File: public/assets/js/searchable-dropdown.js
// ============================================

class SearchableDropdown {
    constructor(selectElement, options = {}) {
        this.select = typeof selectElement === 'string' 
            ? document.querySelector(selectElement) 
            : selectElement;
        
        if (!this.select) {
            console.error('Select element not found');
            return;
        }

        this.options = {
            placeholder: options.placeholder || 'Pilih...',
            searchPlaceholder: options.searchPlaceholder || 'Cari...',
            noResultsText: options.noResultsText || 'Tidak ada hasil',
            allowClear: options.allowClear !== undefined ? options.allowClear : true,
            ...options
        };

        this.isOpen = false;
        this.selectedValue = this.select.value;
        this.selectedText = this.select.options[this.select.selectedIndex]?.text || '';
        
        this.init();
    }

    init() {
        this.createDropdown();
        this.attachEvents();
        this.select.style.display = 'none';
    }

    createDropdown() {
        // Container
        this.container = document.createElement('div');
        this.container.className = 'searchable-dropdown';
        this.container.style.position = 'relative';
        this.container.style.width = '100%';

        // Selected Display
        this.selectedDisplay = document.createElement('div');
        this.selectedDisplay.className = 'searchable-dropdown-selected';
        this.selectedDisplay.innerHTML = `
            <span class="selected-text">${this.selectedText || this.options.placeholder}</span>
            <div class="dropdown-icons">
                ${this.options.allowClear ? '<span class="clear-icon" style="display: none;">×</span>' : ''}
                <span class="arrow-icon">▼</span>
            </div>
        `;

        // Dropdown Menu
        this.dropdownMenu = document.createElement('div');
        this.dropdownMenu.className = 'searchable-dropdown-menu';
        this.dropdownMenu.style.display = 'none';

        // Search Input
        this.searchInput = document.createElement('input');
        this.searchInput.type = 'text';
        this.searchInput.className = 'searchable-dropdown-search';
        this.searchInput.placeholder = this.options.searchPlaceholder;

        // Options List
        this.optionsList = document.createElement('div');
        this.optionsList.className = 'searchable-dropdown-options';

        this.populateOptions();

        this.dropdownMenu.appendChild(this.searchInput);
        this.dropdownMenu.appendChild(this.optionsList);
        this.container.appendChild(this.selectedDisplay);
        this.container.appendChild(this.dropdownMenu);

        this.select.parentNode.insertBefore(this.container, this.select);

        if (this.selectedValue) {
            this.updateClearButton();
        }
    }

    populateOptions(searchTerm = '') {
        this.optionsList.innerHTML = '';
        const options = Array.from(this.select.options);
        let hasResults = false;

        options.forEach((option, index) => {
            if (index === 0 && option.value === '') return;

            const text = option.textContent;
            const value = option.value;

            if (searchTerm && !text.toLowerCase().includes(searchTerm.toLowerCase())) {
                return;
            }

            hasResults = true;

            const optionDiv = document.createElement('div');
            optionDiv.className = 'searchable-dropdown-option';
            optionDiv.textContent = text;
            optionDiv.dataset.value = value;

            if (value === this.selectedValue) {
                optionDiv.classList.add('selected');
            }

            optionDiv.addEventListener('click', () => {
                this.selectOption(value, text);
            });

            this.optionsList.appendChild(optionDiv);
        });

        if (!hasResults) {
            const noResults = document.createElement('div');
            noResults.className = 'searchable-dropdown-no-results';
            noResults.textContent = this.options.noResultsText;
            this.optionsList.appendChild(noResults);
        }
    }

    selectOption(value, text) {
        this.selectedValue = value;
        this.selectedText = text;
        
        this.select.value = value;
        this.selectedDisplay.querySelector('.selected-text').textContent = text;
        
        // Trigger change event on original select
        const event = new Event('change', { bubbles: true });
        this.select.dispatchEvent(event);

        this.updateClearButton();
        this.close();
    }

    updateClearButton() {
        if (!this.options.allowClear) return;
        
        const clearIcon = this.selectedDisplay.querySelector('.clear-icon');
        if (this.selectedValue) {
            clearIcon.style.display = 'inline-block';
        } else {
            clearIcon.style.display = 'none';
        }
    }

    clear() {
        this.selectedValue = '';
        this.selectedText = '';
        this.select.value = '';
        this.selectedDisplay.querySelector('.selected-text').textContent = this.options.placeholder;
        
        const event = new Event('change', { bubbles: true });
        this.select.dispatchEvent(event);
        
        this.updateClearButton();
    }

    open() {
        this.isOpen = true;
        this.dropdownMenu.style.display = 'block';
        this.selectedDisplay.classList.add('open');
        this.searchInput.value = '';
        this.populateOptions();
        this.searchInput.focus();
    }

    close() {
        this.isOpen = false;
        this.dropdownMenu.style.display = 'none';
        this.selectedDisplay.classList.remove('open');
        this.searchInput.value = '';
    }

    attachEvents() {
        // Toggle dropdown
        this.selectedDisplay.addEventListener('click', (e) => {
            if (e.target.closest('.clear-icon')) {
                e.stopPropagation();
                this.clear();
                return;
            }
            
            if (this.isOpen) {
                this.close();
            } else {
                this.open();
            }
        });

        // Search
        this.searchInput.addEventListener('input', (e) => {
            this.populateOptions(e.target.value);
        });

        // Prevent close when clicking inside menu
        this.dropdownMenu.addEventListener('click', (e) => {
            e.stopPropagation();
        });

        // Close when clicking outside
        document.addEventListener('click', (e) => {
            if (!this.container.contains(e.target)) {
                this.close();
            }
        });

        // Keyboard navigation
        this.searchInput.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.close();
            }
        });
    }

    destroy() {
        this.container.remove();
        this.select.style.display = '';
    }
}

// Auto initialize with data-searchable attribute
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('select[data-searchable="true"]').forEach(select => {
        new SearchableDropdown(select, {
            placeholder: select.dataset.placeholder || 'Pilih...',
            searchPlaceholder: select.dataset.searchPlaceholder || 'Cari...',
            noResultsText: select.dataset.noResults || 'Tidak ada hasil',
            allowClear: select.dataset.allowClear !== 'false'
        });
    });
});