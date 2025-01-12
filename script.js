// Wait for the DOM to load before executing the script
document.addEventListener('DOMContentLoaded', () => {
    // Responsive Navigation Menu Toggle
    const menuCheckbox = document.getElementById('menu-checkbox');
    const menuIcon = document.getElementById('menu-icon');
    const navLinks = document.querySelector('.nav-links');

    // Toggle navigation links on menu icon click
    menuIcon.addEventListener('click', () => {
        menuCheckbox.checked = !menuCheckbox.checked;
        // Optionally, toggle a class for animation purposes
        navLinks.classList.toggle('active');
    });

    // Close the menu when a navigation link is clicked (useful for single-page applications)
    const navLinkItems = document.querySelectorAll('.nav-links li a');
    navLinkItems.forEach(link => {
        link.addEventListener('click', () => {
            if (menuCheckbox.checked) {
                menuCheckbox.checked = false;
                navLinks.classList.remove('active');
            }
        });
    });

    // Dynamic Current Year in Footer
    const currentYearSpan = document.getElementById('current-year');
    const currentYear = new Date().getFullYear();
    currentYearSpan.textContent = currentYear;

    // Smooth Scrolling for Anchor Links
    const anchorLinks = document.querySelectorAll('a[href^="#"]');

    anchorLinks.forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            const targetID = this.getAttribute('href').substring(1);
            const targetElement = document.getElementById(targetID);

            if (targetElement) {
                // Scroll to the target element smoothly
                targetElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });

                // Update the URL hash without jumping
                history.pushState(null, null, `#${targetID}`);
            }
        });
    });
});
document.getElementById('submission').addEventListener('submit', async function (e) {
    e.preventDefault(); // Prevent default form submission

    const form = e.target;
    const formData = new FormData(form);

    // Convert FormData to URL-encoded string
    const formBody = new URLSearchParams(formData).toString();

    try {
        const response = await fetch('submit.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: formBody
        });

        const result = await response.json();
        const messageDiv = document.getElementById('formMessage');

        if (response.ok) {
            messageDiv.textContent = result.message;
            messageDiv.classList.remove('error');
            messageDiv.classList.add('success');
            form.reset(); // Reset the form
        } else {
            messageDiv.textContent = result.message;
            messageDiv.classList.remove('success');
            messageDiv.classList.add('error');
        }
    } catch (error) {
        const messageDiv = document.getElementById('formMessage');
        messageDiv.textContent = "An unexpected error occurred. Please try again later.";
        messageDiv.classList.remove('success');
        messageDiv.classList.add('error');
        console.error('Error:', error);
    }
});