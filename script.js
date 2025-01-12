document.addEventListener('DOMContentLoaded', () => {
    /* ====================================
       1. Responsive Navigation Menu Toggle
    ==================================== */
    const menuCheckbox = document.getElementById('menu-checkbox');
    const menuIcon = document.getElementById('menu-icon');
    const navLinks = document.querySelector('.nav-links');

    if (menuCheckbox && menuIcon && navLinks) {
        // Toggle navigation links on menu icon click
        menuIcon.addEventListener('click', () => {
            menuCheckbox.checked = !menuCheckbox.checked;
            navLinks.classList.toggle('active'); // For CSS animations or visibility
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
    } else {
        console.warn('Navigation elements not found. Please check your HTML structure.');
    }

    /* ====================================
       2. Dynamic Current Year in Footer
    ==================================== */
    const currentYearSpan = document.getElementById('current-year');
    if (currentYearSpan) {
        const currentYear = new Date().getFullYear();
        currentYearSpan.textContent = currentYear;
    } else {
        console.warn('Element with ID "current-year" not found.');
    }

    /* ====================================
       3. Smooth Scrolling for Anchor Links
    ==================================== */
    const anchorLinks = document.querySelectorAll('a[href^="#"]');

    anchorLinks.forEach(link => {
        link.addEventListener('click', function (e) {
            // Ignore links that just point to '#' without an actual target
            if (this.getAttribute('href') === '#') return;

            e.preventDefault();
            const targetID = this.getAttribute('href').substring(1);
            const targetElement = document.getElementById(targetID);

            if (targetElement) {
                targetElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });

                // Update the URL hash without jumping
                history.pushState(null, null, `#${targetID}`);
            }
        });
    });

    /* ====================================
       4. FAQ Section - Toggle Answers
    ==================================== */
    const faqItems = document.querySelectorAll('.faq-item');

    if (faqItems.length > 0) {
        faqItems.forEach(item => {
            const question = item.querySelector('.faq-item h2'); // Changed to a more specific class

            if (question) {
                // Click Event
                question.addEventListener('click', () => {
                    // Toggle the active class on the clicked FAQ item
                    const isActive = item.classList.contains('active');
                    item.classList.toggle('active', !isActive);

                    // Optionally, close other open FAQ items
                    faqItems.forEach(otherItem => {
                        if (otherItem !== item) {
                            otherItem.classList.remove('active');
                        }
                    });

                    // Update ARIA attributes for accessibility
                    question.setAttribute('aria-expanded', !isActive);
                    const answer = document.getElementById(question.getAttribute('aria-controls'));
                    if (!isActive) {
                        answer.removeAttribute('hidden');
                    } else {
                        answer.setAttribute('hidden', '');
                    }
                });

                // Optional: Allow toggling via keyboard (Enter and Space)
                question.setAttribute('tabindex', '0'); // Make it focusable
                question.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        question.click();
                    }
                });
            } else {
                console.warn('FAQ question element not found within .faq-item.');
            }
        });
    } else {
        console.warn('No elements with class "faq-item" found.');
    }

    /* ====================================
       5. Form Submission Handling
    ==================================== */
    const contactForm = document.getElementById('contactForm');

    if (contactForm) {
        contactForm.addEventListener('submit', async function (e) {
            e.preventDefault(); // Prevent default form submission

            const form = e.target;
            const formData = new FormData(form);

            // Convert FormData to URL-encoded string
            const formBody = new URLSearchParams(formData).toString();

            try {
                const response = await fetch('contact.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: formBody
                });

                // Attempt to parse JSON response
                const result = await response.json();
                const messageDiv = document.getElementById('formMessage');

                if (!messageDiv) {
                    console.warn('Element with ID "formMessage" not found.');
                    return;
                }

                if (response.ok && result.status === 'success') {
                    messageDiv.textContent = result.message || 'Form submitted successfully!';
                    messageDiv.classList.remove('error');
                    messageDiv.classList.add('success');
                    form.reset(); // Reset the form
                    // Reset reCAPTCHA
                    if (window.grecaptcha) {
                        grecaptcha.reset();
                    }
                } else {
                    messageDiv.textContent = result.message || 'There was an error submitting the form.';
                    messageDiv.classList.remove('success');
                    messageDiv.classList.add('error');
                }
            } catch (error) {
                const messageDiv = document.getElementById('formMessage');
                if (messageDiv) {
                    messageDiv.textContent = "An unexpected error occurred. Please try again later.";
                    messageDiv.classList.remove('success');
                    messageDiv.classList.add('error');
                }
                console.error('Error during form submission:', error);
            }
        });
    } else {
        console.warn('Form with ID "contactForm" not found.');
    }
});