import './bootstrap';
import '../css/app.css';

// Dark mode is always on
document.documentElement.classList.add('dark');

// Mobile-friendly modal handling
document.addEventListener('DOMContentLoaded', function() {
    // Close modals when clicking outside
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('bg-black') && e.target.classList.contains('bg-opacity-50')) {
            const modal = e.target;
            modal.classList.add('hidden');
        }
    });

    // Close modals on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modals = document.querySelectorAll('[id$="Modal"]');
            modals.forEach(modal => {
                if (!modal.classList.contains('hidden')) {
                    modal.classList.add('hidden');
                }
            });
        }
    });

    // Prevent body scroll when modal is open (mobile)
    const modals = document.querySelectorAll('[id$="Modal"]');
    modals.forEach(modal => {
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                    if (modal.classList.contains('hidden')) {
                        document.body.style.overflow = '';
                    } else {
                        document.body.style.overflow = 'hidden';
                    }
                }
            });
        });
        observer.observe(modal, { attributes: true });
    });
});

