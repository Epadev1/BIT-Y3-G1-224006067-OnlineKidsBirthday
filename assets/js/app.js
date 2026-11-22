function validateField(field) {
    field.setCustomValidity('');
    var message = '';

    if (field.required && !field.value.trim()) {
        message = 'Please complete this field.';
    } else if (field.type === 'email' && field.value && field.validity.typeMismatch) {
        message = 'Enter a valid email address.';
    } else if (field.validity.tooShort) {
        message = 'Enter at least ' + field.minLength + ' characters.';
    } else if (field.validity.tooLong) {
        message = 'Use no more than ' + field.maxLength + ' characters.';
    } else if (field.validity.rangeUnderflow || field.validity.rangeOverflow) {
        message = 'Enter a value within the allowed range.';
    } else if (field.validity.stepMismatch) {
        message = 'Use the permitted number of decimal places.';
    }

    if (field.type === 'date' && field.value && field.min && field.value < field.min) {
        message = 'Choose a date that is not in the past.';
    }

    field.setCustomValidity(message);
    return field.checkValidity();
}

document.addEventListener('DOMContentLoaded', function () {
    var toggle = document.querySelector('.nav-toggle');
    var nav = document.querySelector('.main-nav');
    if (toggle && nav) {
        toggle.addEventListener('click', function () {
            var open = nav.classList.toggle('is-open');
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
    }

    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!window.confirm(form.dataset.confirm)) {
                event.preventDefault();
            }
        });
    });

    document.querySelectorAll('form[data-client-validation]').forEach(function (form) {
        var fields = Array.prototype.slice.call(form.querySelectorAll('input, select, textarea'));
        fields.forEach(function (field) {
            field.addEventListener('input', function () { validateField(field); });
            field.addEventListener('change', function () { validateField(field); });
        });
        form.addEventListener('submit', function (event) {
            var valid = fields.reduce(function (allValid, field) {
                return validateField(field) && allValid;
            }, true);
            if (!valid) {
                event.preventDefault();
                form.reportValidity();
            }
        });
    });

    var search = document.getElementById('vendor-search');
    var results = document.getElementById('vendor-results');
    var status = document.getElementById('search-status');
    var empty = document.getElementById('vendor-empty');
    if (!search || !results) {
        return;
    }

    var timer;
    var controller;
    var signedIn = results.dataset.signedIn === '1';
    var admin = results.dataset.admin === '1';
    var token = results.dataset.csrf || '';
    var service = results.dataset.service || '';

    function addText(parent, tagName, className, value) {
        var element = document.createElement(tagName);
        if (className) {
            element.className = className;
        }
        element.textContent = value;
        parent.appendChild(element);
        return element;
    }

    function renderVendors(vendors) {
        results.replaceChildren();
        vendors.forEach(function (vendor) {
            var card = document.createElement('article');
            card.className = 'vendor-card';
            var serviceIcons = { Cake: '🎂', Decorations: '🎈', Entertainment: '🎭', Photography: '📸' };
            var icon = serviceIcons[vendor.service_type] || '📸';
            addText(card, 'span', 'vendor-icon', icon);
            addText(card, 'span', 'tag', vendor.service_type);
            addText(card, 'h2', '', vendor.name);
            addText(card, 'p', 'vendor-price', 'From ' + Number(vendor.price).toLocaleString('en-US', { maximumFractionDigits: 0 }) + ' RWF');
            var email = document.createElement('a');
            email.className = 'vendor-contact';
            email.href = 'mailto:' + encodeURIComponent(vendor.contact_email);
            email.textContent = vendor.contact_email;
            card.appendChild(email);

            var cardActions = document.createElement('div');
            cardActions.className = 'vendor-card-actions';
            var bookingLink = document.createElement('a');
            bookingLink.className = 'text-link';
            bookingLink.href = signedIn
                ? 'index.php?r=bookings/create&vendor_id=' + encodeURIComponent(vendor.vendor_id)
                : 'index.php?r=login';
            bookingLink.textContent = signedIn ? 'Book this vendor →' : 'Sign in to book →';
            cardActions.appendChild(bookingLink);
            card.appendChild(cardActions);

            if (admin) {
                var actions = document.createElement('div');
                actions.className = 'admin-actions';
                var edit = document.createElement('a');
                edit.className = 'text-link';
                edit.href = 'index.php?r=vendors/edit&id=' + encodeURIComponent(vendor.vendor_id);
                edit.textContent = 'Edit';
                actions.appendChild(edit);
                var form = document.createElement('form');
                form.method = 'post';
                form.action = 'index.php?r=vendors/delete';
                form.dataset.confirm = 'Delete this vendor? Existing bookings will also be removed.';
                var csrf = document.createElement('input');
                csrf.type = 'hidden';
                csrf.name = '_csrf';
                csrf.value = token;
                var id = document.createElement('input');
                id.type = 'hidden';
                id.name = 'vendor_id';
                id.value = vendor.vendor_id;
                var remove = document.createElement('button');
                remove.type = 'submit';
                remove.className = 'link-button danger-link';
                remove.textContent = 'Delete';
                form.append(csrf, id, remove);
                form.addEventListener('submit', function (event) {
                    if (!window.confirm(form.dataset.confirm)) {
                        event.preventDefault();
                    }
                });
                actions.appendChild(form);
                card.appendChild(actions);
            }
            results.appendChild(card);
        });
        if (empty) {
            empty.hidden = vendors.length !== 0;
        }
    }

    search.addEventListener('input', function () {
        window.clearTimeout(timer);
        timer = window.setTimeout(function () {
            if (controller) {
                controller.abort();
            }
            controller = new AbortController();
            status.textContent = 'Searching vendors…';
            var url = results.dataset.api + '&q=' + encodeURIComponent(search.value.trim());
            if (service) {
                url += '&service=' + encodeURIComponent(service);
            }
            fetch(url, { headers: { Accept: 'application/json' }, signal: controller.signal })
                .then(function (response) {
                    if (!response.ok) {
                        throw new Error('Search request failed.');
                    }
                    return response.json();
                })
                .then(function (payload) {
                    if (payload?.ok !== true || !Array.isArray(payload.data?.vendors)) {
                        throw new Error('Unexpected search response.');
                    }
                    var vendors = payload.data.vendors;
                    renderVendors(vendors);
                    status.textContent = vendors.length + (vendors.length === 1 ? ' vendor found.' : ' vendors found.');
                })
                .catch(function (error) {
                    if (error.name !== 'AbortError') {
                        status.textContent = 'Vendor search is unavailable. Please try again.';
                    }
                });
        }, 220);
    });
});
