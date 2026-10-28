/**
 * notifications.js - small UX helpers for the notifications page
 */
document.addEventListener('DOMContentLoaded', function () {
    const charCounter = document.getElementById('notifMessageCounter');
    const messageInput = document.getElementById('notifMessage');
    if (charCounter && messageInput) {
        const updateCounter = () => (charCounter.textContent = messageInput.value.length + ' characters');
        messageInput.addEventListener('input', updateCounter);
        updateCounter();
    }
});
