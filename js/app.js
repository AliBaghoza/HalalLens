/**
 * HalalLens AI - Vanilla JavaScript Application Layer
 *
 * Key Architectural Decisions:
 * 1. Arabic is the primary default language.
 * 2. The backend (api/analyze.php) is the multi-agent orchestrator.
 *    The frontend sends ONE analysis request and renders the pipeline state.
 * 3. The Restaurant Finder is decoupled and queries OSM Overpass via api/restaurants.php.
 */

'use strict';

(function () {
    const config = window.HALALLENS_CONFIG || {
        currentLang: 'ar',
        direction: 'rtl',
        i18n: {}
    };

    const i18n = config.i18n;

    // DOM Elements
    const dropZone = document.getElementById('drop-zone');
    const imageInput = document.getElementById('image-input');
    const browseBtn = document.getElementById('browse-btn');
    const previewContainer = document.getElementById('preview-container');
    const previewImg = document.getElementById('preview-img');
    const previewFilename = document.getElementById('preview-filename');
    const previewFilesize = document.getElementById('preview-filesize');
    const removeBtn = document.getElementById('remove-btn');
    const clearBtn = document.getElementById('clear-btn');
    const submitBtn = document.getElementById('submit-btn');
    const submitBtnSpinner = submitBtn ? submitBtn.querySelector('.btn-spinner') : null;
    const submitBtnText = submitBtn ? submitBtn.querySelector('.btn-text') : null;
    const alertBanner = document.getElementById('alert-banner');
    const analyzeForm = document.getElementById('analyze-form');

    // Pipeline Steps
    const stepVision = document.getElementById('step-vision');
    const badgeVision = document.getElementById('badge-vision');
    const stepHalal = document.getElementById('step-halal');
    const badgeHalal = document.getElementById('badge-halal');

    // Results Elements
    const resultsIdle = document.getElementById('results-idle');
    const resultsContent = document.getElementById('results-content');
    const verdictBadge = document.getElementById('verdict-badge');
    const confidenceVal = document.getElementById('confidence-val');
    const ingredientsTags = document.getElementById('ingredients-tags');
    const reasonsList = document.getElementById('reasons-list');
    const assessmentsList = document.getElementById('assessments-list');
    const notesContainer = document.getElementById('notes-container');
    const notesList = document.getElementById('notes-list');

    // Restaurant Finder Elements
    const restaurantSearchInput = document.getElementById('restaurant-search-input');
    const btnSearchCity = document.getElementById('btn-search-city');
    const btnGpsLocation = document.getElementById('btn-gps-location');
    const restaurantStatus = document.getElementById('restaurant-status');
    const restaurantResults = document.getElementById('restaurant-results');

    let currentSelectedFile = null;

    /* ==========================================================================
       1. File Handling & Drag-and-Drop
       ========================================================================== */

    if (browseBtn && imageInput) {
        browseBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            imageInput.click();
        });
    }

    if (dropZone && imageInput) {
        dropZone.addEventListener('click', () => {
            if (!currentSelectedFile) {
                imageInput.click();
            }
        });

        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                dropZone.classList.add('dragover');
            });
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                dropZone.classList.remove('dragover');
            });
        });

        dropZone.addEventListener('drop', (e) => {
            const files = e.dataTransfer?.files;
            if (files && files.length > 0) {
                handleFileSelection(files[0]);
            }
        });

        imageInput.addEventListener('change', () => {
            if (imageInput.files && imageInput.files.length > 0) {
                handleFileSelection(imageInput.files[0]);
            }
        });
    }

    if (removeBtn) {
        removeBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            resetFileInput();
        });
    }

    if (clearBtn) {
        clearBtn.addEventListener('click', () => {
            resetFileInput();
            resetPipeline();
            resetResults();
        });
    }

    function handleFileSelection(file) {
        hideAlert();

        // Validate type
        const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        if (!allowedTypes.includes(file.type)) {
            showAlert(i18n.errorFileType || 'Unsupported file type. Please upload JPG, PNG, or WebP.');
            resetFileInput();
            return;
        }

        // Validate size (5MB)
        const maxBytes = 5 * 1024 * 1024;
        if (file.size > maxBytes) {
            showAlert(i18n.errorFileSize || 'File size exceeds maximum permitted limit (5MB).');
            resetFileInput();
            return;
        }

        currentSelectedFile = file;

        // Render preview
        const reader = new FileReader();
        reader.onload = (e) => {
            if (previewImg) previewImg.src = e.target.result;
            if (previewFilename) previewFilename.textContent = file.name;
            if (previewFilesize) previewFilesize.textContent = formatBytes(file.size);
            if (previewContainer) previewContainer.classList.remove('hidden');
            if (clearBtn) clearBtn.classList.remove('hidden');
            if (submitBtn) submitBtn.disabled = false;
        };
        reader.readAsDataURL(file);
    }

    function resetFileInput() {
        currentSelectedFile = null;
        if (imageInput) imageInput.value = '';
        if (previewImg) previewImg.src = '';
        if (previewFilename) previewFilename.textContent = '';
        if (previewFilesize) previewFilesize.textContent = '';
        if (previewContainer) previewContainer.classList.add('hidden');
        if (clearBtn) clearBtn.classList.add('hidden');
        if (submitBtn) submitBtn.disabled = true;
    }

    function formatBytes(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    /* ==========================================================================
       2. Alert & Pipeline State Management
       ========================================================================== */

    function showAlert(message) {
        if (!alertBanner) return;
        alertBanner.textContent = message;
        alertBanner.classList.remove('hidden');
    }

    function hideAlert() {
        if (!alertBanner) return;
        alertBanner.textContent = '';
        alertBanner.classList.add('hidden');
    }

    function updateStep(stepEl, badgeEl, status, statusText) {
        if (!stepEl || !badgeEl) return;
        stepEl.setAttribute('data-status', status);
        badgeEl.textContent = statusText;
    }

    function resetPipeline() {
        updateStep(stepVision, badgeVision, 'idle', i18n.waiting || 'Waiting');
        updateStep(stepHalal, badgeHalal, 'idle', i18n.waiting || 'Waiting');
    }

    function resetResults() {
        if (resultsIdle) resultsIdle.classList.remove('hidden');
        if (resultsContent) resultsContent.classList.add('hidden');
        if (verdictBadge) {
            verdictBadge.textContent = '-';
            verdictBadge.className = 'verdict-badge';
        }
        if (confidenceVal) confidenceVal.textContent = '--%';
        if (ingredientsTags) ingredientsTags.innerHTML = '';
        if (reasonsList) reasonsList.innerHTML = '';
        if (assessmentsList) assessmentsList.innerHTML = '';
        if (notesList) notesList.innerHTML = '';
        if (notesContainer) notesContainer.classList.add('hidden');
    }

    /* ==========================================================================
       3. Backend-Orchestrated Form Submission
       ========================================================================== */

    if (analyzeForm) {
        analyzeForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            if (!currentSelectedFile) {
                showAlert(i18n.errorNoFile || 'Please select an image first.');
                return;
            }

            hideAlert();
            resetResults();

            // Set UI to loading state
            if (submitBtn) submitBtn.disabled = true;
            if (submitBtnSpinner) submitBtnSpinner.classList.remove('hidden');

            // Visual Pipeline Tracker: Stage 1 Ingestion & Vision Active
            updateStep(stepVision, badgeVision, 'active', i18n.processing || 'Processing...');
            updateStep(stepHalal, badgeHalal, 'idle', i18n.waiting || 'Waiting');

            const formData = new FormData();
            formData.append('image', currentSelectedFile);
            formData.append('lang', config.currentLang || 'ar');

            try {
                // The frontend makes ONE call to the backend orchestrator
                const response = await fetch('api/analyze.php', {
                    method: 'POST',
                    body: formData,
                });

                const rawText = await response.text();
                let data;
                try {
                    data = JSON.parse(rawText);
                } catch (parseErr) {
                    const cleanMsg = rawText.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();
                    const snippet = cleanMsg ? cleanMsg.substring(0, 200) : (i18n.errorGeneric || 'Invalid server response.');
                    showAlert(snippet);
                    updateStep(stepVision, badgeVision, 'error', i18n.failed || 'Failed');
                    updateStep(stepHalal, badgeHalal, 'error', i18n.failed || 'Failed');
                    return;
                }

                if (!response.ok || !data.success) {
                    const errorMsg = data.error || (i18n.errorGeneric || 'Analysis request failed.');
                    showAlert(errorMsg);
                    updateStep(stepVision, badgeVision, 'error', i18n.failed || 'Failed');
                    updateStep(stepHalal, badgeHalal, 'error', i18n.failed || 'Failed');
                    return;
                }

                // Stage 1 Completed
                updateStep(stepVision, badgeVision, 'done', i18n.completed || 'Completed');

                // Stage 2 Completed (both orchestrated seamlessly by backend)
                updateStep(stepHalal, badgeHalal, 'done', i18n.completed || 'Completed');

                // Render the received multi-agent synthesized data
                const analysisData = data.product_analysis || data.data || {};
                renderResults(analysisData);

            } catch (err) {
                showAlert((i18n.errorGeneric || 'Network error occurred.') + ' ' + err.message);
                updateStep(stepVision, badgeVision, 'error', i18n.failed || 'Failed');
                updateStep(stepHalal, badgeHalal, 'error', i18n.failed || 'Failed');
            } finally {
                if (submitBtn) submitBtn.disabled = false;
                if (submitBtnSpinner) submitBtnSpinner.classList.add('hidden');
            }
        });
    }

    /* ==========================================================================
       4. Render Real Assessment Results
       ========================================================================== */

    function renderResults(data) {
        if (!data) return;

        const vision = data.ingredients || data.vision || {};
        const verification = data.halal_verification || data.verification || {};
        const isArabic = (config.currentLang === 'ar');

        // 1. Final Status Badge
        const rawStatus = (verification.status || 'UNKNOWN').toUpperCase();
        const localizedStatus = (isArabic ? verification.status_ar : verification.status_en) ||
                                (i18n.statuses && i18n.statuses[rawStatus]) || rawStatus;

        if (verdictBadge) {
            verdictBadge.textContent = localizedStatus;
            verdictBadge.className = 'verdict-badge status-' + rawStatus;
        }

        // 2. Confidence Percentage
        if (confidenceVal) {
            const conf = Math.round((verification.confidence || 0) * 100);
            confidenceVal.textContent = conf + '%';
        }

        // 3. Extracted Ingredients Tags
        if (ingredientsTags) {
            ingredientsTags.innerHTML = '';
            const list = vision.ingredients || [];
            if (list.length === 0) {
                ingredientsTags.innerHTML = '<span class="ingredient-tag">--</span>';
            } else {
                list.forEach(ing => {
                    const tag = document.createElement('span');
                    tag.className = 'ingredient-tag';
                    const name = (typeof ing === 'object' && ing !== null)
                        ? (ing.normalized_name || ing.original_name || '')
                        : String(ing);
                    tag.textContent = name;
                    ingredientsTags.appendChild(tag);
                });
            }
        }

        // 4. Overall Reasons List
        if (reasonsList) {
            reasonsList.innerHTML = '';
            const reasons = verification.reasons || [];
            if (reasons.length > 0) {
                reasons.forEach(r => {
                    const li = document.createElement('li');
                    li.className = 'reason-item';
                    li.textContent = r;
                    reasonsList.appendChild(li);
                });
            }
        }

        // 5. Individual Ingredient Assessments
        if (assessmentsList) {
            assessmentsList.innerHTML = '';
            const assessments = verification.ingredient_assessments || [];
            if (assessments.length > 0) {
                assessments.forEach(item => {
                    const card = document.createElement('div');
                    card.className = 'assessment-card';

                    const header = document.createElement('div');
                    header.className = 'assessment-card-header';

                    const nameEl = document.createElement('span');
                    nameEl.className = 'assessment-ing-name';
                    nameEl.textContent = item.ingredient;

                    const itemRawStatus = (item.status || 'UNKNOWN').toUpperCase();
                    const itemLocalizedStatus = isArabic ? (item.status_ar || i18n.statuses[itemRawStatus] || itemRawStatus)
                                                         : (item.status_en || i18n.statuses[itemRawStatus] || itemRawStatus);

                    const badgeEl = document.createElement('span');
                    badgeEl.className = 'assessment-status-badge status-' + itemRawStatus;
                    badgeEl.textContent = itemLocalizedStatus;

                    header.appendChild(nameEl);
                    header.appendChild(badgeEl);
                    card.appendChild(header);

                    if (item.reason) {
                        const reasonEl = document.createElement('p');
                        reasonEl.className = 'assessment-reason';
                        reasonEl.textContent = item.reason;
                        card.appendChild(reasonEl);
                    }

                    assessmentsList.appendChild(card);
                });
            }
        }

        // 6. Consumer Notes & Warnings
        if (notesList && notesContainer) {
            notesList.innerHTML = '';
            const notes = verification.notes || [];
            if (notes.length > 0) {
                notesContainer.classList.remove('hidden');
                notes.forEach(note => {
                    const li = document.createElement('li');
                    li.className = 'note-item';
                    li.textContent = note;
                    notesList.appendChild(li);
                });
            } else {
                notesContainer.classList.add('hidden');
            }
        }

        // Reveal results container
        if (resultsIdle) resultsIdle.classList.add('hidden');
        if (resultsContent) resultsContent.classList.remove('hidden');
    }

    /* ==========================================================================
       5. Decoupled Halal Restaurant Finder
       ========================================================================== */

    if (btnGpsLocation) {
        btnGpsLocation.addEventListener('click', () => {
            if (!navigator.geolocation) {
                setRestaurantStatus(i18n.locationDenied || 'Geolocation is not supported by your browser.');
                return;
            }

            setRestaurantStatus(i18n.processing || 'Locating current coordinates...');
            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    const lat = pos.coords.latitude;
                    const lng = pos.coords.longitude;
                    fetchNearbyRestaurants(lat, lng);
                },
                (err) => {
                    setRestaurantStatus(i18n.locationDenied || 'Location access denied or unavailable.');
                },
                { timeout: 10000 }
            );
        });
    }

    if (btnSearchCity && restaurantSearchInput) {
        btnSearchCity.addEventListener('click', () => {
            const query = restaurantSearchInput.value.trim();
            if (!query) return;
            searchLocationAndFetch(query);
        });

        restaurantSearchInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                const query = restaurantSearchInput.value.trim();
                if (query) searchLocationAndFetch(query);
            }
        });
    }

    async function searchLocationAndFetch(query) {
        setRestaurantStatus(i18n.processing || 'Searching location...');
        if (restaurantResults) restaurantResults.innerHTML = '';

        try {
            const geoRes = await fetch('api/geocode.php?q=' + encodeURIComponent(query));
            const geoData = await geoRes.json();

            if (!geoRes.ok || !geoData.success || !geoData.data || geoData.data.length === 0) {
                setRestaurantStatus(i18n.noRestaurants || 'No matching geographical location found.');
                return;
            }

            const firstMatch = geoData.data[0];
            fetchNearbyRestaurants(firstMatch.lat, firstMatch.lng);
        } catch (err) {
            setRestaurantStatus((i18n.errorGeneric || 'Failed to search location.') + ' ' + err.message);
        }
    }

    async function fetchNearbyRestaurants(lat, lng) {
        setRestaurantStatus(i18n.processing || 'Fetching nearby Halal restaurants...');
        if (restaurantResults) restaurantResults.innerHTML = '';

        try {
            const res = await fetch(`api/restaurants.php?lat=${encodeURIComponent(lat)}&lng=${encodeURIComponent(lng)}&radius=5000`);
            const data = await res.json();

            if (!res.ok || !data.success) {
                setRestaurantStatus(data.error || (i18n.errorGeneric || 'Failed to query restaurants.'));
                return;
            }

            const items = data.data || [];
            if (items.length === 0) {
                setRestaurantStatus(i18n.noRestaurants || 'No Halal restaurants found within 5 km.');
                return;
            }

            setRestaurantStatus('');
            renderRestaurantList(items);

        } catch (err) {
            setRestaurantStatus((i18n.errorGeneric || 'Failed to connect to restaurant service.') + ' ' + err.message);
        }
    }

    function setRestaurantStatus(msg) {
        if (!restaurantStatus) return;
        if (!msg) {
            restaurantStatus.textContent = '';
            restaurantStatus.classList.add('hidden');
        } else {
            restaurantStatus.textContent = msg;
            restaurantStatus.classList.remove('hidden');
        }
    }

    function renderRestaurantList(items) {
        if (!restaurantResults) return;
        restaurantResults.innerHTML = '';

        items.forEach(item => {
            const card = document.createElement('div');
            card.className = 'restaurant-card-item';

            const nameEl = document.createElement('h4');
            nameEl.className = 'restaurant-card-name';
            nameEl.textContent = item.name;

            const cuisineEl = document.createElement('p');
            cuisineEl.className = 'restaurant-card-meta';
            cuisineEl.innerHTML = `<strong>${i18n.cuisine || 'Cuisine:'}</strong> ${item.cuisine || 'Halal'}`;

            const distEl = document.createElement('p');
            distEl.className = 'restaurant-card-meta';
            distEl.innerHTML = `<strong>${i18n.distance || 'Distance:'}</strong> ${item.distance_km ? item.distance_km + ' km' : '--'}`;

            card.appendChild(nameEl);
            card.appendChild(cuisineEl);
            card.appendChild(distEl);

            if (item.address) {
                const addrEl = document.createElement('p');
                addrEl.className = 'restaurant-card-meta';
                addrEl.innerHTML = `<strong>${i18n.address || 'Address:'}</strong> ${item.address}`;
                card.appendChild(addrEl);
            }

            restaurantResults.appendChild(card);
        });
    }

})();
