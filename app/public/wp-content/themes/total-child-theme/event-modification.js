document.addEventListener("DOMContentLoaded", function() {
    console.log('heyyyyy dudeeee!')
    // Check if the body element has the class name 'logged-in'
    if (document.body.classList.contains('premiummember')) {
        // If the user is logged in, quit execution
        console.log('already logged in')
        return;
    } else {

        if (!window.location.href.includes("local-discounts")) {
            // Replace all instances of the first paragraph
            const offerParagraphs = document.querySelectorAll(".jre-localdiscounts-id-6");
            offerParagraphs.forEach(function(offerParagraph) {
                offerParagraph.innerHTML = 'The Offer: <span class="jre-offer-text-span">ONLY AVAILABLE TO PREMIUM MEMBERS</span>';
            });

            // Replace all instances of the second paragraph
            const discountVisitCtas = document.querySelectorAll(".jre-localdiscount-visit-cta");
            discountVisitCtas.forEach(function(discountVisitCta) {
                discountVisitCta.outerHTML = `
                    <p class="jre-localdiscount-visit-cta jre-localdiscount-visit-cta-join">
                        <a class="theme-button wpex-block wpex-text-center wpex-py-15 wpex-px-20 wpex-m-0 wpex-text-lg" href="/product/premium-membership/" target="_blank" rel="noopener noreferrer">Join Now</a>
                    </p>
                    <p class="jre-localdiscount-visit-cta jre-localdiscount-visit-login">
                        <a class="theme-button wpex-block wpex-text-center wpex-py-15 wpex-px-20 wpex-m-0 wpex-text-lg" href="/login-account-information/" target="_blank" rel="noopener noreferrer">Log In</a>
                    </p>`;
            });
        }
        
        
    }

    if ( ( !document.body.classList.contains('basicmember')) && ( document.body.classList.contains('membership-content')) && ( document.body.classList.contains('access-restricted'))  ) {


         // Look for the element with the specified class names
        const ticketsWrapper = document.querySelector('.tribe-common.event-tickets.tribe-tickets__tickets-wrapper');
        if (ticketsWrapper) {

            // Create overlay div
            const overlayDiv = document.createElement('div');
            overlayDiv.classList.add('overlay-div');

            // Create text element for line of text
            const textElement = document.createElement('p');

            // Get the element with class name tribe_events
            var tribeEventsElement = document.querySelector('.tribe_events');

            const ctaLink1 = document.createElement('a');

            textElement.textContent = 'This event is only available to Basic and Premium Members. Become a member to receive access to exclusive events two days before the wider Trying to Adult community.';
            // Create CTA link
            ctaLink1.textContent = 'Join Now';
            ctaLink1.href = 'https://tryingtoadultrva.com/product/membership/'; // Add your desired href here
            ctaLink1.classList.add('jre-members-only-event-button');
            ctaLink1.classList.add('jre-members-only-event-button-1'); // Add your custom button class for styling

            // Create CTA link
            const ctaLink2 = document.createElement('a');
            ctaLink2.textContent = 'Log In';
            ctaLink2.href = 'https://tryingtoadultrva.com/login-account-information/'; // Add your desired href here
            ctaLink2.classList.add('jre-members-only-event-button');
            ctaLink2.classList.add('jre-members-only-event-button-2'); // Add your custom button class for styling
           
            // Append text and button to overlay div
            overlayDiv.appendChild(textElement);
            overlayDiv.appendChild(ctaLink1);
            overlayDiv.appendChild(ctaLink2);

            // Append overlay div to element
            ticketsWrapper.appendChild(overlayDiv);

            return;

        }


    }

    // Check if the post date is less than 3 days in the past
    const dateelements = document.querySelectorAll('.jre-class-for-date');
    const today = new Date();






    dateelements.forEach(dateelement => {
        console.log('in the each')
        const classNames = dateelement.className.split(' ');
        const postDateClass = classNames.find(className => className.startsWith('post-date-'));
        if (postDateClass) {
            console.log('in if 1')
            const datePart = postDateClass.split('post-date-')[1];
            const postDate = new Date(datePart);
            const threeDaysAgo = new Date(today);
            threeDaysAgo.setDate(today.getDate() - 3);
            if (postDate >= threeDaysAgo) {
                console.log('in if 2')
                // Look for the element with the class name 'tribe_events_cat-members-early-access-event'
                const earlyAccessEvent = document.querySelector('.tribe_events_cat-members-early-access-event');
                if (earlyAccessEvent) {
                    console.log('in if 3')
                    // Look for the element with the specified class names
                    const ticketsWrapper = document.querySelector('.tribe-common.event-tickets.tribe-tickets__tickets-wrapper');
                    if (ticketsWrapper) {
                        console.log('in if 4')
                        // Create overlay div
                        const overlayDiv = document.createElement('div');
                        overlayDiv.classList.add('overlay-div');

                        // Create text element for line of text
                        const textElement = document.createElement('p');

                        // Get the element with class name tribe_events
                        var tribeEventsElement = document.querySelector('.tribe_events');

                        const ctaLink1 = document.createElement('a');
                        // Check if the element exists and has the specified class name
                        textElement.textContent = 'This event is only available to Premium Members. Become a member to receive access to exclusive events two days before the wider Trying to Adult community.';
                        // Create CTA link
                        ctaLink1.textContent = 'Join Now';
                        ctaLink1.href = 'https://tryingtoadultrva.com/product/premium-membership/'; // Add your desired href here
                        ctaLink1.classList.add('jre-members-only-event-button');
                        ctaLink1.classList.add('jre-members-only-event-button-1'); // Add your custom button class for styling

                        // Create CTA link
                        const ctaLink2 = document.createElement('a');
                        ctaLink2.textContent = 'Log In';
                        ctaLink2.href = 'https://tryingtoadultrva.com/login-account-information/'; // Add your desired href here
                        ctaLink2.classList.add('jre-members-only-event-button');
                        ctaLink2.classList.add('jre-members-only-event-button-2'); // Add your custom button class for styling
                       
                        // Append text and button to overlay div
                        overlayDiv.appendChild(textElement);
                        overlayDiv.appendChild(ctaLink1);
                        overlayDiv.appendChild(ctaLink2);

                        // Append overlay div to element
                        ticketsWrapper.appendChild(overlayDiv);




                    }
                }
            }
        }
    });
});
