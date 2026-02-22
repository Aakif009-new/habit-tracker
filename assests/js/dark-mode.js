class DarkModeManager {
    constructor() {
        this.init();
    }

    init() {
        // Get theme preference from localStorage or system preference
        this.currentTheme = this.getInitialTheme();
        this.applyTheme(this.currentTheme);
        this.setupToggleButton();
        
        // Listen for system theme changes
        this.watchSystemTheme();
    }

    getInitialTheme() {
        // Check localStorage first
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme) {
            return savedTheme;
        }

        // Check system preference
        const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        if (systemPrefersDark) {
            return 'dark';
        }

        // Check if user has preference in database (via PHP)
        if (document.body.dataset.userTheme) {
            return document.body.dataset.userTheme;
        }

        return 'light';
    }

    applyTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        localStorage.setItem('theme', theme);
        
        // Update toggle button icon
        this.updateToggleIcon(theme);
        
        // Save preference to database via AJAX
        this.saveThemeToDatabase(theme);
    }

    toggleTheme() {
        const newTheme = this.currentTheme === 'dark' ? 'light' : 'dark';
        this.currentTheme = newTheme;
        this.applyTheme(newTheme);
    }

    setupToggleButton() {
        const toggleBtn = document.getElementById('darkModeToggle');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', () => this.toggleTheme());
        }
    }

    updateToggleIcon(theme) {
        const toggleBtn = document.getElementById('darkModeToggle');
        if (toggleBtn) {
            const icon = toggleBtn.querySelector('i');
            if (icon) {
                icon.className = theme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
            }
            toggleBtn.title = theme === 'dark' ? 'Switch to Light Mode' : 'Switch to Dark Mode';
        }
    }

    async saveThemeToDatabase(theme) {
        // Only if user is logged in
        if (!document.body.dataset.userId) return;

        try {
            const formData = new FormData();
            formData.append('theme', theme);

            await fetch('api/update_theme.php', {
                method: 'POST',
                body: formData,
                credentials: 'include'
            });
        } catch (error) {
            console.error('Failed to save theme preference:', error);
        }
    }

    watchSystemTheme() {
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
            // Only apply if user hasn't set a manual preference
            if (!localStorage.getItem('theme')) {
                this.currentTheme = e.matches ? 'dark' : 'light';
                this.applyTheme(this.currentTheme);
            }
        });
    }
}

// Initialize dark mode
document.addEventListener('DOMContentLoaded', () => {
    new DarkModeManager();
});