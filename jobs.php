<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Jobs - Job Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .job-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            cursor: pointer;
        }
        .job-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
        }
        .filter-sidebar {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
        }
        .search-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 60px 0;
        }
        .job-type-badge {
            font-size: 0.8rem;
        }
        .salary-highlight {
            color: #28a745;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.html">
                <i class="fas fa-briefcase me-2"></i>Job Portal
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.html">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="jobs.php">Browse Jobs</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.html#about">About</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="admin/login.php">
                            <i class="fas fa-user-shield me-1"></i>Admin Login
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Search Header -->
    <section class="search-header">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h1 class="text-center mb-4">Find Your Perfect Job</h1>
                    <div class="row justify-content-center">
                        <div class="col-lg-8">
                            <div class="input-group input-group-lg">
                                <input type="text" class="form-control" id="searchInput" placeholder="Search for jobs, companies, or keywords...">
                                <button class="btn btn-light" type="button" id="searchBtn">
                                    <i class="fas fa-search"></i> Search
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <div class="container my-5">
        <div class="row">
            <!-- Filters Sidebar -->
            <div class="col-lg-3 mb-4">
                <div class="filter-sidebar">
                    <h5 class="mb-3"><i class="fas fa-filter me-2"></i>Filters</h5>
                    
                    <!-- Job Type Filter -->
                    <div class="mb-4">
                        <h6>Job Type</h6>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="jobType" id="allTypes" value="" checked>
                            <label class="form-check-label" for="allTypes">All Types</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="jobType" id="fullTime" value="full-time">
                            <label class="form-check-label" for="fullTime">Full Time</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="jobType" id="partTime" value="part-time">
                            <label class="form-check-label" for="partTime">Part Time</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="jobType" id="contract" value="contract">
                            <label class="form-check-label" for="contract">Contract</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="jobType" id="internship" value="internship">
                            <label class="form-check-label" for="internship">Internship</label>
                        </div>
                    </div>

                    <!-- Location Filter -->
                    <div class="mb-4">
                        <h6>Location</h6>
                        <input type="text" class="form-control" id="locationFilter" placeholder="Enter location...">
                    </div>

                    <!-- Clear Filters -->
                    <button type="button" class="btn btn-outline-secondary w-100" id="clearFilters">
                        <i class="fas fa-times me-2"></i>Clear All Filters
                    </button>
                </div>
            </div>

            <!-- Job Listings -->
            <div class="col-lg-9">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 id="resultsCount">Loading jobs...</h4>
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="sortDropdown" data-bs-toggle="dropdown">
                            <i class="fas fa-sort me-2"></i>Sort By
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" data-sort="newest">Newest First</a></li>
                            <li><a class="dropdown-item" href="#" data-sort="oldest">Oldest First</a></li>
                            <li><a class="dropdown-item" href="#" data-sort="title">Title A-Z</a></li>
                        </ul>
                    </div>
                </div>

                <div id="jobListings" class="row">
                    <!-- Loading spinner -->
                    <div class="col-12 text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading jobs...</span>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <nav aria-label="Job listings pagination" class="mt-4">
                    <ul class="pagination justify-content-center" id="pagination">
                        <!-- Pagination will be generated by JavaScript -->
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <p>&copy; 2024 Job Portal. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-end">
                    <a href="#" class="text-white me-3"><i class="fab fa-facebook"></i></a>
                    <a href="#" class="text-white me-3"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="text-white me-3"><i class="fab fa-linkedin"></i></a>
                    <a href="#" class="text-white"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let allJobs = [];
        let filteredJobs = [];
        let currentPage = 1;
        const jobsPerPage = 6;

        document.addEventListener('DOMContentLoaded', function() {
            loadJobs();
            setupEventListeners();
        });

        function setupEventListeners() {
            // Search functionality
            document.getElementById('searchBtn').addEventListener('click', applyFilters);
            document.getElementById('searchInput').addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    applyFilters();
                }
            });

            // Filter controls
            document.querySelectorAll('input[name="jobType"]').forEach(radio => {
                radio.addEventListener('change', applyFilters);
            });

            document.getElementById('locationFilter').addEventListener('input', debounce(applyFilters, 500));
            document.getElementById('clearFilters').addEventListener('click', clearFilters);

            // Sort controls
            document.querySelectorAll('[data-sort]').forEach(item => {
                item.addEventListener('click', function(e) {
                    e.preventDefault();
                    sortJobs(this.dataset.sort);
                });
            });
        }

        function loadJobs() {
            fetch('api/get_jobs.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        allJobs = data.jobs;
                        filteredJobs = [...allJobs];
                        displayJobs();
                        updateResultsCount();
                    } else {
                        showError('Failed to load jobs: ' + data.error);
                    }
                })
                .catch(error => {
                    console.error('Error loading jobs:', error);
                    showError('Error loading jobs. Please try again later.');
                });
        }

        function applyFilters() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const jobType = document.querySelector('input[name="jobType"]:checked').value;
            const location = document.getElementById('locationFilter').value.toLowerCase();

            filteredJobs = allJobs.filter(job => {
                const matchesSearch = !searchTerm || 
                    job.title.toLowerCase().includes(searchTerm) ||
                    job.description.toLowerCase().includes(searchTerm) ||
                    (job.location && job.location.toLowerCase().includes(searchTerm));

                const matchesType = !jobType || job.job_type === jobType;
                
                const matchesLocation = !location || 
                    (job.location && job.location.toLowerCase().includes(location));

                return matchesSearch && matchesType && matchesLocation;
            });

            currentPage = 1;
            displayJobs();
            updateResultsCount();
        }

        function clearFilters() {
            document.getElementById('searchInput').value = '';
            document.getElementById('locationFilter').value = '';
            document.getElementById('allTypes').checked = true;
            
            filteredJobs = [...allJobs];
            currentPage = 1;
            displayJobs();
            updateResultsCount();
        }

        function sortJobs(sortType) {
            switch(sortType) {
                case 'newest':
                    filteredJobs.sort((a, b) => new Date(b.posted_date) - new Date(a.posted_date));
                    break;
                case 'oldest':
                    filteredJobs.sort((a, b) => new Date(a.posted_date) - new Date(b.posted_date));
                    break;
                case 'title':
                    filteredJobs.sort((a, b) => a.title.localeCompare(b.title));
                    break;
            }
            
            currentPage = 1;
            displayJobs();
        }

        function displayJobs() {
            const container = document.getElementById('jobListings');
            const startIndex = (currentPage - 1) * jobsPerPage;
            const endIndex = startIndex + jobsPerPage;
            const jobsToShow = filteredJobs.slice(startIndex, endIndex);

            if (jobsToShow.length === 0) {
                container.innerHTML = `
                    <div class="col-12 text-center">
                        <div class="alert alert-info">
                            <i class="fas fa-search me-2"></i>
                            No jobs found matching your criteria. Try adjusting your filters.
                        </div>
                    </div>
                `;
                return;
            }

            container.innerHTML = '';
            jobsToShow.forEach(job => {
                const jobCard = createJobCard(job);
                container.appendChild(jobCard);
            });

            generatePagination();
        }

        function createJobCard(job) {
            const col = document.createElement('div');
            col.className = 'col-md-6 col-lg-4 mb-4';
            
            const deadlineText = job.deadline ? 
                `<p class="text-danger small mb-1"><i class="fas fa-calendar-times me-1"></i>Apply by: ${new Date(job.deadline).toLocaleDateString()}</p>` : '';

            col.innerHTML = `
                <div class="card job-card h-100" onclick="viewJobDetails(${job.id})">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="card-title mb-1">${job.title}</h5>
                            <span class="badge bg-primary job-type-badge">${job.job_type.replace('-', ' ')}</span>
                        </div>
                        
                        <p class="text-muted mb-2">
                            <i class="fas fa-map-marker-alt me-1"></i>${job.location || 'Remote'}
                        </p>
                        
                        <p class="card-text flex-grow-1">${job.description.substring(0, 120)}${job.description.length > 120 ? '...' : ''}</p>
                        
                        ${job.salary_range ? `<p class="salary-highlight mb-2">${job.salary_range}</p>` : ''}
                        ${deadlineText}
                        
                        <div class="mt-auto">
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    <i class="fas fa-clock me-1"></i>${new Date(job.posted_date).toLocaleDateString()}
                                </small>
                                <button class="btn btn-outline-primary btn-sm" onclick="event.stopPropagation(); viewJobDetails(${job.id})">
                                    View Details
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            return col;
        }

        function generatePagination() {
            const totalPages = Math.ceil(filteredJobs.length / jobsPerPage);
            const pagination = document.getElementById('pagination');
            
            if (totalPages <= 1) {
                pagination.innerHTML = '';
                return;
            }

            let paginationHTML = '';
            
            // Previous button
            paginationHTML += `
                <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                    <a class="page-link" href="#" onclick="changePage(${currentPage - 1})">Previous</a>
                </li>
            `;

            // Page numbers
            for (let i = 1; i <= totalPages; i++) {
                if (i === 1 || i === totalPages || (i >= currentPage - 1 && i <= currentPage + 1)) {
                    paginationHTML += `
                        <li class="page-item ${i === currentPage ? 'active' : ''}">
                            <a class="page-link" href="#" onclick="changePage(${i})">${i}</a>
                        </li>
                    `;
                } else if (i === currentPage - 2 || i === currentPage + 2) {
                    paginationHTML += '<li class="page-item disabled"><span class="page-link">...</span></li>';
                }
            }

            // Next button
            paginationHTML += `
                <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                    <a class="page-link" href="#" onclick="changePage(${currentPage + 1})">Next</a>
                </li>
            `;

            pagination.innerHTML = paginationHTML;
        }

        function changePage(page) {
            const totalPages = Math.ceil(filteredJobs.length / jobsPerPage);
            if (page >= 1 && page <= totalPages) {
                currentPage = page;
                displayJobs();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        }

        function updateResultsCount() {
            const count = filteredJobs.length;
            const countElement = document.getElementById('resultsCount');
            
            if (count === 0) {
                countElement.textContent = 'No jobs found';
            } else if (count === 1) {
                countElement.textContent = '1 job found';
            } else {
                countElement.textContent = `${count} jobs found`;
            }
        }

        function viewJobDetails(jobId) {
            window.location.href = `job-details.php?id=${jobId}`;
        }

        function showError(message) {
            document.getElementById('jobListings').innerHTML = `
                <div class="col-12">
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>${message}
                    </div>
                </div>
            `;
        }

        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }
    </script>
</body>
</html>