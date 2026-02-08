let user_tracking_data = {}

const elements = document.querySelectorAll('.user-track');
elements.forEach(btn => {
    btn.addEventListener('click', function (e) {
        let cookieValue = getOrCreateUUIDCookieEOD("daily_visitor")
        let eventname = this.getAttribute("data-eventname")
        const eventdata = {};
        for (let attr of this.attributes) {
            if (attr.name.startsWith('data-eventdata-')) {
                const key = attr.name.replace('data-', '');
                eventdata[key] = attr.value;
            }
        }
        if (eventname != null && eventname != '' && eventname.length > 0) {
            sendData(
                baseUrl + "/track",
                {
                    "uuid": cookieValue,
                    "event_type": "click",
                    "event": eventname,
                    "source": baseUrl+currentUri,
                    ...eventdata
                },
                "POST"
            )
        }
    });
});

function initScrollTracking() {
    let fired = {
        navbarOut: false,
        scroll50: false,
        scrollEnd: false
    };

    const navbar = document.querySelector('.navbar-track-scroll');

    window.addEventListener('scroll', function () {
        const scrollTop = window.scrollY || document.documentElement.scrollTop;
        const windowHeight = window.innerHeight;
        const docHeight = document.documentElement.scrollHeight;

        const scrollPercent = (scrollTop + windowHeight) / docHeight * 100;
        const cookieValue = getOrCreateUUIDCookieEOD("daily_visitor");

        /* 1️⃣ Navbar goes out of viewport */
        if (navbar && !fired.navbarOut) {
            const rect = navbar.getBoundingClientRect();
            if (rect.bottom < 0) {
                fired.navbarOut = true;

                sendData(baseUrl + "/track", {
                    uuid: cookieValue,
                    event_type: "scroll",
                    event: "navbar_out_of_view",
                    source: baseUrl + currentUri
                }, "POST");
            }
        }

        /* 2️⃣ Page scrolled more than 50% */
        if (!fired.scroll50 && scrollPercent >= 50) {
            fired.scroll50 = true;

            sendData(baseUrl + "/track", {
                uuid: cookieValue,
                event_type: "scroll",
                event: "scroll_50_percent",
                source: baseUrl + currentUri,
                scroll_percentage: Math.round(scrollPercent)
            }, "POST");
        }

        /* 3️⃣ Page scrolled to end */
        if (!fired.scrollEnd && (scrollTop + windowHeight >= docHeight - 5)) {
            fired.scrollEnd = true;

            sendData(baseUrl + "/track", {
                uuid: cookieValue,
                event_type: "scroll",
                event: "scroll_to_end",
                source: baseUrl + currentUri
            }, "POST");
        }
    });
}
document.addEventListener('DOMContentLoaded', initScrollTracking);



function getOrCreateUUIDCookieEOD(cookieName) {
    // Check existing cookie
    const cookies = document.cookie.split('; ');
    for (const c of cookies) {
        const [name, value] = c.split('=');
        if (name === cookieName) {
            return decodeURIComponent(value);
        }
    }

    // Generate UUID
    const uuid = crypto.randomUUID();

    // Set expiry to today 23:59:59 (local time)
    const expiry = new Date();
    expiry.setHours(23, 59, 59, 999);

    document.cookie = `${cookieName}=${uuid};expires=${expiry.toUTCString()};path=/;SameSite=Lax`;

    return uuid;
}


/**
 * sendData - send data to a URI using GET or POST
 * @param {string} url - the endpoint to send data
 * @param {object} data - key/value pairs of data to send
 * @param {string} method - "GET" or "POST" (default: POST)
 */
function sendData(url, data = {}, method = 'POST') {
    method = method.toUpperCase();

    if (method === 'GET') {
        // Convert object to query string
        const queryString = new URLSearchParams(data).toString();
        url += '?' + queryString;

        return fetch(url, { method: 'GET' })
            .then(response => response.text())
            .catch(err => console.error('GET Error:', err));
    } else if (method === 'POST') {
        // Convert object to form-data string
        const formData = new URLSearchParams(data).toString();
        return fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: formData
        })
            .then(response => response.text())
            .catch(err => console.error('POST Error:', err));
    } else {
        throw new Error('Method must be GET or POST');
    }
}

