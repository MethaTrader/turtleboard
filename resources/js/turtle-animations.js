// resources/js/turtle-animations.js
document.addEventListener('DOMContentLoaded', function() {
    // Animation for turtle level up
    function animateTurtleLevelUp(turtleElement) {
        if (!turtleElement) return;

        // Add sparkle elements
        for (let i = 0; i < 10; i++) {
            const sparkle = document.createElement('div');
            sparkle.classList.add('turtle-sparkle');
            sparkle.style.left = `${Math.random() * 100}%`;
            sparkle.style.top = `${Math.random() * 100}%`;
            sparkle.style.animationDelay = `${Math.random() * 0.5}s`;
            turtleElement.appendChild(sparkle);
        }

        // Add level up text
        const levelUpText = document.createElement('div');
        levelUpText.classList.add('turtle-level-up-text');
        levelUpText.textContent = 'LEVEL UP!';
        turtleElement.appendChild(levelUpText);

        // Apply level up animation to the turtle
        turtleElement.classList.add('turtle-level-up-animation');

        // Remove elements after animation completes
        setTimeout(() => {
            const sparkles = turtleElement.querySelectorAll('.turtle-sparkle');
            sparkles.forEach(sparkle => sparkle.remove());
            levelUpText.remove();
            turtleElement.classList.remove('turtle-level-up-animation');
        }, 3000);
    }

    // Happiness indicator animations
    function initHappinessIndicator() {
        const happinessElements = document.querySelectorAll('.happiness-indicator');

        happinessElements.forEach(element => {
            const value = parseInt(element.dataset.value || 0);

            if (value >= 75) {
                element.classList.add('happiness-high');
            } else if (value >= 50) {
                element.classList.add('happiness-medium');
            } else if (value >= 25) {
                element.classList.add('happiness-low');
            } else {
                element.classList.add('happiness-critical');
            }
        });
    }

    // Turtle feeding animation
    function animateTurtleFeeding(turtleElement) {
        if (!turtleElement) return;

        // Create food element
        const food = document.createElement('div');
        food.classList.add('turtle-food');
        food.innerHTML = '<i class="fas fa-apple-alt"></i>';
        turtleElement.appendChild(food);

        // Animate food
        food.style.animation = 'foodDrop 1.5s forwards';

        // Animate turtle eating
        turtleElement.classList.add('turtle-eating-animation');

        // Remove elements after animation completes
        setTimeout(() => {
            food.remove();
            turtleElement.classList.remove('turtle-eating-animation');

            // Show heart particles
            showHeartParticles(turtleElement);
        }, 1500);
    }

    // Heart particles animation
    function showHeartParticles(turtleElement) {
        if (!turtleElement) return;

        // Add heart particles
        for (let i = 0; i < 5; i++) {
            const heart = document.createElement('div');
            heart.classList.add('turtle-heart-particle');
            heart.innerHTML = '<i class="fas fa-heart"></i>';
            heart.style.left = `${40 + Math.random() * 20}%`;
            heart.style.animationDelay = `${Math.random() * 0.3}s`;
            turtleElement.appendChild(heart);

            // Remove heart after animation
            setTimeout(() => {
                heart.remove();
            }, 2000);
        }
    }

    // Turtle idle animations
    function initTurtleIdleAnimations() {
        const turtleElements = document.querySelectorAll('.turtle-display');

        turtleElements.forEach(turtle => {
            // Random slight movement every few seconds
            setInterval(() => {
                if (Math.random() > 0.7) {
                    turtle.classList.add('turtle-idle-animation');

                    setTimeout(() => {
                        turtle.classList.remove('turtle-idle-animation');
                    }, 2000);
                }
            }, 5000);
        });
    }

    // Initialize all animations
    function initAllAnimations() {
        // Get turtle element
        const turtleElement = document.querySelector('.turtle-display');

        // Initialize happiness indicators
        initHappinessIndicator();

        // Initialize idle animations
        initTurtleIdleAnimations();

        // Attach feed button click handler
        const feedButton = document.querySelector('.feed-turtle-button');
        if (feedButton && turtleElement) {
            feedButton.addEventListener('click', () => {
                animateTurtleFeeding(turtleElement);
            });
        }

        // Listen for level up event
        document.addEventListener('turtle-level-up', () => {
            if (turtleElement) {
                animateTurtleLevelUp(turtleElement);
            }
        });
    }

    // Initialize animations when document is ready
    initAllAnimations();

    // Expose functions globally
    window.turtleAnimations = {
        levelUp: animateTurtleLevelUp,
        feed: animateTurtleFeeding,
        showHearts: showHeartParticles
    };
});