// assets/js/main.js

// Habit Tracker Main Application
class HabitTracker {
    constructor() {
        this.userId = document.body.dataset.userId;
        this.habits = [];
        this.currentWeek = this.getCurrentWeekDates();
        this.init();
    }

    // Initialize the application
    init() {
        this.loadHabits();
        this.setupEventListeners();
    }

    // Setup all event listeners
    setupEventListeners() {
        // Habit form submission
        const habitForm = document.getElementById('habitForm');
        if (habitForm) {
            habitForm.addEventListener('submit', (e) => this.handleAddHabit(e));
        }

        // Listen for habit updates
        document.addEventListener('habitUpdated', () => this.loadHabits());
    }

    // Get dates for current week (Sunday to Saturday)
    getCurrentWeekDates() {
        const dates = [];
        const today = new Date();
        const firstDay = new Date(today);
        firstDay.setDate(today.getDate() - today.getDay()); // Start from Sunday
        
        for (let i = 0; i < 7; i++) {
            const date = new Date(firstDay);
            date.setDate(firstDay.getDate() + i);
            dates.push(this.formatDate(date));
        }
        return dates;
    }

    // Format date as YYYY-MM-DD
    formatDate(date) {
        return date.toISOString().split('T')[0];
    }

    // Load habits from server
    async loadHabits() {
        if (!document.getElementById('habitsContainer')) return;
        try {
            this.showLoading();
            const response = await fetch('api/get_habits.php', { credentials: 'include' });
            const data = await response.json();
            
            if (data.success) {
                this.habits = data.habits;
                this.renderHabits();
            } else {
                this.showError('Failed to load habits');
            }
        } catch (error) {
            console.error('Error loading habits:', error);
            this.showError('Network error. Please try again.');
        } finally {
            this.hideLoading();
        }
    }

    // Handle adding new habit
    async handleAddHabit(e) {
        e.preventDefault();
        
        const formData = new FormData();
        formData.append('habit_name', document.getElementById('habitName').value);
        formData.append('description', document.getElementById('habitDescription').value);
        formData.append('color', document.getElementById('habitColor').value);

        try {
            const response = await fetch('api/add_habit.php', {
                method: 'POST',
                body: formData,
                credentials: 'include'
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.showSuccess('Habit added successfully!');
                document.getElementById('habitForm').reset();
                this.loadHabits(); // Reload habits
            } else {
                this.showError(data.message || 'Failed to add habit');
            }
        } catch (error) {
            console.error('Error adding habit:', error);
            this.showError('Network error. Please try again.');
        }
    }

    // Toggle habit completion
    async toggleHabit(habitId, date, button) {
        // Optimistic update
        const wasCompleted = button.classList.contains('completed');
        button.classList.toggle('completed');
        
        try {
            const formData = new FormData();
            formData.append('habit_id', habitId);
            formData.append('log_date', date);
            formData.append('completed', !wasCompleted);

            const response = await fetch('api/complete_habit.php', {
                method: 'POST',
                body: formData,
                credentials: 'include'
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Update streak display
                if (data.streak) {
                    this.updateStreakDisplay(habitId, data.streak);
                }
                // Trigger chart update
                document.dispatchEvent(new CustomEvent('habitUpdated'));
            } else {
                // Revert on failure
                button.classList.toggle('completed');
                this.showError('Failed to update habit');
            }
        } catch (error) {
            // Revert on error
            button.classList.toggle('completed');
            console.error('Error toggling habit:', error);
            this.showError('Network error. Please try again.');
        }
    }

    // Delete habit
    async deleteHabit(habitId) {
        if (!confirm('Are you sure you want to delete this habit? This action cannot be undone.')) {
            return;
        }

        try {
            const formData = new FormData();
            formData.append('habit_id', habitId);

            const response = await fetch('api/delete_habit.php', {
                method: 'POST',
                body: formData,
                credentials: 'include'
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.showSuccess('Habit deleted successfully!');
                this.loadHabits(); // Reload habits
            } else {
                this.showError('Failed to delete habit');
            }
        } catch (error) {
            console.error('Error deleting habit:', error);
            this.showError('Network error. Please try again.');
        }
    }

    // Render all habits
    renderHabits() {
        const container = document.getElementById('habitsContainer');
        if (!container) return;

        if (this.habits.length === 0) {
            container.innerHTML = '<div class="empty-state">No habits yet. Create your first habit above!</div>';
            return;
        }

        container.innerHTML = '';
        this.habits.forEach(habit => this.renderHabit(habit, container));
    }

    // Render single habit
    renderHabit(habit, container) {
        const template = document.getElementById('habitCardTemplate');
        const card = template.content.cloneNode(true);
        
        // Set habit data
        const cardElement = card.querySelector('.habit-card');
        cardElement.dataset.habitId = habit.habit_id;
        cardElement.style.setProperty('--habit-color', habit.color);
        
        card.querySelector('.habit-name').textContent = habit.habit_name;
        card.querySelector('.habit-description').textContent = habit.description || 'No description';
        card.querySelector('.current-streak').textContent = habit.current_streak || 0;
        card.querySelector('.longest').textContent = habit.longest_streak || 0;

        // Create week grid
        const weekGrid = card.querySelector('.week-grid');
        this.currentWeek.forEach(date => {
            const dayBtn = this.createDayButton(habit, date);
            weekGrid.appendChild(dayBtn);
        });

        // Add delete handler
        const deleteBtn = card.querySelector('.btn-delete');
        deleteBtn.addEventListener('click', () => this.deleteHabit(habit.habit_id));

        container.appendChild(card);
    }

    // Create day button for habit
    createDayButton(habit, date) {
        const template = document.getElementById('dayButtonTemplate');
        const btn = template.content.cloneNode(true).querySelector('.day-btn');
        
        const dateObj = new Date(date);
        const dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        
        btn.dataset.date = date;
        btn.querySelector('.day-name').textContent = dayNames[dateObj.getDay()];
        btn.querySelector('.day-date').textContent = dateObj.getDate();

        // Check if completed
        const log = habit.logs?.find(log => log.log_date === date);
        if (log?.completed) {
            btn.classList.add('completed');
        }

        // Add click handler
        btn.addEventListener('click', () => 
            this.toggleHabit(habit.habit_id, date, btn)
        );

        return btn;
    }

    // Update streak display for a habit
    updateStreakDisplay(habitId, streakData) {
        const habitCard = document.querySelector(`.habit-card[data-habit-id="${habitId}"]`);
        if (habitCard) {
            const currentStreak = habitCard.querySelector('.current-streak');
            const longestStreak = habitCard.querySelector('.longest');
            
            currentStreak.textContent = streakData.current_streak;
            longestStreak.textContent = streakData.longest_streak;
        }
    }

    // Show loading state
    showLoading() {
        const container = document.getElementById('habitsContainer');
        if (!container) return;
        container.innerHTML = '<div class="loading"><i class="fas fa-spinner"></i> Loading habits...</div>';
    }

    // Hide loading state
    hideLoading() {
        // Loading is removed when rendering
    }

    // Show success message
    showSuccess(message) {
        this.showNotification(message, 'success');
    }

    // Show error message
    showError(message) {
        this.showNotification(message, 'error');
    }

    // Show notification
    showNotification(message, type) {
        const notification = document.createElement('div');
        notification.className = `${type}-message`;
        notification.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
            ${message}
        `;
        
        const container = document.querySelector('.container');
        container.insertBefore(notification, container.firstChild);
        
        // Auto remove after 3 seconds
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }
}

// Initialize app when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new HabitTracker();
});